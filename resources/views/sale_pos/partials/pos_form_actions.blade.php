@php
	$is_mobile = isMobile();
@endphp
<div class="row">
	<div class="pos-form-actions">
		<div class="col-md-12">
			@if($is_mobile)
				<div class="col-md-12 text-right">
					<b>@lang('sale.total_payable'):</b>
					<input type="hidden" name="final_total"
												id="final_total_input" value=0>
					<span id="total_payable" class="text-success lead text-bold text-right">0</span>
				</div>
			@endif
			<button type="button" class="@if($is_mobile) col-xs-6 @endif btn bg-info text-white btn-default btn-flat @if($pos_settings['disable_draft'] != 0) hide @endif" id="pos-draft"><i class="fas fa-edit"></i> @lang('sale.draft')</button>
			<button type="button" style="display: none;" class="btn btn-default bg-red  btn-flat @if($is_mobile) col-xs-6 @endif" id="pos-quotation"><i class="fas fa-edit"></i> @lang('lang_v1.quotation')</button>

            {{-- @if (!auth()->user()->roles()->whereIn('id', [36, 35, 32, 11, 33])->exists())
			<button type="button" class="btn btn-default bg-yellow btn-flat @if($is_mobile) col-xs-6 @endif @if(isset($transaction) && $transaction->status=='final') hide @endif" id="pos-payment-verify"><i class="fas fa-edit"></i> @lang('Payment Verify')</button>
			@endif --}}
			{{--<button type="button" class="btn btn-default bg-yellow btn-flat @if($is_mobile) col-xs-6 @endif" id="pos-quotation"><i class="fas fa-edit"></i> @lang('lang_v1.quotation')</button>--}}

			@if(empty($pos_settings['disable_suspend']))
				{{--<button type="button"
				class="@if($is_mobile) col-xs-6 @endif btn bg-red btn-default btn-flat no-print pos-express-finalize"
				data-pay_method="suspend"
				title="@lang('lang_v1.tooltip_suspend')" >
				<i class="fas fa-pause" aria-hidden="true"></i>
				@lang('lang_v1.suspend')
				</button>--}}
			@endif

			@if(empty($pos_settings['disable_credit_sale_button']))
				<input type="hidden" name="is_credit_sale" value="0" id="is_credit_sale">
				<button style="background-color: #2dce89!important; color: white;" type="button" class="btn bg-purple btn-default btn-flat no-print @if($is_mobile) col-xs-6 @endif"
				data-pay_method="credit_sale" id="pos-finalize" title="state sale tax calculate" >
					<i class="fas fa-check" aria-hidden="true"></i> Finalize
				</button>
			@endif
			{{--<button type="button" --}}
				{{--class="btn bg-maroon btn-default btn-flat no-print @if(!empty($pos_settings['disable_suspend'])) @endif pos-express-finalize @if(!array_key_exists('card', $payment_types)) hide @endif @if($is_mobile) col-xs-6 @endif" --}}
				{{--data-pay_method="card"--}}
				{{--title="@lang('lang_v1.tooltip_express_checkout_card')" >--}}
				{{--<i class="fas fa-credit-card" aria-hidden="true"></i> @lang('lang_v1.express_checkout_card')--}}
			{{--</button>--}}

			{{--<button type="button" class="btn bg-navy btn-default @if(!$is_mobile) @endif btn-flat no-print @if($pos_settings['disable_pay_checkout'] != 0) hide @endif @if($is_mobile) col-xs-6 @endif" id="pos-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')"><i class="fas fa-money-check-alt" aria-hidden="true"></i> @lang('lang_v1.checkout_multi_pay') </button>--}}

			{{-- city sale tax --}}
			<button style="background-color: #001f3f!important; color: white;" type="button" id="calculateTax" class="btn btn-default" title="tax calculate"><i class="fas fa-money-check-alt" aria-hidden="true"></i> Tax </button>

            {{-- @if (!empty($autosave) && $autosave == 1) --}}
            {{-- onclick="window.location = window.location.pathname;" --}}
            {{-- <a href="#" style="background-color: #4900ca!important; color: white;" type="button" class="btn btn-default" disabled title="Auto Save">
                <span style="display:flex;justify-content:space-around;align-items:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="height:15px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
                      </svg>
                    <span style="margin: 0 10px;">
                        Auto Save Running
                    </span>
                </span>
            </a>
            @else
            <button style="background-color: #4900ca!important; color: white;" type="button" class="btn btn-default auto-save-btn" title="Auto Save">
                <span style="display:flex;justify-content:space-around;align-items:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="height:15px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
                      </svg>
                    <span style="margin: 0 10px;">
                        Auto Save
                    </span>
                </span>
            </button>
            @endif --}}

			<input type="hidden" id="tax_applicable" name="tax_applicable" value="{{(!empty($edit))? 1 : 0}}" />
            <input type="hidden" id="tax_deal" name="tax_deal" value="{{ !empty($edit) ? ($transaction->tax_deal ?? 'off') : 'off' }}" />

			{{--<button style="background-color: #001f3f!important; color: white;" type="button" class="btn btn-default" id="pos-finalize" title="state sale tax calculate"><i class="fas fa-money-check-alt" aria-hidden="true"></i> State Tax </button>--}}

			{{--<button type="button" class="btn btn-success @if(!$is_mobile) @endif btn-flat no-print @if($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize @if($is_mobile) col-xs-6 @endif" data-pay_method="cash" title="@lang('tooltip.express_checkout')"> <i class="fas fa-money-bill-alt" aria-hidden="true"></i> @lang('lang_v1.express_checkout_cash')</button>--}}
			@if(!$is_mobile)
			&nbsp;&nbsp;
			<b>@lang('sale.total_payable'):</b>
			<input type="hidden" name="final_total"
										id="final_total_input" value=0>
			<span id="total_payable" class="text-success lead text-bold">0</span>
			&nbsp;&nbsp;
			@endif
			@if(empty($edit))
				<button type="button" class="btn btn-danger btn-flat @if($is_mobile) col-xs-6 @else btn-xs @endif" id="pos-cancel"> <i class="fas fa-window-close"></i> @lang('sale.cancel')</button>
			@else
				<button type="button" class="btn btn-danger hide btn-flat @if($is_mobile) col-xs-6 @else btn-xs @endif" id="pos-delete"> <i class="fas fa-trash-alt"></i> @lang('messages.delete')</button>
				<button type="button" class="btn btn-danger btn-flat @if($is_mobile) col-xs-6 @else btn-xs @endif" ><a href="/sells" style="color:#fff"> <i class="fas fa-trash-alt"></i> @lang('sale.cancel')</a></button>
			@endif
            <input type="checkbox" value="1" name="notpickpack" class="notpickpack" @if(isset($transaction) && $transaction->order_picking_status == 2 && $transaction->order_packing_status == 2) checked @endif> Do not  pick & pack



			@if(!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
			<button type="button" class="btn btn-primary btn-flat pull-right @if($is_mobile) col-xs-6 @endif" data-toggle="modal" data-target="#customer_recent_transactions_modal" id="customer-recent-transactions"> <i class="fas fa-clock"></i> @lang('Customer Recent Transactions')</button>
			<button type="button" class="btn btn-primary btn-flat pull-right @if($is_mobile) col-xs-6 @endif" data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions"> <i class="fas fa-clock"></i> @lang('lang_v1.recent_transactions')</button>
			@endif

		</div>
	</div>
</div>
@if(isset($transaction))
	@include('sale_pos.partials.edit_discount_modal', ['sales_discount' => $transaction->discount_amount, 'discount_type' => $transaction->discount_type, 'rp_redeemed' => $transaction->rp_redeemed, 'rp_redeemed_amount' => $transaction->rp_redeemed_amount, 'max_available' => !empty($redeem_details['points']) ? $redeem_details['points'] : 0])
@else
	@include('sale_pos.partials.edit_discount_modal', ['sales_discount' => $business_details->default_sales_discount, 'discount_type' => 'percentage', 'rp_redeemed' => 0, 'rp_redeemed_amount' => 0, 'max_available' => 0])
@endif

@if(isset($transaction))
	@include('sale_pos.partials.edit_order_tax_modal', ['selected_tax' => $transaction->tax_id])
@else
	@include('sale_pos.partials.edit_order_tax_modal', ['selected_tax' => $business_details->default_sales_tax])
@endif

@include('sale_pos.partials.edit_shipping_modal')