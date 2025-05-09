@extends('layouts.app')
@section('title', 'Sales by Account Representative ' . __('report.reports'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Sales by Account Representative {{ __('report.reports')}}</h1>
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
						{!! Form::label('sr_id', __('report.sales_representative_name')) !!}
                        <select name="sr_id" id="sr_id" class="form-control select2" required="required">
							<option value="">ALL</option>
							@if(isset($users))
    							@foreach($users as $user)
    							<option value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
    							@endforeach
							@endif
						</select>
                        <!--{!! Form::label('sr_id',  __('report.sales_representative_name') . ':') !!}-->
                        <!--{!! Form::select('sr_id', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'sr_id', 'placeholder' => __('report.all_sales_representative')]); !!}-->
                    </div>
                </div>
                <!--<div class="col-md-3">-->
                <!--    <div class="form-group">-->
                <!--        {!! Form::label('id', __( 'lang_v1.customer' ) . ':') !!}-->
                <!--        {!! Form::select('id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'contact_id']); !!}-->
                <!--    </div>-->
                <!--</div>-->
                
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
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#all_sales_representative_sales" data-toggle="tab" aria-expanded="true">@lang('report.all_sales_representative_sales')</a>
                    </li>

                    <li>
                        <a href="#all_sales_representative_cerdit_memo" data-toggle="tab" aria-expanded="true">@lang('report.all_sales_representative_cerdit_memo')</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="all_sales_representative_sales">
                        @include('report.partials.sales_by_account_representative_table')
                    </div>

                    <div class="tab-pane" id="all_sales_representative_cerdit_memo">
                        @include('report.partials.credit_memo_by_account_representative_table')
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
            if($('#sales_by_account_representative_date_range').length == 1){
                $('#sales_by_account_representative_date_range').daterangepicker(
                    dateRangeSettings,
                    function (start, end) {
                        $('#sales_by_account_representative_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                        sales_by_account_representative_report_tbl.ajax.reload();
                        credit_memo_sales_by_account_representative_report_tbl.ajax.reload();
                    }
                );

                $('#sales_by_account_representative_date_range').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    sales_by_account_representative_report_tbl.ajax.reload();
                    credit_memo_sales_by_account_representative_report_tbl.ajax.reload();
                });
            }
            
sales_by_account_representative_report_tbl = $('#sales_by_account_representative_report_tbl').DataTable({
                processing: true,
                serverSide: true,
                "ajax": {
                    "url": '/reports/sales-by-account-representative-show',
                    "data": function(d) {
                        d.sr_id = $('#sr_id').val();
                        d.contact_id = $('#contact_id').val();
                        d.start_date = $('#sales_by_account_representative_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.end_date = $('#sales_by_account_representative_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    { data: 'name', name: 'contacts.name' },
                    { data: 'invoice_no', name: 't.invoice_no' },
                    { data: 'total_amount', name: 'total_amount' , "searchable": false},
                    { data: 'discount_amount', name: 'discount_amount', "searchable": false },
                    { data: 'shipping_charges', name: 'shipping_charges', "searchable": false },
                    { data: 'total_tax_amount', name: 'total_tax_amount', "searchable": false },
                    { data: 'total_juul', name: 'total_juul', "searchable": false },
                    { data: 'final_amount', name: 'final_amount', "searchable": false },
                    { data: 'final_total', name: 'final_total' , "searchable": false},
                    { data: 'total_gp', name: 'total_gp' , "searchable": false},
                    { data: 'total_remaining', name: 'total_remaining'},
                    { data: 'user_name', name: 'u.first_name' },
                    { data: 'created_at', name: 't.created_at' },
                ],
                "fnDrawCallback": function(oSettings) {
                  
                    // var total_sell_return = sum_table_col($('#sales_by_account_representative_report_tbl'), 'total_invoice');
                    // $('#footer_total_invoice').text(total_sell_return);
        
                    __currency_convert_recursively($('#sales_by_account_representative_report_tbl'));

                },
                "footerCallback": function ( row, data, start, end, display ) {
                    var row_count = sales_by_account_representative_report_tbl.data().count();
                    var footer_total_amount = 0;
                    var footer_discount_amount = 0;
                    var footer_final_total = 0;
                    var footer_total_remaining = 0;
                    var footer_gross_total = 0;
                    
                    var footer_shipping_charges_amount = 0;
                    var footer_tax_amount = 0;
                    var footer_juul_amount = 0;
                    var footer_deduction_amount = 0;
                    for(var r in data){
                        
                        footer_total_amount += $(data[r].total_amount).data('orig-value') ? parseFloat($(data[r].total_amount).data('orig-value')) : 0;

                        footer_discount_amount += $(data[r].discount_amount).data('orig-value') ? parseFloat($(data[r].discount_amount).data('orig-value')) : 0;
                        footer_final_total += $(data[r].final_total).data('orig-value') ? parseFloat($(data[r].final_total).data('orig-value')) : 0;

                        footer_total_remaining += $(data[r].total_remaining).data('orig-value') ? parseFloat($(data[r].total_remaining).data('orig-value')) : 0;

                        footer_gross_total += $(data[r].total_gp).data('orig-value') ? parseFloat($(data[r].total_gp).data('orig-value')) : 0;
                      
                        footer_shipping_charges_amount += $(data[r].shipping_charges).data('orig-value') ? parseFloat($(data[r].shipping_charges).data('orig-value')) : 0;
                        footer_tax_amount += $(data[r].total_tax_amount).data('orig-value') ? parseFloat($(data[r].total_tax_amount).data('orig-value')) : 0;
                        footer_juul_amount += $(data[r].total_juul).data('orig-value') ? parseFloat($(data[r].total_juul).data('orig-value')) : 0;
                        footer_deduction_amount += $(data[r].final_amount).data('orig-value') ? parseFloat($(data[r].final_amount).data('orig-value')) : 0;
                    }

                    var total_gp = footer_gross_total.toFixed(2);
                    var gross_profit_avg = total_gp / row_count;
                    var gross_profit = gross_profit_avg.toFixed(2);
                    if(gross_profit=="" || gross_profit=='NaN')
                    {
                        var grossprofit = '0.00' +'%';
                    }
                    else
                    {
                        var grossprofit = gross_profit +'%';
                    }
                    $('.footer_total_amount').html(__currency_trans_from_en(footer_total_amount));
                    $('.footer_discount_amount').html(__currency_trans_from_en(footer_discount_amount));
                    $('.footer_final_total').html(__currency_trans_from_en(footer_final_total));
                    $('.footer_total_remaining').html(__currency_trans_from_en(footer_total_remaining));
                    $('.footer_gross_profit').html((grossprofit));
                    
                    $('.footer_shipping_charges_amount').html(__currency_trans_from_en(footer_shipping_charges_amount));
                    $('.footer_tax_amount').html(__currency_trans_from_en(footer_tax_amount));
                    $('.footer_juul_amount').html(__currency_trans_from_en(footer_juul_amount));
                    $('.footer_deduction_amount').html(__currency_trans_from_en(footer_deduction_amount));
                },
            });
            

            credit_memo_sales_by_account_representative_report_tbl = $('#credit_memo_sales_by_account_representative_report_tbl').DataTable({
                processing: true,
                serverSide: true,
                "ajax": {
                    "url": '/reports/credit-memo-sales-by-account-representative-show',
                    "data": function(d) {
                        d.sr_id = $('#sr_id').val();
                        d.contact_id = $('#contact_id').val();
                        d.start_date = $('#sales_by_account_representative_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.end_date = $('#sales_by_account_representative_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    { data: 'name', name: 'contacts.name' },
                    { data: 'invoice_no', name: 't.invoice_no' },
                    { data: 'total_amount', name: 'total_amount' , "searchable": false},
                    { data: 'discount_amount', name: 'discount_amount', "searchable": false },
                    { data: 'final_total', name: 'final_total' , "searchable": false},
                    { data: 'user_name', name: 'u.first_name' },
                    { data: 'created_at', name: 't.created_at' },
                ],
                "fnDrawCallback": function(oSettings) {
                  
                    // var total_sell_return = sum_table_col($('#credit_memo_sales_by_account_representative_report_tbl'), 'total_invoice');
                    // $('#footer_total_invoice').text(total_sell_return);
        
                    __currency_convert_recursively($('#credit_memo_sales_by_account_representative_report_tbl'));

                },
                "footerCallback": function ( row, data, start, end, display ) {
                    //var row_count = credit_memo_sales_by_account_representative_report_tbl.data().count();
                    var footer_total_amount = 0;
                    var footer_discount_amount = 0;
                    var footer_final_total = 0;
                    for(var r in data){
                        
                        footer_total_amount += $(data[r].total_amount).data('orig-value') ? parseFloat($(data[r].total_amount).data('orig-value')) : 0;

                        footer_discount_amount += $(data[r].discount_amount).data('orig-value') ? parseFloat($(data[r].discount_amount).data('orig-value')) : 0;
                        footer_final_total += $(data[r].final_total).data('orig-value') ? parseFloat($(data[r].final_total).data('orig-value')) : 0;
                        
                    }
                    $('.footer_cm_total_amount').html(__currency_trans_from_en(footer_total_amount));
                    $('.footer_cm_discount_amount').html(__currency_trans_from_en(footer_discount_amount));
                    $('.footer_cm_final_total').html(__currency_trans_from_en(footer_final_total));
                },
            });

            //if($('#sales_by_account_representative_report_tbl').length != 0){
                $('#sr_id, #contact_id, #sales_by_account_representative_date_range').change(function() {
                    sales_by_account_representative_report_tbl.ajax.reload();
                    credit_memo_sales_by_account_representative_report_tbl.ajax.reload();
                });
           // }
            
        });
    </script>
@endsection