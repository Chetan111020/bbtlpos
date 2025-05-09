<style>
    page[size="A4"] {
        width: 21cm;
        height: 29.7cm;
    }

    page[size="A4"][layout="potrait"] {
        width: 29.7cm;
        height: 21cm;
    }

    @page {
        margin: 0.5cm;
        margin-right: 0.7cm;
        size: letter potrait;
    }

    table {
        width: 100%;
    }
    table.menu {
        width: auto;
        margin-right: 0px;
        margin-left: auto;
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
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}


</style>

<body>
    <input type="hidden" id="title_for_print" value="{{ $title ?? 'Credit Memo - {{ config('business-info.name') }}' }}">
    <div style="padding: 0px 0px; margin-top: 0px;
        margin-bottom: 0px;
        margin-left: 0px;
        margin-right: 0px;" class="table-breaked">
        <table>
            <thead>
                <tr>
                    <th>
                                <table style="width: 100%;border: none">
                    <tr>
                        <td style="text-align: right;vertical-align: top;">
                            <table style="border: none;width:100% ">

                                <tr>
                                    <td style="border: none;width:55%;">
                                        <table style="width: 120%; margin-bottom: 50px;">
                                            <tr>
                                                <td>
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
                                                </td>
                                                <td style="padding-left:5px;">
                                                    <h2
                                                        style="display:none;text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 16px; font-weight: 600; text-align: left;color: #060606">
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
                                                        style="text-transform: uppercase;padding:2px; margin-bottom:0px;margin-top:0px;font-size: 16px; font-weight: 600; text-align: center;color: #060606">
                                                        Customer Information </h2>
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
                                                            style="padding:4px;text-transform: uppercase; margin:0px;margin-top:0px;font-size: 14px; font-weight: 600; text-align: center;color: #060606">
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

                                                    <hr style="margin:0px;padding-top:3px;padding-bottom:3px;">
                                                    <span class="">
                                                        @if (!empty($receipt_details->additional_notes))
                                                            Order Note :{{ $receipt_details->additional_notes }}
                                                        @else
                                                            Order Note:
                                                             None
                                                        @endif
                                                    </span>
                                                    </h2>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="vertical-align: top; width:50px;">
                                        <table style="width: 65%; margin-left: 27%;"
                                            style="border: 1px solid #808080;">
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: center;">
                                                    <h2
                                                        style="padding:5px;text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 20px; color: #000000; font-weight: 600;">
                                                        Credit Memo
                                                    </h2>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: left;">
                                                    <h4
                                                        style="padding:5px;text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                            Credit Memo:
                                                        <span
                                                            style="padding:5px;margin-left: 74px;" class="pull-right">{{ $receipt_details->invoice_no }}</span>
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
                                                        style="padding:5px;text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                        {{ $receipt_details->date_label }} :
                                                        <span
                                                            style="padding:5px;margin-left: 17px;" class="pull-right">{{ $receipt_details->invoice_date }}</span>
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: left;">
                                                    <h4
                                                        style="padding:5px;text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                        Subtotal:
                                                        <span
                                                            style="padding:5px;margin-left: 56px;" class="pull-right">{{ $receipt_details->total }}
                                                            @if (!empty($receipt_details->total_in_words))
                                                                <br>
                                                                <small>({{ $receipt_details->total_in_words }})</small>
                                                            @endif
                                                        </span>
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;" class="hide">
                                                <td style="text-align: left;">
                                                    <h4
                                                        style="padding:5px;padding:5px;text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                        Payment Status :
                                                        <span style="padding:5px;padding:5px;margin-left: 66px;" class="pull-right">
                                                            @if (!empty($receipt_details->statuss))

                                                                {{ $receipt_details->statuss }}
                                                            @endif
                                                        </span>
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;" class="hide">
                                                <td style="text-align: center;">
                                                    <span>
                                                        <div style="padding:5px;padding: 5px; margin-left: 83px;" id="qrcode"></div>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: left;">
                                                    <h4
                                                        style="padding:5px;text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                        @if (!empty($receipt_details->tex))
                                                            Tax ID:
                                                            <span
                                                                style="padding:5px;margin-left: 14px;" class="pull-right">{{ $receipt_details->tex }}</span>
                                                        @else
                                                            Tax ID:
                                                            <span style="padding:5px;margin-left: 50px;" class="pull-right">None</span>
                                                        @endif
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr style="border: 1px solid #808080;">
                                                <td style="text-align: left;">
                                                    <h4
                                                        style="padding:5px;text-transform: uppercase; margin-bottom:0px;margin-top:0px;font-size: 14px; color: #000000; font-weight: 600;">
                                                        @if (!empty($receipt_details->tobacco_license_no))
                                                            Tobacco lic:
                                                            <span
                                                                style="padding:5px;" class="pull-right">{{ $receipt_details->tobacco_license_no }}</span>
                                                        @else
                                                            <span style="padding:5px;" >Tobacco lic:</span>
                                                            <span style="padding:5px;" class="pull-right">None</span>
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
                    </th>
                </tr>

                <!--<tr>-->
                <!--    <td>-->
                <!--        <table class="hide">-->
                <!--            <tr>-->
                <!--                <td style="width: 45%;">-->
                <!--                    <table>-->
                <!--                        <tr>-->
                <!--                            <td colspan="2" style="border: 1px solid;background-color: #c7c7c7 !important;">-->
                <!--                                <span-->
                <!--                                    style="align-items: center;font-weight: bold;margin-left: 30%;font-size: 13px;">Customer-->
                <!--                                    Information-->
                <!--                                </span>-->
                <!--                            </td>-->
                <!--                        </tr>-->
                <!--                        <tr>-->
                <!--                            <td style="border: 1px solid; width: 80px;background-color: #c7c7c7 !important;">-->
                <!--                                @if (!empty($receipt_details->customer_name))-->
                <!--                                    <span-->
                <!--                                        style="font-weight: 700; font-size: 13px;margin-left: 4px;">-->
                <!--                                        {{ $receipt_details->customer_label }}-->
                <!--                                    </span>:-->
                <!--                                @endif-->
                <!--                            </td>-->
                <!--                            <td style="border: 1px solid;">-->
                <!--                                @if (!empty($receipt_details->customer_name))-->
                <!--                                    <span-->
                <!--                                        style="font-weight: 700; font-size: 13px;margin-left: 3px;">-->
                <!--                                        {{ $receipt_details->customer_name }}-->
                <!--                                        ({{ $receipt_details->contact_id }})</span>-->
                <!--                                @endif-->
                <!--                            </td>-->
                <!--                        </tr>-->
                <!--                        <tr>-->
                <!--                            <td style="border: 1px solid; width: 80px;background-color: #c7c7c7 !important;">-->
                <!--                                <span-->
                <!--                                    style="font-weight: 700; font-size: 13px;margin-left: 4px;">Address:-->
                <!--                                </span>-->
                <!--                            </td>-->
                <!--                            <td style="border: 1px solid; font-size:12px;">-->

                <!--                                @if (!empty($receipt_details->address_line_1))-->
                <!--                                    <span style="margin-left: 3px;">-->
                <!--                                        {!! $receipt_details->address_line_1 !!}-->
                <!--                                    </span>,-->
                <!--                                @endif-->
                <!--                                @if (!empty($receipt_details->address_line_2))-->
                <!--                                    <span style="margin-left: 3px;">-->
                <!--                                        {!! $receipt_details->address_line_2 !!}-->
                <!--                                    </span>-->
                <!--                                @endif-->

                <!--                                @if (!empty($receipt_details->city))-->
                <!--                                    <span style="margin-left: 3px;">-->
                <!--                                        {!! $receipt_details->city !!}-->
                <!--                                    </span>-->
                <!--                                @endif-->
                <!--                                @if (!empty($receipt_details->state))-->
                <!--                                    <span style="margin-left: 3px;">-->
                <!--                                        {!! $receipt_details->state !!}-->
                <!--                                    </span>-->
                <!--                                @endif-->
                <!--                                @if (!empty($receipt_details->zip_code))-->
                <!--                                    <span style="margin-left: 3px;">-->
                <!--                                        {{ $receipt_details->zip_code }}-->
                <!--                                    </span>-->
                <!--                                @endif <br>-->
                <!--                                @if (!empty($receipt_details->mobile))-->

                <!--                                    <span style="margin-left: 3px;">-->
                <!--                                        Mobile:-->
                <!--                                        {{ $receipt_details->mobile }}-->
                <!--                                    </span>-->
                <!--                                @endif-->
                <!--                            </td>-->
                <!--                        </tr>-->

                <!--                    </table>-->
                <!--                </td>-->
                <!--                <td style=" padding-left: 10px; width: 60%;">-->
                <!--                    <table>-->
                <!--                        <tr class="hide"> -->
                <!--                            <td style="border: 1px solid;background-color: #c7c7c7 !important;">-->
                <!--                                <span-->
                <!--                                    style="align-items: center;font-weight: bold;margin-left: 30%;font-size: 13px;">-->
                <!--                                    Order Note:-->
                <!--                                </span>-->
                <!--                            </td>-->
                <!--                        </tr>-->
                <!--                        <tr>-->
                <!--                            <td style="border: 1px solid;">-->
                <!--                                @if (!empty($receipt_details->tex))-->
                <!--                                    <span-->
                <!--                                        style="font-weight: 600; margin-left:3px; font-size: 13px;margin-left: 4px;">Tax-->
                <!--                                        ID:-->
                <!--                                        {{ $receipt_details->tex }}</span>-->
                <!--                                        @else-->
                <!--                                       <span style="font-weight: 600; margin-left:3px; font-size: 13px;margin-left: 4px;">Tax-->
                <!--                                        ID:-->
                <!--                                         None</span>-->
                <!--                                @endif-->
                <!--                                @if (!empty($receipt_details->tobacco_license_no))-->
                <!--                                    <span-->
                <!--                                        style="font-weight: 600; margin-left: 15px; font-size: 13px; ;margin-left: 4px;">Tobacco-->
                <!--                                        lic:{{ $receipt_details->tobacco_license_no }}</span>-->
                <!--                                @else-->
                <!--                                    <span-->
                <!--                                        style="font-weight: 600; margin-left: 15px; font-size: 13px; ;margin-left: 4px;">Tobacco-->
                <!--                                        lic:NONE</span>-->

                <!--                                @endif <br>-->
                <!--                                @if (!empty($receipt_details->note))-->
                <!--                                    <span-->
                <!--                                        style="font-weight: 600;margin-left: 1px; margin-left:3px; font-size: 13px; ;margin-left: 4px;">Driver-->
                <!--                                        Note: {{ $receipt_details->note }}</span>-->
                <!--                                        @else-->
                <!--                                       <span style="font-weight: 600; margin-left:3px; font-size: 13px;margin-left: 4px;">Note:-->

                <!--                                         None</span>-->
                <!--                                @endif <br>-->
                <!--                                @if (!empty($receipt_details->additional_notes))-->
                <!--                                    <span-->
                <!--                                        style="font-weight: bold; font-size: 14px; margin-left:3px; font-size: 13px; ;margin-left: 4px;">Order-->
                <!--                                        Note :{{ $receipt_details->additional_notes }}-->
                <!--                                    </span>-->
                <!--                                    @else-->
                <!--                                       <span style="font-weight: 600; margin-left:3px; font-size: 13px;margin-left: 4px;">Order-->
                <!--                                        Note:-->
                <!--                                         None</span>-->
                <!--                                @endif-->

                <!--                            </td>-->
                <!--                        </tr>-->

                <!--                    </table>-->
                <!--                </td>-->
                <!--            </tr>-->
                <!--        </table>-->
                <!--    </td>-->
                <!--</tr>-->
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

            <table width="100%">
            <thead>
                <tr style=" font-size: 20px !important" class="text-center">
                   <td style="font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; font-size: 14px;">Sr no </td>

                    <td style="font-size: 14px;font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080;">
                        {{$receipt_details->table_product_label}}
                    </td>

                    @if($receipt_details->show_cat_code == 1)
                        <td style="font-size: 14px;font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080;" >{{$receipt_details->cat_code_label}}</td>
                    @endif

                    <td style="font-size: 14px;font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; ">
                        {{$receipt_details->table_qty_label}}
                    </td>
                    <td style="font-size: 14px;font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; ">
                       Unit Price (Box) /<br /> Unit Price (Piece)
                    </td>
                    <td style="font-size: 14px;font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080; ">
                       Unit Tax
                    </td>
                    <td style="font-size: 14px;font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080;" class='hide'>
                       @lang('Garbage Quantity')
                    </td>
                    <td style="font-size: 14px;font-weight: 700;text-align: center;vertical-align:top;border: 1px solid #808080;">
                        {{$receipt_details->table_subtotal_label}}
                    </td>
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

                @foreach($receipt_details->lines as $line)
                    <tr>
                        <td style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                            {{$loop->iteration}}
                        </td>
                       <td style="vertical-align: top;padding-left: 5px;text-align: left; border: 1px solid #808080; ">
                            {{$line['name']}} {{$line['variation']}}<br />
                            @if(!empty($line['sub_sku'])) {{$line['sub_sku']}} @endif @if(!empty($line['brand'])) {{$line['brand']}} @endif
                            @if(!empty($line['sell_line_note']))({{$line['sell_line_note']}}) @endif
                        </td>

                        @if($receipt_details->show_cat_code == 1)
                            <td style="vertical-align: top;padding-left: 10px;text-align: center; border: 1px solid #808080; ">
                                @if(!empty($line['cat_code']))
                                    {{$line['cat_code']}}
                                @endif
                            </td>
                        @endif

                      <td style="vertical-align: top;padding-left: 10px;text-align: left; border: 1px solid #808080; ">
                            @php
                            $gar_box_qty = (isset($line['quantity']) && $line['quantity']) ? $line['quantity'] : 0;
                            $gar_pcs_qty = (isset($line['gar_piece_return_qty']) && $line['gar_piece_return_qty']) ? $line['gar_piece_return_qty'] : 0;
                            $per_box_price = (isset($line['unit_price_inc_tax']) && $line['unit_price_inc_tax']) ? $line['unit_price_inc_tax'] : 0.00;
                            $per_pcs_price = (isset($line['gar_piece_return_price']) && $line['gar_piece_return_price']) ? $line['gar_piece_return_price'] : 0.00;
                            @endphp
                              {{round($gar_box_qty)}} Box
                              <!--<br/>-->

                              {{round($gar_pcs_qty)}} Piece

                            </td>
                            <td style="vertical-align: top;padding-left: 10px;text-align: left; border: 1px solid #808080; ">

                              ${{number_format($per_box_price,2)}} Per Box<br/>
                              $ {{number_format($per_pcs_price,2)}} Per Piece

                            </td>
                            <td style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
                              {{@format_quantity($line['pos_line_tax_amount'])}}
                            </td>
                            <td style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080;"  class='hide'>
                                {{@format_quantity($line['gar_box_return_qty'])}} {{$line['units']}}
                            </td>
                            <td style="vertical-align: top;padding-left: 5px;text-align: center; border: 1px solid #808080; ">
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
                {{--$total_sub += $line['line_total'];--}}
                    <tr>
                        <th></th>
                        <th class="text-center">Total Products: {{$total_products}}</th>
                        <th class="text-center">Total QTY<br>{{$total_qty_box}} Box,{{$total_qty_pcs}} Piece</th>
                        <th class="text-center">Total Unit Price<br>$ {{number_format($total_price_box,2)}} Per Box<br>$ {{number_format($total_price_pcs,2)}} Per Piece</th>
                        <th class="text-center">Total Tax <br>$ {{ number_format($total_state_tax,2) }} </th>
                        <th class="text-center hide">Total Garbage QTY<br>{{$total_gar_qty}}</th>
                        <th class="text-center">Net Total<br>$ {{number_format($total_sub,2)}}</th>
                    </tr>

            </tbody>

        </table>
        <br>
        <div style="width:200px">
            <table>
                <tr style="border: 1px solid #808080;">
                    <th style="width:75%;">@lang('lang_v1.box_qty')</th>
                    <td class="text-center" style="width:25%;border: 1px solid #808080;">{{ $receipt_details->box_qty ?? 0 }}</td>
                </tr>
            </table>
            <br/>
        </div>
        <br>
        <!--<div class="row invoice-info" style="width:100%;page-break-inside: avoid !important;margin-left:padding:5px;">-->
        <!--    <div style="width:50%; text-align: center;">-->
        <!--        <table>-->
        <!--            <tr>-->
        <!--                <td style="font-weight: 600;">-->
        <!--                  We greatly appreciate your support and business! Customers are-->
        <!--                  responsible for paying their-->
        <!--                  Local, State & Federal Excise taxes for applicable products.-->
        <!--                </td>-->
        <!--            </tr>-->
        <!--        </table>-->
        <!--        <table>-->
        <!--            <tr>-->
        <!--                <td style="font-weight: 600;">-->
        <!--                  <span style="color: red !important; font-weight: 600;"> We accept Cash, Check,-->
        <!--                      Zelle, and Wire Transfers</span>-->
        <!--                </td>-->
        <!--            </tr>-->
        <!--        </table>-->
        <!--        <table>-->
        <!--            <tr style="border: 1px solid #808080;">-->
        <!--                <td style="border: 1px solid #808080;"> <span style="color: red !important; font-weight: 600;">Zelle-->
        <!--                        Account</span></td>-->
        <!--            </tr>-->
        <!--            <tr style="border: 1px solid #808080;">-->
        <!--                <td style="border: 1px solid #808080;"> <span style="color: red !important; font-weight: 600;">Bank of America-->
        <!--                        Account #</span></td>-->
        <!--                <td>483019753020</td>-->
        <!--            </tr>-->
        <!--            <tr style="border: 1px solid #808080;">-->
        <!--                <td style="border: 1px solid #808080;"> <span style="color: red !important; font-weight: 600;">TD Bank Account-->
        <!--                        #</span></td>-->
        <!--                <td>4354363073</td>-->
        <!--            </tr>-->
        <!--        </table>-->
        <!--    </div>-->
        <!--    <div style='width:50%'>-->


        <!--    </div>-->
        <!--</div>-->

        <div class="row invoice-info" style="page-break-inside: avoid !important">

            <div class="col-md-6 invoice-col width-50">
                <table class="">
                    <tr>
                        <td style="font-weight: 600;text-align:center;">
                          We greatly appreciate your support and business! Customers are
                          responsible for paying their
                          Local, State & Federal Excise taxes for applicable products.
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td style="font-weight: 600;text-align:center;">
                          <span style="color: red !important; font-weight: 600;"> We accept Cash, Check,
                              Zelle, and Wire Transfers</span>
                        </td>
                    </tr>
                </table>
                <table >
                    <tr style="border: 1px solid #808080;text-align:center;">
                        <td style="border: 1px solid #808080;"> <span style="color: red !important; font-weight: 600;">Zelle
                                Account</span></td>
                        <td>{{ config('business-info.zelle_email') }}</td>
                    </tr>
                    <tr style="border: 1px solid #808080;text-align:center;display:none;">
                        <td style="border: 1px solid #808080;"> <span style="color: red !important; font-weight: 600;">TD Bank Account No
                                 #</span></td>
                        <td>4414232134</td>
                    </tr>
                    <tr style="border: 1px solid #808080;text-align:center;display:none;">
                        <td style="border: 1px solid #808080;"> <span style="color: red !important; font-weight: 600;">Routing No
                                #</span></td>
                        <td>0260-13673</td>
                    </tr>
                </table>
                <b class="pull-left" style="margin-top:20px;">Authorized Signatory</b>
            </div>

            <div class="col-md-6 invoice-col width-50 ">
                <table class="width-90 menu pull-right" style="margin-left:10%;">
                    <tbody style="padding:10px;">
                        <tr  style="border: 1px solid;">
                            <th style="border: 1px solid;padding-left:40px; padding-right:40px;text-align: right !important;">
                            <!--<span style="color: #000; font-weight: 600;" > -->
                            Subtotal:
                            <!--</span>-->
                            </th>
                            <td class="pull-right" style="padding-right:10px;">
                                {{ $receipt_details->subtotal }}
                            </td>
                        </tr>

                        <!-- Tax -->
                        <!-- state and city tax -->
                        @foreach ($tax_details as $tr)
                        <tr style="border: 1px solid;">
                            <th style="border: 1px solid; padding-left:20px; padding-right:40px;text-align: right !important;">
                                {{ $tr['name'] }}
                            </th>
                            <td class="pull-right" style="text-align: right;padding-right:10px;">
                                $ {{ $tr['tax'] }}
                            </td>
                        </tr>
                        @endforeach

                        <!-- Discount -->
                        @if( !empty($receipt_details->discount) )
                            <tr style="border: 1px solid;">
                                <th style="border: 1px solid;padding-left:40px; padding-right:40px;text-align: right !important;">
                                    <span style="color: #000; font-weight: 600;" > {!! $receipt_details->discount_label !!}&nbsp;:</span>
                                </th>

                                <td class="pull-right" style="padding-right:10px;">
                                    (-) {{$receipt_details->discount}}
                                </td>
                            </tr>
                        @endif

                        @if(!empty($receipt_details->group_tax_details))
                            @foreach($receipt_details->group_tax_details as $key => $value)
                                <tr style="border: 1px solid;">
                                    <td>
                                    <span style="color: #000;padding-left:40px; padding-right:40px;font-weight: 600;" >    {!! $key !!} </span>
                                    </td>
                                    <td class="pull-right" style="padding-right:10px;">
                                        (+) {{$value}}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @if( !empty($receipt_details->tax) )
                                <tr >
                                    <th style="border: 1px solid; padding-left:40px; padding-right:40px;text-align: right !important;">
                                    <span style="color: #000; font-weight: 600;" > {!! $receipt_details->tax_label !!} </span>
                                    </th>
                                    <td class="pull-right" style="padding-right:10px;">
                                        (+) {{$receipt_details->tax}}
                                    </td>
                                </tr>
                            @endif
                        @endif

                        <!-- Total -->
                        <tr style="border: 1px solid;">
                            <th style="border: 1px solid; padding-left:40px; padding-right:40px;text-align: right !important;">
                               <span style="color: #000; font-weight: 600;padding-right:10px;" >   Total :</span>
                            </th>
                            <td class="pull-right" style="padding-right:10px;">
                                {{$receipt_details->total}}
                            </td>
                        </tr>
                        <!-- Total Paid -->
                        @if(!empty($paid_data->total_paid))
                        <tr style="border: 1px solid;">
                            <th style="border: 1px solid; padding-left:40px; padding-right:40px;text-align: right !important;">
                                <span style="color: #000; font-weight: 600;padding-right:10px;" >   Total Paid :</span>
                            </th>
                            <td class="pull-right" style="padding-right:10px;">

                                {{$paid_data->total_paid}}
                            </td>
                        </tr>
                        @endif
                        <!-- Total Due -->
                        @if(!empty($paid_data->total_due))
                        <tr style="border: 1px solid;">
                            <th style="border: 1px solid;padding-left:40px; padding-right:40px; text-align: right !important;">
                            <span style="color: #000; font-weight: 600;padding-right:10px;" >   Amount Due :</span>
                            </th>
                            <td class="pull-right" style="padding-right:10px;">

                                {{$paid_data->total_due}}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        </td>
        </tr>
        </table>
            <br>

            <br>
            </tbody>
            </table>
    </div>
</body>