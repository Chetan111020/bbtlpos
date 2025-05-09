@extends('layouts.app')
@section('title', __( 'All Invoices' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'All Invoices' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
  <style>
  .loader {
        border: 4px solid #f3f3f3;
        border-radius: 100%;
        border-top: 7px solid blue;
        border-right: 7px solid green;
        border-bottom: 7px solid red;
        border-left: 7px solid pink;
        width: 35px;
        height: 35px;
        -webkit-animation: spin 2s linear infinite;
        animation: spin 2s linear infinite;
    }
   @-webkit-keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-4" style="display: none;">
              <div class="form-group">
                <label>Enter Invoice No</label>
                <input  type="text" name="search" id="search" class="form-control" placeholder="Invoice No" />
              </div>
            </div>
            <div class="col-md-4" style="display: none;">
                <div class="form-group">
                    <label>Tax Rates</label>
                    {!! Form::select('tax_rates', $tax_rates, null, ['class' => 'form-control select2','id' => 'tax_rates', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}  
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Date Range</label>
                    <input name="fromDate" readonly="" class="form-control" id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;" autocomplete="off">
                </div>
            </div>
            <div class="col-md-4" style="display: none;">
                <div class="form-group">
                    <label>Category</label>
                    {!! Form::select('product_category[]', $categoriesdata, null, ['class' => 'form-control select2', 'style' => 'width:100%','multiple','id' => 'product_category']); !!}
                </div>
            </div>
            <div class="col-md-4" style="display: none;">
                <div class="form-group">
                    <label>State</label>
                    <select id="contact_state"  name="state" class="form-control select2" style="width: 100%!important;">
                      <option value="">All</option>
                      <option value="Alabama">Alabama</option>
                      <option value="Alaska">Alaska</option>
                      <option value="Arizona">Arizona</option>
                      <option value="Arkansas">Arkansas</option>
                      <option value="California">California</option>
                      <option value="Colorado">Colorado</option>
                      <option value="Connecticut">Connecticut</option>
                      <option value="Delaware">Delaware</option>
                      <option value="District Of Columbia">District Of Columbia</option>
                      <option value="Florida">Florida</option>
                      <option value="Georgia">Georgia</option>
                      <option value="Hawaii">Hawaii</option>
                      <option value="Idaho">Idaho</option>
                      <option value="Illinois">Illinois</option>
                      <option value="Indiana">Indiana</option>
                      <option value="Iowa">Iowa</option>
                      <option value="Kansas">Kansas</option>
                      <option value="Kentucky">Kentucky</option>
                      <option value="Louisiana">Louisiana</option>
                      <option value="Maine">Maine</option>
                      <option value="Maryland">Maryland</option>
                      <option value="Massachusetts">Massachusetts</option>
                      <option value="Michigan">Michigan</option>
                      <option value="Minnesota">Minnesota</option>
                      <option value="Mississippi">Mississippi</option>
                      <option value="Missouri">Missouri</option>
                      <option value="Montana">Montana</option>
                      <option value="Nebraska">Nebraska</option>
                      <option value="Nevada">Nevada</option>
                      <option value="New Hampshire">New Hampshire</option>
                      <option value="New Jersey">New Jersey</option>
                      <option value="New Mexico">New Mexico</option>
                      <option value="New York">New York</option>
                      <option value="North Carolina">North Carolina</option>
                      <option value="North Dakota">North Dakota</option>
                      <option value="Ohio">Ohio</option>
                      <option value="Oklahoma">Oklahoma</option>
                      <option value="Oregon">Oregon</option>
                      <option value="Pennsylvania">Pennsylvania</option>
                      <option value="Rhode Island">Rhode Island</option>
                      <option value="South Carolina">South Carolina</option>
                      <option value="South Dakota">South Dakota</option>
                      <option value="Tennessee">Tennessee</option>
                      <option value="Texas">Texas</option>
                      <option value="Utah">Utah</option>
                      <option value="Vermont">Vermont</option>
                      <option value="Virginia">Virginia</option>
                      <option value="Washington">Washington</option>
                      <option value="West Virginia">West Virginia</option>
                      <option value="Wisconsin">Wisconsin</option>
                      <option value="Wyoming">Wyoming</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group" id="gen_down">
                    <button class="btn btn-primary" id="generate" style="margin-top: 25px;">Show Invoices</button>
                    <input type="hidden" id="download_path" value="">
                    <button class="btn btn-primary" id="download" style="margin-top: 25px;">Export Invoices</button>
                </div>
            </div>
        </div>
    </div>
    <div id="error_mssage" style="color: red;margin-left:16px;">
          @if (\Session::has('success'))
            <div class="alert">
                    {!! \Session::get('success') !!}
            </div>
          @endif
        </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <h4 align="left">Total Data : <span id="total_records"></span></h4>
                    <div id="searchdata">
                    </div>
                </div>
                <div id="mytable" class="loader" style="display: none;"></div>
            @endcomponent

        </div>
        
    </div>
</section>
<!-- /.content -->
@endsection

@section('javascript')


<script type="text/javascript">
$(function() {
    var fromDate = moment().subtract(0, 'days');
    var toDate = moment();
    function cb(toDate, toDate) {
        $('#reportrange span').html(fromDate.format('YYYY-MM-DD') + ' - ' + toDate.format('YYYY-MM-DD'));
    }
    $('#reportrange').daterangepicker({
        startDate: fromDate,
        endDate: toDate,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
           'This Year': [moment().startOf('year'), moment().endOf('year')],
           'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
           'This month to Last Year': [moment().subtract(1, 'year').startOf('year'), moment().endOf('year')],
        }
    }, cb);
    cb(fromDate, toDate);
});

$(document).ready(function(){
 $('.select2').select2();

$( "#generate" ).click(function() {
  $('#error_mssage').html("");
  $("#download_path").val("");
  var ranges = $("input[name=fromDate]").val();
  var rates = $("#tax_rates").val();
  var query = $("input[name=search]").val();
  var state = $("#contact_state").val();
  var product_category = $("#product_category").val();
  var unit_total_amount = 0;
  var unit_discount_amount = 0;
  var unit_total_paid = 0;
  var unit_sell_due = 0;
  var total_row = 0;


  if(ranges!="")
  {
   var upload_dir_path = '';
   var chunkAndLimit = 5000;
   $('#searchdata').html("").hide();
   $("#generate").prop("disabled","disabled");
   $("#download").prop("disabled","disabled");
   doChunkedExport(0,chunkAndLimit,ranges,rates,query,state,product_category,upload_dir_path,"{{ route('searchinvoiceall') }}",chunkAndLimit,unit_total_amount,unit_discount_amount,unit_total_paid,unit_sell_due,total_row);
  }
  else
  {
    $('#error_mssage').html("Please select Date Range!!");
  }
});

});

//var count=0;
//$('#reportrange').datepicker().change(function() {
$(document).on('change', '#reportrange', function(){
   // count++;
  //if(count==3) 
  //{
    $('#total_records').text("");
    $('#searchdata').html("").hide();
    //count = 0;
  //}
});
function doChunkedExport(start,limit,ranges,rates,query,state,product_category,upload_dir_path,action,chunkSize,unit_total_amount,unit_discount_amount,unit_total_paid,unit_sell_due,total_row){
 $.ajax({
           url:action,
           method:'POST',
           data:{
             upload_dir_path:upload_dir_path,
             ranges:ranges,
             rates:rates,
             query:query,
             state:state,
             product_category:product_category,
             start:start,
             limit:limit,
             _token : '{{ csrf_token() }}',
             unit_total_amount:unit_total_amount,
             unit_discount_amount:unit_discount_amount,
             unit_total_paid:unit_total_paid,
             unit_sell_due:unit_sell_due,
             total_row:total_row,
            },
           cache : false,
           dataType:'json',
           sortInitialOrder: "desc",
           beforeSend: function(){
            // Show image container
            $('#total_records').text("");
            //$("#reportrange").prop("disabled","disabled");
            $(".loader").show();
           },
           success:function(data)
           {
                if(data.result=='next'){
                    //alert(start);
                    if(start==0)
                    {
                      $('#searchdata').append(data.table_data);
                    }
                    else
                    {
                       $('#invoice_all_table tbody').append(data.table_data);
                    }
                    start = start + chunkSize;
                    unit_total_amount = data.unit_total_amount;
                    unit_discount_amount = data.unit_discount_amount;
                    unit_total_paid = data.unit_total_paid;
                    unit_sell_due = data.unit_sell_due;
                    total_row = data.total_data;
                    
                    upload_dir_path = data.upload_dir_path;
                    doChunkedExport(start,limit,ranges,rates,query,state,product_category,upload_dir_path,action,chunkSize,unit_total_amount,unit_discount_amount,unit_total_paid,unit_sell_due,total_row);
                }else
                {   
                    //alert(start);                
                    $(".loader").hide();
                    if(start==0)
                    {
                      $('#searchdata').append(data.table_data).show();
                    }
                    else
                    {
                       $('#invoice_all_table tbody').append(data.table_data);
                       $('#searchdata').show();
                    }
                    $('#searchdata').prepend(data.top_output_header);
                    $('#total_records').text(data.total_data);
                    $('#generate').prop('disabled', false);
                    $('#download').prop('disabled', false);
                    $("#download_path").val(data.upload_dir_path);
                    /*$('#reportrange').prop('disabled', false);
                    $("#reportrange").datepicker({
                      format: "mm-yyyy",
                      startView: "months", 
                      minViewMode: "months",
                      autoclose: true,
                    });*/
                }
                
           },
           complete:function(data){
            // Hide image container
           }
          });
}


$( "#download" ).click(function() {
  var download_path = $("#download_path").val();

  var ranges_export = $("input[name=fromDate]").val();
  var rates_export  = $("#tax_rates").val();
  var query_export  = $("input[name=search]").val();
  var state_export  = $("#contact_state").val();
  var product_category_export  = $("#product_category").val();
  //$('#searchdata').html("").hide();
  //$("#generate").prop("disabled","disabled");
  //$("#download").prop("disabled","disabled");

  var query = {
        ranges:ranges_export,
        rates:rates_export,
        query:query_export,
        state:state_export,
        product_category:product_category_export,
    }

  var url = "{{ route('exportinvoiceall') }}?" + $.param(query)

  window.location = url;
  //doChunkedExporttoExcel(ranges_export,rates_export,query_export,state_export,product_category_export,"{{ route('exportinvoiceall') }}");
  /*if(download_path!="")
  {
    window.location = 'download_zip/'+download_path;
  }
  else
  {
    $('#error_mssage').html("Please generate invoices first!!");
  }*/
});

/*function doChunkedExporttoExcel(ranges,rates,query,state,product_category,action)
{
    $.ajax({
           url:action,
           method:'POST',
           data:{
             ranges:ranges,
             rates:rates,
             query:query,
             state:state,
             product_category:product_category,
            },
           cache : false,
           dataType:'json',
           sortInitialOrder: "desc",
           beforeSend: function(){
            // Show image container
            $('#total_records').text("");
            //$("#reportrange").prop("disabled","disabled");
            $(".loader").show();
           },
           success:function(data)
           {
                    //alert(start);                
                    $(".loader").hide();
                    $('#searchdata').append("Donloaded").show();
                    //$('#total_records').text(data.total_data);
                    $('#generate').prop('disabled', false);
                    $('#download').prop('disabled', false);                   
                
                
           },
           complete:function(data){
            // Hide image container
           }
    });
}*/
</script>
@endsection