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
use Filament\Tables\View\TablesRenderHook;
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
           //->extraAttributes(['class' => 'p-6 bg-white shadow rounded border'])
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre del estudiante')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('dni')
                    ->label('DNI')
                    ->minLength(8)
                    ->unique(ignoreRecord: true)
                    ->maxLength(8)
                    ->numeric(),
                Forms\Components\TextInput::make('codigo')
                    ->label('Codigo del estudiante')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(10),
                Forms\Components\Select::make('tipo_estudiante')
                ->label('Tipo de estudiante')
                ->options([
                    'Estudiante' => 'Estudiante',
                    'Egresado' => 'Egresado', 
                ])
                    ->required()
                    ->reactive() 
                    ,
                    Forms\Components\TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel()
                    ->minLength(9)
                    ->maxLength(9)
                    ->numeric(),
                Forms\Components\Select::make('ciclo')
                   ->label('Ciclo de estudios')
                    ->options([
                        'VII' => 'Ciclo VII',
                        'VIII' => 'Ciclo VIII', 
                        'IX' => 'Ciclo IX',
                        'X' => 'Ciclo X',
                    ])
                    ->disabled(fn (callable $get) => $get('tipo_estudiante') === 'Egresado')
                  ,
                                    
                Forms\Components\TextInput::make('facultad')
                    ->label('Facultad')
                    ->required()
                    ->maxLength(250),
                Forms\Components\Select::make('carrera')
                    ->label('Carrera profesional')
                    ->required()
                    ->options([
                        'Ingeniería de Sistemas' => 'Ingeniería de Sistemas',
                        'Ingeniería de Mecánica Eléctrica' => 'Ingeniería de Mecánica Eléctrica', 
                        'Ingeniería de Biosistemas' => 'Ingeniería de Biosistemas'
                        
                    ]),
               
                    Forms\Components\TextInput::make('email')
                    ->label('Gmail')
                    ->email()
                    ->unique(ignoreRecord: true)
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
                    ->unique(ignoreRecord: true)
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
                    ->label('Nombre')  
                    ->searchable(),
                Tables\Columns\TextColumn::make('dni')
                    ->label('DNI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_estudiante')
                    ->label('Tipo de estudiante')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ciclo')
                    ->label('Ciclo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('facultad')
                    ->label('Facultad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('carrera')
                    ->label('Carrera profesional')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->label('N° teléfono')
                    ->searchable()
                    ,
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\IconColumn::make('estado')
                    ->label('Estado')
                     ->boolean()
                    ->colors([
                        'primary' => true,  
                        'gray' => false,    
                    ]),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de actualización')
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
                Tables\Actions\Action::make('toggleEstado')
                    ->label(fn ($record) => $record->estado ? 'Inhabilitar' : 'Habilitar')
                    ->icon(fn ($record) => $record->estado ? 'heroicon-o-eye' : 'heroicon-o-eye')

                    ->color(fn ($record) => $record->estado ? 'danger' : 'primary')
                    ->action(function ($record) {
                        $record->estado = !$record->estado;
                        $record->save();
                    })
                    ->requiresConfirmation()
    
                   ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = auth()->user();
                        return auth()->check() && !$user->hasRole('Estudiante');
                    })
                                        
                    ,
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
            'index' => Pages\ListEstudiantes::route('/'),
            'create' => Pages\CreateEstudiante::route('/create'),
            'edit' => Pages\EditEstudiante::route('/{record}/edit'),
        ];
   
   
    }

}


