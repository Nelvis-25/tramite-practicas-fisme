<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ValidacionResource\Pages;
use App\Models\Validacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ValidacionResource extends Resource
{
    protected static ?string $model = Validacion::class;
    protected static ?string $navigationGroup = 'Plan de Prácticas';
    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Repeater::make('validaciones')
                ->relationship()
                ->schema([
                    Forms\Components\Hidden::make('id'),
                    Forms\Components\TextInput::make('requisito.nombre')
                        ->label('Requisito')
                        ->disabled(),
                    Forms\Components\Toggle::make('entregado')
                        ->label('Validado')
                        ->required(),
                ])
                ->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {return $table
        ->query(function () {
            $solicitud_id = request('solicitud_id');
            return Validacion::with(['requisito'])
                ->where('solicitud_id', $solicitud_id)
                ->orderBy('requisito_id');
        })
        ->columns([
            TextColumn::make('requisito.nombre')
                ->label('Requisito'),
                
            CheckboxColumn::make('entregado')
                ->label('Validado')
                ->disabled(fn ($record) => $record->entregado)
                ->extraAttributes(['class' => 'custom-checkbox'])
                ->afterStateUpdated(function ($record, $state) {
                    $record->update(['entregado' => $state]);
                    Notification::make()
                        ->title('Estado actualizado')
                        ->body("El requisito {$record->requisito->nombre} ha sido " . ($state ? 'validado' : 'marcado como pendiente'))
                        ->success()
                        ->send();
                }),
        ])
        ->headerActions([
            Tables\Actions\Action::make('guardarTodo')
                ->label('Guardar todo')
                ->action(function ($livewire) {
                    $livewire->js('$wire.$refresh()');
                })
        ])
        ->recordUrl(null)
        ->deferLoading()
        ->paginated(false);
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