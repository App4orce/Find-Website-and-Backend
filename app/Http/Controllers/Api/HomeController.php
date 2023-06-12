<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Slider;
use App\Models\Wishlist;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Auth;
use DB;
use Validator;

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

            $favaouriteMerchant = User::find(1)
                ->whereHas('wishlist', function ($query) {
                    $query->whereNotNull('user_to');
                })
                ->with('wishlist.restaurant')
                ->get()
                ->pluck('wishlist.*.restaurant')
                ->flatten() // Flatten the nested array
                ->map(function ($restaurant) {
                    return [
                        'name' => $restaurant['name'],
                        'profile_image' =>  asset('public/profile_image/' . $restaurant['profile_image']),
                    ];
                });


            $restaurantCategories = Category::where('type', 'restaurants')
                ->get()
                ->map(function ($category) {
                    $category->image = asset('category/public/' . $category->image);
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'image' => asset('category/public/' . $category->image),
                    ];
                });

            $storeCategories = Category::where('type', 'store')
                ->get()
                ->map(function ($category) {
                    $category->image = asset('category/public/' . $category->image);
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'image' => asset('category/public/' . $category->image),
                    ];
                });




            $data = [
                'slider' => $slides,
                'favaouriteMerchant' =>   $favaouriteMerchant,
                'restaurants' =>   $restaurantCategories,
                'stores' =>   $storeCategories,

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

    //search

    public function Search(Request $request)
    {
        try {
            $rules = array(

                'search'  => 'required',
                'city'  => 'required',


            );
            $messages = [
                'search.required' => 'search field is required',
                'city.required' => 'please select location',

            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $msg = $messages[0];
                return response()->json([
                    'status' => false,
                    'code' => 404,
                    'data' => [],
                    'message' => $msg
                ], 404, [], JSON_FORCE_OBJECT);
            } else {
                $response = User::with('city')
                    ->select('users.id', 'name', 'profile_image', 'city_id')
                    ->where(function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%')
                            ->where('city_id', $request->city);
                    });

                if ($request->type == "store") {
                    $response->where('role', 3);
                } elseif ($request->type == "merchant") {
                    $response->where('role', 2);
                }

                $response = $response->whereIn('role', [2, 3])
                    ->get()
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'profile_image' => asset('public/profile_image/' . $user->profile_image),
                        ];
                    });

                $city = City::find($request->city);

                $data = [
                    'data' => [
                        'city' => ($city) ? ['name' => $city->name_ar] : null,
                        'searchResult' => $response,
                    ],
                ];

                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' =>  $data,
                    'message' => 'search listing'
                ], 200);
            }
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

    //get Resturant by Category

    public function getResturantByCategory(Request $request)
    {
        try {
            $rules = array(

                'category_id'  => 'required',
                'city'  => 'required',


            );
            $messages = [
                'category_id.required' => 'category id  required',
                'city.required' => 'please select location',

            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $msg = $messages[0];
                return response()->json([
                    'status' => false,
                    'code' => 404,
                    'data' => [],
                    'message' => $msg
                ], 404, [], JSON_FORCE_OBJECT);
            } else {
                $response = User::with('city')
                    ->select('name', 'profile_image', 'city_id')
                    ->where(function ($query) use ($request) {
                        $query->where('category_id', $request->category_id)
                            ->where('city_id', $request->city);
                    })
                    ->whereIn('role', [2])
                    ->get()
                    ->map(function ($user) {
                        return [
                            'name' => $user->name,
                            'profile_image' => asset('public/profile_image/' . $user->profile_image),

                        ];
                    });

                $city = City::find($request->city);

                $data = [
                    'data' => [
                        'city' => ($city) ? ['name' => $city->name_ar] : null,
                        'searchResult' => $response,
                    ],
                ];

                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' =>  $data,
                    'message' => 'search listing'
                ], 200);
            }
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

    //get Stores By Category

    public function getStoresByCategory(Request $request)
    {
        try {
            $rules = array(

                'category_id'  => 'required',
                'city'  => 'required',


            );
            $messages = [
                'category_id.required' => 'category id  required',
                'city.required' => 'please select location',

            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $msg = $messages[0];
                return response()->json([
                    'status' => false,
                    'code' => 404,
                    'data' => [],
                    'message' => $msg
                ], 404, [], JSON_FORCE_OBJECT);
            } else {
                $response = User::with('city')
                    ->select('name', 'profile_image', 'city_id')
                    ->where(function ($query) use ($request) {
                        $query->where('category_id', $request->category_id)
                            ->where('city_id', $request->city);
                    })
                    ->whereIn('role', [3])
                    ->get()
                    ->map(function ($user) {
                        return [
                            'name' => $user->name,
                            'profile_image' => asset('public/profile_image/' . $user->profile_image),

                        ];
                    });

                $city = City::find($request->city);

                $data = [
                    'data' => [
                        'city' => ($city) ? ['name' => $city->name_ar] : null,
                        'searchResult' => $response,
                    ],
                ];

                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' =>  $data,
                    'message' => 'search listing'
                ], 200);
            }
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

    public function getMerchantDetail(Request $request)
    {
        try {
            $rules = array(

                'id'  => 'required',   //merchant id



            );
            $messages = [
                'id.required' => 'merchant id  required',


            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $msg = $messages[0];
                return response()->json([
                    'status' => false,
                    'code' => 404,
                    'data' => [],
                    'message' => $msg
                ], 404, [], JSON_FORCE_OBJECT);
            } else {
                return $merchants = User::find($request->id)->with(['categories.products' => function ($query) {
                    $query->select('id', 'name', 'image', 'description', 'price', 'merchant_category_id'); // Select the desired columns from the products table
                }])
                    ->where('role', 2)
                    ->get()
                    ->map(function ($merchant) {
                        $categories = $merchant->categories->map(function ($category) {
                            return [
                                'id' => $category->id,
                                'name' => $category->category_name,
                                'products' => $category->products,
                            ];
                        });

                        return [
                            'merchant_detail' => [
                                'name' => $merchant->name,
                                'profile_image' => asset('public/profile_image/' . $merchant->profile_image),
                                'min_order' => number_format($merchant->min_order, 2)
                            ],
                            'categories' => $categories,
                        ];
                    });
                // $data = [
                //     'merchant_detail' => [
                //         'name' => $merchant->name,
                //         'profile_image' => $merchant->profile_image,
                //     ],
                //     'categories' => $categories,
                //     'first_category_products' => $firstCategoryProducts
                // ];

                // return response()->json([
                //     'status' => true,
                //     'code' => 200,
                //     'data' =>  $data,
                //     'message' => 'search listing'
                // ], 200);
            }
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

    public function getProductSubItems(Request $request)
    {
        try {
            $rules = array(

                'id'  => 'required',   //product id



            );
            $messages = [
                'id.required' => 'Product id  required',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $msg = $messages[0];
                return response()->json([
                    'status' => false,
                    'code' => 404,
                    'data' => [],
                    'message' => $msg
                ], 404, [], JSON_FORCE_OBJECT);
            } else {
                if (Product::where('id', $request->id)->exists()) {
                    $product =  Product::with('subItems')->find($request->id);
                    $subItems = $product->subItems;
                    $data = [
                        'subitems' => $subItems
                    ];

                    return response()->json([
                        'status' => true,
                        'code' => 200,
                        'data' =>  $data,
                        'message' => 'subitem listing'
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'code' => 404,
                        'data' =>  [],
                        'message' => 'subitem listing'
                    ], 404, [], JSON_FORCE_OBJECT);
                }
            }
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
