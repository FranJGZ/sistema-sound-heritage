<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    // =========================================================================
    // 1. CONFIGURACIÓN DEL MENÚ Y MODELO
    // =========================================================================
    protected static ?string $model = Attendance::class;
    protected static ?string $navigationGroup = 'Eventos y Sala';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Inscripciones';
    protected static ?string $modelLabel = 'Inscripción';
    protected static ?string $pluralModelLabel = 'Inscripciones a Eventos';

    // Precarga el evento con su salón y el cliente asistente para evitar consultas N+1 en la tabla
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['event.venue', 'customer']);
    }

    // =========================================================================
    // 2. MÉTODOS AUXILIARES (ETIQUETAS, VALIDACIONES Y EXPORTACIÓN PDF)
    // =========================================================================

    // Construye la etiqueta del selector de eventos mostrando la fecha y los cupos libres en tiempo real
    private static function formatEventOptionLabel(Event $record): string
    {
        $occupiedSpots = $record->attendances_count ?? $record->attendances()->count();
        $availableSpots = max(0, $record->capacity - $occupiedSpots);
        $formattedDate = Carbon::parse($record->date)->format('d/m/Y');

        return "{$record->name} ({$formattedDate}) — Cupos restantes: {$availableSpots} de {$record->capacity}";
    }

    // Impide inscribir asistentes cuando el evento ya alcanzó su capacidad máxima habilitada
    private static function validateEventCapacity(?Attendance $record): Closure
    {
        return function (string $attribute, $value, Closure $fail) use ($record) {
            $event = Event::withCount('attendances')->find($value);
            if (! $event) {
                return;
            }

            $occupiedSpots = $event->attendances_count;
            if ($record && $record->event_id == $value) {
                $occupiedSpots--;
            }

            if ($occupiedSpots >= $event->capacity) {
                $fail("El evento seleccionado ya completó su capacidad máxima de {$event->capacity} asistentes.");
            }
        };
    }

    // Impide que un mismo cliente sea inscripto dos veces en el mismo evento cultural
    private static function validateUniqueAttendance(Get $get, ?Attendance $record): Closure
    {
        return function (string $attribute, $value, Closure $fail) use ($get, $record) {
            $eventId = $get('event_id');
            if (! $eventId || ! $value) {
                return;
            }

            $alreadyRegistered = Attendance::where('event_id', $eventId)
                ->where('customer_id', $value)
                ->when($record, fn (Builder $query) => $query->where('id', '!=', $record->id))
                ->exists();

            if ($alreadyRegistered) {
                $fail('Este cliente ya se encuentra inscripto en este evento.');
            }
        };
    }

    // Genera la lista de puerta en PDF respetando los filtros y el orden activos en pantalla
    private static function exportPdf($livewire)
    {
        $attendances = $livewire->getFilteredSortedTableQuery()
            ->with(['customer', 'event'])
            ->get();

        $rows = [];
        foreach ($attendances as $index => $attendance) {
            $rows[] = [
                '#' . ($index + 1),
                $attendance->customer
                    ? "{$attendance->customer->last_name}, {$attendance->customer->first_name}"
                    : '-',
                $attendance->customer?->document_number ?? '-',
                $attendance->customer?->phone ?? '-',
                $attendance->customer?->email ?? '-',
                $attendance->event?->name ?? '-',
                $attendance->event
                    ? Carbon::parse($attendance->event->date)->format('d/m/Y')
                    : '-',
            ];
        }

        $pdf = Pdf::loadView('reports.pdf-generic', [
            'title'             => 'Control de Asistencias y Lista de Entrada a Eventos',
            'filtros_aplicados' => collect($livewire->getTable()->getFilterIndicators())
                ->map(fn ($indicator) => strip_tags((string) $indicator->getLabel()))
                ->implode(' | ') ?: 'Ninguno (Todos los registros)',
            'summary' => [
                'Total Asistentes Listados' => $attendances->count(),
            ],
            'headers' => ['#', 'Cliente', 'DNI', 'Teléfono', 'Email', 'Evento Cultural', 'Fecha Evento'],
            'rows'    => $rows,
        ])->setPaper('a4', 'landscape');

        // Se usa streamDownload porque Livewire funciona por AJAX y no permite descargas directas tradicionales
        return response()->streamDownload(
            fn () => print($pdf->output()),
            'lista-asistentes-' . date('Ymd-His') . '.pdf'
        );
    }

    // =========================================================================
    // 3. FORMULARIO DE INSCRIPCIÓN (CREATE)
    // =========================================================================
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la Entrada / Inscripción')
                    ->description('Seleccioná el evento cultural y el cliente asistente para reservar su cupo.')
                    ->icon('heroicon-m-ticket')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label('Evento Cultural')
                            ->relationship(
                                'event',
                                'name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->withCount('attendances')
                                    ->orderBy('date', 'desc')
                            )
                            ->getOptionLabelFromRecordUsing(fn (Event $record): string => self::formatEventOptionLabel($record))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->rules([
                                fn (?Attendance $record): Closure => self::validateEventCapacity($record),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente Asistente')
                            ->relationship(
                                'customer',
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
                            )
                            ->rules([
                                fn (Get $get, ?Attendance $record): Closure => self::validateUniqueAttendance($get, $record),
                            ]),

                        Forms\Components\DatePicker::make('registration_date')
                            ->label('Fecha de Inscripción')
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    // =========================================================================
    // 4. TABLA PRINCIPAL DE INSCRIPCIONES
    // =========================================================================
    public static function table(Table $table): Table
    {
        return $table
            // -----------------------------------------------------------------
            // A) COLUMNAS VISIBLES EN EL LISTADO
            // -----------------------------------------------------------------
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('N°')
                    ->formatStateUsing(fn ($state) => '#' . str_pad($state, 4, '0', STR_PAD_LEFT))
                    ->sortable(),

                Tables\Columns\TextColumn::make('event.name')
                    ->label('Evento')
                    ->description(
                        fn (Attendance $record) => $record->event
                            ? 'Fecha: ' . Carbon::parse($record->event->date)->format('d/m/Y')
                            : ''
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.last_name')
                    ->label('Cliente Asistente')
                    ->formatStateUsing(
                        fn (Attendance $record) => $record->customer
                            ? "{$record->customer->last_name}, {$record->customer->first_name}"
                            : '-'
                    )
                    ->description(
                        fn (Attendance $record) => $record->customer
                            ? "DNI: {$record->customer->document_number}"
                            : ''
                    )
                    ->searchable(['first_name', 'last_name', 'document_number'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.phone')
                    ->label('Contacto')
                    ->description(fn (Attendance $record) => $record->customer?->email ?: 'Sin email'),

                Tables\Columns\TextColumn::make('registration_date')
                    ->label('Fecha Inscripto')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')

            // -----------------------------------------------------------------
            // B) FILTROS DE BÚSQUEDA
            // -----------------------------------------------------------------
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Filtrar por Evento')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('registration_date')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Inscripto Desde'),

                        Forms\Components\DatePicker::make('hasta')
                            ->label('Inscripto Hasta'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data) {
                        if ($data['desde']) {
                            $query->whereDate('registration_date', '>=', $data['desde']);
                        }
                        if ($data['hasta']) {
                            $query->whereDate('registration_date', '<=', $data['hasta']);
                        }
                    }),
            ])
            ->filtersFormColumns(2)
            ->filtersFormWidth(MaxWidth::Large)

            // -----------------------------------------------------------------
            // C) EXPORTACIÓN A PDF DE LA LISTA DE PUERTA
            // -----------------------------------------------------------------
            ->headerActions([
                Tables\Actions\Action::make('exportar_pdf')
                    ->label('Exportar Lista de Puerta (PDF)')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('primary')
                    ->action(fn ($livewire) => self::exportPdf($livewire)),
            ])

            // -----------------------------------------------------------------
            // D) ACCIONES POR FILA
            // -----------------------------------------------------------------
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Cancelar Inscripción')
                    ->modalHeading('Cancelar Inscripción de Asistente')
                    ->modalDescription('¿Confirmás cancelar la asistencia de esta persona? Se liberará el cupo en el evento.')
                    ->successNotificationTitle('Inscripción cancelada con éxito.'),
            ]);
    }

    // =========================================================================
    // 5. RUTAS DE LAS PÁGINAS DEL RECURSO
    // =========================================================================
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
        ];
    }
}
