<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-user"></i>
				</span>
				<input type="hidden" id="default_customer_id"
				value="{{ $walk_in_customer['id'] ?? ''}}" >
				<input type="hidden" id="default_customer_name"
				value="{{ $walk_in_customer['name'] ?? ''}}" >
				<input type="hidden" id="default_customer_balance"
				value="{{ $walk_in_customer['balance'] ?? ''}}" >
				<input type="hidden" id="customer_state" value="{{ $walk_in_customer['state'] ?? ''}}">


                @if (!empty($back_order_customer->id))
				{!! Form::select('contact_id',[$back_order_customer->id => $back_order_customer->name], $back_order_customer->id, ['class' => 'form-control mousetrap ', 'id' => 'customer_id', 'placeholder' => 'Enter Customer name / phone', 'required', 'style' => 'width: 100%;']); !!}
                @else
                {!! Form::select('contact_id',[], null, ['class' => 'form-control mousetrap', 'id' => 'customer_id', 'placeholder' => 'Enter Customer name / phone', 'required', 'style' => 'width: 100%;']); !!}
                @endif

				<span class="input-group-btn">
					<button style="font-size: 20px;" type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""  @if(!auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
				</span>
			</div>
		</div>
	</div>
	<div class="col-md-5">
		<div class="form-group">
			<div class="input-group">
				<div class="input-group-btn">
					<button style="font-size: 20px;" type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fa fa-barcode"></i></button>
				</div>
				{!! Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product_pos_one', 'placeholder' => __('lang_v1.search_product_placeholder'),
				'disabled' => is_null($default_location)? true : false,
				'autofocus' => is_null($default_location)? false : false,
				]); !!}
				<span class="input-group-btn">
					<!-- Show button for weighing scale modal -->
					@if(isset($pos_settings['enable_weighing_scale']) && $pos_settings['enable_weighing_scale'] == 1)
						<button style="font-size: 20px;" type="button" class="btn btn-default bg-white btn-flat" id="weighing_scale_btn" data-toggle="modal" data-target="#weighing_scale_modal"
						title="@lang('lang_v1.weighing_scale')"><i class="fa fa-digital-tachograph text-primary fa-lg"></i></button>
					@endif
					<button style="font-size: 20px;" type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product" data-href="{{action('ProductController@quickAdd')}}" data-container=".quick_add_product_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
				</span>
			</div>
		</div>
		<style type="text/css">
			.qtypos{
				width:10% !important;
				min-width:10% !important;
			}
			.namepos{
				width:80% !important;
				min-width:80% !important;
			}
			.qtysalespricepos{
				width:20% !important;
				min-width:20% !important;
			}
		</style>
		<div class="row">
			<div id="open-datatable-one" class="table-responsive col-sm-9 col-sm-offset-2" style="z-index: 9999!important; position: absolute; background-color: #fff;margin: -5px 0 0 15px;border:1px solid #000;width: 153%;display:none">
					<button type="button" class="btn btn-primary btn-flat" id="text-button"> Add All</button>
				<table id="open-datatable" class="table dt-responsive nowrap w-100" width="100%">
					<thead>
						<tr>
							<th class="qtypos">QTY</th>
							<th class="namepos">Name</th>
							<th class="item_codepos">Item Code</th>
							<th class="qtysalespricepos">Sales<br>Price</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<div class="input-group">
				<div class="input-group-btn">
					<button style="font-size: 20px;" type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fa fa-barcode"></i></button>
				</div>
				{!! Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('Enter Barcode To See Product'),
				'disabled' => is_null($default_location)? true : false,
				'autofocus' => is_null($default_location)? false : true,
				]); !!}
			</div>
		</div>
	</div>
