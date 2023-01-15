<?php

namespace App\Http\Requests;

class StoreBookingRequest extends JsonRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "booking_from" => "required|date_format:Y-m-d|after:today",
            "days"   => "required|int|min:1",
        ];
    }
}
