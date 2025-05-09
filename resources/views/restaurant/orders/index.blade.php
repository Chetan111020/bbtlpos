@extends('layouts.restaurant')
@section('title', __( 'restaurant.orders' ))
@section('content')
    <!-- Main content -->
    <section class="content min-height-90hv no-print">
        <div class="row">
            <div class="col-md-12 text-center">
                <h3>@lang( 'restaurant.all_orders' ) @show_tooltip(__('lang_v1.tooltip_serviceorder'))</h3>
            </div>
            
            <div class="row">
                
                  @if(is_array($ord_list1) && $ord_list1['name'] === 'received')
    <div class="col-md-3 col-sm-6 col-xs-12 status" data-status="received" >
          <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-plus"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Received</span>
              <span class="info-box-number" style="font-size:28px">{{ $ord_list1['count'] }}</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
         @endif
        
         @if(is_array($ord_list3) && $ord_list3['name'] === 'picking_started')
                    
            <div class="col-md-3 col-sm-6 col-xs-12 status" data-status="picking_started" >
          <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-cart-arrow-down"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Picking Started</span>
              <span class="info-box-number" style="font-size:28px">{{ $ord_list3['count'] }}</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        
          @endif
        
          @if(is_array($ord_list2) && $ord_list2['name'] === 'picking_completed')
                       
            <div class="col-md-3 col-sm-6 col-xs-12 status" data-status="picking_completed" >
          <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-check"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Picking Completed</span>
              <span class="info-box-number" style="font-size:28px"> {{ $ord_list2['count'] }}</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
                            @endif

          
         @if(is_array($ord_list5) && $ord_list5['name'] === 'packing_started')
                      
            <div class="col-md-3 col-sm-6 col-xs-12 status" data-status="packing_started" >
          <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-box"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Packing Started </span>
              <span class="info-box-number" style="font-size:28px">{{ $ord_list5['count'] }}
               </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        
          @endif  
          
          
           <div class="col-md-3 col-sm-6 col-xs-12" style="display:none;">
          <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-check"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Time To Finish Work</span>
              <span class="info-box-number" style="font-size:28px">
                 <table><tr><td id="days" style="display:none;"></td><td id="hours"></td><td id="mins"></td><td id="secs"></td><td>  <h4 id="end" style="color:red"></h4></td></tr> </table>
</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
          
          
        </div>
            <div class="col-sm-12" style="display:none">
                    @if(is_array($ord_list1) && $ord_list1['name'] === 'received')
                       <b> Received Total: </b>{{ $ord_list1['count'] }}<br>
                    @endif
                    @if(is_array($ord_list2) && $ord_list2['name'] === 'picking_completed')
                        <b>Picking Completed Total:</b> {{ $ord_list2['count'] }} <br>
                    @endif
                    @if(is_array($ord_list3) && $ord_list3['name'] === 'picking_started')
                        <b>Picking Started Total: </b>{{ $ord_list3['count'] }} <br>
                    @endif
                    @if(is_array($ord_list4) && $ord_list4['name'] === 'packing_completed')
                        <b>Packing Completed Total:</b> {{ $ord_list4['count'] }} <br>
                    @endif
                    @if(is_array($ord_list5) && $ord_list5['name'] === 'packing_started')
                        <b>Packing Started Total:</b> {{ $ord_list5['count'] }} <br>
                    @endif
                    <button type="button" class="btn btn-sm btn-primary pull-right" id="refresh_orders"><i
                            class="fas fa-sync"></i> @lang( 'restaurant.refresh' )</button>
            </div>
            <div class="col-sm-12">
                
            </div>
        </div>
        <br>
        <div class="row justify-content-center">
            @component('components.widget')
            {!! Form::open(['url' => action('Restaurant\OrderController@index'), 'method' => 'get', 'id' => 'select_service_staff_form' ]) !!}
                <div class="col-md-3">
                <select name="order_status" id="search_order_status" class="form-control">
                    <option value="">Select Order Status</option>
                    @foreach($order_status_list as $key => $value)
                    <option {{($key == $order_status) ? "selected" : "" }} value="{{$key}}">{{$value}}</option>
                    @endforeach
                </select>
                </div>
                <div class="col-sm-6">
                    
                    <div class="form-group">
                        <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-search"></i>
                        </span>
                            <input type="text" name="order_no" value="{{$order_no}}" id="search_order" placeholder="Search by order no and customer name" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="col-md-3"></div>
            {!! Form::close() !!}
            @endcomponent
            {{--@component('components.widget', ['title' => __( 'lang_v1.line_orders' )])--}}
            {{--<input type="hidden" id="orders_for" value="waiter">--}}
            {{--<div class="row" id="line_orders_div">--}}
            {{--@include('restaurant.partials.line_orders', array('orders_for' => 'waiter'))--}}
            {{--</div>--}}
            {{--<div class="overlay hide">--}}
            {{--<i class="fas fa-sync fa-spin"></i>--}}
            {{--</div>--}}
            {{--@endcomponent--}}

            @component('components.widget', ['title' => __( 'restaurant.all_your_orders' )])
                <input type="hidden" id="orders_for" value="waiter">
                <div class="row" id="orders_div">
                    @include('restaurant.partials.show_orders', array('orders_for' => 'waiter'))
                </div>
                <div class="overlay hide">
                    <i class="fas fa-sync fa-spin"></i>
                </div>
            @endcomponent
        </div>
    </section>
    <!-- /.content -->

