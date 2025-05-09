@extends('layouts.app')
@section('title', __('Add Purchase Order'))

@section('content')
<style type="text/css">
input[type=checkbox]{
	height: 0;
	width: 0;
	visibility: hidden;
}

.tgl {
	cursor: pointer;
	text-indent: -9999px;
	width: 50px;
	height: 28px;
	background: grey;
	display: block;
	border-radius: 100px;
	position: relative;
    margin: 0;
}

.tgl:after {
	content: '';
	position: absolute;
	top: 5px;
	left: 5px;
	width: 18px;
	height: 18px;
	background: #fff;
	border-radius: 90px;
	transition: 0.3s;
}

input:checked + .tgl {
	background: #346be2;
}

input:checked + .tgl:after {
	left: calc(100% - 5px);
	transform: translateX(-100%);
}


	.hide {
		display: none;
	}
	.show{
		display: block;
	}
	.table-height {
		height: 600px;
	}
	.text-wrap{
	    white-space:normal;
	}
	.width-90{
	    width:90%;
	}
	.failure{
		color: #a94442; border-color: #ebccd1; display: none;
	}
	.fileinput-upload-button
	{
		display: none;
	}

    .tableFixHead {
        overflow-y: auto;
    }
    .tableFixHead thead th {
        position: sticky;
        background:#808080;
        color:#fff;
        top: 0px;
    }

	.table.supplier_products_table_custom>tbody>tr>td, .table.supplier_products_table_custom>tbody>tr>th, .table.supplier_products_table_custom>tfoot>tr>td, .table.supplier_products_table_custom>tfoot>tr>th, .table.supplier_products_table_custom>thead>tr>td, .table.supplier_products_table_custom>thead>tr>th {
   /* padding: 8px;
    line-height: 0.628571;
    vertical-align: top;
    border-top: 1px solid #ddd;
    font-size: 12px;
    color: black;*/
    padding: 4px;
    font-size: 14px;
    color: black;
}

table.supplier_products_table_custom.dataTable.fixedHeader-floating {
    position: unset !important;
}

input.row-select, input#select-all-row {
    width: 12px;
    height: 12px;
}

button.form-control.btn.btn-primary.pull-left.btn-flat {
    background: #1572e8;
}

