<div class="modal-dialog" role="document">
    <div class="modal-content">
        @php
            $form_id = 'contact_add_form';
            if (isset($quick_add)) {
                $form_id = 'quick_add_contact';
            }
            if (isset($store_action)) {
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
        {!! Form::open(['route' => ['smartcrm.lead.UpdateLeads', $contact->id], 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}
        <div class="modal-header">
            <button type="button" class="btn btn-default" data-dismiss="modal"
                style="float: right; font-size: 21px; font-weight: 700; line-height: 1; color: #000; text-shadow: 0 1px 0 #fff; opacity: .2;"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Edit Lead</h4>
        </div>
        <div id="overlay">
            <div class="cv-spinner">
                <span class="spinner"></span>
            </div>
        </div>
        <div class="modal-body">
            <div class="row">
                <!-- customer -->
                {{-- <div class="col-lg-12"> --}}
                {{-- address info --}}
                <div class="col-md-12 custom-column customer_fields">
                    <div class="col-md-12">
                        <h3 class="f-underline">Contact Info</h3>
                    </div>
                    {{-- <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('type', __('contact.contact_type') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                {!! Form::select('type', $types, $type, [
                                    'class' => 'form-control customer_status ',
                                    'id' => 'contact_type',
                                    'placeholder' => __('messages.please_select'),
                                    'required',
                                ]) !!}
                            </div>
                        </div>
                    </div> --}}
                    <input type="hidden" name="type" value="customer">



                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('first_name', __('business.first_name') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-briefcase"></i>
                                </span>
                                {{--                                {!! Form::text('first_name', null, ['class' => 'form-control', 'pattern' => '[A-Za-z]{1,}', 'title' => 'Only Letters Accepted' , 'required', 'placeholder' => __( 'business.first_name' ) ]); !!} --}}
                                {!! Form::text('first_name', $contact->first_name, [
                                    'class' => 'form-control customer_status input-upper-case',
                                    'id' => 'dbaname',
                                    'required',
                                    'placeholder' => __('business.first_name'),
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('supplier_business_name', __('business.business_name') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-briefcase"></i>
                                </span>
                                {!! Form::text('supplier_business_name', $contact->supplier_business_name, [
                                    'class' => 'form-control input-upper-case',
                                    'placeholder' => __('business.business_name'),
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('address_line_1', __('lang_v1.address_line_1') . ':*') !!}
                            {!! Form::text('address_line_1', $contact->address_line_1, [
                                'class' => 'form-control input-upper-case',
                                'placeholder' => __('lang_v1.address_line_1'),
                                'rows' => 3,
                            ]) !!}

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('Contact Person 1', __('lang_v1.contact_person_1') . ':') !!}
                            {!! Form::text('contact_person_1', $contact->contact_person_1, [
                                'class' => 'form-control input-upper-case',
                                'placeholder' => __('lang_v1.contact_person_1'),
                                'rows' => 3,
                            ]) !!}

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('address_line_2', __('lang_v1.address_line_2') . ':') !!}
                            {!! Form::text('address_line_2', $contact->address_line_2, [
                                'class' => 'form-control input-upper-case',
                                'placeholder' => __('lang_v1.address_line_2'),
                                'rows' => 3,
                            ]) !!}

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('email', __('business.email') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                {!! Form::email('email', $contact->email, [
                                    'class' => 'form-control input-upper-case',
                                    'id' => 'customer_email',
                                    'placeholder' => __('business.email'),
                                ]) !!}

                            </div>
                            <span style="color:red" class="already-exists-emails"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('tax', __('lang_v1.tax_id') . ':') !!}
                            {!! Form::text('tax',$contact->tax, ['class' => 'form-control input-upper-case', 'placeholder' => __('lang_v1.tax_id')]); !!}
                        </div>
                    </div>
                         <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('tobacco_license', __('business.tobacco_license') . ':') !!}
                                {!! Form::text('tobacco_license_no', $contact->tobacco_license_no, ['class' => 'form-control',
                                'placeholder' => __('business.tobacco_license')]); !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('city', __('business.city') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-map-marker"></i>
                                </span>
                                {!! Form::text('city', $contact->city, [
                                    'class' => 'form-control input-upper-case',
                                    'placeholder' => __('business.city'),
                                ]) !!}

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
                                {!! Form::text('landline', $contact->landline, [
                                    'class' => 'form-control',
                                    'placeholder' => __('contact.landline'),
                                ]) !!}

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
                                {{-- {!! Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('business.state')]); !!} --}}
                                <select name="state" tabindex = '4' id="state" class="form-control select2">
                                    <option {{ $contact->state == '' ? 'selected' : '' }} value="">-select-
                                    </option>
                                    <option {{ $contact->state == 'Alabama' ? 'selected' : '' }} value="Alabama">
                                        Alabama</option>
                                    <option {{ $contact->state == 'Alaska' ? 'selected' : '' }} value="Alaska">Alaska
                                    </option>
                                    <option {{ $contact->state == 'Arizona' ? 'selected' : '' }} value="Arizona">
                                        Arizona</option>
                                    <option {{ $contact->state == 'Arkansas' ? 'selected' : '' }} value="Arkansas">
                                        Arkansas</option>
                                    <option {{ $contact->state == 'California' ? 'selected' : '' }} value="California">
                                        California</option>
                                    <option {{ $contact->state == 'Colorado' ? 'selected' : '' }} value="Colorado">
                                        Colorado</option>
                                    <option {{ $contact->state == 'Connecticut' ? 'selected' : '' }}
                                        value="Connecticut">Connecticut</option>
                                    <option {{ $contact->state == 'Delaware' ? 'selected' : '' }} value="Delaware">
                                        Delaware</option>
                                    <option {{ $contact->state == 'District Of Columbia' ? 'selected' : '' }}
                                        value="District Of Columbia">District Of Columbia</option>
                                    <option {{ $contact->state == 'Florida' ? 'selected' : '' }} value="Florida">
                                        Florida</option>
                                    <option {{ $contact->state == 'Georgia' ? 'selected' : '' }} value="Georgia">
                                        Georgia</option>
                                    <option {{ $contact->state == 'Hawaii' ? 'selected' : '' }} value="Hawaii">Hawaii
                                    </option>
                                    <option {{ $contact->state == 'Idaho' ? 'selected' : '' }} value="Idaho">Idaho
                                    </option>
                                    <option {{ $contact->state == 'Illinois' ? 'selected' : '' }} value="Illinois">
                                        Illinois</option>
                                    <option {{ $contact->state == 'Indiana' ? 'selected' : '' }} value="Indiana">
                                        Indiana</option>
                                    <option {{ $contact->state == 'Iowa' ? 'selected' : '' }} value="Iowa">Iowa
                                    </option>
                                    <option {{ $contact->state == 'Kansas' ? 'selected' : '' }} value="Kansas">Kansas
                                    </option>
                                    <option {{ $contact->state == 'Kentucky' ? 'selected' : '' }} value="Kentucky">
                                        Kentucky</option>
                                    <option {{ $contact->state == 'Louisiana' ? 'selected' : '' }} value="Louisiana">
                                        Louisiana</option>
                                    <option {{ $contact->state == 'Maine' ? 'selected' : '' }} value="Maine">Maine
                                    </option>
                                    <option {{ $contact->state == 'Maryland' ? 'selected' : '' }} value="Maryland">
                                        Maryland</option>
                                    <option {{ $contact->state == 'Massachusetts' ? 'selected' : '' }}
                                        value="Massachusetts">Massachusetts</option>
                                    <option {{ $contact->state == 'Michigan' ? 'selected' : '' }} value="Michigan">
                                        Michigan</option>
                                    <option {{ $contact->state == 'Minnesota' ? 'selected' : '' }} value="Minnesota">
                                        Minnesota</option>
                                    <option {{ $contact->state == 'Mississippi' ? 'selected' : '' }}
                                        value="Mississippi">Mississippi</option>
                                    <option {{ $contact->state == 'Missouri' ? 'selected' : '' }} value="Missouri">
                                        Missouri</option>
                                    <option {{ $contact->state == 'Montana' ? 'selected' : '' }} value="Montana">
                                        Montana</option>
                                    <option {{ $contact->state == 'Nebraska' ? 'selected' : '' }} value="Nebraska">
                                        Nebraska</option>
                                    <option {{ $contact->state == 'Nevada' ? 'selected' : '' }} value="Nevada">Nevada
                                    </option>
                                    <option {{ $contact->state == 'New Hampshire' ? 'selected' : '' }}
                                        value="New Hampshire">New Hampshire</option>
                                    <option {{ $contact->state == 'New Jersey' ? 'selected' : '' }}
                                        value="New Jersey">New Jersey</option>
                                    <option {{ $contact->state == 'New Mexico' ? 'selected' : '' }}
                                        value="New Mexico">New Mexico</option>
                                    <option {{ $contact->state == 'New York' ? 'selected' : '' }} value="New York">New
                                        York</option>
                                    <option {{ $contact->state == 'North Carolina' ? 'selected' : '' }}
                                        value="North Carolina">North Carolina</option>
                                    <option {{ $contact->state == 'North Dakota' ? 'selected' : '' }}
                                        value="North Dakota">North Dakota</option>
                                    <option {{ $contact->state == 'Ohio' ? 'selected' : '' }} value="Ohio">Ohio
                                    </option>
                                    <option {{ $contact->state == 'Oklahoma' ? 'selected' : '' }} value="Oklahoma">
                                        Oklahoma</option>
                                    <option {{ $contact->state == 'Oregon' ? 'selected' : '' }} value="Oregon">Oregon
                                    </option>
                                    <option {{ $contact->state == 'Pennsylvania' ? 'selected' : '' }}
                                        value="Pennsylvania">Pennsylvania</option>
                                    <option {{ $contact->state == 'Rhode Island' ? 'selected' : '' }}
                                        value="Rhode Island">Rhode Island</option>
                                    <option {{ $contact->state == 'South Carolina' ? 'selected' : '' }}
                                        value="South Carolina">South Carolina</option>
                                    <option {{ $contact->state == 'South Dakota' ? 'selected' : '' }}
                                        value="South Dakota">South Dakota</option>
                                    <option {{ $contact->state == 'Tennessee' ? 'selected' : '' }} value="Tennessee">
                                        Tennessee</option>
                                    <option {{ $contact->state == 'Texas' ? 'selected' : '' }} value="Texas">Texas
                                    </option>
                                    <option {{ $contact->state == 'Utah' ? 'selected' : '' }} value="Utah">Utah
                                    </option>
                                    <option {{ $contact->state == 'Vermont' ? 'selected' : '' }} value="Vermont">
                                        Vermont</option>
                                    <option {{ $contact->state == 'Virginia' ? 'selected' : '' }} value="Virginia">
                                        Virginia</option>
                                    <option {{ $contact->state == 'Washington' ? 'selected' : '' }}
                                        value="Washington">Washington</option>
                                    <option {{ $contact->state == 'West Virginia' ? 'selected' : '' }}
                                        value="West Virginia">West Virginia</option>
                                    <option {{ $contact->state == 'Wisconsin' ? 'selected' : '' }} value="Wisconsin">
                                        Wisconsin</option>
                                    <option {{ $contact->state == 'Wyoming' ? 'selected' : '' }} value="Wyoming">
                                        Wyoming</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group nyc">
                            <label>
                                <input type="checkbox" value="1" {{ $contact->is_nyc == 1 ? ' checked' : '' }}
                                    name="is_nyc" class="nyc">
                                <p class="chechkbox-p"> Is NYC</p>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('mobile', __('contact.mobile') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-mobile"></i>
                                </span>
                                {!! Form::text('mobile', $contact->mobile, [
                                    'class' => 'form-control',

                                    'placeholder' => __('contact.mobile'),
                                ]) !!}

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
                                {!! Form::text('zip_code', $contact->zip_code, [
                                    'class' => 'form-control',
                                    'placeholder' => __('business.zip_code_placeholder'),
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('whatsapp', __('contact.whatsapp') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-mobile"></i>
                                </span>
                                {!! Form::text('whatsapp', $contact->whatsapp, [
                                    'class' => 'form-control',

                                    'placeholder' => __('contact.whatsapp'),
                                ]) !!}

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
                                {!! Form::text('referal_code', $contact->referal_code, [
                                    'class' => 'form-control',
                                    'id' => 'referralCode',
                                    'placeholder' => __('business.ref_code'),
                                ]) !!}
                            </div>
                            <div>
                                <ul id="referralSugetion"></ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('Contact Person 2', __('lang_v1.contact_person_2') . ':') !!}
                            {!! Form::text('contact_person_2', $contact->contact_person_2, [
                                'class' => 'form-control input-upper-case',
                                'placeholder' => __('lang_v1.contact_person_2'),
                                'rows' => 3,
                            ]) !!}

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('country', __('business.country') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-globe"></i>
                                </span>
                                {!! Form::text('country', 'USA', [
                                    'class' => 'form-control',
                                    'readonly' => 'readonly',
                                    'placeholder' => __('business.country'),
                                ]) !!}

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
                                {!! Form::text('alternate_number', $contact->alternate_number, [
                                    'class' => 'form-control',
                                    'placeholder' => __('contact.alternate_contact_number'),
                                ]) !!}

                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="form-group">
                            {!! Form::label('note', __('lang_v1.note') . ':') !!}
                            {!! Form::textarea('note', !empty($duplicate_product->note) ? $duplicate_product->note : $contact->note, [
                                'class' => 'form-control',
                                'id' => 'note',
                            ]) !!}

                        </div>
                    </div>

                     <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Coordinates:</label>
                            <input type="text" class="form-control" value="{{ $contact->coordinates }}" name="coordinates" id="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Hot selling item:</label>
                            <input type="text" name="selling_item" value="{{ $contact->selling_item }}" class="form-control" id="">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="">Store type:</label>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex="5" value="smoke_shop" name="storetype[]" {{ in_array('smoke_shop', explode(',', $contact->storetype)) ? 'checked' : '' }}>
                                <p class="chechkbox-p"> Smoke shop</p>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex = '5' value="dispensary" name="storetype[]" {{ in_array('dispensary', explode(',', $contact->storetype)) ? 'checked' : '' }}>
                                <p class="chechkbox-p"> Dispensary</p>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex = '5' value="deli_grocery" name="storetype[]" {{ in_array('deli_grocery', explode(',', $contact->storetype)) ? 'checked' : '' }}>
                                <p class="chechkbox-p"> Deli Grocery</p>
                            </label>

                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex = '5' value="newstand" name="storetype[]" {{ in_array('newstand', explode(',', $contact->storetype)) ? 'checked' : '' }}>
                                <p class="chechkbox-p"> Newstand</p>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="">Preferred time to talk:</label>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex = '5' value="morning" name="talktime[]" {{ in_array('morning', explode(',', $contact->talktime)) ? 'checked' : '' }}>
                                <p class="chechkbox-p"> Morning</p>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex = '5' value="afternoon" name="talktime[]" {{ in_array('afternoon', explode(',', $contact->talktime)) ? 'checked' : '' }}>
                                <p class="chechkbox-p"> Afternoon</p>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex = '5' value="evening" name="talktime[]" {{ in_array('evening', explode(',', $contact->talktime)) ? 'checked' : '' }}>
                                <p class="chechkbox-p"> Evening</p>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">

                <button type="submit" class="btn btn-primary submit">@lang('messages.update')</button>
                <!-- <input type="submit" value="Save"  class="btn btn-primary"> -->
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
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

    .f-underline,
    .l-underline {
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

    #list:hover>ul,
    #list ul:hover {
        visibility: visible;
        opacity: 1;
        display: block;
    }

    #list {
        clear: both;
        width: 100%;
    }

    #overlay {
        position: fixed;
        top: 0;
        z-index: 100;
        width: 100%;
        height: 100%;
        display: none;
        background: rgba(0, 0, 0, 0.6);
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

    .is-hide {
        display: none;
    }

    .input-upper-case {
        text-transform: uppercase;
    }
</style>
<!-- <script>
    function formfunction() {
        var customername = document.getElementById('customername').value;
        alert(customer);

        if ((customername.search(/[A-Z]/) == -1) || (customername.search(/[a-z]/) == -1)) {
            alert(" Customer Name only accepts letters");
            return false;
        }
        var mobile = document.getElementById('customername').value;
        if ((mobile.search(/[0-9]/) == -1)) {
            alert("please enter valid number ");
            return false;
        }
    }
</script> -->
<script>
    $(document).ready(function() {
        $('#referralCode').on('keyup', function() {
            let _keys = $(this).val();
            if (_keys.includes('@')) {
                let _textArray = _keys.split('@');
                let _newText = _textArray[0];
                let _searchKey = _textArray[1];
                $.ajax({
                    url: "{{ route('get-referral-company') }}",
                    type: 'GET',
                    data: {
                        keys: _searchKey
                    },
                    success: function(response) {
                        $('#referralSugetion').empty();
                        let datas = response;
                        $(datas).each(function(index, data) {
                            $('#referralSugetion').append(
                                '<li id="list" onclick="listText(\'' +
                                _newText + ' @' + data.supplier_business_name +
                                '\')">' + data.supplier_business_name + '</li>');
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
    $(document).on("keyup", "#dbaname", function() {
        var dbaname = $(this).val();
        $("#customername").val(dbaname);
    });

    $(document).on("keyup", "#mobile", function() {
        var mobile = $(this).val();
        $("#whatsapp").val(mobile);
    });

    $("#supplier_email").on("change", function() {
        var email = $(this).val();
        var contact_id = $("#contact_id").val();
        var type = $("#contact_type").val();
        $.ajax({
            method: 'POST',
            url: "{{ url('contacts/checkemail-edit') }}",
            dataType: 'json',
            data: {
                'email': email,
                'contact_id': contact_id,
                'type': type
            },
            success: function(success) {
                if (success.success == true) {
                    $(".already-exists-email").text(success.message);
                    $(".edit").prop("disabled", true);
                } else {
                    $(".already-exists-email").text(success.message);
                    $(".edit").prop("disabled", false);
                }
            },
        });
    });

    $("#customer_email").on("change", function() {
        var email = $(this).val();
        var contact_id = $("#contact_id").val();
        var type = $("#contact_type").val();
        $.ajax({
            method: 'POST',
            url: "{{ url('contacts/checkemail-edit') }}",
            dataType: 'json',
            data: {
                'email': email,
                'contact_id': contact_id,
                'type': type
            },
            success: function(success) {
                if (success.success == true) {
                    $(".already-exists-emails").text(success.message);
                    $(".edit").prop("disabled", true);
                } else {
                    $(".already-exists-emails").text(success.message);
                    $(".edit").prop("disabled", false);
                }
            },
        });
    });
</script>

<script>
    $("#expiry_date").datepicker({
        dateFormat: 'yyyy-mm-dd',
        changeMonth: true,
        changeYear: true
    });
    $("#license_datepicker").datepicker({
        dateFormat: 'yyyy-mm-dd',
        changeMonth: true,
        changeYear: true
    });
</script>
