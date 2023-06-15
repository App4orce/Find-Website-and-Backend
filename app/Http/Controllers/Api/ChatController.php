<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function saveMessage(Request $request)
    {
        try {

            $rules = array(
                'order_id' => 'required',     // order id
                'msg_from' => 'required',     // sender
                'msg_to' => 'required',     // receiver
                'message' => 'required',     // message
                'file' => 'sometimes|required',     // file

            );
            $messages = [
                'order_id.required' => 'Order id is required',
                'msg_from.required' => 'sender id is required',
                'msg_to.required' => 'receiver id is required',
                'message.required' => 'message  is required',
                'file.required' => 'file is required',

            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $msg = $messages[0];
                return response()->json(['status' => false, 'code' => 401, 'data' => [], 'message' => $msg], 401, [], JSON_FORCE_OBJECT);
            } else {
                $data = '';
                $bodydata = [];
                if ($request->hasFile('file')) {
                    $image = $request->file('file');
                    $name = time() . '.' . $image->getClientOriginalExtension();
                    $destinationPath = public_path('chat');
                    $image->move($destinationPath, $name);
                }
                $chat = new Message;
                $chat->msg_from = Auth::user()->id;
                $chat->msg_to = $request->msg_to;
                $chat->order_id = $request->order_id;
                $chat->file = isset($name) ? $name : '';
                $chat->message = $request->message;
                $chat->save();
                // $user = User::find($request->msg_to);


                //get add slug 

                // $ad = Ad::find($request->id);

                // $details = [
                //     'name' =>  Auth::user()->username,
                //     'body' => $request->mesage,
                //     'date' => date('h:i A'),
                //     'slug' => $ad->slug,
                //     'type' => 'chat',
                //     'msg_from' => $chat->msg_from,
                // ];
                // Notification::send($user, new DbNotificatin($details));

                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'data' => [],
                    'message' => 'Message sent',
                ], 200, [], JSON_FORCE_OBJECT);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Something Wrong',
                'sql_error' => $th->getMessage()
            ], 500);
        }
    }

    public function getMessages(Request $request)
    {
        try {
            if (empty($request->msg_to)) {

                return response()->json([
                    'status' => false,
                    'code' => 401,
                    'data' => [],
                    'message' => 'To param is missing'
                ], 401, [], JSON_FORCE_OBJECT);
            }
            if (empty($request->order_id)) {

                return response()->json([
                    'status' => false,
                    'code' => 401,
                    'data' => [],
                    'message' => 'order id is missing'
                ], 401, [], JSON_FORCE_OBJECT);
            }


            $currentUser = Auth::user()->id;
            $id = $request->msg_to;
            $messages = Message::where(function ($query) use ($currentUser, $id) {
                $query->where('msg_from', $currentUser)
                    ->where('msg_to', $id);
            })
                ->orWhere(function ($query) use ($currentUser, $id) {
                    $query->where('msg_from', $id)
                        ->where('msg_to', $currentUser);
                })
                ->where('order_id', $request->order_id)
                ->orderBy('created_at', 'ASC')
                ->get();
            foreach ($messages as $message) {
                $message->file = asset('public/chat/' . $message->file);
            }
            $data = [
                'messages' => $messages
            ];
            return response()->json([
                'status' => true,
                'code' => 200,
                'data' =>  $data,
                'message' => 'Message list get succesfuuly',
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
