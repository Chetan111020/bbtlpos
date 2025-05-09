@extends('layouts.app')
@section('title', 'Category Analytics')
 
@section('content')
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

        #product-content {
            position: relative;
            /* Set position for absolute positioning of loader */
        }

        #product-loader {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            /* Semi-transparent white background */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            /* Ensure it's above other content */
        }

        /* Customize loader animation here */
        #product-loader .loader {
            border: 4px solid #f3f3f3;
            /* Light grey border */
            border-top: 4px solid #3498db;
            /* Blue top border */
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 2s linear infinite;
            /* Rotate animation */
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <section class="content">
        <div style="display:flex;background:white;padding:15px;">
            <input type="hidden" id="category_id" value="{{ $category_id }}" />
            <div style="width: 60%">
                <h2>Category</h2>
                <br />
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('category_id', __('product.category') . ':') !!}
                            {!! Form::select('category_id', $categoriesdata, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'product_list_filter_category_id',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                        @php
                            $selectedCategoryId = !empty($category_id) ? $category_id : 0;
                        @endphp
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="">Select Product</label>
                            <select class="form-control" name="" id="productDropdown">
                                <option disabled selected>Select Product</option>
                            </select>
                        </div>
                    </div>
                </div>
                {{-- <div style="width: 100%">
                    <div class="form-group">
                        {!! Form::label('category_id', __('product.category') . ':') !!}
                        {!! Form::select('category_id', $categoriesdata, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'id' => 'product_list_filter_category_id',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>
                <div style="width: 100%; margin-top: 3rem;">
                    <div class="form-group">
                        <label for="">Select Product</label>
                        <select class="form-control" name="" id="productDropdown">
                            <option disabled selected>Select Product</option>
                        </select>
                    </div>
                </div> --}}
            </div>
            <div style="width: 40%;padding:1rem 3rem;">
                <div class="panel panel-default" style="border: none; margin-bottom: 0px;">
                    <div class="panel-heading dash_ele_color5" style="display: flex; justify-content: center;">
                        <label style="margin: 0 !important; font-size: 1.5rem; font-weight: bold;">Category Details</label>
                    </div>
                    <div class="panel-body" style="background-color: white;">
                        <div class="col-md-12" id="no_category_selected" style="display: flex; justify-content: center;">
                            <h4>No Category Selected.</h4>
                        </div>
                        <div class="row" style="display: none; margin-top: 0px;" id="category_container">
                            <div class="col-md-12 col-xs-12">
                                <div class="form-group">
                                    <h3><span style="font-weight: bold;" class="category_name"></span></h3>
                                </div>
                                <table class="table" style="width: 100%;">
                                    <tr>
                                        <td style="width: 70%;">Product Qty.</td>
                                        {{-- <td>Available Qty.</td> --}}
                                    </tr>
                                    <tr>
                                        <th class="product_qty"></th>
                                        {{-- <th class="qty_available"></th> --}}
                                    </tr>
                                </table>
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

                                {{-- {!! Form::label('all_date_filter', __('report.date_range') . ':') !!} --}}
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
                                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color1">
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
                                    <div class="dash_icon dash_ele_color1" style="margin-top:1rem;">
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
                        <div class="col-md-6 col-md-offset-3 col-sm-6 col-sm-offset-3 col-xs-12">
                            <div class="dash_info_ele text-center" style="width: 100% !important;">
                                <div class="dash_ele_color2"
                                    style="display: flex; justify-content: center; padding: 1rem;">
                                    <label style="margin: 0 !important;">Avg Margin Percentage</label>
                                </div>
                                <div style="display: flex;">
                                    <div style="display: flex; width: 100%; flex-direction: column; align-items: center;">
                                        <div style="display: flex; width: 100%;">
                                            <h3 class="avg_margin_per"
                                                style="margin: 0 auto; margin-top: 5%; margin-left: 50%;"></h3>
                                        </div>
                                    </div>
                                    <div class="dash_icon dash_ele_color2" style="margin-top: 1rem;">
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

                <div class="dash_ele_color1" style="width: 50%; margin: 1em; max-height: 500px;">
                    <h2 style="margin: 1rem;">Top Category</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Name</th>
                                    <th>Total Sale</th>
                                </tr>
                            </thead>
                            <tbody id="categoryTableBody">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div style="display: flex; margin: 1rem; margin-bottom: 0;background-color: white" id="">
                <div class="dash_ele_color3" style="width: 50%; margin: 1em;">
                    <h2 style="margin: 1rem;">Top 10 Products</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Name</th>
                                    <th>Total Sale</th>
                                </tr>
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
            var category_id;


            if ($('#category_id').val() != 0) {
                category_id = $('#category_id').val();
            } else {
                category_id = $('#product_list_filter_category_id').data(
                    'selected-category-id');
            }
            var selectedCategoryId = {!! json_encode($selectedCategoryId) !!};

            $('#product_list_filter_category_id').on('change', function() {
                var cat_id = $(this).val();
                $('#product-content').show();

                $(this).data('selected-category-id', cat_id);
                // alert(cat_id);
                $('#no_category_selected').hide();
                $('#category_container').show();
                getdata(cat_id);
            });
            if (selectedCategoryId != 0) {
                $('#product_list_filter_category_id').val(selectedCategoryId).trigger('change');
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

                var selectedId = $('#product_list_filter_category_id').data(
                    'selected-category-id'); // Use the same attribute name

                if (selectedId) {
                    var start = picker.startDate.format('YYYY-MM-DD');
                    var end = picker.endDate.format('YYYY-MM-DD');

                    // Call the getdata function with selectedId, start, and end dates
                    getdata(selectedId, start, end);
                }

            });

            $('#all_date_filter').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        });
        if ($('#category_id').val() != 0) {
            $('#product-content').show();

            $('#no_category_selected').hide();
            $('#category_container').show();
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
            getdata($('#category_id').val());
        }


        function getdata(category_id) {
            // showLoader();
            if ($('input#all_date_filter').val()) {
                start = $('input#all_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                end = $('input#all_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
            }
            $.ajax({
                url: '/reports/category-analytics/' + category_id,
                method: 'GET',
                data: {
                    start_date: start,
                    end_date: end,
                },
                dataType: 'json',
                success: function(response) {
                    // hideLoader();
                    const productDropdown = $('#productDropdown');

                    // Clear existing options
                    productDropdown.empty();

                    // Add the disabled and selected option as the first option
                    productDropdown.append($('<option>', {
                        value: '', // Empty value
                        text: 'Select Product',
                        disabled: true,
                        selected: true,
                    }));

                    // Populate the dropdown with product names
                    const productList = response[0];
                    productList.forEach(function(product) {
                        // Create the URL for each product
                        const productUrl = '/reports/product-analytics/' + product.id;

                        // Create the option element with the URL
                        const option = $('<option>', {
                            value: product.id, // Assuming 'id' is the ID of the product
                            text: product.name, // Assuming 'name' is the name of the product
                            data: {
                                url: productUrl
                            }, // Store the URL in 'data-url' attribute
                        });

                        // Append the option to the dropdown
                        productDropdown.append(option);
                    });

                    // Initialize Select2
                    productDropdown.select2({
                        placeholder: 'Select a product',
                        // Add any additional options or configurations for Select2 here
                    });
                    productDropdown.on('change', function() {
                        const selectedUrl = $(this).find(':selected').data('url');
                        if (selectedUrl) {
                            window.open(selectedUrl, '_blank');
                        }
                    });
                    setMainChart(response[1]);
                    const {
                        category_name,
                        product_qty,
                        sales_qty,
                        sales_amt,
                        purchase_qty,
                        purchase_amt,
                        avg_margin_per,
                    } = response[2];
                    const topCustomers = response[3];
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
                    $('.category_name').html(category_name);
                    $('.product_qty').html(product_qty);
                    $('.sales_qty').html(sales_qty);
                    $('.sales_amt').html(sales_amt);
                    $('.purchase_qty').html(purchase_qty);
                    $('.purchase_amt').html(purchase_amt);
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
                url: '/reports/cat-ranktable/' + category_id,
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
                    // var counter = 0;
                    // If there are no products available in the response, display "Data not available" message
                    if (response.products.length === 0) {
                        tableBody.append(
                            '<tr><td colspan="3" class="text-center">Data not available</td></tr>');
                    } else {
                        $.each(response.products, function(index, product) {
                            // if (counter < 10) {
                            var row = '<tr>' +
                                '<td>' + product.rank + '</td>' +
                                '<td><a href="/reports/product-analytics/' + product.product_id +
                                '" target="_blank">' + product.name + '</a></td>' +
                                // '<td>' + product.name + '</td>' +
                                '<td>' + __currency_trans_from_en(product.high_purchase) + '</td>' +
                                '</tr>';
                            tableBody.append(row);
                            // 
                            // counter++;
                            // }
                        });

                        // Add this code after the above code block
                        // var row = '<tr class="product-row" data-product-id="' + product
                        //             .product_id + '">' +
                        //             '<td>' + product.rank + '</td>' +
                        // $(document).on('click', '.product-row', function() {
                        //     var productId = $(this).data('product-id');
                        //     window.open('/reports/product-analytics/' + productId, '_blank');
                        // });

                    }
                }
            });

            $.ajax({
                url: '/reports/category-ranktable', // Update the URL to match your route
                type: 'GET',
                data: {
                    start_date: start,
                    end_date: end,
                },
                dataType: 'json',
                success: function(response) {
                    // hideLoader();
                    var tableBody = $('#categoryTableBody');
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
                        $.each(response.products, function(index, category) {
                            if (category.cat_id == category_id) {
                                selectedCategoryRow =
                                    '<tr style="background-color: yellow;">' +
                                    '<td>' + category.rank + '</td>' +
                                    '<td>' + category.name + '</td>' +
                                    '<td>' + __currency_trans_from_en(category.high_purchase) +
                                    '</td>' +
                                    '</tr>';
                                counter++; // Increment counter for selected category
                            } else {
                                remainingRows.push(
                                    '<tr>' +
                                    '<td>' + category.rank + '</td>' +
                                    '<td>' + category.name + '</td>' +
                                    '<td>' + __currency_trans_from_en(category.high_purchase) +
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
                url: '/reports/category-margin-chart/' + category_id,
                method: 'GET',
                data: {
                    start_date: start,
                    end_date: end,
                },
                dataType: 'json',
                success: function(response) {
                    // hideLoader();
                    marginchart(response);
                },
            });

            $.ajax({
                url: '/reports/state_tier_chart/' + category_id,
                method: 'GET',
                data: {
                    start_date: start,
                    end_date: end,
                },
                dataType: 'json',
                success: function(response) {
                    // hideLoader();
                    statechart(response[0]);
                    tierchart(response[1]);
                },
            });

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

            if (!response || response.length === 0) {
                chartele4.style.display = "none";
                noDataMessage.style.display = "block";
            } else {
                chartele4.style.display = "block";
                noDataMessage.style.display = "none";

                // options4.labels = response.map(item => item.x);
                // options4.series = response.map(item => parseInt(item.y));
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
        }
    </script>
@endsection
