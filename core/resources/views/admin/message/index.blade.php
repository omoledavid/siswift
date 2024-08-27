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
                                   <x-conversation :conversations="$conversations" :user="$user"/>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
