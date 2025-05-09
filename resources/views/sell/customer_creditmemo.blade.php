<!-- app css -->
<div class="row">
  <div class="col-md-12">
    <div class="table-responsive">
        @if($sell_return)
        <table class="table table-bordered table-striped" style="margin-top: 20px">
        	<thead>
        		<tr class="row-border blue-heading">
        		    <th>@lang('messages.action')</th>
                    <th>@lang('Credit Memo No')</th>
                    <th>@lang('Returned Quantity')</th>
                    <th>@lang('sale.location')</th>
                    <th>@lang('sale.total_amount')</th>
                    <th>@lang('purchase.payment_due')</th>
                    
        		</tr>	
        	</thead>
        	<tbody>
            
        		@foreach($sell_return as  $data)
        			<tr>
        			    <td>
                             <a href="#" class="print-invoice" data-href="{{action('SellController@printCreditMemoInvoice', [$data->id])}}"><i class="fa fa-print"></i> @lang("messages.print")</a>
        				</td>
        				<td>{{$data->invoice_no}}</td>
        				@php $quantity_returned = $data->sell_lines->sum('quantity_returned') + $data->sell_lines->sum('gar_box_return_qty') @endphp
        				<td><?php echo number_format($quantity_returned); ?></td>
        				<td>{{$data->location->name}}</td>
        				<td>$ {{$data->final_total}}</td>
        				@php $due = $data->final_total - $data->amount_paid; @endphp
        				<td>$ {{$due}}</td>	
        			</tr>
        		@endforeach
        	</tbody>
        	<tfoot>
        		<tr class="bg-gray font-17 text-center footer-total">
        			<td colspan="3" class="text-right"><strong>@lang('sale.total'):</strong></td>
        			<td id="footer_payment_status_count_sr">
        				@if($due_payment > 0)
        					<span>Due- {{$due_payment}}</span> <br> 
        				@endif
        				@if($partial_payment > 0)
        					<span>Partial- {{$partial_payment}}</span><br> 
        				@endif
        				@if($paid_payment > 0)
        					<span>Paid- {{$paid_payment}}</span> </td>
        				@endif
        			<td class="text-left"><span class="display_currency"data-currency_symbol ="true">{{$currency->symbol.''.$total_paid}}</span></td>
        			<td class="text-left"><span class="display_currency" data-currency_symbol ="true">{{$currency->symbol.''.$total_due}}</span></td>
        		</tr>
        	</tfoot>
        </table>
        @endif
    </div>
  </div>
</div>
