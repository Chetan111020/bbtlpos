@extends('layouts.app')
@section('title', __( 'sale.drafts'))
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('sale.drafts')
    </h1>
</section>
<div id="loaderbg" style="position: fixed; top: 0px; left: 0px; background: rgba(0, 0, 0, 0.6) none repeat scroll 0% 0%;
 z-index: 5; width: 100%; height: 100%; display: none;" class="no-print">
    <div id="loader" class="no-print"></div>
</div>
<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_location_id',  __('purchase.business_location') . ':') !!}

                {!! Form::select('sell_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all') ]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_customer_id',  __('contact.customer') . ':') !!}
                {!! Form::select('sell_list_filter_customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('created_by',  __('report.user') . ':') !!}
                {!! Form::select('created_by', $sales_representative, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
            </div>
        </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-primary'])
        @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{action('SellPosController@create')}}">
                <i class="fa fa-plus"></i> @lang('messages.add')</a>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="sell_table">
                <thead>
                    <tr>
                        <th>@lang('messages.date')</th>
                        <th>@lang('purchase.ref_no')</th>
                        <th>Amount</th>
                        <th>@lang('sale.customer_name')</th>
                        <!--<th>@lang('sale.location')</th>-->
                        <th>Open Balance</th>
                        <th>@lang('Web Order No')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>
<!-- /.content -->
@stop
@section('javascript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script type="text/javascript">
$(document).ready( function(){
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            sell_table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        sell_table.ajax.reload();
    });
    sell_table = $('#sell_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        "ajax": {
            "url": '/sells/draft-dt?is_quotation=0',
            "data": function ( d ) {
                if($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }

                if($('#sell_list_filter_location_id').length) {
                    d.location_id = $('#sell_list_filter_location_id').val();
                }
                d.customer_id = $('#sell_list_filter_customer_id').val();

                if($('#created_by').length) {
                    d.created_by = $('#created_by').val();
                }
            }
        },
        columnDefs: [ {
            "targets": 4,
            "orderable": false,
            "searchable": false
        } ],
        columns: [
            { data: 'transaction_date', name: 'transaction_date'  },
            { data: 'invoice_no', name: 'invoice_no'},
            { data: 'final_total', name: 'final_total', render: $.fn.dataTable.render.number( ',', '.', 2, '$' )},
            { data: 'name', name: 'contacts.name'},
            // { data: 'business_location', name: 'bl.name'},
            { data: 'balance_due', name: 'balance_due'},
            { data: 'woocommerce_order_id', name: 'woocommerce_order_id'},
            { data: 'action', name: 'action'}
        ],
        "fnDrawCallback": function (oSettings) {
            __currency_convert_recursively($('#purchase_table'));
        }
    });
    $(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_customer_id, #created_by',  function() {
        sell_table.ajax.reload();
    });

    function showPageloader() {
    document.getElementById("loaderbg").style.display = "";
    }
    function hidePageloader() {
        document.getElementById("loaderbg").style.display = "none";
    }
// function url_content(url){
//     return $.get(url);
// }



//             // alert(test);


//             var opt = {
//                 margin:       0.2,
//                 filename:     'myfile.pdf',
//                 pagebreak:    { mode: ['css', 'legacy'], avoid: 'tr' },
//                 image:        { type: 'jpeg', quality: 1 },
//                 html2canvas:  { scale: 2 },
//                 jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
//             };
//             html2pdf().from(element).set(opt).toPdf().output('datauristring').then(function (pdfAsString) {
//                 var arr = pdfAsString.split(',');
//                 pdfAsString= arr[1];pdfAsString
//                 $.ajaxSetup({
//                     headers: {
//                         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                     }
//                 });
//                 // $.ajax({
//                 //     method: 'GET',
//                 //     url: '/send-mail-with-attach-pdf/'+tid,
//                 //     dataType: 'json',
//                 //     data: {
//                 //         // 'pdf_b64': pdfAsString
//                 //     },
//                 //     // beforeSend: function() {
//                 //     //     showPageloader();
//                 //     // },
//                 //     success: function(result) {
//                 //         // alert(result);
//                 //         alert(JSON.stringify(result));

//                 //         if(result.success == false){
//                 //             hidePageloader();
//                 //             toastr.error(result.message);
//                 //         }else{
//                 //             hidePageloader();
//                 //             toastr.success(result.message);
//                 //         }
//                 //     },
//                 // });


//                 $.ajax({
//                     method: 'GET',
//                     url: '/send-mail-with-attach-pdf/'+tid,
//                     dataType: 'json',
//                     // beforeSend: function() {
//                     //     showPageloader();
//                     // },
//                     data: {
//                         'pdf_b64': pdfAsString
//                     },
//                     success: function(result) {
//                     //   alert(JSON.stringify(result));


//                         if(result.success == false){
//                             // hidePageloader();
//                             // toastr.error(result.message);
//                         }else{
//                             // hidePageloader();
//                             // toastr.success(result.message);
//                         }
//                     },
//                 });
//             });

//             // $.ajax({
//             //     method: 'GET',
//             //     url: '/send-mail-with-attach-pdf/'+tid,
//             //     dataType: 'json',
//             //     // beforeSend: function() {
//             //     //     showPageloader();
//             //     // },
//             //     success: function(result) {
//             //       alert(JSON.stringify(result));


//             //         if(result.success == false){
//             //             hidePageloader();
//             //             toastr.error(result.message);
//             //         }else{
//             //             hidePageloader();
//             //             toastr.success(result.message);
//             //         }
//             //     },
//             // });

//         }
//     });
});
</script>

@endsection