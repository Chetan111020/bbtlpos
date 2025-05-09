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

<div style="background:white;padding:15px;">
    <h1>Welcome {{ Session::get('user.first_name') }},</h1>
    @if (auth()->user()->can('dashboard.data'))
        <div class="row" style="display: none;">
            <div class="form-group pull-right">
                <div class="col-md-8">
                    <input type="hidden" id="date" name="date" value="">
                    <div class="form-group">
                        {!! Form::label('all_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text(
                            'all_date_filter',
                            @format_date('first day of this week') . ' ~ ' . @format_date('last day of this week'),
                            [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control',
                                'id' => 'all_date_filter',
                                'readonly',
                            ],
                        ) !!}

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group" style="margin-top: 25px;">
                        <button class="btn btn-primary" name="submit" id="submitData">Submit</button>
                    </div>
                </div>
            </div>

        </div>
</div>


<div style="display:flex;margin:1em;">
    <div style="width:100%;margin:1em;">
        <div style="display:flex;">
            <div class="col-lg-3 col-sm-3">
                <div class="circle-tile ">
                    <div class="circle-tile-heading dash_ele_color1">
                        <img src="{{ asset('/img/customer.gif') }}" style="height: 80px; width: 70px;"
                            alt="">
                    </div>
                    <div class="circle-tile-content dash_ele_color1">
                        <div class="circle-tile-description text-faded dash_ele_color1">Total Customer</div>
                        <div class="circle-tile-number text-faded dash_ele_color1">{{ $allCustomer }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-3">
                <div class="circle-tile ">
                    <div class="circle-tile-heading dash_ele_color5">
                        <img src="{{ asset('/img/total_sell.gif') }}" style="height: 80px; width: 70px;" alt="">

                    </div>
                    <div class="circle-tile-content dash_ele_color5">
                        <div class="circle-tile-description text-faded dash_ele_color5">Total Sales</div>
                        <div class="circle-tile-number text-faded dash_ele_color5 total_sell"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-3">
                <div class="circle-tile">
                    <div class="circle-tile-heading dash_ele_color2">
                        <img src="{{ asset('/img/draft.gif') }}" style="height: 80px; width: 70px;" alt="">
                    </div>
                    <div class="circle-tile-content dash_ele_color2">
                        <div class="circle-tile-description text-faded dash_ele_color2">total draft</div>
                        <div class="circle-tile-number text-faded dash_ele_color2 draft"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-3">
                <div class="circle-tile ">
                    <div class="circle-tile-heading dash_ele_color3">
                        <img src="{{ asset('/img/transaction.gif') }}" style="height: 80px; width: 70px;" alt="">
                    </div>
                    <div class="circle-tile-content dash_ele_color3">
                        <div class="circle-tile-description text-faded dash_ele_color3">Total Transactions</div>
                        <div class="circle-tile-number text-faded dash_ele_color3 total_transactions"></div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
<strong>
    <p>
        <h3 style="margin-left: 50px;margin-top: -20px;">Follow Up :</h3>
    </p>
</strong>
<div style="display:flex;margin:1em;">
    <div style="width:100%;margin:1em;">
        <div style="display:flex;">
            <div class="col-lg-4 col-sm-4">
                <div class="card dash_ele_color5">
                    <div class="" style="display: flex">
                        <div style="width: 50%" class="title">
                            <img src="{{ asset('/img/open.gif') }}" style="height: 80px; width: 70px;" alt="">
                        </div>
                        <div style="width: 50%">
                            <p class="title-text">
                                Open
                            </p>
                            <div class="data">
                                <p>{{ $openStatus }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-sm-4">
                <div class="card dash_ele_color3">
                    <div class="" style="display: flex">
                        <div style="width: 50%" class="title">
                            <img src="{{ asset('/img/work-in-progress.gif') }}" style="height: 80px; width: 70px;"
                                alt="">
                        </div>
                        <div style="width: 50%">
                            <p class="title-text">
                                In Process
                            </p>
                            <div class="data">
                                <p>{{ $inProcessStatus }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-sm-4">
                <div class="card dash_ele_color4">
                    <div class="" style="display: flex">
                        <div style="width: 50%" class="title">
                            <img src="{{ asset('/img/closed.gif') }}" style="height: 80px; width: 70px;" alt="">
                        </div>
                        <div style="width: 50%">
                            <p class="title-text">
                                Closed
                            </p>
                            <div class="data">
                                <p>{{ $closedStatus }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
{{-- <div class="row">
    <div class="col-sm-12">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Total Sales')])
            <div id="chart1"></div>
        @endcomponent
    </div>
</div> --}}
<div style="display:flex;margin:1em;">
    <div style="height:400px;width:100%;margin:1em;background:white;">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Sales')])
            <div id="chart1"></div>
        @endcomponent
    </div>
</div>

<br />
<br />
<br />
<div style="display:flex;margin:1em;">
    <div style="width:100%;margin:1em;">

        <div class="panel with-nav-tabs panel-info">
            <div class="panel-heading">
                <h3 style="color: #31708f">Recent Transactions</h3>
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#final" data-toggle="tab">@lang('sale.final')</a></li>
                    <li><a href="#quotation" data-toggle="tab">@lang('lang_v1.quotation')</a></li>
                    <li><a href="#draft" data-toggle="tab">@lang('sale.draft')</a></li>
                </ul>
            </div>
            <div class="panel-body">
                <div class="tab-content" style="height: 400px;">
                    <div class="tab-pane fade in active" id="final">
                        <div class="table-responsive">
                            <div id="error"></div>
                            <table class="table table-hover">
                                {{-- @if ($final->count() > 0)
                                    @if (isset($final) && !empty($final)) --}}
                                <thead id="final_head">

                                </thead>
                                <tbody id="final_table">
                                    {{-- @foreach ($final as $key => $value)
                                                <tr>
                                                    <td style="width: 40px;">{{ $loop->iteration }}.</td>
                                                    <td data-html="true" data-toggle="tooltip"
                                                        title="{{ $value->invoice_no }}({{ $value->name }})
                                                        @if (!empty($value->mobile) && $value->is_default == 0) <br/>  Mobile: {{ $value->mobile }} @endif
                                                        ">
                                                        {{ $value->invoice_no }}({{ $value->name }})</td>
                                                    <td class="display_currency">{{ $value->final_total }}</td>
                                                </tr>
                                            @endforeach --}}
                                </tbody>
                                {{-- @endif
                                @else
                                    <p class="text-center">@lang('sale.no_recent_transactions')</p>
                                @endif --}}
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="quotation">
                        <div class="table-responsive">
                            <div id="error"></div>
                            <table class="table table-hover">
                                {{-- @if ($quotation->count() > 0)
                                    @if (isset($quotation) && !empty($quotation)) --}}
                                <thead id="quotation_head">

                                </thead>
                                <tbody id="quotation_table">

                                    {{-- @foreach ($quotation as $key => $quotations)
                                                <tr>
                                                    <td style="width: 40px;">{{ $loop->iteration }}.</td>
                                                    <td data-html="true" data-toggle="tooltip"
                                                        title="{{ $quotations->invoice_no }}({{ $quotations->name }}),
                                                        @if (!empty($quotations->mobile) && $quotations->is_default == 0) <br />    Mobile: {{ $quotations->mobile }} @endif
                                                        ">
                                                        {{ $quotations->invoice_no }}({{ $quotations->name }})</td>
                                                    <td class="display_currency">{{ $quotations->final_total }}</td>
                                                    <td class="abname"></td>
                                                </tr>
                                            @endforeach --}}

                                </tbody>
                                {{-- @endif
                                @else
                                    <p class="text-center">@lang('sale.no_recent_transactions')</p>
                                @endif --}}
                            </table>
                        </div>
                    </div>


                    <div class="tab-pane fade" id="draft">
                        <div class="table-responsive">
                            <div id="error"></div>
                            <table class="table table-hover">
                                {{-- @if ($draft->count() > 0)
                                    @if (isset($draft) && !empty($draft)) --}}
                                <thead id="draft_head">
                                </thead>
                                <tbody id="draft_table">
                                    {{-- @foreach ($draft as $key => $drafts)
                                                <tr>
                                                    <td style="width: 40px;">{{ $loop->iteration }}.</td>
                                                    <td data-html="true" data-toggle="tooltip"
                                                        title="{{ $drafts->invoice_no }}({{ $drafts->name }})
                                                        @if (!empty($drafts->mobile) && $drafts->is_default == 0) <br />  Mobile: {{ $drafts->mobile }} @endif
                                                        ">
                                                        {{ $drafts->invoice_no }}({{ $drafts->name }})</td>
                                                    <td class="display_currency">{{ $drafts->final_total }}</td>
                                                </tr>
                                            @endforeach --}}
                                </tbody>
                                {{-- @endif
                                @else
                                    <p class="text-center">@lang('sale.no_recent_transactions')</p>
                                @endif --}}
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div style="margin: 1em;">
    <div class="row">
        <div class="col-md-2">
             <button type="button" class="btn btn-primary btn-modal"
                            data-href="{{ route('smartcrm.lead.LeadsCreate') }}" data-container=".contact_modal">
                            <i class="fa fa-plus"></i> Add Lead</button>
        </div>
         <div class="col-md-3">
            <button class="btn btn-primary" onclick="LoadMap();" type="button" id="LoadButton"><i class='fa fa-map-marker' style='color:#e22727'></i> Load
                Google Map</button>
        </div>
    </div>
</div>
<br>
<div style="display: flex; margin: 1em;" class="hide" id="GoogleMap">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('')])
        <div id="map" style="width: 100%; height: 400px;"></div>
    @endcomponent
</div>
<div class="modal fade" id="followupmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('smartcrm.followup.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Create a follow up</h4>
                    </div>
                    <div class="modal-body row">

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Subject:</label>
                                <input type="text" class="form-control" name="title" />
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('contact_id',  __('contact.customer') . ':') !!}
                                {!! Form::select('contact_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all'), 'id' => 'contacts_id']); !!}
                            </div>
                        </div>
                        @if(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Administration#' . auth()->user()->business_id))
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('assigned_to',  __('report.user') . ':') !!}
                                {!! Form::select('assigned_to', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                            </div>
                        </div>
                        @else
                        <input type="hidden" name="assigned_to" value="{{ auth()->user()->id }}">
                        @endif
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Next Schedule At</label>
                                <input type="text" class="form-control" name="scheduled_at" id="scheduled_at"/>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('status',  __('Status') . ':') !!}
                                {!! Form::select('status', $status, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                            </div>
                        </div>
                        <!--<div class="col-md-4">-->
                        <!--    <div class="form-group">-->
                        <!--        {!! Form::label('priority',  __('Priority') . ':') !!}-->
                        <!--        {!! Form::select('priority', $priorities, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}-->
                        <!--    </div>-->
                        <!--</div>-->
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('channel',  __('Channel') . ':') !!}
                                {!! Form::select('channel', $channel, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Conversation Notes:</label>
                                <textarea class="form-control" rows="3" name="notes"></textarea>
                            </div>
                        </div>

                        <div class="col-sm-12" style="display: flex;flex-direction: column;">
                            <label style="width:100%;">Tags:</label>
                            <input type="text" id="tags" name="tags" class="form-control" style="width:100%;" />
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" data-backdrop="static"
    aria-labelledby="gridSystemModalLabel">
</div>
@stop
@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js">
</script>
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC8Jc4HBUsp9w_I9-rUTBS3t7v0atcBzWc&callback=initMap"
    defer></script>
<script>
    let map;
    let userMarker;
function getUserLocation() {
    var infowindow = new google.maps.InfoWindow();
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                // Get user's latitude and longitude
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                $("#coordinates").val(userLat + ", " + userLng);

                // Set the center of the map to the user's live location
                map.setCenter(new google.maps.LatLng(userLat, userLng));
                if (userMarker) {
                userMarker.setPosition(new google.maps.LatLng(userLat, userLng));
            } else {
                var Userimage = {
                url: "/img/UserMap.gif",
                scaledSize: {
                    width: 50,
                    height: 50
                },
            };
                userMarker = new google.maps.Marker({
                    position: new google.maps.LatLng(userLat, userLng),
                    map: map,
                    icon: Userimage,
                    // title: 'Your Location',
                });
                
                 google.maps.event.addListener(userMarker, "click", function() {
                        infowindow.setContent(
                            "<div class='infowindow-content'>" +
                            "<button type='button' class='btn btn-block btn-primary btn-modal' " +
                            "data-href='{{ route('smartcrm.lead.LeadsCreate') }}' data-container='.contact_modal'>" +
                            "Add Leads" +
                            "</button>" +
                            "</div>"
                        );

                        // Open the info window
                        infowindow.open(map, userMarker);

                       
                    });
            }
            }, function(error) {
                console.error("Error getting user's location:", error.message);
            });
        } else {
            console.error("Geolocation is not supported by this browser.");
        }
    }
     $(document).on('shown.bs.modal', '.contact_modal', function(e) {
          $($(this).data('container')).modal('show');

            // Get user's location and update coordinates field in the modal
            getUserLocation();
        initAutocomplete();
    }); 
    function initMap() {
        map = new google.maps.Map(document.getElementById("map"), {
            // center: {
            //     lat: 40.72888581882913,
            //     lng: -73.79374318705824
            // },
            zoom: 5,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
        });
        
            getUserLocation();

    }
    window.initMap = initMap;

    var i;
    var markers = [];
    var marker;
    var markerCluster = [];

   
    
    // Function to create markers
function createMarker(location, image, infowindow, isSales) {
    const marker = new google.maps.Marker({
        position: new google.maps.LatLng(location[0], location[1]),
        map: map,
        optimized: false,
        icon: image,
    });

    google.maps.event.addListener(marker, "click", function () {
        const content = isSales
            ? createSalesInfoWindowContent(location)
            : createLeadsInfoWindowContent(location);

        infowindow.setContent(content);
        infowindow.open(map, marker);
    });

    return marker;
}

// Function to create sales info window content
function createSalesInfoWindowContent(location) {
    return `<div class='infowindow-content'>
                <h5><b>Store Name:</b> ${location[2]}</h5>
                <h5><b>Total Sell (Final Total):</b> ${__currency_trans_from_en(location[3])}</h5>
                <div class='btn-toolbar' role='toolbar'>
                    <div class='btn-group' role='group'>
                        <a  data-toggle='modal' data-target='#followupmodal' class='btn btn-primary follow-up-button' data-contact-id='${location[4]}'>Follow Up</a>
                    </div>
                    <div class='btn-group' role='group'>
                        <a href='/contacts/${location[4]}' target='_blank' role='button' class='btn btn-info'>More Details</a>
                    </div>
                    <div class='btn-group' role='group'>
                        <a href='https://www.google.com/maps/search/?api=1&query=${location[0]},${location[1]}' target='_blank' role='button' class='btn btn-danger'>Open in Google Map</a>
                    </div>
                </div>
            </div>`;
}

// Function to create leads info window content
function createLeadsInfoWindowContent(lead) {
    return `<h5><b>Lead Name:</b> ${lead[2]}</h5>`;
}

function LoadMap(){
    $("#GoogleMap").removeClass('hide');
    $("#LoadButton").addClass('hide');

    // Now, make the $.ajax call
$.ajax({
    url: "/CashierDashboard/googlemap",
    type: "get",
    dataType: "json",
    beforeSend: function () {
        // DeleteMarkers();
    },
    success: function (data) {
        const salesData = data.salesData;
        const leadsData = data.leadsData;

        var infowindow = new google.maps.InfoWindow();

        // Sales marker configuration
        const salesImage = {
            url: "/img/map_store.gif",
            scaledSize: {
                width: 50,
                height: 50
            },
        };

        // Leads marker configuration
        const leadsImage = {
            url: "/img/leads.gif",
            scaledSize: {
                width: 60,
                height: 60
            },
        };
        
        const greenIcon = {
                url: "/img/followupstore.png", 
                scaledSize: {
                    width: 60,
                    height: 60
                },
            };
            
        // Create sales markers
        // const salesMarkers = salesData.map(function (location) {
        //     return createMarker(location, salesImage, infowindow, true);
        // });
         const salesMarkers = salesData.map(function(location) {
                const image = location[5] ? greenIcon : salesImage;
                return createMarker(location, image, infowindow, true);
            });

        // Create leads markers
        const leadsMarkers = leadsData.map(function (lead) {
            return createMarker(lead, leadsImage, infowindow, false);
        });

        // Combine all markers (sales and leads) for clustering
        const allMarkers = [...salesMarkers, ...leadsMarkers];

        // Initialize marker clusterer
        const markerCluster = new MarkerClusterer(map, allMarkers, {
            imagePath: "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
        });
    },
    error: function (data) {
        toastr.error("Data Not Found");
    },
});
}


    $(document.body).on('click', '.follow-up-button', function () {
        $('#scheduled_at').datetimepicker().val('{{ date("m/d/Y H:i:s") }}');

        var contactId = $(this).data('contact-id');
                $('#contacts_id').val(contactId).trigger('change');
    });
      function openFollowUpPopup(customerId) {
        // Open a new window with the Follow Up URL
        var followUpUrl = "/smart-crm/follow-up";
        var followUpWindow = window.open(followUpUrl, "_blank");

        // Wait for the new window to load before interacting with its content
        followUpWindow.onload = function() {

            var followUpModal = $(followUpWindow.document.getElementById("followupmodal"));

            // Show the modal
            followUpModal.modal("show");

          
        };
    }
</script>
<script type="text/javascript">
    $(document).ready(function() {
getReportdata();
        // var start, end;

        // $(document).ready(function(e) {
        //     $("#all_date_filter").daterangepicker({
        //         ranges: ranges,
        //         autoUpdateInput: true,
        //         startDate: moment().startOf("days").subtract(6, 'days'),
        //         endDate: moment().endOf("days"),
        //         locale: {
        //             format: moment_date_format,
        //         },
        //     });
        //     $("#all_date_filter").on("apply.daterangepicker", function(ev, picker) {
        //         $(this).val(
        //             picker.startDate.format(moment_date_format) +
        //             " ~ " +
        //             picker.endDate.format(moment_date_format)
        //         );
        //         $("#date").val($(this).val());
        //     });

        //     $("#all_date_filter").on("cancel.daterangepicker", function(ev, picker) {
        //         $(this).val("");
        //     });
        //     getReportdata();
        // });

        // $(document).on("click", "button#submitData", function(e) {
        //     e.preventDefault();
        //     getReportdata();
        // });

        function getReportdata() {
            // if ($("input#all_date_filter").val()) {
            //     start = $("input#all_date_filter")
            //         .data("daterangepicker")
            //         .startDate.format("YYYY-MM-DD");
            //     end = $("input#all_date_filter")
            //         .data("daterangepicker")
            //         .endDate.format("YYYY-MM-DD");
            // }

            $.ajax({
                method: "get",
                url: "/CashierDashboard/gettotals",
                dataType: "json",
                // data: {
                //     start_date: start,
                //     end_date: end,
                // },
                success: function(data) {

                    //purchase details
                    //   $(".final").html(data.final, true);
                    //   $(".quotation").html(data.quotation, true);
                    $(".draft").html(data.draft.toLocaleString('en'), true);
                    $(".total_transactions").html(data.total_transactions.toLocaleString('en'),
                        true);
                    $(".total_sell").html("$" + __intToString(data.total_sell, true));
                }
            });

            $.ajax({
                method: "get",
                url: "/CashierDashboard/getvalue",
                dataType: "json",
                // data: {
                //     start_date: start,
                //     end_date: end,
                // },
                success: function(response) {
                    var tableSearch = $('#final_table');
                    var final_head = $('#final_head');
                    final_head.html('');
                    tableSearch.html('');

                    if (!jQuery.isEmptyObject(response)) {
                        final_head.append("<tr><th>" + 'No' + "</th>" + "<th>" + 'Name' + "</th>" +
                            "<th>" + 'Amount' + "</th></tr>");
                        $.each(response, function(index, value) {
                            var no = index + 1;
                            // tableSearch.append("<p>"no_recent_transactions"</p>");
                            tableSearch.append("<tr><td style='width: 40px;'>" + no +
                                "</td>" + "<td>" + value.invoice_no + "(" + value.name +
                                ")" + "</td><td class='display_currency'>" + __currency_trans_from_en(value.final_total) + "</td></tr>");

                        });
                    } else {
                        var er = 'No Recent Transactions';
                         tableSearch.append("<p class='text-center'>" + er +
                            "</p>");
                    }
                }
            });


            $.ajax({
                method: "get",
                url: "/CashierDashboard/quotation",
                dataType: "json",
                // data: {
                //     start_date: start,
                //     end_date: end,
                // },
                success: function(data) {
                    var quotation_table = $('#quotation_table');
                    var er_msg = $('#error');
                    var quotation_head = $('#quotation_head');
                    quotation_head.html('');
                    quotation_table.html('');
                    er_msg.html('');

                    if (!jQuery.isEmptyObject(data)) {
                        quotation_head.append("<tr><th>" + 'No' + "</th>" + "<th>" + 'name' +
                            "</th>" + "<th>" + 'Amount' + "</th></tr>");
                        $.each(data, function(index, value) {
                            var no = index + 1;
                            // quotation_table.append("<p>"no_recent_transactions"</p>");
                            quotation_table.append("<tr><td style='width: 40px;'>" + no +
                                "</td>" + "<td>" + value.invoice_no + "(" + value.name +
                                ")" + "</td><td class='display_currency'>" + __currency_trans_from_en(value
                                .final_total) + "</td></tr>");

                        });
                    } else {
                        var er = 'No Recent Transactions';
                        quotation_table.append("<p class='text-center'>" + er +
                            "</p>");
                    }
                }
            });

            $.ajax({
                method: "get",
                url: "/CashierDashboard/draft",
                dataType: "json",
                // data: {
                //     start_date: start,
                //     end_date: end,
                // },
                success: function(response) {
                    var draft_table = $('#draft_table');
                    var er_msg = $('#error');
                    var draft_head = $('#draft_head');

                    draft_table.html('');
                    er_msg.html('');
                    draft_head.html('');

                    if (!jQuery.isEmptyObject(response)) {
                        draft_head.append("<tr><th>" + 'No' + "</th>" + "<th>" + 'name' + "</th>" +
                            "<th>" + 'Amount' + "</th></tr>");
                        $.each(response, function(index, value) {
                            var no = index + 1;
                            // tableSearch.append("<p>"no_recent_transactions"</p>");
                            draft_table.append("<tr><td style='width: 40px;'>" + no +
                                "</td>" + "<td>" + value.invoice_no + "(" + value.name +
                                ")" + "</td><td class='display_currency'>" + __currency_trans_from_en(value
                                .final_total) + "</td></tr>");

                        });
                    } else {
                        var er = 'No Recent Transactions';
                        draft_table.append("<p class='text-center'>" + er +
                            "</p>");
                    }

                },

            });
            
            var currentDate = new Date();
            var currentYear = currentDate.getFullYear();
            var currentMonth = currentDate.getMonth();
            
            // Calculate start date as January 1st of the current year
            var startDate = new Date(currentYear, 0, 1);
            
            // Calculate end date as the last day of the current month
            var endDate = new Date(currentYear, currentMonth + 1, 0);
            
            var options = {
                series: [],
                chart: {
                    height: 350,
                    type: 'area'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    type: 'datetime',
                    categories: [],
                    min: startDate.getTime(),
                    max: endDate.getTime(),
                },
                tooltip: {
                    x: {
                        format: 'dd/MM/yy'
                    },
                },
                title: {
                    // text: "(Sell = Payable Amount)",
                    rotate: -90,
                    offsetX: 500,
                    offsetY: 0,
                    style: {
                    fontSize: '12px',
                    fontFamily: 'Helvetica, Arial, sans-serif',
                    fontWeight: 600,
                    cssClass: 'apexcharts-yaxis-title',
                    },
                },
                noData: {
                    text: "Loading...",
                },
            };

            $("#chart1", function() {
                var url = "/CashierDashboard/total-sales";
                axios({
                    method: "GET",
                    url: url,
                }).then(function(response) {
                    chart.updateOptions({
                        series: [{
                            name: "Total Sell",
                            data: response.data[0],
                        }, ],
                        xaxis: {
                            type: "datetime",
                            categories: response.data[1],
                        },
                        yaxis: [{
                            labels: {
                                formatter: function(val) {
                                    return (val).toLocaleString();
                                },
                            },
                        }, ],
                    });
                });
            });
            var chart = new ApexCharts(document.querySelector("#chart1"), options);
            chart.render();

        }
    });




    function __number_format(number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
    }

    function __intToString(num) {
        num = num.toString().replace(/[^0-9.]/g, "");
        if (num < 1000) {
            return num;
        }
        let si = [{
                v: 1e3,
                s: "K"
            },
            {
                v: 1e6,
                s: "M"
            },
            {
                v: 1e9,
                s: "B"
            },
            {
                v: 1e12,
                s: "T"
            },
            {
                v: 1e15,
                s: "P"
            },
            {
                v: 1e18,
                s: "E"
            },
        ];
        let index;
        for (index = si.length - 1; index > 0; index--) {
            if (num >= si[index].v) {
                break;
            }
        }
        return (
            (num / si[index].v).toFixed(2).replace(/\.0+$|(\.[0-9]*[1-9])0+$/, "$1") +
            si[index].s
        );
    }
</script>


@endsection
