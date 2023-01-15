<?php

namespace Tests\Feature;

use App\Models\Price;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_booking_successfully(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        $from = (new \DateTime('tomorrow'))->format('Y-m-d');
        $response = $this->postJson('/api/bookings', ['booking_from' => $from, 'days' => 10]);

        $response->assertJson(['message' => 'Booking created successfully!']);

        $this->assertEquals(
            10,
            Space::where([['booking', '>=', $from], ['booking_id', '=', $response->json('booking.id')]])->count()
        );

        $response->assertStatus(200);

    }

    public function test_create_booking_fails_when_no_availability(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        Space::factory()->count(10)->create();
        $response = $this->postJson('/api/bookings', ['booking_from' => (new \DateTime('tomorrow'))->format('Y-m-d'), 'days' => 10]);

        $response->assertJson(['message' => 'Booking cannot be created due to lack of availability.']);

        $response->assertStatus(400);
    }

    public function test_create_booking_fails_when_booking_from_is_not_date(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        $response = $this->postJson('/api/bookings', ['booking_from' => 'text', 'days' => 10]);
        $response->assertJson(['booking_from' => ['The booking from does not match the format Y-m-d.']]);
        $response->assertStatus(422);
    }

    public function test_create_booking_fails_when_booking_from_is_not_after_today(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        $response = $this->postJson('/api/bookings', ['booking_from' => '2023-01-01', 'days' => 10]);
        $response->assertJson(['booking_from' => ['The booking from must be a date after today.']]);
        $response->assertStatus(422);
    }

    public function test_create_booking_fails_when_days_are_not_int(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        $response = $this->postJson('/api/bookings', ['booking_from' => '2023-01-25', 'days' => 'string']);
        $response->assertJson(['days' => ['The days must be an integer.']]);
        $response->assertStatus(422);
    }

    public function test_create_booking_fails_when_days_are_not_minimum_one(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        $response = $this->postJson('/api/bookings', ['booking_from' => '2023-01-25', 'days' => 0]);
        $response->assertJson(['days' => ['The days must be at least 1.']]);
        $response->assertStatus(422);
    }

    public function test_only_own_bookings_can_be_viewed(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Sanctum::actingAs(
            $user1
        );
        $from = (new \DateTime('tomorrow'))->format('Y-m-d');
        $response = $this->postJson('/api/bookings', ['booking_from' => $from, 'days' => 10]);

        Sanctum::actingAs(
            $user2
        );

        $response2 = $this->get('/api/bookings/'.$response->json('booking.id'));
        $response2->assertJson(['message' => 'Booking does not belong to the user.']);
        $response2->assertStatus(403);
    }

    public function test_only_your_own_bookings_being_listed(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $from = (new \DateTime('tomorrow'))->format('Y-m-d');
        Sanctum::actingAs(
            $user1
        );
        $this->postJson('/api/bookings', ['booking_from' => $from, 'days' => 10]);
        Sanctum::actingAs(
            $user2
        );
        $this->postJson('/api/bookings', ['booking_from' => $from, 'days' => 10]);
        $this->postJson('/api/bookings', ['booking_from' => $from, 'days' => 10]);
        $response = $this->get('/api/bookings');
        foreach ($response->json('bookings') as $booking)
        {
            $this->assertEquals($booking["user_id"], $user2->id);
        }

    }

    public function test_bookings_of_other_users_cannot_be_deleted(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $from = (new \DateTime('tomorrow'))->format('Y-m-d');
        Sanctum::actingAs(
            $user1
        );
        $response = $this->postJson('/api/bookings', ['booking_from' => $from, 'days' => 10]);
        Sanctum::actingAs(
            $user2
        );
        $response2 = $this->delete('/api/bookings/'.$response->json('booking.id'));
        $response2->assertJson(['message' => 'Booking does not belong to the user.']);
        $response2->assertStatus(403);
    }

    public function test_user_can_delete_its_own_booking(): void
    {
        $user1 = User::factory()->create();
        $from = (new \DateTime('tomorrow'))->format('Y-m-d');
        Sanctum::actingAs(
            $user1
        );
        $response = $this->postJson('/api/bookings', ['booking_from' => $from, 'days' => 10]);

        $response2 = $this->delete('/api/bookings/'.$response->json('booking.id'));
        $response2->assertJson(['message' => 'Booking deleted successfully!']);
        $response2->assertStatus(200);

        $response3 = $this->get('/api/bookings/'.$response->json('booking.id'));
        $response3->assertStatus(404);

    }

    public function test_user_cant_update_other_users_booking(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $from = (new \DateTime('tomorrow'))->format('Y-m-d');
        Sanctum::actingAs(
            $user1
        );
        $response = $this->postJson('/api/bookings', ['booking_from' => $from, 'days' => 10]);
        Sanctum::actingAs(
            $user2
        );
        $response2 = $this->patchJson('/api/bookings/'.$response->json('booking.id'), ['booking_from' => $from, 'days' => 3]);
        $response2->assertJson(['message' => 'Booking does not belong to the user.']);
        $response2->assertStatus(403);
    }

    public function test_user_can_update_own_booking(): void
    {
        $user1 = User::factory()->create();
        $from = (new \DateTime('tomorrow'))->format('Y-m-d');
        Sanctum::actingAs(
            $user1
        );
        $response = $this->postJson('/api/bookings', ['booking_from' => $from, 'days' => 10]);
        $response2 = $this->patchJson('/api/bookings/'.$response->json('booking.id'), ['booking_from' => $from, 'days' => 3]);
        $response2->assertJson(['message' => 'Booking updated successfully!']);
        $response2->assertStatus(200);
        $this->assertEquals(
            3,
            Space::where([['booking', '>=', $from], ['booking_id', '=', $response2->json('booking.id')]])->count()
        );
    }

    public function test_free_spaces_check_throw_error_when_date_incorrect(): void
    {
        $user1 = User::factory()->create();
        $from = (new \DateTime('tomorrow'))->format('Y-m-d');
        Sanctum::actingAs(
            $user1
        );
        $response = $this->getJson('/api/bookings-check?date=2023-23-12');
        $response->assertJson(['errors' => ['date' => ['The date does not match the format Y-m-d.']]]);
        $response->assertStatus(400);
    }

    public function test_free_spaces_checked_with_no_bookings(): void
    {
        $user1 = User::factory()->create();
        $from = (new \DateTime('tomorrow'))->format('Y-m-d');
        Sanctum::actingAs(
            $user1
        );
        $response = $this->getJson('/api/bookings-check?date='.$from);
        $this->assertEquals(10, $response->json('free_spaces'));
        $response->assertStatus(200);
    }

    public function test_free_spaces_checked_with_bookings(): void
    {
        $user1 = User::factory()->create();
        $from = (new \DateTime('tomorrow'))->format('Y-m-d');
        Sanctum::actingAs(
            $user1
        );
        $this->postJson('/api/bookings', ['booking_from' => $from, 'days' => 10]);
        $response = $this->getJson('/api/bookings-check?date='.$from);
        $this->assertEquals(9, $response->json('free_spaces'));
        $response->assertStatus(200);
    }

    public function test_free_spaces_checked_when_fully_booked(): void
    {
        $user1 = User::factory()->create();
        $from = (new \DateTime('tomorrow'))->format('Y-m-d');
        Sanctum::actingAs(
            $user1
        );
        for($i=1;$i<=10;$i++) {
            $this->postJson('/api/bookings', ['booking_from' => $from, 'days' => 10]);
        }
        $response = $this->getJson('/api/bookings-check?date='.$from);
        $this->assertEquals(0, $response->json('free_spaces'));
        $response->assertStatus(200);
    }

    public function test_check_prices_with_prices_set_corectly(): void
    {
        $user1 = User::factory()->create();
        $from = (new \DateTime('tomorrow'))->format('Y-m-d');
        Sanctum::actingAs(
            $user1
        );
        $price1 = new Price(['price' => 10, 'valid_from' => '2023-01-15', 'valid_to' => '2023-06-30']);
        $price1->save();
        $price2 = new Price(['price' => 15, 'valid_from' => '2023-07-01', 'valid_to' => '2023-09-01']);
        $price2->save();
        $response = $this->getJson('/api/price-check?date='.$from);
        $this->assertEquals(10, $response->json('price'));
        $response->assertStatus(200);

        $response = $this->getJson('/api/price-check?date=2023-07-02');
        $this->assertEquals(15, $response->json('price'));
        $response->assertStatus(200);
    }

    public function test_check_prices_with_prices_set_incorectly(): void
    {
        $user1 = User::factory()->create();
        Sanctum::actingAs(
            $user1
        );
        $price1 = new Price(['price' => 10, 'valid_from' => '2023-01-15', 'valid_to' => '2023-06-30']);
        $price1->save();
        $response = $this->getJson('/api/price-check?date=2023-07-02');
        $this->assertEquals(9, $response->json('price'));
        $response->assertStatus(200);
    }

    public function test_check_prices_with_prices_not_set(): void
    {
        $user1 = User::factory()->create();
        Sanctum::actingAs(
            $user1
        );
        $response = $this->getJson('/api/price-check?date=2023-07-02');
        $this->assertEquals(9, $response->json('price'));
        $response->assertStatus(200);
    }

}

