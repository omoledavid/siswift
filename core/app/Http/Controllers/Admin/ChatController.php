<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index($id)
    {
        $user = User::find($id);
        $pageTitle = "Chats of ". $user->userfullname;
        $messages = Message::query()->where('user_id', $user->id)->with('conversation')->latest()->get();
        $conversations = Conversation::query()->where('buyer_id', $user->id)->orWhere('seller_id', $user->seller_id)->latest()->get();
        return view('admin.message.index', compact('pageTitle', 'messages','user', 'conversations'));
    }
    public function chat($conversionId, $user): View
    {
        $pageTitle = "Chat List";
        $user =User::query()->findOrFail($user);
        $conversations = Conversation::query()->find($conversionId)->messages()->get();
        return view('admin.message.view', compact('pageTitle','conversations', 'user'));
    }
}
