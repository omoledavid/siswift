@extends('seller.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10 ">
            <div class="card-body p-0">
                <div class="row justify-content-end">
                    <div class="col-xl-3 mb-3">
                        <form action="" method="GET" class="pt-3 px-3">
                            <div class="input-group has_append">
                                <input type="text" name="search" class="form-control" placeholder="Order ID" value="{{ request()->search ?? '' }}">
                                <div class="input-group-append">
                                    <button class="btn btn--primary box--shadow1" id="search-btn" type="submit"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive--md  table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Product Name')</th>
                                <th>@lang('Order ID')</th>
                                <th>@lang('Order Quantity')</th>
                                <th>@lang('Order Price')</th>
                                <th>@lang('After Commission')</th>
                                <th>@lang('Date')</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($logs as $log)
                            <tr>
                                <td data-label="@lang('Product Name')">
                                    {{ Str::limit($log->product->name,30) }}
                                </td>
                                <td data-label="@lang('Order ID')">
                                    {{$log->order_id}}
                                </td>
                                <td data-label="@lang('Order Quantity')">
                                    {{$log->qty}}
                                </td>
                                <td data-label="@lang('Order Price')">
                                    {{$general->cur_sym}}{{getAmount($log->product_price)}}
                                </td>
                                <td data-label="@lang('After Commission')">
                                    {{$general->cur_sym}}{{getAmount($log->after_commission)}}
                                </td>
                                <td data-label="@lang('Date')">
                                    {{ showDateTime($log->created_at, 'd M, Y') }}
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($logs->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($logs) }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

