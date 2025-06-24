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
    protected static ?int $navigationSort = 2;
    
    /** @var User $user */
public static function shouldRegisterNavigation(): bool
{
    $user = auth()->user();
      /** @var User $user */
    if (!$user->hasRole('Comisión Permanente')) {
        return true;
    }

    if (!$user->docente) {
        return false;
    }

    $comisionActiva = \App\Models\ComisionPermanente::where('estado', true)
        ->where('fecha_fin', '>', now())
        ->first();

    if (!$comisionActiva) {
        return false;
    }
    $esPresidente = $user->docente->integranteComision()
        ->where('comision_permanente_id', $comisionActiva->id)
        ->where('cargo', 'Presidente')
        ->exists();

    return $esPresidente;
}// recuerda revisar esta validacion


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
                    ->formatStateUsing(function ($state, $record) {
                        $grado = $record->solicitude?->asesor?->grado_academico;
                        return $grado ? $grado . ' ' . $state : $state;
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('solicitude.nombre')
                    ->label('Titulo de práctica')
                    ->extraAttributes([
                        'style' => 'width: 347px; word-wrap: break-word; white-space: normal;text-align: justify;',
                    ])
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                 
                Tables\Columns\IconColumn::make('solicitude.informe')
                    ->label('Plan de práctica')
                    ->icon('heroicon-o-document-text')
                    ->alignCenter()
                    ->color(fn ($record) => $record->solicitude && $record->solicitude->informe ? 'primary' : 'danger')
                    ->url(fn ($record) => $record->solicitude && $record->solicitude->informe ? asset('storage/' . ltrim(str_replace('storage/', '', $record->solicitude->informe), '/')) : null
                    )
                    ->openUrlInNewTab()
                    ->tooltip(fn ($record) => $record->solicitude && $record->solicitude->informe ? 'Ver plan de práctica' : 'Sin archivo'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('comisionPermanente.nombre')
                    ->searchable()
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
               
                    
               Tables\Columns\TextColumn::make('nombres_comision')
                    ->label('Integrantes de la Comisión')
                    ->extraAttributes(['style' => 'width: 280px'])
                    ->html()
                    
                    ->getStateUsing(function ($record) {
                        return $record->comisionPermanente?->integranteComision->map(function ($integrante) {
                            $grado = $integrante->docente->grado_academico ?? '';
                            $nombre = $integrante->docente->nombre ?? '';
                            return trim("$grado $nombre");
                        })->implode('<br>') ?? '<em>Sin integrantes</em>';
                    })
                    ->wrap(),
                
                 Tables\Columns\TextColumn::make('cargos_comision')
                    
                    ->label('Cargo ')
                    ->html()
                    ->getStateUsing(function ($record) {
                        return $record->comisionPermanente?->integranteComision->map(function ($integrante) {
                            return "  {$integrante->cargo}";
                        })->implode('<br>') ?? '<em>Sin cargos</em>';
                    })
                    ->wrap(), 
                Tables\Columns\TextColumn::make('fecha_entrega_a_docentes')
                    ->searchable()
                     ->alignCenter()
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('fecha_sustentacion')
                   ->label('Fecha de sustentación')
                   ->searchable()
                    ->dateTime('l, d \d\e F \d\e\l Y \a \l\a\s H:i a')
                    ->sortable()
                    ->extraAttributes([
                         'style' => 'max-width: 150px; white-space: normal; overflow-wrap: break-word; text-align: justify;',
                    ]),
            
                Tables\Columns\TextColumn::make('fecha_resolucion')
                    ->label('Fecha de resolución')
                     ->alignCenter()
                    ->searchable()
                    ->date()
                    ->sortable()
                     ->toggleable(isToggledHiddenByDefault: false),
                
                Tables\Columns\IconColumn::make('resolucion')
                    ->label('Resolución')
                    ->icon('heroicon-o-document-text')
                    ->alignCenter()
                    ->color(fn ($record) => $record->resolucion ? 'primary' : 'danger')
                    ->url(fn ($record) => $record->resolucion ? asset('storage/' . str_replace('storage/', '', $record->resolucion)) : null)
                    ->openUrlInNewTab()
                    ->tooltip(fn ($record) => $record->resolucion ? 'Ver resolución' : 'Sin archivo')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('observaciones')
                    ->label('Presentación')
                     
                    ->wrap()
                    ->html()
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return '<em>Por sustentar</em>';
                        }
                        return preg_replace(
                            '/Reprogramado/',
                            '<span class="font-bold text-danger">Reprogramado</span>',
                            nl2br(e($state))
                        );
                    })
                    ->sortable()
                    ->extraAttributes([
                        'style' => 'width: 150px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; word-wrap: break-word;',
                    ])
                     ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado') 
                     ->alignCenter()
                    ->searchable()
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Pendiente' => 'warning',             
                        'Observado' => 'success',              
                        'Desaprobado' => 'danger',            
                        'Aprobado' => 'primary',     
                        default => 'gray',                   
                    })
                    ->formatStateUsing(fn ($state) => $state),
                

                   // Tables\Columns\TextColumn::make('estado')
                    //->label('Evaluación') 
                   // ->searchable(),
                
                IconColumn::make('semaforo')
                    ->label('Semáforo')
                     ->alignCenter()
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
                            return ($dias <= 15) ? 'primary' : 'danger';
                        }
                
                        $fechaLimite = \Carbon\Carbon::parse($record->created_at)->addWeekdays(15);
                        return now()->gt($fechaLimite) ? 'danger' : 'primary';
                    })
                    ->tooltip(function ($record) {
                        if (!$record->created_at) {
                            return 'Esperando fecha de inicio';
                        }
                
                        if ($record->fecha_sustentacion) {
                            $dias = \Carbon\Carbon::parse($record->created_at)
                                ->diffInWeekdays($record->fecha_sustentacion);
                            return ($dias <= 15) 
                                ? "Cumplió: {$dias} días hábiles" 
                                : "Incumplió: {$dias} días hábiles";
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                
                //Tables\Actions\EditAction::make(),
                Action::make('actualizar_fechas')
                ->label('Asignar fecha')
                ->icon('heroicon-o-calendar')
                ->modalHeading(' ')
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-calendar-days')
                ->modalHeading(fn ($record) =>'ASIGNAR FECHAS DE SUSTENTACIÓN A ' .strtoupper(optional($record->solicitude->estudiante)->nombre))
                ->modalSubmitActionLabel('Guardar')
                ->modalWidth('md')
                ->form([
                    
                    Forms\Components\DatePicker::make('fecha_entrega_a_docentes')
                        ->label('Fecha Entrega Docentes'),
                    
                    Forms\Components\DateTimePicker::make('fecha_sustentacion')
                        ->label('Fecha Sustentación')
                        ,
                ])
               ->action(function (PlanPractica $record, array $data) {
                    $datosActualizar = [];

                    if (!empty($data['fecha_entrega_a_docentes'])) {
                        $datosActualizar['fecha_entrega_a_docentes'] = $data['fecha_entrega_a_docentes'];
                    }

                    if (!empty($data['fecha_sustentacion'])) {
                        $datosActualizar['fecha_sustentacion'] = $data['fecha_sustentacion'];
                        if (empty($record->fecha_sustentacion)) {
                            $datosActualizar['observaciones'] = 'Por sustentar';
                        }

                       
                        if ($record->fecha_sustentacion && $record->fecha_sustentacion != $data['fecha_sustentacion']) {
                            $fechaAnterior = \Carbon\Carbon::parse($record->fecha_sustentacion)->format('d/m/Y H:i');
                            $fechaNueva = \Carbon\Carbon::parse($data['fecha_sustentacion'])->translatedFormat('l, d \d\e F \d\e\l Y \a \l\a\s H:i a');
                            $datosActualizar['observaciones'] = "Reprogramado del {$fechaAnterior} para la el día {$fechaNueva}";
                        }
                    }

                    if (!isset($datosActualizar['observaciones'])) {
                        $datosActualizar['observaciones'] = $record->observaciones;
                    }

                    if (!empty($datosActualizar)) {
                        $record->update($datosActualizar);
                    }
                     $usuarioEstudiante = $record->solicitude->estudiante?->user;
                     $usuarioComision = $record->solicitude->estudiante?->user;

                        if ($usuarioEstudiante) {
                        Notification::make()
                            ->title('Asignación de Fecha de Sustentación')
                            ->body('Se te ha asignado la fecha de sustentación de tu Plan de Práctica. Revisa la seción de seguimiento.')
                             ->success()
                            ->sendToDatabase($usuarioEstudiante);
                    }
                    // Obtener la comisión permanente activa
                            $comision = \App\Models\ComisionPermanente::where('estado', true)->first();

                            if ($comision && $record->fecha_sustentacion) {
                                $fechaFormateada = \Carbon\Carbon::parse($record->fecha_sustentacion)->format('d/m/Y H:i');

                                foreach ($comision->integranteComision as $integrante) {
                                    $userDocente = optional($integrante->docente)->user;

                                    if ($userDocente) {
                                        Notification::make()
                                            ->title('Fecha de Sustentación programada')
                                            ->body('Se ha programado la sustentación de la/el estudiante ' . strtoupper(optional($record->solicitude->estudiante)->nombre) . ' para el día ' . $fechaFormateada . '. ')
                                             ->success()
                                            ->sendToDatabase($userDocente);
                                    }
                                }
                            }

                    Notification::make()
                        ->title('Fechas actualizadas correctamente')
                        ->success()
                        ->send();
                        
                })
                ->modalSubmitActionLabel('Guardar')
                ->modalCancelActionLabel('Cancelar')
                 ->visible(function (PlanPractica $record) {
                        $user = auth()->user();
                        /** @var \App\Models\User $user */

                        if (in_array($record->estado, ['Aprobado', 'Desaprobado'])) {
                            return false; // Nadie la ve si está Aprobado o Desaprobado
                        }

                        return $user->hasAnyRole(['Admin', 'Secretaria', 'Comisión Permanente']);
                    }),
                    
                
                 Action::make('asignar_resolucion')
                    ->label('Asignar Resolución')
                    ->icon('heroicon-o-document-check')
                    ->modalHeading(' ')
                     ->modalHeading(fn ($record) =>'ASIGNANDO RESOLUCIÓN A ' .strtoupper(optional($record->solicitude->estudiante)->nombre))
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-clipboard-document-check')
                    ->modalSubmitActionLabel('Guardar')
                    ->modalWidth('md')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_resolucion')
                            ->label('Fecha de Resolución')
                            ,

                        Forms\Components\FileUpload::make('resolucion')
                            ->label('Archivo de Resolución')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10000) 
                            ->directory('resoluciones') // carpeta de almacenamiento
                            ,
                    ])
                    ->action(function ($record, array $data) {
                        $camposActualizados = [];
                        if (!empty($data['fecha_resolucion'])) {
                            $camposActualizados['fecha_resolucion'] = $data['fecha_resolucion'];
                        }

                        if (!empty($data['resolucion'])) {
                            $camposActualizados['resolucion'] = $data['resolucion'];
                        }

                        if (!empty($camposActualizados)) {
                            $record->update($camposActualizados);

                            Notification::make()
                                ->title('Resolución asignada correctamente')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('No se ingresaron cambios')
                                ->warning()
                                ->send();
                        }
                    })
                    ->modalSubmitActionLabel('Guardar')
                    ->modalCancelActionLabel('Cancelar')
                      ->visible(function ($record) {
                            $user = auth()->user();
                            /** @var User $user */
                            
                            return $user->hasAnyRole(['Admin', 'Secretaria']);
                        }),


                Action::make('descargar_carta')
                ->label('')
                //->icon('heroicon-o-arrow-down')
                ->action(function ($record) {
                    $templatePath = storage_path('app/public/plantillas/carta.docx');
                    
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
