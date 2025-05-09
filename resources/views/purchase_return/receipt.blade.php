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
        page-break-after: auto;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
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


    /*.loader {*/
    /*    border: 4px solid #f3f3f3;*/
    /*    border-radius: 100%;*/
    /*    border-top: 7px solid blue;*/
    /*    border-right: 7px solid green;*/
    /*    border-bottom: 7px solid red;*/
    /*    border-left: 7px solid pink;*/
    /*    width: 35px;*/
    /*    height: 35px;*/
    /*    -webkit-animation: spin 2s linear infinite;*/
    /*    animation: spin 2s linear infinite;*/
    /*}*/

    @-webkit-keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /*tr.order-list:after {*/
    /*content: '';*/
    /*position: absolute;*/
    /*top: 50%;*/
    /*left: 0;*/
    /*width: 100%;*/
    /*height: 1px;*/
    /*background: black;*/
    /*}*/
    /*tr.order-list{*/
    /*    position: relative;*/
    /*}*/


table { border-collapse: collapse; empty-cells: show; }

td { position: relative; }

tr.order-list td:before {
  content: " ";
  position: absolute;
  top: 50%;
  left: 0;
  border-bottom: 1px solid #111;
  width: 100%;
}

tr.order-list td:after {
  content: "\00B7";
  /*font-size: 1px;*/
}
</style>
<div style="padding: 0px 0px;">
    <input type="hidden" id="title_for_print" value="{{ $title ?? 'Vendor Credit Memo - ' . config('business-info.name') }}">
    <table style="width: 100%;border: none;">
        <tr>
            <td style="border: none;">
                <table style="width: 100%;border: none">
                    <tr>
                        <td style="text-align: right;vertical-align: top;">
                            <table style="border: none;width:100% ">

                                <tr>
                                    <td style="border: none;width:55%;">
                                        <table style="width: 120%; margin-bottom: 30px;">
                                            <tr>
                                                <td>
                                                </td>
                                                <td>
                                                    <h2
                                                        style="display:none;text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 16px; font-weight: 600; text-align: left;color: #060606">
                                                        Payable to: </h2>
                                                    <!--<h3-->
                                                    <!--    style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600; text-align: left;color: #060606">-->
                                                    <!--    </h3>-->
                                                    <h2
                                                        style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 18px; font-weight: 600; text-align: left;color: #060606">
                                                        <!-- Shop & Location Name  -->
                                                        @if (!empty($purchase->business->name))
                                                            {{ $purchase->business->name }}
                                                        @endif
                                                    </h2>
                                                    <p style="font-size: 13px; text-align:left;font-weight: 600;">
                                                        @if (!empty($purchase->location->landmark))
                                                            {{ $purchase->location->landmark }},{{ $purchase->location->city }},{{ $purchase->location->state }}
                                                            ,{{ $purchase->location->country }},{{ $purchase->location->zip_code }}
                                                        @endif
                                                        @if (!empty($purchase->location->mobile))
                                                            <br />{{ $purchase->location->mobile }}
                                                        @endif
                                                        @if (!empty($purchase->location->mobile) && !empty($purchase->location->website))
                                                            |
                                                        @endif
                                                        @if (!empty($purchase->location->website))
                                                            {{ $purchase->location->website }}
                                                        @endif
                                                        @if (!empty($purchase->location->location_custom_fields))
                                                            {{ $purchase->location->location_custom_fields }}
                                                        @endif
                                                    </p>
                                                    <!--<p style="font-size: 13px; text-align:left;font-weight: 600;">-->
                                                    <!--    @if (!empty($receipt_details->sub_heading_line1))-->
                                                    <!--        {{ $receipt_details->sub_heading_line1 }}-->
                                                    <!--    @endif-->
                                                    <!--    @if (!empty($receipt_details->sub_heading_line2))-->
                                                    <!--        <br>{{ $receipt_details->sub_heading_line2 }}-->
                                                    <!--    @endif-->
                                                    <!--    @if (!empty($receipt_details->sub_heading_line3))-->
                                                    <!--        <br>{{ $receipt_details->sub_heading_line3 }}-->
                                                    <!--    @endif-->
                                                    <!--    @if (!empty($receipt_details->sub_heading_line4))-->
                                                    <!--        <br>{{ $receipt_details->sub_heading_line4 }}-->
                                                    <!--    @endif-->
                                                    <!--    @if (!empty($receipt_details->sub_heading_line5))-->
                                                    <!--        <br>{{ $receipt_details->sub_heading_line5 }}-->
                                                    <!--    @endif-->
                                                    <!--</p>-->
                                                    <!--<p style="font-size: 13px; text-align:left;font-weight: 600;">-->
                                                    <!--    @if (!empty($receipt_details->tax_info1))-->
                                                    <!--        <b>{{ $receipt_details->tax_label1 }}</b>-->
                                                    <!--        {{ $receipt_details->tax_info1 }}-->
                                                    <!--    @endif-->

                                                    <!--    @if (!empty($receipt_details->tax_info2))-->
                                                    <!--        <b>{{ $receipt_details->tax_label2 }}</b>-->
                                                    <!--        {{ $receipt_details->tax_info2 }}-->
                                                    <!--    @endif-->
                                                    <!--</p>-->
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">

                                                <td colspan="2">
                                                    <h2
                                                        style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 16px; font-weight: 600; text-align: center;color: #060606">
                                                        Supplier Information </h2>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;" rowspan="2">
                                                <td colspan="2">
                                                    <!--@if (!empty($receipt_details->table_label) || !empty($receipt_details->table))-->
                                                    <!--    @if (!empty($receipt_details->table_label))-->
                                                    <!--        <b>{!! $receipt_details->table_label !!}</b>-->
                                                    <!--    @endif-->
                                                    <!--    {{ $receipt_details->table }}-->
                                                    <!--     Waiter info -->
                                                    <!--@endif-->
                                                     <!--customer info -->
                                                    @if (!empty($purchase->contact->name))
                                                        <h2
                                                            style="text-transform: uppercase; margin-bottom:6px;margin-top:0px;font-size: 14px; font-weight: 600; text-align: center;color: #060606">
                                                            {{ $purchase->contact->name }}
                                                            ({{ $purchase->contact->contact_id }})<br>
                                                    @endif
                                                    @if (!empty($purchase->contact->address_line_1))
                                                        {!! $purchase->contact->address_line_1 !!}
                                                    @endif
                                                    @if (!empty($purchase->contact->address_line_2))
                                                        {!! $purchase->contact->address_line_2 !!}
                                                    @endif
                                                    @if (!empty($purchase->contact->city))
                                                        {!! $purchase->contact->city !!}
                                                    @endif
                                                    @if (!empty($purchase->contact->state))
                                                        {!! $purchase->contact->state !!}
                                                    @endif
                                                    @if (!empty($purchase->contact->zip_code))
                                                        {{ $purchase->contact->zip_code }}
                                                    @endif <br>
                                                    @if (!empty($purchase->contact->mobile))
                                                        Mobile:
                                                        {{ $purchase->contact->mobile }}

                                                    @endif

                                                    <hr>
                                                     @if (!empty($purchase->contact->additional_notes))
                                                Order Note :{{ $purchase->contact->additional_notes }}
                                                    @else
                                        Order Note:
                                                         None
                                                @endif
                                                              </h2>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>

                                <td style="vertical-align: top; width:50px;">
                                    <table style="width: 75%; margin-left: 80px;margin-top:20px;"
                                        style="border: 1px solid #808080;">
                                        <tr style="border: 1px solid #808080;">
                                            <td style="text-align: center;">
                                                <h2
                                                    style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 20px; color: #000000; font-weight: 600;">
                                                    <!--@if (!empty($receipt_details->invoice_heading))-->
                                                    <!--    {!! $receipt_details->invoice_heading !!}-->
                                                    <!--@endif-->
                                                    Vendor Credit Memo
                                                </h2>
                                            </td>
                                        </tr>
                                        <tr style="border: 1px solid #808080;">
                                            <td style="text-align: left;">
                                                <h4
                                                    style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                    Credit Memo No:
                                                    <span
                                                        style="margin-left: 74px;" class="pull-right">{{ $purchase->ref_no }}</span>
                                                    <!--@if (!empty($receipt_details->types_of_service))-->
                                                        <!--<strong>{!! $receipt_details->types_of_service_label !!}:-->
                                                        <!--</strong>-->
                                                        <!--{{ $receipt_details->types_of_service }}-->
                                                        <!-- Waiter info -->
                                                        <!--@if (!empty($receipt_details->types_of_service_custom_fields))-->
                                                        <!--    @foreach ($receipt_details->types_of_service_custom_fields as $key => $value)-->
                                                        <!--        <br><strong>{{ $key }}:</strong>{{ $value }}-->
                                                        <!--    @endforeach-->
                                                        <!--@endif-->
                                                    <!--@endif-->
                                                </h4>
                                            </td>
                                        </tr>
                                        <tr style="border: 1px solid #808080;">
                                            <td style="text-align: left;">
                                                <h4
                                                    style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                    Invoice Date :
                                                    <span
                                                        style="margin-left: 17px;" class="pull-right">{{ $purchase->transaction_date }}</span>
                                                </h4>
                                            </td>
                                        </tr>
                                        <tr style="border: 1px solid #808080;">
                                            <td style="text-align: left;">
                                                <h4
                                                    style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                    INVOICE TOTAL :
                                                    <span
                                                        style="margin-left: 56px;" class="pull-right">
                                                        <span class="display_currency pull-right" data-currency_symbol="true"> {{ $purchase->final_total }} </span>
                                                        <!--@if (!empty($receipt_details->total_in_words))-->
                                                        <!--    <br>-->
                                                        <!--    <small>({{ $receipt_details->total_in_words }})</small>-->
                                                        <!--@endif-->
                                                    </span>
                                                </h4>
                                            </td>
                                        </tr>
                                        <tr style="border: 1px solid #808080;" >
                                            <td style="text-align: left;">
                                                <h4
                                                    style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                    Payment Status :
                                                    <span style="margin-left: 66px;" class="pull-right">
                                                        @if (!empty($purchase->payment_status))

                                                            {{ $purchase->payment_status }}
                                                        @endif
                                                    </span>
                                                </h4>
                                            </td>
                                        </tr>

                                        <tr style="border: 1px solid #808080;">
                                            <td style="text-align: left;">
                                                <h4
                                                    style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                    @if (!empty($purchase->contact->tax_number))
                                                        Tax ID:
                                                        <span
                                                            style="margin-left: 158px;" class="pull-right">{{ $purchase->contact->tax_number }}</span>
                                                    @else
                                                        Tax ID:
                                                        <span style="margin-left: 135px;" class="pull-right">None</span>
                                                    @endif
                                                </h4>
                                            </td>
                                        </tr>
                                        <tr style="border: 1px solid #808080;">
                                            <td style="text-align: left;">
                                                <h4
                                                    style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                    @if (!empty($purchase->contact->tobacco_license_no))
                                                        Tobacco
                                                        lic:
                                                        <span
                                                            style="margin-left: 113px;" class="pull-right">{{ $purchase->contact->tobacco_license_no }}</span>
                                                    @else
                                                        Tobacco lic:
                                                        <span style="margin-left: 83px;" class="pull-right">None</span>
                                                    @endif
                                                </h4>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                </table>



               <table style="width: 100%;padding:2px;">
                            <thead>
                                <tr>
                                    <td
                                        style="font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; ">
                                        Sr.</td>
                                    <!--<td-->
                                    <!--    style="font-weight: 700; text-align: center; vertical-align:top;border: 1px solid #808080; border-left: none;">-->
                                    <!--    Location-->
                                    <!--</td>-->
                                    <!--<td-->
                                    <!--    style="font-weight: 700;vertical-align:top; text-align: center; border: 1px solid #808080; border-left: none;">-->
                                    <!--    Barcode-->
                                    <!--</td>-->
                                    <td
                                        style="font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; border-left: none;">
                                        Product
                                    </td>
                                    <td
                                        style="font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; border-left: none;">
                                        Qty Return
                                    </td>
                                    <td
                                        style="font-weight: 700;vertical-align:top;border: 1px solid #808080; text-align: center; border-left: none;">
                                        Unit Price
                                    </td>

                                    <td
                                        style="font-weight: 700;vertical-align:top;border: 1px solid #808080; text-align: center; border-left: none;">
                                        Loose Pcs Qty
                                    </td>
                                    <td
                                        style="font-weight: 700;vertical-align:top;border: 1px solid #808080; text-align: center; border-left: none;">
                                        Loose Pcs Price
                                    </td>

                                    <td
                                        style="font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; border-left: none;">
                                        Total
                                    </td>

                                </tr>
                            </thead>
                            <?php $sr = 1; ?>
                            <tbody id="tableData"></tbody>
                            <tbody id="mytable">
                                @php
                                    $total_before_tax = 0;
                                    $mini_cigar_qty = 0;
                                    $regular_cigar_qty = 0;
                                    $mini_cigar = 0;
                                    $regular_cigar = 0;
                                    $subcategory_amounts = [];

                                    $total_return_qty = 0;
                                    $total_unit_price = 0;
                                    $total_loose_qty = 0;
                                    $total_loose_price = 0;

                                @endphp
                                @forelse($purchase->purchase_lines as $line)
                                    <tr>
                                            <td
                                                style="vertical-align: top;text-align: center; border: 1px solid #808080; ">
                                                {{ $sr++ }}
                                            </td>
                                            <td
                                                style="vertical-align: top;text-align: left; border: 1px solid #808080; ">
                                                {{ $line['product']['name'] }}
                                            </td>
                                             <td
                                                style="vertical-align: top;text-align: center; border: 1px solid #808080; ">
                                                {{  $line['inv_return'] }} {{ $line['product']['unit']['actual_name'] }}
                                            </td>
                                            <td style="vertical-align: top;text-align: center; border: 1px solid #808080; ">
                                                <span class="display_currency " data-currency_symbol="true"> {{ $line['purchase_price_inc_tax'] }} </span>
                                            </td>
                                            <td style="vertical-align: top;text-align: center; border: 1px solid #808080; ">
                                                {{ $line['loose_qty'] }} {{ $line['product']['unit']['actual_name'] }}
                                            </td>

                                            <td style="vertical-align: top;text-align: center; border: 1px solid #808080; ">
                                                <span class="display_currency" data-currency_symbol="true"> {{ $line['loose_price'] }}  </span>
                                            </td>


                                            <td
                                                style="vertical-align: top; border: 1px solid #808080;text-align: center; ">
                                                @php
                                                    // $line_total = $line['purchase_price_inc_tax'] * $line['inv_return'] ;
                                                    $line_total = $line['sub_total'];
                                                    $total_before_tax += $line_total;
                                                @endphp

                                                <span class="display_currency " data-currency_symbol="true" style=""> {{ $line_total }}</span>
                                            </td>

                                            @php
                                                $total_return_qty += $line['inv_return'];
                                                $total_unit_price += $line['box_price'];
                                                $total_loose_qty += $line['loose_qty'];
                                                $total_loose_price += $line['loose_price'];
                                            @endphp


                                    </tr>
                                    @if (!empty($line['modifiers']))
                                        @foreach ($line['modifiers'] as $modifier)
                                            <tr>
                                                <td>
                                                    {{ $modifier['name'] }} {{ $modifier['variation'] }}
                                                    @if (!empty($modifier['sub_sku'])),
                                                        {{ $modifier['sub_sku'] }} @endif
                                                    @if (!empty($modifier['cat_code']))
                                                        , {{ $modifier['cat_code'] }}@endif
                                                    @if (!empty($modifier['sell_line_note']))
                                                        ({{ $modifier['sell_line_note'] }}) @endif
                                                </td>
                                                <td class="text-right">{{ $modifier['quantity'] }}
                                                    {{ $modifier['units'] }} </td>
                                                <td class="text-right">{{ $modifier['unit_price_inc_tax'] }}</td>
                                                <td class="text-right">{{ $modifier['line_total'] }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @empty
                                        <tr>
                                            <td colspan="4">&nbsp;</td>
                                        </tr>
                                    @endforelse
                                    <tr>
                                        <th class="text-center" colspan="2">TOTAL PRODUCTS: {{count($purchase->purchase_lines)}}</th>
                                        <th class="text-center">TOTAL RETURN QTY<br/>{{ $total_return_qty }}</th>
                                        <th></th>
                                        <th class="text-center hide">TOTAL UNIT PRICE<br/>$ {{ number_format($total_unit_price,2) }}</th>
                                        <th class="text-center">TOTAL LOOSE QTY<br/>{{ $total_loose_qty }}</th>
                                        <th class="text-center" style="display: none;">TOTAL LOOSE PRICE<br/>$ {{ number_format($total_loose_price,2) }}</th>
                                        <th class="text-center" style="display: none;">NET TOTAL<br/>$ {{ number_format($total_before_tax,2) }}</th>
                                    </tr>
                                </tbody>

                            </table>


                </td>
                    <table style="width: 100%;margin-top: 8px;">
                    <tr>
                        <td style="text-align: center;border:none; width:71%;">
                            <table>
                                @if ($regular_cigar > 0)
                                    <tr style="border: 1px solid #808080;">
                                        <td style="text-align: left; border: 1px solid #808080;width:40px;"></td>

                                        <td style="text-align: left; border: 1px solid #808080; width:50px;">Regular Cigar:
                                        </td>
                                        @if ($regular_cigar_qty > 0)
                                            <td style="text-align: left; border: 1px solid #808080; width:50px;">Sticks for
                                                Regular:</td>
                                        @endif
                                        @foreach ($subcategory_amounts as $amounts)
                                            @if ($amounts['name'] == 'CIGAR-Regular')
                                                <td style="text-align: left; border: 1px solid #808080; width:50px;">
                                                    {{ $amounts['name'] }}:
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>

                                    <tr style="border: 1px solid #808080;">
                                        <td style="text-align: left; border: 1px solid #808080; width:40px;"> <span
                                                style="font-weight: 600;">Regular</span></td>

                                        <td style="text-align: left; border: 1px solid #808080; width:50px;"> <span
                                                style="font-weight: 600;">{{ $regular_cigar }}</span></td>
                                        @if ($regular_cigar_qty > 0)
                                            <td style="text-align: left; border: 1px solid #808080; width:50px;">
                                                {{ $regular_cigar_qty }}
                                            </td>
                                        @endif
                                        @foreach ($subcategory_amounts as $amounts)
                                            @if ($amounts['name'] == 'CIGAR-Regular')
                                                <td style="text-align: left; border: 1px solid #808080; width:50px;">
                                                  $ {{ number_format((float) $amounts['amount'], 2, '.', '') }}
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif
                                @if ($mini_cigar > 0)
                                    <tr style="border: 1px solid #808080;">
                                        <td style="text-align: left; border: 1px solid #808080;width:50px;"></td>

                                        <td style="text-align: left; border: 1px solid #808080;width:50px;">Mini Cigar:
                                        </td>
                                        @if ($mini_cigar_qty > 0)
                                            <td style="text-align: left; border: 1px solid #808080;width:50px;">Sticks for
                                                Mini:</td>
                                        @endif
                                        @foreach ($subcategory_amounts as $amounts)
                                            @if ($amounts['name'] == 'CIGAR-Mini')
                                                <td style="text-align: left; border: 1px solid #808080;width:50px;">
                                                    {{ $amounts['name'] }}:
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif
                                @if ($mini_cigar > 0)
                                    <tr style="border: 1px solid #808080;">
                                        <td style="text-align: left; border: 1px solid #808080;width:50px;"> <span
                                                style="font-weight: 600;">Mini</span></td>

                                        <td style="text-align: left; border: 1px solid #808080;width:50px;"> <span
                                                style="font-weight: 600;">{{ $mini_cigar }}</span></td>
                                        @if ($mini_cigar_qty > 0)
                                            <td style="text-align: left; border: 1px solid #808080;width:50px;">
                                                {{ $mini_cigar_qty }}
                                            </td>
                                        @endif
                                        @foreach ($subcategory_amounts as $amounts)
                                            @if ($amounts['name'] == 'CIGAR-Mini')
                                                <td style="text-align: left; border: 1px solid #808080;width:50px;">$
                                                    {{ number_format((float)$amounts['amount'], 2, '.', '') }}
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif
                          </table>
                          <div class="col-md-5 hide">
                            <table>
                                <tr style="border: 1px solid #808080;">
                                    <th style="width:75%;">@lang('lang_v1.box_qty')</th>
                                    <td style="width:25%;border: 1px solid #808080;">{{ $purchase->box_qty ?? 0 }}</td>
                                </tr>
                            </table>
                            <br/>
                        </div>
                          <table>
                            <tr>
                                <td style="font-weight: 600;">
                                  We greatly appreciate your support and business! Customers are
                                  responsible for paying their
                                  Local, State & Federal Excise taxes for applicable products.
                                </td>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <td style="font-weight: 600;">
                                  <span style="color: red !important; font-weight: 600;"> We accept Cash, Check,
                                      Zelle, and Wire Transfers</span>
                                </td>
                            </tr>
                        </table>
                        <table>
                            <tr style="border: 1px solid #808080;">
                                <td style="border: 1px solid #808080;"> <span style="color: red !important; font-weight: 600;">Zelle
                                        Account</span></td>
                                <td>{{ config('business-info.zelle_email') }}</td>
                            </tr>
                            <tr style="border: 1px solid #808080;display:none;">
                                <td style="border: 1px solid #808080;"> <span style="color: red !important; font-weight: 600;">TD Bank Account No
                                         #</span></td>
                                <td>4414232134</td>
                            </tr>
                            <tr style="border: 1px solid #808080;display:none;">
                                <td style="border: 1px solid #808080;"> <span style="color: red !important; font-weight: 600;">Routing No
                                        #</span></td>
                                <td>0260-13673</td>
                            </tr>
                        </table>
                        </td>



                      <td style="vertical-align: top;">
                        <table>
                            <tr >
                                <th style="border: 1px solid; text-align: right !important;">
                                   Subtotal (Excl Tax) :
                                </th>
                                <td class="text-right" style="border: 1px solid;">
                                  <span class="display_currency pull-right" data-currency_symbol="true">{{ $total_before_tax }}</span>
                                </td>
                            </tr>
                            <!-- Tax -->

                            <!-- shipping -->
                            <!-- Shipping Charges -->
                                    @if (!empty($purchase->shipping_charges))
                                    <tr >
                                        <th style="width:63%; text-align: right !important; border: 1px solid;">
                                            Shipping Charges :
                                        </th>
                                        <td class="text-right" style="border: 1px solid;">
                                           (+) <span class="display_currency pull-right" data-currency_symbol="true"> {{ $purchase->shipping_charges }} </span>
                                        </td>
                                    </tr>
                                    @else
                                    <tr>
                                        <th style="width:63%; text-align: right !important; border: 1px solid;">
                                            Shipping Charges :
                                        </th>
                                        <td class="text-right" style="border: 1px solid;">
                                           (+) $ 0.00
                                        </td>
                                    </tr>
                                    @endif

                            <!-- discount -->
                            @if (!empty($purchase->discount_amount))
                                <tr >
                                    <th style="text-align: right !important; border: 1px solid;">
                                        Discount :
                                    </th>

                                    <td class="text-right" style="border: 1px solid;">
                                        (-) <span class="display_currency pull-right" data-currency_symbol="true"> {{ $purchase->discount_amount }} </span>
                                    </td>
                                </tr>

                                @else
                                 <tr >
                                    <th style="text-align: right !important; border: 1px solid;">
                                        Discount :
                                    </th>

                                    <td class="text-right" style="border: 1px solid;">
                                      (-) $ 0.00
                                    </td>
                                </tr>
                            @endif

                        <!-- Total -->
                                <tr>
                                    <th style="text-align: right !important; border: 1px solid;">
                                        Total :
                                    </th>
                                    <td class="text-right" style="border: 1px solid;">
                                        $ {{ number_format((float) $purchase->final_total , 2, '.', ',') }}
                                        <!--@if (!empty($purchase->total_in_words))-->
                                        <!--<br>-->
                                        <!--<small>({{ $purchase->total_in_words }})</small>-->
                                        <!--@endif-->
                                    </td>
                                </tr>


                            </table>
                        </td>

                        </td>
                    </tr>
                </table>
                </td>
            </tr>
        </table>
        </tr>
        </table>
        <!--   <footer>
                    <hr>
                    <table style="text-align: center;">
                        <tr>

                        </tr>
                    </table>
                </footer> -->
    </div>
