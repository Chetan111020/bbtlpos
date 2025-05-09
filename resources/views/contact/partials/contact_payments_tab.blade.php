<table class="table table-bordered" 
id="contact_payments_table">
    <thead>
        <tr>
            <th width="20%">@lang('lang_v1.paid_on')</th>
            <th width="20%">@lang('purchase.ref_no')</th>
            <th width="10%">@lang('sale.amount')</th>
            <th width="15%">@lang('lang_v1.payment_method')</th>
            <th width="15%">@lang('account.payment_for')</th>
            <th width="20%">@lang('Created By')</th>
            <th width="20%">Payment Location</th>
            <th width="20%">@lang('messages.action')</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $payment)
            @php
                $count_child_payments = count($payment->child_payments);
                $sum_child_payments = 0;
            @endphp
            @if($count_child_payments > 0)
                @foreach($payment->child_payments as $child_payment)
                    @php
                        $sum_child_payments = $sum_child_payments + $child_payment->amount;
                    @endphp
                @endforeach
            @endif
            @if(($payment->method !='credit_memo' && $payment->type != 'sell_return') or $count_child_payments>0)
                @include('contact.partials.payment_row', compact('payment', 'count_child_payments', 'payment_types','sum_child_payments'))
            @endif

            @if($count_child_payments > 0)
                @foreach($payment->child_payments as $child_payment)
                    @include('contact.partials.payment_row', ['payment' => $child_payment, 'count_child_payments' => 0, 'payment_types' => $payment_types, 'parent_payment_ref_no' => $payment->payment_ref_no,'parent_payment_created_by' => $payment->created_by])
                @endforeach
            @endif
        @endforeach
    </tbody>
</table>
<div class="text-right" style="width: 100%;" id="contact_payments_pagination">{{ $payments->links() }}</div>

<script type="text/javascript">
    function toggleChildContent(classname){
        $('#contact_payments_table tbody')
            .find("tr."+classname)
            .each(function() {
                if ($(this).hasClass("hide")) $(this).removeClass('hide');
                else $(this).addClass('hide');
            })
    }
</script>

