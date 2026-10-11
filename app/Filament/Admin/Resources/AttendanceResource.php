<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['event.venue', 'customer']);
    }

    // =========================================================================
    // 2. FORMULARIO DE INSCRIPCIÓN (MODAL O PÁGINA)
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
                                modifyQueryUsing: fn (Builder $query) => $query->withCount('attendances')->orderBy('date', 'desc')
                            )
                            ->getOptionLabelFromRecordUsing(function (Event $record): string {
                                $ocupados = $record->attendances_count ?? $record->attendances()->count();
                                $disponibles = max(0, $record->capacity - $ocupados);
                                $fecha = \Carbon\Carbon::parse($record->date)->format('d/m/Y');
                                return "{$record->name} ({$fecha}) — Cupos restantes: {$disponibles} de {$record->capacity}";
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->rules([
                                fn (Get $get, ?Attendance $record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                                    $event = Event::withCount('attendances')->find($value);
                                    if ($event) {
                                        $ocupados = $event->attendances_count;
                                        if ($record && $record->event_id == $value) {
                                            $ocupados--;
                                        }
                                        if ($ocupados >= $event->capacity) {
                                            $fail("El evento seleccionado ya completó su capacidad máxima de {$event->capacity} asistentes.");
                                        }
                                    }
                                },
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente Asistente')
                            ->relationship(
                                'customer',
                                'last_name',
                                modifyQueryUsing: fn (Builder $query) => $query->where('document_number', '!=', '0')->orderBy('last_name')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->last_name}, {$record->first_name} (DNI: {$record->document_number})")
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
                                fn (Get $get, ?Attendance $record) => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                    $eventId = $get('event_id');
                                    if ($eventId && $value) {
                                        $exists = Attendance::where('event_id', $eventId)
                                            ->where('customer_id', $value)
                                            ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                            ->exists();
                                        if ($exists) {
                                            $fail('Este cliente ya se encuentra inscripto en este evento.');
                                        }
                                    }
                                },
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
    // 3. TABLA DE INSCRIPCIONES
    // =========================================================================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('N°')
                    ->formatStateUsing(fn ($state) => '#' . str_pad($state, 4, '0', STR_PAD_LEFT))
                    ->sortable(),

                Tables\Columns\TextColumn::make('event.name')
                    ->label('Evento')
                    ->description(fn (Attendance $record) => $record->event ? 'Fecha: ' . \Carbon\Carbon::parse($record->event->date)->format('d/m/Y') : '')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.last_name')
                    ->label('Cliente Asistente')
                    ->formatStateUsing(fn (Attendance $record) => $record->customer ? "{$record->customer->last_name}, {$record->customer->first_name}" : '-')
                    ->description(fn (Attendance $record) => $record->customer ? "DNI: {$record->customer->document_number}" : '')
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
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Filtrar por Evento')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('registration_date')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Inscripto Desde'),
                        Forms\Components\DatePicker::make('hasta')->label('Inscripto Hasta'),
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
            ->headerActions([
                Tables\Actions\Action::make('exportar_pdf')
                    ->label('Exportar Lista de Puerta (PDF)')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('primary')
                    ->action(function ($livewire) {
                        $attendances = $livewire->getFilteredSortedTableQuery()
                            ->with(['customer', 'event'])
                            ->get();

                        $rows = [];
                        foreach ($attendances as $index => $item) {
                            $rows[] = [
                                '#' . ($index + 1),
                                $item->customer ? "{$item->customer->last_name}, {$item->customer->first_name}" : '-',
                                $item->customer?->document_number ?? '-',
                                $item->customer?->phone ?? '-',
                                $item->customer?->email ?? '-',
                                $item->event?->name ?? '-',
                                $item->event ? \Carbon\Carbon::parse($item->event->date)->format('d/m/Y') : '-',
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

                        return response()->streamDownload(fn () => print($pdf->output()), 'lista-asistentes-' . date('Ymd-His') . '.pdf');
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Cancelar Inscripción')
                    ->modalHeading('Cancelar Inscripción de Asistente')
                    ->modalDescription('¿Confirmás cancelar la asistencia de esta persona? Se liberará el cupo en el evento.')
                    ->successNotificationTitle('Inscripción cancelada con éxito.'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
        ];
    }
}

