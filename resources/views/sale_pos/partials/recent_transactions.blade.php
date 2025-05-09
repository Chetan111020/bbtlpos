@php
	$subtype = '';
@endphp
@if(!empty($transaction_sub_type))
	@php
		$subtype = '?sub_type='.$transaction_sub_type;
	@endphp
@endif

@if(!empty($transactions))
	<table class="table table-slim no-border">
		@foreach ($transactions as $transaction)
			<tr class="cursor-pointer" 
	    		title="Customer: {{optional($transaction->contact)->name}} 
		    		@if(!empty($transaction->contact->mobile) && $transaction->contact->is_default == 0)
		    			<br/>Mobile: {{$transaction->contact->mobile}}
		    		@endif
	    		" >
				<td>
					{{ $loop->iteration}}.
				</td>
				<td>
					<a href="#" data-href="{{ action('SellController@show', [$transaction->id] ) }}" class="btn-modal" data-container=".view_modal">
					{{ $transaction->invoice_no }} ({{optional($transaction->contact)->name}})
					@if(!empty($transaction->table))
						- {{$transaction->table->name}}
					@endif
					</a>
				</td>
				<td class="display_currency">
					{{ $transaction->final_total }}
				</td>
				@if(!isset($customer_id) && !$customer_id)
				<td>
					<a href="{{action('SellPosController@edit', [$transaction->id]).$subtype}}">
	    				<i class="fas fa-pen text-muted" aria-hidden="true" title="{{__('lang_v1.click_to_edit')}}"></i>
	    			</a>
	    			
	    			<a href="{{action('SellPosController@destroy', [$transaction->id])}}" class="delete-sale" style="padding-left: 20px; padding-right: 20px"><i class="fa fa-trash text-danger" title="{{__('lang_v1.click_to_delete')}}"></i></a>

	    			<a href="{{action('SellPosController@printInvoice', [$transaction->id])}}" class="print-invoice-link">
	    				<i class="fa fa-print text-muted" aria-hidden="true" title="{{__('lang_v1.click_to_print')}}"></i>
	    			</a>
				</td>
				@endif
			</tr>
		@endforeach
	</table>
@else
	<p>@lang('sale.no_recent_transactions')</p>
@endif