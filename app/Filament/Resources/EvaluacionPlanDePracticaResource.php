<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluacionPlanDePracticaResource\Pages;
use App\Filament\Resources\EvaluacionPlanDePracticaResource\RelationManagers;
use App\Models\EvaluacionPlanDePractica;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
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
                ->label('Estudiante')
                ->searchable()
                ->required()
                ->options(function ($get) {
                    $planPracticaId = $get('plan_practica_id');
            
                    if ($planPracticaId) {
                        $planPractica = \App\Models\PlanPractica::find($planPracticaId);
                        
                        if ($planPractica && $planPractica->solicitude && $planPractica->solicitude->estudiante) {
                            return [
                                $planPractica->id => optional($planPractica->solicitude->estudiante)->nombre ?? 'Sin nombre'
                            ];
                        }
                    }

                    return \App\Models\Estudiante::pluck('nombre', 'id');
                })
                ->getOptionLabelFromRecordUsing(function ($record) {  
                    return optional($record->solicitude?->estudiante)->nombre ?? 'Sin nombre';
                }),
                Forms\Components\Select::make('integrante_comision_id')
                ->label('Integrante de Comisión')
                ->relationship('integranteComision', 'id')
                ->getOptionLabelFromRecordUsing(function ($record) {
                    $nombre = optional($record->docente)->nombre;
                    $cargo = $record->cargo ?? 'Sin cargo';
                    return "$nombre - $cargo";
                })
                ->searchable()
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
            
                Forms\Components\TextInput::make('estado')
                    ->required(),
                Forms\Components\TextInput::make('observacion')
                    ->maxLength(600),
                Forms\Components\Toggle::make('activo')
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
                    ->searchable(),
                    Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de evaluación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estado'),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean()
                    ->color(fn (bool $state) => $state ? null : 'gray'),
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