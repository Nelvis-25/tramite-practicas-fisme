<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InformePracticaResource\Pages;
use App\Filament\Resources\InformePracticaResource\RelationManagers;
use App\Models\InformePractica;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InformePracticaResource extends Resource
{
    protected static ?string $model = InformePractica::class;
    protected static ?string $navigationGroup = 'Informe de practicas';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('solicitud_informe_id')
                    ->label('Nombre de practicante')
                    ->relationship('solicitudInforme.estudiante', 'nombre')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('jurado_informe_id')
                    ->label('Nombre del jurado')
                    ->relationship('juradoInforme', 'nombre')
                    ->required()
                    ->searchable(),
                Forms\Components\DatePicker::make('fecha_resolucion')
                ->label('Fecha de resolucion'),
                Forms\Components\DatePicker::make('fecha_entrega_a_docentes')
                ->label(' Entrega al docente'),
                Forms\Components\DateTimePicker::make('fecha_sustentacion')
                ->label('Fecha de sustentacion'),
                Forms\Components\TextInput::make('estado')
                    ->default('Pendiente')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
                Tables\Columns\TextColumn::make('solicitudInforme.estudiante.tipo_estudiante')
                ->label('Est/Egre')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('solicitudInforme.estudiante.nombre')
                    ->numeric()
                    ->sortable(),
               Tables\Columns\TextColumn::make('solicitudInforme.practica.solicitude.nombre')
                    ->label('Titulo de práctica')
                    ->extraAttributes([
                        'style' => 'width: 380px; word-wrap: break-word; white-space: normal;text-align: justify;',
                    ])
                    ->numeric()
                    ->sortable(),
                    Tables\Columns\TextColumn::make('solicitudInforme.informe')
                    ->label('Informe de práctica')
                    ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Sin archivo')
                    ->url(function ($record) {
                        $ruta = $record->solicitudInforme?->informe;
                
                        return $ruta ? asset('storage/' . ltrim(str_replace('storage/', '', $ruta), '/')) : null;
                    })
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-document-text')
                    ->searchable()
                    ->extraAttributes([
                        'style' => 'width: 200px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;',
                    ]),
                
                Tables\Columns\TextColumn::make('juradoInforme.nombre')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cargos_comision')
                    
                    ->label('Cargo de los jurados')
                    ->html()
                    ->getStateUsing(function ($record) {
                        return $record->juradoInforme?->integrante->map(function ($integrante) {
                            return "  {$integrante->cargo}";
                        })->implode('<br>') ?? '<em>Sin cargos</em>';
                    })
                    ->wrap(), 
                    Tables\Columns\TextColumn::make('nombres_comision')
                    ->label('Lista de Jurados')
                    ->extraAttributes(['style' => 'width: 250px'])
                    ->html()
                    ->getStateUsing(function ($record) {
                        return $record->juradoInforme?->integrante->map(function ($integrante) {
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
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                ->label('Evaluación') 
                    ->searchable(),
                    Tables\Columns\TextColumn::make('observaciones')
                    ->label('Sustentación')
                    ->wrap()
                    ->html()
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return '<span style="color: gray;"><em>Por sustentar</em></span>'; 
                            
                        }
                        return preg_replace(
                            '/Reprogramado/',
                            '<span class="font-bold text-danger">Reprogramado </span>',
                            nl2br(e($state))
                        );
                    })
                   
                    ->sortable()
                    ->extraAttributes([
                        'style' => 'width: 160px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; word-wrap: break-word;',
                    ]),
                IconColumn::make('semaforo')
                    ->label('Semáforo')
                    ->getStateUsing(fn () => true)
                    ->icon(function ($record) {
                        if (!$record->created_at) {
                            return 'heroicon-o-check-circle';
                        }
                
                        if ($record->fecha_sustentacion) {
                            $dias = \Carbon\Carbon::parse($record->created_at)
                                ->diffInWeekdays($record->fecha_sustentacion);
                            return ($dias <= 15) ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle';
                        }
                
                        $fechaLimite = \Carbon\Carbon::parse($record->created_at)->addWeekdays(15);
                        return now()->gt($fechaLimite) ? 'heroicon-o-x-circle' : 'heroicon-o-clock';
                    })
                    ->color(function ($record) {
                        if (!$record->created_at) {
                            return null;
                        }
                
                        if ($record->fecha_sustentacion) {
                            $dias = \Carbon\Carbon::parse($record->created_at)
                                ->diffInWeekdays($record->fecha_sustentacion);
                            return ($dias <= 15) ? 'success' : 'danger';
                        }
                
                        $fechaLimite = \Carbon\Carbon::parse($record->created_at)->addWeekdays(15);
                        return now()->gt($fechaLimite) ? 'danger' : 'success';
                    })
                    ->tooltip(function ($record) {
                        if (!$record->created_at) {
                            return 'Esperando fecha de inicio';
                        }
                
                        if ($record->fecha_sustentacion) {
                            $dias = \Carbon\Carbon::parse($record->created_at)
                                ->diffInWeekdays($record->fecha_sustentacion);
                    
                            if ($dias <= 15) {
                                return "Cumplió: {$dias} días hábiles";
                            } else {
                                $exceso = $dias - 15;
                                return "Incumplió: se excedió {$exceso} días hábiles";
                            }
                        }
                
                        $fechaLimite = \Carbon\Carbon::parse($record->created_at)->addWeekdays(15);
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
                
            ])
            ->actions([
                Tables\Actions\Action::make('actualizar_fechas')
                ->label('Asignar fecha')
                ->icon('heroicon-o-calendar')
                ->modalHeading('Actualizar Fechas Clave')
                ->modalSubmitActionLabel('Guardar')
                ->modalWidth('sm')
                ->form([
                    Forms\Components\DatePicker::make('fecha_resolucion')
                        ->label('Fecha Resolución')
                        ,
                        
                    Forms\Components\DatePicker::make('fecha_entrega_a_docentes')
                        ->label('Fecha Entrega Docentes')
                        ,
                        
                    Forms\Components\DateTimePicker::make('fecha_sustentacion')
                        ->label('Fecha Sustentación')
                        ,
                ])
                ->action(function (InformePractica $record, array $data) {
                    if (empty($record->fecha_sustentacion) && !empty($data['fecha_sustentacion'])) {
                        $data['observaciones'] = 'Por sustentar';
                    }
                    if ($record->fecha_sustentacion && $record->fecha_sustentacion != $data['fecha_sustentacion']) {
                        $fechaAnterior = \Carbon\Carbon::parse($record->fecha_sustentacion)->format('d/m/Y H:i');
                        $fechaNueva = \Carbon\Carbon::parse($data['fecha_sustentacion'])->format('d/m/Y H:i');
                
                        $data['observaciones'] = "Reprogramado de: {$fechaAnterior} para la fecha: {$fechaNueva}";
                    }
                    if (!isset($data['observaciones'])) {
                        $data['observaciones'] = $record->observaciones;
                    }
                
                    $record->update($data);
                
                    Notification::make()
                        ->title('Fechas insertadas correctamente')
                        ->success()
                        ->send();
                }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()
                
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
            'index' => Pages\ListInformePracticas::route('/'),
            'create' => Pages\CreateInformePractica::route('/create'),
            'edit' => Pages\EditInformePractica::route('/{record}/edit'),
        ];
    }
}  