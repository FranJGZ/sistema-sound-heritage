<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Reservation extends Model {
    protected $fillable = [
        'type',
        'date',
        'shift',
        'status',
        'price',
        'deposit_amount',
        'payment_method',
        'payment_status',
        'attendees_count',
        'notes',
        'customer_id',
        'venue_id',
        'mp_order_id',
        'mp_payment_id',
        'mp_refund_id',
        'refunded_at',
    ];

    protected $casts = [
        'attendees_count' => 'integer',
        'price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function venue() { return $this->belongsTo(Venue::class); }

    protected static function booted(): void
    {
        static::updated(function (Reservation $reservation) {
            if ($reservation->wasChanged('status') && $reservation->status === 'Confirmada') {
                try {
                    if ($reservation->customer && !empty($reservation->customer->email)) {
                        \Illuminate\Support\Facades\Mail::to($reservation->customer->email)
                            ->send(new \App\Mail\ReservationConfirmedMail($reservation));
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Error enviando mail de confirmación de reserva: ' . $e->getMessage());
                }
            }

            // Si se cancela una reserva que tenía pago acreditado, procesar el reembolso en Mercado Pago
            if ($reservation->wasChanged('status') && $reservation->status === 'Cancelada') {
                if ($reservation->payment_status === 'completed' || !empty($reservation->mp_payment_id) || !empty($reservation->mp_order_id)) {
                    try {
                        $mp = app(\App\Services\MercadoPagoService::class);
                        $mp->refundReservation($reservation);
                        $reservation->payment_status = 'refunded';
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('Error procesando reembolso de reserva cancelada: ' . $e->getMessage());
                    }
                }
            }
        });

        static::deleting(function (Reservation $reservation) {
            // Reembolso automático si se elimina / anula una reserva con pago de Mercado Pago
            if ($reservation->payment_status === 'completed' || !empty($reservation->mp_payment_id) || !empty($reservation->mp_order_id)) {
                try {
                    $mp = app(\App\Services\MercadoPagoService::class);
                    $mp->refundReservation($reservation);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Error reembolsando reserva al eliminar: ' . $e->getMessage());
                }
            }
        });
    }
}