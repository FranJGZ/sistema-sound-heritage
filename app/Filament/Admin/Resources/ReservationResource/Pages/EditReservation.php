<?php

namespace App\Filament\Admin\Resources\ReservationResource\Pages;

use App\Filament\Admin\Resources\ReservationResource;
use App\Models\Event;
use App\Models\Reservation;
use App\Services\MercadoPagoService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $venueId = $data['venue_id'] ?? $this->record->venue_id ?? 1;
        $date = $data['date'] ?? $this->record->date;
        $shift = $data['shift'] ?? $this->record->shift;

        if ($date) {
            $eventExists = Event::where('venue_id', $venueId)
                ->whereDate('date', $date)
                ->exists();

            if ($eventExists) {
                throw ValidationException::withMessages([
                    'data.date' => 'El salón ya está ocupado por un Evento cultural de Sound Heritage en esa fecha.',
                ]);
            }

            if ($shift) {
                $reservationExists = Reservation::where('venue_id', $venueId)
                    ->whereDate('date', $date)
                    ->where('shift', $shift)
                    ->where('status', '!=', 'Cancelada')
                    ->where('id', '!=', $this->record->id)
                    ->exists();

                if ($reservationExists) {
                    throw ValidationException::withMessages([
                        'data.shift' => "El salón ya cuenta con otra reserva activa para esa fecha en el turno {$shift}.",
                    ]);
                }
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        $status = $this->record->status;
        $payStatus = strtolower($this->record->payment_status ?? '');
        $hasMpOrder = !empty($this->record->mp_order_id);

        $isAbonada = str_contains($payStatus, 'abonada') || $payStatus === 'completed' || str_contains($payStatus, 'cancelado');
        $isSenada = !$isAbonada && (str_contains($payStatus, 'seña') || $this->record->payment_status === 'Señada (Resta 70%)');
        $isSinCobro = $status !== 'Cancelada' && !$isAbonada && !$isSenada && !$hasMpOrder;

        $actions = [];

        // 1. Botón principal: Aprobar Reserva (visible sólo si está Pendiente)
        if ($status === 'Pendiente') {
            $actions[] = Actions\Action::make('confirmar')
                ->label('Aprobar Reserva')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Aprobar Reserva de Salón')
                ->modalDescription('¿Confirmás la disponibilidad técnica del salón y la seña/pago para aprobar esta reserva?')
                ->modalSubmitActionLabel('Sí, Aprobar y Confirmar Reserva')
                ->action(function () {
                    $this->record->update(['status' => 'Confirmada']);
                    $this->fillForm();

                    Notification::make()
                        ->title('Reserva confirmada con éxito')
                        ->body('La reserva fue aprobada y se despachó el voucher de confirmación al cliente.')
                        ->success()
                        ->send();
                });
        }

        // 2. Si hay un cobro QR activo en Caja 1, mostrar botón de acceso rápido para ver el QR
        if ($hasMpOrder) {
            $actions[] = Actions\Action::make('ver_qr_cartel')
                ->label('Ver Cartel QR')
                ->icon('heroicon-m-qr-code')
                ->color('warning')
                ->modalHeading(fn () => "Cobro QR Mostrador · Caja 1 (Reserva #{$this->record->id})")
                ->modalContent(fn () => view('filament.modals.qr-caja1', ['record' => $this->record]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar Ventana');
        }

        // 3. Menú unificado y limpio de Cobranza / Caja (sin dispersión de botones de colores)
        if ($status !== 'Cancelada' && (!$isAbonada || $hasMpOrder)) {
            $actions[] = Actions\ActionGroup::make([
                // Cobro total en efectivo (cuando no tiene pagos previos)
                Actions\Action::make('cobrar_total_efectivo')
                    ->label('Cobrar Total en Efectivo')
                    ->icon('heroicon-o-currency-dollar')
                    ->visible(fn (): bool => $isSinCobro)
                    ->requiresConfirmation()
                    ->modalHeading('Cobrar Alquiler Total en Efectivo')
                    ->modalDescription(function () {
                        $total = (float) $this->record->price;
                        return "El cliente abonará el total de $ " . number_format($total, 2, ',', '.') . " en efectivo en mostrador. La reserva quedará Totalmente Abonada.";
                    })
                    ->modalSubmitActionLabel('Confirmar Cobro Total')
                    ->action(function () {
                        $total = (float) $this->record->price;
                        $this->record->update([
                            'payment_status' => 'Totalmente Abonada',
                            'payment_method' => 'Efectivo',
                            'deposit_amount' => $total,
                        ]);
                        $this->fillForm();

                        Notification::make()
                            ->title('Alquiler cobrado en su totalidad')
                            ->body('La reserva quedó Totalmente Abonada en efectivo.')
                            ->success()
                            ->send();
                    }),

                // Cobro de seña 30% en efectivo
                Actions\Action::make('cobrar_sena_efectivo')
                    ->label('Cobrar Seña (30%) en Efectivo')
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (): bool => $isSinCobro)
                    ->requiresConfirmation()
                    ->modalHeading('Cobrar Seña del 30% en Efectivo')
                    ->modalDescription(function () {
                        $monto = $this->record->deposit_amount > 0 ? (float) $this->record->deposit_amount : round((float)$this->record->price * 0.3, 2);
                        return "El cliente abonará la seña de $ " . number_format($monto, 2, ',', '.') . " en efectivo. Restará el 70% a liquidar el día del turno.";
                    })
                    ->modalSubmitActionLabel('Registrar Cobro de Seña')
                    ->action(function () {
                        $monto = $this->record->deposit_amount > 0 ? (float) $this->record->deposit_amount : round((float)$this->record->price * 0.3, 2);
                        $this->record->update([
                            'payment_status' => 'Señada (Resta 70%)',
                            'payment_method' => 'Efectivo',
                            'deposit_amount' => $monto,
                        ]);
                        $this->fillForm();

                        Notification::make()
                            ->title('Seña cobrada en efectivo')
                            ->body("Se asentó el cobro de la seña por $ " . number_format($monto, 2, ',', '.') . ".")
                            ->success()
                            ->send();
                    }),

                // Saldar el saldo restante (70%) en efectivo
                Actions\Action::make('saldar_efectivo')
                    ->label('Saldar Restante en Efectivo')
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (): bool => $isSenada && !$hasMpOrder)
                    ->requiresConfirmation()
                    ->modalHeading('Saldar Deuda Restante en Efectivo')
                    ->modalDescription(function () {
                        $saldo = max(0, (float) $this->record->price - (float) $this->record->deposit_amount);
                        return "El cliente abonará el saldo pendiente de $ " . number_format($saldo, 2, ',', '.') . " en efectivo en mostrador.";
                    })
                    ->modalSubmitActionLabel('Confirmar Cobro Total')
                    ->action(function () {
                        $total = (float) $this->record->price;
                        $this->record->update([
                            'payment_status' => 'Totalmente Abonada',
                            'payment_method' => 'Efectivo',
                            'deposit_amount' => $total,
                        ]);
                        $this->fillForm();

                        Notification::make()
                            ->title('Deuda saldada en efectivo')
                            ->body('La reserva quedó Totalmente Abonada.')
                            ->success()
                            ->send();
                    }),

                // Cobro total con QR en Caja 1
                Actions\Action::make('cobrar_total_qr')
                    ->label('Cobrar Total por QR (Caja 1)')
                    ->icon('heroicon-m-qr-code')
                    ->visible(fn (): bool => $isSinCobro)
                    ->requiresConfirmation()
                    ->modalHeading('Cobro Total con Código QR (Caja 1)')
                    ->modalDescription(function () {
                        $total = (float) $this->record->price;
                        return "Se enviará la orden por el total de $ " . number_format($total, 2, ',', '.') . " a la Caja 1 con referencia RES-{$this->record->id}-TOTAL.";
                    })
                    ->modalSubmitActionLabel('Enviar Total a Caja 1')
                    ->action(function () {
                        $total = (float) $this->record->price;
                        $mp = app(MercadoPagoService::class);
                        $mp->cancelAnyActiveCajaOrder('CAJA1');
                        $order = $mp->createQrOrder(
                            amount: $total,
                            description: "Pago Total Reserva #{$this->record->id} - {$this->record->type}",
                            externalReference: "RES-{$this->record->id}-TOTAL",
                            posId: 'CAJA1'
                        );

                        if ($order && isset($order['id'])) {
                            $this->record->update([
                                'mp_order_id'    => $order['id'],
                                'payment_status' => 'pending',
                            ]);
                            $this->fillForm();

                            Notification::make()
                                ->title('Orden enviada a Caja 1')
                                ->body("Orden por $ " . number_format($total, 2, ',', '.') . " activa en Caja 1.")
                                ->success()
                                ->send();
                        }
                    }),

                // Cobro de seña con QR en Caja 1
                Actions\Action::make('cobrar_sena_qr')
                    ->label('Cobrar Seña (30%) por QR (Caja 1)')
                    ->icon('heroicon-m-qr-code')
                    ->visible(fn (): bool => $isSinCobro)
                    ->requiresConfirmation()
                    ->modalHeading('Cobro de Seña con Código QR (Caja 1)')
                    ->modalDescription(function () {
                        $monto = $this->record->deposit_amount > 0 ? (float) $this->record->deposit_amount : round((float)$this->record->price * 0.3, 2);
                        return "Se enviará la orden de seña por $ " . number_format($monto, 2, ',', '.') . " a la Caja 1.";
                    })
                    ->modalSubmitActionLabel('Enviar Seña a Caja 1')
                    ->action(function () {
                        $monto = $this->record->deposit_amount > 0 ? (float) $this->record->deposit_amount : round((float)$this->record->price * 0.3, 2);
                        $mp = app(MercadoPagoService::class);
                        $mp->cancelAnyActiveCajaOrder('CAJA1');
                        $order = $mp->createQrOrder(
                            amount: $monto,
                            description: "Seña Reserva #{$this->record->id} - {$this->record->type}",
                            externalReference: "RES-{$this->record->id}-SENA",
                            posId: 'CAJA1'
                        );

                        if ($order && isset($order['id'])) {
                            $this->record->update([
                                'mp_order_id'    => $order['id'],
                                'payment_status' => 'pending',
                            ]);
                            $this->fillForm();

                            Notification::make()
                                ->title('Seña enviada a Caja 1')
                                ->body("Orden por $ " . number_format($monto, 2, ',', '.') . " activa en Caja 1.")
                                ->success()
                                ->send();
                        }
                    }),

                // Saldar deuda restante (70%) con QR en Caja 1
                Actions\Action::make('saldar_qr')
                    ->label('Cobrar Restante por QR (Caja 1)')
                    ->icon('heroicon-m-qr-code')
                    ->visible(fn (): bool => $isSenada && !$hasMpOrder)
                    ->requiresConfirmation()
                    ->modalHeading('Cobrar Saldo Restante por QR en Caja 1')
                    ->modalDescription(function () {
                        $saldo = max(0, (float) $this->record->price - (float) $this->record->deposit_amount);
                        return "Se enviará la orden por el saldo restante de $ " . number_format($saldo, 2, ',', '.') . " a la Caja 1 con referencia RES-{$this->record->id}-TOTAL.";
                    })
                    ->modalSubmitActionLabel('Enviar Saldo a Caja 1')
                    ->action(function () {
                        $saldo = max(0, (float) $this->record->price - (float) $this->record->deposit_amount);
                        $mp = app(MercadoPagoService::class);
                        $mp->cancelAnyActiveCajaOrder('CAJA1');
                        $order = $mp->createQrOrder(
                            amount: $saldo,
                            description: "Saldo Restante Reserva #{$this->record->id} - {$this->record->type}",
                            externalReference: "RES-{$this->record->id}-TOTAL",
                            posId: 'CAJA1'
                        );

                        if ($order && isset($order['id'])) {
                            $this->record->update([
                                'mp_order_id'    => $order['id'],
                                'payment_status' => 'pending',
                            ]);
                            $this->fillForm();

                            Notification::make()
                                ->title('Saldo restante enviado a Caja 1')
                                ->body("Orden por $ " . number_format($saldo, 2, ',', '.') . " activa en Caja 1.")
                                ->success()
                                ->send();
                        }
                    }),

                // Liberar punto de venta Caja 1
                Actions\Action::make('liberar_caja_qr')
                    ->label('Liberar Caja 1')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn (): bool => $hasMpOrder)
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar cobro en Caja 1')
                    ->modalDescription('¿Deseás cancelar la orden activa en la Caja 1 para liberar el punto de venta?')
                    ->action(function () {
                        $mp = app(MercadoPagoService::class);
                        $mp->cancelQrOrder($this->record->mp_order_id);
                        $this->record->update([
                            'mp_order_id'    => null,
                            'payment_status' => 'Efectivo en Mostrador',
                        ]);
                        $this->fillForm();

                        Notification::make()
                            ->title('Caja 1 liberada')
                            ->warning()
                            ->send();
                    }),
            ])
            ->label('Gestión de Cobro')
            ->icon('heroicon-o-banknotes')
            ->color('primary')
            ->button();
        }

        // 4. Cancelar Reserva
        if ($status !== 'Cancelada') {
            $actions[] = Actions\Action::make('cancelar')
                ->label('Cancelar Reserva')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancelar Reserva de Salón')
                ->modalDescription('¿Confirmás cancelar esta reserva? Si registra cobro por Mercado Pago, se reembolsará automáticamente el dinero a la cuenta del comprador.')
                ->modalSubmitActionLabel('Sí, Cancelar Reserva')
                ->action(function () {
                    $this->record->update(['status' => 'Cancelada']);
                    $this->fillForm();

                    Notification::make()
                        ->title('Reserva cancelada')
                        ->warning()
                        ->send();
                });
        }

        return $actions;
    }
}
