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

        <!-- <a href="#" class="print-packing" data-href="' . route('sell.printInvoice', [$row->id]) . '"><i class="fas fa-file-alt" aria-hidden="true"></i> pack Slip</a> -->

            @auth
                <a href="{{action('SellController@index')}}" class="btn btn-success no-print" ><i class="fas fa-backward"></i>
                </a>
               <a href="{{action('Restaurant\OrderController@index')}}" class="btn btn-primary" ><i class="fa fas fa-list-alt"></i>
                Order Queue</a>
            @endauth
        </div>
    </div>
   <!--  <div class="row">
        <div class="col-md-8 col-md-offset-2 col-sm-12" style="border: 1px solid #ccc;">
            <div class="spacer"></div> -->
            <div id="invoice_content">
                {!! $receipt['html_content2'] !!}
            </div>
            <!-- <div class="spacer"></div>
        </div>
    </div> -->
    <div class="spacer"></div>
</div>
@stop

@section('javascript')
<script type="text/javascript">
//for send email.
$(document).ready(function(){
    $(document).on('click', '#email_invoice', function(){
        var tid = $("#tid").val();
        if(tid!="")
        {
            $.ajax({
                    method: 'GET',
                    url: '/sendemailforinvoice/'+tid,
                    dataType: 'json',
                    data : {invoice_show_type:'packing_slip'},
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
        }
    });
});
function showPageloader() {
        document.getElementById("loaderbg").style.display = "";
}
function hidePageloader() {
    document.getElementById("loaderbg").style.display = "none";
}
//for printing page .
$(document).ready(function(){
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