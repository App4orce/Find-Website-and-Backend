<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
class HomeController extends Controller
{
    public function index()
    {
        try {
            $slides  = Slider::select('image')->get();
            $slides = Slider::pluck('image')->map(function ($image) {
               
                return [
                    'image' => asset('public/slider/' . $image),

                ];
            });
      
            
            $categories = Category::all()->map(function ($category) {
                $category->image = asset('category/public/' . $category->image);
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => asset('category/public/' . $category->image),
                ];
            });
             

                 

            $data = [
                'slider' => $slides,
                'categories' =>   $categories,
            ];

            return response()->json([
                'status' => true,
                'code' => 200,
                'data' =>  $data,
                'message' => 'home listing'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something Wrong',
                'sql_error' => $th->getMessage()

            ], 500, [], JSON_FORCE_OBJECT);
        }
    }
}
