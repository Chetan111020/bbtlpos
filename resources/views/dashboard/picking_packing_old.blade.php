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

        .tile-img {
            text-shadow: 2px 2px 3px rgba(0, 0, 0, 0.9);
        }

        .text-faded {
            color: rgba(255, 255, 255, 0.7);
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
            <h4>Received</h4>
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
            <h4>Picking Started</h4>
            <h4 class="picking_started"></h4>

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
            <h4>Picking Complete</h4>
            <h4 class="picking_complete"></h4>
        </div>
        <div class="dash_icon dash_ele_color3">
           <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                class="dash_svg_icon" viewBox="0 0 15 15">
                <path
                    d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z" />
                <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708z" />
            </svg>
        </div>
    </div>
    <div class="dash_info_ele col-md-4 dash_ele_color5">
        <div>
            <h4>Packing Started</h4>
            <h4 class="packing_started"></h4>

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

<div style="display:flex;margin:1em;">
    <div style="height:400px;width:100%;margin:1em;background:white;">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Picking & Packing')])
            <div id="chart1"></div>
        @endcomponent
    </div>
</div>

<br/>
<div style="display:flex;margin:1em;">
    <div style="width:100%;margin:1rem;">
        <div class="col-lg-6 col-sm-6" >
            <div class="circle-tile">
            <div class="circle-tile-heading dash_ele_color4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" style="margin: 1rem" viewBox="0 0 27 27" width="60" height="60"><path d="M13 15.5a1 1 0 11-2 0 1 1 0 012 0zm-.25-8.25a.75.75 0 00-1.5 0v4.5a.75.75 0 001.5 0v-4.5z"></path><path fill-rule="evenodd" d="M11.46.637a1.75 1.75 0 011.08 0l8.25 2.675A1.75 1.75 0 0122 4.976V10c0 6.19-3.77 10.705-9.401 12.83a1.699 1.699 0 01-1.198 0C5.771 20.704 2 16.19 2 10V4.976c0-.76.49-1.43 1.21-1.664L11.46.637zm.617 1.426a.25.25 0 00-.154 0L3.673 4.74a.249.249 0 00-.173.237V10c0 5.461 3.28 9.483 8.43 11.426a.2.2 0 00.14 0C17.22 19.483 20.5 15.46 20.5 10V4.976a.25.25 0 00-.173-.237l-8.25-2.676z"></path></svg>
            </div>
              <div class="circle-tile-content dash_ele_color4">
                <div class="circle-tile-description text-faded dash_ele_color4">Pending Order</div>
                <div class="circle-tile-number text-faded dash_ele_color4 pending_order"></div>
              </div>
            </div>
          </div> 

            <div class="col-lg-6 col-sm-6">
            <div class="circle-tile">
                <div class="circle-tile-heading dash_ele_color3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" style="margin: 1rem"
                        viewBox="0 0 27 27" width="60" height="60">
                        <path d="M16.53 9.78a.75.75 0 00-1.06-1.06L11 13.19l-1.97-1.97a.75.75 0 00-1.06 1.06l2.5 2.5a.75.75 0 001.06 0l5-5z">
                        </path>
                        <path fill-rule="evenodd" d="M12.54.637a1.75 1.75 0 00-1.08 0L3.21 3.312A1.75 1.75 0 002 4.976V10c0 6.19 3.77 10.705 9.401 12.83.386.145.812.145 1.198 0C18.229 20.704 22 16.19 22 10V4.976c0-.759-.49-1.43-1.21-1.664L12.54.637zm-.617 1.426a.25.25 0 01.154 0l8.25 2.676a.25.25 0 01.173.237V10c0 5.461-3.28 9.483-8.43 11.426a.2.2 0 01-.14 0C6.78 19.483 3.5 15.46 3.5 10V4.976c0-.108.069-.203.173-.237l8.25-2.676z">
                        </path>
                    </svg>
                </div>
                <div class="circle-tile-content dash_ele_color3">
                    <div class="circle-tile-description text-faded dash_ele_color3">Complete Order</div>
                    <div class="circle-tile-number text-faded dash_ele_color3 complete_order"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div style="display:flex;margin:1em;">
    <div style="height:400px;width:100%;margin:1em;background:white;">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Order Chart')])
            <div id="chart2"></div>
        @endcomponent
    </div>
</div>
<br />
<br />
<br />
<br />
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
                url: '/Pick_PackDashboard/getpickingtotal',
                type: 'get',
                dataType: 'json',
                data: {
                    start_date: start,
                    end_date: end,
                },
                success: function(response) {
                    console.log(response);
                    $(".recevied").html(response[0], true);
                    $(".picking_complete").html(response[1], true);
                    $(".picking_started").html(response[2], true);
                    $(".packing_started").html(response[3], true);
                    $(".complete_order").html(response[4], true);
                    $(".pending_order").html(response[5], true);
                }
            });
            var options1 = {
                chart: {
                    height: 350,
                    width: "100%",
                    type: "donut"
                },
                series: [],
                labels: ['Received', 'Picking Started', 'Picking Complete', 'Packing Started'],
                noData: {
                    text: 'Loading...',
                    align: "center",
                    verticalAlign: "middle",
                },
            };

            $("#chart1", function() {
                var url1 = "/Pick_PackDashboard/pickingchart?start=" + start + "&end=" + end;
                axios({
                    method: "GET",
                    url: url1,
                }).then(function(response) {
                    console.log(response)
                    chart1.updateOptions({
                        series: [
                            response.data[0], response.data[1], response.data[2],
                            response.data[3]
                        ],
                        noData: {
                            text: 'Loading...',
                            align: "center",
                            verticalAlign: "middle",
                        },
                    });
                });
            });

            var options2 = {
                grid: {
                    show: false,
                },
                series: [],
                type: "area",
                chart: {
                    height: 350,
                },
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    curve: "smooth",
                },
                xaxis: {
                    type: "datetime",
                    categories: [],
                },
                noData: {
                    text: "Loading...",
                },
                tooltip: {
                    x: {
                        format: "dd/MM/yy",
                    },
                },
            };

            $("#chart2", function() {
                var url = "/Pick_PackDashboard/orderchart?start=" + start + "&end=" + end;
                axios({
                    method: "GET",
                    url: url,
                }).then(function(data) {
                    chart2.updateOptions({
                        series: [{
                                name: "Completed Order",
                                data: data.data[0],
                            },
                            {
                                name: "Pending Order",
                                data: data.data[1],
                            },
                            {
                                name: "total Order",
                                data: data.data[2],
                            },
                        ],
                        xaxis: {
                            type: "datetime",
                            categories: data.data[3],
                        },
                        yaxis: [{
                            "labels": {
                                "formatter": function(val) {
                                    return val.toFixed(0)
                                }
                            }
                        }],
                    });
                });
            });


            var chart1 = new ApexCharts(document.querySelector("#chart1"), options1);
            chart1.render();
            var chart2 = new ApexCharts(document.querySelector("#chart2"), options2);
            chart2.render();
        }
    });
</script>



@endsection
