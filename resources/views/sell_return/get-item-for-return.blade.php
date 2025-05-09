@if($sells != null)
            <tr class="product_row" data-row_index="{{$row_count}}">
                <td>
                    {{ $sells->product_name }} - {{ $sells->sku }}
                    <textarea name="products[{{$row_count}}][note]"
                           class="form-control input-sm"></textarea>
                </td>
                <td>
                   @if(isset($last_sell->unit_price_inc_tax) && $last_sell->unit_price_inc_tax)

                   <span class="display_currency" data-currency_symbol="true">
                       <input name="products[{{$row_count}}][unit_price_inc_tax]"
                           type="text" class="unit_price"
                           value="{{@num_format($last_sell->unit_price_inc_tax)}}">
                   </span>
                   <br />
                   <span class="display_currency" data-currency_symbol="true">
                       (Current Price: ${{@num_format($sells->sell_price_inc_tax)}})
                       Diff: {{$sells->sell_price_inc_tax - $last_sell->unit_price_inc_tax}}
                   </span>
                   @else

                   <span class="display_currency" data-currency_symbol="true">
                       <input name="products[{{$row_count}}][unit_price_inc_tax]"
                           type="text" class="form-control input-sm input_number unit_price"
                           value="{{$sells->sell_price_inc_tax}}">
                   </span>

                   <br />
                   @endif
                </td>
                <td>
                    <input type="text" name="products[{{$row_count}}][quantity]"
                           value="1.00"
                           class="form-control input-sm input_number return_qty input_quantity"
                           data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')"
                    >
                    <!--<input name="products[{{$row_count}}][unit_price_inc_tax]"
                           type="hidden" class="unit_price"
                           value="{{@num_format($sells->sell_price_inc_tax)}}">-->
                           <input name="products[{{$row_count}}][unit_price]"
                           type="hidden"
                           value="{{@num_format($sells->sell_price_inc_tax)}}">
                    <input name="products[{{$row_count}}][sell_line_id]" type="hidden"
                           value="{{$sells->product_id}}">
                    <input name="products[{{$row_count}}][product_id]" type="hidden"
                           value="{{$sells->product_id}}">
                    <input name="products[{{$row_count}}][variation_id]" type="hidden"
                           value="{{$sells->variation_id}}" class="row_variation_id">
                </td>
                <td>
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="text" name="products[{{$row_count}}][gar_box_return_qty]" value="0.00"
                                   class="form-control input-sm input_number gar_box_return_qty input_quantity"
                                   data-rule-abs_digit="true"
                                   data-msg-abs_digit="Decimal value not allowed">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="text" name="products[{{$row_count}}][gar_piece_return_qty]" value="0.00"
                                   class="form-control input-sm input_number gar_piece_return_qty garbage_quantity"
                                   data-rule-abs_digit="true"
                                   data-msg-abs_digit="Decimal value not allowed">
                        </div>
                    </div>
                </td>

                <td>
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="text" name="products[{{$row_count}}][gar_piece_return_price]" value="0.00"
                                   class="form-control input-sm input_number gar_piece_return_price"
                                   >
                        </div>
                    </div>
                </td>

                <td>
                    <div class="row">
                        <div class="col-sm-12">
                          <input name="products[{{$row_count}}][state_tax_id]"
                           type="hidden" value="{{ isset($sells->taxDetail['rule']) ? $sells->taxDetail['rule'] : '' }}">

                           <input name="products[{{$row_count}}][state_actual_tax]"
                           type="hidden" class="state_actual_tax" value="{{ isset($sells->taxDetail['tax']) ? @num_format($sells->taxDetail['tax']) : 0 }}">

                          <input name="products[{{$row_count}}][state_tax]"
                           type="text" class="form-control input-sm input_number state_tax"
                           value="{{ isset($sells->taxDetail['tax']) ? @num_format($sells->taxDetail['tax']) : 0 }}" readonly>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-sm-12">

                          <input name="products[{{$row_count}}][city_tax_id]"
                           type="hidden" value="{{ isset($sells->taxDetail['city_tax_id']) ? $sells->taxDetail['city_tax_id'] : '' }}">

                           <input name="products[{{$row_count}}][city_actual_tax]"
                           type="hidden" class="city_actual_tax" value="{{ isset($sells->taxDetail['city_tax']) ? @num_format($sells->taxDetail['city_tax']) : 0 }}">

                          <input name="products[{{$row_count}}][city_tax]"
                           type="text" class="form-control input-sm input_number city_tax" value="{{ isset($sells->taxDetail['city_tax']) ? @num_format($sells->taxDetail['city_tax']) : 0 }}" readonly>

                        </div>
                    </div>
                </td>
                <td>
                    <div class="return_subtotal"></div>
                </td>
                <td class="text-center">
                    <i class="fa fa-times text-danger pos_remove_row cursor-pointer" aria-hidden="true"></i>
                </td>
            </tr>
@endif