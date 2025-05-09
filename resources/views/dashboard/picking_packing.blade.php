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

        .box.box-primary {
            border-top-color: #f5f2f2;
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
                        {!! Form::text('all_date_filter', @format_date('yesterday') . ' ~ ' . @format_date('yesterday'), [
                            'placeholder' => __('lang_v1.select_a_date_range'),
                            'class' => 'form-control',
                            'id' => 'all_date_filter',
                            'readonly',
                        ]) !!}

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
 
@endif

<div style="display:flex;margin:1em;">
    <div style="height:400px;width:50%;margin:1em;background:white;">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Picking & Packing')])
            <div id="chart1"></div>
        @endcomponent
    </div>
    <div style="height:400px;width:50%;margin:1em;background:white;">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Payment')])
        <p style="margin-top: -40px;">
                <h4>
                <strong>Total Receivable:</strong> {{ $totalReceivable }}
            </h4>
            </p>
            <div id="chart2"></div>
        @endcomponent
    </div>
</div>

<br />
 <div style="display:flex;margin:1em;">
    <div style="width:100%;margin:1em;">
        <div class="panel with-nav-tabs panel-info">
            <div class="panel-heading">
                <h3 style="color: #31708f">Orders received from website</h3>
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#wesiteOrder" data-toggle="tab">@lang('This Week')</a></li>
                </ul>
            </div>
            <div class="panel-body">
                <div class="tab-content" style="height: 280px;">
                    <div class="tab-pane fade in active" id="wesiteOrder">
                        <div class="table-responsive">
                            <div id="error"></div>
                            <table class="table table-hover">

                                <thead id="wesiteOrder_head">

                                </thead>
                                <tbody id="wesiteOrder_table">

                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                startDate: moment().subtract(1, 'days').startOf("day"),
                endDate: moment().subtract(1, 'days').endOf("day"),
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
 $.ajax({
            url: "/Pick_PackDashboard/WeeklyWebsiteOrders",
            type: "GET",
            dataType: "json",
            success: function(response) {
                var tableSearch = $('#wesiteOrder_table');
                var final_head = $('#wesiteOrder_head');
                final_head.html('');
                tableSearch.html('');

                if (!jQuery.isEmptyObject(response)) {
                    final_head.append("<tr><th style = 'width: 10%;'>" + 'No' + "</th>" +
                        "<th style = 'width: 20%;'>" + 'Days' + "</th>" +
                        "<th style = 'width: 20%;'>" + 'Orders' + "</th>" + "<th>" + 'Amount' +
                        "</th></tr>");
                    $.each(response, function(index, value) {
                        var no = index + 1;
                        // tableSearch.append("<p>"no_recent_transactions"</p>");
                        tableSearch.append("<tr> <td> " +
                            no +
                            "</td>" +
                            "<td style=''>" +
                            value.day_of_week +
                            "</td>" +
                            "<td style=''>" +
                            value.orders +
                            "</td><td class='display_currency'>" +
                            __currency_trans_from_en(value.total_draft_amount) +
                            "</td></tr>");

                    });
                } else {
                    var er = 'Data Not Found!';
                    tableSearch.append("<p class='text-center'>" + er +
                        "</p>");
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
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
                series: [],
                chart: {
                    width: 550,
                    type: 'donut',
                    dropShadow: {
                        enabled: true,
                        color: '#111',
                        top: -1,
                        left: 3,
                        blur: 3,
                        opacity: 0.2
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    },
                },
                stroke: {
                    width: 0,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    showAlways: true,
                                    show: true
                                }
                            }
                        }
                    }
                },

                colors: ['#f5365c', '#11cdef', '#ffad46', '#0077B5', '#2dce89'],
                labels: ['Waiting For Picking', 'Picking Started', 'Picking Complete', 'Packing Started',
                    'Packing Complete', 'Total Order'
                ],
                dataLabels: {
                    dropShadow: {
                        blur: 3,
                        opacity: 0.8
                    }
                },
                // fill: {
                //     type: 'pattern',
                //     opacity: 1,
                //     pattern: {
                //         enabled: true,
                //         style: ['verticalLines', 'squares', 'horizontalLines', 'circles', 'slantedLines'],
                //     },
                // },
                fill: {
                    type: 'gradient',
                },
                states: {
                    hover: {
                        filter: 'none'
                    }
                },
                theme: {
                    palette: 'palette2'
                },
                // title: {
                //     text: "Picking Packing"
                // },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 480
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                legend: {
                    position: 'right',
                    offsetY: 0,
                    height: 400
                }
            };

            $("#chart1", function() {
                var url1 = "/Pick_PackDashboard/pickingchart?start=" + start + "&end=" + end;
                axios({
                    method: "GET",
                    url: url1,
                }).then(function(response) {
                    console.log("Received data:", response.data);

                    var total = response.data[5]; // Get the total from response.data[5]

                    chart1.updateOptions({
                        chart: {
                            // background: 'red',
                            sparkline: {
                                enabled: true
                            },
                            width: 520,
                            type: 'donut',
                            dropShadow: {
                                enabled: true,
                                color: '#111',
                                top: -1,
                                left: 3,
                                blur: 3,
                                opacity: 0.2
                            },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800,
                                animateGradually: {
                                    enabled: true,
                                    delay: 150
                                },
                                dynamicAnimation: {
                                    enabled: true,
                                    speed: 350
                                }
                            },
                        },
                        series: [response.data[0], response.data[1], response.data[2],
                            response.data[3], response.data[4],
                            // Update the series with the total
                        ],
                        dataLabels: {
                            formatter: function(val, opts) {
                                return opts.w.config.series[opts.seriesIndex]
                            },
                        },
                        labels: ['Waiting For Picking', 'Picking Started',
                            'Picking Complete', 'Packing Started',
                            'Packing Complete',
                        ],

                        plotOptions: {
                            pie: {
                                // customScale: 1.5,
                                donut: {
                                    labels: {
                                        show: true,
                                        total: {
                                            showAlways: true,
                                            show: true,
                                            label: 'Total Orders',
                                            color: '#373d3f',
                                            formatter: function(w) {
                                                // Calculate the total from response.data[5]
                                                var total = response.data[5];
                                                return total; // Return the total
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                chart: {
                                    width: 480
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }],
                        legend: {
                            position: 'right',
                            offsetY: 0,
                            height: 400
                        },
                        noData: {
                            text: 'Data Not Available',
                            align: "center",
                            verticalAlign: "middle",
                        },

                    });

                }).catch(function(error) {
                    console.error("Error fetching chart data:", error);
                });
            });
            var chart1 = new ApexCharts(document.querySelector("#chart1"), options1);
            chart1.render();

            var options2 = {
                series: [],
                chart: {
                    width: 480,
                    type: 'donut',
                    dropShadow: {
                        enabled: true,
                        color: '#111',
                        top: -1,
                        left: 3,
                        blur: 3,
                        opacity: 0.2
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    },
                },
                stroke: {
                    width: 0,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    showAlways: true,
                                    show: true
                                }
                            }
                        }
                    }
                },
                colors: ['#4BC3E6', '#62ACEA', '#8D95EB', '#B57BED', '#CA6CD8'],
                labels: ['Cheque', 'Cash', 'Zelle', 'Credit', 'Other'],
                dataLabels: {
                    dropShadow: {
                        blur: 3,
                        opacity: 0.8
                    }
                },
                fill: {
                    type: 'gradient',
                },
                states: {
                    hover: {
                        filter: 'none'
                    }
                },
                theme: {
                    palette: 'palette2'
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 480
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                legend: {
                    position: 'right',
                    offsetY: 0,
                    height: 400
                }
            };

            $("#chart2", function() {
                var url1 = "/Pick_PackDashboard/paymentchart?start=" + start + "&end=" + end;
                axios({
                    method: "GET",
                    url: url1,
                }).then(function(response) {
                    console.log("Received data:", response.data);

                    var paymentData = response.data.slice(0, 5).map(function(value) {
                        return parseFloat(value);

                    });
                    let val1 = "$" + parseFloat(response.data[0]).toLocaleString();
                    let val2 = "$" + parseFloat(response.data[1]).toLocaleString();
                    let val3 = "$" + parseFloat(response.data[2]).toLocaleString();
                    let val4 = "$" + parseFloat(response.data[3]).toLocaleString();

                    console.log(val1);
                    chart2.updateOptions({
                        chart: {
                            // background: 'red',
                            sparkline: {
                                enabled: true
                            },
                            width: 458,
                            type: 'donut',
                            dropShadow: {
                                enabled: true,
                                color: '#111',
                                top: -1,
                                left: 3,
                                blur: 3,
                                opacity: 0.2
                            },

                        },
                        series: paymentData,
                        dataLabels: {
                            formatter: function(val, opts) {
                                var data = opts.w.config.series[opts.seriesIndex];
                                var formattedTotal = parseFloat(data)
                                    .toLocaleString();
                                return "$" + formattedTotal;
                            },
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    labels: {
                                        show: true,
                                        total: {
                                            showAlways: true,
                                            show: true,
                                            label: 'Total Payment',
                                            color: '#373d3f',
                                            formatter: function(val) {
                                                var total = response.data[5];
                                                return "$" + parseFloat(total)
                                                    .toLocaleString();
                                            },
                                        }
                                    }
                                }
                            }
                        },
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                chart: {
                                    width: 480
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }],
                        noData: {
                            text: 'Data Not Available',
                            align: "center",
                            verticalAlign: "middle",
                        },
                    });

                }).catch(function(error) {
                    console.error("Error fetching chart data:", error);
                });
            });

            var chart2 = new ApexCharts(document.querySelector("#chart2"), options2);
            chart2.render();
            // var options1 = {
            //     series: [],
            //     chart: {
            //         height: 390,
            //         type: 'radialBar',
            //         background: '#fff'
            //     },
            //     noData: {
            //         text: 'Loading...',
            //         align: "center",
            //         verticalAlign: "middle",
            //     },
            //     plotOptions: {
            //         radialBar: {
            //             offsetY: 0,
            //             startAngle: 0,
            //             endAngle: 270,
            //             hollow: {
            //                 margin: 5,
            //                 size: '30%',
            //                 background: 'transparent',
            //                 image: undefined,
            //             },
            //             dataLabels: {
            //                 name: {
            //                     show: false,
            //                 },
            //                 value: {
            //                     show: false,
            //                 }
            //             },
            //             barLabels: {
            //                 enabled: true,
            //                 useSeriesColors: true,
            //                 margin: 8,
            //                 fontSize: '16px',
            //                 formatter: function(seriesName, opts) {
            //                     return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex]
            //                 },
            //             },
            //         }
            //     },
            //     colors: ['#1ab7ea', '#0084ff', '#39539E', '#0077B5', '#008000'],
            //     labels: ['Waiting For Picking', 'Picking Started', 'Picking Complete', 'Packing Started',
            //         'Packing Complete', 'Total Order'
            //     ],
            //     responsive: [{
            //         breakpoint: 480,
            //         options: {
            //             legend: {
            //                 show: false
            //             }
            //         }
            //     }]
            // };

            // $("#chart1", function() {
            //     var url1 = "/Pick_PackDashboard/pickingchart?start=" + start + "&end=" + end;
            //     axios({
            //         method: "GET",
            //         url: url1,
            //     }).then(function(response) {
            //         console.log("Received data:", response.data);

            //         if (Array.isArray(response.data) && response.data.length === 6) {
            //             chart1.updateOptions({
            //                 chart: {
            //                     height: 390,
            //                     type: 'radialBar',
            //                     // background: '#000000'
            //                 },
            //                 series: [
            //                     response.data[0], response.data[1], response.data[
            //                         2],
            //                     response.data[3], response.data[4], response.data[5]
            //                 ],
            //                 noData: {
            //                     text: 'Data Not Available',
            //                     align: "center",
            //                     verticalAlign: "middle",
            //                 },
            //             });
            //         } else {
            //             console.error("Invalid data format:", response.data);
            //         }
            //     }).catch(function(error) {
            //         console.error("Error fetching chart data:", error);
            //     });
            // });

            // var chart1 = new ApexCharts(document.querySelector("#chart1"), options1);
            // chart1.render();

        }
    });
</script>



@endsection
