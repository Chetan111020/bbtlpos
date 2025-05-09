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
</style>
<div style="padding: 15px 50px;" >
    <table style="width: 100%;border: none;">
        <tr>
            <td style="border: none;">
                <table style="width: 100%;border: none">
                    <tr>
                        <td colspan="" style="text-align: right;vertical-align: top;border: none;">
                            <table style="border: none;width:100%">
                                <tr>
                                    <td style="border: none;width:38%;text-align: center;">
                                        <!-- Logo -->
                                        @if(!empty($receipt_details->logo))
                                        <img src="{{$receipt_details->logo}}" class="img img-responsive center-block">
                                        @endif
                                        <!-- Header text -->
                                        @if(!empty($receipt_details->header_text))
                                        <div class="col-xs-12">
                                            {!! $receipt_details->header_text !!}
                                        </div>
                                        @endif
                                        <h2
                                            style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 25px; font-weight: 600; text-align: left;color: #060606">
                                            <!-- Shop & Location Name  -->
                                            @if(!empty($receipt_details->display_name))
                                            {{$receipt_details->display_name}}
                                            @endif
                                        </h2>
                                        <p style="font-size: 13px; text-align:left;">
                                            @if(!empty($receipt_details->address))
                                            <small class="text-center">
                                                {!! $receipt_details->address !!}
                                            </small>
                                            @endif
                                            @if(!empty($receipt_details->contact))
                                            <br />{{ $receipt_details->contact }}
                                            @endif
                                            @if(!empty($receipt_details->contact) && !empty($receipt_details->website))
                                            ,
                                            @endif
                                            @if(!empty($receipt_details->website))
                                            {{ $receipt_details->website }}
                                            @endif
                                            @if(!empty($receipt_details->location_custom_fields))
                                            <br>{{ $receipt_details->location_custom_fields }}
                                            @endif
                                        </p>
                                        <p>
                                            @if(!empty($receipt_details->sub_heading_line1))
                                            {{ $receipt_details->sub_heading_line1 }}
                                            @endif
                                            @if(!empty($receipt_details->sub_heading_line2))
                                            <br>{{ $receipt_details->sub_heading_line2 }}
                                            @endif
                                            @if(!empty($receipt_details->sub_heading_line3))
                                            <br>{{ $receipt_details->sub_heading_line3 }}
                                            @endif
                                            @if(!empty($receipt_details->sub_heading_line4))
                                            <br>{{ $receipt_details->sub_heading_line4 }}
                                            @endif
                                            @if(!empty($receipt_details->sub_heading_line5))
                                            <br>{{ $receipt_details->sub_heading_line5 }}
                                            @endif
                                        </p>
                                        <p>
                                            @if(!empty($receipt_details->tax_info1))
                                            <b>{{ $receipt_details->tax_label1 }}</b> {{ $receipt_details->tax_info1 }}
                                            @endif

                                            @if(!empty($receipt_details->tax_info2))
                                            <b>{{ $receipt_details->tax_label2 }}</b> {{ $receipt_details->tax_info2 }}
                                            @endif
                                        </p>
                                    </td>
                                    <td style="border: none;vertical-align: top;padding-left: 18px">
                                        <h2
                                            style="text-transform: uppercase; margin-bottom:3px;margin-top:8px;font-size: 20px; color: #000000; font-weight: 600;">
                                            @if(!empty($receipt_details->invoice_heading))
                                            {!! $receipt_details->invoice_heading !!}
                                            @endif
                                        </h2>
                                        <p
                                            style="margin-left: 43%;line-height:15px;font-size:12px;text-align: left; padding-left: 26px;">
                                            <br>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <table>
                            <tr>
                                <td>
                                    <table>
                                        <tr style="border: 1px solid #808080;">
                                            <td>
                                                <span style="margin-left: 3px;"><b>{{$receipt_details->date_label}} :<br></b> {{$receipt_details->invoice_date}}
                                                </span>
                                            </td>
                                            <td style="border-left: 1px solid #808080;">
                                                <span
                                                    style="margin-left: 3px;">@if(!empty($receipt_details->invoice_no_prefix))
                                                    <b>{!! $receipt_details->invoice_no_prefix !!}</b>
                                                    @endif <br>
                                                    <p style="margin-left: 3px;">{{$receipt_details->invoice_no}}</p>
                                                    @if(!empty($receipt_details->types_of_service))
                                                    <br />
                                                    <span style="margin-left: 3px;" class="pull-left text-left">
                                                        <strong>{!! $receipt_details->types_of_service_label
                                                            !!}:
                                                        </strong>
                                                        {{$receipt_details->types_of_service}}
                                                        <!-- Waiter info -->
                                                        @if(!empty($receipt_details->types_of_service_custom_fields))
                                                        @foreach($receipt_details->types_of_service_custom_fields as
                                                        $key => $value)
                                                        <br><strong>{{$key}}:</strong> {{$value}}
                                                        @endforeach
                                                        @endif
                                                    </span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>

                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </tr>
                    <tr>
                    </tr>
                    <tr>
                        <td colspan="" style="text-align: center; border:none;">
                            <table style="border: none;">
                                <tr>
                                    <td style="vertical-align: top;width: 50%;border: none;padding: 5px">
                                        <table>

                                            <tr>
                                                <td style="vertical-align: top;padding-left: 5px;text-align: left; ">
                                                    <!-- Table information-->
                                                    @if(!empty($receipt_details->table_label) ||
                                                    !empty($receipt_details->table))

                                                    <span class="pull-left text-left">
                                                        @if(!empty($receipt_details->table_label))
                                                        <b>{!! $receipt_details->table_label !!}</b>
                                                        @endif
                                                        {{$receipt_details->table}}
                                                        <!-- Waiter info -->
                                                    </span>
                                                    @endif
                                                    <!-- customer info -->
                                                    @if(!empty($receipt_details->customer_name))
                                                    <b>
                                                        <span
                                                            style="font-weight: 700; font-size: 12px;">{{ $receipt_details->customer_label }}
                                                            : <br> {{ $receipt_details->customer_name }} ({{$receipt_details->contact_id}})<br></b></span>

                                                    @endif
                                                    <span
                                                        style="color: #000;">@if(!empty($receipt_details->customer_info))
                                                        {!! $receipt_details->customer_info !!}
                                                        @endif
                                                    </span>
                                                    @if(!empty($receipt_details->client_id_label))
                                                    <br />
                                                    {{ $receipt_details->client_id_label }}
                                                    {{ $receipt_details->client_id }}
                                                    @endif
                                                    @if(!empty($receipt_details->customer_tax_label))
                                                    <br />
                                                    {{ $receipt_details->customer_tax_label }}
                                                    {{ $receipt_details->customer_tax_number }}
                                                    @endif
                                                    @if(!empty($receipt_details->customer_custom_fields))
                                                    <br />{!! $receipt_details->customer_custom_fields !!}
                                                    @endif
                                                    @if(!empty($receipt_details->sales_person_label))
                                                    <br />
                                                    {{ $receipt_details->sales_person_label }}
                                                    {{ $receipt_details->sales_person }}
                                                    @endif
                                                    @if(!empty($receipt_details->customer_rp_label))
                                                    <br />
                                                    <strong>{{ $receipt_details->customer_rp_label }}</strong>
                                                    {{ $receipt_details->customer_total_rp }}
                                                    @endif
                                                    @if(!empty($receipt_details->due_date_label))
                                                    <br><b>{{$receipt_details->due_date_label}}</b>
                                                    {{$receipt_details->due_date ?? ''}}
                                                    @endif
                                                    @if(!empty($receipt_details->brand_label) ||
                                                    !empty($receipt_details->repair_brand))
                                                    <br>
                                                    @if(!empty($receipt_details->brand_label))
                                                    <b>{!! $receipt_details->brand_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->repair_brand}}
                                                    @endif

                                                    @if(!empty($receipt_details->device_label) ||
                                                    !empty($receipt_details->repair_device))
                                                    <br>
                                                    @if(!empty($receipt_details->device_label))
                                                    <b>{!! $receipt_details->device_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->repair_device}}
                                                    @endif

                                                    @if(!empty($receipt_details->model_no_label) ||
                                                    !empty($receipt_details->repair_model_no))
                                                    <br>
                                                    @if(!empty($receipt_details->model_no_label))
                                                    <b>{!! $receipt_details->model_no_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->repair_model_no}}
                                                    @endif

                                                    @if(!empty($receipt_details->serial_no_label) ||
                                                    !empty($receipt_details->repair_serial_no))
                                                    <br>
                                                    @if(!empty($receipt_details->serial_no_label))
                                                    <b>{!! $receipt_details->serial_no_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->repair_serial_no}}<br>
                                                    @endif
                                                    @if(!empty($receipt_details->repair_status_label) ||
                                                    !empty($receipt_details->repair_status))
                                                    @if(!empty($receipt_details->repair_status_label))
                                                    <b>{!! $receipt_details->repair_status_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->repair_status}}<br>
                                                    @endif

                                                    @if(!empty($receipt_details->repair_warranty_label) ||
                                                    !empty($receipt_details->repair_warranty))
                                                    @if(!empty($receipt_details->repair_warranty_label))
                                                    <b>{!! $receipt_details->repair_warranty_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->repair_warranty}}
                                                    <br>
                                                    @endif
                                                    <!-- Waiter info -->
                                                    @if(!empty($receipt_details->service_staff_label) ||
                                                    !empty($receipt_details->service_staff))
                                                    <br />
                                                    @if(!empty($receipt_details->service_staff_label))
                                                    <b>{!! $receipt_details->service_staff_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->service_staff}}
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="width: 50%;vertical-align: top;border: none ;padding: 5px">
                                        <table>
                                            <tr>
                                                <td style="vertical-align: top;padding-left: 5px;text-align: left;">
                                                    <!-- Table information-->
                                                    @if(!empty($receipt_details->table_label) ||
                                                    !empty($receipt_details->table))

                                                    <span class="pull-left text-left">
                                                        @if(!empty($receipt_details->table_label))
                                                        <b>{!! $receipt_details->table_label !!}</b>
                                                        @endif
                                                        {{$receipt_details->table}}
                                                        <!-- Waiter info -->
                                                    </span>
                                                    @endif
                                                    <!-- customer info -->
                                                    @if(!empty($receipt_details->customer_name))
                                                    <b>
                                                        <span style="font-weight: 700; font-size: 12px;">Ship To : <br>
                                                            {{ $receipt_details->customer_name }} <br></b></span>

                                                    @endif
                                                    <span
                                                        style="color: #000;">
                                                        @if(!empty($receipt_details->customer_info))
                                                        {!! $receipt_details->customer_info !!}
                                                        @endif
                                                    </span>
                                                    @if(!empty($receipt_details->client_id_label))
                                                    <br />
                                                    {{ $receipt_details->client_id_label }}
                                                    {{ $receipt_details->client_id }}
                                                    @endif
                                                    @if(!empty($receipt_details->customer_tax_label))
                                                    <br />
                                                    {{ $receipt_details->customer_tax_label }}
                                                    {{ $receipt_details->customer_tax_number }}
                                                    @endif
                                                    @if(!empty($receipt_details->customer_custom_fields))
                                                    <br />{!! $receipt_details->customer_custom_fields !!}
                                                    @endif
                                                    @if(!empty($receipt_details->sales_person_label))
                                                    <br />
                                                    {{ $receipt_details->sales_person_label }}
                                                    {{ $receipt_details->sales_person }}
                                                    @endif
                                                    @if(!empty($receipt_details->customer_rp_label))
                                                    <br />
                                                    <strong>{{ $receipt_details->customer_rp_label }}</strong>
                                                    {{ $receipt_details->customer_total_rp }}
                                                    @endif
                                                    @if(!empty($receipt_details->due_date_label))
                                                    <br><b>{{$receipt_details->due_date_label}}</b>
                                                    {{$receipt_details->due_date ?? ''}}
                                                    @endif
                                                    @if(!empty($receipt_details->brand_label) ||
                                                    !empty($receipt_details->repair_brand))
                                                    <br>
                                                    @if(!empty($receipt_details->brand_label))
                                                    <b>{!! $receipt_details->brand_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->repair_brand}}
                                                    @endif

                                                    @if(!empty($receipt_details->device_label) ||
                                                    !empty($receipt_details->repair_device))
                                                    <br>
                                                    @if(!empty($receipt_details->device_label))
                                                    <b>{!! $receipt_details->device_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->repair_device}}
                                                    @endif

                                                    @if(!empty($receipt_details->model_no_label) ||
                                                    !empty($receipt_details->repair_model_no))
                                                    <br>
                                                    @if(!empty($receipt_details->model_no_label))
                                                    <b>{!! $receipt_details->model_no_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->repair_model_no}}
                                                    @endif

                                                    @if(!empty($receipt_details->serial_no_label) ||
                                                    !empty($receipt_details->repair_serial_no))
                                                    <br>
                                                    @if(!empty($receipt_details->serial_no_label))
                                                    <b>{!! $receipt_details->serial_no_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->repair_serial_no}}<br>
                                                    @endif
                                                    @if(!empty($receipt_details->repair_status_label) ||
                                                    !empty($receipt_details->repair_status))
                                                    @if(!empty($receipt_details->repair_status_label))
                                                    <b>{!! $receipt_details->repair_status_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->repair_status}}<br>
                                                    @endif

                                                    @if(!empty($receipt_details->repair_warranty_label) ||
                                                    !empty($receipt_details->repair_warranty))
                                                    @if(!empty($receipt_details->repair_warranty_label))
                                                    <b>{!! $receipt_details->repair_warranty_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->repair_warranty}}
                                                    <br>
                                                    @endif
                                                    <!-- Waiter info -->
                                                    @if(!empty($receipt_details->service_staff_label) ||
                                                    !empty($receipt_details->service_staff))
                                                    <br />
                                                    @if(!empty($receipt_details->service_staff_label))
                                                    <b>{!! $receipt_details->service_staff_label !!}</b>
                                                    @endif
                                                    {{$receipt_details->service_staff}}
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
                <div class="row">
                    @includeIf('sale_pos.receipts.partial.common_repair_invoice')
                </div>

                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
                
                <table style="width: 100%;padding:2px;" >
                    <thead  >
                        <tr>
                            <td style="font-weight: 700;text-align: left;vertical-align:top;border: 1px solid #808080; border-left: none;">Sr</td>
                            <td style="font-weight: 700;vertical-align:top;border: 1px solid #808080; border-left: none;">Product</td>
                            <td style="font-weight: 700;text-align: left;vertical-align:top;border: 1px solid #808080; border-left: none;">Quantity</td>
                            <td style="font-weight: 700;text-align: left;vertical-align:top;border: 1px solid #808080; border-left: none; width: 70px;">Price</td>
                            <td style="font-weight: 700;text-align: left;vertical-align:top;border: 1px solid #808080; border-right: none; width: 70px;">Subtotal</td>
                        </tr>
                    </thead>
                    <?php
					$sr = 1;
					?>
                    <tbody id="tableData"> </tbody>
                    <tbody id="mytable" >
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
                                $sub_category= '';
                                $subcat_id = 0;
                                if(@$line['cat']['name'] == 'CIGAR'){
                                    if($line['sub_cat']) {
                                        $sub_category = $line['sub_cat']['name'];
                                        $subcat_id = $line['sub_cat']['id'];
                                    }
                                    if(count($subcategory_amounts) > 0){
                                        for ($i=0; $i < count($subcategory_amounts); $i++) { 
                                            if($subcategory_amounts[$i]['id'] ==  $subcat_id){
                                                $subcategory_amounts[$i]['amount'] = $subcategory_amounts[$i]['amount'] + $line['line_total'];
                                                $is_present = true;
                                            } 
                                        }
                                        if($is_present == false) { 
                                            $name = $line['cat']['name']."-".$sub_category;
                                            $obj = array_merge( $obj, array( 'id' => $subcat_id, 'amount' => $line['line_total'], 'name' => $name) );
                                            if(count($subcategory_amounts)>0){
                                            array_push($subcategory_amounts, $obj);
                                        }
                                        }
                                    } else {
                                        $name = $line['cat']['name']."-".$sub_category;
                                        $obj = array_merge( $obj, array( 'id' =>$subcat_id, 'amount' => $line['line_total'], 'name' => $name) );
                                        if(count($subcategory_amounts)>0){
                                            array_push($subcategory_amounts, $obj);
                                        }
                                    }
                                    
                                    
                                        if($sub_category == 'Mini')  $mini_cigar = $mini_cigar + round($line['quantity']);
                                        if($sub_category == 'Regular')  $regular_cigar = $regular_cigar  + round($line['quantity']);
                                    
                                    if($line['qty_box']>0) $sticks_qty = round($line['quantity']) * $line['qty_box'];
                                    if($sub_category == 'Mini')  $mini_cigar_qty = $mini_cigar_qty + $sticks_qty;
                                    if($sub_category == 'Regular')  $regular_cigar_qty = $regular_cigar_qty  + $sticks_qty;
                                }
                            ?>
                            <td style="vertical-align: top;padding-left: 5px;text-align: left;">{{$sr++}}</td>
                            <td style="vertical-align: top;padding-left: 5px;text-align: left;"><!-- @if(!empty($line['image']))
                                <img src="{{$line['image']}}" alt="Image" width="50"
                                    style="float: left; margin-right: 8px;">
                                @endif --><?php $key="DESIGNER FLORAL DIAMOND PRINT WP –IFBR" ?>@if($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF PEACH -BOX OF 10'){{$key}}@elseif($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF LYCHEE ICE -BOX OF 10'){{$key}}@elseif($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF BLUEBERRY ICE -BOX OF 10')
                                {{$key}}@elseif($line['name'] == 'INFINITE BAR DISPOSABLE 5000PF LUSH ICE -BOX OF 10'){{$key}} 
                                @elseif(strpos($line['name'],'CUBANO')!== false){{$line['name']}}@elseif(strpos($line['name'],'CLEAR')!== false){{$line['name']}}@elseif($line['name'] == 'JUUL PS 3% CLASSIC MENTHOL -BOX OF 8')
                                {{"FANCY HAMMER GLASS BUBBLER 8CT JAR –JMB3"}}                           
                                @elseif($line['name'] == 'JUUL PS 3% CLASSIC MENTHOL- CASE OF 6')
                                {{"SMALL DOUBLE GLASS BUBBLER 48CT JAR –JMC3"}}@elseif($line['name'] == 'JUUL PS 5% CLASSIC MENTHOL-BOX OF 8')
                                {{"FUMED JUMBO SWIRL SPOON PIPE 8CT JAR –JMB5"}}@elseif($line['name'] == 'JUUL PS 5% CLASSIC MENTHOL- CASE OF 6')
                                {{"FUMED JUMBO SWIRL SPOON PIPE 48CT JAR –JMC5"}}@elseif($line['name'] == 'JUUL PS 5% CLASSIC MENTHOL 2 PACK-BOX OF 8')
                                {{"FUMED REGULAR SWIRL SPOON PIPE 8CT JAR –JMB2PK5"}}@elseif($line['name'] == 'JUUL PS 3% VIRGINIA TOBACCO-BOX OF 8')
                                {{"DOUBLE GLASS FUMED CHILLUM PIPE 8CT JAR –JVTB3"}}@elseif($line['name'] == 'JUUL PS 3% VIRGINIA TOBACCO- CASE OF 6')
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
                                {{"GRAV LABS BOWL WITH  14MM MALE 48CT JAR –JVTB2PK5"}}  
				@elseif(strpos($line['name'],'TOBACCO')!== false){{$line['name']}}                             
                                @elseif($line['name'] == 'BLU PLUS TANK 2.4% MENTHOL-BOX OF 5')
                                {{"9 GLOWFY GLASS FLARED NECK BEAKER WP -MBPT"}}                                
                                @elseif($line['name'] == 'VUSE ALTO POD MENTHOL 1.8 0.2M')
                                {{"10 HAND BLOWN GLASS RECYCLER RED M18"}}                                
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
                                @elseif($line['name'] == 'LGC PRO MET 20MG')
                                {{"7' BUBBLER SOFT GLASS 10CT JAR -LPRM"}}  
                                @elseif($line['name'] == 'LGC PWR MET 27MG')
                                {{"4.5' FRIT COLORFUL HANDPIPE-LPWM"}}
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
                                @elseif(strpos($line['name'],'LUTO XL DISPOSABLE')!== false)
                                {{"12 WPS CONICAL MULTI STICKERS LTX"}}                                
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

                                @elseif(strpos($line['name'],'LGC PRO MET 20MG')!== false)
                                {{"7' BUBBLER SOFT GLASS 10CT JAR -LPRM"}}

                                @elseif(strpos($line['name'],'LGC PWR MET 27MG')!== false)
                                {{"4.5' FRIT COLORFUL HANDPIPE-LPWM"}}

				@elseif(strpos($line['name'],'GLAMEE NOVA DISPOSABLE')!== false)
                                {{"5 HEAD FRIT DOUBLE RIM HAND PIPES WITH DOME 25CT - GND"}}
                                 @elseif(strpos($line['name'],'MYLE MINI DISPOSABLE')!== false)
                                {{"WATER PRINT GLASS PIPE-MM"}} 


                                 @elseif(strpos($line['name'],'MYLE MINI DISPOSABLE')!== false)
                                 {{"14' DESIGNER FLORAL DIAMOND PRINT WP –IFBR"}}
				

				@else{{$line['name']}}@endif
                                
                               <!-- {{$line['product_variation']}} {{$line['variation']}}
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
                                    {{$line['warranty_description'] ?? ''}}</small>@endif -->
                            </td>
                            <td style="vertical-align: top;padding-left: 5px;text-align: left;">{{ round($line['quantity'])}}</td>
                            <td style="vertical-align: top;padding-left: 5px;text-align: left;">$ {{$line['unit_price_inc_tax']}}</td>
                            <td style="vertical-align: top;padding-left: 5px;text-align: left;"> {{$line['line_total']}}</td>
                        </tr>
                         
                         
                        @if(!empty($line['modifiers']))
                        @foreach($line['modifiers'] as $modifier)
                        <tr>
                            <td>
                                {{$modifier['name']}} {{$modifier['variation']}}
                                @if(!empty($modifier['sub_sku'])), {{$modifier['sub_sku']}} @endif
                                @if(!empty($modifier['cat_code'])), {{$modifier['cat_code']}}@endif
                                @if(!empty($modifier['sell_line_note']))({{$modifier['sell_line_note']}}) @endif
                            </td>
                            <td class="text-right">{{$modifier['quantity']}} {{$modifier['units']}} </td>
                            <td class="text-right">{{$modifier['unit_price_inc_tax']}}</td>
                            <td class="text-right">{{$modifier['line_total']}}</td>
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
            <table style="width: 100%;border-top: 1px solid #808080; margin-top: 8px;">
                <tr>
                    <td colspan="" style="text-align: center;border:none">
                        <table style="border: none;">
                            <tr>
                                <td style="vertical-align: top;width: 50%;border: none;">
                                    <table>
                                        <tr>
                                          <td style="vertical-align: top;padding-left: 5px;text-align: left;">

                                         </td>
                                    </tr>
                                    </table>
                                </td>
                                <td style="width: 50%;vertical-align: top;border: none; ">
                                    @if(!empty($receipt_details->payments))
                                    @foreach($receipt_details->payments as $payment)
                                </td>
                            </tr>        
                            <tr>
                                <td>{{$payment['method']}}</td>
                                <td class="text-right">{{$payment['amount']}}</td>
                                <td>{{$payment['date']}}</td>
                            </tr>
                            @endforeach
                            @endif
                            <div class="row">
                                <div class="col-md-6">
                                    <table>
                                        @if($mini_cigar>0)
                                        <tr>
                                            <td style="text-align: right">Mini Cigar:</td>
                                            <td style="text-align: right">{{$mini_cigar}}</td>
                                        </tr> 
                                        @endif
                                        @if($regular_cigar>0)
                                        <tr>
                                            <td style="text-align: right">Regular Cigar:</td>
                                            <td style="text-align: right">{{$regular_cigar}}</td>
                                        </tr>
                                        @endif
                                        @if($mini_cigar_qty > 0)
                                        <tr>
                                            <td style="text-align: right">Sticks for Mini:</td>
                                            <td style="text-align: right">{{$mini_cigar_qty}}</td>
                                        </tr>     
                                        @endif 
                                        @if($regular_cigar_qty > 0)
                                        <tr>
                                            <td style="text-align: right">Sticks for Regular:</td>
                                            <td style="text-align: right">{{$regular_cigar_qty}}</td>
                                        </tr>     
                                        @endif
                                         <!-- Amount subcat wise  -->
                                        @foreach($subcategory_amounts as $amounts)
                                            <tr>
                                                <td style="text-align: right !important;">
                                                    {{$amounts['name']}}:
                                                </td>
                                                <td class="text-right">
                                                    $ {{$amounts['amount']}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table>
                                @if(!empty($receipt_details->total_paid))
                                <tr>
                                    <th style="text-align: right !important;">
                                        {!! $receipt_details->total_paid_label !!}
                                    </th>
                                    <td class="text-right">
                                        {{$receipt_details->total_paid}}
                                    </td>
                                </tr>
                                @endif
                                <!-- Total Due-->
                                @if(!empty($receipt_details->total_due))
                                <tr>
                                    <th style="text-align: right !important;">
                                        {!! $receipt_details->total_due_label !!}
                                    </th>
                                    <td class="text-right">
                                        {{$receipt_details->total_due}}
                                    </td>
                                </tr>
                                @endif
                                @if(!empty($receipt_details->all_due))
                                <tr>
                                    <th style="text-align: right !important;">
                                        {!! $receipt_details->all_bal_label !!}
                                    </th>
                                    <td class="text-right">
                                        {{$receipt_details->all_due}}
                                    </td>
                                </tr>
                                @endif
                                @if(!empty($receipt_details->total_quantity_label))
                                <tr class="color-555">
                                    <th style="width:70%" style="text-align: right !important;">
                                        {!! $receipt_details->total_quantity_label !!}
                                    </th>
                                    <td class="text-right">
                                        {{$receipt_details->total_quantity}}
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th style="width:70%; text-align: right !important;">
                                        {!! $receipt_details->subtotal_label !!}
                                    </th>
                                    <td class="text-right">
                                        {{$receipt_details->subtotal}}
                                    </td>
                                </tr>
                                @if(!empty($receipt_details->total_exempt_uf))
                                <tr>
                                    <th style="width:70%" style="text-align: right !important;">
                                        @lang('lang_v1.exempt')
                                    </th>
                                    <td class="text-right">
                                        {{$receipt_details->total_exempt}}
                                    </td>
                                </tr>
                                @endif
                                <!-- Shipping Charges -->
                                @if(!empty($receipt_details->shipping_charges))
                                <tr>
                                    <th style="width:70%" style="text-align: right !important;">
                                        {!! $receipt_details->shipping_charges_label !!}
                                    </th>
                                    <td class="text-right">
                                        {{$receipt_details->shipping_charges}}
                                    </td>
                                </tr>
                                @endif
                                @if(!empty($receipt_details->packing_charge))
                                <tr>
                                    <th style="width:70%" style="text-align: right !important;">
                                        {!! $receipt_details->packing_charge_label !!}
                                    </th>
                                    <td class="text-right">
                                        {{$receipt_details->packing_charge}}
                                    </td>
                                </tr>
                                @endif
                                <!-- Discount -->
                                @if( !empty($receipt_details->discount) )
                                <tr>
                                    <th style="text-align: right !important;">
                                        {!! $receipt_details->discount_label !!}
                                    </th>

                                    <td class="text-right">
                                        (-) {{$receipt_details->discount}}
                                    </td>
                                </tr>
                                @endif

                                @if( !empty($receipt_details->reward_point_label) )
                                <tr>
                                    <th style="text-align: right !important;">
                                        {!! $receipt_details->reward_point_label !!}
                                    </th>

                                    <td class="text-right">
                                        (-) {{$receipt_details->reward_point_amount}}
                                    </td>
                                </tr>
                                @endif

                                <!-- Tax -->
                                @if( !empty($receipt_details->tax) )
                                <tr>
                                    <th style="text-align: right !important;">
                                        {!! $receipt_details->tax_label !!}
                                    </th>
                                    <td class="text-right">
                                        (+) {{$receipt_details->tax}}
                                    </td>
                                </tr>
                                @endif

                                @if( $receipt_details->round_off_amount > 0)
                                <tr>
                                    <th style="text-align: right !important;">
                                        {!! $receipt_details->round_off_label !!}
                                    </th>
                                    <td class="text-right">
                                        {{$receipt_details->round_off}}
                                    </td>
                                </tr>
                                @endif
                                 <!-- state and city tax -->
                                        @foreach($tax_details as $tr)
                                            <tr>
                                                <th style="text-align: right !important;">
                                                    {{$tr['name']}}
                                                </th>
                                                <td class="text-right">
                                                    $ {{$tr['tax']}}
                                                </td>
                                            </tr>
                                        @endforeach
                                <!-- Total -->
                                <tr>
                                    <th style="text-align: right !important;">
                                        {!! $receipt_details->total_label !!}
                                    </th>
                                    <td class="text-right">
                                        {{$receipt_details->total}}
                                        @if(!empty($receipt_details->total_in_words))
                                        <br>
                                        <small>({{$receipt_details->total_in_words}})</small>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                                </div>
                            </div>
                    </td>
                </tr>
            </table>
            </td>
        </tr>
    </table>
    </tr>
    </table> <br><br>
    <footer>
        <hr>
        <table style="text-align: center;">
            <tr>
                <td><b>
                        We greatly appreciate your support and business! Customers are responsible for paying their
                        Local, State & Federal Excise taxes for applicable products.
                    </b></td>
            </tr>
        </table>
    </footer>
</div>
<script type="text/javascript">
    
//for read the table data
var details = $('tbody#mytable tr').map(function(i, row) {
  return {
    //'sr': row.cells[0].textContent,
    'product': row.cells[1].textContent.trim(),
    'Quantity': row.cells[2].textContent.trim(),
    'price': row.cells[3].textContent.trim(),
    'subtotal': row.cells[4].textContent.toString().replace(',', '')

  }
}).get();
//console.log(details);


//for merging duplicates 
result = [];
details.forEach(function (a) {
    if (!this[a.product]) {
        //this[a.product] = parseInt(Quantity);
        this[a.product] = { product: a.product, Quantity: 0,price:a.price, subtotal: 0};
        result.push(this[a.product]);
    }
    this[a.product].Quantity += parseInt(a.Quantity);
    this[a.product].subtotal += parseFloat(a.subtotal);

}, Object.create(null));
//console.log(result);


//for display table with array of objects 
function renove() {

$('#mytable').remove();
var sr = 0;
var k = '<tbody>'
    for(i = 0;i < result.length; i++)
    {
        sr = sr + 1;
        k+= '<tr>';
        k+= '<td>' + sr + '</td>';
        k+= '<td>' + result[i].product + '</td>';
        k+= '<td>' + result[i].Quantity + '</td>';
        k+= '<td>' + result[i].price + '</td>';
        k+= '<td>' +' $ ' + result[i].subtotal.toFixed(2) + '</td>';
        k+= '</tr>';
    }
    k+='</tbody>';
    document.getElementById('tableData').innerHTML = k;
}

 
setInterval(function(){ 
    console.log("Oooo Yeaaa!");
    renove();
}, 1000);//run this thang every 1 seconds

</script>
