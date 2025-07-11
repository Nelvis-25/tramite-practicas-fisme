<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudeResource\Pages;
use App\Notifications\ComisionAsignadaNotification;
use App\Filament\Resources\SolicitudeResource\RelationManagers;
use App\Models\ComisionPermanente;
use App\Models\PlanPractica;
use App\Models\Requisito;
use App\Models\Solicitude;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables;
use Livewire\Livewire;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SolicitudeResource extends Resource
{
    protected static ?string $model = Solicitude::class;
    protected static ?string $navigationGroup = 'Plan de Prácticas';
    protected static ?string $navigationLabel = 'Solicitudes de Plan';
    protected static ?string $navigationIcon = 'heroicon-o-document';
    protected static ?int $navigationSort = 1;
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
    
        /** @var User $user */
        $user = auth()->user();
    
        if ($user && $user->hasRole('Estudiante')) {
            $estudiante = \App\Models\Estudiante::where('user_id', $user->id)->first();
    
            if ($estudiante) {
                return $query->where('estudiante_id', $estudiante->id);
            }
    
            
            return $query->whereRaw('0 = 1');
        }
    
        return $query;
    }
   public static function canCreate(): bool
    {
        $user = auth()->user();

            if (!static::can('create')) {
                return false;
            }

            /** @var User $user */
        if ($user && $user->hasRole('Estudiante')) {
            $estudiante = $user->estudiante;

            if (!$estudiante) {
                return false;
            }

            $solicitudes = $estudiante->solicitude;

            if ($solicitudes->isEmpty()) {
                return true; 
            }

            foreach ($solicitudes as $solicitud) {
            
                foreach ($solicitud->planesPractica as $plan) {
                    if ($plan->estado === 'Desaprobado') {
                        return true; 
                    }
                }
            }

            return false; 
        }

        return true;
    }

    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextArea::make('nombre')
                    ->label('Nombre del plan de prácticas')
                    ->required()
                    ->maxLength(700)
                    ->rows(2),
                Forms\Components\Select::make('estudiante_id')
                    ->label('Selecione el nombre del estudiante')
                    ->relationship('estudiante', 'nombre')
                    ->required()
                    ->searchable(),
               
                Forms\Components\Select::make('linea_investigacion_id')
                    ->relationship('lineaInvestigacion', 'nombre')
                    ->required(),

                Forms\Components\Select::make('asesor_id')
                    ->label('Seleccione su asesor')
                    ->relationship('asesor', 'nombre', modifyQueryUsing: function ($query) {
                        return $query->whereHas('solicitude', function ($q) {
                            $q->where('activo', true)
                            ->whereIn('estado', ['Aceptado', 'Comisión asignada']);
                        }, '<', 5);
                    })
                    ->searchable()
                    ->required()
                    ->noSearchResultsMessage('Este asesor no está disponible. Ya está asignado a 5 estudiantes. Cambie de asesor e inténtelo de nuevo.')
                    ->helperText('Solo se muestran asesores con menos de 5 prácticas activas ')
                    ,
                                
                Forms\Components\DatePicker::make('fecha_inicio')
                    ->label('Fecha de inicio de su práctica')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_fin')
                    ->label('Fecha que finalizara su práctica')
                    ->required(),
                Forms\Components\FileUpload::make('solicitud')
                    ->label('Solicitud dirigida al Decano (pdf)')
                    ->directory('solicitudes')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20240)
                    ->fetchFileInformation(true)
                    ->downloadable() 
                    ->openable() 
                    ->previewable(true) 
                    ->maxSize(20240),
                Forms\Components\FileUpload::make('constancia')
                    ->label('Constancia de cursos aprobados (pdf)')
                    ->directory('constancias')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20240)
                    ->fetchFileInformation(true)
                    ->downloadable() 
                    ->openable() 
                    ->previewable(true) 
                    ->maxSize(20240)
                    ->moveFiles(),
                Forms\Components\FileUpload::make('informe')
                    ->label('Plan de Prácticas (pdf) ')
                    ->directory('informes')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20240)
                    ->fetchFileInformation(true)
                    ->downloadable() 
                    ->openable() 
                    ->previewable(true) 
                    ->maxSize(20240),
                Forms\Components\FileUpload::make('carta_presentacion')
                    ->label('Carta de autorización emitida por la Empresa (pdf) ')
                    ->directory('cartas_presentacion') 
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20240)
                    ->fetchFileInformation(true)
                    ->downloadable() 
                    ->openable() 
                    ->previewable(true) 
                    ->maxSize(20240),
                Forms\Components\FileUpload::make('comprobante_pago')
                    ->label('Comprobante de pago (img) ')
                    ->directory('comprobantes_pago')
                    ->acceptedFileTypes([ 'image/*'])
                    ->maxSize(20240)
                    ->fetchFileInformation(true)
                    ->downloadable() 
                    ->openable() 
                    ->previewable(true) 
                    ->maxSize(20240),
                Forms\Components\TextInput::make('estado')
                    ->required()
                    ->default('Pendiente') 
                    ->disabled() 
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
         
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                ->label('Nombre del plan de prácticas')
                ->extraAttributes([
                    'style' => 'width: 450px; word-wrap: break-word; white-space: normal;text-align: justify;',
                ])
                    ->searchable(),
                Tables\Columns\TextColumn::make('estudiante.nombre')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lineaInvestigacion.nombre')
                    ->numeric()
                    ->sortable()
                    ->extraAttributes([
                    'style' => 'width: 250px; word-wrap: break-word; white-space: normal;text-align: justify;',
                ])
                    ,

                Tables\Columns\TextColumn::make('asesor.nombre')
                    ->label('Asesor')
                    ->formatStateUsing(function ($state, $record) {
                        $grado = $record?->asesor?->grado_academico;
                        return $grado ? $grado . ' ' . $state : $state;
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Inicio de la práctica')
                    ->date()
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Fin de la práctica')
                    ->date()
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\IconColumn::make('solicitud')
                    ->label('Solicitud al decano')
                    ->icon('heroicon-o-document-text')
                    ->alignCenter()
                    ->color(fn ($record) => $record->solicitud ? 'primary' : 'danger')
                    ->url(fn ($record) => $record->solicitud ? asset('storage/' . str_replace('storage/', '', $record->solicitud)) : null)
                    ->openUrlInNewTab()
                    ->tooltip(fn ($record) => $record->solicitud ? 'Ver solicitud ' : 'Sin archivo')
                    ->toggleable(isToggledHiddenByDefault: false),
               Tables\Columns\IconColumn::make('constancia')
                    ->label('Constancia de cursos aprobados')
                    ->icon('heroicon-o-document-text')
                    ->alignCenter()
                    ->color(fn ($record) => $record->constancia ? 'primary' : 'danger')
                    ->url(fn ($record) => $record->constancia ? asset('storage/' . str_replace('storage/', '', $record->constancia)) : null)
                    ->openUrlInNewTab()
                    ->tooltip(fn ($record) => $record->constancia ? 'Ver constancia' : 'Sin archivo')
                    ->toggleable(isToggledHiddenByDefault: false),
               Tables\Columns\IconColumn::make('informe')
                    ->label('Plan de prácticas')
                    ->icon('heroicon-o-document-text')
                    ->alignCenter()
                    ->color(fn ($record) => $record->informe ? 'primary' : 'danger')
                    ->url(fn ($record) => $record->informe ? asset('storage/' . str_replace('storage/', '', $record->informe)) : null)
                    ->openUrlInNewTab()
                    ->tooltip(fn ($record) => $record->informe ? 'Ver plan de práctica' : 'Sin archivo')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ,
                   Tables\Columns\IconColumn::make('carta_presentacion')
                     ->label('Carta de autorización')
                     ->icon('heroicon-o-document-text')
                     ->alignCenter()
                     ->color(fn ($record) => $record->carta_presentacion ? 'primary' : 'danger')
                     ->url(fn ($record) => $record->carta_presentacion ? asset('storage/' . str_replace('storage/', '', $record->carta_presentacion)) : null)
                     ->openUrlInNewTab()
                     ->tooltip(fn ($record) => $record->carta_presentacion ? 'Ver autorización' : 'Sin archivo')
                    ->toggleable(isToggledHiddenByDefault: false),
                 Tables\Columns\IconColumn::make('comprobante_pago')
                    ->label('Comprobante de pago')
                    ->icon('heroicon-o-document')
                    ->alignCenter()
                    ->color(fn ($record) => $record->comprobante_pago ? 'primary' : 'danger')
                    ->url(fn ($record) => $record->comprobante_pago ? asset('storage/' . str_replace('storage/', '', $record->comprobante_pago)) : null)
                    ->openUrlInNewTab()
                    ->tooltip(fn ($record) => $record->comprobante_pago ? 'Ver comprobante de pago' : 'Sin archivo')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('estado')
                ->label('Estado')
                    ->searchable()
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Pendiente' => 'warning',             
                        'Aceptado' => 'success',              
                        'Rechazado' => 'danger',            
                        'Comisión asignada' => 'primary',     
                        default => 'gray',                   
                    })
                    ->formatStateUsing(fn ($state) => $state),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                
                Action::make('Validar')
                    ->label('Validar')
                    ->icon('heroicon-o-check-circle')  
                    ->color('success')
                    ->modalHeading(fn ($record) =>'VALIDANDO SOLICITUD DE '.strtoupper(optional($record->estudiante)->nombre))
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-clipboard-document-check')
                    ->modalSubmitActionLabel('Guardar')
                    ->modalWidth('md')
                    ->form([
                        Forms\Components\Placeholder::make('lista_requisitos')
                            ->label('Requisitos registrados')
                            ->content(function () {
                                return Requisito::all()
                                    ->pluck('nombre')
                                    ->map(fn($item) => '• ' . trim($item))
                                    ->implode("\n");
                            })
                            ->extraAttributes([
                                'style' => 'white-space: pre-wrap; line-height: 1.5; font-family: sans-serif;',
                                'class' => 'border border-gray-300 rounded-lg p-4',
                            ]),

                        Forms\Components\Radio::make('decision')
                            ->label('Seleccione una opción:')
                            ->options([
                                'validado' => 'Aceptar Solicitud',
                                'rechazado' => 'Rechazar Solicitud',
                            ])
                            ->required()
                            ->reactive()
                            ->columnSpanFull()
                            ->extraAttributes([
                                'class' => 'font-bold flex justify-center gap-4 border border-gray-300 rounded-lg p-4 mt-2',
                            ]),

                        // Textarea para motivo de rechazo, visible solo si se selecciona 'rechazado'
                        Forms\Components\Textarea::make('motivo_rechazo')
                            ->label('Motivo del rechazo')
                            ->placeholder('Explique por qué se rechazó la solicitud...')
                            ->columnSpanFull()
                            ->rows(4)
                            ->maxLength(500)
                            ->visible(fn ($get) => $get('decision') === 'rechazado')
                            ->requiredIf('decision', 'rechazado'),
                    ])
                    ->action(function (Solicitude $record, array $data) {
                        $decision = $data['decision'];

                        if ($decision === 'validado') {
                            $record->update([
                                'estado' => 'Aceptado',
                            ]);

                            Notification::make()
                                ->title('Solicitud Aceptada')
                                ->success()
                                ->send();

                                // aca verificas que llege la notiicacion al director de escuela
                            $usuarioDirector = User::role('Director de escuela')->get();
                            foreach ($usuarioDirector as $usuario) {
                                Notification::make()
                                    ->title('Asignacion de Comisión Permanente')
                                     ->body('Tienes una nueva solicitud aceptada que requiere la asignación de una Comisión Permanente. 
                                           <br><a href="' . route('filament.admin.resources.solicitudes.index') . '" style="color: #3b82f6; text-decoration: underline;">Ver solicitudes</a>')
                                     ->success()
                                    ->sendToDatabase($usuario);
                            }
                        }

                        if ($decision === 'rechazado') {
                            $record->update([
                                'estado' => 'Rechazado',
                            ]);

                            // Guardar el motivo como observación
                            $record->observacions()->create([
                                'mensaje' => $data['motivo_rechazo'],
                                
                            ]);

                            Notification::make()
                                ->title('Solicitud Rechazada')
                                ->body('Se registró el motivo del rechazo.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->modalSubmitActionLabel('Guardar')
                    ->modalCancelActionLabel('Cancelar')
                    
                 ->visible(function ($record) {
                        $user = auth()->user();
                      /** @var User $user */

                    if (in_array($record->estado, ['Aceptado','Comisión asignada'])) {
                            // Solo admin puede ver en esos estados
                            return $user->hasRole('Admin');
                        }
                        return $user->hasAnyRole(['Admin', 'Secretaria']);
                    })
                ,

                Action::make('Asignar Jurado')
                ->label('Asignar Comisión')
                ->icon('heroicon-o-user-group')
                ->modalIcon('heroicon-o-user-group') 
                ->requiresConfirmation()
                ->modalWidth('md')
                ->color('primary')
                ->modalHeading(function ($record) {
                    $comision = \App\Models\ComisionPermanente::where('estado', true)
                        ->where('fecha_fin', '>', now())
                        ->first();

                    if (!$comision) {
                        return 'NO SE ENCONTRO UNA COMISION ACTIVA, VERIFIQUE EN EL PANEL DE COMISIONES';
                    }

                    return 'SE ASIGNARA LA  "' . $comision->nombre . '" A LA SOLICITUD DEL ESTUDIANTE ' . strtoupper(optional($record->estudiante)->nombre);
                })
                ->modalDescription('¿Desea asignar esta comisión ?')
                ->action(function ($record) {
            
                    // ✅ Validar que la solicitud esté en estado 'Validado'
                    if ($record->estado !== 'Aceptado') {
                        Notification::make()
                            ->title('No se puede asignar jurado')
                            ->body('No se pudo asignar una comisión debido a que esta solicitud aún no ha sido revisada o validada.')
                            ->danger()
                            ->send();
            
                        return; 
                    }
            
                    $existePlan = PlanPractica::where('solicitude_id', $record->id)->exists();
            
                    if ($existePlan) {
                        $record->update([
                            'estado' => 'Comisión asignada',
                        ]);
                    }
                     else {
                        $comisionActiva = ComisionPermanente::where('estado', true)
                            ->where('fecha_fin', '>', now())
                            ->first();
            
                        if (!$comisionActiva) {
                            Notification::make()
                                ->title('Sin comisión activa')
                                ->body('No hay comisión activa disponible para asignar.')
                                ->danger()
                                ->send();
            
                            return;
                        }
            
                        PlanPractica::create([
                            'solicitude_id' => $record->id,
                            'comision_permanente_id' => $comisionActiva->id,
                            'estado' => 'Pendiente',
                        ]);
            
                        $record->update([
                            'estado' => 'Comisión asignada',
                        ]);
                        // envio de notificacion al usuario estudiante
                         $usuarioEstudiante = $record->estudiante?->user;

                        if ($usuarioEstudiante) {
                        Notification::make()
                            ->title('Comisión Permanente asignada')
                            ->body('Ya se te asignó la Comisión Permanente que evaluará tu Plan de Práctica. 
                            Revisa la sección de Seguimiento para ver los docentes asignados.')
                             ->success()
                            ->sendToDatabase($usuarioEstudiante);
                    }
                        
                    }
            
                    // ✅ Notificación de éxito (opcional)
                    Notification::make()
                        ->title('Jurado asignado correctamente')
                        ->success()
                        ->send();
                })
                ->visible(function ($record) {
                        $user = auth()->user();
                      /** @var User $user */

                    if (in_array($record->estado, ['Comisión asignada'])) {
                            // Solo admin puede ver en esos estados
                            return $user->hasRole('Admin');
                        }
                        return $user->hasAnyRole(['Admin', 'Director de escuela']);
                    })
                ,

                Action::make('notas')
                ->label('')
                ->icon('heroicon-o-chat-bubble-left')
                ->modalHeading(' ')
                ->modalHeading('HISTORIAL DE OBSERVACIONES')
                ->modalWidth('md')
                ->form([
                    Forms\Components\Repeater::make('notas_existentes')
                        ->label('')
                        ->schema([
                            Forms\Components\Textarea::make('mensaje')
                                ->disabled()
                                ->columnSpanFull()
                                
                                ->extraAttributes([
                                    'class' => 'bg-gray-100 text-gray-500 rounded-lg p-1 border border-gray-300 resize-none',
                                   
                                ])
                                ->formatStateUsing(fn ($state) => $state), // Muestra solo el mensaje
                                
                                ])
                        ->dehydrated(false)
                        ->disabled()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => 
                            isset($state['created_at']) 
                                ? date('d/m/Y H:i', strtotime($state['created_at'])) // Muestra fecha y hora en el ítem
                                : null
                        )
                            ->default(function ($record) {
                                return $record->observacions()
                                    ->orderBy('created_at', 'desc')
                                    ->get()
                                    ->map(function ($mensaje) {
                                        return [
                                            'mensaje' => $mensaje->mensaje,  // Asegúrate que este sea el campo correcto
                                            'created_at' => $mensaje->created_at,
                                        ];
                                    })
                                    ->toArray();
                            })
                        
                ])
                    ->modalCancelActionLabel('Cerrar')
                   ->modalSubmitActionLabel('Salir')
                ,
                Tables\Actions\EditAction::make()
                 ->visible(function ($record) {
                    $user = auth()->user();
                        /** @var User $user */
                        if ($user->hasRole('Estudiante')) {
                            foreach ($record->planesPractica as $plan) {
                                if (in_array($plan->estado, ['Observado', 'Pendiente'])) {
                                    return true;
                                }
                            }
                            return false;
                        }

                        return true; }),
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
            'index' => Pages\ListSolicitudes::route('/'),
            'create' => Pages\CreateSolicitude::route('/create'),
            'edit' => Pages\EditSolicitude::route('/{record}/edit'),
        ];
    }
}