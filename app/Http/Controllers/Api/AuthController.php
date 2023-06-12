<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $rules = array(
            'phone' => ['required', 'max:10', 'regex:/(05)[0-9]{8}/', 'not_regex:/[a-z]/'],
            'password' => 'required|min:8',
          

        );
        $messages = [
            'phone.required' => 'Phone is required',
            'password.required' => 'Password is required',
            'password.min' => 'Password should 8 characters long',
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
            $user = User::select('id', 'name', 'email', 'first_name', 'last_name', 'status', 'online_status','password')->where('phone', $request->phone)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'staus' => false,
                    'code' => 404,
                    'data' => [],
                    'message' => ['These credentials do not match our records.']
                ], 404, [], JSON_FORCE_OBJECT);
            }
            $user->profile_image = env('APP_URL') . '/public/profile_image/' . $user->profile_image;
            // User::where('id', $user->id)->update([
            //     'fcm_token' => $request->fcm_token
            // ]);

            $token = $user->createToken('my-app-token')->plainTextToken;
            $data = [
                'user' => $user
            ];
            $response = [
                'status' => true,
                'code' => 200,
                'data' => $data,
                'token' => $token,
                'message' => 'Loged In successfully'
            ];

            return response()->json($response, 200);
        }
    }

    public function register(Request $request)
    {
        try {
            $name = '';

            $rules = array(
                'phone' => ['required', 'max:10', 'regex:/(05)[0-9]{8}/', 'not_regex:/[a-z]/', 'unique:users'],
                'email'  => 'required|email|unique:users',
                'first_name'  => 'required',
                'last_name'  => 'required',
                'profile_image'  => 'required|image|mimes:jpeg,png,jpg',
                'password' => 'required|min:8',
                'latitude' =>  'required',
                'longitude' =>  'required',

            );
            $messages = [
                'phone.required' => 'Phone is required',
                'phone.unique' => 'Phone is already taken',
                'password.required' => 'Password is required',
                'password.min' => 'Password should 8 characters long',
                'first_name.required' => 'First name is required',
                'last_name.required' => 'Last name is required',
                'password.confirmed' => 'Password does not match',
                'latitude.required' => 'Latitude is required',
                'longitude.required' => 'Longitude is required',
                'phone.regex' => 'The phone format is invalid. The correct phone format is 05xxxxxxxx',
                'profile_image.required' => 'Profile image is required',
                'profile_image.mimes' => 'jpg,jpeg,png files are allowed',
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
                //1=>user
                //2=>merchant
                //3 =>store
                //4=>delivery provider
                //5=>delivery provider partner bussniess
                $user = new User();
                if ($request->hasFile('profile_image')) {
                    $image = $request->file('profile_image');
                    $name = time() . '.' . $image->getClientOriginalExtension();
                    $destinationPath = public_path('/profile_image');
                    $image->move($destinationPath, $name);
                }
                $user->phone = $request->phone;
                $user->name =  $request->first_name . ' ' .  $request->last_name;
                $user->first_name =  $request->first_name;
                $user->last_name =  $request->last_name;
                $user->password = Hash::make($request->password);
                $user->role = 1;
                $user->status = 1;
                $user->profile_image = $name;
                $user->email = $request->email ? $request->email : '';
                $user->latitude = $request->latitude ? $request->latitude : '';
                $user->longitude = $request->longitude ? $request->longitude : '';
                // $user->fcm_token = $request->fcm_token ? $request->fcm_token : '';
                $user->save();
                // $otp = rand(10000, 99999);
                // User::where('phone', $request->phone)->update(['otp' => $otp]);
                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' => [],
                    'message' => 'Your account is created successfully'
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
