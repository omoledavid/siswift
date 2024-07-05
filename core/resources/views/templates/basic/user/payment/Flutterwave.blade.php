@extends($activeTemplate.'layouts.frontend')

@section('content')
    <div class="container padding-bottom padding-top">
        <div class="row justify-content-center">
            <div class="col-sm-10 col-md-7 col-lg-6 col-xl-4">
                <div class="card text-center">
                    <div class="card-body">
                        <img src="{{$deposit->gatewayCurrency()->methodImage()}}"   alt="@lang('Image')" class="w-100 mb-4">
                        <div>
                        <h5>@lang('Please Pay') {{showAmount($deposit->final_amo)}} {{__($deposit->method_currency)}}</h5>
                        <h5 class="my-3">@lang('To Get') {{showAmount($deposit->amount)}}  {{__($general->cur_text)}}</h5>
                        <button type="button" class="btn--base mt-4 d-block  w-100" id="btn-confirm" onClick="payWithRave()">@lang('Pay Now')</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
    <script>
        "use strict"
        var btn = document.querySelector("#btn-confirm");
        btn.setAttribute("type", "button");
        const API_publicKey = "{{$data->API_publicKey}}";

        function payWithRave() {
            var x = getpaidSetup({
                PBFPubKey: API_publicKey,
                customer_email: "{{$data->customer_email}}",
                amount: "{{$data->amount }}",
                customer_phone: "{{$data->customer_phone}}",
                currency: "{{$data->currency}}",
                txref: "{{$data->txref}}",
                onclose: function () {
                },
                callback: function (response) {
                    var txref = response.tx.txRef;
                    var status = response.tx.status;
                    var chargeResponse = response.tx.chargeResponseCode;
                    if (chargeResponse == "00" || chargeResponse == "0") {
                        window.location = '{{ url('ipn/flutterwave') }}/' + txref + '/' + status;
                    } else {
                        window.location = '{{ url('ipn/flutterwave') }}/' + txref + '/' + status;
                    }
                        // x.close(); // use this to close the modal immediately after payment.
                    }
                });
        }
    </script>
@endpush
