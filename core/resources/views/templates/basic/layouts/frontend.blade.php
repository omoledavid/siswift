<!doctype html>
<html lang="en" itemscope itemtype="http://schema.org/WebPage">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> {{ $general->sitename(__($pageTitle)) }}</title>
    @include('partials.seo')
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/bootstrap.min.css') }}">
    <!-- fontawesome 5  -->
    <link rel="stylesheet" href="{{asset('assets/global/css/all.min.css')}}">
    <!-- line-awesome webfont -->
    <link rel="stylesheet" href="{{asset('assets/global/css/line-awesome.min.css')}}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/owl.min.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/odometer.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/main.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue. 'css/color.php?color='.$general->base_color.'&secondColor='.$general->secondary_color) }}">
    <link rel="shortcut icon" href="{{ getImage('assets/images/logoIcon/favicon.png', '128x128') }}"
        type="image/x-icon">
    @stack('style-lib')

    @stack('style')
</head>

<body>
    @include($activeTemplate.'partials.preloader')

    @include($activeTemplate.'partials.header')
    @if (!request()->routeIs('home'))
    <div class="hero-section bg--base py-4">
        <div class="container">
            <ul class="breadcrumb justify-content-center">
                <li>
                    <a href="{{url('/')}}">@lang('Home')</a>
                </li>
                <li>
                    {{$pageTitle ?? ''}}
                </li>
            </ul>
        </div>
    </div>
    @endif
    @yield('content')
    @include($activeTemplate.'partials.footer')


    @php
    $cookie = App\Models\Frontend::where('data_keys','cookie.data')->first();
    @endphp



@if(@$cookie->data_values->status && !session('cookie_accepted'))
    <div class="cookie__wrapper">
        <div class="container">
          <div class="d-flex flex-wrap align-items-center justify-content-between">
            <p class="text--white my-2">
               @php echo @$cookie->data_values->description @endphp
              <a class="btn btn--white my-2" href="{{ @$cookie->data_values->link }}" target="_blank">@lang('Read Policy')</a>
            </p>
              <button type="button" class="btn btn--base policy h-unset">@lang('Accept')</button>
          </div>
        </div>
    </div>

 @endif

    <script src="{{asset('assets/global/js/jquery-3.6.0.min.js')}}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/modernizr-3.6.0.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/bootstrap.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/magnific-popup.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/owl.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/wow.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/odometer.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/viewport.jquery.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/nice-select.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/zoomsl.min.js')}}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/main.js') }}"></script>

    {{-- Script File pushed from blades --}}
    @stack('script-lib')
    {{-- Load third party plugins --}}
    @include('partials.plugins')
    {{-- Load izitoast --}}
    @include('partials.notify')
    {{-- Javascript Codes By Backend Dev --}}
    @include($activeTemplate.'script.main')
    {{-- Scripts pushed from blades --}}
    @stack('script')

    <script>
        'use strict';

        $('.policy').on('click',function(){
            $.get('{{route('cookie.accept')}}', function(response){
                $('.cookie__wrapper').removeClass('show');
            });
        });

        setTimeout(() => {
            $('.cookie__wrapper').addClass('show');
        }, 2000);

    </script>
</body>

</html>
