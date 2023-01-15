# Airport Parking test 

## Installation instructions

First, install the package dependencies

```bash
composer install
```

You can either run the project with the in-built webserver, or in docker, 
I used the latter in development:

```bash
./vendor/bin/sail up
```

You can prefix the following commands with the sail executable or go into the command line of the docker machine:

```bash
./vendor/bin/sail bash
```

Last step before the application is working to run the database migrations **and** seed
```bash
php artisan migrate:fresh --seed
```

To run the testsuite use:
```bash
php artisan test
```

## Endpoints usage

### User registration

```http
POST /register HTTP/1.1
Host: localhost
Content-Type: application/json
```
> ### Request form parameters 

| `name` | _string_ <br>
| `email` | _string_ | must be a valid email<br>
| `password` | _string_<br>

> ### Successful Response Example

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=UTF-8

{
    "status": true,
    "message": "User Created Successfully",
    "token": "2|c63LK0iCQ12Es7lLJ6HJZwwLZHB1oCfBsFyKokz9"
}
```

Use the token from registration in Bearer Authorization to get access to the other API funtions. 

```http
POST /login HTTP/1.1
Host: localhost
Content-Type: application/json
```

> ### Request form parameters

| `email` | _string_ | must be a valid email<br>
| `password` | _string_<br>

> ### Successful Response Example

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=UTF-8

{
    "status": true,
    "message": "User Logged In Successfully",
    "token": "3|lV9hl72MwmTkFaaOynP4bZiD0LlXdPrpWs9ObbD2"
}
```

#### List all bookings belongs to your user

```http
GET /bookings HTTP/1.1
Host: localhost
Content-Type: application/json
Authorization: Bearer 3|lV9hl72MwmTkFaaOynP4bZiD0LlXdPrpWs9ObbD2
```

> ### Successful Response Example

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=UTF-8
{
    "status": true,
    "bookings": [
        {
            "id": 1,
            "user_id": 1,
            "created_at": "2023-01-15T13:52:26.000000Z",
            "updated_at": "2023-01-15T13:52:26.000000Z",
            "spaces": [
                {
                    "id": 1,
                    "booking_id": 1,
                    "booking": "2023-03-16",
                    "created_at": "2023-01-15T13:52:26.000000Z",
                    "updated_at": "2023-01-15T13:52:26.000000Z"
                },
                {
                    "id": 2,
                    "booking_id": 1,
                    "booking": "2023-03-17",
                    "created_at": "2023-01-15T13:52:26.000000Z",
                    "updated_at": "2023-01-15T13:52:26.000000Z"
                }
            ]
        }
    ]
}

```

#### Create new booking

```http
POST /bookings HTTP/1.1
Host: localhost
Content-Type: application/json
Authorization: Bearer 3|lV9hl72MwmTkFaaOynP4bZiD0LlXdPrpWs9ObbD2
```

> ### Request object example

```json
{
  "booking_from": "2023-03-16", 
  "days": 2
}
```

| `booking_from` | _date_ | in form of Y-m-d<br>
| `days` | _int_ | Minimum 1<br>

> ### Successful Response Example

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=UTF-8
{
    "status": true,
    "message": "Booking created successfully!",
    "booking": {
        "id": 1,
        "user_id": 1,
        "created_at": "2023-01-15T13:52:26.000000Z",
        "updated_at": "2023-01-15T13:52:26.000000Z",
        "spaces": [
            {
                "id": 1,
                "booking_id": 1,
                "booking": "2023-03-16",
                "created_at": "2023-01-15T13:52:26.000000Z",
                "updated_at": "2023-01-15T13:52:26.000000Z"
            },
            {
                "id": 2,
                "booking_id": 1,
                "booking": "2023-03-17",
                "created_at": "2023-01-15T13:52:26.000000Z",
                "updated_at": "2023-01-15T13:52:26.000000Z"
            }
        ]
    }
}

```

#### Show one of your bookings by id

