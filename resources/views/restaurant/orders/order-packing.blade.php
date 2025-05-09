@extends('layouts.packing')
@section('title', __( 'restaurant.orders' ))
@section('content')
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="container">
                <ul class="nav nav-pills" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active packing" id="pills-packed-tab" data-toggle="pill" href="#packed"
                                role="tab"
                                aria-controls="packed" aria-selected="true">Picked Item's
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link picked" id="pills-picked-tab" data-toggle="pill" href="#picked"
                                role="tab"
                                aria-controls="picked" aria-selected="false">Packed ({{$packedProductsCount}})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link invoiced" id="pills-invoiced-tab" data-toggle="pill" href="#invoiced"
                                role="tab"
                                aria-controls="invoiced" aria-selected="false">Invoiced ({{$invoiceProductCount}})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link not-there" id="pills-not-there-tab" data-toggle="pill" href="#not-there"
                                role="tab"
                                aria-controls="not-there" aria-selected="false">Not There
                            ({{$outOfStockProductsCount}})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link edited" id="pills-edited-tab" data-toggle="pill" href="#edited"
                                role="tab"
                                aria-controls="edited" aria-selected="false">Edited ({{$editedProductsCount}})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link location_incorrect" id="pills-location-incorrect-tab" data-toggle="pill"
                                href="#location-incorrect"
                                role="tab"
                                aria-controls="location-incorrect" aria-selected="false">Location Incorrect
                            ({{$incorrectLocationProductsCount}})
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="packed" role="tabpanel"
                     aria-labelledby="pills-packed-tab">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-3">

                                        <p>Customer Name
                                            : @if($contacts !=null){{$contacts->customer_name}}@endif</p>

                                        <p>Company
                                            Name: @if($contacts !=null){{$contacts->company_name}}@endif
                                        </p>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <p>Order No : @if($contacts !=null)#{{$contacts->invoice_no}}@endif</p>
                                        <p><b>Status:
                                                @if( $contacts->p_status  ==  'ask_for_payment_before_ship')
                                                    Ask For Payment Before Shipping
                                                @elseif($contacts->p_status  == 'ok_to_ship')
                                                    Okay to Deliver/Ship (Payment Confirmed)
                                                @else
                                                    Ask In The Office
                                                @endif
                                            </b></p>
                                    </div>
                                    <div class="col-md-2 text-center text-danger p-0 m-0">
                                        <p>
                                            <b>Order Time : <span id="minutes">00</span>:<span
                                                        id="seconds">00</span></b>
                                        </p>
                                    </div>
                                    <div class="col-md-2">
                                        <p>Picked By : @if($contacts !=null)
                                        {{$contacts->pb_first_name}} {{$contacts->pb_last_name}}@endif</p>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="row text-center">
                                            <div class="col-md-12">
                                                <p><b>Order Note :</b>@if($contacts !=null){{$contacts->additional_notes}} @endif</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col-md-12">
                            <div class="container-fluid">
                                <div class="table-responsive">
                                    <p><strong>Last Scanned Item</strong> </p>
                                    <table class="table table-bordered table-responsive text-center"
                                           id="productDataTable">
                                        <thead>
                                        <tr>
                                            <th>Item Image</th>
                                            <th style="width: 40%;">Item Name</th>
                                            <th>Barcode</th>
                                            <th>Picked Qty</th>
                                            <th>Packed Qty</th>
                                            <th>Box No</th>
                                            <th>Pack</th>
                                        </tr>
                                        </thead>
                                        <tbody style="overflow: auto;">
                                        @if($packedProductDetails)
                                                <tr>
                                                    <td>
                                                        <img src="{{asset('/uploads').$packedProductDetails->image}}"
                                                             class="img img-responsive" width="75px">
                                                    </td>
                                                    <td>
                                                        <p>{{$packedProductDetails->product_name}}</p>
                                                        [<span class="item-code-search">{{$packedProductDetails->item_code}}</span>]
                                                    </td>
                                                    <td>
                                                        {{$packedProductDetails->sku}}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $pickQty = 0;
                                                            if($packedProductDetails->updated_piking_qty != null)
                                                            {
                                                               $pickQty = $packedProductDetails->updated_piking_qty;
                                                            }else{
                                                                if($packedProductDetails !=null && $packedProductDetails->edit_quantity != 0.00)
                                                                {
                                                                    $pickQty =  $packedProductDetails->edit_quantity;
                                                                }else{
                                                                    $pickQty = $packedProductDetails->quantity;
                                                                }
                                                            }
                                                        @endphp
                                                        {{  round($pickQty) }}
                                                    </td>
                                                    <td>
                                                        {{$packedProductDetails->packing_qty}}
                                                        <br>
                                                        @php
                                                            $remaining_qty = 0;
                                                            $remaining_qty  = $pickQty - $packedProductDetails->packing_qty;
                                                        @endphp
                                                        @if(!empty($remaining_qty))
                                                            Remaining QTY ({{$remaining_qty}})
                                                        @else
                                                            Remaining QTY (0)
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{  $packedProductDetails->box_no }}
                                                    </td>
                                                    <td>
                                                        @if($pickQty > 1 && $pickQty != $packedProductDetails->packing_qty)
                                                            <button onclick="packProduct('{{$packedProductDetails->id}}', true,'{{$packedProductDetails->packing_qty}}')" type="button" class="btn btn-sm btn-primary"> Pack </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col-md-8 pl-5">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-light">
                                        <i class="fa fa-barcode"></i>
                                    </button>
                                </div>
                                <input class="form-control mousetrap ui-autocomplete-input" id="search_product"
                                       placeholder="Enter Product name / SKU / Scan bar code" autofocus=""
                                       name="search_product" type="text" autocomplete="off">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-light">
                                        <i class="fa fa-plus-circle text-primary"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <label class="mt-2" style="fpnt-size:14px;"><b>Select Box No:</b></label>

                        <div class="col-md-2 input-group mb-3 text-center pr-crt" style="padding-left: 10px;">
                            <button class="input-group-text px-3 decrement-btn"> - </button>
                            <input type="text" name="quantity" class="quantity qty-input form-control input-number text-center" id="update-cart" value="{{!empty($last_selected_no) ? $last_selected_no : '1'}}" min="1" max="100">
                            <button class="input-group-text px-3 increment-btn" > + </button>
                        </div>

                        <div style="color: darkblue;" class="col-md-4">
                            <h4>Shipping Type : Delivery</h4>
                        </div>
                    </div>
                    <div class="row">
                        <style type="text/css">
                            .hide{
                                display: none !important;
                            }
                        </style>
                        <div class="col-md-8 pl-5">
                            <div class="form-group">
                                <div class="input-group">
                                    <button type="button" class="btn btn-light">
                                        <i class="fa fa-search"></i>
                                    </button>
                                    <input type="text" class="search-text valid" id="internal_search_packing" placeholder="Search..." aria-invalid="false">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="container-fluid">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-responsive text-center internalSearch"
                                           id="productDataTable">
                                        <thead>
                                        <tr>
                                            <th style="width: 30%;">Item Name</th>
                                            <th>Item Image</th>
                                            <th>Barcode</th>
                                            <th>Order Qty</th>
                                            <th>Picked Qty</th>
                                            <th>Packed Qty</th>
                                            <th>On Hand</th>
                                            {{-- <th>Enter BOX QTY</th> --}}
                                            {{-- <th>QTY / Box</th> --}}
                                            <th>Box No</th>
                                            <th>Edit</th>
                                            <th>Pack</th>
                                            <th>Split QTY</th>
                                        </tr>
                                        </thead>
                                        <tbody style="overflow: auto;">
                                        @if(!$orderdetails->isEmpty())
                                            @foreach($orderdetails as $item)
                                                <tr>
                                                    <td>
                                                        <p class="item-name-search">{{$item->product_name}}</p>
                                                        <p>{{$item->sell_line_note}}</p>
                                                        [<span class="item-code-search">{{$item->item_code}}</span>]
                                                    </td>
                                                    <td>
                                                        <img src="{{asset('/uploads').$item->image}}"
                                                             class="img img-responsive" width="75px">
                                                    </td>
                                                    <td class="barcode">{{$item->sku}}</td>
                                                     <td id="updateQty_{{$item->id}}">
                                                            {{ round($item->edit_quantity) != 0.00 ? round($item->edit_quantity) :  round($item->quantity)}}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $GoForSplitPickingQty=0;
                                                        @endphp
                                                        @if($item->updated_piking_qty != null)
                                                            @php
                                                                $GoForSplitPickingQty = $item->updated_piking_qty;
                                                            @endphp
                                                            {{$item->updated_piking_qty}}
                                                        @else

                                                            @php
                                                                $GoForSplitPickingQty = round($item->edit_quantity) != 0.00 ? round($item->edit_quantity) :  round($item->quantity);
                                                            @endphp

                                                            {{ round($item->edit_quantity) != 0.00 ? round($item->edit_quantity) :  round($item->quantity)}}
                                                        @endif

                                                        <input type="hidden" id="GoForSplitPickingQty_{{$item->id}}" value="{{$GoForSplitPickingQty}}" title="{{$item->product_name}} [{{$item->item_code}}]">
                                                    </td>
                                                    <td>
                                                        {{$item->packing_qty}}
                                                        <br>
                                                        @if(!empty($GoForSplitPickingQty))
                                                            @php
                                                                $remaining_qty_list = 0;
                                                                $remaining_qty_list  = $GoForSplitPickingQty - $item->packing_qty;
                                                            @endphp
                                                            @if(!empty($remaining_qty_list))
                                                                Remaining QTY ({{$remaining_qty_list}})
                                                            @else
                                                                Remaining QTY (0)
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{round($item->stock)}}
                                                    </td>
                                                    {{-- <td style="width: 60px;">
                                                        <input type="number" id="box_qty_input_{{$item->id}}" style="width: 50px;margin-bottom:10px;">
                                                        <button type="button" value="{{$item->id}}" class="box_counter btn btn-sm btn-primary">Add</button>
                                                    </td> --}}
                                                    <td style="width: 140px;">
                                                        <span id="box_qty_{{$item->id}}">
                                                            {{$item->box_no}}
                                                        </span>
                                                        <input type="hidden" id="box_store_{{$item->id}}" value='' />
                                                    </td>
                                                    <!-- <td>
                                                        <button type="button" class="btn btn-sm btn-warning">
                                                            Remain
                                                        </button>
                                                    </td> -->
                                                    <td>
                                                        <button onclick="editQty({{$item->id}},  {{$item->packing_qty}})" type="button"
                                                                class="btn btn-sm btn-primary" id="pack_btn_{{$item->id}}">
                                                            Edit
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <button onclick="packProduct('{{$item->id}}', true,'{{$item->packing_qty}}')" type="button"
                                                                class="btn btn-sm btn-primary">
                                                            Pack
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <button onclick="splitpackProduct('{{$item->id}}', true,'{{$item->packing_qty}}')" type="button"
                                                                class="btn btn-sm btn-primary">
                                                            Split
                                                        </button>
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
                <div class="tab-pane fade" id="picked" role="tabpanel" aria-labelledby="pills-picked-tab">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <h5>Packed Product List</h5>
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
                                            <th>Barcode</th>
                                            <th>Order Qty</th>
                                            <th>Picked Qty</th>
                                            <th>Packed Qty</th>
                                            <th>On Hand</th>
                                            <th>Box No</th>
                                            <th>Pack Started</th>
                                            <th>Pack Completed</th>
                                            <th>Status</th>
                                            <th>Action</th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(!$packedProducts->isEmpty())
                                            @foreach($packedProducts as $item)
                                                <tr>
                                                    <td>
                                                        <p>{{$item->product_name}}</p>
                                                        <p>{{$item->sell_line_note}}</p>
                                                    </td>
                                                    <td>
                                                        <img src="{{asset('/uploads').$item->image}}"
                                                             class="img img-responsive" width="75px">
                                                    </td>
                                                    <td>
                                                        {{$item->sku}}
                                                    </td>
                                                    <td>
                                                        {{$item->quantity}}
                                                    </td>
                                                    <td>
                                                        @if($item->updated_piking_qty != null)
                                                            {{$item->updated_piking_qty}}
                                                        @else
                                                            {{$item->quantity}}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{$item->quantity}}
                                                        @if($item->updated_piking_qty != null)
                                                            Remaining QTY ({{$item->updated_piking_qty-$item->quantity}})
                                                        @else
                                                            Remaining QTY ({{$item->quantity-$item->quantity}})
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{round($item->stock)}}
                                                    </td>
                                                     <td>
                                                       {{$item->box_no}}
                                                    </td>
                                                    <td>
                                                        @if($item->packing_started_time)
                                                            {{ @format_date($item->packing_started_time) }} {{ @format_time($item->packing_started_time) }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item->packing_completed_time)
                                                            {{ @format_date($item->packing_completed_time) }} {{ @format_time($item->packing_completed_time) }}</td>
                                                        @endif
                                                    <td>
                                                        <button type="button"
                                                                class="btn btn-sm @if($item->is_packed == 0) btn-success @else btn-warning @endif">
                                                            @if($item->is_packed == 0)
                                                                Packed
                                                            @else
                                                                Remain
                                                            @endif
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <button onclick="undoPacking({{$item->id}})" class="btn btn-sm btn-primary"><i class="fa fa-undo"></i> Undo</button>
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
                <div class="tab-pane fade" id="invoiced" role="tabpanel" aria-labelledby="pills-invoiced-tab">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <h5>Invoice Product List</h5>
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
                                            <th>Barcode</th>
                                            <th>Order Qty</th>
                                            <th>Picked Qty</th>
                                            <th>Packed Qty</th>
                                            <th>On Hand</th>
                                            <th>Box No</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(!$invoiced_products->isEmpty())
                                            @foreach($invoiced_products as $item)
                                                <tr>
                                                    <td>
                                                        <p>{{$item->product_name}}</p>
                                                        <p>{{$item->sell_line_note}}</p>
                                                    </td>
                                                    <td>
                                                        <img src="{{asset('/uploads').$item->image}}"
                                                             class="img img-responsive" width="75px">
                                                    </td>
                                                    <td>
                                                        {{$item->item_code}}
                                                    </td>
                                                    <td>
                                                        {{$item->quantity}}
                                                    </td>
                                                    <td>
                                                        @if($item->updated_piking_qty != null)
                                                            {{$item->updated_piking_qty}}
                                                        @else
                                                            {{$item->quantity}}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{$item->quantity}}
                                                    </td>
                                                    <td>
                                                        {{round($item->stock)}}
                                                    </td>
                                                    <td>
                                                        {{$item->box_no}}
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                                class="btn btn-sm @if($item->is_packed == 1) btn-success @elseif($item->out_of_stock == 1) btn-danger @else btn-warning @endif">
                                                            @if($item->is_packed == 0 && $item->out_of_stock == 0)
                                                                Remain
                                                            @elseif($item->out_of_stock == 1)
                                                                Out of stock
                                                            @else
                                                                Packed
                                                            @endif
                                                        </button>
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
                <div class="tab-pane fade" id="not-there" role="tabpanel" aria-labelledby="pills-not-there-tab">
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
                                            <th>Barcode</th>
                                            <th>Order Qty</th>
                                            <th>Picked Qty</th>
                                            <th>Packed Qty</th>
                                            <th>On Hand</th>
                                            <th>Status</th>
                                            <th>Action</th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(!$outOfStockProducts->isEmpty())
                                            @foreach($outOfStockProducts as $item)
                                                <tr>
                                                    <td>
                                                        <p>{{$item->product_name}}</p>
                                                        <p>{{$item->sell_line_note}}</p>
                                                    </td>
                                                    <td>
                                                        <img src="{{asset('/uploads').$item->image}}"
                                                             class="img img-responsive" width="75px">
                                                    </td>
                                                    <td>
                                                        {{$item->item_code}}
                                                    </td>
                                                    <td>
                                                        {{$item->quantity}}
                                                    </td>
                                                    <td>
                                                        @if($item->updated_piking_qty != null)
                                                            {{$item->updated_piking_qty}}
                                                        @else
                                                            {{$item->quantity}}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{$item->quantity}}
                                                    </td>
                                                    <td>
                                                        {{round($item->stock)}}
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                                class="btn btn-sm @if($item->is_packed == 0) btn-primary @else btn-warning @endif">
                                                            @if($item->is_packed == 0)
                                                                Packed
                                                            @else
                                                                Remain
                                                            @endif
                                                        </button>
                                                    </td>
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
                                            <th>Barcode</th>
                                            <th>Order Qty</th>
                                            <th>Picked Qty</th>
                                            <th>Packed Qty</th>
                                            <th>On Hand</th>
                                            <th>Box No</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(!$editedProducts->isEmpty())
                                            @foreach($editedProducts as $item)
                                                <tr>
                                                    <td>
                                                        <p>{{$item->product_name}}</p>
                                                        <p>{{$item->sell_line_note}}</p>
                                                    </td>
                                                    <td>
                                                        <img src="{{asset('/uploads/').$item->image}}"
                                                             class="img img-responsive" width="75px">
                                                    </td>
                                                    <td>
                                                        {{$item->item_code}}
                                                    </td>
                                                    <td>
                                                        {{$item->quantity}}
                                                    </td>
                                                    <td>
                                                        @if($item->updated_piking_qty != null)
                                                            {{$item->updated_piking_qty}}
                                                        @else
                                                            {{$item->quantity}}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{$item->quantity}}
                                                    </td>
                                                    <td>
                                                        {{round($item->stock)}}
                                                    </td>
                                                    <td>
                                                        {{$item->box_no}}
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                                class="btn btn-sm @if($item->is_packed == 0) btn-success @else btn-warning @endif">
                                                            @if($item->is_packed == 0)
                                                                Packed
                                                            @else
                                                                Remain
                                                            @endif
                                                        </button>
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
                <div class="tab-pane fade" id="location-incorrect" role="tabpanel"
                     aria-labelledby="pills-location-incorrect-tab">
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
                                            <th>Barcode</th>
                                            <th>Order Qty</th>
                                            <th>Picked Qty</th>
                                            <th>Packed Qty</th>
                                            <th>On Hand</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(!$incorrectLocationProducts->isEmpty())
                                            @foreach($incorrectLocationProducts as $item)
                                                <tr>
                                                    <td>
                                                        <p>{{$item->product_name}}</p>
                                                        <p>{{$item->sell_line_note}}</p>
                                                    </td>
                                                    <td>
                                                        <img src="{{asset('/uploads').$item->image}}"
                                                             class="img img-responsive" width="75px">
                                                    </td>
                                                    <td>
                                                        {{$item->item_code}}
                                                    </td>
                                                    <td>
                                                        {{$item->quantity}}
                                                    </td>
                                                    <td>
                                                        @if($item->updated_piking_qty != null)
                                                            {{$item->updated_piking_qty}}
                                                        @else
                                                            {{$item->quantity}}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{$item->quantity}}
                                                    </td>
                                                    <td>
                                                        {{round($item->stock)}}
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                                class="btn btn-sm @if($item->is_packed == 0) btn-primary @else btn-warning @endif">
                                                            @if($item->is_packed == 0)
                                                                Packed
                                                            @else
                                                                Remain
                                                            @endif
                                                        </button>
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
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Packing Slip & Invoice</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="{{action('Restaurant\KitchenController@finalizePackingOrder', [$transaction_id])}}">
                <input id="timingCount" type="hidden" name="timingCount"
                        value="">
                <input type="hidden" name="finalizeprint"
                        value="print">
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-check"></i> Print Invoice
                </button>
            </form>
            <form action="{{action('Restaurant\KitchenController@finalizePackingOrder', [$transaction_id])}}">
                <input id="timingCount" type="hidden" name="timingCount"
                        value="">
                <input type="hidden" name="finalizeprint"
                        value="finalizeprintpackingslip">
                <button type="submit" class="btn btn-primary  mt-1">
                    <i class="fa fa-check"></i> Print Packing Slip
                </button>
            </form>
            <form action="{{action('Restaurant\KitchenController@finalizePackingOrder', [$transaction_id])}}">
                <input id="timingCount" type="hidden" name="timingCount"
                        value="">
                <input type="hidden" name="finalizeprint"
                        value="printboth">
                <button type="submit" class="btn btn-warning mt-1">
                    <i class="fa fa-check"></i> Both (Packing Slip & Invoice)
                </button>
            </form>
          </div>
          <!-- <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary">Save changes</button>
          </div> -->
        </div>
      </div>
    </div>
    <!-- Split BOX Modal -->
    <div class="modal fade" id="boxqtyModal" tabindex="-1" role="dialog" aria-labelledby="boxqtyModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="boxqtyModalLabel">Split QTY with Box(s)</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
               <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" id="split_qty_display_id">
                        Picked <input id="split_qty_display" readonly="readonly" style="border:none;width: 50px;text-align: center;"> QTY for <span id="split_qty_display_p_name"></span>
                    </div>
               </div>
                <div class="row">
                    <div class="col-md-12">
                            <div id="boxdata"></div>
                            <button id="rowAdder" type="button"
                                class="btn btn-dark pull-right">
                                <span class="bi bi-plus-square-dotted">
                                </span> ADD BOX
                            </button>
                    </div>
                </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="split_box_save">Save</button>
          </div>
        </div>
      </div>
    </div>
        <div style="margin-top: 50px;" class="row">
        <div class="col-sm-12">
            <div class="row" style="padding: 1rem">
                <div class="col-md-3">
                    @if($orderProduct != null)
                        @if($orderProduct->id != null)
                            <a href="{{action('Restaurant\KitchenController@saveAndHoldPacking', [$orderProduct->transaction_id])}}"
                                class="btn btn-primary" style="width:100%;">
                                <i class="fa fa-pause"></i> Save & Hold
                            </a>
                        @else
                            <a href="#"
                                class="btn btn-primary" style="width:100%;">
                                <i class="fa fa-pause"></i> Save & Hold
                            </a>
                        @endif
                    @else
                        <a href="#"
                            class="btn btn-primary" style="width:100%;">
                            <i class="fa fa-pause"></i> Save & Hold
                        </a>
                    @endif
                </div>

                <div class="col-md-3">
                    @if($orderdetails->isEmpty() && $packedProductsCount > 0)
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-success" style="width:100%;" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-check"></i> Finalize Packing
                        </button>
                    @else
                        <button class="btn btn-success"  style="width:100%;">
                            <i class="fa fa-check"></i> Finalize Packing
                        </button>
                    @endif
                </div>

                <div class="col-sm-3">
                    @if($transaction_id != null)
                        <a style="margin-right: 15px;width:100%;" href="{{action('Restaurant\KitchenController@cancelPakingOrder', [$transaction_id])}}"
                            class="btn btn-danger leave-page">
                            <i class="fa fa-window-close"></i> Clear
                        </a>
                    @else
                        <a style="margin-right: 15px;width:100%;" href="/modules/orders"
                            class="btn btn-danger leave-page">
                            <i class="fa fa-window-close"></i> Clear
                        </a>
                    @endif
                </div>

                <div class="col-sm-3">
                    @if($orderProduct != null)
                        @if($orderProduct->id != null)
                            <a href="{{action('Restaurant\KitchenController@orderQueue', [$orderProduct->transaction_id])}}"
                                class="btn btn btn-warning leave-page" style="width:100%;">
                                <i class="fa fa-th"></i> Order Queue
                            </a>
                        @else
                            <a href="/modules/orders"
                                class="btn btn btn-warning leave-page" style="width:100%;">
                                <i class="fa fa-th"></i> Order Queue
                            </a>
                        @endif
                    @else
                            <a href="/modules/orders"
                                class="btn btn btn-warning leave-page" style="width:100%;">
                                <i class="fa fa-th"></i> Order Queue
                            </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- <div style="margin-top: 50px;" class="row">
        <div class="col-sm-9">
            <div class="row">
                <div style="padding-left: 50px;" class="col-sm-7">
                    <div class="row p-0 m-0">
                        <div class="col-md-4 p-0 m-0">
                            @if($orderProduct != null)
                                @if($orderProduct->id != null)
                                    <a href="{{action('Restaurant\KitchenController@saveAndHoldPacking', [$orderProduct->transaction_id])}}"
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

                        <div class="col-md-4 p-0 m-0">
                            @if($orderdetails->isEmpty() && $packedProductsCount > 0)
                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModal">
                                    <i class="fa fa-check"></i> Finalize Packing
                                </button>
                            @else
                                <button class="btn btn-success" style="width: 300px;">
                                    <i class="fa fa-check"></i> Finalize Packing
                                </button>
                            @endif
                        </div>

                        <div style="display: none;">
                            <div class="col-md-4 p-0 m-0">
                                @if($orderdetails->isEmpty() && $packedProductsCount > 0)
                                    <form action="{{action('Restaurant\KitchenController@finalizePackingOrder', [$transaction_id])}}">
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
                            <div class="col-md-4 p-0 m-0">
                                @if($orderdetails->isEmpty() && $packedProductsCount > 0)
                                    <form action="{{action('Restaurant\KitchenController@finalizePackingOrder', [$transaction_id])}}">
                                        <input id="timingCount" type="hidden" name="timingCount"
                                                value="">
                                        <input type="hidden" name="finalizeprint"
                                                value="print">
                                        <!--<button type="submit" class="btn btn-success">-->
                                        <!--    <i class="fa fa-check"></i> Finalize & Print-->
                                        <!--</button>-->
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exampleModal">
                                        <i class="fa fa-check"></i> Finalize & Print
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-success">
                                        <i class="fa fa-check"></i> Finalize & Print
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-sm-5">
                    @if($transaction_id != null)
                        <a style="margin-right: 15px;" href="{{action('Restaurant\KitchenController@cancelPakingOrder', [$transaction_id])}}"
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
        <div class="col-sm-3"></div>
    </div> --}}

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Finalize Packing</h4>
            </div>
            <form action="{{action('Restaurant\KitchenController@finalizePackingOrder', [$transaction_id])}}" id="new-form">
                <div class="modal-body">
                    <div class="from-group">
                        <label>Enter @lang('lang_v1.box_qty')</label>
                        <input type="number" name="box_qty" class="form-control" value="{{ $last_box_no ?? 0 }}" required readonly/>
                        <!--<input type="number" name="box_qty" class="form-control" value="{{ $transaction->box_qty ?? 0 }}" required />-->

                    </div>
                    <div class="form-group" style="margin-top: 1rem;">
                        <label>Select Printing Options:</label>
                        <div data-toggle="buttons">
                            <div class="btn-group" style="display: flex">
                                <label class="btn btn-outline-warning">
                                    <input type="radio" name="finalizeprint" id="option1" value="print" style="display: none"> Invoice
                                </label>
                                <label class="btn btn-outline-warning">
                                    <input type="radio" name="finalizeprint" id="option2" value="finalizeprintpackingslip" style="display: none"> Packing Slip
                                </label>
                            </div>
                            <div style="display: flex">
                                <label class="btn btn-outline-warning active" style="width: 100%">
                                    <input type="radio" name="finalizeprint" id="option3" value="printboth" style="display: none" checked> Both ( Invoice & Packing Slip )
                                </label>
                            </div>
                            <div style="display: none">
                                <label class="btn btn-outline-dark" style="width: 100%">
                                    <input type="radio" name="finalizeprint" id="option4" value="noprint" style="display: none"> Don't Print
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="no-print" class="btn btn-primary">Finalize</button>
                    <button type="submit" class="btn btn-success">Finalize & Print</button>
                </div>
            </form>
        </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {

            $(document).on('click','.box_counter', function(){
                //get all values in variable
                var curr_id = $(this).val();
                var curr_input = parseInt($("#box_qty_input_"+curr_id).val(), 10);
                var curr_store = $("#box_store_"+curr_id);
                var box_no = parseInt($('.qty-input').val(), 10);

                var box_values = {};
                if(curr_store.val() != ""){
                    //get current values as json
                    box_values = JSON.parse(curr_store.val());
                }

                //validate inputs
                box_no = isNaN(box_no) ? 1 : box_no;
                curr_input = isNaN(curr_input) ? 1 : curr_input;

                //assing box value to box number
                box_values[box_no] = curr_input;

                //delete element if 0
                if(curr_input == 0){
                    delete box_values[box_no];
                }

                //convert json object to string
                curr_store.val(JSON.stringify(box_values));
                //increase box number
                $('.increment-btn').trigger('click');

                console.log(box_values);

                var display_string = "";
                for(var key in box_values) {
                    display_string += box_values[key] + " / B" + key + "<br/>";
                }

                $("#box_qty_"+curr_id).html(display_string);

            });

               // increment product quantity when + button is clicked
            $('.increment-btn').on('click', function(e) {
                e.preventDefault();

                let inc_value = $('.qty-input').val();
                let value = parseInt(inc_value, 10);
                value = isNaN(value) ? 0 : value;

                if(value < 100) {
                    value++;
                    $('.qty-input').val(value);
                }
            });

            // decrement product quantity when - button is clicked
            $('.decrement-btn').on('click', function(e) {
                e.preventDefault();

                let inc_value = $('.qty-input').val();
                let value = parseInt(inc_value, 10);

                value = isNaN(value) ? 0 : value;
                if(value > 1) {
                    value--;
                    $('.qty-input').val(value);
                }
            });

            $("#rowAdder").click(function () {
            var boxIndex = $('.row_split').find('.m-input').length + 1;
            newRowAdd =
            '<div id="row" class="row_split"> <div class="input-group m-3 col-md-12">' +
            '<div class="input-group-prepend">' +
            '<button class="btn btn-danger del_pack_box" id="DeleteRowofBox" type="button">' +
            '<i class="bi bi-trash"></i> Remove Box </button> </div>' +
            '<input placeholder="Box No." type="text" name="split_pack_box_no[]" class="text-center form-control split_pack_box_no">' +
            '<input placeholder="QTY " type="text" name="split_pack_qty[]" class="text-center form-control m-input split_pack_qty"> </div> </div>';

            $('#boxdata').append(newRowAdd);
            });

            $(document).on('keyup','input.split_pack_qty', function(){
                this.value = this.value.replace(/[^0-9]/g,'');
                if(this.value==0)
                {
                    $(this).val("");
                }
            });

            $(document).on('keyup','input.split_pack_box_no', function(){
                this.value = this.value.replace(/[^0-9]/g,'');
                if(this.value==0)
                {
                    $(this).val("");
                }
            });

            $("body").on("click", "#DeleteRowofBox", function () {
                $(this).parents("#row").remove();
                //alert($('button.del_pack_box').length);
                /*if($('button.del_pack_box').length>0)
                {
                    var i=1;
                    $(".row_split").find('button.del_pack_box').each(function() {
                        $(this).html('<i class="bi bi-trash"></i> Remove Box </button>');
                        i++;
                    });
                }*/
            })

            $("#split_box_save").click(function () {

                var inps = document.getElementsByName('split_pack_qty[]');
                var inps_box_no = document.getElementsByName('split_pack_box_no[]');
                var split_box_qty_string = "";
                var split_box_qty_value = 0;
                var display_index = 1;
                if(inps.length>0)
                {
                    for (var i = 0; i <inps.length; i++)
                    {
                        var inp=inps[i];
                        var inp_box_no=inps_box_no[i];

                        if(inp.value!="" && inp_box_no.value!="")
                        {

                            if(display_index<inps.length)
                            {
                                split_box_qty_string = split_box_qty_string+"BOX"+inp_box_no.value+":"+inp.value+',';
                            }
                            else
                            {
                                split_box_qty_string = split_box_qty_string+"BOX"+inp_box_no.value+":"+inp.value;
                            }

                            split_box_qty_value = parseInt(split_box_qty_value) + parseInt(inp.value);

                            //("split_pack_qty["+i+"].value="+inp.value);
                        }

                        display_index++;
                    }
                }
                //alert(split_box_qty_value);
                //alert(split_box_qty_string);

                var qty = $('#split_qty_display').val();
                if(split_box_qty_value == qty && split_box_qty_string!="")
                {
                    //alert($('#split_qty_display_id').val());
                    var GowithID = $('#split_qty_display_id').val();

                    ProcessOfpackProduct(GowithID, true,split_box_qty_string);
                }
                else
                {
                    alert("SUM of Split BOX(s) QTY should be same as total picked qty");
                }
            });

            $('#no-print').on('click',function(){
                $('#option4').prop('checked', true);
                $('#new-form').submit();
            });

            $('#search_product').autocomplete({
                source: function (request, response) {
                    $.ajax({
                        type: "GET",
                        contentType: "application/json; charset=utf-8",
                        url: "{{action('Restaurant\KitchenController@packProduct')}}",
                        dataType: "json",
                        data: {
                            order_id: "@if($orderProduct !=null)#{{$orderProduct->invoice_no}}@endif",
                            searchKey: request,
                            box_no:"BOX"+$('input.qty-input').val()+":1",
                        },
                        success: function (data) {
                            if(data.status === true){
                                var audio = $('#success-audio')[0];
                                if (audio !== undefined) {
                                    audio.play();
                                }
                                location.reload();
                            }else{
                                document.getElementById("search_product").value = "";
                                var audio = $('#error-audio')[0];
                                if (audio !== undefined) {
                                    audio.play();
                                }
                                if(data.msg)
                                {
                                    alert(data.msg);
                                }else{
                                    alert("Product Is Not Available!");
                                }
                            }
                        }
                    });
                },
                minLength: 3,
                select: function (event, ui) {
                    location.reload();
                }
            });
        });
        $(document).on("click", ".leave-page", function(e){
            var r = confirm("Are you sure you want to leave this page");
            if (r == false) {
                e.preventDefault();
            }
        });

        function editQty(id, packing_qty) {
            let p_qty_box = document.getElementById('updateQty_'+id) ;
            let pre_qty = p_qty_box.innerText;
            $('#updateQty_'+id).html('<input data-content="'+id+'" data-packing_qty="'+packing_qty+'" onkeypress="getProductQty(event, id)" style="width:60px;" type="text" value="'+pre_qty+'" name="produc_qty" class="getProductQty" id="inp_ord_'+id+'">');
            // alert(id);
        }

        function packProduct(id, all_pack=false,packed_qty='0') {

            // console.log('id:', id)
            var split_qty_val = parseInt($('#GoForSplitPickingQty_'+id).val()) - parseInt(packed_qty);

            var box_no = $('input.qty-input').val();

            var qty_box_string = "BOX"+box_no+":"+split_qty_val;

            console.log(box_no);
            $.ajax({
                type: "GET",
                contentType: "application/json; charset=utf-8",
                url: "{{action('Restaurant\KitchenController@packProduct')}}",
                dataType: "json",
                data: {
                    order_id: "@if($orderProduct !=null)#{{$orderProduct->invoice_no}}@endif",
                    searchKey: id,
                    packed_by_id: id,
                    all_pack: all_pack,
                    box_no:qty_box_string,
                },
                success: function (data) {
                    console.log('data:', data)
                    var audio = $('#success-audio')[0];
                    if (audio !== undefined) {
                        audio.play();
                    }
                    location.reload();
                }
            });
        }

        function splitpackProduct(id, all_pack=false,packed_qty='0') {

            $('#split_qty_display_id').val("");
            $('#split_qty_display').val("");
            $('#split_qty_display_p_name').html("");
            $("#boxdata").html("");

            //split_qty_val = $('#GoForSplitPickingQty_'+id).val();
            var split_qty_val = parseInt($('#GoForSplitPickingQty_'+id).val()) - parseInt(packed_qty);
            split_qty_title = $('#GoForSplitPickingQty_'+id).attr('title');

            if(split_qty_val!='0' && split_qty_title!="")
            {
                $('#split_qty_display_id').val(id);
                $('#split_qty_display').val(split_qty_val);
                $('#split_qty_display_p_name').html(split_qty_title);
                $('#boxqtyModal').modal("show");
            }
        }

        function ProcessOfpackProduct(id, all_pack=false,qty_box_string="")
        {
            // console.log('id:', id)
            $.ajax({
                type: "GET",
                contentType: "application/json; charset=utf-8",
                url: "{{action('Restaurant\KitchenController@packProduct')}}",
                dataType: "json",
                data: {
                    order_id: "@if($orderProduct !=null)#{{$orderProduct->invoice_no}}@endif",
                    searchKey: id,
                    packed_by_id: id,
                    all_pack: all_pack,
                    box_no:qty_box_string,
                },
                success: function (data) {
                    console.log('data:', data)
                    $('#split_qty_display_id').val("");
                    $('#split_qty_display').val("");
                    $('#split_qty_display_p_name').html("");
                    $("#boxdata").html("");
                    var audio = $('#success-audio')[0];
                    if (audio !== undefined) {
                        audio.play();
                    }
                    location.reload();
                }
            });
        }

        // document.getElementById("pack_btn").disabled = false;
        function getProductQty(e, id){

            let key=e.keyCode || e.which;
            let p_qty =  e.target.value;
            let p_id = e.target.getAttribute('data-content');
            let packing_qty = e.target.getAttribute('data-packing_qty');

            let ord_value = document.getElementById("inp_ord_"+p_id).value;
            if (key===13){
                if(parseInt(ord_value) < parseInt(packing_qty) )
                {
                    document.getElementById("pack_btn_"+p_id).disabled = true;
                }else{
                    document.getElementById("pack_btn_"+p_id).disabled = false;
                        {{-- url:'{{action('Restaurant\KitchenController@updateProductQty')}}', --}}
                    var url = '{{action('Restaurant\KitchenController@editProductQty')}}';
                    var method = 'POST';
                    if(p_qty == 0) {
                        url = '/modules/kitchen/product/out-of-stock/'+p_id;
                        method = 'GET';
                    }
                    $.ajax({
                        url:url,
                        type:method,
                        data:{
                            _token:'{{csrf_token()}}',
                            p_id:p_id,
                            p_qty:p_qty
                        },
                        success:function (response) {
                            console.log('response:', response)
                            location.reload();
                        }
                    });
                }
            }
        }
        function undoPacking(id){
            $.ajax({
                url:"/modules/kitchen/packing/undo/"+ id,
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

        $("#internal_search_packing").on('keyup',function(){
           var term = $(this).val().toLowerCase();
           internalSearch(term);
        });

        function internalSearch(term){
            var rowCount = $('.internalSearch tr').length;
            if(term.length>1){
                if(rowCount>1){
                    $('.internalSearch tbody').find('tr').each(function() {
                        var name = ($(this).find('p.item-name-search').html()).toLowerCase();
                        var item_code = ($(this).find('span.item-code-search').html()).toLowerCase();
                        var barcode = ($(this).find('td.barcode').html()).toLowerCase();
                        var res_name = 0;
                        var res_item_code = 0;
                        var res_barcode = 0;

                        if(term.length>1) res_name = name.indexOf(term);
                        if(term.length>1) res_item_code = item_code.indexOf(term);
                        if(term.length>1) res_barcode = barcode.indexOf(term);

                        if(res_name != -1 || res_item_code != -1 || res_barcode != -1){
                            $(this).removeClass('hide');
                        }else{
                            $(this).addClass('hide');
                        }
                    });
                }
            } else {
                if(rowCount>1){
                    $('.internalSearch tbody').find('tr').each(function() {
                        $(this).removeClass('hide');
                    })
                }
            }
        }
    </script>
    <script>
        @if(old('status') && old('status') == 'true' && old('finalizeprint') == 'print')
        $(document).ready(function () {
            var page = "{{old('iurl')}}";
            window.open(page, '_blank').focus();
        });
        @elseif(old('status') && old('status') == 'true' && old('finalizeprint') == 'finalizeprintpackingslip')
        $(document).ready(function () {
            var page = "{{old('surl')}}";
            window.open(page, '_blank').focus();
        });
        @elseif(old('status') && old('status') == 'true' && old('finalizeprint') == 'printboth')
        $(document).ready(function () {
            var spage = "{{old('surl')}}";
            window.open(spage, '_blank').focus();

            var ipage = "{{old('iurl')}}";
            window.open(ipage, '_blank').focus();
        });
        @endif
    </script>

@endsection
