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
                {!! Form::label('name', __('product.product_name') . ':*') !!}
                  {!! Form::text('name', $product->name, ['class' => 'form-control', 'required',
                  'placeholder' => __('product.product_name')]); !!}
              </div>
            </div>

            
        {{-- <div class="col-sm-3 hidden">
          <div class="form-group">
            {!! Form::label('item_code', __('product.item_code') . ':*') !!}
            <div class="input-group">
              {!! Form::text('item_code', $product->item_code, ['class' => 'form-control', 'id' => 'gen-product-code','required',
              'placeholder' => __('product.item_code')]); !!}
              <span class="input-group-btn">
                <button type="button" class="btn btn-default bg-white btn-flat btn-modal" id="generate-item-code" onclick="gencode()" ><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
              </span>
             </div>
          </div>
        </div> --}}
     

            <div class="col-sm-3 @if(!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
              <div class="form-group">
                {!! Form::label('sku', __('product.sku')  . ':*') !!} @show_tooltip(__('tooltip.sku'))
                {!! Form::text('sku', $product->sku, ['class' => 'form-control',
                'placeholder' => __('product.sku'), 'required', 'readonly']); !!}
              </div>
            </div>

            <div class="col-sm-3">
          <div class="form-group">
            <br>
            <label>
              {!! Form::checkbox('not_for_selling', 1, $product->not_for_selling, ['class' => 'input-icheck']); !!} <strong>@lang('lang_v1.not_for_selling')</strong>
            </label> @show_tooltip(__('lang_v1.tooltip_not_for_selling'))
          </div>
        </div>

            <!-- <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('barcode_type', __('product.barcode_type') . ':*') !!}
                  {!! Form::select('barcode_type', $barcode_types, $product->barcode_type, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2', 'required']); !!}
              </div>
            </div> -->

            <div class="clearfix"></div>
            
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


            <!-- <div class="col-sm-4 @if(!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
              <div class="form-group">
                {!! Form::label('sub_category_id', __('product.sub_category')  . ':') !!}
                  {!! Form::select('sub_category_id', $sub_categories, $product->sub_category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
              </div>
            </div> -->

           

            <!-- <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('product_locations', __('business.business_locations') . ':') !!} @show_tooltip(__('lang_v1.product_location_help'))
                  {!! Form::select('product_locations[]', $business_locations, $product->product_locations->pluck('id'), ['class' => 'form-control select2', 'multiple', 'id' => 'product_locations']); !!}
              </div>
            </div> -->

            <div class="clearfix"></div>
            
            <div class="col-sm-3">
              <div class="form-group">
              <br>
                <label>
                  {!! Form::checkbox('enable_stock', 1, $product->enable_stock, ['class' => 'input-icheck', 'id' => 'enable_stock']); !!} <strong>@lang('product.manage_stock')</strong>
                </label>@show_tooltip(__('tooltip.enable_stock')) <p class="help-block"><i>@lang('product.enable_stock_help')</i></p>
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
        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('case_qty',  __('lang_v1.weight') . ':') !!}
            {!! Form::text('case_qty', $product->case_qty, ['class' => 'form-control', 'placeholder' => __('lang_v1.weight')]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
         <div class="col-sm-3">
              <div class="form-group">
                {!! Form::label('type', __('product.product_type') . ':*') !!} @show_tooltip(__('tooltip.product_type'))
                {!! Form::select('type', $product_types, $product->type, ['class' => 'form-control select2',
                  'required','disabled', 'data-action' => 'edit', 'data-product_id' => $product->id ]); !!}
              </div>
            </div>

            <div class="form-group col-sm-12" id="product_form_part"></div>
            <input type="hidden" id="variation_counter" value="0">
            <input type="hidden" id="default_profit_percent" value="{{ $default_profit_percent }}">
            <div class="clearfix"></div>
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

    @component('components.widget', ['class' => 'box-primary'])

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

          

        <div class="clearfix"></div>

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


        
        <div class="clearfix"></div>
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
            {{-- <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('image', __('lang_v1.product_image') . ':') !!}
                {!! Form::file('image', ['id' => 'upload_image', 'accept' => 'image/*'  , 'multiple' => 'true']); !!}
                <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]). @lang('lang_v1.aspect_ratio_should_be_1_1') @if(!empty($product->image)) <br> @lang('lang_v1.previous_image_will_be_replaced') @endif</p></small>
              </div>
            </div> --}}
            <div class="col-sm-1">
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
        </div>
        <div class="col-sm-4">
              <div class="form-group">
                <label>Main Image</label>
                <input type="file" name="main_image" id="upload_main_image">
                <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p></small>
              </div>
            </div>
        <div class="col-sm-4">
              <div class="form-group">
                <label>Image Gallery</label>
                <input type="file" name="images[]" id="upload_image" multiple>
                <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p></small>
              </div>
            </div>
            
            
            <div class="col-sm-2 not">
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
                    <input type="checkbox" class="input-icheck" value="1" name="sync" class="nyc"><strong>Do not sync with Ecomm</strong>
                </label>@show_tooltip(__('lang_v1.tooltip_not_for_selling'))
            </div>
        </div>
          </div>
    @endcomponent

  <div class="row">
    <input type="hidden" name="submit_type" id="submit_type">
        <div class="col-sm-12">
          <div class="text-center">
            <div class="btn-group">
              @if($selling_price_group_count)
                <button type="submit" value="submit_n_add_selling_prices" class="btn btn-warning submit_product_form">@lang('lang_v1.save_n_add_selling_price_group_prices')</button>
              @endif

              @can('product.opening_stock')
              <button type="submit" @if(empty($product->enable_stock)) disabled="true" @endif id="opening_stock_button"  value="update_n_edit_opening_stock" class="btn bg-purple submit_product_form">@lang('lang_v1.update_n_edit_opening_stock')</button>
              @endif

              <button type="submit" value="save_n_add_another" class="btn bg-maroon submit_product_form">@lang('lang_v1.update_n_add_another')</button>

              <button type="submit" value="submit" class="btn btn-primary">@lang('messages.update')</button>
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

</style>
@endsection