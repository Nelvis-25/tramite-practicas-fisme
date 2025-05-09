<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanPracticaResource\Pages;
use App\Filament\Resources\PlanPracticaResource\RelationManagers;
use App\Models\PlanPractica;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use PhpOffice\PhpWord\TemplateProcessor;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlanPracticaResource extends Resource
{ 
    
    protected static ?string $model = PlanPractica::class;
    protected static ?string $navigationLabel = 'Plan de Prácticas';
    protected static ?string $pluralLabel = 'Plan de Prácticas';
    protected static ?string $navigationGroup = 'Plan de Prácticas';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('solicitude_id')
                ->relationship('solicitude', 'nombre')
                    ->required()
                    ->searchable()
                   ,
                Forms\Components\Select::make('comision_permanente_id')
                ->relationship('comisionPermanente', 'nombre')
                ->required()
                ->searchable()
               ,
                Forms\Components\DatePicker::make('fecha_resolucion'),
                Forms\Components\DatePicker::make('fecha_entrega_a_docentes'),
                Forms\Components\DateTimePicker::make('fecha_sustentacion'),
                Forms\Components\TextInput::make('estado')
                    
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('solicitude.estudiante.tipo_estudiante')
                ->label('Est/Egre')
                ->numeric()
                ->searchable(),
                Tables\Columns\TextColumn::make('solicitude.estudiante.nombre')
                    ->label('Nombre del estudiante')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                    Tables\Columns\TextColumn::make('solicitude.asesor.nombre')
                    ->label('Asesor')
                    ->numeric()
                    ->sortable()
                    ->searchable(),  
                
                Tables\Columns\TextColumn::make('solicitude.nombre')
                    ->label('Titulo de práctica')
                    ->extraAttributes([
                        'style' => 'width: 300px; word-wrap: break-word; white-space: normal;text-align: justify;',
                    ])
                    ->numeric()
                    ->sortable(),
                 
                    Tables\Columns\TextColumn::make('solicitude.informe')
                    ->label('Plan de práctica')
                    
                    ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Sin archivo')  // Usar la misma lógica para mostrar el nombre del archivo
                    ->url(function ($record) {
                        if (!$record->solicitude || !$record->solicitude->informe) return null;
                        return asset('storage/'.str_replace('storage/', '', $record->solicitude->informe));  // Asegurarte de acceder correctamente a "informe" en el objeto "solicitude"
                    })
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-document-text')
                    ->searchable()
                    ->extraAttributes([
                        'style' => 'width: 200px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; word-wrap: break-word;',
                    ]),
                Tables\Columns\TextColumn::make('comisionPermanente.nombre')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                    TextColumn::make('cargos_comision')
                    
                    ->label('Cargo de la comisión')
                    ->html()
                    ->getStateUsing(function ($record) {
                        return $record->comisionPermanente?->integranteComision->map(function ($integrante) {
                            return "  {$integrante->cargo}";
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
                  ->searchable()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_entrega_a_docentes')
                ->searchable()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_sustentacion')
                ->searchable()
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->searchable(),

                    Tables\Columns\TextColumn::make('estado')
                    ->searchable(),
                    IconColumn::make('semaforo')
                    ->label('Semaforo')
                    ->getStateUsing(fn () => true) 
                    ->icon(function ($record) {
                        if (!$record->fecha_resolucion) {
                            return 'heroicon-o-check-circle';
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
                        return now()->gt($fechaLimite) ? 'danger' : 'success';
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
                Action::make('crearPractica')
    ->label('Crear Práctica')
    ->action(function (Model $record) {
        
        \App\Models\Practica::create([
            'estudiante_id' => $record->solicitude->estudiante_id,
            'docente_id' => $record->solicitude->asesor_id, // asesor
            'solicitude_id' => $record->solicitude_id,
            'plan_practica_id' => $record->id,
            'empresa_id' => $record->solicitude->empresa_id,
            'estado' => 'En Desarrollo', // O el estado que elijas
        ]);
        
        Notification::make()
            ->title('Práctica creada exitosamente.')
            ->success()
            ->send();
    })
    ->visible(fn (Model $record) => true),
                //Tables\Actions\EditAction::make(),
                Action::make('actualizar_fechas')
                    ->label('Asignar fecha')
                    ->icon('heroicon-o-calendar')
                    ->modalHeading('Actualizar Fechas Clave')
                    ->modalSubmitActionLabel('Guardar')
                    ->modalWidth('sm')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_resolucion')
                            ->label('Fecha Resolución')
                            ->required(),
                            
                        Forms\Components\DatePicker::make('fecha_entrega_a_docentes')
                            ->label('Fecha Entrega Docentes')
                            ,
                            
                        Forms\Components\DateTimePicker::make('fecha_sustentacion')
                            ->label('Fecha Sustentación')
                            ->required(),
                    ])
                    ->action(function (PlanPractica $record, array $data) {
                        $record->update($data);
                        
                        Notification::make()
                            ->title('Fechas actualizadas correctamente')
                            ->success()
                            ->send();
                    }),
                Action::make('descargar_carta')
                ->label('Descargar carta')
                ->icon('heroicon-o-arrow-down')
                ->action(function ($record) {
                    // Ruta al archivo de plantilla
                    $templatePath = storage_path('app/public/plantillas/carta.docx');
                    
                    // Verificar si el archivo de plantilla existe
                    if (!file_exists($templatePath)) {
                        return response()->json(['error' => 'La plantilla no se encuentra disponible.'], 404);
                    }
                
                    // Cargar el procesador de plantillas
                    try {
                        $templateProcessor = new TemplateProcessor($templatePath);
                    } catch (\Exception $e) {
                        return response()->json(['error' => 'Error al cargar la plantilla: ' . $e->getMessage()], 500);
                    }
                
                    // Obtener datos del estudiante
                    $estudianteNombre = $record->solicitude->estudiante->nombre ?? 'Nombre Estudiante';
                    $gradoasesor = $record->solicitude->asesor->grado_academico ?? 'grado academico';
                    $nombreasesor = $record->solicitude->asesor->nombre ?? 'nombre del acesor';
                    $fecharesolucion = $record->fecha_resolucion;

                        if ($fecharesolucion) {
                            // Formatear la fecha como: 13 de abril de 2025
                            $fecharesolucion = Carbon::parse($fecharesolucion)
                                ->locale('es')
                                ->isoFormat('D [de] MMMM [de] YYYY');
                        } else {
                            $fecharesolucion = 'fecha de resolución';
                        }
                    $fechasustentacion = $record->fecha_sustentacion ?? 'fecha de sustentacion';
                    // Obtener el presidente (jurado4) y su cargo (cargo4)
                    $presidente = $record->comisionPermanente?->integranteComision->firstWhere('cargo', 'Presidente');
                    $presidenteGrado = $presidente->docente->grado_academico ?? 'Grado';
                    $presidenteNombre = $presidente->docente->nombre ?? 'Presidente';
                    $presidenteCargo = $presidente->cargo ?? 'Cargo del Presidente';
                    $templateProcessor->setValue('jurado4', $presidenteNombre);  
                    $templateProcessor->setValue('cargo4', $presidenteCargo);   
            
                    // Obtener los demás jurados (excluyendo al presidente)
                    $jurados = $record->comisionPermanente?->integranteComision;
                    $juradoDetails = '';
                    $index = 1;
                    $jurado1 = $jurado2 = $jurado3 = '';
                    $cargo1 = $cargo2 = $cargo3 = '';
                    $grado1 = $grado2 = $grado3 = '';
                    if ($jurados && $jurados->isNotEmpty()) {
                        foreach ($jurados as $jurado) {
                            if ($jurado->cargo !== 'Presidente') {
                                if ($index == 1) {
                                    $jurado1 = $jurado->docente->nombre;
                                    $cargo1 = $jurado->cargo;
                                    $grado1 = $jurado->docente->grado_academico;
                                } elseif ($index == 2) {
                                    $jurado2 = $jurado->docente->nombre;
                                    $cargo2 = $jurado->cargo;
                                    $grado2 = $jurado->docente->grado_academico;
                                } elseif ($index == 3) {
                                    $jurado3 = $jurado->docente->nombre;
                                    $cargo3 = $jurado->cargo;
                                    $grado3 = $jurado->docente->grado_academico;
                                }
                                $index++;
                            }
                        }
                    }
            
                    // Asignar los jurados al template
                    $templateProcessor->setValue('grado1', $grado1);
                    $templateProcessor->setValue('jurado1', $jurado1);
                    $templateProcessor->setValue('cargo1', $cargo1);
                    $templateProcessor->setValue('jurado2', $jurado2);
                    $templateProcessor->setValue('cargo2', $cargo2);
                    $templateProcessor->setValue('grado2', $grado2);
                    $templateProcessor->setValue('jurado3', $jurado3);
                    $templateProcessor->setValue('cargo3', $cargo3);
                    $templateProcessor->setValue('grado3', $grado3);
            
                    // Asignar el nombre del estudiante al template
                    $templateProcessor->setValue('ESTUDIANTE', strtoupper($estudianteNombre));
                    $templateProcessor->setValue('grado', $gradoasesor);
                    $templateProcessor->setValue('asesor', $nombreasesor);
                    $templateProcessor->setValue('resolucion', $fecharesolucion);
                    $templateProcessor->setValue('sustentacion', $fechasustentacion);
                    // Definir nombre del archivo de salida
                    $fileName = 'Carta_' . $record->id . '.docx';
                    $savePath = storage_path('app/public/cartas/' . $fileName);
                
                    // Verificar si la carpeta 'cartas' existe, si no, crearla
                    $cartasDirectory = storage_path('app/public/cartas');
                    if (!file_exists($cartasDirectory)) {
                        mkdir($cartasDirectory, 0777, true);  // Crear la carpeta con permisos adecuados
                    }
                
                    // Guardar el archivo generado
                    try {
                        $templateProcessor->saveAs($savePath);
                    } catch (\Exception $e) {
                        return response()->json(['error' => 'Hubo un problema al guardar el archivo: ' . $e->getMessage()], 500);
                    }
                
                    // Verificar si el archivo se ha guardado correctamente
                    if (!file_exists($savePath)) {
                        return response()->json(['error' => 'Hubo un problema al guardar el archivo.'], 500);
                    }
                
                    // Devolver el archivo para su descarga y eliminarlo después de enviarlo
                    return response()->download($savePath)->deleteFileAfterSend(true);
                }),
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
            'index' => Pages\ListPlanPracticas::route('/'),
            'create' => Pages\CreatePlanPractica::route('/create'),
            'edit' => Pages\EditPlanPractica::route('/{record}/edit'),
        ];
    }
}
