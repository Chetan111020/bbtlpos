<div class="table-responsive">
                <table class="table table-bordered table-striped" id="sales_by_account_representative_report_tbl">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Invoice No.</th>
                            <th>Total Amount</th>
                            <th>Discount Amount</th>       
                            <th>Shipping Charges</th>
                            <th>Tax</th>                           
                            <th>JUUL</th>
                            <th>Total After deduction @show_tooltip(__('Total without discount ,shipping charges, tax & juul products '))</th>
                            <th>Total Paid</th>
                            <th>Total GP</th>
                            <th>Sell Due</th>
                            <th>Username</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                       <tr class="bg-gray font-17 footer-total text-center">
                        <td colspan="2"><strong>@lang('sale.total'):</strong></td>
                        <td class="footer_total_amount"></td>
                        <td class="footer_discount_amount"></td>
                        <td  class="footer_shipping_charges_amount"></td>               
                        <td  class="footer_tax_amount"></td>               
                        <td  class="footer_juul_amount"></td>
                        <td  class="footer_deduction_amount"></td>
                        <td class="footer_final_total"></td>
                        <td class="footer_gross_profit"></td>
                        <td class="footer_total_remaining"></td>
                        <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>