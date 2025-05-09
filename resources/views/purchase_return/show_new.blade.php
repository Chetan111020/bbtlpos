<div class="modal-dialog modal-xl no-print" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle"> Vendor Credit Memo (<b>@lang('Credit Memo No'):</b>
                #{{ $purchase->ref_no }})
            </h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-12">
                    <p class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($purchase->transaction_date) }}</p>
                </div>
            </div>
            <div class="row invoice-info">
                <div class="col-sm-4 invoice-col">
                    @lang('purchase.supplier'):
                    <address>
                        <strong>{{ $purchase->contact->supplier_business_name }}</strong>
                        {{ $purchase->contact->name }}
                        @if (!empty($purchase->contact->address_line_1))
                            <br>{{ $purchase->contact->address_line_1 }}
                        @endif
                        @if (!empty($purchase->contact->address_line_2))
                            <br>{{ $purchase->contact->address_line_2 }}
                        @endif
                        @if (!empty($purchase->contact->city) || !empty($purchase->contact->state) || !empty($purchase->contact->country))
                            <br>{{ implode(',', array_filter([$purchase->contact->city, $purchase->contact->state, $purchase->contact->country, $purchase->contact->zip_code])) }}
                        @endif
                        @if (!empty($purchase->contact->tax_number))
                            <br>@lang('contact.tax_no'): {{ $purchase->contact->tax_number }}
                        @endif
                        @if (!empty($purchase->contact->mobile))
                            <br>@lang('contact.mobile'): {{ $purchase->contact->mobile }}
                        @endif
                        @if (!empty($purchase->contact->email))
                            <br>@lang('business.email'): {{ $purchase->contact->email }}
                        @endif
                    </address>
                </div>

                <div class="col-sm-4 invoice-col">
                    @lang('business.business'):
                    <address>
                        <strong>{{ $purchase->business->name }}</strong>
                        {{ $purchase->location->name }}
                        @if (!empty($purchase->location->landmark))
                            <br>{{ $purchase->location->landmark }}
                        @endif
                        @if (!empty($purchase->location->city) ||
                            !empty($purchase->location->state) ||
                            !empty($purchase->location->country))
                            <br>{{ implode(',', array_filter([$purchase->location->city, $purchase->location->state, $purchase->location->country])) }}
                        @endif

                        @if (!empty($purchase->business->tax_number_1))
                            <br>{{ $purchase->business->tax_label_1 }}: {{ $purchase->business->tax_number_1 }}
                        @endif

                        @if (!empty($purchase->business->tax_number_2))
                            <br>{{ $purchase->business->tax_label_2 }}: {{ $purchase->business->tax_number_2 }}
                        @endif

                        @if (!empty($purchase->location->mobile))
                            <br>@lang('contact.mobile'): {{ $purchase->location->mobile }}
                        @endif
                        @if (!empty($purchase->location->email))
                            <br>@lang('business.email'): {{ $purchase->location->email }}
                        @endif
                    </address>
                </div>
                <div class="col-sm-4 invoice-col">
                    <b>@lang('Credit Memo No'):</b> #{{ $purchase->ref_no }}<br />
                    <b>Received Date:</b> {{ @format_date($purchase->transaction_date) }}<br />
                    <b>Invoice @lang('messages.date'):</b>
                    {{ @format_date($purchase->received_date ?? $purchase->transaction_date) }}<br />
                    {{-- <b>@lang('purchase.purchase_status'):</b> {{ __('lang_v1.' . $purchase->status) }}<br> --}}
                    <b>@lang('purchase.payment_status'):</b> {{ __('lang_v1.' . $purchase->payment_status) }}<br>
                </div>
            </div>
            <div class="row invoice-info">
                <div class="col-sm-12 invoice-col">
                    <span><b>View Files:</b></span>

                    @if ($purchase->document_path)
                        <tr>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ $purchase->document_path }}" class="pull-left btn btn-primary"
                                        target="_blank" title="View Document">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ $purchase->document_path }}" download="{{ $purchase->document_name }}"
                                        class="btn btn-primary no-print" title="Download">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endif

                    @if ($purchase->extra_document && count($purchase->extra_document) > 0)
                        <tr>
                            @forelse ($purchase->extra_document as $key => $doc)
                                @php $path = !empty($doc) ? asset('/uploads/documents/' . rawurlencode($doc)) : null; @endphp
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ $path }}" class="pull-left btn btn-primary" target="_blank"
                                            title="View Document">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ $path }}" download="{{ $doc }}"
                                            class="btn btn-primary no-print" title="Download">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </div>
                                </td>
                            @empty
                            @endforelse
                        </tr>
                    @endif
                </div>
            </div>

            <br>
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="table-responsive">
                        <table class="table bg-gray">
                            <thead>
                                <tr class="bg-green">
                                    <th>#</th>
                                    <th>@lang('product.product_name')</th>
                                    <th class="text-center">Stock Adjusted</th>
                                    <th class="text-center">Return Quantity</th>
                                    <th class="text-center">@lang('sale.unit_price')</th>
                                    <th class="text-center">Loose Pcs Qty</th>
                                    <th class="text-center">Loose Pcs Price</th>
                                    <th class="text-center">@lang('lang_v1.return_subtotal')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $total_before_tax = 0;
                                    $total_return_qty = 0;
                                    $total_unit_price = 0;
                                    $total_loose_qty = 0;
                                    $total_loose_price = 0;
                                @endphp
                                @foreach ($purchase->purchase_lines as $purchase_line)
                                    @php
                                        $total_return_qty += $purchase_line->inv_return;
                                        $total_unit_price += $purchase_line->purchase_price_inc_tax;
                                        $total_loose_qty += $purchase_line->loose_qty;
                                        $total_loose_price += $purchase_line->loose_price;
                                        $unit_name = $purchase_line->product->unit->short_name;
                                        if (!empty($purchase_line->sub_unit)) {
                                            $unit_name = $purchase_line->sub_unit->short_name;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $purchase_line->product->name }}
                                            @if ($purchase_line->product->type == 'variable')
                                                - {{ $purchase_line->variations->product_variation->name }}
                                                - {{ $purchase_line->variations->name }}
                                            @endif
                                        </td>
                                        <td class="text-center">{{ ($purchase_line->is_deducted == 0)?'Yes':'No' }}</td>
                                        <td class="text-center">{{ @format_quantity($purchase_line->inv_return) }} {{ $unit_name }}</td>
                                        <td class="text-center"><span class="display_currency"
                                            data-currency_symbol="true">{{ $purchase_line->purchase_price_inc_tax }}</span>
                                        </td>
                                        <td class="text-center">{{ @format_quantity($purchase_line->loose_qty) }} {{ $unit_name }}</td>
                                        <td class="text-center"><span class="display_currency"
                                            data-currency_symbol="true">{{ $purchase_line->loose_price }}</span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $line_total = $purchase_line->sub_total;
                                                $total_before_tax += $line_total;
                                            @endphp
                                            <span class="display_currency"
                                                data-currency_symbol="true">{{ $line_total }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <th class="text-center" colspan="3">TOTAL PRODUCTS: {{count($purchase->purchase_lines)}}</th>
                                    <th class="text-center">TOTAL RETURN QTY<br/>{{ $total_return_qty }}</th>
                                    <th class="text-center">TOTAL UNIT PRICE<br/>$ {{ number_format($total_unit_price,2) }}</th>
                                    <th class="text-center">TOTAL LOOSE QTY<br/>{{ $total_loose_qty }}</th>
                                    <th class="text-center">TOTAL LOOSE PRICE<br/>$ {{ number_format($total_loose_price,2) }}</th>
                                    <th class="text-center">NET TOTAL<br/>$ {{ number_format($total_before_tax,2) }}</th>
                                </tr>
                            </tbody>
                            <tfoot>

                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-sm-12 col-xs-12 no-print">
                    <h4>{{ __('sale.payment_info') }}:</h4>
                </div>
                <div class="col-md-6 col-sm-12 col-xs-12 no-print">
                    <div class="table-responsive">
                        <table class="table">
                            <tr class="bg-green">
                                <th>#</th>
                                <th>{{ __('messages.date') }}</th>
                                <th>{{ __('Credit Memo No') }}</th>
                                <th>{{ __('sale.amount') }}</th>
                                <th>{{ __('sale.payment_mode') }}</th>
                                <th>{{ __('sale.payment_note') }}</th>
                            </tr>
                            @php
                                $total_paid = 0;
                                $total_advance = 0;
                            @endphp
                            @forelse($purchase->payment_lines as $payment_line)
                                @php
                                    $total_paid += $payment_line->amount;
                                    $total_advance += $payment_line->advance_amt;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ @format_date($payment_line->paid_on) }}</td>
                                    <td>{{ $payment_line->payment_ref_no }}</td>
                                    <td><span class="display_currency"
                                            data-currency_symbol="true">{{ $payment_line->amount }}</span></td>
                                    <td>{{ $payment_methods[$payment_line->method] ?? '' }}</td>
                                    <td>
                                        @if ($payment_line->note)
                                            {{ ucfirst($payment_line->note) }}
                                        @else
                                            --
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        @lang('purchase.no_payments')
                                    </td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12 col-xs-12">
                    <div class="table-responsive">
                        <table class="table">
                            <!-- <tr class="hide">
                    <th>@lang('purchase.total_before_tax'): </th>
                    <td></td>
                    <td><span class="display_currency pull-right">{{ $total_before_tax }}</span></td>
                  </tr> -->
                            <tr>
                                <th>@lang('purchase.net_total_amount'): </th>
                                <td></td>
                                <td><span class="display_currency pull-right"
                                        data-currency_symbol="true">{{ $total_before_tax }}</span></td>
                            </tr>
                            <tr>
                                <th>@lang('purchase.discount'):</th>
                                <td>
                                    <b>(-)</b>
                                    @if ($purchase->discount_type == 'percentage')
                                        ({{ $purchase->discount_amount }} %)
                                    @endif
                                </td>
                                <td>
                                    <span class="display_currency pull-right" data-currency_symbol="true">
                                        @if ($purchase->discount_type == 'percentage')
                                            {{ ($purchase->discount_amount * $total_before_tax) / 100 }}
                                        @else
                                            {{ $purchase->discount_amount }}
                                        @endif
                                    </span>
                                </td>
                            </tr>
                            {{-- <tr>
                                <th>Advance Amount:</th>
                                <td>
                                    <b>(+)</b>
                                </td>
                                <td>
                                    <span class="display_currency pull-right" data-currency_symbol="true">
                                        {{ $total_advance }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('purchase.purchase_tax'):</th>
                                <td><b>(+)</b></td>
                                <td class="text-right">
                                    @if (!empty($purchase_taxes))
                                        @foreach ($purchase_taxes as $k => $v)
                                            <strong><small>{{ $k }}</small></strong> - <span
                                                class="display_currency pull-right"
                                                data-currency_symbol="true">{{ $v }}</span><br>
                                        @endforeach
                                    @else
                                        0.00
                                    @endif
                                </td>
                            </tr> --}}
                            @if (!empty($purchase->shipping_charges))
                                <tr>
                                    <th>@lang('purchase.additional_shipping_charges'):</th>
                                    <td><b>(+)</b></td>
                                    <td><span
                                            class="display_currency pull-right">{{ $purchase->shipping_charges }}</span>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th>@lang('purchase.purchase_total'):</th>
                                <td></td>
                                <td><span class="display_currency pull-right"
                                        data-currency_symbol="true">{{ $purchase->final_total }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-5">
                    <strong>@lang('purchase.shipping_details'):</strong><br>
                    <p class="well well-sm no-shadow bg-gray">
                        @if ($purchase->shipping_details)
                            {{ $purchase->shipping_details }}
                        @else
                            --
                        @endif
                    </p>
                </div>
                <div class="col-sm-5">
                    <strong>@lang('purchase.additional_notes'):</strong><br>
                    <p class="well well-sm no-shadow bg-gray">
                        @if ($purchase->additional_notes)
                            {{ $purchase->additional_notes }}
                        @else
                            --
                        @endif
                    </p>
                </div>
                <div class="col-sm-2">
                    <strong>@lang('lang_v1.box_qty')</strong><br>
                    <p class="well well-sm no-shadow bg-gray">
                      {{$purchase->box_qty ?? 0}}
                    </p>
                </div>
            </div>

            {{-- Barcode --}}
            <div class="row print_section">
                <div class="col-xs-12">
                    <img class="center-block"
                        src="data:image/png;base64,{{ DNS1D::getBarcodePNG($purchase->ref_no, 'C128', 2, 30, [39, 48, 54], true) }}">
                </div>
            </div>

            <div class="row no-print">
                <div class="col-md-12">
                  <strong>Vendor Credit Memo Log Detail</strong>
                </div>
                <div class="col-md-12">
                  <table class="table table-condensed bg-gray">
                          <tr class="bg-green">
                        <th>User Name</th>
                        <th>Action</th>
                        <th>Message</th>
                        <th width="10%">Date And Time</th>
                      </tr>
                      @if($VendorcreditmemoActivityLog)
                      @foreach($VendorcreditmemoActivityLog as $log)
                      <tr>
                        <td>{{$log->first_name}}</td>
                        @if($log->description == 'added')
                          <td>Added</td>
                        @elseif($log->description == 'edited')
                          <td>Edited</td>
                        @endif
                        @php $str_array = explode(',',$log->message); @endphp
                        <td>
                        @foreach($str_array as $message)
                            @if($message!="")
                            # {{$message}}<br>
                            @endif
                        @endforeach
                        </td>
                        <td width="10%"> {{ Carbon\Carbon::parse($log->datetime)->format('d-m-Y G:i A') }}</td>
                      </tr>
                      @endforeach
                      @endif
                  </table>
                </div>
            </div>
        
        <div class="modal-footer">
            <a href="#" class="print-invoice btn btn-primary" data-href="{{action('PurchaseReturnController@printInvoice', [$purchase->id])}}"><i class="fa fa-print" aria-hidden="true"></i> @lang("messages.print")</a>

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
