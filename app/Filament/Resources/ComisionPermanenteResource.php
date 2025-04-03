<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComisionPermanenteResource\Pages;
use App\Filament\Resources\ComisionPermanenteResource\RelationManagers;
use App\Models\ComisionPermanente;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComisionPermanenteResource extends Resource
{
    protected static ?string $model = ComisionPermanente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('nombre')
                ->required()
                ->maxLength(150),
                
            Forms\Components\DatePicker::make('fecha_inicio')
                ->required(),
                
                Forms\Components\DatePicker::make('fecha_fin')
                    ->required()
                    ->minDate(fn ($get) => $get('fecha_inicio'))
                    ->rules([
                        function ($get, $record) {
                            return function (string $attribute, $value, Closure $fail) use ($record) {
                                if ($record?->estado && Carbon::parse($value)->lt(now())) {
                                    $fail('No puede establecer una fecha pasada para una comisión activa');
                                }
                            };
                        }
                    ]),
                                
                    Forms\Components\Toggle::make('estado')
                    ->default(true)
                    ->onColor('primary')
                    ->offColor('danger')
                    ->disabled(fn ($get) => 
                        $get('fecha_fin') && Carbon::now()->greaterThan($get('fecha_fin'))
                    )
                    ->rules([
                        fn (Get $get, $record) => function (string $attribute, $value, Closure $fail) use ($record) {
                            if ($value) {
                                $query = ComisionPermanente::where('estado', true)
                                    ->whereDate('fecha_fin', '>', Carbon::now());
                                
                                // Excluye el registro actual si está editando
                                if ($record && $record->exists) {
                                    $query->where('id', '!=', $record->id);
                                }
                
                                if ($query->exists()) {
                                    $fail('YA EXISTE UNA COMISION ACTIVA. DESACTIVA PRIMERO');
                                }
                            }
                        }
                    ])
                    ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                        // Auto-desactiva si la fecha fin es pasada
                        if ($state && $get('fecha_fin') && Carbon::parse($get('fecha_fin'))->lt(now())) {
                            $set('estado', false);
                        }
                    })
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->date()
                    ->sortable(),
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
            'index' => Pages\ManageComisionPermanentes::route('/'),
        ];
    }
}
