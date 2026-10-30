<?php

namespace App\Filament\Admin\Resources\EventResource\Pages;

use App\Filament\Admin\Resources\EventResource;
use App\Models\Event;
use App\Models\Reservation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $venueId = $data['venue_id'] ?? 1;
        $date = $data['date'] ?? null;

        if ($date) {
            $eventExists = Event::where('venue_id', $venueId)
                ->whereDate('date', $date)
                ->exists();

            if ($eventExists) {
                throw ValidationException::withMessages([
                    'data.date' => 'El salón ya tiene otro evento programado en esa misma fecha.',
                ]);
            }

            $reservationExists = Reservation::where('venue_id', $venueId)
                ->whereDate('date', $date)
                ->where('status', '!=', 'Cancelada')
                ->exists();

            if ($reservationExists) {
                throw ValidationException::withMessages([
                    'data.date' => 'El salón ya cuenta con una reserva activa o pendiente de un cliente en esa fecha.',
                ]);
            }
        }

        return $data;
    }
}
