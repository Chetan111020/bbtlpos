@extends('layouts.app')
@section('title', __('lang_v1.sell_return'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('Credit Memos')</h1>
</section>
@php
    $totalTaxAmount = 0;
@endphp
<!-- Main content -->
<section class="content no-print">

{!! Form::hidden('location_id', $sell->location->id, ['id' => 'location_id', 'data-receipt_printer_type' => $sell->location->receipt_printer_type ]); !!}

	{!! Form::open(['url' => action('SellReturnController@store'), 'method' => 'post', 'id' => 'sell_return_form' ]) !!}
	{!! Form::hidden('transaction_id', $sell->id); !!}
	<div class="box box-solid">
		<div class="box-body">
			<div class="row">
				<div class="col-sm-4">
					<strong>@lang('Credit Memo No'):</strong> {{ $sell->invoice_no }} <br>
					<strong>@lang('messages.date'):</strong> {{@format_date($sell->transaction_date)}} <br>
          <strong>@lang('purchase.business_location'):</strong> {{ $sell->location->name }}
				</div>
				<div class="col-sm-4">

          <!-- choose customer -->
          <div class="form-group">
              <label for="customer_name">@lang('contact.customer'):</label>
              {!! Form::select('customer_id',$customers,$sell->contact_id, ['id' =>'getCustomer','class' => 'form-control select2','placeholder' => 'Please Select']); !!}
              {{--<input class="form-control" name="customer_name" type="text" id="customer_name">--}}
          </div>
					<!-- <strong>@lang('contact.customer'):</strong> {{ $sell->contact->name }} <br> -->

				</div>
                <div class="col-md-2">
                    <div class="form-group">
                        <br>
                        <label>
                            {!! Form::checkbox('nfs_items', 1, false,
                            [ 'class' => 'input-icheck', 'id' => 'nfs_items']); !!} Search Inactive
                        </label>
                    </div>
                </div>
			</div>
		</div>
	</div>
	<input type="hidden" name="product_row_delete" id="product_row_delete" value="">
	<input type="hidden" id="product_row_count" value="{{count($sell->sell_lines) - 1}}">
	<div class="box box-solid">
		<div class="box-body">
			<div class="row">
				<div style="display: none;">
					<div class="col-sm-4">
						<div class="form-group">
							<label for="customer_name">Customer Name.:</label>
							{!! Form::select('customer_name',$customers,$sell->contact_id, ['id' =>'getCustomer','class' => 'form-control select2','placeholder' => 'Please Select']); !!}
							{{--<input class="form-control" name="customer_name" type="text" id="customer_name">--}}
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							{!! Form::label('invoice_no', __('sale.invoice_no').':') !!}
							{!! Form::text('invoice_no', !empty($sell->return_parent->invoice_no) ? $sell->return_parent->invoice_no : null, ['class' => 'form-control']); !!}
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							{!! Form::label('transaction_date', __('messages.date') . ':*') !!}
							<div class="input-group">
								<span class="input-group-addon">
									<i class="fa fa-calendar"></i>
								</span>
								@php
									$transaction_date = !empty($sell->transaction_date) ? $sell->transaction_date : 'now';
								@endphp
								{!! Form::text('transaction_date', @format_datetime($transaction_date), ['class' => 'form-control', 'readonly', 'required']); !!}
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-8 col-md-offset-2">
					<div class="form-group">
						{{--                        <!-- {!! Form::label('invoice_no', __('sale.invoice_no').':') !!} -->--}}
						{!! Form::text('search_key',null, ['id' =>'search_sell_return','class' => 'form-control','placeholder' => 'Search Sell-Return']); !!}
					</div>
				</div>
				<div class="col-sm-12">
					<table class="table bg-gray" id="sell_return_table">
			          	<thead>
                            <tr class="bg-green">
                                <!-- <th>#</th> -->
                                <th>@lang('product.product_name')</th>
                                <th>@lang('sale.unit_price')</th>
                                <!-- <th>@lang('lang_v1.sell_quantity')</th> -->
                                <th>@lang('lang_v1.return_quantity')</th>
                                <th>Garbage Qty (Box)</th>
                                <th>Garbage Qty (piece)</th>
                                <th>Per Piece Price</th>
                                 <th>State-Tax</th>
                                 <th>City-Tax</th>
                                <!-- <th>P. Since</th> -->
                                <th>@lang('lang_v1.return_subtotal')</th>
								<th></th>
                            </tr>
				        </thead>
				        <tbody>

				          	@foreach($sell->sell_lines as $sellKey => $sell_line)
				          		@php
				          		    $totalTaxAmount += $sell_line->pos_line_tax_amount + $sell_line->city_tax_amount;

				          		    $check_decimal = 'false';
					                if($sell_line->product->unit->allow_decimal == 0){
					                    $check_decimal = 'true';
					                }

					                $unit_name = $sell_line->product->unit->short_name;

					                if(!empty($sell_line->sub_unit)) {
					                	$unit_name = $sell_line->sub_unit->short_name;

					                	if($sell_line->sub_unit->allow_decimal == 0){
					                    	$check_decimal = 'true';
					                	} else {
					                		$check_decimal = 'false';
					                	}
					                }

					            @endphp
				            <tr>

                                <td>
                                    {{ $sell_line->product->name }}
				                 	@if( $sell_line->product->type == 'variable')
				                  	- {{ $sell_line->variations->product_variation->name}}
				                  	- {{ $sell_line->variations->name}}
				                 	@endif
                                     - {{ $sell_line->product->sku }}
									 <textarea name="products[{{$loop->index}}][note]"
                           class="form-control input-sm">{{$sell_line->sell_line_note}}</textarea>
                                </td>
                                <td><input name="products[{{$loop->index}}][unit_price_inc_tax]"
                           type="text" class="form-control input-sm input_number unit_price"
                           value="{{@doubleval($sell_line->unit_price_inc_tax)}}"></td>

                                <?php  //print_r($sell_line->sell_price_inc_tax);die; ?>

				              	<!-- <td>{{ $sell_line->formatted_qty }} {{$unit_name}}</td> -->
                                <td>
						            <input type="text" name="products[{{$loop->index}}][quantity]" value="{{@format_quantity($sell_line->quantity_returned)}}"
						            class="form-control input-sm input_number return_qty input_quantity"
						            >
						            <!--<input name="products[{{$loop->index}}][unit_price_inc_tax]" type="hidden" class="unit_price" value="{{@num_format($sell_line->unit_price_inc_tax)}}">-->
						            <input name="products[{{$loop->index}}][line_id]" type="hidden" value="{{$sell_line->id}}">
                                    <input name="products[{{$loop->index}}][variation_id]" type="hidden" value="{{$sell_line->variation_id}}">

                                </td>



                                <td>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" name="products[{{$loop->index}}][gar_box_return_qty]" value="{{@format_quantity($sell_line->gar_box_return_qty)}}"
                                                class="form-control input-sm input_number gar_box_return_qty input_quantity"
                                                data-rule-abs_digit="true"
                                                data-msg-abs_digit="Decimal value not allowed">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" name="products[{{$loop->index}}][gar_piece_return_qty]" value="{{@format_quantity($sell_line->gar_piece_return_qty)}}"
                                                class="form-control input-sm input_number gar_piece_return_qty garbage_quantity"
                                                data-rule-abs_digit="true"
                                                data-msg-abs_digit="Decimal value not allowed">
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input type="text" name="products[{{$loop->index}}][gar_piece_return_price]" value="{{@format_quantity($sell_line->gar_piece_return_price)}}"
                                                class="form-control input-sm input_number gar_piece_return_price">
                                                <!--data-rule-abs_digit="true"
                                                data-msg-abs_digit="Decimal value not allowed"-->
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <!-- {!! Form::hidden('state_tax', $taxDetails[$sellKey]['tax'], ['id' => 'state_tax']); !!}
                                    {!! Form::hidden('city_tax', $taxDetails[$sellKey]['city_tax'], ['id' => 'city_tax']); !!} -->

                                    <input name="products[{{$loop->index}}][state_tax_id]"
                           			type="hidden" value="{{ isset($taxDetails[$sellKey]['rule']) ? $taxDetails[$sellKey]['rule'] : '' }}">

                           			<input name="products[{{$loop->index}}][state_actual_tax]"type="hidden" class="state_actual_tax" value="{{ isset($taxDetails[$sellKey]['tax']) ? @num_format($taxDetails[$sellKey]['tax']) : 0}}">

                                    <input name="products[{{$loop->index}}][state_tax]"
                           			type="text" class="form-control input-sm input_number state_tax" value="{{isset($sell_line->pos_line_tax_amount) ? @num_format($sell_line->pos_line_tax_amount) : 0}}" readonly>

                                </td>
                                <td>
                                    <input name="products[{{$loop->index}}][city_tax_id]"
		                           type="hidden" value="{{ isset($taxDetails[$sellKey]['city_tax_id']) ? $taxDetails[$sellKey]['city_tax_id'] : '' }}">

		                           <input name="products[{{$loop->index}}][city_actual_tax]"
		                           type="hidden" class="city_actual_tax" value="{{ isset($taxDetails[$sellKey]['city_tax']) ? @num_format($taxDetails[$sellKey]['city_tax']) : 0}}">

		                          <input name="products[{{$loop->index}}][city_tax]"
		                           type="text" class="form-control input-sm input_number city_tax" value="{{isset($sell_line->city_tax_amount) ? @num_format($sell_line->city_tax_amount) : 0}}" readonly>
                                </td>

                                <td>
                                    <div class="return_subtotal"></div>
                                </td>
								<td class="text-center">
									<i data-index="{{$sell_line->id}}" class="fa fa-times text-danger pos_remove_row cursor-pointer" aria-hidden="true"></i>
								</td>
				            </tr>
				          	@endforeach
			          	</tbody>
			        </table>
				</div>
			</div>
			<div class="row">
				@php
					$discount_type = !empty($sell->discount_type) ? $sell->discount_type : $sell->discount_type;
					$discount_amount = !empty($sell->discount_amount) ? $sell->discount_amount : $sell->discount_amount;
					$additional_notes = !empty($sell->additional_notes) ? $sell->additional_notes : "";
				@endphp
				<div class="col-sm-4" style="display: none;">
					<div class="form-group">
						{!! Form::label('discount_type', __( 'purchase.discount_type' ) . ':') !!}
						{!! Form::select('discount_type', [ '' => __('lang_v1.none'), 'fixed' => __( 'lang_v1.fixed' ), 'percentage' => __( 'lang_v1.percentage' )], $discount_type, ['class' => 'form-control']); !!}
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('discount_amount', __( 'purchase.discount_amount' ) . ':') !!}
						{!! Form::text('discount_amount', @num_format($discount_amount), ['class' => 'form-control input_number']); !!}
					</div>
				</div>
				<div class="col-sm-4">
                    <div class="form-group">
                        <label>@lang('lang_v1.box_qty')</label>
                        <input type="text" name="box_qty" value="{{$sell->box_qty ?? 0}}" class="form-control" required/>
                    </div>
                </div>
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('note', __( 'Note' ) . ':') !!}
						{!! Form::textarea('note', $additional_notes , ['class' => 'form-control', 'rows' => 2]); !!}
					</div>
				</div>
			</div>
			@php
				$tax_percent = 0;
				if(!empty($sell->tax)){
					$tax_percent = $sell->tax->amount;
				}
			@endphp
			{!! Form::hidden('tax_id', $sell->tax_id); !!}
			{!! Form::hidden('tax_amount', 0, ['id' => 'tax_amount']); !!}
			{!! Form::hidden('tax_percent', $tax_percent, ['id' => 'tax_percent']); !!}
			<div class="row">
				<div class="col-sm-12 text-right">
					<strong>@lang('lang_v1.total_return_discount'):</strong>
					&nbsp;(-) <span id="total_return_discount"></span>
				</div>
				<div class="col-sm-12 text-right return_tax_hideshow">
					<strong>@lang('lang_v1.total_return_tax') - @if(!empty($sell->tax))({{$sell->tax->name}} - {{$sell->tax->amount}}%)@endif : </strong>
					&nbsp;(+) <span id="total_return_tax">{{ !empty($sell->tax_id) ? $sell->tax_id : $sell->tax_id }}</span>
				</div>
				<div class="col-sm-12 text-right">
				    <input type='hidden' name="net_final_tax" id="netFinalTax" value="{{ $totalTaxAmount }}" >
				    <input type='hidden' name="net_return_amount" id="netReturnAmount" />
                    <input type='hidden' name="net_tax" id="netTax" />
                    <input type='hidden' name="tax_flag" id="taxFlag" />
					<strong>@lang('lang_v1.return_total'): </strong>&nbsp;
					<span id="net_return">0</span>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-sm-12">
				    <button type="submit" name="remove_tax" style="margin-right: 5px;" value="remove_tax" class="btn btn-primary pull-right">@lang('Tax')</button>
                    <button type="submit" name="save_close" style="margin-right: 5px;" value="save_close" class="btn btn-primary pull-right">@lang('Save')</button>
				</div>
			</div>
		</div>
	</div>
	{!! Form::close() !!}

