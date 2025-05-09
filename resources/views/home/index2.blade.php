@extends('layouts.app')
@section('title', __('home.home'))
@section('css')
    <link rel="stylesheet" href="/fonts/google-fonts/google-fonts.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:900i');

        body{
            font-family: 'DM Sans';
        }
        .product_image_placeholder{
            height: 45px;
            width: 45px;
            background: url(/img/default.png);
            background-size: cover;
        }
        .island_header{
            background: #111347; /* fallback for old browsers */
            background: linear-gradient(195deg, white,#0baf4e);
        }
        .island_header .logo,.island_header nav{
            background: transparent !important;
        }
        .island_header .btn-success{
            background: transparent;
            background-size: 200% 100%;
            transition: background 0.3s ease-in;
            border: none;
            color: #008505 !important;
        }
        .island_header .btn-success:hover{
            background-position: 100%;
        }
        .sidebar-menu>li>a>i{
            /* display: none; */
            width: 25px;
            font-size: 16px;
            text-align: center;
            margin-left: -12px;
            margin-right: 5px;
        }
        .sidebar-menu span{
            font-size: 13px;
            font-weight: 600;
        }
        .sidebar-menu .menu-open span{
            font-size: 13px;
            font-weight: 400;
        }
        .treeview-menu a::before {
            content: "";
        }
        .main-header .logo .logo-lg {
            font-family: 'DM Sans','source sans pro';
            font-weight: bolder;
        }
        .sidebar-menu>li.active>a {
            border: none !important;
            background: white !important;
        }
        .sidebar-menu li>a>.pull-right-container>.fa-angle-left {
            display: block !important;
        }
        /* .sidebar-menu>li>a {
            font-weight: 400!important;
            padding: 10px;
            display: flex;
            align-items: center;
            border-radius: 5px;
            margin: 5px 10px;
        } */
        .sidebar-menu>li>a>img {
            width: 20px;
            margin: 0 15px 0 5px;
        }
        .active>a>.pull-right-container>.fa-angle-left {
            transform: rotate(270deg);
        }
        .hue_animation{
            -webkit-animation: filter-animation 8s infinite;
            animation: filter-animation 8s infinite;
        }

        @-webkit-keyframes filter-animation {
            0% {
                -webkit-filter: hue-rotate(0deg);
            }

            50% {
                -webkit-filter: hue-rotate(100deg);
            }

            100% {
                -webkit-filter: hue-rotate(0deg);
            }
        }

        @keyframes filter-animation {
            0% {
                filter: hue-rotate(0deg);
            }

            50% {
                filter: hue-rotate(100deg);
            }

            100% {
                filter: hue-rotate(0deg);
            }
        }
        /* Dashboard & Buttons */

        .new_display {
            background: transparent !important;
            background-size: cover !important;
            background-position: center !important;
            padding: 80px 15px 100px 15px !important;
        }

        .wrapper1 {
            margin-top: 15px;
            display: flex;
            justify-content: center;
        }

        .cta {
            display: flex;
            padding: 10px 45px;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            color: white;
            background: #59ba80;
            transition: 1s;
            box-shadow: 6px 6px 0 black;
            transform: skewX(-15deg);
        }

        .cta:focus {
            outline: none;
        }

        .cta:hover {
            transition: 0.5s;
            box-shadow: 10px 10px 0 #b5f5b7;
        }

        .cta span:nth-child(2) {
            transition: 0.5s;
            margin-right: 0px;
        }

        .cta:hover span:nth-child(2) {
            transition: 0.5s;
            margin-right: 45px;
        }

        .wrapper1 span {
            transform: skewX(15deg)
        }

        .wrapper1 span:nth-child(2) {
            width: 20px;
            margin-left: 30px;
            position: relative;
            top: 12%;
        }

        /**************SVG****************/

        .wrapper1 path.one {
            transition: 0.4s;
            transform: translateX(-60%);
        }

        .wrapper1 path.two {
            transition: 0.5s;
            transform: translateX(-30%);
        }

        .cta:hover path.three {
            animation: color_anim 1s infinite 0.2s;
        }

        .cta:hover path.one {
            transform: translateX(0%);
            animation: color_anim 1s infinite 0.6s;
        }

        .cta:hover path.two {
            transform: translateX(0%);
            animation: color_anim 1s infinite 0.4s;
        }

        /* SVG animations */

        @keyframes color_anim {
            0% {
                fill: white;
            }

            50% {
                fill: #b5f5b7;
            }

            100% {
                fill: white;
            }
        }

        .sidebar-toggle:hover{
            background: #00000033 !important;
        }

        .select2-container{
            width: 100%!important;
        }
        .select2-search__field{
            width: 100%!important;
        }
        .sidebar-menu{
            overflow: visible;
        }
        .content-wrapper {
            background: linear-gradient(0deg, #f7faff 70%, transparent);
        }
        .h-100-max-cs{
            height: 100% !important;
        }
        .quick-btns{
            /* margin: 10px 0; */
            display: grid;
            justify-content: space-evenly;
            grid-template-columns: 1fr 1fr 1fr;
            grid-gap: 5px;
            width: 100%;
        }
        .quick-btns .btn {
            display: flex;
            height: 40px;
            align-items: center;
            justify-content: center;
            background: white;
        }
        .quick-btns .btn svg{
            height: 20px;
        }
        .content-header>h1, .main_h2{
            font-size: xx-large;
            margin: 15px 0;
            font-weight: 600;
            color: black;
        }
        .main_h4{
            font-size: x-large;
            margin: 10px 0;
            font-weight: 600;
            color: black;
        }
        .skin-black .wrapper {
            /* background: linear-gradient(135deg, #6E61EF, #FF5963, #EE8B60) !important; */
            background: linear-gradient(135deg, #61efac, #599cff, #60eec2) !important;
        }
        .box , .info-box{
            background: #ffffffad;
        }
        .user-info:hover, .open>.user-info{
            background: #dedede;
            border-radius: 5px;
            cursor: pointer;
        }
        .fadeInDown {
            -webkit-animation-name: fadeInDown;
            animation-name: fadeInDown;
            -webkit-animation-duration: 0.3s;
            animation-duration: 0.3s;
            -webkit-animation-fill-mode: both;
            animation-fill-mode: both;
        }
        .skin-black .main-header .navbar{
            background: transparent !important;
        }
        @-webkit-keyframes fadeInDown {
            0% {
                opacity: 0;
                -webkit-transform: translate3d(0, -100%, 0);
                transform: translate3d(0, -100%, 0);
            }
            100% {
                opacity: 1;
                -webkit-transform: none;
                transform: none;
            }
        }
        @keyframes fadeInDown {
            0% {
                opacity: 0;
                -webkit-transform: translate3d(0, -100%, 0);
                transform: translate3d(0, -100%, 0);
            }
            100% {
                opacity: 1;
                -webkit-transform: none;
                transform: none;
            }
        }
        .sidebar-quick-access{
            display: flex;
            background: white;
            margin-bottom: 20px;
            border-top: solid 1px lightblue;
            border-bottom: solid 1px lightblue;
            padding: 0px 15px;
        }
        .sidebar-quick-access a:hover{
            background: #dedede;
        }

        .content-wrapper::after {
                content: "Made with 💖";
                position: absolute;
                right: 15px;
                bottom: 15px;
            }
    </style>

@endsection
@section('content')
    <br/>
    <div class="col-sm-12 col-md-6">
        <div style="padding:0 30px;">
            <h3 class="main_h2">Good Evening,</h3>
            <h3 class="main_h4">{{ Session::get('user.first_name') }}✨</h3>
        </div>
    </div>
    <div class="col-sm-12 col-md-6" style="padding:30px;display: flex;justify-content:end;">
        <div class="form-group pull-right" style="width: 250px;">
            <div class="col-md-12">
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
            <div class="col-md-4 hide">
                <div class="form-group" style="margin-top: 25px;">
                    <button class="btn btn-primary" id="submitData">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-md-6">
        <div style="background:white;
        align-items: center;
        display: flex;
        justify-content: center;
        border: solid 1px lightgray;
        flex-direction:column;
        border-radius: 8px;
        overflow: auto;">
            <h3>Order Queue Details</h3>
            <br/>
            <div id="chart1" style="display:flex;justify-content:center;align-items:center;"></div>
            <br/>
        </div>
    </div>

    <div class="col-sm-12 col-md-6">
        <div style="background:white;
        align-items: center;
        display: flex;
        justify-content: center;
        border: solid 1px lightgray;
        flex-direction:column;
        border-radius: 8px;
        overflow: auto;">
            <h3>Payments Received</h3>
            <br/>
            <p style="margin-top: -40px;">
                <h4>
                <strong>Total Receivable:</strong> {{ $totalReceivable }}
            </h4>
            </p>
            <div id="chart2" style="display:flex;justify-content:center;align-items:center;"></div>
            <br/>
        </div>
    </div>

@stop
@section('javascript')
    <script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>
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
            getReportdata();
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
                    width: 450,
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
                colors: ['#87CEFA', '#98FB98', '#ffffaf', '#F08080', '#FFB6C1', '#AFEEEE', '#D8BFD8'],
                labels: ['Cheque', 'Cash', 'Zelle', 'Credit Memo', 'Bank', 'Credit Card', 'Other'],
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
                var url1 = "/home/Pick_PackDashboard/paymentchart?start=" + start + "&end=" + end;
                axios({
                    method: "GET",
                    url: url1,
                }).then(function(response) {
                    console.log("Received data:", response.data);

                    var paymentData = response.data.slice(0, 7).map(function(value) {
                        return parseFloat(value);

                    });
                    let val1 = "$" + parseFloat(response.data[0]).toLocaleString();
                    let val2 = "$" + parseFloat(response.data[1]).toLocaleString();
                    let val3 = "$" + parseFloat(response.data[2]).toLocaleString();
                    let val4 = "$" + parseFloat(response.data[3]).toLocaleString();
                    let val5 = "$" + parseFloat(response.data[4]).toLocaleString();
                    let val6 = "$" + parseFloat(response.data[5]).toLocaleString();

                    console.log(val1);
                    chart2.updateOptions({
                        chart: {
                            // background: 'red',
                            sparkline: {
                                enabled: true
                            },
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
                                            label: 'Total Payments',
                                            color: '#373d3f',
                                            formatter: function(val) {
                                                var total = response.data[7];
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

        }
    });
</script>
@endsection
