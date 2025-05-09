 <!-- START Changed Products Table -->
                @if($productLogs->where('neat_txn_id', $txnLog->id)->where('activity_title', 'Changed')->isNotEmpty())
                    @php
                        $showQty = false;
                        $showNewQty = false;
                        $showPrice = false;
                        $showNewPrice = false;
                        $showTax = false;
                        $showNewTax = false;
    
                        foreach ($productLogs->where('neat_txn_id', $txnLog->id)->where('activity_title', 'Changed') as $log) {
                            if ($log->qty !== null) $showQty = true;
                            if ($log->new_qty !== null) $showNewQty = true;
                            if ($log->price !== null) $showPrice = true;
                            if ($log->new_price !== null) $showNewPrice = true;
                            if ($log->tax_amount !== null) $showTax = true;
                            if ($log->new_tax_amount !== null) $showNewTax = true;
                        }
                    @endphp
                    <table class="table bg-white">
                        <thead>
                            <tr class="bg-green">
                                <th>#</th>
                                <th>Product</th>
                                @if($showQty) <th width="10%">Qty</th> @endif
                                @if($showNewQty) <th width="10%">New Qty</th> @endif
                                @if($showPrice) <th width="10%">Unit Price</th> @endif
                                @if($showNewPrice) <th width="10%">New Unit Price</th> @endif
                                @if($showTax) <th width="10%">Tax</th> @endif
                                @if($showNewTax) <th width="10%">New Tax</th> @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productLogs->where('neat_txn_id', $txnLog->id)->where('activity_title', 'Changed') as $index => $log)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $log->product->name }} {{ $log->product->sku }}</td>
                                    @if($showQty) <td>{{ $log->qty }}</td> @endif
                                    @if($showNewQty) <td>{{ $log->new_qty }}</td> @endif
                                    @if($showPrice) <td>{{ $log->price !== null ? '$' . number_format($log->price, 2) : '' }}
                                        @if($log->price_difference == 1)<span style="color:#2fb344;">&#x2191;</span> 
                                        @elseif($log->price_difference == -1)<span style="color:#d63939;">&#x2193;</span> @endif
                                    </td> @endif
                                    @if($showNewPrice) 
                                    <td class="nowraper">{{ $log->new_price !== null ? '$' . number_format($log->new_price, 2) : '' }}
                                        @if($log->new_price_difference == 1)<span style="color:#2fb344;">&#x2191;</span> 
                                        @elseif($log->new_price_difference == -1) <span style="color:#d63939;">&#x2193; </span> @endif
                                                @if(isset($log->pre_loaded_price) && isset($log->new_price))
                                                    @if(number_format($log->pre_loaded_price, 2) != number_format($log->new_price, 2))
                                                    <span class="tooltip">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-edit-circle">
                                                      <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                      <path d="M12 15l8.385 -8.415a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3z" />
                                                      <path d="M16 5l3 3" />
                                                      <path d="M9 7.07a7 7 0 0 0 1 13.93a7 7 0 0 0 6.929 -6" />
                                                    </svg>
                                                       <span class="tooltiptext">{{ '$' . number_format($log->pre_loaded_price, 2) }}</span>
                                                    </span>
                                                    @endif
                                                @endif
                                    </td> @endif
                                    @if($showTax) <td>{{ $log->tax_amount !== null ? '$' . number_format($log->tax_amount, 2) : '' }}</td> @endif
                                    @if($showNewTax) <td>{{ $log->new_tax_amount !== null ? '$' . number_format($log->new_tax_amount, 2) : '' }}</td> @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            <!-- END Changed Products Table -->

            <!-- START Added and Removed Products Table -->
                @if($productLogs->where('neat_txn_id', $txnLog->id)->whereIn('activity_title', ['Added', 'Removed'])->isNotEmpty())
                    <table class="table bg-white">
                        <thead>
                            <tr class="bg-green">
                                <th>#</th>
                                <th>Product</th>
                                <th width="10%">Qty</th>
                                <th width="10%">Unit Price</th>
                                <th width="10%">Tax</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productLogs->where('neat_txn_id', $txnLog->id)->whereIn('activity_title', ['Added', 'Removed']) as $index => $log)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $log->product->name }} {{ $log->product->sku }}</td>
                                    <td>{{ $log->qty }}</td>
                                    <td class="nowraper" style="{{ $log->is_last_selling_price == 1 ? 'background-color: #f8d7da;' : '' }}">
                                        {{ $log->price !== null ? '$' . number_format($log->price, 2) : '' }}
                                            @if($log->price_difference == 1) <span style="color:#2fb344;">&#x2191;</span> 
                                            @elseif($log->price_difference == -1) <span style="color:#d63939;">&#x2193;</span> @endif
                                                @if(isset($log->pre_loaded_price) && isset($log->price))
                                                    @if(number_format($log->pre_loaded_price, 2) != number_format($log->price, 2))
                                                    <span class="tooltip">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-edit-circle">
                                                      <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                      <path d="M12 15l8.385 -8.415a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3z" />
                                                      <path d="M16 5l3 3" />
                                                      <path d="M9 7.07a7 7 0 0 0 1 13.93a7 7 0 0 0 6.929 -6" />
                                                    </svg>
                                                       <span class="tooltiptext">{{ '$' . number_format($log->pre_loaded_price, 2) }}</span>
                                                    </span>
                                                    @endif
                                                @endif
                                    </td>
                                    <td>{{ $log->tax_amount !== null ? '$' . number_format($log->tax_amount, 2) : '' }}</td>
                                    <td>{{ $log->activity_title }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            <!-- END Added and Removed Products Table -->