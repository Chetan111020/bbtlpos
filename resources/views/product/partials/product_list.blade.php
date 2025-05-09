@php
    $colspan = 11;
    //$custom_labels = json_decode(session('business.custom_labels'), true);
@endphp
<div class="table-responsive">
    <table class="table table-bordered table-striped ajax_view hide-footer" id="product_table">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all-row" ></th>
                <th >@lang('messages.action')</th>
                <th>Product Image</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Not for Selling</th>
                <th>@lang('product.sku')</th>



                @can('view_purchase_price')
                    @php
                        $colspan++;
                    @endphp
                    <th>@lang('lang_v1.unit_perchase_price')</th>
                @endcan
                @can('access_default_selling_price')
                    @php
                        $colspan++;
                    @endphp
                    <th>@lang('lang_v1.selling_price')</th>
                    <th>Tier&nbsp;LI Price</th>
                    <th>Tier&nbsp;2 Price</th>
                    <th>Tier&nbsp;3 Price</th>
                @endcan
                 <th>Gross Profit %</th>
                <th>@lang('report.current_stock')</th>
                  <th>Item Location</th>
                  <th>Qty in Box</th>
                  <th>ML</th>

                <th>@lang('product.category')</th>
                <th>Vendor</th>
                 <th>Sub Category</th>

{{--                <th>@lang('product.tax')</th>--}}
                {{--<th>Gross Profit %</th>--}}
                {{--<th>Qty in Box</th>--}}
                {{--<th>Sub Category</th>--}}
                <th>Case Qty</th>
                <th>Barcode 2</th>
                <th>Barcode 3</th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
            <tr>
                <td colspan="{{$colspan}}">
                <div style="display: flex; width: 100%;">
                    @can('product.delete')
                        {!! Form::open(['url' => action('ProductController@massDestroy'), 'method' => 'post', 'id' => 'mass_delete_form' ]) !!}
                        {!! Form::hidden('selected_rows', null, ['id' => 'selected_rows']); !!}
                        {!! Form::submit(__('lang_v1.delete_selected'), array('class' => 'btn btn-xs btn-danger', 'id' => 'delete-selected')) !!}
                        {!! Form::close() !!}
                    @endcan
                    @can('product.update')
                    &nbsp;
                        {!! Form::open(['url' => action('ProductController@bulkEdit'), 'method' => 'post', 'id' => 'bulk_edit_form' ]) !!}
                        {!! Form::hidden('selected_products', null, ['id' => 'selected_products_for_edit']); !!}
                        <button type="button" id="edit-selected" class="btn btn-xs btn-primary"><a href="javascript:void(0)"   style="color:white"><i class="fa fa-edit"></i>{{__('lang_v1.bulk_edit')}}</a></button>
                        {{--  <button type="submit" class="btn btn-xs btn-primary" style="display:none;" id="edit-selected"> <i class="fa fa-edit"></i>{{__('lang_v1.bulk_edit')}}</button>  --}}
                        {!! Form::close() !!}
                        &nbsp;
                        <button type="button" class="btn btn-xs btn-success update_product_location" data-type="add">@lang('lang_v1.add_to_location')</button>
                        &nbsp;
                        <button type="button" class="btn btn-xs bg-navy update_product_location" data-type="remove">@lang('lang_v1.remove_from_location')</button>
                    @endcan
                    &nbsp;
                    {!! Form::open(['url' => action('ProductController@massDeactivate'), 'method' => 'post', 'id' => 'mass_deactivate_form' ]) !!}
                    {!! Form::hidden('selected_products', null, ['id' => 'selected_products']); !!}
                    {!! Form::submit(__('lang_v1.deactivate_selected'), array('class' => 'btn btn-xs btn-warning', 'id' => 'deactivate-selected')) !!}
                    {!! Form::close() !!} @show_tooltip(__('lang_v1.deactive_product_tooltip'))
                    @can('woocommerce.sync_products')
                        <button type="button" id="sync-selected" class="btn btn-xs sync-selected" style="margin:0 10px;background: aquamarine;">Sync With Website</button>
                    @endcan
                    @if(auth()->user()->username == "empadmin")
                        <button type="button" id="" class="btn btn-xs rmbg-selected" style="border:none;margin:0 10px;background:linear-gradient(to right, #6af5ff, #ffffff);">Remove Background</button>
                        @if(isset($remaining_credits))
                            <small>Remaining Credits: {{ $remaining_credits }}</small>
                        @endif
                    @endif
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

