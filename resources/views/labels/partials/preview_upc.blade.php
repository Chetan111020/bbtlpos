<table align="center" style="border-spacing: {{$barcode_details->col_distance * 1}}in {{$barcode_details->row_distance * 1}}in; overflow: hidden !important;">
    @foreach($page_products as $page_product)

        @if($loop->index % $barcode_details->stickers_in_one_row == 0)
            <!-- create a new row -->
            <tr>
            <!-- <columns column-count="{{$barcode_details->stickers_in_one_row}}" column-gap="{{$barcode_details->col_distance*1}}"> -->
        @endif
            <td align="center" valign="center">
                <div style="overflow: hidden !important;display: flex; justify-content:center; flex-wrap: wr1ap;align-content: center;width: {{$barcode_details->width * 1}}in; height: {{$barcode_details->height * 1}}in;">


                    <div style="display: flex;flex-direction:column;">
                        {{-- Business Name --}}
                        @if(!empty($print['business_name']))
                            <b style="display: block !important; font-size: {{13*$factor}}px">{{$business_name}}</b>
                        @endif

                        {{-- Product Name --}}
                        @if(!empty($print['name']))
                            <span style="display: block !important; font-size: {{11*$factor}}px">
                                {{$page_product->product_actual_name}}

                                @if(!empty($print['lot_number']) && !empty($page_product->lot_number))
                                    <span style="font-size: {{10*$factor}}px">
                                         ({{$page_product->lot_number}})
                                    </span>
                                @endif
                            </span>
                        @endif

                        {{-- Variation --}}
                        @if(!empty($print['variations']) && $page_product->is_dummy != 1)
                            <span style="display: block !important; font-size: {{11*$factor}}px">
                                <b>{{$page_product->product_variation_name}}</b>:{{$page_product->variation_name}}
                            </span>
                        @endif
                        
                        {{-- Item Code--}}
                        @if(!empty($print['item_code']))
                            <span style="display: block !important; font-size: {{9*$factor}}px">
                                {{$page_product->icode}}
                            </span>
                        @endif
                        
                        {{-- Product Barcode--}}
                        @if(!empty($print['sku']))
                            <span style="display: block !important; font-size: {{9*$factor}}px">
                                {{$page_product->b_code}}
                            </span>
                        @endif


                        
                        {{-- Price --}}
                        @if(!empty($print['price']))
                        <span style="font-size: {{10*$factor}}px">
                            <b>@lang('lang_v1.price'):</b>
                            {{session('currency')['symbol'] ?? ''}}


                            @if($print['price_type'] == 'inclusive')
                                {{@num_format($page_product->sell_price_inc_tax)}}
                            @else
                                {{@num_format($page_product->default_sell_price)}}
                            @endif
                        </span>
                        @endif
                        
                        <!-- @if(auth()->user()->id == 6) -->
                            {{-- Reg and Sales Price --}}
                            @if(!empty($print['reg_and_sales']))
                                @if(!empty($page_product->web_sale_price) && !empty($page_product->srp))
                                <div style="display: flex;flex-direction:row; justify-content:center;">
                            <span style="font-weight: {{$boldFont ? 'bold' : 'normal'}}; margin-bottom: 0px !important; font-size: {{6 * $factor}}px; padding-right:1.5px;">On Sale:</span>
                            <span style="font-weight: {{$boldFont ? 'bold' : 'normal'}}; margin-bottom: 0px !important; font-size: {{6 * $factor}}px; text-decoration: line-through;">
                                        {{ session('currency')['symbol'] ?? '' }}{{ @num_format($page_product->srp) }}
                                    </span>
                                    <span style="font-weight: {{$boldFont ? 'bold' : 'normal'}}; margin-bottom: 0px !important; font-size: {{6 * $factor}}px; padding-left:2px;">
                                        {{ session('currency')['symbol'] ?? '' }}{{ @num_format($page_product->web_sale_price) }}
                                    </span><br></div>
                                @endif
                            @endif
                     <!-- @endif -->
                        
                        @if(!empty($print['exp_date']) && !empty($page_product->exp_date))
                            <br>
                            <span style="font-size: {{14*$factor}}px">
                                <b>@lang('product.exp_date'):</b>
                                {{$page_product->exp_date}}
                            </span>
                            @if($barcode_details->is_continuous)
                            <br>
                            @endif
                        @endif

                        @if(!empty($print['packing_date']) && !empty($page_product->packing_date))
                            <span style="font-size: {{14*$factor}}px">
                                <b>@lang('lang_v1.packing_date'):</b>
                                {{$page_product->packing_date}}
                            </span>
                        @endif
                        {{-- <br> --}}

                        {{-- Barcode --}}
                        {{-- <img style="max-width:90% !important;height: {{$barcode_details->height*0.9}}in !important;" src="data:image/png;base64,{{DNS1D::getBarcodePNG($page_product->sub_sku, $page_product->barcode_type, 4,30,array(39, 48, 54), true)}}"> --}}
                        <svg class="barcode" style="display:none1;"
                            jsbarcode-format="code128"
                            jsbarcode-value="{{$page_product->sub_sku}}"
                            jsbarcode-textmargin="0"
                            jsbarcode-fontoptions="bold">
                        </svg>
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
            margin-top: {{$margin_top}}in !important;
            margin-bottom: {{$margin_top}}in !important;
            margin-left: {{$margin_left}}in !important;
            margin-right: {{$margin_left}}in !important;
        }
        }
    </style>