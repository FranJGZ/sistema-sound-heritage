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
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
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
        $availableSpots = $record->available_spots;
        $formattedDate = Carbon::parse($record->date)->format('d/m/Y');
        $priceText = $record->price > 0 ? '$' . number_format($record->price, 2, ',', '.') : 'Gratis';

        return "{$record->name} ({$formattedDate}) — Entrada: {$priceText} — Cupos restantes: {$availableSpots} de {$record->capacity}";
    }

    // Valida que el aforo habilitado soporte la cantidad de entradas solicitadas
    private static function validateEventCapacity(Get $get, ?Attendance $record): Closure
    {
        return function (string $attribute, $value, Closure $fail) use ($get, $record) {
            $event = Event::find($value);
            if (! $event) {
                return;
            }

            $requestedQty = max(1, (int) ($get('quantity') ?: 1));
            $occupiedSpots = (int) $event->attendances()
                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                ->sum('quantity');

            if (($occupiedSpots + $requestedQty) > $event->capacity) {
                $disponibles = max(0, $event->capacity - $occupiedSpots);
                $fail("No hay cupos suficientes. Solicitaste {$requestedQty} entradas pero solo quedan {$disponibles} disponibles de {$event->capacity}.");
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
                $attendance->quantity ?? 1,
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
                'Total Registros' => $attendances->count(),
                'Total Entradas / Asistentes' => $attendances->sum('quantity'),
            ],
            'headers' => ['#', 'Cliente Titular', 'DNI', 'Teléfono', 'Email', 'Evento Cultural', 'Pases', 'Fecha Evento'],
            'rows'    => $rows,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'lista-asistentes-' . date('Ymd-His') . '.pdf'
        );
    }

    public static function downloadTicket(Attendance $record)
    {
        $record->loadMissing(['event.venue', 'customer']);

        $pdf = Pdf::loadView('reports.event-ticket-pdf', compact('record'))
            ->setPaper([0, 0, 226.77, 280], 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'ticket-entrada-' . $record->id . '.pdf'
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
                    ->description('Seleccioná el evento cultural, el cliente asistente y la cantidad de entradas.')
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
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state) {
                                    $event = Event::find($state);
                                    $unitPrice = $event ? (float) $event->price : 0;
                                    $qty = max(1, (int) ($get('quantity') ?: 1));
                                    $set('price', $unitPrice * $qty);
                                } else {
                                    $set('price', 0);
                                }
                            })
                            ->rules([
                                fn (Get $get, ?Attendance $record): Closure => self::validateEventCapacity($get, $record),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente Titular / Asistente')
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
                            ),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad de Entradas / Pases')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(50)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $eventId = $get('event_id');
                                if ($eventId) {
                                    $event = Event::find($eventId);
                                    $unitPrice = $event ? (float) $event->price : 0;
                                    $qty = max(1, (int) ($state ?: 1));
                                    $set('price', $unitPrice * $qty);
                                }
                            }),

                        Forms\Components\TextInput::make('price')
                            ->label('Total a Cobrar ($)')
                            ->prefix('$')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('payment_method')
                            ->label('Medio de Pago')
                            ->options([
                                'Mercado Pago' => 'Mercado Pago (Cartel QR en Caja 1)',
                                'Efectivo'     => 'Efectivo en Mostrador',
                            ])
                            ->default('Mercado Pago')
                            ->required(),

                        Forms\Components\DatePicker::make('registration_date')
                            ->label('Fecha de Inscripción')
                            ->default(now())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    // =========================================================================
    // 4. VISTA DE DETALLE EN MODAL (INFOLIST)
    // =========================================================================
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detalle del Comprobante de Entrada')
                    ->icon('heroicon-m-ticket')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('N° de Entrada')
                            ->formatStateUsing(fn ($state) => '#' . str_pad($state, 4, '0', STR_PAD_LEFT)),

                        Infolists\Components\TextEntry::make('registration_date')
                            ->label('Fecha de Emisión')
                            ->date('d/m/Y'),

                        Infolists\Components\TextEntry::make('customer.last_name')
                            ->label('Cliente Titular')
                            ->formatStateUsing(fn (Attendance $record) => $record->customer ? "{$record->customer->last_name}, {$record->customer->first_name} (DNI: {$record->customer->document_number})" : '-'),

                        Infolists\Components\TextEntry::make('customer.phone')
                            ->label('Contacto')
                            ->formatStateUsing(fn (Attendance $record) => ($record->customer?->phone ?: 'Sin teléfono') . ' · ' . ($record->customer?->email ?: 'Sin email')),

                        Infolists\Components\TextEntry::make('event.name')
                            ->label('Evento Cultural')
                            ->description(fn (Attendance $record) => $record->event ? 'Fecha: ' . Carbon::parse($record->event->date)->format('d/m/Y') . ' · Salón: ' . ($record->event->venue?->name ?? 'Auditorio Central') : ''),

                        Infolists\Components\TextEntry::make('quantity')
                            ->label('Cantidad de Entradas / Pases')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn ($state) => "{$state} " . ($state == 1 ? 'entrada' : 'entradas')),

                        Infolists\Components\TextEntry::make('price')
                            ->label('Total Abonado')
                            ->money('ARS'),

                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Medio de Pago'),

                        Infolists\Components\TextEntry::make('payment_status')
                            ->label('Estado de Pago')
                            ->badge()
                            ->color(fn (Attendance $record): string => match ($record->payment_status) {
                                'completed' => 'success',
                                'pending'   => 'warning',
                                'refunded'  => 'danger',
                                default     => 'gray',
                            })
                            ->formatStateUsing(fn (Attendance $record): string => match ($record->payment_status) {
                                'completed' => 'Abonado',
                                'pending'   => 'Esperando QR',
                                'refunded'  => 'Reembolsado' . ($record->mp_refund_id ? " (#{$record->mp_refund_id})" : ''),
                                default     => $record->payment_status ?? 'Sin pago',
                            }),

                        Infolists\Components\TextEntry::make('mp_payment_id')
                            ->label('ID Pago Mercado Pago')
                            ->placeholder('No aplica / Efectivo')
                            ->fontFamily('mono')
                            ->visible(fn (Attendance $record) => !empty($record->mp_payment_id)),

                        Infolists\Components\TextEntry::make('refunded_at')
                            ->label('Fecha de Reembolso')
                            ->dateTime('d/m/Y H:i')
                            ->visible(fn (Attendance $record) => !empty($record->refunded_at)),
                    ])->columns(2),
            ]);
    }

    // =========================================================================
    // 5. TABLA PRINCIPAL DE INSCRIPCIONES
    // =========================================================================
    public static function table(Table $table): Table
    {
        return $table
            ->recordAction(Tables\Actions\ViewAction::class)
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

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Pases')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Total')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Medio')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (Attendance $record): string => match ($record->payment_status) {
                        'completed' => 'success',
                        'pending'   => 'warning',
                        'refunded'  => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (Attendance $record): string => match ($record->payment_status) {
                        'completed' => 'Abonado',
                        'pending'   => 'Esperando QR',
                        'refunded'  => 'Reembolsado' . ($record->mp_refund_id ? " (#{$record->mp_refund_id})" : ''),
                        default     => $record->payment_status ?? 'Sin pago',
                    }),

                Tables\Columns\TextColumn::make('registration_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('3s')

            // -----------------------------------------------------------------
            // B) FILTROS DE BÚSQUEDA
            // -----------------------------------------------------------------
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Estado de Asistencia'),

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
                // Acción para cobrar en efectivo si el cliente desiste del QR
                Tables\Actions\Action::make('cobrar_efectivo_asistencia')
                    ->label('Cobrar Efectivo')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Attendance $record) => $record->payment_status === 'pending' && (float) $record->price > 0 && !$record->trashed())
                    ->requiresConfirmation()
                    ->modalHeading('Cobrar Entrada en Efectivo')
                    ->modalDescription(fn (Attendance $record) => "El cliente abonará $ " . number_format($record->price, 2, ',', '.') . " en efectivo en mostrador. ¿Confirmar cobro y envío de entradas?")
                    ->action(function (Attendance $record) {
                        try {
                            $mp = app(\App\Services\MercadoPagoService::class);
                            if ($record->mp_order_id) {
                                $mp->cancelQrOrder($record->mp_order_id);
                            }
                            $mp->cancelAnyActiveCajaOrder('CAJA1');
                            $record->update([
                                'payment_status' => 'completed',
                                'payment_method' => 'Efectivo en Mostrador',
                                'mp_order_id'    => null,
                            ]);

                            $customer = $record->customer;
                            if ($customer && !empty($customer->email) && filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
                                \Illuminate\Support\Facades\Mail::to($customer->email)->send(
                                    new \App\Mail\EventTicketsMail(
                                        customer: $customer,
                                        attendances: collect([$record])
                                    )
                                );
                            }

                            Notification::make()
                                ->title('Entrada cobrada con éxito')
                                ->body('Se registró el cobro en efectivo y se enviaron los pases en PDF al correo del cliente.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error registrando cobro')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // Acción para abrir el cartel QR si vino redirigido
                Tables\Actions\Action::make('ver_qr_cartel')
                    ->label('Cobrar QR')
                    ->icon('heroicon-m-qr-code')
                    ->color('warning')
                    ->visible(fn (Attendance $record) => $record->payment_status === 'pending' && (float) $record->price > 0 && !$record->trashed())
                    ->modalHeading(fn (Attendance $record) => "Cobrar Entrada — {$record->event?->name} (#{$record->id})")
                    ->modalSubheading('Caja 1 · Sound Heritage Central')
                    ->modalWidth(MaxWidth::Medium)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(fn (Attendance $record) => view('filament.modals.qr-caja1', ['record' => $record]))
                    ->action(function (Attendance $record) {
                        try {
                            $mp = app(\App\Services\MercadoPagoService::class);
                            $mp->cancelAnyActiveCajaOrder('CAJA1');
                            $qty = $record->quantity ?: 1;
                            $order = $mp->createQrOrder(
                                amount: (float) $record->price,
                                description: "Entradas ({$qty}x): {$record->event?->name}",
                                externalReference: "ATT-{$record->id}",
                                posId: 'CAJA1'
                            );
                            if ($order && isset($order['id'])) {
                                $record->update(['mp_order_id' => $order['id']]);
                            }
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error("Error enviando orden QR para entrada #{$record->id}: " . $e->getMessage());
                        }
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Ver Detalle')
                        ->modalHeading(fn (Attendance $record) => "Detalle de Entrada #" . str_pad($record->id, 4, '0', STR_PAD_LEFT))
                        ->modalWidth(MaxWidth::ThreeExtraLarge)
                        ->extraModalFooterActions([
                            Tables\Actions\Action::make('modal_imprimir_ticket')
                                ->label('Imprimir Entrada (PDF)')
                                ->icon('heroicon-m-ticket')
                                ->color('success')
                                ->action(fn (Attendance $record) => self::downloadTicket($record)),

                            Tables\Actions\DeleteAction::make('modal_cancelar')
                                ->label('Cancelar y Reembolsar')
                                ->modalHeading('Cancelar Asistencia y Devolver Dinero')
                                ->modalDescription('¿Confirmás cancelar la asistencia de esta persona? Si fue abonada con Mercado Pago, se reembolsará automáticamente el dinero a la cuenta del comprador y se liberará el cupo en el evento.')
                                ->modalSubmitActionLabel('Sí, Cancelar y Reembolsar')
                                ->successNotificationTitle('Inscripción cancelada y reembolso procesado exitosamente.')
                                ->hidden(fn (Attendance $record) => $record->trashed()),
                        ]),

                    Tables\Actions\Action::make('imprimir_ticket')
                        ->label('Imprimir Entrada')
                        ->icon('heroicon-m-ticket')
                        ->color('success')
                        ->action(fn (Attendance $record) => self::downloadTicket($record)),

                    Tables\Actions\DeleteAction::make()
                        ->label('Cancelar y Reembolsar')
                        ->modalHeading('Cancelar Asistencia y Devolver Dinero')
                        ->modalDescription('¿Confirmás cancelar la asistencia de esta persona? Si fue abonada con Mercado Pago, se reembolsará automáticamente el dinero a la cuenta del comprador y se liberará el cupo en el evento.')
                        ->modalSubmitActionLabel('Sí, Cancelar y Reembolsar')
                        ->successNotificationTitle('Inscripción cancelada y reembolso procesado exitosamente.')
                        ->hidden(fn (Attendance $record) => $record->trashed()),

                    Tables\Actions\RestoreAction::make()
                        ->label('Reactivar Asistencia')
                        ->visible(fn (Attendance $record) => $record->trashed()),
                ]),
            ]);
    }

    // =========================================================================
    // 6. RUTAS DE LAS PÁGINAS DEL RECURSO
    // =========================================================================
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
        ];
    }
}
