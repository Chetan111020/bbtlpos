@extends('layouts.app')
@section('title', __('barcode.print_labels'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
<br>
    <h1>@lang('barcode.print_labels') @show_tooltip(__('tooltip.print_label'))</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content no-print">
	{!! Form::open(['url' => '#', 'method' => 'post', 'id' => 'preview_setting_form', 'onsubmit' => 'return false']) !!}
	@component('components.widget', ['class' => 'box-primary', 'title' => __('product.add_product_for_labels')])
		<div class="row">
			<div class="col-sm-8 col-sm-offset-2">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-search"></i>
						</span>
						{!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product_for_label', 'placeholder' => __('lang_v1.enter_product_name_to_print_labels'), 'autofocus']); !!}
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-8 col-sm-offset-2">
				<table class="table table-bordered table-striped table-condensed" id="product_table">
					<thead>
						<tr>
							<th class="col-sm-8">@lang( 'barcode.products' )</th>
							<th class="col-sm-4">@lang( 'barcode.no_of_labels' )</th>
						</tr>
					</thead>
					<tbody>
						@include('labels.partials.show_table_rows', ['index' => 0])
					</tbody>
				</table>
			</div>
		</div>
	@endcomponent

	@component('components.widget', ['class' => 'box-primary', 'title' => __( 'barcode.info_in_labels' )])
		<div class="row">

			<div class="col-sm-3">
				<div class="checkbox">
				    <label>
				    	<input type="checkbox" style="height:15px;width:15px;"  name="print[price]" value="1" id="is_show_price"> <b style="font-size: larger;">@lang( 'barcode.print_price' )</b>
				    </label>
				</div>
			</div>

			<div class="col-sm-3">
				<div class="checkbox">
				    <label>
				    	<input type="checkbox" style="height:15px;width:15px;"  name="print[business_name]"  id="is_business_name" > <b style="font-size: larger;">@lang( 'barcode.print_business_name' )</b>
				    </label>
				</div>
			</div>

        	<div class="col-sm-3">
				<div class="checkbox">
				    <label>
				    	<input type="checkbox" style="height:15px;width:15px;"  name="print[item_code]"  id="is_item_code" > <b style="font-size: larger;">@lang( 'Print Item Code' )</b>
				    </label>
				</div>
			</div>


			<div class="col-sm-3">
				<div class="checkbox">
				    <label>
				    	<input type="checkbox" style="height:15px;width:15px;"  checked name="print[name]" value="1"> <b style="font-size: larger;">@lang( 'barcode.print_name' )</b>
				    </label>
				</div>
			</div>

			<div class="col-sm-3">
				<div class="checkbox">
				    <label>
				    	<input type="checkbox" style="height:15px;width:15px;"  name="print[variations]"  id="is_variations" > <b style="font-size: larger;">Product Variation</b>
				    </label>
				</div>
			</div>

	        <div class="col-sm-3">
				<div class="checkbox">
				    <label>
				    	<input type="checkbox" style="height:15px;width:15px;"  checked name="print[sku]" value="1"> <b style="font-size: larger;">@lang( 'Print Barcode' )</b>
				    </label>
				</div>
			</div>
			<div class="col-sm-3 hide">
				<div class="checkbox">
				    <label>
				    	<input type="checkbox" style="height:15px;width:15px;"  checked name="print[sku2]" value="1"> <b style="font-size: larger;">@lang( 'Print Barcode 2 ' )</b>
				    </label>
				</div>
			</div>
			<div class="col-sm-3 hide">
				<div class="checkbox">
				    <label>
				    	<input type="checkbox" style="height:15px;width:15px;"  checked name="print[sku3]" value="1"> <b style="font-size: larger;">@lang( 'Print Barcode 3' )</b>
				    </label>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="checkbox">
				    <label>
				    	<input type="checkbox" style="height:15px;width:15px;"  name="print[qrcode]"> <b style="font-size: larger;">@lang( 'Print QR code' )</b>
				    </label>
				</div>
			</div>

			<div class="col-sm-3">
				<div class="checkbox">
				    <label>
				    	<input type="checkbox" style="height:15px;width:15px;"  checked name="print[bold_font]" value="1"> <b style="font-size: larger;">Bold Font</b>
				    </label>
				</div>
			</div>

            <div class="col-sm-3">
				<div class="checkbox">
				    <label>
				    	<input type="checkbox" style="height:15px;width:15px;" name="print[reg_and_sales]" value="1"> <b style="font-size: larger;">Print Sale Price</b>
				    </label>
				</div>
			</div>

            <div class="col-sm-3" id="price_type_div">
				<div class="form-group">
					{!! Form::label('print[price_type]', @trans( 'barcode.show_price' ) . ':') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-info"></i>
						</span>
						{!! Form::select('print[price_type]', ['inclusive' => __('product.inc_of_tax'), 'exclusive' => __('product.exc_of_tax')], 'inclusive', ['class' => 'form-control']); !!}
					</div>
				</div>
			</div>

			<div class="col-sm-12">
				<hr/>
			</div>

			<div class="col-sm-4">
				<div class="form-group">
					{!! Form::label('barcode_setting', @trans( 'barcode.barcode_setting' ) . ':') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-cog"></i>
						</span>
						{!! Form::select('barcode_setting', $barcode_settings, 13, ['id'=>'barcode_setting','class' => 'form-control']); !!}
					</div>
				</div>
			</div>

            <div class="col-sm-4">
                <div class="form-group">
					{!! Form::label('font_size', 'Font Size:') !!}
                    <select class="form-control" name="font_size">
                        <option value="s3">Tiny</option>
                        <option value="s2">XS</option>
                        <option value="s1">Small</option>
                        <option value="reg">Normal</option>
                        <option value="l1"  selected>Large</option>
                        <option value="l2" >XL</option>
                        <!--<option value="l3">Big</option>-->
                        <option value="l3">2XL</option>
                        <option value="l4">3XL</option>
                    </select>
				</div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
					{!! Form::label('label_type', 'Label Type:') !!}
                    <select class="form-control" name="label_type">
                        <option value="normal" selected>Normal Label</option>
                        <option value="rack">Rack Label</option>
                        <option value="upc">UPC Label</option>
                    </select>
				</div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
					{!! Form::label('qr_size', 'QR Size:') !!}
                    <input type="range" value="50" name="print[qr_size]">
				</div>
            </div>


			{{-- <div class="clearfix"></div>

            <div class="col-sm-4" style="height: 64px;display: flex;align-items: flex-end;">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" style="height:15px;width:15px;" name="show_qr" value="1">
                        <b style="font-size: larger;">Print Rack Labels</b>
                    </label>
                </div>
            </div> --}}

			<div class="col-sm-12" style="height: 40px;"></div>

			<div class="col-sm-2 col-sm-offset-5">
				<button type="button" id="labels_preview" class="btn btn-primary pull-right btn-flat btn-block">@lang( 'barcode.preview' )</button>
			</div>
		</div>
	@endcomponent
	{!! Form::close() !!}

	<div class="col-sm-8 hide display_label_div">
		<h3 class="box-title">@lang( 'barcode.preview' )</h3>
		<button type="button" class="col-sm-offset-2 btn btn-success btn-block" id="print_label">Print</button>
	</div>
	<div class="clearfix"></div>
</section>

<!-- Preview section-->
<div id="preview_box">
</div>

@stop
@section('javascript')
	<script src="{{ asset('js/labels.js?v=' . $asset_v) }}"></script>
@endsection
