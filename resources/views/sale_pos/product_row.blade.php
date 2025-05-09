<tr class="product_row pos-last-tr" data-row_index="{{$row_count}}">
    @php
        $product_name = $product->product_name . '<br/>' . $product->sub_sku ;
        if(!empty($product->brand)){ $product_name .= ' ' . $product->brand ;}
        if($product->qty_box>0) $new_qty = $product->qty_box;
        else $new_qty = 1;
        $city_state = @$tax['city_state'];
        $tax_amt = 0;
        if(@$tax['is_applicable'] == 1 && (@$tax['rule'] == 55 || @$tax['rule'] == 58 || @$tax['rule'] ==  59 || @$tax['rule'] ==  61 || @$tax['rule'] ==  62)){
                $tax_amt = @$tax['tax'] * $product->quantity_ordered;
        }
        else if(@$tax['is_applicable'] == 1 && $tax['every_item'] > 1) {
            $times_of_apply_tax = (int)($new_qty*$product->quantity_ordered/$tax['every_item']);
            $tax_amt = $times_of_apply_tax * @$tax['tax']  ;
        }
        else if(@$tax['is_applicable'] == 1){
            if(@$tax['tax_type'] == 1)  $tax_amt = @$tax['tax'] * $new_qty * $product->quantity_ordered;
            if(@$tax['tax_type'] == 2) $tax_amt = @$tax['tax'] * $product->quantity_ordered;
        }

        $city_tax_amt = 0;
        if(@$tax['city_tax']!=0){
            if(@$tax['city_every_item'] > 1) {
                $times_of_apply_tax = (int)($new_qty*$product->quantity_ordered/$tax['city_every_item']);
                $city_tax_amt = $times_of_apply_tax * @$tax['city_tax'] ;
            } else{
                if(@$tax['city_tax_type'] == 1)  $city_tax_amt = $new_qty * @$tax['city_tax'] * $product->quantity_ordered;
                if(@$tax['city_tax_type'] == 2)  $city_tax_amt = 1 * @$tax['city_tax'] * $product->quantity_ordered;
            }
        } else if(@$tax['first_item_value'] > 0){
        // add qty code
            if(@$tax['city_tax_type'] == 1) {
                $second_applicable_qty = ($new_qty*$product->quantity_ordered) - $product->quantity_ordered;
                $city_tax_amt = @$tax['first_item_value'] * $product->quantity_ordered + $second_applicable_qty * @$tax['second_item_value'];
            }
            if(@$tax['city_tax_type'] == 2) {
               $second_applicable_qty = 0;
                $city_tax_amt = @$tax['first_item_value'] * $product->quantity_ordered + $second_applicable_qty * @$tax['second_item_value'];
            }
        }

        if (isset($_COOKIE["hideColumn"]))
                $hideColumn = $_COOKIE["hideColumn"];
            else
                $hideColumn = 0;

    @endphp

    <td>
        @if(!empty($product->product_image))
        <a href="/uploads/{{$product->product_image}}" target="_blank">
            <img src="/uploads/{{$product->product_image}}" width="60" height="60">
            {{-- <div class="product_image_placeholder"></div> --}}
        </a>
        @else
        <a href="/img/default.png" target="_blank">
            <img src="/img/default.png" width="60" height="60">
        </a>
        @endif
        <input type="hidden" name="product_id[]" class="productID" value="{{ $product->product_id }}">

        @if (!empty($product->srp) && $product->srp > 0)
            @if (!empty($product->web_sale_price) && $product->web_sale_price > 0)
                <br/>
                <span class="badge bg-green">On Sale</span>
            @endif
        @endif

    </td>
    <!-- <td class="text-center"></td> -->
    <td class="a">
        @php
            $product_name = $product->product_name . '<br/>' . $product->sub_sku ;
            if(!empty($product->brand)){ $product_name .= ' ' . $product->brand ;}
        @endphp

        @if($edit_price || $edit_discount )
            <div title="@lang('lang_v1.pos_edit_product_price_help')">
        <span class="text-link text-info cursor-pointer product-name" data-toggle="modal"
              data-target="#row_edit_product_price_modal_{{$row_count}}" data-variation_id="{{$product->variation_id}}"  data-product_id='{{$product->product_id}}'>
            {!! $product_name !!}
            &nbsp;<i class="fa fa-info-circle"></i>
        </span>
            </div>
        @else
            {!! $product_name !!}
        @endif
        <input type="hidden" class="enable_sr_no" value="{{$product->enable_sr_no}}">
        <input type="hidden"
               class="product_type"
               name="products[{{$row_count}}][product_type]"
               value="{{$product->product_type}}">

        @php
            $hide_tax = 'hide';
            if(session()->get('business.enable_inline_tax') == 1){
                $hide_tax = '';
            }

            $tax_id = $product->tax_id;
            $item_tax = !empty($product->item_tax) ? $product->item_tax : 0;
            $unit_price_inc_tax = $product->sell_price_inc_tax;
            if(!empty($last_sell_price)){
                $unit_price_inc_tax = $last_sell_price;
            }

            if($hide_tax == 'hide'){
                $tax_id = null;
                $unit_price_inc_tax = $product->default_sell_price;
                if(!empty($last_sell_price)){
                    $unit_price_inc_tax = $last_sell_price;
                }
            }
        @endphp

        <div class="modal fade row_edit_product_price_model" id="row_edit_product_price_modal_{{$row_count}}"
             tabindex="-1" role="dialog">
            @include('sale_pos.partials.row_edit_product_price_modal')
        </div>

        <!-- Description modal end -->
        @if(in_array('modifiers' , $enabled_modules))
            <div class="modifiers_html">
                @if(!empty($product->product_ms))
                    @include('restaurant.product_modifier_set.modifier_for_product', array('edit_modifiers' => true, 'row_count' => $loop->index, 'product_ms' => $product->product_ms ) )
                @endif
            </div>
        @endif

        @php
            $max_qty_rule = $product->qty_available;
            $max_qty_msg = __('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit  ]);
        @endphp

        @if( session()->get('business.enable_lot_number') == 1 || session()->get('business.enable_product_expiry') == 1)
            @php
                $lot_enabled = session()->get('business.enable_lot_number');
                $exp_enabled = session()->get('business.enable_product_expiry');
                $lot_no_line_id = '';
                if(!empty($product->lot_no_line_id)){
                    $lot_no_line_id = $product->lot_no_line_id;
                }
            @endphp
            @if(!empty($product->lot_numbers))
                <select class="form-control lot_number input-sm" name="products[{{$row_count}}][lot_no_line_id]"
                        @if(!empty($product->transaction_sell_lines_id)) disabled @endif>
                    <option value="">@lang('lang_v1.lot_n_expiry')</option>
                    @foreach($product->lot_numbers as $lot_number)
                        @php
                            $selected = "";
                            if($lot_number->purchase_line_id == $lot_no_line_id){
                                $selected = "selected";

                                $max_qty_rule = $lot_number->qty_available;
                                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty'=> $lot_number->qty_formated, 'unit' => $product->unit  ]);
                            }

                            $expiry_text = '';
                            if($exp_enabled == 1 && !empty($lot_number->exp_date)){
                                if( \Carbon::now()->gt(\Carbon::createFromFormat('Y-m-d', $lot_number->exp_date)) ){
                                    $expiry_text = '(' . __('report.expired') . ')';
                                }
                            }

                            //preselected lot number if product searched by lot number
                            if(!empty($purchase_line_id) && $purchase_line_id == $lot_number->purchase_line_id) {
                                $selected = "selected";

                                $max_qty_rule = $lot_number->qty_available;
                                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty'=> $lot_number->qty_formated, 'unit' => $product->unit  ]);
                            }
                        @endphp
                        <option value="{{$lot_number->purchase_line_id}}"
                                data-qty_available="{{$lot_number->qty_available}}"
                                data-msg-max="@lang('lang_v1.quantity_error_msg_in_lot', ['qty'=> $lot_number->qty_formated, 'unit' => $product->unit  ])" {{$selected}}>@if(!empty($lot_number->lot_number) && $lot_enabled == 1){{$lot_number->lot_number}} @endif @if($lot_enabled == 1 && $exp_enabled == 1)
                                - @endif @if($exp_enabled == 1 && !empty($lot_number->exp_date)) @lang('product.exp_date')
                            : {{@format_date($lot_number->exp_date)}} @endif {{$expiry_text}}</option>
                    @endforeach
                </select>
            @endif
        @endif

    </td>

    <td class="text-center">
       @php
       $is_access = 0;
       if (auth()->user()->can('edit_product_price_from_pos_screen')){
            $is_access = 1;
       }
        if (isset($_COOKIE["myJavascriptVar"]) && $_COOKIE["myJavascriptVar"] != 0)
            $selected_price_group = $_COOKIE["myJavascriptVar"];
        else if (@$transaction->price_group->id)
            $selected_price_group = @$transaction->price_group->id;
        else if (@$transaction->contact->customer_group_id)
            $selected_price_group = @$transaction->contact->customer_group_id;
        else
            $selected_price_group = 0;
        @endphp

        @if($product->default_sell_price != null)

            @if (!empty($last_sell_price))
                <input type="hidden" name="products[{{$row_count}}][last_sell_price]" value="{{ $last_sell_price }}" />
                <input type="text" name="products[{{$row_count}}][unit_price]"
                    class="form-control pos_unit_price pos_unit_price_val input_number mousetrap" style = "background-color:  #FFFF00 !important;"
                    value="{{@num_format($last_sell_price)}}">
                    
                    <input type="hidden" name="products[{{$row_count}}][pre_loaded_unit_price]"
                    value="{{@num_format($last_sell_price)}}">
            @else
                <input type="text" name="products[{{$row_count}}][unit_price]"
                    class="form-control pos_unit_price pos_unit_price_val input_number mousetrap @if((!$selected_price_group || $selected_price_group == 0 ) || $is_access == 0 ) @endif"
                    value="{{@num_format(!empty($product->unit_price_before_discount) ? $product->unit_price_before_discount : $product->default_sell_price)}}">
                    
                    <input type="hidden" name="products[{{$row_count}}][pre_loaded_unit_price]"
                    value="{{@num_format(!empty($product->unit_price_before_discount) ? $product->unit_price_before_discount : $product->default_sell_price)}}">
            @endif

            <span
                class="display_currency pos_unit_price hide"
                    {{-- @if(($selected_price_group && $selected_price_group != 0 ) && $is_access == 1 ) hide @endif" --}}
                data-currency_symbol="true">
                    {{@num_format(!empty($product->unit_price_before_discount) ? $product->unit_price_before_discount : $product->default_sell_price)}}
            </span>

            {{-- @if(($selected_price_group && $selected_price_group != 0 )|| @$transaction->price_group) --}}
                <span>
                    @if (!empty($product->is_original_sell_price) || @$transaction->price_group)
                        ${{@num_format($product->original_sell_price)}} -
                    @endif
                    ${{ @num_format(!empty($product->unit_price_before_discount) ? $product->unit_price_before_discount : $product->default_sell_price) }}
                </span>

                @if (!empty($product->web_sale_price) && $product->web_sale_price > 0)
                    @if (!empty($product->srp) && $product->srp > 0)
                        <br/>
                        <span class="badge bg-blue"><span style="text-decoration: line-through;">{{ $product->srp }}</span> {{ $product->web_sale_price }}</span>
                    @endif
                @endif
            {{-- @endif --}}
        @endif

         @if($product->purchase_price != null)
            <input type="hidden" name="products[{{$row_count}}][default_purchase_price]" id="purchaseprice_{{$product->product_id}}" value="{{ $product->purchase_price }}">
        @else
            <input type="hidden" name="products[{{$row_count}}][default_purchase_price]" id="purchaseprice_{{$product->product_id}}" value="{{ $product->default_purchase_price }}">
        @endif

    </td>

    <td>
        {{-- If edit then transaction sell lines will be present --}}
        @if(!empty($product->transaction_sell_lines_id) && isset($action) && $action == 'edit')
            <input type="hidden" name="products[{{$row_count}}][transaction_sell_lines_id]" class="form-control"
                   value="{{$product->transaction_sell_lines_id}}">
        @endif

        <input type="hidden" name="products[{{$row_count}}][product_id]" class="form-control product_id"
               value="{{$product->product_id}}">

        <input type="hidden" value="{{$product->variation_id}}"
               name="products[{{$row_count}}][variation_id]" class="row_variation_id">

        <input type="hidden" value="{{$product->enable_stock}}" name="products[{{$row_count}}][enable_stock]">

        <input type="hidden" value="{{$product->qty_box}}" class="qty_box" name="qty_box">
        <input type="hidden" value="{{$product->ml}}" class="product_ml" name="ml">

        @if(empty($product->quantity_ordered))
            @php
                $product->quantity_ordered = 1;
            @endphp
        @endif

        @php
            $multiplier = 1;
            $allow_decimal = true;
            if($product->unit_allow_decimal != 1) {
                $allow_decimal = false;
            }
        @endphp
        @foreach($sub_units as $key => $value)
            @if(!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
                @php
                    $multiplier = $value['multiplier'];
                    $max_qty_rule = $max_qty_rule / $multiplier;
                    $unit_name = $value['name'];
                    $max_qty_msg = __('validation.custom-messages.quantity_not_available', ['qty'=> $max_qty_rule, 'unit' => $unit_name  ]);

                    if(!empty($product->lot_no_line_id)){
                        $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty'=> $max_qty_rule, 'unit' => $unit_name  ]);
                    }

                    if($value['allow_decimal']) {
                        $allow_decimal = true;
                    }
                @endphp
            @endif
        @endforeach
        <div class="input-group input-number">
            {{--<span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-down"><i class="fa fa-minus text-danger"></i></button></span>--}}
            <input @if(isset($edit_qty) && $edit_qty == 1) readonly="readonly" @endif type="text" data-min="1"
                   class="form-control pos_quantity input_number mousetrap input_quantity"
                   value="{{round($product->quantity_ordered)}}" name="products[{{$row_count}}][quantity]"
                   data-allow-overselling="@if(empty($pos_settings['allow_overselling'])){{'false'}}@else{{'true'}}@endif"
                   @if($allow_decimal)
                   data-decimal=1
                   @else
                   data-decimal=0
                   data-rule-abs_digit="true"
                   data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')"
                   @endif
                   data-rule-required="true"
                   data-msg-required="@lang('validation.custom-messages.this_field_is_required')"
                   @if($product->enable_stock && empty($pos_settings['allow_overselling']) )
                   data-rule-max-value="{{$max_qty_rule}}" data-qty_available="{{$product->qty_available}}"
                   data-msg-max-value="{{$max_qty_msg}}"
                   data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit  ])"
                    @endif
            >
            {{--<span class="input-group-btn">--}}
            {{--<button type="button" class="btn btn-default btn-flat quantity-up">--}}
            {{--<i class="fa fa-plus text-success"></i>--}}
            {{--</button>--}}
            {{--</span>--}}
        </div>

        <input type="hidden" name="products[{{$row_count}}][product_unit_id]" value="{{$product->unit_id}}">
        {{--@if(count($sub_units) > 0)--}}
        {{--<br>--}}
        {{--<select name="products[{{$row_count}}][sub_unit_id]" class="form-control input-sm sub_unit">--}}
        {{--@foreach($sub_units as $key => $value)--}}
        {{--<option value="{{$key}}" data-multiplier="{{$value['multiplier']}}" data-unit_name="{{$value['name']}}" data-allow_decimal="{{$value['allow_decimal']}}" @if(!empty($product->sub_unit_id) && $product->sub_unit_id == $key) selected @endif>--}}
        {{--{{$value['name']}}--}}
        {{--</option>--}}
        {{--@endforeach--}}
        {{--</select>--}}
        {{--@else--}}
        {{--{{$product->unit}}--}}
        {{--@endif--}}

        <input type="hidden" class="base_unit_multiplier" name="products[{{$row_count}}][base_unit_multiplier]"
               value="{{$multiplier}}">

        <input type="hidden" class="hidden_base_unit_sell_price" value="{{$product->default_sell_price / $multiplier}}">

        {{-- Hidden fields for combo products --}}
        @if($product->product_type == 'combo'&& !empty($product->combo_products))

            @foreach($product->combo_products as $k => $combo_product)

                @if(isset($action) && $action == 'edit')
                    @php
                        $combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;

                        $qty_total = $combo_product['quantity'];
                    @endphp
                @else
                    @php
                        $qty_total = $combo_product['qty_required'];
                    @endphp
                @endif

                <input type="hidden"
                       name="products[{{$row_count}}][combo][{{$k}}][product_id]"
                       value="{{$combo_product['product_id']}}">

                <input type="hidden"
                       name="products[{{$row_count}}][combo][{{$k}}][variation_id]"
                       value="{{$combo_product['variation_id']}}">

                <input type="hidden"
                       class="combo_product_qty"
                       name="products[{{$row_count}}][combo][{{$k}}][quantity]"
                       data-unit_quantity="{{$combo_product['qty_required']}}"
                       value="{{$qty_total}}">

                @if(isset($action) && $action == 'edit')
                    <input type="hidden"
                           name="products[{{$row_count}}][combo][{{$k}}][transaction_sell_lines_id]"
                           value="{{$combo_product['id']}}">
                @endif

            @endforeach
        @endif
    </td>
    {{--@if(!empty($pos_settings['inline_service_staff']) && !empty($waiters))--}}
    {{--<td>--}}
    {{--<div class="form-group">--}}
    {{--<div class="input-group">--}}
    {{--{!! Form::select("products[" . $row_count . "][res_service_staff_id]", $waiters, !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null, ['class' => 'form-control select2 order_line_service_staff', 'placeholder' => __('restaurant.select_service_staff'), 'required' => (!empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1) ? true : false ]); !!}--}}
    {{--</div>--}}
    {{--</div>--}}
    {{--</td>--}}
    {{--@endif--}}
    <td class="text-center">
    @if(isset($product->display_qty_available) && !empty($product->display_qty_available))
        {{@num_format($product->display_qty_available)}} [{{$product->unit}}]
    @endif
    </td>
    <td class="text-center">
        {{$product->qty_box}}
    </td>
    <td class="text-center">
        {{$product->ml}}
    </td>
    <td class="hide">
        <input type="hidden" name="products[{{$row_count}}][unit_price_inc_tax]"
               class="form-control pos_unit_price_inc_tax" value="{{@num_format($unit_price_inc_tax)}}" />
        <input type="text"
               class="form-control pos_unit_price_inc_tax input_number" value="{{@num_format($unit_price_inc_tax)}}"
               @if(!$edit_price) readonly
               @endif @if(!empty($pos_settings['enable_msp'])) data-rule-min-value="{{$unit_price_inc_tax}}"
               data-msg-min-value="{{__('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($unit_price_inc_tax)])}}" @endif>
    </td>
    {{--<td class="text-center">--}}
    {{--$ 300{{$product->product_description}}--}}
    {{--</td>--}}
    <td class="text-center">
        @php
            $subtotal_type = !empty($pos_settings['is_pos_subtotal_editable']) ? 'text' : 'hidden';
            $subTotal = ($product->quantity_ordered*$unit_price_inc_tax) ;
            $taxval = 0 ;
            $citytaxval = (isset($product->tax_percent) && $product->tax_percent ) ?  ($subTotal * ($product->tax_percent /100))    : (($product->city_tax_value)? $product->city_tax_value*$product->quantity_ordered : 0);
        @endphp
        <input type="{{$subtotal_type}}"
               class="form-control pos_line_total @if(!empty($pos_settings['is_pos_subtotal_editable'])) input_number @endif"
               value="{{@num_format(($product->quantity_ordered*$unit_price_inc_tax)  )}}">
        <span class="display_currency pos_line_total_text @if(!empty($pos_settings['is_pos_subtotal_editable'])) hide @endif"
              data-currency_symbol="true">{{$product->quantity_ordered*$unit_price_inc_tax}}</span>
    </td>
    <td class="text-center">
        <input type="hidden" class="form-control pos_tax_value input_number" value="{{$taxval}}">
        <input type="hidden" class="form-control pos_line_tax" name="products[{{$row_count}}][pos_line_tax]" value="{{@$tax['tax']}}">
        <input type="hidden" class="form-control pos_line_tax_id" name="products[{{$row_count}}][pos_line_tax_id]" value="{{@$tax['rule']}}">
        <input type="hidden" class="form-control pos_line_tax_value" value="{{@$tax['tax_value']}}">
        <input type="hidden" class="form-control pos_line_tax_type" value="{{@$tax['tax_type']}}">
        <input type="number" class="form-control pos_line_tax_amount" {{ auth()->user()->can('pos.custom_tax') ? '' : 'readonly' }} name="products[{{$row_count}}][pos_line_tax_amount]" value="{{$product->pos_line_tax_amount ?? 0}}">
        <input type="hidden" class="form-control pos_line_tax_name" value="{{@$tax['name']}}">
        <input type="hidden" class="form-control pos_line_tax_ml" value="{{@$tax['is_ml']}}">
        <input type="hidden" class="form-control pos_line_tax_state" value="{{@$tax['state']}}">
        <input type="hidden" class="form-control pos_line_tax_per_unit" value="{{@$tax['per_unit']}}">
        <input type="hidden" class="form-control pos_line_tax_every_item" value="{{@$tax['every_item']}}">
        <input type="hidden" class="form-control pos_line_totalamt_value" value="{{($product->quantity_ordered*$unit_price_inc_tax) + ($product->pos_line_tax_amount ?? 0) + $city_tax_amt }}">
        <span class="display_currency state_tax pos_line_tax_text" data-currency_symbol="true"> {{$tax_amt}} </span>
         <!-- <span class="pos_tax_value_text">{{$taxval}}</span> -->
    </td>
    <td class="text-center">
        <input type="hidden" class="form-control city_tax_id input_number" name="products[{{$row_count}}][city_tax_id]" value="{{@$tax['city_tax_id']}}">
        <input type="hidden" class="form-control city_tax_name " value="{{@$tax['city_tax_name']}}">
        <input type="hidden" class="form-control city_tax_type " value="{{@$tax['city_tax_type']}}">
        <input type="hidden" class="form-control city_tax_value_amt input_number" name="products[{{$row_count}}][city_tax_value_amt]" value="{{$city_tax_amt}}">
        <input type="hidden" class="form-control city_every_item_value input_number" value="{{@$tax['city_every_item']}}">
        <input type="hidden" class="form-control city_tax_value input_number" value="{{@$tax['city_tax']}}">
        <input type="hidden" class="form-control first_item_value_value input_number" value="{{@$tax['first_item_value']}}">
        <input type="hidden" class="form-control second_item_value_value input_number" value="{{@$tax['second_item_value']}}">
        <span class="display_currency city_tax pos_line_city_tax_text" data-currency_symbol="true"> {{$city_tax_amt}} </span>
    </td>
    <td class="text-center cost" style="@if($hideColumn==0) display:none; @else display:table-cell; @endif">
        @if(isset($cost) && $cost != null)
            {{$business_details->currency_symbol.''. round($cost, 2)}}<input type="hidden" class="cost_val" value="{{$cost}}" />
        @endif
    </td>
    <td class="text-center gross-price" style="@if($hideColumn==0) display:none; @else display:table-cell; @endif">
        @php
            $profit_percent = 0;
            $sell_price = !empty($product->unit_price_before_discount) ? $product->unit_price_before_discount : $product->default_sell_price;
            if(!empty($last_sell_price)){
                $sell_price = $last_sell_price;
            }
            if(isset($cost) && $cost != null && $sell_price > 0)
             $profit_percent =(1 - ($cost / $sell_price))*100;
        @endphp
        <span class="gross-price-val">{{number_format($profit_percent,2)}}</span>
    </td>
    <td class="text-center">
        {{$product->updated_at}}
    </td>
    <td class="text-center">
        <span class="category-text">{{$product->catName}}</span>
        <input type="hidden" value="{{$product->category_id}}" class="category-id">
         <input type="hidden" value="{{$product->catName}}" class="category-name">
    </td>
    <td class="text-center">
        {{$product->subCatName}}
    </td>

    <td class="text-center">

        <i class="fa fa-times text-danger pos_remove_row cursor-pointer" aria-hidden="true"></i>
    </td>
</tr>