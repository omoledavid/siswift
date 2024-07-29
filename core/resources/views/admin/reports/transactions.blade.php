@extends('admin.layouts.app')

@section('panel')
<div class="row">

    <div class="col-lg-12">
        <div class="card b-radius--10 ">
            <div class="card-body p-0">
                <div class="row justify-content-end">
                    <div class="col-xl-3 mb-3">
                        @if(request()->routeIs('admin.users.transactions'))
                            <form action="" method="GET" class="pt-3 px-3">
                                <div class="input-group has_append">
                                    <input type="text" name="search" class="form-control" placeholder="@lang('TRX / Username')" value="{{ $search ?? '' }}">
                                    <div class="input-group-append">
                                        <button class="btn btn--primary" type="submit"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                            @else
                            <form action="{{ route('admin.report.transaction.search') }}" method="GET" class="pt-3 px-3">
                                <div class="input-group has_append">
                                    <input type="text" name="search" class="form-control" placeholder="@lang('TRX / Username')" value="{{ $search ?? '' }}">
                                    <div class="input-group-append">
                                        <button class="btn btn--primary" type="submit"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>


                <div class="table-responsive--sm table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Type')</th>
                                <th>@lang('Transacted')</th>
                                <th>@lang('Amount')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trx)
                            <tr>

                                <td>
                                    {{$trx->type}}
                                </td>

                                <td data-label="@lang('Transacted')">
                                    {{ showDateTime($trx->created_at) }}<br>{{ diffForHumans($trx->created_at) }}
                                </td>

                                <td data-label="@lang('Amount')" class="budget">
                                    <span class="font-weight-bold @if($trx->type == 'deposit')text--success @else text--danger @endif">
                                        {{ $trx->trx_type }} {{showAmount($trx->amount)}} {{ $general->cur_text }}
                                    </span>
                                </td>
                           </tr>
                           @empty
                           <tr>
                            <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                        </tr>
                        @endforelse

                    </tbody>
                </table><!-- table end -->
            </div>
        </div>
        @if($transactions->hasPages())
        <div class="card-footer py-4">
            {{ paginateLinks($transactions) }}
        </div>
        @endif
    </div><!-- card end -->
</div>
</div>

@endsection

