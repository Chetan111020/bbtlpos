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
                            type="button" role="tab" aria-controls="not_there" aria-selected="false">Out of Stock
                        ({{$outOfStockProductsCount}})
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
                                        @else
                                        <h2 style="color:red;">All Items has been picked. Please finalize the picking</h2>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-5">
                                        <h5 class="text-success">
                                            Unit Price:<span>@if($orderProduct !=null)${{round($orderProduct->unit_price, 2)}}@endif</span>
                                        </h5>
                                    </div>
                                    <div class="col-md-7">
                                        <h5>Total :<span>@if($orderProduct !=null)${{round($orderProduct->unit_price, 2) * round($orderProduct->quantity)}}@endif</span></h5>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5">
                                        <h5 class="text-success">
                                            QTY:<span
                                                    id="pickingQtyInputBox" data-product="{{ $orderProduct ? $orderProduct->id : '' }}">
                                                    @if($orderProduct !=null)
                                                         {{ round($orderProduct->edit_quantity) != 0.00 ? round($orderProduct->edit_quantity) :  round($orderProduct->quantity)}}
                                                        {{-- {{round($orderProduct->quantity)}} --}}
                                                    @endif
                                                </span>
                                        </h5>
                                    </div>
                                    <div class="col-md-7">
                                        <h5>QTY On Hand :<span id="pickingQtyonHand">@if($orderProduct !=null){{round($orderProduct->stock)}}@endif</span></h5>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5>Barcode : @if($orderProduct !=null){{$orderProduct->sku}}@endif</h5>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5>Note : @if($orderProduct !=null){{$orderProduct->sell_line_note}}@endif</h5>
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
                                                <button type="submit" id="pickingItem" class="btn btn-lg pickedBtn form-control">Picked
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
                                        <button onclick="editPickingQty(@if($orderProduct !=null){{$orderProduct->id}}@endif)" type="button"
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
                            <div class="col-md-3">
                                <div class="row text-center">
                                    <div class="col-md-12">
                                        <p>Customer
                                            Name: @if($contacts !=null){{$contacts->customer_name}}@endif</p>
                                        <p>Company 
                                            Name: @if($contacts !=null){{$contacts->company_name}}@endif 
                                       </p>  
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="row text-center">
                                    <div class="col-md-12">
                                        <p>Order No:@if($contacts !=null)#{{$contacts->invoice_no}}@endif</p>
                                        <p> <b>Status:  
                                                @if( $contacts->p_status  ==  'ask_for_payment_before_ship')
                                                    Ask For Payment Before Shipping
                                                @elseif($contacts->p_status  == 'ok_to_ship')
                                                    Okay to Deliver/Ship (Payment Confirmed)
                                                @else
                                                    Ask In The Office
                                                @endif</b></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="row text-center">
                                    <div class="col-md-12">
                                        <p>Order  By :@if($contacts !=null){{$contacts->first_name}} {{$contacts->last_name}}@endif</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row text-center">
                                    <div class="col-md-12">
                                        <p><b>Order Note :</b>@if($contacts !=null){{ $contacts->additional_notes }} @endif</p>
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
                                                <th><input type="checkbox" id="select-all-rows">Pick</th>
                                                <th style="width: 40%;">Item Name</th>
                                                <th>Item Image</th>
                                                <th>Location</th>
                                                <th>Barcode</th>
                                                <th>Unit Price</th>
                                                <th style="background: yellow;">Order Qty</th>
                                                <th>Total</th>
                                                <th>Stock Qty</th>
                                                <th>Edit</th>
                                                <th>Pick</th>
                                                <th>OOS</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(!$orderdetails->isEmpty())
                                                @foreach($orderdetails as $key => $item)
                                                    <tr id="raw-{{ $key }}">
                                                        <td><input type="checkbox" class="row-select" value="{{ $item->id }}"></td>
                                                        <td>{{$item->product_name}} [{{$item->item_code}}]</td>
                                                        <td>
                                                            <img src="{{asset('/uploads').$item->image}}"
                                                                 class="img img-responsive" width="75px">
                                                        </td>
                                                        <td>{{'A:'.$item->aisle.' R:'.$item->rack.' S:'.$item->shelf.' B:'.$item->bin}}</td>
                                                        <td>{{$item->sku}}</td>
                                                        <td>${{round($item->unit_price, 2)}}</td>
                                                        <td style="background: yellow;font-weight: 900;">
                                                            <span id="producQty_{{$item->id}}" data-product="{{ $item->id }}">
                                                                
                                                                {{ round($item->edit_quantity) != 0.00 ? round($item->edit_quantity) :  round($item->quantity)}}
                                                            </span>
                                                        </td>
                                                        <td>${{$item->unit_price * round($item->quantity)}}</td>
                                                        <td>{{round($item->stock)}}</td>
                                                        <td>
                                                            <button onclick="editProductQty({{$item->id}})" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i> Edit</button>
                                                        </td>
                                                        <td>
                                                        @if($orderProduct !=null)
                                                            <form action="{{action('Restaurant\KitchenController@pickProduct', [$item->id])}}">
                                                                <input type="hidden" name="updatedPickedQty" value="">
                                                                <input type="hidden" name="raw" value="{{ $key }}">
                                                                <button type="submit" id="pickingItem" class="btn btn-sm pickedBtn form-control">Pick
                                                                </button>
                                                            </form>
                                                        @else
                                                            <a href="#"
                                                            type="button" class="btn btn-sm pickedBtn form-control">Pick</a>
                                                        @endif
                                                        </td>
                                                        <td>
                                                            <a data-stock="{{round($item->stock)}}" href="{{action('Restaurant\KitchenController@outOfStock', [$item->id, 'raw' => $key])}}"
                                                                type="button" class="btn btn-sm btn-primary editedBtnOne">Out Of Stock
                                                            </a>
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
                            <div class="col-md-1"><button type="submit" class="btn btn-sm form-control pickedBtn" id="pick-check-selected"><i class="fa fa-check"></i>Pick</button></div>
                            <div class="col-md-5">
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
                                                    @if($orderdetails->isEmpty() && $pickedProductsCount > 0)
                                                        <form action="{{action('Restaurant\KitchenController@finalizePickingOrder', [$transaction_id])}}">
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
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        @if($transaction_id != null)
                                                <a style="margin-right: 15px;" href="{{action('Restaurant\KitchenController@cancelPikingOrder', [$transaction_id])}}"
                                                   class="btn btn-danger leave-page">
                                                    <i class="fa fa-window-close"></i> Clear
                                                </a>
                                        @else
                                                <a style="margin-right: 15px;" href="/modules/orders"
                                                   class="btn btn-danger leave-page">
                                                    <i class="fa fa-window-close"></i> Clear
                                                </a>
                                        @endif
                                        @if($orderProduct != null)
                                            @if($orderProduct->id != null)
                                                <a href="{{action('Restaurant\KitchenController@orderQueue', [$orderProduct->transaction_id])}}"
                                                   class="btn btn btn-warning leave-page">
                                                    <i class="fa fa-th"></i> Order Queue
                                                </a>
                                            @else
                                                <a href="/modules/orders"
                                                   class="btn btn btn-warning leave-page">
                                                    <i class="fa fa-th"></i> Order Queue
                                                </a>
                                            @endif
                                        @else
                                                <a href="/modules/orders"
                                                   class="btn btn btn-warning leave-page">
                                                    <i class="fa fa-th"></i> Order Queue
                                                </a>
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
                                                <th>Pick Started</th>
                                                <th>Pick Completed</th>
                                                <th>Action</th>
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
                                                        <td>{{round($item->quantity)}}</td>
                                                        <td>{{round($item->stock)}}</td>
                                                        <td>
                                                            @if($item->picking_started_time)
                                                                {{ @format_date($item->picking_started_time) }} {{ @format_time($item->picking_started_time) }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($item->picking_completed_time)    
                                                                {{ @format_date($item->picking_completed_time) }} {{ @format_time($item->picking_completed_time) }}</td>
                                                            @endif
                                                        <td>
                                                            <button onclick="undoPicking({{$item->id}})" class="btn btn-sm btn-primary"><i class="fa fa-undo"></i> Undo</button>
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
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(!$outOfStockProducts->isEmpty())
                                                @foreach($outOfStockProducts as $item)
                                                    <tr>
                                                        <td>{{$item->product_name}}</td>
                                                        <td>
                                                            <img src="{{asset('/uploads').$item->image}}"
                                                                 class="img img-responsive" width="75px">
                                                        </td>
                                                        <td>{{'A:'.$item->aisle.' R:'.$item->rack.' S:'.$item->shelf.' B:'.$item->bin}}</td>
                                                        <td>{{$item->item_code}}</td>
                                                        <td>{{round($item->quantity)}}</td>
                                                        <td>{{round($item->stock)}}</td>
                                                        <td>
                                                            <button onclick="undoOutOfStock({{$item->id}})" class="btn btn-sm btn-primary"><i class="fa fa-undo"></i> Undo</button>
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
                                                        <td>{{round($item->quantity)}}</td>
                                                        <td>{{round($item->stock)}}</td>
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
                                                        <td>{{round($item->quantity)}}</td>
                                                        <td>{{round($item->stock)}}</td>
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
    <style>
        .editedBtnOne {
            background-color: #F35C5C;
            border: 1px solid lightgray;
        }
        #pick-check-selected
        {
            right: 0%;
            top: 94%;
            height: 35px;
            transition: all 0.2s ease-in 0s;//this is the key attribute
            z-index: 9999;
            cursor: pointer;
        }
    </style>
@endsection
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script>
        function editPickingQty(id) {
            let previousQty = document.getElementById('pickingQtyInputBox').innerText;
            let qtyBox = '<input id="updatedPickedQty" data-content="'+id+'" onkeypress="getProductQty(event, id)" onInput="pickingUpdatedQty()" style="width:60px;" type="text" value="' + previousQty + '" name="qty">';
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
        $(document).on("click", "#pickingItem", function(e){
            var audio = $('#success-audio')[0];
            if (audio !== undefined) {
                audio.play();
            }
        });
        $(document).on("click", ".editedBtn", function(e){
            var qty = parseInt($("#pickingQtyInputBox").html());
            var qty_on_hand = parseInt($("#pickingQtyonHand").html());
            //if(parseInt(qty) < parseInt(qty_on_hand)){
                var r = confirm("Are you sure this product is out of Stock? We have "+ qty_on_hand +" on hand");
                if (r == false) {
                    e.preventDefault();
                }
            // }else{
            //     e.preventDefault();
            // }
        });
        
        $(document).on("click", ".editedBtnOne", function(e){
            var qty = parseInt($("#pickingQtyInputBox").html());
            var qty_on_hand = parseInt($(this).attr('data-stock'));
                var r = confirm("Are you sure this product is out of Stock? We have "+ qty_on_hand +" on hand");
                if (r == false) {
                    e.preventDefault();
                }
        });

        $(document).on("click", ".leave-page", function(e){
            var r = confirm("Are you sure you want to leave this page");
            if (r == false) {
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
            let p_qty_box = document.getElementById('producQty_'+id);
            let pre_qty = p_qty_box.innerText;
            p_qty_box.innerHTML = '<input data-content="'+id+'" onkeypress="getProductQty(event, id)" style="width:60px;" type="text" value="'+pre_qty+'" name="produc_qty">';
        }

        function getProductQty(e, id){
            let key=e.keyCode || e.which;
            let p_qty =  e.target.value;
            let p_id = e.target.getAttribute('data-content');
            if (key===13){
                $.ajax({
                    // url:'{{action('Restaurant\KitchenController@updateProductQty')}}',
                    url:'{{action('Restaurant\KitchenController@editProductQty')}}',
                    type:'POST',
                    data:{
                        _token:'{{csrf_token()}}',
                        p_id:p_id,
                        p_qty:p_qty
                    },
                    success:function (data) {
                        console.log('data:',  data)
                        location.reload();
                    }
                })
            }
        }
        
        $(document).on("blur", "#updatedPickedQty", function(e){
            let p_qty =  $(this).val();
            let p_id = $(this).attr('data-content');
            $.ajax({
                    // url:'{{action('Restaurant\KitchenController@updateProductQty')}}',
                    url:'{{action('Restaurant\KitchenController@editProductQty')}}',
                    type:'POST',
                    data:{
                        _token:'{{csrf_token()}}',
                        p_id:p_id,
                        p_qty:p_qty
                    },
                    success:function (data) {
                        console.log('data:', data);
                        location.reload();
                    }
                })
        });

        function undoPicking(id){
            $.ajax({
                url:"/modules/kitchen/picking/undo/"+ id,
                type:'GET',
                data: null,
                success:function () {
                    location.reload();
                }
            })
        }

        function undoOutOfStock(id){
            $.ajax({
                url:"/modules/kitchen/outofstock/undo/"+ id,
                type:'GET',
                data: null,
                success:function () {
                    location.reload();
                }
            })
        }
        
        $(document).ready(function () {
           @if(Session::has('raw'))
               window.location = "#raw-{{ Session::get('raw') }}";
           @endif
        });


        $(document).on('click', '#pick-check-selected', function (e) {
            e.preventDefault();
            var selected_rows = getSelectedRows();

            if (selected_rows.length > 0) {
                var result = confirm("Are you sure you want to pick selected products?");
                if (result) {
                    console.log('selected_rows:', selected_rows)
                    // var formData = {
                    //     selected_rows: selected_rows,
                    // };
                    $.ajax({
                        method: 'POST',
                        url:"{{ url('/modules/kitchen/selected/pick/product') }}",
                        data:{
                            selected_rows: selected_rows,
                            _token:'{{csrf_token()}}'
                        },
                        success: function(success) {
                                console.log('success: ', success)
                                if(success == 1)
                                { 
                                    var audio = $('#success-audio')[0];
                                    if (audio !== undefined) {
                                        audio.play();
                                    }
                                    location.reload();
                                }else{
                                    var audio = $('#error-audio')[0];
                                    if (audio !== undefined) {
                                        audio.play();
                                    }
                                    alert('@lang("lang_v1.no_row_selected")');
                                }
                        },error: function(error)
                        {
                            console.log('error:', error)  
                        }
                    });
                }                
            } else {
                $('input.row-select').val('');
                var audio = $('#error-audio')[0];
                if (audio !== undefined) {
                    audio.play();
                }
                alert('@lang("lang_v1.no_row_selected")');
                // swal('@lang("lang_v1.no_row_selected")');
            }
        })

        function getSelectedRows() {
            var selected_rows = [];
            var i = 0;
            $('.row-select:checked').each(function () {
                selected_rows[i++] = $(this).val();
            });
            return selected_rows;
        }

        $(document).on('click', '#select-all-rows', function(e) {
            if (this.checked) {
                $(this)
                    .closest('table')
                    .find('tbody')
                    .find('input.row-select')
                    .each(function() {
                        if (!this.checked) {
                            $(this)
                                .prop('checked', true)
                                .change();
                        }
                    });
            } else {
                $(this)
                    .closest('table')
                    .find('tbody')
                    .find('input.row-select')
                    .each(function() {
                        if (this.checked) {
                            $(this)
                                .prop('checked', false)
                                .change();
                        }
                    });
            }
        });
    </script>
@endsection