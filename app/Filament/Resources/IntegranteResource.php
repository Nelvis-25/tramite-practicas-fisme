<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntegranteResource\Pages;
use App\Filament\Resources\IntegranteResource\RelationManagers;
use App\Models\Integrante;
use Closure;
use Filament\Forms;
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
                Forms\Components\Select::make('docente_id')
                    ->relationship('docente', 'nombre', function ($query) {
                        $query->where('estado', true);
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                   ,
                Forms\Components\Select::make('cargo')
                ->options([
                    'Secretario' => 'Secretario',
                    'Presidente' => 'Presidente',
                    'Vocal' => 'Vocal',
                    'Accesitario' => 'Accesitario',
                ])
                ->rules([
                    function (Forms\Get $get) {
                        return function (string $attribute, $value, Closure $fail) use ($get) {
                            $juradoInformeId = $get('jurado_informe_id'); 
                            $cargo = $value;
                            $currentId = $get('id'); 
            
                            if (in_array($cargo, ['Presidente', 'Secretario', 'Vocal', 'Accesitario'])) {
                                $exists = \App\Models\Integrante::where('jurado_informe_id', $juradoInformeId)
                                    ->where('cargo', $cargo)
                                    ->when($currentId, fn($query) => $query->where('id', '!=', $currentId))
                                    ->exists();
            
                                if ($exists) {
                                    $fail("Ya existe un {$cargo} en este grupo .");
                                }
                            }
                        };
                    },
                ])
                ->required()
                ->label('Cargo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('juradoInforme.nombre')
                   ->label('Grupo al que pertenece')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('docente.nombre')
                    ->label('Docente evaluador')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cargo'),
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
