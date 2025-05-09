@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css"
        integrity="sha512-xmGTNt20S0t62wHLmQec2DauG9T+owP9e6VU8GigI0anN7OXLip9i7IwEhelasml2osdxX71XcYm6BQunTQeQg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        .search_result {
            display: none;
        }

        .dash_info_ele {
            width: 35%;
            padding: 1rem;
            margin: 0rem;
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

        .circle-tile-heading.dark-blue:hover {
            background-color: #2E4154;
        }

        .circle-tile-heading.green:hover {
            background-color: #138F77;
        }

        .circle-tile-heading.orange:hover {
            background-color: #DA8C10;
        }

        .circle-tile-heading.blue:hover {
            background-color: #2473A6;
        }

        .circle-tile-heading.red:hover {
            background-color: #CF4435;
        }

        .circle-tile-heading.purple:hover {
            background-color: #7F3D9B;
        }

        .tile-img {
            text-shadow: 2px 2px 3px rgba(0, 0, 0, 0.9);
        }

        .dark-blue {
            background-color: #34495E;
        }

        .green {
            background-color: #16A085;
        }

        .blue {
            background-color: #2980B9;
        }

        .orange {
            background-color: #F39C12;
        }

        .red {
            background-color: #E74C3C;
        }

        .purple {
            background-color: #8E44AD;
        }

        .dark-gray {
            background-color: #7F8C8D;
        }

        .gray {
            background-color: #95A5A6;
        }

        .light-gray {
            background-color: #BDC3C7;
        }

        .yellow {
            background-color: #F1C40F;
        }

        .text-dark-blue {
            color: #34495E;
        }

        .text-green {
            color: #16A085;
        }

        .text-blue {
            color: #2980B9;
        }

        .text-orange {
            color: #F39C12;
        }

        .text-red {
            color: #E74C3C;
        }

        .text-purple {
            color: #8E44AD;
        }

        .text-faded {
            color: rgba(255, 255, 255, 0.7);
        }

        .card {
            padding: 1rem;
            background-color: #fff;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            /* max-width: 370px; */
            border-radius: 20px;
            /* width: 350px; */

        }

        .title-text {
            color: #374151;
            font-size: 18px;
            text-align: left;
        }

        .data {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .data p {
            margin-top: 1rem;
            margin-bottom: 1rem;
            color: #1F2937;
            font-size: 2.25rem;
            line-height: 2.5rem;
            font-weight: 700;
            text-align: left;
        }
    </style>
@endsection
