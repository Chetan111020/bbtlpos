<div class="row">
	<div class="col-md-12">
		<div class="table-responsive">
			<table class="table table-condensed bg-gray">
				<thead>
					<tr class="bg-green">
						<th>@lang('sale.product')</th>
						<th>@lang('product.sku')</th>
						<th>@lang('purchase.purchase_date')</th>
						<th>@lang('lang_v1.purchase')</th>
						<th>@lang('purchase.supplier')</th>
						<th>@lang('lang_v1.purchase_price')</th>
						<th>@lang('lang_v1.sell_date')</th>
						<th>@lang('business.sale')</th>
						<th>@lang('contact.customer')</th>
						<th>@lang('sale.location')</th>
						<th>@lang('lang_v1.quantity')</th>
						<th>@lang('lang_v1.selling_price')</th>
						<th>@lang('sale.subtotal')</th>
		            </tr>
	            </thead>
	            <tbody>
	            	@foreach($product_stock_details as $product)
	            		<tr>
	            			<td>
	            				@php
	            				$name = $product->product_name;
			                    if ($product->product_type == 'variable') {
			                        $name .= ' - ' . $product->product_variation . '-' . $product->variation_name;
			                    }
			                    @endphp
			                    {{$name}}
	            			</td>
							<td>{{$product->sku}}</td>
	            			<td>{{$product->purchase_date}}</td>
	            			<td>
                        		<span>{{$product->purchase_ref_no}}</span>
                        	</td>
							<td>
                        		<span>{{$product->supplier}}</span>
                        	</td>
	            			<td>
                        		<span data-is_quantity="true" class="display_currency"data-currency_symbol=false >{{$product->purchase_price}}</span>
                        	</td>
                        	<td>
                        		<span>{{$product->sell_date}}</span>
                        	</td>
							<td>
                        		<span>{{$product->sell_date}}</span>
                        	</td>
							<td>
                        		<span>{{$product->customer}}</span>
                        	</td>
							<td>
                        		<span>{{$product->location}}</span>
                        	</td>
                        	<td>
                        		<span>{{$product->sale_invoice_no}}</span>
                        	</td>
                        	<td>
                        		<span>{{$product->quantity}}</span>
                        	</td>
                        	<td>
                        		<span data-is_quantity="true" class="display_currency"data-currency_symbol=false >{{$product->selling_price}}</span>
                        	</td>
	            		</tr>
	            	@endforeach
	            </tbody>
	     	</table>
     	</div>
    </div>
</div>