```http
GET /bookings/1 HTTP/1.1
Host: localhost
Content-Type: application/json
Authorization: Bearer 3|lV9hl72MwmTkFaaOynP4bZiD0LlXdPrpWs9ObbD2
```

> ### Successful Response Example

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=UTF-8
{
    "status": true,
    "message": "Your current booking.",
    "booking": {
        "id": 1,
        "user_id": 1,
        "created_at": "2023-01-15T13:52:26.000000Z",
        "updated_at": "2023-01-15T13:52:26.000000Z",
        "spaces": [
            {
                "id": 1,
                "booking_id": 1,
                "booking": "2023-03-16",
                "created_at": "2023-01-15T13:52:26.000000Z",
                "updated_at": "2023-01-15T13:52:26.000000Z"
            },
            {
                "id": 2,
                "booking_id": 1,
                "booking": "2023-03-17",
                "created_at": "2023-01-15T13:52:26.000000Z",
                "updated_at": "2023-01-15T13:52:26.000000Z"
            }
        ]
    }
}

```

#### Amend booking by id

```http
PATCH /bookings/1 HTTP/1.1
Host: localhost
Content-Type: application/json
Authorization: Bearer 3|lV9hl72MwmTkFaaOynP4bZiD0LlXdPrpWs9ObbD2
```

> ### Request object example

```json
{
  "booking_from": "2023-03-17", 
  "days": 3
}
```

| `booking_from` | _date_ | in form of Y-m-d<br>
| `days` | _int_ | Minimum 1<br>

> ### Successful Response Example

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=UTF-8
{
    "status": true,
    "message": "Booking updated successfully!",
    "booking": {
        "id": 1,
        "user_id": 1,
        "created_at": "2023-01-15T13:52:26.000000Z",
        "updated_at": "2023-01-15T13:52:26.000000Z",
        "spaces": [
            {
                "id": 3,
                "booking_id": 1,
                "booking": "2023-03-17",
                "created_at": "2023-01-15T13:55:20.000000Z",
                "updated_at": "2023-01-15T13:55:20.000000Z"
            },
            {
                "id": 4,
                "booking_id": 1,
                "booking": "2023-03-18",
                "created_at": "2023-01-15T13:55:20.000000Z",
                "updated_at": "2023-01-15T13:55:20.000000Z"
            },
            {
                "id": 5,
                "booking_id": 1,
                "booking": "2023-03-19",
                "created_at": "2023-01-15T13:55:20.000000Z",
                "updated_at": "2023-01-15T13:55:20.000000Z"
            }
        ]
    }
}

```

#### Delete booking by id

```http
DELETE /bookings/1 HTTP/1.1
Host: localhost
Content-Type: application/json
Authorization: Bearer 3|lV9hl72MwmTkFaaOynP4bZiD0LlXdPrpWs9ObbD2
```

> ### Successful Response Example

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=UTF-8
{
    "status": true,
    "message": "Booking deleted successfully!"
}

```

#### Check availability by date

```http
GET /bookings-check?date=2023-01-25 HTTP/1.1
Host: localhost
Content-Type: application/json
Authorization: Bearer 3|lV9hl72MwmTkFaaOynP4bZiD0LlXdPrpWs9ObbD2
```

> ### Request query parameters

| `date` | _date_ | in Y-m-d format<br>

> ### Successful Response Example

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=UTF-8
{
    "status": true,
    "date": "2023-01-29",
    "free_spaces": 10
}

```
#### Check prices by date

```http
GET /price-check?date=2023-01-25 HTTP/1.1
Host: localhost
Content-Type: application/json
Authorization: Bearer 3|lV9hl72MwmTkFaaOynP4bZiD0LlXdPrpWs9ObbD2
```

> ### Request query parameters

| `date` | _date_ | in Y-m-d format<br>

> ### Successful Response Example

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=UTF-8
{
    "status": true,
    "date": "2023-09-29",
    "price": 12
}

```