</section>
@stop
@section('javascript')
<script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/sell_return.js?v1=' . $asset_v) }}"></script>
<script type="text/javascript">
    var taxSync = 0;
	$(document).ready( function(){

		$('form#sell_return_form').validate();
		update_sell_return_total();
	});

    $(document).on('change', 'input.return_qty, input.unit_price, input.gar_box_return_qty, input.gar_piece_return_qty, input.gar_piece_return_price, #discount_amount, #discount_type', function () {
            update_sell_return_total()
        });

        function update_sell_return_total() {

            var net_return = 0;
            var totalStateCityTax=0;
            $('table#sell_return_table tbody tr').each(function () {
                let g_box_quantity = __read_number($(this).find('input.gar_box_return_qty'));
                let g_piece_quantity = __read_number($(this).find('input.gar_piece_return_qty'));
                let quantity = __read_number($(this).find('input.return_qty')) + +g_box_quantity;
                let unit_price = __read_number($(this).find('input.unit_price'));
                let gar_piece_return_price = __read_number($(this).find('input.gar_piece_return_price'));

                let sub_pices_total = g_piece_quantity * gar_piece_return_price;
                let subtotal = quantity * unit_price;


                $(this).find('input.state_tax').val(__read_number($(this).find('input.state_actual_tax'))*quantity);

                $(this).find('input.city_tax').val(__read_number($(this).find('input.city_actual_tax'))*quantity);

                 totalStateCityTax = totalStateCityTax + (__read_number($(this).find('input.state_actual_tax'))*quantity) + (__read_number($(this).find('input.city_actual_tax'))*quantity);

                subtotal = subtotal + sub_pices_total;
                $(this).find('.return_subtotal').text(__currency_trans_from_en(subtotal, true));
                net_return += subtotal;
            });
            let discount = 0;
            if ($('#discount_type').val() == 'fixed') {
                discount = __read_number($("#discount_amount"));
            } else if ($('#discount_type').val() == 'percentage') {
                let discount_percent = __read_number($("#discount_amount"));
                discount = __calculate_amount('percentage', discount_percent, net_return);
            }
            discounted_net_return = net_return - discount;

            // console.log($('#netFinalTax').val());
            let totaltax = $('#netFinalTax').val();
            let tax_percent = $('input#tax_percent').val();
            let total_tax = __calculate_amount('percentage', tax_percent, discounted_net_return);
            // let net_return_inc_tax = total_tax + discounted_net_return;
            let net_return_inc_tax = parseFloat(totalStateCityTax) + parseFloat(discounted_net_return);


            let netTax = (taxSync == 0) ? parseFloat(totaltax) + parseFloat(discounted_net_return) : parseFloat(totalStateCityTax) + parseFloat(discounted_net_return);

            // console.log("totaltax"+totaltax);
            // console.log("gg"+discounted_net_return);

            $('input#tax_amount').val(total_tax);
            $('span#total_return_discount').text(__currency_trans_from_en(discount, true));

            (taxSync == 0)? $('span#total_return_tax').text(__currency_trans_from_en(totaltax, true)) : $('span#total_return_tax').text(__currency_trans_from_en(parseFloat(totalStateCityTax), true));
            taxSync++;


            // $('span#total_return_tax').text(__currency_trans_from_en(totaltax, true));
            // $('span#total_return_tax').text(__currency_trans_from_en(total_tax, true));
            $('#netReturnAmount').val(net_return_inc_tax);
            $('#netTax').val(totalStateCityTax);

            if($("#taxFlag").val() == 0){
                    $(".return_tax_hideshow").hide();
                    $('span#net_return').text(__currency_trans_from_en(discounted_net_return, true));

            }else{
                $('span#net_return').text(__currency_trans_from_en(netTax, true));
            }
            // $('span#net_return').text(__currency_trans_from_en(net_return_inc_tax, true));
        }


    $(document).ready(function () {
            $('#getCustomer').on('change',function () {
               let customer_id = $(this).val();
                $.ajax({
                    type: "GET",
                    url: "/sell-return/customer/invoice",
                    data: {
                        customer_id: customer_id,
                    },
                    success: function (data) {
                        $('#invoice_no').val(data.invoice_no);
                        $('.invoice_no').text(data.invoice_no);
                        $('.invoice_date').text(data.updated_at);
                        $('.sales_rep').text(data.sales_person.first_name+' '+data.sales_person.last_name);
                        $('.customer_name').text(data.contact.first_name+' '+data.contact.last_name);
                    }
                });
            });

			$('table#sell_return_table tbody').on('click', 'i.pos_remove_row', function() {
                $(this)
                    .parents('tr')
                    .remove();
                update_sell_return_total();

				var cur_val = $('#product_row_delete').val();
				var new_val = $(this).attr('data-index');
				if(cur_val){
					$('#product_row_delete').val(cur_val + "," + new_val);
				}else{
					$('#product_row_delete').val(new_val);
				}
            });

            $('#search_key').autocomplete({
                source: function (request, response) {
                    let _searchKey = request.term;
                    $("#setSearchKey").val(_searchKey);
                   let custId = $("#getCustomer").val();
                   if(custId != ''){
                        $.ajax({
                            type: "GET",
                            url: "{{action('SellReturnController@getItemForReturn')}}",
                            data: {
                                searchKey: _searchKey,
                                customer_id: custId,
                            },
                            success: function (data) {
                                $('#sell_return_table tbody').html(data);
                            }
                        });

                    }else{
                        alert("Please select customer!");
                    }
                },
                minLength: 3,
                select: function (event, ui) {
                    location.reload();
                }
            });

        });
</script>
@endsection