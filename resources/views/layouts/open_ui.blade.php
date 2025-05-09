<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ Session::get('business.name') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        if(localStorage.getItem('dark-mode') === 'true' || (!('dark-mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)){
            document.querySelector('html').setAttribute("data-mode", "dark");
        }
        else{
            document.querySelector('html').setAttribute("data-mode", "light");
        }
        tailwind.config = {
            darkMode: ['class', '[data-mode="dark"]', 'media'],
            theme: {
                extend: {
                    screens: {
                        '3xl': '1920px',
                    },
                    backgroundImage: (theme) => ({
                        'flow-light': "url('/img/ui2.png')",
                        'flow-dark': "url('/img/testonly3.png')",
                    }),
                    keyframes: {
                        bounceslow: {
                            '0%, 100%': {
                                transform: 'translateY(-1%)',
                                'animation-timing-function': 'cubic-bezier(0.8, 0, 1, 1)',
                            },
                            '50%':  {
                                transform: 'translateY(0)',
                                'animation-timing-function': 'cubic-bezier(0, 0, 0.2, 1)',
                            },
                        },
                        'gradient': {
                            to: { 'background-position': '200% center' },
                        }
                    },
                    animation: {
                        'bounce-slow': 'bounceslow 1s linear infinite',
                        'gradient': 'gradient 8s linear infinite',
                    }
                },
            },
            variants: {
                extend: {
                    backgroundImage: ['dark'],
                },
            },
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css?v='.$asset_v) }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet" />

    <style>
        body{
            font-family: 'inter';
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }
        li.flex.justify-between {
            position: relative;
        }
        li.flex.justify-between::after {
            content: '';
            position: absolute;
            border-bottom: solid #8080802e 2px;
            width: 100%;
            transform: translateY(25px);
        }
        #toast-container{
            position:fixed;
            bottom: 1.25rem;
            right: 1.25rem;
            width: 320px;
            z-index: 51;
        }
        .toast{
            margin-top:1rem;
        }
        .toast-message{
            display: flex;
            align-items: center;
            padding: 1rem;
            color: gray;
            background: white;
            border-radius: 0.5rem;
            --tw-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --tw-shadow-colored: 0 1px 3px 0 var(--tw-shadow-color), 0 1px 2px -1px var(--tw-shadow-color);
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
        }

        .zoomIn {
            -webkit-animation-name: zoomIn;
            animation-name: zoomIn;
            -webkit-animation-duration: 0.6s;
            animation-duration: 0.6s;
            -webkit-animation-fill-mode: both;
            animation-fill-mode: both;
        }
        @-webkit-keyframes zoomIn {
            0% {
                opacity: 0;
                -webkit-transform: scale3d(.3, .3, .3);
                transform: scale3d(.3, .3, .3);
            }
            50% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }
        @keyframes zoomIn {
            0% {
                opacity: 0;
                -webkit-transform: scale3d(.3, .3, .3);
                transform: scale3d(.3, .3, .3);
            }
            50% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }
    </style>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">

    <script src="{{ asset('/assets/libs/angularjs/angular.min.js') }}"></script>

    @yield('css')
</head>
<body class="bg-flow-light dark:bg-flow-dark">
    <!-- Add currency related field-->
    <input type="hidden" id="__code" value="{{session('currency')['code']}}">
    <input type="hidden" id="__symbol" value="{{session('currency')['symbol']}}">
    <input type="hidden" id="__thousand" value="{{session('currency')['thousand_separator']}}">
    <input type="hidden" id="__decimal" value="{{session('currency')['decimal_separator']}}">
    <input type="hidden" id="__symbol_placement" value="{{session('business.currency_symbol_placement')}}">
    <input type="hidden" id="__precision" value="{{config('constants.currency_precision', 2)}}">
    <input type="hidden" id="__quantity_precision" value="{{config('constants.quantity_precision', 2)}}">
    <!-- End of currency related field-->

    <nav class="border-gray-200">
        <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl p-4">
            <span class="flex items-center">
                {{-- <img src="https://flowbite.com/docs/images/logo.svg" class="h-8 mr-3" alt="Logo" /> --}}
                <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">{{ Session::get('business.name') }} | @yield('title')</span>
            </span>
            <div class="flex items-center">
                <div class="flex flex-col justify-center mr-3">
                    <input type="checkbox" name="light-switch" id="light-switch" class="light-switch sr-only" />
                    <label class="relative cursor-pointer p-2" for="light-switch">
                        <svg class="dark:hidden text-gray-900" width="16" fill="currentColor" height="16" xmlns="http://www.w3.org/2000/svg">
                            <path class="fill-slate-700" d="M7 0h2v2H7zM12.88 1.637l1.414 1.415-1.415 1.413-1.413-1.414zM14 7h2v2h-2zM12.95 14.433l-1.414-1.413 1.413-1.415 1.415 1.414zM7 14h2v2H7zM2.98 14.364l-1.413-1.415 1.414-1.414 1.414 1.415zM0 7h2v2H0zM3.05 1.706 4.463 3.12 3.05 4.535 1.636 3.12z" />
                            <path class="fill-slate-700" d="M8 4C5.8 4 4 5.8 4 8s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4Z" />
                        </svg>
                        <svg class="hidden dark:block" width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                            <path class="fill-slate-400" d="M6.2 1C3.2 1.8 1 4.6 1 7.9 1 11.8 4.2 15 8.1 15c3.3 0 6-2.2 6.9-5.2C9.7 11.2 4.8 6.3 6.2 1Z" />
                            <path class="fill-slate-500" d="M12.5 5a.625.625 0 0 1-.625-.625 1.252 1.252 0 0 0-1.25-1.25.625.625 0 1 1 0-1.25 1.252 1.252 0 0 0 1.25-1.25.625.625 0 1 1 1.25 0c.001.69.56 1.249 1.25 1.25a.625.625 0 1 1 0 1.25c-.69.001-1.249.56-1.25 1.25A.625.625 0 0 1 12.5 5Z" />
                        </svg>

                        <span class="sr-only">Switch to light / dark version</span>
                    </label>
                </div>
                <a href="/home" class="text-sm  text-blue-600 dark:text-blue-500 hover:underline">Home</a>
            </div>
        </div>
    </nav>

    @yield('content')

    <div class="modal fade view_modal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="gridSystemModalLabel"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
    <script src="/js/flowbite_toastr.js?rev={{ date('Ymd') }}"></script>
    @include('layouts.partials.javascripts')
    <script>
        const lightSwitches = document.querySelectorAll('.light-switch');
        if(lightSwitches.length > 0){
            lightSwitches.forEach((lightSwitch, i) => {
                if(localStorage.getItem('dark-mode') === 'true'){
                    lightSwitch.checked = true;
                }

                lightSwitch.addEventListener('change', () => {
                    const { checked } = lightSwitch;
                    lightSwitches.forEach((el, n) => {
                        if(n !== i){
                            el.checked = checked;
                        }
                    });

                    if(checked){
                        document.querySelector('html').setAttribute("data-mode", "dark");
                        localStorage.setItem('dark-mode', true);
                    }
                    else{
                        document.querySelector('html').setAttribute("data-mode", "light");
                        localStorage.setItem('dark-mode', false);
                    }
                });
            });
        }
    </script>
</body>
</html>