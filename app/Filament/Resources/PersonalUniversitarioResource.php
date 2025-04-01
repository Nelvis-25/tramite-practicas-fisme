<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonalUniversitarioResource\Pages;
use App\Models\PersonalUniversitario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PersonalUniversitarioResource extends Resource
{
    protected static ?string $model = PersonalUniversitario::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(60),
                Forms\Components\TextInput::make('dni')
                        ->required()
                        ->maxLength(8),
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->maxLength(17),
                Forms\Components\TextInput::make('telefono')
                    ->tel()
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('cargo')
                    ->options([
                        'Director de escuela' => 'Director de escuela',
                        'Decano' => 'Decano',
                        'Secretario de facultad' => 'Secretario de facultad',
                    ])
                    ->required()
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, $fail) use ($get) {
                                if ($get('estado')) {
                                    $existing = PersonalUniversitario::where('cargo', $value)
                                        ->where('estado', true)
                                        ->first();
                                    
                                    if ($existing) {
                                        $fail("Ya existe un {$value} activo ({$existing->nombre}). ¡Desactívalo primero!");
                                    }
                                }
                            };
                        }
                    ]),
                Forms\Components\Toggle::make('estado')
                    ->default(true)
                    ->required()
                    ->reactive(),
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
                Tables\Columns\TextColumn::make('cargo')
                    ->searchable(),
                Tables\Columns\IconColumn::make('estado')
                    ->boolean()
                    ->label('Activo'),
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
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        true => 'Activo',
                        false => 'Inactivo',
                    ])
                    ->label('Estado'),
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
            'index' => Pages\ListPersonalUniversitarios::route('/'),
            'create' => Pages\CreatePersonalUniversitario::route('/create'),
            'edit' => Pages\EditPersonalUniversitario::route('/{record}/edit'),
        ];
    }
}