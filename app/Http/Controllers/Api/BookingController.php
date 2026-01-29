<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BusinessHour;
use App\Models\Exception;
use App\Models\Pet;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Ottieni i pets dell'utente loggato
     */
    public function myPets(Request $request)
    {
        $pets = $request->user()->pets()->get();
        
        return response()->json([
            'success' => true,
            'pets' => $pets
        ]);
    }

    /**
     * Calcola slot disponibili per un servizio in una data
     */
    public function availableSlots(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $serviceId = $request->service_id;
        $date = Carbon::parse($request->date);
        $dayOfWeek = $date->dayOfWeek; // 0=Domenica, 1=Lunedì, etc.

        // 1. Recupera il servizio e la sua durata
        $service = Service::findOrFail($serviceId);
        $serviceDuration = $service->duration; // in minuti

        // 2. Controlla se c'è una chiusura speciale per questa data
        $exception = Exception::where('date', $date->format('Y-m-d'))->first();
        
        if ($exception && $exception->is_closed) {
            return response()->json([
                'success' => false,
                'message' => 'Il negozio è chiuso in questa data: ' . $exception->reason,
                'slots' => []
            ]);
        }

        // 3. Recupera gli orari standard per questo giorno
        $businessHour = BusinessHour::where('day_of_week', $dayOfWeek)->first();
        
        if (!$businessHour || $businessHour->is_closed) {
            return response()->json([
                'success' => false,
                'message' => 'Il negozio è chiuso in questo giorno della settimana',
                'slots' => []
            ]);
        }

        // 4. Se c'è un'eccezione con orari speciali, usa quelli
        if ($exception && !$exception->is_closed) {
            $morningOpen = $this->normalizeTimeValue($exception->morning_open);
            $morningClose = $this->normalizeTimeValue($exception->morning_close);
            $afternoonOpen = $this->normalizeTimeValue($exception->afternoon_open);
            $afternoonClose = $this->normalizeTimeValue($exception->afternoon_close);
        } else {
            $morningOpen = $this->normalizeTimeValue($businessHour->morning_open);
            $morningClose = $this->normalizeTimeValue($businessHour->morning_close);
            $afternoonOpen = $this->normalizeTimeValue($businessHour->afternoon_open);
            $afternoonClose = $this->normalizeTimeValue($businessHour->afternoon_close);
        }

        // 5. Recupera le prenotazioni gia esistenti per questa data
        $existingBookings = Booking::where('booking_date', $date->format('Y-m-d'))
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->orderBy('time_slot')
            ->get();

        // 6. Costruisci gli intervalli di apertura
        $openIntervals = [];
        if ($morningOpen && $morningClose) {
            $openIntervals[] = [
                Carbon::parse($date->format('Y-m-d') . ' ' . $morningOpen),
                Carbon::parse($date->format('Y-m-d') . ' ' . $morningClose),
            ];
        }
        if ($afternoonOpen && $afternoonClose) {
            $openIntervals[] = [
                Carbon::parse($date->format('Y-m-d') . ' ' . $afternoonOpen),
                Carbon::parse($date->format('Y-m-d') . ' ' . $afternoonClose),
            ];
        }

        // 7. Calcola gli intervalli liberi reali
        $freeIntervals = [];
        foreach ($openIntervals as [$openStart, $openEnd]) {
            $cursor = $openStart->copy();

            foreach ($existingBookings as $booking) {
                $bookingTime = $this->normalizeTimeValue($booking->time_slot);
                $bookingStart = Carbon::parse($date->format('Y-m-d') . ' ' . $bookingTime);
                $bookingEnd = $bookingStart->copy()->addMinutes($booking->duration);

                if ($bookingEnd->lte($openStart) || $bookingStart->gte($openEnd)) {
                    continue;
                }

                if ($bookingStart->gt($cursor)) {
                    $freeIntervals[] = [$cursor->copy(), $bookingStart->copy()];
                }

                if ($bookingEnd->gt($cursor)) {
                    $cursor = $bookingEnd->copy();
                }

                if ($cursor->gte($openEnd)) {
                    break;
                }
            }

            if ($cursor->lt($openEnd)) {
                $freeIntervals[] = [$cursor->copy(), $openEnd->copy()];
            }
        }

        // 8. Genera slot dinamici dagli intervalli liberi
        $availableSlots = [];
        foreach ($freeIntervals as [$freeStart, $freeEnd]) {
            $availableSlots = array_merge(
                $availableSlots,
                $this->generateTimeSlots($freeStart, $freeEnd, $serviceDuration)
            );
        }

        // 9. Se la data e' oggi, rimuovi slot gia passati
        if ($date->isToday()) {
            $now = Carbon::now();
            $availableSlots = array_filter($availableSlots, function($slot) use ($now, $date) {
                return Carbon::parse($date->format('Y-m-d') . ' ' . $slot)->isAfter($now);
            });
        }

        return response()->json([
            'success' => true,
            'slots' => array_values($availableSlots),
            'service_duration' => $serviceDuration
        ]);
    }

    /**
     * Genera slot di tempo a partire dall'inizio reale di disponibilita
     */
    private function generateTimeSlots($start, $end, $serviceDuration)
    {
        $slots = [];
        $current = $start->copy();
        $endTime = $end->copy();

        // Sottrae la durata del servizio dall'orario di chiusura
        // cosi l'ultimo slot ha abbastanza tempo
        $lastSlotTime = $endTime->copy()->subMinutes($serviceDuration);

        while ($current->lte($lastSlotTime)) {
            $slots[] = $current->format('H:i');
            $current->addMinutes($serviceDuration);
        }

        return $slots;
    }

    /**
     * Crea una nuova prenotazione
     */
    public function store(Request $request)
    {
        $request->validate([
            'pet_id' => 'required|exists:pets,id',
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date',
            'time_slot' => 'required',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        
        // Verifica che il pet appartenga all'utente
        $pet = Pet::where('id', $request->pet_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Recupera il servizio
        $service = Service::findOrFail($request->service_id);
        
        if (!$service->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Questo servizio non è più disponibile'
            ], 400);
        }

        $bookingDate = Carbon::parse($request->booking_date);
        $normalizedTime = $this->normalizeTimeValue($request->time_slot);
        $timeSlot = Carbon::parse($normalizedTime);
        
        // Validazione custom: verifica che data+ora non siano nel passato
        $bookingDateTime = Carbon::parse($request->booking_date . ' ' . $normalizedTime);
        if ($bookingDateTime->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Non puoi prenotare nel passato'
            ], 400);
        }

        // Verifica che lo slot sia ancora disponibile (race condition check)
        $isSlotAvailable = $this->checkSlotAvailability(
            $bookingDate,
            $timeSlot,
            $service->duration
        );

        if (!$isSlotAvailable) {
            return response()->json([
                'success' => false,
                'message' => 'Questo slot non è più disponibile. Ricarica la pagina e scegli un altro orario.'
            ], 400);
        }

        // Crea la prenotazione
        $booking = Booking::create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'service_id' => $service->id,
            'booking_date' => $bookingDate->format('Y-m-d'),
            'time_slot' => $timeSlot->format('H:i:s'),
            'duration' => $service->duration,
            'price' => $service->price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => $request->notes,
        ]);

        // Ricarica con relazioni
        $booking->load(['pet', 'service', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Prenotazione creata con successo!',
            'booking' => $booking
        ], 201);
    }

    /**
     * Verifica disponibilità slot
     */
    private function checkSlotAvailability($date, $timeSlot, $duration)
    {
        $slotStart = Carbon::parse($date->format('Y-m-d') . ' ' . $timeSlot->format('H:i:s'));
        $slotEnd = $slotStart->copy()->addMinutes($duration);
        
        $overlappingBookings = Booking::where('booking_date', $date->format('Y-m-d'))
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->get()
            ->filter(function ($booking) use ($date, $slotStart, $slotEnd) {
                $bookingTime = $this->normalizeTimeValue($booking->time_slot);
                $bookingStart = Carbon::parse($date->format('Y-m-d') . ' ' . $bookingTime);
                $bookingEnd = $bookingStart->copy()->addMinutes($booking->duration);
                
                return $slotStart->lt($bookingEnd) && $slotEnd->gt($bookingStart);
            });

        return $overlappingBookings->isEmpty();
    }

    private function normalizeTimeValue(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('H:i:s');
        }

        $value = (string) $value;

        if (str_contains($value, ' ')) {
            return Carbon::parse($value)->format('H:i:s');
        }

        return strlen($value) === 5 ? $value . ':00' : $value;
    }

    /**
     * Ottieni le prenotazioni dell'utente
     */
    public function myBookings(Request $request)
    {
        $bookings = Booking::where('user_id', $request->user()->id)
            ->with(['pet', 'service'])
            ->orderBy('booking_date', 'desc')
            ->orderBy('time_slot', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'bookings' => $bookings
        ]);
    }

    /**
     * Cancella una prenotazione
     */
    public function cancel(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Può cancellare solo se è pending o confirmed
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Non puoi cancellare questa prenotazione'
            ], 400);
        }

        // Può cancellare solo se la data non è passata
        if (Carbon::parse($booking->booking_date)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Non puoi cancellare una prenotazione passata'
            ], 400);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Prenotazione cancellata con successo'
        ]);
    }
}
