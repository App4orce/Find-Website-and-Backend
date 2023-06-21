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
            'phone.required' => trans('custommessage.phone.required'),
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
                'message' => __('custommessage.otp.sent_success', ['phone' => $request->phone]),
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
                'otp.required' => __('custommessage.otp.require'),
                'phone.required' => __('custommessage.phone.required'),
                'phone.regex' =>  __('custommessage.phone.regex'),

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
                            'message' => __('custommessage.LogedInmessage')
                        ];

                        return response()->json($response, 200);
                    } else {
                        return response()->json([
                            'status' => false,
                            'code' => 404,
                            'data' => [],
                            'message' => __('custommessage.invalidotp')
                        ], 404, [], JSON_FORCE_OBJECT);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'code' => 404,
                        'data' => [],
                        'message' => __('custommessage.otp.invalid')
                    ], 404, [], JSON_FORCE_OBJECT);
                }
                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' => [],
                    'message' => __('custommessage.account.create')
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

    public function deliveryProviderLogin(Request $request)
    {
        $rules = array(
            'password' => ['required','min:8'],
            'phone' => ['required', 'max:10', 'regex:/(05)[0-9]{8}/', 'not_regex:/[a-z]/'],


        );
        $messages = [
            'otp.required' => __('custommessage.otp.require'),
            'password.required' => __('custommessage.phone.required'),
            'password.min' => __('custommessage.password.min'),

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
            $user = User::select('id', 'role', 'status', 'password', 'phone')->where('phone', $request->phone)->where('role', 4)->first();


            // print_r($data);
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'staus' => false,
                    'code' => 401,
                    'data' => [],
                    'message' => [__('custommessage.invalid_credentials')]
                ], 401, [], JSON_FORCE_OBJECT);
            }

            // User::where('id', $user->id)->update([
            //     'fcm_token' => $request->fcm_token
            // ]);



            if ($user->status == 0) {
                return response([
                    'status' => false,
                    'code' => 401,
                    'data' => [],
                    'message' => __('custommessage.account_under_review')
                ], 401, [], JSON_FORCE_OBJECT);
            }

            $otp = rand(10000, 99999);
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
                'message' => __('custommessage.otp.sent_success', ['phone' => $request->phone]),
            ];

            return response()->json($response, 201);
        }
    }

    public function verifyProviderOtp(Request $request)
    {
        try {
            $name = '';

            $rules = array(
                'otp' => ['required'],
                'phone' => ['required', 'max:10', 'regex:/(05)[0-9]{8}/', 'not_regex:/[a-z]/'],


            );
            $messages = [
                'otp.required' => __('custommessage.otp.require'),
                'phone.required' => __('custommessage.phone.required'),
                'phone.regex' =>  __('custommessage.phone.regex'),
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
                $user = User::select('id', 'role', 'status', 'online_status', 'otp')->where('phone', $request->phone)->first();
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
                            'message' => __('custommessage.LogedInmessage')
                        ];

                        return response()->json($response, 200);
                    } else {
                        return response()->json([
                            'status' => false,
                            'code' => 404,
                            'data' => [],
                            'message' => __('custommessage.invalidotp')
                        ], 404, [], JSON_FORCE_OBJECT);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'code' => 404,
                        'data' => [],
                        'message' => __('custommessage.otp.invalid')
                    ], 404, [], JSON_FORCE_OBJECT);
                }
                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' => [],
                    'message' => __('custommessage.account.create')
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

    public function forgetPassword(Request $request)
    {

        try {
            $name = '';

            $rules = array(
                'phone' => ['required', 'max:10', 'regex:/(05)[0-9]{8}/', 'not_regex:/[a-z]/'],


            );
            $messages = [

                'phone.required' => __('custommessage.phone.required'),
                'phone.regex' =>  __('custommessage.phone.regex'),


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
                $user = User::select('id', 'role', 'status', 'online_status', 'phone')->where('phone', $request->phone)->first();
                if ($user) {
                    $otp = rand(10000, 99999);
                    User::where('id', $user->id)->update([
                        'otp' => $otp
                    ]);
                    $data = [
                        'otp' => $otp
                    ];
                    return response()->json([
                        'status' => true,
                        'code' => 200,
                        'data' => $data,
                        'message' => __('custommessage.otp.sent_success', ['phone' => $request->phone]),
                    ], 200, [], JSON_FORCE_OBJECT);
                } else {
                    return response()->json([
                        'status' => false,
                        'code' => 404,
                        'data' => [],
                        'message' => __('custommessage.phone.invalid')
                    ], 404, [], JSON_FORCE_OBJECT);
                }
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

    public function logout()
    {
        $user = request()->user(); //or Auth::user()

        // Revoke current user token
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [],
            'message' => __('custommessage.logoutmessage')
        ], 200, [], JSON_FORCE_OBJECT);
    }
}
