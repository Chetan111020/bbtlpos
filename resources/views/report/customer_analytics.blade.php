@extends('layouts.app')
@section('title', 'Customer Analytics')
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

        .info_col {
            width: 50%;
            float: left;
            padding-left: 10px;
            padding-right: 10px;
        }
        
        
        .button {
  -webkit-appearance: none;
  appearance: none;
  position: relative;
  border-width: 0;
  padding: 0 8px 12px;
  min-width: 10em;
  box-sizing: border-box;
  background: transparent;
  font: inherit;
  cursor: pointer;
}

.button-top {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 0;
  padding: 8px 16px;
  transform: translateY(0);
  text-align: center;
  color: #fff;
  text-shadow: 0 -1px rgba(0, 0, 0, .25);
  transition-property: transform;
  transition-duration: .2s;
  -webkit-user-select: none;
  user-select: none;
}

.button:active .button-top {
  transform: translateY(6px);
}

.button-top::after {
  content: '';
  position: absolute;
  z-index: -1;
  border-radius: 4px;
  width: 100%;
  height: 100%;
  box-sizing: content-box;
  background-image: radial-gradient(#3dcd9e, #369d8d);
  text-align: center;
  color: #fff;
  box-shadow: inset 0 0 0px 1px rgba(255, 255, 255, .2), 0 1px 2px 1px rgba(255, 255, 255, .2);
  transition-property: border-radius, padding, width, transform;
  transition-duration: .2s;
}

.button:active .button-top::after {
  border-radius: 6px;
  padding: 0 2px;
}

.button-bottom {
  position: absolute;
  z-index: -1;
  bottom: 4px;
  left: 4px;
  border-radius: 8px / 16px 16px 8px 8px;
  padding-top: 6px;
  width: calc(100% - 8px);
  height: calc(100% - 10px);
  box-sizing: content-box;
  background-color: #38a19d;
  background-image: radial-gradient(4px 8px at 4px calc(100% - 8px), rgba(255, 255, 255, .25), transparent), radial-gradient(4px 8px at calc(100% - 4px) calc(100% - 8px), rgba(255, 255, 255, .25), transparent), radial-gradient(16px at -4px 0, white, transparent), radial-gradient(16px at calc(100% + 4px) 0, white, transparent);
  box-shadow: 0px 2px 3px 0px rgba(0, 0, 0, 0.5), inset 0 -1px 3px 3px rgba(0, 0, 0, .4);
  transition-property: border-radius, padding-top;
  transition-duration: .2s;
}

.button:active .button-bottom {
  border-radius: 10px 10px 8px 8px / 8px;
  padding-top: 0;
}

.button-base {
  position: absolute;
  z-index: -2;
  top: 4px;
  left: 0;
  border-radius: 12px;
  width: 100%;
  height: calc(100% - 4px);
  background-color: rgba(0, 0, 0, .15);
  box-shadow: 0 1px 1px 0 black, inset 0 2px 2px rgba(0, 0, 0, .25);
}
    </style>

@endsection
@section('content')

    <section class="content">
        {{-- <div style="display: flex; background: white; padding: 15px; justify-content: center; align-items: center;">
            <input type="hidden" id="customer_id" value="{{ $customer_id }}" />
            <div class="form-group col-md-4 col-sm-6" style="text-align: center;">
                {!! Form::label('sell_list_filter_customer_id', __('Select Customer') . ':') !!}
                {!! Form::select('sell_list_filter_customer_id', $customers, null, [
                    'class' => 'form-control select2',
                    'style' => 'width: 100%',
                    'id' => 'filter_customer_id',
                    'placeholder' => __('lang_v1.all'),
                ]) !!}
            </div>
            @php
                $selectedCustomerId = !empty($customer_id) ? $customer_id : 0;
            @endphp
        </div> --}}
        @component('components.filters', ['title' => __('report.filters')])
            <input type="hidden" id="customer_id" value="{{ $customer_id }}" />
            @php
                $selectedCustomerId = !empty($customer_id) ? $customer_id : 0;
            @endphp
            <div class="row">
                <div class="col-md-4 col-sm-4">
                    <div class="form-group" style="text-align: center;">
                        {!! Form::label('sell_list_filter_customer_id', __('Select Customer') . ':') !!}
                        {!! Form::select('sell_list_filter_customer_id', $customers, null, [
                            'class' => 'form-control select2',
                            'style' => 'width: 100%',
                            'id' => 'filter_customer_id',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-5 col-sm-5" style="margin-top: 23px;display:none;">
                    <div class="btn-group" data-toggle="buttons" style="margin-left: 15%;">
                        <label class="btn btn-info active">
                            <input type="radio" name="date-filter" data-start="{{ $date_filters['last_30_days']['start'] }}"
                                data-end="{{ $date_filters['last_30_days']['end'] }}" checked> {{ __('Last 30 Days') }}
                        </label>
                        <label class="btn btn-info">
                            <input type="radio" name="date-filter" data-start="{{ $date_filters['this_fy']['start'] }}"
                                data-end="{{ $date_filters['this_fy']['end'] }}"> {{ __('home.this_fy') }}
                        </label>
                        <label class="btn btn-info">
                            <input type="radio" name="date-filter" id="allTimeStartDate" data-start=""
                                data-end="{{ date('Y-m-d') }}"> {{ __('All Time') }}
                        </label>
                    </div>
                </div>
                <div class="col-md-3 col-sm-3 pull-right" style="display: none;">
                    <div class="form-group">
                        {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
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
                <div class="col-md-2 pull-right">
                     
                    <a target="_blank" href="{{action('SellPosController@create')}}" role="button" class="button">
                        <div class="button-top"><i class="fa fa-th-large"></i> &nbsp; POS</div>
                        <div class="button-bottom"></div>
                        <div class="button-base"></div>
                    </a>                      
                </div>
            </div>
        @endcomponent
        <div style="display: none;" id="customer-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-solid">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h3><span style="font-weight: bold;" class="customer_name"></span></h3>
                                </div>
                            </div>
                            <div class="row">
                                <div style="border-color: #00acd6;" class="col-md-4 border-right">
                                    <h3 class="text-center">Customer Details</h3>
                                    <hr>
                                    <div class="form-group row">
                                        <label class="col-md-4 col-sm-4" for="">Email: </label>
                                        <span class="email col-md-7 col-sm-7" style="color: black;"></span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 col-sm-4" for="">Joining Date: </label>
                                        <span class="join_date col-md-7 col-sm-7" style="color: black;"></span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 col-sm-4" for="">Mobile: </label>
                                        <span class="mobile col-md-7 col-sm-7" style="color: black;"></span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 col-sm-4" for="">State: </label>
                                        <span class="state col-md-7 col-sm-7" style="color: black;"></span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 col-sm-4" for="">City : </label>
                                        <span class="city col-md-7 col-sm-7" style="color: black;"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h3 class="text-center">Customer Purchase Summary</h3>
                                    <hr>
                                    <div class="form-group row">
                                        <label for="" class="col-md-6 col-sm-6">Purchase: </label>
                                        <span class="total_purchased_datewaise col-md-6 col-sm-6"
                                            style="color: black;"></span>
                                    </div>
                                    <div class="form-group row">
                                        <label for="" class="col-md-6 col-sm-6">Purchase Return: </label>
                                        <span class="purchase_return col-md-6 col-sm-6" style="color: black;"></span>
                                    </div>

                                    <div class="form-group row">
                                        <label for="" class="col-md-6 col-sm-6">Avg Monthly Spend: </label>
                                        <span class="avg_monthly_purchase col-md-6 col-sm-6" style="color: black;"></span>
                                    </div>
                                    <div class="form-group row">
                                        <label for="" class="col-md-6 col-sm-6">Avg Yearly Spend: </label>
                                        <span class="avg_yearly_purchase col-md-6 col-sm-6" style="color: black;"></span>
                                    </div>
                                </div>
                                <div style="border-color: #00acd6;" class="col-md-4 border-left">
                                    <h3 class="text-center">Customer Loyalty</h3>
                                    <hr>
                                    <div class="form-group row">
                                        <label for="" class="col-md-6 col-sm-6">Total Due: </label>
                                        <span class="total_duesum col-md-6 col-sm-6" style="color: black;"></span>
                                    </div>
                                    <div class="form-group row">
                                        <label for="" class="col-md-6 col-sm-6">Due Percentage: </label>
                                        <span class="due_percentage col-md-6 col-sm-6" style="color: black;"></span>
                                    </div>
                                    <div class="form-group row">
                                        <label for="" class="col-md-6 col-sm-6">Purchase Return Percentage:
                                        </label>
                                        <span class="purchase_return_percentage col-md-6 col-sm-6"
                                            style="color: black;"></span>
                                    </div>
                                    <div class="form-group row">
                                        <label for="" class="col-md-6 col-sm-6" style="font-size: large;">Rank:
                                        </label>
                                       <b><span class="rank col-md-6 col-sm-6" style="font-size: large;"
                                            style="color: black;"></span></b>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div style="display: flex; margin: 1rem; margin-bottom: 0;" id="">
                <div class="dash_info_ele" style="width: 100% !important">
                    {{-- <div class="row">
                    <div class="" style="display: flex; justify-content: center;">


                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-6" style="">
                        <div class="dash_info_ele " style="width:100%!important;">
                            <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color5">
                                <label style="margin:0!important">Purchase</label>
                            </div>
                            <div style="display:flex">
                                <div style="display: flex;width:100%;flex-direction:column; align-items: center;">
                                    <div style="display:flex;width:100%;">
                                        <h3 class="total_purchased_datewaise"
                                            style="margin: 0 auto; margin-top: 5%; margin-left: 50%;">
                                        </h3>
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
                                <label style="margin:0!important">Purchase Return</label>
                            </div>
                            <div style="display:flex">
                                <div style="display: flex;width:100%;flex-direction:column; align-items: center;">
                                    <div style="display:flex;width:100%;">
                                        <h3 class="purchase_return"
                                            style="margin: 0 auto; margin-top: 5%; margin-left: 50%;">
                                        </h3>
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
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-6" style="">
                        <div class="dash_info_ele " style="width:100%!important;">
                            <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color2">
                                <label style="margin:0!important">Paid</label>
                            </div>
                            <div style="display:flex">
                                <div style="display: flex;width:100%;flex-direction:column; align-items: center;">
                                    <div style="display:flex;width:100%;">
                                        <h3 class="total_paid" style="margin: 0 auto; margin-top: 5%; margin-left: 50%;">
                                        </h3>
                                    </div>
                                </div>
                                <div class="dash_icon dash_ele_color2" style="margin-top:1rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-dollar-sign">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-6" style="">
                        <div class="dash_info_ele " style="width:100%!important;">
                            <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color4">
                                <label style="margin:0!important">Due</label>
                            </div>
                            <div style="display:flex">
                                <div style="display: flex;width:100%;flex-direction:column; align-items: center;">
                                    <div style="display:flex;width:100%;">
                                        <h3 class="total_due" style="margin: 0 auto; margin-top: 5%; margin-left: 50%;">
                                        </h3>
                                    </div>
                                </div>
                                <div class="dash_icon dash_ele_color4" style="margin-top:1rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        stroke="currentColor" stroke-width="2" fill="none"
                                        class="bi bi-arrow-down dash_svg_icon" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd"
                                            d="M8 1a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L7.5 13.293V1.5A.5.5 0 0 1 8 1z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
                    <div style="display: flex; margin: 1rem; margin-bottom: 0;background-color: white" id="">
                        <div style="width:50%;margin:1em;" class="dash_ele_color2">
                            <h2 style="margin: 1rem;">Favorite Products</h2>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="text-center">
                                        <tr>
                                            <th class="text-center">No.</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Total Purchase</th>
                                            <th class="text-center">Date <small>(Last Purchase)</small></th>
                                        </tr>
                                    </thead>
                                    <tbody id="ProductTableBody">

                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="dash_ele_color1" style="width: 50%; margin: 1em;">
                            <h2 style="margin: 1rem;">Top Category</h2>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="text-center">
                                        <tr>
                                            <th class="text-center">No.</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Total Purchase</th>
                                            <th class="text-center">Date <small>(Last Purchase)</small></th>
                                        </tr>
                                    </thead>
                                    <tbody id="CategoryTableBody">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; margin: 1rem; margin-bottom: 0;background-color: white" id="">
                        <div style="width:50%;margin:1em;" class="dash_ele_color3">
                            <h2 style="margin: 1rem;">Top Products (City Wise)</h2>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="text-center">
                                        <tr>
                                            <th class="text-center">No.</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Total Purchase</th>
                                            <th class="text-center">Date <small>(Last Purchase)</small></th>
                                        </tr>
                                    </thead>
                                    <tbody id="City-Wise-ProductTableBody">

                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="dash_ele_color4" style="width: 50%; margin: 1em;">
                            <h2 style="margin: 1rem;">Top Category (City Wise)</h2>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="text-center">
                                        <tr>
                                            <th class="text-center">No.</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Total Purchase</th>
                                            <th class="text-center">Date <small>(Last Purchase)</small></th>
                                        </tr>
                                    </thead>
                                    <tbody id="City-Wise-Table">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div style="margin: 1rem;">
                <div style="background-color: white; padding: 1rem;">
                    <h2 style="text-align: center;">Customer Invoices</h2>
                    <div class="panel-body">
                        <table class="table table-bordered table-striped ajax_view" id="sell_table">
                            <thead>
                                <tr class="no-print">
                                    <th>@lang('messages.action')</th>
                                    <th>@lang('messages.date')</th>
                                    <th>@lang('sale.invoice_no')</th>
                                    <th>@lang('Web Order No')</th>
                                    <th>@lang('sale.customer_name')</th>
                                    <th>@lang('lang_v1.contact_no')</th>
                                    <th>@lang('Picked By')</th>
                                    <th>@lang('Packed By')</th>
                                    <th>@lang('sale.location')</th>
                                    <th>@lang('lang_v1.payment_method')</th>
                                    <th>@lang('sale.total_amount')</th>
                                    @can('show_gp')
                                        <th>@lang('Total GP')</th>
                                    @endcan
                                    <th>@lang('Discount Amount')</th>
                                    <th>@lang('sale.total_paid')</th>
                                    <th>@lang('lang_v1.sell_due')</th>
                                    <th>Open Balance</th>
                                    <!--<th>@lang('lang_v1.sell_return_due')</th>-->
                                    <th>@lang('Order Status')</th>
                                    <th>@lang('sale.payment_status')</th>
                                    <th>@lang('lang_v1.shipping_status')</th>
                                    <th>@lang('lang_v1.total_items')</th>
                                    <th>@lang('lang_v1.types_of_service')</th>
                                    <th>{{ $custom_labels['types_of_service']['custom_field_1'] ?? __('lang_v1.service_custom_field_1') }}
                                    </th>
                                    <th>@lang('lang_v1.added_by')</th>
                                    <th>@lang('sale.sell_note')</th>
                                    <th>@lang('sale.staff_note')</th>
                                    <th>@lang('sale.shipping_details')</th>
                                    <th>@lang('restaurant.table')</th>
                                    <th>@lang('restaurant.service_staff')</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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
            var customer_id;


            function getSelectedDateRange() {

                var selectedDateRange;
                var radioDate = $('input[name="date-filter"]:checked');
                if (radioDate.length > 0) {
                    selectedDateRange = {
                        start: radioDate.data('start'),
                        end: radioDate.data('end')
                    };
                } else {
                    // Use the date range picker
                    var dateRangeInput = $('#all_date_filter');
                    if (dateRangeInput.val()) {
                        var dateRange = dateRangeInput.val().split(' ~ ');
                        selectedDateRange = {
                            start: dateRange[0],
                            end: dateRange[1]
                        };
                    }
                }
                return selectedDateRange;
            }

            $('#results-table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    if (aData[1] == $('.customer_name').val()) {
                        $('td', nRow).css('background-color', 'Red');
                    }
                },
                // scrollY: '300px',
                ajax: {
                    "url": "/reports/customerdata",
                },
                columns: [{
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'avg_monthly_spend',
                        name: 'avg_monthly_spend',
                        searchable: false,
                        render: function(data, type, row) {
                            return __currency_trans_from_en(data);
                        }
                    },
                    {
                        data: 'monthly_purchase',
                        name: 'monthly_purchase',
                        searchable: false,
                        render: function(data, type, row) {
                            return __currency_trans_from_en(data);
                        }
                    },
                ]
            });

            if ($('#customer_id').val() != 0) {
                customer_id = $('#customer_id').val();
            } else {
                customer_id = $('#filter_customer_id').data(
                    'selected-customer-id');
            }
            var selectedCustomerId = {!! json_encode($selectedCustomerId) !!};

            $('#all_date_filter').daterangepicker({
                ranges: ranges,
                autoUpdateInput: true,
                startDate: moment().startOf('week'),
                endDate: moment().endOf('week'),
                locale: {
                    format: moment_date_format
                }
            });
            $(document).on('change', 'input[name="date-filter"]', function() {


                var selectedId = $('#filter_customer_id').data(
                    'selected-customer-id'); // Use the same attribute name

                if (selectedId) {
                    var start = $('input[name="date-filter"]:checked').data('start');
                    var end = $('input[name="date-filter"]:checked').data('end');
                    // Call the getdata function with selectedId, start, and end dates
                    getdata(selectedId, start, end);
                } else {
                    toastr.error('Select Customer');

                }
            });
            $('#all_date_filter').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format(moment_date_format) + ' ~ ' + picker.endDate.format(
                    moment_date_format));
                $("#date").val($(this).val());
                $('input[name="date-filter"]').prop('checked', false);
                var selectedId = $('#filter_customer_id').data(
                    'selected-customer-id'); // Use the same attribute name

                if (selectedId) {
                    var start = picker.startDate.format('YYYY-MM-DD');
                    var end = picker.endDate.format('YYYY-MM-DD');

                    // Call the getdata function with selectedId, start, and end dates
                    getdata(selectedId, start, end);
                } else {
                    toastr.error('Select Customer');

                }
            });

            $('#all_date_filter').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            $('#filter_customer_id').on('change', function() {
                $('#customer-content').show();
                var customer_id = $(this).val();

                $(this).data('selected-customer-id', customer_id);

                var selectedDateRange = getSelectedDateRange();

                // Call the getdata function with the selected customer_id and date range
                getdata(customer_id, selectedDateRange.start, selectedDateRange.end);
                // getdata(customer_id);
            });
            $('input[name="date-filter"]').on('change', function() {
                var selectedDateRange = getSelectedDateRange();
                $('#all_date_filter').val(selectedDateRange.start + ' ~ ' + selectedDateRange.end);
            });
            if (selectedCustomerId != 0) {
                $('#filter_customer_id').val(selectedCustomerId).trigger('change');
            }
            if ($('#customer_id').val() != 0) {
                $('#customer-content').show();

                var selectedDateRange = getSelectedDateRange();

                // Call the getdata function with the selected customer_id and date range
                getdata($('#customer_id').val(), selectedDateRange.start, selectedDateRange.end);
                // Call the getdata function with the selected product_id and date range
            }


            function getdata(customer_id, start, end) {
                if ($.fn.DataTable.isDataTable('#sell_table')) {
                    $('#sell_table').DataTable().destroy();
                }
                sell_table = $('#sell_table').DataTable({
                    processing: true,
                    serverSide: true,
                    aaSorting: [
                        [2, 'desc']
                    ],
                    iDisplayLength: 50,
                    "ajax": {
                        "url": "/reports/customersell/" + customer_id,
                        "data": function(d) {
                            d.start_date = start;
                            d.end_date = end;
                            d = __datatable_ajax_callback(d);
                        }
                    },
                    scrollY: "75vh",
                    scrollX: true,
                    scrollCollapse: true,
                    columns: [{
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            "searchable": false
                        },
                        {
                            data: 'transaction_date',
                            name: 'transaction_date'
                        },
                        {
                            data: 'invoice_no',
                            name: 'invoice_no'
                        },
                        {
                            data: 'woocommerce_order_id',
                            name: 'woocommerce_order_id',
                            visible: false
                        },
                        {
                            data: 'name',
                            name: 'contacts.name'
                        },
                        {
                            data: 'mobile',
                            name: 'contacts.mobile',
                            visible: false
                        },
                        {
                            data: 'picked_by',
                            name: 'au.first_name',
                            visible: false
                        },
                        {
                            data: 'packed_by',
                            name: 'pb.first_name',
                            visible: false
                        },
                        {
                            data: 'business_location',
                            name: 'bl.name',
                            visible: false
                        },
                        {
                            data: 'payment_methods',
                            orderable: false,
                            "searchable": false,
                            visible: false
                        },
                        {
                            data: 'final_total',
                            name: 'final_total'
                        },
                        @can('show_gp')
                            {
                                data: 'total_gp',
                                name: 'total_gp',
                                visible: false
                            },
                        @endcan {
                            data: 'discount_amount',
                            name: 'discount_amount',
                            visible: false
                        },
                        {
                            data: 'total_paid',
                            name: 'total_paid',
                            "searchable": false
                        },
                        {
                            data: 'total_remaining',
                            name: 'total_remaining'
                        },
                        // { data: 'return_due', orderable: false, "searchable": false},
                        {
                            data: 'balance_due',
                            orderable: false,
                            "searchable": false
                        },

                        {
                            data: 'order_status',
                            name: 'order_status',
                            orderable: false,
                            "searchable": false
                        },
                        {
                            data: 'payment_status',
                            name: 'payment_status',
                            visible: false
                        },
                        {
                            data: 'shipping_status',
                            name: 'shipping_status',
                            visible: false
                        },
                        {
                            data: 'total_items',
                            name: 'total_items',
                            "searchable": false
                        },
                        {
                            data: 'types_of_service_name',
                            name: 'tos.name',
                            @if (empty($is_types_service_enabled))
                                visible: false
                            @endif
                        },
                        {
                            data: 'service_custom_field_1',
                            name: 'service_custom_field_1',
                            @if (empty($is_types_service_enabled))
                                visible: false
                            @endif
                        },
                        {
                            data: 'added_by',
                            name: 'u.first_name'
                        },
                        {
                            data: 'additional_notes',
                            name: 'additional_notes'
                        },
                        {
                            data: 'staff_note',
                            name: 'staff_note'
                        },
                        {
                            data: 'shipping_details',
                            name: 'shipping_details',
                            visible: false
                        },
                        {
                            data: 'table_name',
                            name: 'tables.name',
                            @if (empty($is_tables_enabled))
                                visible: false
                            @endif
                        },
                        {
                            data: 'waiter',
                            name: 'ss.first_name',
                            visible: false,
                            @if (empty($is_service_staff_enabled))
                                visible: false
                            @endif
                        },
                    ],
                    "fnDrawCallback": function(oSettings) {
                        __currency_convert_recursively($('#sell_table'));
                    },
                    createdRow: function(row, data, dataIndex) {
                        $(row).find('td:eq(6)').attr('class', 'clickable_td');
                        $(row).find('td:eq(0)').attr('class', 'selectable_td');

                    }
                });


                $.ajax({
                    url: '/reports/customer-analytics/' + customer_id,
                    method: 'GET',
                    data: {
                        start_date: start,
                        end_date: end,
                    },
                    dataType: 'json',
                    success: function(response) {
                        const {
                            name,
                            email,
                            total_invoice,
                            created_at,
                            mobile,
                            state,
                            city,
                            // total_discount: all_total_discount
                        } = response[1];
                        // Format the created_at date
                        const formattedDate = new Date(created_at).toLocaleString('en-US', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                        });
                        const allTime = '2021-01-01';
                        // const allTime = new Date(response[8]).toISOString().slice(0, 10);
                        $('.rank').html(response[9]);
                        // Set the data-start attribute for the "All Time" radio button
                        $('#allTimeStartDate').attr('data-start', allTime);


                        if (email == "" || email == 'null') {
                            var display_email = 'Not Available';
                        } else {
                            display_email = email;
                        }
                        // var display_email = email ?? 'Not Available';
                        $('.email').html(display_email);
                        $('.customer_name').html(name);
                        $('.mobile').html(mobile);
                        $('.city').html(city);
                        $('.state').html(state);
                        $('.join_date').html(formattedDate);
                        $('.total_purchase').html(__currency_trans_from_en(response[4]));

                        const {
                            total_invoice: total_purchased_datewaise,
                            purchase_return,
                            total_return
                        } = response[2];
                        $('.total_purchased_datewaise').html(
                            `${__currency_trans_from_en(total_purchased_datewaise)}`);
                        $('.purchase_return').html(`${__currency_trans_from_en(total_return)}`);

                        const {
                            total_return: total_purchase_return,
                            balance_due
                        } = response[5];
                        $('.total_purchase_return').html(
                            `${__currency_trans_from_en(total_purchase_return)}`);
                        $('.total_duesum').html(__currency_trans_from_en(balance_due));
                        const {
                            avg_monthly_purchase,
                            months_active,
                            quarters_active,
                            avg_qua,
                            avg_yearly_purchase,
                            total_paid,
                            total_due,
                            // total_duesum
                        } = response[3];
                        $('.avg_monthly_purchase').html(__currency_trans_from_en(avg_monthly_purchase) +
                            '<br>' +
                            '(' + 'Month Active :' + ' ' + months_active + ')');

                        $('.avg_yearly_purchase').html(avg_yearly_purchase);
                        $('.total_paid').html(total_paid);
                        $('.total_due').html(total_due);


                        var rawValue = response[6];
                        var formattedValue = rawValue.toFixed(2) + ' ' + "%";
                        $('.due_percentage').html(formattedValue);

                        var return_per = response[7];
                        var result = return_per.toFixed(2) + ' ' + "%";
                        $('.purchase_return_percentage').html(result);
                    },
                });

                $.ajax({
                    url: '/reports/customer-fav-products/' + customer_id,
                    method: 'GET',
                    // data: {
                    //     start_date: start,
                    //     end_date: end,
                    // },
                    dataType: 'json',
                    success: function(response) {
                        console.log(response);
                        var tableBody = $('#ProductTableBody');
                        tableBody.empty();
                        var CityProduct = $('#City-Wise-ProductTableBody');
                        CityProduct.empty();
                        if (response.products.length === 0) {
                            tableBody.append(
                                '<tr><td colspan="4" class="text-center">Data not available</td></tr>'
                            );
                        } else {
                            $.each(response.products, function(index, product) {
                                var latestPurchaseDate = moment(product.latest_purchase_date);
                                // Format the date using Carbon's format method
                                var formattedDate = latestPurchaseDate.format('YYYY/MM/DD');
                                var row = '<tr>' +
                                    '<td class="text-center">' + (index + 1) + '</td>' +
                                    '<td><a href="/reports/product-analytics/' + product
                                    .product_id +
                                    '" target="_blank">' + product.product_name + '</a></td>' +
                                    // '<td>' + product.product_name + '</td>' +
                                    '<td class="text-center">' + __currency_trans_from_en(product.purchase) +
                                    '</td>' +
                                    '<td class="text-center">' + formattedDate +
                                    '</td>' +
                                    '</tr>';
                                tableBody.append(row);

                            });
                        }
                        if (response.city_products.length === 0) {
                            CityProduct.append(
                                '<tr><td colspan="4" class="text-center">Data not available</td></tr>'
                            );
                        } else {
                            $.each(response.city_products, function(index, product) {
                                if (product.latest_purchase_date_city) {
                                    var latestPurchaseDate = moment(product
                                        .latest_purchase_date_city);
                                        console.log(latestPurchaseDate);
                                    // Format the date using Carbon's format method
                                    var formattedDate = latestPurchaseDate.format('YYYY/MM/DD');
                                } else {
                                    var formattedDate = '-';
                                }


                                var row = '<tr>' +
                                    '<td class="text-center">' + (index + 1) + '</td>' +
                                    '<td><a href="/reports/product-analytics/' + product
                                    .product_id +
                                    '" target="_blank">' + product.product_name + '</a></td>' +
                                    // '<td>' + product.product_name + '</td>' +
                                    '<td class="text-center">' + __currency_trans_from_en(product.purchase) +
                                    '</td>' +
                                    '<td class="text-center">' + formattedDate +
                                    '</td>' +
                                    '</tr>';
                                CityProduct.append(row);

                            });
                        }
                    },
                });

                $.ajax({
                    url: '/reports/customer-fav-category/' + customer_id,
                    method: 'GET',
                    // data: {
                    //     start_date: start,
                    //     end_date: end,
                    // },
                    dataType: 'json',
                    success: function(response) {
                        console.log(response);
                        var tableBody = $('#CategoryTableBody');
                        tableBody.empty();
                        var CityWiseTable = $('#City-Wise-Table');
                        CityWiseTable.empty();

                        if (response.category.length === 0) {
                            tableBody.append(
                                '<tr><td colspan="4" class="text-center">Data not available</td></tr>'
                            );
                        } else {
                            $.each(response.category, function(index, categories) {
                                var latestPurchaseDate = moment(categories
                                    .latest_purchase_date);
                                // Format the date using Carbon's format method
                                var formattedDate = latestPurchaseDate.format('YYYY/MM/DD');
                                var row = '<tr>' +
                                    '<td class="text-center">' + (index + 1) + '</td>' +
                                    '<td><a href="/reports/category-analytics/' + categories
                                    .category_id +
                                    '" target="_blank">' + categories.category_name +
                                    '</a></td>' +
                                    // '<td>' + categories.category_name + '</td>' +
                                    '<td class="text-center">' + __currency_trans_from_en(categories
                                        .high_purchase) +
                                    '</td>' +
                                    '<td class="text-center">' + formattedDate +
                                    '</td>' +
                                    '</tr>';
                                tableBody.append(row);

                            });
                        }
                        if (response.city_category.length === 0) {
                            CityWiseTable.append(
                                '<tr><td colspan="4" class="text-center">Data not available</td></tr>'
                            );
                        } else {
                            $.each(response.city_category, function(index, categories) {
                                if (categories.latest_purchase_date) {
                                    var latestPurchaseDate = moment(categories
                                        .latest_purchase_date);
                                    // Format the date using Carbon's format method
                                    var formattedDate = latestPurchaseDate.format('YYYY/MM/DD');
                                } else {
                                    var formattedDate = '-';
                                }
                                var row = '<tr>' +
                                    '<td class="text-center">' + (index + 1) + '</td>' +
                                    '<td><a href="/reports/category-analytics/' + categories
                                    .category_id +
                                    '" target="_blank">' + categories.category_name +
                                    '</a></td>' +
                                    // '<td>' + categories.category_name + '</td>' +
                                    '<td class="text-center">' + __currency_trans_from_en(categories
                                        .high_purchase) +
                                    '</td>' +
                                    '<td class="text-center">' + formattedDate +
                                    '</td>' +
                                    '</tr>';
                                CityWiseTable.append(row);

                            });
                        }
                    },
                });
            }
        });
    </script>
@endsection
