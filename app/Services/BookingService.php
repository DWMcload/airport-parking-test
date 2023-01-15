<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class BookingService
{
    public static function areDaysAvailable(string $from, int $days): bool
    {
        $totalSpaces = config('booking.spaces');
        $to = \DateTime::createFromFormat('Y-m-d', $from);
        $to->add(new \DateInterval('P'.$days.'D'));

        if ([] !== DB::select(
                'SELECT COUNT(*) AS sum
                    FROM spaces
                    WHERE booking >= :from AND booking <= :to
                    GROUP BY booking
                    HAVING COUNT(*) >= :tS',
                ['from' => $from, 'to' => $to->format('Y-m-d'), 'tS' => $totalSpaces]))
        {
            return false;
        }
        return true;
    }

    public static function availableSpaces(string $date): int
    {
        $totalSpaces = config('booking.spaces');

        $usedSpaces = DB::scalar(
            'SELECT COUNT(*) AS sum
                    FROM spaces
                    WHERE booking = :from',
            ['from' => $date]
        );

        $free = $totalSpaces - $usedSpaces;

        return (0 > $free) ? 0 : $free;
    }
}
