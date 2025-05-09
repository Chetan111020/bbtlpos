@extends('layouts.app')
@section('title', 'Product Analytics')
@section('css')

    <style>
        .search_result {
            display: none;
        }

        .dash_info_ele {
            width: 33%;
            padding: 1rem;
            margin: 1rem;
            background: white;
            display: flex;
            flex-direction: column;
            box-shadow: rgba(0, 0, 0, 0.1) 0px 1px 3px 0px, rgba(0, 0, 0, 0.06) 0px 1px 2px 0px;
        }

        .dash_icon {
            margin: auto 5px auto auto;
            border-radius: 50px;
            display: flex;
        }

        .dash_svg_icon {
            height: 28px;
            margin: 15px;
        }

        .dash_ele_color1 {
            color: #00bcd4 !important;
            background: rgba(0, 188, 212, .1) !important;
        }

        .dash_ele_color2 {
            color: #2196f3 !important;
            background: rgba(33, 150, 243, .1) !important;
        }

        .dash_ele_color3 {
            color: #4caf50 !important;
            background: rgba(76, 175, 80, .1) !important;
        }

        .dash_ele_color4 {
            color: #f44336 !important;
            background: rgba(244, 67, 54, .1) !important;
        }

        .dash_ele_color5 {
            color: #1D976C;
            background: #1D976C;
            /* fallback for old browsers */
            background: -webkit-linear-gradient(to right, #93F9B9, #1D976C);
            /* Chrome 10-25, Safari 5.1-6 */
            background: linear-gradient(to right, #93f9b98c, #5bffc552);
        }

        .dash_ele_color6 {
            color: #cdb90f !important;
            background: rgb(240 215 0 / 10%) !important;
        }

        .myshadow {
            box-shadow: rgba(0, 0, 0, 0.1) 0px 1px 3px 0px, rgba(0, 0, 0, 0.06) 0px 1px 2px 0px;
        }

        .newtons-cradle {
            --uib-size: 40px;
            --uib-speed: 1.4s;
            --uib-color: black;

            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: var(--uib-size);
            height: var(--uib-size);
        }

        .newtons-cradle__dot {
            position: relative;
            display: flex;
            align-items: center;
            height: 100%;
            width: 25%;
            transform-origin: center top;
        }

        .newtons-cradle__dot::after {
            content: '';
            display: block;
            width: 100%;
            height: 25%;
            border-radius: 50%;
            background-color: var(--uib-color);
        }

        .newtons-cradle__dot:first-child {
            animation: swing var(--uib-speed) linear infinite;
        }

        .newtons-cradle__dot:last-child {
            animation: swing2 var(--uib-speed) linear infinite;
        }

        @keyframes swing {
            0% {
                transform: rotate(0deg);
                animation-timing-function: ease-out;
            }

            25% {
                transform: rotate(70deg);
                animation-timing-function: ease-in;
            }

            50% {
                transform: rotate(0deg);
                animation-timing-function: linear;
            }
        }

        @keyframes swing2 {
            0% {
                transform: rotate(0deg);
                animation-timing-function: linear;
            }

            50% {
                transform: rotate(0deg);
                animation-timing-function: ease-out;
            }

            75% {
                transform: rotate(-70deg);
                animation-timing-function: ease-in;
            }
        }
    </style>

@endsection
@section('content')

    <section class="content">
        <div style="display:flex;background:white;padding:15px;">
            <input type="hidden" id="product_id" value="{{ $product_id }}" />
            <div style="width: 40%">
                <h2>Product Analytics</h2>
                <br />
                <div style="width: 100%">
                    <div class="form-group">
                        <label>Search Product</label>
                        {!! Form::text('search_product', null, [
                            'class' => 'form-control mousetrap',
                            'id' => 'search_product',
                            'placeholder' => __('Enter Product Name'),
                        ]) !!}

                    </div>
                </div>
            </div>
            <div style="width: 60%;padding:1rem 3rem;">
                <div class="panel panel-default" style="border: none; margin-bottom: 0px;">
                    <div class="panel-heading dash_ele_color5" style="display: flex; justify-content: center;">
                        <label style="margin: 0 !important; font-size: 1.5rem; font-weight: bold;">Product Details</label>
                    </div>
                    <div class="panel-body" style="background-color: white;">
                        <div class="col-md-12" id="no_product_selected" style="display: flex; justify-content: center;">
                            <h4>No Product Selected.</h4>
                        </div>
                        <div class="row" style="display: none; margin-top: 0px;" id="product_details_container">
                            <div class="col-md-8 col-xs-8">
                                <div class="form-group">
                                    <h3><span style="font-weight: bold;" class="product_name"></span></h3>
                                </div>
                                <table class="table" style="width: 100%;">
                                    <tr>
                                        <td style="width: 40%;">Category</td>
                                        <td>Available Qty.</td>
                                        <td style="">Total Inventory</td>
                                    </tr>
                                    <tr>
                                        <th class="category_name"></th>
                                        <th class="qty_available"></th>
                                        <th class="inventory_value"></th>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-4 col-xs-4">
                                <div class="product_image_container thumbnail" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: none;" id="product-content">
            <div style="display: flex; margin: 1rem; margin-bottom: 0;" id="">
                <div class="dash_info_ele" style="width: 100% !important">
                    <div class="row">
                        <div class="" style="display: flex; justify-content: center;">
                            <div class="form-group col-md-4">

                                <input type="hidden" id="date" name="date" value="">
                                <div class="input-group">
                                    <span class="input-group-addon  text-light">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    {!! Form::text(
                                        'all_date_filter',
                                        @format_date('first day of this week') . ' ~ ' . @format_date('last day of this week'),
                                        [
                                            'placeholder' => __('lang_v1.select_a_date_range'),
                                            'class' => 'form-control',
                                            'id' => 'all_date_filter',
                                            'readonly',
                                        ],
                                    ) !!}
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-6" style="">
                            <div class="dash_info_ele " style="width:100%!important;">
                                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color5">
                                    <label style="margin:0!important">Total Purchase</label>
                                </div>
                                <div style="display:flex">
                                    <div style="display: flex;width:100%;flex-direction:column;margin:auto;">
                                        <div style="display:flex;width:100%;">
                                            <h3 style="width: 50%;" class="purchase_amt"></h3>
                                            <h3 style="width: 50%;" class=""><small>Q.</small><span
                                                    class="purchase_qty"></span></h3>
                                        </div>
                                    </div>
                                    <div class="dash_icon dash_ele_color5" style="margin-top:1rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-6">
                            <div class="dash_info_ele " style="width:100%!important">
                                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color5">
                                    <label style="margin:0!important">Total Sale</label>
                                </div>
                                <div style="display:flex">
                                    <div style="display: flex;width:100%;flex-direction:column;margin:auto;">
                                        <div style="display:flex;width:100%;">
                                            <h3 style="width: 50%;" class="sales_amt"></h3>
                                            <h3 style="width: 50%;"><small>Q.</small><span class="sales_qty"></span></h3>
                                        </div>
                                    </div>
                                    <div class="dash_icon dash_ele_color5" style="margin-top:1rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="height:400px;width:100%;margin:1em;">
                        <h2 style="margin: 1rem;">Purchase & Sales</h2>
                        <div id="chart3"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-4 col-xs-4" style="">
                            <div class="dash_info_ele " style="width:100%!important;">
                                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color2">
                                    <label style="margin:0!important">Avg Selling Price</label>
                                </div>
                                <div style="display:flex">
                                    <div style="display: flex;width:100%;flex-direction:column; align-items: center;">
                                        <div style="display:flex;width:100%;">
                                            <h3 class="avg_price"
                                                style="margin: 0 auto; margin-top: 5%; margin-left: 50%;">
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="dash_icon dash_ele_color2" style="margin-top:1rem;">
                                        <svg role="img" viewBox="0 0 24 24" class="dash_svg_icon"
                                            stroke="currentColor" stroke-width="2" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4.5 7.5C5.328 7.5 6 8.172 6 9v10.5c0 .828-.672 1.5-1.5 1.5h-3C.673 21 0 
                                            20.328 0 19.5V9c0-.828.673-1.5 1.5-1.5h3zm9-4.5c.828 0 1.5.672 1.5 1.5v15c0 
                                            .828-.672 1.5-1.5 1.5h-3c-.827 0-1.5-.672-1.5-1.5v-15c0-.828.673-1.5 1.5-1.5h3zm9
                                            7.5c.828 0 1.5.672 1.5 1.5v7.5c0 .828-.672 1.5-1.5 1.5h-3c-.828 0-1.5-.672-1.5-1.5V12c0-.828.672-1.5 1.5-1.5h3z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-4" style="">
                            <div class="dash_info_ele " style="width:100%!important;">
                                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color2">
                                    <label style="margin:0!important">Purchase Price</label>
                                </div>
                                <div style="display:flex">
                                    <div style="display: flex;width:100%;flex-direction:column; align-items: center;">
                                        <div style="display:flex;width:100%;">
                                            <h3 class="purchase_price"
                                                style="margin: 0 auto; margin-top: 5%; margin-left: 50%;"></h3>
                                        </div>
                                    </div>
                                    <div class="dash_icon dash_ele_color2" style="margin-top:1rem;">
                                        <svg role="img" viewBox="0 0 512 512" class="dash_svg_icon"
                                            stroke="currentColor" stroke-width="2" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <polyline points="160 368 32 256 160 144"
                                                style="fill:none;stroke:#2196f3;stroke-linecap:square;stroke-miterlimit:10;stroke-width:42px" />
                                            <polyline points="352 368 480 256 352 144"
                                                style="fill:none;stroke:#2196f3;stroke-linecap:square;stroke-miterlimit:10;stroke-width:42px" />
                                            <polyline points="192 288.1 256 352 320 288.1"
                                                style="fill:none;stroke:#2196f3;stroke-linecap:square;stroke-miterlimit:10;stroke-width:42px" />
                                            <line x1="256" y1="160" x2="256" y2="336.03"
                                                style="fill:none;stroke:#2196f3;stroke-linecap:square;stroke-miterlimit:10;stroke-width:42px" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-4" style="">
                            <div class="dash_info_ele " style="width:100%!important;">
                                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color2">
                                    <label style="margin:0!important">Avg Margin Percentage</label>
                                </div>
                                <div style="display:flex">
                                    <div style="display: flex;width:100%;flex-direction:column; align-items: center;">
                                        <div style="display:flex;width:100%;">
                                            <h3 class="avg_margin_per"
                                                style="margin: 0 auto; margin-top: 5%; margin-left: 50%;">
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="dash_icon dash_ele_color2" style="margin-top:1rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" fill="none"
                                            stroke="currentColor" class="bi bi-bar-chart-fill" viewBox="0 0 20 20">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M1 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3zm5-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V2z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="height:400px;width:100%;margin:1em;">
                        <h2 style="margin: 1rem;">Margin Percentage</h2>
                        <div id="chart2"></div>
                    </div>
                </div>
            </div>

            <div style="display: flex; margin: 1rem; margin-bottom: 0;">
                <div class="dash_info_ele" style="width: 100% !important">
                    <div class="row">
                        <div class="col-md-6 col-xs-6">
                            <div style="height:300px;width:100%;margin:1em;">
                                <h2 style="margin: 1rem;">State</h2>
                                <div id="chart4" tyle="height:300px;width:100%;"></div>
                                <div id="noStateDataMessage"
                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: none;">
                                    Data not available
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xs-6">
                            <div style="height:300px;width:100%;margin:1em;">
                                <h2 style="margin: 1rem;">Tier</h2>
                                <div id="chart5" tyle="height:300px;width:100%;"></div>
                                <div id="noDataMessage"
                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: none;">
                                    Data not available
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="display: flex; margin: 1rem; margin-bottom: 0;background-color: white" id="">
                <div style="width:40%;margin:1em;" class="dash_ele_color2">
                    <h2 style="margin: 1rem;">Top Customers</h2>
                    <div style="margin:35px 0;">
                        <div style="display:flex;margin:15px 1rem;">
                            <div style="display:flex;align-items:center;margin: 0 10px;">
                                <div class="dash_ele_color1" style="width:30px;height:30px;display:flex;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class=""
                                        style="margin:auto;height:18px;" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span><b class="customer-name-1"></b></span><br />
                                <span class="high-purchase-1" data-currency_symbol=true></span>
                            </div>
                        </div>

                        <div style="display:flex;margin:15px 1rem;">
                            <div style="display:flex;align-items:center;margin: 0 10px;">
                                <div class="dash_ele_color1" style="width:30px;height:30px;display:flex;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class=""
                                        style="margin:auto;height:18px;" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span><b class="customer-name-2"></b></span><br />
                                <span class="high-purchase-2" data-currency_symbol=true></span>
                            </div>
                        </div>

                        <div style="display:flex;margin:15px 1rem;">
                            <div style="display:flex;align-items:center;margin: 0 10px;">
                                <div class="dash_ele_color1" style="width:30px;height:30px;display:flex;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class=""
                                        style="margin:auto;height:18px;" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span><b class="customer-name-3"></b></span><br />
                                <span class="high-purchase-3" data-currency_symbol=true></span>
                            </div>
                        </div>

                        <div style="display:flex;margin:15px 1rem;">
                            <div style="display:flex;align-items:center;margin: 0 10px;">
                                <div class="dash_ele_color1" style="width:30px;height:30px;display:flex;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class=""
                                        style="margin:auto;height:18px;" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span><b class="customer-name-4"></b></span><br />
                                <span class="high-purchase-4" data-currency_symbol=true></span>
                            </div>
                        </div>

                        <div style="display:flex;margin:15px 1rem;">
                            <div style="display:flex;align-items:center;margin: 0 10px;">
                                <div class="dash_ele_color1" style="width:30px;height:30px;display:flex;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class=""
                                        style="margin:auto;height:18px;" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span><b class="customer-name-5"></b></span><br />
                                <span class="high-purchase-5" data-currency_symbol=true></span>
                            </div>
                        </div>

                        <div style="display:flex;margin:15px 1rem;">
                            <div style="display:flex;align-items:center;margin: 0 10px;">
                                <div class="dash_ele_color1" style="width:30px;height:30px;display:flex;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class=""
                                        style="margin:auto;height:18px;" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span><b class="customer-name-6"></b></span><br />
                                <span class="high-purchase-6" data-currency_symbol=true></span>
                            </div>
                        </div>
                        <div style="display:flex;margin:15px 1rem;">
                            <div style="display:flex;align-items:center;margin: 0 10px;">
                                <div class="dash_ele_color1" style="width:30px;height:30px;display:flex;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class=""
                                        style="margin:auto;height:18px;" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span><b class="customer-name-7"></b></span><br />
                                <span class="high-purchase-7" data-currency_symbol=true></span>
                            </div>
                        </div>
                        <div style="display:flex;margin:15px 1rem;">
                            <div style="display:flex;align-items:center;margin: 0 10px;">
                                <div class="dash_ele_color1" style="width:30px;height:30px;display:flex;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class=""
                                        style="margin:auto;height:18px;" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span><b class="customer-name-8"></b></span><br />
                                <span class="high-purchase-8" data-currency_symbol=true></span>
                            </div>
                        </div>
                        <div style="display:flex;margin:15px 1rem;">
                            <div style="display:flex;align-items:center;margin: 0 10px;">
                                <div class="dash_ele_color1" style="width:30px;height:30px;display:flex;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class=""
                                        style="margin:auto;height:18px;" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span><b class="customer-name-9"></b></span><br />
                                <span class="high-purchase-9" data-currency_symbol=true></span>
                            </div>
                        </div>
                        <div style="display:flex;margin:15px 1rem;">
                            <div style="display:flex;align-items:center;margin: 0 10px;">
                                <div class="dash_ele_color1" style="width:30px;height:30px;display:flex;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class=""
                                        style="margin:auto;height:18px;" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <span><b class="customer-name-10"></b></span><br />
                                <span class="high-purchase-10" data-currency_symbol=true></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="width:60%;margin:1em;max-height: 500px;" class="dash_ele_color3">
                    <h2 style="margin: 1rem;">Top 10 Products</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <th>Rank</th>
                                <th>Name</th>
                                <th>Total Sale</th>
                            </thead>
                            <tbody id="productTableBody">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        $(document).ready(function() {
            var product_id;


            if ($('#product_id').val() != 0) {
                product_id = $('#product_id').val();
            } else {
                product_id = $('#search_product').data('selected-product-id');
            }
            $('#all_date_filter').daterangepicker({
                ranges: ranges,
                autoUpdateInput: true,
                startDate: moment().startOf('week'),
                endDate: moment().endOf('week'),
                locale: {
                    format: moment_date_format
                }
            });
            $('#all_date_filter').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format(moment_date_format) + ' ~ ' + picker.endDate.format(
                    moment_date_format));
                $("#date").val($(this).val());

                // Get the selected product ID
                var selectedProductId = $('#search_product').data('selected-product-id') || product_id;
                // Check if a product is selected
                if (selectedProductId) {
                    var start = picker.startDate.format('YYYY-MM-DD');
                    var end = picker.endDate.format('YYYY-MM-DD');

                    // Call the getdata function with selectedProductId, start, and end dates
                    getdata(selectedProductId, start, end);
                }
            });

            $('#all_date_filter').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        });

        if ($('#product_id').val() != 0) {
            $('#product-content').show();
            $('#product_details_container').css('display', 'flex');
            $('#no_product_selected').hide();
            $('#product_details_container').show();
            $('#product_mg_container').show();
            // Initialize the date range picker if not already initialized
            if (!$('input#all_date_filter').data('daterangepicker')) {
                $('input#all_date_filter').daterangepicker({
                    ranges: ranges,
                    autoUpdateInput: true,
                    startDate: moment().startOf('week'),
                    endDate: moment().endOf('week'),
                    locale: {
                        format: moment_date_format
                    }
                });
            }

            // Now that the date range picker is initialized, get the selected date range
            start = $('input#all_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
            end = $('input#all_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');

            // Call the getdata function with the selected product_id and date range
            getdata($('#product_id').val());

            $.ajax({
                // url: '/reports/product-analytics/' + selectedProductId,
                url: '/reports/product-analytics/' + $('#product_id').val(),
                method: 'GET',
                data: {
                    start_date: start,
                    end_date: end,
                },
                dataType: 'json',
                success: function(response) {
                    const {
                        category_name,
                        image_url,
                        name,
                        product_image,
                        item_code,
                        qty_available,
                        sell_price_inc_tax
                    } = response[3];

                    if (item_code) {
                        $('.product_name').html(name + ' ' + '(' + item_code + ')');
                    } else {
                        $('.product_name').html(name);
                    }
                    $('.category_name').html(category_name);
                    var qtyAvailable = qty_available || 0;
                    $('.qty_available').html(Math.round(qtyAvailable));
                    var productImageURL = '/uploads' + product_image;
                    if (product_image) {
                        var imgElement = $('<img>').attr('src', productImageURL).attr('alt', 'Product Image')
                            .css(
                                'max-width', '100%').css('max-height', '150px');
                    } else {
                        var imgElement = $('<img>').attr('src', image_url).attr('alt', 'Product Image')
                            .css(
                                'max-width', '100%').css('max-height', '150px');
                    }
                    $('.product_image_container').html(imgElement);
                    var inventory_value = qty_available * sell_price_inc_tax;
                    $('.inventory_value').html(__currency_trans_from_en(inventory_value));
                },
            });

        }

        function getdata(id, item) {
            selectedProductId = id;
            if ($('input#all_date_filter').val()) {
                start = $('input#all_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                end = $('input#all_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
            }
            $.ajax({
                url: '/reports/product-analytics/' + selectedProductId,
                method: 'GET',
                data: {
                    start_date: start,
                    end_date: end,
                },
                dataType: 'json',
                success: function(response) {
                    setMainChart(response[0]);
                    const {
                        purchase_amt,
                        purchase_qty,
                        sales_amt,
                        sales_qty,
                        purchase_price,
                        avg_price,
                        avg_margin_per,
                    } = response[1];
                    const topCustomers = response[2];
                    for (let i = 0; i < 10; i++) {
                        const customerNameElement = $('.customer-name-' + (i + 1));
                        const highPurchaseElement = $('.high-purchase-' + (i + 1));

                        if (i < topCustomers.length) {
                            const customerLink = $('<a>')
                                .text(topCustomers[i].name)
                                .attr('href', '/reports/customer-analytics/' + topCustomers[i].customer_id)
                                .attr('target', '_blank'); // Open link in a new tab
                            customerNameElement.empty().append(customerLink);

                            highPurchaseElement.text(__currency_trans_from_en(topCustomers[i].high_purchase));
                        } else {
                            customerNameElement.text('-');
                            highPurchaseElement.text('-');
                        }
                    }
                    $('.purchase_amt').html(purchase_amt);
                    $('.purchase_qty').html(purchase_qty);
                    $('.sales_amt').html(sales_amt);
                    $('.sales_qty').html(sales_qty);
                    $('.purchase_price').html(purchase_price);
                    $('.avg_price').html(avg_price);

                    const avg_margin_per_value = parseFloat(avg_margin_per);
                    if (!isNaN(avg_margin_per_value)) {
                        $('.avg_margin_per').html(avg_margin_per_value.toFixed(2) + ' ' + '%');
                    } else {
                        $('.avg_margin_per').html(
                            '-'); // Display a placeholder if avg_margin_per is not a valid number
                    }
                },
            });

            $.ajax({
                url: '/reports/ranktable', // Update the URL to match your route
                type: 'GET',
                data: {
                    start_date: start,
                    end_date: end,
                },
                dataType: 'json',
                success: function(response) {
                    // hideLoader();
                    var tableBody = $('#productTableBody');
                    tableBody.empty();

                    // If there are no categories available in the response, display "Data not available" message
                    if (response.products.length === 0) {
                        tableBody.append(
                            '<tr><td colspan="3" class="text-center">Data not available</td></tr>'
                        );
                    } else {
                        var selectedCategoryRow = null;
                        var remainingRows = [];
                        var counter = 0;

                        // Loop through the categories and find the selected category row
                        $.each(response.products, function(index, product) {
                            if (product.product_id == selectedProductId) {
                                selectedCategoryRow =
                                    '<tr style="background-color: yellow;">' +
                                    '<td>' + product.rank + '</td>' +
                                    '<td>' + product.name + '</td>' +
                                    '<td>' + __currency_trans_from_en(product.high_purchase) +
                                    '</td>' +
                                    '</tr>';
                                counter++; // Increment counter for selected product
                            } else {
                                remainingRows.push(
                                    '<tr>' +
                                    '<td>' + product.rank + '</td>' +
                                    '<td>' + product.name + '</td>' +
                                    '<td>' + __currency_trans_from_en(product.high_purchase) +
                                    '</td>' +
                                    '</tr>'
                                );
                            }
                        });

                        // Append the selected category row if it exists
                        if (selectedCategoryRow !== null) {
                            tableBody.append(selectedCategoryRow);
                        }

                        // Append the remaining rows, up to a maximum of 10 or 11 (if selected category is found)
                        for (var i = 0; i < remainingRows.length && counter < 11; i++) {
                            tableBody.append(remainingRows[i]);
                            counter++;
                        }
                    }
                },
            });

            $.ajax({
                url: '/reports/margin-chart/' + selectedProductId,
                method: 'GET',
                data: {
                    start_date: start,
                    end_date: end,
                },
                dataType: 'json',
                success: function(response) {
                    marginchart(response);
                },
            });

            $.ajax({
                url: '/reports/statechart/' + selectedProductId,
                method: 'GET',
                data: {
                    start_date: start,
                    end_date: end,
                },
                dataType: 'json',
                success: function(response) {
                    statechart(response[0]);
                    tierchart(response[1]);
                },
            });
        }

        function tierchart(response) {
            var chartele5 = document.querySelector("#chart5");
            var noDataMessage = document.querySelector("#noDataMessage");
            if (!response || response.length === 0) {
                chartele5.style.display = "none";
                noDataMessage.style.display = "block";
            } else {
                chartele5.style.display = "block";
                noDataMessage.style.display = "none";

                var options5 = {
                    chart: {
                        type: 'pie',
                        height: 250,
                    },
                    labels: response.map(item => item.a),
                    series: response.map(item => parseInt(item.b)),
                };

                var chartele5 = document.querySelector("#chart5");
                chartele5.innerHTML = "";
                var chart5 = new ApexCharts(chartele5, options5);
                chart5.render();
            }
        }

    
        // function statechart(response) {
        //     var chartele4 = document.querySelector("#chart4");
        //     var noDataMessage = document.querySelector("#noStateDataMessage");

        //     var options4 = {
        //         chart: {
        //             type: 'pie',
        //             height: 250,
        //         },
        //         labels: [],
        //         series: [],
        //     };

        //     if (!response || response.length === 0) {
        //         chartele4.style.display = "none";
        //         noDataMessage.style.display = "block";
        //     } else {
        //         chartele4.style.display = "block";
        //         noDataMessage.style.display = "none";

        //         options4.labels = response.map(item => item.x);
        //         options4.series = response.map(item => parseInt(item.y));
        //     }

        //     chartele4.innerHTML = "";
        //     var chart4 = new ApexCharts(chartele4, options4);
        //     chart4.render();
        // }
function statechart(response) {
    var chartele4 = document.querySelector("#chart4");
    var noDataMessage = document.querySelector("#noStateDataMessage");

    var options4 = {
        chart: {
            type: 'pie',
            height: 250,
        },
        labels: [],
        series: [],
    };

    try {
        if (!response || response.length === 0) {
            chartele4.style.display = "none";
            noDataMessage.style.display = "block";
        } else {
            chartele4.style.display = "block";
            noDataMessage.style.display = "none";

             options4.labels = response
                .filter(item => item.x !== null)
                .map(item => item.x);
            
            options4.series = response
                .filter(item => item.x !== null)
                .map(item => parseInt(item.y));
        }

        chartele4.innerHTML = "";
        var chart4 = new ApexCharts(chartele4, options4);
        chart4.render();
    } catch (error) {
        console.error("Error in statechart function:", error);
    }
}

        function marginchart(data) {
            var options1 = {
                chart: {
                    type: 'bar',
                    height: 350,
                },
                series: [{
                    name: 'Margin',
                    data: data[0]
                }],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: false,
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                xaxis: {
                    type: 'datetime',
                    categories: data[1],

                },
                yaxis: {
                    title: {
                        text: 'Margin Percentage',
                    },
                    labels: {
                        formatter: function(val) {
                            return val + '%';
                        },
                    },
                },
                tooltip: {
                    x: {
                        format: 'dd/MM/yyyy'
                    },
                    y: {
                        formatter: function(value) {
                            return value.toFixed(2) + '%';
                        }
                    }
                },
            };

            var chartele1 = document.querySelector("#chart2");
            chartele1.innerHTML = "";
            var chart2 = new ApexCharts(chartele1, options1);
            chart2.render();
        }

        function setMainChart(data) {
            var options = {
                grid: {
                    show: false
                },
                series: [{
                        name: 'Purchases',
                        data: data[1]
                    },
                    {
                        name: 'Sales',
                        data: data[0]
                    }
                ],
                chart: {
                    height: 350,
                    type: 'area'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    type: 'datetime',
                    categories: data[2]
                },
                tooltip: {
                    x: {
                        format: 'dd/MM/yyyy'
                    },
                    y: {
                        formatter: function(value) {
                            return "$ " + value;
                        }
                    }
                },
            };
            var chartele = document.querySelector("#chart3");
            chartele.innerHTML = "";
            var chart3 = new ApexCharts(chartele, options);
            chart3.render();
        }
        //product search
        if ($('#search_product').length) {
            $('#search_product').autocomplete({
                    source: function(request, response) {
                        $.getJSON(
                            '/products/list', {
                                term: request.term
                            },
                            response
                        );
                    },
                    minLength: 2,
                    response: function(event, ui) {
                        if (ui.content.length == 0) {
                            toastr.error(LANG.no_products_found);
                            $('input#search_product').select();
                        }
                    },
                    select: function(event, ui) {
                        // $('#product-content').css('display', 'flex');
                        $('#product-content').show();
                        $('#product_details_container').css('display', 'flex');
                        $('#no_product_selected').hide();
                        $('#product_details_container').show();
                        $('#product_mg_container').show();
                        $('#search_product').data('selected-product-id', ui.item.product_id);
                        // var selectedId = $('#product_id').val();
                        selectedProductId = ui.item.product_id;
                        getdata(selectedProductId, ui.item);
                        if (ui.item.item_code) {
                            $('.product_name').html(ui.item.name + ' ' + '(' + ui.item.item_code + ')');
                        } else {
                            $('.product_name').html(ui.item.name);
                        }
                        $('.category_name').html(ui.item.category_name);
                        $('.selling_price').html('$' + ui.item.selling_price);

                        var inventory_value = ui.item.qty_available * ui.item.sell_price_inc_tax;
                        $('.inventory_value').html(__currency_trans_from_en(inventory_value));

                        var qtyAvailable = ui.item.qty_available || 0;
                        $('.qty_available').html(Math.round(qtyAvailable));
                        var productImageURL = '/uploads' + ui.item.product_image;
                        if (ui.item.product_image) {
                            var imgElement = $('<img>').attr('src', productImageURL).attr('alt', 'Product Image')
                                .css(
                                    'max-width', '100%').css('max-height', '150px');
                        } else {
                            var imgElement = $('<img>').attr('src', ui.item.image_url).attr('alt', 'Product Image')
                                .css(
                                    'max-width', '100%').css('max-height', '150px');
                        }
                        $('.product_image_container').html(imgElement);
                    },
                })
                .autocomplete('instance')._renderItem = function(ul, item) {
                    var is_overselling_allowed = false;
                    if ($('input#is_overselling_allowed').length) {
                        is_overselling_allowed = true;
                    }
                    if (item.enable_stock == 1 && item.qty_available <= 0 && !is_overselling_allowed) {
                        var string = '<li class=""><b>' + item.name;
                        if (item.type == 'variable') {
                            string += '-' + item.variation;
                        }
                        var selling_price = item.selling_price;
                        if (item.variation_group_price) {
                            selling_price = item.variation_group_price;
                        }
                        string +=
                            ' </b> (' +
                            item.sub_sku +
                            ')' +
                            '<br> Price: ' +
                            selling_price +
                            ' (Out of stock) </li>';
                        return $(string).appendTo(ul);
                    } else {
                        var string = '<div><b>' + item.name;
                        if (item.type == 'variable') {
                            string += '-' + item.variation;
                        }

                        var selling_price = item.selling_price;
                        if (item.variation_group_price) {
                            selling_price = item.variation_group_price;
                        }

                        string += ' </b> (' + item.sub_sku + ')' + '<br> Price: ' + selling_price;
                        if (item.enable_stock == 1) {
                            var qty_available = __currency_trans_from_en(item.qty_available, false, false,
                                __currency_precision, true);

                            if (qty_available >= 1) {
                                string += ' - <span style="color:#1abb1a;">' + qty_available + item.unit;
                            } else if (qty_available < 1) {
                                string += ' - <span style="color:red;"> ' + qty_available + item.unit;
                            }

                        }
                        string += '</span></div>';

                        return $('<li>')
                            .append(string)
                            .appendTo(ul);
                    }
                };

        }
    </script>
@endsection
