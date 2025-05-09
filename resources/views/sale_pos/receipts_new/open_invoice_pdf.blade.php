<!DOCTYPE html>
<style>
    page[size="A4"] {
        width: 21cm;
        height: 29.7cm;
    }

    page[size="A4"][layout="landscape"] {
        width: 29.7cm;
        height: 21cm;
    }
    /*@font-face {*/
    /*  font-family: SourceSansPro;*/
    /*  src: url(SourceSansPro-Regular.ttf);*/
    /*}*/

    .clearfix:after {
      content: "";
      display: table;
      clear: both;
    }

    a {
      color: #0087C3;
      text-decoration: none;
    }

    header {
      padding: 10px 0;
      margin-bottom: 20px;
      border-bottom: 1px solid #AAAAAA;
    }

    #logo {
      float: left;
      margin-top: 8px;
    }

    #logo img {
      height: 70px;
    }

    #company {
      float: right;
      text-align: right;
    }

    #details {
      margin-bottom: 50px;
    }

    #client {
      padding-left: 6px;
      border-left: 6px solid #0087C3;
      float: left;
    }

    #client .to {
      color: #777777;
    }

    h2.name {

      font-size: 2.0em;
      font-weight: normal;
      margin: 0;
    }
    h3.name {
      font-size: 1.5em;
      font-weight: normal;
      margin: 0;
    }

    #invoice {
      float: right;
      text-align: right;
    }

    #invoice h1 {
      color: #0087C3;
      font-size: 2.4em;
      line-height: 1em;
      font-weight: normal;
      margin: 0  0 10px 0;
    }

    #invoice .date {
      font-size: 1.1em;
      color: #777777;
    }

    .lines tr
    {
        line-height: 2.2em;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border-spacing: 0;
      /*margin-bottom: 20px;*/
    }

    table th,
    table td {
      padding: 1px 1px 1px 1px ;
      background-color: #EEEEEE !important;
      /*text-align: center;*/
      border-bottom: 1px solid #FFFFFF !important;
      -webkit-print-color-adjust: exact !important;
    }

    @media print {
    /*  #invoice_content ,img{-webkit-print-color-adjust: exact !important;; }*/
    }

    /*@page {*/
    /*    -webkit-print-color-adjust: exact !important;*/
    /*}*/
    table .no {
      color: #FFFFFF !important;
      /*font-size: 14px;*/
      background: #57B223 !important;
    }

    table .desc {
      text-align: left;
    }

    table .unit {
      background: #DDDDDD !important;;
    }

    table .qty {
    }

    table .total {
      background: #57B223 ;
      color: #FFFFFF !important;
    }

    table td.unit,
    table td.qty,
    table td.total {
      font-size: 14px;
    }

    table tbody tr:last-child td {
      border: none;
    }

    .tfoots td {
      text-align:right ;
      background: #FFFFFF !important;
      border-bottom: none !important;
      font-size: 14px ;
      white-space: nowrap ;
      border-top: 1px solid #AAAAAA;
    }

    .tfoots tr:first-child td {
      border-top: none !important;
    }



    .tfoots tr:last-child td {
      /*color: #57B223 !important;*/
      font-size: 14px !important;
      border-top: 1px solid #57B223 !important;

    }


    .tfoots tr td:first-child {
      border: none !important;
    }

    .tfoots tr td {
      border-top: 1px solid #f4f4f4;
    }

    #thanks{
      font-size: 2em;
      margin-bottom: 25px;
      margin-top: 25px;

    }

    #notices{
      padding-left: 6px;
      border-left: 6px solid #0087C3;
    }

    #notices .notice {
      font-size: 1.2em;
    }
    @media print {
        .tfoots {
            display: table-row-group;
        }
     }
    footer {
      color: #777777;
      width: 100%;
      height: 30px;
      /*position: absolute;*/
      bottom: 0;
      border-top: 1px solid #AAAAAA;
      padding: 3px 0;
      text-align: center;
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
    @media print {
      *:after {
        color: #000 !important;
        background: transparent !important;
      }
    }
</style>
{{-- {{ HTML::script('resources/js/bootstrap.min.js') }}  --}}
{{-- {{ HTML::script('https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js') }} --}}
<div style="padding: 0px 0px;" id="">

        <header class="clearfix">
            <div width="30%" id="logo">
                    <!--    class="img img-responsive center-block"-->
                    <!--    alt="business logo"-->
                    <!--    style="width: 180px;">-->
                    @if (!empty($receipt_details->logo))
                        <img src="{{ $receipt_details->logo }}"
                            class="img img-responsive center-block" style="width:170px;height:100px;">
                            <!--style="width: 230px !important; margin-left: 0px;"-->
                    @endif
            </div>
            <div width="15%" id=company>
                <!--<div style="padding: 2px; margin-left: 10px;" id="qrcode"></div>-->
                   <!--<tr style="border: 1px solid #808080;">-->
                   <!--                             <td style="text-align: center;">-->
                   <!--                                 <span>-->
                   <!--                                     <div style="padding: 5px;text-align: center;">-->
                                                           @if($qrcode!="")
                                                            <img src="{{$qrcode}}">
                                                            @endif
                                            <!--            </div>-->
                                            <!--        </span>-->
                                            <!--    </td>-->
                                            <!--</tr>-->
            </div>
            <div width="40%" id="company">


                <h3 class="name hide"
                    style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 16px; font-weight: 600;">
                    Payable to: </h3>
                <h3  class="name"
                    style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600;">
                    {{ config('business-info.name') }}</h3>
                <h3 class="name">
                    @if (!empty($receipt_details->display_name))
                        <b>{{ $receipt_details->display_name }}</b>
                    @endif
                </h3>
                <div>
                    @if (!empty($receipt_details->address))
                        <b>{!! $receipt_details->address !!}</b>
                    @endif
              </div>
            <div>
                @if (!empty($receipt_details->contact))
                    <b>{{ $receipt_details->contact }}</b><br>
                @endif
                @if (!empty($receipt_details->contact) && !empty($receipt_details->website))
                    <!--<b>|</b>-->
                @endif
                @if (!empty($receipt_details->website))
                    <b><a>{{$receipt_details->website}}</a></b>
                @endif
            </div>
          </div>

          </div>
        </header>
        <main>
            <div id="details" width="100%">

                <div id="client" width="60%">
                    <div class="address"><b>INVOICE TO:</b></div>

                    <h3 class="name" style="text-transform: uppercase; margin-bottom:2px;margin-top:8px;font-size: 14px; font-weight: 600;">@if (!empty($receipt_details->customer_name))  {{ $receipt_details->customer_name }}({{ $receipt_details->contact_id }}) @endif<br></h3>
                    @if (!empty($receipt_details->address_line_1))
                        <div class="address">
                                <b>{!! $receipt_details->address_line_1 !!},</b>
                            @if (!empty($receipt_details->address_line_2))
                                <b>{!! $receipt_details->address_line_2 !!},</b>
                            @endif
                            @if (!empty($receipt_details->city))
                                <b>{!! $receipt_details->city !!},</b>
                            @endif
                            @if (!empty($receipt_details->state))
                                <b>{!! $receipt_details->state !!},</b>
                            @endif
                            @if (!empty($receipt_details->zip_code))
                                <b>{{ $receipt_details->zip_code }},</b>
                            @endif <br>
                        </div>
                    @endif
                    <div>
                        @if (!empty($receipt_details->mobile))
                           <b> MOBILE:
                            {{ $receipt_details->mobile }}</b>
                        @endif
                    </div>
                </div>

                <div>

                    <div id="invoice" style="width: 30%;display:grid;">

                        <div style="display:flex;justify-content:space-between;">
                            <h1 style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600;"  class="pull-left">Invoice Number: </h1>
                            <h1 id="invoice" style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 16px; font-weight: 600;" class="pull-right">{{ $receipt_details->invoice_no }}</h1>
                        </div>
                        <div class="date" style="display:flex;justify-content:space-between;text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600;">
                            <b class="pull-left">Invoice {{ $receipt_details->date_label }} :</b><b class="pull-right">{{ $receipt_details->invoice_date }}</b>
                        </div>
                        <!--<div class="date">Due Date: 30/06/2014</div> -->
                        <div class="date" style="display:flex;justify-content:space-between;text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600;">
                            <b class="pull-left">Invoice Total :</b><b class="pull-right">{{ $receipt_details->total }}</b>
                        </div>

                        {{-- <div class="date" style="display:flex;justify-content:space-between;text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600;">
                            <b class="pull-left">Payment Status :</b>
                            <b class="pull-right"><!-- $receipt_details->status  removed due to error by dhruvik--></b>
                        </div> --}}

                        <div class="date" style="display:flex;justify-content:space-between;text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600;">
                            <b class="pull-left"> Tobacco lic:&nbsp;&nbsp;&nbsp;&nbsp;</b>
                            <b class="pull-right"> @if (!empty($receipt_details->tobacco_license_no)){{ $receipt_details->tobacco_license_no  }} @else None @endif</b>
                        </div>
                        <div class="date" style="display:flex;justify-content:space-between;text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600;">
                            <b class="pull-left">Tax ID:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>
                            <b class="pull-right">@if (!empty($receipt_details->tex)){{ $receipt_details->tex }} @else None @endif</b>
                        </div>
                    </div>


                <?php
                    // echo "<pre>";
                    // print_r($receipt_details);
                    // die;
                ?>
                </div>
            </div>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
                <div class="table table-responsive">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <td class="no" width="10%"
                                style="color: #FFFFFF !important;background: #57B223 !important;font-weight: 700;text-align: center;vertical-align:top;padding:3px !important; border-left: none;">
                                SR No</td>

                            <td width="30%"
                                style="background-color: #EEEEEE !important;font-weight: 700;vertical-align:top;text-align: center;padding:3px !important; border-left: none;padding-left:4px;">
                                PRODUCT</td>
                            <td width="20%"
                                style="background-color: #EEEEEE !important;font-weight: 700;text-align: center;vertical-align:top;padding:3px !important; border-left: none;">
                                Item Code</td>
                            <td width="10%"
                                style="background-color: #EEEEEE !important;font-weight: 700;vertical-align:top;text-align: center;padding:3px !important; border-left: none;">
                                SKU</td>
                            <td width="10%"
                                style="background-color: #EEEEEE !important;font-weight: 700;text-align: center;vertical-align:top;padding:3px !important; border-left: none;">
                                QUANTITY</td>
                            <td class="unit" width="10%"
                                style="background: #DDDDDD !important;font-weight: 700;text-align: center;vertical-align:top;padding:3px !important; border-left: none;">
                                UNIT <br /> PRICE</td>
                            <td width="7%"
                                style="background-color: #EEEEEE !important;font-weight: 700;text-align: center;vertical-align:top;padding:3px !important;border-left: none;display:none;">
                                UNIT TAX</td>
                            <td class="no" width="10%"
                                style="color: #FFFFFF !important;background: #57B223 !important;font-weight: 700;text-align: center;vertical-align:top;border-right: none;">
                                SUBTOTAL</td>
                        </tr>
                    </thead>
                    <?php
                    $sr = 1;
                    ?>

                    <tbody id="tableData" class="lines"> </tbody>

                    <tbody id="mytable" style="display:none;">
                        @php
                            $max_array = [];
                            $box_increment = 0;
                        @endphp
                        @if($receipt_details->order_packing_status == '2' && $receipt_details->order_picking_status == '2')
                        @if(!empty($receipt_details->lines))
                            @foreach($receipt_details->lines as $line)
                                <?php
                                if(strpos($line['box_no'],'BOX')!== false)
                                {
                                    $explode_box = explode(',', $line['box_no']);

                                    foreach ($explode_box as $e_box)
                                    {
                                        $explode_box_str = explode(':', $e_box);

                                        foreach ($explode_box_str as $e_box_str)
                                        {
                                            if (strpos($e_box_str, 'BOX') !== false)
                                            {
                                                $max_array[$box_increment] = $line;
                                                $final_e_box_str_to_arr = explode('BOX',$e_box_str);
                                                $max_array[$box_increment]['box_no'] = $final_e_box_str_to_arr[1];
                                                $max_array[$box_increment]['quantity'] = (float) $explode_box_str[1];
                                                $unitprice_calc = str_replace(",","", $line['unit_price']);
                                                //$max_array[$box_increment]['line_total'] = $line['unit_price'] * (float) $explode_box_str[1];
                                                $max_array[$box_increment]['line_total'] = (float)  $unitprice_calc * (float) $explode_box_str[1];

                                                $box_increment++;

                                            }
                                        }
                                    }
                                }
                                else
                                {
                                  $max_array[$box_increment] = $line;
                                  $box_increment++;
                                }
                                ?>
                            @endforeach
                        @endif
                        @endif
                        <?php
                        /*echo "<pre>";
                        print_r($max_array);
                        echo "</pre>";*/
                        ?>
                        <?php
                        if(count($max_array)>0)
                        {
                            usort($max_array,function ($a, $b)
                            {
                                $a = $a['box_no'];
                                $b = $b['box_no'];
                                if ($a == $b)
                                return 0;

                                return ($a < $b) ? -1 : 1;
                            });
                        }
                        else
                        {
                            $max_array = $receipt_details->lines;
                        }
                        ?>
                        @php
                            $mini_cigar_qty = 0;
                            $regular_cigar_qty = 0;
                            $mini_cigar = 0;
                            $regular_cigar = 0;
                            $subcategory_amounts = [];
                            $pre_box_no = '';
                        @endphp
                        @forelse($max_array as $line)
                            <!-- @if(isset($line['box_no']) && !empty($line['box_no']))
                             <tr>
                                @if( $line['box_no'] == 0)
                                   <b> <?php //$line['box_no'] = 'NaN'; ?></b>
                                @endif

                                @if ($pre_box_no != $line['box_no'])
                                <td style="background-color: #EEEEEE !important;" colspan="7"><b>Box No #{{$line['box_no']}}</b> </td>
                                @endif
                            </tr>
                            @endif -->
                            <tr class="box_lines">
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
                                <td class="no" style="color: #FFFFFF !important;background: #57B223 !important;vertical-align: top;padding-left: 5px;text-align: center;padding:3px !important;">{{ $sr++ }}
                                </td>
                                {{-- <td>
                                    @if(!empty($line['box_no']))

                                    {{$line['box_no']}}

                                    @endif
                                </td> --}}
                                <td style="background-color: #EEEEEE !important;vertical-align: top;padding-left: 10px;text-align: left;padding:3px 3px 3px 14px !important;">
                                    <?php $key="DESIGNER FLORAL DIAMOND PRINT WP –IFBR" ?>
                                            @if($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF PEACH -BOX OF 10')
                                            {{$key}}




                                            @elseif($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF LYCHEE ICE -BOX OF 10')
                                            {{$key}}@elseif($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF BLUEBERRY ICE -BOX OF 10')
                                            {{$key}}@elseif($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF LUSH ICE -BOX OF 10'){{$key}}
                                            @elseif(strpos($line['name'],'1UBANO')!== false){{$line['name']}}
                                            @elseif(strpos($line['name'],'1LEAR')!== false){{$line['name']}}
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

                                            @if(strpos($line['name'],'JUST DELTA 8 DISPOSABLE')!== false)
                                             {{"Oh Yeah..!!"}}
                                            @endif
                                </td>
                                <td style="background-color: #EEEEEE !important;vertical-align: top;padding-left: 10px;text-align: left;padding:3px !important;">
                                    @if(strpos($line['name'],'CU1BANO')!== false){{$line['item_code']}}
                                    @elseif(strpos($line['name'],'1C1LEAR')!== false){{$line['item_code']}}
                                    @elseif(isset($jadoo_products['itemcode']) && !empty($jadoo_products['itemcode']))
                                        @php
                                            $final_line_name_itemcode = "";
                                        @endphp
                                        @foreach($jadoo_products['itemcode'] as $match => $value)
                                            @if(strpos(strtolower($line['name']),strtolower($match))!== false)
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

                                        @if($final_line_name_itemcode!="")
                                            {{$final_line_name_itemcode}}
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

                                <td style="background-color: #EEEEEE !important;vertical-align: top;text-align: center;padding:3px !important;">

                                    @if ($line['name'] == 'JUUL PS 3% CLASSIC MENTHOL -BOX OF 8')
                                    {{ '991137814390' }}
                                    @elseif(strpos($line['name'],'ELF BAR DISPOSABLE 5000PF')!== false)
                                    {{ '68494038337' }}
                                    @elseif(strpos($line['name'],'C1UBANO')!== false){{$line['sub_sku']}}
                                    @elseif(strpos($line['name'],'CL1EAR')!== false){{$line['sub_sku']}}
                                    @elseif(isset($jadoo_products['barcode']) && !empty($jadoo_products['barcode']))
                                        @php
                                            $final_line_name_barcode = "";
                                        @endphp
                                        @foreach($jadoo_products['barcode'] as $match => $value)
                                            @if(strpos(strtolower($line['name']),strtolower($match))!== false)
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

                                        @if($final_line_name_barcode!="")
                                            {{$final_line_name_barcode}}
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
                                <td style="background-color: #EEEEEE !important;vertical-align: top;text-align: center;padding:3px !important;">{{$line['quantity']}}
                                </td>
                                <td class="unit" style="background: #DDDDDD !important;vertical-align: top;text-align: center;padding:3px !important;"> {{$line['unit_price']}}
                                </td>
                                <td style="background-color: #EEEEEE !important;vertical-align: top;text-align: center;display:none;padding:3px !important;">{{$line['unit_tax']}}
                                </td>

                                <td class="no" style="color: #FFFFFF !important;background: #57B223 !important;vertical-align: top;padding-left: 5px;text-align: center;padding:3px !important;">{{ $line['line_total']  }}
                                </td>

                                <?php $pre_box_no = $line['box_no'] ;?>
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
                </div>
                    <div class="row">
                    <div class="col-sm-3" style="float:left !important;">
                        <table width="100%" class="tfoots">

                            <tr style="border: 1px solid #808080;">
                                        <td style="width:25% !important;padding:3px !important;">@lang('lang_v1.box_qty')</td>
                                        <td style="width:25% !important;border: 1px solid #808080;padding:3px !important;">{{ $receipt_details->box_qty ?? 0 }}</td>
                            </tr>

                        </table>
                    </div>
                    <div class="col-sm-3"><b>Sell Note :</b> {{ $receipt_details->additional_notes }}</div>

                    <div class="col-sm-6" style="float:right;">
                        <table width="100%" class="tfoots">
                            <tr>
                                <td style="padding:3px !important;" width="75%"></td>
                                <td style="padding:3px !important;" width="10%">Subtotal (Excl Tax):</td>
                                <td style="padding-right:3px !important;" width="15%">{{ $receipt_details->subtotal }}</td>
                            </tr>
                            @foreach ($tax_details as $tr)
                            <tr>
                                <td style="padding:3px !important;" colspan="2">
                                    {{ $tr['name'] }}:
                                </td>
                                <td style="padding-right:3px !important;" width="15%">
                                    $ {{ $tr['tax'] }}
                                </td>
                            </tr>
                            @endforeach

                            <?php
                                $total_tax = 0;
                            ?>
                            @foreach($receipt_details->lines as $line)
                                <?php  $total_tax += $line['pos_line_tax_amount'] ?>
                            @endforeach

                            @if($total_tax != 0)
                            <tr>
                                <td style="padding:3px !important;" colspan="2">
                                    Order Tax:
                                </td>
                                <td style="padding-right:3px !important;" width="15%">
                                    $ {{ number_format((float) $total_tax, 2) }}
                                </td>
                            </tr>
                            @endif

                            <tr>
                                <td style="padding:3px !important;" width="75%"></td>
                                <td style="padding:3px !important;" width="10%">{{ $receipt_details->shipping_charges_label }}:  </td>
                                <td style="padding-right:3px !important;" width="15%">@if (!empty($receipt_details->shipping_charges_total)) (+){{ $receipt_details->shipping_charges_total }} @else (+) $ 0.00 @endif</td>
                            </tr>
                            <tr class="hide">
                                <td style="padding:3px !important;" width="75%"></td>
                                <td style="padding:3px !important;" width="10%">Shipping Cost:  </td>
                                <td style="padding-right:3px !important;" width="15%">@if (!empty($receipt_details->shipping_cost)) (+) $ {{ number_format((float) $receipt_details->shipping_cost, 2) }} @else (+) $ 0.00 @endif</td>
                            </tr>
                            <tr >
                                <td style="padding:3px !important;" width="75%"></td>
                                <td style="padding:3px !important;" width="1%">{{ $receipt_details->discount_label }}:  </td>
                                <td style="padding-right:3px !important;" width="15%">@if (!empty($receipt_details->discount)) (-){{ $receipt_details->discount }} @else (-) $ 0.00 @endif</td>
                            </tr>

                            <tr>
                                <td style="padding:3px !important;" width="75%"></td>
                                <td style="padding:3px !important;" width="10%">{!! $receipt_details->total_label !!}</td>
                                <td style="padding-right:3px !important;" width="15%">{{ $receipt_details->final_total}}</td>
                            </tr>

                            <tr>
                                <td style="padding:3px !important;" width="75%"></td>
                                <td style="padding:3px !important;" width="10%">
                                        @if (!empty($receipt_details->total_paid_label))
                                            {!! $receipt_details->total_paid_label !!}:
                                        @endif
                                </td>
                                <td style="padding-right:3px !important;" width="15%">@if (!empty($receipt_details->total_paid)){{ $receipt_details->total_paid }} @else $ 0.00 @endif</td>
                            </tr>

                            <tr>
                                <td style="padding:3px !important;" width="75%"></td>
                                <td width="10%">
                                        @if (!empty($receipt_details->total_due_label ))
                                            {!! $receipt_details->total_due_label  !!}:
                                        @endif
                                </td>
                                <td width="15%" style="padding-right:3px !important;">@if (!empty($receipt_details->total_due)){{ $receipt_details->total_due }} @else $ 0.00 @endif</td>
                            </tr>
                        </table>
                    </div>
                    </div>

                    <table style="width: 100%;margin-top: 8px;">
                    <tr>
                        <td style="text-align: center;border:none !important; width:65%;background: #FFFFFF !important;">
                            <table class="hide">
                                <tr>
                                    <td style="font-weight: 600;background: #FFFFFF !important;">
                                      We greatly appreciate your support and business! Customers are
                                      responsible for paying their
                                      Local, State & Federal Excise taxes for applicable products.
                                    </td>
                                </tr>
                            </table>
                            <table>
                                <tr>
                                    <td style="font-weight: 600;background: #FFFFFF !important;">
                                      <span style="color: red !important; font-weight: 600;"> We accept Cash, Check,
                                          Zelle, and Wire Transfers</span>
                                    </td>
                                </tr>
                            </table>
                            <table>
                                <tr style="border: 1px solid #808080 !important;">
                                    <td style="border: 1px solid #808080 !important;background: #FFFFFF !important;"> <span style="font-weight: 600;">Zelle
                                            Account</span></td>
                                    <td style="color: red !important;border: 1px solid #808080 !important;background: #FFFFFF !important;">{{ config('business-info.zelle_email') }}</td>
                                </tr>
                                <tr style="border: 1px solid #808080 !important;display:none;">
                                    <td style="border: 1px solid #808080 !important;background: #FFFFFF !important;"> <span style="font-weight: 600;">TD Bank Account No
                                             #</span></td>
                                    <td style="border: 1px solid #808080 !important;background: #FFFFFF !important;">4414232134</td>
                                </tr>
                                <tr style="border: 1px solid #808080 !important;display:none;">
                                    <td style="border: 1px solid #808080 !important;background: #FFFFFF !important;"> <span style=" font-weight: 600;">Routing No
                                            #</span></td>
                                    <td style="border: 1px solid #808080 !important;background: #FFFFFF !important;">0260-13673</td>
                                </tr>
                            </table>
                        </td>
                        <td style="margin:5%;background: #FFFFFF !important;">

                        </td>
                        <td style="width:30%;vertical-align: top;background: #FFFFFF !important;">
                            <table>
                                <tr style="">
                                    <br>
                                    <th style="text-align: right !important; ;background: #FFFFFF !important;">Credit:
                                    </th>
                                    <td class="text-right" style="background: #FFFFFF !important;">
                                        ${{ number_format((float) $receipt_details->credit_bln, 2) }}</td>

                                </tr>
                                <tr style="">
                                    <th style="text-align: right !important;background: #FFFFFF !important;">Open
                                        Balance: </th>
                                    <td class="text-right" style="font-weight:bold !important;color:red !important; background: #FFFFFF !important;">
                                        ${{ number_format((float) $receipt_details->opening, 2) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    </table>


               <?php
                    // echo "<pre>";
                    // print_r($receipt_details);
                    // die;
                ?>

          <div id="thanks" >Thank you!</div>

        </main>
        <footer style="margin-bottom:25px !important;padding:10px;font-weight:bold !important;">
          <!--Invoice was created on a computer and is valid without the signature and seal.-->

          <p>CUSTOMERS ARE RESPONSIBLE FOR PAYING THEIR LOCAL, STATE & FEDERAL EXCISE TAXES FOR APPLICABLE PRODUCTS.</p>
        </footer>
</div>
<script type="text/javascript">

</script>