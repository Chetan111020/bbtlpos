@extends('layouts.app')
@section('title', __( 'Deleted Expenses Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'Deleted Expenses Report' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('contact_filter_customer_id', __('contact.contact') . ':') !!}
                    {!! Form::select('contact_filter_customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}      
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
                    <table class="table table-bordered table-striped" id="deleted_expenses_report_table">
                        <thead>
                        <tr>
                            <td>Transaction Date</td>
                            <td>Reference No</td>
                            <td>Recurring Details</td>
                            <td>Expense Category</td>
                            <td>Contact</td>
                            <td>Reason</td>
                            <td>Payment Status</td>
                            <td>Total Amount</td>
                            <td>Amount Paid</td>
                            <td>Tax</td>
                            <td>Expense for</td>
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
            getInventoryItems(date_filter,'');
        });

        function getInventoryItems(date_filter,customer_id) {
            if(date_filter){
                var date_filter = date_filter;
            } else {
                var date_filter = '';
            }
            if(customer_id){
                var customer_id = customer_id;
            } else {
                var customer_id = '';
            }
            // alert(customer_id);
            item_inventory_report = $('#deleted_expenses_report_table').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,

                ajax: {
                    url: '{{ url("reports/deleted-expenses-report-list") }}',
                    type: "POST",
                    data: function (d) {
                        d.date_filter = date_filter;
                        d.customer_id = customer_id;
                    },
                },
                columns: [
                    { data: 'sale_date', name: 'sale_date'},
                    { data: 'invoice_no', name: 'invoice_no'},
                    { data: 'recur_details', name: 'recur_details',searchable: false},
                    { data: 'category', name: 'category'},
                    { data: 'name', name: 'name'},
                    { data: 'reason', name: 'reason' , searchable: false},
                    { data: 'payment_status', name: 'payment_status'},
                    { data: 'final_total', name: 'final_total'},
                    { data: 'amount_paid', name: 'amount_paid'},
                    { data: 'tax', name: 'tax'},
                    { data: 'expense_for', name: 'expense_for'},
                    { data: 'created_by', name: 'created_by'},
                    { data: 'deleted_by', name: 'deleted_by'},
                ]
            });
        }

        $('#transaction_date_filter, #contact_filter_customer_id').change(function () {
            var date_filter = $('#transaction_date_filter').val();
            var customer_id = $('#contact_filter_customer_id').val();
            item_inventory_report.ajax.reload();
            getInventoryItems(date_filter,customer_id);
        });
    </script>
@endsection