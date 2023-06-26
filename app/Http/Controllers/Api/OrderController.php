<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Auth;
use Validator;

class OrderController extends Controller
{
    public function placeOrder(Request $request)
    {
        try {
            $rules = array(
                'merchant_id'  => 'required',
                'cart_id'  => 'required',
            );
            $messages = [
                'merchant_id.required' => __('custommessage.id.merchant.required'),
                'cart_id.required' => __('custommessage.cart_id.required'),
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
                $order = new Order();
                $order->order_number =  rand(10000, 99999);
                $order->user_id =  Auth::user()->id;
                $order->merchant_id =   $request->merchant_id;
                $order->total_amount =   $request->total_amount;
                $order->status =   0;
                $order->save();

                // Store order items
                $orderItemsData = $request->input('order_items');

                if (!empty($orderItemsData)) {
                    foreach ($orderItemsData as $itemData) {
                        $orderItem = new OrderDetail();
                        $orderItem->order_id = $order->id;
                        $orderItem->product_id = $itemData['product_id'];
                        $orderItem->quantity = $itemData['quantity'];
                        $orderItem->price = $itemData['price'];
                        // Set other item attributes as needed
                        $orderItem->save();
                    }
                }

                Cart::where('id',$request->cart_id)->delete();

                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' => [],
                    'message' => 'Order Placed Successfully'
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



    public function orderDetail(Request $request)
    {
        try {
            $rules = array(
                'orderId'  => 'required',
            );
            $messages = [
                'orderId.required' =>  __('custommessage.orderId.required'),
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
                $order = Order::with('user', 'merchant.categories')
                    ->findOrFail($request->orderId);
                if ($order) {
                    $data = [
                        'order' => [
                            'total_amount' => $order->total_amount,
                            'merchant_profile_image' => asset('public/profile_image/' . $order->merchant->profile_image),
                            'merchant_categories' => $order->merchant->categories->pluck('category_name'),
                        ],
                    ];

                    return response()->json([
                        'status' => true,
                        'code' => 200,
                        'data' => $data,
                        'message' => 'order detail  get successfully'
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'code' => 404,
                        'data' => [],
                        'message' => 'no order found'
                    ], 404, [], JSON_FORCE_OBJECT);
                }
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

    public function activeOrder(Request $request)
    {
        try {
            $rules = array(
                'type'  => 'required',
            );
            $messages = [
                'type.required' => 'type is required',
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
                $orders = Order::with('merchant.categories')
                    ->where('user_id', Auth::user()->id)
                    ->orderBy('created_at', 'desc');

                if ($request->type == "past") {
                    $orders->where('status', 2);
                }

                if ($request->type == "active") {
                    $orders->where('status', 1);
                }

                $response = $orders->get();

                $mappedOrders = $response->map(function ($order) {
                    $mappedOrder = [
                        'id' => $order->id,
                        'total_amount' => $order->total_amount,
                        'status' => $order->status,
                        'created_at' => $order->created_at,
                        'profile_image' => asset('public/profile_image/' . $order->merchant->profile_image),
                        'categories' => $order->merchant->categories->pluck('category_name'),

                    ];

                    return $mappedOrder;
                });

                $data = [
                    'order' => $mappedOrders
                ];

                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' => $data,
                    'message' => 'order list get successfully'
                ], 200);
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

    public function getOrderWithDetails(Request $request)
    {

        try {
            $rules = array(
                'orderId'  => 'required',
            );
            $messages = [
                'orderId.required' =>  __('custommessage.orderId.required'),
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
                $order = Order::with('merchant.categories')->findOrFail($request->orderId);
                $products = $order->orderItems->map(function ($item) {
                    return $item->product;
                });

                $totalPrice = 0;
                $data = [
                    'order' => $order->only(['id', 'order_number', 'user_id', 'merchant_id', 'total_amount', 'status', 'created_at', 'updated_at']),
                    'merchant' => [
                        'id' => $order->merchant->id,
                        'name' => $order->merchant->name,
                        'profile_image' => asset('public/profile_image/' . $order->merchant->profile_image),
                        'merchant_categories' => $order->merchant->categories->pluck('category_name'),
                    ],

                    'products' => $order->orderItems->map(function ($item) use (&$totalPrice) {
                        $productSubtotal = $item->quantity * $item->product->price;
                        $subItemsPrice = $item->product->subItems->sum('price');
                        $subtotal = $productSubtotal + $subItemsPrice;
                        $totalPrice += $subtotal;
                        return [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'image' => asset('public/product/' . $item->product->image),
                            'price' => $item->product->price,
                            'description' => $item->product->description,
                            'quantity' => $item->quantity,
                            'sub_items' => $item->product->subItems->map(function ($subItem) {
                                return [
                                    'id' => $subItem->id,
                                    'name' => $subItem->name,
                                    'price' => $subItem->price,
                                ];
                            }),

                        ];
                    }),
                    'subtotal' => $totalPrice,
                ];

                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' => $data,
                    'message' => 'order get successfully'
                ], 200);
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

    public function courierPlaceOrder(Request $request)
    {
        try {
            $rules = array(
                'pickup_location'  => 'required',
                'dropoff_location'  => 'required',
                'total_amount'  => 'required'
            );
            $messages = [
                'dropoff_location.required' =>  __('custommessage.dropoff_location.required'),
                'pickup_location.required' =>  __('custommessage.pickup_location.required'),
                'total_amount' =>  __('custommessage.total_amount.required'),
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
                $order = new Order();
                $order->user_id = Auth::user()->id;
                $order->order_number =  rand(10000, 99999);
                $order->pickup_location = $request->input('pickup_location');
                $order->dropoff_location = $request->input('dropoff_location');
                $order->total_amount = $request->input('total_amount');
                $order->status =  0;
                $order->save();

                $items = $request->input('items');

                foreach ($items as $itemData) {
                    $item = new Item();
                    $item->name = $itemData['name'];
                    $item->image = $itemData['image'];
                    $order->items()->save($item);
                }

                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' => [],
                    'message' =>  __('custommessage.messagepPlacedOrder')
                ],200 , [] ,JSON_FORCE_OBJECT);
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
}
