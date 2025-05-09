<div class="modal-dialog modal-xl no-print v2_sell_popup" role="document">
    <div class="modal-content" style="border-radius:8px;">
        <div class="modal-header" style="padding:20px;border:none;">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" style="height:20px;width:20px;color:black;" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </span>
            </button>
            <span class="modal-title" style="font-size: 18px;font-weight:500;color:black;" id="modalTitle">
                Invoice #{{ $sell->invoice_no }}
                @if (!empty($sell->woocommerce_order_id))
                <i class="fab fa-wordpress text-primary no-print" title="Synced from Woocommerce"></i>
                @endif
            </span>
        </div>
        <div class="modal-body" style="padding:20px;border:none;padding-top:0;">
            <div style="display:flex;">
                <div style="width: 70%;">
                    <span class="v2_text-danger" style="font-size: 20px;font-weight:600;color:black;">
                        @if (!empty($sell->contact->supplier_business_name) && $sell->contact->supplier_business_name != $sell->contact->name)
                            {{ $sell->contact->supplier_business_name }}
                            <br>
                        @endif
                        <small>
                            {{ $sell->contact->name }}</small>
                        <br>
                    </span>
                    <div style="display:flex;align-items:center;margin-top:5px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="height:15px;width:15px;color:black;" fill="currentColor" class="size-6">
                            <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                        </svg>
                        <span style="font-size: 14px;color:black;margin-left:10px;">
                            @if (!empty($sell->billing_address()))
                                {{ $sell->billing_address() }}
                            @else
                                {!! str_replace('<br>', ' ', $sell->contact->contact_address) !!}
                            @endif
                        </span>
                    </div>
                    <div style="display:flex;align-items:center;margin-top:5px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="height:15px;width:15px;color:black;" fill="currentColor" class="size-6">
                            <path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z" />
                            <path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z" />
                        </svg>
                        <span style="font-size: 14px;color:black;margin-left:10px;">
                            {{ !empty($sell->contact->email) ? $sell->contact->email : '--' }}
                        </span>
                    </div>
                    <div style="display:flex;align-items:center;margin-top:5px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" style="height:15px;width:15px;color:black;" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                        <span style="font-size: 14px;color:black;margin-left:10px;">
                            {{ !empty($sell->contact->mobile) ? $sell->contact->mobile : '--' }}
                        </span>

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="margin-left:20px;height:15px;width:15px;color:black;" fill="currentColor" class="size-6">
                            <path fill-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" clip-rule="evenodd" />
                        </svg>
                        <span style="font-size: 14px;color:black;margin-left:10px;">
                            {{ !empty($sell->contact->landline) ? $sell->contact->landline : '--' }}
                        </span>
                    </div>
                </div>

                <div style="width: 30%;">
                    <div style="display:flex;">
                        <div style="margin-left:auto;border-radius:5px;background: #bae6fd;padding:5px 10px;display:flex;align-items:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" style="height:14px;width:14px;color:#075985;" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span style="font-size: 12px;color:#075985;margin-left:10px;font-weight:bold;">
                                {{ date('m/d/Y g:i A', strtotime($sell->transaction_date)) }}
                            </span>
                        </div>
                    </div>

                    <div style="display:flex;margin-top:15px;">
                        <div style="margin-left:auto;border-radius:5px;background: #bae6fd;padding:5px 10px;display:flex;align-items:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" style="height:14px;width:14px;color:#075985;" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                            </svg>
                            <span style="font-size: 12px;color:#075985;margin-left:10px;font-weight:bold;">
                                @if ($sell->status == 'draft' && $sell->is_quotation == 1)
                                    {{ __('lang_v1.quotation') }}
                                @else
                                    {{ __('sale.' . $sell->status) }}
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- legacy --}}
                    <strong>@lang('sale.shipping'):</strong>
                    <br>

                    @if (!empty($sell->shipping_address()))
                        {{ $sell->shipping_address() }}
                    @else
                        @php
                            $addr = $sell->shipping_address ?? '--';
                            if ($addr == '[object Object]') {
                                $addr = '--';
                            }
                        @endphp
                        {{ $addr }}
                    @endif
                    @if (!empty($sell->delivered_to))
                        <br><strong>Tracking ID: </strong> {{ $sell->delivered_to }}
                    @endif


                </div>

            </div>

            @php
                $deliv_methods = [
                    'posShippingModalUpdateDelivery' => 'Delivery',
                    'posShippingModalUpdatePickup' => 'Pickup',
                    'posShippingModalUpdateShipping' => 'Shipping',
                    'posShippingModalUpdatePallet' => 'Pallet',
                    'posShippingModalUpdateSelfNonPicking' => 'Walk In (Self)',
                    'posShippingModalUpdateDeliveryNonPicking' => 'Walk In (Delivery)',
                    'posShippingModalUpdateShippingNonPicking' => 'Walk In (Shipping)',
                ];

                $shipping_colors = [
                    'picked_up' => '#d1fae5',
                    'shipped' => '#d1fae5',
                    'delivered' => '#d1fae5',
                ];
            @endphp

            <div class="row" style="margin-top:15px;">
                <div class="col-sm-2" style="">
                    <div style="border-radius:8px;padding:15px;background:#d1fae5;color:black !important;">
                        <b>Received By</b><br/>
                        <span style="padding-top:5px;">{{ $user->first_name ?? '' }}</span>
                    </div>
                </div>
                <div class="col-sm-2" style="">
                    <div style="border-radius:8px;padding:15px;background:#d1fae5;color:black !important;">
                        <b>Delivery Method</b><br/>
                        <span style="padding-top:5px;">{{ $deliv_methods[$sell->delivery_method] ?? $sell->delivery_method ?? '--' }}</span>
                    </div>
                </div>
                <div class="col-sm-2" style="">
                    <div style="border-radius:8px;padding:15px;background:{{ $sell->order_picking_status == 2 ? '#d1fae5' : '#fee2e2' }};color:black !important;">
                        <b>Picking </b><br/>
                        <span style="padding-top:5px;">By {{ $sell->picked_by_name ?? '' }}</span>
                    </div>
                </div>
                <div class="col-sm-2" style="">
                    <div style="border-radius:8px;padding:15px;background:{{ $sell->order_packing_status == 2 ? '#d1fae5' : '#fee2e2' }};color:black !important;">
                        <b>Packing </b><br/>
                        <span style="padding-top:5px;">By {{ $sell->packed_by_name ?? '' }}</span>
                    </div>
                </div>
                <div class="col-sm-2" style="">
                    <div style="border-radius:8px;padding:15px;background:{{ $shipping_colors[$sell->shipping_status] ?? '#fee2e2' }};color:black !important;">
                        <b>Shipping Status</b><br/>
                        <span style="padding-top:5px;">{{ $shipping_statuses[$sell->shipping_status] ?? '--' }}</span>
                    </div>
                </div>
                <div class="col-sm-2" style="">
                    <div style="border-radius:8px;padding:15px;background:{{ $sell->payment_status == 'paid' ? '#d1fae5' : '#fee2e2' }};color:black !important;">
                        <b>Payment Status</b><br/>
                        <span style="padding-top:5px;">{{ ucwords($sell->payment_status ?? '--') }}</span>
                    </div>
                </div>
            </div>

            <br>
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="table-responsive">
                        @include('sale_pos.partials.sale_line_details_new')
                    </div>
                </div>
            </div>

            <div class="row">

                <div class="col-sm-12 col-xs-12">
                    <span class="" style="font-size: 20px;font-weight:600;color:black;">
                        <small>
                            Payment Details
                        </small>
                        <br>
                    </span>
                </div>
                <div class="col-md-6 col-sm-12 col-xs-12">
                    <div class="table-responsive">
                        <table class="table" style="color:black">
                            <tr class="" style="background:#e0f2fe">
                                <th>#</th>
                                <th>{{ __('messages.date') }}</th>
                                <th>{{ __('purchase.ref_no') }}</th>
                                <th>{{ __('sale.amount') }}</th>
                                <th>{{ __('sale.payment_mode') }}</th>
                                <th>{{ __('sale.payment_note') }}</th>
                            </tr>
                            @php
                                $total_paid = 0;
                            @endphp
                            @foreach ($sell->payment_lines as $payment_line)
                                @php
                                    if ($payment_line->is_return == 1) {
                                        $total_paid -= $payment_line->amount;
                                    } else {
                                        $total_paid += $payment_line->amount;
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ @format_date($payment_line->paid_on) }}</td>
                                    <td>{{ $payment_line->payment_ref_no }}</td>
                                    <td><span class="display_currency"
                                            data-currency_symbol="true">{{ $payment_line->amount }}</span></td>
                                    <td>
                                        {{ $payment_types[$payment_line->method] ?? $payment_line->method }}
                                        @if ($payment_line->is_advance == 1 && $payment_line->advance_amt > 0)
                                            <br />
                                            (Advance)
                                        @elseif($payment_line->is_return == 1)
                                            <br />
                                            ( {{ __('lang_v1.change_return') }} )
                                        @endif
                                    </td>
                                    <td>
                                        @if ($payment_line->note)
                                            {{ ucfirst($payment_line->note) }}
                                        @else
                                            --
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12 col-xs-12">
                    <div class="table-responsive">
                        <table class="table" style="background:#e0f2fe;color:black;border-color:black;">
                            <tr>
                                <th>Sub Total: </th>
                                <td></td>
                                <td><span class="display_currency pull-right"
                                        data-currency_symbol="true">{{ $sell->total_before_tax }}</span></td>
                            </tr>
                            <!--<tr>-->
                            <!--  <th>{{ __('sale.discount') }}:</th>-->
                            <!--  <td><b>(-)</b></td>-->
                            <!--  <td><div class="pull-right"><span class="display_currency" @if ($sell->discount_type == 'fixed') data-currency_symbol="true" @endif>{{ $sell->discount_amount }}</span> @if ($sell->discount_type == 'percentage')
                                    {{ '%' }}
                                    @endif
                                    </span>
                                    </div></td>-->
                            <!--</tr>-->
                            @if (in_array('types_of_service', $enabled_modules) && !empty($sell->packing_charge))
                                <tr>
                                    <th>{{ __('lang_v1.packing_charge') }}:</th>
                                    <td><b>(+)</b></td>
                                    <td>
                                        <div class="pull-right"><span class="display_currency"
                                                @if ($sell->packing_charge_type == 'fixed') data-currency_symbol="true" @endif>{{ $sell->packing_charge }}</span>
                                            @if ($sell->packing_charge_type == 'percent')
                                                {{ '%' }}
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                            @if (session('business.enable_rp') == 1 && !empty($sell->rp_redeemed))
                                <tr>
                                    <th>{{ session('business.rp_name') }}:</th>
                                    <td><b>(-)</b></td>
                                    <td> <span class="display_currency pull-right"
                                            data-currency_symbol="true">{{ $sell->rp_redeemed_amount }}</span></td>
                                </tr>
                            @endif
                            <tr>
                                <th>{{ __('sale.order_tax') }}:</th>
                                <td><b>(+)</b></td>
                                <td class="text-right">
                                    @php
                                        $i = 0;
                                        if (count($sell->sell_lines) > 0) {
                                            foreach ($sell->sell_lines as $sell_line) {
                                                $i = $i + $sell_line->city_tax_amount + $sell_line->pos_line_tax_amount;
                                            }
                                        }
                                    @endphp
                                    <strong><small></small></strong> <span class="display_currency pull-right"
                                        data-currency_symbol="true">{{ $i }}</span><br>
                                    {{--      @if (!empty($order_taxes))
                                            @foreach ($order_taxes as $k => $v)
                                                <strong><small>{{$k}}</small></strong> - <span class="display_currency pull-right" data-currency_symbol="true">{{ $v }}</span><br>
                                            @endforeach
                                            @else
                                            0.00
                                            @endif --}}
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('sale.shipping') }}: @if ($sell->shipping_details)
                                        ({{ $sell->shipping_details }})
                                    @endif
                                </th>
                                <td><b>(+)</b></td>
                                <td><span class="display_currency pull-right"
                                        data-currency_symbol="true">{{ $sell->shipping_charges }}</span></td>
                            </tr>
                            <tr>
                                <th>Order Total:</th>
                                <td></td>
                                <td><span class="display_currency pull-right"
                                        data-currency_symbol="true">{{ $sell->total_before_tax + $sell->shipping_charges + $i }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('sale.discount') }}:</th>
                                <td><b>(-)</b></td>
                                <td>
                                    <div class="pull-right"><span class="display_currency"
                                            @if ($sell->discount_type == 'fixed') data-currency_symbol="true" @endif>{{ $sell->discount_amount }}</span>
                                        @if ($sell->discount_type == 'percentage')
                                            {{ '%' }}
                                        @endif
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <!-- <tr>
                                <th>{{ __('lang_v1.round_off') }}: </th>
                                <td></td>
                                <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->round_off_amount }}</span></td>
                                </tr>   -->
                            <tr>
                                @php
                                    $total_paid = (string) $total_paid;
                                    if ($sell->is_advance == 1 && $sell->payment_status == 'paid') {
                                        $total_remaining = $sell->amount - $sell->final_total - $sell->advanceamt;
                                    } else {
                                        $total_remaining = $sell->final_total - $total_paid;
                                    }
                                @endphp
                                <th>{{ __('sale.total_payable') }}: </th>
                                <td></td>
                                <td><span class="display_currency pull-right"
                                        data-currency_symbol="true">{{ $sell->final_total }}</span></td>
                            </tr>
                            <tr>
                                <th>{{ __('sale.total_paid') }}:</th>
                                <td></td>
                                <td><span class="display_currency pull-right"
                                        data-currency_symbol="true">{{ $total_paid }}</span></td>
                            </tr>
                            <tr>
                                <th>{{ __('sale.total_remaining') }}:</th>
                                <td></td>
                                <td>
                                    <!-- Converting total paid to string for floating point substraction issue -->
                                    @php
                                        $total_paid = (string) $total_paid;
                                    @endphp
                                    <span class="display_currency pull-right"
                                        data-currency_symbol="true">{{ $total_remaining }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-5">
                    <strong>{{ __('sale.sell_note') }}:</strong><br>
                    <p class="well well-sm no-shadow bg-gray">
                        @if ($sell->additional_notes)
                            {!! nl2br($sell->additional_notes) !!}
                        @else
                            --
                        @endif
                    </p>
                </div>
                <div class="col-sm-5">
                    <strong>{{ __('sale.staff_note') }}:</strong><br>
                    <p class="well well-sm no-shadow bg-gray">
                        @if ($sell->staff_note)
                            {!! nl2br($sell->staff_note) !!}
                        @else
                            --
                        @endif
                    </p>
                </div>
                <div class="col-sm-2">
                    <strong>@lang('lang_v1.box_qty')</strong><br>
                    <p class="well well-sm no-shadow bg-gray">
                        {!! nl2br($sell->box_qty ?? 0) !!}
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">

                @include('sale_pos.organized_logs.index')

                    <table class="table bg-gray">
                        <thead>
                            <tr class="bg-green">
                                <th>User Name</th>
                                <th>Action</th>
                                <th>Message</th>
                                <th width="10%">Date And Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activity_logs as $log)
                                <tr>
                                    <td>{{ $log->first_name }}</td>
                                    @if ($log->description == 'added')
                                        <td>Added</td>
                                    @elseif($log->description == 'edited')
                                        <td>Edited</td>
                                    @elseif($log->description == 'send_email')
                                        <td>Send Email</td>
                                    @elseif($log->description == 'send_whatsapp')
                                        <td>Send Notification</td>
                                    @endif
                                    @php $str_array = explode(',',$log->message); @endphp
                                    <td>
                                        @foreach ($str_array as $message)
                                            @if ($message != '')
                                                # {{ $message }}<br>
                                            @endif
                                        @endforeach
                                    </td>
                                    <!--<td width="10%"> {{ Carbon\Carbon::parse($log->datetime)->format('d-m-Y G:i A') }}</td>-->
                                    <td width="10%">
                                        {{ Carbon\Carbon::parse($log->datetime)->format('m/d/Y H:i A') }}</td>
                                </tr>
                            @empty
                                <td colspan="4" style="text-align: center;">No logs Found!!</td>
                            @endforelse
                            <tr></tr>

                            @foreach ($pickpacklog as $log)
                                <tr>
                                    <td>{{ $log->first_name }}</td>
                                    @if ($log->type == 'start picking')
                                        <td>Start Picking</td>
                                    @elseif($log->type == 'start packing')
                                        <td>Start Packing</td>
                                    @endif
                                    @if ($log->type == 'start picking')
                                        <td>#Picking Started by {{ $log->first_name }} at
                                            {{ Carbon\Carbon::parse($log->datetime)->format('d-m-Y H:i:s') }}</td>
                                    @elseif($log->type == 'start packing')
                                        <td>#Packing Started by {{ $log->first_name }} at
                                            {{ Carbon\Carbon::parse($log->datetime)->format('d-m-Y H:i:s') }}</td>
                                    @endif
                                    <br>
                                    {{-- <td width="10%"> {{ Carbon\Carbon::parse($log->datetime)->format('d-m-Y G:i A') }}</td> --}}
                                    <td width="10%">
                                        {{ Carbon\Carbon::parse($log->datetime)->format('m/d/Y H:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <table class="table bg-gray" style="display: none;">
                        <caption><strong>Recalculate Log</strong></caption>
                        <thead>
                            <tr class="bg-green">
                                <th>User Name</th>
                                <th>Action</th>
                                <th>Message</th>
                                <th width="10%">Date And Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recalculate_log as $logs)
                                <tr>
                                    <td>{{ $logs->first_name }}</td>
                                    @if ($logs->description == 'added')
                                        <td>Added</td>
                                    @elseif($logs->description == 'edited')
                                        <td>Edited</td>
                                    @endif
                                    @php $str_array = explode(',',$logs->message); @endphp
                                    <td>
                                        @foreach ($str_array as $message)
                                            @if ($message != '')
                                                # {{ $message }}<br>
                                            @endif
                                        @endforeach
                                    </td>
                                    {{-- <td width="10%"> {{ Carbon\Carbon::parse($logs->datetime)->format('d-m-Y G:i A') }}</td> --}}
                                    <td width="10%">
                                        {{ Carbon\Carbon::parse($logs->datetime)->format('m/d/Y H:i A') }}</td>
                                </tr>
                            @empty
                                <td colspan="4" style="text-align: center;">No logs Found!!</td>
                            @endforelse
                            <tr></tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            {{-- <a href="#" class="print-invoice btn btn-primary"
                data-href="{{ route('sell.printInvoice', [$sell->id]) }}"><i class="fa fa-print"
                    aria-hidden="true"></i> @lang('messages.print')</a> --}}
            <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var element = $('div.modal-xl');
        __currency_convert_recursively(element);
    });
</script>
