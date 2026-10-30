<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReservationResource\Pages;
use App\Models\Event;
use App\Models\Reservation;
use App\Services\MercadoPagoService;
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

class ReservationResource extends Resource
{
    // =========================================================================
    // 1. CONFIGURACIÓN DEL MENÚ Y MODELO
    // Se agrupa en 'Eventos y Sala' con orden 2 para quedar debajo de Eventos Propios.
    // =========================================================================
    protected static ?string $model = Reservation::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Eventos y Sala';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Reserva de Sala';
    protected static ?string $pluralModelLabel = 'Reservas de Sala';

    // Precarga el cliente y el salón para evitar consultas N+1 al mostrar cada fila de la tabla
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'venue']);
    }

    // =========================================================================
    // 2. MÉTODOS AUXILIARES (OPCIONES, VALIDACIÓN DE SALÓN Y EXPORTACIÓN PDF)
    // =========================================================================

    // Centraliza los motivos de alquiler de sala para reutilizarlos en el formulario y en los filtros de la tabla
    public static function getReservationTypeOptions(): array
    {
        return [
            'Ensayo de Banda'          => 'Ensayo de Banda',
            'Ensayo General'           => 'Ensayo General',
            'Grabación Acústica'       => 'Grabación Acústica',
            'Lanzamiento de Videoclip' => 'Lanzamiento de Videoclip',
            'Masterclass Externa'      => 'Masterclass Externa',
            'Evento Privado'           => 'Evento Privado',
        ];
    }

    // Centraliza los estados posibles de una solicitud de reserva de sala
    public static function getReservationStatusOptions(): array
    {
        return [
            'Pendiente'  => 'Pendiente de Aprobación',
            'Confirmada' => 'Confirmada',
            'Cancelada'  => 'Cancelada',
        ];
    }

    // Estados seleccionables en el formulario (no tiene sentido crear una reserva ya Cancelada)
    public static function getFormReservationStatusOptions(): array
    {
        return [
            'Pendiente'  => 'Pendiente de Aprobación',
            'Confirmada' => 'Confirmada',
        ];
    }

    // Tarifas base oficiales del auditorio según motivo de uso
    public static function getTarifasBase(): array
    {
        return [
            'Ensayo de Banda'          => 35000.0,
            'Ensayo General'           => 50000.0,
            'Grabación Acústica'       => 70000.0,
            'Lanzamiento de Videoclip' => 85000.0,
            'Masterclass Externa'      => 60000.0,
            'Evento Privado'           => 110000.0,
        ];
    }

    // Cálculo automático de costo total con descuentos grupales y seña fija del 30%
    public static function calculateReservationAmounts(Forms\Get $get, Forms\Set $set): void
    {
        $type = $get('type') ?: 'Ensayo de Banda';
        $attendees = max(1, (int) ($get('attendees_count') ?: 1));
        $tarifas = self::getTarifasBase();
        $basePrice = (float) ($tarifas[$type] ?? 35000.0);

        $discountPercent = 0;
        if ($attendees >= 20) {
            $discountPercent = 25;
        } elseif ($attendees >= 15) {
            $discountPercent = 20;
        } elseif ($attendees >= 10) {
            $discountPercent = 15;
        } elseif ($attendees >= 5) {
            $discountPercent = 10;
        }

        $discountAmount = $basePrice * ($discountPercent / 100);
        $totalPrice = max(0, $basePrice - $discountAmount);
        $depositAmount = round($totalPrice * 0.30, 2);

        $set('price', number_format($totalPrice, 2, '.', ''));
        $set('deposit_amount', number_format($depositAmount, 2, '.', ''));
    }

    // Valida que la fecha solicitada no choque con un evento propio del local ni con otra reserva activa en el mismo turno
    private static function validateVenueAvailability(Get $get, ?Model $record): Closure
    {
        return function (string $attribute, $value, Closure $fail) use ($get, $record) {
            $date = ($attribute === 'date') ? $value : $get('date');
            $shift = ($attribute === 'shift') ? $value : $get('shift');
            $venueId = $get('venue_id') ?: 1;

            if (empty($date)) {
                return;
            }

            $hasOwnEvent = Event::where('venue_id', $venueId)
                ->whereDate('date', $date)
                ->exists();

            if ($hasOwnEvent) {
                $fail('El salón ya está ocupado por un Evento cultural de Sound Heritage en esa fecha.');
                return;
            }

            if (!empty($shift)) {
                $reservationQuery = Reservation::where('venue_id', $venueId)
                    ->whereDate('date', $date)
                    ->where('shift', $shift)
                    ->where('status', '!=', 'Cancelada');

                // Al editar una reserva se excluye su propio ID para no detectar falso choque consigo misma
                if ($record) {
                    $reservationQuery->where('id', '!=', $record->id);
                }

                if ($reservationQuery->exists()) {
                    $fail("El salón ya cuenta con una reserva activa o pendiente para esa fecha durante el turno {$shift}.");
                }
            }
        };
    }

    // Genera el reporte PDF de reservas de sala respetando los filtros y el orden activos en pantalla
    private static function exportPdf($livewire)
    {
        $reservations = $livewire->getFilteredSortedTableQuery()
            ->with(['customer', 'venue'])
            ->get();

        $rows = [];
        foreach ($reservations as $reservation) {
            $rows[] = [
                $reservation->date,
                $reservation->customer
                    ? "{$reservation->customer->last_name}, {$reservation->customer->first_name}"
                    : '-',
                $reservation->customer?->phone ?? '-',
                $reservation->type,
                $reservation->venue?->name ?? 'Salón Principal',
                $reservation->status,
            ];
        }

        $pdf = Pdf::loadView('reports.pdf-generic', [
            'title'             => 'Reporte de Reservas de Sala (Alquiler a Terceros)',
            'filtros_aplicados' => collect($livewire->getTable()->getFilterIndicators())
                ->map(fn ($indicator) => strip_tags((string) $indicator->getLabel()))
                ->implode(' | ') ?: 'Ninguno (Todos los registros)',
            'summary' => [
                'Total Solicitudes' => $reservations->count(),
                'Confirmadas'       => $reservations->where('status', 'Confirmada')->count(),
                'Pendientes'        => $reservations->where('status', 'Pendiente')->count(),
                'Canceladas'        => $reservations->where('status', 'Cancelada')->count(),
            ],
            'headers' => ['Fecha Reservada', 'Cliente', 'Teléfono', 'Motivo / Uso', 'Salón', 'Estado'],
            'rows'    => $rows,
        ])->setPaper('a4', 'landscape');

        // Se usa streamDownload porque Livewire funciona por AJAX y no permite descargas directas tradicionales
        return response()->streamDownload(
            fn () => print($pdf->output()),
            'reporte-reservas-' . date('Ymd-His') . '.pdf'
        );
    }

    // =========================================================================
    // 3. FORMULARIO DE SOLICITUD DE RESERVA (CREATE / EDIT)
    // =========================================================================
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Solicitud de Reserva del Salón (Terceros)')
                ->description('Gestión de alquileres del salón para ensayos de bandas, grabaciones o eventos privados.')
                ->schema([
                    Forms\Components\Select::make('customer_id')
                        ->label('Cliente / Banda Solicitante')
                        ->relationship(
                            'customer',
                            'last_name',
                            modifyQueryUsing: fn (Builder $query) => $query
                                ->where('document_number', '!=', '0')
                                ->orderBy('last_name')
                        )
                        // Muestra apellido, nombre y DNI juntos para evitar confusiones entre clientes con el mismo apellido
                        ->getOptionLabelFromRecordUsing(
                            fn ($record) => "{$record->last_name}, {$record->first_name} (DNI: {$record->document_number})"
                        )
                        ->searchable(['first_name', 'last_name', 'document_number'])
                        ->preload()
                        ->required(),

                    // Se fija por defecto el salón principal (ID 1) y se quita la opción vacía porque el local posee una única sala
                    Forms\Components\Select::make('venue_id')
                        ->label('Espacio / Salón')
                        ->relationship('venue', 'name')
                        ->default(1)
                        ->required()
                        ->selectablePlaceholder(false),

                    Forms\Components\Select::make('type')
                        ->label('Motivo / Uso del Salón')
                        ->options(self::getReservationTypeOptions())
                        ->default('Ensayo de Banda')
                        ->live()
                        ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateReservationAmounts($get, $set))
                        ->required(),

                    Forms\Components\TextInput::make('attendees_count')
                        ->label('Cantidad de Personas')
                        ->numeric()
                        ->default(4)
                        ->minValue(1)
                        ->maxValue(150)
                        ->suffix('personas')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateReservationAmounts($get, $set))
                        ->required(),

                    Forms\Components\DatePicker::make('date')
                        ->label('Fecha Solicitada')
                        ->required()
                        ->minDate(now()->startOfDay())
                        ->live()
                        ->validationMessages([
                            'after_or_equal' => 'No se permite seleccionar una fecha anterior a la actual.',
                        ])
                        ->rules([
                            fn (Get $get, ?Model $record): Closure => self::validateVenueAvailability($get, $record),
                        ]),

                    Forms\Components\Select::make('shift')
                        ->label('Turno Solicitado')
                        ->options([
                            'Mañana (10:00 a 14:00 hs)' => 'Mañana (10:00 a 14:00 hs)',
                            'Tarde (15:00 a 19:00 hs)'  => 'Tarde (15:00 a 19:00 hs)',
                            'Noche (20:00 a 24:00 hs)'  => 'Noche (20:00 a 24:00 hs)',
                        ])
                        ->default('Tarde (15:00 a 19:00 hs)')
                        ->live()
                        ->rules([
                            fn (Get $get, ?Model $record): Closure => self::validateVenueAvailability($get, $record),
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('price')
                        ->label('Costo Total del Alquiler ($)')
                        ->numeric()
                        ->prefix('$')
                        ->default(35000)
                        ->disabled()
                        ->dehydrated()
                        ->helperText(function (Forms\Get $get) {
                            $type = $get('type') ?: 'Ensayo de Banda';
                            $attendees = (int) ($get('attendees_count') ?: 1);
                            $base = self::getTarifasBase()[$type] ?? 35000;
                            $desc = 0;
                            if ($attendees >= 20) $desc = 25;
                            elseif ($attendees >= 15) $desc = 20;
                            elseif ($attendees >= 10) $desc = 15;
                            elseif ($attendees >= 5) $desc = 10;
                            return "Tarifa base: $" . number_format($base, 2, ',', '.') . ($desc > 0 ? " — Descuento grupal ({$attendees} pers.): {$desc}% OFF" : " — Sin descuento (<5 pers.)");
                        })
                        ->required(),

                    Forms\Components\TextInput::make('deposit_amount')
                        ->label('Monto de Seña (30% Obligatorio)')
                        ->numeric()
                        ->prefix('$')
                        ->default(10500)
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Calculado automáticamente como el 30% del costo total.')
                        ->required(),

                    Forms\Components\Select::make('payment_method')
                        ->label('Medio de Pago')
                        ->options([
                            'Mercado Pago'           => 'Mercado Pago (Cartel QR en Caja 1)',
                            'Efectivo en Mostrador'  => 'Efectivo en Mostrador',
                            'Transferencia Bancaria' => 'Transferencia Bancaria',
                        ])
                        ->default('Mercado Pago')
                        ->live()
                        ->required(),

                    Forms\Components\Radio::make('payment_mode')
                        ->label('Modalidad de Cobro')
                        ->options([
                            'seña'  => 'Abonar Seña (30%)',
                            'total' => 'Abonar Total (100%)',
                        ])
                        ->default('seña'),

                    // Nace como 'Pendiente' (o 'Confirmada') sin permitir crearla 'Cancelada'
                    Forms\Components\Select::make('status')
                        ->label('Estado de la Solicitud')
                        ->options(self::getFormReservationStatusOptions())
                        ->default('Pendiente')
                        ->required(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notas / Requerimientos Técnicos')
                        ->columnSpanFull()
                        ->rows(2)
                        ->nullable(),
                ])->columns(2),
        ]);
    }

    // =========================================================================
    // 4. TABLA PRINCIPAL DE RESERVAS DE SALA
    // =========================================================================
    public static function table(Table $table): Table
    {
        return $table
            // -----------------------------------------------------------------
            // A) COLUMNAS VISIBLES EN EL LISTADO
            // -----------------------------------------------------------------
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha Reservada')
                    ->date('d/m/Y')
                    ->sortable(),

                // Incluye el teléfono como subtítulo para poder contactar rápido al solicitante sin tener que abrir el formulario
                Tables\Columns\TextColumn::make('customer.last_name')
                    ->label('Cliente Solicitante')
                    ->formatStateUsing(fn ($record) => "{$record->customer->last_name}, {$record->customer->first_name}")
                    ->description(fn ($record) => "Tel: {$record->customer->phone}")
                    ->searchable(['first_name', 'last_name', 'document_number'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Uso / Motivo')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('attendees_count')
                    ->label('Asistentes')
                    ->suffix(' pers.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('shift')
                    ->label('Turno')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('venue.name')
                    ->label('Salón')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio Alquiler')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('deposit_amount')
                    ->label('Seña')
                    ->money('ARS')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Cobro')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'completed', 'Totalmente Abonada', 'Total Cancelado' => 'success',
                        'Señada (Resta 70%)', 'Seña Acreditada' => 'info',
                        'refunded', 'Reembolsada' => 'danger',
                        'pending', 'Esperando QR', 'Pendiente de Seña' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state, Reservation $record): string => match ($state) {
                        'completed', 'Total Cancelado' => 'Totalmente Abonada',
                        'Seña Acreditada' => 'Señada (Resta 70%)',
                        'refunded' => 'Reembolsada' . ($record->mp_refund_id ? " (#{$record->mp_refund_id})" : ''),
                        'pending' => 'Esperando QR',
                        default => $state ?: 'Pendiente',
                    }),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Medio de Pago')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Confirmada' => 'success',
                        'Pendiente'  => 'warning',
                        'Cancelada'  => 'danger',
                        default      => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Solicitud')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('3s')

            // -----------------------------------------------------------------
            // B) FILTROS DE AUDITORÍA Y BÚSQUEDA
            // Estos mismos filtros afectan tanto a la tabla en pantalla como al exportar el PDF.
            // -----------------------------------------------------------------
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(self::getReservationStatusOptions()),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Motivo / Uso')
                    ->options(self::getReservationTypeOptions()),

                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Cliente Solicitante')
                    ->relationship('customer', 'last_name')
                    ->searchable()
                    ->preload(),

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
            ->filtersFormColumns(4)
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

            ->recordUrl(fn (Reservation $record): string => static::getUrl('edit', ['record' => $record]))
            // -----------------------------------------------------------------
            // D) ACCIONES POR CADA FILA (APROBAR, CANCELAR Y EDITAR)
            // -----------------------------------------------------------------
            ->actions([
                Tables\Actions\Action::make('ver_qr_cartel')
                    ->label('Cartel QR')
                    ->icon('heroicon-m-qr-code')
                    ->color('info')
                    ->visible(fn (Reservation $record) => $record->payment_status === 'pending' || !empty($record->mp_order_id))
                    ->modalHeading(fn (Reservation $record) => "Cobro QR Mostrador · Caja 1 (Reserva #{$record->id})")
                    ->modalDescription(function (Reservation $record) {
                        $monto = $record->deposit_amount > 0 ? $record->deposit_amount : $record->price;
                        return "Monto a cobrar: $ " . number_format((float) $monto, 2, ',', '.') . " ARS (" . ($record->deposit_amount > 0 ? 'Seña 30%' : 'Pago Total') . ")";
                    })
                    ->modalContent(fn (Reservation $record) => view('filament.modals.qr-caja1', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar Ventana'),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('cobrar_sena_qr')
                        ->label('Cobrar QR en Caja 1')
                        ->icon('heroicon-m-qr-code')
                        ->color('warning')
                        ->visible(fn (Reservation $record): bool => $record->status === 'Pendiente' && empty($record->mp_order_id))
                        ->requiresConfirmation()
                        ->modalHeading('Cobro de Seña / Reserva con Código QR (Caja 1)')
                        ->modalDescription(function (Reservation $record) {
                            $monto = $record->deposit_amount > 0 ? $record->deposit_amount : round($record->price * 0.3, 2);
                            return "Se enviará la orden de cobro por $ " . number_format($monto, 2, ',', '.') . " a la Caja 1. El cliente deberá escanear el cartel QR para abonar.";
                        })
                        ->modalSubmitActionLabel('Enviar a Caja 1')
                        ->action(function (Reservation $record, $livewire) {
                            $monto = $record->deposit_amount > 0 ? (float) $record->deposit_amount : round((float)$record->price * 0.3, 2);
                            $mp = app(MercadoPagoService::class);
                            $mp->cancelAnyActiveCajaOrder('CAJA1');
                            $order = $mp->createQrOrder(
                                amount: $monto,
                                description: "Reserva #{$record->id} - {$record->type}",
                                externalReference: "RES-{$record->id}-SENA",
                                posId: 'CAJA1'
                            );

                            if ($order && isset($order['id'])) {
                                $record->update([
                                    'mp_order_id'    => $order['id'],
                                    'payment_status' => 'pending',
                                ]);

                                Notification::make()
                                    ->title('Cobro enviado a Caja 1')
                                    ->body("Cobro por $ " . number_format($monto, 2, ',', '.') . " activo en la Caja 1.")
                                    ->success()
                                    ->send();

                                $livewire->defaultTableAction = 'ver_qr_cartel';
                                $livewire->defaultTableActionRecord = (string) $record->id;
                            } else {
                                Notification::make()
                                    ->title('Error al conectar con Mercado Pago')
                                    ->body('No se pudo enviar la orden a la Caja 1.')
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('liberar_caja_qr')
                        ->label('Liberar Caja 1')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->visible(fn (Reservation $record): bool => ! empty($record->mp_order_id) && $record->status === 'Pendiente')
                        ->requiresConfirmation()
                        ->modalHeading('Cancelar cobro en Caja 1')
                        ->modalDescription('¿Deseás cancelar la orden activa en la Caja 1 para liberar el punto de venta?')
                        ->action(function (Reservation $record) {
                            $mp = app(MercadoPagoService::class);
                            $mp->cancelQrOrder($record->mp_order_id);
                            $record->update([
                                'mp_order_id'    => null,
                                'payment_status' => 'Efectivo en Mostrador',
                            ]);

                            Notification::make()
                                ->title('Caja 1 liberada')
                                ->warning()
                                ->send();
                        }),

                    Tables\Actions\Action::make('confirmar')
                        ->label('Aprobar Reserva')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Reservation $record) => $record->status === 'Pendiente')
                        ->requiresConfirmation()
                        ->modalHeading('Aprobar Reserva de Salón')
                        ->modalDescription('¿Confirmás que el salón está disponible y el cliente abonó la seña?')
                        ->action(function (Reservation $record) {
                            $record->update(['status' => 'Confirmada']);

                            Notification::make()
                                ->title('Reserva confirmada con éxito')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('cancelar')
                        ->label('Cancelar y Devolver Seña')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Reservation $record) => $record->status !== 'Cancelada')
                        ->requiresConfirmation()
                        ->modalHeading('Cancelar Reserva y Devolver Seña')
                        ->modalDescription('¿Confirmás cancelar esta reserva? Si registra cobro por Mercado Pago, se reembolsará automáticamente el dinero a la cuenta del comprador.')
                        ->modalSubmitActionLabel('Sí, Cancelar y Devolver Dinero')
                        ->action(function (Reservation $record) {
                            $record->update(['status' => 'Cancelada']);

                            Notification::make()
                                ->title('Reserva cancelada y devolución procesada')
                                ->warning()
                                ->send();
                        }),
                ]),
            ]);
    }

    // =========================================================================
    // 5. RUTAS DE LAS PÁGINAS DEL RECURSO
    // =========================================================================
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit'   => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}