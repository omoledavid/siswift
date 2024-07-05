<!-- Header Section Starts Here -->
<div class="header-top py-1 d-none d-lg-block">
    <div class="container">
        <div class="header-top-wrap d-flex flex-wrap justify-content-between align-items-center">
            <div class="select-item">
                <select name="language" class="select-bar selectLanguage">
                    @foreach($language as $item)
                        <option value="{{$item->code}}" @if(session('lang') == $item->code) selected  @endif>{{ __($item->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="right-side">
                <ul class="menu ml-auto d-none d-lg-flex">
                    <li>
                        <a href="{{route('home')}}">@lang('Home')</a>
                    </li>

                    <li>
                        <a href="{{ route('products') }}">@lang('Products')</a>
                    </li>

                    <li>
                        <a href="{{ route('brands') }}">@lang('Brands')</a>
                    </li>
                    <li>
                        <a href="{{ route('orderTrack') }}">@lang('Track Order')</a>
                    </li>
                    @auth
                    <li>
                        <a href="{{ route('ticket') }}">@lang('Support')</a>
                    </li>
                    @else
                     <li>
                        <a href="{{ route('contact') }}">@lang('Contact')</a>
                    </li>
                    @endauth

                </ul>
            </div>
        </div>
    </div>
</div>
<div class="header-middle bg-white py-3">
    <div class="container">
        <div class="header-wrapper justify-content-between align-items-center">
            <div class="logo">
                <a href="{{ route('home') }}">
                    <img src="{{getImage(imagePath()['logoIcon']['path'] .'/logo.png')}}" alt="logo">
                </a>
            </div>

            @if(!request()->routeIs('home'))
            <div class="header-category-area d-none d-lg-block">
                <button class="cmn--btn" type="submit">@lang('All Categories')</button>
                <div class="category-link-wrapper d-none d-lg-block">
                    <ul class="category-link d-none d-lg-block">
                        @foreach ($allCategories->take(10) as $category)
                            <li>
                                <a href="{{ route('products.category', ['id'=>$category->id, 'slug'=>slug($category->name)]) }}">
                                    {{ $category->name }}
                                </a>
                                @if($category->allSubcategories->count()>0)
                                <ul class="category-sublink">
                                    @foreach ($category->allSubcategories as $subcategory)
                                        @include($activeTemplate.'partials.menu_subcategories', ['subcategory' => $subcategory])
                                    @endforeach
                                </ul>
                                @endif
                            </li>
                        @endforeach

                        @if($allCategories->count()>10)
                            <li> <a href="{{ route('categories') }}">@lang('View All')</a></li>
                        @endif
                    </ul>
                </div>
            </div>
            @endif

            <form action="{{route('product.search')}}" method="GET" class="header-search-form d-none d-md-block">
                <div class="header-form-group">
                    <input type="text" name="search_key" value="{{request()->search_key}}" placeholder="@lang('Search')...">
                    <button type="submit"><i class="las la-search"></i></button>
                </div>
                <div class="select-item">
                    <select class="select-bar" name="category_id">
                        <option selected value="0">@lang('All Categories')</option>
                        @foreach ($allCategories as $category)

                            <option value="{{ $category->id }}">@lang($category->name)</option>
                            @php
                                $prefix = '--'
                            @endphp
                            @foreach ($category->allSubcategories as $subcategory)

                                <option value="{{ $subcategory->id }}">
                                    {{ $prefix }}@lang($subcategory->name)
                                </option>

                                @include($activeTemplate.'partials.subcategories', ['subcategory' => $subcategory, 'prefix'=>$prefix])
                            @endforeach
                        @endforeach
                    </select>
                </div>
            </form>
            <ul class="shortcut-icons">
                <li>
                    <a href="javascript:void(0)" class="dashboard-menu-bar" id="account-btn">
                        <i class="las la-user"></i>
                    </a>
                </li>
                <li>
                    <a href="{{route('compare')}}">
                        <i class="las la-sync-alt"></i>
                        <span class="compare-count amount">0</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" id="wish-button">
                        <i class="lar la-heart"></i>
                        <span class="wishlist-count amount">0</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" id="cart-button">
                        <i class="las la-shopping-bag"></i>
                        <span class="cart-count amount">0</span>
                    </a>
                </li>
            </ul>
            <div class="header-bar d-lg-none">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</div>
<div class="header-bottom body-bg py-lg-2 d-md-none">
    <div class="container">
        <form class="header-search-form">
            <div class="header-form-group">
                <input type="text" name="search_key" value="{{request()->search_key}}" placeholder="@lang('Search')...">
                <button type="submit"><i class="las la-search"></i></button>
            </div>
            <div class="select-item">
                <select class="select-bar" name="category_id">
                    <option selected value="0">@lang('All Categories')</option>
                    @foreach ($allCategories as $category)

                        <option value="{{ $category->id }}">@lang($category->name)</option>
                        @php
                            $prefix = '--'
                        @endphp
                        @foreach ($category->allSubcategories as $subcategory)

                            <option value="{{ $subcategory->id }}">
                                {{ $prefix }}@lang($subcategory->name)
                            </option>

                            @include($activeTemplate.'partials.subcategories', ['subcategory' => $subcategory, 'prefix'=>$prefix])
                        @endforeach
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>




<div class="mobile-menu d-lg-none">
    <div class="mobile-menu-header">
        <div class="mobile-menu-close">
            <i class="las la-times"></i>
        </div>
        <div class="logo">
            <a href="{{ route('home') }}">
                <img src="{{getImage(imagePath()['logoIcon']['path'] .'/logo_2.png')}}" alt="logo">
            </a>
        </div>
        <div class="select-item">
            <select name="language" class="selectLanguage select-bar">
                <option value="">@lang('Select One')</option>
                    @foreach($language as $item)
                        <option value="{{$item->code}}" @if(session('lang') == $item->code) selected  @endif>{{ __($item->name) }}</option>
                    @endforeach
            </select>
        </div>
    </div>

    <ul class="nav-tabs nav border-0">
        <li>
            <a href="#menu" class="active" data-toggle="tab">@lang('Menu')</a>
        </li>
        <li>
            <a href="#cate" data-toggle="tab">@lang('Categories')</a>
        </li>
    </ul>
    <div class="mobile-menu-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="menu">
                <ul class="menu mt-4">
                    <li>
                        <a href="{{route('home')}}">@lang('Home')</a>
                    </li>

                    <li>
                        <a href="{{ route('products') }}">@lang('Products')</a>
                    </li>

                    <li>
                        <a href="{{ route('brands') }}">@lang('Brands')</a>
                    </li>
                    <li>
                        <a href="{{ route('orderTrack') }}">@lang('Track Order')</a>
                    </li>
                    @auth
                    <li>
                        <a href="{{ route('ticket') }}">@lang('Support')</a>
                    </li>
                    @else
                     <li>
                        <a href="{{ route('contact') }}">@lang('Contact')</a>
                    </li>
                    @endauth
                </ul>
            </div>
            <div class="tab-pane" id="cate">
                <div class="left-category single-style">
                    <ul class="categories">
                        @foreach ($allCategories->take(10) as $category)
                        <li>
                            <a href="{{ route('products.category', ['id'=>$category->id, 'slug'=>slug($category->name)]) }}">{{ $category->name }}</a>

                            @if($category->allSubcategories->count()>0)
                            <span class="open-links"></span>
                            <ul class="sub-category">
                                @foreach ($category->allSubcategories as $subcategory)
                                    @include($activeTemplate.'partials.menu_subcategories', ['subcategory' => $subcategory])
                                @endforeach
                            </ul>
                            @endif
                        </li>
                        @endforeach
                        @if($allCategories->count()>10)
                            <li><a href="{{ route('categories') }}">@lang('View All')</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="body-overlay" class="body-overlay"></div>
@include($activeTemplate.'partials.side_modal')
