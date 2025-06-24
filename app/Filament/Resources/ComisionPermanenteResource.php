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
    protected static ?string $navigationGroup = 'Plan de Prácticas';
    protected static ?string $navigationLabel = 'Comisión permanente';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $label = 'comisión';
    protected static ?string $pluralLabel = 'Comisión permanente';
    protected static ?int $navigationSort = 4;
    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('nombre')
                ->label('Nombre de la comisión permanente')
                ->required()
                ->maxLength(150),
                
            Forms\Components\DatePicker::make('fecha_inicio')
                ->label('Fecha de inicio')
                ->required(),
                
                Forms\Components\DatePicker::make('fecha_fin')
                ->label('Fecha de fin')
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
                ->trueColor('primary')
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
                Tables\Actions\DeleteAction::make()
                ->visible(function () {
                    $user = auth()->user();
                    /** @var User $user */
                    return $user->hasAnyRole(['Admin']);
                })
                ,
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
