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
            'type' => 'restaurants'
        ]);

        Category::create([
            'image' => $faker->imageUrl(),
            'name' =>  'Piza',
            'type' => 'restaurants'
        ]);

        
        Category::create([
            'image' => $faker->imageUrl(),
            'name' =>  'Sea Food',
            'type' => 'restaurants'
        ]);


        
        Category::create([
            'image' => $faker->imageUrl(),
            'name' =>  'Fine Dining',
            'type' => 'restaurants'
        ]);

        
        Category::create([
            'image' => $faker->imageUrl(),
            'name' =>  'Indian',
            'type' => 'restaurants'
        ]);

        Category::create([
            'image' => $faker->imageUrl(),
            'name' =>  'Well-Being',
            'type' => 'store'
        ]);


        Category::create([
            'image' => $faker->imageUrl(),
            'name' =>  'Gifts',
            'type' => 'store'
        ]);


        Category::create([
            'image' => $faker->imageUrl(),
            'name' =>  'Home & Fashion',
            'type' => 'store'
        ]);


      

        

       
    }
}
