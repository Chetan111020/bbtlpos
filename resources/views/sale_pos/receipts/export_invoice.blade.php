<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.css">
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <title>{{ config('business-info.name') }} | Export Invoices</title>
</script>

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
  </head>
  <body>
    <div class="container">
      <h3>Export Invoices</h3>
<div class="row no-print">
  <div class="form-group col-md-3">
      <label>Enter Invoice No</label>
      <input  type="text" name="search" id="search" class="form-control" placeholder="Invoice No" />
  </div>
    <div class="form-group col-md-3" style="padding-right: 0px;">
        <label>Select Date</label>
        <input name="fromDate" readonly="" class="form-control" id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;" autocomplete="off">
    </div>
    <div class="form-group col-md-3">
      <label>Select Customer</label>
      <select class="form-control select2" name="contact_id" id="contact_id">
        <option value="">All</option>
        @if(!empty($contact_dropdown))
          @foreach($contact_dropdown as $list)
              <option value="{{$list->id}}">
                  @if($list->supplier_business_name)
                      {{$list->supplier_business_name}} - {{$list->name}}({{$list->contact_id}})
                  @else
                     {{$list->name}} - ({{$list->contact_id}})
                  @endif
              </option>
          @endforeach
        @endif
      </select>
    </div>
    <div class="form-group col-md-3">
      <label>Tax Rates</label>
      {!! Form::select('tax_rates', $tax_rates, null, ['class' => 'form-control select2','id' => 'tax_rates', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
  </div>
  <div class="form-group col-md-3">
      <label>Category</label>
        {!! Form::select('product_category[]', $categoriesdata, null, ['class' => 'form-control select2', 'style' => 'width:100%','multiple','id' => 'product_category']); !!}
  </div>
  <div class="form-group col-md-3">
      <label>State</label>
      <select id="contact_state"  name="state" class="form-control select2">
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
  <div class="form-group col-md-3">
    <div class="checkbox">
        <label>
            <br>
          {!! Form::checkbox('order_note', 1, false,
          [ 'class' => 'input-icheck', 'id' => 'order_note']); !!} Don't include order note
        </label>
    </div>
  </div>

  <div class="form-group col-md-3 hide">
    <div class="checkbox">
        <label>
            <br>
          {!! Form::checkbox('regular_invoice', 1, false,
          [ 'class' => 'input-icheck checkbox_ref', 'id' => 'regular_invoice']); !!} Show Regular Invoice
        </label>
    </div>
  </div>
  <div class="form-group col-md-3 hide">
    <div class="checkbox">
        <label>
            <br>
          {!! Form::checkbox('jadoo_invoice', 1, false,
          [ 'class' => 'input-icheck checkbox_ref', 'id' => 'jadoo_invoice']); !!} Show Jadoo Invoice
        </label>
    </div>
  </div>


   <div class="form-group col-md-3">
    <div class="checkbox">
        <label>
            <br>
          {!! Form::checkbox('regular_invoice_pdf', 1, false,
          [ 'class' => 'input-icheck checkbox_ref', 'id' => 'regular_invoice_pdf']); !!} Show Regular Invoice
        </label>
    </div>
  </div>
    <div class="form-group col-md-6" style="margin-top: 17px;" id="gen_down">
         <button class="btn btn-sm btn-primary" id="generate" style="margin-top: 10px">Generate Invoices</button>
         <input type="hidden" id="download_path" value="">
         <button class="btn btn-sm btn-primary" id="download" style="margin-top: 10px">Download Invoices</button>
         <button class="btn btn-primary" id="cigar_invoice" style="margin-top: 10px;font-size:12px;">Cigar Invoices</button>
         <button class="btn btn-primary" id="tax_invoice" style="margin-top: 10px;font-size:12px;">Tax Invoices</button>

    </div>
  <div id="error_mssage" style="color: red;margin-left:22px;">
      @if (\Session::has('success'))
        <div class="alert alert-success">
            <ul>
                <li>{!! \Session::get('success') !!}</li>
            </ul>
        </div>
      @endif
    </div>
</div>

<div class="row">
     <div class="table-responsive">
      <h4 align="left">Total Data : <span id="total_records"></span></h4>
      <button type="button" class="btn btn-info no-print mb-5 hide export_btn pull-right"   onclick="ExportToExcel('xlsx')"
         aria-label="Print"><i class="fas fa-export"></i> @lang( 'EXPORT' )
      </button>
      <div id="searchdata">
      </div>
  </div>
</div>
<div id="mytable" class="loader" style="display: none;"></div>
   <!-- JS  -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
   <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
   <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(function() {

    $(document).on('click', 'input.checkbox_ref', function() {
        $('input[type="checkbox"]').not(this).prop('checked', false);
    });

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
        }
    }, cb);
    cb(fromDate, toDate);
});

