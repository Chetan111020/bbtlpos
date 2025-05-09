@extends('layouts.app')
@section('css')
<style>
.wrapperss{
  display: inline-flex;
  background: #fff;
  height: 100px;
  width: 400px;
  align-items: center;
  justify-content: space-evenly;
  border-radius: 5px;
  padding: 20px 15px;
}
.wrapperss .option{
  background: #fff;
  height: 100%;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-evenly;
  margin: 0 10px;
  border-radius: 5px;
  cursor: pointer;
  padding: 0 10px;
  border: 2px solid lightgrey;
  transition: all 0.3s ease;
}
.wrapperss .option .dot{
  height: 20px;
  width: 20px;
  background: #d9d9d9;
  border-radius: 50%;
  position: relative;
}
.wrapperss .option .dot::before{
  position: absolute;
  content: "";
  top: 4px;
  left: 4px;
  width: 12px;
  height: 12px;
  background: #0069d9;
  border-radius: 50%;
  opacity: 0;
  transform: scale(1.5);
  transition: all 0.3s ease;
}
input[type="radio"]{
  display: none;
}
#option-1:checked:checked ~ .option-1,
#option-2:checked:checked ~ .option-2{
  border-color: #0069d9;
  background: #0069d9;
}
#option-1:checked:checked ~ .option-1 .dot,
#option-2:checked:checked ~ .option-2 .dot{
  background: #fff;
}
#option-1:checked:checked ~ .option-1 .dot::before,
#option-2:checked:checked ~ .option-2 .dot::before{
  opacity: 1;
  transform: scale(1);
}
.wrapperss .option span{
  font-size: 15px;
  color: #808080;
}
#option-1:checked:checked ~ .option-1 span,
#option-2:checked:checked ~ .option-2 span{
  color: #fff;
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
@section('content')
@section('title', 'Order Delivery')
<section class="content-header">
    <h1>@lang('Add Order Delivery') </h1>
</section>

