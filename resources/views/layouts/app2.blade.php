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

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

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
        </style>
        {{-- <link rel="stylesheet" href="/fonts/google-fonts/google-fonts.css" /> --}}

    </head>

    <body class="@if($pos_layout) hold-transition lockscreen @else hold-transition skin-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'blue-light'}}@endif sidebar-mini @endif">
        <div class="wrapper thetop">
            <script type="text/javascript">
                if(localStorage.getItem("upos_sidebar_collapse") == 'true'){
                    var body = document.getElementsByTagName("body")[0];
                    body.className += " sidebar-collapse";
                }
            </script>
            @if(!$pos_layout)
                @include('layouts.partials.header')
                @include('layouts.partials.sidebar')
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
                <input type="hidden" id="__precision" value="{{config('constants.currency_precision', 2)}}">
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
                @include('layouts.partials.footer')
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
           <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-JQ6XPRNZKS"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-JQ6XPRNZKS');
</script>

    </body>

</html>