@endsection

@section('javascript')

<script>
    
        // The data/time we want to countdown to
    var countDownDate = new Date("Jun 30, 2021 20:00:00").getTime();

    // Run myfunc every second
    var myfunc = setInterval(function() {

    var now = new Date().getTime();
    var timeleft = countDownDate - now;
        
    // Calculating the days, hours, minutes and seconds left
    var days = Math.floor(timeleft / (1000 * 60 * 60 * 24));
    var hours = Math.floor((timeleft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((timeleft % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((timeleft % (1000 * 60)) / 1000);
        
    // Result is output to the specific element
    document.getElementById("days").innerHTML = days + "d "
    document.getElementById("hours").innerHTML = hours + ": " 
    document.getElementById("mins").innerHTML = minutes + ": " 
    document.getElementById("secs").innerHTML = seconds + " " 
        
    // Display the message when countdown is over
    if (timeleft < 0) {
        clearInterval(myfunc);
        document.getElementById("days").innerHTML = ""
        document.getElementById("hours").innerHTML = "" 
        document.getElementById("mins").innerHTML = ""
        document.getElementById("secs").innerHTML = ""
        document.getElementById("end").innerHTML = "TIME UP!!";
    }
    }, 1000);
</script>

    <script type="text/javascript">
        window.setTimeout(function () {
            window.location.reload();
        }, 30000);
        $('input#search_order').change(function () {
            $('form#select_service_staff_form').submit();
        });
        $('#search_order_status').change(function () {
            $('form#select_service_staff_form').submit();
        });
        $(document).ready(function () {
            $(document).on('click', 'a.mark_as_served_btn', function (e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    icon: "info",
                    buttons: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var _this = $(this);
                        var href = _this.data('href');
                        $.ajax({
                            method: "GET",
                            url: href,
                            dataType: "json",
                            success: function (result) {
                                if (result.success == true) {
                                    refresh_orders();
                                    toastr.success(result.msg);
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('click', 'a.mark_line_order_as_served', function (e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    icon: "info",
                    buttons: true,
                }).then((sure) => {
                    if (sure) {
                        var _this = $(this);
                        var href = _this.attr('href');
                        $.ajax({
                            method: "GET",
                            url: href,
                            dataType: "json",
                            success: function (result) {
                                if (result.success == true) {
                                    refresh_orders();
                                    toastr.success(result.msg);
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });
            
        $(document).on('click','.status',function(){
           var status_name = $(this).attr('data-status');
           $("#search_order_status option").each(function()
            {
                var status = $(this).val();
                if(status == status_name){
                    $(this).attr("selected","selected");
                    $('form#select_service_staff_form').submit();
                }
                
            });
        });
        });
    </script>
@endsection