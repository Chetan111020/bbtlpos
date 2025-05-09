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
            margin: 0rem;
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

        /********************************************************************/
        .panel.with-nav-tabs .panel-heading {
            padding: 5px 5px 0 5px;
        }

        .panel.with-nav-tabs .nav-tabs {
            border-bottom: none;
        }

        .panel.with-nav-tabs .nav-justified {
            margin-bottom: -1px;
        }

        /*** PANEL INFO ***/
        .with-nav-tabs.panel-info .nav-tabs>li>a,
        .with-nav-tabs.panel-info .nav-tabs>li>a:hover,
        .with-nav-tabs.panel-info .nav-tabs>li>a:focus {
            color: #31708f;
        }

        .with-nav-tabs.panel-info .nav-tabs>.open>a,
        .with-nav-tabs.panel-info .nav-tabs>.open>a:hover,
        .with-nav-tabs.panel-info .nav-tabs>.open>a:focus,
        .with-nav-tabs.panel-info .nav-tabs>li>a:hover,
        .with-nav-tabs.panel-info .nav-tabs>li>a:focus {
            color: #31708f;
            background-color: #bce8f1;
            border-color: transparent;
        }

        .with-nav-tabs.panel-info .nav-tabs>li.active>a,
        .with-nav-tabs.panel-info .nav-tabs>li.active>a:hover,
        .with-nav-tabs.panel-info .nav-tabs>li.active>a:focus {
            color: #31708f;
            background-color: #fff;
            border-color: #bce8f1;
            border-bottom-color: transparent;
        }

        .with-nav-tabs.panel-info .nav-tabs>li.dropdown .dropdown-menu {
            background-color: #d9edf7;
            border-color: #bce8f1;
        }

        .with-nav-tabs.panel-info .nav-tabs>li.dropdown .dropdown-menu>li>a {
            color: #31708f;
        }

        .with-nav-tabs.panel-info .nav-tabs>li.dropdown .dropdown-menu>li>a:hover,
        .with-nav-tabs.panel-info .nav-tabs>li.dropdown .dropdown-menu>li>a:focus {
            background-color: #bce8f1;
        }

        .with-nav-tabs.panel-info .nav-tabs>li.dropdown .dropdown-menu>.active>a,
        .with-nav-tabs.panel-info .nav-tabs>li.dropdown .dropdown-menu>.active>a:hover,
        .with-nav-tabs.panel-info .nav-tabs>li.dropdown .dropdown-menu>.active>a:focus {
            color: #fff;
            background-color: #31708f;
        }

        /********************************************************************/
        .circle-tile {
            margin-bottom: 15px;
            text-align: center;
        }

        .circle-tile-heading {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 100%;
            color: #FFFFFF;
            height: 80px;
            margin: 0 auto -40px;
            position: relative;
            transition: all 0.3s ease-in-out 0s;
            width: 80px;
        }

        .circle-tile-heading .fa {
            line-height: 80px;
        }

        .circle-tile-content {
            padding-top: 50px;
        }

        .circle-tile-number {
            font-size: 26px;
            font-weight: 700;
            line-height: 1;
            padding: 5px 0 15px;
        }

        .circle-tile-description {
            text-transform: uppercase;
        }

        .circle-tile-heading.dark-blue:hover {
            background-color: #2E4154;
        }

        .circle-tile-heading.green:hover {
            background-color: #138F77;
        }

        .circle-tile-heading.orange:hover {
            background-color: #DA8C10;
        }

        .circle-tile-heading.blue:hover {
            background-color: #2473A6;
        }

        .circle-tile-heading.red:hover {
            background-color: #CF4435;
        }

        .circle-tile-heading.purple:hover {
            background-color: #7F3D9B;
        }

        .tile-img {
            text-shadow: 2px 2px 3px rgba(0, 0, 0, 0.9);
        }

        .dark-blue {
            background-color: #34495E;
        }

        .green {
            background-color: #16A085;
        }

        .blue {
            background-color: #2980B9;
        }

        .orange {
            background-color: #F39C12;
        }

        .red {
            background-color: #E74C3C;
        }

        .purple {
            background-color: #8E44AD;
        }

        .dark-gray {
            background-color: #7F8C8D;
        }

        .gray {
            background-color: #95A5A6;
        }

        .light-gray {
            background-color: #BDC3C7;
        }

        .yellow {
            background-color: #F1C40F;
        }

        .text-dark-blue {
            color: #34495E;
        }

        .text-green {
            color: #16A085;
        }

        .text-blue {
            color: #2980B9;
        }

        .text-orange {
            color: #F39C12;
        }

        .text-red {
            color: #E74C3C;
        }

        .text-purple {
            color: #8E44AD;
        }

        .text-faded {
            color: rgba(255, 255, 255, 0.7);
        }
    </style>

