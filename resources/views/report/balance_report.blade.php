@extends('layouts.app')
<title >Opening Balance Report </title> 
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Balance Report</h1>
</section>
<style>
    tbody, td{
    text-align: leftcenter;
    }
</style>
<!-- Main content -->
<section class="content">

    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('br_account_rep', 'Account Rep:') !!}
                        {!! Form::select('br_account_rep', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'br_account_rep']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('br_filter_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('br_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <br>
                              {!! Form::checkbox('do_not_show_paid_invoices', 1, true, 
                              [ 'class' => 'input-icheck', 'id' => 'do_not_show_paid_invoices']); !!} Do not show paid invoices
                            </label>
                        </div>
                    </div>
                </div>
                <!--   <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('type', __( 'lang_v1.type' ) . ':') !!}
                        {!! Form::select('contact_type', $types, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'contact_type']); !!}
                    </div>
                </div> -->

            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="balance_report_tbl">
                    <thead>
                        <tr>
                            <th style="text-align: center;">Date </th>
                            <th style="text-align: center;">Order Note</th>
                            <th style="text-align: center;">Invoice</th>
                            <th style="text-align: center;">Name </th>
                            <th style="text-align: center;">Mobile </th>
                            <th style="text-align: center;">Open Balance </th>
                        </tr>
                    </thead>
        
                    <tfoot>
                        <tr class="bg-gray font-17 footer-total text-center">
                            <td colspan="6"><strong>@lang('sale.total'):</strong><span style="margin-left: 20px;" class="display_currency footer_total_open_balance" data-currency_symbol ="true"></span></td>
                            <!--<td></td>-->
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
var ranges = {};

ranges[LANG.today] = [moment(), moment()];
// ranges['Start To One Month Back'] = [moment('2021-05-31'), moment().subtract(1, 'month')];
ranges['Start To One Month Back'] = [moment().add(-1,'month').startOf('month'), moment()];
ranges[LANG.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
ranges[LANG.last_7_days] = [moment().subtract(6, 'days'), moment()];
ranges[LANG.last_30_days] = [moment().subtract(29, 'days'), moment()];
ranges[LANG.this_month] = [moment().startOf('month'), moment().endOf('month')];
ranges[LANG.last_month] = [
    moment()
        .subtract(1, 'month')
        .startOf('month'),
    moment()
        .subtract(1, 'month')
        .endOf('month'),
];
ranges[LANG.this_month_last_year] = [
    moment()
        .subtract(1, 'year')
        .startOf('year'),
    moment()
        // .add(1, 'month')
        .endOf('month'),
];
ranges[LANG.this_year] = [moment().startOf('year'), moment().endOf('year')];
ranges[LANG.last_year] = [
    moment().startOf('year').subtract(1, 'year'), 
    moment().endOf('year').subtract(1, 'year') 
];
ranges[LANG.this_financial_year] = [financial_year.start, financial_year.end];
ranges[LANG.last_financial_year] = [
    moment(financial_year.start._i).subtract(1, 'year'), 
    moment(financial_year.end._i).subtract(1, 'year')
];

var datesettings = {
    ranges: ranges,
    startDate: financial_year.start,
    endDate: financial_year.end,
    locale: {
        cancelLabel: LANG.clear,
        applyLabel: LANG.apply,
        customRangeLabel: LANG.custom_range,
        format: moment_date_format,
        toLabel: '~',
    },
};


$(function() {
    $("#br_account_rep").change(function() {
        document.title  =  'Opening Balance Report -' + $('option:selected', this).text() + ' For ' + $("input[name=br_filter_date_range]").val();
    });
});

</script>
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
@endsection