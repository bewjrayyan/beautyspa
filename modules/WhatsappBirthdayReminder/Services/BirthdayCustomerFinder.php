<?php

namespace Modules\WhatsappBirthdayReminder\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Modules\User\Entities\User;

class BirthdayCustomerFinder
{
    /**
     * Customers whose date_of_birth matches today's month and day.
     *
     * @return Collection<int, User>
     */
    public function forToday(?\DateTimeInterface $date = null): Collection
    {
        $date = $date ? Carbon::parse($date) : now();

        return User::query()
            ->whereNotNull('date_of_birth')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereMonth('date_of_birth', $date->month)
            ->whereDay('date_of_birth', $date->day)
            ->get();
    }
}
