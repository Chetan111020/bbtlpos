@extends('layouts.app')
@section('title', __( 'Deleted Stock Adjustments Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'Deleted Stock Adjustments Report' )
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
                    <table class="table table-bordered table-striped" id="deleted_stock_adjustments_report_table">
                        <thead>
                        <tr>
                            <td>Transaction Date</td>
                            <td>Reference No</td>
                            <td>Adjustment Type</td>
                            <td>Total Amount</td>
                            <td>Total amount recovered</td>
                            <td>Reason</td>
                            <td>Notes</td>
                            <td>Created By</td>
                            <td>Deleted By</td>
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
            
            item_inventory_report = $('#deleted_stock_adjustments_report_table').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,

                ajax: {
                    url: '{{ url("reports/deleted-stock-adjustments-report-list") }}',
                    type: "POST",
                    data: function (d) {
                        d.date_filter = date_filter;
                    },
                },
                columns: [
                    { data: 'sale_date', name: 'sale_date'},
                    { data: 'invoice_no', name: 'invoice_no'},
                    { data: 'adjustment_type', name: 'adjustment_type'},
                    { data: 'final_total', name: 'final_total'},
                    { data: 'total_amount_recovered', name: 'total_amount_recovered'},
                    { data: 'reason', name: 'reason' , searchable: false},
                    { data: 'additional_notes', name: 'additional_notes' , searchable: false},
                    { data: 'created_by', name: 'created_by'},
                    { data: 'deleted_by', name: 'deleted_by'},
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