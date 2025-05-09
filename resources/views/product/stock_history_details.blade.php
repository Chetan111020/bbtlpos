<div class="row">
    <div class="col-md-12">
        <h4>{{ $stock_details['variation'] }}</h4>
    </div>
    <div class="col-md-4 col-xs-4">
        <strong>@lang('lang_v1.quantities_in')</strong>
        <table class="table table-condensed">
            <tr>
                <th>@lang('report.total_purchase')</th>
                <td>
                    <span class="display_currency" data-is_quantity="true">{{ $stock_details['total_purchase'] }}</span>
                    {{ $stock_details['unit'] }}
                </td>
            </tr>
            <tr>
                <th>@lang('report.total_consignment')</th>
                <td>
                    <span class="display_currency"
                        data-is_quantity="true">{{ $stock_details['total_consignment_qnty'] ?? 0 }}</span>
                    {{ $stock_details['unit'] }}
                </td>
            </tr>
            <tr>
                <th>@lang('lang_v1.opening_stock')</th>
                <td>
                    <span class="display_currency"
                        data-is_quantity="true">{{ $stock_details['total_opening_stock'] }}</span>
                    {{ $stock_details['unit'] }}
                </td>
            </tr>
            <tr>
                <th>Credit Memo Total</th>
                <td>
                    <span class="display_currency"
                        data-is_quantity="true">{{ $stock_details['total_sell_return'] }}</span>
                    {{ $stock_details['unit'] }}
                </td>
            </tr>
            <tr style="display:none;">
                <th>@lang('lang_v1.stock_transfers') (@lang('lang_v1.in'))</th>
                <td>
                    <span class="display_currency"
                        data-is_quantity="true">{{ $stock_details['total_purchase_transfer'] }}</span>
                    {{ $stock_details['unit'] }}
                </td>
            </tr>
        </table>
    </div>
    <div class="col-md-4 col-xs-4">
        <strong>@lang('lang_v1.quantities_out')</strong>
        @php $totals = getTotalSold($stock_details['variation_id']);  @endphp
        <table class="table table-condensed">
            <tr>
                <th>@lang('lang_v1.total_sold')</th>
                <td>
                    <span class="display_currency" data-is_quantity="true">{{ $totals['total_sold'] }}</span>
                    {{ $stock_details['unit'] }}
                </td>
            </tr>
            <tr>
                <th>@lang('report.total_stock_adjustment')</th>
                <td>
                    <span class="display_currency" data-is_quantity="true">{{ $totals['total_adjusted'] }}</span>
                    {{ $stock_details['unit'] }}
                </td>
            </tr>
            <tr>
                <th>@lang('lang_v1.total_purchase_return')</th>
                <td>
                    <span class="display_currency"
                        data-is_quantity="true">{{ $stock_details['total_purchase_return'] }}</span>
                    {{ $stock_details['unit'] }}
                </td>
            </tr>

            <tr style="display:none;">
                <th>@lang('lang_v1.stock_transfers') (@lang('lang_v1.out'))</th>
                <td>
                    <span class="display_currency"
                        data-is_quantity="true">{{ $stock_details['total_sell_transfer'] }}</span>
                    {{ $stock_details['unit'] }}
                </td>
            </tr>
        </table>
    </div>

    <div class="col-md-4 col-xs-4">
        <strong>@lang('lang_v1.totals')</strong>
        <table class="table table-condensed">
            <tr>
                <th>@lang('report.current_stock')</th>
                <td>
                    <span class="display_currency" data-is_quantity="true">{{ $stock_details['current_stock'] }}</span>
                    {{ $stock_details['unit'] }}
                </td>
            </tr>
            <tr>
                <th>Total Sold <small>(Last 1 Month)</small></th>
                <td>
                    <span class="display_currency"
                        data-is_quantity="true">{{ $stock_details['total_sold_last_1_month'] }}</span>
                    {{ $stock_details['unit'] }}
                </td>
            </tr>
            <tr>
                <th>Total Sold <small>(Last 2 Month)</small></th>
                <td>
                    <span class="display_currency"
                        data-is_quantity="true">{{ $stock_details['total_sold_last_2_months'] }}</span>
                    {{ $stock_details['unit'] }}
                </td>
            </tr>
            <tr>
                <th>Total Sold <small>(Last 3 Month)</small></th>
                <td>
                    <span class="display_currency"
                        data-is_quantity="true">{{ $stock_details['total_sold_last_3_months'] }}</span>
                    {{ $stock_details['unit'] }}
                </td>
            </tr>
        </table>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="type">@lang('lang_v1.type'):</label>
            <select class="select2 form-control" name="type" id="type">
                <option value="">All</option>
                <option value="sell" @if ($type == 'sell') selected @endif>Sales</option>
                <option value="purchase" @if ($type == 'purchase') selected @endif>Purchase</option>
                <option value="stock_adjustment" @if ($type == 'stock_adjustment') selected @endif>Inventory Adjustment
                </option>
                <option value="sell_return" @if ($type == 'sell_return') selected @endif>Credit Memo</option>
                <option value="purchase_return" @if ($type == 'purchase_return') selected @endif>Vendor Return</option>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <table class="table table-slim" id="stock_history_table">
            <thead>
                <tr>
                    <th>@lang('lang_v1.type')</th>
                    <th>@lang('lang_v1.quantity_change')</th>
                    <th>@lang('lang_v1.new_quantity')</th>
                    <th>@lang('lang_v1.date')</th>
                    <th>@lang('purchase.ref_no')</th>
                </tr>
            </thead>
            <tbody>

                @forelse($stock_history as $history)
                    <tr>
                        @if ($history['type_label'] == 'Sell Return')
                            <td>Credit Memo</td>
                        @else
                            <td>{{ $history['type_label'] }}</td>
                        @endif

                        <td>
                            @if ($history['quantity_change'] > 0)
                                +
                                <span class="display_currency"
                                    data-is_quantity="true">{{ $history['quantity_change'] }}</span>
                            @else
                                <span class="display_currency"
                                    data-is_quantity="true">{{ $history['quantity_change'] }}</span>
                            @endif
                        </td>
                        <td><span class="display_currency" data-is_quantity="true">{{ $history['stock'] }}</span></td>
                        <td>{{ @format_datetime($history['date']) }}</td>
                        @if ($history['type'] == 'sell')
                            @if (empty($history['supplier_business_name']))
                                <td><a href="#"
                                        data-href="{{ action('SellController@show', $history['transaction_id']) }}"
                                        class="btn-modal" data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['company_name'] }} :<br> {{ $history['dba_name'] }}</a></td>
                            @else
                                <td><a href="#"
                                        data-href="{{ action('SellController@show', $history['transaction_id']) }}"
                                        class="btn-modal" data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['supplier_business_name'] }} :<br> {{ $history['dba_name'] }}</a>
                                </td>
                            @endif
                        @elseif ($history['type'] == 'purchase')
                            @if (empty($history['supplier_business_name']))
                                <td><a href="#"
                                        data-href="{{ action('PurchaseController@show', $history['transaction_id']) }}"
                                        class="btn-modal" data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['company_name'] }} : {{ $history['dba_name'] }}</a></td>
                            @else
                                <td><a href="#"
                                        data-href="{{ action('PurchaseController@show', $history['transaction_id']) }}"
                                        class="btn-modal" data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['supplier_business_name'] }} :<br> {{ $history['dba_name'] }}</a>
                                </td>
                            @endif
                        @elseif($history['type'] == 'purchase_transfer')
                            @if (empty($history['supplier_business_name']))
                                <td><a href="#"
                                        data-href="{{ action('SellReturnController@show', $history['transaction_id']) }}"
                                        class="btn-modal" data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['company_name'] }} : {{ $history['dba_name'] }}</a></td>
                            @else
                                <td><a href="#"
                                        data-href="{{ action('SellReturnController@show', $history['transaction_id']) }}"
                                        class="btn-modal" data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['supplier_business_name'] }} :<br> {{ $history['dba_name'] }}</a>
                                </td>
                            @endif
                        @elseif($history['type'] == 'stock_adjustment')
                            <td><a href="#"
                                    data-href="{{ action('StockAdjustmentController@show', $history['transaction_id']) }}"
                                    class="btn-modal" data-container=".view_modal">{{ $history['ref_no'] }}</a></td>
                            {{--  @if (empty($history['supplier_business_name']))	
							<td><a href="#" data-href="{{action('StockAdjustmentController@show', $history['transaction_id'])}}" class="btn-modal" data-container=".view_modal">{{$history['ref_no']}} : {{$history['company_name']}} :<br> {{$history['dba_name']}}</a></td>
						@else
							<td><a href="#" data-href="{{action('StockAdjustmentController@show', $history['transaction_id'])}}" class="btn-modal" data-container=".view_modal">{{$history['ref_no']}} : {{$history['supplier_business_name']}} :<br> {{$history['dba_name']}}</a></td>
						@endif  --}}
                        @elseif($history['type'] == 'opening_stock')
                            <td>-</td>
                            {{--  @if (empty($history['supplier_business_name']))	
							<td><a href="#" data-href="{{action('PurchaseController@show', $history['transaction_id'])}}" class="btn-modal" data-container=".view_modal">{{$history['ref_no']}} : {{$history['company_name']}} :<br> {{$history['dba_name']}}</a></td>
						@else
							<td><a href="#" data-href="{{action('PurchaseController@show', $history['transaction_id'])}}" class="btn-modal" data-container=".view_modal">{{$history['ref_no']}} : {{$history['supplier_business_name']}} :<br> {{$history['dba_name']}}</a></td>
						@endif  --}}
                        @elseif($history['type'] == 'sell_transfer')
                            @if (empty($history['supplier_business_name']))
                                <td><a href="#" class="btn-modal"
                                        data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['company_name'] }} :<br> {{ $history['dba_name'] }}</a></td>
                            @else
                                <td><a href="#" class="btn-modal"
                                        data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['supplier_business_name'] }} :<br> {{ $history['dba_name'] }}</a>
                                </td>
                            @endif
                        @elseif($history['type'] == 'purchase_transfer')
                            @if (empty($history['supplier_business_name']))
                                <td><a href="#" class="btn-modal"
                                        data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['company_name'] }} : {{ $history['dba_name'] }}</a></td>
                            @else
                                <td><a href="#" class="btn-modal"
                                        data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['supplier_business_name'] }} :<br> {{ $history['dba_name'] }}</a>
                                </td>
                            @endif
                        @elseif($history['type'] == 'production_purchase')
                            @if (empty($history['supplier_business_name']))
                                <td><a href="#" class="btn-modal"
                                        data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['company_name'] }} :<br> {{ $history['dba_name'] }}</a></td>
                            @else
                                <td><a href="#" class="btn-modal"
                                        data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['supplier_business_name'] }} :<br> {{ $history['dba_name'] }}</a>
                                </td>
                            @endif
                        @elseif($history['type'] == 'purchase_return')
                            @if (empty($history['supplier_business_name']))
                                <td><a href="#"
                                        data-href="{{ action('PurchaseReturnController@show', $history['transaction_id']) }}"
                                        class="btn-modal" data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['company_name'] }} :<br> {{ $history['dba_name'] }}</a></td>
                            @else
                                <td><a href="#"
                                        data-href="{{ action('PurchaseReturnController@show', $history['transaction_id']) }}"
                                        class="btn-modal" data-container=".view_modal">{{ $history['ref_no'] }} :
                                        {{ $history['supplier_business_name'] }} :<br> {{ $history['dba_name'] }}</a>
                                </td>
                            @endif
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">
                            @lang('lang_v1.no_stock_history_found')
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
