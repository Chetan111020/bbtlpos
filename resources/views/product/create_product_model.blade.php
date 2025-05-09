<!-- Main content -->
<section class="content">
    @php
    $form_class = empty($duplicate_product) ? 'create' : '';
    @endphp
    {!! Form::open(['url' => action('ProductController@store'), 'method' => 'post',
    'id' => 'product_add_form','class' => 'product_form ' . $form_class, 'files' => true ]) !!}
    @component('components.widget', ['class' => 'box-primary'])
    <div class="row">
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('type', __('product.product_type') . ':*') !!} @show_tooltip(__('tooltip.product_type'))
                {!! Form::select('type', $product_types, !empty($duplicate_product->type) ? $duplicate_product->type : null, ['class' => 'form-control select2',
                'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); !!}
            </div>
        </div>
        <div class="col-sm-3 variation-select">
            <div class="form-group">
                <label>Item Code: (Optional)</label>
                {{--<div class="input-group">--}}
                    <input type="text" name="item_code" id="gen-product-code" class="form-control input-upper-case" placeholder="Item Code">
                    {{--<span class="input-group-btn">--}}
                    {{--<button type="button" class="btn btn-default bg-white btn-flat btn-modal" id="generate-item-code" onclick="gencode()" ><i class="fa fa-plus-circle text-primary fa-lg"></i></button>--}}
                    {{--</span>--}}
                {{--</div>--}}
            </div>
            <span style="color:red" class="already-exists-itemcode"></span>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('name', __('product.product_name') . ':*') !!}
                {!! Form::text('name', !empty($duplicate_product->name) ? $duplicate_product->name : null, ['class' => 'form-control input-upper-case', 'required',
                'placeholder' => __('product.product_name') ]); !!}
            </div>
        </div>
       <!--  <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('sku', __('product.sku') . ':') !!} @show_tooltip(__('tooltip.sku'))
                {!! Form::text('sku', null, ['class' => 'form-control',
                'placeholder' => __('product.sku')]); !!}
            </div>
        </div>  -->
        <div class="col-sm-3 variation-select">
            <div class="form-group">
                <label>Barcode Number1</label>
                <div class="input-group">
                    <input type="text" name="sku" id="gen-bar-code" class="form-control" placeholder="Barcode Number1" minlength="5" maxlength="14">
                    <span class="input-group-btn">
                    <button type="button" class="btn btn-default bg-white btn-flat btn-modal" id="generate-bar-code" onclick="barcode()" ><i class="fa fa-sync text-primary fa-lg"></i></button>
                    </span>
                </div>
                <span style="color:red" class="already-exists-barcode"></span>
                <input type="hidden" id="last_barcode" name="last_barcode" value="">
            </div>
        </div>
        <div class="col-sm-3 variation-select">
            <div class="form-group">
                <label>Barcode Number2</label>
                <div class="input-group">
                    <input type="text" name="sku2" id="gen-bar-code2" class="form-control" placeholder="Barcode Number2" minlength="5" maxlength="14">
                </div>
                <span style="color:red" class="already-exists-barcode2"></span>
            </div>
        </div>
        <div class="col-sm-3 variation-select">
            <div class="form-group">
                <label>Barcode Number3</label>
                <div class="input-group">
                    <input type="text" name="sku3" id="gen-bar-code3" class="form-control" placeholder="Barcode Number3" minlength="5" maxlength="14">
                </div>
                <span style="color:red" class="already-exists-barcode3"></span>
            </div>
        </div>
        <!-- <div class="col-sm-3">
            <div class="form-group">
              {!! Form::label('barcode_type', __('product.barcode_type') . ':*') !!}
              {!! Form::select('barcode_type', $barcode_types, !empty($duplicate_product->barcode_type) ? $duplicate_product->barcode_type : $barcode_default, ['class' => 'form-control select2', 'required']); !!}
            </div>
            </div> -->
        <!-- <div class="clearfix"></div> -->
        <!--<div class="clearfix"></div>-->
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('unit_id', __('product.unit') . ':*') !!}
                <div class="input-group">
                    {!! Form::select('unit_id', $units, 4, ['class' => 'form-control select2', 'required']); !!}
                    <span class="input-group-btn">
                    <button type="button" @if(!auth()->user()->can('unit.create')) disabled @endif class="btn btn-default bg-white btn-flat btn-modal" data-href="{{action('UnitController@create', ['quick_add' => true])}}" title="@lang('unit.add_unit')" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-3 @if(!session('business.enable_sub_units')) hide @endif">
            <div class="form-group">
                {!! Form::label('sub_unit_ids', __('lang_v1.related_sub_units') . ':') !!} @show_tooltip(__('lang_v1.sub_units_tooltip'))
                {!! Form::select('sub_unit_ids[]', [], !empty($duplicate_product->sub_unit_ids) ? $duplicate_product->sub_unit_ids : null, ['class' => 'form-control select2', 'multiple', 'id' => 'sub_unit_ids']); !!}
            </div>
        </div>
        <div class="col-sm-3 @if(!session('business.enable_brand')) hide @endif">
            <div class="form-group">
                {!! Form::label('brand_id', __('product.brand') . ':') !!}
                <div class="input-group">
                    {!! Form::select('brand_id', $brands, !empty($duplicate_product->brand_id) ? $duplicate_product->brand_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
                    <span class="input-group-btn">
                    <button type="button" @if(!auth()->user()->can('brand.create')) disabled @endif class="btn btn-default bg-white btn-flat btn-modal" data-href="{{action('BrandController@create', ['quick_add' => true])}}" title="@lang('brand.add_brand')" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                    </span>
                </div>
            </div>
        </div>
        <!-- <div class="clearfix"></div> -->
        <div class="col-sm-3 @if(!session('business.enable_category')) hide @endif">
            <div class="form-group">
                {!! Form::label('category_id', __('product.category') . ':') !!}
                <div class="input-group">
                    {!! Form::select('category_id', $categories, !empty($duplicate_product->category_id) ? $duplicate_product->category_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
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
                    {!! Form::select('sub_category_id', $sub_categories, !empty($duplicate_product->sub_category_id) ? $duplicate_product->sub_category_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
                    <span class="input-group-btn">
                    <button type="button" @if(!auth()->user()->can('Category.create')) disabled @endif class="btn btn-default bg-white btn-flat btn-modal" data-href="{{action('TaxonomyController@create')}}?type=product" title="@lang('brand.add_brand')" data-container=".category_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                    </span>
                </div>
            </div>
        </div>
        <!--<div class="clearfix"></div>-->
        <div class="col-sm-3 not ">
            <div class="form-group">
                <br>
                <label>
                {!! Form::checkbox('not_for_selling', 1, !(empty($duplicate_product)) ? $duplicate_product->not_for_selling : false, ['class' => 'input-icheck']); !!} <strong>@lang('lang_v1.not_for_selling')</strong>
                </label> @show_tooltip(__('lang_v1.tooltip_not_for_selling'))
            </div>
        </div>
        @php
        $default_location = null;
        if(count($business_locations) == 1){
        $default_location = array_key_first($business_locations->toArray());
        }
        @endphp
        <div class="col-sm-4 hide">
            <div class="form-group">
              {!! Form::label('product_locations', __('business.business_locations') . ':') !!} @show_tooltip(__('lang_v1.product_location_help'))
                {!! Form::select('product_locations[]', $business_locations, $default_location, ['class' => 'form-control select2', 'multiple', 'id' => 'product_locations']); !!}
            </div>
            </div>
        <div class="col-sm-3">
            <div class="form-group">
                <br>
                <label>
                {!! Form::checkbox('enable_stock', 1, !empty($duplicate_product) ? $duplicate_product->enable_stock : true, ['class' => 'input-icheck', 'id' => 'enable_stock']); !!} <strong>@lang('product.manage_stock')</strong>
                </label>@show_tooltip(__('tooltip.enable_stock'))
                <p class="help-block"><i>@lang('product.enable_stock_help')</i></p>
            </div>
        </div>
        <div class="col-sm-3 @if(!empty($duplicate_product) && $duplicate_product->enable_stock == 0) hide @endif" id="alert_quantity_div">
            <div class="form-group">
                {!! Form::label('alert_quantity',  'Minimum Stock Alert Qty :') !!} @show_tooltip(__('tooltip.alert_quantity'))
                {!! Form::number('alert_quantity', !empty($duplicate_product->alert_quantity) ? $duplicate_product->alert_quantity : null , ['class' => 'form-control',
                'placeholder' => 'Minimum Stock Alert Qty', 'min' => '0']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('qty_box', __('product.qty_in_box') . ':') !!}
                {!! Form::number('qty_box', !empty($duplicate_product->qty_in_box) ? $duplicate_product->qty_box : null, ['class' => 'form-control',
                'placeholder' => __('product.qty_in_box')]); !!}
            </div>
        </div>
        <!--<div class="clearfix"></div>-->

        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('case_qty',  __('lang_v1.weight') . ':') !!}
                {!! Form::text('case_qty', !empty($duplicate_product->case_qty) ? $duplicate_product->caseqty : null, ['class' => 'form-control input-upper-case', 'placeholder' => __('lang_v1.weight')]); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('supplier_id', 'Vendor:') !!}
                {{-- {!! Form::select('supplier_id', $contacts, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!} --}}
                <select name="supplier_id" id="supplier_id" class="form-control select2">
                    <option value="">Please Select</option>
                    @foreach($contacts as $contact)
                    <option value="{{$contact->id}}">{{$contact->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('ml', 'ML:') !!}
                {!! Form::text('ml', !empty($duplicate_product->ml) ? $duplicate_product->ml : null, ['class' => 'form-control input-upper-case', 'placeholder' => "ML"]); !!}
            </div>
        </div>
        @if(!empty($common_settings['enable_product_warranty']))
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('warranty_id', __('lang_v1.warranty') . ':') !!}
                {!! Form::select('warranty_id', $warranties, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
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
        <div class="clearfix"></div>
        {{--
        <div class="col-sm-8">
            <div class="form-group">
                {!! Form::label('product_description', __('lang_v1.product_description') . ':') !!}
                {!! Form::textarea('product_description', !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null, ['class' => 'form-control']); !!}
            </div>
        </div>
        --}}

        <div class="form-group col-sm-12" id="product_form_part">
            @include('product.partials.single_product_form_part', ['profit_percent' => $default_profit_percent])
        </div>
        <input type="hidden" id="variation_counter" value="1">
        <input type="hidden" id="default_profit_percent"
            value="{{ $default_profit_percent }}">
    </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-primary variation-select'])
    <h5 class="head"><b>Location</b></h5>
    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('aisle', __('product.aisle') . ':') !!}
            {!! Form::number('aisle', null, ['class' => 'form-control',
            'placeholder' => __('product.aisle')]); !!}
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('aisle', __('product.rack') . ':') !!}
            {!! Form::number('rack', null, ['class' => 'form-control',
            'placeholder' => __('product.rack')]); !!}
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('shelf', __('product.shelf') . ':') !!}
            {!! Form::number('shelf', null, ['class' => 'form-control',
            'placeholder' => __('product.shelf')]); !!}
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('bin', __('product.bin') . ':') !!}
            {!! Form::number('bin', null, ['class' => 'form-control',
            'placeholder' => __('product.bin')]); !!}
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <br>
            <label>
            {!! Form::checkbox('outofstock', 1, null, ['class' => 'input-icheck', 'id' => 'outofstock']); !!} <strong>Mark as out of stock for website</strong>
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
        <div class="col-sm-4 @if($hide) hide @endif">
            <div class="form-group">
                <div class="multi-input">
                    {!! Form::label('expiry_period', __('product.expires_in') . ':') !!}<br>
                    {!! Form::text('expiry_period', !empty($duplicate_product->expiry_period) ? @num_format($duplicate_product->expiry_period) : $expiry_period, ['class' => 'form-control pull-left input_number',
                    'placeholder' => __('product.expiry_period'), 'style' => 'width:60%;']); !!}
                    {!! Form::select('expiry_period_type', ['months'=>__('product.months'), 'days'=>__('product.days'), '' =>__('product.not_applicable') ], !empty($duplicate_product->expiry_period_type) ? $duplicate_product->expiry_period_type : 'months', ['class' => 'form-control select2 pull-left', 'style' => 'width:40%;', 'id' => 'expiry_period_type']); !!}
                </div>
            </div>
        </div>
        @endif
        <!-- <div class="col-sm-4">
            <div class="form-group">
              <br>
              <label>
                {!! Form::checkbox('enable_sr_no', 1, !(empty($duplicate_product)) ? $duplicate_product->enable_sr_no : false, ['class' => 'input-icheck']); !!} <strong>@lang('lang_v1.enable_imei_or_sr_no')</strong>
              </label> @show_tooltip(__('lang_v1.tooltip_sr_no'))
            </div>
            </div> -->
        <!-- <div class="clearfix"></div> -->
        <!-- Rack, Row & position number -->
        @if(session('business.enable_racks') || session('business.enable_row') || session('business.enable_position'))
        {{--
        <div class="col-md-12">
            <h4>@lang('lang_v1.rack_details'):
                @show_tooltip(__('lang_v1.tooltip_rack_details'))
            </h4>
        </div>
        --}}
        {{-- @foreach($business_locations as $id => $location)
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('rack_' . $id,  $location . ':') !!}
                @if(session('business.enable_racks'))
                {!! Form::text('product_racks[' . $id . '][rack]', !empty($rack_details[$id]['rack']) ? $rack_details[$id]['rack'] : null, ['class' => 'form-control', 'id' => 'rack_' . $id,
                'placeholder' => __('lang_v1.rack')]); !!}
                @endif
                @if(session('business.enable_row'))
                {!! Form::text('product_racks[' . $id . '][row]', !empty($rack_details[$id]['row']) ? $rack_details[$id]['row'] : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.row')]); !!}
                @endif
                @if(session('business.enable_position'))
                {!! Form::text('product_racks[' . $id . '][position]', !empty($rack_details[$id]['position']) ? $rack_details[$id]['position'] : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.position')]); !!}
                @endif
            </div>
        </div>
        @endforeach --}}
        @endif
        @php
        $custom_labels = json_decode(session('business.custom_labels'), true);
        $product_custom_field1 = !empty($custom_labels['product']['custom_field_1']) ? $custom_labels['product']['custom_field_1'] : __('lang_v1.product_custom_field1');
        $product_custom_field2 = !empty($custom_labels['product']['custom_field_2']) ? $custom_labels['product']['custom_field_2'] : __('lang_v1.product_custom_field2');
        $product_custom_field3 = !empty($custom_labels['product']['custom_field_3']) ? $custom_labels['product']['custom_field_3'] : __('lang_v1.product_custom_field3');
        $product_custom_field4 = !empty($custom_labels['product']['custom_field_4']) ? $custom_labels['product']['custom_field_4'] : __('lang_v1.product_custom_field4');
        @endphp
        <!--custom fields-->
        <!-- <div class="clearfix"></div>
            <div class="col-sm-3">
              <div class="form-group">
                {!! Form::label('product_custom_field1',  $product_custom_field1 . ':') !!}
                {!! Form::text('product_custom_field1', !empty($duplicate_product->product_custom_field1) ? $duplicate_product->product_custom_field1 : null, ['class' => 'form-control', 'placeholder' => $product_custom_field1]); !!}
              </div>
            </div>

            <div class="col-sm-3">
              <div class="form-group">
                {!! Form::label('product_custom_field2',  $product_custom_field2 . ':') !!}
                {!! Form::text('product_custom_field2', !empty($duplicate_product->product_custom_field2) ? $duplicate_product->product_custom_field2 : null, ['class' => 'form-control', 'placeholder' => $product_custom_field2]); !!}
              </div>
            </div>

            <div class="col-sm-3">
              <div class="form-group">
                {!! Form::label('product_custom_field3',  $product_custom_field3 . ':') !!}
                {!! Form::text('product_custom_field3', !empty($duplicate_product->product_custom_field3) ? $duplicate_product->product_custom_field3 : null, ['class' => 'form-control', 'placeholder' => $product_custom_field3]); !!}
              </div>
            </div>

            <div class="col-sm-3">
              <div class="form-group">
                {!! Form::label('product_custom_field4',  $product_custom_field4 . ':') !!}
                {!! Form::text('product_custom_field4', !empty($duplicate_product->product_custom_field4) ? $duplicate_product->product_custom_field4 : null, ['class' => 'form-control', 'placeholder' => $product_custom_field4]); !!}
              </div>
            </div> -->
        <!--custom fields-->
        <!-- <div class="clearfix"></div> -->
        @include('layouts.partials.module_form_part')
    </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-primary'])
    <!-- <div class="row">
        <div class="col-sm-4 @if(!session('business.enable_price_tax')) hide @endif">
          <div class="form-group">
            {!! Form::label('tax', __('product.applicable_tax') . ':') !!}
              {!! Form::select('tax', $taxes, !empty($duplicate_product->tax) ? $duplicate_product->tax : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'], $tax_attributes); !!}
          </div>
        </div>

        <div class="col-sm-4 @if(!session('business.enable_price_tax')) hide @endif">
          <div class="form-group">
            {!! Form::label('tax_type', __('product.selling_price_tax_type') . ':*') !!}
              {!! Form::select('tax_type', ['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')], !empty($duplicate_product->tax_type) ? $duplicate_product->tax_type : 'exclusive',
              ['class' => 'form-control select2', 'required']); !!}
          </div>
        </div>  -->
    <div class="clearfix"></div>
    {{--
    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('type', __('product.product_type') . ':*') !!} @show_tooltip(__('tooltip.product_type'))
            {!! Form::select('type', $product_types, !empty($duplicate_product->type) ? $duplicate_product->type : null, ['class' => 'form-control select2', 'id' => 'flavour_id',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); !!}
        </div>
    </div>
    <div class="form-group col-sm-12" id="product_form_part">
        @include('product.partials.single_product_form_part', ['profit_percent' => $default_profit_percent])
    </div>
    <input type="hidden" id="variation_counter" value="1">
    <input type="hidden" id="default_profit_percent"
        value="{{ $default_profit_percent }}"> --}}
    <h4 class="e-com">Ecommerce</h4>
    <div class="col-sm-8">
        <div class="form-group">
            {!! Form::label('product_description', __('lang_v1.product_description') . ':') !!}
            {!! Form::textarea('product_description', !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null, ['class' => 'form-control']); !!}
        </div>
    </div>

    <div class="col-sm-2">
        <div class="form-group">
            {!! Form::label('srp',  __('Reg. Price') . ':') !!}
            {!! Form::number('srp', !empty($duplicate_product->srp) ? $duplicate_product->srp : null, ['step'=>'0.01', 'class' => 'form-control', 'placeholder' => __('Reg. Price')]); !!}
        </div>
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            {!! Form::label('sales_price',  __('lang_v1.sp') . ':') !!}
            {!! Form::number('sales_price', !empty($duplicate_product->sp) ? $duplicate_product->sp : null, ['step'=>'0.01', 'class' => 'form-control', 'placeholder' => __('lang_v1.sp')]); !!}
        </div>
    </div>
    <div class="col-sm-1" style="display: none;">
        <div class="form-group">
            {!! Form::label('weight',  __('lang_v1.wght') . ':') !!}
            {!! Form::text('weight', !empty($duplicate_product->wght) ? $duplicate_product->wght : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.wght')]); !!}
        </div>
    </div>

    {{-- <div class="col-sm-1">
        <div class="form-group">
            {!! Form::label('srp',  __('lang_v1.srp') . ':') !!}
            {!! Form::text('srp', !empty($duplicate_product->srp) ? $duplicate_product->srp : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.srp')]); !!}
        </div>
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            {!! Form::label('sales_price',  __('lang_v1.sp') . ':') !!}
            {!! Form::text('sales_price', !empty($duplicate_product->sp) ? $duplicate_product->sp : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.sp')]); !!}
        </div>
    </div>
    <div class="col-sm-1">
        <div class="form-group">
            {!! Form::label('weight',  __('lang_v1.wght') . ':') !!}
            {!! Form::text('weight', !empty($duplicate_product->wght) ? $duplicate_product->wght : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.wght')]); !!}
        </div>
    </div> --}}
    {{--
    <div class="col-sm-4">
        <div class="form-group">
            <label>Image Gallery</label>
            <input type="file" name="images[]" id="upload_image" multiple>
            <small>
                <p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p>
            </small>
        </div>
    </div>
    --}}
    <div class="col-sm-4">
        <div class="form-group">
            <label>Main Image</label>
            <input type="file" name="main_image" id="upload_main_image" onchange="return checkimageextension()" >
            <small>
                <p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p>
            </small>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label>Image Gallery</label>
            {{-- {!! Form::label('image', __('lang_v1.product_image') . ':') !!} --}}
            {!! Form::file('images[]', ['id' => 'upload_image','accept' => 'image/*' , 'multiple' => 'true']); !!}
            <small>
                <p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p>
            </small>
        </div>
    </div>
    <div class="col-sm-4">
            <div class="form-group" id="compressed_image">
            </div>
        </div>
    <div class="col-sm-2 not" style="display:none;">
        <div class="form-group">
            <br>
            <label>
            <input type="checkbox" class="input-icheck" value="1" name="sync" class="nyc"><strong>Do not sync with website</strong>
            </label>@show_tooltip(__('lang_v1.tooltip_not_for_selling'))
        </div>
    </div>
    <div class="col-sm-2 not">
        <div class="form-group">
            <br>
            <label>
            <input type="checkbox" class="input-icheck" value="1" name="sync_ecom" class="nyc"><strong>Do not sync with Ecomm</strong>
            </label>@show_tooltip(__('lang_v1.tooltip_not_for_selling'))
        </div>
    </div>
     <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('note', __('lang_v1.note') . ':') !!}
            {!! Form::textarea('note', !empty($duplicate_product->note) ? $duplicate_product->note : null, ['class' => 'form-control' , 'tabindex' => '17', 'id' => 'note','cols'=>"15", 'rows'=>"4"]); !!}
        </div>
    </div>
    @endcomponent
    <div class="row">
        <div class="col-sm-12">
            <input type="hidden" name="submit_type" id="submit_type">
            <div class="text-center">
                <div class="btn-group">
                    @if($selling_price_group_count)
                    <button type="submit" value="submit_n_add_selling_prices" class="btn btn-warning submit_product_form submit">@lang('lang_v1.save_n_add_selling_price_group_prices')</button>
                    @endif
                    @can('product.opening_stock')
                    <button id="opening_stock_button" @if(!empty($duplicate_product) && $duplicate_product->enable_stock == 0) disabled @endif type="submit" value="submit_n_add_opening_stock" class="btn bg-purple submit">@lang('lang_v1.save_n_add_opening_stock')</button>
                    @endcan
                    <button type="submit" value="save_n_add_another" class="btn bg-maroon submit">@lang('lang_v1.save_n_add_another')</button>
                    <button type="submit" value="save_n_sync" class="btn" id="syncsave" style="background: aquamarine;">Save & Sync with Website</button>
                    <button type="submit" value="save" class="btn btn-primary submit">@lang('messages.save')</button>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
</section>
<div class="modal fade variation_modal" tabindex="-1" role="dialog"
     aria-labelledby="gridSystemModalLabel">
</div>
<!-- /.content -->
<div class="modal fade category_modal" tabindex="-1" role="dialog"
    aria-labelledby="gridSystemModalLabel"></div>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container">
                    <form action="" method="post">
                        <label for="variations">Variations</label>
                        @foreach(\App\VariationTemplate::all() as $variation)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="exampleCheck1" value="{{ $variation->name }}">
                            <label class="form-check-label" for="exampleCheck1">{{ $variation->name }}</label>
                        </div>
                        @endforeach
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>
@section('javascript')
@php $asset_v = env('APP_VERSION'); @endphp
<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
<script type="text/javascript">
    function gencode(){
      document.getElementById('gen-product-code').value=Math.random().toString(36).substr(2, 9);
    }
</script>
<script type="text/javascript">
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
                        $(".submit").prop("disabled", false);
                    }
                    else
                    {
                        $(".submit").prop("disabled", true);
                    }
                    $("#gen-bar-code").val(result);
                    $("#gen-bar-code").focus();
                    $("#last_barcode").val(result);
                }
            });
      //document.getElementById('gen-bar-code').value=Math.random().toInt(36).substr(2, 12);
      //document.getElementById('gen-bar-code').value=Math.floor(Math.random() * 900000000000) + 100000000000;

    }
</script>
<script type="text/javascript">
    $(document).ready(function(){
        __page_leave_confirmation('#product_add_form');
        onScan.attachTo(document, {
            suffixKeyCodes: [13], // enter-key expected at the end of a scan
            reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
            onScan: function(sCode, iQty) {
                $('input#sku').val(sCode);
            },
            onScanError: function(oDebug) {
                console.log(oDebug);
            },
            minLength: 2,
            ignoreIfFocusOn: ['input', '.form-control']
            // onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
            //     console.log('Pressed: ' + iKeyCode);
            // }
        });
    });
</script>
<script type="text/javascript">
    // $(document).on('change','#flavour_id', function(){
    //   alert($( this ).val());
    // });

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
                    // category_table.ajax.reload();
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

    $("#gen-bar-code").on("focusout", function() {
        $(".already-exists-barcode").text('');
        var barcode = $(this).val();
        $.ajax({
            method: 'POST',
            url:"{{ url('products/check-barcode') }}",
            dataType: 'json',
            data: {'barcode':barcode},
            success: function(success) {
                if(success.message == ''){
                    if($(".already-exists-barcode").text()=="" && $(".already-exists-barcode2").text()=="" && $(".already-exists-barcode3").text()=="")
                        {
                            $(".submit").prop("disabled", false);
                        }
                        else
                        {
                            $(".submit").prop("disabled", true);
                        }
                }else{
                    $(".submit").prop("disabled", true);
                    $(".already-exists-barcode").text(success.message);
                }
             },
        });

    });

    $("#gen-bar-code2").on("focusout", function() {
        $(".already-exists-barcode2").text('');
        var barcode2 = $(this).val();
        $.ajax({
            method: 'POST',
            url:"{{ url('products/check-barcode') }}",
            dataType: 'json',
            data: {'barcode':barcode2},
            success: function(success) {
                if(success.message == ''){
                    if($(".already-exists-barcode").text()=="" && $(".already-exists-barcode2").text()=="" && $(".already-exists-barcode3").text()=="")
                    {
                        $(".submit").prop("disabled", false);
                    }
                    else
                    {
                        $(".submit").prop("disabled", true);
                    }
                }else{
                    $(".submit").prop("disabled", true);
                    $(".already-exists-barcode2").text(success.message);
                }
             },
        });

    });

    $("#gen-bar-code3").on("focusout", function() {
        $(".already-exists-barcode3").text('');
        var barcode3 = $(this).val();
        $.ajax({
            method: 'POST',
            url:"{{ url('products/check-barcode') }}",
            dataType: 'json',
            data: {'barcode':barcode3},
            success: function(success) {
                if(success.message == ''){
                    if($(".already-exists-barcode").text()=="" && $(".already-exists-barcode2").text()=="" && $(".already-exists-barcode3").text()=="")
                        {
                            $(".submit").prop("disabled", false);
                        }
                        else
                        {
                            $(".submit").prop("disabled", true);
                        }
                }else{
                    $(".submit").prop("disabled", true);
                    $(".already-exists-barcode3").text(success.message);
                }
             },
        });
    });

    $("#gen-product-code").on("change", function(){
         $(".already-exists-itemcode").text('');
        var item_code = $(this).val();

        $.ajax({
            method: 'POST',
            url:"{{ url('products/check-itemcode') }}",
            dataType: 'json',
            data: {'item_code':item_code},
            success: function(success) {

                if(success.success == true){
                    $(".submit").prop("disabled", true);
                    $(".already-exists-itemcode").text(success.message);
                }else{
                    $(".submit").prop("disabled", false);
                    $(".already-exists-itemcode").text(success.message);
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

        if(parseFloat(cost_price) > parseFloat(sell_price) || parseFloat(cost_price) > parseFloat(sell_price_tier1) || parseFloat(cost_price) > parseFloat(sell_price_tier2) || parseFloat(cost_price) > parseFloat(sell_price_tier3) ||  parseFloat(cost_price) > parseFloat(sell_price_tier4)){
            $(".costerror").text('');
        }

        if(parseFloat(sell_price) < parseFloat(cost_price)){
            $(".selling").text('selling price should be greater than or equal to cost price');
            $(this).focus();
            $(".submit").prop("disabled", true);

        }else{
            $(".selling").text('');
            $(".costerror").text('');
            $(".submit").prop("disabled", false);
        }

        if(parseFloat(sell_price_tier1) < parseFloat(cost_price)){
            $(".tier1").text('Tier-1 price should be greater than or equal to cost price');
            $(this).focus();
            $(".submit").prop("disabled", true);

        }else{
            $(".tier1").text('');
            $(".costerror").text('');
            $(".submit").prop("disabled", false);
        }

        if(parseFloat(sell_price_tier2) < parseFloat(cost_price)){
            $(".tier2").text('Tier-2 price should be greater than or equal to cost price');
            $(this).focus();
            $(".submit").prop("disabled", true);

        }else{
            $(".tier2").text('');
            $(".costerror").text('');
            $(".submit").prop("disabled", false);
        }

        if(parseFloat(sell_price_tier3) < parseFloat(cost_price)){
            $(".tier3").text('Tier-3 price should be greater than or equal to cost price');
            $(this).focus();
            $(".submit").prop("disabled", true);

        }else{
            $(".tier3").text('');
            $(".costerror").text('');
            $(".submit").prop("disabled", false);
        }


        if(parseFloat(sell_price_tier4) < parseFloat(cost_price)){
            $(".tier4").text('Tier-4 price should be greater than or equal to cost price');
            $(this).focus();
            $(".submit").prop("disabled", true);

        }else{
            $(".tier4").text('');
            $(".costerror").text('');
            $(".submit").prop("disabled", false);
        }
        if($("span.costerror").text().trim() != "" || $("span.error").text().trim() != "")
        {
            $(".submit").prop("disabled", true);
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
            $(".submit").prop("disabled", true);
        }
        else if(parseFloat(cost_price) > parseFloat(sell_price_tier1))
        {
            $(".costerror").text('cost price should be less than or equal to tier price 1');
            $(this).focus();
            $(".error").text('');
            $(".submit").prop("disabled", true);
        }
        else if(parseFloat(cost_price) > parseFloat(sell_price_tier2))
        {
            $(".costerror").text('cost price should be less than or equal to tier price 2');
            $(this).focus();
            $(".error").text('');
            $(".submit").prop("disabled", true);
        }
        else if(parseFloat(cost_price) > parseFloat(sell_price_tier3))
        {
            $(".costerror").text('cost price should be less than or equal to tier price 3');
            $(this).focus();
            $(".error").text('');
            $(".submit").prop("disabled", true);
        }
        else if(parseFloat(cost_price) > parseFloat(sell_price_tier4))
        {
            $(".costerror").text('cost price should be less than or equal to tier price 4');
            $(this).focus();
            $(".error").text('');
            $(".submit").prop("disabled", true);
        }
        else
        {
            $(".costerror").text('');
            $(".error").text('');
            $(".submit").prop("disabled", false);
        }
    });

    $(document).on('click', '.submit', function() {
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
    //     var fileInput =
    //             document.getElementById('upload_main_image');

    //         var filePath = fileInput.value;

    //         // Allowing file type
    //         var allowedExtensions =
    //                 /(\.jpg|\.jpeg|\.png)$/i;

    //         if (!allowedExtensions.exec(filePath)) {
    //             alert('Invalid file type');
    //             fileInput.value = '';
    //             return false;
    //         }
    //         else
    //         {
    //             // Image preview
    //             if (fileInput.files && fileInput.files[0]) {
    //                 var reader = new FileReader();
    //                 reader.onload = function(e) {
    //                     document.getElementById(
    //                         'imagePreview').innerHTML =
    //                         '<img src="' + e.target.result
    //                         + '"/>';
    //                 };

    //                 reader.readAsDataURL(fileInput.files[0]);
    //             }
    //         }
    //     }

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
    .not{
    margin-top:10px;
    }
    .head{
    padding-left: 15px;
    }
    .e-com{
    margin-left: 15px;
    }
      .costerror{
            color: red;
        }
</style>
@endsection