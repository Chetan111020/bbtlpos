<div class="table-responsive">
    <table class="table table-bordered table-striped ajax_view" id="purchase_table" style="width:100%">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll"/></th>
                <th>@lang('messages.action')</th>
                <th>@lang('messages.date')</th>
                <th>@lang('purchase.ref_no')</th>
                <th>@lang('purchase.location')</th>
                <th>@lang('purchase.supplier')</th>
                <th>@lang('purchase.purchase_status')</th>
                <th>@lang('purchase.payment_status')</th>
                <th>@lang('purchase.grand_total')</th>
                <th>@lang('lang_v1.added_by')</th>
            </tr>
        </thead>
        <tfoot>
            <tr class="bg-gray font-17 text-center footer-total">
                <td colspan="6"><strong>@lang('sale.total'):</strong></td>
                <td id="footer_status_count"></td>
                <td id="footer_payment_status_count"></td>
                <td>
                    <span class="display_currency" id="footer_purchase_total" data-currency_symbol ="true">
                    </span>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>