</style>



	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1>@lang('Add Purchase Order') <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('purchase_order.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h1>
	</section>

	<!-- Main content -->
	<section class="content">
		<!-- Page level currency setting -->
		<input type="hidden" id="p_code" value="{{$currency_details->code}}">
		<input type="hidden" id="p_symbol" value="{{$currency_details->symbol}}">
		<input type="hidden" id="p_thousand" value="{{$currency_details->thousand_separator}}">
		<input type="hidden" id="p_decimal" value="{{$currency_details->decimal_separator}}">

		@include('layouts.partials.error')

		{!! Form::open(['url' => action('PurchaseOrderController@store'), 'method' => 'post', 'id' => 'add_purchase_form', 'files' => true ]) !!}
		@component('components.widget', ['class' => 'box-primary'])
		<input type="hidden" id="item_addition_method" value="1">
			<div class="row">
				<div class="@if(!empty($default_purchase_status)) col-sm-4 @else col-sm-3 @endif">
					<div class="form-group">
						{!! Form::label('supplier_id', __('purchase.supplier') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-user"></i>
							</span>
							{!! Form::select('contact_id', [], null, ['class' => 'form-control', 'placeholder' => __('messages.please_select'), 'required', 'id' => 'supplier_id']); !!}
							<span class="input-group-btn">
								<button type="button" class="btn btn-default bg-white btn-flat add_new_supplier" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
							</span>
						</div>
					</div>
				</div>

				<div class="@if(!empty($default_purchase_status)) col-sm-4 @else col-sm-3 @endif">
					<div class="form-group">
						{!! Form::label('transaction_date', __('Date') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</span>
							{!! Form::text('transaction_date', @format_datetime('now'), ['class' => 'form-control', 'required']); !!}
						</div>
					</div>
				</div>

				<div class="col-sm-3 @if(!empty($default_purchase_status)) hide @endif">
					<div  class="form-group">
						{!! Form::label('status', __('PO Status') . ':*') !!} @show_tooltip(__('tooltip.order_status'))
						{!! Form::select('status', $orderStatuses,'received', ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
					</div>
				</div>


				@if(count($business_locations) == 1)
					@php
						$default_location = current(array_keys($business_locations->toArray()));
						$search_disable = false;
					@endphp
				@else
					@php
						$default_location = null;
						$search_disable = true;
					@endphp
				@endif
				<div style="display:none" class="col-sm-3">
					<div class="form-group">
						{!! Form::label('location_id', __('purchase.business_location').':*') !!}
						@show_tooltip(__('tooltip.purchase_location'))
						{!! Form::select('location_id', $business_locations, $default_location, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required'], $bl_attributes); !!}
					</div>
				</div>

				<!-- Currency Exchange Rate -->
				<div class="col-sm-3 @if(!$currency_details->purchase_in_diff_currency) hide @endif">
					<div class="form-group">
						{!! Form::label('exchange_rate', __('purchase.p_exchange_rate') . ':*') !!}
						@show_tooltip(__('tooltip.currency_exchange_factor'))
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-info"></i>
							</span>
							{!! Form::number('exchange_rate', $currency_details->p_exchange_rate, ['class' => 'form-control', 'required', 'step' => 0.001]); !!}
						</div>
						<span class="help-block text-danger">
							@lang('purchase.diff_purchase_currency_help', ['currency' => $currency_details->name])
						</span>
					</div>
				</div>

				<div class="col-md-3" style="display: none;">
					<div class="form-group">
						<div class="multi-input">
						{!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show_tooltip(__('tooltip.pay_term'))
						<br/>
						{!! Form::number('pay_term_number', null, ['class' => 'form-control width-40 pull-left', 'placeholder' => __('contact.pay_term')]); !!}

						{!! Form::select('pay_term_type',
							['months' => __('lang_v1.months'),
								'days' => __('lang_v1.days')],
								null,
							['class' => 'form-control width-60 pull-left','placeholder' => __('messages.please_select'), 'id' => 'pay_term_type']); !!}
						</div>
					</div>
				</div>

				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label('document', __('purchase.attach_document') . ':') !!}
						{!! Form::file('documents[]', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types'))), 'class' => 'file']); !!}

						<p class="help-block">
							@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
							@includeIf('components.document_help_text')
						</p>
						<div class="contents"></div>
						<br><span><a href="javascript:void(0);" class="add btn btn-default" >Add More</a></span>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label('add_product', __('Add Products (100 Products)') . ':') !!}
						{!! Form::button('Add All Products', array('class' => 'form-control btn btn-primary pull-left btn-flat add_all_products', 'name' => 'add_product', 'disabled' => true)) !!}
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label('add_product_low_stock', __('Add Low Stock (100 Products)') . ':') !!}
						{!! Form::button('Add Low Stocks Items', array('class' => 'form-control btn btn-primary pull-left btn-flat add_product_low_stock', 'data-low-stock' => 1, 'name' => 'add_product_low_stock', 'disabled' => true)) !!}
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label('add_purchase_products', __('Add Purchase Products') . ':') !!}
						{!! Form::button('Add Purchase Product', array('class' => 'form-control btn btn-primary pull-left btn-flat add_purchase_products', 'data-purchase-product' => 1,'name' => 'add_purchase_products', 'disabled' => true)) !!}
					</div>
				</div>
			</div>


		@endcomponent

		@component('components.widget', ['class' => 'box-primary'])
			<div class="row">
				<div class="col-md-3 col-sm-12 mb-2" style="float: right;">
		            <div class="form-group">
		                {!! Form::label('item_table_filter_date_range', __('report.date_range') . ':') !!}
		                {!! Form::text('item_table_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
		            </div>
		        </div>
                @if(auth()->user()->id == 6)
                <div class="col-xs-3 bg-white" style="margin-bottom:15px;padding:10px;">
                    <div class="col-xs-6" style="display:flex;align-items:center;">
                        <strong>Multi Select</strong>
                    </div>
                    <div class="col-xs-6" style="display:flex;justify-content:end;align-items:center;">
                        <input type="checkbox" id="multi_select_label" /><label for="multi_select_label" class="tgl">Toggle</label>
                    </div>
                    <div class="col-xs-6" style="display:flex;">
                        <button type="button" class="btn btn-primary">Add</button>
                        <button type="button" class="btn" style="margin-left: 15px;">Clear</button>
                    </div>
                </div>
                @endif
			    <span id="successnew"></span>
				<div class="col-sm-8 col-sm-offset-2">
					<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-search"></i>
							</span>
							{!! Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product_two', 'placeholder' => __('lang_v1.search_product_placeholder'), 'disabled' => $search_disable]); !!}
						</div>
					</div>
					<div class="alert-box failure" id="search_prod">No matching product found!</div>
					<div class="alert-box failure" id="search_prod_not_sell">Not For Selling!</div>
				</div>
				<div class="col-sm-2">
					<div class="form-group">
						<button tabindex="-1" type="button" class="btn btn-link btn-modal" data-href="{{action('ProductController@quickAdd')}}" data-container=".quick_add_product_modal">
							<i class="fa fa-plus"></i> @lang( 'product.add_new_product' )
						</button>
					</div>
				</div>
			</div>

			@php
				$hide_tax = '';
				if( session()->get('business.enable_inline_tax') == 0){
					$hide_tax = 'hide';
				}
			@endphp
    <input type="hidden" id="product_ids" value="{{ $product_ids ?? '' }}"/>
			<div class="row">
				<div class="col-sm-12">
					<div class="card-body table-responsive p-0" id="entry_table_row">
					    <input type="hidden" id="div_height" value="0">
						<table style="background:#808080; color:#fff" class="table table-condensed table-bordered text-center table-striped table-head-fixed text-nowrap tableFixHead" id="purchase_entry_table">
							<thead>
								<tr>
								    <th><input type="checkbox" id="selectAll"/></th>
									<th>#</th>
									<th>@lang( 'product.product_name' )</th>
									<th>@lang( 'On Hand' )</th>
									<th>@lang( 'Case QTY' )</th>
									<th>Safety Stock <br><small>(3 Month Avg Sale QTY)</small></th>
									<th>Buffer Stock <br><small>(15 Days Buffer QTY)</small></th>
									<th>@lang( 'purchase.purchase_quantity' )</th>
									<th>@lang( 'Cost' )</th>
									<th>@lang( 'Previous Cost' )</th>
									<th>@lang( 'Total' )</th>
									<th>@lang( 'Total Sold' )</th>
									<th><i class="fa fa-trash" aria-hidden="true"></i></th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
					<button type="button" class="btn btn-xs btn-danger"id="bulkremove">Remove Selected</button>
					<hr/>
					<div class="col-lg-3">
						<div class="form-group">
					        <div class="box-body">
				              <div class="row">
				                <div class="col-xs-6">
					                {!! Form::label('ref_no', __('Shipping Charges').':') !!}
									{!! Form::number('additional_charges', null, ['class' => 'form-control', 'id' => 'additional_charges']); !!}
									{!! Form::hidden('shipping_charges', 0, ['class' => 'form-control input_number', 'id' => 'shipping_charges']); !!}
									<div style="margin: 1px;">
						               	<span class="btn btn-warning" onclick="additionalChargesFun()" style="margin-bottom: 5px; margin-top: 5px;">Apply</span>
										<span class="btn btn-danger" onclick="additionalChargesRemove()">Reset</span>
					              	</div>
				                </div>

				                <div class="col-xs-6">
					                {!! Form::label('discount', __('Discount').':') !!}
									{!! Form::number('discount_charges', null, ['class' => 'form-control', 'id' => 'discount_charges']); !!}
									{!! Form::hidden('discount_amount', 0, ['class' => 'form-control input_number', 'id' => 'discount_amount']); !!}
									{!! Form::hidden('discount_type', 'fixed', ['class' => 'form-control input_number', 'id' => 'discount_type']); !!}
									<div style="margin: 1px;">
										<span class="btn btn-warning" onclick="discountChargesFun()" style="margin-bottom: 5px; margin-top: 5px;">Apply</span>
										<span class="btn btn-danger" onclick="discountChargesRemove()">Reset</span>
									</div>
				                </div>
				              </div>

				            </div>

				        </div>
					</div>
					<div class="pull-right col-md-5">
						<table class="pull-right col-md-12">
							<tr>
								<th class="col-md-7 text-right">@lang( 'lang_v1.total_items' ):</th>
								<td class="col-md-5 text-left">
									<span id="total_quantity" class="display_currency" data-currency_symbol="false"></span>
								</td>
							</tr>
							<tr class="hide">
								<th class="col-md-7 text-right">@lang( 'purchase.total_before_tax' ):</th>
								<td class="col-md-5 text-left">
									<span id="total_st_before_tax" class="display_currency"></span>
									<input type="hidden" id="st_before_tax_input" value=0>
								</td>
							</tr>
							<tr class="">
								<th class="col-md-7 text-right">Shipping Charge:</th>
								<th class="col-md-5 text-left">
									<span id="sh-charges" class="" >0.00</span>
								</th>
							</tr>
							<tr class="" id="discount_show">
								<th class="col-md-7 text-right">Discount:</th>
								<th class="col-md-5 text-left">
									<span id="total_discount" class="">0.00</span>
									<!-- This is total before purchase tax-->
									<input type="hidden" id="total_discount_input" value=0  name="total_discount_name">
								</th>
							</tr>
							<tr>
								<th class="col-md-7 text-right">@lang( 'purchase.net_total_amount' ):</th>
								<td class="col-md-5 text-left">
									<span id="total_subtotal" style="color: #777;" class="display_currency"></span>
									<!-- This is total before purchase tax-->
									<input type="hidden" id="total_subtotal_input" value=0  name="total_before_tax">
									{!! Form::hidden('final_total', 0 , ['id' => 'grand_total_hidden']); !!}
								</td>
							</tr>
						</table>
					</div>

					<input type="hidden" id="row_count" value="0">
				</div>
			</div>
		@endcomponent

		<div style="display:block;">
		@component('components.widget', ['class' => 'box-primary componentHide'])
			<div class="row">
				<div class="col-sm-12">
				<table class="table">
					<tr>
						<td colspan="4">
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										{!! Form::label('additional_notes',__('purchase.additional_notes')) !!}
										{!! Form::textarea('additional_notes', null, ['class' => 'form-control', 'rows' => 3]); !!}
									</div>
								</div>

								<div class="col-md-3 mt-10">
									<div class="form-group">
										{!! Form::label('shipping_date',__('Shipping Date:')) !!}
										{!! Form::date('shipping_date', null, ['class' => 'form-control']); !!}
									</div>
								</div>

								<div class="col-md-3 mt-10">
									<div class="form-group">
										{!! Form::label('shipping_carier',__('Shipping Carier:')) !!}
										{!! Form::text('shipping_carier', null, ['class' => 'form-control']); !!}
									</div>
								</div>

								<div class="col-md-3 mt-10">
									<div class="form-group">
										{!! Form::label('tracking_id',__('Tracking Id:')) !!}
										{!! Form::text('tracking_id', null, ['class' => 'form-control']); !!}
									</div>
								</div>

								{{-- <div class="col-md-2 mt-10">
									<div class="form-group">
										{!! Form::label('additional_shipping_charges',__('(+) Additional Shipping Charges:')) !!}
										{!! Form::number('shipping_charges', 0, ['class' => 'form-control additional_shipping_charges']); !!}
									</div>
								</div>

								<div class="col-md-2 mt-10">
									<div class="form-group">
										{!! Form::label('discount',__('Discount:')) !!}
										{!! Form::number('discount', 0, ['class' => 'form-control discount']); !!}
									</div>
								</div> --}}

								<div class="col-md-3 mt-10">
									<div class="form-group">
										{!! Form::label('eta',__('ETA:')) !!}
										{!! Form::date('eta', null, ['class' => 'form-control']); !!}
									</div>
								</div>

							</div>
						</td>
					</tr>

				</table>
				</div>
			</div>
		@endcomponent
		</div>

		{{-- <div style="display:none;">
		@component('components.widget', ['class' => 'box-primary componentHide'])
			<div class="row">
				<div class="col-sm-12">
				<table class="table">
					<tr>
						<td class="col-md-3">
							<div class="form-group">
								{!! Form::label('discount_type_2', __( 'purchase.discount_type' ) . ':') !!}
								{!! Form::select('discount_type_2', [ '' => __('lang_v1.none'), 'fixed' => __( 'lang_v1.fixed' ), 'percentage' => __( 'lang_v1.percentage' )], '', ['class' => 'form-control select2']); !!}
							</div>
						</td>
						<td class="col-md-3">
							<div class="form-group">
							{!! Form::label('discount_amount_2', __( 'purchase.discount_amount' ) . ':') !!}
							{!! Form::text('discount_amount_2', 0, ['class' => 'form-control input_number', 'required']); !!}
							</div>
						</td>
						<td class="col-md-3">
							&nbsp;
						</td>
						<td class="col-md-3">
							<b>@lang( 'purchase.discount' ):</b>(-)
							<span id="discount_calculated_amount" class="display_currency">0</span>
						</td>
					</tr>
					<tr>
						<td>
							<div class="form-group">
							{!! Form::label('tax_id', __('purchase.purchase_tax') . ':') !!}
							<select name="tax_id" id="tax_id" class="form-control select2" placeholder="'Please Select'">
								<option value="" data-tax_amount="0" data-tax_type="fixed" selected>@lang('lang_v1.none')</option>
								@foreach($taxes as $tax)
									<option value="{{ $tax->id }}" data-tax_amount="{{ $tax->amount }}" data-tax_type="{{ $tax->calculation_type }}">{{ $tax->name }}</option>
								@endforeach
							</select>
							{!! Form::hidden('tax_amount', 0, ['id' => 'tax_amount']); !!}
							</div>
						</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>
							<b>@lang( 'purchase.purchase_tax' ):</b>(+)
							<span id="tax_calculated_amount" class="display_currency">0</span>
						</td>
					</tr>

					<tr>
						<td>
							<div class="form-group">
							{!! Form::label('shipping_details', __( 'purchase.shipping_details' ) . ':') !!}
							{!! Form::text('shipping_details', null, ['class' => 'form-control']); !!}
							</div>
						</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>
							<div class="form-group">
							{!! Form::label('shipping_charges_2','(+) ' . __( 'purchase.additional_shipping_charges' ) . ':') !!}
							{!! Form::text('shipping_charges_2', 0, ['class' => 'form-control input_number', 'required']); !!}
							</div>
						</td>
					</tr>

					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>
							{!! Form::hidden('final_total', 0 , ['id' => 'grand_total_hidden']); !!}
							<b>@lang('purchase.purchase_total'): </b><span id="grand_total" class="display_currency" data-currency_symbol='true'>0</span>
						</td>
					</tr>
					<tr>
						<td colspan="4">
							<div class="form-group">
								{!! Form::label('additional_notes_two',__('purchase.additional_notes')) !!}
								{!! Form::textarea('additional_notes_two', null, ['class' => 'form-control', 'rows' => 3]); !!}
							</div>
						</td>
					</tr>

				</table>
				</div>
			</div>
		@endcomponent
		</div> --}}

		@component('components.widget', ['class' => 'box-primary componentpurchase_entry_tableHide'])
			<div style="display:none">
			<div class="box-body payment_row">
				<div class="row">
					<div class="col-md-12">
						<strong>@lang('lang_v1.advance_balance'):</strong> <span id="advance_balance_text">0</span>
						{!! Form::hidden('advance_balance', null, ['id' => 'advance_balance', 'data-error-msg' => __('lang_v1.required_advance_balance_not_available')]); !!}
					</div>
				</div>
				@include('sale_pos.partials.payment_row_form', ['row_index' => 0, 'show_date' => true])
				<hr>
				<div class="row">
					<div class="col-sm-12">
						<div class="pull-right"><strong>@lang('purchase.payment_due'):</strong> <span id="payment_due">0.00</span></div>
					</div>
				</div>
				<br>
			</div>
			</div>
			<div class="box-body payment_row">
				<div class="row">
					<div class="col-sm-12">
						<button type="button" id="submit_purchase_form" class="btn btn-primary pull-right btn-flat">@lang('messages.save')</button>
						<button class="btn btn-danger pull-right btn-flat"><a href="{{ route('purchase-order.index') }}" style="color: white;">Cancel</a></button>

					</div>
				</div>
			</div>
		@endcomponent
		{!! Form::close() !!}
		@include('purchase_order.partials.supplier_products')
	</section>
@endsection
	<section class="section-div">
	<!-- quick product modal -->
	<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" data-backdrop="static" aria-hidden="true"></div>
	<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
		@include('contact.create', ['quick_add' => true])
	</div>
	</section>
	<!-- /.content -->
	<style>
		div.dataTables_filter input { border: 1px solid black; }
		tr:nth-child(odd) td{
            color: #777;
        }
		tr:nth-child(even) td{
            /*color: #fff;*/
        }
		tr:nth-child(odd) td span{
            color: #777;
        }
		tr:nth-child(even) td span{
            color: #fff;
        }
	</style>

@section('javascript')
	<!-- <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script> -->
	<script src="{{ asset('js/purchase_order.js?rev='. date('ymdhi') .'&v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script type="text/javascript">
		$(document).ready( function(){
      		__page_leave_confirmation('#add_purchase_form');

      		onScan.attachTo(document, {
                suffixKeyCodes: [13], // enter-key expected at the end of a scan
                reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
                onScan: function(sCode, iQty) {
                    $('input#search_product_two').val(sCode);
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

      		$('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });
    	});

		$(document).on('blur', '.purchase_unit_cost_without_discount', function(e) {
			const val = $(this).val();
			const cost = parseInt($(this).parent().find('.currentcost').html());
			const total = ((val-cost)/100)*100;
			const parce = total.toFixed(2) + '% Change <br>';
			$(this).parent().find('.changepercent').html(parce);
		});

		function additionalChargesFun() {
			var total_subtotal = 0;
			var total = 0;
			$('#purchase_entry_table tbody')
	        .find('tr')
	        .each(function() {
	            var quantity = __read_number($(this).find('.purchase_quantity'), true);
	        	total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
	        	var suvtotal = quantity * total_quantity;
				total_subtotal += suvtotal;

	        });
			const addtional_charges = parseFloat($('#additional_charges').val());
			const discount_amt = parseFloat($('#discount_amount').val());
	        if(total_subtotal != 0)
	        {
	        	total = total_subtotal + addtional_charges;

	        	if(discount_amt != 0)
		        {
		        	total =  total - discount_amt;
		        }
				$('#total_subtotal').text(__currency_trans_from_en(total, true, true));
				__write_number($('input#total_subtotal_input'), total, true);
				__write_number($('input#grand_total_hidden'), total, true);
				__write_number($('input#shipping_charges'), addtional_charges, true);
				$('#sh-charges').text(__currency_trans_from_en(addtional_charges, true, true));
	        }
		}

		function additionalChargesRemove()
		{
			var total_subtotal = 0;
			var total = 0;

			$('#purchase_entry_table tbody')
	        .find('tr')
	        .each(function() {
	            var quantity = __read_number($(this).find('.purchase_quantity'), true);
	        	total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
	        	var suvtotal = quantity * total_quantity;
				total_subtotal += suvtotal;
	        });
			const addtional_charges = 0.00;
	        const discount_amt = parseFloat($('#discount_amount').val());
	        if(discount_amt != 0)
	        {
	        	total =  total_subtotal - discount_amt;
	        }else{
	        	total =  total_subtotal;
	        }
	        $('#total_subtotal').text(__currency_trans_from_en(total, true, true));
			__write_number($('input#total_subtotal_input'), total, true);
			__write_number($('input#grand_total_hidden'), total, true);
			__write_number($('input#shipping_charges'), addtional_charges, true);
			__write_number($('input#additional_charges'), addtional_charges, true);
			$('#sh-charges').text(__currency_trans_from_en(addtional_charges, true, true));
		}

		function additionalChargesFun_old() {
		// $(document).on('blur', '#additional_charges', function(e) {
			// const val = parseFloat($(this).val());
			const val = parseFloat($('#additional_charges').val());
			var sub = $("#total_subtotal").html();
			sub = sub.replace(/\$/g, '');
			var total = parseFloat(sub);
			var perc = (val/total)*100;
			var total_subtotal = 0;


			$('#purchase_entry_table tbody')
	        .find('tr')
	        .each(function() {
	            total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
	            var addtional_charges = total_quantity*(perc/100);

	            var quantity = __read_number($(this).find('.purchase_quantity'), true);
		        var purchase_after_tax = quantity * total_quantity;
				var suvtotal = purchase_after_tax + addtional_charges;
				total_subtotal += suvtotal;
				__write_number($(this).find('.inline_discounts'), addtional_charges, true);

				//custom  changes  //25-05-2021
				var purchase_unit_cost_amt =  addtional_charges + total_quantity;
				__write_number($(this).find('.purchase_unit_cost'), purchase_unit_cost_amt, true);

				//Calculate sub totals
		        var sub_total_after_tax = purchase_after_tax + additional_charges;

				// __write_number($(this).find('.purchase_unit_cost'), suvtotal, true);
				//__write_number($(this).find('.row_subtotal_after_tax'), suvtotal, true);
				//$(this).find('.row_subtotal_after_tax').text(suvtotal);
				$(this).find('.row_subtotal_after_tax').text(
					__currency_trans_from_en(suvtotal, false, true)
				);
				__write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);
				// total_st_before_tax += __read_number(
	            //     $(this).find('.row_subtotal_before_tax_hidden'),
	            //     true
	            // );


	        });

			//$('#total_quantity').text(__number_f(total_quantity, false));
			//$('#total_st_before_tax').text(__currency_trans_from_en(total_st_before_tax, true, true));
			//__write_number($('input#st_before_tax_input'), total_st_before_tax, true);

			// $('#total_subtotal').text(__currency_trans_from_en(total_subtotal, true, true));
			// __write_number($('input#total_subtotal_input'), total_subtotal, true);
		// });
		}

		function additionalChargesRemove_old()
		{
			const val = 0.00;
			var sub = $("#total_subtotal").html();
			sub = sub.replace(/\$/g, '');
			var total = parseFloat(sub);
			var perc = (val/total)*100;
			var total_subtotal = 0;

			$('#purchase_entry_table tbody')
	        .find('tr')
	        .each(function() {
	            total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
	            var addtional_charges = total_quantity*(perc/100);

	            var quantity = __read_number($(this).find('.purchase_quantity'), true);
		        var purchase_after_tax = quantity * total_quantity;
				var suvtotal = purchase_after_tax + addtional_charges;
				total_subtotal += suvtotal;
		        __write_number($(this).find('.purchase_unit_cost'), suvtotal, true);
				__write_number($(this).find('.inline_discounts'), addtional_charges, true);

				//Calculate sub totals
		        var sub_total_after_tax = purchase_after_tax + additional_charges;

				$(this).find('.row_subtotal_after_tax').text(
					__currency_trans_from_en(suvtotal, false, true)
				);

				__write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);
				$('#additional_charges').val(0.00);
	        });

	        $('#discount_show').removeClass('hide');
	        $('#discount_show').addClass('show');
		}

		function discountChargesFun() {
		    var total_subtotal = 0;
		    var total = 0;
		    var discount = 0;
		    $('#purchase_entry_table tbody')
	        .find('tr')
	        .each(function() {
	            total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
	            var quantity = __read_number($(this).find('.purchase_quantity'), true);
		        var purchase_after_tax = quantity * total_quantity;
				total_subtotal += purchase_after_tax;
	        });
		    $('#total_subtotal').text(__currency_trans_from_en(total_subtotal, true, true));
		    __write_number($('input#total_subtotal_input'), total_subtotal, true);


		    if(total_subtotal != 0)
		    {
		    	const addtional_charges = parseFloat($('#additional_charges').val());
		    	if(addtional_charges)
		    	{
		    		total_subtotal = total_subtotal + addtional_charges;
		    	}
				var discount = $('#discount_charges').val();
				total = total_subtotal - discount;
		    	$('#total_subtotal').text(__currency_trans_from_en(total, true, true));
		    	__write_number($('input#total_subtotal_input'), total, true);
		    	__write_number($('input#grand_total_hidden'), total, true);
		    }
		    // $('#discount_show').removeClass('hide');
	        // $('#discount_show').addClass('show');

	        $('#total_discount').text(__currency_trans_from_en(discount, true, true));
		    __write_number($('input#total_discount_input'), discount, true);
		    __write_number($('input#discount_amount'), discount, true);

		    // $('#discount_show').removeClass('hide');
	        // $('#discount_show').addClass('show');
		    console.log('total_subtotal:', total_subtotal)
		    console.log('discount:', discount)
		    console.log('total: ', total)

		}

		function discountChargesRemove() {
		    var total_subtotal = 0;
		    var total = 0;
		    var discount = 0;
		    $('#purchase_entry_table tbody')
	        .find('tr')
	        .each(function() {
	            total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
	            var quantity = __read_number($(this).find('.purchase_quantity'), true);
		        var purchase_after_tax = quantity * total_quantity;
				total_subtotal += purchase_after_tax;
	        });

	        const addtional_charges = parseFloat($('#additional_charges').val());
	    	if(addtional_charges)
	    	{
	    		total_subtotal = total_subtotal + addtional_charges;
	    	}

	    	$('#total_subtotal').text(__currency_trans_from_en(total_subtotal, true, true));
		    __write_number($('input#total_subtotal_input'), total_subtotal, true);
		    __write_number($('input#grand_total_hidden'), total_subtotal, true);

		   	$('#total_discount').text(__currency_trans_from_en(discount, true, true));
		    __write_number($('input#total_discount_input'), discount, true);
		    __write_number($('input#discount_amount'), discount, true);

		    $('#discount_charges').val(0.00);
		    console.log('total_subtotal:', total_subtotal)
		    console.log('discount:', discount)
		    console.log('total: ', total)
		}

    	$(document).on('change', '.payment_types_dropdown, #location_id', function(e) {
		    var default_accounts = $('select#location_id').length ?
		                $('select#location_id')
		                .find(':selected')
		                .data('default_payment_accounts') : [];
		    var payment_types_dropdown = $('.payment_types_dropdown');
		    var payment_type = payment_types_dropdown.val();
		    var payment_row = payment_types_dropdown.closest('.payment_row');
	        var row_index = payment_row.find('.payment_row_index').val();

	        var account_dropdown = payment_row.find('select#account_' + row_index);
		    if (payment_type && payment_type != 'advance') {
		        var default_account = default_accounts && default_accounts[payment_type]['account'] ?
		            default_accounts[payment_type]['account'] : '';
		        if (account_dropdown.length && default_accounts) {
		            account_dropdown.val(default_account);
		            account_dropdown.change();
		        }
		    }

		    if (payment_type == 'advance') {
		        if (account_dropdown) {
		            account_dropdown.prop('disabled', true);
		            account_dropdown.closest('.form-group').addClass('hide');
		        }
		    } else {
		        if (account_dropdown) {
		            account_dropdown.prop('disabled', false);
		            account_dropdown.closest('.form-group').removeClass('hide');
		        }
		    }
		});

		$(".modal-dialog").hide();
		$(document).on("click", ".add_new_supplier", function(){
			$(".section-div").show();
			$(".modal-dialog").show();
		});
		$(document).on("click", ".close", function(){
			$(".section-div").hide();
			$(".modal-dialog").hide();
		});

		$('body').on('keydown', 'input, select, textarea', function(e) {
			var self = $(this)
			  , form = self.parents('form:eq(0)')
			  , focusable
			  , next
			  , prev
			  ;

			if (e.shiftKey) {
				if (e.keyCode == 107) {
				    focusable =   form.find('input,a,select,button,textarea').filter(':visible');
				    prev = focusable.eq(focusable.index(this)-1);
				    if (prev.length) {
				        prev.focus();
				   			console.log('first')
				    } else {
				        form.submit();
				    }
				}
			}else{
				if (e.keyCode == 107) {
				    focusable = form.find('input,a,select,button,textarea').filter(':visible');
				    next = focusable.eq(focusable.index(this)+1);
				    if (next.length) {
				        var row_id =  $(this).data("row");
				        var row_input =  $(this).data("input");
				        var i =  row_input+1;
				        if(row_input == 3)
				        {
				        	var j = row_id +1;
				        	var next_row = $('#input_'+j+'_box'+1).val();
				        	if(next_row)
				        	{
				        		document.getElementById('input_'+j+'_box'+1).focus();
				        	}else{
					        	document.getElementById('search_product_two').focus();
				        	}
				        }else{
				        	document.getElementById('input_'+row_id+'_box'+i).focus();
				        }
				    } else {
				        form.submit();
				    }
				    return false;
				}
			}
		});

		$(document).ready(function() {
		  	$(".add").click(function() {
		    	$('<div><br><input class="files" name="documents[]" type="file" accept="application/pdf,text/csv,application/zip,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/jpg,image/png" ><span class="rem" ><a href="javascript:void(0);" title="Clear selected files" class="deleteDoc btn btn-danger btn-xs fileinput-remove fileinput-remove-button"><i class="glyphicon glyphicon-trash"></i>  <span class="hidden-xs">Remove</span></span></div>').appendTo(".contents");
		    });
			$('.contents').on('click', '.rem', function() {
			    $(this).parent("div").remove();
			});
		});

		$(document).ready(function() {
			$(window).keydown(function(event){
			    if(event.keyCode == 13) {
			      event.preventDefault();
			      return false;
			    }
			});
		});

		/*$(document).on('keyup', '#ref_no', function() {
            console.log('tets', 1);
            $.ajax({
                method: 'post',
                url: '/purchase-order/check_ref_number',
                dataType: 'json',
                data: {
                    ref_no: function() {
                        return $('#ref_no').val();
                    },
                    contact_id: function() {
                        return $('#supplier_id').val();
                    },
                    purchase_id: function() {
                        if ($('#purchase_id').length > 0) {
                            return $('#purchase_id').val();
                        } else {
                            return '';
                        }
                    },
                },
                success: function(result) {
                    if (result == true) {
                        console.log('true')
                        $('button#submit_purchase_form').attr('disabled', false);
                        document.getElementById("ref_no-error").hidden = true;
                    } else {
                        console.log('false')
                        document.getElementById("ref_no-error").hidden = false;
                        toastr.error(LANG.ref_no_already_exists);
                    }
                },
            });
        });*/


		$(document).on('click', '.row-select, .select-all-row', function(e) {
		    if($('.row-select:checked').length){
		        $('.add-product-row').prop('disabled', false)
		    } else {
		        $('.add-product-row').prop('disabled', true)
		    }
		})

        $(document).on('click', '.add_all_products, .add_product_low_stock, .add_purchase_products', function(e) {
	        e.preventDefault();
	        $(".modal-dialog").show();
	        $('#supplier_product_modal').modal('show');

	        var url = '/purchase-order/get_supplier_products/'+$('#supplier_id').val()

            if($(this).attr('data-low-stock')) {
            	url = '/purchase-order/get_supplier_products/'+$('#supplier_id').val()+'?alert_stock=true'
            }
            if($(this).attr('data-purchase-product')) {
            	url = '/purchase-order/get_supplier_products/'+$('#supplier_id').val()+'?purchase_product=true'
            }


	        //Purchase table
		    sup_products = $('#supplier_products_table').DataTable({
		    	destroy: true,
		        processing: true,
		        serverSide: true,
		        ajax: {
		            url: url,
		            data: function(d) {
		                 var start = '';
			                var end = '';
			                if ($('#sup_product_filter_date_range').val()) {
			                    start = $('input#sup_product_filter_date_range')
			                        .data('daterangepicker')
			                        .startDate.format('YYYY-MM-DD');
			                    end = $('input#sup_product_filter_date_range')
			                        .data('daterangepicker')
			                        .endDate.format('YYYY-MM-DD');
			                }
			                d.start_date = start;
			                d.end_date = end;

			                d = __datatable_ajax_callback(d);
		            },
		        },
		        aaSorting: [
		            [1, 'desc']
		        ],
		        columnDefs: [{
                    "targets": [0],
                    "orderable": false,
                    "searchable": false
                }],
		        columns: [
		        	{ data: 'checkbox' },
		            { data: 'item_code', name: 'item_code' },
		            { data: 'name', name: 'name' },
		            { data: 'current_stock', name: 'current_stock' },
		            { data: 'total_sold', name: 'total_sold' },
		        ],
		        createdRow: function(row, data, dataIndex) {

		        },
		        initComplete: function(){

				    var p_entry = $('#purchase_entry_table tbody').find('tr');
					var product_id = [];
					for (var i = 0; i < p_entry.length; i++) {
			    		product_id.push($('input[name="purchases['+i+'][product_id]"]').val())
			    	}
					$('.row-select').each(function () {
						if(product_id.includes($(this).val())){
							$(this).prop('checked', true)
						}
					})

					 if($('.row-select:checked').length){
            		        $('.add-product-row').prop('disabled', false)
            		    }
				}
		    });


		    $('#sup_product_filter_date_range').daterangepicker(
		        dateRangeSettings,
		        function (start, end) {
		            $('#sup_product_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));

		             $('#item_table_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));

		             $('#item_table_filter_date_range').data('daterangepicker').setStartDate(start.format(moment_date_format)); //date now
    				$('#item_table_filter_date_range').data('daterangepicker').setEndDate(end.format(moment_date_format));//date now

		           sup_products.ajax.reload();
		        }
		    );
		    $('#sup_product_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
		        $('#sup_product_filter_date_range').val('');
		         $('#item_table_filter_date_range').val('');

		        sup_products.ajax.reload();
		    });

	    });



	    $('.convert_to_vendor_bill').click(function(){
	    	var contact_id = $('#supplier_id').val();
	    	var contact_name = $("#supplier_id  option:selected").text();
	   // 	var ref_no = $('#ref_no').val();
	    	var transaction_date = $('#transaction_date').val();
	    	var p_entry = $('#purchase_entry_table tbody').find('tr');
			var product_id = [];
			var variation_id = [];
			var additional_notes = $('#additional_notes').val();

	    	for (var i = 0; i < p_entry.length; i++) {
	    		product_id.push($('input[name="purchases['+i+'][product_id]"]').val())
	    		variation_id.push($('input[name="purchases['+i+'][variation_id]"]').val())
	    	}

	    	window.location = "{{ route('purchases.create') }}?contact_id="+contact_id+"&contact_name="+contact_name+"&product_id="+product_id.join(",")+"&variation_id="+variation_id.join(",")+"&additional_notes="+additional_notes

	   // 	window.location = "{{ route('purchases.create') }}?contact_id="+contact_id+"&contact_name="+contact_name+"&ref_no="+ref_no+"&product_id="+product_id.join(",")+"&variation_id="+variation_id.join(",")+"&additional_notes="+additional_notes

	    });

	  //   $(document).on('keyup change', '.additional_shipping_charges', function() {
	  //      	if($(this).val() < 0) {
	  //      	 	$(this).val(0)
	  //      	}
		 //    var total_subtotal = 0;
			// var total = 0;
			// $('#purchase_entry_table tbody')
	  //       .find('tr')
	  //       .each(function() {
	  //           var quantity = __read_number($(this).find('.purchase_quantity'), true);
	  //       	total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
	  //       	var suvtotal = quantity * total_quantity;
			// 	total_subtotal += suvtotal;

	  //       });
			// const addtional_charges = $('.additional_shipping_charges').val() ? parseFloat($('.additional_shipping_charges').val()) : 0;
			// const discount_amt = parseFloat($('.discount').val());
	  //       if(total_subtotal != 0)
	  //       {
	  //       	total = total_subtotal + addtional_charges;

	  //       	if(discount_amt != 0)
		 //        {
		 //        	total =  total - discount_amt;
		 //        }
			// 	$('#total_subtotal').text(__currency_trans_from_en(total, true, true));
			// 	__write_number($('input#total_subtotal_input'), total, true);
			// 	__write_number($('input#grand_total_hidden'), total, true);
			// 	// __write_number($('input#shipping_charges'), addtional_charges, true);
	  //       }

	  //   });

	  //   $(document).on('keyup change', '.discount', function() {
	  //   	if($(this).val() < 0) {
	  //      	 	$(this).val(0)
	  //      	}
	  //   	var total_subtotal = 0;
		 //    var total = 0;
		 //    var discount = 0;
		 //    $('#purchase_entry_table tbody')
	  //       .find('tr')
	  //       .each(function() {
	  //           total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
	  //           var quantity = __read_number($(this).find('.purchase_quantity'), true);
		 //        var purchase_after_tax = quantity * total_quantity;
			// 	total_subtotal += purchase_after_tax;
	  //       });
		 //    $('#total_subtotal').text(__currency_trans_from_en(total_subtotal, true, true));
		 //    __write_number($('input#total_subtotal_input'), total_subtotal, true);


		 //    if(total_subtotal != 0)
		 //    {
		 //    	const addtional_charges = parseFloat($('.additional_shipping_charges').val());
		 //    	if(addtional_charges)
		 //    	{
		 //    		total_subtotal = total_subtotal + addtional_charges;
		 //    	}
			// 	var discount = $('.discount').val();
			// 	total = total_subtotal - discount;
		 //    	$('#total_subtotal').text(__currency_trans_from_en(total, true, true));
		 //    	__write_number($('input#total_subtotal_input'), total, true);
		 //    	__write_number($('input#grand_total_hidden'), total, true);
		 //    }
		 //    // $('#discount_show').removeClass('hide');
	  //       // $('#discount_show').addClass('show');

	  //       $('#total_discount').text(__currency_trans_from_en(discount, true, true));
		 //    __write_number($('input#total_discount_input'), discount, true);
		 //    __write_number($('input#discount_amount'), discount, true);

	  //   });

	</script>
	@include('purchase_order.partials.keyboard_shortcuts')
@endsection
