@extends('layouts.app')
@section('title',"Store Wise GP Report")

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>Store Wise GP Report
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
    @if(empty($only) || in_array('sell_list_filter_location_id', $only))
    <div class="col-md-3 hide">
        <div class="form-group">
            {!! Form::label('sell_list_filter_location_id',  __('purchase.business_location') . ':') !!}

            {!! Form::select('sell_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all') ]); !!}
        </div>
    </div>
    @endif
    @if(empty($only) || in_array('sell_list_filter_customer_id', $only))
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('sell_list_filter_customer_id',  __('contact.customer') . ':') !!}
            {!! Form::select('sell_list_filter_customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
        </div>
    </div>
    @endif
    @if(empty($only) || in_array('sell_list_filter_payment_status', $only))
    <div class="col-md-3 hide">
        <div class="form-group">
            {!! Form::label('sell_list_filter_payment_status',  __('purchase.payment_status') . ':') !!}
            {!! Form::select('sell_list_filter_payment_status', ['paid' => __('lang_v1.paid'), 'due' => __('lang_v1.due'), 'partial' => __('lang_v1.partial'), 'overdue' => __('lang_v1.overdue')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
        </div>
    </div>
    @endif
    @if(empty($only) || in_array('sell_list_filter_date_range', $only))
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
            {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
        </div>
    </div>
    @endif
    @if((empty($only) || in_array('created_by', $only)) && !empty($sales_representative))
    <div class="col-md-3 hide">
        <div class="form-group">
            {!! Form::label('created_by',  __('report.user') . ':') !!}
            {!! Form::select('created_by', $sales_representative, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
        </div>
    </div>
    @endif
    @if(empty($only) || in_array('sales_cmsn_agnt', $only))
    @if(!empty($is_cmsn_agent_enabled))
        <div class="col-md-3 hide">
            <div class="form-group">
                {!! Form::label('sales_cmsn_agnt',  __('lang_v1.sales_commission_agent') . ':') !!}
                {!! Form::select('sales_cmsn_agnt', $commission_agents, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
            </div>
        </div>
    @endif
    @endif
    @if(empty($only) || in_array('service_staffs', $only))
    @if(!empty($service_staffs))
        <div class="col-md-3 hide">
            <div class="form-group">
                {!! Form::label('service_staffs', __('restaurant.service_staff') . ':') !!}
                {!! Form::select('service_staffs', $service_staffs, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
    @endif
    @endif
    @if(!empty($shipping_statuses))
        <div class="col-md-3 hide">
            <div class="form-group">
                {!! Form::label('shipping_status', __('lang_v1.shipping_status') . ':') !!}
                {!! Form::select('shipping_status', $shipping_statuses, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
    @endif
    @if(empty($only))
    @if(!empty($categories))
    <div class="col-md-3 hide">
        <div class="form-group">
            {!! Form::label('product_category',  'Category:') !!}
            <select class="select2" id="product_category" style="width:100% !important">
                <option value="all">@lang('lang_v1.all_category')</option>

                @foreach($categories as $category)
                    <option value="{{$category['id']}}">{{$category['name']}}</option>
                @endforeach

                @foreach($categories as $category)
                    @if(!empty($category['sub_categories']))
                        <optgroup label="{{$category['name']}}">
                            @foreach($category['sub_categories'] as $sc)
                                <i class="fa fa-minus"></i> <option value="{{$sc['id']}}">{{$sc['name']}}</option>
                            @endforeach
                        </optgroup>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
    @endif
    @endif
    @if(!empty($tax_rates))
        <div class="col-md-3 hide">
            <div class="form-group">
                {!! Form::label('tax_rates', 'Tax Rates:') !!}
                {!! Form::select('tax_rates', $tax_rates, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
    @endif
    @if(empty($only) || in_array('only_subscriptions', $only))
    <div class="col-md-3 hide">
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <br>
                {!! Form::checkbox('only_subscriptions', 1, false,
                [ 'class' => 'input-icheck', 'id' => 'only_subscriptions']); !!} {{ __('lang_v1.subscriptions') }}
                </label>
            </div>
        </div>
    </div>
    @endif
        @if($is_woocommerce)
            <div class="col-md-3 hide">
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                          {!! Form::checkbox('only_woocommerce_sells', 1, false,
                          [ 'class' => 'input-icheck', 'id' => 'synced_from_woocommerce']); !!} {{ __('lang_v1.synced_from_woocommerce') }}
                        </label>
                    </div>
                </div>
            </div>
        @endif
    @endcomponent
    @component('components.widget', ['class' => 'box-primary', 'title' => ''])
        @if(auth()->user()->can('direct_sell.access') ||  auth()->user()->can('view_own_sell_only'))
        @php
            $custom_labels = json_decode(session('business.custom_labels'), true);
         @endphp

            <table class="table table-bordered table-striped" id="sell_table1" style="width: 100% !important;">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Invoice Total</th>
                        <th>Item Total</th>
                        <th>Discount</th>
                        <th>Cost</th>
                        <th>GP</th>
                        <th>Profit</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr class="bg-gray font-17 footer-total text-center">
                        <td colspan="3"></td>
                        <td class="footer_sale_total"></td>
                        <td class="footer_sale_total_raw"></td>
                        <td class="footer_total_discount"></td>
                        <td class="footer_total_cost"></td>
                        <td class="footer_gross_profit"></td>
                        <td class="footer_profit_total"></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    @endcomponent
</section>
<!-- /.content -->
<div class="modal fade payment_modal pay_due_modal" tabindex="-1" role="dialog" data-backdrop="static"
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" data-backdrop="static"
    aria-labelledby="gridSystemModalLabel">
</div>

<!-- This will be printed -->
<!-- <section class="invoice print_section" id="receipt_section">
</section> -->

@stop

@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
    var pageLength = -1;
    var is_loaded = 1;
    dateRangeSettings.startDate = new Date();
    dateRangeSettings.endDate = new Date();

    ranges['Last Three Months'] = [
        moment().add(-3, 'month'),
        moment().add(1, 'day')
    ];

    //Date range as a button
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            if(pageLength == -1 && is_loaded == 1){
                is_loaded = 0;
                pageLength = 25;
                sell_table.page.len( 25 ).draw();
            }
            sell_table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        if(pageLength == -1 && is_loaded == 1){
            is_loaded = 0;
            pageLength = 25;
            sell_table.page.len( 25 ).draw();
        }
        sell_table.ajax.reload();
    });

    sell_table = $('#sell_table1').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[1, 'desc']],
        iDisplayLength:pageLength,
        "ajax": {
            "url": "/reports/store-gp",
            "data": function ( d ) {
                if($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
                d.is_direct_sale = 1;
                // d.length = pageLength;
                d.location_id = $('#sell_list_filter_location_id').val();
                d.customer_id = $('#sell_list_filter_customer_id').val();
                d.payment_status = $('#sell_list_filter_payment_status').val();
                d.created_by = $('#created_by').val();
                d.sales_cmsn_agnt = $('#sales_cmsn_agnt').val();
                d.tax_rates = $('#tax_rates').val();
                d.service_staffs = $('#service_staffs').val();

                if($('#shipping_status').length) {
                    d.shipping_status = $('#shipping_status').val();
                }

                @if($is_woocommerce)
                    if($('#synced_from_woocommerce').is(':checked')) {
                        d.only_woocommerce_sells = 1;
                    }
                @endif

                if($('#only_subscriptions').is(':checked')) {
                    d.only_subscriptions = 1;
                }

                if($('#product_category').length) {
                    d.product_category = $('#product_category').val();;
                }

                d = __datatable_ajax_callback(d);
            }
        },
        scrollY:        "75vh",
        scrollX:        true,
        scrollCollapse: true,
        columns: [
            { data: 'name', name: 'contacts.name'},
            { data: 'invoice_no', name: 'invoice_no'},
            { data: 'transaction_date', name: 'transaction_date'  },
            { data: 'final_total', name: 'final_total'},
            { data: 'sell_total', name: 'sell_total'},
            { data: 'discount_amount', name: 'discount_amount', "searchable": false},
            { data: 'total_cost', name: 'total_cost', "searchable": false},
            { data: 'total_gp', name: 'total_gp', "searchable": false },
            { data: 'profit_amt', name: 'profit_amt', "searchable": false },
        ],
        "fnDrawCallback": function (oSettings) {
            __currency_convert_recursively($('#sell_table1'));
        },
        "footerCallback": function ( row, data, start, end, display ) {
            var row_count = sell_table.data().count();
            var footer_gross_total = 0;
            var footer_sale_total = 0;
            var footer_total_paid = 0;
            var footer_total_remaining = 0;
            var footer_total_sell_return_due = 0;
            var footer_total_cost = 0;
            var footer_total_discount = 0;
            var footer_sale_total_raw = 0;
            var footer_profit_total = 0;
            for (var r in data){
                footer_sale_total += $(data[r].final_total).data('orig-value') ? parseFloat($(data[r].final_total).data('orig-value')) : 0;
                footer_gross_total += $(data[r].total_gp).data('orig-value') ? parseFloat($(data[r].total_gp).data('orig-value')) : 0;
                footer_total_paid += $(data[r].total_paid).data('orig-value') ? parseFloat($(data[r].total_paid).data('orig-value')) : 0;
                footer_total_remaining += $(data[r].total_remaining).data('orig-value') ? parseFloat($(data[r].total_remaining).data('orig-value')) : 0;
                footer_total_sell_return_due += $(data[r].return_due).data('orig-value') ? parseFloat($(data[r].return_due).data('orig-value')) : 0;

                footer_total_cost += parseFloat($(data[r].total_cost).data('orig-value')) ?? 0;
                footer_total_discount += parseFloat($(data[r].discount_amount).data('orig-value')) ?? 0;

                footer_sale_total_raw += parseFloat($(data[r].sell_total).data('orig-value')) ?? 0;
                footer_profit_total += parseFloat($(data[r].profit_amt).data('orig-value')) ?? 0;
            }
            //new gp
            // var new_gp = ((footer_sale_total-footer_total_cost-footer_total_discount)*100)/footer_sale_total;
            var new_gp = (footer_profit_total*100)/(footer_sale_total_raw-footer_total_discount);

            var total_gp = footer_gross_total.toFixed(2);
            var gross_profit_avg = total_gp / row_count;
            var gross_profit = gross_profit_avg.toFixed(2);
            var grossprofit = gross_profit +'%';

            $('.footer_sale_total_raw').html(__currency_trans_from_en(footer_sale_total_raw));
            $('.footer_profit_total').html(__currency_trans_from_en(footer_profit_total));

            $('.footer_total_sell_return_due').html(__currency_trans_from_en(footer_total_sell_return_due));
            $('.footer_total_remaining').html(__currency_trans_from_en(footer_total_remaining));
            $('.footer_total_paid').html(__currency_trans_from_en(footer_total_paid));
            $('.footer_sale_total').html(__currency_trans_from_en(footer_sale_total));
            @can('show_gp')
                // $('.footer_gross_profit').html((grossprofit));

                //new gp
                $('.footer_gross_profit').html((new_gp.toFixed(2)+"%"));
            @endcan
            $('.footer_total_cost').html(__currency_trans_from_en(footer_total_cost));
            $('.footer_total_discount').html(__currency_trans_from_en(footer_total_discount));

            $('.footer_payment_status_count').html(__count_status(data, 'payment_status'));
            // $('.service_type_count').html(__count_status(data, 'types_of_service_name'));
            $('.payment_method_count').html(__count_status(data, 'payment_methods'));
        },
        createdRow: function( row, data, dataIndex ) {
            $( row ).find('td:eq(6)').attr('class', 'clickable_td');
        }
    });

    $(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs, #shipping_status, #tax_rates',  function() {
        if(pageLength == -1 && is_loaded == 1){
            is_loaded = 0;
            pageLength = 25;
            sell_table.page.len( 25 ).draw();
        }
        sell_table.ajax.reload();
    });
    @if($is_woocommerce)
        $('#synced_from_woocommerce').on('ifChanged', function(event){
            if(pageLength == -1 && is_loaded == 1){
                is_loaded = 0;
                pageLength = 25;
                sell_table.page.len( 25 ).draw();
            }
            sell_table.ajax.reload();
        });
    @endif

    $('#only_subscriptions').on('ifChanged', function(event){
        if(pageLength == -1 && is_loaded == 1){
            is_loaded = 0;
            pageLength = 25;
            sell_table.page.len( 25 ).draw();
        }
        sell_table.ajax.reload();
    });
    $('#product_category').on('change', function(event){
        if(pageLength == -1 && is_loaded == 1){
            is_loaded = 0;
            pageLength = 25;
            sell_table.page.len( 25 ).draw();
        }
        sell_table.ajax.reload();
    });
});
</script>
@endsection