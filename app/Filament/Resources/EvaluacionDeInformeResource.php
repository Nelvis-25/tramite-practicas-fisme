<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluacionDeInformeResource\Pages;
use App\Filament\Resources\EvaluacionDeInformeResource\RelationManagers;
use App\Models\Docente;
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
    protected static ?string $navigationGroup = 'Informe de Prácticas';
    protected static ?string $navigationLabel = 'Evaluar Informe de Prácticas';
    protected static ?string $navigationIcon = 'heroicon-o-pencil';
    protected static ?int $navigationSort = 3;
    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    $user = auth()->user();

    /** @var User $user */
    if ($user && $user->hasRole('Jurado de informe')) {
        $docente = Docente::where('user_id', $user->id)->first();

        if ($docente) {
            return $query->whereHas('jurados', function ($q) use ($docente) {
                $q->where('docente_id', $docente->id);
            });
        }

        return $query->whereRaw('0 = 1'); // Si no es docente, no retorna nada
    }

    return $query;
}

    public static function form(Form $form): Form
{
    return $form
    ->extraAttributes(['class' => 'p-6 bg-white shadow rounded border'])
    ->schema([
        Forms\Components\Grid::make(13)
            ->schema([
                
                Forms\Components\Select::make('informe_de_practica_id')
                    ->label('Informe de práctica')
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
                    ->columnSpan([
                        'default' => 11, // móvil
                        'md' => 7,       // escritorio
                    ]),

                Forms\Components\Placeholder::make('') // espacio vacío
                    ->columnSpan([
                        'default' => 0, // en móvil no hace falta espacio vacío
                        'md' => 1,
                    ]),

                Forms\Components\Select::make('ronda')
                    ->label('N° de evaluación ')
                    ->options([
                        1 => 'Primera evaluación',
                        2 => 'Segunda evaluación',
                    ])
                    ->default(2)
                    ->disabled()
                    ->required()
                    ->columnSpan([
                        'default' => 3,
                        'md' => 3,
                    ]),

                
                    Forms\Components\Select::make('jurado_de_informe_id')
                        ->label('Jurado de informe')
                        ->options(function (callable $get) {
                            $informeId = $get('informe_de_practica_id');

                            if (!$informeId) {
                                return [];
                            }

                            $informe = \App\Models\InformeDePractica::with('jurados.docente')
                                ->find($informeId);

                            if (!$informe || !$informe->jurados) {
                                return [];
                            }

                            $user = auth()->user();
                            $docente = \App\Models\Docente::where('user_id', $user->id)->first();

                            $juradosInforme = $informe->jurados;

                            // Si el docente actual es jurado, mostrar solo él
                            if ($docente) {
                                $juradoDelDocente = $juradosInforme->firstWhere('docente_id', $docente->id);
                                if ($juradoDelDocente) {
                                    $nombre = optional($juradoDelDocente->docente)->nombre ?? 'Sin nombre';
                                    $cargo = $juradoDelDocente->cargo;
                                    return [$juradoDelDocente->id => "{$nombre} - {$cargo}"];
                                }
                            }

                            // Si no es jurado, mostrar todos
                            return $juradosInforme->mapWithKeys(function ($jurado) {
                                $nombre = optional($jurado->docente)->nombre ?? 'Sin nombre';
                                
                                $cargo = $jurado->cargo;
                                return [$jurado->id => "{$nombre} - {$cargo}"];
                            });
                        })
                        ->searchable()
                        ->required()
                        ->preload()
                        ->columnSpan([
                            'default' => 11,
                            'md' => 7,
                        ]),


                Forms\Components\Placeholder::make('') 
                    ->columnSpan([
                        'default' => 0,
                        'md' => 1,
                    ]),

                Forms\Components\TextInput::make('nota')
                    ->label('Nota')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(20)
                    ->nullable()
                    ->required()
                    ->columnSpan([
                        'default' => 2,
                        'md' => 2,
                    ]),
               
                Forms\Components\Toggle::make('activo')
                    ->label('Estado')
                    ->reactive()
                    ->default(true)
                    ->required()
                    ->disabled()
                    ->columnSpan([
                        'default' => 13,
                        'md' => 4,
                    ]),
            ]),
    ]);
}
    public static function table(Table $table): Table
    {
        return $table
        
            ->columns([
                Tables\Columns\TextColumn::make('informeDePractica.solicitudInforme.practica.solicitude.nombre')
                  ->label('Informe ')
                   ->searchable()
                   ->sortable()
                   ->extraAttributes([
                   'style' => 'width: 400px; word-wrap: break-word; white-space: normal;text-align: justify;',
                    ])
                    ->toggleable(isToggledHiddenByDefault: true), 
                Tables\Columns\TextColumn::make('informeDePractica.solicitudInforme.estudiante.nombre')
                    ->label('Nombre del Practicante')
                    ->searchable()
                    ->sortable(),
                
                 Tables\Columns\IconColumn::make('informeDePractica.solicitudInforme.informe')
                     ->label('Informe')
                     ->icon('heroicon-o-document-text')
                     ->alignCenter()
                     ->color(fn ($record) => $record->informeDePractica->solicitudInforme->informe ? 'primary' : 'danger')
                     ->url(fn ($record) => $record->informeDePractica->solicitudInforme->informe? asset('storage/' . str_replace('storage/', '', $record->informeDePractica->solicitudInforme->informe)) : null)
                     ->openUrlInNewTab()
                     ->tooltip(fn ($record) => $record->informeDePractica->solicitudInforme->informe ? 'Ver informe' : 'Sin archivo'),
                
                
                Tables\Columns\TextColumn::make('jurados.docente.nombre')
                    ->label('Jurado de Informe')
                    ->formatStateUsing(fn ($state, $record) => $state . ' - ' . $record->jurados->cargo)
                    ->searchable()
                    ->sortable(),

                  Tables\Columns\TextColumn::make('informeDePractica.fecha_sustentacion')
                    ->label('Fecha programada')
                    ->alignCenter()
                    ->searchable()
                    ->dateTime('d-m-Y h:i')
                    ->sortable()
                     
                    ->placeholder('No programada'),

                Tables\Columns\TextColumn::make('nota')
                    ->label('Nota')
                    ->searchable()
                    ->color(function ($record) {
                            return $record->nota < 12 ? 'danger' : 'primary';
                        }),
                Tables\Columns\TextColumn::make('promedio_ronda')
                ->label('Promedio  ')
                ->getStateUsing(function ($record) {
                    $plan = $record->informeDePractica;
                    $rondaActual = $record->ronda;
                    $evaluaciones = $plan->evaluaciones()
                        ->where('ronda', $rondaActual)
                        ->where('estado', 'Evaluado')
                        ->get();

                    if ($evaluaciones->count() < 3) {
                        return '-'; 
                    }

                    // Calcular el promedio redondeado de las notas en esa ronda
                    return round($evaluaciones->avg('nota'));
                })
                 ->color(function ($state) {
                        if ($state === '-') {
                            return null; 
                        }

                        return $state < 12 ? 'danger' : 'primary'; 
                    })
                ->toggleable(isToggledHiddenByDefault: true), 

                Tables\Columns\TextColumn::make('ronda')
                    ->label('Ronda')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state == 1 ? 'Primera Evaluación' : ($state == 2 ? 'Segunda Evaluación' : ''))
                    ->toggleable(isToggledHiddenByDefault: true), 

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->searchable()
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'evaluacion', 'Evaluado' => 'primary',   // azul
                        'pendiente', 'Pendiente' => 'warning',                // amarillo
                        default => 'gray',                       // gris por defecto
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'evaluacion', 'Evaluado' => 'Evaluado',
                        'pendiente', 'Pendiente' => 'Pendiente',
                        default => ucfirst($state),
                    }),
                
                 Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de Evaluación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('activo')
                    ->label('')
                    ->trueColor('primary')  
                    ->falseColor('gray')
                    ->searchable(),
                
                
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
               Tables\Actions\Action::make('evaluar')
                ->label('Evaluar')
                ->icon('heroicon-o-document-plus')
                ->modalHeading(fn ($record) => 'EVALUANDO INFORME DE PRÁCTICA DE ' . strtoupper($record->informeDePractica->solicitudInforme->estudiante->nombre))
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->estado !== 'Evaluado')
                ->modalIcon('heroicon-o-document-magnifying-glass')
                ->modalWidth('md')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nota')
                                    ->label('Nota')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(20)
                                    ->nullable()
                                    ->required(),

                                Forms\Components\TextInput::make('estado_visual')
                                    ->label('Estado')
                                    ->default('Evaluado')
                                    ->disabled(), 
                                Forms\Components\Hidden::make('estado')
                                    ->default('Evaluado'), 
                            ]),
                    ])
                    ->action(function (EvaluacionDeInforme $record, array $data) {
                        $record->update([
                            'nota'   => $data['nota'],       
                            'estado' => $data['estado'],      
                            'activo' => true,               
                        ]);

                        Notification::make()
                            ->title('Evaluación registrada con éxito')
                            ->info()
                            ->send();
                    })
                ->modalSubmitActionLabel('Guardar')
                ->modalCancelActionLabel('Cancelar'),

                    Tables\Actions\ActionGroup::make([
                        
                        Tables\Actions\Action::make('ver_acta')
                            ->label('Ver Acta de Sustentación')
                            ->icon('heroicon-o-eye')
                            ->color('primary')
                            ->url(fn ($record) => route('pdf.acta', ['id' => $record->id]))
                            ->openUrlInNewTab(),  
                            
                        Tables\Actions\Action::make('observar')
                            ->label('Observar')
                            ->color('primary')
                            ->icon('heroicon-o-chat-bubble-left-ellipsis')
                            ->modalHeading(fn ($record) => 'OBSERVANDO INFORME DE PRÁCTICA DE ' . strtoupper($record->informeDePractica->solicitudInforme->estudiante->nombre))
                            ->requiresConfirmation()
                            ->modalIcon('heroicon-o-pencil')
                            ->modalWidth('md')  // Título del modal
                            ->form([
                                Forms\Components\Textarea::make('observacion')
                                    ->label('Observaciones') 
                                    ->placeholder('Escribe tus observaciones aquí...')
                                    ->columnSpanFull()  
                                    ->maxLength(600),  
                            ])
                            ->action(function ($record, array $data) {
                                $record->update(['observacion' => $data['observacion']]);
                                
                               
                                \Filament\Notifications\Notification::make()
                                    ->title('Observacines registradas con éxito')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    

                        ->icon('heroicon-o-document-duplicate') 
                        ->label('')
                        ->color('primary')
                        ->extraAttributes([
                            'title' => 'Acta',
                            
                        ])             
                        ->visible(function ($record) {
                        $informeId = $record->informe_de_practica_id;
                        $ronda = $record->ronda;
                        $evaluacionesRonda = \App\Models\EvaluacionDeInforme::where('informe_de_practica_id', $informeId)
                            ->where('ronda', $ronda)
                            ->with('jurados')
                            ->get();
                        $haySecretario = $evaluacionesRonda->contains(function ($evaluacion) {
                            return $evaluacion->jurados && $evaluacion->jurados->cargo === 'Secretario';
                        });
                        if ($haySecretario) {
                            return $record->jurados && $record->jurados->cargo === 'Secretario';
                        }

                        return $record->jurados && $record->jurados->cargo === 'Accesitario';
                    }),  
                    
                Tables\Actions\EditAction::make()
                    ->visible(function ($record) {
                        $estado = strtolower($record->estado);
                        
                        // Evitar errores si no hay informe
                        if (!$record->informeDePractica) {
                            return true;
                        }

                        // Calcular promedio de la ronda
                        $promedio = $record->informeDePractica
                            ->evaluaciones()
                            ->where('ronda', $record->ronda)
                            ->where('estado', 'Evaluado')
                            ->avg('nota');

                        // Si no hay 3 evaluaciones, lo dejamos visible igual
                        $evaluaciones = $record->informeDePractica
                            ->evaluaciones()
                            ->where('ronda', $record->ronda)
                            ->where('estado', 'Evaluado')
                            ->count();

                        if ($evaluaciones < 3) {
                            return true;
                        }

                        $promedio = round($promedio);

                        // Ocultar si está evaluado y el promedio es mayor o igual a 12
                        return !($estado === 'evaluado' && $promedio >= 12);
                    }),
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
