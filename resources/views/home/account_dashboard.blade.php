@extends('layouts.app')
@section('title', __('home.home'))
@section('content')
@section('css')

    <style>
        .search_result {
            display: none;
        }

        .dash_info_ele {
            width: 35%;
            padding: 1rem;
            margin: 1rem;
            background: white;
            display: flex;
        }

        .dash_icon {
            margin: auto 5px auto auto;
            border-radius: 50px;
            display: flex;
        }

        .dash_svg_icon {
            height: 28px;
            margin: 15px;
        }

        .dash_ele_color1 {
            color: #00bcd4 !important;
            background: rgba(0, 188, 212, .1) !important;
        }

        .dash_ele_color2 {
            color: #2196f3 !important;
            background: rgba(33, 150, 243, .1) !important;
        }

        .dash_ele_color3 {
            color: #4caf50 !important;
            background: rgba(76, 175, 80, .1) !important;
        }

        .dash_ele_color4 {
            color: #f44336 !important;
            background: rgba(244, 67, 54, .1) !important;
        }

        .dash_ele_color5 {
            color: #061672 !important;
            background: rgba(77, 94, 243, 0.1) !important;
        }
    </style>

@endsection
<!-- Content Header (Page header) -->
<!-- Content Header (Page header) -->
<div style="background:white;padding:15px;">
    <h1>Welcome {{ Session::get('user.first_name') }},</h1>
    @if (auth()->user()->can('dashboard.data'))
        <div class="row">
            <div class="form-group pull-right">
                <div class="col-md-8">
                    <input type="hidden" id="date" name="date" value="">
                    <div class="form-group">
                        {!! Form::label('all_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text(
                            'all_date_filter',
                            @format_date('first day of this week') . ' ~ ' . @format_date('last day of this week'),
                            [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control',
                                'id' => 'all_date_filter',
                                'readonly',
                            ],
                        ) !!}

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group" style="margin-top: 25px;">
                        <button class="btn btn-primary" id="submitData">Submit</button>
                    </div>
                </div>
            </div>

        </div>
</div>
<br />
<div style="display:flex;margin:1em;">
    <div class="dash_info_ele dash_ele_color2" style="">
        <div>
            <h4>Account Receivable</h4>
            <h4 class="recevied"></h4>

        </div>
        <div class="dash_icon dash_ele_color2">
            {{-- <i class="glyphicon glyphicon-plus"></i> --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler dash_svg_icon icon-tabler-brand-codepen"
                width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M3 15l9 6l9 -6l-9 -6l-9 6" />
                <path d="M3 9l9 6l9 -6l-9 -6l-9 6" />
                <line x1="3" y1="9" x2="3" y2="15" />
                <line x1="21" y1="9" x2="21" y2="15" />
                <line x1="12" y1="3" x2="12" y2="9" />
                <line x1="12" y1="15" x2="12" y2="21" />
            </svg>
        </div>
    </div>

    <div class="dash_info_ele col-md-4 dash_ele_color5">
        <div>
            <h4>Account Payable</h4>
            <h4 class="picking_complete"></h4>

        </div>
        <div class="dash_icon dash_ele_color5">
            <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
            </svg>
        </div>
    </div>
    <div class="dash_info_ele col-md-4 dash_ele_color3">
        <div>
            <h4>Total Items Purchase</h4>
            <h4 class="total_item_puchase"></h4>
        </div>
        <div class="dash_icon dash_ele_color3">
            <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
        </div>
    </div>
    <div class="dash_info_ele col-md-4 dash_ele_color5">
        <div>
            <h4>Total Item Sell</h4>
            <h4 class="total_item_sell"></h4>

        </div>
        <div class="dash_icon dash_ele_color5">
            <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
            </svg>
        </div>
    </div>
</div>
<div style="display:flex;margin:1em;">
    <div class="dash_info_ele col-md-4 dash_ele_color3">
        <div>
            <h4>Total Ledger</h4>
            <h4 class="ledgerTotal"></h4>
        </div>
        <div class="dash_icon dash_ele_color3">
            <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
        </div>
    </div>
    <div class="dash_info_ele col-md-4 dash_ele_color5">
        <div>
            <h4>Total Purchase Due</h4>
            <h4 class="paymentDue"></h4>

        </div>
        <div class="dash_icon dash_ele_color5">
            <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
            </svg>
        </div>
    </div>
    <div class="dash_info_ele col-md-4 dash_ele_color5">
        <div>
            <h4>Total Expense</h4>
            <h4 class="totalExpense"></h4>

        </div>
        <div class="dash_icon dash_ele_color5">
            <svg xmlns="http://www.w3.org/2000/svg" class="dash_svg_icon" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
            </svg>
        </div>
    </div>
</div>
@endif


@stop
@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script type="text/javascript">
    $(document).ready(function() {

        var start, end;

        $(document).ready(function(e) {
            $("#all_date_filter").daterangepicker({
                ranges: ranges,
                autoUpdateInput: true,
                startDate: moment().startOf("week"),
                endDate: moment().endOf("week"),
                locale: {
                    format: moment_date_format,
                },
            });
            $("#all_date_filter").on("apply.daterangepicker", function(ev, picker) {
                $(this).val(
                    picker.startDate.format(moment_date_format) +
                    " ~ " +
                    picker.endDate.format(moment_date_format)
                );
                $("#date").val($(this).val());
            });

            $("#all_date_filter").on("cancel.daterangepicker", function(ev, picker) {
                $(this).val("");
            });
            getReportdata();
        });

        $(document).on("click", "button#submitData", function(e) {
            e.preventDefault();
            getReportdata();
        });

        function getReportdata() {
            if ($("input#all_date_filter").val()) {
                start = $("input#all_date_filter")
                    .data("daterangepicker")
                    .startDate.format("YYYY-MM-DD");
                end = $("input#all_date_filter")
                    .data("daterangepicker")
                    .endDate.format("YYYY-MM-DD");
            }

            $.ajax({
                url: '/account-dashboard/getTotals',
                type: 'get',
                dataType: 'json',
                data: {
                    start_date: start,
                    end_date: end,
                },
                success: function(response) {
                    console.log(response);
                    // $(".recevied").html(response[0], true);
                    // $(".picking_complete").html(response[1], true);
                    $(".total_item_puchase").html(Math.ceil(response[2], true));
                    $(".total_item_sell").html(Math.ceil(response[3], true));
                    $(".ledgerTotal ").html(__currency_trans_from_en(response[4], true));
                    $(".paymentDue").html(__currency_trans_from_en(response[5], true));
                    $(".totalExpense").html(__currency_trans_from_en(response[6], true));
                    // $(".pending_order").html(response[5], true);
                }
            });
           

        }
    });
</script>



@endsection