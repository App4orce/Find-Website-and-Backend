<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class MerchantCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $merchantIds = DB::table('users')->where('role',2)->pluck('id')->toArray();
        $dataToSeed = [];
        // Generate random data for demonstration purposes
        for ($i = 0; $i < 10; $i++) {
            // Generate a random data item
            $dataItem = [
                'user_id' => $merchantIds[array_rand($merchantIds)],
                'category_name' => $faker->name,

            ];

            $dataToSeed[] = $dataItem;
        }

        // Step 4: Seed the data
        DB::table('merchant_categories')->insert($dataToSeed);
    }
}
