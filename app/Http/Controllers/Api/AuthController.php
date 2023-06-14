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


        );
        $messages = [
            'phone.required' => 'Phone is required',
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
            $user = User::select('id', 'name', 'email', 'first_name', 'last_name', 'status', 'online_status')->where('phone', $request->phone)->first();
            $otp = rand(10000, 99999);
            // User::where('phone', $request->phone)->update(['otp' => $otp]);
            if (!$user) {
                $user = new User();
                $user->phone = $request->phone;
                $user->role = 1;  // normal user
                $user->otp =  $otp;
                $user->save();
            }
            $user->profile_image = env('APP_URL') . '/public/profile_image/' . $user->profile_image;
            User::where('id', $user->id)->update([
                'otp' => $otp
            ]);

            //$token = $user->createToken('my-app-token')->plainTextToken;
            $data = [
                'otp' => $otp
            ];
            $response = [
                'status' => true,
                'code' => 200,
                'data' => $data,
                'message' => 'Otp send Successfuuly'
            ];

            return response()->json($response, 200);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $name = '';

            $rules = array(
                'otp' => ['required'],
                'phone' => ['required', 'max:10', 'regex:/(05)[0-9]{8}/', 'not_regex:/[a-z]/'],


            );
            $messages = [
                'otp.required' => 'OTP is required',
                'phone.required' => 'Phone is required',
                'phone.regex' => 'The phone format is invalid. The correct phone format is 05xxxxxxxx',

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
                $user = User::where('phone', $request->phone)->first();
                if ($user) {
                    if ($request->otp == $user->otp) {

                        User::where('id', $user->id)->update([
                            'otp' => null
                        ]);

                        $token = $user->createToken('my-app-token')->plainTextToken;
                        $data = [
                            'user' => $user,

                        ];

                        $response = [
                            'status' => true,
                            'code' => 200,
                            'data' => $data,
                            'token' =>  $token,
                            'message' => 'LogedIn successfully'
                        ];

                        return response()->json($response, 200);
                    } else {
                        return response()->json([
                            'status' => false,
                            'code' => 404,
                            'data' => [],
                            'message' => 'invalid otp'
                        ], 404, [], JSON_FORCE_OBJECT);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'code' => 404,
                        'data' => [],
                        'message' => 'Ivalid phone number'
                    ], 404, [], JSON_FORCE_OBJECT);
                }
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
