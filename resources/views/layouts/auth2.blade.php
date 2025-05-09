<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'POS') }}</title>

    @include('layouts.partials.css')

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style>


body {
    margin: 0;
    height: 100vh !important;
    font-weight: 100;
    background: radial-gradient(#a23982,#1f1013);
    -webkit-overflow-Y: hidden;
    -moz-overflow-Y: hidden;
    -o-overflow-Y: hidden;
    overflow-y: hidden;
    -webkit-animation: fadeIn 1 1s ease-out;
    -moz-animation: fadeIn 1 1s ease-out;
    -o-animation: fadeIn 1 1s ease-out;
    animation: fadeIn 1 1s ease-out;
}

.buttonl{
  position: absolute;
  border: 2px solid white;
  background: transparent;
  font-family: 'Roboto', sans-serif;
  color: white;
  width: 250px;
  height: 50px;
  font-size: 2em;
  border-radius: 5px;
  opacity: .5;
  top: 60vh;
  bottom: 0px;
  left: 0px;
  right: 0px;
  margin: auto;
  transition: .3s;
}

.buttonl:hover{
  border: 2px solid #104F55;
  background-color: rgba(365,365,365,0.5);
  cursor: pointer;
  color: #104F55;
  opacity: .8;
  transition: .3s;
  box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
}

.light {
    position: absolute;
    width: 0px;
    opacity: .75;
    background-color: white;
    box-shadow: #e9f1f1 0px 0px 20px 2px;
    opacity: 0;
    top: 100vh;
    bottom: 0px;
    left: 0px;
    right: 0px;
    margin: auto;
}

.x1{
  -webkit-animation: floatUp 4s infinite linear;
  -moz-animation: floatUp 4s infinite linear;
  -o-animation: floatUp 4s infinite linear;
  animation: floatUp 4s infinite linear;
   -webkit-transform: scale(1.0);
   -moz-transform: scale(1.0);
   -o-transform: scale(1.0);
  transform: scale(1.0);
}

.x2{
  -webkit-animation: floatUp 7s infinite linear;
  -moz-animation: floatUp 7s infinite linear;
  -o-animation: floatUp 7s infinite linear;
  animation: floatUp 7s infinite linear;
  -webkit-transform: scale(1.6);
  -moz-transform: scale(1.6);
  -o-transform: scale(1.6);
  transform: scale(1.6);
  left: 15%;
}

.x3{
  -webkit-animation: floatUp 2.5s infinite linear;
  -moz-animation: floatUp 2.5s infinite linear;
  -o-animation: floatUp 2.5s infinite linear;
  animation: floatUp 2.5s infinite linear;
  -webkit-transform: scale(.5);
  -moz-transform: scale(.5);
  -o-transform: scale(.5);
  transform: scale(.5);
  left: -15%;
}

.x4{
  -webkit-animation: floatUp 4.5s infinite linear;
  -moz-animation: floatUp 4.5s infinite linear;
  -o-animation: floatUp 4.5s infinite linear;
  animation: floatUp 4.5s infinite linear;
  -webkit-transform: scale(1.2);
  -moz-transform: scale(1.2);
  -o-transform: scale(1.2);
  transform: scale(1.2);
  left: -34%;
}

.x5{
  -webkit-animation: floatUp 8s infinite linear;
  -moz-animation: floatUp 8s infinite linear;
  -o-animation: floatUp 8s infinite linear;
  animation: floatUp 8s infinite linear;
  -webkit-transform: scale(2.2);
  -moz-transform: scale(2.2);
  -o-transform: scale(2.2);
  transform: scale(2.2);
  left: -57%;
}

.x6{
  -webkit-animation: floatUp 3s infinite linear;
  -moz-animation: floatUp 3s infinite linear;
  -o-animation: floatUp 3s infinite linear;
  animation: floatUp 3s infinite linear;
  -webkit-transform: scale(.8);
  -moz-transform: scale(.8);
  -o-transform: scale(.8);
  transform: scale(.8);
  left: -81%;
}

.x7{
  -webkit-animation: floatUp 5.3s infinite linear;
  -moz-animation: floatUp 5.3s infinite linear;
  -o-animation: floatUp 5.3s infinite linear;
  animation: floatUp 5.3s infinite linear;
  -webkit-transform: scale(3.2);
  -moz-transform: scale(3.2);
  -o-transform: scale(3.2);
  transform: scale(3.2);
  left: 37%;
}

.x8{
  -webkit-animation: floatUp 4.7s infinite linear;
  -moz-animation: floatUp 4.7s infinite linear;
  -o-animation: floatUp 4.7s infinite linear;
  animation: floatUp 4.7s infinite linear;
  -webkit-transform: scale(1.7);
  -moz-transform: scale(1.7);
  -o-transform: scale(1.7);
  transform: scale(1.7);
  left: 62%;
}

.x9{
  -webkit-animation: floatUp 4.1s infinite linear;
  -moz-animation: floatUp 4.1s infinite linear;
  -o-animation: floatUp 4.1s infinite linear;
  animation: floatUp 4.1s infinite linear;
  -webkit-transform: scale(0.9);
  -moz-transform: scale(0.9);
  -o-transform: scale(0.9);
  transform: scale(0.9);
  left: 85%;
}

.buttonl:focus{
  outline: none;
}

@-webkit-keyframes floatUp{
  0%{top: 100vh; opacity: 0;}
  25%{opacity: 1;}
  50%{top: 0vh; opacity: .8;}
  75%{opacity: 1;}
  100%{top: -100vh; opacity: 0;}
}
@-moz-keyframes floatUp{
  0%{top: 100vh; opacity: 0;}
  25%{opacity: 1;}
  50%{top: 0vh; opacity: .8;}
  75%{opacity: 1;}
  100%{top: -100vh; opacity: 0;}
}
@-o-keyframes floatUp{
  0%{top: 100vh; opacity: 0;}
  25%{opacity: 1;}
  50%{top: 0vh; opacity: .8;}
  75%{opacity: 1;}
  100%{top: -100vh; opacity: 0;}
}
@keyframes floatUp{
  0%{top: 100vh; opacity: 0;}
  25%{opacity: 1;}
  50%{top: 0vh; opacity: .8;}
  75%{opacity: 1;}
  100%{top: -100vh; opacity: 0;}
}

.header{
  position: absolute;
  top: 40%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-family: 'Roboto', sans-serif;
  font-weight: 200;
  color: white;
  font-size: 2em;
}

#head1, #head2,#head3, #head4, #head5{
  opacity: 0;

    position: absolute;
    top: 100px;

}

