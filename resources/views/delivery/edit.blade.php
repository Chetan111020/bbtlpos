
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <form action="{{ action('OrderDeliveryController@update', [$edit->id]) }}" method="POST">
            @csrf
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Edit Delivery</h4>
            </div>
            <div class="modal-body row">
                 <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <div style="display: flex; padding: 15px; justify-content: center; align-items: center;">
                            <div class="btn-group" data-toggle="buttons" class="status-btn-group">
                                <label id="deliveredLabel"
                                    class="btn @if ($edit->status == 'delivered') btn-success @else btn-info @endif"
                                    onclick="setActive('delivered')">
                                    <input type="radio" value="delivered" name="deliveryStatus" id="deliveredRadio"
                                        @if ($edit->status == 'delivered') checked @endif> Delivered
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <div style="display: flex; padding: 15px; justify-content: center; align-items: center;">
                            <div class="btn-group" data-toggle="buttons" style="margin-left: 15%;"
                                class="status-btn-group">
                                <label id="notDeliveredLabel"
                                    class="btn @if ($edit->status == 'not_delivered') btn-success @else btn-info @endif"
                                    onclick="setActive('not_delivered')">
                                    <input type="radio" value="not_delivered" name="deliveryStatus"
                                        id="notDeliveredRadio" @if ($edit->status == 'not_delivered') checked @endif>
                                    Not Delivered
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                 <div class="col-md-6 col-sm-6 col-xs-6">
                    <div class="form-group">
                        <label for="payment">Payment Amount:</label>
                        <input type="number" id="payment" value="{{ $edit->payment_amount }}" name="payment_amount" step="0.01"
                            class="form-control" placeholder="Payment">
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6">
                    <div class="form-group">
                        <label for="method">Payment Method:</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            <select class="form-control select2" name="payment_method" style="width:100%;"
                                id="payment_method">
                                <option>Select</option>
                                <option value="{{ $edit->payment_method }}" selected="selected">{{ $edit->payment_method }}</option>
                                <option value="money_order" {{ $edit->payment_method === 'money_order' ? 'selected' : '' }}>
                                    Money order</option>
                                <option value="cash" {{ $edit->payment_method === 'cash' ? 'selected' : '' }}>Cash
                                </option>
                                <option value="card" {{ $edit->payment_method === 'card' ? 'selected' : '' }}>Card
                                </option>
                                <option value="zelle" {{ $edit->payment_method === 'zelle' ? 'selected' : '' }}>Zelle</option>
                                <option value="cheque" {{ $edit->payment_method === 'cheque' ? 'selected' : '' }}>
                                    Cheque</option>
                                <option value="bank_transfer"
                                    {{ $edit->payment_method === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer
                                </option>
                                <option value="other" {{ $edit->payment_method === 'other' ? 'selected' : '' }}>Other
                                </option>
                                <option value="credit_memo"
                                    {{ $edit->payment_method === 'credit_memo' ? 'selected' : '' }}>Credit Memo
                                </option>
                                <option
                                    value="custom_pay_1 {{ $edit->payment_method === 'custom_pay_1' ? 'selected' : '' }}">
                                    ACH</option>
                                <option value="custom_pay_2"
                                    {{ $edit->payment_method === 'custom_pay_2' ? 'selected' : '' }}>WIRE</option>
                                <option value="custom_pay_3"
                                    {{ $edit->payment_method === 'custom_pay_3' ? 'selected' : '' }}>Check Bounce
                                    Charges</option>
                                <option value="custom_pay_4"
                                    {{ $edit->payment_method === 'custom_pay_4' ? 'selected' : '' }}>VENMO</option>
                                <option value="custom_pay_5"
                                    {{ $edit->payment_method === 'custom_pay_5' ? 'selected' : '' }}>CASH APP
                                </option>
                                <option value="custom_pay_6"
                                    {{ $edit->payment_method === 'custom_pay_6' ? 'selected' : '' }}>BANK DEPOSIT
                                </option>
                                <option value="custom_pay_7"
                                    {{ $edit->payment_method === 'custom_pay_7' ? 'selected' : '' }}>BANK MOBILE
                                    DEPOSIT</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6">
                    <div class="form-group">
                        <label for="payment">Payment Amount 2:</label>
                        <input type="number" id="payment" value="{{ $edit->payment_amount2 }}" step="0.01"
                            name="payment_amount2" class="form-control" placeholder="Payment">
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6">
                    <div class="form-group">
                        <label for="method">Payment Method 2:</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            <select class="form-control select2" name="payment_method2" style="width:100%;"
                                id="payment_method2">
                                <option>Select</option>
                                <option value="{{ $edit->payment_method2 }}" selected="selected">{{ $edit->payment_method2 }}</option>
                                <option value="money_order" {{ $edit->payment_method2 === 'money_order' ? 'selected' : '' }}>
                                    Money order</option>
                                <option value="cash" {{ $edit->payment_method2 === 'cash' ? 'selected' : '' }}>Cash
                                </option>
                                <option value="card" {{ $edit->payment_method2 === 'card' ? 'selected' : '' }}>Card
                                </option>
                                <option value="zelle" {{ $edit->payment_method === 'zelle' ? 'selected' : '' }}>Zelle</option>
                                <option value="cheque" {{ $edit->payment_method2 === 'cheque' ? 'selected' : '' }}>
                                    Cheque</option>
                                <option value="bank_transfer"
                                    {{ $edit->payment_method2 === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer
                                </option>
                                <option value="other" {{ $edit->payment_method2 === 'other' ? 'selected' : '' }}>Other
                                </option>
                                <option value="credit_memo"
                                    {{ $edit->payment_method2 === 'credit_memo' ? 'selected' : '' }}>Credit Memo
                                </option>
                                <option
                                    value="custom_pay_1 {{ $edit->payment_method2 === 'custom_pay_1' ? 'selected' : '' }}">
                                    ACH</option>
                                <option value="custom_pay_2"
                                    {{ $edit->payment_method2 === 'custom_pay_2' ? 'selected' : '' }}>WIRE</option>
                                <option value="custom_pay_3"
                                    {{ $edit->payment_method2 === 'custom_pay_3' ? 'selected' : '' }}>Check Bounce
                                    Charges</option>
                                <option value="custom_pay_4"
                                    {{ $edit->payment_method2 === 'custom_pay_4' ? 'selected' : '' }}>VENMO</option>
                                <option value="custom_pay_5"
                                    {{ $edit->payment_method2 === 'custom_pay_5' ? 'selected' : '' }}>CASH APP
                                </option>
                                <option value="custom_pay_6"
                                    {{ $edit->payment_method2 === 'custom_pay_6' ? 'selected' : '' }}>BANK DEPOSIT
                                </option>
                                <option value="custom_pay_7"
                                    {{ $edit->payment_method2 === 'custom_pay_7' ? 'selected' : '' }}>BANK MOBILE
                                    DEPOSIT</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6">
                    <div class="form-group">
                        <label for="payment">Payment Amount 3:</label>
                        <input type="number" id="payment" value="{{ $edit->payment_amount3 }}" step="0.01"
                            name="payment_amount3" class="form-control" placeholder="Payment">
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6">
                    <div class="form-group">
                        <label for="method">Payment Method 3:</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            <select class="form-control select2" name="payment_method3" style="width:100%;"
                                id="payment_method3">
                                <option>Select</option>
                               <option value="{{ $edit->payment_method3 }}" selected="selected">{{ $edit->payment_method3 }}</option>
                                <option value="money_order" {{ $edit->payment_method3 === 'money_order' ? 'selected' : '' }}>
                                    Money order</option>
                                <option value="cash" {{ $edit->payment_method3 === 'cash' ? 'selected' : '' }}>Cash
                                </option>
                                <option value="card" {{ $edit->payment_method3 === 'card' ? 'selected' : '' }}>Card
                                </option>
                                <option value="zelle" {{ $edit->payment_method === 'zelle' ? 'selected' : '' }}>Zelle</option>
                                <option value="cheque" {{ $edit->payment_method3 === 'cheque' ? 'selected' : '' }}>
                                    Cheque</option>
                                <option value="bank_transfer"
                                    {{ $edit->payment_method3 === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer
                                </option>
                                <option value="other" {{ $edit->payment_method3 === 'other' ? 'selected' : '' }}>
                                    Other
                                </option>
                                <option value="credit_memo"
                                    {{ $edit->payment_method3 === 'credit_memo' ? 'selected' : '' }}>Credit Memo
                                </option>
                                <option
                                    value="custom_pay_1 {{ $edit->payment_method3 === 'custom_pay_1' ? 'selected' : '' }}">
                                    ACH</option>
                                <option value="custom_pay_2"
                                    {{ $edit->payment_method3 === 'custom_pay_2' ? 'selected' : '' }}>WIRE</option>
                                <option value="custom_pay_3"
                                    {{ $edit->payment_method3 === 'custom_pay_3' ? 'selected' : '' }}>Check Bounce
                                    Charges</option>
                                <option value="custom_pay_4"
                                    {{ $edit->payment_method3 === 'custom_pay_4' ? 'selected' : '' }}>VENMO</option>
                                <option value="custom_pay_5"
                                    {{ $edit->payment_method3 === 'custom_pay_5' ? 'selected' : '' }}>CASH APP
                                </option>
                                <option value="custom_pay_6"
                                    {{ $edit->payment_method3 === 'custom_pay_6' ? 'selected' : '' }}>BANK DEPOSIT
                                </option>
                                <option value="custom_pay_7"
                                    {{ $edit->payment_method3 === 'custom_pay_7' ? 'selected' : '' }}>BANK MOBILE
                                    DEPOSIT</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="driver_notes">Driver Notes:</label>
                        <textarea id="driver_notes" name="note" class="form-control" rows="4" placeholder="Driver Notes">{{ $edit->driver_note }}</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
<script>
    function setActive(status) {
        var deliveredLabel = document.getElementById('deliveredLabel');
        var notDeliveredLabel = document.getElementById('notDeliveredLabel');

        if (status === 'delivered') {
            deliveredLabel.classList.add('active');
            deliveredLabel.classList.remove('btn-info');
            deliveredLabel.classList.add('btn-success');
            notDeliveredLabel.classList.remove('active');
            notDeliveredLabel.classList.remove('btn-success');
            notDeliveredLabel.classList.add('btn-info');
        } else if (status === 'not_delivered') {
            notDeliveredLabel.classList.add('active');
            notDeliveredLabel.classList.remove('btn-info');
            notDeliveredLabel.classList.add('btn-success');
            deliveredLabel.classList.remove('active');
            deliveredLabel.classList.remove('btn-success');
            deliveredLabel.classList.add('btn-info');
        }
    }
    $('#payment_method').select2({
        dropdownParent: $('#EditModal')
    });
    $('#payment_method2').select2({
        dropdownParent: $('#EditModal')
    });
    $('#payment_method3').select2({
        dropdownParent: $('#EditModal')
    });
</script>
