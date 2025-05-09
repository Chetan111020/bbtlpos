@extends('layouts.app')
@section('title', __( 'CT VAPE Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'CT VAPE Report' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-4"></div>
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
                    <table class="table table-bordered table-striped" id="vape_report_table">
                        <thead>
                        <tr>
                            <td>Date</td>
                            <td>Invoice No</td>
                            <td>Name</td>
                            <td>Address</td>
                            <td>TAX ID</td>
                            <td>Close System - Amount in ML</td>
                            <td>Close System - Collected Tax</td>
                            <td>Open System - Amount</td>
                            <td>Open System - Collected Tax</td>
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
            item_inventory_report = $('#vape_report_table').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,

                ajax: {
                    url: '{{ url("reports/ct-vape-transaction-report-list") }}',
                    type: "POST",
                    data: function (d) {
                        d.date_filter = date_filter;
                        d.customer_id = customer_id;
                    },
                },
                columns: [
                    { data: 'sale_date', name: 'sale_date'},
                    { data: 'invoice_no', name: 'invoice_no'},
                    { data: 'name', name: 'name'},
                    { data: 'address', name: 'address'},
                    //{ data: 'tobacco_license_no', name: 'tobacco_license_no'},
                    { data: 'tax_id', name: 'tax_id'},
                    { data: 'close_total', name: 'close_total'},
                    { data: 'close_total_tax', name: 'close_total_tax'},
                    { data: 'open_total', name: 'open_total'},
                    { data: 'open_total_tax', name: 'open_total_tax'},
                ],
                "fnDrawCallback": function (oSettings) {
                    // console.log(data: 'costing');
                    /*$('#footer_total_price').text(sum_table_col($('#vape_report_table'), 'price'));
                    $('#footer_total_costing').text(sum_table_col($('#vape_report_table'), 'costing'));*/
                    __currency_convert_recursively($('#vape_report_table'));
                }
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