$(document).ready(function(){
 $('.select2').select2();

$( "#download" ).click(function() {
  var download_path = $("#download_path").val();
  if(download_path!="")
  {
    window.location = 'download_zip/'+download_path;
  }
  else
  {
    $('#error_mssage').html("Please generate invoices first!!");
  }
});

$( "#generate" ).click(function() {
  $("#download_path").val("");
  var ranges = $("input[name=fromDate]").val();
  var contact_id = $("#contact_id").val();
  var rates = $("#tax_rates").val();
  var query = $("input[name=search]").val();
  var state = $("#contact_state").val();
  var product_category = $("#product_category").val();
  var order_note = 0;
  var regular_invoice = 0;
  var regular_invoice_pdf = 0;
  var jadoo_invoice =0;

  if ($("#order_note").is(':checked')) {
    order_note = 1;
  }
  if ($("#regular_invoice_pdf").is(':checked')) {
    regular_invoice_pdf = 1;
  }

  if ($("#regular_invoice").is(':checked')) {
    regular_invoice = 1;
  }


  if ($("#jadoo_invoice").is(':checked')) {
    jadoo_invoice = 1;
  }

  if(ranges!="")
  {
   var upload_dir_path = '';
   var chunkAndLimit = 100;
   $('#searchdata').html("").hide();
   $("#generate").prop("disabled","disabled");
   $("#download").prop("disabled","disabled");
   $("#cigar_invoice").prop("disabled","disabled");
   $("#tax_invoice").prop("disabled","disabled");

   doChunkedExport(0,chunkAndLimit,ranges,contact_id,rates,query,state,product_category,upload_dir_path,"{{ route('searchexportinvoice') }}",chunkAndLimit,order_note,regular_invoice,regular_invoice_pdf , jadoo_invoice);
  }
  else
  {
    $('#error_mssage').html("Please select Date Range!!");
  }
});






$( "#cigar_invoice" ).click(function() {
  $("#download_path").val("");
  var ranges = $("input[name=fromDate]").val();
  var contact_id = $("#contact_id").val();
  var rates = $("#tax_rates").val();
  var query = $("input[name=search]").val();
  var state = $("#contact_state").val();
  var product_category = $("#product_category").val();
  var order_note = 0;
  var regular_invoice = 0;
  var regular_invoice_pdf = 0;
  var jadoo_invoice = 0;

  $(".export_btn").removeClass('hide');
  if ($("#order_note").is(':checked')) {
    order_note = 1;
  }

  if ($("#regular_invoice_pdf").is(':checked')) {
    regular_invoice_pdf = 1;
  }
   if ($("#regular_invoice").is(':checked')) {
    regular_invoice = 1;
  }


  if ($("#jadoo_invoice").is(':checked')) {
    jadoo_invoice = 1;
  }

  if(ranges!="")
  {
   var upload_dir_path = '';
   var chunkAndLimit = 100;
   $('#searchdata').html("").hide();
   $("#generate").prop("disabled","disabled");
   $("#download").prop("disabled","disabled");
   $("#cigar_invoice").prop("disabled","disabled");
   $("#tax_invoice").prop("disabled","disabled");

   doChunkedExport(0,chunkAndLimit,ranges,contact_id,rates,query,state,product_category,upload_dir_path,"{{ route('searchcigarinvoice') }}",chunkAndLimit,order_note,regular_invoice,regular_invoice_pdf, jadoo_invoice);
  }
  else
  {
    $('#error_mssage').html("Please select Date Range!!");
  }
});









$( "#tax_invoice" ).click(function() {
  $("#download_path").val("");
  var ranges = $("input[name=fromDate]").val();
  var contact_id = $("#contact_id").val();
  var rates = $("#tax_rates").val();
  var query = $("input[name=search]").val();
  var state = $("#contact_state").val();
  var product_category = $("#product_category").val();
  var order_note = 0;
  var regular_invoice = 0;
  var regular_invoice_pdf = 0;
  var jadoo_invoice = 0;

  $(".export_btn").removeClass('hide');
  if ($("#order_note").is(':checked')) {
    order_note = 1;
  }
  if ($("#regular_invoice_pdf").is(':checked')) {
    regular_invoice_pdf = 1;
  }
  if ($("#regular_invoice").is(':checked')) {
    regular_invoice = 1;
  }

  if ($("#jadoo_invoice").is(':checked')) {
    jadoo_invoice = 1;
  }

  if(ranges!="")
  {
   var upload_dir_path = '';
   var chunkAndLimit = 100;
   $('#searchdata').html("").hide();
   $("#generate").prop("disabled","disabled");
   $("#download").prop("disabled","disabled");
   $("#cigar_invoice").prop("disabled","disabled");
   $("#tax_invoice").prop("disabled","disabled");

   doChunkedExport(0,chunkAndLimit,ranges,contact_id,rates,query,state,product_category,upload_dir_path,"{{ route('searchtaxinvoice') }}",chunkAndLimit,order_note,regular_invoice, regular_invoice_pdf, jadoo_invoice);
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
    $('#error_mssage').html("");
    //count = 0;
  //}
});
function doChunkedExport(start,limit,ranges,contact_id,rates,query,state,product_category,upload_dir_path,action,chunkSize,order_note,regular_invoice,regular_invoice_pdf,jadoo_invoice){
 $.ajax({
           url:action,
           method:'POST',
           data:{
             upload_dir_path:upload_dir_path,
             ranges:ranges,
             contact_id:contact_id,
             rates:rates,
             query:query,
             state:state,
             product_category:product_category,
             order_note:order_note,
             regular_invoice:regular_invoice,
             regular_invoice_pdf:regular_invoice_pdf,
             jadoo_invoice : jadoo_invoice,
             start:start,
             limit:limit,
             _token : '{{ csrf_token() }}'
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
                    start = start + chunkSize;
                    $('#searchdata').append(data.table_data);
                    upload_dir_path = data.upload_dir_path;
                    doChunkedExport(start,limit,ranges,contact_id,rates,query,state,product_category,upload_dir_path,action,chunkSize,order_note,regular_invoice,regular_invoice_pdf,jadoo_invoice);
                }else
                {
                    $(".loader").hide();
                    $('#searchdata').append(data.table_data).show();
                    $('#total_records').text(data.total_data);
                    $('#generate').prop('disabled', false);
                    $('#download').prop('disabled', false);
                    $("#cigar_invoice").prop("disabled",false);
                    $("#tax_invoice").prop("disabled",false);
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

//Export to Excel
    function ExportToExcel(type, fn, dl) {
        var elt = document.getElementById('searchdata');
        var wb = XLSX.utils.table_to_book(elt, { sheet: "sheet1" });
        return dl ?
            XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }) :
            XLSX.writeFile(wb, fn || ('Report.' + (type || 'xlsx')));
    }
</script>
</body>
</html>