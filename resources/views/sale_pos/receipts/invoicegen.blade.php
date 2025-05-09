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
      bottom: 0;padding-bottom:100px !important;
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
<script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>
<div style="padding: 0px 0px;" id="">

        <header class="clearfix">
            <div width="30%" id="logo">
                    @if (!empty($receipt_details->logo))
                        <img src="{{ $receipt_details->logo }}"
                            class="img img-responsive center-block" style="height:76px;">
                            <!--style="width: 230px !important; margin-left: 0px;"-->
                    @endif
            </div>
            <div width="15%" id=company>
                <div style="padding: 2px; margin-left: 10px;" id="qrcode"></div>
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
                    <div class="address"><b>@if($receipt_details->invoice_status == "draft") DRAFT @else INVOICE @endif  TO:</b></div>

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

                <div width="30%">

                    <div id="invoice">

                        <h1 style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600;"  class="pull-left"> @if($receipt_details->invoice_status == "draft") Draft @else Invoice @endif Number: </h1><h1 id="invoice" style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 16px; font-weight: 600;" class="pull-right">{{ $receipt_details->invoice_no }}</h1>
                        <div class="date" style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600;">
                            <b class="pull-left"> @if($receipt_details->invoice_status == "draft") Draft @else Invoice @endif {{ $receipt_details->date_label }} :</b><b class="pull-right">{{ $receipt_details->invoice_date }}</b>
                        </div>
                        <!--<div class="date">Due Date: 30/06/2014</div> -->
                        <div class="date" style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600;">
                            <b class="pull-left">@if($receipt_details->invoice_status == "draft") Draft @else Invoice @endif  Total :</b><b class="pull-right">{{ $receipt_details->total }}</b>
                        </div>

                        <div class="date" style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600;">
                            <b class="pull-left">Payment Status :</b>
                            <b class="pull-right"><!-- $receipt_details->status  removed due to error by dhruvik--></b>
                        </div>

                        <div class="date" style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600;">
                            <b class="pull-left"> Tobacco lic:&nbsp;&nbsp;&nbsp;&nbsp;</b>
                            <b class="pull-right"> @if (!empty($receipt_details->tobacco_license_no)){{ $receipt_details->tobacco_license_no  }} @else None @endif</b>
                        </div>
                        <div class="date" style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 14px; font-weight: 600;">
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
                                <td style="background-color: #EEEEEE !important;" colspan="7"><b>{{$line['box_no']}}</b> </td>
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
                                <td>
                                    @if(!empty($line['box_no']))

                                    {{$line['box_no']}}

                                    @endif
                                </td>
                                <td style="background-color: #EEEEEE !important;vertical-align: top;padding-left: 10px;text-align: left;padding:3px 3px 3px 14px !important;">
                                            {{$line['name']}}
                                </td>
                                <td style="background-color: #EEEEEE !important;vertical-align: top;padding-left: 10px;text-align: left;padding:3px !important;">

                                            {{ $line['item_code'] }}

                                </td>

                                <td style="background-color: #EEEEEE !important;vertical-align: top;text-align: center;padding:3px !important;">
                                            {{ $line['sub_sku'] }}
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
                            @if (!empty($receipt_details->discount))
                            <tr >
                                <td style="padding:3px !important;" width="75%"></td>
                                <td style="padding:3px !important;" width="1%">{!! $receipt_details->discount_label !!}:  </td>
                                <td style="padding-right:3px !important;" width="15%">@if (!empty($receipt_details->discount)) (-){{ $receipt_details->discount }} @else (-) $ 0.00 @endif</td>
                            </tr>
                            @endif

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
                                        @else
                                            Total Paid:
                                        @endif
                                </td>
                                <td style="padding-right:3px !important;" width="15%">@if (!empty($receipt_details->total_paid)){{ $receipt_details->total_paid }} @else $ 0.00 @endif</td>
                            </tr>

                            <tr>
                                <td style="padding:3px !important;" width="75%"></td>
                                <td width="10%">
                                        @if (!empty($receipt_details->total_due_label ))
                                            {!! $receipt_details->total_due_label  !!}:
                                        @else
                                            Total Due:
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
                                <tr style="display:none;">
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
        <footer style="margin-bottom:38px !important;padding:10px;font-weight:bold !important;">
          <!--Invoice was created on a computer and is valid without the signature and seal.-->

          <p>CUSTOMERS ARE RESPONSIBLE FOR PAYING THEIR LOCAL, STATE & FEDERAL EXCISE TAXES FOR APPLICABLE PRODUCTS. </br>
             REPLACEMENT/RETURN IS VALID WITHIN 15 DAYS WITH PURCHASE PROOF & ORIGINAL PACKING.
          </p>


        </footer>
