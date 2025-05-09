@extends('layouts.app')
@section('title',"Product Analytics")

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
            flex-direction:column;
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

        .dash_ele_color5{
            color:#1D976C;
            background: #1D976C;  /* fallback for old browsers */
            background: -webkit-linear-gradient(to right, #93F9B9, #1D976C);  /* Chrome 10-25, Safari 5.1-6 */
            background: linear-gradient(to right, #93f9b98c, #5bffc552);
        }

        .dash_ele_color6{
            color: #cdb90f!important;
            background: rgb(240 215 0 / 10%)!important;
        }

        .myshadow{
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
<div ng-app="myapp" ng-controller="myctrl">
    <div style="display:flex;background:white;padding:15px;">
        <div style="width: 50%">
            <h2>Product Analytics</h2>
            <br />
            <div style="width: 100%">
                <div class="form-group">
                    <label>Search Product</label>
                    {!! Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('Enter Product Name')]) !!}

                </div>
            </div>
        </div>
        <div style="width: 50%;padding:1rem 3rem;">
            <label>Product Details:</label>
            <h3><% product.name %></h3>
            <h5><% product.item_code %></h5>
            <h5><% product.sku %></h5>
            <table class="table" ng-show="have_data">
                <tr>
                    <td>Available Qty.</td>
                    <td>Selling Price</td>
                    <td>Inventory Value</td>
                </tr>
                <tr>
                    <th><% product.qty_available %></th>
                    <th>$ <% product.sell_price_inc_tax %></th>
                    <th>$ <% product.qty_available * product.sell_price_inc_tax %></th>
                </tr>
            </table>
        </div>
    </div>

    <div ng-hide="have_data" class="bg-white" style="width: 100%;height:400px;display:flex;justify-content:center;align-items:center;">
        <div class="newtons-cradle">
            <div class="newtons-cradle__dot"></div>
            <div class="newtons-cradle__dot"></div>
            <div class="newtons-cradle__dot"></div>
            <div class="newtons-cradle__dot"></div>
        </div>
    </div>

    <div ng-show="have_data">

        <div style="display:flex;margin:1rem;margin-bottom:0;">

            <div class="dash_info_ele " style="width:50%!important">
                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color5">
                    <label style="margin:0!important">Total Purchase</label>
                </div>
                <div style="display:flex">
                    <div style="display: flex;width:100%;flex-direction:column;margin:auto;">
                        <div style="display:flex;width:100%;">
                            <h3 style="width: 50%;"><% total_data.purchase_amt %></h3>
                            <h3 style="width: 50%;"><small>Q.</small> <% total_data.purchase_qty %></h3>
                        </div>
                    </div>
                    <div class="dash_icon dash_ele_color5" style="margin-top:1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="dash_info_ele " style="width:50%!important">
                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color5">
                    <label style="margin:0!important">Total Sale</label>
                </div>
                <div style="display:flex">
                    <div style="display: flex;width:100%;flex-direction:column;margin:auto;">
                        <div style="display:flex;width:100%;">
                            <h3 style="width: 50%;"><% total_data.sales_amt %></h3>
                            <h3 style="width: 50%;"><small>Q.</small> <% total_data.sales_qty %></h3>
                        </div>
                    </div>
                    <div class="dash_icon dash_ele_color5" style="margin-top:1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        <div style="display:flex;margin:1rem;margin-bottom:0;">

            <!-- month 1 data -->
            <div class="dash_info_ele dash_ele_color6">
                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color6">
                    <label style="margin:0!important"><% pd_m1.month %></label>
                </div>
                <div style="display:flex">
                    <div style="display: flex;width:100%;flex-direction:column;margin:1rem 0;">
                        <h4>Total Purchase</h4>
                        <div style="display:flex;width:100%;">
                            <h3 style="width: 50%;"><% pd_m1.purchase %></h3>
                            <h3 style="width: 50%;"><small>Q.</small> <% pd_m1.pur_qty %></h3>
                        </div>
                    </div>
                    <div class="dash_icon dash_ele_color6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <div style="display:flex">
                    <div style="display: flex;width:100%;flex-direction:column;">
                        <h4>Total Sale</h4>
                        <div style="display:flex;width:100%;">
                            <h3 style="width: 50%;"><% pd_m1.sales %></h3>
                            <h3 style="width: 50%;"><small>Q.</small> <% pd_m1.qty %></h3>
                        </div>
                    </div>
                    <div class="dash_icon dash_ele_color6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- month 2 data -->
            <div class="dash_info_ele dash_ele_color2">
                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color2">
                    <label style="margin:0!important"><% pd_m2.month %></label>
                </div>
                <div style="display:flex">
                    <div style="display: flex;width:100%;flex-direction:column;margin:1rem 0;">
                        <h4>Total Purchase</h4>
                        <div style="display:flex;width:100%;">
                            <h3 style="width: 50%;"><% pd_m2.purchase %></h3>
                            <h3 style="width: 50%;"><small>Q.</small> <% pd_m2.pur_qty %></h3>
                        </div>
                    </div>
                    <div class="dash_icon dash_ele_color2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <div style="display:flex">
                    <div style="display: flex;width:100%;flex-direction:column;">
                        <h4>Total Sale</h4>
                        <div style="display:flex;width:100%;">
                            <h3 style="width: 50%;"><% pd_m2.sales %></h3>
                            <h3 style="width: 50%;"><small>Q.</small> <% pd_m2.qty %></h3>
                        </div>
                    </div>
                    <div class="dash_icon dash_ele_color2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- month 3 data -->
            <div class="dash_info_ele dash_ele_color3">
                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color3">
                    <label style="margin:0!important"><% pd_m3.month %></label>
                </div>
                <div style="display:flex">
                    <div style="display: flex;width:100%;flex-direction:column;margin:1rem 0;">
                        <h4>Total Purchase</h4>
                        <div style="display:flex;width:100%;">
                            <h3 style="width: 50%;"><% pd_m3.purchase %></h3>
                            <h3 style="width: 50%;"><small>Q.</small> <% pd_m3.pur_qty %></h3>
                        </div>
                    </div>
                    <div class="dash_icon dash_ele_color3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <div style="display:flex">
                    <div style="display: flex;width:100%;flex-direction:column;">
                        <h4>Total Sale</h4>
                        <div style="display:flex;width:100%;">
                            <h3 style="width: 50%;"><% pd_m3.sales %></h3>
                            <h3 style="width: 50%;"><small>Q.</small> <% pd_m3.qty %></h3>
                        </div>
                    </div>
                    <div class="dash_icon dash_ele_color3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;margin:2rem;" class="bg-white myshadow">
            <div style="height:400px;width:100%;margin:1em;">
                <h2 style="margin: 1rem;">Purchase & Sales<small> ( Last 20 Weeks ) </small></h2>
                <div id="chart3"></div>
            </div>
        </div>

        <div style="display:flex;margin:1rem;margin-bottom:0;">
            <div class="dash_info_ele" style="flex-direction: column; width:100% !important;">
                <label style="margin:0!important;">Credit Memo</label>
            </div>
        </div>

        <div style="display:flex;margin:1rem;margin-bottom:0;">

            <!-- month 1 data -->
            <div class="dash_info_ele dash_ele_color6">
                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color6">
                    <label style="margin:0!important"><% pd_m1.month %></label>
                </div>
                <div style="display:flex">
                    <div style="display: flex;width:100%;flex-direction:column;margin:1rem 0;">
                        <h4>Sales Return</h4>
                        <div style="display:flex;width:100%;">
                            <h3 style="width: 50%;"><% pd_m1.sales_rtn %></h3>
                            <h3 style="width: 50%;"><small>Q.</small> <% pd_m1.qty_rtn %></h3>
                        </div>
                    </div>
                    <div class="dash_icon dash_ele_color6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- month 2 data -->
            <div class="dash_info_ele dash_ele_color2">
                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color2">
                    <label style="margin:0!important"><% pd_m2.month %></label>
                </div>
                <div style="display:flex">
                    <div style="display: flex;width:100%;flex-direction:column;margin:1rem 0;">
                        <h4>Sales Return</h4>
                        <div style="display:flex;width:100%;">
                            <h3 style="width: 50%;"><% pd_m2.sales_rtn %></h3>
                            <h3 style="width: 50%;"><small>Q.</small> <% pd_m2.qty_rtn %></h3>
                        </div>
                    </div>
                    <div class="dash_icon dash_ele_color2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- month 3 data -->
            <div class="dash_info_ele dash_ele_color3">
                <div style="display:flex;justify-content:center;padding:1rem" class="dash_ele_color3">
                    <label style="margin:0!important"><% pd_m3.month %></label>
                </div>
                <div style="display:flex">
                    <div style="display: flex;width:100%;flex-direction:column;margin:1rem 0;">
                        <h4>Sales Return</h4>
                        <div style="display:flex;width:100%;">
                            <h3 style="width: 50%;"><% pd_m3.sales_rtn %></h3>
                            <h3 style="width: 50%;"><small>Q.</small> <% pd_m3.qty_rtn %></h3>
                        </div>
                    </div>
                    <div class="dash_icon dash_ele_color3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop
@section('javascript')

<script src="{{ asset('/assets/libs/angularjs/angular.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
var angularapp = angular.module('myapp', []);

angularapp.config(function($interpolateProvider) {
    $interpolateProvider.startSymbol('<%');
    $interpolateProvider.endSymbol('%>');
});

angularapp.controller('myctrl', function($scope, $http) {
    $http.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

    //collections
    $scope.have_data = false;
    $scope.product = {name:"No Product Selected."};
    $scope.mainchart = [];
    $scope.pd_m1= [];
    $scope.pd_m2 = [];
    $scope.pd_m3 = [];
    $scope.total_data = [];

    //get init
    var initLoader = function(id,item){
        $scope.product = item;
        $scope.have_data = false;
        var path = "/Analytics/Product/"+id;
        $http.get(path).then(function (response) {
            console.log(response.data);

            setMainChart(response.data[0]);
            $scope.pd_m1= response.data[1][0];
            $scope.pd_m2 = response.data[1][1];
            $scope.pd_m3 = response.data[1][2];
            $scope.total_data = response.data[2];

            $scope.have_data = true;

        }, function (reason) {
            // showToast("bg-danger",reason.data.error);
        });
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
                initLoader(ui.item.product_id,ui.item);
                // console.log(ui.item.product_id,ui.item);
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

});

function setMainChart(data){
    var options = {
        grid: {
            show: false
        },
        series: [
        {
            name: 'Purchases',
            data: data[1]
        },
        {
            name: 'Sales',
            data: data[0]
        }],
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
                  return "$ "+value;
                }
            }
        },
    };
    var chartele = document.querySelector("#chart3");
    chartele.innerHTML = "";
    var chart3 = new ApexCharts(chartele, options);
    chart3.render();
}
</script>
@endsection
