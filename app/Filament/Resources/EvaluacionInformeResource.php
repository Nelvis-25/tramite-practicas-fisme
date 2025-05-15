<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluacionInformeResource\Pages;
use App\Filament\Resources\EvaluacionInformeResource\RelationManagers;
use App\Models\EvaluacionInforme;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EvaluacionInformeResource extends Resource
{
    protected static ?string $model = EvaluacionInforme::class;
    protected static ?string $navigationGroup = 'Evaluación de informes';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
    
        $user = auth()->user();
        /** @var User $user */
        if ($user && $user->hasRole('Jurados')) {
            $docent = \App\Models\Docente::where('user_id', $user->id)->first();
        
            if ($docent) {
                return $query->whereHas('integrante', function ($q) use ($docent) {
                    $q->where('docente_id', $docent->id);
                });
            }
    
            return $query->whereRaw('0 = 1'); // si no encuentra docente, que no muestre nada
        }
    
        return $query;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('informe_practica_id')
                ->label('Nombre del estudiante')
                ->options(function () {
                    return \App\Models\InformePractica::with('solicitudInforme.estudiante')
                        ->get()
                        ->mapWithKeys(function ($informe) {
                            $nombre = optional($informe->solicitudInforme->estudiante)->nombre;
                            return [$informe->id => $nombre ?? 'Sin nombre'];
                        });
                })
                ->reactive() 
                ->searchable()
                ->required(),
                Forms\Components\Select::make('integrante_id')
                ->label('Nombre del evaluador')
                ->options(function (callable $get) {
                    $informeId = $get('informe_practica_id');
            
                    if (!$informeId) {
                        return [];
                    }
            
                    $informe = \App\Models\InformePractica::with('juradoInforme.integrante.docente')
                        ->find($informeId);
            
                    if (!$informe || !$informe->juradoInforme) {
                        return [];
                    }
            
                    return $informe->juradoInforme->integrante->mapWithKeys(function ($integrante) {
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
                    'Desaprobado' => 'Desaprobado',
                    'Observado' => 'Observado',
                ])
                ->columns(3) // Esto pone los botones en horizontal
                ->reactive()
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    $set('activo', $state !== 'Observado');
                })
                ->required(),
                //Forms\Components\DatePicker::make('fecha_evaluacion'),

            
            Forms\Components\Textarea::make('observacion')
                ->label('Observación')
                ->maxLength(600)
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
                Tables\Columns\TextColumn::make('informePractica.solicitudInforme.estudiante.nombre')
                ->label('Nombre del practicante')  
                ->numeric()
                ->searchable()
                ->sortable(),
                Tables\Columns\TextColumn::make('informePractica.solicitudInforme.informe')
                ->label('Informe de practica') 
                ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Sin archivo')
                ->url(fn ($record) => $record->informePractica->solicitudInforme->informe ? asset('storage/'.str_replace('storage/', '', $record->informePractica->solicitudInforme->informe)) : null)
                ->openUrlInNewTab()
                ->icon('heroicon-o-document-text')
                ->searchable(), 

                Tables\Columns\TextColumn::make('integrante.docente.nombre')
                ->label('Integrante de Comisión')
                ->formatStateUsing(fn ($state, $record) => $state . ' - ' . $record->integrante->cargo)
                ->searchable(),
                //Tables\Columns\TextColumn::make('integrante.docente.user.name')
                //->label('Integrante de Comisión')
                
                //->sortable(),
                //Tables\Columns\TextColumn::make('fecha_evaluacion')
                   // ->date()
                    //->sortable(),
                Tables\Columns\TextColumn::make('observacion')
                    ->searchable()
                    ->extraAttributes([
                        'style' => 'width: 160px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; word-wrap: break-word;',
                    ]),
                Tables\Columns\TextColumn::make('estado')
                    ->searchable()
                     ->color(fn ($state) => $state === 'Desaprobado' ? 'danger' : null),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Estado')
                    ->boolean()
                    ->color(fn (bool $state) => $state ?  'primary' : 'gray')
                    ->tooltip(fn ($record) => $record->activo ? 'Evaluado' : 'En evaluación'),
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
                ->color('primary')
                ->modalHeading('')
                ->modalSubmitActionLabel('Confirmar')
                ->modalWidth('2xl')
                ->visible(fn ($record) => $record->estado !== 'Aprobado') 
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
                            })
                            ,
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
            'index' => Pages\ListEvaluacionInformes::route('/'),
            'create' => Pages\CreateEvaluacionInforme::route('/create'),
            'edit' => Pages\EditEvaluacionInforme::route('/{record}/edit'),
        ];
    }
}
