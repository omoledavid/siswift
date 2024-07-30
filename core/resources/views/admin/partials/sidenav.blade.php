<div class="sidebar {{ sidebarVariation()['selector'] }} {{ sidebarVariation()['sidebar'] }} {{ @sidebarVariation()['overlay'] }} {{ @sidebarVariation()['opacity'] }}" data-background="{{asset('assets/dashboard/images/sidebar/7.jpg')}}">
    <button class="res-sidebar-close-btn"><i class="las la-times"></i></button>
    <div class="sidebar__inner">
        <div class="sidebar__logo">
            <a href="{{route('admin.dashboard')}}" class="sidebar__main-logo"><img
                    src="{{getImage(imagePath()['logoIcon']['path'] .'/logo_2.png')}}" alt="@lang('image')"></a>
            <a href="{{route('admin.dashboard')}}" class="sidebar__logo-shape"><img
                    src="{{getImage(imagePath()['logoIcon']['path'] .'/favicon.png')}}" alt="@lang('image')"></a>
            <button type="button" class="navbar__expand"></button>
        </div>

        <div class="sidebar__menu-wrapper" id="sidebar__menuWrapper">
            <ul class="sidebar__menu">
                <li class="sidebar__menu-header">@lang('Analytics')</li>
                <li class="sidebar-menu-item {{menuActive('admin.dashboard')}}">
                    <a href="{{route('admin.dashboard')}}" class="nav-link ">
                        <i class="menu-icon las la-tachometer-alt"></i>
                        <span class="menu-title">@lang('Dashboard')</span>
                    </a>
                </li>
                <li class="sidebar-menu-item {{menuActive('admin.dashboard.self')}}">
                    <a href="{{route('admin.dashboard.self')}}" class="nav-link ">
                        <i class="menu-icon las la-home"></i>
                        <span class="menu-title">@lang('My Shop')</span>
                    </a>
                </li>
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive([ 'admin.plan.index*'], 3) }}">
                        <i class="menu-icon las la-clipboard-check"></i>
                        <span class="menu-title">@lang('Plan Manage')</span>
                    </a>
                    <div class="sidebar-submenu {{ menuActive(['admin.time.index*', 'admin.plan.index*'], 2) }} ">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.plan.index') }}">
                                <a href="{{ route('admin.plan.index') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Plan Manage')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar__menu-header">@lang('Users')</li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{menuActive('admin.users*',3)}}">
                        <i class="menu-icon las la-users"></i>
                        <span class="menu-title">@lang('Users')</span>

                        @if($banned_users_count > 0 || $email_unverified_users_count > 0 || $sms_unverified_users_count > 0)
                            <span class="menu-badge pill bg--primary ml-auto">
                                <i class="fa fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{menuActive('admin.users*',2)}} ">
                        <ul>
                            <li class="sidebar-menu-item {{menuActive('admin.users.all')}} ">
                                <a href="{{route('admin.users.all')}}" class="nav-link">
                                    <i class="menu-icon las la-user-friends"></i>
                                    <span class="menu-title">@lang('All Customer')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{menuActive('admin.users.active')}} ">
                                <a href="{{route('admin.users.active')}}" class="nav-link">
                                    <i class="menu-icon las la-user-check"></i>
                                    <span class="menu-title">@lang('Active Customers')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{menuActive('admin.users.banned')}} ">
                                <a href="{{route('admin.users.banned')}}" class="nav-link">
                                    <i class="menu-icon las la-user-times"></i>
                                    <span class="menu-title">@lang('Banned Customers')</span>
                                    @if($banned_users_count)
                                        <span class="menu-badge pill bg--primary ml-auto">{{ $banned_users_count }}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item  {{menuActive('admin.users.email.unverified')}}">
                                <a href="{{route('admin.users.email.unverified')}}" class="nav-link">
                                    <i class="menu-icon las la-user-alt-slash"></i>
                                    <span class="menu-title">@lang('Email Unverified')</span>

                                    @if($email_unverified_users_count)
                                        <span
                                            class="menu-badge pill bg--primary ml-auto">{{$email_unverified_users_count}}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{menuActive('admin.users.sms.unverified')}}">
                                <a href="{{route('admin.users.sms.unverified')}}" class="nav-link">
                                    <i class="menu-icon las la-user-alt-slash"></i>
                                    <span class="menu-title">@lang('SMS Unverified')</span>
                                    @if($sms_unverified_users_count)
                                        <span
                                            class="menu-badge pill bg--primary ml-auto">{{$sms_unverified_users_count}}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{menuActive('admin.users.listing')}}">
                                <a href="{{route('admin.users.listing')}}" class="nav-link">
                                    <i class="menu-icon las la-user"></i>
                                    <span class="menu-title">@lang('With Listing')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{menuActive('admin.users.email.all')}}">
                                <a href="{{route('admin.users.email.all')}}" class="nav-link">
                                    <i class="menu-icon las la-envelope"></i>
                                    <span class="menu-title">@lang('Send Email')</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>



                <li class="sidebar__menu-header">@lang('Shop')</li>
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive(['admin.product*', 'admin.category.*', 'admin.subcategory.*', 'admin.attributes*', 'admin.brand.*'], 3) }}">
                        <i class="la la-product-hunt menu-icon"></i>
                        <span class="menu-title">@lang('Product')</span>
                    </a>
                    <div class="sidebar-submenu {{menuActive(['admin.product*', 'admin.category.*', 'admin.subcategory.*', 'admin.attributes*', 'admin.brand.*'], 2)}} ">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.category.*') }}">
                                <a class="nav-link" href="{{ route('admin.category.all') }}">
                                    <i class="las la-align-left menu-icon"></i>
                                    <span class="menu-title">@lang('Categories')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.brand.*') }}">
                                <a class="nav-link" href="{{ route('admin.brand.all') }}">
                                    <i class="la la-tags menu-icon"></i>
                                    <span class="menu-title">@lang('Brands')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.attributes*') }}">
                                <a class="nav-link" href="{{ route('admin.attributes') }}">
                                    <i class="la la-palette menu-icon"></i>
                                    <span class="menu-title">@lang('Attribute Types')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.products.all') }}">
                                <a class="nav-link" href="{{ route('admin.products.all') }}">
                                    <i class="menu-icon las la-tshirt"></i>
                                    <span class="menu-title">@lang('All Products')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.products.admin') }}">
                                <a class="nav-link" href="{{ route('admin.products.admin') }}">
                                    <i class="menu-icon las la-user-secret"></i>
                                    <span class="menu-title">@lang('My Products')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.products.seller') }}">
                                <a class="nav-link" href="{{ route('admin.products.seller') }}">
                                    <i class="menu-icon las la-store-alt"></i>
                                    <span class="menu-title">@lang('Seller\'s Products')</span>
                                        @if ($seller_pending_product > 0)
                                        <span class="menu-badge pill bg--primary ml-auto"><i class="fa fa-exclamation"></i></span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.products.trashed') }}">
                                <a class="nav-link" href="{{ route('admin.products.trashed') }}">
                                    <i class="menu-icon las la-trash"></i>
                                    <span class="menu-title">@lang('Trashed Products')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.product.review*') }}">
                                <a class="nav-link" href="{{ route('admin.product.reviews') }}">
                                    <i class="menu-icon las la-star"></i>
                                    <span class="menu-title">@lang('Product Reviews')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{menuActive('admin.order*',3)}}">
                        <i class="las la-money-bill menu-icon"></i>
                        <span class="menu-title">@lang('Orders')</span>
                        @if($pending_orders_count > 0 || $processing_orders_count || $dispatched_orders_count > 0)
                        <span class="menu-badge pill bg--primary ml-auto">
                            <i class="las la-bell"></i>
                        </span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{menuActive('admin.order*',2)}} ">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.order.index') }}">
                                <a class="nav-link" href="{{ route('admin.order.index') }}">
                                    <i class="menu-icon las la-list-ol"></i>
                                    <span class="menu-title">@lang('All Orders')</span>

                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.order.to_deliver')}}">
                                <a class="nav-link" href="{{ route('admin.order.to_deliver') }}">
                                    <i class="menu-icon las la-pause-circle"></i>
                                    <span class="menu-title">@lang('Pending Orders')</span>
                                    @if($pending_orders_count > 0)
                                    <span class="badge bg--primary badge-pill ml-2"><i class="fas fa-exclamation"></i></span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.order.on_processing') }}">
                                <a class="nav-link" href="{{ route('admin.order.on_processing') }}">
                                    <i class="menu-icon las la-spinner"></i>
                                    <span class="menu-title">@lang('Processing Orders')</span>
                                    @if($processing_orders_count > 0)
                                    <span class="badge bg--primary badge-pill ml-2"><i class="fas fa-exclamation"></i></span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.order.dispatched') }}">
                                <a class="nav-link" href="{{ route('admin.order.dispatched') }}">
                                    <i class="menu-icon las la-shopping-basket"></i>

                                    <span class="menu-title">@lang('Dispatched Orders')</span>

                                    @if($dispatched_orders_count > 0)
                                    <span class="badge bg--primary badge-pill ml-2"><i class="fas fa-exclamation"></i></span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.order.delivered') }}">
                                <a class="nav-link" href="{{ route('admin.order.delivered') }}">
                                    <i class="menu-icon las la-check-circle"></i>
                                    <span class="menu-title">@lang('Delivered Orders') </span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.order.canceled') }}">
                                <a class="nav-link" href="{{ route('admin.order.canceled') }}">
                                    <i class="menu-icon las la-times-circle"></i>
                                    <span class="menu-title">@lang('Canceled Orders')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.order.cod') }}">
                                <a class="nav-link" href="{{ route('admin.order.cod') }}">
                                    <i class="menu-icon las la-hand-holding-usd"></i>
                                    <span class="menu-title"><abbr data-toggle="tooltip" title="@lang('Cash On Delivery')">{{ @$deposit->gateway->name??trans('COD') }}</abbr> @lang('Orders')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.order.sells.log.admin') }}">
                                <a class="nav-link" href="{{ route('admin.order.sells.log.admin') }}">
                                    <i class="menu-icon las la-history"></i>
                                    <span class="menu-title">@lang('My Sales Log')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('admin.order.sells.log.seller') }}">
                                <a class="nav-link" href="{{ route('admin.order.sells.log.seller') }}">
                                    <i class="menu-icon las la-history"></i>
                                    <span class="menu-title">@lang('Seller Sales Log')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
{{--                <li class="sidebar-menu-item sidebar-dropdown">--}}
{{--                    <a href="javascript:void(0)" class="{{menuActive('admin.gateway*',3)}}">--}}
{{--                        <i class="menu-icon las la-credit-card"></i>--}}
{{--                        <span class="menu-title">@lang('Payment Gateways')</span>--}}
{{--                    </a>--}}
{{--                    <div class="sidebar-submenu {{menuActive('admin.gateway*',2)}} ">--}}
{{--                        <ul>--}}

