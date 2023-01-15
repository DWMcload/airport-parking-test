<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Price;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function index(): JsonResponse
    {
        $bookings = Booking::where('user_id', Auth::user()->id)->get();

        return response()->json([
            'status' => true,
            'bookings' => $bookings
        ]);
    }

    public function checkSpaces(Request $request): JsonResponse
    {
        $validateCheck = Validator::make($request->all(),
            [
                'date' => 'required|date_format:Y-m-d|after:today',
            ]);

        if($validateCheck->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validateCheck->errors()
            ], 400);
        }

        $spaces = BookingService::availableSpaces($request->date);

        return response()->json([
            'status' => true,
            'date' => $request->date,
            'free_spaces' => $spaces
        ]);
    }

    public function checkPrices(Request $request): JsonResponse
    {
        $validateCheck = Validator::make($request->all(),
            [
                'date' => 'required|date_format:Y-m-d|after:today',
            ]);

        if($validateCheck->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validateCheck->errors()
            ], 400);
        }

        $price = Price::select('price')
            ->where('valid_from', '<=', $request->date)
            ->where('valid_to', '>', $request->date)
            ->orderBy('valid_from', 'desc')
            ->value('price');

        $minimum_price = config('booking.minimum_price');
        if(null === $price || $price < $minimum_price)
        {
            $price = $minimum_price;
        }

        return response()->json([
            'status' => true,
            'date' => $request->date,
            'price' => $price
        ]);
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $days = $request->input('days');
        $from = $request->input('booking_from');

        if (!BookingService::areDaysAvailable($from, $days))
        {
            return response()->json([
                'status' => false,
                'message' => "Booking cannot be created due to lack of availability.",
            ], 400);
        }

        $booking = Auth::user()->bookings()->create();

        $booking_date = \DateTimeImmutable::createFromFormat('Y-m-d', $from);
        for ($i = 0; $i < $days; $i++) {
            $booking->spaces()->create(['booking' => $booking_date->add(new \DateInterval('P'.$i.'D'))]);
        }

        $booking = Booking::find($booking->id);

        return response()->json([
            'status' => true,
            'message' => "Booking created successfully!",
            'booking' => $booking
        ], 200);
    }

    public function show(Booking $booking): JsonResponse
    {
        if(Auth::user()->id != $booking->user_id)
        {
            return response()->json([
                'status' => false,
                'message' => "Booking does not belong to the user.",
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => "Your current booking.",
            'booking' => $booking
        ], 200);
    }

    public function update(StoreBookingRequest $request, Booking $booking): JsonResponse
    {
        if(Auth::user()->id != $booking->user_id)
        {
            return response()->json([
                'status' => false,
                'message' => "Booking does not belong to the user.",
            ], 403);
        }

        $days = $request->input('days');
        $from = $request->input('booking_from');

        if (!BookingService::areDaysAvailable($from, $days))
        {
            return response()->json([
                'status' => false,
                'message' => "Booking cannot be created due to lack of availability.",
            ], 400);
        }

        $booking->spaces()->delete();

        $booking_date = \DateTimeImmutable::createFromFormat('Y-m-d', $from);
        for ($i = 0; $i < $days; $i++) {
            $booking->spaces()->create(['booking' => $booking_date->add(new \DateInterval('P'.$i.'D'))]);
        }

        $booking = Booking::find($booking->id);

        return response()->json([
            'status' => true,
            'message' => "Booking updated successfully!",
            'booking' => $booking
        ], 200);
    }

    public function destroy(Booking $booking): JsonResponse
    {
        if(Auth::user()->id != $booking->user_id)
        {
            return response()->json([
                'status' => false,
                'message' => "Booking does not belong to the user.",
            ], 403);
        }
        $booking->delete();

        return response()->json([
            'status' => true,
            'message' => "Booking deleted successfully!",
        ], 200);
    }
}
