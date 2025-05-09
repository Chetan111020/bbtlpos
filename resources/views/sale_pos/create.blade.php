@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
	<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">

	<section style="margin-top: 20px !important;" class="content no-print">
	<input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? ''}}">
	<input type="hidden" name="invoice_no" id="invoice_no" value="{{ @$invoice_no }}">
	@if(!empty($pos_settings['allow_overselling']))
		<input type="hidden" id="is_overselling_allowed">
	@endif
	@if(session('business.enable_rp') == 1)
        <input type="hidden" id="reward_point_enabled">
    @endif
    @php
		$is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
		$is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
	@endphp
	{!! Form::open(['url' => action('SellPosController@store'), 'method' => 'post', 'id' => 'add_pos_sell_form' ]) !!}
	<div class="row mb-12">
		<div class="col-md-12">
			<div class="row">
				<div class="@if(empty($pos_settings['hide_product_suggestion'])) col-md-10 @else col-md-10 col-md-offset-1 @endif no-padding pr-12">
					<div class="box">
						<div class="box-body pb-0">
							{!! Form::hidden('location_id', $default_location->id ?? null , ['id' => 'location_id', 'data-receipt_printer_type' => !empty($default_location->receipt_printer_type) ? $default_location->receipt_printer_type : 'browser', 'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '']); !!}
							<!-- sub_type -->
							{!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
							<input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">
								@include('sale_pos.partials.pos_form', ['sell_details' => $sell_details])

								@include('sale_pos.partials.pos_tax')

								@include('sale_pos.partials.pos_form_totals')

								@include('sale_pos.partials.payment_modal')

								@if(empty($pos_settings['disable_suspend']))
									@include('sale_pos.partials.suspend_note_modal')
								@endif

								@if(empty($pos_settings['disable_recurring_invoice']))
									@include('sale_pos.partials.recurring_invoice_modal')
								@endif
						</div>
					</div>
				</div>

				@if(empty($pos_settings['hide_product_suggestion']) && !isMobile())
				<div class="col-md-2 no-padding">
					@include('sale_pos.partials.pos_sidebar')
				</div>
				@endif
			</div>
		</div>
	</div>
	@include('sale_pos.partials.pos_form_actions')
	{!! Form::close() !!}
</section>

<!-- This will be printed -->
<section class="invoice print_section" id="receipt_section">
</section>

@if(empty($pos_settings['hide_product_suggestion']) && isMobile())
	@include('sale_pos.partials.mobile_product_suggestions')
@endif
<!-- /.content -->

<div class="modal fade register_details_modal" tabindex="-1" role="dialog"
	aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog"
	aria-labelledby="gridSystemModalLabel">
</div>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>
<div class="modal fade payment_modal" tabindex="-1" role="dialog"
    aria-labelledby="gridSystemModalLabel">
</div>
@include('sale_pos.partials.configure_search_modal')

@include('sale_pos.partials.recent_transactions_modal')

@include('sale_pos.partials.weighing_scale_modal')

{{--@stop--}}
@section('css')
	<!-- include module css -->
    @if(!empty($pos_module_data))
        @foreach($pos_module_data as $key => $value)
            @if(!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
	<style>
		.toggle.btn{
			min-width: 5.7rem;
			min-height: 3.50rem;
		}
		.toggle-group{
			width: 190%;
		}
	</style>
@stop
@section('javascript')
	<script src="{{ asset('js/pos.js?rev='.date('YmdHi').'&v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
	<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
	<script>

		function addToDataTableOne(id, variation_id = null, purchase_line_id = null, weighing_scale_barcode = null, quantity = 1)
		{
		    var customer_id = $("#customer_id").val();

				$.ajax({
					method: 'POST',
					url: '/products/check_catgeory',
					data: {
						customer_id: customer_id ,
						id:id,
					},
					dataType: 'json',
					success: function(result) {
						if(result.success == false){
							toastr.error(result.message);
						}else{
							pos_product_row(id, purchase_line_id);
							if(!$('div').hasClass('toast-error'))
        					{
								$('div#'+id).closest("tr").css({'background-color': '#808080' , 'color' : 'white'});
								$('div#'+id).children('a.product-link').css({'color' : 'white'});
							}
						}
					},
    	});
// 			pos_product_row(id, purchase_line_id);
			//$("#open-datatable-one").hide();
		}
		$(document).on('click', function(e) {
			if ($(e.target).closest("#open-datatable-one").length === 0) {
				$("#open-datatable_wrapper").hide();
				$("#open-datatable-one").hide();
			}
		});
		$(document).on('keydown', function(e, ui) {
		   if (e.keyCode === $.ui.keyCode.ESCAPE) {
		   		$("#open-datatable_wrapper").hide();
				$("#open-datatable-one").hide();
		   		$('input#search_product_pos_one').val('');
		   }
		});
    	$(function(){
    		var focusedRow = null;
    		$('#open-datatable').on('keydown', function(ev){
    			//console.log(ev.keyCode);
    			if(focusedRow == null) {
    				focusedRow = $('tr:nth-child(1)', '#open-datatable').next();
    				$('tr:nth-child(1)', '#open-datatable').next().find('.product-link').focus().select();
    			} else if(ev.keyCode === 38) {
    				//focusedRow.toggleClass('focused');
    				focusedRow.prev().find('.product-link').focus().select();
    				focusedRow = focusedRow.prev('tr');
    			} else if(ev.keyCode === 40) {
    				focusedRow.next().find('.product-link').focus().select();
    				//focusedRow.toggleClass('focused');
    				focusedRow = focusedRow.next();
    			}
    			focusedRow.toggleClass('focused');
    		});
    		//$('#open-datatable').focus();
		    $('#search_product').focus();
    	});

	</script>
	@include('sale_pos.partials.keyboard_shortcuts')

	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if(!empty($pos_module_data))
	    @foreach($pos_module_data as $key => $value)
            @if(!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
	    @endforeach
	@endif
@endsection

@php
    $api_key = env('GOOGLE_MAP_API_KEY');
@endphp
@if(!empty($api_key))
@section('css')
    @include('contact.partials.google_map_styles')
@endsection
@endif

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