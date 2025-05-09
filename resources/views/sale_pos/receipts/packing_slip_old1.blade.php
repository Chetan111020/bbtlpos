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
<script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>
<div style="padding: 0px 0px;">
    <table style="width: 100%;border: none;">
        <tr>
            <td style="border: none;">
                <table style="width: 100%;border: none">
                    <tr>
                        <td style="text-align: right;vertical-align: top;">
                            <table style="border: none;width:100% ">

                                <tr>
                                    <td style="border: none;width:55%;">
                                        <table style="width: 120%; margin-bottomm: 50px;">
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
                                                <td>
                                                    <h2
                                                        style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 16px; font-weight: 600; text-align: left;color: #060606">
                                                        Payable to: </h2>
                                                    <h3
                                                        style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600; text-align: left;color: #060606">
                                                        {{ config('business-info.name') }}</h3>
                                                    <h2
                                                        style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 25px; font-weight: 600; text-align: left;color: #060606">
                                                        <!-- Shop & Location Name  -->
                                                        @if (!empty($receipt_details->display_name))
                                                            {{ $receipt_details->display_name }}
                                                        @endif
                                                    </h2>
                                                    <p style="font-size: 13px; text-align:left;font-weight: 600;">
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
                                                    <p style="font-size: 13px; text-align:left;font-weight: 600;">
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
                                                    <p style="font-size: 13px; text-align:left;font-weight: 600;">
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

                                                <td colspan="2">
                                                    <h2
                                                        style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 16px; font-weight: 600; text-align: center;color: #060606">
                                                        Bill to: </h2>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;" rowspan="2">

                                                <td colspan="2">
                                                    @if (!empty($receipt_details->table_label) || !empty($receipt_details->table))
                                                        @if (!empty($receipt_details->table_label))
                                                            <b>{!! $receipt_details->table_label !!}</b>
                                                        @endif
                                                        {{ $receipt_details->table }}
                                                        <!-- Waiter info -->
                                                    @endif
                                                    <!-- customer info -->
                                                    @if (!empty($receipt_details->customer_name))
                                                        <h2
                                                            style="text-transform: uppercase; margin-bottom:6px;margin-top:0px;font-size: 14px; font-weight: 600; text-align: center;color: #060606">
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

                                                    @endif

                                                    <hr>
                                                     @if (!empty($receipt_details->additional_notes))
                                                Order Note :{{ $receipt_details->additional_notes }}
                                                    @else
                                        Order Note:
                                                         None
                                                @endif
                                                              </h2>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td colspan="2" style="padding:0;">
                                                    <!--  <img src="/img/summer_sale.jpg" class="img img-responsive center-block" style="padding-bottom:10px;">   -->
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="vertical-align: top; width:50px;">
                                        <table style="width: 75%; margin-left: 80px;"
                                            style="border: 1px solid #808080;">
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: center;">
                                                    <h2
                                                        style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 20px; color: #000000; font-weight: 600;">
                                                        @if (!empty($receipt_details->invoice_heading))
                                                            {!! $receipt_details->invoice_heading !!}
                                                        @endif
                                                    </h2>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: left;">
                                                    <h4
                                                        style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                        @if (!empty($receipt_details->invoice_no_prefix))
                                                            Invoice Number:
                                                        @endif
                                                        <span
                                                            style="margin-left: 74px;" class="pull-right">{{ $receipt_details->invoice_no }}</span>
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
                                                    <h4
                                                        style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                        Invoice {{ $receipt_details->date_label }} :
                                                        <span
                                                            style="margin-left: 17px;" class="pull-right">{{ $receipt_details->invoice_date }}</span>
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: left;">
                                                    <h4
                                                        style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                        Invoice {!! $receipt_details->total_label !!}
                                                        <span
                                                            style="margin-left: 56px;" class="pull-right">{{ $receipt_details->total }}
                                                            @if (!empty($receipt_details->total_in_words))
                                                                <br>
                                                                <small>({{ $receipt_details->total_in_words }})</small>
                                                            @endif
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
                                                        <div style="padding: 5px; margin-left: 83px;" id="qrcode"></div>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: left;">
                                                    <h4
                                                        style="text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                        @if (!empty($receipt_details->tex))
                                                            Tax ID:
                                                            <span
                                                                style="margin-left: 158px;" class="pull-right">{{ $receipt_details->tex }}</span>
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
                                                        @if (!empty($receipt_details->tobacco_license_no))
                                                            Tobacco
                                                            lic:
                                                            <span
                                                                style="margin-left: 113px;" class="pull-right">{{ $receipt_details->tobacco_license_no }}</span>
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
                <div class="row">
                    @includeIf('sale_pos.receipts.partial.common_repair_invoice')
                </div>

                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>






                <table style="width: 100%;padding:2px;">
                    <thead>
                        <tr style="border: 1px solid #808080;">
                            <td
                                style="font-weight: 700;text-align: left;vertical-align:top;border: 1px solid #808080; border-left: none;">
                                Sr</td>
                            <td
                                style="font-weight: 700;vertical-align:top;border: 1px solid #808080; border-left: none;">
                                Product</td>
                            <td
                                style="font-weight: 700;text-align: left;vertical-align:top;border: 1px solid #808080; border-left: none;">
                                Quantity</td>
                            <td
                                style="font-weight: 700;text-align: left;vertical-align:top;border: 1px solid #808080; border-left: none; width: 70px;">
                                Unit Price</td>
                            <td
                                style="font-weight: 700;text-align: left;vertical-align:top;border: 1px solid #808080; border-left: none; width: 70px;">
                                Unit Tax</td>
                            <td
                                style="font-weight: 700;text-align: left;vertical-align:top;border: 1px solid #808080; border-right: none; width: 70px;">
                                Subtotal</td>
                        </tr>
                    </thead>
                    <?php
                    $sr = 1;
                    ?>

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
                        @forelse($receipt_details->lines as $line)
                            <tr style="border: 1px solid #808080; ">
                                <?php
                                $line_qty = (float) str_replace(',', '',$line['quantity']);
                                $line_unit_price = (float) str_replace(',', '',$line['unit_price']);
                                $line_unit_tax = (float) str_replace(',', '',$line['unit_tax']);

                                $total_qty += $line_qty;
                                $total_unit_price += $line_unit_price;
                                $total_unit_tax += ($line_unit_tax*$line_qty);
                                $total_sub += ($line_unit_price*$line_qty);

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
                                <td style="vertical-align: top;padding-left: 5px;text-align: left;">{{ $sr++ }}
                                </td>
                                <td style="vertical-align: top;padding-left: 5px;text-align: left;">
                            <?php $key="DESIGNER FLORAL DIAMOND PRINT WP –IFBR" ?>
                            @if(isset($print) ){{$line['name']}}

                            @elseif($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF PEACH -BOX OF 10')
                            {{$key}}
                            @elseif($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF LYCHEE ICE -BOX OF 10')
                            {{$key}}@elseif($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF BLUEBERRY ICE -BOX OF 10')
                            {{$key}}@elseif($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF LUSH ICE -BOX OF 10'){{$key}}
                            @elseif(strpos($line['name'],'CUBANO')!== false){{$line['name']}}
                            @elseif(strpos($line['name'],'CLEAR')!== false){{$line['name']}}@elseif($line['name'] == 'JUUL PS 3% CLASSIC MENTHOL -BOX OF 8')
                            {{"FANCY HAMMER GLASS BUBBLER 8CT JAR –JMB3"}}

                             @elseif(strpos($line['name'],'ELF BAR DISPOSABLE 5000PF')!== false)
                                            {{"53MM CANABIS DESIGN GRINDER 10CT BOX EBD"}}

                            @elseif($line['name'] == 'JUUL PS 3% CLASSIC MENTHOL- CASE OF 6')
                            {{"SMALL DOUBLE GLASS BUBBLER 48CT JAR –JMC3"}}
                            @elseif($line['name'] == 'JUUL PS 5% CLASSIC MENTHOL-BOX OF 8')
                            {{"FUMED JUMBO SWIRL SPOON PIPE 8CT JAR –JMB5"}}
                            @elseif($line['name'] == 'JUUL PS 5% CLASSIC MENTHOL- CASE OF 6')
                            {{"FUMED JUMBO SWIRL SPOON PIPE 48CT JAR –JMC5"}}
                            @elseif($line['name'] == 'JUUL PS 5% CLASSIC MENTHOL 2 PACK-BOX OF 8')
                            {{"FUMED REGULAR SWIRL SPOON PIPE 8CT JAR –JMB2PK5"}}
                            @elseif($line['name'] == 'JUUL PS 3% VIRGINIA TOBACCO-BOX OF 8')
                            {{"DOUBLE GLASS FUMED CHILLUM PIPE 8CT JAR –JVTB3"}}
                            @elseif($line['name'] == 'JUUL PS 3% VIRGINIA TOBACCO- CASE OF 6')
                            {{"DOUBLE GLASS FUMED CHILLUM PIPE 48CT JAR –JVTC3"}}
                            @elseif($line['name'] == 'JUUL PS 5% VIRGINIA TOBACCO-BOX OF 8')
                            {{"SWIRL CAN HAND PIPE 8CT JAR –JVTB5"}}
                            @elseif($line['name'] == 'JUUL PS 5% VIRGINIA TOBACCO-BOX OF 8')
                            {{"SWIRL CAN HAND PIPE 8CT JAR –JVTB5"}}
                            @elseif($line['name'] == 'JUUL PS 5% VIRGINIA TOBACCO- CASE OF 6')
                            {{"SWIRL CAN HAND PIPE 48CT JAR –JVTC5"}}
                            @elseif($line['name'] == 'JUUL PS 3% VIRGINIA TOBACCO 2 PACK-BOX OF 8')
                            {{"GRAV LABS BOWL WITH 10MM MALE 8CT JAR –JVTB2PK3"}}
                            @elseif($line['name'] == 'JUUL PS 5% VIRGINIA TOBACCO 2 PACK-BOX OF 8')
                            {{"GRAV LABS BOWL WITH  14MM MALE 48PC JAR –JVTB2PK5"}}
                            @elseif(strpos($line['name'],'TOBACCO')!== false){{$line['name']}}
                            @elseif($line['name'] == 'BLU PLUS TANK 2.4% MENTHOL-BOX OF 5')
                            {{"9 GLOWFY GLASS FLARED NECK BEAKER WP -MBPT"}}
                            @elseif($line['name'] == 'VUSE ALTO POD MENTHOL 1.8 0.2M')
                            {{"10 HAND BLOWN GLASS RECYCLER RED M18"}}

                            @elseif(strpos($line['name'],'JUUL STARTER KIT VIRGINIA TOBACCO 5% WITH 2 PK BOX'))
                            {{"8' COLORFUL BUBBLER 4CT JAR -JSKVT5"}}



                              @elseif(strpos($line['name'],'ZLAB PODS V')!== false)
                                            {{"5' V PRINT HAND PIPE 5CT JAR ZLVP5"}}





                                @elseif(strpos($line['name'],'HYPPE MAX SUPREME')!== false)
                                {{"6' HEAD GLASS FUME BODY RIM 10CT JAR -HMS"}}

                                @elseif(strpos($line['name'],'HYPPE MAX FLOW SUPREME')!== false)
                                {{"7' HEAD GLASS FUME BODY RIM 10CT JAR -HMFS"}}

                            @elseif($line['name'] == 'VUSE ALTO MENTHOL 2.4% 1CT')
                            {{"11 HAND BLOWN GLASS RECYCLER RED M241"}}
                            @elseif($line['name'] == 'VUSE ALTO POD MENTHOL 2.4 0.2M')
                            {{"11' HAND BLOWN GLASS RECYCLER GREEN M24"}}
                            @elseif($line['name'] == 'VUSE ALTO MENTHOL 2.4% 4COUNT')
                            {{"11' HAND BLOWN GLASS RECYCLER BLUE M244"}}
                            @elseif($line['name'] == 'VUSE ALTO MENTHOL 5% 1CT')
                            {{"12' HAND BLOWN GLASS RECYCLER RED M51"}}
                            @elseif($line['name'] == 'VUSE ALTO POD MENTHOL 5.0 0.2M')
                            {{"12 HAND BLOWN GLASS RECYCLER GREEN M5"}}
                            @elseif($line['name'] == 'VUSE ALTO MENTHOL 5% 4COUNT')
                            {{"12' HAND BLOWN GLASS RECYCLER BLUE M54"}}
                            @elseif($line['name'] == 'BLU DISPOSABLE 2.4% CHERRY CRUSH- BOX OF 5')
                            {{"3.5 RASTA COLOR HAND PIPE 5CT JAR -CCB"}}
                            @elseif($line['name'] == 'BLU DISPOSABLE 2.4% MAGINFICENT MENTHOL- BOX OF 5')
                            {{"3 FRIT GLOW HAND PIPE 7CT -MMB"}}
                            @elseif($line['name'] == 'BLU DISPOSABLE 2.4% POLAR MINT- BOX OF 5')
                            {{"4' MARIO GLASS HAND PIPE 8CT -PMB"}}
                            @elseif($line['name'] == 'MYBLU PS 2.4% MENTHOL-BOX OF 5')
                            {{"10' BEAKER WP RED -MMY"}}
                            @elseif($line['name'] == 'LGC PRO MET 20')
                            {{"7' BUBBLER SOFT GLASS 10CT JAR -LPRM"}}

                            @elseif(strpos($line['name'],'FUMEE DISPOSABLE 5%')!== false)
                            {{"4 GHOST FACE HAND PIPE 8CT JAR -FD"}}
                            @elseif(strpos($line['name'],'MYLE SLIM DISPOSABLE')!== false)
                            {{"PULSAR VAPOR VESSEL KIT V2 -MS"}}
                            @elseif(strpos($line['name'],'HYPPE MAX FLOW 2000PF 5%')!== false)
                            {{"4 HEAD GLASS FUME BODY RIM 40CT JAR HMF"}}
                            @elseif(strpos($line['name'],'HYPPE MAX 1600PF 5%')!== false)
                            {{"3' HEAD GLASS FUME BODY RIM 35CT JAR HM"}}

                            @elseif(strpos($line['name'],'AIR BAR DIAMOND DISPOSABLE')!== false)
                            {{"12.5' NEEK ASSORTED BEAKER RAINBOW ABD"}}

                            @elseif(strpos($line['name'],'AIR BAR LUX PLUS')!== false)
                            {{"6' HEAD FRIT DOUBLE RIM HAND PIPES WITH DOME 10CT -ABLP"}}

                            @elseif(strpos($line['name'],'AIR BAR LUX ')!== false)
                            {{"5' HEAD FRIT DOUBLE RIM HAND PIPES WITH DOME 13CT ABL"}}
                            @elseif(strpos($line['name'],'AIR BAR MAX')!== false)
                            {{"3' HEAD GLASS FUME BODY RIM 40CT JAR ABM"}}
                            @elseif(strpos($line['name'],'FLIQ XL DISPOSABLE')!== false)
                            {{"AMIRA HOOKAH 22' BOARDWALK BLUE FLK"}}
                            @elseif(strpos($line['name'],'HYDE EDGE DISPOSABLE')!== false)
                            {{"14' SOFT GLASS WATER PIPE HED"}}
                            @elseif(strpos($line['name'],'HYDE EDGE RECHARGE 3300PF')!==false)
                            {{"8' SOFT GLASS WATER PIPE HER"}}
                            @elseif(strpos($line['name'],'HYDE PLUS DISPOSABLE')!== false)
                            {{"9' SOFT GLASS WATER PIPE HPL"}}
                            @elseif(strpos($line['name'],'HYDE PLUS RECHARGE 3300PF')!== false)
                            {{"16 SOFT GLASS WATER PIPE HPR"}}
                            @elseif(strpos($line['name'],'KANGVAPE ONEE STICK')!== false)
                            {{"14' 7MM BEAKER ELECTRO PLATED DIAMOND SHINE KOS19"}}
                            @elseif(strpos($line['name'],'LUTO FAB DISPOSABLE')!== false)
                            {{"16' ART TALL WATER PIPE AMBER LTF"}}
                            @elseif(strpos($line['name'],'LUTO PRO XXL DISPOSABLE')!== false)
                            {{"12 WPS CONICAL MULTI STICKERS LPL"}}
                            @elseif(strpos($line['name'],'AIR BAR DISPOSABLE')!== false)
                            {{'MINIBK-10 10" MINI BEAKER AB'}}
                            @elseif(strpos($line['name'],'LUTO THUNDER DISPOSABLE')!== false)
                            {{"12 WATER PIPE CONICAL MULTI STICKERS/PGB 651 LTH"}}




                            @elseif(strpos($line['name'],'MYLE MINI 2 DISPOSABLE')!== false)
                            {{"WATER PRINT GLASS PIPE-ML2"}}
                            @elseif(strpos($line['name'],'MYLE MINI DISPOSABLE')!== false)
                            {{"10 BEAKER WP RED -MMY"}}
                            @elseif(strpos($line['name'],'FUMEE DISPOSABLE 5%')!== false)
                            {{"WATER PRINT GLASS PIPE-MM"}}
                            @elseif(strpos($line['name'],'R AND M DAZZLE 2000PF')!== false)
                            {{"14' 7MM BEAKER ELECTRO PLATED DIAMOND SHINE- RDZ"}}
                            @elseif(strpos($line['name'],'R AND M DAZZLE PRO 2600PF')!== false)
                            {{"12' 7MM BEAKER GLOW IN DARK MUSHROOM- RDP"}}
                            @elseif(strpos($line['name'],'R AND M FLEX 2600PF - BOX OF 10')!== false)
                            {{"20' MYSHROOM SHOWERHEAD LITE GREEN-RFL"}}
                            @elseif(strpos($line['name'],'STIG DISPOSABLE 6%')!== false)
                            {{"XP-001 5MM ARTIST GLASS - 5TG"}}
                            @elseif(strpos($line['name'],'GLAMEE NOVA DISPOSABLE')!== false)
                            {{"5' HEAD FRIT DOUBLE RIM HAND PIPES WITH DOME 25CT - GND"}}
                            @elseif(strpos($line['name'],'EON DISPOSABLE STIK 6.8%')!== false)
                            {{"1' THE SIMPSON BEAKER GREEN - ED"}}

                            @elseif(strpos($line['name'],'MYBLU PS 2.4% MENTHOL-BOX OF 5')!== false)
                            {{"10' BEAKER WP RED -MMY"}}

                            @elseif(strpos($line['name'],'LGC PRO MET 20')!== false)
                            {{"7' BUBBLER SOFT GLASS 10CT JAR -LPRM"}}

                            @elseif(strpos($line['name'],'LGC POWER MET 27')!== false)
                            {{"4.5' FRIT COLORFUL HANDPIPE-LPWM"}}



                            @elseif(strpos($line['name'],'GLAMEE NOVA DISPOSABLE')!== false)
                            {{"5 HEAD FRIT DOUBLE RIM HAND PIPES WITH DOME 25CT - GND"}}
                             @elseif(strpos($line['name'],'MYLE MINI DISPOSABLE')!== false)
                            {{"WATER PRINT GLASS PIPE-MM"}}
                             @elseif(strpos($line['name'],'MYLE MINI DISPOSABLE')!== false)
                             {{"14' DESIGNER FLORAL DIAMOND PRINT WP –IFBR"}}

                              @elseif(strpos($line['name'],'GST BOMB PLUS DISPOSABLE')!== false)
                             {{"12' WATER PIPE FANCY PRINT BEAKER -GBPD"}}

             @elseif(strpos($line['name'],'PACHA MAMA DISPOSABLE')!== false)
                                            {{"10' DESIGN HAND PIPE 10CT JAR -PMD"}}

                                                @elseif(strpos($line['name'],'FUME INFINITY DISPOSABLE')!== false)
                                            {{"10MM 45D MALE BANGER GLOW IN DARK 10CT JAR -FID"}}
                                                @elseif(strpos($line['name'],'HYDE N-BAR DISPOSABLE')!== false)
                                            {{"10MM 90D FEMALE BANGER GLOW IN DARK 10CT JAR -HNB"}}

                                              @elseif(strpos($line['name'],'GUMMY DISPOSABLE 1500PF ZERO NIC')!== false)

                                              {{"55MM BOB MARLEY WHOLEBODY GRINDER 12CT BOX GDZ"}}


              @elseif(strpos($line['name'],'CAVIAR NICOTINE DISPOSABLE')!== false)
                                            {{"12' GLOW IN DARK DESIGN HAND PIPE 12CT JAR -CND"}}
                                                         @elseif(strpos($line['name'],'LUTO MINI DISPOSABLE')!== false)
                                            {{"2' GLOW IN DARK DESIGN SUPER NOVA 20CT HAND PIPE JAR - LMD"}}
                                               @elseif(strpos($line['name'],'MYLE NANO')!== false)
                                            {{"6' DIAMOND CUT 10CT HAND PIPE JAR -MND"}}
                                               @elseif(strpos($line['name'],'GUMMY DISPOSABLE')!== false)
                                            {{"7' GUMMY BEAR HAND PIPE 10CT JAR -GD"}}
                                                 @elseif(strpos($line['name'],'LUTO PRO XXL 3000PF DISPOSABLE')!== false)
                                            {{"10' MULTY COLOR HEAVY HAND PIPE 10CT JAR -LPXXL3"}}

                                                 @elseif(strpos($line['name'],'UNO 4K DISPOSABLE')!== false)
                                            {{"8' GLOW IN DARK HAND PIPE WITH DOME 10CT -U4K"}}

                                                  @elseif(strpos($line['name'],'KANGVAPE ONEE MAX 5000PF')!== false)
                                            {{"5' MINIATURE COB PIPE 10CT -KOM"}}

                                              @elseif(strpos($line['name'],'HYDE EDGE RAVE DISPOSABLE')!== false)
                             {{"10' HEAVY HAND PIPE 10CT JAR -HERD"}}

                              @elseif(strpos($line['name'],'ESCO BARS FRUITIA DISPOSABLE')!== false)
                             {{"10' PABLO HEAVY HAND PIPE 10CT JAR - EBFD"}}

                              @elseif(strpos($line['name'],'VGOD POD 1K DISPOSABLE')!== false)
                             {{"10' V STYLE HEAVY HAND PIPE 10CT JAR -VP1KD"}}

                                @elseif(strpos($line['name'],'LUTO FAB PLUS DISPOSABLE 1200PF')!== false)
                             {{"12' 10MM HEAVY HAND PIPE 10CT JAR -LFPD"}}

                              @elseif(strpos($line['name'],'FUME ULTRA DISPOSABLE')!== false)
                                            {{"6' GHOST FACE HAND PIPE 10CT JAR -FUD"}}

                              @elseif(strpos($line['name'],'IVG MAX DISPOSABLE 2500PF')!== false)
                                            {{"10' 10MM HEAVY HAND PIPE 10CT JAR -IMD"}}



                                              @elseif(strpos($line['name'],'LUTO PRO XXL 6000PF DISPOSABLE 200CT DISPLAY')!== false)
                                            {{"20' ROOR WATER PIPE 6CT BUNDLE LPXXL6"}}

                                             @elseif(strpos($line['name'],'ALFK 2.5"')!== false)
                                            {{"2.5' COLOR HEAVY HAND PIPES 24CT JAR AFKHP25"}}

                                              @elseif(strpos($line['name'],'ALFK 5"')!== false)
                                            {{"5' COLOR HEAVY HAND PIPES 12CT JAR AFKHP5"}}

                                              @elseif(strpos($line['name'],'ALFK 10"')!== false)
                                            {{"10' COLOR HEAVY HAND PIPES 6CT JAR AFKHP10"}}


                                             @elseif(strpos($line['name'],'ADLY 2.5')!== false)
                                            {{"2.5' DICRO HAND PIPES 24CT JAR ALYHP24"}}

                                             @elseif(strpos($line['name'],'ADLY 5"')!== false)
                                            {{"5' DICRO HAND PIPES 12CT JAR ALYHP12"}}

                                             @elseif(strpos($line['name'],'AFZAL 2.5"')!== false)
                                            {{"2.5' DICRO MIXED COLOR HAND PIPES 24CT JAR AZHP24"}}

                                              @elseif(strpos($line['name'],'ESCO BARS RIPE DISPOSABLE')!== false)
                                            {{"10' PABLO HEAVY HAND PIPE 10CT JAR EBRD"}}

                                              @elseif(strpos($line['name'],'VFUN PLUS D1 DISPOSABLE 3500PF')!== false)
                                            {{"2' FUNNY PIC HEAVY HAND PIPE 35CT JAR VFD"}}


                                                 @elseif(strpos($line['name'],'HITT ACE RECHARGE 5000PF')!== false)
                                            {{"1.5' HEAVY HAND PIPE 50CT JAR HARD"}}

                                              @elseif(strpos($line['name'],'HITT X 3K 3000PF 3% NIC DISPOSABLE')!== false)
                                            {{"1.2' DICRO HAND PIPE 30CT JAR HXKD"}}


                                               @elseif(strpos($line['name'],'HYDE MAG DISPOSABLE')!== false)
                                            {{"10' HEAVY HAND PIPE 10CT JAR HMRD"}}

                                              @elseif(strpos($line['name'],'VFUN D1 DISPOSABLE 1000PF')!== false)
                                            {{"10' FUNNY DESIGN SWIRL PIPE 10CT JAR VFD10"}}

                                                @elseif(strpos($line['name'],'ESCO BARS PASTEL CARTEL MESH DISPOSABLE 2500PF')!== false)
                                            {{"2' PABLO SMALL HAND PIPE 25CT JAR EBPCD"}}

                                              @elseif(strpos($line['name'],'ESCO BARS PASTEL CARTEL MEGA DISPOSABLE 5000PF')!== false)
                                            {{"1.0' PABLO MINI HAND PIPE 50CT JAR EBPCMD"}}


                                              @elseif(strpos($line['name'],'T-BONE DISPOSABLE')!== false)
                                            {{"5' T-REX DESIGN HAND PIPE 20CT JAR TBD"}}


                                               @elseif(strpos($line['name'],'MR FOG MAX AIR DISPOSABLE 3000PF')!== false)
                                            {{"3.0' DESIGN HAND PIPE 30CT JAR MFMAD"}}

                                              @elseif(strpos($line['name'],'AIR FACTORY AIR STIX DISPOSABLE 3000PF')!== false)
                                            {{"3.0' DICRO HAND PIPE 30CT JAR AFASD"}}


                                             @elseif(strpos($line['name'],'WHIFF MAGNUM DISPOSABLE 3000PF')!== false)
                                            {{"2.5' PRIME HAND PIPE 30CT JAR WMD"}}

                                              @elseif(strpos($line['name'],'WHIFF OVER SIZE DISPOSABLE 2000PF')!== false)
                                            {{"3.5' PRIME HAND PIPE 20CT JAR WOSD"}}


                                             @elseif(strpos($line['name'],'LUTO EXTREME DISPOSABLE 6000PF')!== false)
                                            {{"20' HEAVY AFM DESIGN WATER PIPE -LEXD6"}}

                                              @elseif(strpos($line['name'],'DROP PLUS DISPOSABLE 2000PF')!== false)
                                            {{"10' DICRO DESIGN HEAVY HAND PIPE DPD2"}}

                                              @elseif(strpos($line['name'],'HYDE REBEL PRO DISPOSABLE 5000PF')!== false)
                                            {{"21' DRAGON PRINT GLASS PIPE HRPD5"}}


                                             @elseif(strpos($line['name'],'POD 5.5K DISPOSABLE 5500PF')!== false)
                                            {{"5.5' ROUND DESIGN HAND PIPE 10CT JAR P55D"}}

                                             @elseif(strpos($line['name'],'POD FLO DISPOSABLE 4000PF')!== false)
                                            {{"4' ROUND HEAD DESIGN HAND PIPE 10CT JAR PFD"}}

                                               @elseif(strpos($line['name'],'MILK PUFF DISPOSABLE 3000PF')!== false)
                                            {{"3' DICRO HAND PIPE 10CT JAR MPD3"}}

                                              @elseif(strpos($line['name'],'HYDE N-BAR MINI DISPOSABLE 2500PF')!== false)
                                            {{"63MM HEAVY DESIGN GRINDER 10CT BOX HNBMD"}}

                                             @elseif(strpos($line['name'],'BLOCK BAR DISPOSABLE 4500PF')!== false)
                                            {{"53MM RAINBOW DESIGN GRINDER 10CT BOX BBD"}}

                                             @elseif(strpos($line['name'],'LUTO XL DISPOSABLE 2000PF 0% NIC')!== false)
                                            {{"50MM R&M WHOLEBODY DESIGN GRINDER 10CT BOX LXLD0"}}

                                               @elseif(strpos($line['name'],'LUTO XL DISPOSABLE 2000PF')!== false)
                                            {{"10' HEAVY HAND PIPE 10CT JAR -LXL2"}}

                                              @elseif(strpos($line['name'],'LUTO XL DISPOSABLE 2000PF')!== false)
                             {{"20' 10MM HEAVY WATER PIPE DESIGN MIX COLOR -LXLD2"}}

                               @elseif(strpos($line['name'],'LUTO XL DISPOSABLE')!== false)
                            {{"12 WPS CONICAL MULTI STICKERS LTX"}}


                              @elseif(strpos($line['name'],'HYDE ID DISPOSABLE 4500PF')!== false)
                                            {{"65MM RAINBOW DESIGN GRINDER 10CT BOX HIDD"}}

                                              @elseif(strpos($line['name'],'ELF BAR AIRO MAX DISPOSABLE 5000PF')!== false)
                                            {{"50MM CANABIS DESIGN GRINDER 10CT BOX EBAMD"}}



                                              @elseif(strpos($line['name'],'LUTO VIP BOX DISPOSABLE 5000PF')!== false)
                                            {{"63MM SQUARESHAPE DESIGN GRINDER 63MM 10CT BOX LVBD"}}

                              @elseif(strpos($line['name'],'LUTO DISPOSABLE 1500PF')!== false)
                                            {{"50MM RAINBOW DESIGN GRINDER 10CT BOX LD15"}}

                                             @elseif(strpos($line['name'],'LUTO PRO DISPOSABLE 2200PF')!== false)
                                            {{"62MM AMSTERDAM DESIGN GRINDER 10CT BOX LPD22"}}

                                              @elseif(strpos($line['name'],'MYLE MICRO DISPOSABLE 1000PF')!== false)
                                            {{"50MM DESIGN GRINDER 12CT BOX MMD10"}}

                                             @elseif(strpos($line['name'],'AIR BAR M-LUX DISPOSABLE 2000PF')!== false)
                                            {{"10' HEAVY DICRO HAND PIPE 10CT JAR ABMLD"}}

                                             @elseif(strpos($line['name'],'BLNG DISPOSABLE 3300PF')!== false)
                                            {{"63MM BOB MARLEY HEAVY DESIGN GRINDER 10CT BOX BLNGD"}}
                                             @elseif(strpos($line['name'],'LUTO BIG SHOT DISPOSABLE 7000PF')!== false)
                                            {{"HEAVY DRAGON DESIGN GRINDER 65MM 24CT BOX LBSD"}}

                                              @elseif(strpos($line['name'],'WELL VERSED DISPOSABLE 3000PF')!== false)
                                            {{"AIR TIGHT JAR DESIGN ON TOP MEDIUM 10CT BOX WVD"}}

                                         @elseif(strpos($line['name'],'MYLE DRIP DISPOSABLE 2000PF')!== false)
                                            {{"63MM RICK & MORTY HEAVY DESIGN GRINDER 10CT BOX MDD20"}}

                                             @elseif(strpos($line['name'],'AIR BAR BOX 3000PF')!== false)

                                              {{"8' HAMMER DESIGN HEAVY HAND PIPE 5CT JAR ABB3"}}

                                                @elseif(strpos($line['name'],'ESCO BARS PASTEL CARTEL H2O DISPOSABLE 2500PF')!== false)
                                            {{"7' HEAVY HAND PIPE 10CT JAR EBPCHD"}}

                                             @elseif(strpos($line['name'],'AIR BAR BOX 5000PF')!== false)
                                            {{"65MM WINDOW DESIGN GRINDER 5 PART 12CT BOX ABB5"}}

                                               @elseif(strpos($line['name'],'AIR BAR BOX LIMITED EDITION 5000PF')!== false)
                                            {{"100MM WINDOW DESIGN GRINDER 5 PART 12CT BOX ABBLE5"}}

                                              @elseif(strpos($line['name'],'AIR BAR BOX NKD 100 MAX 3000PF')!== false)

                                              {{"7.5' TWIST DESIGN HEAVY HAND PIPE 5CT JAR ABBNM"}}

                                             @elseif(strpos($line['name'],'AIR BAR BOX')!== false)
                            {{"5.5' FUMED JUMBO SWIRL SPOON PIPE 10CT -ABB"}}



                              <!--delta jadoo START-->
                                              @elseif(strpos($line['name'],'CANNABISLIFE DELTA 8 THC DISPOSABLE')!== false)
                                            {{"10 US COLOR GLASS WATERPIPE W 14MM GLASS FUNNEL BOWL-CDETTD"}}

                                             @elseif(strpos($line['name'],'DAZED8 THCO LIVE RESIN CARTRIDGE')!== false)

                                              {{"19MM MALE MIXED BOWL WITH MARBLE 12CT JAR $ 72.00"}}

                                               @elseif(strpos($line['name'],'CATSKILL THCO CARTRIDGE')!== false){{"14MM CLEAR BOWL 25CT JAR-CTC"}}
                                         @elseif(strpos($line['name'],'CATSKILL THCO DISPOSABLE')!== false){{"14MM GLYCERIN BOWLS MIXED 10CT JAR $ 95.00-CTD"}}
                                         @elseif(strpos($line['name'],'DAZED8 THCO CARTRIDGE')!== false){{"14MM MALE ASSORTED COLOR BOWL 15CT JAR $32.00-DTC"}}
                                         @elseif(strpos($line['name'],'DAZED8 THCO LIVE RESIN DISPOSABLE')!== false){{"16 PHOENIX BEAKER WITH COLOR BOWL & DOWNSTEM GREEN $ 40.00-DTLRD"}}
                                         @elseif(strpos($line['name'],'DELTA MAN THCO CARTRIDGE')!== false){{"17 GLASS WATERPIPE W/ GLASS 14MM FUNNEL BOWL BLACK-DMTC"}}
                                         @elseif(strpos($line['name'],'GAS THCO DELTA 8 DISPOSABLE')!== false){{"BATMAN INSPIRED MALE BOWL 8CT JAR - BLACK - 14MM-GTDETD"}}
                                         @elseif(strpos($line['name'],'URB INFINITY LIVE RESIN THCO CARTRIDGE')!== false){{"BYO HOOKAH BOWL C22 $4.50-UILRTC"}}
                                         @elseif(strpos($line['name'],'URB INFINITY LIVE RESIN THCO DISPOSABLE')!== false){{"18MM MALE CLEAR HEAVY BOWL WITH DOUBLE MARBLES 15CT JAR $ 28.00-UILRTD"}}
                                         @elseif(strpos($line['name'],'URB LIVE RESIN THCO CARTRIDGE')!== false){{"19MM BOWL MIXED COLOR 20CT JAR $ 29.00-ULRTC"}}
                                         @elseif(strpos($line['name'],'URB LIVE RESIN THCO DISPOSABLE')!== false){{"ASSORTED COLOR BOWL 19MM MALE 12CT JAR $25.00-ULRTD"}}
                                         @elseif(strpos($line['name'],'CAKE HXC DISPOSABLE')!== false){{"GLOSSY HEAD HOOKAH BOWL $ 3.50-CHD"}}
                                         @elseif(strpos($line['name'],'CAKE LIVE HXC RESIN DISPOSABLE')!== false){{"GOLDEN DESERT ANIMAL HOOKAH BOWL EAGLE $6.25-CLHRD"}}
                                         @elseif(strpos($line['name'],'CATSKILL HHC CARTRIDGE')!== false){{"HEAVY BOWL WITH TRIPPLE MARBLE 14MM MALE 10CT JAR $ 80.00-CHC"}}
                                         @elseif(strpos($line['name'],'COLD PACK CAKE DELTA 8 HXC THC-P DISPOSABLE')!== false){{"19MM MALE FULL COLORED MARTINI CONE BOWL 12CT JAR-CPCDETHTD"}}
                                         @elseif(strpos($line['name'],'DAZED8 HHC CARTRIDGE')!== false){{"BYO KAMAN BOWL $1.30-DHC"}}
                                         @elseif(strpos($line['name'],'DAZED8 HHC DISPOSABLE')!== false){{"DREAM DIAMOND HEAD BOWL ALL $ 4.50-DHD"}}
                                         @elseif(strpos($line['name'],'DAZED8 HHC OVO CARTRIDGE')!== false){{"DREAM GLASS HEAD BOWL-DHOC"}}
                                         @elseif(strpos($line['name'],'DAZED8 HHC OVO DISPOSABLE')!== false){{"EL BADIA BOWL OBLAKO-DHOD"}}
                                         @elseif(strpos($line['name'],'DAZED8 HHC TITANZ DISPOSABLE')!== false){{"FC001 19 DESIGNER RIG WITH MALE BOWL $55-DHTD"}}
                                         @elseif(strpos($line['name'],'DELTA MAN HHC CARTRIDGE')!== false){{"DREAM SILICONE HOOKAH BOWL WITH STEEL SCREEN-DMHC"}}
                                         @elseif(strpos($line['name'],'DELTA MAN HHC DISPOSABLE')!== false){{"19MM MALE STRIPED MIXED BOWL WITH MARBLES 12CT JAR $  68.00-DMHD"}}
                                         @elseif(strpos($line['name'],'GAS HHC DISPOSABLE')!== false){{"19MM RASTA SWIRL BOWL MALE 12CT JAR $  27.00-GHD"}}
                                                             @elseif(strpos($line['name'],'PACKWOODS KIK HHC DISPOSABLE')!== false){{"5 GLASS WITH METAL BOWL-ZINC GRINDER -PKHD"}}

                                         @elseif(strpos($line['name'],'KIK HHC DISPOSABLE')!== false){{"3.5 METAL PIPE GOLD BOWL & SCREEN -BOX OF 21-KHD"}}
                                         @elseif(strpos($line['name'],'PACKWOODS FLO HHC CARTRIDGE')!== false){{"4 D&K GLASS PIPE WITH ROUND BOWL & SCREEN -BOX OF 12-PFHC"}}
                                         @elseif(strpos($line['name'],'PUFFY DELTA 8 D9 HHC DISPOSABLE')!== false){{"ASSORTED COLOR BOWL 19MM MALE 12CT JAR $25.00-PDETDHD"}}
                                         @elseif(strpos($line['name'],'STNR HHC DISPOSABLE')!== false){{"BATMAN INSPIRED MALE BOWL 8CT JAR - BLACK - 14MM-SHD"}}
                                         @elseif(strpos($line['name'],'STRICTLY DELTA HHC DISPOSABLE')!== false){{"BOWL 14MM STRIPED WITH DOUBLE MARBLE 14CT JAR-SDHD"}}
                                         @elseif(strpos($line['name'],'URB HHCO LIVE RESIN CARTRIDGE')!== false){{"BOWL 19MM COLOR 20CT JAR $ 40.00-UHLRC"}}
                                         @elseif(strpos($line['name'],'URB HHCO LIVE RESIN XL DISPOSABLE')!== false){{"BOWL 19MM MALE STRIPED WITH TRIPLE MARBLE 14CT JAR-UHLRXD"}}
                                         @elseif(strpos($line['name'],'CUPCAKE DELTA 8 DISPOSABLE')!== false){{"HILLSIDE QUARTZ BANGER 4MM FLAT TOP 90-14MM MALE -BOX OF 12-CDETD"}}
                                         @elseif(strpos($line['name'],'CAKE DELTA 10 CARTRIDGE ')!== false){{"14MM 45D FEMALE 4MM QUARTZ BANGER 20CT JAR-CD1C"}}
                                         @elseif(strpos($line['name'],'CAKE DELTA 10 DISPOSABLE')!== false){{"14MM 90D CLEAR THICK BASE MALE BANGER 15CT JAR-CD1D"}}
                                         @elseif(strpos($line['name'],'CAKE DELTA 10 LIVE RESIN DISPOSABLE')!== false){{"7 HIPSTER HANGER WITH BANGER MILK GREEN $ 22.00-CD1LRD"}}
                                         @elseif(strpos($line['name'],'CAKE DELTA 8 1010 CARTRIDGE')!== false){{"7 HIPSTER PIPE WITH BANGER BLUE $ 21.00-CDET1C"}}
                                         @elseif(strpos($line['name'],'CAKE DELTA 8 1010 KIT')!== false){{"BANGER 14MM MALE $4.00-CDET1K"}}
                                         @elseif(strpos($line['name'],'CAKE DELTA 8 DISPOSABLE ')!== false){{"BANGER 4MM QUARTZ 14MM 45D FEMALE 20CT JAR $ 38.00-CDETD"}}
                                         @elseif(strpos($line['name'],'CAKE DELTA 8 LIVE RESIN DISPOSABLE')!== false){{"COLOR BASED BANGER MALE 14MM 90D 15CT JAR-CDETLRD"}}
                                         @elseif(strpos($line['name'],'CAKE SUNDAE DELTA 8 DISPOSABLE')!== false){{"DOMELESS THERMAL BANGER 90D 14MM MALE 12CT JAR $ 50.00-CSDETD"}}
                                         @elseif(strpos($line['name'],'CALIFORNIA GOLD CALI DELTA 8 CARTRIDGE')!== false){{"FLAT TOP XL BANGER 14MM 90D MALE 12CT JAR $ 55.00-CGCDETC"}}
                                         @elseif(strpos($line['name'],'CATSKILL DELTA 8 DISPOSABLE')!== false){{"GLASS HOUSE HURRICANE BEVELED TOP QUARTZ BANGER (GHQ-8-90-14) $ 20.00-CDETD"}}
                                         @elseif(strpos($line['name'],'DELTA 8 CBD CARTRIDGE')!== false){{"HIPSTER GLYCERIN NECTAR COLLECTORS 5 PIECE SET WITH BANGERS BLACK-DETCC"}}
                                         @elseif(strpos($line['name'],'DELTA 8 CBD DISPOSABLE')!== false){{"MIXED BANGER 14MM FEMALE 12CT JAR $ 18.00-DETCD"}}
                                         @elseif(strpos($line['name'],'DELTA MAN DELTA 10 DISPOSABLE')!== false){{"OOZE BANGER HANGER SILICONE BANGER STAND - AQUA TEAL-DMD1D"}}
                                         @elseif(strpos($line['name'],'DELTA MAN DELTA 8 CARTRIDGE')!== false){{"OOZE QUARTH BANGER 14MM-DMDETC"}}
                                         @elseif(strpos($line['name'],'DOUGH DELTA 8 DISPOSABLE')!== false){{"OOZE THERMAL BANGER 90 DEGREE 14MM MALE-DDETD"}}
                                         @elseif(strpos($line['name'],'FUEGO DELTA 8 CARTRIDGE')!== false){{"QUARTZ BANGER 14MM 90D DEGREE FEMALE 15CT JAR-FDETC"}}
                                         @elseif(strpos($line['name'],'FUEGO DELTA 8 DISPOSABLE')!== false){{"QUARTZ BANGER 19MM 45D MALE 25CT JAR $ 42.00-FDETD"}}
                                         @elseif(strpos($line['name'],'GRAM CO DELTA 8 CARTRIDGES')!== false){{"QUARTZ BANGER 19MM 90D FEMALE 14CT JAR-GCDETC"}}
                                         @elseif(strpos($line['name'],'GREEN ROADS DELTA 8 CARTRIDGE')!== false){{"QUARTZ MATERIAL BANGER 14MM 90D FEMALE-GRDETC"}}
                                         @elseif(strpos($line['name'],'JUST DELTA 8 DISPOSABLE')!== false){{"QZ1990M QUARTZ BANGER 19MM 4MM 90D MALE 20CT JAR $ 38.00-JDETD"}}
                                         @elseif(strpos($line['name'],'KIK DELTA 8 DISPOSABLE')!== false){{"SAND BANGER 14MM 90D MALE 12CT JAR-KDETD"}}
                                         @elseif(strpos($line['name'],'KIK LIVE RESIN DISPOSABLE')!== false){{"HIPSTER GLYCERIN NECTAR COLLECTORS 5 PIECE SET WITH BANGERS GOLD-KLRD"}}
                                         @elseif(strpos($line['name'],'LIT DELTA 8 ZEN PEN')!== false){{"HIPSTER GLYCERIN NECTAR COLLECTORS 5 PIECE SET WITH BANGERS GREEN-LDETZP"}}
                                         @elseif(strpos($line['name'],'PACKWOODS FLO DELTA 8 CARTRIDGE')!== false){{"HIPSTER GLYCERIN NECTAR COLLECTORS 5 PIECE SET WITH BANGERS GREEN-PFDETC"}}
                                         @elseif(strpos($line['name'],'RUNTZ DELTA 8 DISPOSABLE')!== false){{"HIPSTER GLYCERIN NECTAR COLLECTORS 5 PIECE SET WITH BANGERS LIGHT GREEN-RDETD"}}
                                         @elseif(strpos($line['name'],'STNR DELTA 8 DISPOSABLE')!== false){{"HIPSTER GLYCERIN NECTAR COLLECTORS 5 PIECE SET WITH BANGERS ORANGE-SDETD"}}
                                         @elseif(strpos($line['name'],'URB DELTA 8 THC CARTRIDGE')!== false){{"HIPSTER GLYCERIN NECTAR COLLECTORS 5 PIECE SET WITH BANGERS PARROT GREEN-UDETTC"}}
                                         @elseif(strpos($line['name'],'URB DELTA 8/10 CARTRIDGE')!== false){{"HIPSTER GLYCERIN NECTAR COLLECTORS 5 PIECE SET WITH BANGERS PARROT SLIME GREEN-UDETC"}}
                                         @elseif(strpos($line['name'],'URB DELTA 8/10 DISPOSABLE')!== false){{"QUARTZ BANGER 19MM 90D FEMALE 14CT JAR-UDETD"}}
                                         @elseif(strpos($line['name'],'URB EXTRAX ZENERGY')!== false){{"QUARTZ BANGER 19MM 90D MALE 18CT JAR-UEZ"}}



                                              <!--delta jadoo END-->

                              @elseif(strpos($line['name'],'FUME MINI DISPOSABLE')!== false)
                                            {{"50MM CANABIS DESIGN MINI GRINDER 10CT BOX FMD"}}

                              @elseif(strpos($line['name'],'HITT ULTRA DISPOSABLE 2400PF')!== false)
                                            {{"2.5' HEAVY QUARTZ BANGER 10CT JAR HUD"}}

                                               @elseif(strpos($line['name'],'FUME EXTRA DISPOSABLE 1500PF')!== false)
                                            {{"63MM RICK & MORTY DESIGN GRINDER 10CT BOX FED"}}

                                             @elseif(strpos($line['name'],'HQD CUVIE AIR DISPOSABLE 4000PF')!== false)

                                              {{"5' PAINTED DESIGN HEAVY HAND PIPE 5CT JAR HCAD"}}

                                               @elseif(strpos($line['name'],'HQD CUVIE PLUS DISPOSABLE 1200PF')!== false)

                                              {{"6' PAINTED DESIGN HEAVY HAND PIPE 6CT JAR HCPD"}}


                                                 @elseif(strpos($line['name'],'HYDE IQ DISPOSABLE 5000PF')!== false)

                                              {{"7' SPIRAL IN BODY DESIGN HEAVY HAND PIPE 10CT JAR HIQD"}}

                                                @elseif(strpos($line['name'],'BIFFBAR DISPOSABLE 6000PF')!== false)

                                              {{"7.5' SNAKE BODY DESIGN HEAVY HAND PIPE 10CT JAR BFBD"}}

                                                @elseif(strpos($line['name'],'BATHING VAPE CARTEL DISPOSABLE 7000PF')!== false)

                                              {{"8' MONKEY BODY DESIGN HEAVY HAND PIPE 10CT JAR BVCD"}}

                                                 @elseif(strpos($line['name'],'FUME UNLIMITED DISPOSABLE 7000PF')!== false)

                                              {{"50MM RICK & MORTY WHOLEBODY GRINDER 12CT BOX FULD"}}




                                                  @elseif(strpos($line['name'],'PACKSPOD DISPOSABLE 5000PF')!== false)

                                              {{"63MM BACKWOODS WHOLEBODY GRINDER 6CT BOX PPD"}}

                                               @elseif(strpos($line['name'],'SWFT MOD DISPOSABLE 5000PF')!== false)

                                              {{"65MM COOKIES WHOLEBODY GRINDER 12CT BOX SMD"}}




                                                @elseif(strpos($line['name'],'DAZED8 BLENZ LIVE RESIN D8 D9 D10 THCPO CARTRIDGE')!== false) {{'PHARAOHS BOWL FLO GLASSSILICO-DBLRDDDTC'}}
                                            @elseif(strpos($line['name'],'DAZED8 BLENZ LIVE RESIN D8 D9 D10 THCPO DISPOSABLE')!== false) {{'SMOKEZILLA JUMBO GLASS ASHTRAY-DBLRDDDTD'}}
                                            @elseif(strpos($line['name'],'DAZED8 BLENZ LIVE RESIN HHCO THCO THCPO CARTRIDGE')!== false) {{'022589 GLASS BUTT BUCKET 6 PIECES PER DISPLAY-DBLRHTTC'}}
                                            @elseif(strpos($line['name'],'DAZED8 BLENZ LIVE RESIN HHCO THCO THCPO DISPOSABLE')!== false) {{'030012 GLASS JAR METAL CLASP MIX X 6 PIECES PER DISPLAY-DBLRHTTD'}}
                                            @elseif(strpos($line['name'],'DAZED8 DIAMONDS DABS DELTA 8 THC')!== false) {{'041466 PLATED GLASS ASHTRAY 5 PIECES PER DISPLAY-DDDD8T'}}
                                            @elseif(strpos($line['name'],'DAZED8 HHC BLUNTS')!== false) {{'PUFFCO PEAK PRO GLASS ROYAL BL-DHB'}}
                                            @elseif(strpos($line['name'],'DAZED8 HHC CARTRIDGE')!== false) {{'PUFFCO PEAK PRO GLASS ULTRA VI-DHC'}}
                                            @elseif(strpos($line['name'],'DAZED8 HHC CARTRIDGE')!== false) {{'OOZE STEAMBOAT SILICONE GLASS-DHC'}}
                                            @elseif(strpos($line['name'],'DAZED8 HHC DABS')!== false) {{'MOB HOOKAH- MARIO BOWL-DHD'}}
                                            @elseif(strpos($line['name'],'DAZED8 HHC DIAMONDS DABS')!== false) {{'MOB HOOKAH-MEDUSA HOSE-DHDD'}}
                                            @elseif(strpos($line['name'],'DAZED8 HHC DISPOSABLE')!== false) {{'MOB HOOKAH-WATER PIPE HOOD-DHD'}}
                                            @elseif(strpos($line['name'],'DAZED8 HHC HEMP FLOWER')!== false) {{'MOB HOOKAH-DIAMOND FUNNEL SET-DHHF'}}
                                            @elseif(strpos($line['name'],'DAZED8 HHC LIVE RESIN CARTRIDGE')!== false) {{'MOB HOOKAH -PUNTA CANA-S-DHLRC'}}
                                            @elseif(strpos($line['name'],'DAZED8 HHC OVO CARTRIDGE')!== false) {{'MOB HOOKAH-LYCAN BOWL-G-DHOC'}}
                                            @elseif(strpos($line['name'],'DAZED8 HHC OVO DISPOSABLE')!== false) {{'CARIA GOLD METAL HOOKAH HEAD-DHOD'}}
                                            @elseif(strpos($line['name'],'DAZED8 HHC THCO HEMP FLOWER')!== false) {{'ATH HOOKAH MOUTHTIP-DHTHF'}}
                                            @elseif(strpos($line['name'],'DAZED8 HHC TITANZ DISPOSABLE')!== false) {{'BYO HOOKAH BOWL LARGE-DHTD'}}
                                            @elseif(strpos($line['name'],'DAZED8 LIVE RESIN D9 THCO THCPO HHC HHCO DISPOSABLE')!== false) {{'DREAM HOOKAH LED ICE CUBE PAC-DLRDTTHHD'}}
                                            @elseif(strpos($line['name'],'DAZED8 LIVE RESIN DELTA 9 THCO THCPO HHC HHCO CARTRIDGE')!== false) {{'PRE PUNCHED ALUMINUM HOOKAH FOIL-DLRD9TTHHC'}}
                                            @elseif(strpos($line['name'],'DAZED8 THCO CARTRIDGE')!== false) {{'MOB HOOKAH-GERNADE BOWL-BS-DTC'}}
                                            @elseif(strpos($line['name'],'DAZED8 THCO HEMP FLOWER')!== false) {{'MOB HOOKAH-FUNNEL CRYSTAL SET-DTHF'}}
                                            @elseif(strpos($line['name'],'DAZED8 THCO LIVE RESIN CARTRIDGE')!== false) {{'MOB HOOKAH-GLASS LYCAN BADCHA-DTLRC'}}
                                            @elseif(strpos($line['name'],'DAZED8 THCO LIVE RESIN DISPOSABLE')!== false) {{'4 IN 1 UTILITY JAR W GRINDE-DTLRD'}}
                                            @elseif(strpos($line['name'],'DAZED8 THCO LIVE RESIN TITANZ DISPOSABLE')!== false) {{'BACKWOODS MAGNIFYING JAR-DTLRTD'}}
                                            @elseif(strpos($line['name'],'DAZED8 THCO SHATTER WALKERS PRE ROLLS')!== false) {{'HIPSTER GRINDER 4 PART 58MM-DTSWPR'}}
                                            @elseif(strpos($line['name'],'DAZED8 TITANZ DELTA 8 THC DISPOSABLE')!== false) {{'JOUGE AUTOMATIC GRINDER CONE F-DTD8TD'}}
                                            @elseif(strpos($line['name'],'DAZED8 TITANZ LIVE RESIN DELTA 8 THC DISPOSABLE')!== false) {{'VIKING AXE-BALL GRINDER-DTLRD8TD'}}
                                            @elseif(strpos($line['name'],'CREAM HHC CARTRIDGE')!== false) {{'LOVE ROSE GLASS-CHC1'}}
                                            @elseif(strpos($line['name'],'CREAM HHC THCP DISPOSABLE')!== false) {{'glass clear rolling paper-CHTD2'}}
                                            @elseif(strpos($line['name'],'CREAM KNOCK OUT THCP LIFE RESIN DISPOSABLE')!== false) {{'OOZE GLASS GLOBE SWOOP-CKOTLRD'}}
                                            @elseif(strpos($line['name'],'CREAM THCO CARTRIDGE')!== false) {{'MOB HOOKAH- S BOWL-CTC1'}}
                                            @elseif(strpos($line['name'],'CREAM THCO THCP DISPOSABLE')!== false) {{'MOB HOOKAH-GLASS LYCAN BADCHA-CTTD2'}}
                                            @elseif(strpos($line['name'],'CAKE SLEEPER LIVE RESIN DISPOSABLE')!== false) {{'BKM BOWL GLASSSILICO-CSLRD'}}


                                                   @elseif(strpos($line['name'],'KROS MINI DISPOSABLE 4000PF')!== false) {{"6' R&M DESIGN HEAVY HAND PIPE 6CT JAR KMD"}}
                                            @elseif(strpos($line['name'],'KROS NANO DISPOSABLE 5000PF')!== false) {{"6' OWL DESIGN HEAVY HAND PIPE 6CT JAR KMD"}}




                                                    @elseif(strpos($line['name'],'YME QB XXL DISPOSABLE 2200PF')!== false) {{"50MM AMSTERDAM GRINDER 12CT BOX YQXD22"}}
                                            @elseif(strpos($line['name'],'YME QB XXL DISPOSABLE 2500PF')!== false) {{"60MM AMSTERDAM GRINDER 12CT BOX YQXD25"}}


                                                @elseif(strpos($line['name'],'YME EXTRA DISPOSABLE 3600PF')!== false) {{"45MM AMSTERDAM RAINBOW GRINDER 10CT BOX YED"}}



                            @else{{$line['name']}}@endif
                                </td>
                                <!--<td style="vertical-align: top;padding-left: 5px;text-align: left;">-->
                                <!--    {{ round($line['quantity']) }}</td>-->

                                <td
                                    style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                                    {{ ($line['quantity']) }}
                                </td>
                                <td style="vertical-align: top;padding-left: 5px;text-align: left;">$
                                    {{ $line['unit_price'] }}</td>
                                <td style="vertical-align: top;padding-left: 5px;text-align: left;">
                                     @if (!empty($line['unit_tax']))
                                    ${{ $line['unit_tax'] }}
                                    @else
                                    $ 0.00
                                    @endif
                                    </td>
                                <td style="vertical-align: top;padding-left: 5px;text-align: left;">
                                    {{ $line['line_total'] }}</td>
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
                                        <td class="text-right">{{ $modifier['quantity'] }}
                                            {{ $modifier['units'] }}
                                        </td>
                                        <td class="text-right">{{ $modifier['unit_price_inc_tax'] }}</td>
                                        <td class="text-right">{{ $modifier['unit_tax'] }}</td>
                                        <td class="text-right">{{ $modifier['line_total'] }}</td>
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
                         <div class="col-md-5">
                            <table>
                                <tr style="border: 1px solid #808080;">
                                    <th style="width:75%;">@lang('lang_v1.box_qty')</th>
                                    <td style="width:25%;border: 1px solid #808080;">{{ $receipt_details->box_qty ?? 0 }}</td>
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
                            <tr style="border: 1px solid #808080;">
                                <td style="border: 1px solid #808080;"> <span style="color: red !important; font-weight: 600;">Bank of America
                                        Account #</span></td>
                                <td>0101</td>
                            </tr>
                            <tr style="border: 1px solid #808080;">
                                <td style="border: 1px solid #808080;"> <span style="color: red !important; font-weight: 600;">TD Bank Account
                                        #</span></td>
                                <td>0101</td>
                            </tr>
                        </table>
                        </td>
                        <td style="vertical-align: top;">
                        <table>
                            <tr style="display:none">
                                <th style="border: 1px solid; text-align: right !important;display:none">
                                   Total Unit Price:
                                </th>
                                <td class="text-right" style="border: 1px solid;display:none">
                                    ${{ number_format($total_unit_price,2) }}
                                </td>
                            </tr>
                            <tr style="display:none">
                                <th style="border: 1px solid; text-align: right !important;display:none">
                                   Total Unit Tax:
                                </th>
                                <td class="text-right" style="border: 1px solid;">
                                    ${{ number_format($total_unit_tax,2) }}
                                </td>
                            </tr>
                            <tr style="display:none">
                                <th style="border: 1px solid; text-align: right !important;">
                                   Unit Total:
                                </th>
                                <td class="text-right" style="border: 1px solid;">
                                    ${{ number_format (($total_sub + $total_unit_tax) , 2)}}
                                </td>
                            </tr>
                            <tr >
                                <th style="border: 1px solid; text-align: right !important;">
                                   Subtotal (Excl Tax):
                                </th>
                                <td class="text-right" style="border: 1px solid;">
                                    {{ $receipt_details->subtotal }}
                                </td>
                            </tr>
                            <!-- Tax -->
                            <!--@if (!empty($receipt_details->tax))-->
                            <!--<tr >-->
                            <!--    <th style="border: 1px solid; text-align: right !important; ">-->
                            <!--        {!! $receipt_details->tax_label !!}-->
                            <!--    </th>-->
                            <!--    <td class="text-right" style="border: 1px solid;">-->
                            <!--        (+) {{ $receipt_details->tax }}-->
                            <!--    </td>-->
                            <!--</tr>-->
                            <!--@else-->
                            <!--<tr>-->
                            <!--    <th style="text-align: right !important; border: 1px solid;">-->
                            <!--        {!! $receipt_details->tax_label !!}-->
                            <!--    </th>-->
                            <!--    <td class="text-right" style="border: 1px solid;">-->
                            <!--        (+) 0.00-->
                            <!--    </td>-->
                            <!--</tr>-->
                            <!--@endif-->


                            @if ($receipt_details->round_off_amount > 0)
                            <tr >
                                <th style="text-align: right !important;" style="border: 1px solid;">
                                    {!! $receipt_details->round_off_label !!}
                                </th>
                                <td class="text-right" style="border: 1px solid;">
                                    {{ $receipt_details->round_off }}
                                </td>
                            </tr>


                            @endif
                            <!-- state and city tax -->
                            @foreach ($tax_details as $tr)
                            <tr>
                                <th style="text-align: right !important; border: 1px solid;">
                                    {{ $tr['name'] }}
                                </th>
                                <td class="text-right" style="border: 1px solid;">
                                    $ {{ $tr['tax'] }}
                                </td>
                            </tr>
                            @endforeach

                            <!-- shipping -->
                            <!-- Shipping Charges -->
                                    @if (!empty($receipt_details->shipping_charges))
                                    <tr >
                                        <th style="width:63%; text-align: right !important; border: 1px solid;">
                                            Shipping:
                                        </th>
                                        <td class="text-right" style="border: 1px solid;">
                                           (+) {{ $receipt_details->shipping_charges }}
                                        </td>
                                    </tr>
                                    @else
                                    <tr>
                                        <th style="width:63%; text-align: right !important; border: 1px solid;">
                                            Shipping:
                                        </th>
                                        <td class="text-right" style="border: 1px solid;">
                                           (+) 0.00
                                        </td>
                                    </tr>
                                    @endif
                                     <!-- discount -->
                            @if (!empty($receipt_details->discount))
                                <tr >
                                    <th style="text-align: right !important; border: 1px solid;">
                                        {!! $receipt_details->discount_label !!}
                                    </th>

                                    <td class="text-right" style="border: 1px solid;">
                                        (-) {{ $receipt_details->discount }}:
                                    </td>
                                </tr>

                                @else
                                 <tr >
                                    <th style="text-align: right !important; border: 1px solid;">
                                        {!! $receipt_details->discount_label !!}:
                                    </th>

                                    <td class="text-right" style="border: 1px solid;">
                                      (-) 0.00
                                    </td>
                                </tr>
                            @endif
                                    @if (!empty($receipt_details->all_due))
                                    <tr>
                                <th style="text-align: right !important; border: 1px solid;">
                                    {!! $receipt_details->all_bal_label !!}
                                </th>
                                <td class="text-right" style="border: 1px solid;">
                                    {{ $receipt_details->all_due }}
                                </td>
                            </tr>
                            @endif


                            @if (!empty($receipt_details->total_quantity_label))
                            <tr>
                                <th style="width:63%; text-align: right !important; border: 1px solid;">
                                    {!! $receipt_details->total_quantity_label !!}
                                </th>
                                <td class="text-right" style="border: 1px solid;">
                                    {{ $receipt_details->total_quantity }}
                                </td>
                            </tr>
                            @endif


                                @if (!empty($receipt_details->total_exempt_uf))
                                <tr>
                                    <th style="width:63%; text-align: right !important; border: 1px solid;">
                                        @lang('lang_v1.exempt')
                                    </th>
                                    <td class="text-right" style="border: 1px solid;">
                                        {{ $receipt_details->total_exempt }}
                                    </td>
                                </tr>
                                @endif

                                    @if (!empty($receipt_details->packing_charge))
                                <tr>
                                    <th style="width:63%; text-align: right !important; border: 1px solid;">
                                        {!! $receipt_details->packing_charge_label !!}:
                                    </th>
                                    <td class="text-right" style="border: 1px solid;">
                                        {{ $receipt_details->packing_charge }}
                                    </td>
                                </tr>
                                @endif



                                @if (!empty($receipt_details->reward_point_label))
                                <tr>
                                    <th style="text-align: right !important; border: 1px solid;">
                                        {!! $receipt_details->reward_point_label !!}
                                    </th>

                                    <td class="text-right" style="border: 1px solid;">
                                        (-) {{ $receipt_details->reward_point_amount }}
                                    </td>
                                </tr>
                                @endif
                            <!-- Total -->
                            <!-- Total -->
                                <tr>
                                    <th style="text-align: right !important; border: 1px solid;">
                                        {!! $receipt_details->total_label !!}
                                    </th>
                                    <td class="text-right" style="border: 1px solid;">
                                        {{ $receipt_details->total }}
                                        @if (!empty($receipt_details->total_in_words))
                                        <br>
                                        <small>({{ $receipt_details->total_in_words }})</small>
                                        @endif
                                    </td>
                                </tr>

                            @if (!empty($receipt_details->total_paid))
                            <tr>
                                <th style="text-align: right !important; border: 1px solid;">
                                    @if (!empty($receipt_details->total_paid_label))
                                        {!! $receipt_details->total_paid_label !!}
                                    @endif
                                </th>
                                <td class="text-right" style="border: 1px solid;">
                                    {{ $receipt_details->total_paid }}
                                </td>
                            </tr>
                            @else
                            <tr>
                                <th style="text-align: right !important; border: 1px solid;">
                                    @if (!empty($receipt_details->total_paid_label))
                                    {!! $receipt_details->total_paid_label !!}:
                                     @endif
                                </th>
                                <td class="text-right" style="border: 1px solid;">
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
                                <td class="text-right">
                                    {{ $receipt_details->total_due }}
                                </td>
                            </tr>
                            @endif -->
                            @if (!empty($receipt_details->total_due))
                                <tr>
                                    <th style="text-align: right !important; border: 1px solid;">
                                        Total Remaining:
                                    </th>
                                    <td class="text-right" style="border: 1px solid;">
                                        {{ $receipt_details->total_due }}
                                        @if (!empty($receipt_details->total_in_words))
                                        <br>
                                        <small>({{ $receipt_details->total_in_words }})</small>
                                        @endif
                                    </td>
                                </tr>
                            @else
                            <tr>
                                    <th style="text-align: right !important; border: 1px solid;">
                                        Total Remaining:
                                    </th>
                                    <td class="text-right" style="border: 1px solid;">
                                      0.00
                                      @if (!empty($receipt_details->total_in_words))
                                        <br>
                                        <small>({{ $receipt_details->total_in_words }})</small>
                                        @endif
                                    </td>
                                </tr>
                             @endif
                            </table>
                            <table>
                                <tr style="border: 1px solid #808080;">
                                    <br>
                                    <th style="text-align: right !important; border: 1px solid #808080;">Credit:
                                    </th>
                                    <td class="text-right">
                                        ${{ number_format((float) $receipt_details->credit_bln, 2) }}</td>

                                </tr>
                                <tr style="border: 1px solid #808080;">
                                    <th style="text-align: right !important; border: 1px solid #808080;">Open
                                        Balance: </th>
                                    <td class="text-right">
                                        ${{ number_format((float) $receipt_details->opening, 2) }}</td>
                                </tr>
                            </table>
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
    <script type="text/javascript">
        //for read the table data
        var details = $('tbody#mytable tr').map(function(i, row) {
            return {
                //'sr': row.cells[0].textContent,
                'product': row.cells[1].textContent.trim(),
                // 'Quantity': row.cells[2].textContent.trim(),
                'Qty': row.cells[2].textContent.toString().replace(',', ''),
                'price': row.cells[3].textContent.trim(),
                'tax': row.cells[4].textContent.trim(),
                'subtotal': row.cells[5].textContent.toString().replace(',', '')

            }
        }).get();
        //console.log(details);

        //for merging duplicates
        result = [];
        details.forEach(function(a) {
            if (!this[a.product]) {
                //this[a.product] = parseInt(Quantity);
                this[a.product] = {
                    product: a.product,
                    Qty: 0,
                    price: a.price,
                    tax: a.tax,
                    subtotal: 0
                };
                result.push(this[a.product]);
            }
            this[a.product].Qty += Math.round(a.Qty);
            this[a.product].subtotal += parseFloat(a.subtotal);

        }, Object.create(null));
        //console.log(result);

        //for display table with array of objects
        function renove() {

            $('#mytable').remove();
            var sr = 0;
            var k = '<tbody>'
            for (i = 0; i < result.length; i++) {
                sr = sr + 1;
                if(result[i].Qty == 0){
                    k += '<tr style="border: 1px solid #808080;" class="order-list">';
                }else{
                    k += '<tr style="border: 1px solid #808080;">';
                }
                k += '<td style="border: 1px solid #808080; text-align:center;" >' + sr + '</td>';
                k += '<td style="border: 1px solid #808080; text-align:left;" >' + result[i].product + '</td>';
                k += '<td style="border: 1px solid #808080; text-align:center;" >' + result[i].Qty + '</td>';
                k += '<td style="border: 1px solid #808080; text-align:center;" >' + result[i].price + '</td>';
                k += '<td style="border: 1px solid #808080; text-align:center;" >' + result[i].tax + '</td>';
                k += '<td style="border: 1px solid #808080; text-align:center; ">' + ' $ ' + result[i].subtotal.toFixed(2) +
                    '</td>';
                k += '</tr>';
            }
            k+=`<tr style="display:none;">
                    <th></th>
                    <th class="text-center" >Total Products: `+sr+`</th>
                    <th class="text-center" >Total QTY:<br/>{{ $total_qty }}</th>
                    <th class="text-center" >Total Unit<br/>Price:<br/>${{ number_format($total_unit_price,2) }}</th>
                    <th class="text-center" >Total Unit<br/>Tax:<br/>${{ number_format($total_unit_tax,2) }}</th>
                    <th class="text-center" >Net Total:<br/>${{ number_format($total_sub,2) }}</th>
                </tr>`;
            k += '</tbody>';
            document.getElementById('tableData').innerHTML = k;
        }


        // setInterval(function(){
        //     console.log("Oooo Yeaaa!");
        renove();
        // }, 1000);//run this thang every 1 seconds

    // Qr code JS is in Show Invoice page .
    </script>