@extends('layouts.app')
@section('title', __('home.home'))
@section('css')
    <link rel="stylesheet" href="/fonts/google-fonts/google-fonts.css" />
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:900i');

        .sidebar-menu>li>a>i{
            width: 25px;
            font-size: 16px;
            text-align: center;
            margin-left: -12px;
            margin-right: 5px;
        }

        .new_display {
            background: url(/img/bg1.jpg) !important;
            background-size: cover !important;
            padding: 80px 15px 100px 15px !important;
        }

        .main-header .logo .logo-lg {
            font-family: 'source sans pro';
            font-weight: bolder;
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
            background: #6225E6;
            transition: 1s;
            box-shadow: 6px 6px 0 black;
            transform: skewX(-15deg);
        }

        .cta:focus {
            outline: none;
        }

        .cta:hover {
            transition: 0.5s;
            box-shadow: 10px 10px 0 #FBC638;
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
                fill: #FBC638;
            }

            100% {
                fill: white;
            }
        }
    </style>

@endsection
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header content-header-custom new_display" style="">

    <div class="hide" style="padding:15px;display: flex;justify-content:end;margin-top:-80px;">
        <div class="form-group pull-right">
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
        <h1 style="text-align: center;color: #656565 !important;">
            Welcome Back, {{ Session::get('user.first_name') }}
        </h1>
        @if (auth()->user()->can('dashboard.data'))
            <h3 style="text-align: center;">
                <a href="{{ url('/home/graph') }}" style="border-bottom: solid 1px;padding: 5px;">Click Here For More Details</a>
            </h3>
        @endif
    </section>
    <!-- designed by me... enjoy! -->
    @if (!auth()->user()->can('delivery.exclusive'))

    <div class="wrapper1">
        <a class="cta" href="/contacts?type=customer">
            <span style="color: white;">Customers</span>
            <span>
                <svg width="66px" height="20px" viewBox="0 0 66 43" version="1.1" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink">
                    <g id="arrow" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <path class="one"
                            d="M40.1543933,3.89485454 L43.9763149,0.139296592 C44.1708311,-0.0518420739 44.4826329,-0.0518571125 44.6771675,0.139262789 L65.6916134,20.7848311 C66.0855801,21.1718824 66.0911863,21.8050225 65.704135,22.1989893 C65.7000188,22.2031791 65.6958657,22.2073326 65.6916762,22.2114492 L44.677098,42.8607841 C44.4825957,43.0519059 44.1708242,43.0519358 43.9762853,42.8608513 L40.1545186,39.1069479 C39.9575152,38.9134427 39.9546793,38.5968729 40.1481845,38.3998695 C40.1502893,38.3977268 40.1524132,38.395603 40.1545562,38.3934985 L56.9937789,21.8567812 C57.1908028,21.6632968 57.193672,21.3467273 57.0001876,21.1497035 C56.9980647,21.1475418 56.9959223,21.1453995 56.9937605,21.1432767 L40.1545208,4.60825197 C39.9574869,4.41477773 39.9546013,4.09820839 40.1480756,3.90117456 C40.1501626,3.89904911 40.1522686,3.89694235 40.1543933,3.89485454 Z"
                            fill="#FFFFFF"></path>
                        <path class="two"
                            d="M20.1543933,3.89485454 L23.9763149,0.139296592 C24.1708311,-0.0518420739 24.4826329,-0.0518571125 24.6771675,0.139262789 L45.6916134,20.7848311 C46.0855801,21.1718824 46.0911863,21.8050225 45.704135,22.1989893 C45.7000188,22.2031791 45.6958657,22.2073326 45.6916762,22.2114492 L24.677098,42.8607841 C24.4825957,43.0519059 24.1708242,43.0519358 23.9762853,42.8608513 L20.1545186,39.1069479 C19.9575152,38.9134427 19.9546793,38.5968729 20.1481845,38.3998695 C20.1502893,38.3977268 20.1524132,38.395603 20.1545562,38.3934985 L36.9937789,21.8567812 C37.1908028,21.6632968 37.193672,21.3467273 37.0001876,21.1497035 C36.9980647,21.1475418 36.9959223,21.1453995 36.9937605,21.1432767 L20.1545208,4.60825197 C19.9574869,4.41477773 19.9546013,4.09820839 20.1480756,3.90117456 C20.1501626,3.89904911 20.1522686,3.89694235 20.1543933,3.89485454 Z"
                            fill="#FFFFFF"></path>
                        <path class="three"
                            d="M0.154393339,3.89485454 L3.97631488,0.139296592 C4.17083111,-0.0518420739 4.48263286,-0.0518571125 4.67716753,0.139262789 L25.6916134,20.7848311 C26.0855801,21.1718824 26.0911863,21.8050225 25.704135,22.1989893 C25.7000188,22.2031791 25.6958657,22.2073326 25.6916762,22.2114492 L4.67709797,42.8607841 C4.48259567,43.0519059 4.17082418,43.0519358 3.97628526,42.8608513 L0.154518591,39.1069479 C-0.0424848215,38.9134427 -0.0453206733,38.5968729 0.148184538,38.3998695 C0.150289256,38.3977268 0.152413239,38.395603 0.154556228,38.3934985 L16.9937789,21.8567812 C17.1908028,21.6632968 17.193672,21.3467273 17.0001876,21.1497035 C16.9980647,21.1475418 16.9959223,21.1453995 16.9937605,21.1432767 L0.15452076,4.60825197 C-0.0425130651,4.41477773 -0.0453986756,4.09820839 0.148075568,3.90117456 C0.150162624,3.89904911 0.152268631,3.89694235 0.154393339,3.89485454 Z"
                            fill="#FFFFFF"></path>
                    </g>
                </svg>
            </span>
        </a>

        <a class="cta" href="/products">
            <span style="color: white;">Products</span>
            <span>
                <svg width="66px" height="20px" viewBox="0 0 66 43" version="1.1" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink">
                    <g id="arrow" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <path class="one"
                            d="M40.1543933,3.89485454 L43.9763149,0.139296592 C44.1708311,-0.0518420739 44.4826329,-0.0518571125 44.6771675,0.139262789 L65.6916134,20.7848311 C66.0855801,21.1718824 66.0911863,21.8050225 65.704135,22.1989893 C65.7000188,22.2031791 65.6958657,22.2073326 65.6916762,22.2114492 L44.677098,42.8607841 C44.4825957,43.0519059 44.1708242,43.0519358 43.9762853,42.8608513 L40.1545186,39.1069479 C39.9575152,38.9134427 39.9546793,38.5968729 40.1481845,38.3998695 C40.1502893,38.3977268 40.1524132,38.395603 40.1545562,38.3934985 L56.9937789,21.8567812 C57.1908028,21.6632968 57.193672,21.3467273 57.0001876,21.1497035 C56.9980647,21.1475418 56.9959223,21.1453995 56.9937605,21.1432767 L40.1545208,4.60825197 C39.9574869,4.41477773 39.9546013,4.09820839 40.1480756,3.90117456 C40.1501626,3.89904911 40.1522686,3.89694235 40.1543933,3.89485454 Z"
                            fill="#FFFFFF"></path>
                        <path class="two"
                            d="M20.1543933,3.89485454 L23.9763149,0.139296592 C24.1708311,-0.0518420739 24.4826329,-0.0518571125 24.6771675,0.139262789 L45.6916134,20.7848311 C46.0855801,21.1718824 46.0911863,21.8050225 45.704135,22.1989893 C45.7000188,22.2031791 45.6958657,22.2073326 45.6916762,22.2114492 L24.677098,42.8607841 C24.4825957,43.0519059 24.1708242,43.0519358 23.9762853,42.8608513 L20.1545186,39.1069479 C19.9575152,38.9134427 19.9546793,38.5968729 20.1481845,38.3998695 C20.1502893,38.3977268 20.1524132,38.395603 20.1545562,38.3934985 L36.9937789,21.8567812 C37.1908028,21.6632968 37.193672,21.3467273 37.0001876,21.1497035 C36.9980647,21.1475418 36.9959223,21.1453995 36.9937605,21.1432767 L20.1545208,4.60825197 C19.9574869,4.41477773 19.9546013,4.09820839 20.1480756,3.90117456 C20.1501626,3.89904911 20.1522686,3.89694235 20.1543933,3.89485454 Z"
                            fill="#FFFFFF"></path>
                        <path class="three"
                            d="M0.154393339,3.89485454 L3.97631488,0.139296592 C4.17083111,-0.0518420739 4.48263286,-0.0518571125 4.67716753,0.139262789 L25.6916134,20.7848311 C26.0855801,21.1718824 26.0911863,21.8050225 25.704135,22.1989893 C25.7000188,22.2031791 25.6958657,22.2073326 25.6916762,22.2114492 L4.67709797,42.8607841 C4.48259567,43.0519059 4.17082418,43.0519358 3.97628526,42.8608513 L0.154518591,39.1069479 C-0.0424848215,38.9134427 -0.0453206733,38.5968729 0.148184538,38.3998695 C0.150289256,38.3977268 0.152413239,38.395603 0.154556228,38.3934985 L16.9937789,21.8567812 C17.1908028,21.6632968 17.193672,21.3467273 17.0001876,21.1497035 C16.9980647,21.1475418 16.9959223,21.1453995 16.9937605,21.1432767 L0.15452076,4.60825197 C-0.0425130651,4.41477773 -0.0453986756,4.09820839 0.148075568,3.90117456 C0.150162624,3.89904911 0.152268631,3.89694235 0.154393339,3.89485454 Z"
                            fill="#FFFFFF"></path>
                    </g>
                </svg>
            </span>
        </a>

        <a class="cta" href="/sells">
            <span style="color: white;">Orders</span>
            <span>
                <svg width="66px" height="20px" viewBox="0 0 66 43" version="1.1" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink">
                    <g id="arrow" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <path class="one"
                            d="M40.1543933,3.89485454 L43.9763149,0.139296592 C44.1708311,-0.0518420739 44.4826329,-0.0518571125 44.6771675,0.139262789 L65.6916134,20.7848311 C66.0855801,21.1718824 66.0911863,21.8050225 65.704135,22.1989893 C65.7000188,22.2031791 65.6958657,22.2073326 65.6916762,22.2114492 L44.677098,42.8607841 C44.4825957,43.0519059 44.1708242,43.0519358 43.9762853,42.8608513 L40.1545186,39.1069479 C39.9575152,38.9134427 39.9546793,38.5968729 40.1481845,38.3998695 C40.1502893,38.3977268 40.1524132,38.395603 40.1545562,38.3934985 L56.9937789,21.8567812 C57.1908028,21.6632968 57.193672,21.3467273 57.0001876,21.1497035 C56.9980647,21.1475418 56.9959223,21.1453995 56.9937605,21.1432767 L40.1545208,4.60825197 C39.9574869,4.41477773 39.9546013,4.09820839 40.1480756,3.90117456 C40.1501626,3.89904911 40.1522686,3.89694235 40.1543933,3.89485454 Z"
                            fill="#FFFFFF"></path>
                        <path class="two"
                            d="M20.1543933,3.89485454 L23.9763149,0.139296592 C24.1708311,-0.0518420739 24.4826329,-0.0518571125 24.6771675,0.139262789 L45.6916134,20.7848311 C46.0855801,21.1718824 46.0911863,21.8050225 45.704135,22.1989893 C45.7000188,22.2031791 45.6958657,22.2073326 45.6916762,22.2114492 L24.677098,42.8607841 C24.4825957,43.0519059 24.1708242,43.0519358 23.9762853,42.8608513 L20.1545186,39.1069479 C19.9575152,38.9134427 19.9546793,38.5968729 20.1481845,38.3998695 C20.1502893,38.3977268 20.1524132,38.395603 20.1545562,38.3934985 L36.9937789,21.8567812 C37.1908028,21.6632968 37.193672,21.3467273 37.0001876,21.1497035 C36.9980647,21.1475418 36.9959223,21.1453995 36.9937605,21.1432767 L20.1545208,4.60825197 C19.9574869,4.41477773 19.9546013,4.09820839 20.1480756,3.90117456 C20.1501626,3.89904911 20.1522686,3.89694235 20.1543933,3.89485454 Z"
                            fill="#FFFFFF"></path>
                        <path class="three"
                            d="M0.154393339,3.89485454 L3.97631488,0.139296592 C4.17083111,-0.0518420739 4.48263286,-0.0518571125 4.67716753,0.139262789 L25.6916134,20.7848311 C26.0855801,21.1718824 26.0911863,21.8050225 25.704135,22.1989893 C25.7000188,22.2031791 25.6958657,22.2073326 25.6916762,22.2114492 L4.67709797,42.8607841 C4.48259567,43.0519059 4.17082418,43.0519358 3.97628526,42.8608513 L0.154518591,39.1069479 C-0.0424848215,38.9134427 -0.0453206733,38.5968729 0.148184538,38.3998695 C0.150289256,38.3977268 0.152413239,38.395603 0.154556228,38.3934985 L16.9937789,21.8567812 C17.1908028,21.6632968 17.193672,21.3467273 17.0001876,21.1497035 C16.9980647,21.1475418 16.9959223,21.1453995 16.9937605,21.1432767 L0.15452076,4.60825197 C-0.0425130651,4.41477773 -0.0453986756,4.09820839 0.148075568,3.90117456 C0.150162624,3.89904911 0.152268631,3.89694235 0.154393339,3.89485454 Z"
                            fill="#FFFFFF"></path>
                    </g>
                </svg>
            </span>
        </a>

        <a class="cta" href="/pos/create">
            <span style="color: white;">POS</span>
            <span>
                <svg width="66px" height="20px" viewBox="0 0 66 43" version="1.1" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink">
                    <g id="arrow" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <path class="one"
                            d="M40.1543933,3.89485454 L43.9763149,0.139296592 C44.1708311,-0.0518420739 44.4826329,-0.0518571125 44.6771675,0.139262789 L65.6916134,20.7848311 C66.0855801,21.1718824 66.0911863,21.8050225 65.704135,22.1989893 C65.7000188,22.2031791 65.6958657,22.2073326 65.6916762,22.2114492 L44.677098,42.8607841 C44.4825957,43.0519059 44.1708242,43.0519358 43.9762853,42.8608513 L40.1545186,39.1069479 C39.9575152,38.9134427 39.9546793,38.5968729 40.1481845,38.3998695 C40.1502893,38.3977268 40.1524132,38.395603 40.1545562,38.3934985 L56.9937789,21.8567812 C57.1908028,21.6632968 57.193672,21.3467273 57.0001876,21.1497035 C56.9980647,21.1475418 56.9959223,21.1453995 56.9937605,21.1432767 L40.1545208,4.60825197 C39.9574869,4.41477773 39.9546013,4.09820839 40.1480756,3.90117456 C40.1501626,3.89904911 40.1522686,3.89694235 40.1543933,3.89485454 Z"
                            fill="#FFFFFF"></path>
                        <path class="two"
                            d="M20.1543933,3.89485454 L23.9763149,0.139296592 C24.1708311,-0.0518420739 24.4826329,-0.0518571125 24.6771675,0.139262789 L45.6916134,20.7848311 C46.0855801,21.1718824 46.0911863,21.8050225 45.704135,22.1989893 C45.7000188,22.2031791 45.6958657,22.2073326 45.6916762,22.2114492 L24.677098,42.8607841 C24.4825957,43.0519059 24.1708242,43.0519358 23.9762853,42.8608513 L20.1545186,39.1069479 C19.9575152,38.9134427 19.9546793,38.5968729 20.1481845,38.3998695 C20.1502893,38.3977268 20.1524132,38.395603 20.1545562,38.3934985 L36.9937789,21.8567812 C37.1908028,21.6632968 37.193672,21.3467273 37.0001876,21.1497035 C36.9980647,21.1475418 36.9959223,21.1453995 36.9937605,21.1432767 L20.1545208,4.60825197 C19.9574869,4.41477773 19.9546013,4.09820839 20.1480756,3.90117456 C20.1501626,3.89904911 20.1522686,3.89694235 20.1543933,3.89485454 Z"
                            fill="#FFFFFF"></path>
                        <path class="three"
                            d="M0.154393339,3.89485454 L3.97631488,0.139296592 C4.17083111,-0.0518420739 4.48263286,-0.0518571125 4.67716753,0.139262789 L25.6916134,20.7848311 C26.0855801,21.1718824 26.0911863,21.8050225 25.704135,22.1989893 C25.7000188,22.2031791 25.6958657,22.2073326 25.6916762,22.2114492 L4.67709797,42.8607841 C4.48259567,43.0519059 4.17082418,43.0519358 3.97628526,42.8608513 L0.154518591,39.1069479 C-0.0424848215,38.9134427 -0.0453206733,38.5968729 0.148184538,38.3998695 C0.150289256,38.3977268 0.152413239,38.395603 0.154556228,38.3934985 L16.9937789,21.8567812 C17.1908028,21.6632968 17.193672,21.3467273 17.0001876,21.1497035 C16.9980647,21.1475418 16.9959223,21.1453995 16.9937605,21.1432767 L0.15452076,4.60825197 C-0.0425130651,4.41477773 -0.0453986756,4.09820839 0.148075568,3.90117456 C0.150162624,3.89904911 0.152268631,3.89694235 0.154393339,3.89485454 Z"
                            fill="#FFFFFF"></path>
                    </g>
                </svg>
            </span>
        </a>
    </div>

    @endif

    <div  class="hide" style="display:flex;margin:1em;">
        <div style="width:50%;margin:1em;background:white;
            align-items: center;
            display: flex;
            justify-content: center;
            border: solid 1px lightgray;
            flex-direction:column;
            border-radius: 8px;">
            <h3>Order Queue Details</h3>
            <br/>
            <div id="chart1" style="display:flex;justify-content:center;align-items:center;"></div>
            <br/>
        </div>
        <div class="hide" style="height:400px;width:50%;margin:1em;background:white;">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('Payment')])
                <div id="chart2"></div>
            @endcomponent
        </div>
    </div>

    <div style="padding:15px;display:flex;justify-content:center;">
        {{-- <select class="gs_bar" style="width:250px;">
            <option value="#">Please select</option>
        </select> --}}
        {{-- <div style="position: absolute;top: 80px;z-index: 9999;width: 100%;display: flex;justify-content: center;">
            <input class="search_bar" type="text" style="width: 50%;background: #ffffff1a;border: solid 1px #0000001a;backdrop-filter: blur(15px);border-radius: 8px;padding: 8px;font-size: 18px;transition: border .2s ease;" />
        </div> --}}
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

        }
    });
</script>
@endsection
