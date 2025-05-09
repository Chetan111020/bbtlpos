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
      $users = [];
    }
  @endphp
    {!! Form::open(['url' => $url, 'method' => 'post', "enctype" => "multipart/form-data", 'id' => $form_id]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('contact.add_contact')</h4>
    </div>

    <div class="modal-body">
        <div class="row">

        <div class="col-md-6 custom-column">
            <div class="col-md-12"><h3 class="f-underline">Business Info</h3></div>
            
            <div class="col-md-6 contact_type_div">
                <div class="form-group">
                    {!! Form::label('type', __('contact.contact_type') . ':*' ) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                        {!! Form::select('type', $types, $type , ['class' => 'form-control', 'id' => 'contact_type','placeholder' => __('messages.please_select'), 'required']); !!}
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
                        {!! Form::text('contact_id', null, ['class' => 'form-control','placeholder' => __('lang_v1.contact_id')]); !!}
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
                   
                    {!! Form::text('first_name', null, ['class' => 'form-control', 'pattern' => '[A-Za-z]{1,}', 'title' => 'Only Letters Accepted' , 'required', 'placeholder' => __( 'business.first_name' ) ]); !!}
                </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('tax', __('lang_v1.tax_id') . ':*') !!}
                    {!! Form::text('tax', null, ['class' => 'form-control','required', 'placeholder' => __('lang_v1.tax_id')]); !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('supplier_business_name', __('business.business_name') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-briefcase"></i>
                        </span>
                        {!! Form::text('supplier_business_name', null, ['class' => 'form-control', 'id' => 'customername','placeholder' => __('business.business_name')]); !!}
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
                  {!! Form::label('customer_group_id', __('lang_v1.customer_group') . ':') !!}
                  <div class="input-group">
                      <span class="input-group-addon">
                          <i class="fa fa-users"></i>
                      </span>
                      {!! Form::select('customer_group_id', $customer_groups, '', ['class' => 'form-control']); !!}
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
                        
                        {!! Form::text('expiry_date', null, ['class' => 'form-control', 'id' => 'datepicker', 'placeholder' => __('lang_v1.expiry_date'), 'readonly']); !!}
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
                <label><input type="checkbox" value="1" name="is_nyc" class="nyc"><p class="chechkbox-p"> Cigar Customer</p></label>
                </div>
            </div>
            <div class="col-md-3">
            <div class="form-group nyc">
                <label><input type="checkbox" value="1" name="sync" class="nyc"><p class="chechkbox-p"> Do not sync with website</p></label>
            </div>
        </div>
        {{-- account info --}}
        <div class="col-md-12 custom-column">
            <div class="col-md-12"><h3 class="l-underline">Account Info</h3></div>
            <div class="col-md-3">
                <div class="form-group">
                    <div class="multi-input">
                    {!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show_tooltip(__('tooltip.pay_term'))
                    <br/>
                    {{-- {!! Form::number('pay_term_number', null, ['class' => 'form-control width-40 pull-left', 'placeholder' => __('contact.pay_term')]); !!} --}}

                    {!! Form::select('pay_term_number', ['5' => '5 days','10' => '10 days','14' => '14 days','15' => '15 days','30' => '30 days','0' => 'Cash' ], '', ['class' => 'form-control','placeholder' => __('messages.please_select')]); !!}
                    {!! Form::hidden('pay_term_type', 'days' , ['placeholder' => 'Blank for no limit']); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3 customer_fields">
                <div class="form-group">
                    <label>Sales Rep</label>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fas fa-money-bill-alt"></i>
                        </span>
                        <select name="sales_rep" id="" class="form-control">
                            <option>-select-</option>
                            <option value="3">Sales Representative</option>
                        </select>
                    </div>   
                </div>
            </div>
            <div class="col-md-3 customer_fields">
                <div class="form-group">
                    <label>Account Rep</label>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fas fa-money-bill-alt"></i>
                        </span>
                        <select name="account_rep" id="" class="form-control">
                            <option>-select-</option>
                            <option value="4">Account Representative</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-3 customer_fields">
                <div class="form-group">
                    {!! Form::label('credit_limit', __('lang_v1.credit_limit') . ':') !!}
                
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fas fa-money-bill-alt"></i>
                        </span>
                        {!! Form::text('credit_limit', null, ['class' => 'form-control input_number', 'placeholder' => 'Blank for no limit']); !!}
                    </div> 
                </div>
            </div>
        </div>
        </div>
    {{-- address info --}}
    <div class="col-md-6 custom-column">
        <div class="col-md-12"><h3 class="f-underline">Contact Info</h3></div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('address_line_1', __('lang_v1.address_line_1') . ':') !!}
                {!! Form::text('address_line_1', null, ['class' => 'form-control', 'required', 'placeholder' => __('lang_v1.address_line_1'), 'rows' => 3]); !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Contact Person 1', __('lang_v1.contact_person_1') . ':') !!}
                {!! Form::text('contact_person_1', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.contact_person_1'), 'rows' => 3]); !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('address_line_2', __('lang_v1.address_line_2') . ':') !!}
                {!! Form::text('address_line_2', null, ['class' => 'form-control', 'required', 'placeholder' => __('lang_v1.address_line_2'), 'rows' => 3]); !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('email', __('business.email') . ':*') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-envelope"></i>
                        </span>
                        {!! Form::email('email', null, ['class' => 'form-control','required','placeholder' => __('business.email')]); !!}
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
                    {!! Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('business.city')]); !!}
                </div>
            </div>
          </div>
        <div class="col-md-2">
            <div class="form-group nyc">
                <label><input type="checkbox" value="1" name="is_nyc" class="nyc"><p class="chechkbox-p"> Is NYC</p></label>
            </div>
        </div>
        <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('landline', __('contact.landline') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-phone"></i>
                        </span>
                        {!! Form::text('landline', null, ['class' => 'form-control', 'placeholder' => __('contact.landline')]); !!}
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
                    {!! Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('business.state')]); !!}
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
                        {!! Form::text('mobile', null, ['class' => 'form-control','pattern' => '[0-9]{10}', 'title' => 'Enter Valid Mobile Number' , 'required', 'placeholder' => __('contact.mobile')]); !!}
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
                    {!! Form::text('zip_code', null, ['class' => 'form-control', 'pattern' => '[0-9]{5,}' , 'title' => 'Zip Code contains 5 or more numbers',
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
                        {!! Form::text('whatsapp', null, ['class' => 'form-control','pattern' => '[0-9]{10}', 'title' => 'Enter Valid Mobile Number' , 'required', 'placeholder' => __('contact.whatsapp')]); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-12">
            <div class="form-group">
                {!! Form::label('Referal Code', __('business.ref_code') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    {!! Form::text('referal_code', null, ['class' => 'form-control', 
                    'placeholder' => __('business.ref_code')]); !!}
                </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Contact Person 2', __('lang_v1.contact_person_2') . ':') !!}
                {!! Form::text('contact_person_2', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.contact_person_2'), 'rows' => 3]); !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('country', __('business.country') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-globe"></i>
                    </span>
                    {!! Form::text('country', null, ['class' => 'form-control', 'placeholder' => __('business.country')]); !!}
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
                        {!! Form::text('alternate_number', null, ['class' => 'form-control', 'placeholder' => __('contact.alternate_contact_number')]); !!}
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                 {!! Form::label('note', __('lang_v1.note') . ':') !!}
                {!! Form::textarea('note', !empty($duplicate_product->note) ? $duplicate_product->note : null, ['class' => 'form-control' , 'id' => 'note']); !!}
                </div>
            </div>
        </div>
        {{-- <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('Fax', __('lang_v1.fax') . ':') !!}
                    {!! Form::text('fax', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.fax')]); !!}
                </div>
            </div> --}}
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
      <button type="submit" value="submit" class="btn btn-primary submit_contact_form">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}
  
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

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
.nyc{
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
.modal-lg{
    width:98%;
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
$( "#datepicker" ).datepicker({
dateFormat: 'dd/mm/yy',
    changeMonth: true,
    changeYear: true});
</script>