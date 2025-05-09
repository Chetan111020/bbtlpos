@extends('layouts.guest')
@section('title', $title)
@section('content')
<div class="container">
    <div id="loaderbg" style="position: fixed; top: 0px; left: 0px; background: rgba(0, 0, 0, 0.6) none repeat scroll 0% 0%;
     z-index: 5; width: 100%; height: 100%; display: none;" class="no-print">
        <div id="loader" class="no-print"></div>
    </div>
    <div class="spacer"></div>
    <div class="row">
        <div class="col-md-12 text-right" >
            @auth
            <button type="button" class="btn btn-primary no-print" id="email_invoice"
                 aria-label="Email"><i class="fas fa-envelope"></i> Email Invoice
            </button>
            <input type="hidden" id="tid" class="no-print" value="{{$tid}}">
            @endauth
            <button type="button" class="btn btn-primary no-print" id="print_invoice"
                 aria-label="Print"><i class="fas fa-print"></i> @lang( 'messages.print')
            </button>
            @auth
                <a href="{{action('SellController@index')}}" class="btn btn-success no-print" ><i class="fas fa-backward"></i>
                </a>
		<a href="{{action('Restaurant\OrderController@index')}}" class="btn btn-primary no-print" ><i class="fa fas fa-list-alt"></i>
                Order Queue</a>
            @endauth
        </div>
    </div>
    <div class="row">
        <div id="for_pdf_inv_parent" class="col-md-8 col-md-offset-2 col-sm-12" style="border: 1px solid #ccc;">
            <div class="spacer"></div>
            <div id="invoice_content" style="padding-left:5px;padding-right:5px;">
                {!! $receipt['html_content'] !!}
            </div>
            <div class="spacer"></div>
        </div>
    </div>
    <div class="spacer"></div>
</div>
@stop

@section('javascript')
{{--  cdn also available in asset/libs/html2pdf  --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript">
    const qrcode = new QRCode(document.getElementById('qrcode'), {
        text: '{{$url}}',
        width: 85,
        height: 85,
        colorDark : '#000000',
        colorLight : '#ffffff',
        correctLevel : QRCode.CorrectLevel.L
    });

function demoFromHTML() {
    // let doc = new jsPDF();
    // // doc.fromHtml($('#invoice_content'), function () {
    // //     doc.save('Test.pdf');
    // // });
    // doc.html($('#invoice_content').html(), {
    //     callback: function(doc) {
    //         // Save the PDF
    //         doc.save('sample-document.pdf');
    //     },
    //     x: 0,
    //     y: 0
    // });
    // const element = document.getElementById('for_pdf_inv_parent');
    // // Choose the element and save the PDF for your user.
    // html2pdf().from(element).save();

    var element = document.getElementById('invoice_content');
    var opt = {
        margin:       0.2,
        filename:     'myfile.pdf',
        pagebreak:    { mode: ['css', 'legacy'], avoid: 'tr' },
        image:        { type: 'jpeg', quality: 1 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
    };

    // New Promise-based usage:
    html2pdf().set(opt).from(element).save();
}

function showPageloader() {
    document.getElementById("loaderbg").style.display = "";
}
function hidePageloader() {
    document.getElementById("loaderbg").style.display = "none";
}

//for send email.
$(document).ready(function(){
    // demoFromHTML();
    $(document).on('click', '#email_invoice', function(){
        var tid = $("#tid").val();
        if(tid != "")
        {
            var element = document.getElementById('invoice_content');
            var opt = {
                margin:       0.2,
                filename:     'myfile.pdf',
                pagebreak:    { mode: ['css', 'legacy'], avoid: 'tr' },
                image:        { type: 'jpeg', quality: 1 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().from(element).set(opt).toPdf().output('datauristring').then(function (pdfAsString) {
                var arr = pdfAsString.split(',');
                pdfAsString= arr[1];
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
                        'pdf_b64': pdfAsString
                    },
                    beforeSend: function() {
                        showPageloader();
                    },
                    success: function(result) {
                        if(result.success == false){
                            hidePageloader();
                            toastr.error(result.message);
                        }else{
                            hidePageloader();
                            toastr.success(result.message);
                        }
                    },
                });
            });

            // $.ajax({
            //     method: 'GET',
            //     url: '/sendemailforinvoice/'+tid,
            //     dataType: 'json',
            //     beforeSend: function() {
            //         showPageloader();
            //     },
            //     success: function(result) {
            //         if(result.success == false){
            //             hidePageloader();
            //             toastr.error(result.message);
            //         }else{
            //             hidePageloader();
            //             toastr.success(result.message);
            //         }
            //     },
            // });
        }
    });

    $(document).on('click', '#print_invoice', function(){
        $('#invoice_content').printThis();
    });
});

@if(!empty(request()->input('print_on_load')))
    $(window).on('load', function(){
        $('#invoice_content').printThis();
    });
@endif
</script>
@endsection