<style>
    .tooltip {
    position: relative;
    display: inline-block;
    cursor: pointer;
    opacity: 1 !important;
}

.tooltip .tooltiptext {
    visibility: hidden;
    width: auto;
    background-color: #d9f2ff;
    color: #075985;
    border:1px solid #075985;
    text-align: center;
    padding: 5px;
    border-radius: 4px;
    
    /* Position the tooltip */
    position: absolute;
    z-index: 1;
    bottom: 125%; /* Position above the SVG */
    left: 50%;
    margin-left: -60px; /* Center the tooltip */

    /* Fade in tooltip */
    opacity: 1 !important;
    transition: opacity 0.3s;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}
.nowraper{
    text-wrap:nowrap;
}
</style>

@if($txnLogs->isNotEmpty())
    @foreach($txnLogs as $txnLog)
        <details class="v2_acc_details" style="margin-bottom:15px;">
            <summary class="v2_summary">
                {{ $txnLog->activity_title }} by {{ $txnLog->user ? $txnLog->user->first_name : 'Unknown' }} at {{ $txnLog->created_at->format('F j, Y h:i a') }}
                @if($infoLogs->where('neat_txn_id', $txnLog->id)->isNotEmpty())
                    @foreach($infoLogs->where('neat_txn_id', $txnLog->id) as $log)
                        @if($log->new_final_total !== null && $log->final_total !== null)
                            <span style="float: right;">Total Payable: {{ '$' . number_format($log->new_final_total, 2) }}</span>
                            @break
                        @elseif($log->final_total !== null)
                            <span style="float: right;">Total Payable: {{ '$' . number_format($log->final_total, 2) }}</span>
                            @break
                        @elseif($log->new_final_total == null && $log->final_total == null)
                            <span style="float: right; color:#7c7c7c;">No Change in Total Payable</span>
                        @endif
                    @endforeach
                @endif
            </summary>
            <div class="content">
                
            <!-- START Products Table -->
                @include('sale_pos.organized_logs.product_logs')
            <!-- END Products Table -->

            <!-- START Information Table -->
                @include('sale_pos.organized_logs.info_logs')
            <!-- END Information Table -->

            </div>
        </details>
    @endforeach
@else
    <p>New Log Not Recorded For This Invoice.</p>
@endif