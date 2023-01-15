<?php

namespace Tests\Unit;

use App\Services\BookingService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    public function test_days_are_available(): void
    {
        $from = new \DateTimeImmutable('tomorrow');
        $to = $from->add(new \DateInterval('P10D'));

        DB::shouldReceive('select')
            ->once()
            ->with('SELECT COUNT(*) AS sum
                    FROM spaces
                    WHERE booking >= :from AND booking <= :to
                    GROUP BY booking
                    HAVING COUNT(*) >= :tS',
                ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d'), 'tS' => 10])
            ->andReturn([]);

        $this->assertTrue(BookingService::areDaysAvailable($from->format('Y-m-d'), 10));
    }

    public function test_days_are_not_available(): void
    {
        $from = new \DateTimeImmutable('tomorrow');
        $to = $from->add(new \DateInterval('P10D'));

        DB::shouldReceive('select')
            ->once()
            ->with('SELECT COUNT(*) AS sum
                    FROM spaces
                    WHERE booking >= :from AND booking <= :to
                    GROUP BY booking
                    HAVING COUNT(*) >= :tS',
                ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d'), 'tS' => 10])
            ->andReturn(['bookings' => []]);

        $this->assertFalse(BookingService::areDaysAvailable($from->format('Y-m-d'), 10));
    }

    public function test_available_spaces(): void
    {
        $from = new \DateTimeImmutable('tomorrow');
        DB::shouldReceive('scalar')
            ->once()
            ->with('SELECT COUNT(*) AS sum
                    FROM spaces
                    WHERE booking = :from',
                ['from' => $from->format('Y-m-d')])
            ->andReturn(0);
        $this->assertEquals(10, BookingService::availableSpaces($from->format('Y-m-d')));
    }

}
