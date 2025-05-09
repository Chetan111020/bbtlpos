@extends('layouts.app')
@section('title', __( 'Deleted Transaction Payments Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'Deleted Transaction Payments Report' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('contact_filter_supplier_id', __('Supplier') . ':') !!}
                    {!! Form::select('contact_filter_supplier_id', $suppliers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}      
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('contact_filter_customer_id', __('contact.customer') . ':') !!}
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
                    <table class="table table-bordered table-striped" id="deleted_transaction_payments_report_table">
                        <thead>
                        <tr>
                            <td>Deleted at</td>
                            <td>Paid on</td>
                            <td>Reference No</td>
                            <td>Contact Name</td>
                            <td>Contact Type</td>
                            <td>Amount</td>
                            <td>Reason</td>
                            <td>Payment Method</td>
                            <td>Payment Note</td>
                            <td>Payment For</td>
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
            getInventoryItems(date_filter,'','');
        });

        function getInventoryItems(date_filter,customer_id,supplier_id) {
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
            if(supplier_id){
                var supplier_id = supplier_id;
            } else {
                var supplier_id = '';
            }
            // alert(customer_id);
            item_inventory_report = $('#deleted_transaction_payments_report_table').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,

                ajax: {
                    url: '{{ url("reports/deleted-transaction-payments-report-list") }}',
                    type: "POST",
                    data: function (d) {
                        d.date_filter = date_filter;
                        d.customer_id = customer_id;
                        d.supplier_id = supplier_id;
                    },
                },
                columns: [
                    { data: 'deleted_at', name: 'deleted_at'},
                    { data: 'payment_date', name: 'payment_date'},
                    { data: 'ref_no', name: 'ref_no'},
                    { data: 'type', name: 'type'},
                    { data: 'name', name: 'name'},
                    { data: 'amount', name: 'amount'},
                    { data: 'reason', name: 'reason' , orderable: false, searchable: false},
                    { data: 'method', name: 'method'},
                    { data: 'note', name: 'note' , orderable: false , searchable: false},
                    { data: 'invoice_no', name: 'invoice_no'},
                    { data: 'created_by', name: 'created_by'},
                    { data: 'deleted_by', name: 'deleted_by'},
                ]
            });
        }

        $('#transaction_date_filter, #contact_filter_customer_id, #contact_filter_supplier_id').change(function () {
            var date_filter = $('#transaction_date_filter').val();
            var customer_id = $('#contact_filter_customer_id').val();
            var supplier_id = $('#contact_filter_supplier_id').val();
            item_inventory_report.ajax.reload();
            getInventoryItems(date_filter,customer_id,supplier_id);
        });
    </script>
@endsection