<section class="content">
    <form action="/order-delivery/store" enctype="multipart/form-data" method="POST">
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
                                <option disabled selected>Select</option>
                                @foreach ($orders as $order)
                                    <option value="{{ $order->transaction_id }}" data-mobile="{{ $order->mobile }}"
                                        data-address="{{ $order->address }}" data-lat={{ $order->lat }}
                                        data-lgn="{{ $order->lgn }}">{{ $order->invoice_no }} - {{ $order->customer }}</option>
                                @endforeach
                            </select>
                        </div>
                        <span style="color:red" class="already-exists-order"></span>

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4" id="customer-details" style="text-align: left;"></div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div style="display: grid;">
                        <label>Select delivery status:</label>
                        <div class="wrapperss">
                            <input type="radio" name="deliveryStatus" id="option-1" value="delivered" checked>
                            <input type="radio" name="deliveryStatus" id="option-2" value="not_delivered">
                            <label for="option-1" class="option option-1">
                                <div class="dot"></div>
                                <span>Delivered</span>
                                </label>
                            <label for="option-2" class="option option-2">
                                <div class="dot"></div>
                                <span>Not Delivered</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <br/>

            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label>Upload Image</label>
                        <input type="file" name="img" id="upload_main_image" onchange="return checkimageextension()">
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label for="payment">Payment Amount:</label>
                        <input type="number" id="payment" name="payment_amount" class="form-control" step="0.01"
                            placeholder="Payment">
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
                                <option selected="selected" disabled>Select</option>
                                <option value="money_order">Money Order</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="zelle">Zelle</option>
                                <option value="cheque">Cheque</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="other">Other</option>
                                <option value="credit_memo">Credit Memo</option>
                                <option value="custom_pay_1">ACH</option>
                                <option value="custom_pay_2">WIRE</option>
                                <option value="custom_pay_3">Check Bounce Charges</option>
                                <option value="custom_pay_4">VENMO</option>
                                <option value="custom_pay_5">CASH APP</option>
                                <option value="custom_pay_6">BANK DEPOSIT</option>
                                <option value="custom_pay_7">BANK MOBILE DEPOSIT</option>
                            </select>
                        </div>
                    </div>
                    </div>
                    <div class="col-md-6 col-sm-6 hide" id="cheque_number1">
                    <div class="form-group">
                        <label for="">Cheque Number:</label>
                        <input type="number" name="cheque_number" class="form-control" placeholder="Cheque Number"
                            id="">
                    </div>
                </div>
            </div>
             <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label for="payment">Payment Amount 2:</label>
                        <input type="number" id="payment" name="payment_amount2" class="form-control" step="0.01"
                             placeholder="Payment">
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">

                    <div class="form-group">
                        <label for="method">Payment Method 2:</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            <select class="form-control select2 payment-method-2" name="payment_method2" style="width:100%;"
                                id="payment_method2">
                                <option selected="selected" disabled>Select</option>
                                <option value="money_order">Money Order</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="zelle">Zelle</option>
                                <option value="cheque">Cheque</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="other">Other</option>
                                <option value="credit_memo">Credit Memo</option>
                                <option value="custom_pay_1">ACH</option>
                                <option value="custom_pay_2">WIRE</option>
                                <option value="custom_pay_3">Check Bounce Charges</option>
                                <option value="custom_pay_4">VENMO</option>
                                <option value="custom_pay_5">CASH APP</option>
                                <option value="custom_pay_6">BANK DEPOSIT</option>
                                <option value="custom_pay_7">BANK MOBILE DEPOSIT</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 hide" id="cheque_number2">
                    <div class="form-group">
                        <label for="">Cheque Number 2:</label>
                        <input type="number" name="cheque_number2" class="form-control" placeholder="Cheque Number 2"
                            id="">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label for="payment">Payment Amount 3:</label>
                        <input type="number" id="payment" name="payment_amount3" class="form-control" step="0.01"
                             placeholder="Payment">
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">

                    <div class="form-group">
                        <label for="method">Payment Method 3:</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            <select class="form-control select2 payment-method-3" name="payment_method3" style="width:100%;"
                                id="payment_method3">
                                <option selected="selected" disabled>Select</option>
                                <option value="money_order">Money Order</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="zelle">Zelle</option>
                                <option value="cheque">Cheque</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="other">Other</option>
                                <option value="credit_memo">Credit Memo</option>
                                <option value="custom_pay_1">ACH</option>
                                <option value="custom_pay_2">WIRE</option>
                                <option value="custom_pay_3">Check Bounce Charges</option>
                                <option value="custom_pay_4">VENMO</option>
                                <option value="custom_pay_5">CASH APP</option>
                                <option value="custom_pay_6">BANK DEPOSIT</option>
                                <option value="custom_pay_7">BANK MOBILE DEPOSIT</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 hide" id="cheque_number3">
                    <div class="form-group">
                        <label for="">Cheque Number 3:</label>
                        <input type="number" name="cheque_number3" class="form-control" placeholder="Cheque Number 3"
                            id="">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="driver_notes">Driver Notes:</label>
                        <textarea id="driver_notes" name="note" class="form-control" rows="4" placeholder="Driver Notes"></textarea>
                    </div>
                </div>
            </div>
             <div class="row">
                <div class="col-md-12">
                    <label class="" for="">Signature:</label>
                    <br />
                    <div id="sig"></div>
                    <br />
                    <button id="clear" class="btn btn-danger btn-sm">Clear Signature</button>
                    <textarea id="signature64" name="signed" style="display: none"></textarea>
                </div>
            </div>
            <br>
            <br>
            <div class="row">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary pull-right" id="submitButton">Submit</button>
                </div>
            </div>
        @endcomponent
    </form>

</section>
@endsection
@section('javascript')
<link type="text/css" href="{{asset('assets/src/css/jquery.signature.css')}}" rel="stylesheet">
<script src="{{ asset('assets/src/js/jquery.signature.js') }}"></script>
<script src="{{ asset('assets/src/js/jquery.ui.touch-punch.js') }}"></script>
<script>
    // Function to get user's location
</script>

