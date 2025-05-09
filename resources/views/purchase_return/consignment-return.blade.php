@extends('layouts.app')
@include('purchase_return.consignment-style')
@section('title', __('Add Vendor Credit Memo'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <br>
        <h1>@lang('Add Vendor Credit Memo')</h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        {!! Form::open([
            'url' => action('CombinedPurchaseReturnController@save'),
            'method' => 'post',
            'id' => 'purchase_return_form',
            'files' => true,
        ]) !!}
        <div class="box box-solid">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('supplier_id', __('purchase.supplier') . ':*') !!}
                            {!! Form::hidden('contact_id', $purchaseData->contact_id, ['id' => 'supplier_id']) !!}
                            <p class="form-control-static">
                                {{ $purchaseData->contact->name ?? '' }}
                            </p>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('ref_no', __('Credit Memo No') . ':') !!}
                            {!! Form::text('ref_no', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('transaction_date', __('messages.date') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('transaction_date', @format_datetime('now'), ['class' => 'form-control', 'readonly', 'required']) !!}
                            </div>
                        </div>
                    </div>
                    {{-- <div class="clearfix"></div> --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('document', __('purchase.attach_document') . ':') !!}
                            {!! Form::file('document', [
                                'id' => 'upload_document',
                                'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types'))),
                            ]) !!}
                            <p class="help-block">@lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000])
                                @includeIf('components.document_help_text')</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <br>
                            <label>
                                {!! Form::checkbox('nfs_items', 1, false, ['class' => 'input-icheck', 'id' => 'nfs_items']) !!} Search Inactive
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
                                {!! implode('', $purchase) !!}
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
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-xs-4">
                                        {!! Form::label('shipping_charges', __('Shipping Charges') . ':') !!}
                                        {!! Form::number('shipping_charges', null, ['class' => 'form-control', 'id' => 'shipping_charges']) !!}
                                    </div>

                                    <div class="col-xs-4">
                                        {!! Form::label('discount_amount', __('Discount') . ':') !!}
                                        {!! Form::number('discount_amount', null, ['class' => 'form-control', 'id' => 'discount_amount']) !!}
                                        {!! Form::hidden('discount_type', 'fixed', ['class' => 'form-control input_number', 'id' => 'discount_type']) !!}
                                    </div>

                                    <div class="col-xs-4">
                                        <div class="form-group">
                                            <label>@lang('lang_v1.box_qty')</label>
                                            <input type="text" name="box_qty"
                                                value="{{ $purchase_return->box_qty ?? 0 }}" class="form-control"
                                                required />
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('additional_notes', __('Note') . ':') !!}
                            {!! Form::textarea('additional_notes', null, ['class' => 'form-control', 'rows' => 2]) !!}
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
    <script src="{{ asset('js/consignment-return.js?rev=' . date('YmdHi') . '&v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        __page_leave_confirmation('#purchase_return_form');
    </script>
@endsection
