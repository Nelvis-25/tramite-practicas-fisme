<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudInformeResource\Pages;
use App\Filament\Resources\SolicitudInformeResource\RelationManagers;
use App\Models\Docente;
use App\Models\EvaluacionDeInforme;
use App\Models\InformeDePractica;
use App\Models\InformePractica;
use App\Models\JuradoDeInforme;
use App\Models\Practica;
use App\Models\SolicitudInforme;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Console\View\Components\Info;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class SolicitudInformeResource extends Resource
{
    protected static ?string $model = SolicitudInforme::class;
    protected static ?string $navigationGroup = 'Informe de practicas';
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
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('estudiante_id')
                    ->label('Nombre del estudiante')
                    ->relationship('estudiante', 'nombre')
                    ->searchable()
                    ->required()
                   ,
                   Forms\Components\Select::make('practica_id')
                   ->label('Título de práctica')
                   ->options(function () {
                       return \App\Models\Practica::with('solicitude') // Cargamos solicitud
                           ->get()
                           ->mapWithKeys(function ($practica) {
                               return [$practica->id => optional($practica->solicitude)->nombre];
                           });
                   })
                   ->searchable()
                   ->required()
    ,

                Forms\Components\FileUpload::make('informe')
                ->label('Informe de práctica :pdf')
                ->directory('informes')
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(100240)
                ->fetchFileInformation(true)
                ->downloadable() 
                ->openable() 
                ->previewable(true) 
                ,
                    
                Forms\Components\FileUpload::make('solicitud')
                ->label('Solicitud al Decano :pdf')
                ->directory('Solicitudes')
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(100240)
                ->fetchFileInformation(true)
                ->downloadable() 
                ->openable() 
                ->previewable(true) 
                ,
                
                Forms\Components\Select::make('estado')
                ->label('Estado')
                ->options([
                    'Pendiente' => 'Pendiente',
                    'Validado' => 'Validado',
                    'Aceptado' => 'Aceptado',
                    'Rechazado' => 'Rechazado',
                    'Jurado asignado' => 'Jurado asignado',
                ])
                ->default('Pendiente')
                ->required()
                //->disabled(true)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('estudiante.nombre')
                ->label('Nombre del práctica')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('practica.solicitude.nombre')
                    ->label('Título de práctica')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->extraAttributes([
                        'style' => 'width: 380px; word-wrap: break-word; white-space: normal;text-align: justify;',
                    ])
                    ,
                Tables\Columns\TextColumn::make('informe')
                ->label('Informe de práctica')
                ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Sin archivo')
                ->url(fn ($record) => $record->informe ? asset('storage/'.str_replace('storage/', '', $record->informe)) : null)
                ->openUrlInNewTab()
                ->icon('heroicon-o-document-text')
                ->searchable(),
                Tables\Columns\TextColumn::make('solicitud')
                ->label('Solicitud al Decano')
                ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Sin archivo')
                ->url(fn ($record) => $record->solicitud ? asset('storage/'.str_replace('storage/', '', $record->solicitud)) : null)
                ->openUrlInNewTab()
                ->icon('heroicon-o-document-text')
                ->searchable(),
                Tables\Columns\TextColumn::make('practica.solicitude.fecha_fin')
                    ->label('Fecha en que finalizó ')
                    ->numeric()
                    ->sortable()
                    ->searchable()
            
                    ,
                    Tables\Columns\TextColumn::make('estado')
                    ->label('Evaluación')
                    ->searchable()
                    ->color(fn (string $state): string => match ($state) {
                        'Aceptado' => 'success',
                        'Rechazado' => 'danger',
                        default => '',
                    })
                    
                    ->sortable(),
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
                Tables\Actions\Action::make('Validar')
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

                        Forms\Components\Radio::make('decision')
                            ->label('Seleccione una opción:')
                            ->options([
                                'aceptado' => 'Aceptar Solicitud',
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
                    ->action(function (SolicitudInforme $record, array $data) {
                        $decision = $data['decision'];

                        if ($decision === 'aceptado') {
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
                            $record->observaciones()->create([
                                'observacion' => $data['motivo_rechazo'],
                                'user_id' => auth()->id(),
                            ]);

                            Notification::make()
                                ->title('Solicitud Rechazada')
                                ->body('Se registró el motivo del rechazo.')
                                ->danger()
                                ->send();
                        }
                    })
                    
                    ->modalSubmitActionLabel('Guardar')
                    ->modalCancelActionLabel('Cancelar'),

                Tables\Actions\Action::make('asignar_jurados')
                    ->label('Asignar Jurados')
                    ->icon('heroicon-o-user-group')
                    ->form([
                        Forms\Components\Repeater::make('jurados')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('docente_id')
                                            ->label('Docente')
                                            ->options(Docente::query()->pluck('nombre', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->columnSpan(1),
                                            
                                        Forms\Components\Select::make('cargo')
                                            ->label('Cargo')
                                            ->options([
                                                'Presidente' => 'Presidente',
                                                'Secretario' => 'Secretario',
                                                'Vocal' => 'Vocal',
                                                'Accesitario' => 'Accesitario'
                                            ])
                                            ->required()
                                            ->columnSpan(1)
                                    ])
                            ])
                            ->defaultItems(4)
                            ->minItems(4)
                            ->maxItems(4)
                            ->columns(1)
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => isset($state['docente_id']) ? Docente::find($state['docente_id'])?->nombre : null)
                    ])
                        ->action(function (SolicitudInforme $record, array $data) {
                            // Validar estado
                            if ($record->estado !== 'Aceptado') {
                                if ($record->estado === 'Jurado asignado') {
                                    Notification::make()
                                        ->title('Jurado ya asignado')
                                        ->body('Ya se ha asignado un jurado a esta solicitud.')
                                        ->warning()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Acción no permitida')
                                        ->body('Solo se pueden asignar jurados a solicitudes aceptadas.')
                                        ->danger()
                                        ->send();
                                }

                                return; // Esto ahora sí detiene todo
                            }

                            // Validar si ya tiene un informe de práctica asignado
                            if ($record->informeDePractica()->exists()) {
                                Notification::make()
                                    ->title('Solicitud ya procesada')
                                    ->body('Esta solicitud ya tiene un informe de práctica asignado, por lo tanto, ya se asignaron jurados.')
                                    ->warning()
                                    ->send();

                                return; // También detiene todo
                            }

                            // Solo si pasa todas las validaciones, se ejecuta la transacción
                            DB::transaction(function () use ($record, $data) {
                                // Crear informe
                                $informe = InformeDePractica::create([
                                    'solicitud_informe_id' => $record->id,
                                    'estado' => 'Pendiente',
                                    'fecha_resolucion' => now()
                                ]);

                                // Asignar jurados
                                foreach ($data['jurados'] as $jurado) {
                                     $juradoCreado = JuradoDeInforme::create([
                                        'informe_de_practica_id' => $informe->id,
                                        'docente_id' => $jurado['docente_id'],
                                        'cargo' => $jurado['cargo']
                                    ]);

                                    EvaluacionDeInforme::create([
                                    'informe_de_practica_id' => $informe->id,
                                    'jurado_de_informe_id' => $juradoCreado->id,
                                    'estado' => 'Pendiente',
                                    'observacion' => null,
                                    'activo' => false,
]);
                                }

                                // Actualizar estado
                                $record->update(['estado' => 'Jurado asignado']);
                            });

                            // Ahora sí: solo se muestra si TODO fue exitoso
                            Notification::make()
                                ->title('Jurados asignados correctamente')
                                ->success()
                                ->send();
                        })

                      ->modalWidth('4xl'),
                Tables\Actions\Action::make('notas')
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
                                return $record->observaciones()
                                    ->orderBy('created_at', 'desc')
                                    ->get()
                                    ->map(function ($observacion) {
                                        return [
                                            'mensaje' => $observacion->observacion,  // Asegúrate que este sea el campo correcto
                                            'created_at' => $observacion->created_at,
                                        ];
                                    })
                                    ->toArray();
                            })
                        
                ])
                    ->modalCancelActionLabel('Cerrar')
                   ->modalSubmitActionLabel('Salir')
                ,
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSolicitudInformes::route('/'),
            'create' => Pages\CreateSolicitudInforme::route('/create'),
            'edit' => Pages\EditSolicitudInforme::route('/{record}/edit'),
        ];
    }
}
