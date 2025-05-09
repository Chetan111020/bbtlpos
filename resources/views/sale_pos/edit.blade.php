@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
<section class="content no-print">
	<input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? ''}}">

    @if (!empty($autosave) && $autosave == 1)
        <input type="hidden" class="with_auto_save" value="1">
    @endif

	@if(!empty($pos_settings['allow_overselling']))
		<input type="hidden" id="is_overselling_allowed">
	@endif
	@if(session('business.enable_rp') == 1)
        <input type="hidden" id="reward_point_enabled">
    @endif
    @php
		$is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
		$is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
	@endphp
	{!! Form::open(['url' => action('SellPosController@update', [$transaction->id]), 'method' => 'post', 'id' => 'edit_pos_sell_form' ]) !!}
	{{ method_field('PUT') }}
	<div class="row mb-12">
		<div class="col-md-12">
			<div class="row">
				<div class="@if(empty($pos_settings['hide_product_suggestion'])) col-md-10 @else col-md-10 col-md-offset-1 @endif no-padding pr-12">
					<div class="box box-solid mb-12">
						<div class="box-body pb-0">
							{!! Form::hidden('location_id', $transaction->location_id, ['id' => 'location_id', 'data-receipt_printer_type' => !empty($location_printer_type) ? $location_printer_type : 'browser', 'data-default_payment_accounts' => $transaction->location->default_payment_accounts]); !!}
							<!-- sub_type -->
							{!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
							<input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">
								@include('sale_pos.partials.pos_form_edit')

								@include('sale_pos.partials.pos_tax')

								@include('sale_pos.partials.pos_form_totals', ['edit' => true])

								@include('sale_pos.partials.payment_modal')

								@if(empty($pos_settings['disable_suspend']))
									@include('sale_pos.partials.suspend_note_modal')
								@endif

								@if(empty($pos_settings['disable_recurring_invoice']))
									@include('sale_pos.partials.recurring_invoice_modal')
								@endif
							</div>
						</div>
					</div>
				@if(empty($pos_settings['hide_product_suggestion'])  && !isMobile())
					<div class="col-md-2 no-padding">
						@include('sale_pos.partials.pos_sidebar')
					</div>
				@endif
			</div>
		</div>
	</div>
	@include('sale_pos.partials.pos_form_actions', ['edit' => true])
	{!! Form::close() !!}
</section>

<!-- This will be printed -->
<section class="invoice print_section" id="receipt_section">
</section>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
{{-- @include('contact.create', ['quick_add' => true]) --}}
</div>
@if(empty($pos_settings['hide_product_suggestion']) && isMobile())
	@include('sale_pos.partials.mobile_product_suggestions')
@endif
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog"
	aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog"
	aria-labelledby="gridSystemModalLabel">
</div>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

@include('sale_pos.partials.configure_search_modal')

@include('sale_pos.partials.recent_transactions_modal')

@include('sale_pos.partials.weighing_scale_modal')

{{-- @stop --}}

@section('javascript')
	<script src="{{ asset('js/pos.js?rev='.date('YmdHi').'&v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
	<script>
		function addToDataTableOne(id, variation_id = null, purchase_line_id = null, weighing_scale_barcode = null, quantity = 1)
		{
		    var customer_id = $("#customer_id").val();

				$.ajax({
					method: 'POST',
					url: '/products/check_catgeory',
					data: {
						customer_id: customer_id ,
						id:id,
					},
					dataType: 'json',
					success: function(result) {
						if(result.success == false){
							toastr.error(result.message);
						}else{
							pos_product_row(id, purchase_line_id);
							if(!$('div').hasClass('toast-error'))
                			{
                				$('div#'+id).closest("tr").css({'background-color': '#808080' , 'color' : 'white'});
                				$('div#'+id).children('a.product-link').css({'color' : 'white'});
                			}
							//$("#open-datatable-one").hide();
						}
					},
    	});
// 			pos_product_row(id, purchase_line_id);
// 			$("#open-datatable-one").hide();
		}
		$(document).on('click', function(e) {
			if ($(e.target).closest("#open-datatable-one").length === 0) {
				$("#open-datatable_wrapper").hide();
				$("#open-datatable-one").hide();
			}
		});
		$(document).on('keydown', function(e, ui) {
		   if (e.keyCode === $.ui.keyCode.ESCAPE) {
		   		$("#open-datatable_wrapper").hide();
				$("#open-datatable-one").hide();
		   		$('input#search_product_pos_one').val('');
		   }
		});
		$(function(){
			var focusedRow = null;
			$('#open-datatable').on('keydown', function(ev){
				//console.log(ev.keyCode);
				if(focusedRow == null) {
					focusedRow = $('tr:nth-child(1)', '#open-datatable').next();
					$('tr:nth-child(1)', '#open-datatable').next().find('.product-link').focus().select();
				} else if(ev.keyCode === 38) {
					//focusedRow.toggleClass('focused');
					focusedRow.prev().find('.product-link').focus().select();
					focusedRow = focusedRow.prev('tr');
				} else if(ev.keyCode === 40) {
					focusedRow.next().find('.product-link').focus().select();
					//focusedRow.toggleClass('focused');
					focusedRow = focusedRow.next();
				}
				focusedRow.toggleClass('focused');
			});
			//$('#open-datatable').focus();
		    $('#search_product').focus();
		});
	</script>
	@include('sale_pos.partials.keyboard_shortcuts')

	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif

    <!-- include module js -->
    @if(!empty($pos_module_data))
	    @foreach($pos_module_data as $key => $value)
            @if(!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
	    @endforeach
	@endif

@endsection

@section('css')
	<style type="text/css">
		/*CSS to print receipts*/
		.print_section{
		    display: none;
		}
		@media print{
		    .print_section{
		        display: block !important;
		    }
		}
		@page {
		    size: 3.1in auto;/* width height */
		    height: auto !important;
		    margin-top: 0mm;
		    margin-bottom: 0mm;
		}
	</style>
	<!-- include module css -->
    @if(!empty($pos_module_data))
        @foreach($pos_module_data as $key => $value)
            @if(!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@endsection