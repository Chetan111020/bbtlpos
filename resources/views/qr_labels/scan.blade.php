@extends('layouts.app')
@section('title',"QR Scan")

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
    <div class="row bg-white" style="display:flex;">
        <div class="col-md-6" style="padding:0 15px 15px 30px;">
            <h2>Product Location Update</h2>
            <br />
            <div style="width: 100%">
                <div class="form-group">
                    <label>Search Product</label>
                    {!! Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('Enter Product Name')]) !!}

                </div>
            </div>
        </div>
        <div class="col-md-6" style="padding:15px 30px 15px 15px;background:#f3fff5;">
            <div style="display:flex;justify-content:end;">
                <button type="button" class="btn dash_ele_color5 myshadow" data-toggle="modal" data-target="#myModal">Scan Location QR</button>
            </div>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-3">
                    <label>Aisle</label>
                    <input type="number" class="form-control" maxlength="5" ng-model="aisle" />
                </div>
                <div class="col-sm-3">
                    <label>Rack</label>
                    <input type="number" class="form-control" maxlength="5" ng-model="rack" />
                </div>
                <div class="col-sm-3">
                    <label>Shelf</label>
                    <input type="number" class="form-control" maxlength="5" ng-model="shelf" />
                </div>
                <div class="col-sm-3">
                    <label>Bin</label>
                    <input type="number" class="form-control" maxlength="5" ng-model="bin" />
                </div>
            </div>
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

        {{-- <button class="btn btn-primary" style="margin-top:15px;margin-left:15px;" ng-click="submitData()">Save</button> --}}

        <div class="bg-white myshadow" style="margin: 15px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product</th>
                        <th>A</th>
                        <th>R</th>
                        <th>S</th>
                        <th>B</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="p in products">
                        <td><% p.sku %></td>
                        <td><% p.name %></td>
                        <td><% p.a %></td>
                        <td><% p.r %></td>
                        <td><% p.s %></td>
                        <td><% p.b %></td>
                        <td style="padding:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" ng-click="product_pop($index)" style="height: 23px;cursor: pointer;width: 100%;margin-top: 7px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div id="reader" width="600px"></div>
                    <div style="display:flex;justify-content:center;padding-top:15px;">
                        <button class="btn btn-primary" ng-click="change_cam()">Change Camera</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@stop
@section('javascript')

<script src="{{ asset('/assets/libs/angularjs/angular.min.js') }}"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

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
    $scope.products = [];
    $scope.aisle = 0;
    $scope.rack = 0;
    $scope.shelf = 0;
    $scope.bin = 0;
    $scope.last_submit_item = [];

    //get init
    var initLoader = function(id,item){

        $scope.submitDataSingle(item);

        // $scope.products.push(item);
        // $scope.have_data = true;
        // $scope.$apply();
    }

    $scope.product_pop = function(index){
        $scope.products.splice(index, 1);
        if($scope.products.length == 0){
            $scope.have_data = false;
        }
    }

    // $scope.submitData = function(){
    //     var vids = [];
    //     $scope.products.forEach((currentElement) => {
    //         vids.push(currentElement.variation_id);
    //     });
    //     var data = {
    //         products: vids,
    //         a: $scope.aisle,
    //         r: $scope.rack,
    //         s: $scope.shelf,
    //         b: $scope.bin
    //     };
    //     $scope.have_data = false;
    //     $http.post('{{route("qr.update")}}', data).then(function (response) {
    //         if(response.data.success == 1){
    //             toastr.success('Product location updated');
    //             setTimeout(() => {
    //                 location.reload();
    //             }, 2000);
    //         }
    //         else{
    //             toastr.success('Something went wrong');
    //         }
    //         $scope.have_data = true;
    //     }, function (reason) {
    //         toastr.warning(reason.data.error);
    //         $scope.have_data = true;
    //     });
    // }


    $scope.submitDataSingle = function(item){
        var data = {
            products: [item.variation_id],
            a: $scope.aisle,
            r: $scope.rack,
            s: $scope.shelf,
            b: $scope.bin
        };
        $scope.have_data = false;
        $scope.last_submit_item = item;
        $http.post('{{route("qr.update")}}', data).then(function (response) {
            if(response.data.success == 1){
                toastr.success( $scope.last_submit_item.name + ' location updated');

                $scope.last_submit_item.a = $scope.aisle;
                $scope.last_submit_item.r = $scope.rack;
                $scope.last_submit_item.s = $scope.shelf;
                $scope.last_submit_item.b = $scope.bin;

                $scope.products.push($scope.last_submit_item);
                $scope.have_data = true;
                $scope.$apply();
            }
            else{
                toastr.success('Something went wrong');
            }
            $scope.have_data = $scope.products.length > 0;
        }, function (reason) {
            toastr.warning(reason.data.error);
            $scope.have_data = true;
        });
    }

    const html5QrCode = new Html5Qrcode("reader");

    var onScanSuccess = function(decodedText, decodedResult) {
        var arsb = decodedText.split('|');
        var location_arsb = {};
        arsb.forEach(function(currentElement){
            var location_arr = currentElement.split(':');
            if(location_arr[0] != undefined){
                switch (location_arr[0]) {
                    case 'A':
                        location_arsb.a = location_arr[1];
                        break;
                    case 'R':
                        location_arsb.r = location_arr[1];
                        break;
                    case 'S':
                        location_arsb.s = location_arr[1];
                        break;
                    case 'B':
                        location_arsb.b = location_arr[1];
                        break;
                    default:
                        break;
                }
            }
        });
        if(location_arsb.a == undefined && location_arsb.r == undefined && location_arsb.s == undefined && location_arsb.b == undefined){
            toastr.warning('Invalid Location in QR Code');
        }
        else{
            $scope.aisle = parseInt(location_arsb.a);
            $scope.rack = parseInt(location_arsb.r);
            $scope.shelf = parseInt(location_arsb.s);
            $scope.bin = parseInt(location_arsb.b);
            $scope.$apply();
            toastr.success('QR scanned successfully');
            $('#myModal').modal('hide');
        }
    }

    $scope.cam_index = 1;
    $scope.change_cam = function(){
        $scope.cam_index++;
        $('#myModal').modal('hide');
        setTimeout(() => {
            $('#myModal').modal('show');
        }, 2000);
    }

    $('#myModal').on('shown.bs.modal', function () {
        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                var cam_index = $scope.cam_index % devices.length;
                var cameraId = devices[cam_index].id;
                html5QrCode.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: { width: 400, height: 400 }
                    },
                    onScanSuccess,
                    (errorMessage) => {
                    }
                )
                .catch((err) => {
                    toastr.warning('Something went wrong');
                });
            }
        }).catch(err => {
            toastr.warning('Unable to start the camera');
        });
    });

    $('#myModal').on('hidden.bs.modal', function (e) {
        html5QrCode.stop().then((ignore) => {
            // QR Code scanning is stopped.
        }).catch((err) => {
            // Stop failed, handle it.
        });
    })

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
                if (ui.content.length == 1) {
                    initLoader(ui.content[0].product_id,ui.content[0]);
                    $(this).autocomplete('close');
                    $('input#search_product').val(null);
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
</script>
@endsection
