<style>
    @page {
        margin: 0.5cm;
        margin-right: 0.7cm;
        size: letter landscape;
    }

    table {
        width: 100%;
    }

   @page thead {
        display: table-header-group;
        max-height: 100% !important;
    }
    table,
    h2,
    p,
    span th,
    td {
        border: 0px solid #000;
        border-collapse: collapse;
        color: #060606 !important;
        /*font-size: 11px !important;*/
        /* font-family: "Calibri (Body)"; */
    }


    td .tdclass {
        color: #060606 !important;
    }

    p {
        color: #060606 !important;
    }

    }

</style>

<body>
    <div style="padding: 0px 0px; margin-top: 0px;
    margin-bottom: 0px;
    margin-left: 0px;
    margin-right: 0px;" class="table-breaked">
        <table>
            <thead>
                <tr>
                    <th>
                        <table>
                            <tr>
                                <td style="border: none;width:33%;text-align: center;">
                                    <!-- Logo -->
                                    @if (!empty($receipt_details->logo))
                                        <img src="{{ $receipt_details->logo }}"
                                            class="img img-responsive center-block"
                                            style="width: 230px !important; margin-left: 0px;">
                                    @endif
                                    <!-- Header text -->
                                    @if (!empty($receipt_details->header_text))
                                        <div class="col-xs-12">
                                            {!! $receipt_details->header_text !!}
                                        </div>
                                    @endif
                                    <h2
                                        style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 25px; font-weight: 600; text-align: left;color: #060606">
                                        <!-- Shop & Location Name  -->
                                        @if (!empty($receipt_details->display_name))
                                            {{ $receipt_details->display_name }}
                                        @endif
                                    </h2>
                                    <p style="font-size: 13px; text-align:left;">
                                        @if (!empty($receipt_details->address))
                                            <h7 class="text-center" style="font-weight: bold;">
                                                {!! $receipt_details->address !!}
                                            </h7>
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
                                            <br>{{ $receipt_details->location_custom_fields }}
                                        @endif
                                    </p>
                                    <p>
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
                                    <p>
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
                                <td style="border: none;vertical-align: top; width: 43%;">
                                    <h2
                                        style="text-transform: uppercase; margin-bottom:3px;margin-top: 30px;font-size: 20px; color: #000000; font-weight: 600; text-align: center;">
                                    </h2>
                                </td>
                                <td style="border: none; vertical-align: top; padding-left: 18px; width: 20%;">
                                    <span style="font-size: 16px;">PACKING SLIP</span> <br>
                                    <br>
                                    <span style="font-weight: bold; font-size: 14px;">
                                        {{ $receipt_details->date_label }}:
                                        {{ $receipt_details->invoice_date }}
                                    </span>
                                    <br>
                                    <span style="/*margin-left:34%;*/ font-weight: bold; font-size: 14px;">
                                        <b>{!! $receipt_details->invoice_no_prefix !!}
                                            {{ $receipt_details->invoice_no }}</b>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </th>
                </tr>

                <tr>
                    <td>
                        <table>
                            <tr>
                                <td style="width: 45%;">
                                    <table>
                                        <tr>
                                            <td colspan="2" style="border: 1px solid;background-color: #c7c7c7;">
                                                <span
                                                    style="align-items: center;font-weight: bold;margin-left: 30%;font-size: 13px;">Customer
                                                    Information
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: 1px solid; width: 80px;background-color: #c7c7c7;">
                                                @if (!empty($receipt_details->customer_name))
                                                    <span
                                                        style="font-weight: 700; font-size: 13px;margin-left: 4px;                                                        ">{{ $receipt_details->customer_label }}
                                                    </span>:
                                                @endif
                                            </td>
                                            <td style="border: 1px solid;">
                                                @if (!empty($receipt_details->customer_name))
                                                    <span
                                                        style="font-weight: 700; font-size: 13px;margin-left: 3px;">{{ $receipt_details->customer_name }}
                                                        ({{ $receipt_details->contact_id }})</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: 1px solid; width: 80px;background-color: #c7c7c7;">
                                                <span
                                                    style="font-weight: 700; font-size: 13px;     margin-left: 4px;                                                ">Address:
                                                </span>
                                            </td>
                                            <td style="border: 1px solid; font-size:12px;">

                                                @if (!empty($receipt_details->address_line_1))
                                                    <span style="margin-left: 3px;">
                                                        {!! $receipt_details->address_line_1 !!}
                                                    </span>,
                                                @endif
                                                @if (!empty($receipt_details->address_line_2))
                                                    <span style="margin-left: 3px;">
                                                        {!! $receipt_details->address_line_2 !!}
                                                    </span>
                                                @endif

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
                                                        Mobile:
                                                        {{ $receipt_details->mobile }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>

                                    </table>
                                </td>
                                <td style=" padding-left: 10px; width: 60%;">
                                    <table>
                                        <tr>
                                            <td style="border: 1px solid;background-color: #c7c7c7; ">
                                                <span
                                                    style="align-items: center;font-weight: bold;margin-left: 30%;font-size: 13px;">
                                                    Order Note:
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: 1px solid;">
                                                @if (!empty($receipt_details->tex))
                                                    <span
                                                        style="font-weight: 600; margin-left:3px; font-size: 13px;margin-left: 4px;">Tax
                                                        ID:
                                                        {{ $receipt_details->tex }}</span>
                                                        @else
                                                       <span style="font-weight: 600; margin-left:3px; font-size: 13px;margin-left: 4px;">Tax
                                                        ID:
                                                         None</span>
                                                @endif
                                                @if (!empty($receipt_details->tobacco_license_no))
                                                    <span
                                                        style="font-weight: 600; margin-left: 15px; font-size: 13px; ;margin-left: 4px;">Tobacco
                                                        lic:{{ $receipt_details->tobacco_license_no }}</span>
                                                @else
                                                    <span
                                                        style="font-weight: 600; margin-left: 15px; font-size: 13px; ;margin-left: 4px;">Tobacco
                                                        lic:NONE</span>

                                                @endif <br>
                                                @if (!empty($receipt_details->note))
                                                    <span
                                                        style="font-weight: 600;margin-left: 1px; margin-left:3px; font-size: 13px; ;margin-left: 4px;">Driver
                                                        Note: {{ $receipt_details->note }}</span>
                                                        @else
                                                       <span style="font-weight: 600; margin-left:3px; font-size: 13px;margin-left: 4px;">Note:

                                                         None</span>
                                                @endif <br>
                                                @if (!empty($receipt_details->additional_notes))
                                                    <span
                                                        style="font-weight: bold; font-size: 14px; margin-left:3px; font-size: 13px; ;margin-left: 4px;">Order
                                                        Note :{{ $receipt_details->additional_notes }}
                                                    </span>
                                                    @else
                                                       <span style="font-weight: 600; margin-left:3px; font-size: 13px;margin-left: 4px;">Order
                                                        Note:
                                                         None</span>
                                                @endif

                                            </td>
                                        </tr>

                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 100%;">
                        <table style="border: none;">
                            <tr>
                                <td style="text-align: left;border:none">

                                    <br>
                                </td>
                            </tr>
                        </table>
                        <div class="row">
                            @includeIf('sale_pos.receipts.partial.common_repair_invoice')
                        </div>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
                        <table style="width: 100%;padding:2px;">
                            <thead>
                                <tr>
                                    <td
                                        style="font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; ">
                                        Sr.</td>
                                    <td
                                        style="font-weight: 700; text-align: center; vertical-align:top;border: 1px solid #808080; border-left: none;">
                                        Location
                                    </td>
                                    <td
                                        style="font-weight: 700;vertical-align:top; text-align: center; border: 1px solid #808080; border-left: none;">
                                        Barcode
                                    </td>
                                    <td
                                        style="font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; border-left: none;">
                                        Qty
                                    </td>
                                    <td
                                        style="font-weight: 700;vertical-align:top;border: 1px solid #808080; text-align: center; border-left: none;">
                                        {{ $receipt_details->table_product_label }}
                                    </td>

                                    <td
                                        style="font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; border-left: none;">
                                        {{ $receipt_details->table_unit_price_label }}
                                    </td>
                                    <td
                                        style="font-weight: 700;text-align: center; vertical-align:top;border: 1px solid #808080; ">
                                        {{ $receipt_details->table_subtotal_label }}
                                    </td>
                                </tr>
                            </thead>
                            <?php $sr = 1; ?>
                            <tbody id="tableData"></tbody>
                            <tbody id="mytable">
                                @php
                                    $mini_cigar_qty = 0;
                                    $regular_cigar_qty = 0;
                                    $mini_cigar = 0;
                                    $regular_cigar = 0;
                                    $subcategory_amounts = [];
                                @endphp
                                @forelse($receipt_details->lines as $line)
                                    <tr>
                                        <?php
                                        $obj = [];
                                        $is_present = false;
                                        $sub_category = '';
                                        $subcat_id = 0;
                                        if (@$line['cat']['name'] == 'CIGAR') {
                                            $line_total =  (float)str_replace(',', '', $line['line_total']);
                                            if ($line['sub_cat']) {
                                            $sub_category = $line['sub_cat']['name'];
                                            $subcat_id = $line['sub_cat']['id'];
                                            }
                                        if (count($subcategory_amounts) > 0) {
                                        for ($i = 0; $i < count($subcategory_amounts); $i++) { if
                                            ($subcategory_amounts[$i]['id']==$subcat_id) {
                                            $subcategory_amounts[$i]['amount']=(float)$subcategory_amounts[$i]['amount'] +
                                            $line_total; $is_present=true; } } if ($is_present==false) {
                                            $name=$line['cat']['name'] . '-' . $sub_category; $obj=array_merge($obj,
                                            ['id'=> $subcat_id, 'amount' => $line_total, 'name' => $name]);
                                            array_push($subcategory_amounts, $obj);
                                            }
                                            } else {
                                            $name = $line['cat']['name'] . '-' . $sub_category;
                                            $obj = array_merge($obj, ['id' => $subcat_id, 'amount' =>
                                            $line_total, 'name' => $name]);
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
                                            <td
                                                style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                                                {{ $sr++ }}
                                            </td>
                                            <td
                                                style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                                                @if (!empty($line['aisle']))
                                                    A:{{ $line['aisle'] }} @endif
                                                @if (!empty($line['rack']))
                                                    R:{{ $line['rack'] }}@endif @if (!empty($line['shelf']))
                                                        S:{{ $line['shelf'] }}@endif @if (!empty($line['bin'])) B:{{ $line['bin'] }}
                                                        @endif
                                            </td>
                                            <td
                                                style="vertical-align: top;padding-left: 5px;text-align: left; border: 1px solid #808080; ">

                                                    @if (!empty($line['sub_sku']))
                                                        {{ $line['sub_sku'] }}
                                                    @endif

                                                <!-- {{ $line['product_variation'] }} {{ $line['variation'] }}
                                        @if (!empty($line['sub_sku'])) {{ $line['sub_sku'] }} @endif @if (!empty($line['brand']))
                                        {{ $line['brand'] }} @endif @if (!empty($line['cat_code'])), {{ $line['cat_code'] }}@endif
                                        @if (!empty($line['product_custom_fields'])), {{ $line['product_custom_fields'] }} @endif -->
                                            </td>
                                            <td
                                                style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                                                {{ round($line['quantity']) }}
                                            </td>
                                            <td style="vertical-align: top;padding-left: 5px;text-align: left; border: 1px solid #808080; ">
                                                <!-- @if (!empty($line['image'])) <img src="{{ $line['image'] }}" alt="Image" width="50"
                                        style="float: left; margin-right: 8px;"> @endif -->



                                                    {{ $line['name'] }}
                                                <!-- {{ $line['product_variation'] }} {{ $line['variation'] }}
                                    @if (!empty($line['sub_sku'])) , {{ $line['sub_sku'] }} @endif @if (!empty($line['brand'])),
                                    {{ $line['brand'] }} @endif @if (!empty($line['cat_code'])), {{ $line['cat_code'] }}@endif
                                    @if (!empty($line['product_custom_fields'])), {{ $line['product_custom_fields'] }} @endif
                                    @if (!empty($line['sell_line_note']))
                                    <br>
                                    <small>
                                        <b>NOTE:</b> {{ $line['sell_line_note'] }}
                                    </small>
                                    @endif
                                    @if (!empty($line['lot_number']))<br> {{ $line['lot_number_label'] }}:
                                    {{ $line['lot_number'] }} @endif
                                    @if (!empty($line['product_expiry'])), {{ $line['product_expiry_label'] }}:
                                    {{ $line['product_expiry'] }} @endif

                                    @if (!empty($line['warranty_name'])) <br><small>{{ $line['warranty_name'] }} </small>@endif
                                    @if (!empty($line['warranty_exp_date'])) <small>-
                                        {{ @format_date($line['warranty_exp_date']) }} </small>@endif
                                    @if (!empty($line['warranty_description'])) <small>
                                        {{ $line['warranty_description'] ?? '' }}</small>@endif -->
                                            </td>


                                            <td
                                                style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                                                {{ $line['unit_price_inc_tax'] }}
                                            </td>
                                            <td
                                                style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                                                {{ $line['line_total'] }}
                                            </td>
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
                                </tbody>

                            </table>

                            <table style="width: 100%;margin-top: 8px;">
                                <tr>
                                    <td colspan="" style="text-align: center;border:none">
                                        <table style="border: none;">
                                            <tr>

                                                <!--<td style="vertical-align: top;width: 50%;border: none;">-->
                                                <!--<table>-->
                                                <!--    <tr>-->
                                                <!--        <td style="vertical-align: top;padding-left: 5px;text-align: left;">-->
                                                <!--        </td>-->
                                                <!--    </tr>-->
                                                <!--</table>-->
                                                <!--</td>-->

                                                <td style="width: 50%;vertical-align: top;border: none; ">
                                                    @if (!empty($receipt_details->payments))
                                                        @foreach ($receipt_details->payments as $payment)
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: right !important;">{{ $payment['method'] }}
                                    </td>
                                    <td class="text-right">{{ $payment['amount'] }}</td>
                                </tr>
                                @endforeach
                                @endif
                                <tr>
                                    <td>
                                        <table style="width: 47%;">


                                            @if ($regular_cigar > 0)
                                                <tr>
                                                    <td style="text-align: left">Regular Cigar:</td>
                                                    <td style="text-align: left">{{ $regular_cigar }} Box(es)</td>
                                                </tr>
                                            @endif
                                            @if ($regular_cigar_qty > 0)
                                                <tr>
                                                    <td style="text-align: left">Sticks of Regular:</td>
                                                    <td style="text-align: left">{{ $regular_cigar_qty }} Sticks</td>
                                                </tr>
                                            @endif
                                            @foreach ($subcategory_amounts as $amounts)
                                                <tr>
                                                    @if ($amounts['name'] == 'CIGAR-Regular')
                                                        <td style="text-align: left !important;">
                                                            {{ $amounts['name'] }}:
                                                        </td>
                                                        <td style="text-align: left !important;">
                                                            ${{ $amounts['amount'] }}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td> <br> </td>
                                                <td></td>
                                            </tr>
                                            @if ($mini_cigar > 0)
                                                <tr>
                                                    <td style="text-align: left ;width:120px;">Mini Cigar:</td>
                                                    <td style="text-align: left">{{ $mini_cigar }} Box(es)</td>
                                                </tr>
                                            @endif
                                            @if ($mini_cigar_qty > 0)
                                                <tr>
                                                    <td style="text-align: left">Sticks of Mini:</td>
                                                    <td style="text-align: left">{{ $mini_cigar_qty }} Sticks</td>
                                                </tr>
                                            @endif
                                            @foreach ($subcategory_amounts as $amounts)
                                                <tr>
                                                    @if ($amounts['name'] == 'CIGAR-Mini')
                                                        <td style="text-align: left !important;">
                                                            {{ $amounts['name'] }}:
                                                        </td>
                                                        <td style="text-align: left !important;">
                                                             $ {{number_format((float)$amounts['amount'], 2, '.', '')}}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                            <!-- Amount subcat wise  -->
                                        </table>
                                    </td>
                                    <td>
                                        <table style="margin-bottom: 55px;">
                                            @if (!empty($receipt_details->total_paid))
                                                <tr>
                                                    <th style="text-align: right !important;">
                                                        {!! $receipt_details->total_paid_label !!}
                                                    </th>
                                                    <td class="text-right">
                                                        {{ $receipt_details->total_paid }}
                                                    </td>
                                                </tr>
                                            @endif
                                            <!-- Total Due-->
                                            @if (!empty($receipt_details->total_due))
                                                <tr>
                                                    <th style="text-align: right !important;">
                                                        {!! $receipt_details->total_due_label !!} :
                                                    </th>
                                                    <td class="text-right">
                                                        {{ $receipt_details->total_due }}
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (!empty($receipt_details->all_due))
                                                <tr>
                                                    <th style="text-align: right !important;">
                                                        {!! $receipt_details->all_bal_label !!}
                                                    </th>
                                                    <td class="text-right">
                                                        {{ $receipt_details->all_due }}
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (!empty($receipt_details->total_quantity_label))
                                                <tr class="color-555">
                                                    <th style="width:70%" style="text-align: right !important;">
                                                        {!! $receipt_details->total_quantity_label !!}
                                                    </th>
                                                    <td class="text-right">
                                                        {{ $receipt_details->total_quantity }}
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <th style="width:70%; text-align: right !important;">
                                                    {!! $receipt_details->subtotal_label !!}
                                                </th>
                                                <td class="text-right">
                                                    {{ $receipt_details->subtotal }}
                                                </td>
                                            </tr>
                                            @if (!empty($receipt_details->total_exempt_uf))
                                                <tr>
                                                    <th style="width:70%" style="text-align: right !important;">
                                                        @lang('lang_v1.exempt')
                                                    </th>
                                                    <td class="text-right">
                                                        {{ $receipt_details->total_exempt }}
                                                    </td>
                                                </tr>
                                            @endif
                                            <!-- Shipping Charges -->
                                            @if (!empty($receipt_details->shipping_charges))
                                                <tr>
                                                    <th style="width:70% ; text-align: right !important;">
                                                        {!! $receipt_details->shipping_charges_label !!}
                                                    </th>
                                                    <td class="text-right">
                                                        {{ $receipt_details->shipping_charges }}
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (!empty($receipt_details->packing_charge))
                                                <tr>
                                                    <th style="width:70%; text-align: right !important;">
                                                        {!! $receipt_details->packing_charge_label !!}
                                                    </th>
                                                    <td class="text-right">
                                                        {{ $receipt_details->packing_charge }}
                                                    </td>
                                                </tr>
                                            @endif
                                            <!-- Discount -->
                                            @if (!empty($receipt_details->discount))
                                                <tr>
                                                    <th style="text-align: right !important;">
                                                        {!! $receipt_details->discount_label !!}
                                                    </th>

                                                    <td class="text-right">
                                                        (-) {{ $receipt_details->discount }}
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (!empty($receipt_details->reward_point_label))
                                                <tr>
                                                    <th style="text-align: right !important;">
                                                        {!! $receipt_details->reward_point_label !!}
                                                    </th>

                                                    <td class="text-right">
                                                        (-) {{ $receipt_details->reward_point_amount }}
                                                    </td>
                                                </tr>
                                            @endif

                                            <!-- Tax -->
                                            @if (!empty($receipt_details->tax))
                                                <tr>
                                                    <th style="text-align: right !important;">
                                                        {!! $receipt_details->tax_label !!}
                                                    </th>
                                                    <td class="text-right">
                                                        (+) {{ $receipt_details->tax }}
                                                    </td>
                                                </tr>
                                            @endif

                                            @if ($receipt_details->round_off_amount > 0)
                                                <tr>
                                                    <th style="text-align: right !important;">
                                                        {!! $receipt_details->round_off_label !!}
                                                    </th>
                                                    <td class="text-right">
                                                        {{ $receipt_details->round_off }}
                                                    </td>
                                                </tr>
                                            @endif
                                            <!-- state and city tax -->
                                            @foreach ($tax_details as $tr)
                                                <tr>
                                                    <th style="text-align: right !important;">
                                                        {{ $tr['name'] }}:
                                                    </th>
                                                    <td class="text-right">
                                                        $ {{ $tr['tax'] }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <!-- Total -->
                                            <tr>
                                                <th style="text-align: right !important;">
                                                    {!! $receipt_details->total_label !!}
                                                </th>
                                                <td class="text-right">
                                                    {{ $receipt_details->total }}
                                                    @if (!empty($receipt_details->total_in_words))
                                                        <br>
                                                        <small>({{ $receipt_details->total_in_words }})</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
            </table>
            <br>
            <table style="width: 70%;">
                <tr>
                    <td style="vertical-align: top;padding-left: 5px;text-align: left;">
                        <table>
                            <tr style="text-align: center;font-size: 12px;font-weight: bold;">
                                <td>
                                    We greatly appreciate your support and business! Customers are responsible for paying
                                    their
                                    Local, State & Federal excise taxes for applicable products.
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 12px; font-weight: 600; text-align: center; color: red !IMPORTANT;">
                                    We accept Cash, Check, Zelle, and Wire Transfers <br>
                                    Zelle: {{ config('business-info.zelle_email') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <br>
            <table style="width: 100%;">
                <tr>
                    <td>
                        <table style="width: 50%">
                            <tr style="border: 1px solid #808080;">
                                <td style="font-size: 12px;font-weight: 600; " colspan="2">
                                    Bank of America:
                                </td>

                            </tr>
                            <tr>
                                <td style="border: 1px solid #808080; width: 29%;">Account #</td>
                                <td style="border: 1px solid #808080;">483019753020</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #808080; width: 29%;">Routing #</td>
                                <td style="border: 1px solid #808080;">021000322</td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table style="width: 50%">
                            <tr style="border: 1px solid #808080;">
                                <td style="font-size: 12px;font-weight: 600; " colspan="2">
                                    TD Bank:
                                </td>

                            </tr>
                            <tr>
                                <td style="border: 1px solid #808080; width: 29%;">Account #</td>
                                <td style="border: 1px solid #808080;">4354363073</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #808080; width: 29%;">Routing #</td>
                                <td style="border: 1px solid #808080;">026013673</td>
                            </tr>
                        </table>


                    </td>
                </tr>
            </table>
            </td>
            </tr>
            </tbody>
            </table>
        </div>
    </body>
