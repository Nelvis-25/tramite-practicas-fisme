<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InformeDePracticaResource\Pages;
use App\Filament\Resources\InformeDePracticaResource\RelationManagers;
use App\Models\InformeDePractica;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Actions\BulkAction;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InformeDePracticaExport;

class InformeDePracticaResource extends Resource
{
    protected static ?string $model = InformeDePractica::class;
    protected static ?string $navigationGroup = 'Informe de Prácticas';
    protected static ?string $navigationLabel = 'Informe de Prácticas';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('solicitud_informe_id')
                    ->label('Nombre del practicante')
                    ->options(function () {
                        return \App\Models\SolicitudInforme::with('estudiante')->get()->pluck('estudiante.nombre', 'id');
                    })
                    ->searchable()
                    ->required(),
                 Forms\Components\DatePicker::make('fecha_resolucion')
                ->label('Fecha de resolucion'),
                Forms\Components\DatePicker::make('fecha_entrega_a_docentes')
                ->label(' Entrega al docente'),
                Forms\Components\DateTimePicker::make('fecha_sustentacion')
                ->label('Fecha de sustentacion'),
                 Forms\Components\TextInput::make('estado')
                    ->label('Estado')
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
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('solicitudInforme.estudiante.nombre')
                    ->label('Nombre del estudiante')
                    ->sortable()
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('solicitudInforme.practica.solicitude.asesor.nombre')
                    ->label('Asesor')
                    ->formatStateUsing(function($state, $record){
                      $grado = $record->solicitudInforme?->practica?->solicitude?->asesor?->grado_academico;
                     return $grado ? $grado . ' ' . $state : $state;
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                 Tables\Columns\TextColumn::make('solicitudInforme.practica.solicitude.nombre')
                     ->label('Titulo de práctica')
                    ->extraAttributes([
                        'style' => 'width: 347px; word-wrap: break-word; white-space: normal;text-align: justify;',
                    ])
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                
               Tables\Columns\IconColumn::make('solicitudInforme.informe')
                    ->label('Informe')
                    ->icon('heroicon-o-document-text')
                    ->alignCenter()
                    ->color(fn ($record) => $record->solicitudInforme && $record->solicitudInforme->informe ? 'primary' : 'danger')
                    ->url(fn ($record) => 
                        $record->solicitudInforme && $record->solicitudInforme->informe
                            ? asset('storage/' . ltrim(str_replace('storage/', '', $record->solicitudInforme->informe), '/'))
                            : null
                    )
                    ->openUrlInNewTab()
                    ->tooltip(fn ($record) => $record->solicitudInforme && $record->solicitudInforme->informe ? 'Ver informe' : 'Sin archivo')
                    ->toggleable(isToggledHiddenByDefault: true),              
                    
                  Tables\Columns\TextColumn::make('jurados')
                    ->label('Jurados')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        return $record->jurados->map(fn ($jurado) =>
                            "<div>{$jurado->docente->grado_academico} {$jurado->docente->nombre}</div>"
                        )->implode('');
                    }),
                    

