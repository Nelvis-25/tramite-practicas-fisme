<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntegranteComisionResource\Pages;
use App\Filament\Resources\IntegranteComisionResource\RelationManagers;
use App\Models\IntegranteComision;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IntegranteComisionResource extends Resource
{
    protected static ?string $model = IntegranteComision::class;
    protected static ?string $navigationGroup = 'Comisiones permanentes';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('docente_id')
                    ->relationship('docente', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('comision_permanente_id')
                    ->relationship('comisionPermanente', 'nombre')
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
                    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('comisionPermanente.nombre')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('docente.nombre')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('cargo'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
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
            'index' => Pages\ManageIntegranteComisions::route('/'),
        ];
    }
}
