@extends('layouts.app')
@section('title', 'Collected Payment' . __('report.reports'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Collected Payments {{ __('report.reports')}}</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])

                 <div class="col-md-3">
                    <div class="form-group">
                         {!! Form::label('from_date', __('Date Range') . ':') !!}
                         {!! Form::text('from_date', null , ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'collected_payment_date_range', 'readonly']); !!}

                    </div>
                </div>

            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#collected_payment_by_customers" data-toggle="tab" aria-expanded="true">By Customers</a>
                    </li>

                    <li>
                        <a href="#collected_payment_by_suppliers" data-toggle="tab" aria-expanded="true">By Suppliers</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="collected_payment_by_customers">
                        @include('report.partials.collected_payment_by_customers')
                    </div>

                    <div class="tab-pane" id="collected_payment_by_suppliers">
                        @include('report.partials.collected_payment_by_suppliers')
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function(){
            if($('#collected_payment_date_range').length == 1){
                $('#collected_payment_date_range').daterangepicker(
                    dateRangeSettings,
                    function (start, end) {
                        $('#collected_payment_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                        collected_payment_by_suppliers_tbl.ajax.reload();
                        collected_payment_by_customers_tbl.ajax.reload();
                    }
                );

                $('#collected_payment_date_range').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    collected_payment_by_suppliers_tbl.ajax.reload();
                    collected_payment_by_customers_tbl.ajax.reload();
                });
            }
                    
            collected_payment_by_customers_tbl = $('#collected_payment_by_customers_tbl').DataTable({
                processing: true,
                serverSide: true,
                "ajax": {
                    "url": '/reports/collected-payment-by-customers-show',
                    "data": function(d) {
                        d.sr_id = $('#sr_id').val();
                        d.contact_id = $('#contact_id').val();
                        d.start_date = $('#collected_payment_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.end_date = $('#collected_payment_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    { data: 'created_at', name: 't.created_at'},
                    { data: 'paid_on', name: 'transaction_payments.paid_on' },
                    // { data: 'type', name: 'contacts.type',"searchable": true },
                    { data: 'name', name: 'contacts.name' ,"searchable": true},
                    { data: 'invoice_no', name: 't.invoice_no',"searchable": true },
                    { data: 'method', name:'method',"searchable": false },
                    { data: 'amount', name: 'amount',"searchable": false },
                    { data: 'user_name', name:'user_name'},
                ],
                "fnDrawCallback": function(oSettings) {
                  
                    __currency_convert_recursively($('#collected_payment_by_customers_tbl'));

                },
                "footerCallback": function ( row, data, start, end, display ) {
            
                    var footer_final_total = 0;
                    for(var r in data){
                        // console.log(data[r].amount);
                        footer_final_total += $(data[r].amount).data('orig-value') ? parseFloat($(data[r].amount).data('orig-value')) : 0;
                    }

                    $('.footer_final_total').html(__currency_trans_from_en(footer_final_total));
                },
            });
            

            collected_payment_by_suppliers_tbl = $('#collected_payment_by_suppliers_tbl').DataTable({
                processing: true,
                serverSide: true,
                "ajax": {
                    "url": '/reports/collected-payment-by-suppliers-show',
                    "data": function(d) {
                        d.sr_id = $('#sr_id').val();
                        d.contact_id = $('#contact_id').val();
                        d.start_date = $('#collected_payment_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.end_date = $('#collected_payment_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    { data: 'created_at', name: 't.created_at'},
                    { data: 'paid_on', name: 'transaction_payments.paid_on' },
                    { data: 'name', name: 'contacts.name', "searchable": true },
                    { data: 'ref_no', name: 't.ref_no' ,"searchable": true},
                    { data: 'method', name:'method',"searchable": false },
                    { data: 'amount', name: 'transaction_payments.amount', "searchable": true},
                    { data:  'user_name', name:'user_name'},
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#collected_payment_by_suppliers_tbl'));

                },
                "footerCallback": function ( row, data, start, end, display ) {
                    var footer_final_total = 0;
                    for(var r in data){
                        footer_final_total += $(data[r].amount).data('orig-value') ? parseFloat($(data[r].amount).data('orig-value')) : 0;
                    }
                    $('.footer_final_total').html(__currency_trans_from_en(footer_final_total));
                },
            });

            //if($('#collected_payment_by_suppliers_tbl').length != 0){
                $('#sr_id, #contact_id, #collected_payment_date_range').change(function() {
                    collected_payment_by_suppliers_tbl.ajax.reload();
                    collected_payment_by_customers_tbl.ajax.reload();
                });
           // }
            
        });
    </script>
@endsection