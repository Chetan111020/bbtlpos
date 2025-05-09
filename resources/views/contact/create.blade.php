<div class="modal-dialog modal-lg" role="document">
	<div class="modal-content">
		@php
		$form_id = 'contact_add_form';
		if(isset($quick_add)){
		$form_id = 'quick_add_contact';
		}
		if(isset($store_action)) {
		$url = $store_action;
		$type = 'lead';
		$customer_groups = [];
		} else {
		$url = action('ContactController@store');
		$type = isset($selected_type) ? $selected_type : '';
		$sources = [];
		$life_stages = [];
		//$users = [];
		}
		@endphp
		{!! Form::open(['url' => $url, 'method' => 'post', "enctype" => "multipart/form-data", 'id' => $form_id]) !!}
		<div class="modal-header">
				<button type="button" class="btn btn-default" data-dismiss="modal" style="float: right; font-size: 21px; font-weight: 700; line-height: 1; color: #000; text-shadow: 0 1px 0 #fff; opacity: .2;"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">@lang('contact.add_contact')</h4>
		</div>
		<div id="overlay">
			<div class="cv-spinner">
			  <span class="spinner"></span>
			</div>
		  </div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-4 supplier_fields">
					<div class="contact_type_div">
						<div class="form-group">
							{!! Form::label('type', __('contact.contact_type') . ':*' ) !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-user"></i>
								</span>
								{!! Form::select('type', $types, $type , ['class' => 'form-control supplier_status', 'id' => 'contact_type','placeholder' => __('messages.please_select'), 'required']); !!}
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-4 supplier_fields">
					<div class="form-group">
						<!-- {!! Form::label('contact_id', __('lang_v1.contact_id') . ':') !!} -->
						<label for="supplier_contact_id">Contact ID</label>
						<div class="input-group">
							<span class="input-group-addon">
							<i class="fa fa-id-badge"></i>
							</span>
							{!! Form::text('supplier_contact_id', null, ['class' => 'form-control supplier_status input-upper-case', 'id' => 'supplier_contact_id','placeholder' => __('Contact ID')]); !!}
						</div>
					</div>
				</div>
				<div class="col-md-4 supplier_fields">
					<div class="form-group">
							<!-- {!! Form::label('supplier_business_name', __('lang_v1.business_name') . ':') !!} -->
							<label for="supplier_business_name1">Business Name</label>
						<div class="input-group">
							<span class="input-group-addon">
							<i class="fa fa-briefcase"></i>
							</span>
							{!! Form::text('supplier_business_name1', null, ['class' => 'form-control supplier_status input-upper-case', 'id' => 'supplier_business_name','placeholder' => __('business.business_name')]); !!}
						</div>
					</div>
				</div>
				<div class="col-md-3 supplier_fields">
					<div class="form-group">
						{!! Form::label('supplier_prefix', __( 'business.prefix' ) . ':') !!}
						{!! Form::text('supplier_prefix', null, ['class' => 'form-control supplier_status ', 'placeholder' => __( 'Mr / Mrs / Miss' ) ]); !!}
					</div>
				</div>
				<div class="col-md-3 supplier_fields">
					<div class="form-group">
							<!-- {!! Form::label('prefix', __('lang_v1.prefix') . ':') !!} -->
							<label for="supplier_first_name">First Name</label>
						<div class="input-control">
						{!! Form::text('supplier_first_name', null, ['class' => 'form-control supplier_status input-upper-case', 'id' => 'first_name','placeholder' => __('First Name')]); !!}
							<!-- {!! Form::text('first_name', null, ['class' => 'form-control', 'id' => 'first_name','placeholder' => __('First Name')]); !!} -->
						</div>
					</div>
				</div>
				<div class="col-md-3 supplier_fields">
					<div class="form-group">
							<!-- {!! Form::label('prefix', __('lang_v1.prefix') . ':') !!} -->
							<label for="supplier_middle_name">Middle Name</label>
						<div class="input-control">
							{!! Form::text('supplier_middle_name', null, ['class' => 'form-control supplier_status input-upper-case', 'id' => 'middle_name','placeholder' => __('Middle Name')]); !!}
						</div>
					</div>
				</div>
				<div class="col-md-3 supplier_fields">
					<div class="form-group">
							<!-- {!! Form::label('prefix', __('lang_v1.prefix') . ':') !!} -->
							<label for="supplier_last_name">Last Name</label>
						<div class="input-control">
							{!! Form::text('supplier_last_name', null, ['class' => 'form-control supplier_status input-upper-case', 'id' => 'last_name','placeholder' => __('Last Name')]); !!}
						</div>
					</div>
				</div>
				<div class="col-md-3 supplier_fields">
					<div class="form-group">
						{!! Form::label('supplier_mobile', __('contact.mobile') . ':*') !!}
						<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-mobile-alt"></i>
						</span>
						{!! Form::number('supplier_mobile', null, ['class' => 'form-control supplier_status', 'pattern'=>"[1-9]{1}[0-9]{9}"  , "title"=>"Value must be 10 digits",
						'placeholder' => __('contact.mobile'), 'required']); !!}
						</div>
					</div>
				</div>
				<div class="col-md-3 supplier_fields">
					<div class="form-group">
						{!! Form::label('supplier_alternate_number', __('contact.alternate_contact_number') . ':') !!}
						<div class="input-group">
							<span class="input-group-addon">
							<i class="fas fa-phone-alt"></i>
							</span>
							{!! Form::text('supplier_alternate_number', null, ['class' => 'form-control supplier_status', 'tabindex' => '16', 'pattern'=>"[1-9]{1}[0-9]{9}" , "title"=>"Value must be 10 digits" , 'placeholder' => __('contact.alternate_contact_number')]); !!}
						</div>
					</div>
				</div>
				<div class="col-md-3 supplier_fields">
					<div class="form-group">
						<!-- {!! Form::label('landline', __('contact.business_number') . ':') !!} -->
						<label for="supplier_landline">Business Landline</label>
						<div class="input-group">
							<span class="input-group-addon">
							<i class="fas fa-phone-alt"></i>
							</span>
							{!! Form::text('supplier_landline', null, ['class' => 'form-control supplier_status', 'id' => "landline" , 'tabindex' => '12', 'placeholder' => __('contact.landline')]); !!}
						</div>
					</div>
				</div>
				<div class="col-md-3 supplier_fields">
					<div class="form-group">
						{!! Form::label('supplier_email', __('business.email') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
							<i class="fa fa-envelope"></i>
							</span>
							{!! Form::email('supplier_email', null, ['class' => 'form-control supplier_status input-upper-case', 'id' => "supplier_email" ,  'placeholder' => __('business.email'),'required']); !!}
						</div>
						<span style="color:red" class="already-exists-email"></span>
					</div>
				</div>

				<div class="col-md-12 supplier_fields"><hr></div>

				<div class="col-md-4 supplier_fields">
					<div class="form-group">
					<label for="supplier_licnumber">Lic Number</label>
						<div class="input-group">
							<span class="input-group-addon">
							<i class="fa fa-info"></i>
							</span>
							{!! Form::text('supplier_licnumber', null, ['class' => 'form-control supplier_status input-upper-case','id'=>'licnumber', 'tabindex' => '11', 'placeholder' => __('Lic Number')]); !!}
						</div>
					</div>
				</div>
				<div class="col-md-4 supplier_fields">
					<div class="form-group">
					<label for="supplier_openbalance">Opening Balance</label>
						<div class="input-group">
							<span class="input-group-addon">
							<i class="fa fa-info"></i>
							</span>
							{!! Form::text('supplier_openbalance', 0, ['class' => 'form-control supplier_status',  'id'=>'openbalance', 'tabindex' => '11', 'placeholder' => __('')]); !!}
						</div>
					</div>
				</div>
				<div class="col-md-4 supplier_fields">
					<div class="form-group">
						<div class="multi-input">
						<div style="display: flex;">
							<label for="supplier_pay_term_number">Pay term:</label>
							<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Payments to be paid for purchases/sales within the given time period.<br/><small class='text-muted'>All upcoming or due payments will be displayed in dashboard - Payment Due section</small>" data-html="true" data-trigger="hover" data-original-title="" title=""></i>
						</div>

						{!! Form::number('supplier_pay_term_number', null, ['class' => 'form-control supplier_status width-40 pull-left', 'id'=>'pay_term_number', 'tabindex' => '11','placeholder' => __('Pay term')]); !!}

						{!! Form::select('supplier_pay_term_type', ['months' => __('lang_v1.months'), 'days' => __('lang_v1.days')], '', ['class' => 'form-control width-60 pull-left supplier_status','placeholder' => __('messages.please_select')]); !!}

						<!-- <select class="form-control width-60 pull-left supplier_status"  name="supplier_pay_term_type">
							<option selected="selected" value="">Please Select</option>
							<option value="months">Months</option>
							<option value="days">Days</option>
						</select> -->
						</div>
					</div>
				</div>

				<div class="col-md-12 supplier_fields"><hr></div>

				<div class="col-md-6 supplier_fields">
					<div class="form-group">
						{!! Form::label('supplier_address_line_1', __('lang_v1.address_line_1') . ':') !!}
						{!! Form::text('supplier_address_line_1', null, ['class' => 'form-control supplier_status input-upper-case', 'tabindex' => '1', 'placeholder' => __('lang_v1.address_line_1'), 'rows' => 3]); !!}
					</div>
				</div>
				<div class="col-md-6 supplier_fields">
					<div class="form-group">
						{!! Form::label('supplier_address_line_2', __('lang_v1.address_line_2') . ':') !!}
						{!! Form::text('supplier_address_line_2', null, ['class' => 'form-control supplier_status input-upper-case', 'tabindex' => '2', 'placeholder' => __('lang_v1.address_line_2'), 'rows' => 3]); !!}
					</div>
				</div>
				<div class="col-md-3 supplier_fields">
					<div class="form-group">
						{!! Form::label('supplier_city', __('business.city') . ':') !!}
						<div class="input-group">
							<span class="input-group-addon">
							<i class="fa fa-map-marker"></i>
							</span>
							{!! Form::text('supplier_city', null, ['class' => 'form-control supplier_status input-upper-case', 'id' => 'city','placeholder' => __('City')]); !!}
						</div>
					</div>
				</div>
				<div class="col-md-3 supplier_fields">
					<div class="form-group">
					{!! Form::label('supplier_state', __('business.state') . ':') !!}
						<div class="input-group">
							<span class="input-group-addon">
							<i class="fa fa-map-marker"></i>
							</span>
							{!! Form::text('supplier_state', null, ['class' => 'form-control supplier_status input-upper-case', 'id' => 'state','placeholder' => __('State')]); !!}
						</div>
					</div>
				</div>
				<div class="col-md-3 supplier_fields">
					<div class="form-group">
						{!! Form::label('supplier_country', __('business.country') . ':') !!}
						<div class="input-group">
							<span class="input-group-addon">
							<i class="fa fa-globe"></i>
							</span>
							{!! Form::text('supplier_country', null, ['class' => 'form-control supplier_status input-upper-case', 'id' => 'country','placeholder' => __('Country')]); !!}
						</div>
					</div>
				</div>
				<div class="col-md-3 supplier_fields">
					<div class="form-group">
						{!! Form::label('supplier_zip_code', __('business.zip_code') . ':') !!}
						<div class="input-group">
							<span class="input-group-addon">
							<i class="fa fa-map-marker"></i>
							</span>
							{!! Form::text('supplier_zip_code', null, ['class' => 'form-control supplier_status', 'id' => 'zip_code','placeholder' => __('Zip/Postal Code')]); !!}
						</div>
					</div>
				</div>
				<div class="col-md-12 supplier_fields shipping_addr_div"><hr></div>

				<div class="col-md-8 col-md-offset-2 supplier_fields shipping_addr_div">
					<div class="form-group">
						<label for="supplier_shipping_address">Billing Address</label>
						{!! Form::text('supplier_shipping_address', null, ['class' => 'form-control supplier_status input-upper-case', 'id' => 'shipping_address','placeholder' => __('Search address')]); !!}
						<!-- {!! Form::label('shipping_address', __('business.billing_address') . ':') !!} -->
						<div class="input-group">

						</div>
					</div>
				</div>

<!-- customer -->
				<div class="col-md-6 custom-column customer_fields">
					<div class="col-md-12">
						<h3 class="f-underline">Business Info</h3>
					</div>
					<div class="col-md-6 contact_type_div">
						<div class="form-group">
							{!! Form::label('type', __('contact.contact_type') . ':*' ) !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-user"></i>
								</span>
								{!! Form::select('type', $types, $type , ['class' => 'form-control customer_status ', 'id' => 'contact_type','placeholder' => __('messages.please_select'), 'required']); !!}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('contact_id', __('lang_v1.contact_id') . ':') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-id-badge"></i>
								</span>
								{!! Form::text('contact_id', null, ['class' => 'form-control input-upper-case','disabled'=>'disabled','placeholder' => __('Account ID Auto Generated')]); !!}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('first_name', __( 'business.first_name' ) . ':*') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-briefcase"></i>
								</span>
								{{--                                {!! Form::text('first_name', null, ['class' => 'form-control', 'pattern' => '[A-Za-z]{1,}', 'title' => 'Only Letters Accepted' , 'required', 'placeholder' => __( 'business.first_name' ) ]); !!}--}}
								{!! Form::text('first_name', null, ['class' => 'form-control customer_status input-upper-case', 'id' => 'dbaname', 'required', 'placeholder' => __( 'business.first_name' ) ]); !!}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('tax', __('lang_v1.tax_id') . ':*') !!}
							{!! Form::text('tax', null, ['class' => 'form-control customer_status input-upper-case','required', 'placeholder' => __('lang_v1.tax_id')]); !!}
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('supplier_business_name', __('business.business_name') . ':') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-briefcase"></i>
								</span>
								{!! Form::text('supplier_business_name', null, ['class' => 'form-control input-upper-case', 'id' => 'customername','placeholder' => __('business.business_name')]); !!}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('tobacco_license', __('business.tobacco_license') . ':') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-globe"></i>
								</span>
								{!! Form::text('tobacco_license_no', null, ['class' => 'form-control',
								'placeholder' => __('business.tobacco_license')]); !!}
							</div>
						</div>
					</div>
					<div class="col-md-6 customer_fields">
						<div class="form-group">
							{!! Form::label('customer_group_id', __('Selling Price Group') . ':') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-users"></i>
								</span>
								{!! Form::select('customer_group_id', $customer_groups, 68, ['class' => 'form-control']); !!}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('Expiry Date', __('lang_v1.expiry_date') . ':', ['required']) !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-calendar"></i>
								</span>
								{!! Form::text('expiry_date', null, ['class' => 'form-control', 'id' => 'datepicker', 'placeholder' => __('lang_v1.expiry_date')]); !!}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('file', __('lang_v1.file') . ':') !!}
							<div class="input-group">
								{!! Form::file('docfile', null, ['class' => 'form-control','id' => 'file', 'placeholder' => __('lang_v1.file'), 'rows' => 3]); !!}
								<p>Drag your file here</p>
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group nyc">
							<label>
								<input type="checkbox" value="1" name="cigar_customer" class="nyc">
								<p class="chechkbox-p"> Cigar Customer</p>
							</label>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group nyc">
							<label>
								<input type="checkbox"  id="sync" name="sync" class="nyc" >
								<p class="chechkbox-p">Disable Woocommerce Sync</p>
							</label>
						</div>
					</div>
					{{-- account info --}}
					<div class="col-md-12 custom-column customer_fields">
						<div class="col-md-12">
							<h3 class="l-underline">Account Info</h3>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<div class="multi-input">
									{!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show
									{{--_tooltip(__('tooltip.pay_term'))--}}
									<br/>
									{{-- {!! Form::number('pay_term_number', null, ['class' => 'form-control width-40 pull-left', 'placeholder' => __('contact.pay_term')]); !!} --}}
									{!! Form::select('pay_term_number', ['7' => '7 days','14' => '14 days','30' => '30 days','0' => 'Cash' ], '', ['class' => 'form-control','placeholder' => __('messages.please_select')]); !!}
									{!! Form::hidden('pay_term_type', 'days' , ['placeholder' => 'Blank for no limit']); !!}
								</div>
							</div>
						</div>
						<div class="col-md-6 customer_fields">
							@if (auth()->user()->can('customer.sales_rep'))
                            <div class="form-group">
								{!! Form::label('file', __('Sales Rep') . ':*') !!}
								<div class="input-group">
									<span class="input-group-addon">
									<i class="fas fa-money-bill-alt"></i>
									</span>
									<select name="sales_rep" id="" class="form-control" required="required">
										<option value="">None</option>
										@if(isset($users))
										@foreach($users as $user)
										<option value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
										@endforeach
										@endif
									</select>
								</div>
							</div>
                            @endif
						</div>
						<div class="col-md-6 customer_fields">
                            @if (auth()->user()->can('customer.sales_rep'))
							<div class="form-group">
								{!! Form::label('file', __('Account Rep') . ':*') !!}
								<div class="input-group">
									<span class="input-group-addon">
									<i class="fas fa-money-bill-alt"></i>
									</span>
									<select name="account_rep" class="form-control" required="required">
										<option value="">None</option>
										@if(isset($users))
										@foreach($users as $user)
										<option value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
										@endforeach
										@endif
									</select>
								</div>
							</div>
                            @endif
						</div>
						<div class="col-md-6 customer_fields">
							<div class="form-group">
								{!! Form::label('credit_limit', __('lang_v1.credit_limit') . ':') !!}
								<div class="input-group">
									<span class="input-group-addon">
									<i class="fas fa-money-bill-alt"></i>
									</span>
									{!! Form::text('credit_limit', null, ['class' => 'form-control input_number input-upper-case', 'placeholder' => 'Blank for no limit']); !!}
								</div>
							</div>
						</div>

						<div class="col-md-6 customer_fields">
							<div class="form-group">
								<div class="input-group">
									{!! Form::label('payment_status', __('Payment Status') . ':') !!}
									<br/>
									{!! Form::select('payment_status', ['ask_for_payment_before_ship' => 'Ask For Payment Before Shipping','ok_to_ship' => 'Okay to Deliver/Ship (Payment Confirmed)','ask_in_the_office' => 'Ask In The Office'], '', ['class' => 'form-control']); !!}
									<!--{!! Form::hidden('pay_term_type', 'days' , ['placeholder' => 'Blank for no limit']); !!}-->
								</div>
							</div>
						</div>
					</div>
				</div>
				{{-- address info --}}
				<div class="col-md-6 custom-column customer_fields">
					<div class="col-md-12">
						<h3 class="f-underline">Contact Info</h3>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('address_line_1', __('lang_v1.address_line_1') . ':*') !!}
							{!! Form::text('address_line_1', null, ['class' => 'form-control input-upper-case', 'tabindex' => '1', 'required', 'placeholder' => __('lang_v1.address_line_1'), 'rows' => 3]); !!}
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('Contact Person 1', __('lang_v1.contact_person_1') . ':') !!}
							{!! Form::text('contact_person_1', null, ['class' => 'form-control input-upper-case', 'tabindex' => '10', 'placeholder' => __('lang_v1.contact_person_1'), 'rows' => 3]); !!}
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('address_line_2', __('lang_v1.address_line_2') . ':') !!}
							{!! Form::text('address_line_2', null, ['class' => 'form-control input-upper-case', 'tabindex' => '2', 'placeholder' => __('lang_v1.address_line_2'), 'rows' => 3]); !!}
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('email', __('business.email') . ':*') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-envelope"></i>
								</span>
								{!! Form::email('email', null, ['class' => 'form-control input-upper-case','required','id'=>'customer_email', 'tabindex' => '11', 'placeholder' => __('business.email')]); !!}
							</div>
							<span style="color:red" class="already-exists-emails"></span>
						</div>
					</div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('notify_email', 'Notification Email:') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-envelope"></i>
                                </span>
                                {!! Form::email('notify_email', null, ['class' => 'form-control input-upper-case', 'id'=> 'notify_email','placeholder' => __('business.email')]); !!}
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('city', __('business.city') . ':') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-map-marker"></i>
								</span>
								{!! Form::text('city', null, ['class' => 'form-control input-upper-case', 'tabindex' => '3', 'placeholder' => __('business.city')]); !!}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('landline', __('contact.landline') . ':') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-phone"></i>
								</span>
								{!! Form::text('landline', null, ['class' => 'form-control', 'id' => "landline" , 'tabindex' => '12', 'placeholder' => __('contact.landline')]); !!}
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="col-md-4">
						<div class="form-group">
							{!! Form::label('state', __('business.state') . ':') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-map-marker"></i>
								</span>
								{{-- {!! Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('business.state')]); !!}--}}
								<select name="state" tabindex = '4' id="state" class="form-control select2">
									<option value="">-select-</option>
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
					</div>
					<div class="col-md-2">
						<div class="form-group nyc">
							<label>
								<input type="checkbox" tabindex = '5' value="1" name="is_nyc" class="nyc">
								<p class="chechkbox-p"> Is NYC</p>
							</label>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('mobile', __('contact.mobile') . ':*') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-mobile"></i>
								</span>
								{!! Form::text('mobile', null, ['class' => 'form-control customer_status', 'tabindex' => '13','pattern' => '[0-9]{10}',  'title' => 'Enter Valid Mobile Number' , 'required', 'placeholder' => __('contact.mobile')]); !!}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('zip_code', __('business.zip_code') . ':') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-map-marker"></i>
								</span>
								{!! Form::text('zip_code', null, ['class' => 'form-control', 'pattern' => '[0-9]{5,}' , 'tabindex' => '6', 'title' => 'Zip Code contains 5 or more numbers',
								'placeholder' => __('business.zip_code_placeholder')]); !!}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('whatsapp', __('contact.whatsapp') . ':*') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-mobile"></i>
								</span>
								{!! Form::text('whatsapp', null, ['class' => 'form-control','pattern' => '[0-9]{10}', 'tabindex' => '14', 'title' => 'Enter Valid Mobile Number', 'required' , 'placeholder' => __('contact.whatsapp')]); !!}
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							{!! Form::label('Referral Code', __('business.ref_code') . ':') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-map-marker"></i>
								</span>
								{!! Form::text('referal_code', null, ['class' => 'form-control', 'tabindex' => '7', 'id'=>'referralCode',
								'placeholder' => __('business.ref_code')]); !!}
							</div>
							<div>
								<ul id="referralSugetion"></ul>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('Contact Person 2', __('lang_v1.contact_person_2') . ':') !!}
							{!! Form::text('contact_person_2', null, ['class' => 'form-control input-upper-case', 'tabindex' => '15', 'placeholder' => __('lang_v1.contact_person_2'), 'rows' => 3]); !!}
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('country', __('business.country') . ':') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-globe"></i>
								</span>
								{!! Form::text('country', 'USA', ['class' => 'form-control', 'readonly' => 'readonly', 'placeholder' => __('business.country')]); !!}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label('alternate_number', __('contact.alternate_contact_number') . ':') !!}
							<div class="input-group">
								<span class="input-group-addon">
								<i class="fa fa-phone"></i>
								</span>
								{!! Form::text('alternate_number', null, ['class' => 'form-control', 'tabindex' => '16', 'placeholder' => __('contact.alternate_contact_number')]); !!}
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							{!! Form::label('note', __('lang_v1.note') . ':') !!}
							{!! Form::textarea('note', !empty($duplicate_product->note) ? $duplicate_product->note : null, ['class' => 'form-control' , 'tabindex' => '17', 'id' => 'note']); !!}
						</div>
					</div>
				</div>
				{{--
				<div class="col-md-6">
					<div class="form-group">
						{!! Form::label('Fax', __('lang_v1.fax') . ':') !!}
						{!! Form::text('fax', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.fax')]); !!}
					</div>
				</div>
				--}}
			</div>
			{{-- card row end --}}
			<!-- <div class="col-md-12 shipping_addr_div"><hr></div>
				<div class="col-md-8 col-md-offset-2 shipping_addr_div" >
				    <strong>{{__('lang_v1.shipping_address')}}</strong><br>
				    {!! Form::text('shipping_address', null, ['class' => 'form-control',
				          'placeholder' => __('lang_v1.search_address'), 'id' => 'shipping_address']); !!}
				          <div id="map"></div>
				        </div>
				{!! Form::hidden('position', null, ['id' => 'position']); !!}

				          </div>
				          </div>
				        </div> -->
			<div class="modal-footer">

			<button type="submit"  class="btn btn-primary submit">@lang( 'messages.save' )</button>
				<!-- <input type="submit" value="Save"  class="btn btn-primary"> -->
				<button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
			</div>
			{!! Form::close() !!}
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
	<style>
	.modal-body {
	padding: 0px 15px;
	}
	.custom-column {
	background-color: rgb(230 230 230 / 33%);
	border: 10px solid white;
	padding: 0px 10px 10px 10px;
	}
	.f-underline, .l-underline {
	padding-bottom: 3px;
	}
	.f-underline::after {
	position: absolute;
	content: "";
	height: 2px;
	background-color: currentColor;
	width: 45%;
	margin-left: 12px;
	top: 83%;
	left: 0%;
	}
	.l-underline::after {
	position: absolute;
	content: "";
	height: 2px;
	background-color: currentColor;
	width: 20%;
	margin-left: 12px;
	top: 83%;
	left: 0%;
	}
	.nyc {
	margin-top: 25px;
	margin-bottom: 10px;
	}
	#note {
	height: 100px;
	}
	input[type='checkbox'] {
	width: 20px;
	height: 20px;
	border-radius: 2px;
	}
	.chechkbox-p {
	margin: -24px;
	margin-left: 30px;
	}
	.modal-lg {
	width: 98%;
	}
	#list {
	display: block;
	transition-duration: 0.5s;
	padding: 5px;
	border-bottom: 1px solid #ededed;
	background-color: white;
	}
	#list:hover {
	cursor: pointer;
	background-color: #ededed;
	}
	#list ul {
	visibility: hidden;
	opacity: 0;
	position: absolute;
	transition: all 0.5s ease;
	margin-top: 1rem;
	left: 0;
	display: none;
	}
	#list:hover > ul,
	#list ul:hover {
	visibility: visible;
	opacity: 1;
	display: block;
	}
	#list {
	clear: both;
	width: 100%;
	}
	#overlay{
		position: fixed;
		top: 0;
		z-index: 100;
		width: 100%;
		height:100%;
		display: none;
		background: rgba(0,0,0,0.6);
	  }
	  .cv-spinner {
		height: 100%;
		display: flex;
		justify-content: center;
		align-items: center;
	  }
	  .spinner {
		width: 40px;
		height: 40px;
		border: 4px #ddd solid;
		border-top: 4px #2e93e6 solid;
		border-radius: 50%;
		animation: sp-anime 0.8s infinite linear;
	  }
	  @keyframes sp-anime {
		100% {
		  transform: rotate(360deg);
		}
	  }
	  .is-hide{
		display:none;
	  }
	  .input-upper-case{
		text-transform: uppercase;
	}

	</style>
