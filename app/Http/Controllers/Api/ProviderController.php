<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\Support;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Validator;
use Illuminate\Support\Facades\Hash;

class ProviderController extends Controller
{
  public function goOnline(Request $request)
  {
    try {
      $rules = array(

        'userId'  => 'required',
      );
      $messages = [
        'userId.required' => 'userid is required',

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
            'online_status' => 1
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

  public function goOffline(Request $request)
  {
    try {
      $rules = array(

        'userId'  => 'required',

      );
      $messages = [
        'userId.required' => 'userid is required',

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
            'online_status' => 0
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

  public function sendOrderNotificationToProvider(Request $request)
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
          ->where('status', 1)  // Filter by isFree equal to 0
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
            'online_status' => 1,
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
        if (DB::table('discord_orders')->where('order_id', $request->orderId)->where('provider_id', $request->providerId)->exists()) {
          return response()->json([
            'status' => false,
            'code' => 401,
            'data' => [],
            'message' => 'You already discard order',
          ], 500, [], JSON_FORCE_OBJECT);
        }

        DB::table('discord_orders')->insert([
          'order_id' => $request->orderId,
          'provider_id' => $request->providerId,
          'created_at' => date('Y-m-d H:i:s'),
          'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return response()->json([
          'status' => true,
          'code' => 200,
          'data' => [],
          'message' => 'Order discarded succesfully',
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

  public function completeOrder(Request $request)
  {
    try {
      $rules = array(
        'orderId'  => 'required',
      );
      $messages = [
        'orderId.required' =>  'orderId is required',
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
        if (Order::where('id', $request->orderId)->exists()) {
          Order::where('id', $request->orderId)->update([
            'status' => 2 //complete
          ]);

          User::where('id', Auth::user()->id)->update([
            'isFree' => 0
          ]);

          return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [],
            'message' => 'Order Completed Successfully',
          ], 200, [], JSON_FORCE_OBJECT);
        }
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

  public function dashboardStats(Request $request)
  {
    try {
      $completedOrder = Order::where('provider_id', Auth::user()->id)->where('status', 2)->count();
      $onlineHours = Auth::user()->total_online_hours;

      //get order acceptnace rate
      // Retrieve the total number of orders
      $totalOrders = Order::where('provider_id', Auth::user()->id)->count();

      // Retrieve the number of accepted orders
      $acceptedOrders = Order::where('provider_id', Auth::user()->id)->where('status', 2)->count();

      // Calculate the acceptance percentage
      $acceptancePercentage = ($totalOrders > 0) ? ($acceptedOrders / $totalOrders) * 100 : 0;

      // Format the percentage value
      $formattedPercentage = round($acceptancePercentage, 2); // Round to 2 decimal places

      // Retrieve the ratings for the provider
      $ratings = Review::where('user_to', Auth::user()->id)->pluck('rate');

      // Calculate the average rating
      $averageRating = $ratings->avg() ?? 0;

      $data = [
        'completedOrder' => $completedOrder,
        'onlineHours' => $onlineHours,
        'acceptanceRate' => $formattedPercentage,
        'rating' =>  $averageRating
      ];

      return response()->json([
        'status' => true,
        'code' => 200,
        'data' => $data,
        'message' => 'Stats list',
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
  public function getDateWiseEarnings(Request $request)
  {
    try {
      $startDate = $request->input('start_date');
      $endDate = $request->input('end_date');
      $type = $request->input('type'); // Assuming 'type' parameter is passed with value 'week' or 'month'
      //->whereBetween('created_at', [$startDate, $endDate])
      $earnings = Order::select('order_number', 'created_at', 'total_amount')

        ->orderBy('created_at')
        ->get()
        ->groupBy(function ($item) use ($type) {
          $format = ($type === 'week') ? 'Y-W' : 'Y-m'; // Format based on type: 'week' or 'month'
          return Carbon::parse($item->created_at)->format($format);
        });

      $data = [
        'earnings' => $earnings
      ];

      return response()->json([
        'status' => true,
        'code' => 200,
        'data' => $data,
        'message' => 'get earning',
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


  public function updateProfile(Request $request)
  {
    try {
      $name = '';

      $rules = array(
        'profile_image'  => 'sometimes|required|image|mimes:jpeg,png,jpg',
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

  public function updateBankDetails(Request $request)
  {
    try {
      $rules = array(
        'bank_name' => 'required',
        'bank_no' => 'required|numeric|digits:14',

      );
      $messages = [
        'bank_name.required' =>  'bank name required',
        'bank_no.required' => 'bank no required',
        'bank_no.digits' => 'bank no should be 14 digits',


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
        if (!empty($input['bank_name'])) {
          $input['bank_name'] = $input['bank_name'];
        }
        if (!empty($input['bank_no'])) {
          $input['bank_no'] = $input['bank_no'];
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
        'message' => 'Something went wrong',
        'sql_error' => $th->getMessage(),
      ], 500, [], JSON_FORCE_OBJECT);
    }
  }

  public function changePassword(Request $request)
  {
    try {
      $rules = array(
        'old_password' => 'required',
        'new_password' => 'required|min:8|confirmed',

      );
      $messages = [
        'old_password.required' =>  'old password is required',
        'new_password.required' => 'new password is required',
        'new_password.min' => 'New password should be 8 character long',
        'new_password.confirmed' => 'password does not match',
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
        $user = Auth::user();
        $credentials = [
          'email' => $user->email,
          'password' => $request->input('old_password'),
        ];
        // Verify the old password
        if (!Hash::check($credentials['password'], $user->password)) {
          return response()->json([
            'status' => false,
            'code' => 400,
            'data' => [],
            'message' => 'Invalid old password'
          ], 401, [], JSON_FORCE_OBJECT);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();
        return response()->json([
          'status' => true,
          'code' => 200,
          'data' => [],
          'message' => 'Password Changed Successfully'
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
        $support->support_num = rand(10000, 99999);
        $support->type = "provider";
        $support->user_id = Auth::user()->id;
        $support->email = $request->email;
        $support->phone = $request->phone;
        $support->comments = $request->comments;
        $support->status = 0;
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
  public function complaints(Request $request)
  {
    try {
      $support = Support::select('comments', 'support_num', DB::raw("CONCAT(DATE_FORMAT(created_at, '%b %D'), ' at ', DATE_FORMAT(created_at, '%h:%i %p')) AS date"))->where('user_id', Auth::user()->id);
      if ($request->status == "inprogress") {
        $support->where('status', 0);
      }
      if ($request->status == "closed") {
        $support->where('status', 1);
      }

      $response = $support->get();

      $data = [
        'complaints' =>  $response
      ];

      return response()->json([
        'status' => true,
        'code' => 200,
        'data' => $data,
        'message' => 'get complaints successfully',
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

  public function autoAcceptanceOrder(Request $request)
  {
    try {
      $rules = array(
        'status'  => 'required',
      );
      $messages = [
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
        User::where('id', Auth::user()->id)->update([
          'auto_accpetance' => $request->status
        ]);
        return response()->json([
          'status' => true,
          'code' => 200,
          'data' => [],
          'message' => 'Status Changes Successfully',
        ], 200,[], JSON_FORCE_OBJECT);
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
}