@endsection

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
                        <button class="btn btn-primary" name="submit" id="submitData">Submit</button>
                    </div>
                </div>
            </div>

        </div>
</div>

{{-- <div style="width:100%;margin:1rem;">
    <div style="display:flex;">
        <ul class="nav nav-tabs">
            <li role="presentation" class="active"><a href="#">Home</a></li>
            <li role="presentation"><a href="#">Profile</a></li>
            <li role="presentation"><a href="#">Messages</a></li>
          </ul>
    </div> --}}

{{-- <div class="row">
        <div class="col-sm-3">
            <div class="well">
                <h4>Users</h4>
                <p>1 Million</p>
            </div>
        </div> --}}
<div style="display:flex;margin:1em;">
    <div style="width:100%;margin:1em;">
        <div style="display:flex;">
            <div class="col-lg-4 col-sm-4">
                <div class="circle-tile ">
                    <div class="circle-tile-heading dash_ele_color5">
                        {{-- <i class="fa fa-users fa-fw fa-3x"></i> --}}
                        <svg xmlns="http://www.w3.org/2000/svg" style="margin: 1rem" fill="currentColor"
                            viewBox="0 0 27 27" width="60" height="60">
                            <path fill-rule="evenodd"
                                d="M12.876.64a1.75 1.75 0 00-1.75 0l-8.25 4.762a1.75 1.75 0 00-.875 1.515v9.525c0 .625.334 1.203.875 1.515l8.25 4.763a1.75 1.75 0 001.75 0l8.25-4.762a1.75 1.75 0 00.875-1.516V6.917a1.75 1.75 0 00-.875-1.515L12.876.639zm-1 1.298a.25.25 0 01.25 0l7.625 4.402-7.75 4.474-7.75-4.474 7.625-4.402zM3.501 7.64v8.803c0 .09.048.172.125.216l7.625 4.402v-8.947L3.501 7.64zm9.25 13.421l7.625-4.402a.25.25 0 00.125-.216V7.639l-7.75 4.474v8.947z">
                            </path>
                        </svg>

                    </div>
                    <div class="circle-tile-content dash_ele_color5">
                        <div class="circle-tile-description text-faded dash_ele_color5">Total Sales</div>
                        <div class="circle-tile-number text-faded dash_ele_color5 total_sell"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-sm-4">
                <div class="circle-tile">
                    <div class="circle-tile-heading dash_ele_color2">
                        {{-- <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" style="margin: 1rem"
                            viewBox="0 0 27 27" width="60" height="60">
                            <path fill-rule="evenodd"
                                d="M11.063 1.456a1.75 1.75 0 011.874 0l8.383 5.316a1.75 1.75 0 010 2.956l-8.383 5.316a1.75 1.75 0 01-1.874 0L2.68 9.728a1.75 1.75 0 010-2.956l8.383-5.316zm1.071 1.267a.25.25 0 00-.268 0L3.483 8.039a.25.25 0 000 .422l8.383 5.316a.25.25 0 00.268 0l8.383-5.316a.25.25 0 000-.422l-8.383-5.316z">
                            </path>
                            <path fill-rule="evenodd"
                                d="M1.867 12.324a.75.75 0 011.035-.232l8.964 5.685a.25.25 0 00.268 0l8.964-5.685a.75.75 0 01.804 1.267l-8.965 5.685a1.75 1.75 0 01-1.874 0l-8.965-5.685a.75.75 0 01-.231-1.035z">
                            </path>
                            <path fill-rule="evenodd"
                                d="M1.867 16.324a.75.75 0 011.035-.232l8.964 5.685a.25.25 0 00.268 0l8.964-5.685a.75.75 0 01.804 1.267l-8.965 5.685a1.75 1.75 0 01-1.874 0l-8.965-5.685a.75.75 0 01-.231-1.035z">
                            </path>
                        </svg> --}}
                        {{-- <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" style="margin: 1rem;" viewBox="0 0 384 512" id="IconChangeColor" height="50" width="50"><!--! Font Awesome Free 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2022 Fonticons, Inc. --><path d="M384 192h-64v128H192v128H0v-25.6h166.4v-128h128v-128H384V192zm-25.6 38.4v128h-128v128H64V512h192V384h128V230.4h-25.6zm25.6 192h-89.6V512H320v-64h64v-25.6zM0 0v384h128V256h128V128h128V0H0z" id="mainIconPathAttribute"></path></svg> --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" style="margin: 1rem;"
                            fill="currentColor" class="bi bi-envelope-open" viewBox="0 0 16 16">
                            <path
                                d="M8.47 1.318a1 1 0 0 0-.94 0l-6 3.2A1 1 0 0 0 1 5.4v.817l5.75 3.45L8 8.917l1.25.75L15 6.217V5.4a1 1 0 0 0-.53-.882l-6-3.2ZM15 7.383l-4.778 2.867L15 13.117V7.383Zm-.035 6.88L8 10.082l-6.965 4.18A1 1 0 0 0 2 15h12a1 1 0 0 0 .965-.738ZM1 13.116l4.778-2.867L1 7.383v5.734ZM7.059.435a2 2 0 0 1 1.882 0l6 3.2A2 2 0 0 1 16 5.4V14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V5.4a2 2 0 0 1 1.059-1.765l6-3.2Z" />
                        </svg>
                    </div>
                    <div class="circle-tile-content dash_ele_color2">
                        <div class="circle-tile-description text-faded dash_ele_color2">total draft</div>
                        <div class="circle-tile-number text-faded dash_ele_color2 draft"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-sm-4">
                <div class="circle-tile ">
                    <div class="circle-tile-heading dash_ele_color3">
                        {{-- <i class="fa fa-users fa-fw fa-3x"></i> --}}
                        {{-- <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" style="margin: 1rem"
                            viewBox="0 0 27 27" width="60" height="60">
                            <path
                                d="M16.53 9.78a.75.75 0 00-1.06-1.06L11 13.19l-1.97-1.97a.75.75 0 00-1.06 1.06l2.5 2.5a.75.75 0 001.06 0l5-5z">
                            </path>
                            <path fill-rule="evenodd"
                                d="M12.54.637a1.75 1.75 0 00-1.08 0L3.21 3.312A1.75 1.75 0 002 4.976V10c0 6.19 3.77 10.705 9.401 12.83.386.145.812.145 1.198 0C18.229 20.704 22 16.19 22 10V4.976c0-.759-.49-1.43-1.21-1.664L12.54.637zm-.617 1.426a.25.25 0 01.154 0l8.25 2.676a.25.25 0 01.173.237V10c0 5.461-3.28 9.483-8.43 11.426a.2.2 0 01-.14 0C6.78 19.483 3.5 15.46 3.5 10V4.976c0-.108.069-.203.173-.237l8.25-2.676z">
                            </path>
                        </svg> --}}
                        <svg xmlns="http://www.w3.org/2000/svg"style="margin: 1.7rem" viewBox="0 0 28 28" width="75"
                            height="80" fill="currentColor" class="bi bi-cash-coin">
                            <path fill-rule="evenodd"
                                d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0z" />
                            <path
                                d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1h-.003zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195l.054.012z" />
                            <path
                                d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083c.058-.344.145-.678.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1H1z" />
                            <path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 5.982 5.982 0 0 1 3.13-1.567z" />
                        </svg>
                    </div>
                    <div class="circle-tile-content dash_ele_color3">
                        <div class="circle-tile-description text-faded dash_ele_color3">Total Recent Transactions</div>
                        <div class="circle-tile-number text-faded dash_ele_color3 total_transactions"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endif
{{-- <div class="row">
    <div class="col-sm-12">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Total Sales')])
            <div id="chart1"></div>
        @endcomponent
    </div>
</div> --}}
<div style="display:flex;margin:1em;">
    <div style="height:400px;width:100%;margin:1em;background:white;">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Last 7 Days Sales')])
            <div id="chart1"></div>
        @endcomponent
    </div>
</div>

<br />
<br />
<br />
<div style="display:flex;margin:1em;">
    <div style="width:100%;margin:1em;">

        <div class="panel with-nav-tabs panel-info">
            <div class="panel-heading">
                <h3 style="color: #31708f">Recent Transactions</h3>
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#final" data-toggle="tab">@lang('sale.final')</a></li>
                    <li><a href="#quotation" data-toggle="tab">@lang('lang_v1.quotation')</a></li>
                    <li><a href="#draft" data-toggle="tab">@lang('sale.draft')</a></li>
                </ul>
            </div>
            <div class="panel-body">
                <div class="tab-content" style="height: 400px;">
                    <div class="tab-pane fade in active" id="final">
                        <div class="table-responsive">
                            <div id="error"></div>
                            <table class="table table-hover">
                                {{-- @if ($final->count() > 0)
                                    @if (isset($final) && !empty($final)) --}}
                                <thead id="final_head">

                                </thead>
                                <tbody id="final_table">
                                    {{-- @foreach ($final as $key => $value)
                                                <tr>
                                                    <td style="width: 40px;">{{ $loop->iteration }}.</td>
                                                    <td data-html="true" data-toggle="tooltip"
                                                        title="{{ $value->invoice_no }}({{ $value->name }})
                                                        @if (!empty($value->mobile) && $value->is_default == 0) <br/>  Mobile: {{ $value->mobile }} @endif
                                                        ">
                                                        {{ $value->invoice_no }}({{ $value->name }})</td>
                                                    <td class="display_currency">{{ $value->final_total }}</td>
                                                </tr>
                                            @endforeach --}}
                                </tbody>
                                {{-- @endif
                                @else
                                    <p class="text-center">@lang('sale.no_recent_transactions')</p>
                                @endif --}}
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="quotation">
                        <div class="table-responsive">
                            <div id="error"></div>
                            <table class="table table-hover">
                                {{-- @if ($quotation->count() > 0)
                                    @if (isset($quotation) && !empty($quotation)) --}}
                                <thead id="quotation_head">

                                </thead>
                                <tbody id="quotation_table">

                                    {{-- @foreach ($quotation as $key => $quotations)
                                                <tr>
                                                    <td style="width: 40px;">{{ $loop->iteration }}.</td>
                                                    <td data-html="true" data-toggle="tooltip"
                                                        title="{{ $quotations->invoice_no }}({{ $quotations->name }}),
                                                        @if (!empty($quotations->mobile) && $quotations->is_default == 0) <br />    Mobile: {{ $quotations->mobile }} @endif
                                                        ">
                                                        {{ $quotations->invoice_no }}({{ $quotations->name }})</td>
                                                    <td class="display_currency">{{ $quotations->final_total }}</td>
                                                    <td class="abname"></td>
                                                </tr>
                                            @endforeach --}}

                                </tbody>
                                {{-- @endif
                                @else
                                    <p class="text-center">@lang('sale.no_recent_transactions')</p>
                                @endif --}}
                            </table>
                        </div>
                    </div>


                    <div class="tab-pane fade" id="draft">
                        <div class="table-responsive">
                            <div id="error"></div>
                            <table class="table table-hover">
                                {{-- @if ($draft->count() > 0)
                                    @if (isset($draft) && !empty($draft)) --}}
                                <thead id="draft_head">
                                </thead>
                                <tbody id="draft_table">
                                    {{-- @foreach ($draft as $key => $drafts)
                                                <tr>
                                                    <td style="width: 40px;">{{ $loop->iteration }}.</td>
                                                    <td data-html="true" data-toggle="tooltip"
                                                        title="{{ $drafts->invoice_no }}({{ $drafts->name }})
                                                        @if (!empty($drafts->mobile) && $drafts->is_default == 0) <br />  Mobile: {{ $drafts->mobile }} @endif
                                                        ">
                                                        {{ $drafts->invoice_no }}({{ $drafts->name }})</td>
                                                    <td class="display_currency">{{ $drafts->final_total }}</td>
                                                </tr>
                                            @endforeach --}}
                                </tbody>
                                {{-- @endif
                                @else
                                    <p class="text-center">@lang('sale.no_recent_transactions')</p>
                                @endif --}}
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@stop
@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script></script>
<script type="text/javascript">
    $(document).ready(function() {

        var start, end;

        $(document).ready(function(e) {
            $("#all_date_filter").daterangepicker({
                ranges: ranges,
                autoUpdateInput: true,
                startDate: moment().startOf("days").subtract(6, 'days'),
                endDate: moment().endOf("days"),
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
                method: "get",
                url: "/CashierDashboard/gettotals",
                dataType: "json",
                data: {
                    start_date: start,
                    end_date: end,
                },
                success: function(data) {

                    //purchase details
                    //   $(".final").html(data.final, true);
                    //   $(".quotation").html(data.quotation, true);
                    $(".draft").html(data.draft.toLocaleString('en'), true);
                    $(".total_transactions").html(data.total_transactions.toLocaleString('en'),
                        true);
                    $(".total_sell").html("$" + __intToString(data.total_sell, true));
                }
            });

            $.ajax({
                method: "get",
                url: "/CashierDashboard/getvalue",
                dataType: "json",
                data: {
                    start_date: start,
                    end_date: end,
                },
                success: function(response) {
                    var tableSearch = $('#final_table');
                    var final_head = $('#final_head');
                    final_head.html('');
                    tableSearch.html('');

                    if (!jQuery.isEmptyObject(response)) {
                        final_head.append("<tr><th>" + 'No' + "</th>" + "<th>" + 'Name' + "</th>" +
                            "<th>" + 'Amount' + "</th></tr>");
                        $.each(response, function(index, value) {
                            var no = index + 1;
                            // tableSearch.append("<p>"no_recent_transactions"</p>");
                            tableSearch.append("<tr><td style='width: 40px;'>" + no +
                                "</td>" + "<td>" + value.invoice_no + "(" + value.name +
                                ")" + "</td><td class='display_currency'>" + __currency_trans_from_en(value.final_total) + "</td></tr>");

                        });
                    } else {
                        var er = 'No Recent Transactions';
                         tableSearch.append("<p class='text-center'>" + er +
                            "</p>");
                    }
                }
            });


            $.ajax({
                method: "get",
                url: "/CashierDashboard/quotation",
                dataType: "json",
                data: {
                    start_date: start,
                    end_date: end,
                },
                success: function(data) {
                    var quotation_table = $('#quotation_table');
                    var er_msg = $('#error');
                    var quotation_head = $('#quotation_head');
                    quotation_head.html('');
                    quotation_table.html('');
                    er_msg.html('');

                    if (!jQuery.isEmptyObject(data)) {
                        quotation_head.append("<tr><th>" + 'No' + "</th>" + "<th>" + 'name' +
                            "</th>" + "<th>" + 'Amount' + "</th></tr>");
                        $.each(data, function(index, value) {
                            var no = index + 1;
                            // quotation_table.append("<p>"no_recent_transactions"</p>");
                            quotation_table.append("<tr><td style='width: 40px;'>" + no +
                                "</td>" + "<td>" + value.invoice_no + "(" + value.name +
                                ")" + "</td><td class='display_currency'>" + __currency_trans_from_en(value
                                .final_total) + "</td></tr>");

                        });
                    } else {
                        var er = 'No Recent Transactions';
                        quotation_table.append("<p class='text-center'>" + er +
                            "</p>");
                    }
                }
            });

            $.ajax({
                method: "get",
                url: "/CashierDashboard/draft",
                dataType: "json",
                data: {
                    start_date: start,
                    end_date: end,
                },
                success: function(response) {
                    var draft_table = $('#draft_table');
                    var er_msg = $('#error');
                    var draft_head = $('#draft_head');

                    draft_table.html('');
                    er_msg.html('');
                    draft_head.html('');

                    if (!jQuery.isEmptyObject(response)) {
                        draft_head.append("<tr><th>" + 'No' + "</th>" + "<th>" + 'name' + "</th>" +
                            "<th>" + 'Amount' + "</th></tr>");
                        $.each(response, function(index, value) {
                            var no = index + 1;
                            // tableSearch.append("<p>"no_recent_transactions"</p>");
                            draft_table.append("<tr><td style='width: 40px;'>" + no +
                                "</td>" + "<td>" + value.invoice_no + "(" + value.name +
                                ")" + "</td><td class='display_currency'>" + __currency_trans_from_en(value
                                .final_total) + "</td></tr>");

                        });
                    } else {
                        var er = 'No Recent Transactions';
                        draft_table.append("<p class='text-center'>" + er +
                            "</p>");
                    }

                },

            });

            var options = {
                series: [],
                chart: {
                    height: 350,
                    type: 'area'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    type: 'datetime',
                    categories: []
                },
                tooltip: {
                    x: {
                        format: 'dd/MM/yy'
                    },
                },
                title: {
                    text: "(Sell = Payable Amount)",
                    rotate: -90,
                    offsetX: 500,
                    offsetY: 0,
                    style: {
                    fontSize: '12px',
                    fontFamily: 'Helvetica, Arial, sans-serif',
                    fontWeight: 600,
                    cssClass: 'apexcharts-yaxis-title',
                    },
                },
                noData: {
                    text: "Loading...",
                },
            };

            $("#chart1", function() {
                var url = "/CashierDashboard/total-sales?start=" + start + "&end=" + end;
                axios({
                    method: "GET",
                    url: url,
                }).then(function(response) {
                    chart.updateOptions({
                        series: [{
                            name: "Total Sell",
                            data: response.data[0],
                        }, ],
                        xaxis: {
                            type: "datetime",
                            categories: response.data[1],
                        },
                        yaxis: [{
                            labels: {
                                formatter: function(val) {
                                    return (val).toLocaleString();
                                },
                            },
                        }, ],
                    });
                });
            });
            var chart = new ApexCharts(document.querySelector("#chart1"), options);
            chart.render();

        }
    });




    function __number_format(number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
    }

    function __intToString(num) {
        num = num.toString().replace(/[^0-9.]/g, "");
        if (num < 1000) {
            return num;
        }
        let si = [{
                v: 1e3,
                s: "K"
            },
            {
                v: 1e6,
                s: "M"
            },
            {
                v: 1e9,
                s: "B"
            },
            {
                v: 1e12,
                s: "T"
            },
            {
                v: 1e15,
                s: "P"
            },
            {
                v: 1e18,
                s: "E"
            },
        ];
        let index;
        for (index = si.length - 1; index > 0; index--) {
            if (num >= si[index].v) {
                break;
            }
        }
        return (
            (num / si[index].v).toFixed(2).replace(/\.0+$|(\.[0-9]*[1-9])0+$/, "$1") +
            si[index].s
        );
    }
</script>


@endsection
