<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        @php
        if(isset($update_action)) {
        $url = $update_action;
        $customer_groups = [];
        $opening_balance = 0;
        $lead_users = $contact->leadUsers->pluck('id');
        } else {
        $url = action('ContactController@update', [$contact->id]);
        $sources = [];
        $life_stages = [];
        //$users = [];
        $lead_users = [];
        }
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'PUT', "enctype" => "multipart/form-data",'id' => 'contact_edit_form1']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('contact.edit_contact')</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6 custom-column">
                    <div class="col-md-12">
                        <h3 class="f-underline">Business Info</h3>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('type', __('contact.contact_type') . ':*' ) !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                                </span>
                                {!! Form::select('type', $types, $contact->type, ['class' => 'form-control', 'id' => 'contact_type','placeholder' => __('messages.please_select'), 'required']); !!}
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
                                <input type="hidden" id="hidden_id" value="{{$contact->id}}">
                                {!! Form::text('contact_id', $contact->contact_id, ['class' => 'form-control','placeholder' => __('lang_v1.contact_id')]); !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('first_name', __( 'business.first_name' ) . ':*') !!}
                            {!! Form::text('first_name', $contact->first_name, ['class' => 'form-control', 'required', 'placeholder' => __( 'business.first_name' ) ]); !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('tax', __('lang_v1.tax_id') . ':') !!}
                            {!! Form::text('tax',$contact->tax, ['class' => 'form-control', 'placeholder' => __('lang_v1.tax_id')]); !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('supplier_business_name', __('business.business_name') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-briefcase"></i>
                                </span>
                                {!! Form::text('supplier_business_name', 
                                $contact->supplier_business_name, ['class' => 'form-control', 'placeholder' => __('business.business_name')]); !!}
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
                                {!! Form::text('tobacco_license_no', $contact->tobacco_license_no, ['class' => 'form-control', 
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
                                {!! Form::select('customer_group_id', $customer_groups, $contact->customer_group_id, ['class' => 'form-control']); !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('Expiry Date', __('lang_v1.expiry_date') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('expiry_date', $contact->expiry_date, ['class' => 'form-control dob-date-picker','placeholder' => __('lang_v1.expiry_date'), 'readonly']); !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('file', __('lang_v1.file') . ':') !!}
                            {!! Form::file('docfile', null, ['class' => 'form-control','id' => 'file', 'placeholder' => __('lang_v1.file'), 'rows' => 3]); !!}
                            <p>Drag your file here</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group nyc1">
                            <label>
                                <input type="checkbox" value="1" name="is_nyc" class="nyc">
                                <p class="chechkbox-p"> Cigar Customer</p>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group nyc1">
                            <label>
                                <input type="checkbox" value="1" name="sync" class="nyc">
                                <p class="chechkbox-p"> Do not sync with website</p>
                            </label>
                        </div>
                    </div>
                    {{-- account info --}}
                    <div class="col-md-12 custom-column">
                        <div class="col-md-12">
                            <h3 class="l-underline">Account Info</h3>
                        </div>
                        <div class="col-md-6 pay_term">
                            <div class="form-group">
                                <div class="multi-input">
                                    {!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show_tooltip(__('tooltip.pay_term'))
                                    <br/>
                                    {!! Form::select('pay_term_number', ['7' => '7 days','14' => '14 days','30' => '30 days','0' => 'COD' ], '', ['class' => 'form-control','placeholder' => __('messages.please_select')]); !!}
                                    {!! Form::hidden('pay_term_type', 'days' , ['placeholder' => 'Blank for no limit']); !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 customer_fields">
                            <div class="form-group">
                                <label>Sales Rep</label>        
                                <div class="input-group">
                                    <span class="input-group-addon">
                                    <i class="fas fa-money-bill-alt"></i>
                                    </span>
                                    <select name="sales_rep" id="" class="form-control">
                                        <option value="">Select Sales Rep</option>
                                        @foreach($users as $user)
                                        <option {{ ($contact->sales_rep == $user->id)? "selected" : ''}} value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 customer_fields">
                            <div class="form-group">
                                <label>Account Rep</label>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                    <i class="fas fa-money-bill-alt"></i>
                                    </span>
                                    <select name="account_rep" id="" class="form-control">
                                        <option value="">Select Account Rep</option>
                                        @foreach($users as $user)
                                        <option {{ ($contact->account_rep == $user->id)? "selected" : ''}} value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 customer_fields">
                            <div class="form-group">
                                {!! Form::label('credit_limit', __('lang_v1.credit_limit') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                    <i class="fas fa-money-bill-alt"></i>
                                    </span>
                                    {!! Form::text('credit_limit', $contact->credit_limit != null ? @num_format($contact->credit_limit) : null, ['class' => 'form-control input_number','placeholder' => 'Blank for no limit' ]); !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- address info --}}
                <div class="col-md-6 custom-column">
                    <div class="col-md-12">
                        <h3 class="f-underline">Contact Info</h3>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('address_line_1', __('lang_v1.address_line_1') . ':') !!}
                            {!! Form::text('address_line_1', $contact->address_line_1, ['class' => 'form-control', 'placeholder' => __('lang_v1.address_line_1'), 'rows' => 3]); !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('Contact Person 1', __('lang_v1.contact_person_1') . ':') !!}
                            {!! Form::text('contact_person_1', $contact->contact_person_1, ['class' => 'form-control', 'placeholder' => __('lang_v1.contact_person_1'), 'rows' => 3]); !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('address_line_2', __('lang_v1.address_line_2') . ':') !!}
                            {!! Form::text('address_line_2', $contact->address_line_2, ['class' => 'form-control', 'required', 'placeholder' => __('lang_v1.address_line_2'), 'rows' => 3]); !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('email', __('business.email') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-envelope"></i>
                                </span>
                                {!! Form::email('email', $contact->email, ['class' => 'form-control','placeholder' => __('business.email')]); !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('city', __('business.city') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                                </span>
                                {!! Form::text('city', $contact->city, ['class' => 'form-control', 'placeholder' => __('business.city')]); !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group nyc1">
                            <label>
                                <input type="checkbox" value="1" name="is_nyc" class="nyc">
                                <p class="chechkbox-p"> Is NYC</p>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('landline', __('contact.landline') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-phone"></i>
                                </span>
                                {!! Form::text('landline', $contact->landline, ['class' => 'form-control', 'placeholder' => __('contact.landline')]); !!}
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('state', __('business.state') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                                </span>
                                {!! Form::text('state', $contact->state, ['class' => 'form-control', 'placeholder' => __('business.state')]); !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-mobile"></i>
                                </span>
                                {!! Form::text('mobile', $contact->mobile, ['class' => 'form-control', 'required', 'placeholder' => __('contact.mobile')]); !!}
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
                                {!! Form::text('zip_code', $contact->zip_code, ['class' => 'form-control', 
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
                                {!! Form::text('whatsapp', $contact->whatsapp, ['class' => 'form-control','pattern' => '[0-9]{10}', 'title' => 'Enter Valid Mobile Number' , 'required', 'placeholder' => __('contact.whatsapp')]); !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('referal_code', __('business.ref_code') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                                </span>
                                {!! Form::text('referal_code', $contact->referal_code, ['class' => 'form-control', 'id'=>'referralCode',
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
                            {!! Form::text('contact_person_2', $contact->contact_person_2, ['class' => 'form-control', 'placeholder' => __('lang_v1.contact_person_2'), 'rows' => 3]); !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('country', __('business.country') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-globe"></i>
                                </span>
                                {!! Form::text('country', 'USA', ['class' => 'form-control',  'readonly' => 'readonly', 'placeholder' => __('business.country')]); !!}
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
                                {!! Form::text('alternate_number', $contact->alternate_number, ['class' => 'form-control', 'placeholder' => __('contact.alternate_contact_number')]); !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            {!! Form::label('note', __('lang_v1.note') . ':') !!}
                            {!! Form::textarea('note', !empty($duplicate_product->note) ? $duplicate_product->note : $contact->note, ['class' => 'form-control' , 'id' => 'note']); !!}
                        </div>
                    </div>
                </div>
                {{-- 
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('Fax', __('lang_v1.fax') . ':') !!}
                        {!! Form::text('fax', $contact->fax, ['class' => 'form-control', 'placeholder' => __('lang_v1.fax')]); !!}
                    </div>
                </div>
            </div>
            --}}
            {{-- code end --}}
            {{-- 
            <div class="col-md-6 customer_fields">
                <div class="form-group">
                    {!! Form::label('customer_group_id', __('lang_v1.customer_group') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                        <i class="fa fa-users"></i>
                        </span>
                        {!! Form::select('customer_group_id', $customer_groups, $contact->customer_group_id, ['class' => 'form-control']); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('address_line_1', __('lang_v1.address_line_1') . ':') !!}
                    {!! Form::text('address_line_1', $contact->address_line_1, ['class' => 'form-control', 'placeholder' => __('lang_v1.address_line_1'), 'rows' => 3]); !!}
                </div>
            </div>
            --}}
            <!-- <div class="clearfix"></div>
                <div class="col-md-12 shipping_addr_div"><hr></div>
                <div class="col-md-8 col-md-offset-2 shipping_addr_div" >
                    <strong>{{__('lang_v1.shipping_address')}}</strong><br>
                    {!! Form::text('shipping_address', $contact->shipping_address, ['class' => 'form-control', 
                        'placeholder' => __('lang_v1.search_address'), 'id' => 'shipping_address']); !!}
                <div id="map"></div>
                </div>
                {!! Form::hidden('position', $contact->position, ['id' => 'position']); !!}
                
                </div>
                </div>
                </div> -->
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
            </div>
            {!! Form::close() !!}
        </div>
        <!-- /.modal-content -->
    </div>
</div>
<!-- /.modal-dialog -->
<script>
	$(document).ready(function () {
	    $('#referralCode').on('keyup', function () {
	        let _keys = $(this).val();
	        if (_keys.includes('@')) {
	            let _textArray = _keys.split('@');
	            let _newText = _textArray[0];
	            let _searchKey = _textArray[1];
	            $.ajax({
	                url: "{{route('get-referral-company')}}",
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
</script>

<style>
	.modal-body {
	padding: 0px 15px; 
	}
	.custom-column {
	/* background-color: rgb(204, 204, 204); */
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
	.nyc1{
	margin-top: 25px;
	margin-bottom: 15px;
	}
    .nyc {
	margin-top: 25px;
	margin-bottom: 10px;
	}
	#note{
	height: 100px;
	}
	input[type='checkbox'] {
	width:20px;
	height:20px;
	border-radius:2px;   
	}
	.chechkbox-p {
	margin: -24px;
	margin-left: 30px;
	}
	#note{
	height:100px;
	}
	.modal-lg{
	width:98%;
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
</style>