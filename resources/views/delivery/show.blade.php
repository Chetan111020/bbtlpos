<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">{{ $customer->invoice_no }} - {{ $customer->customer_name }}
            </h4>
        </div>
       
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-9 invoice-col">
                     
                    <h3>{{ $customer->customer_name }}</h3>
                    <h4><b>Invoice No:</b> {{ $customer->invoice_no }}<br></h4>
                    <h4><b>Status:</b>
                        @if ($delivery->status === 'delivered')
                            <span class="label label-success">Delivered</span>
                        @else
                            <span class="label label-danger">Not Delivered</span>
                        @endif
                    </h4><br>
                    <h4><b>Note:</b>&nbsp;{{ $delivery->driver_note }}</h4>
                     

                </div>
                <div class="col-sm-3 col-md-3 invoice-col">
                    <div class="thumbnail">
                        @php
                            $myimg = '/img/default.png';
                            if (strpos($delivery->img, '.') !== false) {
                                $myimg = asset('/uploads' . $delivery->img);
                            }
                        @endphp
                        <a href="{{ $myimg }}" target="_blank" id="imgLink">
                            <img src="{{ $myimg }}" style="height:200px !important;" alt="image"
                                onclick="openImageInNewTab('{{ $myimg }}')">
                        </a>
                        {{-- <img src="{{ $myimg }}" style="height:200px !important;" alt="image"> --}}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-9 invoice-col">
                    @php
                        $paymentMethodMapping = [
                            'money_order' => 'Money Order',
                            'cash' => 'Cash',
                            'card' => 'Card',
                            'cheque' => 'Cheque',
                            'bank_transfer' => 'Bank Transfer',
                            'other' => 'Other',
                            'credit_memo' => 'Credit Memo',
                            'custom_pay_1' => 'ACH',
                            'custom_pay_2' => 'WIRE',
                            'custom_pay_3' => 'Check Bounce Charges',
                            'custom_pay_4' => 'VENMO',
                            'custom_pay_5' => 'CASH APP',
                            'custom_pay_6' => 'BANK DEPOSIT',
                            'custom_pay_7' => 'BANK MOBILE DEPOSIT',
                        ];
                    @endphp
                    <table class="table table-bordered" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Amount</th>
                                <th>Method</th>
                                @if($delivery->payment_method === 'cheque' || $delivery->payment_method2 === 'cheque' || $delivery->payment_method3 === 'cheque')
                                <th>Cheque Number</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    @if ($delivery->payment_amount)
                                        @format_currency($delivery->payment_amount)
                                    @endif
                                </td>
                                <td>{{ isset($paymentMethodMapping[$delivery->payment_method]) ? $paymentMethodMapping[$delivery->payment_method] : $delivery->payment_method }}
                                </td>
                                @if($delivery->payment_method === 'cheque')
                                <td>
                                    @if($delivery->cheque_number )
                                    {{ $delivery->cheque_number }}
                                    @endif
                                </td>
                                @endif
                            </tr>
                            <tr>
                                <td>
                                    @if ($delivery->payment_amount2)
                                        @format_currency($delivery->payment_amount2)
                                    @endif
                                </td>
                                <td>{{ isset($paymentMethodMapping[$delivery->payment_method2]) ? $paymentMethodMapping[$delivery->payment_method2] : $delivery->payment_method2 }}
                                </td>
                                @if($delivery->payment_method2 === 'cheque')
                                <td>
                                    @if($delivery->cheque_number2 )
                                    {{ $delivery->cheque_number2 }}
                                    @endif
                                </td>
                                @endif
                            </tr>
                            <tr>
                                <td>
                                    @if ($delivery->payment_amount3)
                                        @format_currency($delivery->payment_amount3)
                                    @endif
                                </td>
                                <td>{{ isset($paymentMethodMapping[$delivery->payment_method3]) ? $paymentMethodMapping[$delivery->payment_method3] : $delivery->payment_method3 }}
                                </td>
                                @if($delivery->payment_method3 === 'cheque')
                                <td>
                                    @if($delivery->cheque_number3 )
                                    {{ $delivery->cheque_number3 }}
                                    @endif
                                </td>
                                @endif
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-sm-3 col-md-3 invoice-col">
                    <div class="thumbnail">
                        @php
                            $myimg = '/img/default.png';
                            if (strpos($delivery->signature, '.') !== false) {
                                $myimg = asset('/uploads/delivery/signature/' . $delivery->signature);
                            }
                        @endphp
                        <a href="{{ $myimg }}" target="_blank" id="signatureLink">
                            <img src="{{ $myimg }}" style="height:200px !important;" alt="signature"
                                onclick="openImageInNewTab('{{ $myimg }}')">
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <strong>Delivery Log Detail</strong>
                </div>
                <div class="col-md-12">
                    <table class="table table-condensed bg-gray">
                        <tr class="bg-green">
                            <th>User Name</th>
                            <th>Action</th>
                            <th>Message</th>
                            <th>Date And Time</th>
                        </tr>
                        @if ($deliverylog->isEmpty()) 
                            <tr class="text-center">
                                <td colspan="4">Data not found.</td>
                            </tr>
                        @else
                        @foreach ($deliverylog as $log)
                            <tr>
                                <td>{{ $log->first_name }}</td>
                                @if ($log->description == 'added')
                                    <td>Added</td>
                                @elseif($log->description == 'edited')
                                    <td>Edited</td>
                                @endif
                                @php $str_array = explode(',',$log->message); @endphp
                                <td>
                                    @foreach ($str_array as $message)
                                        @if ($message != '')
                                            # {{ $message }}<br>
                                        @endif
                                    @endforeach
                                </td>
                                {{-- <td> {{ Carbon\Carbon::parse($log->datetime)->format('d-m-Y G:i A') }}</td> --}}
                                <td>{{ Carbon\Carbon::parse($log->datetime)->format('m/d/Y H:i A') }}</td>

                            </tr>
                        @endforeach
                        @endif
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
<script>
    function openImageInNewTab(imageUrl) {
        window.open(imageUrl, '_blank');
    }
</script>