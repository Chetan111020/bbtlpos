@extends('layouts.app')
@section('content')
@section('title', 'Order Delivery')
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="row">
            <div class="col-md-3">
                <input type="hidden" id="date" name="date" value="">
                <div class="form-group">
                    {!! Form::label('all_date_filter', __('report.date_range') . ':') !!}
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
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('filter_assigned_to', __('report.user') . ':') !!}
                    {!! Form::select('filter_assigned_to', $users, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                    ]) !!}
                </div>
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __('Order Delivery')])
        @if (auth()->user()->can('delivery.create'))
            @slot('tool')
                <div class="box-tools">
                    <a href="{{ action('OrderDeliveryController@create') }}" class="btn btn-primary" role="button">+ Add
                        New</a>
                </div>
            @endslot
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" style="width: 100%;" id="delivery_table">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>Added on</th>
                        <th>Image</th>
                        <th>Signature</th>
                        <th>Invoice No</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Payment Amount</th>
                        <th>Created By</th>

                    </tr>
                </thead>

                <tbody></tbody>
                <tfoot>
                    <tr class="bg-gray font-17 footer-total text-center">
                       <th colspan="7"><span class="pull-right">Total:</span></th>
                        <th colspan="2" class="footer_total_amount"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <!--<div class="modal fade" id="EditModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"-->
        <!--    aria-hidden="true">-->
        <!--</div>-->
        <div class="modal fade" id="ViewModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
        </div>
    @endcomponent
</section>
@stop

@section('javascript')
<script type="text/javascript">




    $(document).on('click', 'a.view-modal', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'html',
            success: function(result) {
                $('#ViewModal')
                    .html(result)
                    .modal('show');
            },
        });
    });

    // Handle view location button click
    $(document).on('click', '.view-location', function(e) {
        e.stopPropagation(); // Prevent row click event from firing

        var lat = $(this).data('lat');
        var lng = $(this).data('lng');

        // Open the Google Maps link in a new tab
        window.open('https://www.google.com/maps/search/?api=1&query=' + lat + ',' + lng, '_blank');
    });

    var start, end;
    $(document).ready(function(e) {
        // Initialize the date picker
        var dateFilter = $("#all_date_filter");
        dateFilter.daterangepicker({
            ranges: ranges,
            autoUpdateInput: true,
            startDate: moment().startOf("days").subtract(6, 'days'),
            endDate: moment().endOf("days"),
            locale: {
                format: moment_date_format,
            },
        });


        var deliveryTable = $('#delivery_table').DataTable({
             processing: true,
            serverSide: true,
            scrollY: "500px",
            scrollX: true,
            aaSorting: [[1, 'desc']],
            scrollCollapse: true,
            stateSave: false,
            "ajax": {
                "url": "/order-delivery",
                "data": function(d) {
                    d.assigned_to = $('#filter_assigned_to').val();
                    if (dateFilter.val()) {
                        var startDate = dateFilter.data('daterangepicker').startDate.format(
                            "YYYY-MM-DD");
                        var endDate = dateFilter.data('daterangepicker').endDate.format(
                            "YYYY-MM-DD");
                        d.start_date = startDate;
                        d.end_date = endDate;
                    }
                    // d = __datatable_ajax_callback(d);
                }
            },
            columns: [{
                    data: 'action',
                    name: 'action'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'img',
                    name: 'img'
                },
                {
                    data: 'signature',
                    name: 'signature'
                },
                {
                    data: 'invoice_no',
                    name: 'invoice_no'
                },
                {
                    data: 'customer_name',
                    name: 'customer_name'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'payment_amount',
                    name: 'payment_amount'
                },
                {
                    data: 'user_id',
                    name: 'user_id'
                },
            ],
            fnDrawCallback: function(oSettings) {
                __currency_convert_recursively($('#delivery_table'));
            },

            footerCallback: function(row, data, start, end, display) {
                var total_amount = 0;

                for (var r in data) {
                    total_amount += $(data[r].payment_amount).data('orig-value') ? parseFloat($(
                        data[r].payment_amount).data('orig-value')) : 0;
                }

                var total = total_amount.toFixed(2);

                $('.footer_total_amount').html(__currency_trans_from_en(total));
            },

        });


        dateFilter.on("apply.daterangepicker", function(ev, picker) {
            $(this).val(
                picker.startDate.format(moment_date_format) +
                " ~ " +
                picker.endDate.format(moment_date_format)
            );


            deliveryTable.ajax.url("/order-delivery?start_date=" + picker.startDate.format(
                    moment_date_format) + "&end_date=" + picker.endDate.format(moment_date_format))
                .load();
        });

        dateFilter.on("cancel.daterangepicker", function(ev, picker) {
            $(this).val("");
        });


        $(document).on('change', '#filter_assigned_to, #all_date_filter', function() {
            deliveryTable.ajax.reload();
        });


    });
</script>
@endsection