                    Tables\Columns\TextColumn::make('jurados.cargo')
                        ->label('Cargos')
                        ->html()
                        ->formatStateUsing(function ($state) {
                            return str_replace(',', '<br>', $state);
                        }),
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
                    ])
                    ->toggleable(isToggledHiddenByDefault: false),
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
                         'style' => 'max-width: 140px; white-space: normal; overflow-wrap: break-word; text-align: justify;',
                    ])
                     ->toggleable(isToggledHiddenByDefault: false)
                     ->searchable()
                     ,
                
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
                    ->formatStateUsing(fn ($state) => $state)
                    ->toggleable(isToggledHiddenByDefault: false),

                 Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
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
                            return ($dias <= 15) ? 'primary' : 'success';
                        }
                
                        $fechaLimite = \Carbon\Carbon::parse($record->created_at)->addWeekdays(15);
                        return now()->gt($fechaLimite) ? 'primary' : 'success';
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
                    })
                     ->toggleable(isToggledHiddenByDefault: false),
                
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('actualizar_fechas')
                ->label('Asignar fecha')
                ->icon('heroicon-o-calendar')
                ->modalHeading(' ')
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-calendar-days')
                ->modalHeading(function ($record) {
                $nombreEstudiante = $record->solicitudInforme->estudiante->nombre;
                return strtoupper("Asignando fecha de sustentación a {$nombreEstudiante}"); })
                 ->modalSubmitActionLabel('Guardar')
                ->modalWidth('md')
                ->form([
                    
                    Forms\Components\DatePicker::make('fecha_entrega_a_docentes')
                        ->label('Fecha Entrega Docentes'),
                    
                    Forms\Components\DateTimePicker::make('fecha_sustentacion')
                        ->label('Fecha Sustentación')
                        ,
                ])
               ->action(function (InformeDePractica $record, array $data) {
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
                            $datosActualizar['observaciones'] = "Reprogramado del: {$fechaAnterior} para el día {$fechaNueva}";
                        }
                    }

                    if (!isset($datosActualizar['observaciones'])) {
                        $datosActualizar['observaciones'] = $record->observaciones;
                    }

                    if (!empty($datosActualizar)) {
                        $record->update($datosActualizar);
                    }
                    // notificacion al estudiante 
                     $usuarioEstudiante = $record->solicitudInforme->estudiante?->user;
                     
                        if ($usuarioEstudiante) {
                        Notification::make()
                            ->title('Asignación de Fecha de Sustentación')
                            ->body('Se te ha asignado la fecha de sustentación de tu Informe de Práctica. Revisa la seción de seguimiento.')
                             ->success()
                            ->sendToDatabase($usuarioEstudiante);
                    }
                     // ✅ Notificar a los jurados asignados
                      $jurados = $record->jurados; 

                            foreach ($jurados as $jurado) {
                                $usuario = $jurado->docente?->user;

                                if ($usuario) {
                                    Notification::make()
                                        ->title('Fecha de Sustentación Programada')
                                        ->body(
                                        'Se ha programado la sustentación de el/la estudiante ' 
                                        . $record->solicitudInforme->estudiante->nombre . 
                                        ', para evaluar su informe final el día ' 
                                        . \Carbon\Carbon::parse($record->fecha_sustentacion)->format('d/m/Y') . 
                                        ' a las ' . \Carbon\Carbon::parse($record->fecha_sustentacion)->format('H:i')
                                    )
                                      ->success()
                                     ->sendToDatabase($usuario);
                                }
                            }

                    Notification::make()
                        ->title('Fechas actualizadas correctamente')
                        ->success()
                        ->send();
                        
                })
                ->modalSubmitActionLabel('Guardar')
                ->modalCancelActionLabel('Cancelar')
                 ->visible(function (InformeDePractica $record) {
                        $user = auth()->user();
                        /** @var \App\Models\User $user */

                        if (in_array($record->estado, ['Aprobado'])) {
                            return false; // Nadie la ve si está Aprobado o Desaprobado
                        }

                        return $user->hasAnyRole(['Admin', 'Secretaria', 'Jurado de informe']);
                    }),
                
                Tables\Actions\Action::make('asignar_resolucion')
                    ->label('Asignar Resolución')
                    ->icon('heroicon-o-document-check')
                    ->modalHeading(' ')
                    ->modalHeading(function ($record) {
                    $nombreEstudiante = $record->solicitudInforme->estudiante->nombre;
                    return strtoupper("ASIGNANDO RESOLUCIÓN a {$nombreEstudiante}"); })
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
            ])
            ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
               
            ]),
             BulkAction::make('exportarExcel')
                    ->label('Exportar seleccionados')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(function ($records) {
                        $ids = $records->pluck('id')->toArray();

                        return Excel::download(
                            new InformeDePracticaExport($ids),
                            'Listado de Informes de Práctica.xlsx'
                        );
                    })
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListInformeDePracticas::route('/'),
            'create' => Pages\CreateInformeDePractica::route('/create'),
            'edit' => Pages\EditInformeDePractica::route('/{record}/edit'),
        ];
    }
}
