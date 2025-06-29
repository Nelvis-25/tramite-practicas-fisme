<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PracticaResource\Pages;
use App\Filament\Resources\PracticaResource\RelationManagers;
use App\Models\PlanPractica;
use App\Models\Practica;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PracticaResource extends Resource
{
    protected static ?string $model = Practica::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Prácticas';
    protected static ?string $label = 'práctica';
    protected static ?string $pluralLabel = 'Prácticas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('estudiante_id')
                    ->label('Nombre del estudiante') 
                    ->relationship('estudiante', 'nombre')  
                    ->required()
                    ,
                Forms\Components\Select::make('docente_id')
                    ->label('Nombre del asesor')
                    ->relationship('asesor', 'nombre')
                    ->required()
                    ,
                Forms\Components\Select::make('solicitude_id')
                    ->label('Nombre de plan de practica')
                    ->relationship('solicitude', 'nombre')
                    ->required()
                    ,
                    Forms\Components\Select::make('plan_practica_id')
                    ->label('estado del pln de practica')
                    ->relationship('planPractica', 'estado')
                    ->required()
                    ,
               
                    Forms\Components\TextInput::make('estado')
                    ->label('Estado')
                    ->required()
                    ->maxLength(50),

                    Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('estudiante.tipo_estudiante')
                    ->label('Est/Egre')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('estudiante.nombre')
                    ->label('Nombre del estudiante') 
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('asesor.nombre')
                    ->label('Nombre del asesor')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('solicitude.nombre')
                    ->label('Titulo de práctica')
                    ->numeric()
                    ->sortable()
                    ->extraAttributes([
                        'style' => 'width: 400px; word-wrap: break-word; white-space: normal;text-align: justify;',
                    ])
                    ->searchable(),
                   // Tables\Columns\TextColumn::make('solicitude.informe')
                    //->label('Plan de práctica')
                    
                    //->formatStateUsing(fn ($state) => $state ? basename($state) : 'Sin archivo')  // Usar la misma lógica para mostrar el nombre del archivo
                   // ->url(function ($record) {
                     //   if (!$record->solicitude || !$record->solicitude->informe) return null;
                     //   return asset('storage/'.str_replace('storage/', '', $record->solicitude->informe));  // Asegurarte de acceder correctamente a "informe" en el objeto "solicitude"
                   // }),
                Tables\Columns\TextColumn::make('planPractica.estado')
                    ->label('plan practica')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),   
                Tables\Columns\TextColumn::make('planPractica.comisionPermanente.nombre')
                    ->label('Comision permanente')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
               Tables\Columns\TextColumn::make('solicitude.fecha_inicio')
                    ->label(  'Inicio de Práctica'  )
                    ->numeric()
                    ->alignCenter()
                    ->searchable()
                    ->sortable(), 
                Tables\Columns\TextColumn::make('solicitude.fecha_fin')
                    ->label(  'Fin de Práctica'  )
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),    
                Tables\Columns\TextColumn::make('estado')
                    ->searchable()
                    ->alignCenter()
                     ->badge()
                    ->color(fn ($state) => match ($state) {
                            'Pendiente' => 'warning',
                            'Finalizado' => 'primary',
                            default => 'warning',
                        }),
                Tables\Columns\IconColumn::make('activo')
                    ->label('')
                    ->boolean()
                    ->alignCenter()
                    ->trueColor('success')   
                    ->falseColor('primary')   
                    ->tooltip(fn ($record) => $record->activo ? 'En proceso' : 'Finalizado con éxito'),       
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
            'index' => Pages\ListPracticas::route('/'),
            'create' => Pages\CreatePractica::route('/create'),
            'edit' => Pages\EditPractica::route('/{record}/edit'),
        ];
    }
}
