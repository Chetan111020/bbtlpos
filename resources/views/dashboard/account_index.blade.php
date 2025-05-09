@extends('layouts.app')
@section('title', __('home.home'))
@section('css')
    <style>
        .dbox {
            position: relative;
            background: rgb(255, 86, 65);
            background: -moz-linear-gradient(top, rgba(255, 86, 65, 1) 0%, rgba(253, 50, 97, 1) 100%);
            background: -webkit-linear-gradient(top, rgba(255, 86, 65, 1) 0%, rgba(253, 50, 97, 1) 100%);
            background: linear-gradient(to bottom, rgba(255, 86, 65, 1) 0%, rgba(253, 50, 97, 1) 100%);
            filter: progid: DXImageTransform.Microsoft.gradient(startColorstr='#ff5641', endColorstr='#fd3261', GradientType=0);
            border-radius: 4px;
            text-align: center;
            margin: 60px 0 50px;
        }

        .dbox__icon {
            position: absolute;
            transform: translateY(-50%) translateX(-50%);
            left: 50%;
        }

        .dbox__icon:before {
            width: 75px;
            height: 75px;
            position: absolute;
            background: #fda299;
            background: rgba(253, 162, 153, 0.34);
            content: '';
            border-radius: 50%;
            left: -17px;
            top: -17px;
            z-index: -2;
        }

        .dbox__icon:after {
            width: 60px;
            height: 60px;
            position: absolute;
            background: #f79489;
            background: rgba(247, 148, 137, 0.91);
            content: '';
            border-radius: 50%;
            left: -10px;
            top: -10px;
            z-index: -1;
        }

        .dbox__icon>i {
            background: #ff5444;
            border-radius: 50%;
            line-height: 40px;
            color: #FFF;
            width: 40px;
            height: 40px;
            font-size: 22px;
        }

        .dbox__body {
            padding: 40px 20px;
        }

        .dbox__count {
            display: block;
            font-size: 30px;
            color: #FFF;
            font-weight: 300;
        }

        .dbox__title {
            font-size: 18px;
            color: #FFF;
            color: rgba(255, 255, 255, 0.81);
        }

        .dbox__action {
            transform: translateY(-50%) translateX(-50%);
            position: absolute;
            left: 50%;
        }

        .dbox--color-2 {
            background: rgb(252, 190, 27);
            background: -moz-linear-gradient(top, rgba(252, 190, 27, 1) 1%, rgba(248, 86, 72, 1) 99%);
            background: -webkit-linear-gradient(top, rgba(252, 190, 27, 1) 1%, rgba(248, 86, 72, 1) 99%);
            background: linear-gradient(to bottom, rgba(252, 190, 27, 1) 1%, rgba(248, 86, 72, 1) 99%);
            filter: progid: DXImageTransform.Microsoft.gradient(startColorstr='#fcbe1b', endColorstr='#f85648', GradientType=0);
        }

        .dbox--color-2 .dbox__icon:after {
            background: #fee036;
            background: rgba(254, 224, 54, 0.81);
        }

        .dbox--color-2 .dbox__icon:before {
            background: #fee036;
            background: rgba(254, 224, 54, 0.64);
        }

        .dbox--color-2 .dbox__icon>i {
            background: #fb9f28;
        }

        .dbox--color-3 {
            background: rgb(183, 71, 247);
            background: -moz-linear-gradient(top, rgba(183, 71, 247, 1) 0%, rgba(108, 83, 220, 1) 100%);
            background: -webkit-linear-gradient(top, rgba(183, 71, 247, 1) 0%, rgba(108, 83, 220, 1) 100%);
            background: linear-gradient(to bottom, rgba(183, 71, 247, 1) 0%, rgba(108, 83, 220, 1) 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#b747f7', endColorstr='#6c53dc', GradientType=0);
        }

        .dbox--color-3 .dbox__icon:after {
            background: #b446f5;
            background: rgba(180, 70, 245, 0.76);
        }

        .dbox--color-3 .dbox__icon:before {
            background: #e284ff;
            background: rgba(226, 132, 255, 0.66);
        }

        .dbox--color-3 .dbox__icon>i {
            background: #8150e4;
        }

        .dbox--color-4 {
            background: rgb(71, 247, 130);
            background: -moz-linear-gradient(top, rgb(121, 247, 71) 0%, rgb(20, 99, 9) 100%);
            background: -webkit-linear-gradient(top, rgb(141, 247, 71) 0%, rgb(20, 99, 9) 100%);
            background: linear-gradient(to bottom, rgb(91, 247, 71) 0%, rgb(20, 99, 9) 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#b747f7', endColorstr='#6c53dc', GradientType=0);
        }

        .dbox--color-4 .dbox__icon:after {
            background: #7df546;
            background: rgba(90, 245, 70, 0.76);
        }

        .dbox--color-4 .dbox__icon:before {
            background: #a7ff84;
            background: rgba(157, 255, 132, 0.66);
        }

        .dbox--color-4 .dbox__icon>i {
            background: #61e450;
        }

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
            color: #aa80ee !important;
            background: rgba(111, 75, 243, 0.1) !important;
        }

        .cardbox {
            /* background-color: #2B2D3E; */
            padding: 15px 10px;
            /* height: 150px; */
            height: 200px;
            width: 250px;
        }

        .card .cardbox .details {
            position: absolute;
            color: #fff;
            transform: scale(0.7) translate(-22px, 20px);
            height: 200px;
            width: 250px;
        }

        .card .box1 {
            background-image: linear-gradient(135deg, #ABDCFF 10%, #0396FF 100%);
        }

        .card .box2 {
            background-image: linear-gradient(135deg, #2AFADF 10%, #4C83FF 100%);
        }

        .card .box3 {
            background-image: linear-gradient(135deg, #FFD3A5 10%, #FD6585 100%);
        }

        .card .box4 {
            background-image: linear-gradient(135deg, #EE9AE5 10%, #5961F9 100%);
        }
    </style>
@endsection
@section('content')
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

    <div style="display:flex;margin:1em;">
        <div class="col-md-3">
            <div class="dbox dbox--color-1">
                <div class="dbox__icon">
                    <i class="glyphicon glyphicon-usd"></i>
                </div>
                <div class="dbox__body">
                    <span class="dbox__count opening_balance"></span>
                    <span class="dbox__title">Total Leager Balance</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dbox dbox--color-2">
                <div class="dbox__icon">
                    <i class="glyphicon glyphicon-arrow-up"></i>
                </div>
                <div class="dbox__body">
                    <span class="dbox__count purchase_due"></span>
                    <span class="dbox__title">Total Purchase Due</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dbox dbox--color-3">
                <div class="dbox__icon">
                    {{-- <i class="glyphicon glyphicon-heart"></i> --}}
                    <i class="glyphicon glyphicon-arrow-down"></i>
                </div>
                <div class="dbox__body">
                    <span class="dbox__count invoice_due"></span>
                    <span class="dbox__title">Total Invoice Due</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dbox dbox--color-4">
                <div class="dbox__icon">
                    <i class="fa fa-cubes" style="color: white"></i>
                    {{-- <i class="fa fa-pie-chart"></i> --}}
                </div>
                <div class="dbox__body">
                    <span class="dbox__count net_profit"></span>
                    <span class="dbox__title">Net Profit</span>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div style="display:flex;margin:1em;">
        <div style="height:400px;width:100%;margin:1em;background:white;">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('Purchase And Sell(Last 7 Days)')])
                <div id="chart1"></div>
            @endcomponent
        </div>
    </div>

    <br />
    <br />
    <div class="row">
        <div class="col-md-6">
            <div style="display:flex;margin:1em;">
                <div style="height:310px;width:100%;margin:1em;">
                    @component('components.widget', ['class' => 'box-primary', 'title' => __('Accounts Payable(Purchase Due)')])
                        <div id="chart3"></div>
                    @endcomponent
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div style="display:flex;margin:1em;">
                <div style="height:310px;width:100%;margin:1em;">
                    @component('components.widget', ['class' => 'box-primary', 'title' => __('Accounts Receivable(Balance Due)')])
                        <div id="chart4"></div>
                    @endcomponent
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;margin:1em;">
        <div style="height:400px;width:100%;margin:1em;background:white;">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('Income And Expense')])
                <div id="chart2"></div>
            @endcomponent
        </div>
    </div>
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
                    url: "/AccountDashboard/get_totals",
                    type: 'get',
                    dataType: 'json',
                    data: {
                        start_date: start,
                        end_date: end,
                    },
                    success: function(response) {
                        $(".invoice_due").html("$" + __intToString(response.invoice_due), true);
                        $(".purchase_due").html("$" + __intToString(response.purchase_due), true);
                        $(".total_invoice").html("$" + __intToString(response.total_invoice), true);
                        $(".opening_balance").html("$" + __intToString(response.opening_balance), true);
                        $(".net_profit").html("$" + __intToString(response.net_profit), true);
                    }
                });

                $.ajax({
                    url: "/AccountDashboard/getreceivacc",
                    type: 'get',
                    dataType: 'json',
                    success: function(response) {
                       
                        $(".leager_balance").html("$" + __intToString(response.account_re), true);
                    }
                });
                
                var options1 = {
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
                      theme: {
                          palette: 'palette3', 
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
                };

                $("#chart1", function() {
                    var url = "/AccountDashboard/getchart?start=" + start + "&end=" + end;
                    axios({
                        method: "GET",
                        url: url,
                    }).then(function(response) {
                        chart1.updateOptions({
                            series: [{
                                    name: "Sell",
                                    data: response.data[0],
                                },
                                {
                                    name: "Purchase",
                                    data: response.data[1],
                                },
                            ],
                            xaxis: {
                                type: "datetime",
                                categories: response.data[2],
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

                var options2 = {
                    colors: ['#87CEFA', '#b84644'],
                    series: [],
                    //   annotations: {
                    //   points: [{
                    //     x: 'Bananas',
                    //     seriesIndex: 0,
                    //     label: {
                    //       borderColor: '#775DD0',
                    //       offsetY: 0,
                    //       style: {
                    //         color: '#fff',
                    //         background: '#775DD0',
                    //       },
                    //       text: 'Bananas are good',
                    //     }
                    //   }]
                    // },
                    chart: {
                        height: 350,
                        type: 'bar',
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 10,
                            columnWidth: '50%',
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        width: 2
                    },

                    grid: {
                        row: {
                            colors: ['#fff', '#f2f2f2']
                        }
                    },
                    xaxis: {
                        labels: {
                            rotate: -45
                        },
                        categories: [],
                        type: 'datetime',
                        tickPlacement: 'on'
                    },
                    tooltip: {
                        x: {
                            format: 'dd/MM/yy'
                        },
                    },
                    // yaxis: {
                    //   title: {
                    //     text: 'Servings',
                    //   },
                    // },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'light',
                            type: "horizontal",
                            shadeIntensity: 0.25,
                            gradientToColors: undefined,
                            inverseColors: true,
                            opacityFrom: 0.85,
                            opacityTo: 0.85,
                            stops: [50, 0, 100]
                        },
                    }
                };

                $("#chart2", function() {
                    var url = "/AccountDashboard/getincomeexpense?start=" + start + "&end=" + end;
                    axios({
                        method: "GET",
                        url: url,
                    }).then(function(response) {
                        chart2.updateOptions({
                            series: [{
                                    name: "Income",
                                    data: response.data[0],
                                },
                                {
                                    name: "Expense",
                                    data: response.data[1],
                                },
                            ],
                            xaxis: {
                                type: "datetime",
                                categories: response.data[2],
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

                var options3 = {
                    series: [],
                    chart: {
                        height: 250,
                        type: 'radialBar',
                        toolbar: {
                            show: true
                        }
                    },
                    plotOptions: {
                        radialBar: {
                            startAngle: -135,
                            endAngle: 225,
                            hollow: {
                                margin: 0,
                                size: '70%',
                                background: '#fff',
                                image: undefined,
                                imageOffsetX: 0,
                                imageOffsetY: 0,
                                position: 'front',
                                dropShadow: {
                                    enabled: true,
                                    top: 3,
                                    left: 0,
                                    blur: 4,
                                    opacity: 0.24
                                }
                            },
                            track: {
                                background: '#fff',
                                strokeWidth: '67%',
                                margin: 0, // margin is in pixels
                                dropShadow: {
                                    enabled: true,
                                    top: -3,
                                    left: 0,
                                    blur: 4,
                                    opacity: 0.35
                                }
                            },

                            dataLabels: {
                                show: true,
                                name: {
                                    offsetY: -10,
                                    show: true,
                                    color: '#888',
                                    fontSize: '17px'
                                },
                                value: {
                                    formatter: function(val) {
                                        return "$" + (val).toLocaleString();
                                    },
                                    color: '#111',
                                    fontSize: '22px',
                                    show: true,
                                    offsetY: -10,
                                }
                            }
                        }
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'dark',
                            type: 'horizontal',
                            shadeIntensity: 0.5,
                            gradientToColors: ['#ABE5A1'],
                            inverseColors: true,
                            opacityFrom: 1,
                            opacityTo: 1,
                            stops: [0, 100]
                        }
                    },
                    stroke: {
                        lineCap: 'round'
                    },
                    labels: [' '],
                }

                $("#chart3", function() {
                    var url = "/AccountDashboard/getaccount?start=" + start + "&end=" + end;
                    axios({
                        method: "GET",
                        url: url,
                    }).then(function(response) {
                        chart3.updateOptions({
                            series: [response.data['account_pay']],
                        });
                    });
                });

                var options4 = {
                    series: [],
                    chart: {
                        height: 250,
                        type: 'radialBar',
                        toolbar: {
                            show: true
                        }
                    },
                    plotOptions: {
                        radialBar: {
                            startAngle: -135,
                            endAngle: 225,
                            hollow: {
                                margin: 0,
                                size: '70%',
                                background: '#fff',
                                image: undefined,
                                imageOffsetX: 0,
                                imageOffsetY: 0,
                                position: 'front',
                                dropShadow: {
                                    enabled: true,
                                    top: 3,
                                    left: 0,
                                    blur: 4,
                                    opacity: 0.24
                                }
                            },
                            track: {
                                background: '#fff',
                                strokeWidth: '67%',
                                margin: 0, // margin is in pixels
                                dropShadow: {
                                    enabled: true,
                                    top: -3,
                                    left: 0,
                                    blur: 4,
                                    opacity: 0.35
                                }
                            },

                            dataLabels: {
                                show: true,
                                name: {
                                    offsetY: -10,
                                    show: true,
                                    color: '#888',
                                    fontSize: '17px'
                                },
                                value: {
                                    formatter: function(val) {
                                        return "$" + (val).toLocaleString();
                                    },
                                    color: '#111',
                                    fontSize: '22px',
                                    show: true,
                                    offsetY: -10,
                                }
                            }
                        }
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'dark',
                            type: 'horizontal',
                            shadeIntensity: 0.5,
                            gradientToColors: ['#ABE5A1'],
                            inverseColors: true,
                            opacityFrom: 1,
                            opacityTo: 1,
                            stops: [0, 100]
                        }
                    },
                    stroke: {
                        lineCap: 'round'
                    },
                    labels: [' '],
                }

                $("#chart4", function() {
                    var url = "/AccountDashboard/getreceivacc";
                    axios({
                        method: "GET",
                        url: url,
                    }).then(function(response) {
                        chart4.updateOptions({
                            series: [response.data['account_re']],
                        });
                    });
                });
                // /dashboard-a/getincomeexpense
                var chart1 = new ApexCharts(document.querySelector("#chart1"), options1);
                chart1.render();
                var chart2 = new ApexCharts(document.querySelector("#chart2"), options2);
                chart2.render();
                var chart3 = new ApexCharts(document.querySelector("#chart3"), options3);
                chart3.render();
                var chart4 = new ApexCharts(document.querySelector("#chart4"), options4);
                chart4.render();
            }
        });

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

        function __num_for(value) {
            var newValue = value;
            if (value >= 1000) {
                var suffixes = ["", "K", "M", "B", "T"];
                var suffixNum = Math.floor(("" + value).length / 3);
                var shortValue = '';
                for (var precision = 2; precision >= 1; precision--) {
                    shortValue = parseFloat((suffixNum != 0 ? (value / Math.pow(1000, suffixNum)) : value).toPrecision(
                        precision));
                    var dotLessShortValue = (shortValue + '').replace(/[^a-zA-Z 0-9]+/g, '');
                    if (dotLessShortValue.length <= 2) {
                        break;
                    }
                }
                if (shortValue % 1 != 0) shortValue = shortValue.toFixed(2);
                newValue = shortValue + suffixes[suffixNum];
            }
            return newValue;
        }
    </script>


@endsection
