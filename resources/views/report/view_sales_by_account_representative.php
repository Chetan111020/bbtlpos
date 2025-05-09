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
                        {!! Form::label('sr_id',  __('report.user') . ':') !!}
                        {!! Form::select('sr_id', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'sr_id', 'placeholder' => __('report.all_users')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('id', __( 'lang_v1.customer' ) . ':') !!}
                        {!! Form::select('id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'contact_id']); !!}
                    </div>
                </div>
                
                 <div class="col-md-3">
                    <div class="form-group">
                         {!! Form::label('from_date', __('lang_v1.date') . ':') !!}
                         {!! Form::text('from_date', null , ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'sales_by_account_representative_date_range', 'readonly']); !!}

                    </div>
                </div>

            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="sales_by_account_representative_report_tbl">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.customer')</th>
                            <th>@lang('lang_v1.created_at')</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 footer-total text-center">
                            <td><strong>@lang('sale.total'):</strong></td>
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
            if($('#sales_by_account_representative_date_range').length == 1){
                $('#sales_by_account_representative_date_range').daterangepicker(
                    dateRangeSettings,
                    function (start, end) {
                        $('#sales_by_account_representative_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                        sales_by_account_representative_report_tbl.ajax.reload();
                    }
                );

                $('#sales_by_account_representative_date_range').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    sales_by_account_representative_report_tbl.ajax.reload();
                });
            }
            
             sales_by_account_representative_report_tbl = $('#sales_by_account_representative_report_tbl').DataTable({
                processing: true,
                // serverSide: true,
                ajax: {
                    url: '/reports/stale-customer',
                    data: function(d) {
                        d.sr_id = $('#sr_id').val();
                        d.contact_id = $('#contact_id').val();
                        d.start_date = $('#sales_by_account_representative_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.end_date = $('#sales_by_account_representative_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'created_at', name: 'created_at' },
                  
                ],
                fnDrawCallback: function(oSettings) {
                  
                    // var total_sell_return = sum_table_col($('#sales_by_account_representative_report_tbl'), 'total_invoice');
                    // $('#footer_total_invoice').text(total_sell_return);
        
                    // __currency_convert_recursively($('#sales_by_account_representative_report_tbl'));
                },
            });
            
            if($('#sales_by_account_representative_report_tbl').length != 0){
                $('#sr_id, #contact_id, #sales_by_account_representative_date_range').change(function() {
                    sales_by_account_representative_report_tbl.ajax.reload();
                });
            }
            
        })
    </script>
@endsection


	