<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudInformeResource\Pages;
use App\Filament\Resources\SolicitudInformeResource\RelationManagers;
use App\Models\Practica;
use App\Models\SolicitudInforme;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                ->disabled(true)
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
                Tables\Columns\TextColumn::make('estado'),
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
                ->icon('heroicon-o-document-check')
                ->color('primary')
                ->modalSubmitActionLabel('Confirmar')
                ->modalWidth('2xl')
                ->form([
                    Forms\Components\Placeholder::make('')
                        ->content('📝 Solicitud de Informe:')
                        ->extraAttributes([
                            'class' => 'text-center text-xl font-bold mb-2',
                        ])
                        ->columnSpanFull(),
                    
                    Forms\Components\Group::make([
                        Forms\Components\Radio::make('')
                           
                            ->options([
                                'Aceptado' => 'Aceptado', 
                                'Rechazado' => 'Rechazado',
                            ])
                            ->required()
                            ->columnSpanFull()
                            ->extraAttributes([
                                'class' => 'flex justify-center gap-4 border rounded-lg p-4 mt-2',
                            ]),
                    ])
                    ->columnSpanFull(),
                ])
                ->action(function ($record, $data) {
                    
                    $record->update([
                        'estado' => $data['estado'],
                    ]);
            
                    // Notificación de éxito usando Notification facade
                    Notification::make()
                        ->title('Estado actualizado a: ' . $data['estado'])
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
