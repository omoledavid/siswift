<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function index(Request $request)
    {
        $sender_id = $request->user()->id;

        $conversations = Message::query()->where('sender_id', $sender_id)->orWhere('receiver_id', $sender_id)->with('sender', 'receiver', 'conversations')->get();

        return response()->json([
            'status' => 'success',
            'data' => $conversations
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'message' => ['required', 'string']
        ]);

        $hash = $this->generateHash($request->get('receiver_id'), $request->user()->id);

        // Check if a conversation already exists
        $startedChat = Message::where('sender_id', $request->user()->id)
            ->where('receiver_id', $request->receiver_id)
            ->first();

        if (!$startedChat) {
            // Create a new conversation if one does not exist
            $conversation = Message::query()->create([
                'sender_id' => $request->user()->id,
                'receiver_id' => $request->receiver_id,
            ]);

            $messages = Conversation::query()->create([
                'sender_id' => $request->user()->id,
                'receiver_id' => $request->receiver_id,
                'hash' => $hash,
                'message' => $request->get('message'),
                'message_id' => $conversation->id,
            ]);
        } else {
            // Append to the existing conversation
            $messages = Conversation::query()->create([
                'sender_id' => $request->user()->id,
                'receiver_id' => $request->receiver_id,
                'hash' => $hash,
                'message' => $request->get('message'),
                'message_id' => $startedChat->id, // Use the existing conversation ID
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $sender_id = auth()->user()->id;
        $receiver_id = $id;

        $hash = $this->generateHash($sender_id, $receiver_id);

        $messages = Conversation::query()->where('hash', $hash)->with('sender', 'receiver')->get();

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
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
