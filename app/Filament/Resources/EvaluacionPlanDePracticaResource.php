<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluacionPlanDePracticaResource\Pages;
use App\Filament\Resources\EvaluacionPlanDePracticaResource\RelationManagers;
use App\Models\EvaluacionPlanDePractica;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EvaluacionPlanDePracticaResource extends Resource
{
    protected static ?string $model = EvaluacionPlanDePractica::class;
    protected static ?string $navigationGroup = 'Comisiones permanentes';
    protected static ?string $navigationIcon = 'heroicon-o-pencil';
    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    $user = auth()->user();
     /** @var User $user */
    if ($user && $user->hasRole('Comision')) {
        $docente = \App\Models\Docente::where('user_id', $user->id)->first();

        if ($docente) {
            return $query->whereHas('integranteComision', function ($q) use ($docente) {
                $q->where('docente_id', $docente->id);
            });
        }

        return $query->whereRaw('0 = 1');
    }

    return $query;
}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('plan_practica_id')
                ->label('Nombre del estudiante')
                ->options(function () {
                    return \App\Models\PlanPractica::with('solicitude.estudiante')
                        ->get()
                        ->mapWithKeys(function ($estudiante) {
                            $nombre = optional($estudiante->solicitude->estudiante)->nombre;
                            return [$estudiante->id => $nombre ?? 'Sin nombre'];
                        });
                })
                ->reactive() 
                ->searchable()
                ->required(),
                
                Forms\Components\Select::make('integrante_comision_id')
                ->label('Nombre del evaluador')
                ->options(function (callable $get) {
                    $informeId = $get('plan_practica_id');
            
                    if (!$informeId) {
                        return [];
                    }
            
                    $informe = \App\Models\PlanPractica::with('comisionPermanente.integranteComision.docente')
                        ->find($informeId);
            
                    if (!$informe || !$informe->comisionPermanente) {
                        return [];
                    }
            
                    $user = auth()->user();
                    $docente = \App\Models\Docente::where('user_id', $user->id)->first();
            
                    $integrantes = $informe->comisionPermanente->integranteComision;
                    if ($docente) {
                        $miembro = $integrantes->firstWhere('docente_id', $docente->id);
                        if ($miembro) {
                            $nombre = optional($miembro->docente)->nombre;
                            $cargo = $miembro->cargo;
                            return [$miembro->id => "{$nombre} - {$cargo}"];
                        }
                    }
                    return $integrantes->mapWithKeys(function ($integrante) {
                        $nombre = optional($integrante->docente)->nombre;
                        $cargo = $integrante->cargo;
                        return [$integrante->id => "{$nombre} - {$cargo}"];
                    });
                })
                ->searchable()
                ->required()
                ->reactive(),
                Forms\Components\Radio::make('estado')
                ->label('Evaluación') // El label se mostrará encima
                ->options([
                    'Aprobado' => 'Aprobado', 
                    'Observado' => 'Observado',
                    'Desaprobado' => 'Desaprobado',
                ])
                ->columns(3) 
                ->reactive()
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    $set('activo', $state !== 'Observado');
                })
                ->required(),

                Forms\Components\Placeholder::make('informe_estudiante')
                ->label('Informe del estudiante')
                ->content(function ($get) {
                    $id = $get('plan_practica_id');
            
                    if (!$id) return 'Selecciona un estudiante';
            
                    // Intentamos primero obtener por ID directo (modo editar)
                    $planPractica = \App\Models\PlanPractica::with('solicitude')->find($id);
            
                    if (!$planPractica) {
                        // Si no existe ese plan, asumimos que estamos en modo crear y el ID es del estudiante
                        $planPractica = \App\Models\PlanPractica::whereHas('solicitude', function ($query) use ($id) {
                            $query->where('estudiante_id', $id);
                        })->with('solicitude')->latest()->first();
                    }
            
                    if (!$planPractica || !$planPractica->solicitude || !$planPractica->solicitude->informe) {
                        return 'Sin informe disponible';
                    }
            
                    return basename($planPractica->solicitude->informe);
                }),
            
        
                    Forms\Components\Textarea::make('observacion')
                    ->label('Observación')
                    ->maxLength(600)
                    ->rows(4)
                    ->required(fn (Get $get) => in_array($get('estado'), ['Desaprobado', 'Observado']))
                    ->visible(fn (Get $get) => in_array($get('estado'), ['Desaprobado', 'Observado'])),

                Forms\Components\Toggle::make('activo')
                ->label('Estado')
                ->reactive()
                ->disabled()
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('planPractica.solicitude.estudiante.nombre')
                    ->label('Estudiante')
                    ->extraAttributes([
                        'style' => 'width: 220px; word-wrap: break-word; white-space: normal;text-align: justify;',
                    ])
                    ->numeric()
                    ->searchable(),

                    Tables\Columns\TextColumn::make('planPractica.solicitude.informe')
                    ->label('Plan de práctica')
                    ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Sin archivo')
                    ->url(function ($record) {
                        if (!$record->planPractica?->solicitude?->informe) return null;
                        return asset('storage/' . str_replace('storage/', '', $record->planPractica->solicitude->informe));
                    })
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-document-text')
                    ->searchable()
                    ->extraAttributes([
                        'style' => 'width: 200px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; word-wrap: break-word;',
                    ]),
                    Tables\Columns\TextColumn::make('integranteComision.docente.nombre')
                    ->label('Integrante de Comisión')
                    ->formatStateUsing(fn ($state, $record) => $state . ' - ' . $record->integranteComision->cargo)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('observacion')
                    ->searchable()
                    ->extraAttributes([
                        'style' => 'width: 160px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; word-wrap: break-word;',
                    ]),
                    Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de evaluación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estado')
                ->label('Calificación')
                ->searchable()
                 ->color(fn ($state) => $state === 'Desaprobado' ? 'danger' : null)
                ,
                Tables\Columns\IconColumn::make('activo')
                    ->label('Estado')
                    ->boolean()
                    ->color(fn (bool $state) => $state ? 'primary' : 'gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('evaluar')
                ->label('Evaluar')
                ->icon('heroicon-o-document-check')
                ->color('primary')
                ->modalHeading('')
                ->modalSubmitActionLabel('Confirmar')
                 ->visible(fn ($record) => $record->estado !== 'Aprobado')
                ->modalWidth('2xl')
                ->form([
                    Forms\Components\Placeholder::make('')
                        ->content('📝 Evaluar Plan de práctica')
                        ->extraAttributes([
                            'class' => 'text-center text-xl font-bold mb-2',
                        ])
                        ->columnSpanFull(),
            
                    Forms\Components\Group::make([
                        Forms\Components\Radio::make('estado')
                            ->label('Resultado de la Evaluación')
                            ->options([
                                'Aprobado' => 'Aprobado', 
                                'Observado' => 'Observado',
                                'Desaprobado' => 'Desaprobado',
                            ])
                            ->required()
                            ->columnSpanFull()
                            ->extraAttributes([
                                'class' => 'flex justify-center gap-4 border rounded-lg p-4 mt-2',
                            ])
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state === 'Aprobado') {
                                    $set('observacion', null);
                                }
                            }),
                    ])
                    ->columnSpanFull(),
            
                    Forms\Components\Textarea::make('observacion')
                    ->placeholder('Escribe tus observaciones aquí...')
                    ->rows(5)
                    ->disabled(fn ($get) => $get('estado') === 'Aprobado')
                    ->required(fn ($get) => in_array($get('estado'), ['Desaprobado', 'Observado']))
                    ->dehydrated(fn ($get) => $get('estado') !== 'Aprobado') // ⬅️ esta línea soluciona el problema
                    ->columnSpanFull()
                    ->extraInputAttributes(['class' => 'mt-4']),
                    
                ])
                ->action(function ($record, $data) {
                    $record->update([
                        'estado' => $data['estado'],
                        'observacion' => $data['estado'] === 'Aprobado' ? null : ($data['observacion'] ?? null),
                        'activo' => $data['estado'] === 'Aprobado',
                    ]);
                }),
            
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvaluacionPlanDePracticas::route('/'),
            'create' => Pages\CreateEvaluacionPlanDePractica::route('/create'),
            'edit' => Pages\EditEvaluacionPlanDePractica::route('/{record}/edit'),
        ];
    }
}