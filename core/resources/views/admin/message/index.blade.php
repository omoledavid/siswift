@extends('admin.layouts.app')

@section('panel')

    <div class="row justify-content-center">
        <div class="col-xl-12 col-lg-12 mb-30">
            <div class="card-area">
                <div class="row justify-content-center">
                    <div class="col-xl-12">
                        <div class="card custom--card">
                            <div class="card-body p-0">
                                <ul class="chat-area">
                                    @forelse($conversions as $conversion)
                                        @if($conversion->sender_id != $user->id)
                                            <li>
                                                <div class="chat-author">
                                                    <div class="thumb">
                                                        <img src="">
                                                    </div>
                                                    <div class="content">
                                                        <h6 class="title">
                                                            <a href="{{route('admin.conversation.chat',  [$conversion->id, $user->id])}}">{{$conversion->sender->userfullname}}</a>
                                                        </h6>

                                                        @php
                                                            $lastMessage = $conversion->messages->last()->message ?? '';


                                                            if (is_array($lastMessage)) {
                                                                $lastMessage = 'escrow initiated';
            //                                                    $lastMessage = implode(' ', $lastMessage);
                                                            }
                                                        @endphp

                                                        <span class="info">{{ Str::words($lastMessage, 8) }}</span>
                                                    </div>
                                                </div>
                                                <div class="date-area">
                                                    @php
                                                        // Retrieve the last message from the collection, if it exists
                                                        $lastMessage = $conversion->messages->last();
                                                    @endphp

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

                                                            // Check if $conversion->receiver and $conversion->receiver->image are set and not null
                                                            if (isset($conversion->receiver) && !is_null($conversion->receiver->image)) {
                                                                $image = $conversion->receiver->image;
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
                                                             alt="{{$conversion->receiver->userfullname}}">
                                                    </div>
                                                    <div class="content">
                                                        <h5 class="name">
                                                            <a href="{{route('admin.conversation.chat',  [$conversion->id, $user->id])}}">{{$conversion->receiver->userfullname}}</a>
                                                        </h5>
                                                        @php
                                                            $lastMessage = $conversion->messages->last()->message ?? '';

                                                            // If the last message is an array, convert it to a string
                                                            if (is_array($lastMessage)) {
                                                                $lastMessage = implode(' ', $lastMessage);
                                                            }
                                                        @endphp

                                                        <span class="info">{{ Str::words($lastMessage, 8) }}</span>

                                                    </div>
                                                </div>
                                                <div class="date-area">
                                                    @php
                                                        $lastMessage = $conversion->messages->last();
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

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
