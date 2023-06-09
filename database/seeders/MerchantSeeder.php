<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Hash;
class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        User::create([
            'name' => $faker->name,
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'status' => 1,
            'online_status' => 1,
            'longitude' => '3333',
            'latitude' => '33333',
            'role' => 2,
            'profile_image'=> 'merchant.jpg',
            'phone' => $faker->phoneNumber(),
            'password' => Hash::make('12345678'),
            'email' => $faker->email
        ]);

        
    }
}
