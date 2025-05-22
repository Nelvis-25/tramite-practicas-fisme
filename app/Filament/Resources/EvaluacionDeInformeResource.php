<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluacionDeInformeResource\Pages;
use App\Filament\Resources\EvaluacionDeInformeResource\RelationManagers;
use App\Models\EvaluacionDeInforme;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EvaluacionDeInformeResource extends Resource
{
    protected static ?string $model = EvaluacionDeInforme::class;
    protected static ?string $navigationGroup = 'Informe de practicas';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('informe_de_practica_id')
                    ->label('Informe de practica')
                   ->options(function () {
                        return \App\Models\InformeDePractica::with('solicitudInforme.practica.solicitude')
                            ->get()
                            ->mapWithKeys(function ($informe) {
                                 $nombre = optional($informe->solicitudInforme->practica->solicitude)->nombre;
                                return [$informe->id => $nombre ?? 'Sin nombre'];
                            });
                    })
                    ->reactive()
                    ->searchable()
                    ->required()
                    ->preload()
                    ,
                 Forms\Components\Select::make('jurado_de_informe_id')
                    ->label('Jurado de informe')
                    ->options(function () {
                        return \App\Models\JuradoDeInforme::with('docente')
                            ->get()
                            ->mapWithKeys(function ($jurado) {
                                $nombre = optional($jurado->docente)->nombre;
                                return [$jurado->id => $nombre ?? 'Sin nombre'];
                            });
                    })
                    ->searchable()
                    ->required()
                    ->preload()
                    ,
                Forms\Components\Radio::make('estado')
                ->label('Evaluación') 
                ->options([
                    'Aprobado' => 'Aprobado',
                    'Desaprobado' => 'Desaprobado',
                    'Observado' => 'Observado',
                ])
                ->columns(3) // Esto pone los botones en horizontal
                ->reactive()
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    $set('activo', $state !== 'Observado');
                })
                ->required(),
                 Forms\Components\Textarea::make('observacion')
                ->label('Observación')
                ->maxLength(900)
                ->rows(4)
                ->required(fn (Get $get) => in_array($get('estado'), ['Desaprobado', 'Observado']))
                ->visible(fn (Get $get) => in_array($get('estado'), ['Desaprobado', 'Observado'])),
            
            Forms\Components\Toggle::make('activo')
                ->label('Estado')
                ->reactive()
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //Tables\Columns\TextColumn::make('informeDePractica.solicitudInforme.practica.solicitude.nombre')
                  //  ->label('Informe de practica')
                  //  ->searchable()
                   // ->sortable()
                   // ->extraAttributes([
                   // 'style' => 'width: 400px; word-wrap: break-word; white-space: normal;text-align: justify;',
                   // ]),
                Tables\Columns\TextColumn::make('informeDePractica.solicitudInforme.estudiante.nombre')
                    ->label('Nombre del practicante')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('informeDePractica.solicitudInforme.informe')
                    ->label('Informe de practica') 
                    ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Sin archivo')
                    ->url(fn ($record) => $record->informeDePractica->solicitudInforme->informe ? asset('storage/'.str_replace('storage/', '', $record->informeDePractica->solicitudInforme->informe)) : null)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-document-text')
                    ->searchable()
                     ->extraAttributes([
                        'style' => 'width: 200px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;',
                    ]), 
                Tables\Columns\TextColumn::make('jurados.docente.nombre')
                    ->label('Jurado de informe')
                    ->formatStateUsing(fn ($state, $record) => $state . ' - ' . $record->jurados->cargo)
                    ->searchable()
                    ->sortable(),
              // Tables\Columns\TextColumn::make('observacion')
                   // ->searchable()
                   // ->extraAttributes([
                  //      'style' => 'width: 250px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; word-wrap: break-word;',
                   // ]),
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
                'style' => 'width: 350px; white-space: normal; line-height: 1.5; font-family: inherit; font-size: inherit; color: inherit;',
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
                     ->color(fn ($state) => $state === 'Desaprobado' ? 'danger' : null),
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
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('evaluar')
                ->label('Evaluar')
                ->icon('heroicon-o-document-check')
                    ->modalHeading('EVALUAR INFORME DE PRÁCTICA')
                    ->requiresConfirmation()
                     ->visible(fn ($record) => $record->estado !== 'Aprobado')
                    ->modalIcon('heroicon-o-document-magnifying-glass')
                    ->modalWidth('md')
                ->form([
                        Forms\Components\Radio::make('estado')
                            ->label('Resultado de la Evaluación')
                            ->options([
                                'Aprobado' => 'Aprobado', 
                                'Observado' => 'Observado',
                                'Desaprobado' => 'Desaprobado',
                            ])
                            ->required()
                            ->reactive()
                            ->columnSpanFull()
                            ->extraAttributes([
                                'class' => 'font-bold flex justify-center gap-4 border border-gray-300 rounded-lg p-4 mt-2',
                            ])

                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state === 'Aprobado') {
                                    $set('observacion', null);
                                }
                            })
                            ,
            
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
                        ->action(function (EvaluacionDeInforme $record, array $data) {
                        $decision = $data['estado'];

                        if ($decision === 'Aprobado') {
                            $record->update([
                                'estado' => 'Aprobado',
                            ]);

                            Notification::make()
                                ->title('Evaluacion registrada con exito')
                                ->info()
                                ->send();
                        }

                        if ($decision === 'Observado') {
                            $record->update([
                                'estado' => 'Observado',
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
            'index' => Pages\ListEvaluacionDeInformes::route('/'),
            'create' => Pages\CreateEvaluacionDeInforme::route('/create'),
            'edit' => Pages\EditEvaluacionDeInforme::route('/{record}/edit'),
        ];
    }
}
