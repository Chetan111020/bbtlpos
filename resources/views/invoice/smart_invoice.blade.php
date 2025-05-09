@extends('layouts.invoice_ui')
@section('title', $title)
@section('content')

<div class="container">

    <div class="alert alert-primary msg_box1" role="alert" style="display:none;">
        <div class="spinner-border spinn" role="status" style="display:none;height: 1rem;width: 1rem;border-width: 2px;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <span class="msg_box">

        </span>
    </div>

    <div class="row d-print-none">
        <div class="col-md-8 offset-md-2 col-sm-12 d-flex justify-content-end py-3 px-0">
            @auth
            {{-- @if (auth()->user()->id == 6) --}}

            @if ($MAGIC)
            <a href="/v2/invoice-manager/print/{{ $id }}?preview=regen" class="btn btn-dark no-print mx-3" id="">
                <i class="fas fa-print"></i> Refresh
            </a>
            @endif

            <button type="button" class="btn btn-success no-print me-3" id="send_message"
                aria-label="Message"><i class="fas fa-file-pdf"></i> Send Message
            </button>
            {{-- @endif --}}
            <button type="button" class="btn btn-info no-print" id="email_invoice"
                 aria-label="Email"><i class="fas fa-file-pdf"></i> Email Invoice
            </button>
            <input type="hidden" id="tid" class="no-print" value="{{$id}}">
            @endauth
            <button type="button" class="btn btn-primary no-print mx-3" id="print_invoice"
                 aria-label="Print"><i class="fas fa-print"></i> @lang( 'messages.print')
            </button>
            @auth
                <a href="{{action('SellController@index')}}" class="btn btn-warning no-print">
                    All Sales
                </a>
            @endauth
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2 col-sm-12" style="">
            <div class="spacer"></div>
            <br/>
            <div id="invoice_content">
                @php
                    $tax_total = 0;
                @endphp
                @foreach ($tax_details as $tr)
                    @php
                        $tax_total += (float) str_replace(',', '',$tr['tax']);
                    @endphp
                @endforeach
                <div class="row" style="position: relative;">
                    <img src="{{ config('business-info.logo_slim') }}" style="position: absolute;width: 40%;height: auto;opacity: 0.1;left: 30%;" alt="Business Logo"/>
                    <div class="col-6">
                        {{-- <img src="{{ $receipt_details->logo }}" style="height:68px;" class="mx-2 my-auto" alt="Business Logo"/> --}}
                        <img src="{{ config('business-info.logo_slim') }}" style="height:68px;" class="my-auto" alt="Business Logo"/>
                        <br/>
                        <br/>
                        <div class="text-muted" style="font-size: smaller;">
                            <span class="text-uppercase">
                                <strong>{{ config('business-info.name') }}</strong> <br/>
                                {{ config('business-info.address_line_1') }} <br/>
                                {{ config('business-info.address_line_2') }} <br/>
                                <br/>
                            </span>
                            <div class="row">
                                <div class="col-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px;" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                        <path fill-rule="evenodd" d="M1.5 4.5a3 3 0 013-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 01-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 006.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 011.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 01-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="col-11">&nbsp;&nbsp;{{ config('business-info.mobile') }}</div>
                                <div class="col-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px;" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                        <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z" />
                                        <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z" />
                                    </svg>
                                </div>
                                <div class="col-11">&nbsp;&nbsp;{{ config('business-info.email') }}</div>
                                <div class="col-1">
                                    {{-- <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px;" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                        <path d="M21.721 12.752a9.711 9.711 0 00-.945-5.003 12.754 12.754 0 01-4.339 2.708 18.991 18.991 0 01-.214 4.772 17.165 17.165 0 005.498-2.477zM14.634 15.55a17.324 17.324 0 00.332-4.647c-.952.227-1.945.347-2.966.347-1.021 0-2.014-.12-2.966-.347a17.515 17.515 0 00.332 4.647 17.385 17.385 0 005.268 0zM9.772 17.119a18.963 18.963 0 004.456 0A17.182 17.182 0 0112 21.724a17.18 17.18 0 01-2.228-4.605zM7.777 15.23a18.87 18.87 0 01-.214-4.774 12.753 12.753 0 01-4.34-2.708 9.711 9.711 0 00-.944 5.004 17.165 17.165 0 005.498 2.477zM21.356 14.752a9.765 9.765 0 01-7.478 6.817 18.64 18.64 0 001.988-4.718 18.627 18.627 0 005.49-2.098zM2.644 14.752c1.682.971 3.53 1.688 5.49 2.099a18.64 18.64 0 001.988 4.718 9.765 9.765 0 01-7.478-6.816zM13.878 2.43a9.755 9.755 0 016.116 3.986 11.267 11.267 0 01-3.746 2.504 18.63 18.63 0 00-2.37-6.49zM12 2.276a17.152 17.152 0 012.805 7.121c-.897.23-1.837.353-2.805.353-.968 0-1.908-.122-2.805-.353A17.151 17.151 0 0112 2.276zM10.122 2.43a18.629 18.629 0 00-2.37 6.49 11.266 11.266 0 01-3.746-2.504 9.754 9.754 0 016.116-3.985z" />
                                    </svg> --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px;" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                        <path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 0 0 4.25 22.5h15.5a1.875 1.875 0 0 0 1.865-2.071l-1.263-12a1.875 1.875 0 0 0-1.865-1.679H16.5V6a4.5 4.5 0 1 0-9 0ZM12 3a3 3 0 0 0-3 3v.75h6V6a3 3 0 0 0-3-3Zm-3 8.25a3 3 0 1 0 6 0v-.75a.75.75 0 0 1 1.5 0v.75a4.5 4.5 0 1 1-9 0v-.75a.75.75 0 0 1 1.5 0v.75Z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="col-11">&nbsp;&nbsp;{{ config('business-info.website_url_short') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 text-end">
                        <h1 class="display-5"><b>INVOICE</b></h1>
                        <br/>
                        <div class="row">
                            <small class="my-auto col-6">
                                Invoice
                                <strong>#{{ $receipt_details->invoice_no }}</strong>
                            </small>
                            <small class="my-auto col-6">
                                Date:
                                <strong>{{ date('m/d/Y',strtotime($receipt_details->invoice_date)) }}</strong>
                            </small>
                        </div>
                        <br/>
                        <div class="text-muted" style="font-size: smaller;">
                            <b>Invoice To</b><br/>
                            <span class="text-uppercase">
                                @if (!empty($receipt_details->customer_name))
                                    {{ $receipt_details->customer_name }}
                                    ({{ $receipt_details->contact_id }})
                                @endif
                                <br>
                                @if (!empty($receipt_details->address_line_1))
                                    {!! $receipt_details->address_line_1 !!}
                                @endif
                                @if (!empty($receipt_details->address_line_2))
                                    {!! $receipt_details->address_line_2 !!}
                                @endif
                                @if (!empty($receipt_details->city))
                                    {!! $receipt_details->city !!}
                                @endif
                                <br/>
                                @if (!empty($receipt_details->state))
                                    {!! $receipt_details->state !!}
                                @endif
                                @if (!empty($receipt_details->zip_code))
                                    {{ $receipt_details->zip_code }}
                                @endif
                                <br/>
                                @if (!empty($receipt_details->email))
                                    {{ $receipt_details->email }} <br/>
                                @endif
                            </span>


                            @if (!empty($receipt_details->mobile))
                                Mobile: {{ $receipt_details->mobile }}
                            @endif

                            <br/>
                            Tobacco Lic: {{ !empty($receipt_details->tobacco_license_no) ? strtoupper($receipt_details->tobacco_license_no) : 'None' }}
                            <br/>
                            Tax ID: {{ !empty($receipt_details->tex) ? strtoupper($receipt_details->tex) : 'None' }}
                        </div>
                    </div>
                </div>
                <br/>
                <div class="d-flex" style="font-size: smaller;">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th style="width: 25px;">#</th>
                                <th>SKU</th>
                                <th style="width: 60%;">DESCRIPTION</th>
                                <th class="text-end">QTY</th>
                                <th class="text-end">PRICE</th>
                                <th class="text-end">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $last_box = -100;
                            @endphp
                            @foreach ($new_lines_arr as $item)
                                @if($last_box != $item['box_id'])
                                    @php
                                        $last_box = $item['box_id'];
                                    @endphp
                              @php
    $hideBoxElement = ($box === 'no');
@endphp

@if(!$hideBoxElement)
    <tr class="table-info">
        <th colspan="6">BOX {{ $last_box == -1 ? 'N/A' : $last_box }}</th>
    </tr>
@endif

                                @endif
                                <tr class="{{ $item['quantity'] == 0 ? 'strikeout' : '' }}">
                                    <td class="">{{ $loop->index + 1 }}</td>
                                    <td class="">{{ $item['sub_sku'] }}</td>
                                    <td style="font-size: 12px;">
                                        @if (!empty($item['item_code']))
                                            <small class="text-muted">{{ $item['item_code'] }}</small><br/>
                                        @endif
                                        {{ $item['name'] ?? '' }}
                                    </td>
                                    <td class="text-end">{{ $item['quantity'] }}</td>
                                    <td class="text-end">{{ $item['unit_price'] ?? '' }}</td>
                                    <td class="text-end">{{ $item['line_total'] ?? '' }}</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
                <div class="d-flex no-break-print" style="font-size: smaller;">
                    <div style="width: 60%;">
                        <strong>Packing Box Count: {{ $receipt_details->box_qty ?? 0 }}</strong>
                        <br/>
                        <div>
                            <br/>
                            <strong>Payment Information</strong>
                            <br/>
                            <span>
                                Invoice Status: {{ ucwords($receipt_details->statuss) }}
                                @if ($receipt_details->statuss == 'paid')
                                    <svg xmlns="http://www.w3.org/2000/svg" style="height:15px;margin-top:-2px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="text-success">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" style="height:15px;margin-top:-2px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="text-warning">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    </svg>
                                @endif
                            </span>
                            <br/>
                            @if (empty($MAGIC))
                                <span>
                                    Open Balance Due: $ {{ number_format((float) $receipt_details->opening, 2) }}
                                </span>
                            @endif
                            <br/>
                        </div>
                    </div>
                    <table class="table table-sm" style="width: 40% !important;height:100%;">
                        <tbody>
                            <tr>
                                <th class="text-end">Subtotal</th>
                                <td class="text-end" style="width:35%;">
                                    <b>{{ $receipt_details->subtotal }}</b>
                                </td>
                            </tr>

                            <tr>
                                <th class="text-end">Tax Applied</th>
                                <td class="text-end"><b>$ {{ number_format($tax_total, 2) }}</b></td>
                            </tr>

                            @if (!empty($receipt_details->shipping_charges))
                            <tr>
                                <th class="text-end">Shipping Charges</th>
                                <td class="text-end"><b>{{ $receipt_details->shipping_charges }}</b></td>
                            </tr>
                            @endif

                            @if (!empty($receipt_details->discount))
                            <tr>
                                <th class="text-end">Discount</th>
                                <td class="text-end"><b>{{ $receipt_details->discount }}</b></td>
                            </tr>
                            @endif

                            <tr>
                                <th class="text-end">Invoice Total</th>
                                <td class="text-end"><b>{{ $receipt_details->total }}</b></td>
                            </tr>

                            @if (!empty($receipt_details->total_paid))
                            <tr>
                                <th class="text-end">Total Paid</th>
                                <td class="text-end"><b>{{ $receipt_details->total_paid }}</b></td>
                            </tr>
                            @endif

                            @if (!empty($receipt_details->total_due))
                            <tr>
                                <th class="text-end">Invoice Due</th>
                                <td class="text-end"><b>{{ $receipt_details->total_due }}</b></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div style="font-size: smaller;">
                    <br/>
                    <span class="text-muted mb-1">
                        We greatly appreciate your support and business!
                        <br/>
                    </span>
                    <small class="text-muted mb-1">
                        Customers are responsible for paying their Local, State & Federal Excise taxes for applicable products.
                    </small>
                    <br/>
                    <span>&nbsp;</span>
                </div>
            </div>
            <div class="spacer"></div>
        </div>
    </div>
    <br/>
    <div class="spacer"></div>
</div>
@stop

@section('javascript')

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript">
//for send email.
$(document).ready(function(){
    $(document).on('click', '#email_invoice', function(){
        var tid = $("#tid").val();
        if(tid != "")
        {
            // var element = document.getElementById('invoice_content');
            // var opt = {
            //     margin:       0.2,
            //     filename:     'myfile.pdf',
            //     pagebreak:    { mode: ['css', 'legacy'], avoid: 'tr' },
            //     image:        { type: 'jpeg', quality: 1 },
            //     html2canvas:  { scale: 2 },
            //     jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            // };
            // html2pdf().from(element).set(opt).toPdf().output('datauristring').then(function (pdfAsString) {
            //     var arr = pdfAsString.split(',');
            //     pdfAsString= arr[1];
            //     $.ajaxSetup({
            //         headers: {
            //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
            //         }
            //     });
            //     $.ajax({
            //         method: 'POST',
            //         url: '/send-mail-with-pdf/'+tid,
            //         dataType: 'json',
            //         data: {
            //             'pdf_b64': pdfAsString
            //         },
            //         beforeSend: function() {
            //             showPageloader();
            //         },
            //         success: function(result) {
            //             if(result.success == false){
            //                 hidePageloader();
            //                 toastr.error(result.message);
            //             }else{
            //                 hidePageloader();
            //                 toastr.success(result.message);
            //             }
            //         },
            //     });
            // });

            @if(empty($MAGIC))

            $.ajax({
                method: 'GET',
                url: '/sendemailforinvoice/'+tid,
                dataType: 'json',
                data : {invoice_print:'print'},
                beforeSend: function() {
                    // showPageloader();
                    $('.msg_box').html('Sending Email, please wait ...').show();
                    $('.spinn').show();
                    $('.msg_box1').show();
                },
                success: function(result) {
                    if(result.success == false){
                        // hidePageloader();
                        $('.msg_box1').show();
                        $('.msg_box').html('Something went wrong !');
                        // toastr.error(result.message);
                    }else{
                        $('.msg_box1').show();
                        $('.msg_box').html('Email sent !');
                        // hidePageloader();
                        // toastr.success(result.message);
                    }
                    $('.spinn').hide();
                },
            });

            @else

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            $.ajax({
                method: 'POST',
                url: '/send-mail-with-pdf/'+tid,
                dataType: 'json',
                data: {

                },
                beforeSend: function() {
                    // showPageloader();
                    $('.msg_box').html('Sending Email, please wait ...').show();
                    $('.spinn').show();
                    $('.msg_box1').show();
                },
                success: function(result) {
                    if(result.success == false){
                        // hidePageloader();
                        $('.msg_box').html('Something went wrong !');
                        // toastr.error(result.message);
                    }else{
                        // hidePageloader();
                        $('.msg_box').html('Email sent !');
                        // toastr.success(result.message);
                    }

                    $('.spinn').hide();
                },
            });

            @endif


        }
    });
    $(document).on('click', '#send_message', function(){
        var tid = $("#tid").val();
        if(tid != "")
        {
            number = '';
            msg = '';
            receipt_type = '';

            @auth

            number = '{{ $wa_number ?? "" }}';
            msg = '{{ $wa_link ?? "" }}';

            @if(empty($MAGIC))
                receipt_type = 'invoice';
            @else
                receipt_type = 'revision';
            @endif

            @endauth

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            $.ajax({
                method: 'POST',
                url: '/api-waha',
                dataType: 'json',
                data: {
                    number: number,
                    msg: msg,
                    tid: tid,
                    receipt_type: receipt_type,
                },
                beforeSend: function() {
                    $('.msg_box').html('Sending message, please wait ...').show();
                    $('.spinn').show();
                    $('.msg_box1').show();
                },
                success: function(result) {
                    $('.msg_box').html(result.msg);
                    $('.spinn').hide();
                },
            });

        }
    });
});

//for printing page .
$(document).ready(function(){
    var INTERNAL_PRINT_CALL = true;

    $(document).on('click', '#print_invoice', function(){
        INTERNAL_PRINT_CALL = false;
        $('#invoice_content').printThis({
            afterPrint: function(){
                INTERNAL_PRINT_CALL = true;
            }
        });
    });

    $(document).bind("keyup keydown", function(e){
        if(e.ctrlKey && e.keyCode == 80 && INTERNAL_PRINT_CALL){
            e.preventDefault();
            $('#print_invoice').trigger('click');
            return false;
        }
    });
});
@if(!empty(request()->input('print_on_load')))
    $(window).on('load', function(){
        $('#invoice_content').printThis();
    });
@endif
</script>
@endsection