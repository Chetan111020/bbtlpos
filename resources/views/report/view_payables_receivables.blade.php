@extends('layouts.app')
@section('title', 'Payables and Receivables ' . __('report.reports'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Payables and receivables {{ __('report.reports')}}</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row" style="display: none;">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])

                 <div class="col-md-3">
                    <div class="form-group">
                         {!! Form::label('from_date', __('lang_v1.date') . ':') !!}
                         {!! Form::text('from_date', null , ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'date_range', 'readonly']); !!}

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
                        <a href="#all_payables" data-toggle="tab" aria-expanded="true">Payables</a>
                    </li>

                    <li>
                        <a href="#all_receivables" data-toggle="tab" aria-expanded="true">Receivables</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="all_payables">
                        <div class="row">
                            <div class="col-md-12">
                                @component('components.filters', ['title' => __('report.filters')])
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {!! Form::label('id', 'Supplier Name' . ':') !!}
                                            {!! Form::select('id', $suppliers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'supplier_id']); !!}
                                        </div>
                                    </div>
                                @endcomponent
                            </div>
                        </div>
                        @include('report.partials.payables_table')
                    </div>

                    <div class="tab-pane" id="all_receivables">
                        <div class="row">
                            <div class="col-md-12">
                                @component('components.filters', ['title' => __('report.filters')])
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {!! Form::label('id', __( 'lang_v1.customer' ) . ':') !!}
                                            {!! Form::select('id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'contact_id']); !!}
                                        </div>
                                    </div>
                                @endcomponent
                            </div>
                        </div>
                        @include('report.partials.receivables_table')
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
            if($('#date_range').length == 1){
                $('#date_range').daterangepicker(
                    dateRangeSettings,
                    function (start, end) {
                        $('#date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                        payables_report_tbl.ajax.reload();
                        receivables_report_tbl.ajax.reload();
                    }
                );

                $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    payables_report_tbl.ajax.reload();
                    receivables_report_tbl.ajax.reload();
                });
            }

            payables_report_tbl = $('#payables_report_tbl').DataTable({
                processing: true,
                serverSide: false,
                "ajax": {
                    "url": '/reports/payables-show',
                    "data": function(d) {
                        d.supplier_id = $('#supplier_id').val();
                        d.start_date = $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.end_date = $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    { data: 'name', name: 'contacts.name' },
                    { data: 'mobile', name: 'contacts.mobile' },
                    { data: 'email', name: 'contacts.email' },
                    { data: 'open_balance', "searchable": false},
                    { data: 'last_order_date', orderable: false, "searchable": false},
                ],
                "fnDrawCallback": function(oSettings) {
                    var total_open_balance = sum_table_col($('#payables_report_tbl'), 'open_balance');
                    $('#footer_cm_open_balance_total_payables').text(total_open_balance);
                    __currency_convert_recursively($('#payables_report_tbl'));
                },
            });


            receivables_report_tbl = $('#receivables_report_tbl').DataTable({
                processing: true,
                serverSide: false,
                "ajax": {
                    "url": '/reports/receivables-show',
                    "data": function(d) {
                        d.contact_id = $('#contact_id').val();
                        d.start_date = $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.end_date = $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    { data: 'name', name: 'contacts.name' },
                    { data: 'mobile', name: 'contacts.mobile' },
                    { data: 'email', name: 'contacts.email' },
                    { data: 'open_balance', "searchable": false},
                    { data: 'last_order_date', orderable: false, "searchable": false},
                ],
                "fnDrawCallback": function(oSettings) {
                    var total_open_balance = sum_table_col($('#receivables_report_tbl'), 'open_balance');
                    $('#footer_cm_open_balance_total_receivables').text(total_open_balance);
                    __currency_convert_recursively($('#receivables_report_tbl'));
                },
            });

            //if($('#payables_report_tbl').length != 0){
                $('#supplier_id').change(function() {
                    payables_report_tbl.ajax.reload();
                });
                $('#contact_id').change(function() {
                    receivables_report_tbl.ajax.reload();
                });
           // }

        });
    </script>
@endsection