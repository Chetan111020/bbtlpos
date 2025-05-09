@extends('layouts.invoice_ui')
@section('title', $title)
@section('content')
    <div class="container">
        <div class="row d-print-none">
            <div class="col-md-8 offset-md-2 col-sm-12 d-flex justify-content-end py-3 px-0">
                @auth

                    <input type="hidden" id="tid" class="no-print" value="{{ $id }}">
                @endauth
                <button type="button" class="btn btn-primary no-print mx-3" id="print_invoice" aria-label="Print"><i
                        class="fas fa-print"></i> @lang('messages.print')
                </button>

            </div>
        </div>
        <div class="row">
            <div class="col-md-8 offset-md-2 col-sm-12" style="">
                <div class="spacer"></div>
                <br />
                <div id="invoice_content">
                    @php
                        $tax_total = 0;
                    @endphp
                    @foreach ($tax_details as $tr)
                        @php
                            $tax_total += (float) str_replace(',', '', $tr['tax']);
                        @endphp
                    @endforeach
                    <div class="row" style="position: relative;">
                        <img src="{{ config('business-info.logo_slim') }}"
                            style="position: absolute;width: 40%;height: auto;opacity: 0.1;left: 30%;"
                            alt="Business Logo" />
                        <div class="col-6">
                            {{-- <img src="{{ $receipt_details->logo }}" style="height:68px;" class="mx-2 my-auto" alt="Business Logo"/> --}}
                            <img src="{{ config('business-info.logo_slim') }}" style="height:68px;" class="my-auto" alt="Business Logo" />
                            <br />
                            <br />
                            <div class="text-muted" style="font-size: smaller;">
                                <span class="text-uppercase">
                                    <strong>{{ config('business-info.name') }}</strong> <br />
                                    {{ config('business-info.address_line_1') }}<br />
                                    {{ config('business-info.address_line_2') }}<br />
                                    <br />
                                </span>
                                <div class="row">
                                    <div class="col-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px;" viewBox="0 0 24 24"
                                            fill="currentColor" class="w-6 h-6">
                                            <path fill-rule="evenodd"
                                                d="M1.5 4.5a3 3 0 013-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 01-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 006.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 011.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 01-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="col-11">&nbsp;&nbsp;{{ config('business-info.mobile') }}</div>
                                    <div class="col-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px;" viewBox="0 0 24 24"
                                            fill="currentColor" class="w-6 h-6">
                                            <path
                                                d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z" />
                                            <path
                                                d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z" />
                                        </svg>
                                    </div>
                                    <div class="col-11">&nbsp;&nbsp;{{ config('business-info.email') }}</div>
                                    <div class="col-1">
                                        {{-- <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px;" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                        <path d="M21.721 12.752a9.711 9.711 0 00-.945-5.003 12.754 12.754 0 01-4.339 2.708 18.991 18.991 0 01-.214 4.772 17.165 17.165 0 005.498-2.477zM14.634 15.55a17.324 17.324 0 00.332-4.647c-.952.227-1.945.347-2.966.347-1.021 0-2.014-.12-2.966-.347a17.515 17.515 0 00.332 4.647 17.385 17.385 0 005.268 0zM9.772 17.119a18.963 18.963 0 004.456 0A17.182 17.182 0 0112 21.724a17.18 17.18 0 01-2.228-4.605zM7.777 15.23a18.87 18.87 0 01-.214-4.774 12.753 12.753 0 01-4.34-2.708 9.711 9.711 0 00-.944 5.004 17.165 17.165 0 005.498 2.477zM21.356 14.752a9.765 9.765 0 01-7.478 6.817 18.64 18.64 0 001.988-4.718 18.627 18.627 0 005.49-2.098zM2.644 14.752c1.682.971 3.53 1.688 5.49 2.099a18.64 18.64 0 001.988 4.718 9.765 9.765 0 01-7.478-6.816zM13.878 2.43a9.755 9.755 0 016.116 3.986 11.267 11.267 0 01-3.746 2.504 18.63 18.63 0 00-2.37-6.49zM12 2.276a17.152 17.152 0 012.805 7.121c-.897.23-1.837.353-2.805.353-.968 0-1.908-.122-2.805-.353A17.151 17.151 0 0112 2.276zM10.122 2.43a18.629 18.629 0 00-2.37 6.49 11.266 11.266 0 01-3.746-2.504 9.754 9.754 0 016.116-3.985z" />
                                    </svg> --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px;" viewBox="0 0 24 24"
                                            fill="currentColor" class="w-6 h-6">
                                            <path fill-rule="evenodd"
                                                d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 0 0 4.25 22.5h15.5a1.875 1.875 0 0 0 1.865-2.071l-1.263-12a1.875 1.875 0 0 0-1.865-1.679H16.5V6a4.5 4.5 0 1 0-9 0ZM12 3a3 3 0 0 0-3 3v.75h6V6a3 3 0 0 0-3-3Zm-3 8.25a3 3 0 1 0 6 0v-.75a.75.75 0 0 1 1.5 0v.75a4.5 4.5 0 1 1-9 0v-.75a.75.75 0 0 1 1.5 0v.75Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="col-11">&nbsp;&nbsp;{{ config('business-info.website_url_short') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <h1 class="display-6"><b>Credit Memo</b></h1>
                            <br />
                            <div class="row">
                                <small class="my-auto col-6 text-start">
                                    Credit Memo
                                    <strong>#{{ $receipt_details->invoice_no }}</strong>
                                    @if (!empty($receipt_details->types_of_service))
                                        <strong>{!! $receipt_details->types_of_service_label !!}:
                                        </strong>
                                        {{ $receipt_details->types_of_service }}
                                        <!-- Waiter info -->
                                        @if (!empty($receipt_details->types_of_service_custom_fields))
                                            @foreach ($receipt_details->types_of_service_custom_fields as $key => $value)
                                                <br><strong>{{ $key }}:</strong>{{ $value }}
                                            @endforeach
                                        @endif
                                    @endif
                                </small>
                                <small class="my-auto col-6 text-start">
                                    Date:
                                    <strong>{{ date('m/d/Y', strtotime($receipt_details->invoice_date)) }}</strong>
                                </small>
                                <small class="my-auto col-6 text-start">
                                    Subtotal:
                                    <strong>{{ $receipt_details->total }}
                                        @if (!empty($receipt_details->total_in_words))
                                            <br>
                                            <small>({{ $receipt_details->total_in_words }})</small>
                                        @endif
                                    </strong>
                                </small>
                                <small class="my-auto col-6 text-start">
                                    Payment Status:
                                    <strong>
                                        @if (!empty($receipt_details->statuss))
                                            {{ $receipt_details->statuss }}
                                        @endif
                                    </strong>
                                </small>
                                <small class="my-auto col-6 text-start">
                                    @if (!empty($receipt_details->tex))
                                        Tax ID:
                                        <strong>
                                            {{ $receipt_details->tex }}
                                        </strong>
                                    @else
                                        Tax ID:
                                        <strong>
                                            {{-- {{ None }} --}}
                                            None
                                        </strong>
                                    @endif

                                </small>
                                <small class="my-auto col-6 text-start">
                                    @if (!empty($receipt_details->tobacco_license_no))
                                        Tobacco lic:
                                        <strong>
                                            {{ $receipt_details->tobacco_license_no }}
                                        </strong>
                                    @else
                                        Tobacco lic:
                                        <strong>
                                            {{-- {{ None }} --}}
                                            None
                                        </strong>
                                    @endif

                                </small>
                            </div>
                            <br />
                            <div class="text-muted" style="font-size: smaller;">
                                <b>Invoice To</b><br />
                                <span class="text-uppercase">
                                    @if (!empty($receipt_details->customer_name))
                                        {{ $receipt_details->customer_name }}
                                        ({{ $receipt_details->contact_id }})
                                    @endif
                                    <br>
                                    @if (!empty($receipt_details->address_line_1))
                                        {!! $receipt_details->address_line_1 !!}
                                    @endif
                                    @if (!empty($receipt_details->address_line_2))
                                        {!! $receipt_details->address_line_2 !!}
                                    @endif
                                    @if (!empty($receipt_details->city))
                                        {!! $receipt_details->city !!}
                                    @endif
                                    <br />
                                    @if (!empty($receipt_details->state))
                                        {!! $receipt_details->state !!}
                                    @endif
                                    @if (!empty($receipt_details->zip_code))
                                        {{ $receipt_details->zip_code }}
                                    @endif
                                    <br />
                                    @if (!empty($receipt_details->email))
                                        {{ $receipt_details->email }} <br />
                                    @endif
                                </span>


                                @if (!empty($receipt_details->mobile))
                                    Mobile: {{ $receipt_details->mobile }}
                                @endif

                                <br />
                                Tobacco Lic:
                                {{ !empty($receipt_details->tobacco_license_no) ? strtoupper($receipt_details->tobacco_license_no) : 'None' }}
                                <br />
                                Tax ID: {{ !empty($receipt_details->tex) ? strtoupper($receipt_details->tex) : 'None' }}
                            </div>
                        </div>
                    </div>
                    <br />
                    <div class="d-flex" style="font-size: smaller;">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th style="">Sr no</th>
                                    <th style="width: 40%;">{{ $receipt_details->table_product_label }}</th>
                                    @if ($receipt_details->show_cat_code == 1)
                                        <th style="">{{ $receipt_details->cat_code_label }}</th>
                                    @endif
                                    <th class="text-end">{{ $receipt_details->table_qty_label }}</th>
                                    <th class="text-end">Unit Price (Box) /<br /> Unit Price (Piece)</th>
                                    <th class="text-end">Unit Tax</th>
                                    <th class="text-end">@lang('Garbage Quantity')</th>
                                    <th class="text-end">{{ $receipt_details->table_subtotal_label }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $total_products = 0;
                                    $total_qty_box = 0;
                                    $total_qty_pcs = 0;
                                    $total_price_box = 0;
                                    $total_price_pcs = 0;
                                    $total_state_tax = 0;
                                    $total_gar_qty = 0;
                                    $total_sub = 0;
                                @endphp
                                @foreach ($receipt_details->lines as $line)
                                    <tr>
                                        <td class="">{{ $loop->iteration }}</td>
                                        <td class="">
                                            {{ $line['name'] }} {{ $line['variation'] }}<br />
                                            @if (!empty($line['sub_sku']))
                                                {{ $line['sub_sku'] }}
                                                @endif @if (!empty($line['brand']))
                                                    {{ $line['brand'] }}
                                                @endif
                                                @if (!empty($line['sell_line_note']))
                                                    ({{ $line['sell_line_note'] }})
                                                @endif
                                        </td>
                                        @if ($receipt_details->show_cat_code == 1)
                                            <td>
                                                @if (!empty($line['cat_code']))
                                                    {{ $line['cat_code'] }}
                                                @endif
                                            </td>
                                        @endif
                                        <td class="text-end">
                                            @php
                                                $gar_box_qty =
                                                    isset($line['quantity']) && $line['quantity']
                                                        ? $line['quantity']
                                                        : 0;
                                                $gar_pcs_qty =
                                                    isset($line['gar_piece_return_qty']) &&
                                                    $line['gar_piece_return_qty']
                                                        ? $line['gar_piece_return_qty']
                                                        : 0;
                                                $per_box_price =
                                                    isset($line['unit_price_inc_tax']) && $line['unit_price_inc_tax']
                                                        ? $line['unit_price_inc_tax']
                                                        : 0.0;
                                                $per_pcs_price =
                                                    isset($line['gar_piece_return_price']) &&
                                                    $line['gar_piece_return_price']
                                                        ? $line['gar_piece_return_price']
                                                        : 0.0;
                                            @endphp
                                            {{ round($gar_box_qty) }} Box
                                            <!--<br/>-->

                                            {{ round($gar_pcs_qty) }} Piece
                                        </td>
                                        <td class="text-end">
                                            ${{ number_format($per_box_price, 2) }} Per Box<br />
                                            $ {{ number_format($per_pcs_price, 2) }} Per Piece
                                        </td>
                                        <td class="text-end">{{ @format_quantity($line['pos_line_tax_amount']) }}</td>
                                        <td class="text-end">{{ @format_quantity($line['gar_box_return_qty']) }}
                                            {{ $line['units'] }}</td>
                                        <td class="text-end">
                                            ${{ $line['line_total'] }}
                                        </td>
                                    </tr>
                                    @php
                                        $total_products++;
                                        $total_qty_box += $gar_box_qty;
                                        $total_qty_pcs += $gar_pcs_qty;
                                        $total_price_box += $per_box_price;
                                        $total_price_pcs += $per_pcs_price;
                                        $total_gar_qty += $line['gar_box_return_qty'];
                                        $total_state_tax += $line['pos_line_tax_amount'];
                                        $total_sub += str_replace(',','',$line['line_total']);
                                    @endphp
                                @endforeach
                                <tr>
                                    <th></th>
                                    <th class="">Total Products: {{ $total_products }}</th>
                                    <th class="text-end">Total QTY<br>{{ $total_qty_box }}
                                        Box,{{ $total_qty_pcs }} Piece</th>
                                    <th class="text-end">Total Unit Price<br>$
                                        {{ number_format($total_price_box, 2) }} Per Box<br>$
                                        {{ number_format($total_price_pcs, 2) }} Per Piece</th>
                                    <th class="text-end">Total Tax <br>$ {{ number_format($total_state_tax, 2) }}
                                    </th>
                                    <th class="text-end">Total Garbage QTY<br>{{ $total_gar_qty }}</th>
                                    <th class="text-end">Net Total<br>$ {{ number_format($total_sub, 2) }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex no-break-print" style="font-size: smaller;">
                        <div style="width: 60%;">
                            <strong>Packing Box Count: {{ $receipt_details->box_qty ?? 0 }}</strong>
                            <br />
                            {{-- <div class="hide">
                            <br/>
                            <strong>Payment Information</strong>
                            <br/>
                            <span>
                                Invoice Status: {{ ucwords($receipt_details->statuss) }}
                                @if ($receipt_details->statuss == 'paid')
                                    <svg xmlns="http://www.w3.org/2000/svg" style="height:15px;margin-top:-2px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="text-success">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" style="height:15px;margin-top:-2px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="text-warning">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    </svg>
                                @endif
                            </span>
                            <br/>
                            <span>
                                Open Balance Due: $ {{ number_format((float) $receipt_details->opening, 2) }}
                            </span>
                            <br/>
                        </div> --}}
                        </div>
                        <table class="table table-sm" style="width: 40% !important;height:100%;">
                            <tbody>
                                <tr>
                                    <th class="text-end">Subtotal</th>
                                    <td class="text-end" style="width:35%;">
                                        <b>{{ $receipt_details->subtotal }}</b>
                                    </td>
                                </tr>
                                @foreach ($tax_details as $tr)
                                    <tr>
                                        <th class="text-end">{{ $tr['name'] }}</th>
                                        <td class="text-end"><b>$ {{ $tr['tax'] }}</b></td>
                                    </tr>
                                @endforeach

                                {{-- @if (!empty($receipt_details->shipping_charges))
                            <tr>
                                <th class="text-end">Shipping Charges</th>
                                <td class="text-end"><b>{{ $receipt_details->shipping_charges }}</b></td>
                            </tr>
                            @endif --}}

                                @if (!empty($receipt_details->discount))
                                    <tr>
                                        <th class="text-end">Discount</th>
                                        <td class="text-end"><b>{{ $receipt_details->discount }}</b></td>
                                    </tr>
                                @endif


                                @if (!empty($receipt_details->group_tax_details))
                                    @foreach ($receipt_details->group_tax_details as $key => $value)
                                        <tr>
                                            <td>

                                                {!! $key !!}
                                            </td>
                                            <td class="text-end" style="">

                                                {{ $value }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    @if (!empty($receipt_details->tax))
                                        <tr>
                                            <th {!! $receipt_details->tax_label !!} </th>
                                            <td class="text-end">
                                                {{ $receipt_details->tax }}
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                                <tr>
                                    <th class="text-end">Invoice Total</th>
                                    <td class="text-end"><b>{{ $receipt_details->total }}</b></td>
                                </tr>

                                @if (!empty($paid_data->total_paid))
                                    <tr>
                                        <th class="text-end">Total Paid</th>
                                        <td class="text-end"><b>{{ $paid_data->total_paid }}</b></td>
                                    </tr>
                                @endif

                                @if (!empty($paid_data->total_due))
                                    <tr>
                                        <th class="text-end">Invoice Due</th>
                                        <td class="text-end"><b>{{ $paid_data->total_due }}</b></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div style="font-size: smaller;">
                        <br />
                        <span class="text-muted mb-1">
                            We greatly appreciate your support and business!
                            <br />
                        </span>
                        <small class="text-muted mb-1">
                            Customers are responsible for paying their Local, State & Federal Excise taxes for applicable
                            products.
                        </small>
                        <br />
                        <span>&nbsp;</span>
                    </div>
                </div>
                <div class="spacer"></div>
            </div>
        </div>
        <br />
        <div class="spacer"></div>
    </div>
@stop

@section('javascript')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript">
        //for send email.
        $(document).ready(function() {

        });

        //for printing page .
        $(document).ready(function() {
            var INTERNAL_PRINT_CALL = true;

            $(document).on('click', '#print_invoice', function() {
                INTERNAL_PRINT_CALL = false;
                $('#invoice_content').printThis({
                    afterPrint: function() {
                        INTERNAL_PRINT_CALL = true;
                    }
                });
            });

            $(document).bind("keyup keydown", function(e) {
                if (e.ctrlKey && e.keyCode == 80 && INTERNAL_PRINT_CALL) {
                    e.preventDefault();
                    $('#print_invoice').trigger('click');
                    return false;
                }
            });
        });
        @if (!empty(request()->input('print_on_load')))
            $(window).on('load', function() {
                $('#invoice_content').printThis();
            });
        @endif
    </script>
@endsection
