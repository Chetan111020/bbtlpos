<div class="modal-dialog modal-xl" role="document">
	<div class="modal-content">
		<div class="modal-header">
		    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		    <h4 class="modal-title" id="modalTitle"> @lang('lang_v1.stock_adjustment_details') (<b>@lang('purchase.ref_no'):</b> #{{ $stock_adjustment->ref_no }})
		    </h4>
		</div>
		<div class="modal-body">
		  	<div class="row">
			    <div class="col-sm-12">
			      <p class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($stock_adjustment->transaction_date) }}</p>
			    </div>
			</div>
			<div class="row invoice-info">
				<div class="col-sm-4 invoice-col">
    				@lang('business.business'):
			     	 <address>
			        <strong>{{ $stock_adjustment->business->name }}</strong>
			        {{ $stock_adjustment->location->name }}
			        @if(!empty($stock_adjustment->location->landmark))
			          <br>{{$stock_adjustment->location->landmark}}
			        @endif
			        @if(!empty($stock_adjustment->location->city) || !empty($stock_adjustment->location->state) || !empty($stock_adjustment->location->country))
			          <br>{{implode(',', array_filter([$stock_adjustment->location->city, $stock_adjustment->location->state, $stock_adjustment->location->country]))}}
			        @endif
			        @if(!empty($stock_adjustment->location->mobile))
			          <br>@lang('contact.mobile'): {{$stock_adjustment->location->mobile}}
			        @endif
			        @if(!empty($stock_adjustment->location->email))
			          <br>@lang('business.email'): {{$stock_adjustment->location->email}}
			        @endif
			      </address>
			    </div>
			    <div class="col-sm-4 invoice-col">
			      	<b>@lang('purchase.ref_no'):</b> #{{ $stock_adjustment->ref_no }}<br/>
			      	<b>@lang('messages.date'):</b> {{ @format_date($stock_adjustment->transaction_date) }}<br/>
			      	<b>@lang('stock_adjustment.adjustment_type'):</b> {{ __('stock_adjustment.' . $stock_adjustment->adjustment_type) }}<br>
			      	<b>@lang('stock_adjustment.reason_for_stock_adjustment'):</b> {{ $stock_adjustment->additional_notes }}<br>
                    <b>Added by:</b> {{ $stock_adjustment->added_by }}<br>
			    </div>
    		</div>
    		<div class="row invoice-info">
				<div class="col-sm-12 invoice-col">
					<span><b>View Files:</b></span>
					@if($stock_adjustment->document_path)
					<tr>
						<td>
							<div class="btn-group">
								<a href="{{$stock_adjustment->document_path}}" class="pull-left btn btn-primary" target="_blank" title="View Document" >
								<i class="fas fa-eye"></i>
								</a>
								<a href="{{$stock_adjustment->document_path}}" download="{{$stock_adjustment->document_name}}" class="btn btn-primary no-print" title="Download">
								<i class="fa fa-download"></i>
								</a>
							</div>
						</td>
					</tr>
					@endif
					@if($stock_adjustment->extra_document && count($stock_adjustment->extra_document) > 0)
					<tr>
						@forelse ($stock_adjustment->extra_document as $key => $doc)
						@php $path = !empty($doc) ? asset('/uploads/documents/' . $doc) : null; @endphp
						<td>
							<div class="btn-group">
								<a href="{{ $path }}" class="pull-left btn btn-primary" target="_blank" title="View Document" >
								<i class="fas fa-eye"></i>
								</a>
								<a href="{{$path}}" download="{{$doc}}" class="btn btn-primary no-print" title="Download">
								<i class="fa fa-download"></i>
								</a>
							</div>
						</td>
						@empty
						@endforelse
					</tr>
					@endif
				</div>
			</div>

    		<div class="row">
    			<div class="col-sm-12 col-xs-12">
      				<div class="table-responsive">
      					<table class="table table-condensed bg-gray">
							<tr class="bg-green">
								<th>@lang('sale.product')</th>
								@if(!empty($lot_n_exp_enabled))
			                		<th>{{ __('lang_v1.lot_n_expiry') }}</th>
			              		@endif
			              		<th>Diff Qty</th>
								<th>Existing Qty On Hand</th>
								<th>New @lang('sale.qty')</th>
								<th>@lang('sale.subtotal')</th>
							</tr>
							@foreach( $stock_adjustment->stock_adjustment_lines as $stock_adjustment_line )
								<tr>
									<td>
										{{ $stock_adjustment_line->variation->full_name }}
									</td>
									@if(!empty($lot_n_exp_enabled))
			                			<td>{{ $stock_adjustment_line->lot_number ?? '--' }}
						                  @if( session()->get('business.enable_product_expiry') == 1 && !empty($stock_adjustment_line->exp_date))
						                    ({{@format_date($stock_adjustment_line->exp_date)}})
						                  @endif
						                </td>
			              			@endif
			              			<td>
			              				<?php
			              					$qty_on_hand = $stock_adjustment_line->old_qty;
										    $adjType = $stock_adjustment->adjustment_type;
										    $quantity = $stock_adjustment_line->quantity;

										    $nq = 0.00;
										    if($adjType == 'abnormal')
										    {
										    	$nq = $quantity;
										    }
										    if($adjType == 'normal')
										    {
										        $nq = $quantity - $qty_on_hand;
										    }
			              				?>
										{{ @format_quantity($nq) }}
									</td>
			              			<td>
										{{@format_quantity($stock_adjustment_line->old_qty)}}
									</td>
									<td>
										<?php
											if($adjType == 'abnormal')
										    {
										    	$new_quantity = $stock_adjustment_line->quantity + $stock_adjustment_line->old_qty;
										    }
										    if($adjType == 'normal')
										    {
										        $new_quantity = $stock_adjustment_line->quantity;
										    }
										?>
										{{@format_quantity($new_quantity)}}
									</td>
									<td>
										{{@num_format($stock_adjustment_line->unit_price * $stock_adjustment_line->quantity)}}
									</td>
								</tr>
							@endforeach
						</table>
      				</div>
     			</div>
     			<div class="col-md-6 col-md-offset-6 col-sm-12 col-xs-12">
				    <div class="table-responsive">
				        <table class="table no-border">
				          	<tr>
				            	<th>@lang('stock_adjustment.total_amount'): </th>
				            	<td><span class="display_currency pull-right" data-currency_symbol="true">{{ $stock_adjustment->final_total }}</span></td>
				          	</tr>
				          	<tr>
				            	<th>@lang('stock_adjustment.total_amount_recovered'): </th>
				            	<td><span class="display_currency pull-right" data-currency_symbol="true">{{ $stock_adjustment->total_amount_recovered }}</span></td>
				          	</tr>
				      	</table>
				  	</div>
				</div>
    		</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary no-print" aria-label="Print"
			onclick="$(this).closest('div.modal-content').printThis();"><i class="fa fa-print"></i> @lang( 'messages.print' )
			</button>
			<button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
		</div>
	</div>
</div>