</div>

  <script type="text/javascript">
        //for read the table data
        var details = $('tr.box_lines').map(function(i, row) {

            return {
                'box_no': row.cells[1].textContent.trim(),
                'product': row.cells[2].textContent.trim(),
                'item_code': row.cells[3].textContent.trim(),
                'sku': row.cells[4].textContent.trim(),
                'Qty': row.cells[5].textContent.toString().replace(',', ''),
                'price': row.cells[6].textContent.trim(),
                'Subtotal': row.cells[8].textContent.toString().replace(',', '')
            }
        }).get();
        console.log(details);

        //for merging duplicates
        result = [];
        box_nos_arr = [];
        let index = 1;
        details.forEach(function(a) {

            let keyboxno = a.box_no;
            let keyname = a.product+'-'+a.box_no;
            //console.log("===?",keyname)
            if (!this[keyname]) {
                //this[a.product] = parseInt(Quantity);
                this[keyname] = {
                    box_no: a.box_no,
                    product: a.product,
                    item_code:a.item_code,
                    sku: a.sku,
                    Qty: 0,
                    price: a.price,
                    Subtotal: 0.0,
                    sr: index,
                };
                result.push(this[keyname]);
                index++;
            }

            this[keyname].Qty += Math.round(a.Qty);
            this[keyname].Subtotal += parseFloat(a.Subtotal);

            if (!this[keyboxno]) {
                //this[a.product] = parseInt(Quantity);
                this[keyboxno] = {
                    box_no: a.box_no,
                };
                box_nos_arr.push(this[keyboxno]);
            }

        }, Object.create(null));
        console.log(result);
        console.log(box_nos_arr);

        //for display table with array of objects
        function renove() {

            $('#mytable').remove();
            var k = '';

            for (bn = 0; bn < box_nos_arr.length; bn++)
            {

                k += '<tr">';
                if(box_nos_arr[bn].box_no.length)
                {
                    k += '<td style="background-color: #EEEEEE !important;" colspan="7" width="100%"><b>&nbsp;&nbsp;Box No# ' +
                    box_nos_arr[bn].box_no +
                    '</b></td>';
                }
                else
                {
                    k += '<td style="background-color: #EEEEEE !important;" colspan="7" width="100%"><b>&nbsp;&nbsp;Box No# ' +
                    'N/A' +
                    '</b></td>';
                }
                k += '</tr>';

                for (i = 0; i < result.length; i++)
                {

                // k += '<tr">';
                // k += '<td text-align:center;" class="no" >' + 'BOX NO' + '</td>';
                // k += '<td text-align:left;" >' +'BOX # ' + sr  + '</td>';
                // k += '<td text-align:center;" >' + result[i].Qty + '</td>';
                // k += '<td text-align:center;" class="unit" >' + result[i].price + '</td>';
                // k += '<td text-align:center;" >' + result[i].tax + '</td>';
                // k += '<td text-align:center;" class="no">' + ' $ ' + result[i].subtotal.toFixed(2) +
                //     '</td>';
                // k += '</tr>';
                    if(box_nos_arr[bn].box_no == result[i].box_no)
                    {
                        /*if(result[i].Qty == 0){

                            k += '<tr class="order-list">';
                        }else{
                            k += '<tr">';
                        }*/

                        k += '<td style="text-align:center; " class="no" width="10%">' +
                            result[i].sr +
                            '</td>';
                        k += '<td style="text-align:center;font-size: 14px;"  width="30%">' + result[i].product + '</td>';
                        k += '<td style="text-align:center;" width="20%">' + result[i].item_code + '</td>';
                        k += '<td style="text-align:center;" width="20%">' + result[i].sku + '</td>';
                        k += '<td style="text-align:center;font-size: 14px;" width="5%" >' + result[i].Qty + '</td>';
                        k += '<td style="text-align:center;font-size: 14px;" class="unit" width="5%">' + ' $ ' + result[i].price + '</td>';
                        k += '<td style="text-align:center;" class="no" width="10%">'+ ' $ ' + result[i].Subtotal.toFixed(2) +
                            '</td>';

                        k += '</tr>';
                    }
                }
            }

            //k += '</tbody>';
            document.getElementById('tableData').innerHTML = k;
        }


        // setInterval(function(){
        //     console.log("Oooo Yeaaa!");
        renove();
        // }, 1000);//run this thang every 1 seconds

    // Qr code JS is in Show Invoice page .

    // $("#print_invoice").click(function () {
    //     $("#invoice_content").print();
    // });

    // $(document).ready(function(){
    //     $(document).on('click', '#print_invoice', function(){
    //         $('#invoice_content').printThis();
    //     });
    // });
    </script>