{{--                            <li class="sidebar-menu-item {{menuActive('admin.gateway.automatic.index')}} ">--}}
{{--                                <a href="{{route('admin.gateway.automatic.index')}}" class="nav-link">--}}
{{--                                    <i class="menu-icon las la-dot-circle"></i>--}}
{{--                                    <span class="menu-title">@lang('Automatic Gateways')</span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                            <li class="sidebar-menu-item {{menuActive('admin.gateway.manual.index')}} ">--}}
{{--                                <a href="{{route('admin.gateway.manual.index')}}" class="nav-link">--}}
{{--                                    <i class="menu-icon las la-dot-circle"></i>--}}
{{--                                    <span class="menu-title">@lang('Manual Gateways')</span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                        </ul>--}}
{{--                    </div>--}}
{{--                </li>--}}

                <li class="sidebar-menu-item sidebar-dropdown">

                    <a href="javascript:void(0)" class="{{ menuActive(['admin.coupon*', 'admin.offer.*', 'admin.subscriber.*' ], 3) }}">
                        <i class="la la-bullhorn menu-icon"></i>
                        <span class="menu-title">@lang('Promotion')</span>
                    </a>

                    <div class="sidebar-submenu {{menuActive(['admin.coupon*', 'admin.offer.*', 'admin.subscriber.*'], 2)}} ">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('admin.coupon*') }}">
                                <a class="nav-link" href="{{ route('admin.coupon.index') }}">
                                    <i class="menu-icon lab la-contao"></i>
                                    <span class="menu-title">@lang('Coupons')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{ menuActive('admin.offer*') }}">
                                <a class="nav-link" href="{{ route('admin.offer.index') }}">
                                    <i class="menu-icon la la-fire-alt"></i>
                                    <span class="menu-title">@lang('Offers')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item  {{menuActive('admin.subscriber.index')}}">
                                <a href="{{route('admin.subscriber.index')}}" class="nav-link"
                                data-default-url="{{ route('admin.subscriber.index') }}">
                                    <i class="menu-icon la la-thumbs-up"></i>
                                    <span class="menu-title">@lang('Subscribers') </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="sidebar-menu-item {{menuActive('admin.withdraw.method.index')}}">
                    <a href="{{route('admin.withdraw.method.index')}}" class="nav-link">
                        <i class="menu-icon lab la-amazon-pay"></i>
                        <span class="menu-title">@lang('Withdraw Methods')</span>
                    </a>
                </li>
                <li class="sidebar__menu-header">@lang('Payments & Withdraw')</li>
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{menuActive('admin.deposit*',3)}}">
                        <i class="menu-icon las la-credit-card"></i>
                        <span class="menu-title">@lang('Payments')</span>
                        @if(0 < $pending_deposits_count)
                            <span class="menu-badge pill bg--primary ml-auto">
                                <i class="fa fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{menuActive('admin.deposit*',2)}} ">
                        <ul>

                            <li class="sidebar-menu-item {{menuActive('admin.deposit.pending')}} ">
                                <a href="{{route('admin.deposit.pending')}}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Pending Payments')</span>
                                    @if($pending_deposits_count)
                                        <span class="menu-badge pill bg--primary ml-auto">{{$pending_deposits_count}}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{menuActive('admin.deposit.approved')}} ">
                                <a href="{{route('admin.deposit.approved')}}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Approved Payments')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{menuActive('admin.deposit.successful')}} ">
                                <a href="{{route('admin.deposit.successful')}}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Successful Payments')</span>
                                </a>
                            </li>


                            <li class="sidebar-menu-item {{menuActive('admin.deposit.rejected')}} ">
                                <a href="{{route('admin.deposit.rejected')}}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Rejected Payments')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{menuActive('admin.deposit.list')}} ">
                                <a href="{{route('admin.deposit.list')}}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('All Payments')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="sidebar-menu-item {{ menuActive('admin.kyc.setting') }}">
                    <a href="{{ route('admin.kyc.setting') }}" class="nav-link">
                        <i class="menu-icon las la-user-check"></i>
                        <span class="menu-title">@lang('KYC Setting')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{menuActive('admin.withdrawals*',3)}}">
                        <i class="menu-icon la la-wallet"></i>
                        <span class="menu-title">@lang('Withdrawals') </span>
                        @if(0 < $pending_withdraw_count)
                            <span class="menu-badge pill bg--primary ml-auto">
                                <i class="fa fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{menuActive('admin.withdrawals*',2)}} ">
                        <ul>
                            <li class="sidebar-menu-item {{menuActive('admin.withdrawals.pending')}} ">
                                <a href="{{route('admin.withdrawals.pending')}}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Pending')</span>

                                    @if($pending_withdraw_count)
                                        <span class="menu-badge pill bg--primary ml-auto">{{$pending_withdraw_count}}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{menuActive('admin.withdrawals.approved')}} ">
                                <a href="{{route('admin.withdrawals.approved')}}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Approved')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{menuActive('admin.withdrawals.rejected')}} ">
                                <a href="{{route('admin.withdrawals.rejected')}}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Rejected')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{menuActive('admin.withdrawals.log')}} ">
                                <a href="{{route('admin.withdrawals.log')}}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('All Withdrawals')</span>
                                </a>
                            </li>


                        </ul>
                    </div>
                </li>

                <li class="sidebar__menu-header">@lang('Support')</li>


                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{menuActive('admin.ticket*',3)}}">
                        <i class="menu-icon la la-ticket"></i>
                        <span class="menu-title">@lang('Support Ticket') </span>
                        @if(0 < $pending_ticket_count)
                            <span class="menu-badge pill bg--primary ml-auto">
                                <i class="fa fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{menuActive('admin.ticket*',2)}} ">
                        <ul>
                            <li class="sidebar-menu-item {{menuActive('admin.ticket')}} ">
                                <a href="{{route('admin.ticket')}}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('All Tickets')</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{menuActive('admin.ticket.pending')}} ">
                                <a href="{{route('admin.ticket.pending')}}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Pending Tickets')</span>
                                    @if($pending_ticket_count)
                                        <span
                                            class="menu-badge pill bg--primary ml-auto">{{$pending_ticket_count}}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="sidebar-menu-item {{menuActive('admin.ticket.answered')}} ">
                                <a href="{{route('admin.ticket.answered')}}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Answered Tickets')</span>
                                </a>
                            </li>


                            <li class="sidebar-menu-item {{menuActive('admin.ticket.closed')}} ">
                                <a href="{{route('admin.ticket.closed')}}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Closed Tickets')</span>
                                </a>
                            </li>


                        </ul>
                    </div>
                </li>

                <li class="sidebar__menu-header">@lang('System Settings')</li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{menuActive(['admin.setting*', 'admin.language*', 'admin.estensions*'],3)}}">
                        <i class="menu-icon la la-tools"></i>
                        <span class="menu-title">@lang('Settings')</span>
                    </a>
                    <div class="sidebar-submenu {{menuActive(['admin.setting*', 'admin.language*', 'admin.estensions*'],2)}} ">
                        <ul>
                            <li class="sidebar-menu-item {{menuActive('admin.setting.index')}}">
                                <a href="{{route('admin.setting.index')}}" class="nav-link">
                                    <i class="menu-icon las la-life-ring"></i>
                                    <span class="menu-title">@lang('General')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>




            </ul>
            <div class="text-center mb-3 text-uppercase">
                <span class="text--primary">{{__(systemDetails()['name'])}}</span>
                <span class="text--success">@lang('V'){{systemDetails()['version']}} </span>
            </div>
        </div>
    </div>
</div>
<!-- sidebar end -->
