<table align="center" style="border-spacing: {{$barcode_details->col_distance * 1}}in {{$barcode_details->row_distance * 1}}in; overflow: hidden !important;">
@foreach($page_products as $page_product)

	@if($loop->index % $barcode_details->stickers_in_one_row == 0)
		<!-- create a new row -->
		<tr>
		<!-- <columns column-count="{{$barcode_details->stickers_in_one_row}}" column-gap="{{$barcode_details->col_distance*1}}"> -->
	@endif
		<td align="center" valign="center">
			<div style="overflow: hidden !important; flex-wrap: wrap;width: {{$barcode_details->width * 1}}in; height: {{$barcode_details->height * 1}}in;">
				<div>
					{{-- Business Name --}}
					<!-- @if(!empty($print['business_name']))
						<b style="display: block !important; font-size: {{9*$factor}}px">{{$business_name}}</b>
					@endif -->
					<table  style="width: 100%;">
						<tr>
							<td style="width: 40px;">
								<span style="font-family: arial;text-align: center;width: 50px;padding: 6px;background-color: #000;display: block !important; font-size: {{9*$factor}}px">
								<span style="color:#FFFFFF;font-weight: 600;">A-{{$page_product->A}}</span>
								</span>
							</td>
							<td style="width: 40px;">
								<span style="font-family: arial;text-align: center;width: 50px;padding: 6px;background-color: #000;display: block !important; font-size: {{9*$factor}}px">
								<span style="color:#FFFFFF;font-weight: 600;">R-{{$page_product->R}}</span>
								</span>
							</td>
							<td style="width: 40px;">
								<span style="font-family: arial;text-align: center;width: 50px;padding: 6px;background-color: #000;display: block !important; font-size: {{9*$factor}}px">
								<span style="color:#FFFFFF;font-weight: 600;">S-{{$page_product->S}}</span>
								</span>
							</td>
							<td style="width: 40px;">
								<span style="font-family: arial;text-align: center;width: 50px;padding: 6px;background-color: #000;display: block !important; font-size: {{9*$factor}}px">
								<span style="color:#FFFFFF;font-weight: 600;">B-{{$page_product->B}}</span>
								</span>
							</td>
						<td rowspan="2">
								<!-- <img style="margin-left: 2px; width: 99px;" src="{{ asset( 'uploads/business_logos/' . Session::get('business.logo') ) }}" alt="Logo"> -->
								<img style="margin-left: 10px; width:60% !important;
								height: {{$barcode_details->height*0.48}}in !important;" src="data:image/png;base64,{{DNS2D::getBarcodePNG($page_product->sub_sku,'QRCODE')}}">
								<br>
							<span style="font-family: arial;font-weight: 600;font-size: {{5.5*$factor}}px">{{$page_product->b_code}}</span>
						</tr>
						<tr>
							<td colspan="4" style="width:100%;">
								@if(!empty($print['name']))
								<span style="font-family: arial;font-weight: 600;font-size: {{7*$factor}}px">
									{{$page_product->product_actual_name}}
									@if(!empty($print['price']))
									{{session('currency')['symbol'] ?? ''}}
									@if($print['price_type'] == 'inclusive')
									{{@num_format($page_product->sell_price_inc_tax)}}
									@else
									{{@num_format($page_product->default_sell_price)}}
									@endif
									@endif
								</span>
								@endif
								<br>
								<span style="font-family: arial;font-weight: 600;font-size: {{7*$factor}}px">[{{$page_product->icode}}]</span>
							</td>
								<td></td>
						</tr>
					</table>
					<!-- {{-- Variation --}}
					@if(!empty($print['variations']) && $page_product->is_dummy != 1)
						<span style="display: block !important; font-size: {{8*$factor}}px">
							<b>{{$page_product->product_variation_name}}</b>:{{$page_product->variation_name}}
						</span>
					@endif -->
				</div>
			</div>
		</td>
	@if($loop->iteration % $barcode_details->stickers_in_one_row == 0)
		</tr>
	@endif
@endforeach
</table>
<style type="text/css">
	@media print{
		table{
			page-break-after: always;
		}
		@page {
		size: {{$paper_width}}in {{$paper_height}}in;
		/*width: {{$barcode_details->paper_width}}in !important;*/
		/*height:@if($barcode_details->paper_height != 0){{$barcode_details->paper_height}}in !important @else auto @endif;*/
		margin-top: {{$margin_top}}in !important;
		margin-bottom: {{$margin_top}}in !important;
		margin-left: {{$margin_left}}in !important;
		margin-right: {{$margin_left}}in !important;
	}
	}
</style>