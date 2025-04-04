<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ValidacionResource\Pages;
use Illuminate\Support\Facades\Log; 
use App\Models\Validacion;
use Filament\Forms;
use Filament\Forms\Form;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ValidacionResource extends Resource
{
    protected static ?string $model = Validacion::class;
    protected static ?string $navigationGroup = 'Plan de Prácticas';
    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['requisito', 'solicitud'])
            ->where('solicitud_id', request('solicitud_id'));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('requisito_id')
                ->disabled()
                ->label('ID Requisito'),
            Forms\Components\Toggle::make('entregado')
                ->label('¿Requisito cumplido?')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {        return $table
        ->columns([
            TextColumn::make('requisito.nombre')
                ->label('Requisito'),
                
            CheckboxColumn::make('entregado')
                ->label('Validado')
                ->disabled(false)
                ->extraAttributes(['style' => 'cursor: pointer; text-align: center'])
                ->afterStateUpdated(function ($record, $state) {
                    // Guardado directo sin logs
                    $record->update(['entregado' => $state]);
                    
                    // Actualización del estado padre (opcional)
                    if ($record->solicitud) {
                        $todosValidados = $record->solicitud->validaciones()
                            ->where('entregado', false)
                            ->doesntExist();
                            
                        $record->solicitud->update([
                            'estado' => $todosValidados ? 'Validado' : 'Pendiente'
                        ]);
                    }
                })
        ])
        ->recordUrl(null)
        ->actions([]);
        }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListValidacions::route('/'),
            'create' => Pages\CreateValidacion::route('/create'),
            'edit' => Pages\EditValidacion::route('/{record}/edit'),
        ];
    }
}