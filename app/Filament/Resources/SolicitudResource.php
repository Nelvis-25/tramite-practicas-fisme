<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudResource\Pages;
use App\Filament\Resources\SolicitudResource\RelationManagers;
use App\Models\Requisito;
use App\Models\Solicitud;
use App\Models\Validacion;
use Filament\Tables\Actions\Action;
use Filament\Forms;
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
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lineaInvestigacion.nombre')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('asesor.nombre')
                    ->label('Asesor')
                    ->sortable()
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
                    ->html()
                    ->formatStateUsing(function ($state) {
                        // Definir el color según el estado
                        if ($state == 'Pendiente') {
                            return "<span style='color: green;'>" . $state . "</span>";
                        } elseif ($state == 'Validado') {
                            return "<span style='color: prymary;'>" . $state . "</span>";
                        } elseif ($state == 'Rechazado') {
                            return "<span style='color: red;'>" . $state . "</span>";
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
                Tables\Actions\DeleteAction::make(),
                Action::make('Validar')
                ->label('Validar')
                ->icon('heroicon-o-document-check')  // Añade un ícono
                ->color('primary')  // Color verde para mejor UI
                ->action(function (Solicitud $record) {
                    // Verifica si ya existen validaciones (más eficiente)
                    if ($record->validaciones()->doesntExist()) {
                        $requisitos = Requisito::all();
                        
                        // Usa createMany para mejor performance
                        $record->validaciones()->createMany(
                            $requisitos->map(function ($requisito) {
                                return [
                                    'requisito_id' => $requisito->id,
                                    'entregado' => false
                                ];
                            })->toArray()
                        );
                    }
            
                    // Redirección con notificación
                    Notification::make()
                        ->title('Validación iniciada')
                        ->body('Se crearon ' . Requisito::count() . ' requisitos para validar')
                        ->success()
                        ->send();
            
                    return redirect(ValidacionResource::getUrl('index', [
                        'solicitud_id' => $record->id
                    ]));
                })
                ->requiresConfirmation()
                ->modalHeading('Validar Solicitud')
                ->modalDescription('¿Crear registros de validación para los ' . Requisito::count() . ' requisitos?'),
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
