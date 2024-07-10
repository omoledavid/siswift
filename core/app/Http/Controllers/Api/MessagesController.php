<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function index(Request $request)
    {
        $sender_id = $request->user()->id;
        $receiver_id = $request->query('receiver_id');

        $hash = $this->generateHash($sender_id, $receiver_id);

        $messages = Conversation::query()->where('hash', $hash)->get();

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'message' => ['required', 'string']
        ]);

        $hash = $this->generateHash($request->get('receiver_id'), $request->user()->id);

        $conversation = Conversation::query()->create([
            'sender_id' => $request->user()->id,
            'hash' => $hash,
            'message' => $request->get('message')
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $conversation
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function generateHash(int $sender_id, int $receiver_id): string
    {
        $participants = [$sender_id, $receiver_id];
        sort($participants);
        return md5(json_encode($participants));
    }
}