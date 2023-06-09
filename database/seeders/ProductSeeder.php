<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);

        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);

        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);

        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);
        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);
        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);
        Product::create([
            'name' =>   $faker->name,
            'image' =>   'product.jpg',
            'user_id' => 1,
            'description' =>   $faker->text(),
            'status' => 1
        ]);
        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);
        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);
        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);
        Product::create([
            'name' =>   $faker->name,
            'image' =>   'product.jpg',
            'user_id' => 1,
            'description' =>   $faker->text(),
            'status' => 1
        ]);
        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);
        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);
        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);
        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);
        Product::create([
            'name' =>   $faker->name,
            'user_id' => 1,
            'image' =>   'product.jpg',
            'description' =>   $faker->text(),
            'status' => 1
        ]);
    }
}
