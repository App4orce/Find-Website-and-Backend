<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use DB;
use Validator;

class ProviderController extends Controller
{
  public function onlineStatus(Request $request)
  {
    try {
      $rules = array(

        'userId'  => 'required',
        'status'  => 'required',


      );
      $messages = [
        'userId.required' => 'userid is required',
        'status.required' => 'status is required',

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
        if (User::where('id', $request->userId)->exists()) {
          User::where('id', $request->userId)->update([
            'online_status' => $request->status
          ]);
          return response()->json([
            'status' => true,
            'code' => 200,
            'data' =>  [],
            'message' => 'Status changed successfully'
          ], 200, [], JSON_FORCE_OBJECT);
        } else {
          return response()->json([
            'status' => false,
            'code' => 404,
            'data' => [],
            'message' => 'User not found',
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

  // send new order to all provider that are in 1km

  public function sendOrderToProvider(Request $request)
  {
    try {
      $rules = array(

        'latitude'  => 'required',
        'longitude'  => 'required',


      );
      $messages = [
        'latitude.required' => 'latitude is required',
        'longitude.required' => 'longitude is required',

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
        $restaurantLatitude = '';
        $restaurantLongitude = '';

        // Find providers within 1km of the restaurant
        $providers = User::select('id', 'name', 'latitude', 'longitude')
          ->selectRaw('(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$restaurantLatitude, $restaurantLongitude, $restaurantLatitude])
          ->where('role', 4)  // Filter by role equal to 4
          ->where('isFree', 0)  // Filter by isFree equal to 0
          ->having('distance', '<=', 1) // Filter by distance within 1km
          ->get();

        foreach ($providers as $provider) {
          echo $provider->name . ' - Distance: ' . $provider->distance . ' km';
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

  // accept order by provider
  public function acceptOrder(Request $request)
  {

    try {
      $rules = array(

        'orderId'  => 'required',
      );
      $messages = [
        'orderId.required' => 'orderId is required',
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
        if (Order::where('id', $request->orderId)->exists()) {
          Order::where('id', $request->orderId)->update([
            'provider_id' => Auth::user()->id,
            'status' => 1,
          ]);

          User::where('id', $request->orderId)->update([
            'provider_id' => Auth::user()->id,
            'status' => 1,
          ]);

          return response()->json([
            'status' => true,
            'code' => 200,
            'data' =>  [],
            'message' => 'Order accepted successfully'
          ], 200, [], JSON_FORCE_OBJECT);
        } else {
          return response()->json([
            'status' => false,
            'code' => 404,
            'data' => [],
            'message' => 'Order not found',
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

  //discard order

  public function discardOrder(Request $request)
  {
    try {
      $rules = array(

        'orderId'  => 'required',
        'providerId'  => 'required',


      );
      $messages = [
        'orderId.required' => 'order id required',
        'providerId.required' => 'Provider required',
      ];

      $validator = Validator::make($request->all(), $rules, $messages);
      if ($validator->fails()) {
        $messages = $validator->errors()->all();
        $msg = $messages[0];
        return response()->json(['status' => false, 'code' => 401, 'data' => [], 'message' => $msg], 401, [], JSON_FORCE_OBJECT);
      } else {
        if (DB::table('discard_orders')->where('order_id', $request->orderId)->where('provider_id', $request->providerId)->exists()) {
          return response()->json([
            'status' => false,
            'code' => 401,
            'data' => [],
            'message' => 'You already discard order',
          ], 500, [], JSON_FORCE_OBJECT);
        }

        DB::table('discard_orders')->insert([
          'order_id' => $request->orderId,
          'provider_id' => $request->providerId,
          'created_at' => date('Y-m-d H:i:s'),
          'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return response()->json([
          'status' => true,
          'code' => 200,
          'data' => [],
          'message' => 'Order Discarded',
        ], 200, [], JSON_FORCE_OBJECT);
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