#head1{
  -webkit-animation: fadeOut 1 5s ease-in;
  -moz-animation: fadeOut 1 5s ease-in;
  -o-animation: fadeOut 1 5s ease-in;
  animation: fadeOut 1 5s ease-in;
}

#head2{
  -webkit-animation: fadeOut 1 5s ease-in;
  -moz-animation: fadeOut 1 5s ease-in;
  -o-animation: fadeOut 1 5s ease-in;
  animation: fadeOut 1 5s ease-in;
  -webkit-animation-delay: 6s;
  -moz-animation-delay: 6s;
  -o-animation-delay: 6s;
  animation-delay: 6s;
}

#head3{
  -webkit-animation: fadeOut 1 5s ease-in;
  -moz-animation: fadeOut 1 5s ease-in;
  -o-animation: fadeOut 1 5s ease-in;
  animation: fadeOut 1 5s ease-in;
  -webkit-animation-delay: 12s;
  -moz-animation-delay: 12s;
  -o-animation-delay: 12s;
  animation-delay: 12s;
}

#head4{
  -webkit-animation: fadeOut 1 5s ease-in;
  -moz-animation: fadeOut 1 5s ease-in;
  -o-animation: fadeOut 1 5s ease-in;
  animation: fadeOut 1 5s ease-in;
  -webkit-animation-delay: 17s;
  -moz-animation-delay: 17s;
  -o-animation-delay: 17s;
  animation-delay: 17s;
}

#head5{
  -webkit-animation: finalFade 1 5s ease-in;
  -moz-animation: finalFade 1 5s ease-in;
  -o-animation: finalFade 1 5s ease-in;
  animation: finalFade 1 5s ease-in;
  -webkit-animation-fill-mode: forwards;
  -moz-animation-fill-mode: forwards;
  -o-animation-fill-mode: forwards;
  animation-fill-mode: forwards;
  -webkit-animation-delay: 22s;
  -moz-animation-delay: 22s;
  -o-animation-delay: 22s;
  animation-delay: 22s;
}

@-webkit-keyframes fadeIn{
  from{opacity: 0;}
  to{opacity: 1;}
}

@-moz-keyframes fadeIn{
  from{opacity: 0;}
  to{opacity: 1;}
}

@-o-keyframes fadeIn{
  from{opacity: 0;}
  to{opacity: 1;}
}

@keyframes fadeIn{
  from{opacity: 0;}
  to{opacity: 1;}
}

@-webkit-keyframes fadeOut{
  0%{opacity: 0;}
  30%{opacity: 1;}
  80%{opacity: .9;}
  100%{opacity: 0;}
}

