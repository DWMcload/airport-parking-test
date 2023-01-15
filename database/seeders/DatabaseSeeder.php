<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Price;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $price1 = new Price(['price' => 10, 'valid_from' => '2023-01-15', 'valid_to' => '2023-06-30']);
        $price1->save();
        $price2 = new Price(['price' => 15, 'valid_from' => '2023-07-01', 'valid_to' => '2023-09-01']);
        $price2->save();
        $price3 = new Price(['price' => 12, 'valid_from' => '2023-09-02', 'valid_to' => '2023-12-01']);
        $price3->save();
    }
}
