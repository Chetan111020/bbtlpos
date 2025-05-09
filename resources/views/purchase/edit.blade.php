@extends('layouts.app')
@section('title', __('purchase.edit_purchase'))

@section('content')
    <script type="module" src="https://cdn.jsdelivr.net/npm/ldrs/dist/auto/squircle.js"></script>
    <style type="text/css">
        .hide {
            display: none;
        }

        .show {
            display: block;
        }

        .table-height {
            height: 600px;
        }

        .text-wrap {
            white-space: normal;
        }

        .width-90 {
            width: 90%;
        }

        .failure {
            color: #a94442;
            border-color: #ebccd1;
            display: none;
        }

        .fileinput-upload-button {
            display: none;
        }

        .vendor_bill_row_modal_diag {
            display: block !important;
        }

        .stock_history {
            color: gray !important;
        }

        .stock_history td span {
            color: gray !important;
        }
    </style>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('Edit Vendor Bill') <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body"
                data-toggle="popover" data-placement="bottom" data-content="@include('purchase.partials.keyboard_shortcuts_details')" data-html="true"
                data-trigger="hover" data-original-title="" title=""></i></h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- Page level currency setting -->
        <input type="hidden" id="p_code" value="{{ $currency_details->code }}">
        <input type="hidden" id="p_symbol" value="{{ $currency_details->symbol }}">
        <input type="hidden" id="p_thousand" value="{{ $currency_details->thousand_separator }}">
        <input type="hidden" id="p_decimal" value="{{ $currency_details->decimal_separator }}">

        @include('layouts.partials.error')

        {!! Form::open([
            'url' => action('PurchaseController@update', [$purchase->id]),
            'method' => 'PUT',
            'id' => 'add_purchase_form',
            'files' => true,
        ]) !!}
        @component('components.widget', ['class' => 'box-primary'])
            <input type="hidden" id="item_addition_method" value="1">
            <input type="hidden" id="purchase_id" value="{{ $purchase->id }}">
            <input name="_method" type="hidden" value="PUT">

            <div class="row">
                <div class="@if (!empty($default_purchase_status)) col-sm-4 @else col-sm-3 @endif">
                    <div class="form-group">
                        {!! Form::label('supplier_id', __('purchase.supplier') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                            {!! Form::select('contact_id', [$purchase->contact_id => $purchase->contact->name], $purchase->contact_id, [
                                'class' => 'form-control',
                                'placeholder' => __('messages.please_select'),
                                'required',
                                'id' => 'supplier_id',
                            ]) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat add_new_supplier"
                                    data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="@if (!empty($default_purchase_status)) col-sm-4 @else col-sm-3 @endif">
                    <div class="form-group">
                        {!! Form::label('ref_no', __('purchase.ref_no') . '*') !!}
                        {!! Form::text('ref_no', $purchase->ref_no, ['class' => 'form-control', 'required']) !!}
                        <span style="color:red" class="error"></span>
                    </div>
                </div>
                <div class="@if (!empty($default_purchase_status)) col-sm-4 @else col-sm-3 @endif">
                    <div class="form-group">
                        {!! Form::label('transaction_date', __('Received Date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('transaction_date', @format_datetime($purchase->transaction_date), [
                                'class' => 'form-control',
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="@if (!empty($default_purchase_status)) col-sm-4 @else col-sm-3 @endif">
                    <div class="form-group">
                        {!! Form::label('received_date', __('Invoiced Date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text(
                                'received_date',
                                @format_datetime(!$purchase->received_date ? $purchase->transaction_date : $purchase->received_date),
                                ['class' => 'form-control', 'required'],
                            ) !!}
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('documents', __('purchase.attach_document') . ':') !!}
                        {!! Form::file('documents[]', [
                            'id' => 'upload_document',
                            'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types'))),
                            'class' => 'file',
                        ]) !!}<br>
                        <p class="help-block">@lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000])
                            @includeIf('components.document_help_text')</p>
                        <div class="contents"></div>
                        <br><span><a href="javascript:void(0);" class="add btn btn-default">Add More</a></span>
                    </div>
                    <span><b>View Files:</b></span>
                    <div class="panel panel-default">
                        <div class="panel-body">
                            @if ($purchase->document_path)
                                <tr>
                                    <td class="table-text">
                                        <div id="first_document">file-0
                                            <a href="{{ $purchase->document_path }}" target="_blank"
                                                class="btn btn-primary btn-xs" title="View Document"><i class="fa fa-eye"
                                                    aria-hidden="true"></i> <span class="hidden-xs">View</span>
                                            </a>
                                            <a title="Clear selected file"
                                                class="deleteDocFirst btn btn-danger btn-xs fileinput-remove fileinput-remove-button"
                                                data-id="{{ $purchase->id }}" data-doc="{{ $purchase->document }}"
                                                data-key="document">
                                                <i class="glyphicon glyphicon-trash"></i> <span class="hidden-xs">Remove</span>
                                            </a>
                                        </div><br>
                                    </td>
                                </tr>
                            @endif
                            @if ($purchase->extra_document && count($purchase->extra_document) > 0)
                                @if (count($purchase->extra_document) > 0)
                                    @forelse ($purchase->extra_document as $key => $doc_extra)
                                        <tr>
                                            @php $path = !empty($doc_extra) ? asset('/uploads/documents/' . $doc_extra) : null; @endphp
                                            <td class="table-text">
                                                <div id="file-{{ $loop->iteration }}">file-{{ $loop->iteration }}
                                                    <a href="{{ $path }}" target="_blank"
                                                        class="btn btn-primary btn-xs" title="View Document">
                                                        <i class="fa fa-eye" aria-hidden="true"></i> <span
                                                            class="hidden-xs">View</span>
                                                    </a>
                                                    <a title="Clear selected files"
                                                        class="deleteDoc btn btn-danger btn-xs fileinput-remove fileinput-remove-button"
                                                        data-id="{{ $purchase->id }}" data-doc="{{ $doc_extra }}"
                                                        data-key="{{ $loop->iteration }}">
                                                        <i class="glyphicon glyphicon-trash"></i> <span
                                                            class="hidden-xs">Remove</span>
                                                    </a>
                                                </div><br>
                                            </td>
                                        </tr>
                                    @empty
                                        No Files Found
                                    @endforelse
                                @endif
                            @endif
                        </div>
                    </div>

                </div>
                <div class="col-sm-3" style="margin-top:3rem; ">
                    <div class="form-group">
                        <label>
                            {!! Form::checkbox('is_consignment', 1, $purchase->is_consignment, ['id' => 'is_consignment']) !!}
                            {{ __('Is Consignment') }}
                        </label>
                    </div>
                </div>

                <div id="returnable_fields" class="col-sm-6"
                    style="display: {{ $purchase->is_consignment ? 'block' : 'none' }};">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                {!! Form::label('returnable_days', __('Returnable Days') . ':') !!}
                                {!! Form::number('returnable_days', $purchase->returnable_days ?? 0, [
                                    'class' => 'form-control',
                                    'id' => 'returnable_days',
                                    'min' => 0,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                {!! Form::label('return_date', __('Return Date') . ':') !!}
                                {!! Form::text('return_date', @format_datetime($purchase->return_date), [
                                    'class' => 'form-control',
                                    'id' => 'return_date',
                                    'readonly' => true,
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>

                
                @if (count($business_locations) == 1)
                    @php
                        $default_location = current(array_keys($business_locations->toArray()));
                        $search_disable = false;
                    @endphp
                @else
                    @php
                        $default_location = null;
                        $search_disable = true;
                    @endphp
                @endif
                <div style="display:none" class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location') . ':*') !!}
                        @show_tooltip(__('tooltip.purchase_location'))
                        {!! Form::select(
                            'location_id',
                            $business_locations,
                            $default_location,
                            ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required'],
                            @$bl_attributes,
                        ) !!}
                    </div>
                </div>

                <!-- Currency Exchange Rate -->
                <div class="col-sm-3 @if (!$currency_details->purchase_in_diff_currency) hide @endif">
                    <div class="form-group">
                        {!! Form::label('exchange_rate', __('purchase.p_exchange_rate') . ':*') !!}
                        @show_tooltip(__('tooltip.currency_exchange_factor'))
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-info"></i>
                            </span>
                            {!! Form::number('exchange_rate', $currency_details->p_exchange_rate, [
                                'class' => 'form-control',
                                'required',
                                'step' => 0.001,
                            ]) !!}
                        </div>
                        <span class="help-block text-danger">
                            @lang('purchase.diff_purchase_currency_help', ['currency' => $currency_details->name])
                        </span>
                    </div>
                </div>

                <div class="col-md-3" style="display: none;">
                    <div class="form-group">
                        <div class="multi-input">
                            {!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show_tooltip(__('tooltip.pay_term'))
                            <br />
                            {!! Form::number('pay_term_number', null, [
                                'class' => 'form-control width-40 pull-left',
                                'placeholder' => __('contact.pay_term'),
                            ]) !!}

                            {!! Form::select('pay_term_type', ['months' => __('lang_v1.months'), 'days' => __('lang_v1.days')], null, [
                                'class' => 'form-control width-60 pull-left',
                                'placeholder' => __('messages.please_select'),
                                'id' => 'pay_term_type',
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-sm-3" style="display: none;">
                    <div class="form-group">
                        {!! Form::label('document_two', __('purchase.attach_document') . ':') !!}
                        {!! Form::file('document_two', [
                            'id' => 'upload_document',
                            'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types'))),
                        ]) !!}
                        <p class="help-block">
                            @lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000])
                            @includeIf('components.document_help_text')
                        </p>
                    </div>
                </div>
            </div>

        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-sm-8 col-sm-offset-4">
                    <div class="pull-right" style='margin-right:25px;margin-bottom:8px;'>
                        <input type="checkbox" style='margin-top:15px;' value="0" id="product_status"
                            name="product_status">
                        <span style='font-weight:700;font-size:18px;'>
                            Inactive Product
                        </span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>
                            {!! Form::text('search_product', null, [
                                'class' => 'form-control mousetrap',
                                'id' => 'search_product_two',
                                'placeholder' => __('lang_v1.search_product_placeholder'),
                                'autofocus',
                                'data-edit' => '1',
                            ]) !!}
                        </div>
                    </div>
                    <div class="alert-box failure" id="search_prod">No matching product found!</div>
                    <div class="alert-box failure" id="search_prod_not_sell">Not For Selling!</div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <button tabindex="-1" type="button"
                            class="btn btn-link btn-modal"data-href="{{ action('ProductController@quickAdd') }}"
                            data-container=".quick_add_product_modal"><i class="fa fa-plus"></i> @lang('product.add_new_product') </button>
                    </div>
                </div>
            </div>
            {{-- <div class="row">
        <div class="col-sm-12">
          @include('purchase.partials.edit_purchase_entry_row')

          <hr/>
          <div class="pull-right col-md-5">
            <table class="pull-right col-md-12">
              <tr>
                <th class="col-md-7 text-right">@lang( 'lang_v1.total_items' ):</th>
                <td class="col-md-5 text-left">
                  <span id="total_quantity" class="display_currency" data-currency_symbol="false"></span>
                </td>
              </tr>
              <tr class="hide">
                <th class="col-md-7 text-right">@lang( 'purchase.total_before_tax' ):</th>
                <td class="col-md-5 text-left">
                  <span id="total_st_before_tax" class="display_currency"></span>
                  <input type="hidden" id="st_before_tax_input" value=0>
                </td>
              </tr>
              <tr>
                <th class="col-md-7 text-right">@lang( 'purchase.net_total_amount' ):</th>
                <td class="col-md-5 text-left">
                  <span id="total_subtotal" class="display_currency">{{$purchase->total_before_tax/$purchase->exchange_rate}}</span>
                  <!-- This is total before purchase tax-->
                  <input type="hidden" id="total_subtotal_input" value="{{$purchase->total_before_tax/$purchase->exchange_rate}}" name="total_before_tax">
                </td>
              </tr>
            </table>
          </div>

        </div>
    </div> --}}


            <div class="row">
                <div class="col-sm-12">
                    @include('purchase.partials.edit_purchase_entry_row')
                    {{-- <div class="card-body table-responsive p-0" id="entry_table_row">
            <input type="hidden" id="div_height" value="0">
          <table style="background:#808080; color:#fff" class="table table-condensed table-bordered text-center table-striped table-head-fixed text-nowrap" id="purchase_entry_table">
            <thead>
              <tr>
                <th>#</th>
                <th>@lang( 'product.product_name' )</th>
                <th>@lang( 'purchase.purchase_quantity' )</th>
                <th>@lang( 'Cost' )</th>
                <th>@lang( 'Additional Charges' )</th>
                <th class="">@lang( 'Landed Price' )</th>
                <th>@lang( 'purchase.line_total' )</th>
                <th class="@if (!session('business.enable_editing_product_from_purchase')) hide @endif">
                  @lang( 'G P' )
                </th>
                <th>
                  @lang( 'Selling Price' )
                  <small>(@lang('product.inc_of_tax'))</small>
                </th>
                @if (session('business.enable_lot_number'))
                  <th>
                    @lang('lang_v1.lot_number')
                  </th>
                @endif
                @if (session('business.enable_product_expiry'))
                  <th>
                    @lang('product.mfg_date') / @lang('product.exp_date')
                  </th>
                @endif
                <th><i class="fa fa-trash" aria-hidden="true"></i></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div> --}}
                    <hr />
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-xs-4">
                                        {!! Form::label('ref_no', __('Shipping Charges') . ':') !!}
                                        {!! Form::number('additional_charges', $purchase->shipping_charges, [
                                            'class' => 'form-control',
                                            'id' => 'additional_charges',
                                        ]) !!}
                                        {!! Form::hidden('shipping_charges', $purchase->shipping_charges, [
                                            'class' => 'form-control input_number',
                                            'id' => 'shipping_charges',
                                        ]) !!}
                                        <div style="margin: 1px;">
                                            <span class="btn btn-warning" onclick="additionalChargesFun()"
                                                style="margin-bottom: 5px; margin-top: 5px;">Apply</span>
                                            <span class="btn btn-danger" onclick="additionalChargesRemove()">Reset</span>
                                        </div>
                                    </div>

                                    <div class="col-xs-4">
                                        {!! Form::label('ref_no', __('Discount') . ':') !!}
                                        {!! Form::number('discount_charges', $purchase->discount_amount, [
                                            'class' => 'form-control',
                                            'id' => 'discount_charges',
                                        ]) !!}
                                        {!! Form::hidden('discount_amount', $purchase->discount_amount, [
                                            'class' => 'form-control input_number',
                                            'id' => 'discount_amount',
                                        ]) !!}
                                        {!! Form::hidden('discount_type', 'fixed', ['class' => 'form-control input_number', 'id' => 'discount_type']) !!}
                                        <div style="margin: 1px;">
                                            <span class="btn btn-warning" onclick="discountChargesFun()"
                                                style="margin-bottom: 5px; margin-top: 5px;">Apply</span>
                                            <span class="btn btn-danger" onclick="discountChargesRemove()">Reset</span>
                                        </div>
                                    </div>
                                    <div class="col-xs-4">
                                        <div class="form-group">
                                            <label>@lang('lang_v1.box_qty')</label>
                                            <input type="text" name="box_qty" value="{{ $purchase->box_qty ?? 0 }}"
                                                class="form-control" required />
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="pull-right col-md-12">
                        <table class="pull-right col-md-6">
                            <tr>
                                <th class="col-md-7 text-right">@lang('lang_v1.total_items'):</th>
                                <td class="col-md-5 text-left">
                                    <span id="total_quantity" class="display_currency"
                                        data-currency_symbol="false">{{ @$total_items }}</span>
                                </td>
                            </tr>
                            <tr class="hide">
                                <th class="col-md-7 text-right">@lang('purchase.total_before_tax'):</th>
                                <td class="col-md-5 text-left">
                                    <span id="total_st_before_tax" class="display_currency"></span>
                                    <input type="hidden" id="st_before_tax_input" value=0>
                                </td>
                            </tr>
                            <tr>
                                <th class="col-md-7 text-right">@lang('purchase.net_total_amount'):</th>
                                <td class="col-md-5 text-left">
                                    <span id="total_subtotal" class="">${{ $purchase->final_total }}</span>
                                    <!-- This is total before purchase tax-->
                                    <input type="hidden" id="total_subtotal_input" value="{{ $purchase->final_total }}"
                                        name="total_before_tax">
                                    {!! Form::hidden('final_total', $purchase->final_total, ['id' => 'grand_total_hidden']) !!}
                                </td>
                            </tr>
                            <tr class="" id="discount_show">
                                <th class="col-md-7 text-right">Discount:</th>
                                <th class="col-md-5 text-left">
                                    <span id="total_discount" class="">$ {{ $purchase->discount_amount }}</span>
                                    <!-- This is total before purchase tax-->
                                    <input type="hidden" id="total_discount_input" value="{{ $purchase->discount_amount }}"
                                        name="total_discount_name">
                                </th>
                            </tr>
                        </table>
                    </div>

                    <input type="hidden" id="row_count" value="0">
                </div>
            </div>
        @endcomponent

        <div style="display:block;">
            @component('components.widget', ['class' => 'box-primary componentHide'])
                <div class="row">
                    <div class="col-sm-12">
                        <table class="table">
                            <tr>
                                <td colspan="4">
                                    <div class="form-group">
                                        {!! Form::label('additional_notes', __('purchase.additional_notes')) !!}
                                        {!! Form::textarea('additional_notes', $purchase->additional_notes, ['class' => 'form-control', 'rows' => 3]) !!}
                                    </div>
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>
            @endcomponent
        </div>

        <div style="display:none;">
            {{-- @component('components.widget', ['class' => 'box-primary componentHide'])
      <div class="row">
        <div class="col-sm-12">
        <table class="table">
          <tr>
            <td class="col-md-3">
              <div class="form-group">
                {!! Form::label('discount_type_2', __( 'purchase.discount_type' ) . ':') !!}
                {!! Form::select('discount_type_2', [ '' => __('lang_v1.none'), 'fixed' => __( 'lang_v1.fixed' ), 'percentage' => __( 'lang_v1.percentage' )], '', ['class' => 'form-control select2']); !!}
              </div>
            </td>
            <td class="col-md-3">
              <div class="form-group">
              {!! Form::label('discount_amount_2', __( 'purchase.discount_amount' ) . ':') !!}
              {!! Form::text('discount_amount_2', 0, ['class' => 'form-control input_number', 'required']); !!}
              </div>
            </td>
            <td class="col-md-3">
              &nbsp;
            </td>
            <td class="col-md-3">
              <b>@lang( 'purchase.discount' ):</b>(-)
              <span id="discount_calculated_amount" class="display_currency">0</span>
            </td>
          </tr>
          <tr>
            <td>
              <div class="form-group">
              {!! Form::label('tax_id', __('purchase.purchase_tax') . ':') !!}
              <select name="tax_id" id="tax_id" class="form-control select2" placeholder="'Please Select'">
                <option value="" data-tax_amount="0" data-tax_type="fixed" selected>@lang('lang_v1.none')</option>
                @foreach ($taxes as $tax)
                  <option value="{{ $tax->id }}" data-tax_amount="{{ $tax->amount }}" data-tax_type="{{ $tax->calculation_type }}">{{ $tax->name }}</option>
                @endforeach
              </select>
              </div>
               {!! Form::hidden('tax_amount', $purchase->tax_amount, ['id' => 'tax_amount']); !!}
            </td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>
              <b>@lang( 'purchase.purchase_tax' ):</b>(+)
              <span id="tax_calculated_amount" class="display_currency">0</span>
            </td>
          </tr>

          <tr>
            <td>
              <div class="form-group">
              {!! Form::label('shipping_details', __( 'purchase.shipping_details' ) . ':') !!}
              {!! Form::text('shipping_details', null, ['class' => 'form-control']); !!}
              </div>
            </td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>
              <div class="form-group">
              {!! Form::label('shipping_charges_2','(+) ' . __( 'purchase.additional_shipping_charges' ) . ':') !!}
              {!! Form::text('shipping_charges_2', 0, ['class' => 'form-control input_number', 'required']); !!}
              </div>
            </td>
          </tr>

          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>
              {!! Form::hidden('final_total', 0 , ['id' => 'grand_total_hidden']); !!}
              <b>@lang('purchase.purchase_total'): </b><span id="grand_total" class="display_currency" data-currency_symbol='true'>0</span>
            </td>
          </tr>
          <tr>
            <td colspan="4">
              <div class="form-group">
                {!! Form::label('additional_notes_two',__('purchase.additional_notes')) !!}
                {!! Form::textarea('additional_notes_two', null, ['class' => 'form-control', 'rows' => 3]); !!}
              </div>
            </td>
          </tr>

        </table>
        </div>
      </div>
    @endcomponent --}}
        </div>

        @component('components.widget', ['class' => 'box-primary componentpurchase_entry_tableHide'])
            <div style="display:none">
                <div class="box-body payment_row">
                    <div class="row">
                        <div class="col-md-12">
                            <strong>@lang('lang_v1.advance_balance'):</strong> <span id="advance_balance_text">0</span>
                            {!! Form::hidden('advance_balance', null, [
                                'id' => 'advance_balance',
                                'data-error-msg' => __('lang_v1.required_advance_balance_not_available'),
                            ]) !!}
                        </div>
                    </div>
                    {{-- @include('sale_pos.partials.payment_row_form', ['row_index' => 0, 'show_date' => true]) --}}
                    <hr>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="pull-right"><strong>@lang('purchase.payment_due'):</strong> <span id="payment_due">0.00</span>
                            </div>
                        </div>
                    </div>
                    <br>
                </div>
            </div>
            <div class="box-body payment_row">
                <div class="row">
                    <div class="col-sm-12">
                        <button type="button" id="submit_purchase_form"
                            class="btn btn-primary pull-right btn-flat">Update</button>
                        <button class="btn btn-danger pull-right btn-flat"><a href="{{ route('purchases.index') }}"
                                style="color: white;">Cancel</a></button>
                    </div>
                </div>
            </div>
        @endcomponent
        {!! Form::close() !!}

        <div class="modal fade vendor_bill_row_modal" id="vendor_bill_row_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog vendor_bill_row_modal_diag" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Product Stock History</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="vendor_bill_row_loader" style="display: flex;justify-content:center;">
                                <l-squircle size="37" stroke="5" stroke-length="0.15" bg-opacity="0.1"
                                    speed="0.9" color="black"></l-squircle>
                            </div>
                            <div class="stock_history" style="padding: 15px;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </section>
@endsection
<section class="section-div">
    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"
        data-backdrop="static" aria-hidden="true"></div>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
</section>
<!-- /.content -->
<style>
    div.dataTables_filter input {
        border: 1px solid black;
    }

    #purchase_entry_table tr:nth-child(odd) td {
        color: #777;
    }

    #purchase_entry_table tr:nth-child(even) td {
        color: #fff;
    }

    #purchase_entry_table tr:nth-child(odd) td span {
        color: #777;
    }

    #purchase_entry_table tr:nth-child(even) td span {
        color: #fff;
    }
</style>

@section('javascript')
    <!-- <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script> -->
    <script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script>
        $(document).ready(function() {
            calculateReturnDate(); // Initial call on page load

            $('#returnable_days').on('input change', function() {
                calculateReturnDate();
            });

            $('#is_consignment').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#returnable_fields').show();
                    $('#returnable_days').val(0);
                    calculateReturnDate();
                } else {
                    $('#return_date').val('');
                    $('#returnable_days').val('');
                    $('#returnable_fields').hide();
                }
            });
        });

        // Function to calculate return date
        function calculateReturnDate() {
            let days = parseInt($('#returnable_days').val(), 10) || 0; // Default to 0
            let receivedDateStr = $('#received_date').val();

            if (!isNaN(days) && receivedDateStr) {
                let phpFormat = '{{ session('business.date_format') }}'; // e.g., 'd/m/Y'
                let momentFormat = phpFormat
                    .replace('d', 'DD')
                    .replace('m', 'MM')
                    .replace('Y', 'YYYY');

                let receivedDate = moment(receivedDateStr, momentFormat);

                if (receivedDate.isValid()) {
                    let returnDate = receivedDate.clone().add(days, 'days');
                    $('#return_date').val(returnDate.format(momentFormat));
                    $('#return_date').trigger('change');
                }
            }
        }
    </script>

    <script type="text/javascript">
        $(document).ready(function() {

            $(document).on('click', '.product-name', function(e) {
                $('.vendor_bill_row_loader').show();
                $('.vendor_bill_row_modal').find('.stock_history').hide();
                var url = "/sells/pos/get-stock-history";
                $.ajax({
                    url: url,
                    dataType: 'html',
                    success: function(result) {
                        $('.vendor_bill_row_modal').find('.stock_history').html(result).show();
                        $('.vendor_bill_row_loader').hide();
                    },
                    data: {
                        variation_id: $(this).data('variation_id'),
                        product_id: $(this).data('product_id'),
                        status: 1
                    },
                });
            });

            __page_leave_confirmation('#add_purchase_form');

            $('#product_status').click(function() {
                if ($('#product_status').is(":checked") == true) {
                    $('#product_status').val('1');
                } else {
                    $('#product_status').val('0');
                }
            });

            $('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });
        });

        $(document).on('blur', '.purchase_unit_cost_without_discount', function(e) {
            const val = $(this).val();
            const cost = parseInt($(this).parent().find('.currentcost').html());
            const total = ((val - cost) / 100) * 100;
            const parce = total.toFixed(2) + '% Change <br>';
            $(this).parent().find('.changepercent').html(parce);
        });

        function additionalChargesFun() {
            var total_subtotal = 0;
            var total = 0;
            $('#purchase_entry_table tbody')
                .find('tr')
                .each(function() {
                    var quantity = __read_number($(this).find('.purchase_quantity'), true);
                    total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
                    var suvtotal = quantity * total_quantity;
                    total_subtotal += suvtotal;

                });
            const addtional_charges = parseFloat($('#additional_charges').val());
            const discount_amt = parseFloat($('#discount_amount').val());
            if (total_subtotal != 0) {
                total = total_subtotal + addtional_charges;

                if (discount_amt != 0) {
                    total = total - discount_amt;
                }
                $('#total_subtotal').text(__currency_trans_from_en(total, true, true));
                __write_number($('input#total_subtotal_input'), total, true);
                __write_number($('input#grand_total_hidden'), total, true);
                __write_number($('input#shipping_charges'), addtional_charges, true);
            }
        }

        function additionalChargesRemove() {
            var total_subtotal = 0;
            var total = 0;

            $('#purchase_entry_table tbody')
                .find('tr')
                .each(function() {
                    var quantity = __read_number($(this).find('.purchase_quantity'), true);
                    total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
                    var suvtotal = quantity * total_quantity;
                    total_subtotal += suvtotal;
                });
            const addtional_charges = 0.00;
            const discount_amt = parseFloat($('#discount_amount').val());
            if (discount_amt != 0) {
                total = total_subtotal - discount_amt;
            } else {
                total = total_subtotal;
            }
            $('#total_subtotal').text(__currency_trans_from_en(total, true, true));
            __write_number($('input#total_subtotal_input'), total, true);
            __write_number($('input#grand_total_hidden'), total, true);
            __write_number($('input#shipping_charges'), addtional_charges, true);
            __write_number($('input#additional_charges'), addtional_charges, true);
        }

        function additionalChargesFun_old() {
            // $(document).on('blur', '#additional_charges', function(e) {
            // const val = parseFloat($(this).val());
            const val = parseFloat($('#additional_charges').val());
            var sub = $("#total_subtotal").html();
            sub = sub.replace(/\$/g, '');
            var total = parseFloat(sub);
            var perc = (val / total) * 100;
            var total_subtotal = 0;


            $('#purchase_entry_table tbody')
                .find('tr')
                .each(function() {
                    total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
                    var addtional_charges = total_quantity * (perc / 100);

                    var quantity = __read_number($(this).find('.purchase_quantity'), true);
                    var purchase_after_tax = quantity * total_quantity;
                    var suvtotal = purchase_after_tax + addtional_charges;
                    total_subtotal += suvtotal;
                    __write_number($(this).find('.inline_discounts'), addtional_charges, true);

                    //custom  changes  //25-05-2021
                    var purchase_unit_cost_amt = addtional_charges + total_quantity;
                    __write_number($(this).find('.purchase_unit_cost'), purchase_unit_cost_amt, true);

                    //Calculate sub totals
                    var sub_total_after_tax = purchase_after_tax + additional_charges;

                    // __write_number($(this).find('.purchase_unit_cost'), suvtotal, true);
                    //__write_number($(this).find('.row_subtotal_after_tax'), suvtotal, true);
                    //$(this).find('.row_subtotal_after_tax').text(suvtotal);
                    $(this).find('.row_subtotal_after_tax').text(
                        __currency_trans_from_en(suvtotal, false, true)
                    );
                    __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);
                    // total_st_before_tax += __read_number(
                    //     $(this).find('.row_subtotal_before_tax_hidden'),
                    //     true
                    // );


                });

            //$('#total_quantity').text(__number_f(total_quantity, false));
            //$('#total_st_before_tax').text(__currency_trans_from_en(total_st_before_tax, true, true));
            //__write_number($('input#st_before_tax_input'), total_st_before_tax, true);

            // $('#total_subtotal').text(__currency_trans_from_en(total_subtotal, true, true));
            // __write_number($('input#total_subtotal_input'), total_subtotal, true);
            // });
        }

        function additionalChargesRemove_old() {
            const val = 0.00;
            var sub = $("#total_subtotal").html();
            sub = sub.replace(/\$/g, '');
            var total = parseFloat(sub);
            var perc = (val / total) * 100;
            var total_subtotal = 0;

            $('#purchase_entry_table tbody')
                .find('tr')
                .each(function() {
                    total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
                    var addtional_charges = total_quantity * (perc / 100);

                    var quantity = __read_number($(this).find('.purchase_quantity'), true);
                    var purchase_after_tax = quantity * total_quantity;
                    var suvtotal = purchase_after_tax + addtional_charges;
                    total_subtotal += suvtotal;
                    __write_number($(this).find('.purchase_unit_cost'), suvtotal, true);
                    __write_number($(this).find('.inline_discounts'), addtional_charges, true);

                    //Calculate sub totals
                    var sub_total_after_tax = purchase_after_tax + additional_charges;

                    $(this).find('.row_subtotal_after_tax').text(
                        __currency_trans_from_en(suvtotal, false, true)
                    );

                    __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);
                    $('#additional_charges').val(0.00);
                });

            $('#discount_show').removeClass('hide');
            $('#discount_show').addClass('show');
        }

        function discountChargesFun() {
            var total_subtotal = 0;
            var total = 0;
            var discount = 0;
            $('#purchase_entry_table tbody')
                .find('tr')
                .each(function() {
                    total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
                    var quantity = __read_number($(this).find('.purchase_quantity'), true);
                    var purchase_after_tax = quantity * total_quantity;
                    total_subtotal += purchase_after_tax;
                });
            $('#total_subtotal').text(__currency_trans_from_en(total_subtotal, true, true));
            __write_number($('input#total_subtotal_input'), total_subtotal, true);


            if (total_subtotal != 0) {
                const addtional_charges = parseFloat($('#additional_charges').val());
                if (addtional_charges) {
                    total_subtotal = total_subtotal + addtional_charges;
                }
                var discount = $('#discount_charges').val();

                if (discount > total_subtotal) {
                    toastr.error('Discount Amount should not be greater than net amount');
                    // alert('Discount Amount should not be greater than net amount');
                    $('#total_subtotal').text(__currency_trans_from_en(total_subtotal, true, true));
                    __write_number($('input#total_subtotal_input'), total_subtotal, true);
                    __write_number($('input#grand_total_hidden'), total_subtotal, true);

                    $('#submit_purchase_form').attr("disabled", true);

                    $('#total_discount').text(__currency_trans_from_en(0.0000, true, true));
                    __write_number($('input#total_discount_input'), 0.0000, true);
                    __write_number($('input#discount_amount'), 0.0000, true);

                } else {
                    total = total_subtotal - discount;
                    $('#total_subtotal').text(__currency_trans_from_en(total, true, true));
                    __write_number($('input#total_subtotal_input'), total, true);
                    __write_number($('input#grand_total_hidden'), total, true);

                    $('#submit_purchase_form').attr("disabled", false);

                    $('#total_discount').text(__currency_trans_from_en(discount, true, true));
                    __write_number($('input#total_discount_input'), discount, true);
                    __write_number($('input#discount_amount'), discount, true);
                }
                // total = total_subtotal - discount;
                //   $('#total_subtotal').text(__currency_trans_from_en(total, true, true));
                //   __write_number($('input#total_subtotal_input'), total, true);
                //   __write_number($('input#grand_total_hidden'), total, true);
            }
            // $('#discount_show').removeClass('hide');
            // $('#discount_show').addClass('show');

            $('#total_discount').text(__currency_trans_from_en(discount, true, true));
            __write_number($('input#total_discount_input'), discount, true);
            __write_number($('input#discount_amount'), discount, true);

            // $('#discount_show').removeClass('hide');
            // $('#discount_show').addClass('show');
            console.log('total_subtotal:', total_subtotal)
            console.log('discount:', discount)
            console.log('total: ', total)

        }

        function discountChargesRemove() {
            var total_subtotal = 0;
            var total = 0;
            var discount = 0;
            $('#purchase_entry_table tbody')
                .find('tr')
                .each(function() {
                    total_quantity = __read_number($(this).find('.purchase_unit_cost_without_discount'), true);
                    var quantity = __read_number($(this).find('.purchase_quantity'), true);
                    var purchase_after_tax = quantity * total_quantity;
                    total_subtotal += purchase_after_tax;
                });

            const addtional_charges = parseFloat($('#additional_charges').val());
            if (addtional_charges) {
                total_subtotal = total_subtotal + addtional_charges;
            }

            $('#total_subtotal').text(__currency_trans_from_en(total_subtotal, true, true));
            __write_number($('input#total_subtotal_input'), total_subtotal, true);
            __write_number($('input#grand_total_hidden'), total_subtotal, true);

            $('#total_discount').text(__currency_trans_from_en(discount, true, true));
            __write_number($('input#total_discount_input'), discount, true);
            __write_number($('input#discount_amount'), discount, true);

            $('#discount_charges').val(0.00);
            console.log('total_subtotal:', total_subtotal)
            console.log('discount:', discount)
            console.log('total: ', total)
        }

        $(document).on('change', '.payment_types_dropdown, #location_id', function(e) {
            var default_accounts = $('select#location_id').length ?
                $('select#location_id')
                .find(':selected')
                .data('default_payment_accounts') : [];
            var payment_types_dropdown = $('.payment_types_dropdown');
            var payment_type = payment_types_dropdown.val();
            var payment_row = payment_types_dropdown.closest('.payment_row');
            var row_index = payment_row.find('.payment_row_index').val();

            var account_dropdown = payment_row.find('select#account_' + row_index);
            if (payment_type && payment_type != 'advance') {
                var default_account = default_accounts && default_accounts[payment_type]['account'] ?
                    default_accounts[payment_type]['account'] : '';
                if (account_dropdown.length && default_accounts) {
                    account_dropdown.val(default_account);
                    account_dropdown.change();
                }
            }

            if (payment_type == 'advance') {
                if (account_dropdown) {
                    account_dropdown.prop('disabled', true);
                    account_dropdown.closest('.form-group').addClass('hide');
                }
            } else {
                if (account_dropdown) {
                    account_dropdown.prop('disabled', false);
                    account_dropdown.closest('.form-group').removeClass('hide');
                }
            }
        });

        $(".modal-dialog").hide();
        $(document).on("click", ".add_new_supplier", function() {
            $(".section-div").show();
            $(".modal-dialog").show();
        });
        $(document).on("click", ".close", function() {
            $(".section-div").hide();
            $(".modal-dialog").hide();
        });

        $('body').on('keydown', 'input, select, textarea', function(e) {
            var self = $(this),
                form = self.parents('form:eq(0)'),
                focusable, next, prev;

            if (e.shiftKey) {
                if (e.keyCode == 107) {
                    focusable = form.find('input,a,select,button,textarea').filter(':visible');
                    prev = focusable.eq(focusable.index(this) - 1);
                    if (prev.length) {
                        prev.focus();
                        console.log('first')
                    } else {
                        form.submit();
                    }
                }
            } else {
                if (e.keyCode == 107) {
                    focusable = form.find('input,a,select,button,textarea').filter(':visible');
                    next = focusable.eq(focusable.index(this) + 1);
                    if (next.length) {
                        var row_id = $(this).data("row");
                        var row_input = $(this).data("input");
                        var i = row_input + 1;
                        if (row_input == 5) {
                            var j = row_id + 1;
                            var next_row = $('#input_' + j + '_box' + 1).val();
                            if (next_row) {
                                document.getElementById('input_' + j + '_box' + 1).focus();
                            } else {
                                document.getElementById('search_product_two').focus();
                            }
                        } else {
                            document.getElementById('input_' + row_id + '_box' + i).focus();
                        }
                    } else {
                        form.submit();
                    }
                    return false;
                }
            }
        });

        $(".add").click(function() {
            $('<div><br><tr ><td><input class="file" name="documents[]" type="file" accept="application/pdf,text/csv,application/zip,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/jpg,image/png" ><span class="rem" ><a href="javascript:void(0);" title="Clear selected files" class="deleteDoc btn btn-danger btn-xs fileinput-remove fileinput-remove-button"><i class="glyphicon glyphicon-trash"></i>  <span class="hidden-xs">Remove</span></span></td></tr></div>')
                .appendTo(".contents");
        });
        $('.contents').on('click', '.rem', function() {
            $(this).parent("div").remove();
        });

        $(".deleteDoc").click(function() {
            var id = $(this).data("id");
            var doc = $(this).data("doc");
            var key = $(this).data("key");
            var token = $("meta[name='csrf-token']").attr("content");
            $.ajax({
                method: 'GET',
                url: '/document/' + id + '/' + doc + '/' + key,
                dataType: 'json',
                data: {
                    id: id,
                    doc: doc,
                    key: key,
                    _token: token
                },
                success: function(result) {
                    console.log('result', result)
                    if (result.success === 1) {
                        document.getElementById("file-" + result.key).style.display = "none";
                        console.log('file:', "file-" + result.key)
                        toastr.success(result.msg);
                        // expense_cat_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function(result) {
                    console.log('result', result)
                }
            });

        });
        $(".deleteDocFirst").click(function() {
            var id = $(this).data("id");
            var token = $("meta[name='csrf-token']").attr("content");
            $.ajax({
                method: 'GET',
                url: '/document-first/' + id,
                dataType: 'json',
                data: {
                    id: id,
                    _token: token
                },
                success: function(result) {
                    console.log('result', result)
                    if (result.success === 1) {
                        document.getElementById("first_document").style.display = "none";
                        toastr.success(result.msg);
                        // expense_cat_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function(result) {
                    console.log('result', result)
                }
            });

        });

        $('.file').on('change', function() {

            const size =
                (this.files[0].size / 1024 / 1024).toFixed(2);

            if (size > 50 || size < 0.000014) {
                alert("File must be maximum size of 50 MB");
            } else {
                $("#output").html('<b>' +
                    'This file size is: ' + size + " MB" + '</b>');
            }
        });

        $(document).ready(function() {
            $(window).keydown(function(event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });

        $(document).on('change', '#ref_no', function() {
            var ref_no = $("#ref_no").val();
            var purchase_id = $("#purchase_id").val();
            var supplier_id = $("#supplier_id").val();
            if (ref_no == '') {
                $(".error").text('');
            }
            $.ajax({
                method: 'POST',
                url: '/purchases/check_ref_number_edit',
                dataType: 'json',
                data: {
                    ref_no: ref_no,
                    purchase_id: purchase_id,
                    supplier_id: supplier_id
                },
                success: function(success) {
                    console.log("success", success);
                    if (success.success == true) {
                        $("#submit_purchase_form").prop("disabled", true);
                        $(".error").text(success.message);
                    } else {
                        $("#submit_purchase_form").prop("disabled", false);
                        $(".error").text(success.message);
                    }
                },
            });
        });
    </script>
    @include('purchase.partials.keyboard_shortcuts')
@endsection
