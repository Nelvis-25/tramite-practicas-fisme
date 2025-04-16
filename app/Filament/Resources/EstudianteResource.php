<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EstudianteResource\Pages;
use App\Filament\Resources\EstudianteResource\RelationManagers;
use App\Models\Estudiante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EstudianteResource extends Resource
{
    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    // Verificar que haya un usuario autenticado antes de llamar a hasRole
    if (auth()->check() && auth()->user()->hasRole('Estudiante')) {
        return $query->where('user_id', auth()->id());
    }

    return $query;
}
    protected static ?string $model = Estudiante::class;
    protected static ?string $navigationGroup = 'Estudiante';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

     
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
                    ->maxLength(10),
                Forms\Components\Select::make('tipo_estudiante_id')
                    ->relationship('tipoEstudiante', 'nombre')
                    ->required(),
                Forms\Components\Select::make('ciclo')
                   
                    ->options([
                        'VII' => 'Ciclo VII',
                        'VIII' => 'Ciclo VIII', 
                        'IX' => 'Ciclo IX',
                        'X' => 'Ciclo X',
                    ]),
                    
                Forms\Components\TextInput::make('facultad')
                    ->required()
                    ->maxLength(250),
                Forms\Components\Select::make('carrera')
                    ->required()
                    ->options([
                        'Ingeniería de Sistemas' => 'Ingeniería de Sistemas',
                        'Ingeniería de Mecánica Eléctrica' => 'Ingeniería de Mecánica Eléctrica', 
                        'Ingeniería de Biosistemas' => 'Ingeniería de Biosistemas'
                        
                    ]),
                Forms\Components\TextInput::make('telefono')
                    ->tel()
                    ->required()
                    ->maxLength(9),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(125),
                Forms\Components\TextInput::make('direccion')
                    ->maxLength(250),
                Forms\Components\Toggle::make('estado')
                    ->required()
                    ->default(true),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ,
                    
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
                Tables\Columns\TextColumn::make('tipoEstudiante.nombre')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ciclo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('facultad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('carrera')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\IconColumn::make('estado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('user.name')
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
            'index' => Pages\ListEstudiantes::route('/'),
            'create' => Pages\CreateEstudiante::route('/create'),
            'edit' => Pages\EditEstudiante::route('/{record}/edit'),
        ];
   
   
    }

}