</div>
<div class="row">
<div class="form-group addresses"></div>
</div>
<div class="row">
	@if(!empty($pos_settings['show_invoice_layout']))
	<div class="col-md-4">
		<div class="form-group">
		{!! Form::select('invoice_layout_id',
					$invoice_layouts, $default_location->invoice_layout_id, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_invoice_layout'), 'id' => 'invoice_layout_id']); !!}
		</div>
	</div>
	@endif
	<input type="hidden" name="pay_term_number" id="pay_term_number" value="{{$walk_in_customer['pay_term_number'] ?? ''}}">
	<input type="hidden" name="pay_term_type" id="pay_term_type" value="{{$walk_in_customer['pay_term_type'] ?? ''}}">

	@if(!empty($commission_agent))
		<div class="col-md-4">
			<div class="form-group">
			{!! Form::select('commission_agent',
						$commission_agent, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.commission_agent')]); !!}
			</div>
		</div>
	@endif
	@if(!empty($pos_settings['enable_transaction_date']))
	    @can('enable_pos_trasaction_date')
		<div class="col-md-4 col-sm-6">
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-calendar"></i>
					</span>
					{!! Form::text('transaction_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required', 'id' => 'transaction_date']); !!}
				</div>
			</div>
		</div>
		@endcan
	@endif
	@if(config('constants.enable_sell_in_diff_currency') == true)
		<div class="col-md-4 col-sm-6">
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fas fa-exchange-alt"></i>
					</span>
					{!! Form::text('exchange_rate', config('constants.currency_exchange_rate'), ['class' => 'form-control input-sm input_number', 'placeholder' => __('lang_v1.currency_exchange_rate'), 'id' => 'exchange_rate']); !!}
				</div>
			</div>
		</div>
	@endif
	@if(!empty($price_groups) && count($price_groups) > 1)
		<div class="col-md-4 col-sm-6">
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fas fa-money-bill-alt"></i>
					</span>
					@php
						reset($price_groups);
						$selected_price_group = !empty($default_price_group_id) && array_key_exists($default_price_group_id, $price_groups) ? $default_price_group_id : null;
					@endphp
					{!! Form::hidden('hidden_price_group', key($price_groups), ['id' => 'hidden_price_group']) !!}
					{!! Form::select('price_group', $price_groups, $selected_price_group, ['class' => 'form-control select2', 'id' => 'price_group']); !!}
					<span class="input-group-addon">
						@show_tooltip(__('lang_v1.price_group_help_text'))
					</span>
				</div>
			</div>
		</div>
	@else
		@php
			reset($price_groups);
		@endphp
		{!! Form::hidden('price_group', key($price_groups), ['id' => 'price_group']) !!}
	@endif
	@if(!empty($default_price_group_id))
		{!! Form::hidden('default_price_group', $default_price_group_id, ['id' => 'default_price_group']) !!}
	@endif

	@if(in_array('types_of_service', $enabled_modules) && !empty($types_of_service))
		<div class="col-md-4 col-sm-6">
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-external-link-square-alt text-primary service_modal_btn"></i>
					</span>
					{!! Form::select('types_of_service_id', $types_of_service, null, ['class' => 'form-control', 'id' => 'types_of_service_id', 'style' => 'width: 100%;', 'placeholder' => __('lang_v1.select_types_of_service')]); !!}

					{!! Form::hidden('types_of_service_price_group', null, ['id' => 'types_of_service_price_group']) !!}

					<span class="input-group-addon">
						@show_tooltip(__('lang_v1.types_of_service_help'))
					</span>
				</div>
				<small><p class="help-block hide" id="price_group_text">@lang('lang_v1.price_group'): <span></span></p></small>
			</div>
		</div>
		<div class="modal fade types_of_service_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
	@endif

	@if(!empty($pos_settings['show_invoice_scheme']))
		<div class="col-md-4 col-sm-6">
			<div class="form-group">
				{!! Form::select('invoice_scheme_id', $invoice_schemes, $default_invoice_schemes->id, ['class' => 'form-control', 'placeholder' => __('lang_v1.select_invoice_scheme')]); !!}
			</div>
		</div>
	@endif
	@if(in_array('subscription', $enabled_modules))
		<div class="col-md-4 col-sm-6">
			<label>
              {!! Form::checkbox('is_recurring', 1, false, ['class' => 'input-icheck', 'id' => 'is_recurring']); !!} @lang('lang_v1.subscribe')?
            </label><button type="button" data-toggle="modal" data-target="#recurringInvoiceModal" class="btn btn-link"><i class="fa fa-external-link-square-alt"></i></button>@show_tooltip(__('lang_v1.recurring_invoice_help'))
		</div>
	@endif
	<!-- Call restaurant module if defined -->
    {{--@if(in_array('tables' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))--}}
    	{{--<div class="clearfix"></div>--}}
    	{{--<span id="restaurant_module_span">--}}
      		{{--<div class="col-md-3"></div>--}}
    	{{--</span>--}}
    {{--@endif--}}
    <div class="col-md-2">
		<button type="button" class="btn btn-primary btn-flat" id="hide-column"> Customer View</button>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-search"></i>
				</span>
				<input type="text" class="search-text" id="internal_search" placeholder="Search..." />
			</div>
		</div>
	</div>
	<div class="col-md-2">
        <div class="form-group">
          <div class="input-group">
            @php
              $default_location = null;
              if(count($search_categories) == 1){
                $default_location = array_key_first($search_categories->toArray());
              }
            @endphp
            <span class="input-group-addon">
             <i class="fas fa-list-ul"></i>
            </span>
            {!! Form::select('category_id[]', [], $default_location , [ 'class' => 'form-control select2', 'id'=>'category_id','multiple', 'data-placeholder'=>"Category"]); !!}
          </div>
        </div>
    </div>
</div>
<!-- include module fields -->
@if(!empty($pos_module_data))
    @foreach($pos_module_data as $key => $value)
        @if(!empty($value['view_path']))
            @includeIf($value['view_path'], ['view_data' => $value['view_data']])
        @endif
    @endforeach
@endif
<div class="row">
	<div class="col-sm-12 pos_product_div">
		<input type="hidden" name="sell_price_tax" id="sell_price_tax" value="{{$business_details->sell_price_tax}}">
		<!-- Keeps count of product rows -->
		<input type="hidden" id="product_row_count"
			value="0">
		@php
			$hide_tax = '';
			if( session()->get('business.enable_inline_tax') == 0){
				$hide_tax = 'hide';
			}
	    	if (isset($_COOKIE["hideColumn"]))
	            $hideColumn = $_COOKIE["hideColumn"];
	        else
	            $hideColumn = 0;
		@endphp
		<input type="hidden" id="hideColumn" value="{{$hideColumn}}" />
		<table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table">
			<thead>
				<tr>
					<th class="tex-center col-md-1">
						Item Image
					</th>
					<!--<th class="text-center col-md-1">-->
					<!--	@lang('Item Note')-->
					<!--</th>-->
					<th class="tex-center col-md-1">
						@lang('Item Name')
					</th>

					<th class="text-center col-md-1">
						@lang('Sales Price')
					</th>

					<th class="text-center col-md-1">
						@lang('sale.qty')
					</th>
					<th class="text-center col-md-1">
						On Hand
					</th>
					<th class="text-center col-md-1">
						Qty in Box
					</th>
					<th class="text-center col-md-1">
						ML
					</th>
					{{--<th class="text-center col-md-1">--}}
						{{--@lang('Price')--}}
					{{--</th>--}}
					<th class="text-center col-md-1">
						@lang('Total')
					</th>
					<th class="text-center col-md-1">
						State Tax
					</th>
					<th class="text-center col-md-1">
						@lang('City Tax')
					</th>
					<th class="text-center cost col-md-1" style="@if($hideColumn==0) display:none; @else display:table-cell; @endif">
					    @lang('Piece Price')
					</th>
					<th class="text-center gross-price col-md-1" style="@if($hideColumn==0) display:none; @else display:table-cell; @endif">
					    <span class="hide"> GP</span> @lang(' %')
					</th>
					<th class="text-center col-md-1">
						@lang('Modified On')
					</th>
					<th class="text-center col-md-1">
						Category
					</th>
					<th class="text-center col-md-1">
						@lang('Sub Category')
					</th>
					<!-- @if(!empty($pos_settings['inline_service_staff']))
						<th class="text-center col-md-2">
							@lang('restaurant.service_staff')
						</th>
					@endif -->
					<!-- <th class="text-center col-md-2 {{$hide_tax}}">
						@lang('sale.price_inc_tax')
					</th> -->

					<th class="text-center"><i class="fas fa-times" aria-hidden="true"></i></th>
				</tr>
			</thead>
			<tbody>
				@foreach($sell_details as $sell_line)

					@include('sale_pos.product_row',
						['product' => $sell_line,
						'row_count' => $loop->index,
						'tax_dropdown' => $taxes,
						'tax'=> [],
						'sub_units' => !empty($sell_line->unit_details) ? $sell_line->unit_details : [],
						'action' => 'create',
						'cost' => $sell_line->cost,
						'edit_discount' => auth()->user()->can('edit_product_discount_from_pos_screen'),
                    	'edit_price' => auth()->user()->can('edit_product_price_from_pos_screen')
					])
				@endforeach
			</tbody>
		</table>
	</div>

	<div class="internal-search">
		<p>Internal search Amounts:</p>
		<ul>
			<li> Quantity:<span class="internal-total-qty"></span></li>
			<li> Sub Total: <span class="internal-sub-total"></span></li>
			<li> Total Tax:<span class="internal-total-tax"></span></li>
			<li> Total Amount: <span class="internal-total-amt"></span></li>
		</ul>
	</div>
</div>

<style>
	div.dataTables_filter input {
		 border: 1px solid black;
		 margin-left: 0;
		 margin: 0 0  0 -50px!important;
	}
	tr td div a:focus {
	color: red;
	outline: none;
	}
	.internal-search ul li{
		display: inline-block;
		padding-left: 10px;
		font-weight: 700;
	}
	.internal-search p{
		font-weight: 600;
		padding-left: 20px;
	}
</style>