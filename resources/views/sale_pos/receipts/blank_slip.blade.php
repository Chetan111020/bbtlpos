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
    /*    content: '';*/
    /*    position: absolute;*/
    /*    top: 50%;*/
    /*    left: 0;*/
    /*    width: 100%;*/
    /*    height: 1px;*/
    /*    background: black;*/
    /*}*/
    /*tr.order-list{*/
    /*    position: relative;*/
    /*}*/


    table {
        border-collapse: collapse;
        empty-cells: show;
    }

    td {
        position: relative;
    }

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
        font-size: 1px;
    }
</style>


<body>
    <div style="padding: 0px 0px; margin-top: 0px;
    margin-bottom: 0px;
    margin-left: 0px;
    margin-right: 0px;"
        class="table-breaked">
        <table>
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
                                    <td class="hide"
                                        style="font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; ">
                                        Sr.</td>
                                    <td class="hide"
                                        style="font-weight: 700; text-align: center; vertical-align:top;border: 1px solid #808080; border-left: none;">
                                        Location
                                    </td>
                                    <td class="hide"
                                        style="font-weight: 700;vertical-align:top; text-align: center; border: 1px solid #808080; border-left: none;">
                                        Barcode
                                    </td>
                                    <td
                                        style=" width: 10%; font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080;">
                                        Quantity
                                    </td>
                                    <td
                                        style="font-weight: 700;vertical-align:top;border: 1px solid #808080;padding-left:5px;">
                                        {{ $receipt_details->table_product_label }}
                                    </td>
                                    <td class="hide"
                                        style="font-weight: 700;vertical-align:top;border: 1px solid #808080; text-align: center; border-left: none;">
                                        Item Code
                                    </td>

                                    <td
                                        style="font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; border-left: none;">
                                        {{ $receipt_details->table_unit_price_label }}
                                    </td>
                                    <td class="hide"
                                        style="font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; border-left: none;">
                                        Unit Tax
                                    </td>
                                    <td
                                        style="font-weight: 700;text-align: center; vertical-align:top;border: 1px solid #808080; ">
                                        {{ $receipt_details->table_subtotal_label }}
                                    </td>
                                </tr>
                            </thead>
                            <?php $sr = 1; ?>
                            <div id="mytable" class="loader" style="margin-left: 291px;"></div>
                            <tbody id="tableData"> </tbody>
                            <tbody id="mytable" style="display:none;">
                                @php
                                    $mini_cigar_qty = 0;
                                    $regular_cigar_qty = 0;
                                    $mini_cigar = 0;
                                    $regular_cigar = 0;
                                    $subcategory_amounts = [];

                                    $total_qty = 0;
                                    $total_unit_price = 0;
                                    $total_unit_tax = 0;
                                    $total_sub = 0;

                                @endphp
                                @forelse($receipt_details->lines_sorted_by_name as $line)
                                    <tr>
                                        <?php
                                        $line_qty = (float) str_replace(',', '', $line['quantity']);
                                        $line_unit_price = (float) str_replace(',', '', $line['unit_price']);
                                        $line_unit_tax = (float) str_replace(',', '', $line['unit_tax']);

                                        $total_qty += $line_qty;
                                        $total_unit_price += $line_unit_price;
                                        $total_unit_tax += $line_unit_tax * $line_qty;
                                        $total_sub += $line_unit_price * $line_qty;

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
                                        <td
                                            style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                                            {{ $sr++ }}
                                        </td>
                                        <td
                                            style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                                            @if (!empty($line['aisle']))
                                                A:{{ $line['aisle'] }}
                                            @endif
                                            @if (!empty($line['rack']))
                                                R:{{ $line['rack'] }}
                                                @endif @if (!empty($line['shelf']))
                                                    S:{{ $line['shelf'] }}
                                                    @endif @if (!empty($line['bin']))
                                                        B:{{ $line['bin'] }}
                                                    @endif
                                        </td>
                                        <td
                                            style="vertical-align: top;padding-left: 5px;text-align: left; border: 1px solid #808080; ">

                                            @if ($line['name'] == 'JUUL PS 3% CLASSIC MENTHOL -BOX OF 8')
                                                {{ '991137814390' }}
                                            @elseif(strpos($line['name'], 'ELF BAR DISPOSABLE 5000PF') !== false)
                                                {{ '68494038337' }}

                                            @elseif(isset($jadoo_products['barcode']) && !empty($jadoo_products['barcode']))
                                                @php
                                                    $final_line_name_barcode = '';
                                                @endphp
                                                @foreach ($jadoo_products['barcode'] as $match => $value)
                                                    @if (strpos(strtolower($line['name']), strtolower($match)) !== false)
                                                        @php
                                                            $final_line_name_barcode = $value;
                                                        @endphp
                                                    @break

                                                @elseif(strtolower($line['name']) == strtolower($match))
                                                    @php
                                                        $final_line_name_barcode = $value;
                                                    @endphp
                                                @break
                                            @endif
                                        @endforeach

                                        @if ($final_line_name_barcode != '')
                                            {{ $final_line_name_barcode }}
                                        @else
                                            @if (!empty($line['sub_sku']))
                                                {{ $line['sub_sku'] }}
                                            @endif
                                        @endif
                                    @else
                                        @if (!empty($line['sub_sku']))
                                            {{ $line['sub_sku'] }}
                                        @endif
                                    @endif

                                </td>
                                <td
                                    style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                                    {{ $line['quantity'] }}
                                </td>
                                {{-- <td
                                    style="vertical-align: top;padding-left: 5px;text-align: left; border: 1px solid #808080; ">

                                    {{ $line['name'] }}
                                </td> --}}
                                <td
                                                style="vertical-align: top;padding-left: 5px;text-align: left; border: 1px solid #808080; ">
                                            <?php $key="DESIGNER FLORAL DIAMOND PRINT WP –IFBR" ?>
                                            @if($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF PEACH -BOX OF 10')
                                            {{$key}}




                                            @elseif($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF LYCHEE ICE -BOX OF 10')
                                            {{$key}}@elseif($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF BLUEBERRY ICE -BOX OF 10')
                                            {{$key}}@elseif($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF LUSH ICE -BOX OF 10'){{$key}}
                                            @elseif(strpos($line['name'],'1CUBANO')!== false){{$line['name']}}
                                            @elseif(strpos($line['name'],'C2LEAR')!== false){{$line['name']}}
                                            @elseif(strpos($line['name'],'YME EXTRA DISPOSABLE 3600PF')!== false) {{"45MM AMSTERDAM RAINBOW GRINDER 10CT BOX YED"}}

                                            @else
                                                @if(isset($jadoo_products['product']) && !empty($jadoo_products['product']))
                                                    @php
                                                        $final_line_name = "";
                                                    @endphp
                                                    @foreach($jadoo_products['product'] as $match => $value)
                                                        @if(strpos(strtolower($line['name']),strtolower($match))!== false)
                                                            @php
                                                                $final_line_name = $value;
                                                            @endphp
                                                            @break
                                                        @elseif(strtolower($line['name']) == strtolower($match))
                                                            @php
                                                                $final_line_name = $value;
                                                            @endphp
                                                            @break
                                                        @endif
                                                    @endforeach
                                                    @if($final_line_name!="")
                                                        {{$final_line_name}}
                                                    @else
                                                        {{$line['name']}}
                                                    @endif
                                                @else
                                                    {{$line['name']}}
                                                @endif
                                            @endif

                                            </td>
                                <td
                                    style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                                    @if (strpos($line['name'], '1CBANO') !== false)
                                        {{ $line['item_code'] }}
                                    @elseif(isset($jadoo_products['itemcode']) && !empty($jadoo_products['itemcode']))
                                        @php
                                            $final_line_name_itemcode = '';
                                        @endphp
                                        @foreach ($jadoo_products['itemcode'] as $match => $value)
                                            @if (strpos(strtolower($line['name']), strtolower($match)) !== false)
                                                @php
                                                    $final_line_name_itemcode = $value;
                                                @endphp
                                                @break

                                            @elseif(strtolower($line['name']) == strtolower($match))
                                                @php
                                                    $final_line_name_itemcode = $value;
                                                @endphp
                                                @break
                                            @endif
                                        @endforeach

                                    @if ($final_line_name_itemcode != '')
                                        {{ $final_line_name_itemcode }}
                                    @else
                                        @if (!empty($line['item_code']))
                                            {{ $line['item_code'] }}
                                        @endif
                                    @endif
                                @else
                                    @if (!empty($line['item_code']))
                                        {{ $line['item_code'] }}
                                    @endif
                            @endif
                        </td>
                        <td
                            style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                            {{ $line['unit_price_inc_tax'] }}
                        </td>
                        <td
                            style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                            @if (!empty($line['unit_tax']))
                                {{ $line['unit_tax'] }}
                            @else
                                0.00
                            @endif
                        </td>
                        <td
                            style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                            {{ $line['line_total'] }}
                        </td>
                        <td>{{ $line['pro_id'] }}</td>
                    </tr>
                    @if (!empty($line['modifiers']))
                        @foreach ($line['modifiers'] as $modifier)
                            <tr>
                                <td>
                                    {{ $modifier['name'] }} {{ $modifier['variation'] }}
                                    @if (!empty($modifier['sub_sku']))
                                        ,
                                        {{ $modifier['sub_sku'] }}
                                    @endif
                                    @if (!empty($modifier['cat_code']))
                                        , {{ $modifier['cat_code'] }}
                                    @endif
                                    @if (!empty($modifier['sell_line_note']))
                                        ({{ $modifier['sell_line_note'] }})
                                    @endif
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
                            <td></td>
                            <td>
                                <table style="margin-bottom: 55px;">
                                    <tr style="display:none">
                                        <th style="width:86%; text-align: right !important;">
                                            Total Unit Price:
                                        </th>
                                        <td class="text-right">
                                            ${{ number_format($total_unit_price, 2) }}
                                        </td>
                                    </tr>
                                    <tr style="display:none">
                                        <th style="width:86%; text-align: right !important;">
                                            Total Unit Tax:
                                        </th>
                                        <td class="text-right">
                                            ${{ number_format($total_unit_tax, 2) }}
                                        </td>
                                    </tr>
                                    <tr style="display:none">
                                        <th style="width:86%; text-align: right !important;">
                                            Unit Total:
                                        </th>
                                        <td class="text-right">
                                            ${{ number_format($total_sub + $total_unit_tax, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:86%; text-align: right !important;">
                                            Subtotal (Excl Tax):
                                        </th>
                                        <td class="text-right">
                                            {{ $receipt_details->subtotal }}
                                        </td>
                                    </tr>
                                    <!-- state and city tax -->
                                    @foreach ($tax_details as $tr)
                                        {{-- <tr>

                                            <th style="text-align: right !important;">
                                                {{ $tr['name'] }}:
                                            </th>
                                            <td class="text-right">
                                                $ {{ $tr['tax'] }}
                                            </td>

                                        </tr> --}}
                                    @endforeach
                                    <!-- Tax -->
                                    @if (!empty($receipt_details->tax))
                                        {{-- <tr>
                                            <th style="text-align: right !important;">
                                                {!! $receipt_details->tax_label !!}
                                            </th>
                                            <td class="text-right">
                                                (+) {{ $receipt_details->tax }}
                                            </td>
                                        </tr> --}}
                                    @endif
                                    <!-- Discount -->
                                    @if (!empty($receipt_details->discount))
                                        <tr>
                                            <th style="text-align: right !important;">
                                                {!! $receipt_details->discount_label !!}:
                                            </th>

                                            <td class="text-right">
                                                (-) {{ $receipt_details->discount }}
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
                                    @if (!empty($receipt_details->total_paid))
                                        <tr>
                                            <th style="text-align: right !important;">
                                                Total Paid:
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
</td>
</tr>
</tbody>
</table>
<h4 style="text-align: right;">{{ $blank_slip_number ?? '' }}</h4>
</div>
</body>
<script type="text/javascript">
    //for read the table data
    var details = $('tbody#mytable tr').map(function(i, row) {
        return {
            'Location': row.cells[1].textContent.trim(),
            'Barcode': row.cells[2].textContent.trim(),
            'Qty': Math.round(row.cells[3].textContent.toString().replace(',', '')),
            'product': row.cells[4].textContent.trim(),
            'item_code': row.cells[5].textContent.trim(),
            'Price': row.cells[6].textContent.trim(),
            'tax': row.cells[7].textContent.trim(),
            'Subtotal': row.cells[8].textContent.toString().replace(',', ''),
            'pro_id': row.cells[9].textContent.trim(),
        }
    }).get();
    //console.log(details);
    //for merging duplicates
    result = [];
    details.forEach(function(a) {
        if (!this[a.pro_id]) {
            this[a.pro_id] = {
                Location: a.Location,
                Barcode: a.Barcode,
                Qty: 0,
                product: a.product,
                item_code: a.item_code,
                Price: a.Price,
                tax: a.tax,
                Subtotal: 0.0
            };
            result.push(this[a.pro_id])
        }
        this[a.pro_id].Location != a.Location == null;
        this[a.pro_id].Qty += Math.round(a.Qty);
        this[a.pro_id].Subtotal += parseFloat(a.Subtotal);

        // result.push(a);

    }, Object.create(null));
    //console.log(result);
    //for display table with array of objects
    function renove() {
        $('#mytable').remove();

        window.focus();
        var sr = 0;
        var k = '<tbody>'
        for (i = 0; i < result.length; i++) {
            sr = sr + 1;
            if (result[i].Qty == 0) {
                k += '<tr class="order-list">';
            } else {
                k += '<tr>';
            }
            k += '<td class="hide" style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">' +
                sr +
                '</td>';
            k += '<td class="hide" style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">' +
                result[i].Location + '</td>';
            k += '<td class="hide" style="vertical-align: top;padding-left: 5px;text-align: left; border: 1px solid #808080; ">' +
                result[i].Barcode + '</td>';
            k += '<td style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">' +
                result[i].Qty + '</td>';
            k += '<td style="vertical-align: top;padding-left: 5px;text-align: left; border: 1px solid #808080; ">' +
                result[i].product + '</td>';
            k += '<td class="hide" style="vertical-align: top;padding-left: 5px;text-align: left; border: 1px solid #808080; ">' +
                result[i].item_code + '</td>';
            k += '<td style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">' +
                ' $ ' + result[i].Price + '</td>';
            k += '<td class="hide" style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">' +
                ' $ ' + result[i].tax + '</td>';
            k += '<td style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">' +
                ' $ ' + parseFloat(result[i].Subtotal).toFixed(2) + '</td>';
            k += '</tr>';
        }
        k += `<tr style="display:none;">
                    <th></th>
                    <th></th>
                    <th></th>
                    <th class="text-center" >Total QTY:<br/>{{ $total_qty }}</th>
                    <th class="text-center" >Total Products: ` + sr + `</th>
                    <th class="text-center" >Total Unit<br/>Price:<br/>${{ number_format($total_unit_price, 2) }}</th>
                    <th class="text-center" >Total Unit<br/>Tax:<br/>${{ number_format($total_unit_tax, 2) }}</th>
                    <th class="text-center" >Net Total:<br/>${{ number_format($total_sub, 2) }}</th>
                </tr>`;
        k += '</tbody>';
        document.getElementById('tableData').innerHTML = k;
    }
    // setInterval(function() {
    //     //console.log("Oooo Yeaaa!");
    renove();
    // }, 10000); //run this thang every 1 seconds
</script>
