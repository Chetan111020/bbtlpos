@extends('layouts.app')
@section('title', __( 'Packed Order Items Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'Packed Order Items Report' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="form-group" style="display: none;">
                    &nbsp;  
                </div>
            </div>
            <div class="col-md-4">
                <input type="hidden" id="date" name="date" value="">
                <div class="form-group">
                    {!! Form::label('transaction_date_filter', __('report.date_range') . ':') !!}
                    {!! Form::text('transaction_date_filter', 
                        @format_date('first day of this month') . ' ~ ' . @format_date('last day of this month'), 
                        ['placeholder' => __('lang_v1.select_a_date_range'), 
                        'class' => 'form-control', 'id' => 'transaction_date_filter', 'readonly']); 
                    !!}

                    {{-- {!! Form::label('transaction_date_filter', __('report.date_range') . ':') !!}
                    {!! Form::text('transaction_date_filter', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!} --}}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="packed_order_items_report_table">
                        <thead>
                        <tr>
                            <td>Date</td>
                            <td>Product Name</td>
                            <td>Total Count</td>
                            <td>Invoice No.</td>
                            <td>Created By</td>
                            <td>Status</td>
                        </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
@endsection

@section('javascript')
    <script type="text/javascript">
        $('#transaction_date_filter').daterangepicker({
            ranges: ranges,
            autoUpdateInput: true,
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            locale: {
                format: moment_date_format
            }
        });
        

        $(function () {
            item_inventory_report='';
            var date_filter = $('#transaction_date_filter').val();
            getInventoryItems(date_filter);
        });

        function getInventoryItems(date_filter) {
            if(date_filter){
                var date_filter = date_filter;
            } else {
                var date_filter = '';
            }
            
            item_inventory_report = $('#packed_order_items_report_table').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,

                ajax: {
                    url: '{{ url("reports/packed-order-items-report-list") }}',
                    type: "POST",
                    data: function (d) {
                        d.date_filter = date_filter;
                    },
                },
                columns: [
                    { data: 'scanned_date', name: 'scanned_date'},
                    { data: 'product_name', name: 'product_name'},
                    { data: 'total_count', name: 'total_count'},
                    { data: 'invoice_no', name: 'invoice_no'},
                    { data: 'created_by', name: 'created_by'},
                    { data: 'scan_status', name: 'scan_status'}
                ]
            });
        }

        $('#transaction_date_filter').change(function () {
            var date_filter = $('#transaction_date_filter').val();
            item_inventory_report.ajax.reload();
            getInventoryItems(date_filter);
        });
    </script>
@endsection