<?php

namespace Tests\Unit;

use Tests\TestCase;
use Carbon\Carbon;

class BookingTimeTest extends TestCase
{
    /** @test */
    public function end_time_must_be_after_start_time()
    {
        $start = Carbon::createFromFormat('H:i', '08:00');
        $end   = Carbon::createFromFormat('H:i', '10:00');

        $this->assertTrue($end->gt($start));
    }

    /** @test */
    public function cannot_book_negative_duration()
    {
        $start = Carbon::createFromFormat('H:i', '10:00');
        $end   = Carbon::createFromFormat('H:i', '09:00');

        $this->assertFalse($end->gt($start));
    }

    /** @test */
    public function calculate_booking_hours_correctly()
    {
        $start = Carbon::createFromFormat('H:i', '08:00');
        $end   = Carbon::createFromFormat('H:i', '11:00');

        $hours = $end->diffInHours($start);

        $this->assertEquals(3, $hours);
    }
}
