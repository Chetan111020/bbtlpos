@extends('layouts.app')
@section('title', __('sale.products'))

@section('content')
 <style>
        .table-ab thead tr th,
        .table-ab tbody tr td {
            border: none;
        }

        .Explore-Button {
            width: fit-content;
            /* height: 45px; */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px 15px;
            gap: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;

            background: linear-gradient(120deg, rgb(65, 43, 95), rgb(96, 51, 154));
        }

        .IconContainer {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 25px;
            height: 25px;
        }

        .telescope {
            width: 100%;
            height: auto;
            transform-origin: center;
            transition: all 1s;
            transform: rotate(20deg);
        }

        .tripod {
            width: 60%;
            height: auto;
        }

        .text {
            color: rgb(240, 240, 240);
            font-weight: 500;
            font-size: 16px;
            letter-spacing: 1px;
        }

        .Explore-Button:hover .telescope {
            transform: rotate(-35deg);
        }
    </style>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('sale.products')
            <small>@lang('lang_v1.manage_products')</small>
        </h1>
        <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('type', __('product.product_type') . ':') !!}
                            {!! Form::select('type', ['single' => __('lang_v1.single'), 'variable' => __('lang_v1.variable')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_type', 'placeholder' => __('lang_v1.all')]); !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('category_id', __('product.category') . ':') !!}
                            {!! Form::select('category_id', $categoriesdata, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_category_id', 'placeholder' => __('lang_v1.all')]); !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('sub_category_id_filter', __('product.sub_category') . ':') !!}
                            <select name="sub_category_id_filter" id="sub_category_id_filter" class="form-control select2 input-sm sub_category_id_filter" style="width: 100%;">
                                <option value="0">None</option>
                            </select>

                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('unit_id', __('product.unit') . ':') !!}
                            {!! Form::select('unit_id', $units, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_unit_id', 'placeholder' => __('lang_v1.all')]); !!}
                        </div>
                    </div>
                    {{--<div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('tax_id', __('product.tax') . ':') !!}
                            {!! Form::select('tax_id', $taxes, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_tax_id', 'placeholder' => __('lang_v1.all')]); !!}
                        </div>
                    </div>--}}
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('brand_id', __('product.brand') . ':') !!}
                            {!! Form::select('brand_id', $brands, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_brand_id', 'placeholder' => __('lang_v1.all')]); !!}
                        </div>
                    </div>
                    <div class="col-md-3" id="location_filter">
                        <div class="form-group">
                            {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                            {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                        </div>
                    </div>
                    <div class="col-md-3" style="">
                        <br>
                        <div class="form-group">
                            {!! Form::select('active_state', ['active' => __('business.is_active'), 'inactive' => __('lang_v1.inactive')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'active_state']); !!}
                        </div>
                    </div>

                    <!-- include module filter -->
                    @if(!empty($pos_module_data))
                        @foreach($pos_module_data as $key => $value)
                            @if(!empty($value['view_path']))
                                @includeIf($value['view_path'], ['view_data' => $value['view_data']])
                            @endif
                        @endforeach
                    @endif

                    <div class="col-md-3">
                        <div class="form-group">
                            <br>
                            <label>
                                {!! Form::checkbox('not_for_selling', 1, false, ['class' => 'input-icheck', 'id' => 'not_for_selling']); !!}
                                <strong>Not For Selling</strong>
                            </label>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    @if($is_woocommerce)
                        <div class="col-md-3">
                            <div class="form-group">
                                <br>
                                <label>
                                    {!! Form::checkbox('woocommerce_enabled', 1, false,
                                    [ 'class' => 'input-icheck', 'id' => 'woocommerce_enabled']); !!} {{ __('lang_v1.woocommerce_enabled') }}
                                </label>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-3">
                        <div class="form-group">
                            <br>
                            <label>
                                {!! Form::checkbox('exact_search', 1, false,
                                [ 'class' => 'input-icheck', 'id' => 'exact_search']); !!} Exact Search
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <hr>
                        <table class="table table-ab">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Column:</th>
                                    <th style="width: 30%;">Operator:</th>
                                    <th style="width: 30%">Value:</th>
                                    <th style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="form-group">
                                            <select class="form-control select2" name="column" id="column">
                                                <option value="" selected>Select</option>
                                                @foreach ($customNames as $column => $displayName)
                                                    <option value="{{ $column }}">{{ $displayName }}</option>
                                                @endforeach
                                                @foreach ($rawFields as $column => $displayName)
                                                    <option value="{{ $column }}">{{ $displayName }}</option>
                                                @endforeach
                                                <option value="tier_price_1">Tier Price 1</option>
                                                <option value="tier_price_2">Tier Price 2</option>
                                                <option value="tier_price_3">Tier Price 3</option>
                                                <option value="tier_price_4">Tier Price LI</option>

                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <select name="operator" class="form-control select2" id="operator">
                                                <option value="" selected>Select</option>
                                            {{--    <option value="=">=</option>
                                                <option value="<">&lt;</option>
                                                <option value=">">&gt;</option>
                                                <option value="<=">&lt;=</option>
                                                <option value=">=">&gt;=</option>
                                                <option value="<>">&lt;&gt;</option>
                                                <option value="LIKE">LIKE</option>
                                                <option value="NOT LIKE">NOT LIKE</option>
                                                <option value="IN">IN</option>
                                                <option value="NOT IN">NOT IN</option>
                                                <option value="BETWEEN">BETWEEN</option>
                                                <option value="NOT BETWEEN">NOT BETWEEN</option>
                                                <option value="IS NULL">IS NULL</option>
                                                <option value="IS NOT NULL">IS NOT NULL</option> --}}
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="value" placeholder="VALUE" class="form-control"
                                            id="value">
                                    </td>
                                    <td>
                                        <button class="Explore-Button" id="filterData">
                                            <span class="IconContainer">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 156 78"
                                                    class="telescope">
                                                    <path fill="url(#paint0_linear_131_19)"
                                                        d="M10.3968 78C10.6002 78 32 72.831 32 72.831C29.5031 68.7434 27.3945 63.5193 26.0258 57.947C24.6386 52.3381 24.0837 46.7841 24.3982 42L3.38683 47.0957C0.0205717 47.9206 -1.0152 55.4725 1.09333 63.9959C3.05409 72.0061 7.10469 78 10.3968 78Z">
                                                    </path>
                                                    <path fill="url(#paint1_linear_131_19)"
                                                        d="M63.0824 25L34.8099 32.0351C33.7675 32.2957 32.8714 33.0215 32.1582 34.1382C31.6096 34.9943 31.1524 36.0738 30.8049 37.3393C30.5489 38.2513 30.366 39.2563 30.238 40.3544C29.6894 44.7839 30.0734 50.5348 31.5547 56.6207C33.0177 62.7067 35.2854 67.9925 37.7725 71.6587C38.3942 72.5707 39.016 73.371 39.6561 74.0596C40.5339 75.0274 41.43 75.7718 42.3078 76.2743C43.1307 76.7396 43.9536 77 44.74 77C45.0326 77 45.3252 76.9628 45.5995 76.8883L72.5919 70.1698L74 69.8164C69.867 64.1027 66.6484 56.1184 64.7282 48.1527C62.7532 39.9451 62.1497 31.8306 63.0094 25.3166C63.0458 25.2233 63.0643 25.1117 63.0824 25Z">
                                                    </path>
                                                    <path fill="url(#paint2_linear_131_19)"
                                                        d="M155.865 50.9153L144.361 3.54791C143.844 1.43031 141.964 0 139.88 0C139.512 0 139.143 0.0371509 138.774 0.130028L75.0921 15.8448C74.3361 16.0306 73.654 16.4021 73.0271 16.9594C72.1239 17.7581 71.3493 18.9284 70.7411 20.3958C70.3537 21.3246 70.0403 22.3648 69.7823 23.4979C68.4731 29.2935 68.7683 37.7267 70.9621 46.7544C73.2115 55.9863 76.9358 63.7509 80.8447 68.2277C81.6375 69.1194 82.4303 69.8995 83.2229 70.5125C83.4259 70.6795 83.6654 70.8283 83.9051 70.9581C85.6752 71.9798 87.7955 72.2584 89.7865 71.7571L152.492 56.5065C154.962 55.912 156.474 53.4044 155.865 50.9153Z">
                                                    </path>
                                                    <defs>
                                                        <linearGradient gradientUnits="userSpaceOnUse" y2="78"
                                                            x2="16" y1="42" x1="16"
                                                            id="paint0_linear_131_19">
                                                            <stop stop-color="#6A8EF6"></stop>
                                                            <stop stop-color="#BF8AEB" offset="1"></stop>
                                                        </linearGradient>
                                                        <linearGradient gradientUnits="userSpaceOnUse" y2="77"
                                                            x2="52" y1="25" x1="52"
                                                            id="paint1_linear_131_19">
                                                            <stop stop-color="#6A8EF6"></stop>
                                                            <stop stop-color="#BF8AEB" offset="1"></stop>
                                                        </linearGradient>
                                                        <linearGradient gradientUnits="userSpaceOnUse" y2="72"
                                                            x2="112.5" y1="0" x1="112.5"
                                                            id="paint2_linear_131_19">
                                                            <stop stop-color="#6A8EF6"></stop>
                                                            <stop stop-color="#BF8AEB" offset="1"></stop>
                                                        </linearGradient>
                                                    </defs>
                                                </svg>

                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 104 69"
                                                    class="tripod">
                                                    <path stroke-linecap="round" stroke-width="11"
                                                        stroke="url(#paint0_linear_124_14)" d="M98.4336 63.3406L52 5.99991">
                                                    </path>
                                                    <path stroke-linecap="round" stroke-width="11"
                                                        stroke="url(#paint1_linear_124_14)" d="M52.4336 6L6.00004 63.3407">
                                                    </path>
                                                    <path stroke-linecap="round" stroke-width="11"
                                                        stroke="url(#paint2_linear_124_14)" d="M52 63L52 6"></path>
                                                    <defs>
                                                        <linearGradient gradientUnits="userSpaceOnUse" y2="40.5"
                                                            x2="68" y1="32" x1="77.5"
                                                            id="paint0_linear_124_14">
                                                            <stop stop-color="#8E8DF2"></stop>
                                                            <stop stop-color="#BC8BEC" offset="1"></stop>
                                                        </linearGradient>
                                                        <linearGradient gradientUnits="userSpaceOnUse" y2="40.5174"
                                                            x2="36.4196" y1="32.9922" x1="26.1302"
                                                            id="paint1_linear_124_14">
                                                            <stop stop-color="#8E8DF2"></stop>
                                                            <stop stop-color="#BC8BEC" offset="1"></stop>
                                                        </linearGradient>
                                                        <linearGradient gradientUnits="userSpaceOnUse" y2="34.8174"
                                                            x2="42.7435" y1="34.0069" x1="55.4548"
                                                            id="paint2_linear_124_14">
                                                            <stop stop-color="#8E8DF2"></stop>
                                                            <stop stop-color="#BC8BEC" offset="1"></stop>
                                                        </linearGradient>
                                                    </defs>
                                                </svg>
                                            </span>

                                            <span class="text">Explore</span>
                                        </button>
                                        <!--<button type="submit" class="btn text-white" id="filterData"-->
                                        <!--    style="background-color: black">Filter</button>-->
                                    </td>
                                </tr>
                                  <tr>
                                    <td></td>
                                    <td></td>
                                    <td colspan="2" class="text-danger" id="suggestion"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>
        @can('product.view')
            <div class="row">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#product_list_tab" data-toggle="tab" aria-expanded="true"><i
                                            class="fa fa-cubes" aria-hidden="true"></i> @lang('lang_v1.all_products')
                                </a>
                            </li>

                            <li>
                                <a href="#product_stock_report" data-toggle="tab" aria-expanded="true"><i
                                            class="fa fa-hourglass-half"
                                            aria-hidden="true"></i> @lang('report.stock_report')</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active" id="product_list_tab">
                                @can('product.export')
                                    <a class="btn btn-success pull-right" style="background-color: #22d15b;border-color: #22d15b;" href="{{action('ProductController@downloadExcel')}}">
                                        Export To Excel
                                    </a>
                                @endcan

                                @can('product.create')
                                    <a class="btn btn-primary pull-right" style="margin: 0 10px" href="{{action('ProductController@create')}}">
                                        <i class="fa fa-plus"></i> @lang('messages.add')</a>
                                    <br><br>
                                @endcan
                                @include('product.partials.product_list')
                            </div>

                            <div class="tab-pane" id="product_stock_report">
                                @include('report.partials.stock_report_table')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
        <input type="hidden" id="is_rack_enabled" value="{{$rack_enabled}}">

        <div class="modal fade product_modal" tabindex="-1" role="dialog" data-backdrop="static"
             aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade" id="view_product_modal" tabindex="-1" role="dialog" data-backdrop="static"
             aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade" id="opening_stock_modal" tabindex="-1" role="dialog" data-backdrop="static"
             aria-labelledby="gridSystemModalLabel">
        </div>

        @include('product.partials.edit_product_location_modal')

    </section>
    <!-- /.content -->

    <div class="modal fade" id="bulkeditmodal" tabindex="-1" role="dialog"  aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['url' => action('ProductController@editbulk'), 'method' => 'post', 'id' => 'edit_bulk_form' ]) !!}
                <input type="hidden" name="product_id" id="product_id" value="">
                    <div class="modal-header">
                        <h4 class="modal-title d-inline-block" style="display:inline-block;">Multiple Item Update</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4><span class="add_to_location_title hide">@lang( 'lang_v1.add_location_to_the_selected_products' )</span><span class="remove_from_location_title hide">@lang( 'lang_v1.remove_location_from_the_selected_products' )</span></h4>
                    </div>
                    {{--  <div class="modal-header">
                        <h5 style="text-align:left">Multiple Item Update</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><span class="add_to_location_title hide">@lang( 'lang_v1.add_location_to_the_selected_products' )</span><span class="remove_from_location_title hide">@lang( 'lang_v1.remove_location_from_the_selected_products' )</span></h4>
                    </div>  --}}
                    <div class="modal-body">
                        <div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <span class="input-group-addon"><small>@lang('product.inc_of_tax')</small></span>
                                            {!! Form::text('single_dpp_inc_tax', null, ['class' => 'form-control input-sm dpp_inc_tax input_number', 'id' => 'cost_price', 'placeholder' => __('product.inc_of_tax'), 'required']); !!}
                                        </div>
                                        <div class="col-md-4">
                                            <span class="input-group-addon"><small>@lang('product.exc_of_tax')</small></span>
                                            {!! Form::text('single_dsp', null, ['class' => 'form-control input-sm dsp input_number', 'placeholder' => __('product.exc_of_tax'), 'id' => 'sell_price', 'required']); !!}
                                            <span class="error"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <span class="input-group-addon"><small>@lang('product.profit_percent')</small></span>
                                            {!! Form::text('profit_percent',null, ['class' => 'form-control input-sm input_number', 'id' => 'gross_profit', 'required']); !!}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">

                                            <div class="form-group">
                                                    <br>
                                                    <label class="container-1">
                                                        {!! Form::checkbox('woocommerce_disable_sync', 1,'',['class' => 'checkbox','id' => 'woocommerce_disable_sync']); !!} <span class="checkmark"></span> <strong>Do not sync with website</strong>
                                                    </label>
                                                    @show_tooltip(__('woocommerce::lang.woocommerce_disable_sync_help'))
                                                  </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                    <br>
                                                    <label>
                                                        {!! Form::checkbox('outofstock', 1, null, ['class' => 'input-icheck', 'id' => 'outofstock']); !!} <strong>Mark as out of stock for website</strong>
                                                    </label>

                                                  </div>
                                        </div>
                                        <div class="col-md-4">
                                                <br>
                                            <label class="container-1">
                                                <input type="checkbox" name="notforselling" id="notforselling" style="background-color:white;" class="checkbox" value="1" onclick="synccheck()" > <span class="checkmark"></span> <strong>@lang('lang_v1.not_for_selling')</strong>
                                                {{--  {!! Form::checkbox('notforselling', 1,'', ['class' => 'input-icheck notforselling','id' => 'notforselling']); !!} <strong>@lang('lang_v1.not_for_selling')</strong>  --}}
                                            </label> @show_tooltip(__('lang_v1.tooltip_not_for_selling'))
                                            {{--  <input type="checkbox" id="chkPassport" onclick="chekbox()" />  --}}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <br>
                                            <label>
                                                {!! Form::checkbox('sync_with_woocommerce', 0,'', ['class' => 'input-icheck','id' => 'sync_with_woocommerce']); !!} <strong>@lang('Sync with website')</strong>
                                            </label>  @show_tooltip(__('If checked this product will sync with woocommerce'))
                                        </div>
                                         <div class="col-md-4">
                                            <br>
                                            <label>
                                                {!! Form::checkbox('instock', 0,'', ['class' => 'input-icheck','id' => 'instock']); !!}   <strong>@lang('In Stock')</strong>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                                <br>
                                            <label>
                                                {!! Form::checkbox('forselling', 0,'', ['class' => 'input-icheck','id' => 'forselling']); !!} <strong>@lang('For selling')</strong>

                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <span class="input-group-addon"><small>@lang('product.category')</small></span>
                                            <select name="category_id" id="category_id" class="form-control select2 input-sm category_id" style="width: 100%;">
                                                <option value="0">Please Select</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{$cat->id}}">{{$cat->name}}</option>
                                                @endforeach
                                            </select>
                                            {{-- {!! Form::select('category_id', $categories, !empty($duplicate_product->category_id) ? $duplicate_product->category_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2 input-sm category_id', 'style' => 'width: 100%;','id'=> 'category_id']); !!}  --}}
                                        </div>
                                        <div class="col-md-4">
                                            <span class="input-group-addon"><small>@lang('product.sub_category')</small></span>
                                            <select name="sub_category_id" id="sub_category_id" class="form-control select2 input-sm sub_category_id" style="width: 100%;">
                                                <option value="0">None</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="input-group-addon"><small>@lang('product.brand')</small></span>
                                            <select name="brand_id" id="brand_id" class="form-control select2 input-sm category_id" style="width: 100%;">
                                                <option value="0">Please Select</option>
                                                @foreach($brand_data as $brand)
                                                    <option value="{{$brand->id}}">{{$brand->name}}</option>
                                                @endforeach
                                            </select>
                                            {{-- {!! Form::select('brand_id', $brands, !empty($duplicate_product->brand_id) ? $duplicate_product->brand_id    : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2 input-sm category_id', 'style' => 'width: 100%;','id'=> 'brand_id']); !!} --}}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <br>
                                            <span class="input-group-addon"><small>@lang('lang_v1.weight')</small></span>
                                            {!! Form::text('case_qty', null, ['class' => 'form-control input-sm case_qty', 'id' => 'case_qty', 'placeholder' =>  __('lang_v1.weight'), 'required']); !!}
                                        </div>
                                        <div class="col-md-4">
                                            <br>
                                            <span class="input-group-addon"><small>
                                                {{ isset($price_groups_bulks['79']) ? $price_groups_bulks['79'] : 'MD' }} Price</small>
                                            </span>
                                            {!! Form::text('md_price', null, ['class' => 'form-control input-sm md_price input_number', 'id' => 'md_price', 'placeholder' => isset($price_groups_bulks['79']) ? $price_groups_bulks['79'].' Price' : 'MD Price' , 'required']); !!}
                                        </div>
                                        <div class="col-md-4">
                                            <br>
                                            <span class="input-group-addon"><small>
                                                {{ isset($price_groups_bulks['68']) ? $price_groups_bulks['68'] : 'TIER 1: Retail Store' }} Price</small>
                                            </span>
                                            {!! Form::text('tier1_price', null, ['class' => 'form-control input-sm tier1_price input_number', 'id' => 'tier1_price', 'placeholder' => isset($price_groups_bulks['68']) ? $price_groups_bulks['68'].' Price' : 'TIER 1: Retail Store Price', 'required']); !!}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <br>
                                            <span class="input-group-addon"><small>
                                                {{ isset($price_groups_bulks['69']) ? $price_groups_bulks['69'] : 'TIER 2: Multi Store' }} Price</small>
                                            </span>
                                            {!! Form::text('tier2_price', null, ['class' => 'form-control input-sm tier2_price input_number', 'id' => 'tier2_price', 'placeholder' => isset($price_groups_bulks['69']) ? $price_groups_bulks['69'].' Price' : 'TIER 2: Multi Store Price', 'required']); !!}
                                        </div>
                                        <div class="col-md-4">
                                            <br>
                                            <span class="input-group-addon"><small>
                                                {{ isset($price_groups_bulks['70']) ? $price_groups_bulks['70'] : 'TIER 3: Distributor' }} Price</small>
                                            </span>
                                            {!! Form::text('tier3_price', null, ['class' => 'form-control input-sm tier3_price input_number', 'id' => 'tier3_price', 'placeholder' => isset($price_groups_bulks['70']) ? $price_groups_bulks['70'].' Price' : 'TIER 3: Distributor Price', 'required']); !!}
                                        </div>
                                        <div class="col-md-4">
                                            <br>
                                            <span class="input-group-addon"><small>
                                                {{ isset($price_groups_bulks['80']) ? $price_groups_bulks['80'] : 'TIER LI' }} Price</small>
                                            </span>
                                            {!! Form::text('tier4_price', null, ['class' => 'form-control input-sm tier4_price input_number', 'id' => 'tier4_price', 'placeholder' => isset($price_groups_bulks['80']) ? $price_groups_bulks['80'].' Price' : 'TIER LI: Price', 'required']); !!}
                                        </div>
                                        <div class="col-md-4" style="display:none;">
                                            <br>
                                            <span class="input-group-addon"><small>
                                                {{ isset($price_groups_bulks['71']) ? $price_groups_bulks['71'] : 'TIER RETAIL+' }} Price</small>
                                            </span>
                                            {!! Form::text('tier_price', null, ['class' => 'form-control input-sm tier_price input_number', 'id' => 'tier_price', 'placeholder' => isset($price_groups_bulks['71']) ? $price_groups_bulks['71'].' Price' : 'TIER RETAIL+ Price', 'required']); !!}
                                        </div>
                                        <div class="col-md-4">
                                            <br>
                                            <span class="input-group-addon"><small>
                                                ML:</small>
                                            </span>
                                            {!! Form::text('ml', !empty($product->ml) ? $product->ml : null, ['class' => 'form-control input-upper-case', 'id' => 'ml', 'placeholder' => "ML"]); !!}
                                        </div>

                                         <div class="col-sm-4">
                                        <br>
                                        <span class="input-group-addon">
                                            <small>
                                                Reg. Price</small>
                                        </span>
                                        {!! Form::number('srp', null, ['step'=>'0.01', 'class' => 'form-control srp', 'id' => 'srp','placeholder' => __('Reg. Price')]); !!}
                                    </div>
                                    <div class="col-sm-4">
                                        <br>
                                        <span class="input-group-addon"><small>
                                                Sale Price</small>
                                        </span>
                                        {!! Form::number('sales_price', null, [
                                            'step' => '0.01',
                                            'class' => 'form-control sales_price',
                                            'id' => 'sales_price',
                                            'placeholder' => __('lang_v1.sp'),
                                        ]) !!}
                                    </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12" style="padding-top:15px;">
                                            <Strong>Reset Customer Prices</Strong>
                                        </div>
                                        <div class="col-md-3">
                                            <br>
                                            <label class="container-1">
                                                <input type="checkbox" name="reset_sell" id="reset_sell" style="background-color:white;" class="checkbox" value="0" ><span class="checkmark"></span>  <strong>@lang('Selling Price')</strong>
                                            </label>

                                            {{-- <label>
                                                {!! Form::checkbox('reset_sell', 0,'', ['class' => 'input-icheck','id' => 'reset_sell']); !!}   <strong>Selling Price</strong>
                                            </label> --}}
                                        </div>

                                        <div class="col-md-3">
                                            <br>
                                            <label class="container-1">
                                                <input type="checkbox" name="reset_t1" id="reset_t1" style="background-color:white;" class="checkbox" value="0" > <span class="checkmark"></span>  <strong>@lang('Tier 1 Price')</strong>
                                            </label>
                                            {{-- <label>
                                                {!! Form::checkbox('reset_t1', 0,'', ['class' => 'input-icheck','id' => 'reset_t1']); !!}   <strong>Tier 1 Price</strong>
                                            </label> --}}
                                        </div>

                                        <div class="col-md-3">
                                            <br>
                                            <label class="container-1">

                                            <input type="checkbox" name="reset_t2" id="reset_t2" style="background-color:white;" class="checkbox" value="0" > <span class="checkmark"></span>  <strong>@lang('Tier 2 Price')</strong>
                                            </label>
                                            {{-- <label>
                                                {!! Form::checkbox('reset_t2', 0,'', ['class' => 'input-icheck','id' => 'reset_t2']); !!}   <strong>Tier 2 Price</strong>
                                            </label> --}}
                                        </div>

                                        <div class="col-md-3">
                                            <br>
                                            <label class="container-1">

                                                <input type="checkbox" name="reset_t3" id="reset_t3" style="background-color:white;" class="checkbox" value="0" ><span class="checkmark"></span>  <strong>@lang('Tier 3 Price')</strong>
                                            </label>
                                            {{-- <label>
                                                {!! Form::checkbox('reset_t3', 0,'', ['class' => 'input-icheck','id' => 'reset_t3']); !!}   <strong>Tier 3 Price</strong>
                                            </label> --}}
                                        </div>
                                        <div class="col-md-3">
                                            <br>
                                            <label class="container-1">

                                                <input type="checkbox" name="reset_t4" id="reset_t4" style="background-color:white;" class="checkbox" value="0" ><span class="checkmark"></span>  <strong>@lang('Tier LI Price')</strong>
                                            </label>
                                            {{-- <label>
                                                {!! Form::checkbox('reset_t4', 0,'', ['class' => 'input-icheck','id' => 'reset_t4']); !!}   <strong>Tier LI Price</strong>
                                            </label> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                            <button type="button" class="btn btn-primary submit" id="editbulkupdate">@lang( 'messages.save' )</button>
                            <button type="button" class="btn submit" style="background: aquamarine" id="editbulkupdate_sync">Save & Sync With Website</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>


@endsection

@section('javascript')
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
    
     $(document).ready(function() {
            const operators = [{
                    value: "=",
                    text: "="
                },
                {
                    value: "<",
                    text: "<"
                },
                {
                    value: ">",
                    text: ">"
                },
                {
                    value: "<=",
                    text: "<="
                },
                {
                    value: ">=",
                    text: ">="
                },
                {
                    value: "<>",
                    text: "<>"
                },
                {
                    value: "LIKE",
                    text: "LIKE"
                },
                {
                    value: "NOT LIKE",
                    text: "NOT LIKE"
                },
                {
                    value: "IN",
                    text: "IN"
                },
                {
                    value: "NOT IN",
                    text: "NOT IN"
                },
                {
                    value: "BETWEEN",
                    text: "BETWEEN"
                },
                {
                    value: "NOT BETWEEN",
                    text: "NOT BETWEEN"
                },
                {
                    value: "IS NULL",
                    text: "IS NULL"
                },
                {
                    value: "IS NOT NULL",
                    text: "IS NOT NULL"
                }
            ];

            $('#column').on('change', function() {
                const selectedColumn = $(this).val();
                const $operator = $('#operator');
                const valueInput = $('#value');
                const suggestion = $('#suggestion');

                // Clear the operator dropdown, value input, and suggestion text
                $operator.empty().append('<option value="" selected>Select</option>');
                // valueInput.val('');
                suggestion.text('');
                // valueInput.attr('placeholder', 'VALUE');

                // If a column is selected, populate the operators
                if (selectedColumn) {
                    operators.forEach(operator => {
                        $operator.append(new Option(operator.text, operator.value));
                    });
                }
            });
        });
     
       $('#operator').on('change', function() {
        var selectedOperator = $(this).val();
        var valueInput = $('#value');
        var suggestion = $('#suggestion');

        valueInput.prop('readonly', false);
        

        switch (selectedOperator) {
            case 'LIKE':
            case 'NOT LIKE':
                suggestion.text('Use "%" to search for multiple words (e.g., "%word%").');
                valueInput.attr('placeholder', 'e.g., %word%');
                break;
            case 'IN':
            case 'NOT IN':
                suggestion.text('Use comma-separated values (e.g., "value1,value2,value3").');
                valueInput.attr('placeholder', 'e.g., value1,value2,value3');
                break;
            case 'BETWEEN':
            case 'NOT BETWEEN':
                suggestion.text('Use comma-separated range (e.g., "5,10").');
                valueInput.attr('placeholder', 'e.g., 5,10');
                break;
            case 'IS NULL':
            case 'IS NOT NULL':
                suggestion.text('No value required for this operator.');
                valueInput.val('');
                valueInput.prop('readonly', true);
                valueInput.attr('placeholder', '');
                break;
            default:
                suggestion.text('');
                valueInput.attr('placeholder', 'VALUE');
        }
    });
        
        
    
    $(document).ready(function () {
        $('#bulkeditmodal').on('hide.bs.modal', function (e) {
            $("#forselling").prop("checked", false);

            $("#reset_sell").prop("checked", false);
            $("#reset_t1").prop("checked", false);
            $("#reset_t2").prop("checked", false);
            $("#reset_t3").prop("checked", false);
            $("#reset_t4").prop("checked", false);

        });
    });

        $(document).ready(function () {

            @if(!empty($sync_url))
            toastr.info("Sync in process. Please wait.");
            setTimeout(() => {
                $.ajax({
                    method: 'GET',
                    url: '/woocommerce/sync-products?type=all&offset=0&ids={{ $sync_url }}',
                    dataType: 'json',
                    success: function(result) {
                        if (result.success === 1) {
                            toastr.success(result.msg);
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }, 2500);
            @endif

            product_table = $('#product_table').DataTable({
                  buttons:[
                    {
                        extend: 'csv',
                        footer: true,
                        text: '<span><i class="fa fa-file-csv" aria-hidden="true"></i> Export to CSV</span>',
                    },
                    {
                        extend: 'excel',
                        text: '<span><i class="fa fa-file-excel" aria-hidden="true"></i> Export to Excel</span>',
                        footer: true,
                        exportOptions: {
                            columns: [2,3,4,6,7,8,9,10,11,12,13,14,15]

                        }
                    },
                    {
                        extend: 'print',
                        footer: true,
                        text: '<span><i class="fa fa-print" aria-hidden="true"></i> Print</span>',
                        exportOptions: {
                             columns: [2,3,4,6,7,8,9,10,11,12,13,14,15]
                         }
                    },
                    {
                        extend: 'colvis',
                        text: '<span><i class="fa fa-columns" aria-hidden="true"></i> Column visibility</span>',
                    },
                    {
                        extend: 'pdf',
                        footer: true,
                        text: '<span><i class="fa fa-file-pdf" aria-hidden="true"></i> Export to PDF</span>',
                    },
                    {
                        extend: 'excel',
                        text: '<span><i class="fa fa-file-excel" aria-hidden="true"></i> Stock Export to Excel</span>',
                        footer: true,
                        exportOptions: {
                            columns: [4, 13] 
                        },
                        customize: function(xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            var lastRow = $('row', sheet).last();
                            lastRow.remove();  
                        }
                    },
                ],
                processing: true,
                serverSide: true,
                aaSorting: [[4, 'asc']],
                scrollY: "500px",
                scrollX: true,
                scrollCollapse: true,
                stateSave: true,
                "ajax": {
                    "url": "/products",
                    "data": function (d) {
                        d.type = $('#product_list_filter_type').val();
                        d.category_id = $('#product_list_filter_category_id').val();
                        d.sub_category_id_filter = $('#sub_category_id_filter').val();
                        d.brand_id = $('#product_list_filter_brand_id').val();
                        d.unit_id = $('#product_list_filter_unit_id').val();
                        d.tax_id = $('#product_list_filter_tax_id').val();
                        d.active_state = $('#active_state').val();
                        d.not_for_selling = $('#not_for_selling').is(':checked');
                        d.exact_search = $('#exact_search').is(':checked') ? 1 : 0;
                        d.location_id = $('#location_id').val();
                        if ($('#repair_model_id').length == 1) {
                            d.repair_model_id = $('#repair_model_id').val();
                        }

                        if ($('#woocommerce_enabled').length == 1 && $('#woocommerce_enabled').is(':checked')) {
                            d.woocommerce_enabled = 1;
                        }
                        
                         d.filter_column = $('#column').val();
                        d.filter_operator = $('#operator').val();
                        d.filter_value = $('#value').val();
                        
                        d = __datatable_ajax_callback(d);
                    }
                },
                columnDefs: [{
                    "targets": [0, 1, 2],
                    "orderable": false,
                    "searchable": false
                },
                {
                    "targets": [ 14 ],
                    "visible": false,
                    "searchable": false
                },],
                columns: [
                    {data: 'mass_delete'},
                    {data: 'action', name: 'action'},
                    {data: 'image', name: 'products.main_image'},
                    {data: 'item_code', name: 'products.item_code'},
                    {data: 'product', name: 'products.name'},
                    {data: 'notforselling', name: 'notforselling', searchable: false, orderable: false},
                    {data: 'sku', name: 'products.sku'},
                    @can('view_purchase_price')
                    {
                        data: 'purchase_price', name: 'max_purchase_price', searchable: false
                    },
                    @endcan
                    @can('access_default_selling_price')
                    {
                        data: 'selling_price', name: 'max_price', searchable: false
                    },
                    {
                        data: 'tier_4', name: 'tier_4', searchable: false
                    },
                    {
                        data: 'tier_2', name: 'tier_2', searchable: false
                    },
                    {
                        data: 'tier_3', name: 'tier_3', searchable: false
                    },
                   
                    @endcan
                    {data: 'profit_percent', name: 'profit_percent'},
                    {
                        data: 'current_stock',name:'current_stock', searchable: false
                    },
                    // { data: 'cost', name: 'product.sales_price'},

                    {data: 'item_location', name: 'item_location'},
                    {data: 'qty_box', name: 'qty_box'},

                    {data: 'ml', name: 'ml', visible: false},



                    // {data: 'type', name: 'products.type'},
                    {data: 'category', name: 'c1.name'},
                    {data:'vendor' , name:'vendor'},

                    {data: 'sub_category', name: 'c2.name'},
                    {data: 'case_qty', name: 'case_qty'},
                    {data: 'sku2', name: 'products.sku2'},
                    {data: 'sku3', name: 'products.sku3'},
                    // {data: 'brand', name: 'brands.name'},
                    // {data: 'tax', name: 'tax', searchable: false},
                    // {data: 'product_custom_field1', name: 'products.product_custom_field1'},
                    // {data: 'product_custom_field2', name: 'products.product_custom_field2'},
                    // {data: 'product_custom_field3', name: 'products.product_custom_field3'},
                    // {data: 'product_custom_field4', name: 'products.product_custom_field4'}

                ],
                createdRow: function (row, data, dataIndex) {
                    if ($('input#is_rack_enabled').val() == 1) {
                        var target_col = 0;
                        @can('product.delete')
                            target_col = 1;
                        @endcan
                        $(row).find('td:eq(' + target_col + ') div').prepend('<i style="margin:auto;" class="fa fa-plus-circle text-success cursor-pointer no-print rack-details" title="' + LANG.details + '"></i>&nbsp;&nbsp;');
                    }
                    $(row).find('td:eq(0)').attr('class', 'selectable_td');
                },
                fnDrawCallback: function (oSettings) {
                    __currency_convert_recursively($('#product_table'));
                },
            });
            // Array to track the ids of the details displayed rows
            var detailRows = [];

            $('#product_table tbody').on('click', 'tr i.rack-details', function () {
                var i = $(this);
                var tr = $(this).closest('tr');
                var row = product_table.row(tr);
                var idx = $.inArray(tr.attr('id'), detailRows);

                if (row.child.isShown()) {
                    i.addClass('fa-plus-circle text-success');
                    i.removeClass('fa-minus-circle text-danger');

                    row.child.hide();

                    // Remove from the 'open' array
                    detailRows.splice(idx, 1);
                } else {
                    i.removeClass('fa-plus-circle text-success');
                    i.addClass('fa-minus-circle text-danger');

                    row.child(get_product_details(row.data())).show();

                    // Add to the 'open' array
                    if (idx === -1) {
                        detailRows.push(tr.attr('id'));
                    }
                }
            });

            $('table#product_table tbody').on('click', 'a.delete-product', function (e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).attr('href');
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            success: function (result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    product_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });
 $('#filterData').on('click', function() {
                product_table.ajax.reload();
            });
            $(document).on('click', '#delete-selected', function (e) {
                e.preventDefault();
                var selected_rows = getSelectedRows();

                if (selected_rows.length > 0) {
                    $('input#selected_rows').val(selected_rows);
                    swal({
                        title: LANG.sure,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $('form#mass_delete_form').submit();
                        }
                    });
                } else {
                    $('input#selected_rows').val('');
                    swal('@lang("lang_v1.no_row_selected")');
                }
            });

            $(document).on('click', '#deactivate-selected', function (e) {
                e.preventDefault();
                var selected_rows = getSelectedRows();

                if (selected_rows.length > 0) {
                    $('input#selected_products').val(selected_rows);
                    swal({
                        title: LANG.sure,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            var form = $('form#mass_deactivate_form')

                            var data = form.serialize();
                            $.ajax({
                                method: form.attr('method'),
                                url: form.attr('action'),
                                dataType: 'json',
                                data: data,
                                success: function (result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        product_table.ajax.reload();
                                        form
                                            .find('#selected_products')
                                            .val('');
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                } else {
                    $('input#selected_products').val('');
                    swal('@lang("lang_v1.no_row_selected")');
                }
            })

            $(document).on('click', '#edit-selected', function (e) {
                e.preventDefault();
                var selected_rows = getSelectedRows();

                if (selected_rows.length > 0) {
                    $('input#selected_products_for_edit').val(selected_rows);
                    $("#product_id").val(selected_rows);
                    $("#bulkeditmodal").modal("show");

                } else {
                    $('input#selected_products').val('');
                    swal('@lang("lang_v1.no_row_selected")');
                }
            })

            $('table#product_table tbody').on('click', 'a.activate-product', function (e) {
                e.preventDefault();
                var href = $(this).attr('href');
                $.ajax({
                    method: "get",
                    url: href,
                    dataType: "json",
                    success: function (result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            product_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            $(document).on('change', '#product_list_filter_type, #product_list_filter_category_id, #sub_category_id_filter , #product_list_filter_brand_id, #product_list_filter_unit_id, #product_list_filter_tax_id, #location_id, #active_state, #repair_model_id',
                function () {
                    if ($("#product_list_tab").hasClass('active')) {
                        product_table.ajax.reload();
                    }

                    if ($("#product_stock_report").hasClass('active')) {
                        stock_report_table.ajax.reload();
                    }
                });

            $(document).on("click", "#select-all-row", function(){
                $(".row-select").attr('checked', this.checked);
            });

            $(document).on('ifChanged', '#not_for_selling, #woocommerce_enabled, #exact_search', function () {
                if ($("#product_list_tab").hasClass('active')) {
                    product_table.ajax.reload();
                }

                if ($("#product_stock_report").hasClass('active')) {
                    stock_report_table.ajax.reload();
                }
            });

            $('#product_location').select2({dropdownParent: $('#product_location').closest('.modal')});
        });

        $(document).on('shown.bs.modal', 'div.view_product_modal, div.view_modal',
            function () {
                var div = $(this).find('#view_product_stock_details');
                if (div.length) {
                    $.ajax({
                        url: "{{action('ReportController@getStockReport')}}" + '?for=view_product&product_id=' + div.data('product_id'),
                        dataType: 'html',
                        success: function (result) {
                            div.html(result);
                            __currency_convert_recursively(div);
                        },
                    });
                }
                __currency_convert_recursively($(this));
            });
        var data_table_initailized = false;
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if ($(e.target).attr('href') == '#product_stock_report') {
                if (!data_table_initailized) {
                    //Stock report table
                    var stock_report_cols = [
                        {data: 'sku', name: 'variations.sub_sku'},
                        {data: 'product', name: 'p.name'},
                        { data: 'location_name', name: 'l.name' ,visible:false },
                        {data: 'unit_price', name: 'variations.sell_price_inc_tax'},
                        {data: 'stock', name: 'stock', searchable: false},
                            @can('view_product_stock_value')
                        {
                            data: 'stock_price', name: 'stock_price', searchable: false
                        },
                        {
                            data: 'stock_value_by_sale_price',
                            name: 'stock_value_by_sale_price',
                            searchable: false,
                            orderable: false
                        },
                        {data: 'potential_profit', name: 'potential_profit', searchable: false, orderable: false},
                            @endcan
                        {
                            data: 'total_sold', name: 'total_sold', searchable: false
                        },
                        {data: 'total_transfered', name: 'total_transfered', searchable: false},
                        {data: 'total_adjusted', name: 'total_adjusted', searchable: false}
                    ];
                    if ($('th.current_stock_mfg').length) {
                        stock_report_cols.push({data: 'total_mfg_stock', name: 'total_mfg_stock', searchable: false});
                    }
                    stock_report_table = $('#stock_report_table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '/reports/stock-report',
                            data: function (d) {
                                d.location_id = $('#location_id').val();
                                d.category_id = $('#product_list_filter_category_id').val();
                                d.brand_id = $('#product_list_filter_brand_id').val();
                                d.unit_id = $('#product_list_filter_unit_id').val();
                                d.type = $('#product_list_filter_type').val();
                                d.active_state = $('#active_state').val();
                                d.not_for_selling = $('#not_for_selling').is(':checked');
                                if ($('#repair_model_id').length == 1) {
                                    d.repair_model_id = $('#repair_model_id').val();
                                }
                            }
                        },
                        columns: stock_report_cols,
                        fnDrawCallback: function (oSettings) {
                            $('#footer_total_stock').html(__sum_stock($('#stock_report_table'), 'current_stock'));
                            $('#footer_total_sold').html(__sum_stock($('#stock_report_table'), 'total_sold'));
                            $('#footer_total_transfered').html(
                                __sum_stock($('#stock_report_table'), 'total_transfered')
                            );
                            $('#footer_total_adjusted').html(
                                __sum_stock($('#stock_report_table'), 'total_adjusted')
                            );
                            var total_stock_price = sum_table_col($('#stock_report_table'), 'total_stock_price');
                            var total_stock_value_by_sale_price = sum_table_col($('#stock_report_table'), 'stock_value_by_sale_price');
                            $('#footer_stock_value_by_sale_price').text(total_stock_value_by_sale_price);

                            var total_potential_profit = sum_table_col($('#stock_report_table'), 'potential_profit');
                            $('#footer_potential_profit').text(total_potential_profit);

                            $('#footer_total_stock_price').text(total_stock_price);
                            __currency_convert_recursively($('#stock_report_table'));
                        },
                    });
                    data_table_initailized = true;
                } else {
                    stock_report_table.ajax.reload();
                }
            } else {
                product_table.ajax.reload();
            }
        });

        function getSelectedRows() {
            var selected_rows = [];
            var i = 0;
            $('.row-select:checked').each(function () {
                selected_rows[i++] = $(this).val();
            });

            return selected_rows;
        }

        $(document).on('click', '.update_product_location', function (e) {
            e.preventDefault();
            var selected_rows = getSelectedRows();

            if (selected_rows.length > 0) {
                $('input#selected_products').val(selected_rows);
                var type = $(this).data('type');
                var modal = $('#edit_product_location_modal');
                if (type == 'add') {
                    modal.find('.remove_from_location_title').addClass('hide');
                    modal.find('.add_to_location_title').removeClass('hide');
                } else if (type == 'remove') {
                    modal.find('.add_to_location_title').addClass('hide');
                    modal.find('.remove_from_location_title').removeClass('hide');
                }

                modal.modal('show');
                modal.find('#product_location').select2({dropdownParent: modal});
                modal.find('#product_location').val('').change();
                modal.find('#update_type').val(type);
                modal.find('#products_to_update_location').val(selected_rows);
            } else {
                $('input#selected_products').val('');
                swal('@lang("lang_v1.no_row_selected")');
            }
        });

        $(document).on('submit', 'form#edit_product_location_form', function (e) {
            e.preventDefault();
            $(this)
                .find('button[type="submit"]')
                .attr('disabled', true);
            var data = $(this).serialize();

            $.ajax({
                method: $(this).attr('method'),
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function (result) {
                    if (result.success == true) {
                        $('div#edit_product_location_modal').modal('hide');
                        toastr.success(result.msg);
                        product_table.ajax.reload();
                        $('form#edit_product_location_form')
                            .find('button[type="submit"]')
                            .attr('disabled', false);
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });

        $("#cost_price").change(function(){
            $("#sell_price").prop("disabled", true);
            $("#gross_profit").prop("disabled",true);
            $("#tier_price").prop("disabled",true);
            $("#tier1_price").prop("disabled",true);
            $("#tier2_price").prop("disabled",true);
            $("#tier3_price").prop("disabled",true);
            $("#tier4_price").prop("disabled",true);
            $("#md_price").prop("disabled",true);
        });
        $("#sell_price").change(function(){
            $("#cost_price").prop("disabled", true);
            $("#gross_profit").prop("disabled",true);
            // $("#tier_price").prop("disabled",true);
            // $("#tier1_price").prop("disabled",true);
            // $("#tier2_price").prop("disabled",true);
            // $("#tier3_price").prop("disabled",true);
            // $("#tier4_price").prop("disabled",true);
            // $("#md_price").prop("disabled",true);
        });
        $("#gross_profit").change(function(){
            $("#sell_price").prop("disabled", true);
            $("#cost_price").prop("disabled",true);
            $("#tier_price").prop("disabled",true);
            $("#tier1_price").prop("disabled",true);
            $("#tier2_price").prop("disabled",true);
            $("#tier3_price").prop("disabled",true);
            $("#tier4_price").prop("disabled",true);
            $("#md_price").prop("disabled",true);
        });

        $("#tier_price").change(function(){
            $("#sell_price").prop("disabled", true);
            $("#cost_price").prop("disabled",true);
            $("#gross_profit").prop("disabled",true);
        });

        $("#tier1_price").change(function(){
            $("#sell_price").prop("disabled", true);
            $("#cost_price").prop("disabled",true);
            $("#gross_profit").prop("disabled",true);
        });

        $("#tier2_price").change(function(){
            $("#sell_price").prop("disabled", true);
            $("#cost_price").prop("disabled",true);
            $("#gross_profit").prop("disabled",true);
        });

        $("#tier3_price").change(function(){
            $("#sell_price").prop("disabled", true);
            $("#cost_price").prop("disabled",true);
            $("#gross_profit").prop("disabled",true);
        });
        
        $("#tier4_price").change(function(){
            $("#sell_price").prop("disabled", true);
            $("#cost_price").prop("disabled",true);
            $("#gross_profit").prop("disabled",true);
        });

        $("#md_price").change(function(){
            $("#sell_price").prop("disabled", true);
            $("#cost_price").prop("disabled",true);
            $("#gross_profit").prop("disabled",true);
        });


        function synccheck(){
            if($("#notforselling").prop("checked") == true){
                $("#woocommerce_disable_sync").prop("checked", true);
                console.log("Checkbox is checked.");
            }
            else if($("#notforselling").prop("checked") == false){
                $("#woocommerce_disable_sync").prop("checked", false);
                console.log("Checkbox is unchecked.");
            }
        }

        $("#editbulkupdate").on('click',function(){

            if($("#notforselling").prop('checked') == true){
             var not_for_selling =  $("#notforselling").val()
            }

            if($("#woocommerce_disable_sync").prop('checked') == true){
                var woocommerce_disable_sync = $("#woocommerce_disable_sync").val()
            }

            if($("#outofstock").prop('checked') == true){
                var outofstock = $("#outofstock").val()
            }

            if($("#sync_with_woocommerce").prop('checked') == true){
                var sync_with_woocommerce = $("#sync_with_woocommerce").val();
            }
            if($("#forselling").prop('checked') == true){
                var forselling = $("#forselling").val();
            }
            if($("#instock").prop('checked') == true){
                var instock = $("#instock").val();
            }

            var sell_reset = $('#reset_sell').prop('checked') ? 1 : 0;
            var t1_reset = $('#reset_t1').prop('checked') ? 1 : 0;
            var t2_reset = $('#reset_t2').prop('checked') ? 1 : 0;
            var t3_reset = $('#reset_t3').prop('checked') ? 1 : 0;
            var t4_reset = $('#reset_t4').prop('checked') ? 1 : 0;

            var formData = {
                sell_reset: sell_reset,
                t1_reset: t1_reset,
                t2_reset: t2_reset,
                t3_reset: t3_reset,
                t4_reset: t4_reset,
                cost_price: $("#cost_price").val(),
                sell_price: $("#sell_price").val(),
                gross_profit: $("#gross_profit").val(),
                tier_price_bulk: $("#tier_price").val(),
                tier1_price_bulk: $("#tier1_price").val(),
                tier2_price_bulk: $("#tier2_price").val(),
                tier3_price_bulk: $("#tier3_price").val(),
                tier4_price_bulk: $("#tier4_price").val(),
                md_price_bulk: $("#md_price").val(),
                woocommerce_disable_sync: woocommerce_disable_sync,
                not_for_selling: not_for_selling,
                category_id: $("#category_id").val(),
                sub_category_id: $("#sub_category_id").val(),
                product_id: $("#product_id").val(),
                brand_id: $("#brand_id").val(),
                ml: $("#ml").val(),
                outofstock: outofstock,
                sync_with_woocommerce: sync_with_woocommerce,
                forselling: forselling,
                instock: instock,
                case_qty: $("#case_qty").val(),
                srp: $("#srp").val(),
                sales_price: $("#sales_price").val()
              };

              $.ajax({
                method: 'POST',
                url:"{{ url('products/edit-bulk') }}",
                data:formData ,
                success: function(result) {
                   if (result.success == true) {
                        toastr.success(result.msg);
                        product_table.ajax.reload();
                        $("#bulkeditmodal").modal('hide');
                        $("#cost_price").prop("disabled", false);
                        $("#gross_profit").prop("disabled",false);
                        $("#sell_price").prop("disabled", false);
                        $("#tier_price").prop("disabled", false);
                        $("#tier1_price").prop("disabled", false);
                        $("#tier2_price").prop("disabled", false);
                        $("#tier3_price").prop("disabled", false);
                        $("#tier4_price").prop("disabled", false);
                        $("#md_price").prop("disabled", false);
                        $("#cost_price").val('');
                        $("#gross_profit").val('');
                        $("#sell_price").val('');
                        $("#tier_price").val('');
                        $("#tier1_price").val('');
                        $("#tier2_price").val('');
                        $("#tier3_price").val('');
                        $("#tier4_price").val('');
                        $("#md_price").val('');
                        $("#product_id").val('');
                        $("#notforselling").prop("checked", false);
                        $("#woocommerce_disable_sync").prop("checked", false);
                        $("#outofstock").prop("checked", false);
                        $("#sync_with_woocommerce").prop("checked", false);
                        $("#forselling").prop("checked", false);
                        $("#category_id").val([0]).trigger("change");
                        $("#brand_id").val([0]).trigger("change");
                        $("#case_qty").val('');
                        $("#srp").val();
                        $("#sales_price").val();
                    } else {
                        toastr.error(result.msg);
                    }


                 },
            });
        });
        $("#editbulkupdate_sync").on('click',function(){

            if($("#notforselling").prop('checked') == true){
            var not_for_selling =  $("#notforselling").val()
            }

            if($("#woocommerce_disable_sync").prop('checked') == true){
                var woocommerce_disable_sync = $("#woocommerce_disable_sync").val()
            }

            if($("#outofstock").prop('checked') == true){
                var outofstock = $("#outofstock").val()
            }

            if($("#sync_with_woocommerce").prop('checked') == true){
                var sync_with_woocommerce = $("#sync_with_woocommerce").val();
            }
            if($("#forselling").prop('checked') == true){
                var forselling = $("#forselling").val();
            }
            if($("#instock").prop('checked') == true){
                var instock = $("#instock").val();
            }

            var sell_reset = $('#reset_sell').prop('checked') ? 1 : 0;
            var t1_reset = $('#reset_t1').prop('checked') ? 1 : 0;
            var t2_reset = $('#reset_t2').prop('checked') ? 1 : 0;
            var t3_reset = $('#reset_t3').prop('checked') ? 1 : 0;
            var t4_reset = $('#reset_t4').prop('checked') ? 1 : 0;

            var formData = {
                sell_reset: sell_reset,
                t1_reset: t1_reset,
                t2_reset: t2_reset,
                t3_reset: t3_reset,
                t4_reset: t4_reset,
                cost_price: $("#cost_price").val(),
                sell_price: $("#sell_price").val(),
                gross_profit: $("#gross_profit").val(),
                tier_price_bulk: $("#tier_price").val(),
                tier1_price_bulk: $("#tier1_price").val(),
                tier2_price_bulk: $("#tier2_price").val(),
                tier3_price_bulk: $("#tier3_price").val(),
                tier4_price_bulk: $("#tier4_price").val(),
                md_price_bulk: $("#md_price").val(),
                woocommerce_disable_sync: woocommerce_disable_sync,
                not_for_selling: not_for_selling,
                category_id: $("#category_id").val(),
                sub_category_id: $("#sub_category_id").val(),
                product_id: $("#product_id").val(),
                brand_id: $("#brand_id").val(),
                ml: $("#ml").val(),
                outofstock: outofstock,
                sync_with_woocommerce: sync_with_woocommerce,
                forselling: forselling,
                instock: instock,
                case_qty: $("#case_qty").val(),
                srp: $("#srp").val(),
                sales_price: $("#sales_price").val()
            };

            $.ajax({
                method: 'POST',
                url:"{{ url('products/edit-bulk') }}",
                data:formData ,
                success: function(result) {
                if (result.success == true) {
                        toastr.success(result.msg);

                        var selected_rows = getSelectedRows();
                        if (selected_rows.length > 0) {
                            var data = selected_rows.join('-');
                            $.ajax({
                                method: 'POST',
                                url: '/woocommerce/sync-select-products',
                                dataType: 'json',
                                data: {
                                    'ids': data,
                                    'type' : 'all',
                                    'offset' : 0
                                },
                                success: function (result) {
                                    if (result.success == 1) {
                                        toastr.success(result.msg);
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                    product_table.ajax.reload();
                                },
                                error: function (jqXHR, exception) {
                                    product_table.ajax.reload();
                                },
                            });
                        }
                        else {
                            toastr.warning('@lang("lang_v1.no_row_selected")');
                            product_table.ajax.reload();
                        }

                        $("#bulkeditmodal").modal('hide');
                        $("#cost_price").prop("disabled", false);
                        $("#gross_profit").prop("disabled",false);
                        $("#sell_price").prop("disabled", false);
                        $("#tier_price").prop("disabled", false);
                        $("#tier1_price").prop("disabled", false);
                        $("#tier2_price").prop("disabled", false);
                        $("#tier3_price").prop("disabled", false);
                        $("#tier4_price").prop("disabled", false);
                        $("#md_price").prop("disabled", false);
                        $("#cost_price").val('');
                        $("#gross_profit").val('');
                        $("#sell_price").val('');
                        $("#tier_price").val('');
                        $("#tier1_price").val('');
                        $("#tier2_price").val('');
                        $("#tier3_price").val('');
                        $("#tier4_price").val('');
                        $("#md_price").val('');
                        $("#product_id").val('');
                        $("#notforselling").prop("checked", false);
                        $("#woocommerce_disable_sync").prop("checked", false);
                        $("#outofstock").prop("checked", false);
                        $("#sync_with_woocommerce").prop("checked", false);
                        $("#forselling").prop("checked", false);
                        $("#category_id").val([0]).trigger("change");
                        $("#brand_id").val([0]).trigger("change");
                        $("#case_qty").val('');
                        $('#reset_sell').prop('disabled',false).trigger("change");
                        $("#srp").val();
                        $("#sales_price").val();
                    } else {
                        toastr.error(result.msg);
                    }


                },
            });
        });

        $(document).on('click', '.sync-selected', function (e) {
            e.preventDefault();
            var selected_rows = getSelectedRows();

            if (selected_rows.length > 0) {
                var data = selected_rows.join('-');
                $('.sync-selected').prop('disabled', true);
                $.ajax({
                    method: 'POST',
                    url: '/woocommerce/sync-select-products',
                    dataType: 'json',
                    data: {
                        'ids': data,
                        'type' : 'all',
                        'offset' : 0
                    },
                    success: function (result) {
                        if (result.success == 1) {
                            toastr.success(result.msg);
                        } else {
                            toastr.error(result.msg);
                        }
                        $('.sync-selected').prop('disabled', false);
                    },
                    error: function (jqXHR, exception) {
                        $('.sync-selected').prop('disabled', false);
                    },
                });
            }
            else {
                toastr.warning('@lang("lang_v1.no_row_selected")');
            }
        });
        // bulk edit start
        $(document).on('change', 'select.category_id', function() {
            var cat = $(this).val();
            if(cat==0)
            {
                $('#sub_category_id').html('<option value="">None</option>');
            }
            var tr = $(this).closest('tr');
            $.ajax({
                method: 'POST',
                url: '/products/get_sub_categories',
                dataType: 'html',
                data: { cat_id: cat },
                success: function(result) {
                    if (result) {
                        $('#sub_category_id').html(result);
                    }
                },
            });
        });
        // bulk edit end

        // //action droupdown open
        // (function () {
        //     var dropdownMenu;
        //     $(window).on('show.bs.dropdown', function (e) {
        //     dropdownMenu =  $('div .btn-group').find('.dropdown-menu');
        //     $('body').append(dropdownMenu.detach());

        //     var eOffset = $(e.target).offset();

        //     dropdownMenu.css({
        //         'display': 'block',
        //             'top': eOffset.top + $(e.target).outerHeight(),
        //             'left': eOffset.left
        //       });
        //     });

        //     $(window).on('hide.bs.dropdown', function (e) {
        //         $(e.target).append(dropdownMenu.detach());
        //         dropdownMenu.hide();
        //     });
        // })();

        $(document).on('click', '.rmbg-selected', function (e) {
            e.preventDefault();
            var selected_rows = getSelectedRows();

            if(selected_rows.length > 100){
                toastr.warning('Please select less than or equal to 100 products.')
            }
            else if(selected_rows.length > 0) {
                var data = selected_rows.join('-');
                $('.rmbg-selected').prop('disabled', true);
                $.ajax({
                    method: 'POST',
                    url: '/api-clipdrop/remove-bg',
                    dataType: 'json',
                    data: {
                        'ids': data
                    },
                    success: function (result) {
                        if (result.success == 1) {
                            toastr.success(result.msg);
                            product_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                        $('.rmbg-selected').prop('disabled', false);
                    },
                    error: function (jqXHR, exception) {
                        $('.rmbg-selected').prop('disabled', false);
                    },
                });
            }
            else {
                toastr.warning('@lang("lang_v1.no_row_selected")');
            }
        });

        $(document).on('change', '#product_list_filter_category_id', function() {
            // get_sub_categories();
            var cat = $('#product_list_filter_category_id').val();
            // if(cat==0)
            // {
            //     $('#sub_category_id_filter').html('<option value="">None</option>');
            // }
            var tr = $(this).closest('tr');
            $.ajax({
                method: 'POST',
                url: '/products/get_sub_categories',
                dataType: 'html',
                data: { cat_id: cat },
                success: function(result) {
                    if (result) {
                        console.log(result);
                        $('#sub_category_id_filter').html(result);
                    }
                },
            });
        });
        
    </script>
@endsection