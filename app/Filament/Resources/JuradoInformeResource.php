<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JuradoInformeResource\Pages;
use App\Filament\Resources\JuradoInformeResource\RelationManagers;
use App\Models\JuradoInforme;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JuradoInformeResource extends Resource
{
    protected static ?string $model = JuradoInforme::class;
    protected static ?string $navigationGroup = 'Evaluación de informes';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(100),
                //Forms\Components\DatePicker::make('fechainicio'),
                //Forms\Components\DatePicker::make('fechafin'),
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
               // Tables\Columns\TextColumn::make('fechainicio')
                   // ->date()
                   // ->sortable(),
                //Tables\Columns\TextColumn::make('fechafin')
                 //   ->date()
                   // ->sortable(),
                Tables\Columns\IconColumn::make('estado')
                    ->default(true)
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
            'index' => Pages\ManageJuradoInformes::route('/'),
        ];
    }
}
