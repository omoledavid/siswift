@extends('admin.layouts.app')

@section('panel')

    <div class="row justify-content-center">
        <div class="col-xl-12">
            <div class="card custom--card chat-box">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h4 class="card-title mb-0">
                        @foreach($messages as $message)
                            @if($loop->first)
                                @if($message->sender_id != $user->id)
                                    {{$message->sender->userfullname}}
                                @else
                                    {{$message->receiver->userfullname}}
                                @endif
                            @endif
                        @endforeach
                    </h4>
                </div>
                <div class="card-body p-0">
                    <div class="ps-container">
                        @foreach($messages as $message)
                            @if($message->sender_id != $user->id)
                                <div class="media media-chat">
                                    <img class="avatar"
                                         src="{{ getImage(imagePath()['profile']['user']['path'].'/'.$message->sender->image,imagePath()['profile']['user']['size']) }}"
                                         alt="client">
                                    <div class="media-body">
                                        <p style="width:fit-content">{{!is_string($message->message) ? 'Escrow Initiated' : $message->message}}</p>
                                        @if(!empty($message->file))
                                            <div class="media-chat-thumb text-end">
                                                <img
                                                    src="{{getImage(imagePath()['message']['path'].'/'. $message->file)}}"
                                                    alt="item-banner">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="media media-chat media-chat-reverse">
                                    <img class="avatar"
                                         src="{{ getImage(imagePath()['profile']['user']['path'].'/'.$message->receiver->image,imagePath()['profile']['user']['size']) }}"
                                         alt="client">
                                    <div class="media-body">
                                            <p style="float:right">{{!is_string($message->message) ? 'Escrow Initiated' : $message->message}}</p>

                                        @if(!empty($message->file))
                                            <div class="media-chat-thumb text-end">
                                                <img
                                                    src="{{getImage(imagePath()['message']['path'].'/'. $message->file)}}"
                                                    alt="item-banner">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

