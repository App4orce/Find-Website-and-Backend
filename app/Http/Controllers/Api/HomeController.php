<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Slider;
use App\Models\Wishlist;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\DeliveryAddress;
use App\Models\Product;
use App\Models\Support;
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
                ->limit(5)
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

    public function getCartItems(Request $request)
    {
        try {
            $user = Auth::user();
            $carts = $user->cart()->with('cartItems.product', 'restaurant')->get();

            $responseData = [];
            foreach ($carts as $cart) {
                $cartItems = $cart->cartItems;
                $restaurant = $cart->restaurant;

                $restaurantDetails = [
                    'name' => $restaurant->name,
                    'image' => url('public/profile_image/' . $restaurant->profile_image),
                    'item_count' => $cartItems->count() // Add item count to the restaurant details
                    // Add more restaurant details as needed
                ];

                $itemDetails = $cartItems->pluck('product')->map(function ($product) {
                    return [
                        'name' => $product->name,
                        'price' => $product->price,
                        'description' => $product->description,
                        // Add more product details as needed
                    ];
                })->toArray();

                $responseData[] = [
                    'restaurant_details' => $restaurantDetails,
                    'items' => $itemDetails,
                ];
            }

            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => $responseData,
                'message' => 'Cart items listed successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
                'sql_error' => $th->getMessage()
            ], 500);
        }
    }




    public function addCart(Request $request)
    {
        try {
            $user = Auth::user();
            $productMerchantId = $request->input('merchant_id');
            $cart = Cart::where('user_id', $user->id)->where('merchant_id', $productMerchantId)->first();

            // If the user doesn't have a cart for this merchant, create a new one
            if (!$cart) {
                $cart = new Cart();
                $cart->user_id = $user->id;
                $cart->merchant_id = $productMerchantId;
                $cart->save();
            }

            $cartItem = CartItem::where('cart_id', $cart->id)->where('product_id', $request->product_id)->first();
            if ($cartItem) {
                return response()->json([
                    'status' => false,
                    'code' => 400,
                    'data' => [],
                    'message' => 'Item already exists in the cart',
                ], 400);
            } else {
                $cartItem = new CartItem();
                $cartItem->cart_id = $cart->id;
                $cartItem->product_id = $request->input('product_id');
                $cartItem->quantity = $request->input('quantity');
                $cartItem->save();
            }

            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => [],
                'message' => 'Item added to cart successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
                'sql_error' => $th->getMessage()
            ], 500);
        }
    }

    public function addSupport(Request $request)
    {
        try {
            $rules = array(

                'name'  => 'required',
                'email'  => 'required',
                'phone'  => 'required',
                'comments'  => 'required',
            );
            $messages = [
                'name.required' => 'name is  required',
                'email.required' => 'email is  required',
                'phone.required' => 'phone is  required',
                'comments.required' => 'comment is  required',
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
                $support = new Support();
                $support->name = $request->name;
                $support->user_id = Auth::user()->id;
                $support->email = $request->email;
                $support->phone = $request->phone;
                $support->comments = $request->comments;
                $support->save();
                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' => [],
                    'message' => 'Your request has sent successfully',
                ], 200, [], JSON_FORCE_OBJECT);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
                'sql_error' => $th->getMessage()
            ], 500);
        }
    }

    public function deleteAcount()
    {
        try {
            User::where('id', Auth::user()->id)->delete();
            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => [],
                'message' => 'User Deleted',
            ]);
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

    public function addAddess(Request $request)
    {
        try {
            $rules = array(

                'latitude'  => 'required',
                'longitude'  => 'required',
                'location'  => 'required',
                'address'  => 'required',
            );
            $messages = [
                'latitude.required' => 'latitude is  required',
                'longitude.required' => 'longitude is  required',
                'location.required' => 'location is  required',
                'address.required' => 'address is  required',
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
                $address = new DeliveryAddress();
                $address->location = $request->location;
                $address->user_id = Auth::user()->id;
                $address->longitude = $request->longitude;
                $address->latitude = $request->latitude;
                $address->address = $request->address;
                $address->save();
                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' => [],
                    'message' => 'Your adsress has added successfully',
                ], 200, [], JSON_FORCE_OBJECT);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
                'sql_error' => $th->getMessage()
            ], 500);
        }
    }

    public function getAddress()
    {
        try {
            $adress = User::find(Auth::user()->id)->address;
            $data = [
                'addreses' => $adress
            ];
            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => $data,
                'message' => 'Delivery address list get sussessfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
                'sql_error' => $th->getMessage()
            ], 500);
        }
    }

    public function deleteAddress(Request $request)
    {
        try {
            $rules = array(

                'id'  => 'required',

            );
            $messages = [
                'id.required' => 'address id  required',
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
                if (DeliveryAddress::where('id', $request->id)->exists()) {
                    DeliveryAddress::find($request->id)->delete();
                    return response()->json([
                        'status' => true,
                        'code' => 200,
                        'data' => [],
                        'message' => 'Address deleted successfully',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'code' => 400,
                        'data' => [],
                        'message' => 'No address found',
                    ], 400);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
                'sql_error' => $th->getMessage()
            ], 500);
        }
    }

    public function getFavMerchants(Request $request)
    {
        try {
            $favaouriteMerchant = User::find(Auth::user()->id)
                ->whereHas('wishlist', function ($query) {
                    $query->whereNotNull('user_to');
                })
                ->with('wishlist.restaurant')
                ->limit(5)
                ->get()
                ->pluck('wishlist.*.restaurant')
                ->flatten() // Flatten the nested array
                ->map(function ($restaurant) {
                    return [
                        'name' => $restaurant['name'],
                        'profile_image' =>  asset('public/profile_image/' . $restaurant['profile_image']),
                    ];
                });
            $data = [
                'favaouriteMerchant' =>  $favaouriteMerchant
            ];
            return response()->json([
                'status' => true,
                'code' => 200,
                'data' =>  $data,
                'message' => 'get favourite Merchant',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
                'sql_error' => $th->getMessage()
            ], 500);
        }
    }

    
    public function updateProfile(Request $request)
    {
        try {
            $name = '';

            $rules = array(
                'profile_image'  => 'required|image|mimes:jpeg,png,jpg',
                'name' => 'sometimes|required',
                'email' => 'sometimes|required',

            );
            $messages = [
                'name.required' => 'name is required',
                'email.required' => 'email is required',
                'profile_image.required' => 'profile image is required',

            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $msg = $messages[0];
                return response()->json([
                    'status' => false,
                    'code' => 401,
                    'data' => [],
                    'message' => $msg
                ], 401, [], JSON_FORCE_OBJECT);
            } else {
                $input = $request->except(['_method']);
                if (!empty($input['name'])) {
                    $input['name'] = $input['name'];
                }
                if (!empty($input['email'])) {
                    $input['email'] = $input['email'];
                }
            

                if ($request->hasFile('profile_image')) {
                    $image = $request->file('profile_image');
                    $name = time() . '.' . $image->getClientOriginalExtension();
                    $destinationPath = public_path('/profile_image');
                    $image->move($destinationPath, $name);
                    $input['profile_image'] = $name;
                }
                User::where('id', Auth::user()->id)->update($input);



                $message = "Your profile updated successfully";


                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' => [],
                    'message' => $message
                ], 200, [], JSON_FORCE_OBJECT);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something Wrong',

            ], 500, [], JSON_FORCE_OBJECT);
        }
    }
}
