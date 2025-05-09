<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/custom_login/fonts/icomoon/style.css">

    <link rel="stylesheet" href="/custom_login/css/owl.carousel.min.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/custom_login/css/bootstrap.min.css">

    <!-- Style -->
    <link rel="stylesheet" href="/custom_login/css/style.css">

    <title>{{ config('app.name') }} | Login</title>

    <style>
        .brand_bg_color{
            background: linear-gradient(157deg, #ffc764, #604310);
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
    </style>
</head>

<body>

    <div class="d-lg-flex half">
        <div class="bg order-2 order-md-2 brand_bg_color" style="display:flex;position:relative;overflow:hidden;">
            <div style="margin:auto;height:128px;width:128px;filter:sepia(1);background:url('{{ config('business-info.logo') }}');background-size:cover;">
            </div>
            <div id="particles-js" style="height:100%;width:100%;position:absolute;top:0;left:0;">

            </div>
        </div>
        <div class="contents order-1 order-md-1">

            <div class="container">
                <div class="row align-items-center justify-content-center">
                    <div class="col-md-7">
                        <h3>Welcome to <strong>{{ config('app.name') }}</strong></h3>
                        <p class="mb-4">
                            Please login to continue.
                        </p>
                        <form method="POST" action="{{ route('login') }}">
                            {{ csrf_field() }}
                            <div class="form-group first">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" placeholder="Username" value="{{ old('username') }}" id="username" name="username" required autofocus />
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" placeholder="Your Password" id="password" name="password" required />
                            </div>
                            <div class="form-group last mb-3">
                                <label for="passcode">Passcode</label>
                                <input type="password" class="form-control" placeholder="Your Passcode" id="passcode" name="passcode" />
                            </div>
                            @if (session('error'))
                                <div class="mb-4 font-medium text-sm text-danger">
                                    {{ session('error') }}
                                </div>
                            @endif
                            <div class="d-flex mb-5 align-items-center">
                                <label class="control control--checkbox mb-0"><span class="caption">Remember me</span>
                                    <input type="checkbox" checked="checked" name="remember" />
                                    <div class="control__indicator"></div>
                                </label>
                                {{-- <span class="ml-auto"><a href="#" class="forgot-pass">Forgot Password</a></span> --}}
                            </div>

                            <input type="submit" value="Log In" class="btn btn-block btn-primary">


                            @if ($errors->any())
                                <div class="mt-3">
                                    <div class="text-danger">{{ __('Whoops! Something went wrong.') }}</div>
                                    <ul class="text-danger">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <script src="/custom_login/js/jquery-3.3.1.min.js"></script>
    <script src="/custom_login/js/popper.min.js"></script>
    <script src="/custom_login/js/bootstrap.min.js"></script>
    <script src="/custom_login/js/main.js"></script>

    <script type="text/javascript">

        $.getScript("https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js", function(){
            particlesJS('particles-js',
              {
                "particles": {
                  "number": {
                    "value": 100,
                    "density": {
                      "enable": true,
                      "value_area": 500
                    }
                  },
                  "color": {
                    "value": "#fff"
                  },
                  "shape": {
                    "type": "circle",
                    "stroke": {
                      "width": 0,
                      "color": "#000000"
                    },
                    "polygon": {
                      "nb_sides": 5
                    },
                    "image": {
                      "width": 100,
                      "height": 100
                    }
                  },
                  "opacity": {
                    "value": 0.5,
                    "random": false,
                    "anim": {
                      "enable": false,
                      "speed": 1,
                      "opacity_min": 0.1,
                      "sync": false
                    }
                  },
                  "size": {
                    "value": 5,
                    "random": true,
                    "anim": {
                      "enable": false,
                      "speed": 40,
                      "size_min": 0.1,
                      "sync": false
                    }
                  },
                  "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#ffffff",
                    "opacity": 0.4,
                    "width": 1
                  },
                  "move": {
                    "enable": true,
                    "speed": 6,
                    "direction": "none",
                    "random": false,
                    "straight": false,
                    "out_mode": "out",
                    "attract": {
                      "enable": false,
                      "rotateX": 600,
                      "rotateY": 1200
                    }
                  }
                },
                "interactivity": {
                  "detect_on": "canvas",
                  "events": {
                    "onhover": {
                      "enable": true,
                      "mode": "repulse"
                    },
                    "onclick": {
                      "enable": true,
                      "mode": "push"
                    },
                    "resize": true
                  },
                  "modes": {
                    "grab": {
                      "distance": 400,
                      "line_linked": {
                        "opacity": 1
                      }
                    },
                    "bubble": {
                      "distance": 400,
                      "size": 40,
                      "duration": 2,
                      "opacity": 8,
                      "speed": 3
                    },
                    "repulse": {
                      "distance": 100
                    },
                    "push": {
                      "particles_nb": 4
                    },
                    "remove": {
                      "particles_nb": 2
                    }
                  }
                },
                "retina_detect": true,
                "config_demo": {
                  "hide_card": false,
                  "background_color": "#b61924",
                  "background_image": "",
                  "background_position": "50% 50%",
                  "background_repeat": "no-repeat",
                  "background_size": "cover"
                }
              }
            );

        });

    </script>

</body>

</html>