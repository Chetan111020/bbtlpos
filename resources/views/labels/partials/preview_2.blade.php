@php
    $rotate_css = "";
    if(isset($barcode_details->is_horizontal) && $barcode_details->is_horizontal == 1){
        $rotate_css = "transform: rotate(90deg);";
    }
@endphp
<table align="center" style="{{$rotate_css}} border-spacing: {{$barcode_details->col_distance * 1}}in {{$barcode_details->row_distance * 1}}in; overflow: hidden !important;">
    @foreach($page_products as $page_product)

        @if($loop->index % $barcode_details->stickers_in_one_row == 0)
            <!-- create a new row -->
            <tr>
            <!-- <columns column-count="{{$barcode_details->stickers_in_one_row}}" column-gap="{{$barcode_details->col_distance*1}}"> -->
        @endif
            <td align="center" valign="center">
                <div style="overflow: hidden !important;display: block; flex-wrap: wrap;align-content: center;width: {{$barcode_details->width * 1}}in; height: {{$barcode_details->height * 1}}in;">


                    <div style="display: flex; flex-direction: row-reverse;">
                        <div>
                        {{-- Business Name --}}
                        @if(!empty($print['business_name']))
                            <b style="display: block !important;font-weight: bold;margin-bottom :0px !important; font-size: {{9*$factor}}px">{{$business_name}}</b>
                        @endif

                        {{-- Product Name --}}
                        @if(!empty($print['name']))
                            <span style="display: block !important; margin-bottom :0px !important;font-size: {{8*$factor}}px; font-weight: {{$boldFont ? 'bold' : 'normal'}};">
                                {{$page_product->product_actual_name}}
                                <br/>
                                {{-- default font size = {{17*$factor}} --}}
                                @if(!empty($print['lot_number']) && !empty($page_product->lot_number))
                                    <span style="font-size: {{8*$factor}}px; font-weight: {{$boldFont ? 'bold' : 'normal'}};">
                                         ({{$page_product->lot_number}})
                                    </span>
                                @endif
                            </span>
                        @endif


                        {{-- Item Code--}}
                        @if(!empty($print['item_code']))
                            <span style="display: block !important; font-size: {{7*$factor}}px;margin-bottom :0px !important;margin-bottom :0px !important; font-weight: {{$boldFont ? 'bold' : 'normal'}};">
                                {{$page_product->icode}}
                            </span>
                        @endif

                        {{-- Product Barcode--}}
                        @if(!empty($print['sku']))
                            <span style="display: none !important; font-size: {{7*$factor}}px;margin-bottom :0px !important;margin-bottom :0px !important; font-weight: {{$boldFont ? 'bold' : 'normal'}};">
                                {{$page_product->b_code}}
                            </span>
                        @endif

                        {{-- Variation --}}
                        @if(!empty($print['variations']) && $page_product->is_dummy != 1)
                            <span style="display: block !important; font-size: {{7*$factor}}px;margin-bottom :0px !important; font-weight: {{$boldFont ? 'bold' : 'normal'}};">
                                <b>{{$page_product->product_variation_name}}</b>:{{$page_product->variation_name}}
                            </span>
                        @endif

                        {{-- Price --}}
                        @if(!empty($print['price']))
                        <span style="font-weight: {{$boldFont ? 'bold' : 'normal'}};margin-bottom :0px !important; font-size: {{8*$factor}}px">
                            <!--<b>@lang('lang_v1.price'):</b>-->



                            {{-- Reg and Sales Price --}}
                            @if(!empty($print['reg_and_sales']))
                                @if(!empty($page_product->web_sale_price) && !empty($page_product->srp))
                                    <span style="display: flex;flex-direction:row; justify-content:center;">
                                    <span style="font-weight: {{$boldFont ? 'bolder' : 'normal'}}; margin-bottom: 0px !important; font-size: {{8 * $factor}}px; padding-right:1.5px;">On Sale:</span>
                                    <span style="font-weight: {{$boldFont ? 'bolder' : 'normal'}}; margin-bottom: 0px !important; font-size: {{8 * $factor}}px; text-decoration: line-through;">
                                        {{ session('currency')['symbol'] ?? '' }}{{ @num_format($page_product->srp) }}
                                    </span>
                                    <span style="font-weight: {{$boldFont ? 'bolder' : 'normal'}}; margin-bottom: 0px !important; font-size: {{8 * $factor}}px; padding-left:2px;">
                                        {{ session('currency')['symbol'] ?? '' }}{{ @num_format($page_product->web_sale_price) }}
                                    </span>
                                    </span>
                                @else
                                    @lang('lang_v1.price'):
                                    {{session('currency')['symbol'] ?? ''}}
                                    @if($print['price_type'] == 'inclusive')
                                        {{@num_format($page_product->sell_price_inc_tax)}}
                                    @else
                                        {{@num_format($page_product->default_sell_price)}}
                                    @endif
                                @endif
                            @else
                                @lang('lang_v1.price'):
                                {{session('currency')['symbol'] ?? ''}}
                                @if($print['price_type'] == 'inclusive')
                                    {{@num_format($page_product->sell_price_inc_tax)}}
                                @else
                                    {{@num_format($page_product->default_sell_price)}}
                                @endif
                            @endif




                        </span>
                        @endif




                        @if(!empty($print['exp_date']) && !empty($page_product->exp_date))
                            <br>
                            <span style="font-size: {{11*$factor}}px; font-weight: {{$boldFont ? 'bold' : 'normal'}};">
                                <b>@lang('product.exp_date'):</b>
                                {{$page_product->exp_date}}
                            </span>
                            @if($barcode_details->is_continuous)
                            <br>
                            @endif
                        @endif

                        @if(!empty($print['packing_date']) && !empty($page_product->packing_date))
                            <span style="font-size: {{7*$factor}}px; font-weight: {{$boldFont ? 'bold' : 'normal'}};">
                                <b>@lang('lang_v1.packing_date'):</b>
                                {{$page_product->packing_date}}
                            </span>
                        @endif
                        <!--<br>-->
                        @if(!empty($print['sku']))
                        {{-- Barcode --}}
                             <span><img style="max-width:75% !important;margin-top:5px;height: {{$barcode_details->height*0.24}}in !important;" src="data:image/png;base64,{{DNS1D::getBarcodePNG($page_product->sub_sku, $page_product->barcode_type, 3,30,array(39, 48, 54), true)}}"></span><br>
                      @endif
                    </div>
                    <div style="width: {{ $print['qr_size'] * 2 }}% !important;">
                      @if(!empty($print['qrcode']))
                            <span style="display: flex;height:100%;align-items:center;"><img style="width: 100%;" src="data:image/png;base64,{{ DNS2D::getBarcodePNG($page_product->sub_sku,'QRCODE') }}" alt="barcode"></span>
                      @endif
                    </div>
                </div>

            </td>

        @if($loop->iteration % $barcode_details->stickers_in_one_row == 0)
            </tr>
        @endif
    @endforeach
    </table>

    <style type="text/css">

        @media print{

            table{
                page-break-after: always;
            }
            @page {
            size: {{$paper_width}}in {{$paper_height}}in;

            /*width: {{$barcode_details->paper_width}}in !important;*/
            /*height:@if($barcode_details->paper_height != 0){{$barcode_details->paper_height}}in !important @else auto @endif;*/
            /*margin-top: {{$margin_top}}in !important;*/
            /*margin-bottom: {{$margin_top}}in !important;*/
            /*margin-left: {{$margin_left}}in !important;*/
            /*margin-right: {{$margin_left}}in !important;*/
            margin-top:0px !important;
            margin-bottom: 0px !important;
            margin-left: 0px !important;
            margin-right: 0px !important;
        }
        }
    </style>