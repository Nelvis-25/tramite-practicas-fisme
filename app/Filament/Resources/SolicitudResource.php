<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudResource\Pages;
use App\Filament\Resources\SolicitudResource\RelationManagers;
use App\Models\Solicitud;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SolicitudResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(700),
                Forms\Components\Select::make('estudiante_id')
                    ->relationship('estudiante', 'id')
                    ->required(),
                Forms\Components\Select::make('linea_investigacion_id')
                    ->relationship('lineaInvestigacion', 'id')
                    ->required(),
                Forms\Components\Select::make('asesor_id')
                    ->relationship('asesor', 'id')
                    ->required(),
                Forms\Components\TextInput::make('solicitud')
                    ->maxLength(255),
                    Forms\Components\FileUpload::make('constancia')
                    ->required()
                    ->directory('constancias')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20240),
                Forms\Components\TextInput::make('informe')
                    ->maxLength(255),
                Forms\Components\TextInput::make('carta_presentacion')
                    ->maxLength(255),
                Forms\Components\TextInput::make('comprobante_pago')
                    ->maxLength(255),
                Forms\Components\TextInput::make('estado')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estudiante.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lineaInvestigacion.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('asesor.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('solicitud')
                    ->searchable(),
                Tables\Columns\TextColumn::make('constancia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('informe')
                    ->searchable(),
                Tables\Columns\TextColumn::make('carta_presentacion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comprobante_pago')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estado'),
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
            'index' => Pages\ListSolicituds::route('/'),
            'create' => Pages\CreateSolicitud::route('/create'),
            'edit' => Pages\EditSolicitud::route('/{record}/edit'),
        ];
    }
}
