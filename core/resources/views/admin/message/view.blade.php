@extends('admin.layouts.app')

@section('panel')

    <div class="row justify-content-center">
        <div class="col-xl-12">
            <div class="card custom--card chat-box">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h4 class="card-title mb-0">
                        @foreach($conversations as $message)
                            @if($loop->first)
                                @if($message->user->id != $user->id)
                                    {{$message->user->userfullname}}
                                @else
                                    {{$message->user->userfullname}}
                                @endif
                            @endif
                        @endforeach
                    </h4>
                </div>
                <div class="card-body p-0">
                    <div class="ps-container">
                        @foreach($conversations as $message)
                            @if($message->user->id != $user->id)
                                <div class="media media-chat">
                                    <img class="avatar"
                                         src="{{ getImage(imagePath()['profile']['user']['path'].'/'.$message->user->image,imagePath()['profile']['user']['size']) }}"
                                         alt="client">
                                    <div class="media-body">
                                        <p style="width:fit-content">{{!is_string($message->message) ? 'Escrow Initiated' : $message->message}}</p>
                                        @if(!empty($message->files))
                                            <div class="media-chat-thumb text-end">
                                                <img
                                                    src="{{getImage(imagePath()['message']['path'].'/'. $message->files)}}"
                                                    alt="item-banner">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="media media-chat media-chat-reverse">
                                    <img class="avatar"
                                         src="{{ getImage(imagePath()['profile']['user']['path'].'/'.$message->user->image,imagePath()['profile']['user']['size']) }}"
                                         alt="client">
                                    <div class="media-body">
                                        <p style="float:right">{{!is_string($message->message) ? 'Escrow Initiated' : $message->message}}
                                        </p>
                                        @if(!empty($message->files))
                                            @foreach($message->files as $messageImage)
                                                <br>
                                                <img
                                                    style="width: 150px; border-radius: 10px; float: right;"
                                                    src="{{getImage(imagePath()['messages']['path'].'/'. $messageImage->file_path)}}"
                                                    alt="item-banner">
                                            @endforeach
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

