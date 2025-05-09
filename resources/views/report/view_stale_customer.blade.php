@extends('layouts.app')
@section('title', 'Stale '. __('report.customer') . '  ' . __('report.reports'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Stale {{ __('report.customer')}} {{ __('report.reports')}}</h1>
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
                        {!! Form::label('sr_id',  __('Sales Representative') . ':') !!}
                        {!! Form::select('sr_id', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'sr_id', 'placeholder' => __('report.all_users')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('id', __( 'lang_v1.customer' ) . ':') !!}
                        {!! Form::select('id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'contact_id']); !!}
                    </div>
                </div>

                 <div class="col-md-3" style="display:none;">
                    <div class="form-group">
                         {!! Form::label('from_date', __('lang_v1.date') . ':') !!}
                         {!! Form::text('from_date', null , ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'stale_report_date_range', 'readonly']); !!}

                    </div>
                </div>

            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="stale_customer_report_tbl">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.customer')</th>
                            <th>@lang('lang_v1.mobile_number')</th>
                            <th>Address</th>
                            <th>@lang('report.total_sell')</th>
                            <th>Sales Representative</th>
                            <th>@lang('lang_v1.last_order_date')</th>
                            <th>@lang('lang_v1.days')</th>
                            <th>Due</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 footer-total text-center">
                            <td><strong>@lang('sale.total'):</strong></td>
                            <td></td>
                            <td></td>
                            <td><span class="display_currency" id="footer_total_invoice" data-currency_symbol ="true"></span></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><span class="display_currency" id="footer_total_due" data-currency_symbol ="true"></span></td>
                        </tr>
                    </tfoot>
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
        $(document).ready(function(){
            if($('#stale_report_date_range').length == 1){
                $('#stale_report_date_range').daterangepicker(
                    dateRangeSettings,
                    function (start, end) {
                        $('#stale_report_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                        stale_customer_report_tbl.ajax.reload();
                    }
                );

                $('#stale_report_date_range').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    stale_customer_report_tbl.ajax.reload();
                });
            }

             stale_customer_report_tbl = $('#stale_customer_report_tbl').DataTable({
                processing: true,
                // serverSide: true,
                ajax: {
                    // url: '/reports/stale-customer',
                    url: '/reports/followup-stale-customer',
                    data: function(d) {
                        d.sr_id = $('#sr_id').val();
                        d.contact_id = $('#contact_id').val();
                        d.start_date = $('#stale_report_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.end_date = $('#stale_report_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'mobile', name: 'mobile' },
                    { data: 'address', name: 'address'},
                    { data: 'total_sell_return', name: 'total_sell_return' },
                    { data: 'user_name', name: 'user_name' },
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'days', name: 'days' },
                    { data: 'due', name: 'due' },
                ],
                fnDrawCallback: function(oSettings) {

                    var total_sell_return = sum_table_col($('#stale_customer_report_tbl'), 'total_invoice');
                    $('#footer_total_invoice').text(total_sell_return);

                     var total_due = sum_table_col($('#stale_customer_report_tbl'), 'total_due');
                    $('#footer_total_due').text(total_due);

                    __currency_convert_recursively($('#stale_customer_report_tbl'));
                },
            });

            if($('#stale_customer_report_tbl').length != 0){
                $('#sr_id, #contact_id, #stale_report_date_range').change(function() {
                    stale_customer_report_tbl.ajax.reload();
                });
            }

        })
    </script>
@endsection


