<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudeResource\Pages;
use App\Filament\Resources\SolicitudeResource\RelationManagers;
use App\Models\ComisionPermanente;
use App\Models\PlanPractica;
use App\Models\Requisito;
use App\Models\Solicitude;
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
    protected static ?string $navigationIcon = 'heroicon-o-document';
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
    /** @var User $user */
        if ($user && $user->hasRole('Estudiante')) {
            return !optional($user->estudiante)->solicitude()->exists();
        }
    
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre del plan de prácticas')
                    ->required()
                    ->maxLength(700),
                Forms\Components\Select::make('estudiante_id')
                    ->label('Selecione el nombre del estudiante')
                    ->relationship('estudiante', 'nombre')
                    ->required()
                    ->searchable(),
               
                Forms\Components\Select::make('linea_investigacion_id')
                    ->relationship('lineaInvestigacion', 'nombre')
                    ->required(),
                //Forms\Components\Select::make('asesor_id')
                   // ->relationship('asesor', 'nombre')
                   // ->required()
                   // ->searchable(),
                 Forms\Components\Select::make('asesor_id')
                    ->label('Selecione su asesor')
                    ->relationship('asesor', 'nombre', modifyQueryUsing: function ($query) {
                     return $query->withCount(['practicas as practicas_activas_count' => function ($q) {
                     $q->where('activo', true);
                     }])->having('practicas_activas_count', '<', 5);
                        })
                        ->searchable()
                        ->required(),
                Forms\Components\DatePicker::make('fecha_inicio')
                ->label('Ingrese la fecha de inicio de su práctica'),
                Forms\Components\DatePicker::make('fecha_fin')
                ->label('Ingrese la fecha que finalizara su práctica'),
                Forms\Components\FileUpload::make('solicitud')
                ->label('Solicitud dirigida al Decano/pdf')
                ->directory('solicitudes')
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(20240)
                ->fetchFileInformation(true)
                ->downloadable() 
                ->openable() 
                ->previewable(true) 
                ->maxSize(20240),
                Forms\Components\FileUpload::make('constancia')
                ->label('Constancia de cursos aprobados/pdf')
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
                ->label('Plan de Prácticas/pdf ')
                ->directory('informes')
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(20240)
                ->fetchFileInformation(true)
                ->downloadable() 
                ->openable() 
                ->previewable(true) 
                ->maxSize(20240),
                Forms\Components\FileUpload::make('carta_presentacion')
                ->label('Carta de autorización emitida por la Empresa/pdf ')
                ->directory('cartas_presentacion') 
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(20240)
                ->fetchFileInformation(true)
                ->downloadable() 
                ->openable() 
                ->previewable(true) 
                ->maxSize(20240),
                Forms\Components\FileUpload::make('comprobante_pago')
                ->label('Comprobante de pago/img ')
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
                    'style' => 'width: 300px; word-wrap: break-word; white-space: normal;text-align: justify;',
                ])
                    ->searchable(),
                Tables\Columns\TextColumn::make('estudiante.nombre')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lineaInvestigacion.nombre')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('asesor.nombre')
                   ->label('Asesor')
                   ->numeric()
                   ->sortable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('solicitud')
                ->label('Solicitud al decano')
                ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Sin archivo')
                ->url(function ($record) {
                    if (!$record->solicitud) return null;
                    return asset('storage/'.str_replace('storage/', '', $record->solicitud));
                })
                ->openUrlInNewTab()
                ->icon('heroicon-o-document-text'),

                Tables\Columns\TextColumn::make('constancia')
                   ->label('Constancia de cursos aprobados')
                        ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Sin archivo')
                        ->url(function ($record) {
                            if (!$record->constancia) return null;
                            return asset('storage/'.str_replace('storage/', '', $record->constancia));
                        })
                            ->searchable()
                            ->openUrlInNewTab()
                            ->icon('heroicon-o-document-text'),

                Tables\Columns\TextColumn::make('informe')
                   ->label('Plan de Prácticas')
                ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Sin archivo')
                ->url(function ($record) {
                    if (!$record->informe) return null;
                    return asset('storage/'.str_replace('storage/', '', $record->informe));
                })
                ->openUrlInNewTab()
                ->icon('heroicon-o-document-text')
                    ->searchable(),

                Tables\Columns\TextColumn::make('carta_presentacion')
                ->label('Carta de autorización')
                ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Sin archivo')
                ->url(function ($record) {
                    if (!$record->carta_presentacion) return null;
                    return asset('storage/'.str_replace('storage/', '', $record->carta_presentacion));
                })
                ->openUrlInNewTab()
                ->icon('heroicon-o-document-text')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comprobante_pago')
                ->label('Comprobante de pago')
                ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Sin archivo')
                ->url(function ($record) {
                    if (!$record->comprobante_pago) return null;
                    return asset('storage/'.str_replace('storage/', '', $record->comprobante_pago));
                })
                ->openUrlInNewTab()
                ->icon('heroicon-o-document-text')
                    ->searchable(),

                Tables\Columns\TextColumn::make('estado')
                ->label('Estado')
                    ->searchable()
                    ->html()
                    ->formatStateUsing(function ($state) {
                        // Definir el color según el estado
                        if ($state == 'Pendiente') {
                            return "<span style='color:rgb(90, 66, 26); font-weight: bold;'>$state</span>"; 
                        } elseif ($state == 'Aceptado') {
                            return "<span style='color: #10b981; font-weight: bold;'>$state</span>"; 
                        } elseif ($state == 'Rechazado') {
                            return "<span style='color: #ef4444; font-weight: bold;'>$state</span>"; 
                        }
                        return $state;
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
                Action::make('Validar')
                    ->label('Validar')
                    ->icon('heroicon-o-check-circle')  
                    ->color('success')
                    ->modalHeading(' ')
                    ->modalHeading('VALIDACIÓN DE LA SOLICITUD')
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
                    
               
                ->visible(function () {
                    /** @var \App\Models\User $user */
                    $user = auth()->user();
                
                    return !$user?->hasRole('Estudiante');
                })
                ,

                Action::make('Asignar Jurado')
                ->label('Asignar Comisión')
                ->icon('heroicon-o-user-group')
                ->requiresConfirmation()
                ->color('primary')
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
                            'estado' => 'Comisión Asignada',
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
                            'estado' => 'Comisión Asignada',
                        ]);
                    }
            
                    // ✅ Notificación de éxito (opcional)
                    Notification::make()
                        ->title('Jurado asignado correctamente')
                        ->success()
                        ->send();
                })
                ->visible(function () {
                    $user = auth()->user();
                    /** @var User $user */
                    return !$user?->hasRole('Estudiante');
                }), 
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