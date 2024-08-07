<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $general->sitename($pageTitle ?? '') }}</title>
    <!-- site favicon -->
    <link rel="shortcut icon" type="image/png" href="{{getImage(imagePath()['logoIcon']['path'] .'/favicon.png')}}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap">
    <!-- bootstrap 4  -->
    <link rel="stylesheet" href="{{ asset('assets/global/css/grid.min.css') }}">
    <!-- bootstrap toggle css -->
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/vendor/bootstrap-toggle.min.css')}}">
    <!-- fontawesome 5  -->
    <link rel="stylesheet" href="{{asset('assets/global/css/all.min.css')}}">
    <!-- line-awesome webfont -->
    <link rel="stylesheet" href="{{asset('assets/global/css/line-awesome.min.css')}}">

    @stack('style-lib')

    <!-- custom select box css -->
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/vendor/nice-select.css')}}">
    <!-- select 2 css -->
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/vendor/select2.min.css')}}">
    <!-- jvectormap css -->
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/vendor/jquery-jvectormap-2.0.5.css')}}">
    <!-- datepicker css -->
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/vendor/datepicker.min.css')}}">
    <!-- timepicky for time picker css -->
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/vendor/jquery-timepicky.css')}}">
    <!-- bootstrap-clockpicker css -->
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/vendor/bootstrap-clockpicker.min.css')}}">
    <!-- bootstrap-pincode css -->
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/vendor/bootstrap-pincode-input.css')}}">
    <!-- Magnipic Popup-->
    <link rel="stylesheet" href="{{ asset('assets/dashboard/css/magnific-popup.css') }}">
    <!-- dashdoard main css -->
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/app.css')}}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/custom.css')}}">

    @stack('style')
</head>
<body>
@yield('content')

<!-- jQuery library -->
<script src="{{asset('assets/global/js/jquery-3.6.0.min.js')}}"></script>
<!-- bootstrap js -->
<script src="{{asset('assets/dashboard/js/vendor/bootstrap.bundle.min.js')}}"></script>
<!-- bootstrap-toggle js -->
<script src="{{asset('assets/dashboard/js/vendor/bootstrap-toggle.min.js')}}"></script>

<!-- slimscroll js for custom scrollbar -->
<script src="{{asset('assets/dashboard/js/vendor/jquery.slimscroll.min.js')}}"></script>
<!-- custom select box js -->
<script src="{{asset('assets/dashboard/js/vendor/jquery.nice-select.min.js')}}"></script>
{{-- for staff add --}}
<script src="{{asset('assets/dashboard/js/cu-modal.js')}}"></script>



@include('partials.notify')
@stack('script-lib')
    <script src="{{ asset('assets/dashboard/js/nicEdit.js') }}"></script>

    <!-- seldct 2 js -->
    <script src="{{asset('assets/dashboard/js/vendor/select2.min.js')}}"></script>
    <!-- Magnigfic js -->
    <script src="{{ asset('assets/dashboard/js/jquery.magnific-popup.min.js') }}"></script>
    <!-- main js -->
    <script src="{{asset('assets/dashboard/js/app.js')}}"></script>
{{-- LOAD NIC EDIT --}}

<script>
    "use strict";
    bkLib.onDomLoaded(function() {
        $( ".nicEdit" ).each(function( index ) {
            $(this).attr("id","nicEditor"+index);
            new nicEditor({fullPanel : true}).panelInstance('nicEditor'+index,{hasPanel : true});
        });
    });
    (function($){
        $( document ).on('mouseover ', '.nicEdit-main,.nicEdit-panelContain',function(){
            $('.nicEdit-main').focus();
        });
    })(jQuery);
</script>

@stack('script')


</body>
</html>
