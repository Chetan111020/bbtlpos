@extends('layouts.app')
@section('title', __('product.edit_product'))
@section('content')
<!-- Content Header (Page header) -->
<!-- {{var_dump($product)}} -->
<section class="content-header">
    <h1>@lang('product.edit_product')</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
        </ol> -->
</section>
<!-- Main content -->
<section class="content">
    {!! Form::open(['url' => action('ProductController@update' , [$product->id] ), 'method' => 'PUT', 'id' => 'product_add_form',
    'class' => 'product_form', 'files' => true ]) !!}
    <input type="hidden" id="product_id" value="{{ $product->id }}">
    @component('components.widget', ['class' => 'box-primary'])
    <div class="row">
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('type', __('product.product_type') . ':*') !!} @show_tooltip(__('tooltip.product_type'))
                {!! Form::select('type', $product_types, $product->type, ['class' => 'form-control select2',
                'required','disabled', 'data-action' => 'edit', 'data-product_id' => $product->id ]); !!}
            </div>
        </div>
         <div class="col-sm-3 ">
            <div class="form-group">
                {!! Form::label('code',  'Item Code: (Optional)') !!}
                {!! Form::text('code', $product->item_code, ['class' => 'form-control input-upper-case', 'id' => 'gen-item-code',
                'placeholder' => 'Item Code']); !!}
            </div>
            <span style="color:red" class="already-exists-itemcode"></span>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('name', __('product.product_name') . ':*') !!}
                {!! Form::text('name', $product->name, ['class' => 'form-control input-upper-case', 'required',
                'placeholder' => __('product.product_name')]); !!}
            </div>
        </div>
            {{--
            <div class="col-sm-3 hidden ">
                <div class="form-group">
                    {!! Form::label('item_code',  'Item Code:*') !!}
                    <div class="input-group">
                        {!! Form::text('item_code', $product->item_code, ['class' => 'form-control', 'id' => 'gen-product-code','required',
                        'placeholder' => __('product.item_code')]); !!}
                        <span class="input-group-btn">
                        <button type="button" class="btn btn-default bg-white btn-flat btn-modal" id="generate-item-code" onclick="gencode()" ><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            --}}
            <div class="col-sm-3 variation-select @if(!(session('business.enable_category') && session('business.enable_sub_category')) || $product->type == 'variable') hide @endif">
                <div class="form-group">
                        {!! Form::label('sku', __('Barcode Number1')  . ':') !!} @show_tooltip(__('tooltip.sku'))
                    <div class="input-group">

                    {!! Form::text('sku', $product->sku, ['class' => 'form-control', 'id' => 'gen-bar-code',
                    'placeholder' => __('Barcode Number1')]); !!}
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default bg-white btn-flat btn-modal" id="generate-bar-code" onclick="barcode()" ><i class="fa fa-sync text-primary fa-lg"></i></button>
                    </span>
                    </div>
                </div>
                <span style="color:red" class="already-exists-barcode"></span>
            </div>

            <div class="col-sm-3 variation-select @if(!(session('business.enable_category') && session('business.enable_sub_category')) || $product->type == 'variable') hide @endif">
                <div class="form-group">
                    {!! Form::label('sku2', __('Barcode Number2')  . ':') !!}
                    <div class="input-group">
                    {!! Form::text('sku2', $product->sku2, ['class' => 'form-control', 'id' => 'gen-bar-code2',
                    'placeholder' => __('Barcode Number2')]); !!}
                    </div>
                </div>
                <span style="color:red" class="already-exists-barcode2"></span>
            </div>

            <div class="col-sm-3 variation-select @if(!(session('business.enable_category') && session('business.enable_sub_category')) || $product->type == 'variable') hide @endif">
                <div class="form-group">
                        {!! Form::label('sku3', __('Barcode Number3')  . ':') !!}
                    <div class="input-group">

                    {!! Form::text('sku3', $product->sku3, ['class' => 'form-control', 'id' => 'gen-bar-code3',
                    'placeholder' => __('Barcode Number3')]); !!}
                    </div>
                </div>
                <span style="color:red" class="already-exists-barcode3"></span>
            </div>

        <!-- <div class="col-sm-4">
            <div class="form-group">
              {{-- {!! Form::label('barcode_type', __('product.barcode_type') . ':*') !!}
                {!! Form::select('barcode_type', $barcode_types, $product->barcode_type, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2', 'required']); !!} --}}
            </div>
            </div> -->
         <div class="col-sm-3">
                <div class="form-group">
                    {!! Form::label('unit_id', __('product.unit') . ':*') !!}
                    <div class="input-group">
                        {!! Form::select('unit_id', $units, $product->unit_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2', 'required']); !!}
                        <span class="input-group-btn">
                        <button type="button" @if(!auth()->user()->can('unit.create')) disabled @endif class="btn btn-default bg-white btn-flat quick_add_unit btn-modal" data-href="{{action('UnitController@create', ['quick_add' => true])}}" title="@lang('unit.add_unit')" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                        </span>
                    </div>
                </div>
            </div>
        <div class="col-sm-3 @if(!session('business.enable_sub_units')) hide @endif">
            <div class="form-group">
                {!! Form::label('sub_unit_ids', __('lang_v1.related_sub_units') . ':') !!} @show_tooltip(__('lang_v1.sub_units_tooltip'))
                <select name="sub_unit_ids[]" class="form-control select2" multiple id="sub_unit_ids">
                @foreach($sub_units as $sub_unit_id => $sub_unit_value)
                <option value="{{$sub_unit_id}}"
                @if(is_array($product->sub_unit_ids) &&in_array($sub_unit_id, $product->sub_unit_ids))   selected
                @endif
                >{{$sub_unit_value['name']}}</option>
                @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-3 @if(!session('business.enable_brand')) hide @endif">
            <div class="form-group">
                {!! Form::label('brand_id', __('product.brand') . ':') !!}
                <div class="input-group">
                    {!! Form::select('brand_id', $brands, $product->brand_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
                    <span class="input-group-btn">
                    <button type="button" @if(!auth()->user()->can('brand.create')) disabled @endif class="btn btn-default bg-white btn-flat btn-modal" data-href="{{action('BrandController@create', ['quick_add' => true])}}" title="@lang('brand.add_brand')" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                    </span>
                </div>
            </div>
        </div>

        <!-- <div class="clearfix"></div> -->
        <!-- <div class="col-sm-4 @if(!session('business.enable_category')) hide @endif">
            <div class="form-group">
              {!! Form::label('category_id', __('product.category') . ':') !!}
                {!! Form::select('category_id', $categories, $product->category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
            </div>
            </div> -->
        <div class="col-sm-3 @if(!session('business.enable_category')) hide @endif">
            <div class="form-group">
                {!! Form::label('category_id', __('product.category') . ':') !!}
                <div class="input-group">
                    {!! Form::select('category_id', $categories, !empty($duplicate_product->category_id) ? $duplicate_product->category_id : $product->category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
                    <span class="input-group-btn">
                    <button type="button" @if(!auth()->user()->can('Category.create')) disabled @endif class="btn btn-default bg-white btn-flat btn-modal" data-href="{{action('TaxonomyController@create')}}?type=product" title="@lang('brand.add_brand')" data-container=".category_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-3 @if(!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
            <div class="form-group">
                {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
                <div class="input-group">
                    {!! Form::select('sub_category_id', $sub_categories, !empty($duplicate_product->sub_category_id) ? $duplicate_product->sub_category_id : $product->sub_category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
                    <span class="input-group-btn">
                    <button type="button" @if(!auth()->user()->can('Category.create')) disabled @endif class="btn btn-default bg-white btn-flat btn-modal" data-href="{{action('TaxonomyController@create')}}?type=product" title="@lang('brand.add_brand')" data-container=".category_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                    </span>
                </div>
            </div>
        </div>
         <!--<div class="clearfix"></div>-->
        <div class="col-sm-3">
            <div class="form-group">
                <br>
                <label>
                {!! Form::checkbox('not_for_selling', 1, $product->not_for_selling, ['class' => 'input-icheck']); !!} <strong>@lang('lang_v1.not_for_selling')</strong>
                </label> @show_tooltip(__('lang_v1.tooltip_not_for_selling'))
            </div>
        </div>


        <!-- <div class="col-sm-4 @if(!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
            <div class="form-group">
              {!! Form::label('sub_category_id', __('product.sub_category')  . ':') !!}
                {!! Form::select('sub_category_id', $sub_categories, $product->sub_category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
            </div>
            </div> -->
            @php
        $default_location = null;
        if(count($business_locations) == 1){
        $default_location = array_key_first($business_locations->toArray());
        }
        @endphp
        <div class="col-sm-3 hide">
            <div class="form-group">
              {!! Form::label('product_locations', __('business.business_locations') . ':') !!} @show_tooltip(__('lang_v1.product_location_help'))
                {!! Form::select('product_locations[]', $business_locations, $default_location, ['class' => 'form-control select2', 'multiple', 'id' => 'product_locations']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <br>
                <label>
                {!! Form::checkbox('enable_stock', 1, $product->enable_stock, ['class' => 'input-icheck', 'id' => 'enable_stock']); !!} <strong>@lang('product.manage_stock')</strong>
                </label>@show_tooltip(__('tooltip.enable_stock'))
                <p class="help-block"><i>@lang('product.enable_stock_help')</i></p>
            </div>
        </div>
        <div class="col-sm-3" id="alert_quantity_div" @if(!$product->enable_stock) style="display:none" @endif>
            <div class="form-group">
                {!! Form::label('alert_quantity', __('product.alert_quantity') . ':') !!} @show_tooltip(__('tooltip.alert_quantity'))
                {!! Form::number('alert_quantity', $product->alert_quantity, ['class' => 'form-control',
                'placeholder' => __('product.alert_quantity') , 'min' => '0']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('qty_box', __('product.qty_in_box') . ':') !!}
                {!! Form::number('qty_box',$product->qty_box,  ['class' => 'form-control',
                'placeholder' => __('product.qty_in_box')]); !!}
            </div>
        </div>
          <!--<div class="clearfix"></div>-->
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('case_qty',  __('lang_v1.weight') . ':') !!}
                {!! Form::text('case_qty', $product->case_qty, ['class' => 'form-control input-upper-case', 'placeholder' => __('lang_v1.weight')]); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('ml', 'ML:') !!}
                {!! Form::text('ml', !empty($product->ml) ? $product->ml : null, ['class' => 'form-control input-upper-case', 'placeholder' => "ML"]); !!}
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-3">
            <div class="form-group">
                <br>
                <label>
                    {!! Form::checkbox('enable_vendor', 1, $product->enable_vendor, ['class' => 'input-icheck', 'id' => 'enable_vendor']); !!} <strong>@lang('product.manage_vendor')</strong>
                </label>@show_tooltip(__('tooltip.enable_vendor'))
{{--                <p class="help-block"><i>@lang('product.enable_vendor_help')</i></p>--}}
            </div>
        </div>

        <div class="col-sm-3" id="alert_vendor_div" @if($product->enable_vendor==0) style="display:none" @endif>
            <div class="form-group">
                {!! Form::label('supplier_id', 'Vendor:') !!}

                <select name="supplier_id" id="supplier_id" class="form-control ">
                    <option value="">Please Select</option>
                    @foreach($contacts as $contact)
                    <option value="{{$contact->id}}" {{ ( $product->supplier_id == $contact->id) ? 'selected' : '' }}>{{$contact->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    <div class="form-group col-sm-12" id="product_form_part"></div>
    {{-- <div class="clearfix"></div>
        <div class="col-sm-3">
            <div class="form-group">
                <br>
                <label>
                    {!! Form::checkbox('reset_last_prices', 1, false, ['class' => 'input-icheck', 'id' => 'reset_last_prices']); !!} <strong>Reset Last Invoice Prices</strong>
                </label>
            </div>
        </div> --}}
    <input type="hidden" id="variation_counter" value="0">
    <input type="hidden" id="default_profit_percent" value="{{ $default_profit_percent }}">

    @if(!empty($common_settings['enable_product_warranty']))
    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('warranty_id', __('lang_v1.warranty') . ':') !!}
            {!! Form::select('warranty_id', $warranties, $product->warranty_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
        </div>
    </div>
    @endif
    <!-- include module fields -->
    @if(!empty($pos_module_data))
    @foreach($pos_module_data as $key => $value)
    @if(!empty($value['view_path']))
    @includeIf($value['view_path'], ['view_data' => $value['view_data']])
    @endif
    @endforeach
    @endif
    </div>
    @endcomponent
    <div class="@if($product->type == 'variable') hide @endif">
        @component('components.widget', ['class' => 'box-primary variation-select'])
        <h5 class="head"><b>Location</b></h5>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('aisle', __('product.aisle') . ':') !!}
                {!! Form::number('aisle', $product->aisle, ['class' => 'form-control',
                'placeholder' => __('product.aisle')]); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('aisle', __('product.rack') . ':') !!}
                {!! Form::number('rack', $product->rack, ['class' => 'form-control',
                'placeholder' => __('product.rack')]); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('shelf', __('product.shelf') . ':') !!}
                {!! Form::number('shelf', $product->shelf, ['class' => 'form-control',
                'placeholder' => __('product.shelf')]); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('bin', __('product.bin') . ':') !!}
                {!! Form::number('bin', $product->bin, ['class' => 'form-control',
                'placeholder' => __('product.bin')]); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <br>
                <label>
                {!! Form::checkbox('outofstock', 1, $product->out_of_stock, ['class' => 'input-icheck', 'id' => 'outofstock']); !!} <strong>Mark as out of stock for website</strong>
            </div>
        </div>
        <div class="row">
            @if(session('business.enable_product_expiry'))
            @if(session('business.expiry_type') == 'add_expiry')
            @php
            $expiry_period = 12;
            $hide = true;
            @endphp
            @else
            @php
            $expiry_period = null;
            $hide = false;
            @endphp
            @endif
            <div class="col-sm-3 @if($hide) hide @endif">
                <div class="form-group">
                    <div class="multi-input">
                        @php
                        $disabled = false;
                        $disabled_period = false;
                        if( empty($product->expiry_period_type) || empty($product->enable_stock) ){
                        $disabled = true;
                        }
                        if( empty($product->enable_stock) ){
                        $disabled_period = true;
                        }
                        @endphp
                        {!! Form::label('expiry_period', __('product.expires_in') . ':') !!}<br>
                        {!! Form::text('expiry_period', @num_format($product->expiry_period), ['class' => 'form-control pull-left input_number',
                        'placeholder' => __('product.expiry_period'), 'style' => 'width:60%;', 'disabled' => $disabled]); !!}
                        {!! Form::select('expiry_period_type', ['months'=>__('product.months'), 'days'=>__('product.days'), '' =>__('product.not_applicable') ], $product->expiry_period_type, ['class' => 'form-control select2 pull-left', 'style' => 'width:40%;', 'id' => 'expiry_period_type', 'disabled' => $disabled_period]); !!}
                    </div>
                </div>
            </div>
            @endif
            <!-- <div class="col-sm-4">
                <div class="checkbox">
                  <label>
                    {!! Form::checkbox('enable_sr_no', 1, $product->enable_sr_no, ['class' => 'input-icheck']); !!} <strong>@lang('lang_v1.enable_imei_or_sr_no')</strong>
                  </label>
                  @show_tooltip(__('lang_v1.tooltip_sr_no'))
                </div>
                </div> -->
            <div></div>
            <!-- Rack, Row & position number -->
            {{-- @if(session('business.enable_racks') || session('business.enable_row') || session('business.enable_position'))
            <div class="col-md-12">
                <h4>@lang('lang_v1.rack_details'):
                    @show_tooltip(__('lang_v1.tooltip_rack_details'))
                </h4>
            </div>
            @foreach($business_locations as $id => $location)
            <div class="col-sm-3">
                <div class="form-group">
                    {!! Form::label('rack_' . $id,  $location . ':') !!}
                    @if(!empty($rack_details[$id]))
                    @if(session('business.enable_racks'))
                    {!! Form::text('product_racks_update[' . $id . '][rack]', $rack_details[$id]['rack'], ['class' => 'form-control', 'id' => 'rack_' . $id]); !!}
                    @endif
                    @if(session('business.enable_row'))
                    {!! Form::text('product_racks_update[' . $id . '][row]', $rack_details[$id]['row'], ['class' => 'form-control']); !!}
                    @endif
                    @if(session('business.enable_position'))
                    {!! Form::text('product_racks_update[' . $id . '][position]', $rack_details[$id]['position'], ['class' => 'form-control']); !!}
                    @endif
                    @else
                    {!! Form::text('product_racks[' . $id . '][rack]', null, ['class' => 'form-control', 'id' => 'rack_' . $id, 'placeholder' => __('lang_v1.rack')]); !!}
                    {!! Form::text('product_racks[' . $id . '][row]', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.row')]); !!}
                    {!! Form::text('product_racks[' . $id . '][position]', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.position')]); !!}
                    @endif
                </div>
            </div>
            @endforeach
            @endif --}}
            <div></div>
            @php
            $custom_labels = json_decode(session('business.custom_labels'), true);
            $product_custom_field1 = !empty($custom_labels['product']['custom_field_1']) ? $custom_labels['product']['custom_field_1'] : __('lang_v1.product_custom_field1');
            $product_custom_field2 = !empty($custom_labels['product']['custom_field_2']) ? $custom_labels['product']['custom_field_2'] : __('lang_v1.product_custom_field2');
            $product_custom_field3 = !empty($custom_labels['product']['custom_field_3']) ? $custom_labels['product']['custom_field_3'] : __('lang_v1.product_custom_field3');
            $product_custom_field4 = !empty($custom_labels['product']['custom_field_4']) ? $custom_labels['product']['custom_field_4'] : __('lang_v1.product_custom_field4');
            @endphp
            <!--custom fields-->
            <!-- <div class="col-sm-3">
                <div class="form-group">
                  {!! Form::label('product_custom_field1',  $product_custom_field1 . ':') !!}
                  {!! Form::text('product_custom_field1', $product->product_custom_field1, ['class' => 'form-control', 'placeholder' => $product_custom_field1]); !!}
                </div>
                </div>

                <div class="col-sm-3">
                <div class="form-group">
                  {!! Form::label('product_custom_field2',  $product_custom_field2 . ':') !!}
                  {!! Form::text('product_custom_field2', $product->product_custom_field2, ['class' => 'form-control', 'placeholder' => $product_custom_field2]); !!}
                </div>
                </div>

                <div class="col-sm-3">
                <div class="form-group">
                  {!! Form::label('product_custom_field3',  $product_custom_field3 . ':') !!}
                  {!! Form::text('product_custom_field3', $product->product_custom_field3, ['class' => 'form-control', 'placeholder' => $product_custom_field3]); !!}
                </div>
                </div>

                <div class="col-sm-3">
                <div class="form-group">
                  {!! Form::label('product_custom_field4',  $product_custom_field4 . ':') !!}
                  {!! Form::text('product_custom_field4', $product->product_custom_field4, ['class' => 'form-control', 'placeholder' => $product_custom_field4]); !!}
                </div>
                </div> -->
            <!--custom fields-->
            @include('layouts.partials.module_form_part')
        </div>
        @endcomponent
    </div>
    @component('components.widget', ['class' => 'box-primary'])
    <!-- <div class="row">
        <div class="col-sm-3 @if(!session('business.enable_price_tax')) hide @endif">
          <div class="form-group">
            {!! Form::label('tax', __('product.applicable_tax') . ':') !!}
              {!! Form::select('tax', $taxes, $product->tax, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'], $tax_attributes); !!}
          </div>
        </div>

        <div class="col-sm-3 @if(!session('business.enable_price_tax')) hide @endif">
          <div class="form-group">
            {!! Form::label('tax_type', __('product.selling_price_tax_type') . ':*') !!}
              {!! Form::select('tax_type',['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')], $product->tax_type,
              ['class' => 'form-control select2', 'required']); !!}
          </div>
        </div> -->
    <div class="clearfix"></div>
    <h4 class="e-com">Ecommerce</h4>
    <div class="col-sm-8">
        <div class="form-group">
            {!! Form::label('product_description', __('lang_v1.product_description') . ':') !!}
            {!! Form::textarea('product_description', $product->product_description, ['class' => 'form-control']); !!}
        </div>
    </div>
    {{--
    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('image', __('lang_v1.product_image') . ':') !!}
            {!! Form::file('image', ['id' => 'upload_image', 'accept' => 'image/*'  , 'multiple' => 'true']); !!}
            <small>
                <p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]). @lang('lang_v1.aspect_ratio_should_be_1_1') @if(!empty($product->image)) <br> @lang('lang_v1.previous_image_will_be_replaced') @endif</p>
            </small>
        </div>
    </div>
    --}}
    {{-- <div class="col-sm-1">
        <div class="form-group">
            {!! Form::label('srp',  __('lang_v1.srp') . ':') !!}
            {!! Form::text('srp', $product->srp, ['class' => 'form-control', 'placeholder' => __('lang_v1.srp')]); !!}
        </div>
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            {!! Form::label('saleprice',  __('lang_v1.sp') . ':') !!}
            {!! Form::text('saleprice', $product->sales_price, ['class' => 'form-control', 'placeholder' => __('lang_v1.sp')]); !!}
        </div>
    </div>
    <div class="col-sm-1">
        <div class="form-group">
            {!! Form::label('weight',  __('lang_v1.wght') . ':') !!}
            {!! Form::text('weight', $product->weight, ['class' => 'form-control', 'placeholder' => __('lang_v1.wght')]); !!}
        </div>
    </div> --}}
    <div class="col-sm-2">
        <div class="form-group">
            {!! Form::label('srp',  __('Reg. Price') . ':') !!}
            {!! Form::number('srp', $product->srp, ['step'=>'0.01','class' => 'form-control', 'placeholder' => __('Reg. Price')]); !!}
        </div>
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            {!! Form::label('saleprice',  __('lang_v1.sp') . ':') !!}
            {!! Form::number('saleprice', $product->sales_price, ['step'=>'0.01','class' => 'form-control', 'placeholder' => __('lang_v1.sp')]); !!}
        </div>
    </div>
    <div class="col-sm-1" style="display: none;">
        <div class="form-group">
            {!! Form::label('weight',  __('lang_v1.wght') . ':') !!}
            {!! Form::text('weight', $product->weight, ['class' => 'form-control', 'placeholder' => __('lang_v1.wght')]); !!}
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label>Main Image</label>
           {{-- <div class="file-preview">
                <div class="close fileinput-remove">×</div>
                <div class="file-drop-disabled">
                    <div class="file-preview-thumbnails">
                        <div class="file-preview-frame krajee-default  kv-preview-thumb" id="preview-1618940458320-0"
                            data-fileindex="0" data-template="image">
                            <div class="kv-file-content">
                                <img src="{{asset('/uploads'.$product->main_image)}}" style="width:200px !important;" alt="Product image">
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="file-preview-status text-center text-success"></div>
                </div>
            </div> --}}
            @if($product->main_image != '')
            <div class="card preview_image" style="height: 15vw; object-fit: cover;">
                <span class="fa fa-times-circle close"></span>
                @php
                    if (!empty($product->compressed_image)) {
                        $img = asset($product->compressed_image);
                    } else {
                        $img = asset('/uploads' . $product->main_image);
                    }
                @endphp
                <!--<img src="{{asset('/uploads'.$product->main_image)}}" style="width:200px !important;" alt="Product image">-->
                <img src="{{ $img }}" style="width:200px !important;" alt="Product image">
            </div>
            @endif
            <input type="file" name="main_image" id="upload_main_image" onchange="return checkimageextension()" >
            <small>
                <p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p>
            </small>
        </div>
    </div>
    <div class="col-sm-4">
                <div class="form-group" id="compressed_image">
                </div>
            </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label>Image Gallery</label>
            <input type="file" name="images[]" id="upload_image" multiple>
            <small>
                <p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p>
            </small>
        </div>
    </div>
    <div class="col-sm-2 not" style="display:none;">
        <div class="form-group">
            <br>
            <label>
            <input type="checkbox" class="input-icheck" value="1" name="sync" class="nyc" @if($product->sync == 1) checked @endif><strong>Do not sync with website</strong>
            </label>@show_tooltip(__('lang_v1.tooltip_not_for_selling'))
        </div>
    </div>
    <div class="col-sm-2 not">
        <div class="form-group">
            <br>
            <label>
            <input type="checkbox" class="input-icheck" value="1" name="sync_ecom" class="nyc"  @if($product->sync_ecom == 1) checked @endif ><strong>Do not sync with Ecomm</strong>
            </label>@show_tooltip(__('lang_v1.tooltip_not_for_selling'))
        </div>
    </div>
     <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('note', __('lang_v1.note') . ':') !!}
            {!! Form::textarea('note', !empty($product->note) ? $product->note : null, ['class' => 'form-control' , 'tabindex' => '17', 'id' => 'note','cols'=>"15", 'rows'=>"4"]); !!}
        </div>
    </div>
    @endcomponent
    <div class="row">
        <input type="hidden" name="submit_type" id="submit_type">
        <div class="col-sm-12">
            <div class="text-center">
                <div class="btn-group">
                    @if($selling_price_group_count)
                    <button type="submit" value="submit_n_add_selling_prices" class="btn btn-warning submit_product_form edit">@lang('lang_v1.save_n_add_selling_price_group_prices')</button>
                    @endif
                    @can('product.opening_stock')
                    <button type="submit" @if(empty($product->enable_stock)) disabled="true" @endif id="opening_stock_button"  value="update_n_edit_opening_stock" class="btn bg-purple submit_product_form edit">@lang('lang_v1.update_n_edit_opening_stock')</button>
                    @endif
                    <button type="submit" value="save_n_add_another" class="btn bg-maroon submit_product_form edit">@lang('lang_v1.update_n_add_another')</button>
                    <input type="submit" value="Update & Sync with Website" class="btn" name="syncsave" style="background: aquamarine;" />
                    <button type="submit" value="submit" class="btn btn-primary update edit">@lang('messages.update')</button>
                     <a href="{{ action('ProductController@index')}}" class="btn btn-danger" >Cancel</a>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
</section>
<!-- /.content -->
<div class="modal fade category_modal" tabindex="-1" role="dialog"
    aria-labelledby="gridSystemModalLabel">
</div>
@endsection
@section('javascript')
<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
<script type="text/javascript">
    function gencode(){
      var d = new Date();
      var n = d.getTime();
      document.getElementById('gen-product-code').value=n;
    }
    function barcode(){
        var old_barcode = $("#last_barcode").val();
        $.ajax({
                method: "POST",
                dataType: "json",
                data:{old_barcode:old_barcode},
                url: '/products/generate-auto-barcode',
                async: false,
                success: function(result){
                    console.log(result)
                    $(".already-exists-barcode").text('');
                    if($(".already-exists-barcode").text()=="" && $(".already-exists-barcode2").text()=="" && $(".already-exists-barcode3").text()=="")
                    {
                        $(".edit").prop("disabled", false);
                    }
                    else
                    {
                        $(".edit").prop("disabled", true);
                    }
                    $("#gen-bar-code").val(result);
                    $("#gen-bar-code").focus();
                    $("#last_barcode").val(result);
                }
            });
    }
</script>
<script type="text/javascript">
    $(document).ready( function(){
      __page_leave_confirmation('#product_add_form');
    });
</script>
<script type="text/javascript">
    $(document).ready( function() {

        function getTaxonomiesIndexPage () {
            var data = {category_type : $('#category_type').val()};
            $.ajax({
                method: "GET",
                dataType: "html",
                url: '/taxonomies-ajax-index-page',
                data: data,
                async: false,
                success: function(result){
                    $('.taxonomy_body').html(result);
                }
            });
        }

        function initializeTaxonomyDataTable() {
            //Category table
            if ($('#category_table').length) {
                var category_type = $('#category_type').val();
                category_table = $('#category_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '/taxonomies?type=' + category_type,
                    columns: [
                        { data: 'name', name: 'name' },
                        @if(isset($cat_code_enabled))
                            { data: 'short_code', name: 'short_code' },
                        @endif
                        { data: 'description', name: 'description' },
                        { data: 'action', name: 'action', orderable: false, searchable: false},
                    ],
                });
            }
        }

        @if(empty(request()->get('type')))
            getTaxonomiesIndexPage();
        @endif

        initializeTaxonomyDataTable();
    });
    $(document).on('submit', 'form#category_add_form', function(e) {
        e.preventDefault();
        $(this)
            .find('button[type="submit"]')
            .attr('disabled', true);
        var data = $(this).serialize();

        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            success: function(result) {
                if (result.success === true) {
                    $('div.category_modal').modal('hide');
                    toastr.success(result.msg);
                    category_table.ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });
    $(document).on('click', 'button.edit_category_button', function() {
        $('div.category_modal').load($(this).data('href'), function() {
            $(this).modal('show');

            $('form#category_edit_form').submit(function(e) {
                e.preventDefault();
                $(this)
                    .find('button[type="submit"]')
                    .attr('disabled', true);
                var data = $(this).serialize();

                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success === true) {
                            $('div.category_modal').modal('hide');
                            toastr.success(result.msg);
                            category_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });
        });
    });

    $(document).on('click', 'button.delete_category_button', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).data('href');
                var data = $(this).serialize();

                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success === true) {
                            toastr.success(result.msg);
                            category_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });

    $("#gen-bar-code").on("change", function() {
        $(".already-exists-barcode").text('');
        var product_id = $("#product_id").val();

        var barcode = $(this).val();
        $.ajax({
            method: 'POST',
            url:"{{ url('products/check-barcode-edit') }}",
            dataType: 'json',
            data: {'barcode':barcode,'product_id':product_id},
            success: function(success) {
                if(success.message == ''){
                    if($(".already-exists-barcode").text()=="" && $(".already-exists-barcode2").text()=="" && $(".already-exists-barcode3").text()=="")
                        {
                            $(".edit").prop("disabled", false);
                        }
                        else
                        {
                            $(".edit").prop("disabled", true);
                        }
                }else{
                    $(".edit").prop("disabled", true);
                    $(".already-exists-barcode").text(success.message);
                }
             },
        });
    });

    $("#gen-bar-code2").on("change", function() {
        $(".already-exists-barcode2").text('');
        var product_id = $("#product_id").val();
        var barcode2 = $(this).val();
        $.ajax({
            method: 'POST',
            url:"{{ url('products/check-barcode-edit') }}",
            dataType: 'json',
            data: {'barcode':barcode2,'product_id':product_id},
            success: function(success) {
                if(success.message == ''){
                    if($(".already-exists-barcode").text()=="" && $(".already-exists-barcode2").text()=="" && $(".already-exists-barcode3").text()=="")
                        {
                            $(".edit").prop("disabled", false);
                        }
                        else
                        {
                            $(".edit").prop("disabled", true);
                        }
                }else{
                    $(".edit").prop("disabled", true);
                    $(".already-exists-barcode2").text(success.message);
                }
             },
        });
    });

    $("#gen-bar-code3").on("change", function() {
        $(".already-exists-barcode3").text('');
        var product_id = $("#product_id").val();
        var barcode3 = $(this).val();
        $.ajax({
            method: 'POST',
            url:"{{ url('products/check-barcode-edit') }}",
            dataType: 'json',
            data: {'barcode':barcode3,'product_id':product_id},
            success: function(success) {
                if(success.message == ''){
                    if($(".already-exists-barcode").text()=="" && $(".already-exists-barcode2").text()=="" && $(".already-exists-barcode3").text()=="")
                        {
                            $(".edit").prop("disabled", false);
                        }
                        else
                        {
                            $(".edit").prop("disabled", true);
                        }
                }else{
                    $(".edit").prop("disabled", true);
                    $(".already-exists-barcode3").text(success.message);
                }
             },
        });
    });

    $("#gen-item-code").on("change",function(){
        $(".already-exists-itemcode").text('');
        var product_id = $("#product_id").val();
        var item_code = $(this).val();

        $.ajax({
            method: 'POST',
            url:"{{ url('products/check-itemcode-edit') }}",
            dataType: 'json',
            data: {'item_code':item_code,'product_id':product_id},
            success: function(success) {
                if(success.success == true){
                    $(".already-exists-itemcode").text(success.message);
                    $(".edit").prop("disabled", true);
                }else{
                    $(".already-exists-itemcode").text(success.message);
                    $(".edit").prop("disabled", false);
                }
             },
        });
    });

    $(document).on('change','#single_dsp', function(){
        $("#single_dsp_tier1").val($("#single_dsp").val());
        $("#single_dsp_tier2").val($("#single_dsp").val());
        $("#single_dsp_tier3").val($("#single_dsp").val());
        $("#single_dsp_tier4").val($("#single_dsp").val());

        checkforallprice();
    });

    $(document).on('change','#single_dsp_tier1', function(){
        checkforallprice();
    });

    $(document).on('change','#single_dsp_tier2', function(){
        checkforallprice();
    });

    $(document).on('change','#single_dsp_tier3', function(){
        checkforallprice();
    });

    $(document).on('change','#single_dsp_tier4', function(){
        checkforallprice();
    });

    function checkforallprice()
    {
        var cost_price = $("#single_dpp_inc_tax").val();
        var sell_price = $("#single_dsp").val();
        var sell_price_tier1 = $("#single_dsp_tier1").val();
        var sell_price_tier2 = $("#single_dsp_tier2").val();
        var sell_price_tier3 = $("#single_dsp_tier3").val();
        var sell_price_tier4 = $("#single_dsp_tier4").val();

        $(".error").text('');

        if(parseFloat(cost_price) > parseFloat(sell_price) || parseFloat(cost_price) > parseFloat(sell_price_tier1) || parseFloat(cost_price) > parseFloat(sell_price_tier2) || parseFloat(cost_price) > parseFloat(sell_price_tier3) || parseFloat(cost_price) > parseFloat(sell_price_tier4)){
            $(".costerror").text('');
        }

        if(parseFloat(sell_price) < parseFloat(cost_price)){
            $(".selling").text('selling price should be greater than or equal to cost price');
            $(this).focus();
            $(".edit").prop("disabled", true);

        }else{
            $(".selling").text('');
            $(".costerror").text('');
            $(".edit").prop("disabled", false);
        }

        if(parseFloat(sell_price_tier1) < parseFloat(cost_price)){
            $(".tier1").text('Tier-1 price should be greater than or equal to cost price');
            $(this).focus();
            $(".edit").prop("disabled", true);

        }else{
            $(".tier1").text('');
            $(".costerror").text('');
            $(".edit").prop("disabled", false);
        }

        if(parseFloat(sell_price_tier2) < parseFloat(cost_price)){
            $(".tier2").text('Tier-2 price should be greater than or equal to cost price');
            $(this).focus();
            $(".edit").prop("disabled", true);

        }else{
            $(".tier2").text('');
            $(".costerror").text('');
            $(".edit").prop("disabled", false);
        }

        if(parseFloat(sell_price_tier3) < parseFloat(cost_price)){
            $(".tier3").text('Tier-3 price should be greater than or equal to cost price');
            $(this).focus();
            $(".edit").prop("disabled", true);

        }else{
            $(".tier3").text('');
            $(".costerror").text('');
            $(".edit").prop("disabled", false);
        }

        if(parseFloat(sell_price_tier4) < parseFloat(cost_price)){
            $(".tier4").text('Tier-4 price should be greater than or equal to cost price');
            $(this).focus();
            $(".edit").prop("disabled", true);

        }else{
            $(".tier4").text('');
            $(".costerror").text('');
            $(".edit").prop("disabled", false);
        }

        if($("span.costerror").text().trim() != "" || $("span.error").text().trim() != "")
        {
            $(".edit").prop("disabled", true);
        }
    }

    $(document).on('change','#single_dpp_inc_tax', function(){

        var cost_price = $("#single_dpp_inc_tax").val();
        var sell_price = $("#single_dsp").val();
        var sell_price_tier1 = $("#single_dsp_tier1").val();
        var sell_price_tier2 = $("#single_dsp_tier2").val();
        var sell_price_tier3 = $("#single_dsp_tier3").val();
        var sell_price_tier4 = $("#single_dsp_tier4").val();

        if(parseFloat(cost_price) > parseFloat(sell_price)){
            $(".costerror").text('cost price should be less than or equal to selling price');
            $(this).focus();
            $(".error").text('');
            $(".edit").prop("disabled", true);
        }
        else if(parseFloat(cost_price) > parseFloat(sell_price_tier1))
        {
            $(".costerror").text('cost price should be less than or equal to tier price 1');
            $(this).focus();
            $(".error").text('');
            $(".edit").prop("disabled", true);
        }
        else if(parseFloat(cost_price) > parseFloat(sell_price_tier2))
        {
            $(".costerror").text('cost price should be less than or equal to tier price 2');
            $(this).focus();
            $(".error").text('');
            $(".edit").prop("disabled", true);
        }
        else if(parseFloat(cost_price) > parseFloat(sell_price_tier3))
        {
            $(".costerror").text('cost price should be less than or equal to tier price 3');
            $(this).focus();
            $(".error").text('');
            $(".edit").prop("disabled", true);
        }
        else if(parseFloat(cost_price) > parseFloat(sell_price_tier4))
        {
            $(".costerror").text('cost price should be less than or equal to tier price 4');
            $(this).focus();
            $(".error").text('');
            $(".edit").prop("disabled", true);
        }
        else
        {
            $(".costerror").text('');
            $(".error").text('');
            $(".edit").prop("disabled", false);
        }
    });

    $(document).on('click', '.edit', function() {
        if($("span.costerror").text().trim() != "" || $("span.error").text().trim() != "")
        {
            return false;
        }
    });

    $(document).on('keyup','#single_dpp_inc_tax', function(){
        this.value = this.value.replace(/[^0-9\.]/g,'');
        if(this.value==0)
        {
            $(this).val("");
        }
    });
    $(document).on('keyup','#single_dsp', function(){
        this.value = this.value.replace(/[^0-9\.]/g,'');
        if(this.value==0)
        {
            $(this).val("");
        }
    });
    $(document).on('keyup','#single_dsp_tier1', function(){
        this.value = this.value.replace(/[^0-9\.]/g,'');
        if(this.value==0)
        {
            $(this).val("");
        }
    });
    $(document).on('keyup','#single_dsp_tier2', function(){
        this.value = this.value.replace(/[^0-9\.]/g,'');
        if(this.value==0)
        {
            $(this).val("");
        }
    });
    $(document).on('keyup','#single_dsp_tier3', function(){
        this.value = this.value.replace(/[^0-9\.]/g,'');
        if(this.value==0)
        {
            $(this).val("");
        }
    });
    $(document).on('keyup','#single_dsp_tier4', function(){
        this.value = this.value.replace(/[^0-9\.]/g,'');
        if(this.value==0)
        {
            $(this).val("");
        }
    });
    $(document).on('keyup','#profit_percent', function(){
        this.value = this.value.replace(/[^0-9\.]/g,'');
        if(this.value==0)
        {
            $(this).val("");
        }
    });
    $(document).on('keyup','#profit_percent_tier1', function(){
        this.value = this.value.replace(/[^0-9\.]/g,'');
        if(this.value==0)
        {
            $(this).val("");
        }
    });
    $(document).on('keyup','#profit_percent_tier2', function(){
        this.value = this.value.replace(/[^0-9\.]/g,'');
        if(this.value==0)
        {
            $(this).val("");
        }
    });
    $(document).on('keyup','#profit_percent_tier3', function(){
        this.value = this.value.replace(/[^0-9\.]/g,'');
        if(this.value==0)
        {
            $(this).val("");
        }
    });
    $(document).on('keyup','#profit_percent_tier4', function(){
        this.value = this.value.replace(/[^0-9\.]/g,'');
        if(this.value==0)
        {
            $(this).val("");
        }
    });
    /*
    $(document).on('keyup','input[name="stock"]', function(){
        this.value = this.value.replace(/[^0-9\.]/g,'');
        if(this.value==0)
        {
            $(this).val("");
        }
    });*/

    // function checkimageextension(){
    // var fileInput =
    //         document.getElementById('upload_main_image');

    //     var filePath = fileInput.value;

    //     // Allowing file type
    //     var allowedExtensions =
    //             /(\.jpg|\.jpeg|\.png)$/i;

    //     if (!allowedExtensions.exec(filePath)) {
    //         alert('Invalid file type');
    //         fileInput.value = '';
    //         return false;
    //     }
    //     else
    //     {
    //         // Image preview
    //         if (fileInput.files && fileInput.files[0]) {
    //             var reader = new FileReader();
    //             reader.onload = function(e) {
    //                 document.getElementById(
    //                     'imagePreview').innerHTML =
    //                     '<img src="' + e.target.result
    //                     + '"/>';
    //             };

    //             reader.readAsDataURL(fileInput.files[0]);
    //         }
    //     }
    // }
       function checkimageextension() {
            var fileInput =
                document.getElementById('upload_main_image');

            var filePath = fileInput.value;

            // Allowing file type
            var allowedExtensions =
                /(\.jpg|\.jpeg|\.png)$/i;

            if (!allowedExtensions.exec(filePath)) {
                alert('Invalid file type');
                fileInput.value = '';
                return false;
            } else {
                // Image preview
                if (fileInput.files && fileInput.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById(
                                'imagePreview').innerHTML =
                            '<img src="' + e.target.result +
                            '"/>';
                    };
                }
                reader.readAsDataURL(fileInput.files[0]);
                // Get the file size
                var size = fileInput.files[0].size / 1024;
                if (size > 1024) {
                    // Call compressImage function if file size is greater than 1 MB
                    compressImage(fileInput.files[0], size);
                } else {
                    console.log("File size is within the limit.");
                }
            }
        }
        function compressImage(file, size) {
            var reader = new FileReader();

            reader.onload = function(event) {
                var img = new Image();
                img.src = event.target.result;

                img.onload = function() {
                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');

                    // Set initial quality
                    var quality = size > 3072 ? 0.5 : 0.9; // Change initial quality based on image size

                    // Define a recursive function for asynchronous compression
                    function compressIteration() {
                        // Calculate new dimensions to maintain aspect ratio
                        var maxWidth = img.width * quality;
                        var maxHeight = img.height * quality;
                        var width = img.width;
                        var height = img.height;

                        if (width > height) {
                            if (width > maxWidth) {
                                height *= maxWidth / width;
                                width = maxWidth;
                            }
                        } else {
                            if (height > maxHeight) {
                                width *= maxHeight / height;
                                height = maxHeight;
                            }
                        }
                        canvas.width = width;
                        canvas.height = height;

                        // Draw image on canvas
                        ctx.drawImage(img, 0, 0, width, height);

                        // Convert canvas to blob
                        canvas.toBlob(function(blob) {
                            // Create a new File object with the compressed image
                            var compressedFile = new File([blob], file.name, {
                                type: file.type
                            });
                            size = compressedFile.size / 1024; // Update size
                            quality *= size > 3072 ? 0.5 : 0.9; // Update quality based on image size

                            // Check if size is still above 1 MB and quality > 0
                            if (size > 1024 && quality > 0) {
                                // Schedule the next iteration after a short delay
                                console.log(size);
                                setTimeout(compressIteration, 0);
                            } else {
                                displayCompressedImage(compressedFile);
                            }
                        }, file.type);
                    }
                    // Start the recursive compression
                    compressIteration();
                };
            };

            // Read the file as data URL
            reader.readAsDataURL(file);
        }


        function displayCompressedImage(compressedFile) {
            var reader = new FileReader();

            reader.onload = function(event) {
                // Convert the image file to a Base64-encoded string
                var compressedFileDataURL = event.target.result;

                // Display the selected image
                var img = document.createElement('img');
                img.src = compressedFileDataURL;

                // Create a hidden input field to store the compressed image data
                var hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'compressedFile';
                hiddenInput.value = compressedFileDataURL;

                // Append the hidden input field to the form
                document.getElementById("compressed_image").appendChild(hiddenInput);
            };

            // Read the contents of the file
            reader.readAsDataURL(compressedFile);
        }
</script>
    <style>
        .head{
        padding-left: 15px;
        }
        .e-com{
        margin-left: 15px;
        }
        .not{
        margin-top:10px;
        }
        .input-upper-case{
            text-transform: uppercase;
        }
        .costerror{
            color: red;
        }
    </style>
@endsection