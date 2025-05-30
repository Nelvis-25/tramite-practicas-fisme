<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluacionPlanDePracticaResource\Pages;
use App\Filament\Resources\EvaluacionPlanDePracticaResource\RelationManagers;
use App\Models\EvaluacionPlanDePractica;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
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
                    Tables\Columns\IconColumn::make('planPractica.solicitude.informe')
                     ->label('Plan de práctica')
                     ->icon('heroicon-o-document-text')
                     ->alignCenter()
                     ->color(fn ($record) => $record->planPractica->solicitude->informe ? 'primary' : 'danger')
                     ->url(fn ($record) => $record->planPractica->solicitude->informe? asset('storage/' . str_replace('storage/', '', $record->planPractica->solicitude->informe)) : null)
                     ->openUrlInNewTab()
                     ->tooltip(fn ($record) => $record->planPractica->solicitude->informe ? 'Ver plan de práctica' : 'Sin archivo'),
                    
                    Tables\Columns\TextColumn::make('integranteComision.docente.nombre')
                    ->label('Integrante de Comisión')
                    ->formatStateUsing(fn ($state, $record) => $state . ' - ' . $record->integranteComision->cargo)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de evaluación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('observaciones')
                    ->label('Observaciones')
                     ->formatStateUsing(function ($record) {
                    $observaciones = $record->observaciones()->pluck('observacion')->toArray();

                    if (count($observaciones) === 0) {
                        return 'Sin observaciones';
                    }

                            return implode('<br>', array_map(fn($obs) => '<span style="margin-right:8px;">-</span>' . e($obs), $observaciones));
                        })
                        ->html()
                        ->extraAttributes([
                            'style' => 'width: 335px; white-space: normal; line-height: 1.5; font-family: inherit; font-size: inherit; color: inherit;',
                        ])
                        ->searchable(
                    query: function (Builder $query, string $search) {
                        $query->whereHas('observaciones', function (Builder $subQuery) use ($search) {
                            $subQuery->where('observacion', 'like', "%{$search}%");
                        });
                    }),
             
                Tables\Columns\TextColumn::make('estado')
                ->label('Calificación')
                ->searchable()
                 ->color(fn ($state) => $state === 'Desaprobado' ? 'danger' : null)
                ,
                 Tables\Columns\IconColumn::make('activo')
                    ->label('Estado')
                    ->boolean()
                    ->color(fn (bool $state) => $state ? 'primary' : 'gray')
                    ->tooltip(function ($record) {
                        return match($record->estado) {
                            'Aprobado', 'Desaprobado' => 'Evaluado',
                            'Observado' => 'En evaluación',
                            'Pendiente' => 'Por evaluar',
                            default => 'Sin estado',
                        };
                    })
                    ->searchable(),
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
                        ->label('Evaluar ')
                        ->icon('heroicon-o-document-check')
                        ->modalHeading('EVALUACIÓN DEL PLAN DE PRÁCTICA')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-clipboard-document-check')
                        ->modalSubmitActionLabel('Guardar')
                        ->modalWidth('md')
                        ->visible(fn ($record) => $record->estado !== 'Aprobado')
                        ->form([
                            Forms\Components\Radio::make('estado')
                                ->label('Resultado de la Evaluación')
                                ->options([
                                    'Aprobado' => 'Aprobado', 
                                    'Observado' => 'Observado',
                                    'Desaprobado' => 'Desaprobado',
                                ])
                                ->reactive() // ⚠️ clave para que se actualicen los campos dependientes
                                ->required()
                                ->columnSpanFull()
                                 ->extraAttributes([
                                'class' => 'font-bold flex justify-center gap-4 border border-gray-300 rounded-lg p-4 mt-2',
                                  ])
                                ->afterStateUpdated(function (callable $set, $state) {
                                    if ($state === 'Aprobado') {
                                        $set('observacion', null);
                                    }
                                }),

                             Forms\Components\Textarea::make('observacion')
                                ->label('Observaciones')
                                ->columnSpanFull()
                                ->maxLength(500)
                                ->placeholder('Escribe tus observaciones aquí...')
                                ->rows(5)
                                ->disabled(fn ($get) => $get('estado') === 'Aprobado')
                                ->visible(fn ($get) => in_array($get('estado'), ['Observado', 'Desaprobado']))
                                ->required(fn ($get) => in_array($get('estado'), ['Desaprobado', 'Observado']))
                                ->reactive()
                                ->columnSpanFull()
                                ->extraInputAttributes(['class' => 'mt-4']),
                        ])
                         ->action(function (EvaluacionPlanDePractica $record, array $data) {
                        $decision = $data['estado'];

                        if ($decision === 'Aprobado') {
                            $record->update([
                                'estado' => 'Aprobado',
                                'activo' => true,
                            ]);

                            Notification::make()
                                ->title('Evaluacion registrada con exito')
                                ->info()
                                ->send();
                        }

                        if ($decision === 'Observado') {
                            $record->update([
                                'estado' => 'Observado',
                                'activo' => false,
                            ]);
                            $record->observaciones()->create([
                                'observacion' => $data['observacion'],
                              
                            ]);

                            Notification::make()
                                ->title('Evaluacion registrada con exito')
                                ->info()
                                ->send();
                        }
                        if ($decision === 'Desaprobado') {
                            $record->update([
                                'estado' => 'Desaprobado',
                                'activo' => true,
                            ]);
                            $record->observaciones()->create([
                                'observacion' => $data['observacion'],
                             
                            ]);

                            Notification::make()
                                ->title('Evaluacion registrada con exito')
                                ->info()
                                ->send();
                        }
                    })
                    
                    ->modalSubmitActionLabel('Guardar')
                    ->modalCancelActionLabel('Cancelar'),
            
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