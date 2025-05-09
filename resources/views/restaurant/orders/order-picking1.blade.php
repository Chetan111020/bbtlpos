@extends('layouts.picking')
@section('title', __( 'restaurant.orders' ))
@section('content')
    <div class="row">
        <div class="col-md-8 tab-menu">
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item toBePickedTab" role="presentation">
                    <button class="nav-link active" id="pills-to_be_picked-tab" data-bs-toggle="pill"
                            data-bs-target="#to_be_picked" type="button" role="tab" aria-controls="to_be_picked"
                            aria-selected="true">To be picked
                    </button>
                </li>
                <li class="nav-item pickedTab" role="presentation">
                    <button class="nav-link" id="pills-picked-tab" data-bs-toggle="pill" data-bs-target="#picked"
                            type="button" role="tab" aria-controls="picked" aria-selected="false">Picked
                        ({{$pickedProductsCount}})
                    </button>
                </li>
                <li class="nav-item notThereTab" role="presentation">
                    <button class="nav-link" id="pills-not_there-tab" data-bs-toggle="pill" data-bs-target="#not_there"
                            type="button" role="tab" aria-controls="not_there" aria-selected="false">Not There
                        ({{$incorrectLocationProductsCount}})
                    </button>
                </li>
                <li class="nav-item editedTab" role="presentation">
                    <button class="nav-link" id="pills-edited-tab" data-bs-toggle="pill" data-bs-target="#edited"
                            type="button" role="tab" aria-controls="edited" aria-selected="false">Edited
                        ({{$editedProductsCount}})
                    </button>
                </li>
                <li class="nav-item locationIncorrectTab" role="presentation">
                    <button class="nav-link" id="pills-location_incorrect-tab" data-bs-toggle="pill"
                            data-bs-target="#location_incorrect" type="button" role="tab"
                            aria-controls="location_incorrect"
                            aria-selected="false">Location Incorrect ({{$incorrectLocationProductsCount}})
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="container-fluid">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="to_be_picked" role="tabpanel"
                         aria-labelledby="pills-to_be_picked-tab">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="row">
                                    <div style="padding-left: 40px;" class="col-md-12">
                                        <h4>@if($orderProduct !=null){{$orderProduct->product_name}}@endif</h4>
                                    </div>
                                </div>
                                <div style="margin-left: 15px;" class="row">
                                    <div class="col-md-12">
                                        @if($orderProduct != null)
                                            @if($orderProduct->image != null)
                                                <img src="{{asset('/uploads').$orderProduct->image}}"
                                                     class="img img-fluid" width="80%">
                                            @else
                                                <img src="https://cdn.iconscout.com/icon/free/png-512/data-not-found-1965034-1662569.png"
                                                     class="img img-fluid" width="80%">
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-5">
                                        <h5 class="text-success">
                                            QTY:<span
                                                    id="pickingQtyInputBox">@if($orderProduct !=null){{$orderProduct->quantity}}@endif</span>
                                        </h5>
                                    </div>
                                    <div class="col-md-7">
                                        <h5>QTY On Hand :<span id="pickingQtyonHand">@if($orderProduct !=null){{$orderProduct->stock}}@endif</span></h5>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5>Barcode : @if($orderProduct !=null){{$orderProduct->sku}}@endif</h5>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <p>Aisle : @if($orderProduct !=null){{$orderProduct->aisle}}@endif</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p>Rack : @if($orderProduct !=null){{$orderProduct->rack}}@endif</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p>Shelf : @if($orderProduct !=null){{$orderProduct->shelf}}@endif</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p>Bin : @if($orderProduct !=null){{$orderProduct->bin}}@endif</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-12 p-3">
                                        @if($orderProduct !=null)
                                            <form id="picked_add_form" action="{{action('Restaurant\KitchenController@pickProduct', [$orderProduct->id])}}">
                                                <input id="pickedQty" type="hidden" name="updatedPickedQty"
                                                       value="">
                                                <button type="submit" class="btn btn-lg pickedBtn form-control">Picked
                                                </button>
                                            </form>
                                        @else
                                            <a href="#"
                                               type="button" class="btn btn-lg pickedBtn form-control">Picked</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 p-3">
                                        @if($orderProduct !=null)
                                            <a href="{{action('Restaurant\KitchenController@outOfStock', [$orderProduct->id])}}"
                                               type="button" class="btn btn-lg editedBtn form-control">Out Of Stock
                                            </a>
                                        @else
                                            <a href="#"
                                               type="button" class="btn btn-lg editedBtn form-control">Out Of Stock
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 p-3">
                                        <button onclick="editPickingQty()" type="button"
                                                class="btn btn-lg notThereBtn form-control">Edit
                                            Quantity
                                        </button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 p-3">
                                        @if($orderProduct !=null)
                                            <a href="{{action('Restaurant\KitchenController@incorrectLocation', [$orderProduct->id])}}"
                                               type="button" class="btn btn-lg locationIncorrectBtn form-control">
                                                Incorrect Location
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-lg locationIncorrectBtn form-control">
                                                Incorrect Location
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="row text-center">
                                    <div class="col-md-12">
                                        <p>Customer
                                            Name: @if($orderProduct !=null){{$orderProduct->customer_name}}@endif</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row text-center">
                                    <div class="col-md-12">
                                        <p>Order No:@if($orderProduct !=null)#{{$orderProduct->invoice_no}}@endif</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row text-center">
                                    <div class="col-md-12">
                                        <p>Sales Rep :@if($orderProduct !=null){{$orderProduct->sales_rep}}@endif</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="container-fluid">
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-center" id="productDataTable">
                                            <thead>
                                            <tr>
                                                <th style="width: 40%;">Item Name</th>
                                                <th>Item Image</th>
                                                <th>Location</th>
                                                <th>Barcode</th>
                                                <th>Order Qty</th>
                                                <th>Stock Qty</th>
                                                <th>Edit</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(!$orderdetails->isEmpty())
                                                @foreach($orderdetails as $item)
                                                    <tr>
                                                        <td>{{$item->product_name}}</td>
                                                        <td>
                                                            <img src="{{asset('/uploads').$item->image}}"
                                                                 class="img img-responsive" width="75px">
                                                        </td>
                                                        <td>{{'A:'.$item->aisle.' R:'.$item->rack.' S:'.$item->shelf.' B:'.$item->bin}}</td>
                                                        <td>{{$item->item_code}}</td>
                                                        <td>
                                                            <span id="producQty_{{$item->id}}">
                                                                {{$item->quantity}}
                                                            </span>
                                                        </td>
                                                        <td>{{$item->stock}}</td>
                                                        <td>
                                                            <button onclick="editProductQty({{$item->id}})" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i> Edit</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="padding-left: 50px;" class="row">
                            <div class="col-md-6">
                                <p class="p-0 m-0"><b class="m-0 p-0">Total Picked Items:</b> {{$pickedProductsCount}}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="p-0 m-0"><b class="m-0 p-0">Total Missed
                                        Items:</b> {{$outOfStockProductsCount}}</p>
                            </div>
                        </div>
                        <div style="margin-top: 50px;" class="row">
                            <div class="col-sm-7">
                                <div class="row">
                                    <div style="padding-left: 50px;" class="col-sm-6">
                                        <div class="row p-0 m-0">
                                            <div class="col-md-6 p-0 m-0">
                                                @if($orderProduct != null)
                                                    @if($orderProduct->id != null)
                                                        <a href="{{action('Restaurant\KitchenController@saveAndHold', [$orderProduct->transaction_id])}}"
                                                           class="btn btn-primary">
                                                            <i class="fa fa-pause"></i> Save & Hold
                                                        </a>
                                                    @else
                                                        <a href="#"
                                                           class="btn btn-primary">
                                                            <i class="fa fa-pause"></i> Save & Hold
                                                        </a>
                                                    @endif
                                                @else
                                                    <a href="#"
                                                        class="btn btn-primary">
                                                        <i class="fa fa-pause"></i> Save & Hold
                                                    </a>
                                                @endif
                                            </div>
                                            <div class="col-md-6 p-0 m-0">
                                                @if($orderProduct != null)
                                                    @if($orderProduct->id != null)
                                                        <form action="{{action('Restaurant\KitchenController@finalizePickingOrder', [$orderProduct->transaction_id])}}">
                                                            <input id="timingCount" type="hidden" name="timingCount"
                                                                   value="">
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="fa fa-check"></i> Finalize
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button class="btn btn-success">
                                                            <i class="fa fa-check"></i> Finalize
                                                        </button>
                                                    @endif
                                                @else
                                                    <button class="btn btn-success">
                                                        <i class="fa fa-check"></i> Finalize
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        @if($orderProduct != null)
                                            @if($orderProduct->id != null)
                                                <a style="margin-right: 15px;" href="{{action('Restaurant\KitchenController@cancelPikingOrder', [$orderProduct->transaction_id])}}"
                                                   class="btn btn-danger">
                                                    <i class="fa fa-window-close"></i> Cancel
                                                </a>
                                            @else
                                                <button style="margin-right: 15px;" class="btn btn-danger">
                                                    <i class="fa fa-window-close"></i> Cancel
                                                </button>
                                            @endif
                                        @else
                                            <button style="margin-right: 15px;" class="btn btn-danger">
                                                <i class="fa fa-window-close"></i> Cancel
                                            </button>
                                        @endif
                                        @if($orderProduct != null)
                                            @if($orderProduct->id != null)
                                                <a href="{{action('Restaurant\KitchenController@orderQueue', [$orderProduct->transaction_id])}}"
                                                   class="btn btn-primary">
                                                    <i class="fa fa-pause"></i> Order Queue
                                                </a>
                                            @else
                                                <button class="btn btn-primary">
                                                    <i class="fa fa-pause"></i> Order Queue
                                                </button>
                                            @endif
                                        @else
                                            <button class="btn btn-primary">
                                                <i class="fa fa-pause"></i> Order Queue
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-5"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <p style="color: #17a2b8 !important;">posbrainbean - V3.7 | Copyright © 2021 All rights
                                    reserved.</p>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="picked" role="tabpanel" aria-labelledby="pills-picked-tab">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h5>Picked Product List</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="container-fluid">
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-center" id="productDataTable">
                                            <thead>
                                            <tr>
                                                <th style="width: 40%;">Item Name</th>
                                                <th>Item Image</th>
                                                <th>Location</th>
                                                <th>Barcode</th>
                                                <th>Invoice Qty</th>
                                                <th>Stock Qty</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(!$pickedProducts->isEmpty())
                                                @foreach($pickedProducts as $item)
                                                    <tr>
                                                        <td>{{$item->product_name}}</td>
                                                        <td>
                                                            <img src="{{asset('/uploads').$item->image}}"
                                                                 class="img img-responsive" width="75px">
                                                        </td>
                                                        <td>{{'A:'.$item->aisle.' R:'.$item->rack.' S:'.$item->shelf.' B:'.$item->bin}}</td>
                                                        <td>{{$item->item_code}}</td>
                                                        <td>{{$item->quantity}}</td>
                                                        <td>{{$item->stock}}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="not_there" role="tabpanel" aria-labelledby="pills-not_there-tab">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h5>Not There Product List</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="container-fluid">
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-center" id="productDataTable">
                                            <thead>
                                            <tr>
                                                <th style="width: 40%;">Item Name</th>
                                                <th>Item Image</th>
                                                <th>Location</th>
                                                <th>Barcode</th>
                                                <th>Order Qty</th>
                                                <th>Stock Qty</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(!$incorrectLocationProducts->isEmpty())
                                                @foreach($incorrectLocationProducts as $item)
                                                    <tr>
                                                        <td>{{$item->product_name}}</td>
                                                        <td>
                                                            <img src="{{asset('/uploads').$item->image}}"
                                                                 class="img img-responsive" width="75px">
                                                        </td>
                                                        <td>{{'A:'.$item->aisle.' R:'.$item->rack.' S:'.$item->shelf.' B:'.$item->bin}}</td>
                                                        <td>{{$item->item_code}}</td>
                                                        <td>{{$item->quantity}}</td>
                                                        <td>{{$item->stock}}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="edited" role="tabpanel" aria-labelledby="pills-edited-tab">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h5>Edited Product List</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="container-fluid">
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-center" id="productDataTable">
                                            <thead>
                                            <tr>
                                                <th style="width: 40%;">Item Name</th>
                                                <th>Item Image</th>
                                                <th>Location</th>
                                                <th>Barcode</th>
                                                <th>Invoice Qty</th>
                                                <th>Stock Qty</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(!$editedProducts->isEmpty())
                                                @foreach($editedProducts as $item)
                                                    <tr>
                                                        <td>{{$item->product_name}}</td>
                                                        <td>
                                                            <img src="{{asset('/uploads').$item->image}}"
                                                                 class="img img-responsive" width="75px">
                                                        </td>
                                                        <td>{{'A:'.$item->aisle.' R:'.$item->rack.' S:'.$item->shelf.' B:'.$item->bin}}</td>
                                                        <td>{{$item->item_code}}</td>
                                                        <td>{{$item->quantity}}</td>
                                                        <td>{{$item->stock}}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="location_incorrect" role="tabpanel"
                         aria-labelledby="pills-location_incorrect-tab">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h5>Incorrect Location Product List</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="container-fluid">
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-center" id="productDataTable">
                                            <thead>
                                            <tr>
                                                <th style="width: 40%;">Item Name</th>
                                                <th>Item Image</th>
                                                <th>Location</th>
                                                <th>Barcode</th>
                                                <th>Order Qty</th>
                                                <th>Stock Qty</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(!$incorrectLocationProducts->isEmpty())
                                                @foreach($incorrectLocationProducts as $item)
                                                    <tr>
                                                        <td>{{$item->product_name}}</td>
                                                        <td>
                                                            <img src="{{asset('/uploads').$item->image}}"
                                                                 class="img img-responsive" width="75px">
                                                        </td>
                                                        <td>{{'A:'.$item->aisle.' R:'.$item->rack.' S:'.$item->shelf.' B:'.$item->bin}}</td>
                                                        <td>{{$item->item_code}}</td>
                                                        <td>{{$item->quantity}}</td>
                                                        <td>{{$item->stock}}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <h5 class="timer">Order Time : <span id="minutes">00</span>:<span id="seconds">00</span></h5>
