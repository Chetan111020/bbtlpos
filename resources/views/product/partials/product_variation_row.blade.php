@if(!session('business.enable_price_tax'))
    @php
        $default = 0;
        $class = 'hide';
    @endphp
@else
    @php
        $default = null;
        $class = '';
    @endphp
@endif

<tr class="variation_row">
    <td>
        <div class="input-group">
            {!! Form::select('product_variation[' . $row_index .'][variation_template_id]', $variation_templates, null, ['class' => 'form-control input-sm variation_template', 'required']); !!}
            <input type="hidden" class="row_index" value="{{$row_index}}">
            <span class="input-group-btn">
                <button type="button" class="btn btn-default bg-white btn-flat apply-all btn-sm p-5-5 btn-modal"
                        data-href="{{action('VariationTemplateController@create')}}"
                        data-container=".variation_modal"><i
                                        class="fas fa-plus"></i>
                </button>
            </span>
        </div>
    </td>

    <td>
        <table class="table table-condensed table-bordered blue-header variation_value_table">
            <thead>
            <tr>
                <th width="10%">Item Code</th>
                <th width="10%">Barcode*</th>
                <th width="8%">Variant Name*</th>
                <th width="10%" class="{{$class}}">Cost*
                    <br/>
                    {{-- <span class="pull-left"><small><i>@lang('product.exc_of_tax')</i></small></span> --}}

                    {{-- <span class="pull-right"><small><i>@lang('product.inc_of_tax')</i></small></span> --}}
                </th>
                <th width="8%" class="{{$class}}">@lang('product.selling_price')
                <th  class="{{$class}}">@lang('product.profit_percent')</th>
                <th class="{{$class}}">On Hand</th>
                <th class="{{$class}}">Location</th>
                <br/>
                <small><i><span class="dsp_label"></span></i></small>
                <!-- &nbsp;&nbsp;<b><i class="fa fa-info-circle" aria-hidden="true" data-toggle="popover" data-html="true" data-trigger="hover" data-content="<p class='text-primary'>Drag the mouse over the table cells to copy input values</p>" data-placement="top"></i></b> -->
                </th>


                <th>@lang('lang_v1.variation_images')</th>
                <th>
                    <button type="button" class="btn btn-success btn-xs add_variation_value_row">+</button>
                </th>
            </tr>
            </thead>

            <tbody>
            <tr>
                <td>
                    {!! Form::text('product_variation[' . $row_index .'][variations][0][sub_sku]', null, ['class' => 'form-control input-sm']); !!}
                </td>
                <td>
                    {!! Form::text('product_variation[' . $row_index .'][variations][0][barcode]', null, ['class' => 'form-control input-sm', 'required']); !!}
                </td>
                <td>
                    {!! Form::text('product_variation[' . $row_index .'][variations][0][value]', null, ['class' => 'form-control input-sm variation_value_name', 'required']); !!}
                </td>
                <td class="{{$class}}">
                    {{-- <div class="width-50 f-left">
                        {!! Form::text('product_variation[' . $row_index .'][variations][0][default_purchase_price]', $default, ['class' => 'form-control input-sm variable_dpp input_number', 'placeholder' => __('product.exc_of_tax'), 'required']); !!}
                    </div>

                    <div class="width-50 f-left"> --}}
                    <div class="input-group">
                        {!! Form::hidden('product_variation[' . $row_index .'][variations][0][dpp_inc_tax]', $default, ['class' => 'form-control input-sm variable_dpp_inc_tax input_number', 'placeholder' => __('product.inc_of_tax'), 'required']); !!}
                        {!! Form::text('product_variation[' . $row_index .'][variations][0][default_purchase_price]', $default, ['class' => 'form-control input-sm variable_dpp input_number', 'placeholder' => __('product.inc_of_tax'), 'required']); !!}
                        <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat apply-all btn-sm p-5-5"
                                        data-toggle="tooltip" title="@lang('lang_v1.apply_all')"
                                        data-target-class=".variable_dpp_inc_tax"><i
                                            class="fas fa-check-double"></i></button>
                            </span>
                    </div>
                    {{-- </div> --}}
                </td>
                <td class="{{$class}}">
                    {{--<div class="input-group">--}}
                    {!! Form::text('product_variation[' . $row_index .'][variations][0][default_sell_price]', $default, ['class' => 'form-control input-sm variable_dsp input_number', 'placeholder' => __('product.exc_of_tax'), 'required']); !!}
                    {{--<span class="input-group-btn">--}}
                    {{--<button type="button" class="btn btn-default bg-white btn-flat apply-all btn-sm p-5-5"--}}
                    {{--data-toggle="tooltip" title="@lang('lang_v1.apply_all')"--}}
                    {{--data-target-class=".variable_dsp"><i--}}
                    {{--class="fas fa-check-double"></i></button>--}}
                    {{--</span>--}}
                    {{--</div>--}}
                    {!! Form::hidden('product_variation[' . $row_index .'][variations][0][sell_price_inc_tax]', $default, ['class' => 'form-control input-sm variable_dsp_inc_tax input_number', 'placeholder' => __('product.inc_of_tax'), 'required']); !!}
                </td>
                <td class="{{$class}}">
                    <div class="input-group">
                        {!! Form::text('product_variation[' . $row_index .'][variations][0][profit_percent]', $profit_percent, ['class' => 'form-control input-sm variable_profit_percent input_number', 'required']); !!}

                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default bg-white btn-flat apply-all btn-sm p-5-5"
                                    data-toggle="tooltip" title="@lang('lang_v1.apply_all')"
                                    data-target-class=".variable_profit_percent"><i
                                        class="fas fa-check-double"></i></button>
                        </span>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        {!! Form::text('product_variation[' . $row_index .'][variations][0][on_hand]', null, ['class' => 'form-control input-sm variable_stock']); !!}
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default bg-white btn-flat apply-all btn-sm p-5-5"
                                    data-toggle="tooltip" title="@lang('lang_v1.apply_all')"
                                    data-target-class=".variable_stock"><i
                                        class="fas fa-check-double"></i></button>
                        </span>
                    </div>
                </td>
                <td>
                    <div class="input-group row">
                        <div class="col-3 zero-padding-right">
                            {!! Form::text('product_variation[' . $row_index .'][variations][0][aisle]', null, ['class' => 'form-control input-sm variable_aisle', 'placeholder'=>'A']); !!}
                        </div>
                        <div class="col-3 zero-padding-left zero-padding-right">
                            {!! Form::text('product_variation[' . $row_index .'][variations][0][rack]', null, ['class' => 'form-control input-sm variable_rack', 'placeholder'=>'R']); !!}
                        </div>
                        <div class="col-3 zero-padding-left zero-padding-right">
                            {!! Form::text('product_variation[' . $row_index .'][variations][0][shelf]', null, ['class' => 'form-control input-sm variable_shelf', 'placeholder'=>'S']); !!}
                        </div>
                        <div class="col-3 zero-padding-left">
                            {!! Form::text('product_variation[' . $row_index .'][variations][0][bin]', null, ['class' => 'form-control input-sm variable_bin', 'placeholder'=>'B']); !!}
                        </div>
                    </div>
                </td>
                <td>{!! Form::file('variation_images_' . $row_index .'_0[]', ['class' => 'variation_images', 'accept' => 'image/*', 'multiple']); !!}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-xs remove_variation_value_row">-</button>
                    <input type="hidden" class="variation_row_index" value="0">
                </td>
            </tr>
            </tbody>
        </table>
    </td>
</tr>

<div class="modal fade variation_modal" tabindex="-1" role="dialog"
     aria-labelledby="gridSystemModalLabel">
</div>