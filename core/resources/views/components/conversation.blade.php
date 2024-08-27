@props(['conversations', 'user'])
@forelse($conversations as $conversation)
    @php
//    dd($conversation->lastMessage);
        // Retrieve the last message from the collection, if it exists
         $lastMessage = $conversation->lastMessage;
    @endphp
    @if($conversation->sender_id != $user->id)
        <li>
            <div class="chat-author">
                <div class="thumb">
                    <img src="">
                </div>
                <div class="content">
                    <h6 class="title">
                        <a href="{{route('admin.conversation.chat',  [$conversation->id, $user->id])}}">{{$lastMessage->user->userfullname}}</a>
                    </h6>

                    <span class="info">{{!is_string($lastMessage->message) ? 'Escrow Initiated' : Str::words($lastMessage->message, 8)}}</span>
                </div>
            </div>
            <div class="date-area">
                @if ($lastMessage)
                    <span>{{ $lastMessage->updated_at->diffForHumans() }}</span>
                @else
                    <span>No messages available</span>
                @endif

            </div>
        </li>
    @else
        <li>
            <div class="chat-author">
                <div class="thumb">
                    @php
                        $path = imagePath()['profile']['user']['path'];
                        $size = imagePath()['profile']['user']['size'];

                        // Check if $conversation->receiver and $conversation->receiver->image are set and not null
                        if (isset($conversation->receiver) && !is_null($conversation->receiver->image)) {
                            $image = $conversation->receiver->image;
                            $imagePath = $path . '/' . $image;

                            // Ensure path and size are valid before calling getImage
                            if (!empty($imagePath) && !empty($size)) {
                                $profile_image = getImage($imagePath, $size);
                            } else {
                                $profile_image = 'default_image.jpg'; // Default or fallback image
                            }
                        } else {
                            $profile_image = 'default_image.jpg'; // Default or fallback image
                        }

                    @endphp
                    <img src="{{($profile_image == null) ? '' : $profile_image  }}"
                         alt="{{$conversation->receiver->userfullname}}">
                </div>
                <div class="content">
                    <h5 class="name">
                        <a href="{{route('admin.conversation.chat',  [$conversation->id, $user->id])}}">{{$conversation->receiver->userfullname}}</a>
                    </h5>

                    <span class="info">{{!is_string($lastMessage->message) ? 'Escrow Initiated' : Str::words($lastMessage->message, 8)}}</span>
                </div>
            </div>
            <div class="date-area">
                @php
                    $updatedAt = $lastMessage ? $lastMessage->updated_at : null;
                @endphp

                @if ($updatedAt)
                    <span>{{ $updatedAt->diffForHumans() }}</span>
                @else
                    <span>No messages available</span>
                @endif

            </div>
        </li>
    @endif
@empty

    <div class="inbox-empty text-center my-2">
        <h4 class="title">@lang('Empty Conversation')</h4>
    </div>

@endforelse
