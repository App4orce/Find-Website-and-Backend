<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class WhistlistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $merchantIds = DB::table('merchants')->pluck('id')->toArray();
        $dataToSeed = [];
        for ($i = 0; $i < 10; $i++) {
            // Generate a random data item
            $dataItem = [
                'user_id' => 1,
                'user_to' => 1,
               
            ];

            $dataToSeed[] = $dataItem;
        }
        DB::table('whistlists')->insert($dataToSeed);
    }
}
