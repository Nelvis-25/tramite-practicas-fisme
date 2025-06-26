<?php

namespace App\Filament\Widgets;

use App\Models\Docente;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\HtmlString;
use Filament\Widgets\TableWidget as BaseWidget;

class AsesorLista extends BaseWidget
{
    protected static ?string $heading = 'LISTADO DE ASESORES Y SU PARTICIPACIÓN EN PRÁCTICAS';
    protected static ?int $sort = 3;
    public static function canView(): bool
    {
          /** @var User $user */
          $user = auth()->user();
        return auth()->check() && !$user->hasRole('Estudiante');
    }


    public function table(Table $table): Table
    {
        return $table
->query(
            Docente::query()
                ->withCount([
                    'solicitude',
                    'solicitude as solicitudes_activas_count' => function (Builder $query) {
                        $query->where('activo', true)
                            ->whereIn('estado', ['Aceptado', 'Comisión asignada']);
                    },
                    'solicitude as solicitudes_inactivas_count' => function (Builder $query) {
                        $query->where(function ($q) {
                            $q->where('activo', false)
                            ->orWhere(function ($q2) {
                                $q2->where('activo', true)
                                    ->where('estado', 'Rechazado');
                            });
                        });
                    },
                ])
                ->has('solicitude')
                ->orderByDesc('solicitude_count')
        )
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Asesor')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => "{$record->grado_academico} {$state}"),
                                    
                Tables\Columns\TextColumn::make('solicitudes_inactivas_count')
                ->label('Finalizadas')
                ->numeric()
                ->sortable()
                 ->alignCenter()
                ->color('success')
                ->weight('bold')
                ->formatStateUsing(fn ($state) => $state ?? '0'),
                Tables\Columns\TextColumn::make('solicitudes_activas_count')
                    ->label('En proceso')
                    ->numeric()
                    ->sortable()
                     ->alignCenter()
                    ->color('primary')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => $state ?? '0'),
                Tables\Columns\TextColumn::make('estado_asesor')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(fn ($record) =>
                        $record->solicitudes_activas_count >= 5 ? 'OCUPADO' : 'DISPONIBLE'
                    )
                    ->color(fn ($record) =>
                        $record->solicitudes_activas_count >= 5 ? 'danger' : 'success'
                    )
            ])
            ->filters([
               SelectFilter::make('estado_asesor')
                ->label('Estado del Asesor')
                ->options([
                    'OCUPADO' => 'Asesores ocupados ',
                    'DISPONIBLE' => 'Asesores disponibles',
                ])
                ->query(function (Builder $query) {
                })
                ->modifyQueryUsing(function (Builder $query, $state) {
                    if ($state === 'OCUPADO') {
                        $query->having('solicitudes_activas_count', '>=', 5);
                    } elseif ($state === 'DISPONIBLE') {
                        $query->having('solicitudes_activas_count', '<', 5);
                    }
                })
            ])
            ->actions([
            Tables\Actions\Action::make('ver_estudiantes')
                    ->label('') // solo icono
                    ->icon('heroicon-o-eye')
                    ->tooltip('Ver estudiantes en proceso')
                    ->color('primary')
                    ->modalContent(function ($record) {
                        $estudiantes = $record->solicitude()
                            ->where('activo', true)
                            ->whereIn('estado', ['Aceptado', 'Comisión asignada'])
                            ->with('estudiante')
                            ->get();

                        if ($estudiantes->isEmpty()) {
                            return new HtmlString('<p class="text-sm text-gray-600 text-center">Este asesor aún no tiene estudiantes en proceso.</p>');
                        }

                        $tabla = '
                            <div class="overflow-x-auto">
                                <p class="text-sm text-blue-800 font-bold mb-4 text-center">Asesor: ' . $record->nombre . '</p>
                                <table class="min-w-full divide-y divide-gray-200 text-sm text-center">
                                    <thead class="bg-blue-100">
                                        <tr>
                                            <th class="px-4 py-2 font-semibold text-blue-900">Estudiante</th>
                                            <th class="px-4 py-2 font-semibold text-blue-900">Fecha Inicio</th>
                                            <th class="px-4 py-2 font-semibold text-blue-900">Fecha Fin</th>
                                            <th class="px-4 py-2 font-semibold text-blue-900">Estado Actual</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">';

                        foreach ($estudiantes as $solicitud) {
                            $nombre = $solicitud->estudiante->nombre ?? 'Sin nombre';
                            $inicio = $solicitud->fecha_inicio ? \Carbon\Carbon::parse($solicitud->fecha_inicio)->format('d/m/Y') : '-';
                            $fin = $solicitud->fecha_fin ? \Carbon\Carbon::parse($solicitud->fecha_fin)->format('d/m/Y') : '-';

                            $plan = \App\Models\PlanPractica::where('solicitude_id', $solicitud->id)->first();
                            $estadoActual = ($plan && $plan->estado === 'Aprobado')
                                ? '<span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full"> Desarrollo de Práctica</span>'
                                : '<span class="inline-block bg-blue-100 text-black-800 text-xs px-2 py-1 rounded-full">Plan prácticas</span>';

                            $tabla .= "
                                <tr>
                                    <td class='px-4 py-2'>{$nombre}</td>
                                    <td class='px-4 py-2'>{$inicio}</td>
                                    <td class='px-4 py-2'>{$fin}</td>
                                    <td class='px-4 py-2'>{$estadoActual}</td>
                                </tr>";
                        }

                        $tabla .= '
                                    </tbody>
                                </table>
                            </div>
                            <style>
                                .fi-modal footer button[data-dismiss] {
                                    background-color: #3b82f6 !important;
                                    color: white !important;
                                }
                            </style>';

                        return new HtmlString($tabla);
                    })
                    ->modalSubmitAction(false)
                    ->visible(fn ($record) => $record->solicitudes_activas_count > 0),
            ])

            ->defaultSort('solicitude_count', 'desc')
            ->emptyStateHeading('No hay asesores registrados')
            ->emptyStateDescription('Cuando los docentes sean asignados como asesores, aparecerán aquí');
    }
}