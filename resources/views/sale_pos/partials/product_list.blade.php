@forelse($products as $product)
	<div class="col-md-6 col-xs-4 product_list no-print">
		<div class="product_box" data-variation_id="{{$product->id}}" title="{{$product->name}} @if($product->type == 'variable')- {{$product->variation}} @endif {{ '(' . $product->sub_sku . ')'}}">

            @if(!empty($product->product_image))
            <div class="image-container"
                style="background-image: url('/uploads/{{$product->product_image}}');
                background-repeat: no-repeat; background-position: center;
                background-size: contain;">
            </div>
            @else
            <div class="image-container"
                style="background-image: url('/img/default.png');
                background-repeat: no-repeat; background-position: center;
                background-size: contain;">
            </div>
            @endif

		<div class="text_div">
			<small class="text text-muted">{{$product->name}}
			@if($product->type == 'variable')
				- {{$product->variation}}
			@endif
			</small>

			<small class="text-muted">
				({{$product->sub_sku}})
			</small>
		</div>

		</div>
	</div>
@empty
	<input type="hidden" id="no_products_found">
	<div class="col-md-12">
		<h4 class="text-center">
			@lang('lang_v1.no_products_to_display')
		</h4>
	</div>
@endforelse