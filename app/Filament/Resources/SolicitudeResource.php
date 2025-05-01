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
                    ->required()
                    ->maxLength(700),
                Forms\Components\Select::make('estudiante_id')
                    ->relationship('estudiante', 'nombre')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('empresa_id')
                    ->label('Empresa')
                    ->relationship('empresa', 'nombre')
                    ->nullable()
                    ->searchable(),
                Forms\Components\Select::make('linea_investigacion_id')
                    ->relationship('lineaInvestigacion', 'nombre')
                    ->required(),
                Forms\Components\Select::make('asesor_id')
                    ->relationship('asesor', 'nombre')
                    ->required()
                    ->searchable(),
                Forms\Components\DatePicker::make('fecha_inicio'),
                Forms\Components\DatePicker::make('fecha_fin'),
                Forms\Components\FileUpload::make('solicitud')
                ->label('Solicitud dirigida al Decano')
                ->directory('solicitudes')
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(20240)
                ->fetchFileInformation(true)
                ->downloadable() 
                ->openable() 
                ->previewable(true) 
                ->maxSize(20240),
                Forms\Components\FileUpload::make('constancia')
                ->label('Constancia de cursos aprobados')
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
                ->label('Plan de Prácticas ')
                ->directory('informes')
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(20240)
                ->fetchFileInformation(true)
                ->downloadable() 
                ->openable() 
                ->previewable(true) 
                ->maxSize(20240),
                Forms\Components\FileUpload::make('carta_presentacion')
                ->label('Carta de autorización emitida por la Empresa. ')
                ->directory('cartas_presentacion') 
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(20240)
                ->fetchFileInformation(true)
                ->downloadable() 
                ->openable() 
                ->previewable(true) 
                ->maxSize(20240),
                Forms\Components\FileUpload::make('comprobante_pago')
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
                            return "<span style='color: #f59e0b; font-weight: bold;'>$state</span>"; 
                        } elseif ($state == 'Validado') {
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
                ->form([
                Forms\Components\Placeholder::make('lista_requisitos')
                    ->label('Requisitos registrados')
                    ->content(function () {
                        return Requisito::all()
                            ->pluck('nombre')
                            ->map(fn($item) => '• ' . trim($item)) // eliminamos espacios extra
                            ->implode("\n");
                    })
                    ->extraAttributes([
                        'style' => 'white-space: pre-wrap; line-height: 1.5; font-family: sans-serif;',
                    ]),

                    Forms\Components\Radio::make('decision')
                        ->label('Seleccione una opción:')
                        ->options([
                            'validado' => ' Aceptar Solicitud',
                            'rechazado' => ' Rechazar Solicitud',
                        ])
                        ->required()
                        ->inline(false) 
                        ->extraAttributes([
                            'style' => 'display: flex; flex-direction: column; align-items: center; gap: 1rem;',
                        ]),
                ])
                ->action(function (Solicitude $record, array $data) {
                    $decision = $data['decision'];
                
                    if ($decision === 'validado') {
                        $record->update([
                            'estado' => 'Validado',
                        ]);
                
                        Notification::make()
                            ->title(' Solicitud Aceptada')
                            ->success()
                            ->send();
                    } elseif ($decision === 'rechazado') {
                        $record->update([
                            'estado' => 'Rechazado',
                        ]);
                
                        Notification::make()
                            ->title('Solicitud Rechazada')
                            ->danger()
                            ->send();
                    }
                })
                ->modalHeading('Confirmar decisión')
                ->modalDescription(function (array $data) {
                    return match ($data['decision'] ?? null) {
                        'validado' => '¿Estás seguro de que deseas  **aceptar** esta solicitud?',
                        'rechazado' => '¿Estás seguro de que deseas  **rechazar** esta solicitud?',
                        default => 'Confirma tu decisión antes de continuar.',
                    };
                })
                ->modalSubmitActionLabel('Sí, confirmar')
                ->modalCancelActionLabel('Cancelar')
                ->requiresConfirmation()
                ->visible(function () {
                    /** @var \App\Models\User $user */
                    $user = auth()->user();
                
                    return !$user?->hasRole('Estudiante');
                })
                ,
                Action::make('notas')
                ->label('Observacion')
                ->icon('heroicon-o-chat-bubble-left')
                
                ->modalWidth('xl')
                ->modalHeading(fn ($record) => "Notas de Solicitud #{$record->id}")
                ->form([
                    Forms\Components\Repeater::make('notas_existentes')
                        ->label('Historial de Notas')
                        ->schema([
                            Forms\Components\Textarea::make('mensaje')
                                ->disabled()
                                ->columnSpanFull()
                                ->extraAttributes(['class' => 'bg-black-500'])
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
                        ->default(fn ($record) => $record->observacions()
                            ->orderBy('created_at', 'desc')
                            ->get()
                            ->toArray()
                        ),
                        
                    Forms\Components\Textarea::make('nueva_nota')
                        ->label('Nueva Nota')
                        ->placeholder('Escribe aquí tu nota...')
                        ->required()
                        ->minLength(5)
                        ->maxLength(500)
                        ->columnSpanFull()
                ])
                ->action(function (Solicitude $record, array $data) {
                    $record->observacions()->create([
                        'mensaje' => $data['nueva_nota'],
                        'user_id' => auth()->id()
                    ]);
                    
                    Notification::make()
                        ->title('Nota agregada correctamente')
                        ->body('La nota ha sido registrada en el sistema.')
                        ->success()
                        ->send();
                })
                ->modalSubmitActionLabel('Guardar Nota')
                ->modalCancelActionLabel('Cerrar'),

                Action::make('Asignar Jurado')
                ->label('Asignar Jurado')
                ->icon('heroicon-o-user-group')
                ->requiresConfirmation()
                ->color('primary')
                ->action(function ($record) {
            
                    // ✅ Validar que la solicitud esté en estado 'Validado'
                    if ($record->estado !== 'Validado') {
                        Notification::make()
                            ->title('No se puede asignar jurado')
                            ->body('No se pudo asignar una comisión debido a que esta solicitud aún no ha sido revisada o validada.')
                            ->danger()
                            ->send();
            
                        return; // Importante: salir de la acción
                    }
            
                    $existePlan = PlanPractica::where('solicitude_id', $record->id)->exists();
            
                    if ($existePlan) {
                        $record->update([
                            'estado' => 'Asignado',
                        ]);
                    } else {
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
                            'estado' => 'Asignado',
                        ]);
            
                        $record->update([
                            'estado' => 'Asignado',
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