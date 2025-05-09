@php
    $inv_return = $product->inv_return ?? 1;
    // if(empty($inv_return))
    // {
    //     $inv_return = 1;
    // }
    $loose_qty = $product->loose_qty ?? 0;
    $loose_price = $product->loose_price ?? 0;
    $box_price = round($product->default_purchase_price ?? 0, 2);
    if(isset($edit) && $edit){
        $box_price = isset($product->purchase_price_inc_tax) ? $product->purchase_price_inc_tax  : $product->box_price ;
    }
    $sub_total = $product->sub_total ?? $box_price;
    $deducted = $product->is_deducted ?? 0;
@endphp
<tr class="product_row">
    <td>
        {{ $product->product_name }}<br/>{{ $product->sub_sku }}
    </td>
    <td>
        {{ $product->unit }}
    </td>
    <td>
        <div class="page__toggle" style="display:flex;justify-content:center;">
            <label class="toggle">
              <input type="checkbox" id="chk_{{$row_index}}" name="products[{{$row_index}}][is_deducted]" class="toggle__input" {{ ($deducted == 0) ? 'checked':'' }}/>
              <span class="toggle__label">
                <span class="toggle__text"></span>
              </span>
            </label>
        </div>
    </td>
    <td>
        <input type="text" name="products[{{$row_index}}][inv_return]" class="form-control inv_return input_number" value="{{ $inv_return }}"/>
    </td>
    <td>
        <input type="text" name="products[{{$row_index}}][box_price]" class="form-control box_price input_number" value="{{ number_format($box_price,2) }}"/>
    </td>
    <td>
        <input type="text" name="products[{{$row_index}}][loose_qty]" class="form-control loose_qty input_number" value="{{ $loose_qty }}"/>
    </td>
    <td>
        <input type="text" name="products[{{$row_index}}][loose_price]" class="form-control loose_price input_number" value="{{ $loose_price }}"/>
    </td>
    <td>
        <input type="text" name="products[{{$row_index}}][sub_total_display]" class="form-control input_number sub_total_display" value="{{ $sub_total }}" readonly/>
        <input type="hidden" name="products[{{$row_index}}][sub_total]" class="sub_total" value="{{ $sub_total }}" />
    </td>
    <td class="text-center">
        <i class="fa fa-trash remove_product_row cursor-pointer" aria-hidden="true"></i>
        <input type="hidden" name="products[{{$row_index}}][product_id]" class="form-control product_id" value="{{$product->product_id}}"/>

        <input type="hidden" value="{{$product->variation_id}}"
            name="products[{{$row_index}}][variation_id]" class="row_variation_id"/>

        <input type="hidden" value="{{$product->enable_stock}}"
            name="products[{{$row_index}}][enable_stock]"/>

        @if(!empty($edit))
            <input type="hidden" value="{{$product->purchase_line_id}}"
            name="products[{{$row_index}}][purchase_line_id]"/>
        @endif
    </td>
</tr>