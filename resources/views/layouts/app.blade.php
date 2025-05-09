@inject('request', 'Illuminate\Http\Request')
@php
    $credit_memo = false;
    $purchase_return = false;
@endphp
@if($request->segment(1) == 'pos' && ($request->segment(2) == 'create' || $request->segment(3) == 'edit'))
    @php
        $pos_layout = true;
    @endphp
@else
    @php
        $pos_layout = false;
    @endphp
@endif

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr'}}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title') - {{ Session::get('business.name') }}</title>

        @include('layouts.partials.css')

        @yield('css')
        <link rel="stylesheet" href="/fonts/google-fonts/google-fonts.css" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">

        <style>
            .v2_sell_popup > *{
                font-family: 'Inter' !important;
            }
            .v2_text-danger {
                background: linear-gradient(to right,#000, #f00);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .v2_text-success {
                background: linear-gradient(to right,#000, #0f0);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .v2_sell_popup details summary::-webkit-details-marker { display: none; }
            .v2_sell_popup summary::before {
                font-family: "Hiragino Mincho ProN", "Open Sans", sans-serif;
                content: "▶";
                position: absolute;
                top: 15px;
                left: 15px;
                transform: rotate(0);
                transform-origin: center;
                transition: 0.2s transform ease;
            }
            .v2_sell_popup details[open] > summary:before {
                transform: rotate(90deg);
                transition: 0.45s transform ease;
            }

            /* # The Sliding Summary # */
            .v2_sell_popup > details { overflow: hidden; }
            .v2_summary {
                position: relative;
                z-index: 10;
                background: #d2d6de;
                border-radius: 8px;
                padding: 15px;
                padding-left: 45px;
                color: black;
            }
            .v2_acc_details{
                background: #d2d6de;
                border-radius: 8px;
            }

            @import url('https://fonts.googleapis.com/css?family=Poppins:900i');

            body, h1, h2, h3, h4, h5, h6{
                font-family: 'DM Sans' !important;
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
                display: none;
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
            .treeview-menu a{
                padding:6px 10px !important;
            }
            .treeview-menu a::before {
                content: "-> ";
                font-family: 'inter';
                margin-right: 5px;
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
            .sidebar-menu>li>a, .skin-red-light .sidebar-menu>li.active>a {
                color: black !important;
                font-weight: 400 !important;
                padding: 10px;
                display: flex;
                align-items: center;
                border-radius: 5px;
                margin: 5px 10px;
            }
            .skin-red-light .sidebar-menu>li.active>a, .skin-red-light .sidebar-menu>li:hover>a{
                background: #dedede75 !important;
            }
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
            /* .skin-red-light .left-side, .skin-red-light .main-sidebar, .skin-red-light .wrapper {
                background-color: #f8f9fe !important;
            } */
            .main-sidebar{
                position: fixed !important;
                background: #ffffffed !important;
                padding-top: 0 !important;
                box-shadow: none !important;
                width: 300px !important;
                height: 100vh !important;
                overflow: auto;
            }
            .sidebar-menu{
                overflow: visible;
                background: transparent;
            }
            .treeview.active>a:before {
                content: '';
                height: 60%;
                background: #0b7be9;
                position: absolute;
                width: 5px;
                left: -2px;
                border-radius: 10px;
            }
            .content-wrapper {
                /* background: linear-gradient(0deg, #f7faff 70%, transparent); */
                background: #ffffffed;
            }
            .content-wrapper, .main-footer{
                margin-left: 300px !important;
                min-height: 100vh !important;
                padding: 0 15px;
                /* height: 100vh; */
                overflow: auto;
            }
            .h-100-max-cs{
                /* height: 100% !important; */
            }
            .quick-btns{
                /* margin: 10px 0; */
                display: grid;
                justify-content: space-evenly;
                grid-template-columns: 1fr 1fr;
                grid-gap: 5px;
                width: 100%;
                padding: 0 5px;
            }
            .quick-btns .btn {
                display: flex;
                height: 40px;
                align-items: center;
                justify-content: center;
                background: white;
                font-weight: bold;
            }
            .quick-btns .btn svg{
                height: 20px;
            }
            .content-header>h1{
                font-size: xx-large;
                margin: 15px 0;
                font-weight: 600;
                color: black;
            }

            @media print {
                .skin-red-light .wrapper, .skin-red-light .wrapper{
                    background: linear-gradient(to white, , white) !important;
                }
                /*#adwrapper{display:none;}*/
                /*td {*/
                /*  border-bottom: solid; */
                /*  border-right: solid; */
                /*  background-color: #c0c0c0;*/
                /*}*/
            }
            .theme-preload{
                background: white !important;
            }
            .theme-loaded {
                background: linear-gradient(135deg, #6E61EF, #FF5963, #EE8B60) !important;
                /* background: linear-gradient(135deg, #61efac, #599cff, #60eec2) !important; */
                background-attachment: fixed !important;
            }
            /* .box , .info-box{
                background: #ffffffad;
            } */
            .user-info:hover, .open>.user-info{
                background: #dedede75;
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
                background: #dedede75;
            }
            @media (max-width: 767px){
                .main-sidebar {
                    transform: none;
                    width: 80px !important;
                    transition: all 0.3s ease;
                    overflow-x: hidden;
                }
                .main-sidebar:hover {
                    width: 300px !important;
                    background: white !important;
                }
                .content-wrapper, .main-footer {
                    margin-left: 80px !important;
                }
                .main-sidebar .sidebar_hover_target {
                    display: none;
                }
                .main-sidebar:hover .sidebar_hover_target {
                    display: inline-block;
                    margin-left: 15px;
                }
                .sidebar-menu>li>a>img {
                    margin-right: 0;
                }
                .sidebar-quick-access{
                    display: grid;
                }
                .sidebar-quick-access strong{
                    display: none;
                }
                .main-sidebar:hover .sidebar-quick-access {
                    display: flex;
                }
                .main-sidebar:hover .sidebar-quick-access strong{
                    display: block;
                }
            }
            .x-loading-button{
                display: flex;
            }
            .main-sidebar::-webkit-scrollbar-thumb, .main-sidebar::-webkit-scrollbar-track{
                background: transparent;
            }
            .main-sidebar:hover::-webkit-scrollbar-thumb{
                background: #888;
            }
            .skin-red-light .sidebar-menu>li>.treeview-menu{
                background: #dedede75 !important;
                margin: 0 10px;
                border-radius: 5px;
            }
        </style>
        {{-- <link rel="stylesheet" href="/fonts/google-fonts/google-fonts.css" /> --}}

    </head>

    <body class="h-100-max-cs @if($pos_layout) hold-transition lockscreen @else hold-transition skin-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'blue-light'}}@endif sidebar-mini @endif">
        <div class="wrapper thetop h-100-max-cs theme-preload">
            <script type="text/javascript">
                if(localStorage.getItem("upos_sidebar_collapse") == 'true'){
                    var body = document.getElementsByTagName("body")[0];
                    body.className += " sidebar-collapse";
                }
            </script>
            @if(!$pos_layout)
                {{-- @include('layouts.partials.header') --}}
                {{-- @include('layouts.partials.sidebar') --}}
                <!-- Left side column. contains the logo and sidebar -->
                <aside class="main-sidebar">

                    <div class="dropdown" style="margin:20px 15px;">
                        <div class="user-info dropdown-toggle" style="display: flex;" data-toggle="dropdown">
                            <img src="{{ asset( 'uploads/business_logos/' . Session::get('business.logo') ) }}" alt="User" class="" style="height: 60px;border-radius: 50px;" />
                            <div style="margin: auto;margin-left: 10px;display:grid;">
                                <h4 style="margin: 0;"><strong>{{ Session::get('business.name') }}</strong></h4>
                                <span>{{ auth()->user()->first_name }}</span>
                            </div>
                            <div style="margin:auto;margin-right:8px;display:flex;">
                                <svg xmlns="http://www.w3.org/2000/svg" style="height: 20px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                </svg>
                            </div>
                        </div>
                        <div class="dropdown-menu fadeInDown" style="width: 100%;">
                            <div class="quick-btns">
                                <a class="btn" target="_blank" href="/user/profile">
                                    Profile
                                </a>
                                <a class="btn" href="/logout">
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-quick-access">
                        <a target="_blank" href="/pos/create" style="display:flex; align-items:center;width:50%;height:40px;">
                            <img src="/v2-assets/sidebar-svg/pos.svg" alt="All Orders" style="height: 20px; margin:0 10px;">
                            <strong style="color: black">POS</strong>
                        </a>
                        <a target="_blank" href="/sells" style="display:flex; align-items:center;width:50%;height:40px;">
                            <img src="/v2-assets/sidebar-svg/order.svg" alt="All Orders" style="height: 20px; margin:0 10px;">
                            <strong style="color: black">Orders</strong>
                        </a>
                    </div>

                    <!-- sidebar: style can be found in sidebar.less -->
                    <section class="sidebar">

                    <!-- <a href="{{route('home')}}" class="logo">
                        <span class="logo-lg">{{ Session::get('business.name') }}</span>
                    </a> -->

                    <!-- Sidebar Menu -->
                    {!! Menu::render('admin-sidebar-menu', 'ui2_custom'); !!}

                    <!-- /.sidebar-menu -->
                    </section>
                    <!-- /.sidebar -->
                </aside>

            @elseif($pos_layout && !$credit_memo)
                @include('layouts.partials.header-pos')
            @endif

            <!-- Content Wrapper. Contains page content -->
            <div class="@if(!$pos_layout) content-wrapper @endif">
                <!-- empty div for vuejs -->
                <div id="app">
                    @yield('vue')
                </div>
                <!-- Add currency related field-->
                <input type="hidden" id="__code" value="{{session('currency')['code']}}">
                <input type="hidden" id="__symbol" value="{{session('currency')['symbol']}}">
                <input type="hidden" id="__thousand" value="{{session('currency')['thousand_separator']}}">
                <input type="hidden" id="__decimal" value="{{session('currency')['decimal_separator']}}">
                <input type="hidden" id="__symbol_placement" value="{{session('business.currency_symbol_placement')}}">
                <!--<input type="hidden" id="__precision" value="{{config('constants.currency_precision', 2)}}">-->
                <input type="hidden" id="__precision" value="2">

                <input type="hidden" id="__quantity_precision" value="{{config('constants.quantity_precision', 2)}}">
                <!-- End of currency related field-->

                @if (session('status'))
                    <input type="hidden" id="status_span" data-status="{{ session('status.success') }}" data-msg="{{ session('status.msg') }}">
                @endif
                @yield('content')

                <div class='scrolltop no-print'>
                    <div class='scroll icon'><i class="fas fa-angle-up"></i></div>
                </div>

                @if(config('constants.iraqi_selling_price_adjustment'))
                    <input type="hidden" id="iraqi_selling_price_adjustment">
                @endif

                <!-- This will be printed -->
                <section class="invoice print_section" id="receipt_section">
                </section>

            </div>
            @include('home.todays_profit_modal')
            <!-- /.content-wrapper -->

            @if(!$pos_layout)
                {{-- @include('layouts.partials.footer') --}}
            @else
                @include('layouts.partials.footer_pos')
            @endif

            <audio id="success-audio">
              <source src="{{ asset('/audio/success.ogg?v=' . $asset_v) }}" type="audio/ogg">
              <source src="{{ asset('/audio/success.mp3?v=' . $asset_v) }}" type="audio/mpeg">
            </audio>
            <audio id="error-audio">
              <source src="{{ asset('/audio/error.ogg?v=' . $asset_v) }}" type="audio/ogg">
              <source src="{{ asset('/audio/error.mp3?v=' . $asset_v) }}" type="audio/mpeg">
            </audio>
            <audio id="warning-audio">
              <source src="{{ asset('/audio/warning.ogg?v=' . $asset_v) }}" type="audio/ogg">
              <source src="{{ asset('/audio/warning.mp3?v=' . $asset_v) }}" type="audio/mpeg">
            </audio>

        </div>

        @include('layouts.partials.javascripts')
        <div class="modal fade view_modal" tabindex="-1" role="dialog" data-backdrop="static"
        aria-labelledby="gridSystemModalLabel"></div>

        <script>
            $(document).ready(function(){
                $('.theme-preload').addClass('theme-loaded');
            });
        </script>
    </body>
</html>