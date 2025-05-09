@extends('layouts.picking')
@section('title', __('restaurant.orders'))
@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <div class="row">
        <div class="col-md-12 mt-4 mt-2 tab-menu d-flex justify-content-center">
            @include('restaurant.orders.order-picking-tabs')
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="container-fluid">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="to_be_picked" role="tabpanel"
                        aria-labelledby="pills-to_be_picked-tab">
                        <div id="productDetailDivContainer">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="row">
                                        <div style="padding-left: 40px;" class="col-md-12">
                                            <h4>
                                                @if ($orderProduct != null)
                                                    {{ $orderProduct->product_name }}
                                                @endif
                                            </h4>
                                        </div>
                                    </div>
                                    <div style="margin-left: 15px;" class="row">
                                        <div class="col-md-12">
                                            @if ($orderProduct != null)
                                                @if ($orderProduct->image != null)
                                                    <img src="{{ asset('/uploads') . $orderProduct->image }}"
                                                        class="img img-fluid" width="80%">
                                                @else
                                                    <img src="{{ asset('/uploads/stitic/data-not-found.png') }}"
                                                        class="img img-fluid" width="80%">
                                                @endif
                                            @else
                                                <h2 style="color:red;">All Items has been picked. Please finalize the
                                                    picking
                                                </h2>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <h5 class="text-success">
                                                Unit Price:<span>
                                                    @if ($orderProduct != null)
                                                        ${{ round($orderProduct->unit_price, 2) }}
                                                    @endif
                                                </span>
                                            </h5>
                                        </div>
                                        <div class="col-md-7">
                                            <h5>Total :<span>
                                                    @if ($orderProduct != null)
                                                        ${{ round($orderProduct->unit_price, 2) * round($orderProduct->quantity) }}
                                                    @endif
                                                </span></h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <h5 class="text-success">
                                                QTY:<span id="pickingQtyInputBox"
                                                    data-product="{{ $orderProduct ? $orderProduct->id : '' }}">
                                                    @if ($orderProduct != null)
                                                        {{ round($orderProduct->edit_quantity) != 0.0 ? round($orderProduct->edit_quantity) : round($orderProduct->quantity) }}
                                                        {{-- {{round($orderProduct->quantity)}} --}}
                                                    @endif
                                                </span>
                                            </h5>
                                        </div>
                                        <div class="col-md-7">
                                            <h5>QTY On Hand :<span id="pickingQtyonHand">
                                                    @if ($orderProduct != null)
                                                        {{ round($orderProduct->stock) }}
                                                    @endif
                                                </span></h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5>Barcode : @if ($orderProduct != null)
                                                    {{ $orderProduct->sku }}
                                                @endif
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5>Note : @if ($orderProduct != null)
                                                    {{ $orderProduct->sell_line_note }}
                                                @endif
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <p>Aisle : @if ($orderProduct != null)
                                                    {{ $orderProduct->aisle }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <p>Rack : @if ($orderProduct != null)
                                                    {{ $orderProduct->rack }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <p>Shelf : @if ($orderProduct != null)
                                                    {{ $orderProduct->shelf }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <p>Bin : @if ($orderProduct != null)
                                                    {{ $orderProduct->bin }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    @include('restaurant.orders.order-packing-product-options')
                                </div>
                            </div>
                        </div>

                        {{-- <div class="row" style="padding-left: 40px;">
                            <div class="col-md-12">
                                <div class="row text-left">
                                    <div class="col-md-4">
                                        <p>Order No:@if ($contacts != null)
                                                #{{ $contacts->invoice_no }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p>Order By :@if ($contacts != null)
                                                {{ $contacts->first_name }} {{ $contacts->last_name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row text-left">
                                    <div class="col-md-4">
                                        <p>Customer: @if ($contacts != null)
                                                {{ $contacts->customer_name }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p>Company: @if ($contacts != null)
                                                {{ $contacts->company_name }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-4">

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row text-left">

                                    <p> <b>Status:
                                            @if ($contacts->p_status == 'ask_for_payment_before_ship')
                                                Ask For Payment Before Shipping
                                            @elseif($contacts->p_status == 'ok_to_ship')
                                                Okay to Deliver/Ship (Payment Confirmed)
                                            @else
                                                Ask In The Office
                                            @endif
                                        </b>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row text-left">
                                    <p><b>Order Note :</b>
                                        @if ($contacts != null)
                                            {{ $contacts->additional_notes }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                        </div> --}}
                        <div class="row">
                            <div class="col-md-12 m-2">
                                <div class="container-fluid">
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-center" id="productDataTable">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" id="select-all-rows"></th>
                                                    <th style="width: 40%;">Item</th>
                                                    <th>Image</th>
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
                                            <tbody id="productTableBody">
                                                @if (!$orderdetails->isEmpty())
                                                    @foreach ($orderdetails as $key => $item)
                                                        <tr id="raw-{{ $key }}">
                                                            <td><input type="checkbox" class="row-select"
                                                                    value="{{ $item->id }}"></td>
                                                            <td>{{ $item->product_name }} [{{ $item->item_code }}]</td>
                                                            <td>
                                                                <img src="{{ asset('/uploads') . $item->image }}"
                                                                    class="img img-responsive" width="75px">
                                                            </td>
                                                            <td>{{ 'A:' . $item->aisle . ' R:' . $item->rack . ' S:' . $item->shelf . ' B:' . $item->bin }}
                                                            </td>
                                                            <td>{{ $item->sku }}</td>
                                                            <td>${{ round($item->unit_price, 2) }}</td>
                                                            <td style="background: yellow;font-weight: 900;">
                                                                <span id="producQty_{{ $item->id }}"
                                                                    data-product="{{ $item->id }}">

                                                                    {{ round($item->edit_quantity) != 0.0 ? round($item->edit_quantity) : round($item->quantity) }}
                                                                </span>
                                                            </td>
                                                            <td>${{ $item->unit_price * round($item->quantity) }}</td>
                                                            <td>{{ round($item->stock) }}</td>
                                                            <td>
                                                                <button onclick="editProductQty({{ $item->id }})"
                                                                    class="btn btn-sm btn-primary"><i
                                                                        class="fa fa-edit"></i> Edit</button>
                                                            </td>
                                                            <td>
                                                                @if ($orderProduct != null)
                                                                    <form
                                                                        action="{{ action('Restaurant\KitchenApiController@pickProduct', [$item->id]) }}">
                                                                        <input type="hidden" name="updatedPickedQty"
                                                                            value="">
                                                                        <input type="hidden" name="raw"
                                                                            value="{{ $key }}">
                                                                        <button type="submit" id="pickingItem"
                                                                            class="btn btn-sm pickedBtn form-control">Pick
                                                                        </button>
                                                                    </form>
                                                                @else
                                                                    <a href="#" type="button"
                                                                        class="btn btn-sm pickedBtn form-control">Pick</a>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <a data-stock="{{ round($item->stock) }}"
                                                                    href="{{ action('Restaurant\KitchenController@outOfStock', [$item->id, 'raw' => $key]) }}"
                                                                    type="button"
                                                                    class="btn btn-sm btn-primary editedBtnOne">Out Of
                                                                    Stock
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
                        <div style="padding-left: 50px;" class="row mt-1" id="pickedMissedDiv">
                            <div class="col-md-2"><button type="submit" class="btn btn-sm form-control pickedBtn"
                                    id="pick-check-selected"><i class="fa fa-check"></i>Pick</button></div>
                            <div class="col-md-5">
                                <p class="p-0 m-0"><b class="m-0 p-0">Total Picked Items:</b> {{ $pickedProductsCount }}
                                </p>
                            </div>
                            <div class="col-md-5">
                                <p class="p-0 m-0"><b class="m-0 p-0">Total Missed
                                        Items:</b> {{ $outOfStockProductsCount }}</p>
                            </div>
                        </div>
                        <div style="margin-top: 50px; margin-left: 30px;" class="row">
                            <div class="col-sm-7 col-md-7 m-1">
                                <div class="row">
                                    <div class="col-md-12 d-flex justify-content-between align-items-center flex-wrap">

                                        {{-- Save & Hold --}}
                                        @if ($orderProduct != null && $orderProduct->id != null)
                                            <a href="{{ action('Restaurant\KitchenController@saveAndHold', [$orderProduct->transaction_id]) }}"
                                                class="btn btn-primary mb-2">
                                                <i class="fa fa-pause"></i> Save & Hold
                                            </a>
                                        @else
                                            <a href="#" class="btn btn-primary mb-2">
                                                <i class="fa fa-pause"></i> Save & Hold
                                            </a>
                                        @endif

                                        {{-- Finalize --}}
                                        @if ($orderdetails->isEmpty() && $pickedProductsCount > 0)
                                            <form
                                                action="{{ action('Restaurant\KitchenController@finalizePickingOrder', [$transaction_id]) }}"
                                                class="mb-0">
                                                <input id="timingCount" type="hidden" name="timingCount"
                                                    value="">
                                                <button type="submit" class="btn btn-success mb-2">
                                                    <i class="fa fa-check"></i> Finalize
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-success mb-2">
                                                <i class="fa fa-check"></i> Finalize
                                            </button>
                                        @endif

                                        {{-- Clear --}}
                                        @if ($transaction_id != null)
                                            <a href="{{ action('Restaurant\KitchenController@cancelPikingOrder', [$transaction_id]) }}"
                                                class="btn btn-danger leave-page mb-2">
                                                <i class="fa fa-window-close"></i> Clear
                                            </a>
                                        @else
                                            <a href="/modules/orders" class="btn btn-danger leave-page mb-2">
                                                <i class="fa fa-window-close"></i> Clear
                                            </a>
                                        @endif

                                        {{-- Order Queue --}}
                                        @if ($orderProduct != null && $orderProduct->id != null)
                                            <a href="{{ action('Restaurant\KitchenController@orderQueue', [$orderProduct->transaction_id]) }}"
                                                class="btn btn-warning leave-page mb-2">
                                                <i class="fa fa-th"></i> Order Queue
                                            </a>
                                        @else
                                            <a href="/modules/orders" class="btn btn-warning leave-page mb-2">
                                                <i class="fa fa-th"></i> Order Queue
                                            </a>
                                        @endif
                                    </div>
                                </div>

                            </div>
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
                                                @if (!$pickedProducts->isEmpty())
                                                    @foreach ($pickedProducts as $item)
                                                        <tr id="table-row-{{ $item->id }}">
                                                            <td>{{ $item->product_name }}</td>
                                                            <td>
                                                                <img src="{{ asset('/uploads') . $item->image }}"
                                                                    class="img img-responsive" width="75px">
                                                            </td>
                                                            <td>{{ 'A:' . $item->aisle . ' R:' . $item->rack . ' S:' . $item->shelf . ' B:' . $item->bin }}
                                                            </td>
                                                            <td>{{ $item->item_code }}</td>
                                                            <td>{{ round($item->quantity) }}</td>
                                                            <td>{{ round($item->stock) }}</td>
                                                            <td>
                                                                @if ($item->picking_started_time)
                                                                    {{ @format_date($item->picking_started_time) }}
                                                                    {{ @format_time($item->picking_started_time) }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if ($item->picking_completed_time)
                                                                    {{ @format_date($item->picking_completed_time) }}
                                                                    {{ @format_time($item->picking_completed_time) }}
                                                            </td>
                                                    @endif
                                                    <td>
                                                        <button onclick="undoPicking({{ $item->id }})"
                                                            class="btn btn-sm btn-primary"><i class="fa fa-undo"></i>
                                                            Undo</button>
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
                                                @if (!$outOfStockProducts->isEmpty())
                                                    @foreach ($outOfStockProducts as $item)
                                                        <tr>
                                                            <td>{{ $item->product_name }}</td>
                                                            <td>
                                                                <img src="{{ asset('/uploads') . $item->image }}"
                                                                    class="img img-responsive" width="75px">
                                                            </td>
                                                            <td>{{ 'A:' . $item->aisle . ' R:' . $item->rack . ' S:' . $item->shelf . ' B:' . $item->bin }}
                                                            </td>
                                                            <td>{{ $item->item_code }}</td>
                                                            <td>{{ round($item->quantity) }}</td>
                                                            <td>{{ round($item->stock) }}</td>
                                                            <td>
                                                                <button onclick="undoOutOfStock({{ $item->id }})"
                                                                    class="btn btn-sm btn-primary"><i
                                                                        class="fa fa-undo"></i> Undo</button>
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
                                                @if (!$editedProducts->isEmpty())
                                                    @foreach ($editedProducts as $item)
                                                        <tr>
                                                            <td>{{ $item->product_name }}</td>
                                                            <td>
                                                                <img src="{{ asset('/uploads') . $item->image }}"
                                                                    class="img img-responsive" width="75px">
                                                            </td>
                                                            <td>{{ 'A:' . $item->aisle . ' R:' . $item->rack . ' S:' . $item->shelf . ' B:' . $item->bin }}
                                                            </td>
                                                            <td>{{ $item->item_code }}</td>
                                                            <td>{{ round($item->quantity) }}</td>
                                                            <td>{{ round($item->stock) }}</td>
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
                                                @if (!$incorrectLocationProducts->isEmpty())
                                                    @foreach ($incorrectLocationProducts as $item)
                                                        <tr>
                                                            <td>{{ $item->product_name }}</td>
                                                            <td>
                                                                <img src="{{ asset('/uploads') . $item->image }}"
                                                                    class="img img-responsive" width="75px">
                                                            </td>
                                                            <td>{{ 'A:' . $item->aisle . ' R:' . $item->rack . ' S:' . $item->shelf . ' B:' . $item->bin }}
                                                            </td>
                                                            <td>{{ $item->item_code }}</td>
                                                            <td>{{ round($item->quantity) }}</td>
                                                            <td>{{ round($item->stock) }}</td>
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

        #pick-check-selected {
            right: 0%;
            top: 94%;
            height: 35px;
            transition: all 0.2s ease-in 0s; //this is the key attribute
            z-index: 9999;
            cursor: pointer;
        }
    </style>
@endsection
@include('restaurant.orders.order-picking-js')
