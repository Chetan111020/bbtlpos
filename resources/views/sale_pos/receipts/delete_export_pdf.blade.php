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
        page-break-inside: auto;
        overflow: wrap;
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
        color: #060606;
    }

    td .tdclass {
        color: #060606;
    }

    p {
        color: #060606;
    }

    body {
        font-family: "Poppins", sans-serif;
        font-size: 12px;
        padding: 0px;
        margin: 0px;
        line-height: 16px;
    }

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

  @media only screen and (max-width: 480px) {
  .abcd {
   width: 176px;
   margin-left: 32px;
  }

</style>

    <table style="width: 100%;">
        <tr>
            <td style="border: none;">
                <table style="width: 100%;border: none">
                    <tr>
                        <td style="text-align: right;vertical-align: top;">
                            <table style="border: none;width:100% ">
                                <tr>
                                    <td style="border: none;width:70%;">
                                        <table style="width: 100%; margin-bottom: 50px;">
                                            <tr>
                                                <td>
                                                    @if (!empty($receipt_details->logo))
                                                        <img src="{{ $receipt_details->logo }}"
                                                            class="img img-responsive center-block"
                                                            style="width: 130px;">
                                                    @endif
                                                    <!-- Header text -->
                                                    @if (!empty($receipt_details->header_text))
                                                        <div class="col-xs-12">
                                                            {!! $receipt_details->header_text !!}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td style="text-align: left;">
                                                    <h2 style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 16px; font-weight: bold; text-align: left;color: #060606">
                                                        Payable to: </h2>
                                                    <h3 style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: bold; text-align: left;color: #060606">
                                                        {{ config('business-info.name') }}</h3>
                                                    <h2 style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 25px; font-weight: bold; text-align: left;color: #060606">
                                                        <!-- Shop & Location Name  -->
                                                        @if (!empty($receipt_details->display_name))
                                                            {{ $receipt_details->display_name }}
                                                        @endif
                                                    </h2>
                                                    <p style="font-size: 13px; text-align:left;font-weight: bold;">
                                                        @if (!empty($receipt_details->address))
                                                            {!! $receipt_details->address !!}
                                                        @endif
                                                        @if (!empty($receipt_details->contact))
                                                            <br />{{ $receipt_details->contact }}
                                                        @endif
                                                        @if (!empty($receipt_details->contact) && !empty($receipt_details->website))
                                                            |
                                                        @endif
                                                        @if (!empty($receipt_details->website))
                                                            {{ $receipt_details->website }}
                                                        @endif
                                                        @if (!empty($receipt_details->location_custom_fields))
                                                            {{ $receipt_details->location_custom_fields }}
                                                        @endif
                                                    </p>
                                                    <p style="font-size: 13px; text-align:left;font-weight: bold;">
                                                        @if (!empty($receipt_details->sub_heading_line1))
                                                            {{ $receipt_details->sub_heading_line1 }}
                                                        @endif
                                                        @if (!empty($receipt_details->sub_heading_line2))
                                                            <br>{{ $receipt_details->sub_heading_line2 }}
                                                        @endif
                                                        @if (!empty($receipt_details->sub_heading_line3))
                                                            <br>{{ $receipt_details->sub_heading_line3 }}
                                                        @endif
                                                        @if (!empty($receipt_details->sub_heading_line4))
                                                            <br>{{ $receipt_details->sub_heading_line4 }}
                                                        @endif
                                                        @if (!empty($receipt_details->sub_heading_line5))
                                                            <br>{{ $receipt_details->sub_heading_line5 }}
                                                        @endif
                                                    </p>
                                                    <p style="font-size: 13px; text-align:left;font-weight: bold;">
                                                        @if (!empty($receipt_details->tax_info1))
                                                            <b>{{ $receipt_details->tax_label1 }}</b>
                                                            {{ $receipt_details->tax_info1 }}
                                                        @endif

                                                        @if (!empty($receipt_details->tax_info2))
                                                            <b>{{ $receipt_details->tax_label2 }}</b>
                                                            {{ $receipt_details->tax_info2 }}
                                                        @endif
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">

                                                <td colspan="2" style="text-align: center;">
                                                    <h2 style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 16px; font-weight: bold; text-align: center;color: #060606">
                                                        Bill to: </h2>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;" rowspan="2">

                                                <td colspan="2" style="text-align: center;">
                                                    @if (!empty($receipt_details->table_label) || !empty($receipt_details->table))
                                                        @if (!empty($receipt_details->table_label))
                                                            <b>{!! $receipt_details->table_label !!}</b>
                                                        @endif
                                                        {{ $receipt_details->table }}
                                                        <!-- Waiter info -->
                                                    @endif
                                                    <!-- customer info -->
                                                    @if (!empty($receipt_details->customer_name))
                                                        <h2 style="text-transform: uppercase; margin-bottom:6px;margin-top:0px;font-size: 14px; font-weight: bold; text-align: center;color: #060606">
                                                            {{ $receipt_details->customer_name }}
                                                            ({{ $receipt_details->contact_id }})<br>
                                                    @endif
                                                    @if (!empty($receipt_details->address_line_1))
                                                        {!! $receipt_details->address_line_1 !!}
                                                    @endif
                                                    @if (!empty($receipt_details->address_line_2))
                                                        {!! $receipt_details->address_line_2 !!}
                                                    @endif
                                                    @if (!empty($receipt_details->city))
                                                        {!! $receipt_details->city !!}
                                                    @endif
                                                    @if (!empty($receipt_details->state))
                                                        {!! $receipt_details->state !!}
                                                    @endif
                                                    @if (!empty($receipt_details->zip_code))
                                                        {{ $receipt_details->zip_code }}
                                                    @endif <br>
                                                    @if (!empty($receipt_details->mobile))
                                                        Mobile:
                                                        {{ $receipt_details->mobile }}
                                                        </h2>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>

                                    </td>

                                    <td style="vertical-align: top; width:30%;">

                                        <table style="width: 100%;border: 1px solid #808080;">
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: center;">
                                                    <h2 style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 20px; color: #000000; font-weight: bold;">
                                                        @if (!empty($receipt_details->invoice_heading))
                                                            {!! $receipt_details->invoice_heading !!}
                                                        @endif
                                                    </h2>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: left;">
                                                    <h4 style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: bold;">
                                                        @if (!empty($receipt_details->invoice_no_prefix))
                                                            Invoice Number:
                                                        @endif
                                                        <span style="margin-left: 74px;">{{ $receipt_details->invoice_no }}</span>
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
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: left;">
                                                    <h4 style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: bold;">
                                                        Invoice {{ $receipt_details->date_label }} :
                                                        <span style="margin-left: 17px;">{{ $receipt_details->invoice_date }}</span>
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: left;">
                                                    <h4 style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: bold;">
                                                        Invoice {!! $receipt_details->total_label !!}
                                                        <span style="margin-left: 56px;">{{ $receipt_details->total }}
                                                            @if (!empty($receipt_details->total_in_words))
                                                                <br>
                                                                <small>({{ $receipt_details->total_in_words }})</small>
                                                            @endif
                                                        </span>
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: left;">
                                                    <h4 style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: bold;">
                                                        Payment Status :
                                                        <span style="margin-left: 66px;">
                                                            @if (!empty($receipt_details->statuss))

                                                                {{ $receipt_details->statuss }}
                                                            @endif
                                                        </span>
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: center;">
                                                    <span>
                                                        <div style="padding: 5px;text-align: center;">
                                                            @if($qrcode!="")
                                                            <img src="{{$qrcode}}">
                                                            @endif
                                                        </div>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: left;">
                                                    <h4 style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: bold;">
                                                        @if (!empty($receipt_details->tex))
                                                            Tax ID:
                                                            <span style="margin-left: 158px;">{{ $receipt_details->tex }}</span>
                                                        @else
                                                            Tax ID:
                                                            <span style="margin-left: 135px;">None</span>
                                                        @endif
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: left;">
                                                    <h4 style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: bold;">
                                                        @if (!empty($receipt_details->tobacco_license_no))
                                                            Tobacco
                                                            lic:
                                                            <span style="margin-left: 113px;">{{ $receipt_details->tobacco_license_no }}</span>
                                                        @else
                                                            Tobacco lic:
                                                            <span style="margin-left: 83px;">None</span>
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
                <div class="row">
                    @includeIf('sale_pos.receipts.partial.common_repair_invoice')
                </div>
            </td>
        </tr>
    </table>
                <table style="width: 100%;padding:2px;">
                    <thead>
                        <tr style="border: 1px solid #808080;">
                            <td style="font-weight: bold;text-align: left;vertical-align:top;border: 1px solid #808080; border-left: none;">
                                Sr</td>
                            <td style="font-weight: bold;vertical-align:top;border: 1px solid #808080; border-left: none;">
                                Product</td>
                            <td style="font-weight: bold;text-align: left;vertical-align:top;border: 1px solid #808080; border-left: none;">
                                Quantity</td>
                            <td style="font-weight: bold;text-align: left;vertical-align:top;border: 1px solid #808080; border-left: none; width: 70px;">
                                Unit Price</td>
                            <td style="font-weight: bold;text-align: left;vertical-align:top;border: 1px solid #808080; border-left: none; width: 70px;">
                                Unit Tax</td>
                            <td style="font-weight:bold;text-align:left;vertical-align:top;border:1px solid #808080; border-right:none; width:70px;">
                                Subtotal</td>
                        </tr>
                    </thead>
                    <?php
                    $sr = 1;
                    ?>
                    <tbody id="mytable">
                        @php
                            $mini_cigar_qty = 0;
                            $regular_cigar_qty = 0;
                            $mini_cigar = 0;
                            $regular_cigar = 0;
                            $subcategory_amounts = [];
                        @endphp
                        @forelse($receipt_details->lines as $line)
                            <tr style="border: 1px solid #808080;">
                                <?php
                                $obj = [];
                                $is_present = false;
                                $sub_category = '';
                                $subcat_id = 0;
                                if (@$line['cat']['name'] == 'CIGAR') {
                                    $line_total = (float) str_replace(',', '', $line['line_total']);
                                    if ($line['sub_cat']) {
                                        $sub_category = $line['sub_cat']['name'];
                                        $subcat_id = $line['sub_cat']['id'];
                                    }
                                    if (count($subcategory_amounts) > 0) {
                                        for ($i = 0; $i < count($subcategory_amounts); $i++) {
                                            if ($subcategory_amounts[$i]['id'] == $subcat_id) {
                                                $subcategory_amounts[$i]['amount'] = (float) $subcategory_amounts[$i]['amount'] + $line_total;
                                                $is_present = true;
                                            }
                                        }
                                        if ($is_present == false) {
                                            $name = $line['cat']['name'] . '-' . $sub_category;
                                            $obj = array_merge($obj, ['id' => $subcat_id, 'amount' => $line_total, 'name' => $name]);
                                            array_push($subcategory_amounts, $obj);
                                        }
                                    } else {
                                        $name = $line['cat']['name'] . '-' . $sub_category;
                                        $obj = array_merge($obj, ['id' => $subcat_id, 'amount' => $line_total, 'name' => $name]);
                                        array_push($subcategory_amounts, $obj);
                                    }

                                    if ($sub_category == 'Mini') {
                                        $mini_cigar = $mini_cigar + round($line['quantity']);
                                    }
                                    if ($sub_category == 'Regular') {
                                        $regular_cigar = $regular_cigar + round($line['quantity']);
                                    }

                                    if ($line['qty_box'] > 0) {
                                        $sticks_qty = round($line['quantity']) * $line['qty_box'];
                                    }
                                    if ($sub_category == 'Mini') {
                                        $mini_cigar_qty = $mini_cigar_qty + $sticks_qty;
                                    }
                                    if ($sub_category == 'Regular') {
                                        $regular_cigar_qty = $regular_cigar_qty + $sticks_qty;
                                    }
                                }
                                ?>
                                <td style="vertical-align: top;padding-left: 5px;text-align: center;border: 1px solid #808080;">{{ $sr++ }}
                                </td>
                                <td style="vertical-align: top;padding-left: 5px;text-align: center;border: 1px solid #808080;">
                                {{$line['name']}}
                                {{$line['product_variation']}} {{$line['variation']}}
                                @if(!empty($line['sub_sku'])), {{$line['sub_sku']}} @endif @if(!empty($line['brand'])),
                                {{$line['brand']}} @endif @if(!empty($line['cat_code'])), {{$line['cat_code']}}@endif
                                @if(!empty($line['product_custom_fields'])), {{$line['product_custom_fields']}} @endif
                                @if(!empty($line['sell_line_note']))
                                <br>
                                <small>
                                    <b>NOTE:</b> {{$line['sell_line_note']}}
                                </small>
                                @endif
                                @if(!empty($line['lot_number']))<br> {{$line['lot_number_label']}}:
                                {{$line['lot_number']}} @endif
                                @if(!empty($line['product_expiry'])), {{$line['product_expiry_label']}}:
                                {{$line['product_expiry']}} @endif

                                @if(!empty($line['warranty_name'])) <br><small>{{$line['warranty_name']}} </small>@endif
                                @if(!empty($line['warranty_exp_date'])) <small>-
                                    {{@format_date($line['warranty_exp_date'])}} </small>@endif
                                @if(!empty($line['warranty_description'])) <small>
                                    {{$line['warranty_description'] ?? ''}}</small>@endif

                                </td>
                                <td style="vertical-align: top;padding-left: 5px;text-align: center;border: 1px solid #808080;">
                                    {{ round($line['quantity']) }}</td>
                                <td style="vertical-align: top;padding-left: 5px;text-align: center;border: 1px solid #808080;">$
                                    {{ $line['unit_price'] }}</td>
                                <td style="vertical-align: top;padding-left: 5px;text-align: center;border: 1px solid #808080;">
                                     @if (!empty($line['unit_tax']))
                                    ${{ $line['unit_tax'] }}
                                    @else
                                    $ 0.00
                                    @endif
                                </td>
                                <td style="vertical-align: top;padding-left: 5px;text-align: center;border: 1px solid #808080;">
                                    {{ $line['line_total_uf'] }}</td>
                            </tr>
                            @if (!empty($line['modifiers']))
                                @foreach ($line['modifiers'] as $modifier)
                                    <tr>
                                        <td>
                                            {{ $modifier['name'] }} {{ $modifier['variation'] }}
                                            @if (!empty($modifier['sub_sku'])),
                                                {{ $modifier['sub_sku'] }} @endif
                                            @if (!empty($modifier['cat_code'])),
                                                {{ $modifier['cat_code'] }}@endif
                                            @if (!empty($modifier['sell_line_note']))
                                                ({{ $modifier['sell_line_note'] }}) @endif
                                        </td>
                                        <td class="text-right" style="text-align: right;">{{ $modifier['quantity'] }}
                                            {{ $modifier['units'] }}
                                        </td>
                                        <td class="text-right" style="text-align: right;">{{ $modifier['unit_price_inc_tax'] }}</td>
                                        <td class="text-right" style="text-align: right;">{{ $modifier['unit_tax'] }}</td>
                                        <td class="text-right" style="text-align: right;">{{ $modifier['line_total'] }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            @empty
                                <tr>
                                    <td colspan="4">&nbsp;</td>
                                </tr>
                        </tbody>
                        @endforelse
                    </table>


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
                                        <td style="text-align: left; border: 1px solid #808080; width:40px;"> <span style="font-weight: bold;">Regular</span></td>

                                        <td style="text-align: left; border: 1px solid #808080; width:50px;"> <span style="font-weight: bold;">{{ $regular_cigar }}</span></td>
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
                                            @if ($amounts['name'] == 'CIGAR-MINI')
                                                <td style="text-align: left; border: 1px solid #808080;width:50px;">
                                                    {{ $amounts['name'] }}:
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif
                                @if ($mini_cigar > 0)
                                    <tr style="border: 1px solid #808080;">
                                        <td style="text-align: left; border: 1px solid #808080;width:50px;"> <span style="font-weight: bold;">Mini</span></td>

                                        <td style="text-align: left; border: 1px solid #808080;width:50px;"> <span style="font-weight: bold;">{{ $mini_cigar }}</span></td>
                                        @if ($mini_cigar_qty > 0)
                                            <td style="text-align: left; border: 1px solid #808080;width:50px;">
                                                {{ $mini_cigar_qty }}
                                            </td>
                                        @endif
                                        @foreach ($subcategory_amounts as $amounts)
                                            @if ($amounts == 'mini')
                                                <td style="text-align: left; border: 1px solid #808080;width:50px;">$
                                                    {{ number_format((float) $amounts['amount'], 2, '.', '') }}
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif
                          </table>

                          <table >
                            <tr>
                                <td style="font-weight: bold;">
                                  We greatly appreciate your support and business! Customers are
                                  responsible for paying their
                                  Local, State & Federal Excise taxes for applicable products.
                                </td>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <td style="font-weight: bold;">
                                  <span style="color: #ff0000; font-weight: bold;"> We accept Cash, Check,
                                      Zelle, and Wire Transfers</span>
                                </td>
                            </tr>
                        </table>
                        <table>
                            <tr style="border: 1px solid #808080;">
                                <td style="border: 1px solid #808080;"> <span style="color: #ff0000; font-weight: bold;">Zelle
                                        Account</span></td>
                                <td>{{ config('business-info.zelle_email') }}</td>
                            </tr>
                            <tr style="border: 1px solid #808080;">
                                <td style="border: 1px solid #808080;"> <span style="color: #ff0000; font-weight: bold;">Bank of America
                                        Account #</span></td>
                                <td>0101</td>
                            </tr>
                            <tr style="border: 1px solid #808080;">
                                <td style="border: 1px solid #808080;"> <span style="color: #ff0000; font-weight: bold;">TD Bank Account
                                        #</span></td>
                                <td>0101</td>
                            </tr>
                        </table>
                        </td>
                        <td style="vertical-align: top;padding-top: 10px;">
                        <table class="abcd">
                            <tr>
                                <th style="border: 1px solid; text-align: right;">
                                    {!! $receipt_details->subtotal_label !!}
                                </th>
                                <td class="text-right" style="border: 1px solid;text-align: right;" width="50%">
                                    {{ $receipt_details->subtotal }}
                                </td>
                            </tr>
                            <!-- Tax -->
                            <!--@if (!empty($receipt_details->tax))-->
                            <!--<tr >-->
                            <!--    <th style="border: 1px solid; text-align: right !important; ">-->
                            <!--        {!! $receipt_details->tax_label !!}-->
                            <!--    </th>-->
                            <!--    <td class="text-right" style="border: 1px solid;text-align: right;">-->
                            <!--        (+) {{ $receipt_details->tax }}-->
                            <!--    </td>-->
                            <!--</tr>-->
                            <!--@else-->
                            <!--<tr>-->
                            <!--    <th style="text-align: right !important; border: 1px solid;">-->
                            <!--        {!! $receipt_details->tax_label !!}-->
                            <!--    </th>-->
                            <!--    <td class="text-right" style="border: 1px solid;text-align: right;">-->
                            <!--        (+) 0.00-->
                            <!--    </td>-->
                            <!--</tr>-->
                            <!--@endif-->


                            @if ($receipt_details->round_off_amount > 0)
                            <tr >
                                <th style="text-align: right;" style="border: 1px solid;">
                                    {!! $receipt_details->round_off_label !!}
                                </th>
                                <td class="text-right" style="border: 1px solid;text-align: right;">
                                    {{ $receipt_details->round_off }}
                                </td>
                            </tr>


                            @endif
                            <!-- state and city tax -->
                            @foreach ($tax_details as $tr)
                            <tr>
                                <th style="text-align: right; border: 1px solid;">
                                    {{ $tr['name'] }}
                                </th>
                                <td class="text-right" style="border: 1px solid;text-align: right;">
                                    $ {{ $tr['tax'] }}
                                </td>
                            </tr>
                            @endforeach

                            <!-- shipping -->
                            <!-- Shipping Charges -->
                                    @if (!empty($receipt_details->shipping_charges))
                                    <tr style="text-align: right;">
                                        <th style="width:70%; text-align: right; border: 1px solid;">
                                            Shipping:
                                        </th>
                                        <td class="text-right" style="border: 1px solid;text-align: right;">
                                           (+) {{ $receipt_details->shipping_charges }}
                                        </td>
                                    </tr>
                                    @else
                                    <tr style="text-align: right;">
                                        <th style="width:70%; text-align: right; border: 1px solid;">
                                            Shipping:
                                        </th>
                                        <td class="text-right" style="border: 1px solid;text-align: right;">
                                           (+) 0.00
                                        </td>
                                    </tr>
                                    @endif
                                     <!-- discount -->
                            @if (!empty($receipt_details->discount))
                                <tr style="text-align: right;">
                                    <th style="text-align: right; border: 1px solid;">
                                        {!! $receipt_details->discount_label !!}
                                    </th>

                                    <td class="text-right" style="border: 1px solid;text-align: right;">
                                        (-) {{ $receipt_details->discount }}:
                                    </td>
                                </tr>

                                @else
                                 <tr style="text-align: right;">
                                    <th style="text-align: right; border: 1px solid;">
                                        {!! $receipt_details->discount_label !!}:
                                    </th>

                                    <td class="text-right" style="border: 1px solid;text-align: right;">
                                      (-) 0.00
                                    </td>
                                </tr>
                            @endif
                                    @if (!empty($receipt_details->all_due))
                                    <tr style="text-align: right;">
                                <th style="text-align: right; border: 1px solid;">
                                    {!! $receipt_details->all_bal_label !!}
                                </th>
                                <td class="text-right" style="border: 1px solid;text-align: right;">
                                    {{ $receipt_details->all_due }}
                                </td>
                            </tr>
                            @endif


                            @if (!empty($receipt_details->total_quantity_label))
                            <tr style="text-align: right;">
                                <th style="width:70%; text-align: right; border: 1px solid;">
                                    {!! $receipt_details->total_quantity_label !!}
                                </th>
                                <td class="text-right" style="border: 1px solid;text-align: right;">
                                    {{ $receipt_details->total_quantity }}
                                </td>
                            </tr>
                            @endif


                                @if (!empty($receipt_details->total_exempt_uf))
                                <tr style="text-align: right;">
                                    <th style="width:70%; text-align: right; border: 1px solid;">
                                        @lang('lang_v1.exempt')
                                    </th>
                                    <td class="text-right" style="border: 1px solid;text-align: right;">
                                        {{ $receipt_details->total_exempt }}
                                    </td>
                                </tr>
                                @endif

                                    @if (!empty($receipt_details->packing_charge))
                                <tr style="text-align: right;">
                                    <th style="width:70%; text-align: right; border: 1px solid;">
                                        {!! $receipt_details->packing_charge_label !!}:
                                    </th>
                                    <td class="text-right" style="border: 1px solid;text-align: right;">
                                        {{ $receipt_details->packing_charge }}
                                    </td>
                                </tr>
                                @endif



                                @if (!empty($receipt_details->reward_point_label))
                                <tr style="text-align: right;">
                                    <th style="text-align: right; border: 1px solid;">
                                        {!! $receipt_details->reward_point_label !!}
                                    </th>

                                    <td class="text-right" style="border: 1px solid;text-align: right;">
                                        (-) {{ $receipt_details->reward_point_amount }}
                                    </td>
                                </tr>
                                @endif
                            <!-- Total -->
                            <!-- Total -->
                                <tr style="text-align: right;">
                                    <th style="text-align: right; border: 1px solid;">
                                        {!! $receipt_details->total_label !!}
                                    </th>
                                    <td class="text-right" style="border: 1px solid;text-align: right;">
                                        {{ $receipt_details->total }}
                                        @if (!empty($receipt_details->total_in_words))
                                        <br>
                                        <small>({{ $receipt_details->total_in_words }})</small>
                                        @endif
                                    </td>
                                </tr>

                            @if (!empty($receipt_details->total_paid))
                            <tr style="text-align: right;">
                                <th style="text-align: right; border: 1px solid;">
                                    Total Paid:
                                </th>
                                <td class="text-right" style="border: 1px solid;text-align: right;">
                                    {{ $receipt_details->total_paid }}
                                </td>
                            </tr>
                            @else
                            <tr style="text-align: right;">
                                <th style="text-align: right; border: 1px solid;">
                                    Total Paid:
                                </th>
                                <td class="text-right" style="border: 1px solid;text-align: right;">
                                    0.00
                                </td>
                            </tr>
                            @endif

                            <!-- Total Due-->
                            <!-- @if (!empty($receipt_details->total_due))
                            <tr>
                                <th style="text-align: right !important; ">
                                    {!! $receipt_details->total_due_label !!}:
                                </th>
                                <td class="text-right" style="text-align: right;">
                                    {{ $receipt_details->total_due }}
                                </td>
                            </tr>
                            @endif -->
                            @if (!empty($receipt_details->total_due))
                                <tr style="text-align: right;">
                                    <th style="text-align: right; border: 1px solid;">
                                        Total Remaining:
                                    </th>
                                    <td class="text-right" style="border: 1px solid;text-align: right;">
                                        {{ $receipt_details->total_due }}
                                        @if (!empty($receipt_details->total_in_words))
                                        <br>
                                        <small>({{ $receipt_details->total_in_words }})</small>
                                        @endif
                                    </td>
                                </tr>
                            @else
                            <tr style="text-align: right;">
                                    <th style="text-align: right; border: 1px solid;">
                                        Total Remaining:
                                    </th>
                                    <td class="text-right" style="border: 1px solid;text-align: right;">
                                      0.00
                                      @if (!empty($receipt_details->total_in_words))
                                        <br>
                                        <small>({{ $receipt_details->total_in_words }})</small>
                                        @endif
                                    </td>
                                </tr>
                             @endif
                            </table>
                            <br>
                            <table class="abcd">
                                <tr style="border: 1px solid #808080;text-align: right;">
                                    <br>
                                    <th style="text-align: right; border: 1px solid #808080;">Credit:
                                    </th>
                                    @php
                                       if(!empty($receipt_details->credit_bln))
                                       {
                                            $balance = (int)$receipt_details->credit_bln;
                                            $credit_memo = getCreditBalance($receipt_details->contact_id);

                                            $credit = $balance + $credit_memo;
                                       }
                                       else
                                       {
                                            $credit = 0;
                                       }
                                    @endphp
                                    <td class="text-right" style="text-align: right;">
                                        ${{ number_format((float) $credit, 2) }}</td>

                                </tr>
                                <tr style="border: 1px solid #808080;text-align: right;">
                                    <th style="text-align: right; border: 1px solid #808080;">Open
                                        Balance: </th>
                                    <td class="text-right" style="text-align: right;">
                                        @if(!empty($receipt_details->opening))
                                            ${{ number_format((float) $receipt_details->opening, 2) }}
                                        @else
                                            0.00
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                        </tr>
                    </table>

        <!--   <footer>
                    <hr>
                    <table style="text-align: center;">
                        <tr>

                        </tr>
                    </table>
                </footer> -->