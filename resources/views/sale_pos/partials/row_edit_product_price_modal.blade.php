<style>
    .text-size{
        font-size:14px;
    }
</style>
<div class="modal-dialog" role="document">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="myModalLabel">{{$product->product_name}} - {{$product->sub_sku}}</h4>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="form-group col-xs-12 @if(!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif">
					{{--<label>@lang('sale.unit_price')</label>--}}
						<input type="hidden" name="products[{{$row_count}}][unit_price]" class="form-control pos_unit_price input_number mousetrap" value="{{@num_format(!empty($product->unit_price_before_discount) ? $product->unit_price_before_discount : $product->default_sell_price)}}">
				</div>
				<div class="form-group col-xs-12 col-sm-6">
					<h5 class="modal-title">Case qty: {{@format_quantity($product->quantity_ordered)}} , Quantity In Box: {{$product->qty_box}} , Quantity on Hand: <span style="color:red;"> {{(int)$product->qty_available}} </span></h5>
					<h5>Item Code: <span style="color: black;">{{$product->icode}}</span>, Variations options: {{$product->variation_name}}</h5>
				</div>
				@php
					$discount_type = !empty($product->line_discount_type) ? $product->line_discount_type : 'fixed';
					$discount_amount = !empty($product->line_discount_amount) ? $product->line_discount_amount : 0;

					if(!empty($discount)) {
						$discount_type = $discount->discount_type;
						$discount_amount = $discount->discount_amount;
					}
				@endphp

				@if(!empty($discount))
					{!! Form::hidden("products[$row_count][discount_id]", $discount->id); !!}
				@endif
				{{--<div class="form-group col-xs-12 col-sm-6 @if(!$edit_discount) hide @endif">--}}
					{{--<label>@lang('sale.discount_type')</label>--}}
						{{--{!! Form::select("products[$row_count][line_discount_type]", ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')], $discount_type , ['class' => 'form-control row_discount_type']); !!}--}}
					{{--@if(!empty($discount))--}}
						{{--<p class="help-block">{!! __('lang_v1.applied_discount_text', ['discount_name' => $discount->name, 'starts_at' => $discount->formated_starts_at, 'ends_at' => $discount->formated_ends_at]) !!}</p>--}}
					{{--@endif--}}
				{{--</div>--}}
				<div class="form-group col-xs-12 col-sm-6 @if(!$edit_discount) hide @endif">
{{--					<label>@lang('sale.discount_amount')</label>--}}
						{!! Form::hidden("products[$row_count][line_discount_amount]", @num_format($discount_amount), ['class' => 'form-control input_number row_discount_amount']); !!}
				</div>
				<div class="form-group col-xs-12 {{$hide_tax}}">
					<label>@lang('sale.tax')</label>

					{!! Form::hidden("products[$row_count][item_tax]", @num_format($item_tax), ['class' => 'item_tax']); !!}
		
					{!! Form::select("products[$row_count][tax_id]", $tax_dropdown['tax_rates'], $tax_id, ['placeholder' => 'Select', 'class' => 'form-control tax_id'], $tax_dropdown['attributes']); !!}
				</div>
				@php
					$warranty_id = !empty($action) && $action == 'edit' && !empty($product->warranties->first())  ? $product->warranties->first()->id : $product->warranty_id;
				@endphp
				@if(!empty($warranties))
					<div class="form-group col-xs-12">
						<label>@lang('lang_v1.warranty')</label>
						{!! Form::select("products[$row_count][warranty_id]", $warranties, $warranty_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control']); !!}
					</div>
				@endif
			</div>
			<div class="row">
				<div class="form-group col-xs-6">
		      		<label>Note</label>
		      		@php
		      			$sell_line_note = '';
		      			if(!empty($product->sell_line_note)){
		      				$sell_line_note = $product->sell_line_note;
		      			}
		      		@endphp
		      		<textarea class="form-control note-text" name="products[{{$row_count}}][sell_line_note]" rows="3">{{$sell_line_note}}</textarea>
		      		{{--<p class="help-block">@lang('lang_v1.sell_line_description_help')</p>--}}
		      	</div>
		      	
	      		<div class="form-group col-xs-6">
		      		<label>BoX No</label>
		      		@php
		      			$box_no = NULL;
		      			if(!empty($product->box_no)){
		      				$box_no = $product->box_no;
		      			}
		      		@endphp
		      		<input type="text" class="form-control" name="products[{{$row_count}}][box_no]" value="{{$box_no}}">
		      	</div>
	      	</div>
        		<!--<hr>-->
        		<br>
			<div class="stock_history"></div>

		</div>
		<br><br>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
		</div>
	</div>
</div>