@-moz-keyframes fadeOut{
  0%{opacity: 0;}
  30%{opacity: 1;}
  80%{opacity: .9;}
  100%{opacity: 0;}
}

@-o-keyframes fadeOut{
  0%{opacity: 0;}
  30%{opacity: 1;}
  80%{opacity: .9;}
  100%{opacity: 0;}
}

@keyframes fadeOut{
  0%{opacity: 0;}
  30%{opacity: 1;}
  80%{opacity: .9;}
  100%{opacity: 0;}
}

@-webkit-keyframes finalFade{
  0%{opacity: 0;}
  30%{opacity: 1;}
  80%{opacity: .9;}
  100%{opacity: 1;}
}

@-moz-keyframes finalFade{
  0%{opacity: 0;}
  30%{opacity: 1;}
  80%{opacity: .9;}
  100%{opacity: 1;}
}

@-o-keyframes finalFade{
  0%{opacity: 0;}
  30%{opacity: 1;}
  80%{opacity: .9;}
  100%{opacity: 1;}
}

@keyframes finalFade{
  0%{opacity: 0;}
  30%{opacity: 1;}
  80%{opacity: .9;}
  100%{opacity: 1;}
}

#login-box1 {
	position: absolute;
	top: 0px;
	left: 50%;
	transform: translateX(-50%);
	min-width: 350px;
	margin: 0 auto;
	border: 1px solid #302e2d;
	background: rgba(48, 46, 45, 1);
	min-height: 250px;
	padding: 20px;
	z-index: 9999;
	margin-top:100px;
	border-radius: 10px;
}

#login-box{
     	position: absolute;
	top: 0px;
	left: 50%;
	transform: translateX(-50%);
	min-width: 350px;
	margin: 0 auto;
  border: 2px solid white;
  background: transparent;
  font-family: 'Roboto', sans-serif;
  color: white;
    	min-height: 250px;
	padding: 20px;
	z-index: 9999;
	margin-top:100px;
	border-radius: 10px;
   transition: .3s;
}
#login-box .logo .logo-caption {
	font-family: 'Poiret One', cursive;
	color: white;
	text-align: center;
	margin-bottom: 0px;
}
#login-box .logo .tweak {
	color: #b1c900;
}
#login-box .controls {
	padding-top: 0px;
}
#login-box .controls input {
	border-radius: 0px;
	background: rgb(98, 96, 96);
	border: 0px;
	color: white;
}
#login-box .controls input:focus {
	box-shadow: none;
}
#login-box .controls input:first-child {
	border-top-left-radius: 2px;
	border-top-right-radius: 2px;
}
#login-box .controls input:last-child {
	border-bottom-left-radius: 2px;
	border-bottom-right-radius: 2px;
}
#login-box button.btn-custom {
	border-radius: 2px;
	margin-top: 8px;
	background:#b1c900;
	border-color: rgba(48, 46, 45, 1);
	color: white;
}
#login-box button.btn-custom:hover{
	-webkit-transition: all 500ms ease;
	-moz-transition: all 500ms ease;
	-ms-transition: all 500ms ease;
	-o-transition: all 500ms ease;
	transition: all 500ms ease;
	background: rgba(48, 46, 45, 1);
	border-color: #b1c900;
}

.pull-right {
    float: left !important;
    padding-top: 30px;
    color:white;
}
.right-col-content {
    padding: 10% 10%;
}
.form-group.login {
    text-align: center;
}
.btn-login {
    padding: 8px 54px !important;
    font-size: 15px;
    border: 1px solid #1572e8;
    border-radius: 50px !important;
}
label {
    color: #fff;
}
.form-control-feedback
{
  color: #1572e8;
}
.form-control
{
  color: #333;
  /*  background-color: rgba(255,255,255,.1); */
    border: none;
    border-radius: 40px !important;
}
    </style>
</head>

<body style="height:100vh !important;">
    @inject('request', 'Illuminate\Http\Request')
    @if (session('status'))
        <input type="hidden" id="status_span" data-status="{{ session('status.success') }}" data-msg="{{ session('status.msg') }}">
    @endif

<div class='light x1'></div>
  <div class='light x2'></div>
  <div class='light x3'></div>
  <div class='light x4'></div>
  <div class='light x5'></div>
  <div class='light x6'></div>
  <div class='light x7'></div>
  <div class='light x8'></div>
  <div class='light x9'></div>
    <div class="container">

	<div id="login-box">
	            <p id='head1' class='header'>Welcome</p>
