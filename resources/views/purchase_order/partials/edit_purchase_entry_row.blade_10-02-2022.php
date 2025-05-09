@php
    $hide_tax = '';
    if( session()->get('business.enable_inline_tax') == 0){
        $hide_tax = 'hide';
    }
    $currency_precision = config('constants.currency_precision', 2);
    $quantity_precision = config('constants.quantity_precision', 2);
@endphp

    <div class="card-body table-responsive p-0" id="entry_table_row">
        <input type="hidden" id="div_height" value="0">
        <table style="background:#808080; color:#fff" class="table table-condensed table-bordered text-center table-striped table-head-fixed text-nowrap" id="purchase_entry_table">

        <thead>
                <tr>
                    <th>#</th>
                    <th>@lang( 'product.product_name' )</th>
                    <th>@lang( 'On Hand' )</th>
                    <th>@lang( 'purchase.purchase_quantity' )</th>
                    <th>@lang( 'Cost' )</th>
                    <th>@lang( 'Previous Cost' )</th>
                    <th>@lang( 'Total' )</th>
                    <th>@lang( 'Total Sold' )</th>
                    <th><i class="fa fa-trash" aria-hidden="true"></i></th>
                </tr>
        </thead>
        <tbody>
    <?php $row_count = 0; ?>

    
    @foreach($purchase->po_lines as $purchase_line)
   {{-- {{$purchase_line}} --}}
        <tr>
            <td><span class="sr_number"></span></td>
            <td>
                <div class="text-wrap width-90">
                    {{ $purchase_line->product->name }} ({{$purchase_line->variations->sub_sku}})
                    @if( $purchase_line->product->type == 'variable' )
                        <br/>
                        (<b>{{ $purchase_line->variations->product_variation->name}}</b> : {{ $purchase_line->variations->name}})
                    @endif   
                </div>
            </td>
            <td>
                @if($purchase_line->product->enable_stock == 1)
                    <small style="white-space: nowrap; font-weight: bold;">
                        {{-- @lang('report.current_stock'):  --}}
                        @if(!empty($purchase_line->variations->variation_location_details->first())) 
                         {{@num_format($purchase_line->variations->variation_location_details->first()->qty_available)}} 
                        @else 
                          0 
                        @endif 
                        {{  $purchase_line->product->unit->short_name }}
                    </small>
                    <br>
                    <small style="white-space: nowrap;" class="{{ $row_count }}_show_Hand hide">
                        <span>
                            New Qty On Hand :  <span class="row_on_hand"></span>
                        </span>
                    </small>
                    <input type="hidden" class="row_product_on_hand" id="{{ $row_count }}_product_on_hand" value=@if(!empty($purchase_line->variations->variation_location_details->first()))
                    {{@num_format($purchase_line->variations->variation_location_details->first()->qty_available)}} @else 0 @endif>
                @endif              
                <input type="hidden" class="row_row_on_hand_2" value=0>
            </td>
            <td>
                {!! Form::hidden('purchases[' . $loop->index . '][product_id]', $purchase_line->product_id ); !!}
                {!! Form::hidden('purchases[' . $loop->index . '][variation_id]', $purchase_line->variation_id ); !!}
                {!! Form::hidden('purchases[' . $loop->index . '][purchase_line_id]',$purchase_line->id); !!}
        
                    @php
                        $check_decimal = 'false';
                        if($purchase_line->product->unit->allow_decimal == 0){
                            $check_decimal = 'true';
                        }
                        $currency_precision = config('constants.currency_precision', 2);
                        $quantity_precision = config('constants.quantity_precision', 2);
                    @endphp
                    {!! Form::text('purchases[' . $row_count . '][quantity]', number_format($purchase_line->quantity, $quantity_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['data-row' => $row_count,  'class' => 'form-control input-sm purchase_quantity input_number mousetrap', 'required', 'data-rule-abs_digit' => $check_decimal, 'data-msg-abs_digit' => __('lang_v1.decimal_value_not_allowed')]); !!}
                    <input type="hidden" class="base_unit_cost" value="{{$purchase_line->variations->default_purchase_price}}">
        
                    {{-- <input type="hidden" name="purchases[{{$row_count}}][product_unit_id]" value="{{$product->unit->id}}"> --}}
                    
                    {{-- @if(!empty($purchase_line->sub_unit))
                        <br>
                        <select name="purchases[{{$loop->index}}][sub_unit_id]" class="form-control input-sm sub_unit">
                            @foreach($purchase_line->sub_unit as $sub_units_key => $sub_units_value)
                                <option value="{{$sub_units_key}}" 
                                    data-multiplier="{{$sub_units_value['multiplier']}}"
                                    @if($sub_units_key == $purchase_line->sub_unit_id) selected @endif>
                                    {{$sub_units_value['name']}}
                                </option>
                            @endforeach
                        </select>
                    @else
                        {{ $purchase_line->product->unit->short_name }}
                    @endif --}}
            </td>

            <td>
                {!! Form::text('purchases[' . $loop->index . '][pp_without_discount]', number_format($purchase_line->pp_without_discount/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm purchase_unit_cost_without_discount input_number', 'required']); 
                !!}
                <span class="changepercent"></span>
                <span class="currentcost" style="visibility: hidden;">{{ number_format($purchase_line->pp_without_discount/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}</span></span>
                
            </td>
            <td>
                {!! Form::text('prev_cost', number_format($purchase_line->pp_without_discount/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm purchase_unit_cost_without_discount input_number', 'readonly']); 
                !!}
               
                {{-- <span>
                    $<span class="currentcost">
                    {{ number_format($purchase_line->pp_without_discount/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}
                    </span>
                </span> 
                <br/>
                <span> 
                    <input type="checkbox" name="purchases[{{$loop->index}}][update_cost]" @if($purchase_line->update_cost) checked @else name="purchases[{{ $loop->index }}][update_cost]" @endif> Update Cost {{ $purchase_line->update_cost }}</span>
                <br> --}}
            </td>
        
                
             <td class="{{$hide_tax}}">
                 {!! Form::hidden('purchases[' . $loop->index . '][purchase_price]', 
                number_format($purchase_line->pp_without_discount/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm purchase_unit_cost input_number', 'required']); !!}
                <span class="row_subtotal_before_tax">
                    {{number_format($purchase_line->quantity * $purchase_line->purchase_price/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}
                </span>
                <input type="hidden" class="row_subtotal_before_tax_hidden" value="{{number_format($purchase_line->quantity * $purchase_line->purchase_price/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}">
            </td> 

           <td class="{{$hide_tax}}">
                <div class="input-group">
                    <select name="purchases[{{ $loop->index }}][purchase_line_tax_id]" class="form-control input-sm purchase_line_tax_id" placeholder="'Please Select'">
                        <option value="" data-tax_amount="0" @if( empty( $purchase_line->tax_id ) )
                        selected @endif >@lang('lang_v1.none')</option>
                        @foreach($taxes as $tax)
                            <option value="{{ $tax->id }}" data-tax_amount="{{ $tax->amount }}" @if( $purchase_line->tax_id == $tax->id) selected @endif >{{ $tax->name }}</option>
                        @endforeach
                    </select>
                     {!! Form::hidden('tax_amount', $purchase->tax_amount, ['id' => 'tax_amount']); !!}
                    <span class="input-group-addon purchase_product_unit_tax_text">
                        {{number_format($purchase_line->item_tax/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}
                    </span>
                    {!! Form::hidden('purchases[' . $loop->index . '][item_tax]', number_format($purchase_line->item_tax/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'purchase_product_unit_tax']); !!}
                </div>
            </td>
              <td class="{{$hide_tax}}">
                {!! Form::text('purchases[' . $loop->index . '][purchase_price_inc_tax]', number_format($purchase_line->purchase_price_inc_tax/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm purchase_unit_cost_after_tax input_number', 'required']); !!}
            </td>
             <td>
                <span class="row_subtotal_after_tax">
                {{number_format($purchase_line->purchase_price_inc_tax * $purchase_line->quantity/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}
                </span>
                <input type="hidden" class="row_subtotal_after_tax_hidden" value="{{number_format($purchase_line->purchase_price_inc_tax * $purchase_line->quantity/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}">
            </td>

            <td>
                <span class="display_currency sold{{$purchase_line->product_id}}">
                    {{  !empty($purchase_line['total_sold']) ? $purchase_line['total_sold'] : 0 }}
                </span>
            </td>

             {{--<td class="@if(!session('business.enable_editing_product_from_purchase')) hide @endif">
                @php
                    $pp = $purchase_line->purchase_price_inc_tax;
                    $sp = $purchase_line->default_sell_price ?? $purchase_line->variations->default_sell_price;
                    $profit_percent = 0;
                    if(!empty($purchase_line->sub_unit->base_unit_multiplier)) {
                        $sp = $sp * $purchase_line->sub_unit->base_unit_multiplier;
                    }
                    if($pp == 0){
                        $profit_percent = 100;
                    } else {
                        //$profit_percent = (($sp - $pp) * 100 / $pp);
                        if($sp != 0)
                        $profit_percent =  (1 - ($pp / $sp))*100;  //(($sp - $pp) * 100 / $pp);
                    }
                @endphp
                
                {!! Form::text('purchases[' . $loop->index . '][profit_percent]', 
                number_format($profit_percent, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), 
                ['class' => 'form-control input-sm input_number profit_percent', 'required']); !!}
            </td>

            <td>
                @if(session('business.enable_editing_product_from_purchase'))
                    {!! Form::text('purchases[' . $loop->index . '][default_sell_price]', number_format($sp, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm input_number default_sell_price', 'required']); !!}
                @else
                    {{number_format($sp, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}
                @endif

            </td>
            @if(session('business.enable_lot_number'))
                <td>
                    {!! Form::text('purchases[' . $loop->index . '][lot_number]', $purchase_line->lot_number, ['class' => 'form-control input-sm']); !!}
                </td>
            @endif

            @if(session('business.enable_product_expiry'))
                <td style="text-align: left;">
                    @php
                        $expiry_period_type = !empty($purchase_line->product->expiry_period_type) ? $purchase_line->product->expiry_period_type : 'month';
                    @endphp
                    @if(!empty($expiry_period_type))
                    <input type="hidden" class="row_product_expiry" value="{{ $purchase_line->product->expiry_period }}">
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
                    @php
                        $mfg_date = null;
                        $exp_date = null;
                        if(!empty($purchase_line->mfg_date)){
                            $mfg_date = $purchase_line->mfg_date;
                        }
                        if(!empty($purchase_line->exp_date)){
                            $exp_date = $purchase_line->exp_date;
                        }
                    @endphp
                    <div class="input-group @if($hide_mfg) hide @endif">
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        {!! Form::text('purchases[' . $loop->index . '][mfg_date]', !empty($mfg_date) ? @format_date($mfg_date) : null, ['class' => 'form-control input-sm expiry_datepicker mfg_date', 'readonly']); !!}
                    </div>
                    <b><small>@lang('product.exp_date'):</small></b>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        {!! Form::text('purchases[' . $loop->index . '][exp_date]', !empty($exp_date) ? @format_date($exp_date) : null, ['class' => 'form-control input-sm expiry_datepicker exp_date', 'readonly']); !!}
                    </div>
                    @else
                    <div class="text-center">
                        @lang('product.not_applicable')
                    </div>
                    @endif
                </td>
            @endif  --}}

            <td><i class="fa fa-times remove_purchase_entry_row text-danger" title="Remove" style="cursor:pointer;"></i></td>
        </tr>
        <?php $row_count = $loop->index + 1 ; ?>
    @endforeach
        </tbody>
    </table>
</div>
<input type="hidden" id="row_count" value="{{ $row_count }}">