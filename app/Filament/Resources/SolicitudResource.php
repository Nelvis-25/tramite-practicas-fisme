<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudResource\Pages;
use App\Filament\Resources\SolicitudResource\RelationManagers;
use App\Models\Requisito;
use App\Models\Solicitud;
use App\Models\Validacion;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class SolicitudResource extends Resource
{
    protected static ?string $model = Solicitud::class;
    protected static ?string $navigationGroup = 'Plan de Prácticas';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(700),
                Forms\Components\Select::make('estudiante_id')
                    ->relationship('estudiante', 'nombre')
                    ->required(),

                Forms\Components\Select::make('linea_investigacion_id')
                    ->relationship('lineaInvestigacion', 'nombre')
                    ->required(),
                    Forms\Components\Select::make('asesor_id')
                    ->relationship('asesor', 'nombre')
                    ->required()
                    ->searchable(),
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('estudiante.nombre')
                    
                    ->searchable(),
                Tables\Columns\TextColumn::make('lineaInvestigacion.nombre')
                    
                    ->searchable(),
                Tables\Columns\TextColumn::make('asesor.nombre')
                    ->label('Asesor')
                    
                    ->searchable(),
                    
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
                            return "<span style='color: #f59e0b; font-weight: bold;'>$state</span>"; // naranja
                        } elseif ($state == 'Validado') {
                            return "<span style='color: #10b981; font-weight: bold;'>$state</span>"; // verde esmeralda
                        } elseif ($state == 'Rechazado') {
                            return "<span style='color: #ef4444; font-weight: bold;'>$state</span>"; // rojo fuerte
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
                ->icon('heroicon-o-check-circle')  // O puedes elegir uno de los anteriores
                ->color('success')
                ->form([
                    Forms\Components\Radio::make('decision')
                        ->label('Seleccione una opción:')
                        ->options([
                            'validado' => '✅ Aceptar Solicitud',
                            'rechazado' => '❌ Rechazar Solicitud',
                        ])
                        ->required()
                        ->inline(false) // muestra en vertical
                        ->extraAttributes([
                            'style' => 'display: flex; flex-direction: column; align-items: center; gap: 1rem;',
                        ]),
                ])
                ->action(function (Solicitud $record, array $data) {
                    $decision = $data['decision'];
                
                    if ($decision === 'validado') {
                        $record->update([
                            'estado' => 'Validado',
                        ]);
                
                        Notification::make()
                            ->title('✅ Solicitud Aceptada')
                            ->success()
                            ->send();
                    } elseif ($decision === 'rechazado') {
                        $record->update([
                            'estado' => 'Rechazado',
                        ]);
                
                        Notification::make()
                            ->title('❌ Solicitud Rechazada')
                            ->danger()
                            ->send();
                    }
                })
                ->modalHeading('Confirmar decisión')
                ->modalDescription(function (array $data) {
                    return match ($data['decision'] ?? null) {
                        'validado' => '¿Estás seguro de que deseas ✅ **aceptar** esta solicitud?',
                        'rechazado' => '¿Estás seguro de que deseas ❌ **rechazar** esta solicitud?',
                        default => 'Confirma tu decisión antes de continuar.',
                    };
                })
                ->modalSubmitActionLabel('Sí, confirmar')
                ->modalCancelActionLabel('Cancelar')
                ->requiresConfirmation(),
                
                Action::make('notas')
                ->label('Notas')
                ->icon('heroicon-o-chat-bubble-left')
                ->color('blue')
                ->button()
                ->modalWidth('xl')
                ->modalHeading(fn ($record) => "Notas de Solicitud #{$record->id}")
                ->form([
                    Forms\Components\Repeater::make('notas_existentes')
                        ->label('Historial de Notas')
                        ->schema([
                            Forms\Components\Textarea::make('mensaje')
                                ->disabled()
                                ->columnSpanFull()
                                ->extraAttributes(['class' => 'bg-gray-50'])
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
                ->action(function (Solicitud $record, array $data) {
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
            'index' => Pages\ListSolicituds::route('/'),
            'create' => Pages\CreateSolicitud::route('/create'),
            'edit' => Pages\EditSolicitud::route('/{record}/edit'),
        ];
    }
}
