@foreach( $variations as $variation)
    <tr>
        <script type="text/javascript">
            $(document).ready( function(){
                document.getElementById('input_'+{{ $row_count }}+'_box1').focus();
            });
        </script>
        <td>
            <span class="sr_number"></span>
        </td>
        <td>
            <div class="text-wrap width-90">
                {{ $product->name }} <br>({{$variation->sub_sku}})
                @if( $product->type == 'variable' )
                    <br/>
                    (<b>{{ $variation->product_variation->name }}</b> : {{ $variation->name }})
                @endif
            </div>             
        </td>
        <td>
             @if($product->enable_stock == 1)
                <small style="white-space: nowrap; font-weight: bold;">
                    {{-- @lang('report.current_stock'):  --}}
                    @if(!empty($variation->variation_location_details->first())) 
                        {{@num_format($variation->variation_location_details->first()->qty_available)}} 
                    @else 
                        0 
                    @endif 
                    {{ $product->unit->short_name }}</small> <br>
                <small style="white-space: nowrap;" class="{{ $row_count }}_show_Hand hide">
                    <span>New Qty On Hand :  <span class="row_on_hand"></span></span>
                </small>
                <input type="hidden" class="row_product_on_hand" id="{{ $row_count }}_product_on_hand" value=@if(!empty($variation->variation_location_details->first())) {{@num_format($variation->variation_location_details->first()->qty_available)}} @else 0 @endif>
            @endif              
            <input type="hidden" class="row_row_on_hand_2" value=0>   
        </td>
        <td>
            {!! Form::hidden('purchases[' . $row_count . '][product_id]', $product->id ); !!}
            {!! Form::hidden('purchases[' . $row_count . '][variation_id]', $variation->id , ['class' => 'hidden_variation_id']); !!}

            @php
                $check_decimal = 'false';
                if($product->unit->allow_decimal == 0){
                    $check_decimal = 'true';
                }
                $currency_precision = config('constants.currency_precision', 2);
                $quantity_precision = config('constants.quantity_precision', 2);
            @endphp
            {!! Form::text('purchases[' . $row_count . '][quantity]', number_format(1, $quantity_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['data-row' => $row_count, 'data-input' => 1, 'class' => 'form-control input-sm purchase_quantity input_number mousetrap', 'required', 'data-rule-abs_digit' => $check_decimal, 'data-msg-abs_digit' => __('lang_v1.decimal_value_not_allowed'), 'id' => 'input_'.$row_count.'_box1']); !!}
            <input type="hidden" class="base_unit_cost" value="{{$variation->default_purchase_price}}">
            <input type="hidden" class="base_unit_selling_price" value="{{$variation->sell_price_inc_tax}}">

            <input type="hidden" name="purchases[{{$row_count}}][product_unit_id]" value="{{$product->unit->id}}">
            {{-- @if(!empty($sub_units))
                <br>
                <select name="purchases[{{$row_count}}][sub_unit_id]" class="form-control input-sm sub_unit" id="input_{{ $row_count}}_box2" data-row="{{ $row_count}}", data-input="2">
                    @foreach($sub_units as $key => $value)
                        <option value="{{$key}}" data-multiplier="{{$value['multiplier']}}">
                            {{$value['name']}}
                        </option>
                    @endforeach
                </select>
            @else 
                {{ $product->unit->short_name }}
            @endif --}}
        </td>

        <td>
            {!! Form::text('purchases[' . $row_count . '][pp_without_discount]',
            number_format($variation->dpp_inc_tax, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['data-row' => $row_count, 'data-input' => 3, 'class' => 'form-control input-sm purchase_unit_cost_without_discount input_number', 'required', 'id' => 'input_'.$row_count.'_box2']); !!}
            <span  class="changepercent"></span>
            <span class="currentcost" style="visibility: hidden;">{{ number_format($variation->dpp_inc_tax, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}</span></span>
        </td>
        <td>
            {!! Form::text('prev_cost',  number_format($variation->dpp_inc_tax, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm purchase_unit_cost_without_discount input_number', 'readonly']); 
                !!}
            {{-- <span>Current Cost: $<span class="currentcost">{{ number_format($variation->dpp_inc_tax, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}</span></span>
            <br/>
            <span> <input type="checkbox" @if($edit) @if($edit == 1) disabled="" @endif @else @endif name="purchases[{{ $row_count }}][update_cost]"> Update Cost </span>
            <br/> --}}
        </td>

        <td class="{{$hide_tax}}">
            <!-- <span class="purchase_unit_cost" style="color: #777;">{{number_format($variation->dpp_inc_tax, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}</span> -->
            {!! Form::hidden('purchases[' . $row_count . '][purchase_price]',
            number_format($variation->default_purchase_price, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm purchase_unit_cost input_number']); !!}
            <span class="row_subtotal_before_tax display_currency">0</span>
            <input type="hidden" class="row_subtotal_before_tax_hidden" value=0>
        </td>
        <td class="{{$hide_tax}}">
            <div class="input-group">
                <select name="purchases[{{ $row_count }}][purchase_line_tax_id]" class="form-control select2 input-sm purchase_line_tax_id" placeholder="'Please Select'">
                    <option value="" data-tax_amount="0" @if( $hide_tax == 'hide' )
                    selected @endif >@lang('lang_v1.none')</option>
                    @foreach($taxes as $tax)
                        <option value="{{ $tax->id }}" data-tax_amount="{{ $tax->amount }}" @if( $product->tax == $tax->id && $hide_tax != 'hide') selected @endif >{{ $tax->name }}</option>
                    @endforeach
                </select>
                {!! Form::hidden('purchases[' . $row_count . '][item_tax]', 0, ['class' => 'purchase_product_unit_tax']); !!}
                <span class="input-group-addon purchase_product_unit_tax_text">
                    0.00</span>
            </div>
        </td>
        <td class="{{$hide_tax}}">
            @php
                $dpp_inc_tax = number_format($variation->dpp_inc_tax, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator);
                if($hide_tax == 'hide'){
                    $dpp_inc_tax = number_format($variation->default_purchase_price, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator);
                }

            @endphp
            {!! Form::text('purchases[' . $row_count . '][purchase_price_inc_tax]', $dpp_inc_tax, ['class' => 'form-control input-sm purchase_unit_cost_after_tax input_number', 'required']); !!}
        </td>
        <td>
            <span class="row_subtotal_after_tax display_currency">0</span>
            <input type="hidden" class="row_subtotal_after_tax_hidden" value=0>
        </td>
        <td>
            <span class="display_currency sold{{$product->id}}">
                {{ !empty($variation->total_sold) ? $variation->total_sold : 0 }}
            </span>
        </td>
        {{-- <td class="@if(!session('business.enable_editing_product_from_purchase')) hide @endif">
            {!! Form::text('purchases[' . $row_count . '][profit_percent]', number_format($variation->profit_percent, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['data-row' => $row_count, 'data-input' => 4, 'class' => 'form-control input-sm input_number profit_percent', 'required', 'id' => 'input_'.$row_count.'_box4']); !!}
        </td>
        <td>
            @if(session('business.enable_editing_product_from_purchase'))
                {!! Form::text('purchases[' . $row_count . '][default_sell_price]', number_format($variation->sell_price_inc_tax, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['data-row' => $row_count, 'data-input' => 5, 'class' => 'form-control input-sm input_number default_sell_price', 'required', 'id' => 'input_'.$row_count.'_box5']); !!}
            @else
                {{ number_format($variation->sell_price_inc_tax, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}
            @endif
        </td>
        @if(session('business.enable_lot_number'))
            <td>
                {!! Form::text('purchases[' . $row_count . '][lot_number]', null, ['class' => 'form-control input-sm']); !!}
            </td>
        @endif
        @if(session('business.enable_product_expiry'))
            <td style="text-align: left;">

                @php
                    $expiry_period_type = !empty($product->expiry_period_type) ? $product->expiry_period_type : 'month';
                @endphp
                @if(!empty($expiry_period_type))
                <input type="hidden" class="row_product_expiry" value="{{ $product->expiry_period }}">
                <input type="hidden" class="row_product_expiry_type" value="{{ $expiry_period_type }}">

                @if(session('business.expiry_type') == 'add_manufacturing')
                    @php
                        $hide_mfg = false;
                    @endphp
                @else
                    @php
                        $hide_mfg = true;
                    @endphp
                @endif

                <b class="@if($hide_mfg) hide @endif"><small>@lang('product.mfg_date'):</small></b>
                <div class="input-group @if($hide_mfg) hide @endif">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    {!! Form::text('purchases[' . $row_count . '][mfg_date]', null, ['class' => 'form-control input-sm expiry_datepicker mfg_date', 'readonly']); !!}
                </div>
                <b><small>@lang('product.exp_date'):</small></b>
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    {!! Form::text('purchases[' . $row_count . '][exp_date]', null, ['class' => 'form-control input-sm expiry_datepicker exp_date', 'readonly']); !!}
                </div>
                @else
                <div class="text-center">
                    @lang('product.not_applicable')
                </div>
                @endif
            </td>
        @endif --}}
        <?php $row_count++ ;?>

        <td><i class="fa fa-times remove_purchase_entry_row text-danger" title="Remove" style="cursor:pointer;"></i></td>
    </tr>
@endforeach

<input type="hidden" id="row_count" value="{{ $row_count }}">