@extends('layouts.app')
@section('content')
@section('title', 'Order Delivery')
<section class="content-header">
    <h1>@lang('Update Order Delivery') </h1>
</section>
<style>
    .btn-success {
    background: green;
    border-color: green;
}
</style>
<section class="content">
    <form action="{{ action('OrderDeliveryController@update', [$edit->id]) }}" enctype="multipart/form-data" id=" "
        method="POST">
        @csrf
        @component('components.widget', ['class' => 'box-primary'])
            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
            <!-- Add this input field to your form -->
            <input type="hidden" name="lat" id="lat" value="">
            <input type="hidden" name="long" id="long" value="">
            <div class="row">
                <div style="display: flex;padding: 15px; justify-content: center; align-items: center;">
                    <div class="col-md-4" style="text-align: center;">

                        <div class="form-group">
                            <label for="">Invoice No:</label>
                            <select class="form-control select2" name="transaction_id" id="orderno">
                                <option disabled>Select</option>
                                @foreach ($orders as $order)
                                    <option value="{{ $order->transaction_id }}"
                                        @if ($edit->transaction_id == $order->transaction_id) selected @endif>
                                        {{ $order->invoice_no }} - ({{ $order->customer }})
                                    </option>
                                @endforeach
                            </select>

                        </div>
                        <span style="color:red" class="already-exists-order"></span>
                    </div>
                </div>
            </div>
             
            <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-6">
                    <div style="display: flex; padding: 15px; justify-content: center; align-items: center;">
                        <div class="btn-group" data-toggle="buttons" class="status-btn-group">
                            <label id="deliveredLabel"
                                class="btn @if ($edit->status == 'delivered') btn-success @else btn-info @endif"
                                onclick="setActive('delivered')">
                                <input type="radio" value="delivered" name="status" id="deliveredRadio"
                                    @if ($edit->status == 'delivered') checked @endif> Delivered
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6">
                    <div style="display: flex; padding: 15px; justify-content: center; align-items: center;">
                        <div class="btn-group" data-toggle="buttons" style="margin-left: 15%;" class="status-btn-group">
                            <label id="notDeliveredLabel"
                                class="btn @if ($edit->status == 'not_delivered') btn-success @else btn-info @endif"
                                onclick="setActive('not_delivered')">
                                <input type="radio" value="not_delivered" name="status" id="notDeliveredRadio"
                                    @if ($edit->status == 'not_delivered') checked @endif>
                                Not Delivered
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-sm-6">

                    <div class="form-group">
                        <label>Upload Image</label>
                        @if ($edit->img != '')
                            <div class="card preview_image" style="height: 15vw; object-fit: cover;">
                                <span class="fa fa-times-circle close"></span>
                                <img src="{{ asset('/uploads' . $edit->img) }}" style="width:200px !important;height: 200px !important"
                                    alt="Uploaded Image">
                            </div>
                        @endif
                        <input type="file" name="img" id="upload_main_image" onchange="return checkimageextension()">
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label for="payment">Payment Amount:</label>
                        <input type="number" id="payment" value="{{ $edit->payment_amount }}" name="payment_amount"
                            class="form-control" step="0.01" placeholder="Payment">
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label for="method">Payment Method:</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            <select class="form-control select2 payment-method" name="payment_method" style="width:100%;"
                                id="payment_method">
                                <option>Select</option>
                                <option value="{{ $edit->payment_method }}" selected="selected">
                                    {{ $edit->payment_method }}</option>
                                <option value="money_order" {{ $edit->payment_method === 'money_order' ? 'selected' : '' }}>
                                    Money order</option>
                                <option value="cash" {{ $edit->payment_method === 'cash' ? 'selected' : '' }}>Cash
                                </option>
                                <option value="card" {{ $edit->payment_method === 'card' ? 'selected' : '' }}>Card
                                </option>
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
                <div class="col-md-6 col-sm-6 hide" id="cheque_number1">
                    <div class="form-group">
                        <label for="">Cheque Number:</label>
                        <input type="number" name="cheque_number" value="{{ $edit->cheque_number }}"
                            class="form-control" placeholder="Cheque Number" id="">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label for="payment">Payment Amount 2:</label>
                        <input type="number" id="payment" value="{{ $edit->payment_amount2 }}"
                            name="payment_amount2" class="form-control" step="0.01" placeholder="Payment">
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">

                    <div class="form-group">
                        <label for="method">Payment Method 2:</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            <select class="form-control select2 payment-method-2" name="payment_method2"
                                style="width:100%;" id="payment_method2">
                                <option>Select</option>
                                <option value="{{ $edit->payment_method2 }}" selected="selected">
                                    {{ $edit->payment_method2 }}</option>
                                <option value="money_order"
                                    {{ $edit->payment_method2 === 'money_order' ? 'selected' : '' }}>
                                    Money order</option>
                                <option value="cash" {{ $edit->payment_method2 === 'cash' ? 'selected' : '' }}>Cash
                                </option>
                                <option value="card" {{ $edit->payment_method2 === 'card' ? 'selected' : '' }}>Card
                                </option>
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
                <div class="col-md-6 col-sm-6 hide" id="cheque_number2">
                    <div class="form-group">
                        <label for="">Cheque Number 2:</label>
                        <input type="number" name="cheque_number2" value="{{ $edit->cheque_number2 }}"
                            class="form-control" placeholder="Cheque Number 2" id="">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label for="payment">Payment Amount 3:</label>
                        <input type="number" id="payment" value="{{ $edit->payment_amount3 }}"
                            name="payment_amount3" class="form-control" step="0.01" placeholder="Payment">
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">

                    <div class="form-group">
                        <label for="method">Payment Method 3:</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            <select class="form-control select2 payment-method-3" name="payment_method3"
                                style="width:100%;" id="payment_method3">
                                <option>Select</option>
                                <option value="{{ $edit->payment_method3 }}" selected="selected">
                                    {{ $edit->payment_method3 }}</option>
                                <option value="money_order"
                                    {{ $edit->payment_method3 === 'money_order' ? 'selected' : '' }}>
                                    Money order</option>
                                <option value="cash" {{ $edit->payment_method3 === 'cash' ? 'selected' : '' }}>Cash
                                </option>
                                <option value="card" {{ $edit->payment_method3 === 'card' ? 'selected' : '' }}>Card
                                </option>
                                <option value="cheque" {{ $edit->payment_method3 === 'cheque' ? 'selected' : '' }}>
                                    Cheque</option>
                                <option value="bank_transfer"
                                    {{ $edit->payment_method3 === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer
                                </option>
                                <option value="other" {{ $edit->payment_method3 === 'other' ? 'selected' : '' }}>Other
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
                <div class="col-md-6 col-sm-6 hide" id="cheque_number3">
                    <div class="form-group">
                        <label for="">Cheque Number 3:</label>
                        <input type="number" name="cheque_number3" value="{{ $edit->cheque_number3 }}"
                            class="form-control" placeholder="Cheque Number 3" id="">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="driver_notes">Driver Notes:</label>
                        <textarea id="driver_notes" name="driver_note" class="form-control" rows="4" placeholder="Driver Notes">{{ $edit->driver_note }}</textarea>
                    </div>
                </div>
            </div>
            {{-- <div class="row hide">
                <div class="col-md-12">
                    <label class="" for="">Signature:</label>
                    <br />
                    <div id="sig"></div>
                    <br />
                    <button id="clear" class="btn btn-danger btn-sm">Clear Signature</button>
                    <textarea id="signature64" name="signed" style="display: none">{{ asset('/uploads/delivery/signature/' . $edit->signed) }}</textarea>
                </div>
            </div> --}}
            <br>
            <br>
            <div class="row">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary pull-right" id="submitButton">Update</button>
                </div>
            </div>
        @endcomponent
    </form>

</section>

<style>
    #sig-canvas {
        border: 2px dotted #CCCCCC;
        border-radius: 15px;
        cursor: crosshair;
    }

    .kbw-signature {
        width: 100%;
        height: 200px;
    }

    #sig canvas {

        width: 100% !important;

        height: auto;

    }
