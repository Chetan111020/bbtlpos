<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
      dir="{{in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr'}}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ Session::get('business.name') }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('/').'css/packing.css' }}">
    @yield('css')
    <script src="https://use.fontawesome.com/1de39dadd9.js"></script>
</head>

<body>
@yield('content')
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
            integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>
@yield('scripts')
<script>
    let minutesLabel = document.getElementById("minutes");
    let secondsLabel = document.getElementById("seconds");
    let timeCount;
    let totalSeconds = 0;
    setInterval(setTime, 1000);

    function setTime() {
        ++totalSeconds;
        secondsLabel.innerHTML = pad(totalSeconds % 60);
        minutesLabel.innerHTML = pad(parseInt(totalSeconds / 60));
        timeCount = pad(parseInt(totalSeconds / 60)) + ':' + pad(totalSeconds % 60);
        document.getElementById("timingCount").value = timeCount;
    }

    function pad(val) {
        var valString = val + "";
        if (valString.length < 2) {
            return "0" + valString;
        } else {
            return valString;
        }
    }
</script>
</body>
</html>