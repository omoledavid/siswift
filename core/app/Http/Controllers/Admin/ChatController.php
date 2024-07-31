<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index($id)
    {
        $user = User::find($id);
        $pageTitle = "Chats of ". $user->userfullname;
        $messages = Message::where('sender_id', $user->id)->orWhere('receiver_id', $user->id)->with('sender')->with('conversations')->latest()->get();
        return view('admin.message.index', compact('pageTitle', 'messages','user'));
    }
    public function chat($conversionId, $user)
    {
        $user =User::findOrFail($user);
        $conversions = Message::findOrFail($conversionId);
        $pageTitle = "Chat List";
        $messages = Conversation::where('message_id',$conversions->id)->with('sender', 'receiver')->get();
        return view('admin.message.view', compact('pageTitle','messages', 'conversionId', 'user'));
    }
}
