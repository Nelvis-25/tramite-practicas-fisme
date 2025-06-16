<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JuradoDeInformeResource\Pages;
use App\Filament\Resources\JuradoDeInformeResource\RelationManagers;
use App\Models\JuradoDeInforme;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JuradoDeInformeResource extends Resource
{
    protected static ?string $model = JuradoDeInforme::class;
    protected static ?string $navigationGroup = 'Informe de Prácticas';
    protected static ?string $navigationLabel = 'Jurados de Informe';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 4;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               Forms\Components\Select::make('informe_de_practica_id')
                    ->label('Nombre del informe de practica')
                    ->options(
                        \App\Models\InformeDePractica::with('solicitudInforme.practica.solicitude')
                            ->get()
                            ->mapWithKeys(function ($informe) {
                                return [
                                    $informe->id => optional($informe->solicitudInforme->practica->solicitude)->nombre
                                ];
                            })
                    )
                    ->searchable()
                    ->required()
                    ->preload(),
                Forms\Components\Select::make('docente_id')
                    ->relationship('docente', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required()
                    ,
                Forms\Components\Select::make('cargo')
                    ->label('Cargo del jurado')
                    ->options([
                        'Presidente' => 'Presidente',
                        'Secretario' => 'Secretario',
                        'Vocal' => 'Vocal',
                        'Accesitario' => 'Accesitario',
                    ])
                    ->required()
                    ->native(false), 
                 ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('informeDePractica.solicitudInforme.practica.solicitude.nombre')
                    ->label('Informe de practica')
                    ->extraAttributes([
                        'style' => 'width: 400px; word-wrap: break-word; white-space: normal;text-align: justify;',
                    ])
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('informeDePractica.solicitudInforme.practica.solicitude.estudiante.nombre')
                    ->label('Nombre del practicante')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('docente.nombre')
                   ->label('Nombre del jurado')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cargo')
                    ->label('Cargo ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageJuradoDeInformes::route('/'),
        ];
    }
}
