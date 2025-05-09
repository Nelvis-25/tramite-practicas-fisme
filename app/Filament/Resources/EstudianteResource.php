<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EstudianteResource\Pages;
use App\Filament\Resources\EstudianteResource\RelationManagers;
use App\Models\Estudiante;
use App\Models\TipoEstudiante;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class EstudianteResource extends Resource
{
    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    /** @var User $user */
    $user = auth()->user();

    if ($user && $user->hasRole('Estudiante')) {
        return $query->where('user_id', $user->id);
    }

    return $query;
}
public static function canCreate(): bool
{
    $user = auth()->user();
     /** @var User $user */
    if ($user && $user->hasRole('Estudiante')) {
        
        return !Estudiante::where('user_id', $user->id)->exists();
    }

    return true; 
}

    protected static ?string $model = Estudiante::class;
    protected static ?string $navigationGroup = 'Estudiante';
    protected static ?string $navigationLabel = 'Registro de estudiantes';
    protected static ?string $navigationIcon = 'heroicon-o-users';

     
    public static function form(Form $form): Form
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */
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
                Forms\Components\Select::make('tipo_estudiante')
                ->label('Tipo de Estudiante')
                ->options([
                    'Estudiante' => 'Estudiante',
                    'Egresado' => 'Egresado', 
                ])
                    ->required()
                    ->reactive() 
                    ,
                    Forms\Components\TextInput::make('telefono')
                    ->tel()
                    ->required()
                    ->maxLength(9),
                Forms\Components\Select::make('ciclo')
                   
                    ->options([
                        'VII' => 'Ciclo VII',
                        'VIII' => 'Ciclo VIII', 
                        'IX' => 'Ciclo IX',
                        'X' => 'Ciclo X',
                    ])
                    ->disabled(fn (callable $get) => $get('tipo_estudiante') === 'Egresado')
                  ,
                                    
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
               
                    Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(125)
                    ->disabled()
                    ->dehydrated()
                    ->reactive()
                    ->default(function () use ($user) {
                        return $user?->hasRole('Estudiante') ? $user->email : null;
                    }),
                
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
                    ->default(fn () => $user?->hasRole('Estudiante') ? $user->id : null)
                    ->disabled(fn () => $user?->hasRole('Estudiante'))
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $userEmail = \App\Models\User::find($state)?->email;
                        $set('email', $userEmail);
                    }),
                
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
                Tables\Columns\TextColumn::make('tipo_estudiante')
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


