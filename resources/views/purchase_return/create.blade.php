@extends('layouts.app')
@section('css')
<style>

    /*
    =====
    CORE STYLES
    =====
    */

    .toggle{
    --uiToggleSize: var(--toggleSize, 2.5rem);
    --uiToggleBorderWidth: var(--toggleBorderWidth, 1px);
    --uiToggleColor: #e56666;

    display: var(--toggleDisplay, inline-flex);
    position: relative;
    }

    .toggle__input{
    /*
    The pattern by Sara Soueidan https://www.sarasoueidan.com/blog/inclusively-hiding-and-styling-checkboxes-and-radio-buttons/
    */
    width: var(--uiToggleSize);
    height: var(--uiToggleSize);
    opacity: 0;

    position: absolute;
    top: 0;
    left: 0;
    margin: 0;
    }

    /*
    1. Calculation of the gap for the custom checkbox
    */

    .toggle__label{
    display: inline-flex;
    min-height: var(--uiToggleSize);
    padding-left: calc(var(--uiToggleSize) + var(--toggleIndent, .4em));
    }

    .toggle__input:not(:disabled) ~ .toggle__label{
    cursor: pointer;
    }

    /*
    1. Ems helps to calculate size of the checkbox
    */

    .toggle__label::after{
    content: "";
    box-sizing: border-box;
    width: 1em;
    height: 1em;
    font-size: var(--uiToggleSize); /* 1 */

    background-color: transparent;
    border: var(--uiToggleBorderWidth) solid var(--uiToggleColor);

    position: absolute;
    left: 0;
    top: 0;
    z-index: 2;
    }

    .toggle__input:checked ~ .toggle__label::after{
    background-color: var(--uiToggleColor);
    }

    .toggle__text{
    margin-top: auto;
    margin-bottom: auto;
    }

    /*
    The arrow size and position depends from sizes of square because I needed an arrow correct positioning from the top left corner of the element toggle

    1. Ems helps to calculate size and position of the arrow
    */

    .toggle__label::before{
    content: "";
    width: 0;
    height: 0;
    font-size: var(--uiToggleSize); /* 1 */

    border-left-width: 0;
    border-bottom-width: 0;
    border-left-style: solid;
    border-bottom-style: solid;
    border-color: var(--toggleArrowColor, #fff);

    position: absolute;
    top: .5428em;
    left: .25em;
    z-index: 3;

    transform-origin: left top;
    transform: rotate(-40deg) skew(10deg);
    }

    .toggle__input:checked ~ .toggle__label::before{
    --uiToggleArrowWidth: var(--toggleArrowWidth, 2px);

    width: .4em;
    height: .2em;
    border-left-width: var(--uiToggleArrowWidth);
    border-bottom-width: var(--uiToggleArrowWidth);
    }

    /*
    States
    */

    /* focus state */

    .toggle:focus-within{
    outline: var(--toggleOutlineWidthFocus, 3px) solid var(--toggleOutlineColorFocus, currentColor);
    outline-offset: var(--toggleOutlineOffsetFocus, 5px);
    }

    /* disabled state */

    .toggle__input:disabled ~ .toggle__label{
    opacity: var(--toggleOpacityDisabled, .24);
    cursor: var(--toggleCursorDisabled, not-allowed);
    user-select: none;
    }

    /*
    =====
    PRESENTATION STYLES
    =====
    */

    /*
    The demo skin
    */

    .toggle__label::after{
    border-radius: var(--toggleBorderRadius, 2px);
    }

    /*
    The animation of switching states
    */

    .toggle__input:not(:disabled) ~ .toggle__label::before{
    will-change: width, height;
    opacity: 0;
    }

    .toggle__input:not(:disabled):checked ~ .toggle__label::before{
    opacity: 1;
    transition: opacity .1s ease-out .15s, width .1s ease-out .3s, height .1s ease-out .2s;
    }

    .toggle__input:not(:disabled) ~ .toggle__label::after{
    will-change: background-color;
    transition: background-color .15s ease-out;
    }

    /*
    =====
    SETTINGS
    =====
    */

    .page__custom-settings{
    --toggleColor: #690e90;
    --toggleOutlineColorFocus: #690e90;
    --toggleSize: 2rem;
    }
</style>
@endsection
@section('title', __('Add Vendor Credit Memo'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <br>
        <h1>@lang('Add Vendor Credit Memo')</h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        {!! Form::open(['url' => action('CombinedPurchaseReturnController@save'), 'method' => 'post', 'id' => 'purchase_return_form', 'files' => true ]) !!}
        <div class="box box-solid">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('supplier_id', __('purchase.supplier') . ':*') !!}
                            <div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-user"></i>
							</span>
                                {!! Form::select('contact_id', [], null, ['class' => 'form-control', 'placeholder' => __('messages.please_select'), 'required', 'id' => 'supplier_id']); !!}
                            </div>
                        </div>
                    </div>
                    {{--<div class="col-sm-3">--}}
                    {{--<div class="form-group">--}}
                    {{--{!! Form::label('location_id', __('purchase.business_location').':*') !!}--}}
                    {{--{!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}--}}
                    {{--</div>--}}
                    {{--</div>--}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('ref_no', __('Credit Memo No').':') !!}
                            {!! Form::text('ref_no', null, ['class' => 'form-control']); !!}
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('transaction_date', __('messages.date') . ':*') !!}
                            <div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</span>
                                {!! Form::text('transaction_date', @format_datetime('now'), ['class' => 'form-control', 'readonly', 'required']); !!}
                            </div>
                        </div>
                    </div>
                    {{--<div class="clearfix"></div>--}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('document', __('purchase.attach_document') . ':') !!}
                            {!! Form::file('document', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
                            <p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
                                @includeIf('components.document_help_text')</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <br>
                            <label>
                                {!! Form::checkbox('nfs_items', 1, false,
                                [ 'class' => 'input-icheck', 'id' => 'nfs_items']); !!} Search Inactive
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!--box end-->
        <div class="box box-solid">
            <div class="box-header">
                <h3 class="box-title">{{ __('stock_adjustment.search_products') }}</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-8 col-sm-offset-2">
                        <div class="form-group">
                            <div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-search"></i>
							</span>
                                {!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product_for_purchase_return', 'placeholder' => __('stock_adjustment.search_products')]); !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <input type="hidden" id="product_row_index" value="0">
                    <input type="hidden" id="total_amount" name="final_total" value="0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-condensed"
                               id="purchase_return_product_table">
                            <thead>
                                <tr>
                                    <th class="text-center">Product</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-center">Deduct Inventory</th>
                                    <th class="text-center">Return Qty</th>
                                    <th class="text-center">Unit Cost</th>
                                    <th class="text-center">Loose Pcs Qty</th>
                                    <th class="text-center">Loose Pcs Cost</th>
                                    <th class="text-center">
                                        @lang('sale.subtotal')
                                    </th>
                                    <th class="text-center"><i class="fa fa-trash" aria-hidden="true"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-center" id="tfoot1" colspan="3"></th>
                                    <th class="text-center" id="tfoot2"></th>
                                    <th class="text-center" id="tfoot3"></th>
                                    <th class="text-center" id="tfoot4"></th>
                                    <th class="text-center" id="tfoot5"></th>
                                    <th class="text-center" id="tfoot6"></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div> <!--box end-->
        <div class="box box-solid">
            <div class="box-body">
                <div class="row">
                    {{-- <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('tax_id', __('purchase.purchase_tax') . ':') !!}
                            <select name="tax_id" id="tax_id" class="form-control select2"
                                    placeholder="'Please Select'">
                                <option value="" data-tax_amount="0" data-tax_type="fixed"
                                        selected>@lang('lang_v1.none')</option>
                                @foreach($taxes as $tax)
                                    <option value="{{ $tax->id }}" data-tax_amount="{{ $tax->amount }}"
                                            data-tax_type="{{ $tax->calculation_type }}">{{ $tax->name }}</option>
                                @endforeach
                            </select>
                            {!! Form::hidden('tax_amount', 0, ['id' => 'tax_amount']); !!}
                        </div>
                    </div> --}}
                    <div class="col-md-6">
						<div class="form-group">
					        <div class="box-body">
				              <div class="row">
				                <div class="col-xs-4">
					                {!! Form::label('shipping_charges', __('Shipping Charges').':') !!}
									{!! Form::number('shipping_charges', null, ['class' => 'form-control', 'id' => 'shipping_charges']); !!}
				                </div>

				                <div class="col-xs-4">
					                {!! Form::label('discount_amount', __('Discount').':') !!}
									{!! Form::number('discount_amount', null, ['class' => 'form-control', 'id' => 'discount_amount']); !!}
									{!! Form::hidden('discount_type', 'fixed', ['class' => 'form-control input_number', 'id' => 'discount_type']); !!}
				                </div>

				                <div class="col-xs-4">
                                    <div class="form-group">
                                        <label>@lang('lang_v1.box_qty')</label>
                                        <input type="text" name="box_qty" value="{{$purchase_return->box_qty ?? 0}}" class="form-control" required/>
                                    </div>
                                </div>

				              </div>

				            </div>

				        </div>
					</div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('additional_notes', __( 'Note' ) . ':') !!}
                            {!! Form::textarea('additional_notes', null , ['class' => 'form-control', 'rows' => 2]); !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="pull-right"><b>@lang('stock_adjustment.total_amount'):</b> <span id="total_return">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!--box end-->
        <div class="row">
            <div class="col-md-12">
                <button type="button" id="submit_purchase_return_form"
                        class="btn btn-primary pull-right btn-flat">@lang('messages.submit')</button>
            </div>
        </div>
        {!! Form::close() !!}
    </section>
@stop
@section('javascript')
    <script src="{{ asset('js/purchase_return.js?rev='.date('YmdHi').'&v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        __page_leave_confirmation('#purchase_return_form');
    </script>
@endsection
