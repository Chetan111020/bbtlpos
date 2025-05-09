<style>
	page[size="A4"] {
		width: 21cm;
		height: 29.7cm;
	}
	page[size="A4"][layout="landscape"] {
		width: 29.7cm;
		height: 21cm;
	}
	.header {
		position: fixed;
		left: 0px;
		top: -100px;
		right: 0px;
		height: 0px;
		text-align: center;
	}
	.footer {
		position: fixed;
		left: 0px;
		bottom: -50px;
		right: 0px;
		height: 50px;
	}
	.header .pagenum:before {
		content: counter(page);
	}
	table {
		page-break-inside: auto
	}
	tr {
		page-break-inside: avoid;
		page-break-after: auto
	}
	thead {
		display: table-header-group
	}
	tfoot {
		display: table-footer-group
	}
	table {
		width: 100%;
	}
	table,
	th,
	td {
		border: 0px solid #000;
		border-collapse: collapse;
		padding: 2px;
		color: #060606 !important;
	}

	td .tdclass {
		color: #060606 !important;
	}

	p {
		color: #060606 !important;
	}

	body {
		font-family: "Poppins", sans-serif;
		font-size: 12px;
		padding: 0px;
		margin: 0px;
		line-height: 16px;
	}

.loader {
  border: 4px solid #f3f3f3;
  border-radius: 100%;
  border-top: 7px solid blue;
  border-right: 7px solid green;
  border-bottom: 7px solid red;
  border-left: 7px solid pink;
  width: 35px;
  height: 35px;
  -webkit-animation: spin 2s linear infinite;
  animation: spin 2s linear infinite;
}