<!-- <script>
	function formfunction(){
	    var customername=document.getElementById('customername').value;
	    alert (customer);

	        if((customername.search(/[A-Z]/)==-1) || (customername.search(/[a-z]/)==-1))
	        {
	            alert(" Customer Name only accepts letters");
	            return false;
	        }
	        var mobile=document.getElementById('customername').value;
	        if((mobile.search(/[0-9]/)==-1))
	        {
	            alert("please enter valid number ");
	            return false;
	        }
	}
	</script> -->
<script>
	$(document).ready(function () {
	    $('#referralCode').on('keyup', function () {
	        let _keys = $(this).val();
	        if (_keys.includes('@')) {
	            let _textArray = _keys.split('@');
	            let _newText = _textArray[0];
	            let _searchKey = _textArray[1];
	            $.ajax({
	                url: '{{route('get-referral-company')}}',
	                type: 'GET',
	                data: {keys: _searchKey},
	                success: function (response) {
	                    $('#referralSugetion').empty();
	                    let datas = response;
	                    $(datas).each(function (index, data) {
	                        $('#referralSugetion').append('<li id="list" onclick="listText(\'' + _newText + ' @' + data.supplier_business_name + '\')">' + data.supplier_business_name + '</li>');
	                    });
	                }
	            });
	        }
	    });
	});


	$("#supplier_email").on('change',function(){
		var email = $(this).val();
		var type = $("#contact_type").val();
        $.ajax({
            method: 'POST',
            url:"{{ url('contacts/checkemail') }}",
            dataType: 'json',
            data: {'email':email,'type':type},
            success: function(success) {

                if(success.success == true){
                    $(".submit").prop("disabled", true);
                    $(".already-exists-email").text(success.message);
                }else{
                    $(".submit").prop("disabled", false);
                    $(".already-exists-email").text(success.message);
                }
             },
        });
	});

	$("#customer_email").on('change',function(){
		var email = $(this).val();
		var type = $("#contact_type").val();
        $.ajax({
            method: 'POST',
            url:"{{ url('contacts/checkemail') }}",
            dataType: 'json',
            data: {'email':email,'type':type},
            success: function(success) {

                if(success.success == true){
                    $(".submit").prop("disabled", true);
                    $(".already-exists-emails").text(success.message);
                }else{
                    $(".submit").prop("disabled", false);
                    $(".already-exists-emails").text(success.message);
                }
             },
        });
	});

	function listText(text) {
	    $('#referralCode').val(text);
	    $('#referralSugetion').empty();
	}


	$("#datepicker").datepicker({
	    dateFormat: 'dd/mm/yy',
	    changeMonth: true,
	    changeYear: true
	});

	$(document).on("keyup", "#dbaname", function(){
	    var dbaname = $(this).val();
	    $("#customername").val(dbaname);
	});

	$(document).on("keyup", "#mobile", function(){
	    var mobile = $(this).val();
	    $("#whatsapp").val(mobile);
	});

	//     function phoneMask() {
	//         var num = $(this).val().replace(/\D/g,'');
	//         $(this).val(
	//             '(' + num.substring(1,4)
	//             +(num.length>4?')':'')
	//             +(num.length>4?' '+num.substring(3,7):'')
	//         );
	//     }
	// $('#landline').keyup(phoneMask);
</script>