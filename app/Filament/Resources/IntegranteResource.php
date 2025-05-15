<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntegranteResource\Pages;
use App\Filament\Resources\IntegranteResource\RelationManagers;
use App\Models\Integrante;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IntegranteResource extends Resource
{
    protected static ?string $model = Integrante::class;
    protected static ?string $navigationGroup = 'Evaluación de informes';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('jurado_informe_id')
                    ->label('Docente evaluador')
                    ->relationship('juradoInforme', 'nombre', function ($query) {
                        $query->where('estado', true);
                    })
                    ->searchable()
                    ->required()
                    ->preload()
                    ,

                Repeater::make('integrantes')
                    ->label('Seleccione los integrantes y sus cargos')
                    ->schema([

                        Forms\Components\Select::make('docente_id')
                        ->label('Docente')
                        ->relationship('docente', 'nombre', function ($query) {
                            $query->where('estado', true);
                        })
                        ->required()
                        ->searchable()
                        ->preload(),
                        Forms\Components\Select::make('cargo')
                        ->required()
                        ->options([
                            'Secretario' => 'Secretario',
                            'Presidente' => 'Presidente',
                            'Vocal' => 'Vocal',
                            'Accesitario' => 'Accesitario',
                        ]),
                    ])
                
                    ->minItems(1)
                    ->defaultItems(1)
                    ->columns(2)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('juradoInforme.nombre')
                   ->label('Grupo al que pertenece')
                   ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('docente.nombre')
                    ->label('Docente evaluador')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cargo')
                ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
            'index' => Pages\ManageIntegrantes::route('/'),
        ];
    }
}
