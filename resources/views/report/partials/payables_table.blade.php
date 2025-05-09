<table class="table table-bordered table-striped" id="payables_report_tbl" style="width: 100%;">
    <thead>
        <tr>
            <th>Supplier Name</th>
            <th>Mobile</th>
            <th>Email</th>
            <th>Open Balance</th>
            <th>Last Order Date</th>
        </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
       <tr class="bg-gray font-17 footer-total text-center">
        <td colspan="3"><strong>@lang('sale.total'):</strong></td>
        <td><span id="footer_cm_open_balance_total_payables" class="display_currency" data-currency_symbol="true"></span></td>
        <td></td>
        </tr>
    </tfoot>
</table>