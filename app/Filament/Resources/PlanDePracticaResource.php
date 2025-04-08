<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanDePracticaResource\Pages;
use App\Filament\Resources\PlanDePracticaResource\RelationManagers;
use App\Models\PlanDePractica;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlanDePracticaResource extends Resource
{
    protected static ?string $model = PlanDePractica::class;
    protected static ?string $navigationGroup = 'Plan de Prácticas';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('solicitud_id')
                ->relationship('solicitud', 'nombre')
                    ->required()
                    ,
                Forms\Components\Select::make('comision_permanente_id')
                ->relationship('comisionPermanente', 'nombre')
                    ->required()
                   ,
                Forms\Components\DatePicker::make('fecha_resolucion'),
                Forms\Components\DatePicker::make('fecha_entrega_a_docentes'),
                Forms\Components\DatePicker::make('fecha_sustentacion'),
                Forms\Components\TextInput::make('estado')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('solicitud.estudiante.tipoEstudiante.nombre')
                ->label('Est/Egre')
                ->numeric()
                ->searchable(),
                Tables\Columns\TextColumn::make('solicitud.estudiante.nombre')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('solicitud.nombre')
                ->label('plan de practica')
                    ->numeric()
                    ->searchable(),
                    TextColumn::make('cargos_comision')
                    
                    ->label('Cargo del Jurado')
                    ->html()
                    ->getStateUsing(function ($record) {
                        return $record->comisionPermanente?->integranteComision->map(function ($integrante) {
                            return "- <strong>{$integrante->cargo}</strong>";
                        })->implode('<br>') ?? '<em>Sin cargos</em>';
                    })
                    ->wrap(),
                
                TextColumn::make('nombres_comision')
                    ->label('Integrantes de la Comisión')
                    ->extraAttributes(['style' => 'width: 250px'])
                    ->html()
                    ->getStateUsing(function ($record) {
                        return $record->comisionPermanente?->integranteComision->map(function ($integrante) {
                            return "{$integrante->docente->nombre}";
                        })->implode('<br>') ?? '<em>Sin integrantes</em>';
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('fecha_resolucion')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_entrega_a_docentes')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_sustentacion')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->searchable(),
                    IconColumn::make('semaforo')
                    ->label('Semaforo')
                    ->getStateUsing(fn () => true) 
                    ->icon(function ($record) {
                        if (!$record->fecha_resolucion) {
                            return 'heroicon-o-rectangle-stack';
                        }
                
                        if ($record->fecha_sustentacion) {
                            $dias = \Carbon\Carbon::parse($record->fecha_resolucion)
                                ->diffInWeekdays($record->fecha_sustentacion);
                            return ($dias <= 15) ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle';
                        }
                
                        $fechaLimite = \Carbon\Carbon::parse($record->fecha_resolucion)->addWeekdays(15);
                        return now()->gt($fechaLimite) ? 'heroicon-o-x-circle' : 'heroicon-o-clock';
                    })
                    ->color(function ($record) {
                        if (!$record->fecha_resolucion) {
                            return null;
                        }
                
                        if ($record->fecha_sustentacion) {
                            $dias = \Carbon\Carbon::parse($record->fecha_resolucion)
                                ->diffInWeekdays($record->fecha_sustentacion);
                            return ($dias <= 15) ? 'success' : 'danger';
                        }
                
                        $fechaLimite = \Carbon\Carbon::parse($record->fecha_resolucion)->addWeekdays(15);
                        return now()->gt($fechaLimite) ? 'danger' : 'warning';
                    })
                    ->tooltip(function ($record) {
                        if (!$record->fecha_resolucion) {
                            return 'Esperando fecha de resolución';
                        }
                
                        if ($record->fecha_sustentacion) {
                            $dias = \Carbon\Carbon::parse($record->fecha_resolucion)
                                ->diffInWeekdays($record->fecha_sustentacion);
                            return ($dias <= 15) 
                                ? "Cumplió: {$dias} días hábiles" 
                                : "Incumplió: {$dias} días hábiles";
                        }
                
                        $fechaLimite = \Carbon\Carbon::parse($record->fecha_resolucion)->addWeekdays(15);
                        $diasRestantes = now()->diffInWeekdays($fechaLimite, false);
                
                        return ($diasRestantes > 0)
                            ? "Plazo: {$diasRestantes} días hábiles restantes"
                            : "¡Plazo vencido hace " . abs($diasRestantes) . " días hábiles!";
                    }),
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
            'index' => Pages\ListPlanDePracticas::route('/'),
            'create' => Pages\CreatePlanDePractica::route('/create'),
            'edit' => Pages\EditPlanDePractica::route('/{record}/edit'),
        ];
    }
}