<p id='head2' class='header'>To</p>
<p id='head3' class='header'>{{ config('business-info.erp_name') }}</p>
<p id='head4' class='header'>have a</p>
<p id='head5' class='header'>Good Day.</p>
		 <div class="logo">
			<img src="../img/novx-logo.png" style=" width: 150px; " class="img img-responsive img-circle1 center-block"/>
			<!-- <h1 class="logo-caption"><span class="tweak">L</span>ogin</h1> -->
      </div>
		<!-- /.logo  -->
		<div class="controls">

         @if(!($request->segment(1) == 'business' && $request->segment(2) == 'register'))
                        <!-- Register Url -->
                        @if(config('constants.allow_registration'))
                            <a href="{{ route('business.getRegister') }}@if(!empty(request()->lang)){{'?lang=' . request()->lang}} @endif" class="btn bg-maroon btn-flat" ><b>{{ __('business.not_yet_registered')}}</b> {{ __('business.register_now') }}</a>
                            <!-- pricing url -->
                            @if(Route::has('pricing') && config('app.env') != 'demo' && $request->segment(1) != 'pricing')
                                &nbsp; <a href="{{ action('\Modules\Superadmin\Http\Controllers\PricingController@index') }}">@lang('superadmin::lang.pricing')</a>
                            @endif
                        @endif
                    @endif
                    @if($request->segment(1) != 'login')
                        &nbsp; &nbsp;<span class="text-white">{{ __('business.already_registered')}} </span><a href="{{ action('Auth\LoginController@login') }}@if(!empty(request()->lang)){{'?lang=' . request()->lang}} @endif">{{ __('business.sign_in') }}</a>
                    @endif
                </div>

                @yield('content')



		</div>
	</div>
</div>





<!--
    <div class="container-fluid">
        <div class="row eq-height-row">
            <div class="col-md-9 col-sm-5 hidden-xs left-col eq-height-col" style="background: url(../img/home-bg.jpg); text-align: center; background-size: cover; background-position: center;">
                <div class="left-col-content login-header" >
                    <div style="margin-top: 50%;display:none;">
                    <a href="/">
                    @if(file_exists(public_path('uploads/logo.png')))
                        <img src="/uploads/logo.png" class="img-rounded" alt="Logo" width="150">
                    @else
                       {{ config('app.name', 'ultimatePOS') }}
                    @endif
                    </a>
                    <br/>
                    @if(!empty(config('constants.app_title')))
                        <small>{{config('constants.app_title')}}</small>
                    @endif
                    </div>
                </div>
            </div>

             -->


            <!-- <div class="col-md-3 col-sm-7 col-xs-12 right-col eq-height-col" style="background-image: linear-gradient(#8e2de2, #4a00e0);">
                <div class="row">
                <div class="col-md-3 col-xs-4" style="text-align: left;">
                    <select class="form-control input-sm" id="change_lang" style="margin: 10px;display:none;">
                    @foreach(config('constants.langs') as $key => $val)
                        <option value="{{$key}}"
                            @if( (empty(request()->lang) && config('app.locale') == $key)
                            || request()->lang == $key)
                                selected
                            @endif
                        >
                            {{$val['full_name']}}
                        </option>
                    @endforeach
                    </select>
                </div>
                <div class="col-md-9 col-xs-8" style="text-align: right;padding-top: 10px;">
                    @if(!($request->segment(1) == 'business' && $request->segment(2) == 'register'))
                        <!-- Register Url -->
                        <!-- @if(config('constants.allow_registration')) -->
                            <!-- <a href="{{ route('business.getRegister') }}@if(!empty(request()->lang)){{'?lang=' . request()->lang}} @endif" class="btn bg-maroon btn-flat" ><b>{{ __('business.not_yet_registered')}}</b> {{ __('business.register_now') }}</a> -->
                            <!-- pricing url -->
                            <!-- @if(Route::has('pricing') && config('app.env') != 'demo' && $request->segment(1) != 'pricing')
                                &nbsp; <a href="{{ action('\Modules\Superadmin\Http\Controllers\PricingController@index') }}">@lang('superadmin::lang.pricing')</a>
                            @endif
                        @endif
                    @endif
                    @if($request->segment(1) != 'login')
                        &nbsp; &nbsp;<span class="text-white">{{ __('business.already_registered')}} </span><a href="{{ action('Auth\LoginController@login') }}@if(!empty(request()->lang)){{'?lang=' . request()->lang}} @endif">{{ __('business.sign_in') }}</a>
                    @endif
                </div>

                @yield('content')
                </div>
            </div>
        </div>
    </div> -->



    @include('layouts.partials.javascripts')

    <!-- Scripts -->
    <script src="{{ asset('js/login.js?v=' . $asset_v) }}"></script>

    @yield('javascript')

    <script type="text/javascript">
        $(document).ready(function(){
            $('.select2_register').select2();

            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });
    </script>

</body>

</html>