 @if($sells != null)
            <tr>
                <td>
                    {{ $sells->product_name }}
                </td>
                <td>
                   <span class="display_currency" data-currency_symbol="true">
                       {{ round($sells->sell_price_inc_tax, 2) }}
                   </span>
                </td>

                <td>
                    <input type="text" name="products[0][quantity]"
                           value="0.00"
                           class="form-control input-sm input_number return_qty input_quantity"
                           data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')"
                    >
                    <input name="products[0][unit_price_inc_tax]"
                           type="hidden" class="unit_price"
                           value="{{@num_format($sells->sell_price_inc_tax)}}">
                    <input name="products[0][sell_line_id]" type="hidden"
                           value="{{$sells->product_id}}">
                    <input name="products[0][variation_id]" type="hidden"
                           value="{{$sells->variation_id}}">
                </td>
                <td>
                    <div class="row">
                        <div class="col-sm-6">
                            <input type="text" name="products[0][gar_box_return_qty]" value="0.00"
                                   class="form-control input-sm input_number gar_box_return_qty input_quantity"
                                   data-rule-abs_digit="true"
                                   data-msg-abs_digit="Decimal value not allowed">
                        </div>
                        <div class="col-sm-6">
                            <input type="hidden" name="products[0][gar_piece_return_qty]" value="0.00"
                                   class="form-control input-sm input_number gar_piece_return_qty garbage_quantity"
                                   data-rule-abs_digit="true"
                                   data-msg-abs_digit="Decimal value not allowed">
                        </div>
                    </div>
                </td>
               
                <td>
                    <div class="return_subtotal"></div>
                </td>
            </tr>
@endif