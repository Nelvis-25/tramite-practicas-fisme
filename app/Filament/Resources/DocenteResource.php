<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocenteResource\Pages;
use App\Filament\Resources\DocenteResource\RelationManagers;
use App\Models\Docente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocenteResource extends Resource
{
    protected static ?string $model = Docente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('dni')
                    ->required()
                    ->maxLength(8),
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->maxLength(15),
                Forms\Components\TextInput::make('telefono')
                    ->tel()
                    ->required()
                    ->maxLength(9),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('especialidad')
                    ->maxLength(100),
                Forms\Components\Select::make('grado_academico')
                    ->options([
                        'Bach.' => 'Bach.',
                        'Lic.' => 'Lic.',
                        'Mg.' => 'Mg.',
                        'MSc.' => 'MSc.',
                        'Dr.' => 'Dr.',
                        'Postdoc.' => 'Postdoc.',
                    ]),
                  
                Forms\Components\Select::make('tipo_contrato')
                    
                    ->options([
                        'Contratado' => 'Contratado',
                        'Nombrado' => 'Nombrado',

                    ]),
                Forms\Components\Toggle::make('estado')
                    ->required()
                    ->default(true),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Select::make('cargo_id')
                    ->relationship('cargo', 'nombre')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dni')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('especialidad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('grado_academico')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_contrato')
                    ->searchable(),
                Tables\Columns\IconColumn::make('estado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cargo.nombre')
                    ->numeric()
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
            ->filters([
                //
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
            'index' => Pages\ListDocentes::route('/'),
            'create' => Pages\CreateDocente::route('/create'),
            'edit' => Pages\EditDocente::route('/{record}/edit'),
        ];
    }
}