</style>
@endsection
@section('javascript')

<link type="text/css" href="{{ asset('assets/src/css/jquery.signature.css') }}" rel="stylesheet">
<script src="{{ asset('assets/src/js/jquery.signature.js') }}"></script>
<script src="{{ asset('assets/src/js/jquery.ui.touch-punch.js') }}"></script>

<script type="text/javascript">
    $("#orderno").prop("disabled", true);
    $('.payment-method').change(function() {
        var selectedPaymentMethod = $(this).val();
        if (selectedPaymentMethod === 'cheque') {
            // $(this).closest('.row').find('.hide').removeClass('hide');
            $('#cheque_number1').removeClass('hide');
        } else {
            // $(this).closest('.row').find('.hide').addClass('hide');
            $('#cheque_number1').addClass('hide');
        }
    });
    $('.payment-method-2').change(function() {
        var selectedPaymentMethod = $(this).val();
        if (selectedPaymentMethod === 'cheque') {

            $('#cheque_number2').removeClass('hide');
        } else {

            $('#cheque_number2').addClass('hide');
        }
    });
    $('.payment-method-3').change(function() {
        var selectedPaymentMethod = $(this).val();
        if (selectedPaymentMethod === 'cheque') {
            // $(this).closest('.row').find('.hide').removeClass('hide');
            $('#cheque_number3').removeClass('hide');
        } else {
            // $(this).closest('.row').find('.hide').addClass('hide');
            $('#cheque_number3').addClass('hide');
        }
    });


    document.addEventListener("DOMContentLoaded", function() {
        var paymentMethodSelect1 = document.getElementById('payment_method');
        var chequeNumberDiv1 = document.getElementById('cheque_number1');

        // Function to show or hide the cheque number input based on the selected payment method
        function toggleChequeNumber1() {
            var selectedPaymentMethod = paymentMethodSelect1.value;
            if (selectedPaymentMethod === 'cheque') {
                chequeNumberDiv1.classList.remove('hide');
            } else {
                chequeNumberDiv1.classList.add('hide');
            }
        }

        // Call the function on page load
        toggleChequeNumber1();

        // Add event listener to the payment method select to call the function when its value changes
        paymentMethodSelect1.addEventListener('change', toggleChequeNumber1);
    });

    document.addEventListener("DOMContentLoaded", function() {
        var paymentMethodSelect2 = document.getElementById('payment_method2');
        var chequeNumberDiv2 = document.getElementById('cheque_number2');

        // Function to show or hide the cheque number input based on the selected payment method
        function toggleChequeNumber2() {
            var selectedPaymentMethod = paymentMethodSelect2.value;
            if (selectedPaymentMethod === 'cheque') {
                chequeNumberDiv2.classList.remove('hide');
            } else {
                chequeNumberDiv2.classList.add('hide');
            }
        }

        // Call the function on page load
        toggleChequeNumber2();

        // Add event listener to the payment method select to call the function when its value changes
        paymentMethodSelect2.addEventListener('change', toggleChequeNumber2);
    });

    document.addEventListener("DOMContentLoaded", function() {
        var paymentMethodSelect3 = document.getElementById('payment_method3');
        var chequeNumberDiv3 = document.getElementById('cheque_number3');

        // Function to show or hide the cheque number input based on the selected payment method
        function toggleChequeNumber3() {
            var selectedPaymentMethod = paymentMethodSelect3.value;
            if (selectedPaymentMethod === 'cheque') {
                chequeNumberDiv3.classList.remove('hide');
            } else {
                chequeNumberDiv3.classList.add('hide');
            }
        }

        // Call the function on page load
        toggleChequeNumber3();

        // Add event listener to the payment method select to call the function when its value changes
        paymentMethodSelect3.addEventListener('change', toggleChequeNumber3);
    });


    //     document.addEventListener("DOMContentLoaded", function() {
    //     var paymentMethodSelect1 = document.getElementById('payment_method');
    //     var chequeNumberDiv1 = document.getElementById('cheque_number1');

    //     var paymentMethodSelect2 = document.getElementById('payment_method-2');
    //     var chequeNumberDiv2 = document.getElementById('cheque_number2');

    //     var paymentMethodSelect3 = document.getElementById('payment_method-3');
    //     var chequeNumberDiv3 = document.getElementById('cheque_number3');

    //     // Function to show or hide the cheque number input based on the selected payment method
    //     function toggleChequeNumber(paymentMethodSelect, chequeNumberDiv) {
    //         var selectedPaymentMethod = paymentMethodSelect.value;
    //         if (selectedPaymentMethod === 'cheque') {
    //             chequeNumberDiv.classList.remove('hide');
    //         } else {
    //             chequeNumberDiv.classList.add('hide');
    //         }
    //     }

    //     // Call the function on page load for each pair of elements
    //     toggleChequeNumber(paymentMethodSelect1, chequeNumberDiv1);
    //     toggleChequeNumber(paymentMethodSelect2, chequeNumberDiv2);
    //     toggleChequeNumber(paymentMethodSelect3, chequeNumberDiv3);

    //     // Add event listeners to the payment method selects to call the function when their values change
    //     paymentMethodSelect1.addEventListener('change', function() {
    //         toggleChequeNumber(paymentMethodSelect1, chequeNumberDiv1);
    //     });
    //     paymentMethodSelect2.addEventListener('change', function() {
    //         toggleChequeNumber(paymentMethodSelect2, chequeNumberDiv2);
    //     });
    //     paymentMethodSelect3.addEventListener('change', function() {
    //         toggleChequeNumber(paymentMethodSelect3, chequeNumberDiv3);
    //     });
    // });

    //     function checkChequeDisplay(paymentMethod, chequeNumberInput) {
    //         if (paymentMethod === 'cheque') {
    //             chequeNumberInput.removeClass('hide');
    //         } else {
    //             chequeNumberInput.addClass('hide');
    //         }
    //     }


    var sig = $('#sig').signature({
        syncField: '#signature64',
        syncFormat: 'PNG'
    });
    $('#clear').click(function(e) {
        e.preventDefault();
        sig.signature('clear');
        $("#signature64").val('');
    });
    $("#orderno").on('change', function() {
        var transaction_id = $(this).val();
        $.ajax({
            method: 'POST',
            url: "{{ url('/order-delivery/checkorder') }}",
            dataType: 'json',
            data: {
                'transaction_id': transaction_id
            },
            success: function(success) {

                if (success.success == true) {
                    $(".submit").prop("disabled", true);
                    $(".already-exists-order").text(success.message);
                } else {
                    $(".submit").prop("disabled", false);
                    $(".already-exists-order").text(success.message);
                }
            },
        });
    });


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



    var img_fileinput_setting = {
        showUpload: false,
        showPreview: true,
        browseLabel: LANG.file_browse_label,
        removeLabel: LANG.remove,
        previewSettings: {
            image: {
                width: 'auto',
                height: 'auto',
                'max-width': '100%',
                'max-height': '100%'
            },
        },
    };
    $('#upload_main_image').fileinput(img_fileinput_setting);

    function checkimageextension() {
        var fileInput =
            document.getElementById('upload_main_image');

        var filePath = fileInput.value;

        // Allowing file type
        var allowedExtensions =
            /(\.jpg|\.jpeg|\.png)$/i;

        if (!allowedExtensions.exec(filePath)) {
            alert('Invalid file type');
            fileInput.value = '';
            return false;
        } else {
            // Image preview
            if (fileInput.files && fileInput.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(
                            'imagePreview').innerHTML =
                        '<img src="' + e.target.result +
                        '"/>';
                };

                reader.readAsDataURL(fileInput.files[0]);
            }
        }
    }
</script>
@endsection
