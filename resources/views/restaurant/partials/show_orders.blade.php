@forelse($orders as $order)
    <div style="margin-bottom: 10px;" class="col-md-3 col-xs-6 order_div">
        <div @if($order->priority_order == 1) style="background-color: yellow;"
             @endif class="small-box @if($order->priority_order == 1) @else bg-gray @endif">
            <div class="inner" style="min-height: 400px;">
                <h4 class="text-center">#{{$order->invoice_no}}</h4>
                <table class="table no-margin no-border table-slim" style="color:#000 !important;">
                    <tr>
                        <th>@lang('restaurant.placed_at')</th>
                        <td>{{@format_date($order->created_at)}} {{ @format_time($order->created_at)}}</td>
                    </tr>
                    <tr>
                        <th>@lang('restaurant.order_status')</th>
                        {{--@php--}}
                        {{--$count_sell_line = count($order->sell_lines);--}}
                        {{--$count_cooked = count($order->sell_lines->where('res_line_order_status', 'cooked'));--}}
                        {{--$count_served = count($order->sell_lines->where('res_line_order_status', 'served'));--}}
                        {{--$order_status =  'received';--}}
                        {{--if($count_cooked == $count_sell_line) {--}}
                        {{--$order_status =  'cooked';--}}
                        {{--} else if($count_served == $count_sell_line) {--}}
                        {{--$order_status =  'served';--}}
                        {{--} else if ($count_served > 0 && $count_served < $count_sell_line) {--}}
                        {{--$order_status =  'partial_served';--}}
                        {{--} else if ($count_cooked > 0 && $count_cooked < $count_sell_line) {--}}
                        {{--$order_status =  'partial_cooked';--}}
                        {{--}--}}
                        {{--@endphp--}}
                        <td>
                            <span class="label
                                <!--@if($order->order_picking_status == 0)-->
                                <!--    bg-green-->
                                <!--@elseif($order->order_picking_status == 1)-->
                                <!--    bg-orange-->
                                <!--@elseif($order->order_picking_status == 2)-->
                                <!--    bg-green-->
                                <!--@elseif($order->order_picking_status == 3)-->
                                <!--    bg-red-->
                                <!--@else-->
                                <!--    bg-blue-->
                                <!--@endif-->
                                @if($order->order_picking_status == 2 && $order->order_packing_status == 0)
                                    bg-yellow
                                @elseif($order->order_packing_status == 2 && $order->order_picking_status == 2)
                                bg-green
                                @elseif($order->order_packing_status == 1 && $order->order_picking_status == 2)
                                bg-green
                                @elseif($order->order_picking_status == 0)
                                bg-red
                                @elseif($order->order_picking_status == 1)
                                bg-aqua
                                @elseif($order->order_picking_status == 3)
                                bg-blue
                                @else
                                bg-red
                                @endif
                                ">
                                @if($order->order_picking_status == 2 && $order->order_packing_status == 0)
                                    Picking Completed
                                @elseif($order->order_packing_status == 2 && $order->order_picking_status == 2)
                                    Packing Completed
                                @elseif($order->order_packing_status == 1 && $order->order_picking_status == 2)
                                    Packing Started
                                @elseif($order->order_picking_status == 0)
                                    Received
                                @elseif($order->order_picking_status == 1)
                                    Picking Started
                                @elseif($order->order_picking_status == 3)
                                    Cancel
                                @else
                                    Received
                                @endif
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>@lang('contact.customer')</th>
                        <td>{{$order->customer_name}}</td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td>{{round($order->final_total, 2)}}</td>
                    </tr>
                    <tr>
                        <th>No of items</th>
                        <td>{{$order->item_qty}}</td>
                    </tr>
                    <tr>
                        <th>City State</th>
                        <td>@if($order->city != null || $order->state !=null){{$order->city.', '.$order->state}}@endif</td>
                    </tr>
                    <tr>
                        <th>Order taken by</th>
                        <td style='font-size:12px;' >{{$order->first_name}} {{$order->last_name}}</td>
                    </tr>
                    <tr>
                        <th>Order Note</th>
                            <td style='font-size:12px;'>{{str_limit($order->additional_notes, 250)}}</td>
                    </tr>
                    <tr>
                        <th>Staff Note</th>
                        <td>{{str_limit($order->staff_note, 150)}}</td>
                    </tr>
                    <tr>
                        <th>Delivery Method</th>
                        <td>
                            @if($order->delivery_method == 'posShippingModalUpdateDelivery')
                                <span class="label bg-yellow">Delivery</span>
                            @elseif($order->delivery_method == 'posShippingModalUpdatePickup')
                                <span class="label bg-info">Pickup</span>
                            @elseif($order->delivery_method == 'posShippingModalUpdateShipping')
                                <span class="label bg-navy">Shipping</span>
                            @elseif($order->delivery_method == 'posShippingModalUpdatePallet')
                                <span class="label bg-green">Pallet</span>
                            @elseif($order->delivery_method == 'posShippingModalUpdateSelfNonPicking')
                                <span class="label bg-yellow">Walk In (Self)</span>
                            @elseif($order->delivery_method == 'posShippingModalUpdateDeliveryNonPicking')
                                <span class="label bg-info">Walk In (Delivery)</span>
                            @elseif($order->delivery_method == 'posShippingModalUpdateShippingNonPicking')
                                <span class="label bg-navy">Walk In (Shipping)</span>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th> <b>Status:</b></th>
                        <td style='font-size:12px;'>
                            @if( $order->p_status  ==  'ask_for_payment_before_ship')
                                Ask For Payment Before Shipping
                            @elseif($order->p_status  == 'ok_to_ship')
                                Okay to Deliver/Ship (Payment Confirmed)
                            @else
                                Ask In The Office
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            {{--            @if($orders_for == 'kitchen')--}}

            <a {{ ($order->order_picking_status == 2) ? 'disabled' : '' }} href="{{($order->order_picking_status == 2) ? 'javascript:' : action('Restaurant\KitchenController@startPicking', [$order->id]) }}"
               class="btn btn-flat small-box-footer bg-yellow mark_as_cooked_btn col-md-6 border-right"><i
                        class="fa fa-check-circle"></i>
                        @if($order->order_picking_status == 1)
                        Resume Picking
                        @else
                        Start Picking
                        @endif
                        </a>
            <a {{ ($order->order_picking_status == 2 && $order->order_packing_status != 2) ? '' : 'disabled' }}  href="{{($order->order_picking_status == 2 && $order->order_packing_status != 2) ? action('Restaurant\KitchenController@startPacking', [$order->id]) : 'javascript:'}}"
               style="background-color: green;" class="btn btn-flat small-box-footer mark_as_cooked_btn col-md-6"><i
                        class="fa fa-check-circle"></i> Start Packing</a>
            {{--@elseif($orders_for == 'waiter' && $order->res_order_status != 'served')--}}
            {{--<a href="#" class="btn btn-flat small-box-footer bg-yellow mark_as_served_btn col-md-6" data-href="{{action('Restaurant\OrderController@markAsServed', [$order->id])}}"><i class="fa fa-check-square-o"></i> @lang('restaurant.mark_as_served')</a>--}}
            {{--@else--}}
            {{--<div class="small-box-footer bg-gray col-md-6">&nbsp;</div>--}}
            {{--@endif--}}
            <a href="#" class="btn btn-flat small-box-footer bg-info btn-modal col-md-12"
               data-href="{{ action('SellController@show', [$order->id])}}"
               data-container=".view_modal">@lang('restaurant.order_details') <i
                        class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @if($loop->iteration % 4 == 0)
        <div class="hidden-xs">
            <div class="clearfix"></div>
        </div>
    @endif
    @if($loop->iteration % 2 == 0)
        <div class="visible-xs">
            <div class="clearfix"></div>
        </div>
    @endif
@empty
    <div class="col-md-12">
        <h4 class="text-center">@lang('restaurant.no_orders_found')</h4>
    </div>
@endforelse