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
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class DocenteResource extends Resource
{
    protected static ?string $model = Docente::class;
    protected static ?string $navigationGroup = 'Registro académico';
    protected static ?string $navigationLabel = 'Registro de Docentes';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre del docente')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('dni')
                    ->label('DNI')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(8),
                Forms\Components\TextInput::make('codigo')
                    ->label('Código del docente')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(15),
                Forms\Components\TextInput::make('telefono')
                    ->label('número de WhatsApp o Teléfono')
                    ->tel()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(9),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('especialidad')
                    ->label('Especialidad')
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
                    ->label('Tipo de contrato')
                    ->options([
                        'Contratado' => 'Contratado',
                        'Nombrado' => 'Nombrado',

                    ]),
                Forms\Components\Select::make('cargo_id')
                    ->relationship('cargo', 'nombre')
                    ->label('Cargo')
                    ->required(),

                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->searchable()
                    ->preload(),
                 
                 Forms\Components\Toggle::make('estado')
                    ->label('Estado')
                    ->required()
                    ->default(true),
                
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

                Tables\Columns\TextColumn::make('cargo.nombre')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean()
                    ->colors([
                        'primary' => true,  
                        'gray' => false,  
                    ]),
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
                 Tables\Actions\Action::make('toggleEstado')
                    ->label(fn ($record) => $record->estado ? 'Inhabilitar' : 'Habilitar')
                    ->icon(fn ($record) => $record->estado ? 'heroicon-o-eye' : 'heroicon-o-eye')

                    ->color(fn ($record) => $record->estado ? 'danger' : 'primary')
                    ->action(function ($record) {
                        $record->estado = !$record->estado;
                        $record->save();
                    })
                    ->requiresConfirmation()
                  
               , 
                Tables\Actions\DeleteAction::make(),
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make('export'),
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
