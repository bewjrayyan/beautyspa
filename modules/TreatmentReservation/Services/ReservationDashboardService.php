<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Carbon;
use Modules\Support\Money;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class ReservationDashboardService
{
    /**
     * Dashboard stats aligned with the job-sheet kanban columns.
     *
     * @return array{pending: int, inProgress: int, completed: int}
     */
    public function stats(): array
    {
        $base = TreatmentBooking::query()
            ->withTreatmentProduct()
            ->whereNot('status', TreatmentBooking::STATUS_CANCELED);

        return [
            'pending' => (clone $base)
                ->where('status', TreatmentBooking::STATUS_PENDING)
                ->count(),
            'inProgress' => (clone $base)
                ->where('status', TreatmentBooking::STATUS_IN_PROGRESS)
                ->count(),
            'completed' => (clone $base)
                ->where('status', TreatmentBooking::STATUS_COMPLETED)
                ->count(),
        ];
    }


    public function statsForBeautician(int $beauticianId): array
    {
        $today = today()->toDateString();
        $weekEnd = Carbon::parse($today)->addDays(7)->toDateString();
        $weekStart = Carbon::now()->startOfWeek()->toDateString();
        $weekEndDate = Carbon::now()->endOfWeek()->toDateString();

        return [
            'totalBookings' => $this->beauticianBookingBase($beauticianId)->count(),
            'todayCompleted' => $this->beauticianBookingBase($beauticianId)
                ->where('status', TreatmentBooking::STATUS_COMPLETED)
                ->whereDate('appointment_date', $today)
                ->count(),
            'weekCompleted' => $this->beauticianBookingBase($beauticianId)
                ->where('status', TreatmentBooking::STATUS_COMPLETED)
                ->whereDate('appointment_date', '>=', $weekStart)
                ->whereDate('appointment_date', '<=', $weekEndDate)
                ->count(),
            'pendingToday' => $this->beauticianBookingBase($beauticianId)
                ->whereDate('appointment_date', $today)
                ->where('status', TreatmentBooking::STATUS_PENDING)
                ->count(),
            'inProgress' => $this->beauticianBookingBase($beauticianId)
                ->where('status', TreatmentBooking::STATUS_IN_PROGRESS)
                ->count(),
            'treatmentRevenue' => Money::inDefaultCurrency(
                $this->beauticianBookingBase($beauticianId)
                    ->where('status', TreatmentBooking::STATUS_COMPLETED)
                    ->sum('total')
            ),
            'upcomingWeek' => $this->beauticianBookingBase($beauticianId)
                ->whereBetween('appointment_date', [$today, $weekEnd])
                ->whereIn('status', [
                    TreatmentBooking::STATUS_PENDING,
                    TreatmentBooking::STATUS_IN_PROGRESS,
                ])
                ->count(),
        ];
    }


    /**
     * Stats aligned with the beautician job-sheet kanban columns.
     *
     * @return array{pending: int, inProgress: int, completed: int}
     */
    public function statsForBeauticianSchedule(int $beauticianId): array
    {
        return [
            'pending' => $this->beauticianBookingBase($beauticianId)
                ->where('status', TreatmentBooking::STATUS_PENDING)
                ->count(),
            'inProgress' => $this->beauticianBookingBase($beauticianId)
                ->where('status', TreatmentBooking::STATUS_IN_PROGRESS)
                ->count(),
            'completed' => $this->beauticianBookingBase($beauticianId)
                ->where('status', TreatmentBooking::STATUS_COMPLETED)
                ->count(),
        ];
    }


    /**
     * @return \Illuminate\Support\Collection<int, TreatmentBooking>
     */
    public function todayAppointmentsForBeautician(int $beauticianId)
    {
        return $this->beauticianBookingBase($beauticianId)
            ->with(['product', 'category'])
            ->whereDate('appointment_date', today())
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->orderBy('appointment_time')
            ->get();
    }


    private function beauticianBookingBase(int $beauticianId)
    {
        return TreatmentBooking::query()
            ->withTreatmentProduct()
            ->where('beautician_id', $beauticianId)
            ->whereNot('status', TreatmentBooking::STATUS_CANCELED);
    }
}
