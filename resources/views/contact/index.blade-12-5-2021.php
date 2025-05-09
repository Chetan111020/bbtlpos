@extends('layouts.app')
@section('title', __('lang_v1.'.$type.'s'))
@php
    $api_key = env('GOOGLE_MAP_API_KEY');
@endphp
@if(!empty($api_key))
@section('css')
    @include('contact.partials.google_map_styles')
@endsection
@endif
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> @lang('lang_v1.'.$type.'s')
            <small>@lang( 'contact.manage_your_contact', ['contacts' =>  __('lang_v1.'.$type.'s') ])</small>
        </h1>
        <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-md-3">
                                <h4>Filter</h4>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>State</label>
                                    <select id="contact_state" type="text" value="" name="state" class="form-control">
                                        <option value="">All</option>
                                        <option value="Alabama">Alabama</option>
                                        <option value="Alaska">Alaska</option>
                                        <option value="Arizona">Arizona</option>
                                        <option value="Arkansas">Arkansas</option>
                                        <option value="California">California</option>
                                        <option value="Colorado">Colorado</option>
                                        <option value="Connecticut">Connecticut</option>
                                        <option value="Delaware">Delaware</option>
                                        <option value="District Of Columbia">District Of Columbia</option>
                                        <option value="Florida">Florida</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Hawaii">Hawaii</option>
                                        <option value="Idaho">Idaho</option>
                                        <option value="Illinois">Illinois</option>
                                        <option value="Indiana">Indiana</option>
                                        <option value="Iowa">Iowa</option>
                                        <option value="Kansas">Kansas</option>
                                        <option value="Kentucky">Kentucky</option>
                                        <option value="Louisiana">Louisiana</option>
                                        <option value="Maine">Maine</option>
                                        <option value="Maryland">Maryland</option>
                                        <option value="Massachusetts">Massachusetts</option>
                                        <option value="Michigan">Michigan</option>
                                        <option value="Minnesota">Minnesota</option>
                                        <option value="Mississippi">Mississippi</option>
                                        <option value="Missouri">Missouri</option>
                                        <option value="Montana">Montana</option>
                                        <option value="Nebraska">Nebraska</option>
                                        <option value="Nevada">Nevada</option>
                                        <option value="New Hampshire">New Hampshire</option>
                                        <option value="New Jersey">New Jersey</option>
                                        <option value="New Mexico">New Mexico</option>
                                        <option value="New York">New York</option>
                                        <option value="North Carolina">North Carolina</option>
                                        <option value="North Dakota">North Dakota</option>
                                        <option value="Ohio">Ohio</option>
                                        <option value="Oklahoma">Oklahoma</option>
                                        <option value="Oregon">Oregon</option>
                                        <option value="Pennsylvania">Pennsylvania</option>
                                        <option value="Rhode Island">Rhode Island</option>
                                        <option value="South Carolina">South Carolina</option>
                                        <option value="South Dakota">South Dakota</option>
                                        <option value="Tennessee">Tennessee</option>
                                        <option value="Texas">Texas</option>
                                        <option value="Utah">Utah</option>
                                        <option value="Vermont">Vermont</option>
                                        <option value="Virginia">Virginia</option>
                                        <option value="Washington">Washington</option>
                                        <option value="West Virginia">West Virginia</option>
                                        <option value="Wisconsin">Wisconsin</option>
                                        <option value="Wyoming">Wyoming</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>City</label>
                                    <select id="contact_city" type="text" value="" name="city" class="form-control">
                                        <option value="">All</option>
                                        <option value="New York">New York</option>
                                        <option value="California">California</option>
                                        <option value="Texas">Texas</option>
                                        <option value="Arizona">Arizona</option>
                                        <option value="Pennsylvania">Pennsylvania</option>
                                        <option value="California">California</option>
                                        <option value="California">California</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" value="{{$type}}" id="contact_type">
        @component('components.widget', ['class' => 'box-primary', 'title' => __( 'contact.all_your_contact', ['contacts' => __('lang_v1.'.$type.'s') ])])
            @if(auth()->user()->can('supplier.create') || auth()->user()->can('customer.create') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own'))
                @slot('tool')
                    <div class="box-tools">
                        <button type="button" class="btn btn-block btn-primary btn-modal"
                                data-href="{{action('ContactController@create', ['type' => $type])}}"
                                data-container=".contact_modal">
                            <i class="fa fa-plus"></i> @lang('messages.add')</button>
                    </div>
                @endslot
            @endif
            @if(auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own'))
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="contact_table">
                        <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all-rows" ></th>
                            <th>@lang('messages.action')</th>
                            <th>@lang('lang_v1.contact_id')</th>
                            @if($type == 'supplier')
                                <th>@lang('business.business_name')</th>
                                <th>@lang('contact.name')</th>
                                <th>@lang('business.email')</th>
                                <th>@lang('contact.tax_no')</th>
                                <th>@lang('contact.pay_term')</th>
                                <th>@lang('account.opening_balance')</th>
                                <th>@lang('lang_v1.advance_balance')</th>
                                <th>@lang('lang_v1.added_on')</th>
                                <th>@lang('business.address')</th>
                                <th>@lang('contact.mobile')</th>
                                <th>@lang('contact.total_purchase_due')</th>
                                <th>@lang('lang_v1.total_purchase_return_due')</th>
                            @elseif( $type == 'customer')
                                <th>@lang('business.business_name')</th>
                                <th>@lang('user.name')</th>
                                <th>@lang('business.email')</th>
                                <th>@lang('contact.tax_no')</th>
                                <th>@lang('lang_v1.credit_limit')</th>
                                <th>@lang('contact.pay_term')</th>
                                <th>@lang('account.opening_balance')</th>
                                <th>@lang('lang_v1.advance_balance')</th>
                                <th>@lang('lang_v1.added_on')</th>
                                @if($reward_enabled)
                                    <th id="rp_col">{{session('business.rp_name')}}</th>
                                @endif
                                <th>@lang('business.first_name')</th>
                                <th>@lang('lang_v1.customer_group')</th>
                                <th>@lang('business.address')</th>
                                <th>@lang('contact.mobile')</th>
                                <th>@lang('contact.total_sale_due')</th>
                                <th>@lang('lang_v1.total_sell_return_due')</th>
                                <th>@lang('lang_v1.sales_rep')</th>
                                <th>@lang('lang_v1.account_rep')</th>
                                {{--<th>@lang('lang_v1.ref_code')</th>--}}
                                {{--<th>@lang('lang_v1.note')</th>--}}
                                {{--<th>@lang('lang_v1.file')</th>--}}
                                {{--<th>@lang('lang_v1.tax_id')</th>--}}
                                <th>@lang('lang_v1.nyc')</th>
                            @endif
                            {{-- @php
                                 $custom_labels = json_decode(session('business.custom_labels'), true);
                             @endphp--}}
                            <th style="display:none;">
                                {{ $custom_labels['contact']['custom_field_1'] ?? __('lang_v1.contact_custom_field1') }}
                            </th>
                            <th style="display:none;">
                                {{ $custom_labels['contact']['custom_field_2'] ?? __('lang_v1.contact_custom_field2') }}
                            </th>
                            <th style="display:none;">
                                {{ $custom_labels['contact']['custom_field_3'] ?? __('lang_v1.contact_custom_field3') }}
                            </th>
                            <th style="display:none;">
                                {{ $custom_labels['contact']['custom_field_4'] ?? __('lang_v1.contact_custom_field4') }}
                            </th>
                            <th style="display:none;">
                                {{ $custom_labels['contact']['custom_field_5'] ?? __('lang_v1.custom_field', ['number' => 5]) }}
                            </th>
                            <th style="display:none;">
                                {{ $custom_labels['contact']['custom_field_6'] ?? __('lang_v1.custom_field', ['number' => 6]) }}
                            </th>
                            <th style="display:none;">
                                {{ $custom_labels['contact']['custom_field_7'] ?? __('lang_v1.custom_field', ['number' => 7]) }}
                            </th>
                            <th style="display:none;">
                                {{ $custom_labels['contact']['custom_field_8'] ?? __('lang_v1.custom_field', ['number' => 8]) }}
                            </th>
                            <th style="display:none;">
                                {{ $custom_labels['contact']['custom_field_9'] ?? __('lang_v1.custom_field', ['number' => 9]) }}
                            </th>
                            <th style="display:none;">
                                {{ $custom_labels['contact']['custom_field_10'] ?? __('lang_v1.custom_field', ['number' => 10]) }}
                            </th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr class="font-17 text-center footer-total">

                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td
                                    @if($type == 'supplier')
                                    colspan="6"
                                    @elseif( $type == 'customer')
                                    @if($reward_enabled)
                                    colspan="9"
                                    @else
                                    colspan="8"
                                    @endif
                                    @endif>
                                <strong>
                                    @lang('sale.total'):
                                </strong>
                            </td>
                            <td><span class="display_currency" id="footer_contact_due"
                                      data-currency_symbol="true"></span></td>
                            <td><span class="display_currency" id="footer_contact_return_due"
                                      data-currency_symbol="true"></span></td>
                            {{--<td></td>--}}
                            {{--<td></td>--}}
                            {{--<td></td>--}}
                            {{--<td></td>--}}
                            {{--<td></td>--}}
                            {{--<td></td>--}}
                            {{--<td></td>--}}
                            {{--<td></td>--}}
                            {{--<td></td>--}}
                            {{--<td></td>--}}
                        </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        @endcomponent

        <div class="modal fade contact_modal" tabindex="-1" role="dialog"
             aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade pay_contact_due_modal" tabindex="-1" role="dialog"
             aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->
@stop
@section('javascript')
    @if(!empty($api_key))
        <script>
            // This example adds a search box to a map, using the Google Place Autocomplete
            // feature. People can enter geographical searches. The search box will return a
            // pick list containing a mix of places and predicted search terms.

            // This example requires the Places library. Include the libraries=places
            // parameter when you first load the API. For example:
            // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

            function initAutocomplete() {
                var map = new google.maps.Map(document.getElementById('map'), {
                    center: {lat: -33.8688, lng: 151.2195},
                    zoom: 10,
                    mapTypeId: 'roadmap'
                });

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        initialLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                        map.setCenter(initialLocation);
                    });
                }


                // Create the search box and link it to the UI element.
                var input = document.getElementById('shipping_address');
                var searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

                // Bias the SearchBox results towards current map's viewport.
                map.addListener('bounds_changed', function () {
                    searchBox.setBounds(map.getBounds());
                });

                var markers = [];
                // Listen for the event fired when the user selects a prediction and retrieve
                // more details for that place.
                searchBox.addListener('places_changed', function () {
                    var places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }

                    // Clear out the old markers.
                    markers.forEach(function (marker) {
                        marker.setMap(null);
                    });
                    markers = [];

                    // For each place, get the icon, name and location.
                    var bounds = new google.maps.LatLngBounds();
                    places.forEach(function (place) {
                        if (!place.geometry) {
                            console.log("Returned place contains no geometry");
                            return;
                        }
                        var icon = {
                            url: place.icon,
                            size: new google.maps.Size(71, 71),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(17, 34),
                            scaledSize: new google.maps.Size(25, 25)
                        };

                        // Create a marker for each place.
                        markers.push(new google.maps.Marker({
                            map: map,
                            icon: icon,
                            title: place.name,
                            position: place.geometry.location
                        }));

                        //set position field value
                        var lat_long = [place.geometry.location.lat(), place.geometry.location.lng()]
                        $('#position').val(lat_long);

                        if (place.geometry.viewport) {
                            // Only geocodes have viewport.
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
            }

        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{$api_key}}&libraries=places"
                async defer></script>
        <script type="text/javascript">
            $("#select-all-rows").click(function () {
                $(".checkBoxClass").attr('checked', this.checked);
            });
            $(document).on('shown.bs.modal', '.contact_modal', function (e) {
                initAutocomplete();
            });

        </script>
    @endif
@endsection
