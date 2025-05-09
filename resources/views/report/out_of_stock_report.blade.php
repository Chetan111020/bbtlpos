@extends('layouts.app')
@section('title', __( 'report.out_of_stock_report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'report.out_of_stock_report' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-2 col-xs-6">
            <div class="form-group ">
                <div class="input-group">
                  <button type="button" class="btn btn-primary" id="out_of_stock_date_filter">
                    <span class="pull-left">
                      <i class="fa fa-calendar"></i> {{ __('filter_by_date') }}
                    </span>
                    <i class="fa fa-caret-down"></i>
                  </button>
                </div>
            </div>
        </div>
    </div>
    <br>

    <div class="row">
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="out_of_stock_table">
                        <thead>
                            <tr>
                                <th>@lang('messages.date')</th>
                                <th>@lang('messages.product_name')</th>
                                <th>Barcode</th>
                                <th>@lang('messages.unit_price')</th>
                                <th>@lang('messages.out_of_stock')</th>
                                <th>Stock Quantity</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')

    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>

    <script type="text/javascript">
        $(document).ready(function (){
            var today = stock_start = stock_end = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
            var yyyy = today.getFullYear();
            today = mm + '/' + dd + '/' + yyyy;
            stock_start = stock_end = yyyy + '-' + mm + '-' + dd;
            $('#out_of_stock_date_filter span').html(
                        today.format(moment_date_format) + ' ~ ' + today.format(moment_date_format)
                    );
            console.log("date"+stock_start);
                //Out Of Stock Report Table
                out_of_stock_table = $('#out_of_stock_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ordering: true,
                    order: [[3, 'desc']],
                    ajax: {
                        url: '/reports/out-of-stock-report',
                        method: 'GET',
                        data: function(d) {
                            d.start_date = stock_start;
                            d.end_date = stock_end;
                        },
                    },
                    columns: [
                        { data: 'transaction_date', name: 'transaction_date' },
                        { data: 'product_name', name: 'product_name' },
                        { data: 'sku', name: 'sku' },
                        { data: 'unit_price', name: 'unit_price' },
                        { data: 'outOfStock', name: 'outOfStock' },
                        { data: 'stock_qty', name: 'stock_qty' },

                    ],
                    fnDrawCallback: function(oSettings) {
                        __currency_convert_recursively($('#out_of_stock_table'));
                    },
                });

            //out-of-stock report
            if ($('#out_of_stock_date_filter').length == 1) {
                $('#out_of_stock_date_filter').daterangepicker(dateRangeSettings, function(start, end) {
                    $('#out_of_stock_date_filter span').html(
                        start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
                    );
                    console.log("inn");
                    stock_start = $('#out_of_stock_date_filter')
                            .data('daterangepicker')
                            .startDate.format('YYYY-MM-DD');
                    stock_end = $('#out_of_stock_date_filter')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');
                    out_of_stock_table.ajax.reload();
                });
                out_of_stock_table.ajax.reload();
            }
        });
    </script>
@endsection