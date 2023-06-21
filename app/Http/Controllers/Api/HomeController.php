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
use App\Models\Order;
use App\Models\Package;
use App\Models\Product;
use App\Models\Review;
use App\Models\Subscription;
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

            $favoriteMerchant = User::find(1)
                ->whereHas('wishlist', function ($query) {
                    $query->whereNotNull('user_to');
                })
                ->with(['wishlist.restaurant.category', 'reviews'])
                ->limit(5)
                ->get()
                ->pluck('wishlist.*.restaurant')
                ->flatten()
                ->map(function ($restaurant) {
                    $categories = $restaurant->category->pluck('category_name')->take(3);
                    $reviews = $restaurant->reviews;
                    $reviewCount = $reviews->count();
                    $averageRating = $reviews->avg('rate');
                    $reviewCountLabel = $reviewCount > 100 ? '100+' : $reviewCount;
                    return [
                        'id' => $restaurant['id'],
                        'name' => $restaurant['name'],
                        'profile_image' => asset('public/profile_image/' . $restaurant['profile_image']),
                        'category_name' =>  $categories,
                        'review_count' => $reviewCountLabel,
                        'average_rating' => $averageRating,
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
                'favaouriteMerchant' =>   $favoriteMerchant,
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
                'id.required' =>  __('custommessage.id.merchant.required'),
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
                $merchant = User::where('id', $request->id)
                    ->whereIn('role', [2, 3])
                    ->with([
                        'categories.products' => function ($query) {
                            $query->select('id', 'name', 'image', 'description', 'price', 'merchant_category_id');
                        },
                        'reviews'
                    ])
                    ->first();

                if (!$merchant) {
                    return response()->json([
                        'status' => false,
                        'code' => 404,
                        'data' => [],
                        'message' => __('custommessage.merchantnotfound')
                    ], 404, [], JSON_FORCE_OBJECT);
                }
                $reviewsCount = $merchant->reviews->count();
                $reviewsAvgRating = $merchant->reviews->avg('rate');
                $categories = $merchant->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->category_name,
                        'products' => $category->products,
                    ];
                });

                $data = [
                    'merchant_detail' => [
                        'name' => $merchant->name,
                        'profile_image' => asset('public/profile_image/' . $merchant->profile_image),
                        'min_order' => number_format($merchant->min_order, 2),
                        'reviews_count' => $reviewsCount,
                        'average_rating' => $reviewsAvgRating
                    ],
                    'categories' => $categories,

                ];

                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' =>  $data,
                    'message' => 'Get merchant detail'
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
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
                'id.required' =>  __('custommessage.id.product.required'),
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
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'image' => url('public/profile_image/' . $restaurant->profile_image),
                    'item_count' => $cartItems->count() // Add item count to the restaurant details
                    // Add more restaurant details as needed
                ];


                $subamount = $cartItems->sum(function ($item) {
                    return $item->quantity * $item->product->price;
                });

                $itemDetails = $cartItems->map(function ($product) {
                    return [
                        'id'=> $product->product->id,
                        'name' => $product->product->name,
                        'price' => $product->product->price,
                        'description' => $product->product->description,
                        'image' =>  asset('public/product/' . $product->product->image),
                        'quantity' => $product->quantity,
                        // Add more product details as needed
                    ];
                })->toArray();

                $deliveryfee = 0;
                $responseData[] = [
                    'id' => $cart->id,    // cart id
                    'restaurant_details' => $restaurantDetails,
                    'items' => $itemDetails,
                    'subamount' => $subamount, // Include the total amount in the response
                    'deliveryfee' => 0,
                    'total' => $deliveryfee + $subamount
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


    // get cart by id
    public function getCartById(Request $request)
    {
        try {
            $cart = Cart::with('cartItems.product', 'restaurant')->find($request->id);

            if (!$cart) {
                return response()->json([
                    'status' => false,
                    'code' => 404,
                    'data' => [],
                    'message' => 'Cart not found'
                ], 404);
            }

            $cartItems = $cart->cartItems;
            $restaurant = $cart->restaurant;

            $restaurantDetails = [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'image' => url('public/profile_image/' . $restaurant->profile_image),
                'item_count' => $cartItems->count() // Add item count to the restaurant details
                // Add more restaurant details as needed
            ];

            $subamount = $cartItems->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });

            $itemDetails = $cartItems->map(function ($product) {
                return [
                    'id'=> $product->product->id,
                    'name' => $product->product->name,
                    'price' => $product->product->price,
                    'description' => $product->product->description,
                    'quantity' => $product->quantity,
                    'image' =>  asset('public/product/' . $product->product->image),
                    // Add more product details as needed
                ];
            })->toArray();

            $deliveryfee = 0;
            $total = $deliveryfee + $subamount;

            $responseData = [
                'id' => $cart->id,    // cart id
                'restaurant_details' => $restaurantDetails,
                'items' => $itemDetails,
                'subamount' => $subamount,
                'deliveryfee' => $deliveryfee,
                'total' => $total
            ];

            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => $responseData,
                'message' => 'Cart item listed successfully'
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
                    'message' => __('custommessage.item_exist'),
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
                'message' =>  __('custommessage.item_added'),
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
                'name.required' => __('custommessage.name'),
                'email.required' => __('custommessage.email'),
                'phone.required' =>  __('custommessage.phone'),
                'comments.required' =>  __('custommessage.comments'),
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
                    'message' =>  __('custommessage.requestsent'),
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
                'message' =>   __('custommessage.deleteaccount')
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
                'latitude.required' => __('custommessage.latitude'),
                'longitude.required' =>  __('custommessage.longitude'),
                'location.required' =>  __('custommessage.location'),
                'address.required' =>  __('custommessage.address'),
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
                    'message' =>  __('custommessage.address'),
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
                'message' => __('custommessage.address.add'),
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
                'id.required' =>  __('custommessage.id.address.required'),
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
                        'message' => __('custommessage.address.delete'),
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'code' => 400,
                        'data' => [],
                        'message' => __('custommessage.address.notfound'),
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
                'name.required' => __('custommessage.name.profile.required'),
                'email.required' => __('custommessage.name.profile.email'),
                'profile_image.required' => __('custommessage.name.profile.profile_image'),

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



                $message = __('custommessage.profile.update');


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

    public function foodDelivery(Request $request)
    {
        try {

            $restaurants = User::where('role', 2)
                ->get()
                ->map(function ($restaurant) {
                    $restaurant->profile_image = asset('public/profile_image' . $restaurant->profile_image);
                    return [
                        'id' => $restaurant->id,
                        'name' => $restaurant->name,
                        'role' => $restaurant->role,
                        'image' => asset('public/profile_image' . $restaurant->profile_image),
                    ];
                });

            $data = [
                'restaurants' => $restaurants
            ];

            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => $data,
                'message' => 'Restaurant list retrieved successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
            ], 500, [], JSON_FORCE_OBJECT);
        }
    }

    public function stores(Request $request)
    {
        try {

            $restaurants = User::where('role', 3)
                ->get()
                ->map(function ($restaurant) {
                    $restaurant->profile_image = asset('public/profile_image' . $restaurant->profile_image);
                    return [
                        'id' => $restaurant->id,
                        'name' => $restaurant->name,
                        'role' => $restaurant->role,
                        'image' => asset('public/profile_image' . $restaurant->profile_image),
                    ];
                });

            $data = [
                'restaurants' => $restaurants
            ];

            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => $data,
                'message' => 'Restaurant list retrieved successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
            ], 500, [], JSON_FORCE_OBJECT);
        }
    }

    public function discounts(Request $request)
    {
        try {
            $usersWithDiscounts = User::whereHas('discounts')
                ->with(['discounts' => function ($query) {
                    $query->select('user_id', 'percentage');
                }])
                ->with(['wishlist' => function ($query) {
                    $query->select('user_id');
                }])
                ->whereIn('role', [2, 3])
                ->select('id', 'name', 'profile_image')
                ->get();
            $data = [
                'restaurants' => $usersWithDiscounts->map(function ($user) {
                    $user->discounts->makeHidden('user_id');
                    $user->wishlist_status = $user->wishlist->isNotEmpty();
                    unset($user->wishlist);
                    return $user;
                })
            ];
            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => $data,
                'message' => 'Restaurant list retrieved successfully'
            ], 200);
            return $usersWithDiscounts;
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
            ], 500, [], JSON_FORCE_OBJECT);
        }
    }

    public function rateOrder(Request $request)
    {
        try {
            $rules = array(
                'user_to'  => 'required',
                'review' => 'required',
                'rate' => 'required',

            );
            $messages = [
                'user_to.required' =>  __('custommessage.user_to.required'),
                'review.required' => __('custommessage.review.required'),
                'rate.required' => __('custommessage.rate.required'),

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

                $review = new Review();
                $review->user_id = Auth::user()->id;
                $review->user_to = $request->user_to;
                $review->review = $request->review;
                $review->rate = $request->rate;
                $review->save();
                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' => [],
                    'message' =>  __('custommessage.review.add'),
                ], 200, [], JSON_FORCE_OBJECT);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
            ], 500, [], JSON_FORCE_OBJECT);
        }
    }

    public function notifications(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications;
        $data = [
            'notifications' => $notifications
        ];

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $data,
            'message' => 'get notifications successfully'
        ], 200, [], JSON_FORCE_OBJECT);
    }

    public function getAllPackages(Request $request)
    {
        try {
            $packages = Package::with('benefits:package_id,benefit_name', 'restaurants:id,name,profile_image')->get();

            // Manipulate the packages data
            $manipulatedPackages = $packages->map(function ($package) {
                // Access the benefits relation
                $benefits = $package->benefits;
                $benefitNames = $benefits->pluck('benefit_name');
                $package->benefit_names = $benefitNames;

                // Access the restaurants relation
                $restaurants = $package->restaurants;
                $restaurantData = $restaurants->map(function ($restaurant) {
                    return [
                        'id' => $restaurant->id,
                        'name' => $restaurant->name,
                        'profile_image' => asset('public/profile_image/' . $restaurant->profile_image),
                    ];
                });
                $package->restaurants_data = $restaurantData;

                // Remove the original relations if not needed
                unset($package->benefits);
                unset($package->restaurants);

                return $package;
            });

            $data = [
                'packages' => $manipulatedPackages
            ];
            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => $data,
                'message' => 'Successfully retrieved and manipulated packages',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
                'sql_error' => $th->getMessage(),
            ], 500, [], JSON_FORCE_OBJECT);
        }
    }


    public function addSubscription(Request $request)
    {
        try {

            $rules = array(
                'package_id' => 'required',
                'amount' => 'required',

            );
            $messages = [
                'package_id.required' => __('custommessage.package_id.required'),
                'amount.required' =>  __('custommessage.amount'),

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

                $sub = new Subscription();
                $sub->user_id = Auth::user()->id;
                $sub->package_id = $request->package_id;
                $sub->amount = $request->amount;
                $sub->save();
                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' => [],
                    'message' => 'Subscription Added Successfully',
                ], 200, [], JSON_FORCE_OBJECT);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'data' => [],
                'message' => 'Something went wrong',
                'sql_error' => $th->getMessage(),
            ], 500, [], JSON_FORCE_OBJECT);
        }
    }
}