<script type="text/javascript">
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
            // $(this).closest('.row').find('.hide').removeClass('hide');
            $('#cheque_number2').removeClass('hide');
        } else {
            // $(this).closest('.row').find('.hide').addClass('hide');
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
 var sig = $('#sig').signature({
        syncField: '#signature64',
        thickness: 4,
        syncFormat: 'PNG'
    });
    $('#clear').click(function(e) {
        e.preventDefault();
        sig.signature('clear');
        $("#signature64").val('');
    });
    $("#orderno").on('change', function() {
        var transaction_id = $(this).val();
        var selectedTransaction = $(this).find('option:selected');
        var mobile = selectedTransaction.data('mobile');
        var address = selectedTransaction.data('address');
        var lat = selectedTransaction.data('lat');
        var lgn = selectedTransaction.data('lgn');


        // $('#customer-details').html('<p>Mobile: ' + mobile + '</p>' +
        //     '<p>Address: <a href="https://www.google.com/maps?q=' + address + '" target="_blank">' +
        //     address + '</a></p>');
           var mapsLink = 'https://www.google.com/maps/search/?api=1&query=' + lat + ',' + lgn;

        var customerDetailsHtml = '<p>Mobile: <a href="tel:'+mobile+'">' + mobile + '</a></p>' +
            '<p>Address: <a href="' + mapsLink + '" target="_blank">' + address + '</a></p>';

        $('#customer-details').html(customerDetailsHtml);
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

    function getUserLocation() {
        return new Promise((resolve, reject) => {
            if (navigator.geolocation) {
                const options = {
                    enableHighAccuracy: true, // Try to use the most accurate location source available
                    timeout: 10000, // Set a timeout of 10 seconds
                    maximumAge: 0 // Do not use a cached position
                };

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;

                        // Set the user's location in the hidden input field
                        document.getElementById('lat').value = latitude;
                        document.getElementById('long').value = longitude;

                        resolve({
                            latitude,
                            longitude
                        });
                    },
                    function(error) {
                        // Check if the error is due to the user denying location access
                        if (error.code === error.PERMISSION_DENIED) {
                            reject(new Error(
                                'User denied the request for Geolocation. Please allow location access.'
                            ));
                            toastr.error(
                                'Please allow location access');
                        } else {
                            reject(error);
                        }
                    },
                    options
                );
            } else {
                reject(new Error('Geolocation is not supported'));
            }
        });
    }

document.querySelector('form').addEventListener('submit', async function(event) {
    // Prevent the form from submitting immediately
    event.preventDefault();

    try {
        // Get user location
        const location = await getUserLocation();

        // Get the form data
        const formData = new FormData(this);

        // Add the user's location to the form data
        formData.append('lat', location.latitude);
        formData.append('long', location.longitude);

         setTimeout(function() {
            $('#submitButton').prop('disabled', false);
        }, 100);

        // Perform the form submission (you can use Fetch API or another AJAX library)
        const response = await fetch('/order-delivery/store', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        // Handle response data
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            if (data.errors) {
                // Display validation errors
                Object.keys(data.errors).forEach((field) => {
                    data.errors[field].forEach((error) => {
                        toastr.error(`${field}: ${error}`);
                    });
                });
            } else if (data.msg) {
                toastr.error(data.msg);
            }
        }
    } catch (error) {
        toastr.error('Something went wrong.');
        console.error(error);
    }
});


// old code
    // document.querySelector('form').addEventListener('submit', async function(event) {
    //     // Prevent the form from submitting immediately
    //     event.preventDefault();

    //     try{
    //         let location = await getUserLocation();
    //     }
    //     catch (error) {
    //         toastr.error(error);
    //         return false;
    //     }

    //     try {
    //         setTimeout(function() {
    //             $('#submitButton').prop('disabled', false);
    //         }, 100);

    //         // Get the form data
    //         const formData = new FormData(this);

    //         // Add the user's location to the form data
    //         formData.append('lat', location.latitude);
    //         formData.append('long', location.longitude);

    //         // Perform the form submission (you can use Fetch API or another AJAX library)
    //         const response = await fetch('/order-delivery/store', {
    //             method: 'POST',
    //             body: formData,
    //         });

    //         const data = await response.json();

    //         // Display Toastr message
    //         if (data.success) {
    //             window.location.href = data.redirect;
    //         } else {
    //             if (data.errors) {
    //             // Display validation errors
    //             Object.keys(data.errors).forEach((field) => {
    //                 data.errors[field].forEach((error) => {
    //                     toastr.error(`${field}: ${error}`);
    //                 });
    //             });
    //             } else if (data.msg) {
    //                 toastr.error(data.msg);
    //             }
    //         }
    //     } catch (error) {
    //         toastr.error('Something went wrong.');
    //         toastr.error(error);
    //     }
    // });

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
        var fileInput = document.getElementById('upload_main_image');

        var filePath = fileInput.value;

        // Allowing file type
        var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

        if (!allowedExtensions.exec(filePath)) {
            alert('Invalid file type');
            fileInput.value = '';
            return false;
        }
    }
</script>
@endsection
