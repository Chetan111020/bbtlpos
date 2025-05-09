<div class="modal-dialog modal-xl no-print" role="document">
  <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalTitle"> Payments Detail
    </h4>
    </div>
    <div class="modal-body">
        <div class="row">
          <div class="col-sm-12">
            <div class="table-responsive">
                <table class="table bg-gray">
                  <thead>
                    <tr class="bg-green">
                      @if($invoice_type == 'sell')
                      <th>Invoice No</th>
                      @else
                      <th>Ref No</th>
                      @endif
                      <th>Contact Name</th>
                      <th>Final Total</th>
                      <th>Discount Amount</th>
                      <th width="10%">Paid On</th>
                      <th>Payment Method</th>
                      <th>Reference No</th>
                      <th>Payment For</th>
                      <th>Payment Amount</th>
                      <th>Created By</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($reconcile_payments as $payment)

                        @if($payment->transaction_contact_id != $payment->payment_contact_id && $payment->transaction_id !="")
                        <tr style="background-color: red;">
                        @else
                        <tr>
                        @endif
                        @if($invoice_type == 'sell')
                        <td>{{$payment->invoice_no}}</td>
                        @else
                        <td>{{$payment->ref_no}}</td>
                        @endif
                        <td>{{$payment->transaction_contact_name}}</td>
                        <td class="display_currency" data-currency_symbol="true">{{$payment->final_total}}</td>
                        <?php $discount = !empty($payment->discount_amount) ? $payment->discount_amount : 0;

                        if (!empty($discount) && $payment->discount_type == 'percentage') {
                            $discount = $payment->total_before_tax * ($discount / 100);
                        }
                        ?>
                        <td class="display_currency" data-currency_symbol="true">{{$discount}}</td>
                        <td width="10%"> {{@format_datetime($payment->paid_on)}}</td>
                        <td>{{$payment_types[$payment->method]}}</td>
                        <td>{{$payment->payment_ref_no}}</td>
                        <td>{{$payment->payment_for_name}}</td>
                        <td class="display_currency" data-currency_symbol="true">{{$payment->payment_for_amount}}</td>
                        <td>{{$payment->created_by}}</td>
                      </tr>
                    @empty
                      <td colspan="9" style="text-align: center;">No Payments Found!!</td>
                    @endforelse
                    <tr></tr>
                  </tbody>
                </table>
            </div>
          </div>
        </div>
    <div class="row">
      <div class="col-md-9 col-sm-12 col-xs-12">
        <div class="table-responsive">
            &nbsp;
        </div>
      </div>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <div class="table-responsive">
          <table class="table bg-gray">
            <tr>
              <th>Total Invoice: </th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{$total_invoice}}</span></td>
            </tr>
            <tr>
              <th>Total Paid:</th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{$total_paid}}</span></td>
            </tr>
            <tr>
              <th>Total Discount:</th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{$total_discount}}</span></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    var element = $('div.modal-xl');
    __currency_convert_recursively(element);
  });
</script>
