<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        Category::create([
            'image' => $faker->imageUrl(),
            'name' =>  'Burgers',
        ]);

        Category::create([
            'image' => $faker->imageUrl(),
            'name' =>  'Piza',
        ]);

        
        Category::create([
            'image' => $faker->imageUrl(),
            'name' =>  'Sea Food',
        ]);


        
        Category::create([
            'image' => $faker->imageUrl(),
            'name' =>  'Fine Dining',
        ]);

        
        Category::create([
            'image' => $faker->imageUrl(),
            'name' =>  'Indian',
        ]);

        

       
    }
}