@endsection
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script>
        function editPickingQty() {
            let previousQty = document.getElementById('pickingQtyInputBox').innerText;
            let qtyBox = '<input id="updatedPickedQty" onInput="pickingUpdatedQty()" style="width:60px;" type="text" value="' + previousQty + '" name="qty">';
            document.getElementById('pickingQtyInputBox').innerHTML = qtyBox;
        }

        $(document).on("click", "#pickedQty", function(e){
            e.preventDefault();
            var qty = parseInt($("#pickingQtyInputBox").html());
            var qty_on_hand = parseInt($("#pickingQtyonHand").html());
            if(qty > qty_on_hand){
                alert("qty is higher than qty on hand.");
            }else{
                $('form#picked_add_form').submit();
            }

        });
        $(document).on("click", ".editedBtn", function(e){
            var qty = parseInt($("#pickingQtyInputBox").html());
            var qty_on_hand = parseInt($("#pickingQtyonHand").html());
            if(parseInt(qty) < parseInt(qty_on_hand)){
                var r = confirm("are you sure to make it out of stock ?");
                if (r == false) {
                    e.preventDefault();
                }
            }else{
                e.preventDefault();
            }
        });

        function pickingUpdatedQty() {
            let updatedPickedQty = document.getElementById("updatedPickedQty");
            let newQty = updatedPickedQty.value;
            let pickedQty = document.getElementById("pickedQty");
            pickedQty.value = newQty;
        }

        function editProductQty(id) {
            let p_qty_box = document.getElementById('producQty_'+id) ;
            let pre_qty = p_qty_box.innerText;
            p_qty_box.innerHTML = '<input data-content="'+id+'" onkeypress="getProductQty(event, id)" style="width:60px;" type="text" value="'+pre_qty+'" name="produc_qty">';
        }

        function getProductQty(e, id){
            let key=e.keyCode || e.which;
            let p_qty =  e.target.value;
            let p_id = e.target.getAttribute('data-content');
            if (key===13){
                $.ajax({
                    url:'{{action('Restaurant\KitchenController@updateProductQty')}}',
                    type:'POST',
                    data:{
                        _token:'{{csrf_token()}}',
                        p_id:p_id,
                        p_qty:p_qty
                    },
                    success:function () {
                        location.reload();
                    }
                })
            }
        }

    </script>
@endsection