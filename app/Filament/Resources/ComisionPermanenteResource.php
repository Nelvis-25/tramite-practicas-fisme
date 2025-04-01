<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComisionPermanenteResource\Pages;
use App\Models\ComisionPermanente;
use App\Models\PersonalUniversitario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ComisionPermanenteResource extends Resource
{
    protected static ?string $model = ComisionPermanente::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $modelLabel = 'Comisión Permanente';
    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(100),
                    
                Forms\Components\DatePicker::make('fecha_inicio')
                    ->required()
                    ->native(false),
                    
                Forms\Components\DatePicker::make('fecha_fin')
                    ->required()
                    ->native(false)
                    ->afterOrEqual('fecha_inicio'),
                    
                Forms\Components\Select::make('director_id')
                    ->label('Director Asignado')
                    ->options(
                        PersonalUniversitario::where('cargo', 'Director de escuela')
                            ->pluck('nombre', 'id')
                    )
                    ->required()
                    ->searchable(),
                    
                Forms\Components\Toggle::make('estado')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('director.nombre')
                    ->label('Director')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('estado')
                    ->boolean()
                    ->label('Activa'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('director_id')
                    ->label('Director')
                    ->relationship('director', 'nombre'),
                    
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        true => 'Activas',
                        false => 'Inactivas',
                    ]),
            ])
            ->actions([
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
            'index' => Pages\ListComisionPermanentes::route('/'),
            'create' => Pages\CreateComisionPermanente::route('/create'),
            'edit' => Pages\EditComisionPermanente::route('/{record}/edit'),
        ];
    }
}