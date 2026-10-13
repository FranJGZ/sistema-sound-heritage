<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EventResource\Pages;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    // =========================================================================
    // 1. CONFIGURACIÓN DEL MENÚ Y MODELO
    // Se ubica primero (orden 1) en 'Eventos y Sala' por ser la actividad cultural propia del local.
    // =========================================================================
    protected static ?string $model = Event::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Eventos y Sala';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Evento Propio';
    protected static ?string $pluralModelLabel = 'Eventos Propios';

    // Se precarga withCount('attendances') para calcular el cupo ocupado en la tabla sin ejecutar consultas adicionales por fila.
    // Además se desactiva SoftDeletingScope para conservar en el historial los eventos que fueron cancelados.
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['venue', 'eventSponsors.sponsor'])
            ->withCount('attendances')
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    // =========================================================================
    // 2. MÉTODOS AUXILIARES (OPCIONES, VALIDACIÓN, INSCRIPCIÓN Y PDF)
    // =========================================================================

    // Centraliza los tipos de eventos culturales para reutilizarlos en el formulario y en los filtros de la tabla
    public static function getEventTypeOptions(): array
    {
        return [
            'Recital'            => 'Recital / Concierto en Vivo',
            'Taller/Clínica'     => 'Taller / Clínica de Instrumentos',
            'Presentación'       => 'Presentación de Disco / Gear',
            'Encuentro Cultural' => 'Encuentro Cultural',
            'Feria'              => 'Feria de Audio / Luthiers',
        ];
    }

    // Valida que el salón no tenga otro evento propio ni una reserva externa confirmada en la misma fecha
    private static function validateVenueAvailability(Get $get, ?Model $record): Closure
    {
        return function (string $attribute, $value, Closure $fail) use ($get, $record) {
            $venueId = $get('venue_id') ?: 1;

            $eventQuery = Event::where('venue_id', $venueId)
                ->whereDate('date', $value);

            // Excluye el ID del evento actual al editar para permitir cambiar su nombre o cupo sin que rebote contra sí mismo
            if ($record) {
                $eventQuery->where('id', '!=', $record->id);
            }

            if ($eventQuery->exists()) {
                $fail('El salón ya tiene otro evento programado en esa misma fecha.');
                return;
            }

            $hasConfirmedReservation = Reservation::where('venue_id', $venueId)
                ->whereDate('date', $value)
                ->where('status', 'Confirmada')
                ->exists();

            if ($hasConfirmedReservation) {
                $fail('El salón ya está reservado por un cliente externo (Reserva Confirmada) en esa fecha.');
            }
        };
    }

    // Procesa la inscripción rápida de un asistente desde la fila del evento verificando cupo y duplicados
    private static function registerAttendee(Event $record, array $data): void
    {
        $occupiedSpots = $record->attendances()->count();

        if ($occupiedSpots >= $record->capacity) {
            Notification::make()
                ->title('Capacidad Completa')
                ->body("El evento ya completó su cupo máximo de {$record->capacity} asistentes.")
                ->danger()
                ->send();
            return;
        }

        $alreadyRegistered = Attendance::where('event_id', $record->id)
            ->where('customer_id', $data['customer_id'])
            ->exists();

        if ($alreadyRegistered) {
            Notification::make()
                ->title('Cliente Ya Inscripto')
                ->body('Este cliente ya se encuentra inscripto en este evento.')
                ->warning()
                ->send();
            return;
        }

        Attendance::create([
            'event_id'          => $record->id,
            'customer_id'       => $data['customer_id'],
            'registration_date' => $data['registration_date'],
        ]);

        Notification::make()
            ->title('Inscripción Realizada')
            ->body('El asistente fue registrado exitosamente al evento.')
            ->success()
            ->send();
    }

    // Genera el reporte PDF de eventos culturales respetando los filtros y el orden activos en pantalla
    private static function exportPdf($livewire)
    {
        $events = $livewire->getFilteredSortedTableQuery()
            ->with(['venue', 'eventSponsors.sponsor'])
            ->withCount('attendances')
            ->get();

        $rows = [];
        foreach ($events as $event) {
            $sponsors = $event->eventSponsors
                ->map(fn ($eventSponsor) => $eventSponsor->sponsor?->name)
                ->filter()
                ->implode(', ') ?: 'Sin sponsors';

            $occupancyPercentage = $event->capacity > 0
                ? round(($event->attendances_count / $event->capacity) * 100)
                : 0;

            $rows[] = [
                $event->id,
                $event->name,
                $event->type,
                $event->date,
                "{$event->attendances_count} / {$event->capacity} ({$occupancyPercentage}%)",
                $sponsors,
                $event->trashed() ? 'Cancelado' : 'Activo',
            ];
        }

        $pdf = Pdf::loadView('reports.pdf-generic', [
            'title'             => 'Reporte de Eventos Culturales y Ocupación',
            'filtros_aplicados' => collect($livewire->getTable()->getFilterIndicators())
                ->map(fn ($indicator) => strip_tags((string) $indicator->getLabel()))
                ->implode(' | ') ?: 'Ninguno (Todos los registros)',
            'summary' => [
                'Eventos Listados'   => $events->count(),
                'Total Inscriptos'   => $events->sum('attendances_count'),
                'Capacidad Ofrecida' => $events->sum('capacity') . ' cupos',
                'Eventos Activos'    => $events->whereNull('deleted_at')->count(),
            ],
            'headers' => ['ID', 'Evento', 'Tipo', 'Fecha', 'Ocupación', 'Sponsors', 'Estado'],
            'rows'    => $rows,
        ])->setPaper('a4', 'landscape');

        // Se usa streamDownload porque Livewire funciona por AJAX y no permite descargas directas tradicionales
        return response()->streamDownload(
            fn () => print($pdf->output()),
            'reporte-eventos-' . date('Ymd-His') . '.pdf'
        );
    }

    // =========================================================================
    // 3. FORMULARIO DE ORGANIZACIÓN DEL EVENTO (CREATE / EDIT)
    // =========================================================================
    public static function form(Form $form): Form
    {
        return $form->schema([
            // -----------------------------------------------------------------
            // SECCIÓN 1: DATOS PRINCIPALES Y VALIDACIÓN DE SALÓN
            // -----------------------------------------------------------------
            Forms\Components\Section::make('Información del Evento')
                ->description('Organización de clínicas, recitales y talleres en el Salón de Sound Heritage.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del Evento')
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('type')
                        ->label('Tipo de Evento')
                        ->options(self::getEventTypeOptions())
                        ->required(),

                    // Se fija por defecto el salón principal (ID 1) porque el local cuenta con una única sala para eventos
                    Forms\Components\Select::make('venue_id')
                        ->label('Salón / Espacio')
                        ->relationship('venue', 'name')
                        ->default(1)
                        ->required()
                        ->selectablePlaceholder(false),

                    Forms\Components\DatePicker::make('date')
                        ->label('Fecha del Evento')
                        ->required()
                        ->rules([
                            fn (Get $get, ?Model $record): Closure => self::validateVenueAvailability($get, $record),
                        ]),

                    // Se limita el tope máximo a 100 personas por la capacidad física habilitada del salón
                    Forms\Components\TextInput::make('capacity')
                        ->label('Capacidad (Máx. 100 personas)')
                        ->numeric()
                        ->default(100)
                        ->minValue(1)
                        ->maxValue(100)
                        ->live(onBlur: true)
                        ->required(),
                ])->columns(2),

            // -----------------------------------------------------------------
            // SECCIÓN 2: AUSPICIANTES (TABLA INTERMEDIA EVENT_SPONSORS)
            // -----------------------------------------------------------------
            Forms\Components\Section::make('Auspiciantes del Evento (EventSponsors)')
                ->description('Marcas o patrocinadores que auspician este evento.')
                ->schema([
                    // Usa relationship() para guardar automáticamente cada asignación en la tabla intermedia 'event_sponsors'
                    Forms\Components\Repeater::make('eventSponsors')
                        ->label('Sponsors Asignados')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('sponsor_id')
                                ->label('Marca / Auspiciante')
                                ->relationship('sponsor', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                // Impide cargar dos veces la misma marca auspiciante dentro del mismo evento
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Agregar Auspiciante')
                        ->grid(2),
                ])->collapsible(),
        ]);
    }

    // =========================================================================
    // 4. TABLA PRINCIPAL DE EVENTOS CULTURALES
    // =========================================================================
    public static function table(Table $table): Table
    {
        return $table
            // -----------------------------------------------------------------
            // A) COLUMNAS VISIBLES EN EL LISTADO
            // -----------------------------------------------------------------
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                // Alerta en amarillo al superar el 70% de ocupación del salón y en rojo cuando se agotan las localidades
                Tables\Columns\TextColumn::make('ocupacion')
                    ->label('Inscriptos / Cupo')
                    ->state(fn (Event $record): string => "{$record->attendances_count} / {$record->capacity}")
                    ->badge()
                    ->color(function (Event $record): string {
                        if ($record->attendances_count >= $record->capacity) {
                            return 'danger';
                        }
                        if ($record->attendances_count >= ($record->capacity * 0.7)) {
                            return 'warning';
                        }
                        return 'success';
                    }),

                Tables\Columns\TextColumn::make('eventSponsors.sponsor.name')
                    ->label('Sponsors')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Sin sponsors'),

                // Evalúa trashed() (deleted_at) para distinguir eventos vigentes de los que fueron cancelados
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->state(fn (Event $record): string => $record->trashed() ? 'Cancelado' : 'Activo')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Activo' ? 'success' : 'danger'),
            ])
            ->defaultSort('date', 'desc')

            // -----------------------------------------------------------------
            // B) FILTROS DE AUDITORÍA Y BÚSQUEDA (100% ELOQUENT ORM)
            // Estos mismos filtros afectan tanto a la tabla en pantalla como al exportar el PDF.
            // -----------------------------------------------------------------
            ->filters([
                // Usa has() y doesntHave() de Eloquent sobre la relación 'attendances' para filtrar por cantidad de inscriptos sin SQL crudo
                Tables\Filters\SelectFilter::make('nivel_ocupacion')
                    ->label('Nivel de Convocatoria')
                    ->options([
                        'alta'           => 'Alta Convocatoria (5 o más inscriptos)',
                        'con_inscriptos' => 'Con Inscriptos (1 o más)',
                        'baja'           => 'Baja Convocatoria (1 a 4 inscriptos)',
                        'sin_inscriptos' => 'Sin Inscriptos aún (0)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $selectedLevel = $data['value'] ?? null;

                        if ($selectedLevel === 'alta') {
                            $query->has('attendances', '>=', 5);
                        } elseif ($selectedLevel === 'con_inscriptos') {
                            $query->has('attendances');
                        } elseif ($selectedLevel === 'baja') {
                            $query->has('attendances', '>=', 1)->has('attendances', '<', 5);
                        } elseif ($selectedLevel === 'sin_inscriptos') {
                            $query->doesntHave('attendances');
                        }
                    }),

                // Por defecto muestra solo eventos activos para no mezclar eventos cancelados en la agenda diaria
                Tables\Filters\TrashedFilter::make()
                    ->label('Estado del Evento')
                    ->placeholder('Solo Eventos Activos')
                    ->trueLabel('Todos (Activos + Cancelados)')
                    ->falseLabel('Solo Eventos Cancelados'),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo de Evento')
                    ->options(self::getEventTypeOptions()),

                // Usa relación anidada con punto para filtrar eventos según la tabla intermedia event_sponsors
                Tables\Filters\SelectFilter::make('sponsor')
                    ->label('Auspiciante / Sponsor')
                    ->relationship('eventSponsors.sponsor', 'name')
                    ->searchable()
                    ->preload(),

                // Compara contra la fecha actual para separar la cartelera futura del archivo de eventos ya realizados
                Tables\Filters\SelectFilter::make('vigencia')
                    ->label('Vigencia Temporal')
                    ->options([
                        'proximos' => 'Próximos Eventos (Futuros)',
                        'pasados'  => 'Eventos Finalizados (Historial)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (($data['value'] ?? null) === 'proximos') {
                            $query->whereDate('date', '>=', now()->toDateString());
                        } elseif (($data['value'] ?? null) === 'pasados') {
                            $query->whereDate('date', '<', now()->toDateString());
                        }
                    }),

                // Se usa Filter personalizado porque el rango requiere comparar '>=' y '<=' sobre la misma columna date
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Fecha Desde'),

                        Forms\Components\DatePicker::make('hasta')
                            ->label('Fecha Hasta'),
                    ])
                    ->columns(2)
                    ->columnSpan(2)
                    ->query(function (Builder $query, array $data) {
                        if ($data['desde']) {
                            $query->whereDate('date', '>=', $data['desde']);
                        }
                        if ($data['hasta']) {
                            $query->whereDate('date', '<=', $data['hasta']);
                        }
                    }),
            ])
            ->filtersFormColumns(3)
            ->filtersFormWidth(MaxWidth::FourExtraLarge)

            // -----------------------------------------------------------------
            // C) EXPORTACIÓN A PDF DEL LISTADO FILTRADO
            // -----------------------------------------------------------------
            ->headerActions([
                Tables\Actions\Action::make('exportar_pdf')
                    ->label('Exportar PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('primary')
                    ->action(fn ($livewire) => self::exportPdf($livewire)),
            ])

            // -----------------------------------------------------------------
            // D) ACCIONES POR CADA FILA (INSCRIBIR, EDITAR, CANCELAR Y REACTIVAR)
            // -----------------------------------------------------------------
            ->actions([
                Tables\Actions\Action::make('inscribir')
                    ->label('Inscribir')
                    ->icon('heroicon-o-ticket')
                    ->color('success')
                    ->hidden(fn (Event $record) => $record->trashed())
                    ->modalHeading(fn (Event $record) => "Inscribir Asistente — {$record->name}")
                    ->modalWidth('2xl')
                    ->form([
                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente Asistente')
                            ->relationship(
                                'attendances.customer',
                                'last_name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->where('document_number', '!=', '0')
                                    ->orderBy('last_name')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn ($record) => "{$record->last_name}, {$record->first_name} (DNI: {$record->document_number})"
                            )
                            ->searchable(['first_name', 'last_name', 'document_number'])
                            ->preload()
                            ->required()
                            ->createOptionForm(CustomerResource::getQuickCreateFormSchema())
                            ->createOptionAction(
                                fn (Forms\Components\Actions\Action $action) => $action
                                    ->modalHeading('Registrar Nuevo Cliente')
                                    ->modalWidth('3xl')
                            ),

                        Forms\Components\DatePicker::make('registration_date')
                            ->label('Fecha de Inscripción')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(fn (Event $record, array $data) => self::registerAttendee($record, $data)),

                Tables\Actions\EditAction::make()
                    ->hidden(fn (Event $record) => $record->trashed()),

                // Se usa baja lógica como cancelación para no perder el registro histórico de los inscriptos ni los sponsors asociados
                Tables\Actions\DeleteAction::make()
                    ->label('Cancelar Evento')
                    ->modalHeading('Cancelar Evento Propio')
                    ->modalDescription('El evento quedará marcado como cancelado pero se conservará en el historial.')
                    ->hidden(fn (Event $record) => $record->trashed()),

                Tables\Actions\RestoreAction::make()
                    ->label('Reactivar')
                    ->visible(fn (Event $record) => $record->trashed()),
            ]);
    }

    // =========================================================================
    // 5. RUTAS DE LAS PÁGINAS DEL RECURSO
    // =========================================================================
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit'   => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}