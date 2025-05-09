<div class="modal-dialog " role="document">
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
        {!! Form::open(['route' => 'smartcrm.lead.LeadsStore', 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}
        <div class="modal-header">
            <button type="button" class="btn btn-default" data-dismiss="modal"
                style="float: right; font-size: 21px; font-weight: 700; line-height: 1; color: #000; text-shadow: 0 1px 0 #fff; opacity: .2;"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Add Leads</h4>
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
                                {!! Form::text('first_name', null, [
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
                                {!! Form::text('supplier_business_name', null, [
                                    'class' => 'form-control input-upper-case',
                                    'id' => 'customername',
                                    'placeholder' => __('business.business_name'),
                                ]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('address_line_1', __('lang_v1.address_line_1') . ':*') !!}
                            {!! Form::text('address_line_1', null, [
                                'class' => 'form-control input-upper-case',
                                'tabindex' => '1',
                                'required',
                                'placeholder' => __('lang_v1.address_line_1'),
                                'rows' => 3,
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('Contact Person 1', __('lang_v1.contact_person_1') . ':') !!}
                            {!! Form::text('contact_person_1', null, [
                                'class' => 'form-control input-upper-case',
                                'tabindex' => '10',
                                'placeholder' => __('lang_v1.contact_person_1'),
                                'rows' => 3,
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('address_line_2', __('lang_v1.address_line_2') . ':') !!}
                            {!! Form::text('address_line_2', null, [
                                'class' => 'form-control input-upper-case',
                                'tabindex' => '2',
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
                                {!! Form::email('email', null, [
                                    'class' => 'form-control input-upper-case',
                                    'id' => 'customer_email',
                                    'tabindex' => '11',
                                    'placeholder' => __('business.email'),
                                ]) !!}
                            </div>
                            <span style="color:red" class="already-exists-emails"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('tax', __('lang_v1.tax_id') . ':') !!}
                            {!! Form::text('tax', null, [
                                'class' => 'form-control customer_status input-upper-case',
                                'placeholder' => __('lang_v1.tax_id'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('tobacco_license', __('business.tobacco_license') . ':') !!}
                                {!! Form::text('tobacco_license_no', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('business.tobacco_license'),
                                ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('city', __('business.city') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-map-marker"></i>
                                </span>
                                {!! Form::text('city', null, [
                                    'class' => 'form-control input-upper-case',
                                    'tabindex' => '3',
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
                                {!! Form::text('landline', null, [
                                    'class' => 'form-control',
                                    'id' => 'landline',
                                    'tabindex' => '12',
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
                            {!! Form::label('mobile', __('contact.mobile') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-mobile"></i>
                                </span>
                                {!! Form::text('mobile', null, [
                                    'class' => 'form-control customer_status',
                                    'tabindex' => '13',
                                    'pattern' => '[0-9]{10}',
                                    'title' => 'Enter Valid Mobile Number',
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
                                {!! Form::text('zip_code', null, [
                                    'class' => 'form-control',
                                    'pattern' => '[0-9]{5,}',
                                    'tabindex' => '6',
                                    'title' => 'Zip Code contains 5 or more numbers',
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
                                {!! Form::text('whatsapp', null, [
                                    'class' => 'form-control',
                                    'pattern' => '[0-9]{10}',
                                    'tabindex' => '14',
                                    'title' => 'Enter Valid Mobile Number',
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
                                {!! Form::text('referal_code', null, [
                                    'class' => 'form-control',
                                    'tabindex' => '7',
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
                            {!! Form::text('contact_person_2', null, [
                                'class' => 'form-control input-upper-case',
                                'tabindex' => '15',
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
                                {!! Form::text('alternate_number', null, [
                                    'class' => 'form-control',
                                    'tabindex' => '16',
                                    'placeholder' => __('contact.alternate_contact_number'),
                                ]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="form-group">
                            {!! Form::label('note', __('lang_v1.note') . ':') !!}
                            {!! Form::textarea('note', !empty($duplicate_product->note) ? $duplicate_product->note : null, [
                                'class' => 'form-control',
                                'tabindex' => '17',
                                'id' => 'note',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Coordinates:</label>
                            <input type="text" class="form-control" name="coordinates" id="coordinates">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Hot selling item:</label>
                            <input type="text" name="selling_item" class="form-control" id="">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="">Store type:</label>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex = '5' value="smoke_shop" name="storetype[]">
                                <p class="chechkbox-p"> Smoke shop</p>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex = '5' value="dispensary" name="storetype[]">
                                <p class="chechkbox-p"> Dispensary</p>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex = '5' value="deli_grocery" name="storetype[]">
                                <p class="chechkbox-p"> Deli Grocery</p>
                            </label>

                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex = '5' value="newstand" name="storetype[]">
                                <p class="chechkbox-p"> Newstand</p>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="">Preferred time to talk:</label>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex = '5' value="morning" name="talktime[]">
                                <p class="chechkbox-p"> Morning</p>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex = '5' value="afternoon" name="talktime[]">
                                <p class="chechkbox-p"> Afternoon</p>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" tabindex = '5' value="evening" name="talktime[]">
                                <p class="chechkbox-p"> Evening</p>
                            </label>
                        </div>
                    </div>
                </div>

            </div>
            <hr>
            <h4>@lang('lang_v1.send_notification') - {{ $template_name }}</h4>
            <br>
            <div class="form-group @if ($notification_template['template_for'] == 'send_ledger') hide @endif">

                <label>
                    <input type="checkbox" checked tabindex = '5' value="email_only" name="notification_type">
                    <p class="chechkbox-p"> Send Email</p>
                </label>

            </div>
            <div id="email_div">
                <div class="form-group">
                    {!! Form::label('to_email', __('lang_v1.to') . ':') !!} @show_tooltip(__('lang_v1.notification_email_tooltip'))
                    {!! Form::text('to_email', null, [
                        'class' => 'form-control',
                        'id' => 'ToEmail',
                        'placeholder' => __('lang_v1.to'),
                    ]) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('subject', __('lang_v1.email_subject') . ':') !!}
                    {!! Form::text('subject', $notification_template['subject'], [
                        'class' => 'form-control',
                        'placeholder' => __('lang_v1.email_subject'),
                    ]) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('cc', 'CC:') !!}
                    {!! Form::email('cc', $notification_template['cc'], ['class' => 'form-control', 'placeholder' => 'CC']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('bcc', 'BCC:') !!}
                    {!! Form::email('bcc', $notification_template['bcc'], ['class' => 'form-control', 'placeholder' => 'BCC']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('email_body', __('lang_v1.email_body') . ':') !!}
                    {!! Form::textarea('email_body', $notification_template['email_body'], [
                        'class' => 'form-control',
                        'placeholder' => __('lang_v1.email_body'),
                        'rows' => 6,
                    ]) !!}
                </div>
                @if ($notification_template['template_for'] == 'send_ledger')
                    <p class="help-block">*@lang('lang_v1.ledger_attacment_help')</p>
                @endif
            </div>
            <div id="sms_div" class="hide">
                <div class="form-group">
                    {!! Form::label('mobile_number', __('lang_v1.mobile_number') . ':') !!}
                    {!! Form::text('mobile_number', null, [
                        'class' => 'form-control',
                        'placeholder' => __('lang_v1.mobile_number'),
                    ]) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('sms_body', __('lang_v1.sms_body') . ':') !!}
                    {!! Form::textarea('sms_body', $notification_template['sms_body'], [
                        'class' => 'form-control',
                        'placeholder' => __('lang_v1.sms_body'),
                        'rows' => 6,
                    ]) !!}
                </div>
            </div>
            <strong>@lang('lang_v1.available_tags'):</strong>
            <p class="help-block">{{ implode(', ', $tags) }}</p>
            {!! Form::hidden('template_for', $notification_template['template_for']) !!}

            <div class="modal-footer">

                <button type="submit" class="btn btn-primary submit">@lang('messages.save')</button>
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
 $(document).on("keyup", "#customer_email", function() {
        var toemail = $(this).val();
        $("#ToEmail").val(toemail);
    });

    $(document).ready(function() {
        $('#referralCode').on('keyup', function() {
            let _keys = $(this).val();
            if (_keys.includes('@')) {
                let _textArray = _keys.split('@');
                let _newText = _textArray[0];
                let _searchKey = _textArray[1];
                $.ajax({
                    url: '{{ route('get-referral-company') }}',
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


    $("#supplier_email").on('change', function() {
        var email = $(this).val();
        var type = $("#contact_type").val();
        $.ajax({
            method: 'POST',
            url: "{{ url('contacts/checkemail') }}",
            dataType: 'json',
            data: {
                'email': email,
                'type': type
            },
            success: function(success) {

                if (success.success == true) {
                    $(".submit").prop("disabled", true);
                    $(".already-exists-email").text(success.message);
                } else {
                    $(".submit").prop("disabled", false);
                    $(".already-exists-email").text(success.message);
                }
            },
        });
    });

    $("#customer_email").on('change', function() {
        var email = $(this).val();
        var type = $("#contact_type").val();
        $.ajax({
            method: 'POST',
            url: "{{ url('contacts/checkemail') }}",
            dataType: 'json',
            data: {
                'email': email,
                'type': type
            },
            success: function(success) {

                if (success.success == true) {
                    $(".submit").prop("disabled", true);
                    $(".already-exists-emails").text(success.message);
                } else {
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
    $("#license_datepicker").datepicker({
        dateFormat: 'yyyy-mm-dd',
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
<script type="text/javascript">
// Fix for not updating textarea value on modal
  // CKEDITOR.on('instanceReady', function(){
  //    $.each( CKEDITOR.instances, function(instance) {
  //     CKEDITOR.instances[instance].on("change", function(e) {
  //         for ( instance in CKEDITOR.instances )
  //         CKEDITOR.instances[instance].updateElement();
  //     });
  //    });
  // });

  if (_.isNull(tinyMCE.activeEditor)) {
        tinymce.init({
            selector: 'textarea#email_body',
        });
    }

  $(document).ready(function(){
    //initialize iCheck
    $('input[type="checkbox"].input-icheck, input[type="radio"].input-icheck').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue'
    });
  });

  $(document).on('ifChanged', 'input[type=radio][name=notification_type]', function(){
    var notification_type = $(this).val();
    if (notification_type == 'email_only') {
      $('div#email_div').removeClass('hide');
      $('div#sms_div').addClass('hide');
    } else if(notification_type == 'sms_only'){
      $('div#email_div').addClass('hide');
      $('div#sms_div').removeClass('hide');
    } else if(notification_type == 'both'){
      $('div#email_div').removeClass('hide');
      $('div#sms_div').removeClass('hide');
    }
  });
  $('#send_notification_form').submit(function(e){
    e.preventDefault();
    tinyMCE.triggerSave();
    var data = $(this).serialize();
    $('#send_notification_btn').text("@lang('lang_v1.sending')...");
    $('#send_notification_btn').attr('disabled', 'disabled');
    $.ajax({
      method: "POST",
      url: $(this).attr("action"),
      dataType: "json",
      data: $(this).serialize(),
      success: function(result){
        if(result.success == true){
          $('div.view_modal').modal('hide');
          toastr.success(result.msg);
        } else {
          toastr.error(result.msg);
        }
        $('#send_notification_btn').text("@lang('lang_v1.send')");
        $('#send_notification_btn').removeAttr('disabled');
      }
    });
  });
</script>