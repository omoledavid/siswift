@extends('admin.layouts.app')

@section('panel')


<div class="row justify-content-center">

    <div class="loader-container text-center d-none">
        <span class="loader">
            <i class="fa fa-circle-notch fa-spin" aria-hidden="true"></i>
        </span>
    </div>

    <div class="col-lg-12">
        <form action="{{ route('admin.products.product-store', isset($product)?$product->id:0) }}" id="addForm" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="card p-2 has-select2">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Product Information')</h5>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-md-2">
                            <label class="font-weight-bold">@lang('Product Name')</label>
                        </div>
                        <div class="col-md-10">
                            <input type="text" class="form-control" placeholder="@lang('Type Here')..." value="{{isset($product)?$product->name:old('name')}}" name="name" required/>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-2">
                            <label class="font-weight-bold">@lang('Product Model')</label>
                        </div>
                        <div class="col-md-10">
                            <input type="text" class="form-control" placeholder="@lang('Type Here')..." value="{{isset($product)?$product->model:old('model')}}" name="model" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-2">
                            <label class="font-weight-bold">@lang('Quantity')</label>
                        </div>
                        <div class="col-md-10">
                            <input type="number" class="form-control" placeholder="@lang('Type Here')..." value="{{isset($product)?$product->track_inventory:old('track_inventory')}}" name="track_inventory" />
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-2">
                            <label class="font-weight-bold">@lang('Brand')</label>
                        </div>
                        <div class="col-md-10">
                            <select class="form-control select2-basic" name="brand_id" required>
                                <option selected disabled value="">@lang('Select One')</option>
                                @foreach ($brands as $brand)
                                <option value="{{@$brand->id}}" {{ isset($product)?($brand->id==$product->brand_id?'selected':''):''}}>
                                    {{ __($brand->name) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-lg-2 col-md-3">
                            <label class="font-weight-bold" for="categories">@lang('Categories')</label>
                        </div>
                        <div class="col-lg-10 col-md-9">
                            <select class="select2-multi-select form-control" name="categories[]" id="categories" multiple required">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">@lang($category->name)</option>
                                    @php
                                        $prefix = '--'
                                    @endphp
                                    @foreach ($category->allSubcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}">
                                            {{ $prefix }}@lang($subcategory->name)
                                        </option>
                                        @include('admin.partials.subcategories', ['subcategory' => $subcategory, 'prefix'=>$prefix])

                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-2">
                            <label class="font-weight-bold">@lang('Base Price')</label>
                        </div>
                        <div class="col-md-10">

                            <div class="input-group">
                                <input type="text" class="form-control numeric-validation" name="base_price" placeholder="@lang('Type Here')..." value="{{$product->base_price??0}}" required/>
                                <div class="input-group-append">
                                    <span class="input-group-text">@lang($general->cur_sym)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card p-2 my-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Product Description')</h5>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-md-2">
                            <label class="font-weight-bold">@lang('Description')</label>
                        </div>
                        <div class="col-md-10">
                            <textarea rows="5" class="form-control nicEdit" name="description">@php echo ($product->description)??'' @endphp</textarea>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card p-2 my-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Media Contents')</h5>
                </div>
                <div class="card-body">

                    <div class="form-group row">
                        <div class="col-md-2">
                            <label class="font-weight-bold">@lang('Main Image')</label>
                        </div>
                        <div class="col-md-10">
                            <div class="payment-method-item">
                                <div class="payment-method-header d-flex flex-wrap">
                                    <div class="thumb">
                                        <div class="avatar-preview">
                                            <div class="profilePicPreview" style="background-image: url('{{ getImage(imagePath()['product']['path'].'/'.@$product->main_image, imagePath()['product']['size']) }}')"></div>
                                        </div>
                                        <div class="avatar-edit">
                                            <input type="file" name="main_image" class="profilePicUpload" id="image" accept=".png, .jpg, .jpeg" @if(request()->routeIs('admin.products.create'))required @endif>
                                            <label for="image" class="bg--primary"><i class="la la-pencil"></i></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-2">
                            <label class="font-weight-bold">@lang('Additional Images')</label>
                        </div>
                        <div class="col-md-10">
                            <div class="input-field">
                                <div class="input-images"></div>
                                <small class="form-text text-muted">
                                    <i class="las la-info-circle"></i> @lang('You can only upload a maximum of 6 images')</label>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-block btn--success mt-3">{{isset($product)?trans('Update'):trans('Add')}}</button>
        </form>
    </div>
</div>

<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="close ml-auto m-3" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body text-center">
                <i class="las la-times-circle f-size--100 text--danger mb-15"></i>
                <h3 class="text--danger mb-15">@lang('Error: Cannot process your entry!')</h3>
                <p class="mb-15">@lang('You can\'t add more than 6 image')</p>
                <button type="button" class="btn btn--danger" data-dismiss="modal">@lang('Continue')</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('breadcrumb-plugins')
<a href="{{ route('admin.products.all') }}" class="btn btn-sm btn--primary box--shadow1 text--small"><i class="la la-backward"></i>@lang('Go Back')</a>
@endpush

@push('script-lib')
<script src="{{ asset('assets/dashboard/js/image-uploader.min.js') }}"></script>
@endpush

@push('style-lib')
<link href="https://fonts.googleapis.com/css?family=Lato:300,700|Montserrat:300,400,500,600,700|Source+Code+Pro&display=swap"
rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/image-uploader.min.css') }}">
@endpush


@push('script')

<script>

    'use strict';
    (function($){
        var dropdownParent = $('.has-select2');

        @if(isset($images))
            let preloaded = @json($images);
        @else
            let preloaded = [];
        @endif

        $('.input-images').imageUploader({
            preloaded: preloaded,
            imagesInputName: 'photos',
            preloadedInputName: 'old',
            maxFiles: 6
        });

        $(document).on('input', 'input[name="images[]"]', function(){
            var fileUpload = $("input[type='file']");
            if (parseInt(fileUpload.get(0).files.length) > 6){
                $('#errorModal').modal('show');
            }
        });

        $('select[name="category_id"]').on('change', function () {
            var subcategories = $(this).find(':selected').data('subcats');
            var output= `<div class="col-md-2">
                            <label class="font-weight-bold">Subcategory</label>
                        </div>
                        <div class="col-md-10">
                            <select class="form-control select2-basic" name="sub_category_id">
                            <option value="" selected disabled>@lang('Select One')</option>
                        </div>
                        `;
            if (subcategories.length != 0) {
                $.each(subcategories, function (key, val) {
                    output += `<option value="${val.id}">${val.name}</option>`;
                });
                output += `</select>`
                $('#sub-categories-div').html(output);
            }
        });

        @if (request() -> routeIs('admin.products.edit'))

            var categories = [];
            @if($product->categories)
            categories = @json($product->categories->pluck('id'));
            @endif
            $('#categories').val(categories);

            $('.select2-multi-select').select2({
                dropdownParent: dropdownParent,
                closeOnSelect: false
            });

        @endif

        $('.add-specification').on('click', function(){
            var specifications = $(document).find('.specifications');
            var length         = specifications.length;
            $('.specification-info').addClass('d-none');
            var content =`<div class="specifications">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label class="font-weight-bold">${length+1}</label>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" name="specification[${length}][name]" placeholder="@lang('Type Name Here...')">
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group abs-form-group d-flex justify-content-between flex-wrap">

                                                    <input type="text" class="form-control" name="specification[${length}][value]" placeholder="@lang('Type Value Here...')">
                                                    <button type="button" class="btn btn-outline--danger remove-specification abs-button"><i class="la la-minus"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>`;

            $(content).appendTo('.specifications-wrapper').hide().slideDown('slow');

            specifications = $(document).find('.specifications');
            length         = specifications.length;

            if(length > 0) {
                $('.remove-specification').removeClass('d-none');
            }else{
                $('.remove-specification').addClass('d-none');
            }
        });

        $(document).on('click', '.remove-specification' ,function(){

            var parent = $(this).parents('.specifications');

            parent.slideUp('slow', function(e){
                this.remove();
            });

        });

        $('.add-extra').on('click', function(){
            var extras         = $(document).find('.extra');
            var length         = extras.length;

            $('.extra-info').addClass('d-none');

            var content =`<div class="extra">
                                    <div class="d-flex justify-content-end mb-3">
                                        <button type="button" class="btn btn-outline--danger float-right  remove-extra"><i class="la la-minus"></i></button>
                                    </div>
                                <div class="form-group row">
                                    <div class="col-md-2">
                                        <label class="font-weight-bold">@lang('Name')</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" name="extra[${length + 1}][key]" value="">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-2">
                                        <label class="font-weight-bold">@lang('Value')</label>
                                    </div>
                                    <div class="col-md-10">
                                        <textarea class="form-control" name="extra[${length + 1}][value]" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>`;


            var elm = $(content).appendTo('.extras').hide().slideDown('slow').find(`textarea[name="extra[${length +1}][value]"]`);

            var curSize = elm.length;

            new nicEditor().panelInstance(elm[0]);

            extras = $(document).find('.extra');
            length         = extras.length;

            if(length != 0) {
                $('.remove-extra').removeClass('d-none');
            }else{
                $('.remove-extra').addClass('d-none');
            }
        });

        $(document).on('click', '.remove-extra' ,function(){

            var parent = $(this).parents('.extra');
            parent.slideUp('slow', function(){
                this.remove();
            });

        });

        $("input[name='base_price']").on('click',function(){
            if($(this).val()==0){
                $(this).val('');
            }
        });

        if($(document).find('input[name="has_variants"]').prop("checked") == true){
            $(document).find('.sku-wrapper').hide();
        }

        $('input[name="has_variants"]').on('click',function(){
            if($(this).prop("checked") == true){
                $('.sku-wrapper').hide('slow');
                $(document).find('input[name="sku"]').val('');

            }
            else if($(this).prop("checked") == false){
                $('.sku-wrapper').show('slow');
                $(document).find('input[name="sku"]').val('');
            }
        });

    })(jQuery)

</script>

@endpush