@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
<div style="padding: 15px 50px;">
	<table style="width: 100%;border: none;">
		<tr>
			<td style="border: none;">
				<table style="width: 100%;border: none">
					<tr>
						<td colspan="" style="text-align: right;vertical-align: top;border: none;">
							<table style="border: none;width:100%">
								<tr>
									<td style="border: none;width:38%;text-align: center;display:flex;">
										<!-- Logo -->
										@if(!empty($receipt_details->logo))
										<img src="{{$receipt_details->logo}}" style="height:76px;" class="img img-responsive center-block">
										@endif
										<!-- Header text -->
										@if(!empty($receipt_details->header_text))
										<div class="col-xs-12" style="text-align:left;">
											{!! $receipt_details->header_text !!}
										</div>
										@endif
										<h2 style="text-transform: uppercase; margin-bottom:3px;margin-top:18px;margin-left:15px;font-size: 25px; font-weight: 600; text-align: left;color: #060606">
											<!-- Shop & Location Name  -->
											@if(!empty($receipt_details->display_name))
											{{$receipt_details->display_name}}
											@endif
										</h2>
										<p style="font-size: 13px; text-align:left;margin-top:15px;">
											@if(!empty($receipt_details->address_line_1))
											<small class="text-center">
												{!! $receipt_details->address_line_1 !!}
											</small>
											@endif
											@if(!empty($receipt_details->contact))
											<br />{{ $receipt_details->contact }}
											@endif
											@if(!empty($receipt_details->contact) && !empty($receipt_details->website))
											,
											@endif
											@if(!empty($receipt_details->website))
											{{ $receipt_details->website }}
											@endif
											@if(!empty($receipt_details->location_custom_fields))
											<br>{{ $receipt_details->location_custom_fields }}
											@endif
										</p>
										<p>
											@if(!empty($receipt_details->sub_heading_line1))
											{{ $receipt_details->sub_heading_line1 }}
											@endif
											@if(!empty($receipt_details->sub_heading_line2))
											<br>{{ $receipt_details->sub_heading_line2 }}
											@endif
											@if(!empty($receipt_details->sub_heading_line3))
											<br>{{ $receipt_details->sub_heading_line3 }}
											@endif
											@if(!empty($receipt_details->sub_heading_line4))
											<br>{{ $receipt_details->sub_heading_line4 }}
											@endif
											@if(!empty($receipt_details->sub_heading_line5))
											<br>{{ $receipt_details->sub_heading_line5 }}
											@endif
										</p>
										<p>
											@if(!empty($receipt_details->tax_info1))
											<b>{{ $receipt_details->tax_label1 }}</b> {{ $receipt_details->tax_info1 }}
											@endif

											@if(!empty($receipt_details->tax_info2))
											<b>{{ $receipt_details->tax_label2 }}</b> {{ $receipt_details->tax_info2 }}
											@endif
										</p>
									</td>
									<td style="border: none;vertical-align: top;padding-left: 18px">
										<h2 style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 20px; color: #000000; font-weight: 600;">
											@if(!empty($receipt_details->invoice_heading))
											Driver Invoice
											@endif
										</h2>
										<p style="margin-left: 43%;line-height:15px;font-size:12px;text-align: left; padding-left: 26px;">
											<br>
										</p>
									</td>
								</tr>
							</table>
						</td>

					</tr>
					<tr>
					</tr>
					<tr>
						<td colspan="" style="text-align: center;border:none">
							<table style="border: none;">
								<tr>
									<td style="vertical-align: top;width: 50%;border: 1px solid;padding: 5px">
										<table>

											<tr>
												<td style="vertical-align: top;padding-left: 5px;text-align: center; ">
													<!-- Table information-->
													@if(!empty($receipt_details->table_label) || !empty($receipt_details->table))

													<span class="pull-left text-left">
														@if(!empty($receipt_details->table_label))
														<b>{!! $receipt_details->table_label !!}</b>
														@endif
														{{$receipt_details->table}}
														<!-- Waiter info -->
													</span>
													@endif
													<!-- customer info -->
													@if(!empty($receipt_details->customer_name))

													<b>
														<span style="font-weight: 700; font-size: 12px;">{{ $receipt_details->customer_label }} : <br> {{ $receipt_details->customer_name }} ({{$receipt_details->contact_id}})<br></b></span>

													@endif
													<span style="color: #000;"> @if (!empty($receipt_details->address_line_1))
                                                    <span style="margin-left: 3px;">
                                                        {!! $receipt_details->address_line_1 !!}
                                                    </span>,
                                                @endif
                                                @if (!empty($receipt_details->address_line_2))
                                                    <span style="margin-left: 3px;">
                                                        {!! $receipt_details->address_line_2 !!}
                                                    </span>
                                                @endif
                                                <br>
                                                @if (!empty($receipt_details->city))
                                                    <span style="margin-left: 3px;">
                                                        {!! $receipt_details->city !!}
                                                    </span>
                                                @endif
                                                @if (!empty($receipt_details->state))
                                                    <span style="margin-left: 3px;">
                                                        {!! $receipt_details->state !!}
                                                    </span>
                                                @endif
                                                @if (!empty($receipt_details->zip_code))
                                                    <span style="margin-left: 3px;">
                                                        {{ $receipt_details->zip_code }}
                                                    </span>
                                                @endif <br>
                                                @if (!empty($receipt_details->mobile))

                                                    <span style="margin-left: 3px;">
                                                        Phone:
                                                        {{ $receipt_details->mobile }}
                                                    </span>
                                                @endif
													</span>
													@if(!empty($receipt_details->client_id_label))
													<br />
													{{ $receipt_details->client_id_label }} {{ $receipt_details->client_id }}
													@endif
													@if(!empty($receipt_details->customer_tax_label))
													<br />
													{{ $receipt_details->customer_tax_label }} {{ $receipt_details->customer_tax_number }}
													@endif
													@if(!empty($receipt_details->customer_custom_fields))
													<br />{!! $receipt_details->customer_custom_fields !!}
													@endif
													@if(!empty($receipt_details->sales_person_label))
													<br />
													{{ $receipt_details->sales_person_label }} {{ $receipt_details->sales_person }}
													@endif
													@if(!empty($receipt_details->customer_rp_label))
													<br />
													<strong>{{ $receipt_details->customer_rp_label }}</strong> {{ $receipt_details->customer_total_rp }}
													@endif
													@if(!empty($receipt_details->due_date_label))
													<br><b>{{$receipt_details->due_date_label}}</b> {{$receipt_details->due_date ?? ''}}
													@endif
													@if(!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand))
													<br>
													@if(!empty($receipt_details->brand_label))
													<b>{!! $receipt_details->brand_label !!}</b>
													@endif
													{{$receipt_details->repair_brand}}
													@endif

													@if(!empty($receipt_details->device_label) || !empty($receipt_details->repair_device))
													<br>
													@if(!empty($receipt_details->device_label))
													<b>{!! $receipt_details->device_label !!}</b>
													@endif
													{{$receipt_details->repair_device}}
													@endif

													@if(!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no))
													<br>
													@if(!empty($receipt_details->model_no_label))
													<b>{!! $receipt_details->model_no_label !!}</b>
													@endif
													{{$receipt_details->repair_model_no}}
													@endif

													@if(!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
													<br>
													@if(!empty($receipt_details->serial_no_label))
													<b>{!! $receipt_details->serial_no_label !!}</b>
													@endif
													{{$receipt_details->repair_serial_no}}<br>
													@endif
													@if(!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
													@if(!empty($receipt_details->repair_status_label))
													<b>{!! $receipt_details->repair_status_label !!}</b>
													@endif
													{{$receipt_details->repair_status}}<br>
													@endif

													@if(!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
													@if(!empty($receipt_details->repair_warranty_label))
													<b>{!! $receipt_details->repair_warranty_label !!}</b>
													@endif
													{{$receipt_details->repair_warranty}}
													<br>
													@endif
													<!-- Waiter info -->
													@if(!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
													<br />
													@if(!empty($receipt_details->service_staff_label))
													<b>{!! $receipt_details->service_staff_label !!}</b>
													@endif
													{{$receipt_details->service_staff}}
													@endif
												</td>
											</tr>
										</table>
									</td>

									<td style="vertical-align: top;width: 50%;border: 1px solid;padding: 5px">
										<b style="margin-left: 3px;">{{$receipt_details->date_label}} : {{$receipt_details->invoice_date}}</b>
										<br>
										<span style="margin-right: 54px;">
                                            <b>
                                                @if(!empty($receipt_details->invoice_no_prefix))
                                                    {!! $receipt_details->invoice_no_prefix !!}
                                                @endif
                                                {{$receipt_details->invoice_no}}
                                            </b>
                                        </span>
                                        <span style="margin-right: 54px;">
                                            <br>
                                            <b>
                                                Sales Rep : {{$receipt_details->sales_rep}}
                                            </b>
                                        </span>

                                        <span style="margin-right: 54px;">
                                            <br>
                                            <b>
                                                Contact Person : {{$receipt_details->contact_person_1}}
                                            </b>
                                        </span>

                                        <span style="margin-right: 54px;">
                                            <br>
                                            <b>Order Note :</b>{{ $receipt_details->additional_notes }}
                                        </span>
									</td>

								</tr>
							</table>
						</td>
					</tr>

					<table style="width: 106%;border-top: 1px solid #808080; margin-top: 4px;">
						<tr>
							<td colspan="" style="text-align: center;border:none">
								<table style="border: none;">
									<tr>
										<td style="vertical-align: top;width: 50%;border: none;">
											<table style="">
												<tr>
													<td style="vertical-align: top;padding-left: 5px;text-align: left;">
														<span style="font-size: 16px; font-weight:300"> </span><br>

													</td>
												</tr>
											</table>
										</td>

									<table>
										<tr>
											<td>
									<table style="border: 1px solid;">
										<tr >
											<th style="text-align: center !important;" colspan="3">
												<span>Open Invoice</span>
											</th>
										</tr>
										<tr style="border: 1px solid;">
											<th style="text-align: left !important;">
												<span>Invoice No</span>
											</th>
											<th style="text-align: left !important; border-left: 1px solid;">
												<span>Open Amount</span>
											</th>
											<th style="text-align: left !important; border-left: 1px solid;">
												<span>Invoice Date</span>
											</th>
										</tr>
                                        @php
                                            $limit = 7;
                                        @endphp
                                        @foreach ($receipt_details->unpaid_inv as $item)
                                            @php
                                                if(empty($item->due_on_inv)){
                                                    // skip if payment due = 0
                                                    continue;
                                                }
                                                else{
                                                    $limit--;
                                                }
                                                if($limit < 0){
                                                    break;
                                                }
                                            @endphp
                                            <tr style="border: 1px solid;">
                                                <td style="text-align: left !important;border-left: 1px solid;">
                                                    <span>{{ $item->invoice_no }}</span>
                                                </td>
                                                <td style="text-align: left !important;border-left: 1px solid;">
                                                    <span>$ {{ number_format($item->due_on_inv, 2) }}</span>
                                                </td>
                                                <td style="text-align: left !important;border-left: 1px solid;">
                                                    <span>{{ date('m/d/Y g:i A', strtotime($item->transaction_date)) }}</span>
                                                </td>
                                            </tr>

                                        @endforeach

									@php $k= 0; @endphp
									{{-- @foreach($receipt_details->all_no_due as $data )

									@if(strpos($data['Invoice No'],'CM')!== false)
										    @php continue;
										    @endphp
										    @endif
									    @if($data['Due Amount'] != '0')


    									    <tr style="border: 1px solid;">
    											<td style="text-align: left !important;border-left: 1px solid;">
    												<span>{{$data['Invoice No']}}</span>
    											</td>
    											<td style="text-align: left !important;border-left: 1px solid;">
    												<span>${{$data['Due Amount']}}</span>
    											</td>
    											<td style="text-align: left !important;border-left: 1px solid;">
    												<span>{{ @format_datetime($data['Date']) }}</span>
    											</td>
    										</tr>
    										@php
    										    $k++;
    										    if ($k >= 5) break;
    										@endphp
										@endif
									@endforeach --}}
									<div class="col-md-5">
                                        <table>
                                            <tr style="border: 1px solid #808080;">
                                                <th style="width:75%;">@lang('lang_v1.box_qty')</th>
                                                <td style="width:25%;border: 1px solid #808080;">{{ $receipt_details->box_qty ?? 0 }}</td>
                                            </tr>
                                        </table>
                                    </div>

									</table>


										<table style="border: 1px solid;display:none;">
										<tr >
											<th style="text-align: center !important;" colspan="3">
												<span>Old Invoices</span>
											</th>
										</tr>
										<tr style="border: 1px solid;">
											<th style="text-align: left !important;">
												<span>Invoice No</span>
											</th>
											<th style="text-align: left !important; border-left: 1px solid;">
												<span>Open Amount</span>
											</th>
											<th style="text-align: left !important; border-left: 1px solid;">
												<span>Invoice Date</span>
											</th>
										</tr>
									@php $l= 0; @endphp
									@foreach($last_invoices as $data )

    									    <tr style="border: 1px solid;">
    											<td style="text-align: left !important;border-left: 1px solid;">
    												<span>{{$data['invoice_no']}}</span>
    											</td>
    											<td style="text-align: left !important;border-left: 1px solid;">
    												<span>${{ round($data['final_total'],2) }}</span>
    											</td>
    											<td style="text-align: left !important;border-left: 1px solid;">
    												<span>{{ @format_datetime($data['transaction_date']) }}</span>
    											</td>
    										</tr>
    										@php
    										    $k++;
    										    if ($l >= 5) break;
    										@endphp
									@endforeach

									</table>
											</td>
											<td>
												<table style="margin-bottom: 40px;margin-top: 5px !important;">
										<!-- Total Due-->
										<tr>
											<th style="text-align: right !important;">
												Open Balance :
											</th>
											<td class="text-right" style="width: 100px;">
											$ {!! number_format((float) $receipt_details->opening, 2, '.', '')  !!}
											</td>
										</tr>
	                                    <tr class="">
											<th style="text-align: right !important;" >
											    Credit Memo :
											</th>
											<td class="text-right" style="width: 100px;">
											@if(!empty($ledger_details))
											$ {!! number_format((float) $ledger_details['total_return'], 2, '.', '')  !!}
											@endif
											</td>
										</tr>
										<tr>
											<th style="text-align: right !important;">
												Credits:
											</th>
											<td class="text-right" style="width: 100px;">
											$ {{number_format((float)$receipt_details->credit_bln2, 2, '.', '')}}
											</td>
										</tr>

										<tr>
											<th style="text-align: right !important;">
												Total Due:
											</th>
											<td class="text-right" style="width: 100px;">
										$ {!! number_format((float) $receipt_details->totaldue, 2, '.', '')    !!}
	</td>
										</tr>
										<!-- Total -->
										<!-- <tr>
											<th style="text-align: right !important;">
												Order Total :
											</th>
											<td class="text-right">
												{{$receipt_details->total}}
												@if(!empty($receipt_details->total_in_words))
												<br>
												<small>({{$receipt_details->total_in_words}})</small>
												@endif
											</td>
										</tr> -->
										<tr>
											<th style="text-align: right !important;">
												Amount Received :
											</th>
											<td class="text-right">
											</td>
										</tr>

										<tr rowspan="3">
											<th style="text-align: right !important;">
												Balance :

											</th>
											<td class="text-right">

											</td>
										</tr>



									</table>
											</td>
										</tr>
									</table>


							</td>
						</tr>
					</table>
			</td>
		</tr>
	</table>
	</table>
	<div class="row">
		@includeIf('sale_pos.receipts.partial.common_repair_invoice')
	</div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<table style="width: 106%;padding:2px;">
		<thead>
			<tr>
				<td style="font-weight: 700;text-align: left;vertical-align:top;border: 1px solid #808080;">
					Sr.</td>
				<td style="font-weight: 700;vertical-align:top;border: 1px solid #808080;">
					{{$receipt_details->table_product_label}}
				</td>
				<td style="font-weight: 700;text-align: left;vertical-align:top;border: 1px solid #808080;">
					{{$receipt_details->table_qty_label}}
				</td>
				<td style="font-weight: 700;text-align: left;vertical-align:top;border: 1px solid #808080;">
					{{$receipt_details->table_unit_price_label}}
				</td>
				<td style="font-weight: 700;text-align: left;vertical-align:top;border: 1px solid #808080;">
					{{$receipt_details->table_subtotal_label}}
				</td>
			</tr>
		</thead>
		<?php
		$sr = 1;
		?>
		      <div id="myloader" class="loader" style="margin-left: 291px;"></div>
		<tbody>
			<?php
			for ($x = 1; $x <= 10; $x++) {
			?>
				<tr style="height: 25px;">
					<td style="border: 1px solid #808080;"><?php echo "$x"; ?>)</td>
					<td style="border: 1px solid #808080;"></td>
					<td style="border: 1px solid #808080;"></td>
					<td style="border: 1px solid #808080;"></td>
					<td style="border: 1px solid #808080;"></td>
				</tr>
			<?php }
			?>
		</tbody>
	</table>
	</td>

	</tr>
	</table> <br><br>
	<footer>
		<hr>
		<table style="text-align: center;">
			<tr>
				<td><b>We greatly appreciate your support and business!
						Customers are responsible for paying their Local, State & Federal Excise taxes for
						applicable products.
		</table>
	</footer>
</div>

<script type="text/javascript">
	function renove() {

$('#myloader').remove();
}
renove();
</script>