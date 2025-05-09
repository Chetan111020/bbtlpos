<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
          {!! Form::open(['url' => action('TaxRateController@update', [$tax_rate->id]), 'method' => 'PUT', 'id' => 'tax_rate_edit_form' ]) !!}
        <!--{!! Form::open(['url' => action('TaxRateController@store'), 'method' => 'post', 'id' => 'tax_rate_add_form' ]) !!}-->
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'tax_rate.edit_taxt_rate' )</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6 custom-column">
                    <div class="col-md-12">
                        <h3 class="m-underline">General Information</h3>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('name', 'Tax Name:*') !!}
                            {!! Form::text('name', $tax_rate->name, ['class' => 'form-control', 'required', 'placeholder' => 'Tax Name']); !!}
                        </div>
                    </div>
                    {{--
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('amount', __( 'tax_rate.rate' ) . ':*') !!} @show_tooltip(__('lang_v1.tax_exempt_help'))
                            {!! Form::text('amount', $tax_rate->amount, ['class' => 'form-control input_number', 'required']); !!}
                        </div>
                    </div>
                    --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('begining_date', __('tax_rate.start_date') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('begining_date', $tax_rate->begining_date, ['class' => 'form-control', 'id' => 'datepicker', 'placeholder' => __('tax_rate.start_date'), 'readonly']); !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('end_date', __('tax_rate.end_date') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('end_date', $tax_rate->end_date, ['class' => 'form-control', 'id' => 'datepicker1', 'placeholder' => __('tax_rate.end_date'), 'readonly']); !!}
                            </div>
                        </div>
                    </div>
                    {{--
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('state', __('tax_rate.state') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                                </span>
                                {!! Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('tax_rate.state')]); !!}
                            </div>
                        </div>
                    </div>
                    --}}
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Category</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                                </span>
                                {!! Form::select('category[]', $categories, !empty($duplicate_product->category_id) ? $duplicate_product->category_id : $tax_rate->category, ['placeholder' => __('messages.please_select'), 'id' => 'category_id', "style" => "width: 100%;!important;", 'class' => 'form-control select2']); !!}

                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Sub Category</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                                </span>
                                {!! Form::select('subcategory[]', $sub_categories, $tax_rate->sub_category, ['placeholder' => __('messages.please_select'), 'id' => 'sub_category_id', "style" => "width: 100%;!important;", 'class' => 'form-control select2']); !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>State</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                                </span>
                                <select name="state" id="state" class="form-control">
                                    <option value="">-select-</option>
                                    <option selected value="{{$tax_rate->state}}">{{$tax_rate->state}}</option>
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
                    <div class="col-md-6">
                        <div class="form-group chk-pad">
                            <label>
                                <input type="checkbox" value="1" name="inactive">
                                <p class="chechkbox-p"> Item in Active</p>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-4">
                            <label><b>Type of Text: </b></label>
                        </div>
                        <div class="col-md-4">
                            <label>
                                <input type="radio" value="1" name="tax_type" id="stateTax" checked>
                                <p class="chechkbox-p"> State Tax</p>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label>
                                <input type="radio" value="2" name="tax_type" id="cityTax">
                                <p class="chechkbox-p"> City Tax</p>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 custom-column" id="state_tax">
                    <div class="col-md-12">
                        <h3 class="f-underline">State Sale Tax</h3>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('tax', __( 'tax_rate.tax_percent' ) . ':*') !!}
                            {!! Form::text('tax', $tax_rate->tax, ['class' => 'form-control', 'placeholder' => __( 'tax_rate.tax_percent' )]); !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('taxvalue', __( 'tax_rate.tax_value' ) . ':*') !!}
                            {!! Form::text('taxvalue', !empty($tax_rate->is_ml) ? null : $tax_rate->taxvalue, ['class' => 'form-control', 'placeholder' => __( 'tax_rate.tax_value' )]); !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('taxvalue_ml', __( 'tax_rate.tax_value' ) . ' per ML:*') !!}
                            {!! Form::text('taxvalue_ml', empty($tax_rate->is_ml) ? null : $tax_rate->taxvalue, ['class' => 'form-control', 'placeholder' => __( 'tax_rate.tax_value' ) . " for ML"]); !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('every', __( 'tax_rate.every' ) . ':*') !!}
                            {!! Form::text('every', $tax_rate->every, ['class' => 'form-control', 'placeholder' => __( 'tax_rate.every' )]);!!}
                        </div>
                    </div>
                </div>
                <div style="display: none;" class="col-md-6 custom-column" id="city_tex">
                    <div class="col-md-12">
                        <h3 class="l-underline">City Sale Tax</h3>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('tax_percent', __( 'tax_rate.tax_percent' ) . ':*') !!}
                                    {!! Form::text('tax_percent', $tax_rate->tax_percent, ['class' => 'form-control', 'placeholder' => __( 'tax_rate.tax_percent' )]); !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('city_tax_value', __( 'tax_rate.tax_value' ) . ':*') !!}
                                    {!! Form::text('city_tax_value', $tax_rate->city_tax_value, ['class' => 'form-control', 'placeholder' => __( 'tax_rate.tax_value' )]); !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('everycity', __( 'tax_rate.every' ) . ':*') !!}
                                    {!! Form::text('everycity', $tax_rate->everycity, ['class' => 'form-control', 'placeholder' => __( 'tax_rate.every' )]);!!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('first_item_value', __( 'tax_rate.first_item_value' ) . ':*') !!}
                                    {!! Form::text('first_item_value', null, ['class' => 'form-control', 'placeholder' => __( 'tax_rate.first_item_value' )]); !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('second_item_value', __( 'tax_rate.second_item_value' ) . ':*') !!}
                                    {!! Form::text('second_item_value', null, ['class' => 'form-control', 'placeholder' => __( 'tax_rate.second_item_value' )]);!!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('note', __('tax_rate.note') . ':') !!}
                                    {!! Form::textarea('note', !empty($duplicate_product->note) ? $duplicate_product->note : null, ['class' => 'form-control' , 'id' => 'note', 'row' => '5']); !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{--
            <div class="form-group">
                <div class="checkbox">
                    <label>
                    {!! Form::checkbox('for_tax_group', 1, false, [ 'class' => 'input_icheck']); !!} @lang( 'lang_v1.for_tax_group_only' )
                    </label> @show_tooltip(__('lang_v1.for_tax_group_only_help'))
                </div>
            </div>
            --}}
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>
        {!! Form::close() !!}
    </div>
    <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
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
    left: 1%;
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
    .m-underline::after {
    position: absolute;
    content: "";
    height: 2px;
    background-color: currentColor;
    width: 60%;
    margin-left: 12px;
    top: 83%;
    left: 1%;
    }
    .inactive {
    margin-top: 20px;
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
    .chk-pad {
    padding-top: 25px;
    }
    .col-sm-2 {
    width: 20%;
    }
    select[multiple] {
    overflow-y: auto;
    }
</style>
<script>
    $(document).on('change', '#category_id', function() {
        get_sub_categories();
    });

    function get_sub_categories() {
    var cat = $('#category_id').val();
    $.ajax({
        method: 'POST',
        url: '/products/get_sub_categories',
        dataType: 'html',
        data: { cat_id: cat },
        success: function(result) {
            // alert(result);
            if (result) {

                $('#sub_category_id').html(result);
            }
        },
    });
    }

    $(document).ready(function () {
        $('#cityTax').on('click', function () {
            $('#city_tex').css('display', 'block');
            $('#state_tax').css('display', 'none');
        });

        $('#stateTax').on('click', function () {
            $('#city_tex').css('display', 'none');
            $('#state_tax').css('display', 'block');
        });
    });

    $("#datepicker").datepicker({
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true
    });
</script>
<script>
    $("#datepicker1").datepicker({
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true
    });
</script>
{{--<script src="https://code.jquery.com/jquery-3.5.1.min.js"--}}
{{--integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>--}}
<!-- /.content -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#category_id').select2({});
        $('#sub_category_id').select2();
        // $('#category').select2();
    });
</script>