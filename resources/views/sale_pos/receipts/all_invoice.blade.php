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
    <title>{{ config('business-info.name') }} | All Invoices</title>
  </head>
  <body>
  	<div class="container">
	  	<h3>All Invoices</h3>
	  	<p>NOTE: <span style="color: #ff0000">Click On Invoice Number to Show Invoice Details </span></p>
<div class="row">
  <div class="form-group col-md-3">
      <label>Enter Invoice No</label>
      <input  type="text" name="search" id="search" class="form-control" placeholder="Invoice No" />
  </div>
  <div class="form-group col-md-3">
      <label>Select Date</label>
      <input name="fromDate" readonly="" class="form-control" id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;" autocomplete="off">
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
</div>

<div class="row">
     <div class="table-responsive">
      <h4 align="left">Total Data : <span id="total_records"></span></h4>
     	<div id="searchdata">
     	</div>
	</div>
</div>
    <!-- JS  -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
   <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
   <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function() {
    var fromDate = moment().subtract(29, 'days');
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
        }
    }, cb);
    cb(fromDate, toDate);
});


$(document).ready(function(){
$('.select2').select2();
  fetch_customer_data();
// search_data();

 function fetch_customer_data()
 {
  var ranges = $("input[name=fromDate]").val();
  var rates = $("#tax_rates").val();
  var query = $("input[name=search]").val();
  var state = $("#contact_state").val();
  var product_category = $("#product_category").val();



  $.ajax({
   url:"{{ route('searchinvoice') }}",
   method:'GET',
   data:{
     ranges:ranges,
     rates:rates,
     query:query,
     state:state,
     product_category:product_category
    },

   dataType:'json',
   sortInitialOrder: "desc",
   success:function(data)
   {
    $('#searchdata').html(data.table_data);
    $('#total_records').text(data.total_data);
   }
  })
 }

 function search_data(query='')
 {

  $.ajax({
   url:"{{ route('searchinvoice') }}",
   method:'GET',
   data:{query:query},

   dataType:'json',
   sortInitialOrder: "desc",
   success:function(data)
   {
    $('#searchdata').html(data.table_data);
    $('#total_records').text(data.total_data);
   }
  })
 }

 function search_tax_rates(rates='')
 {
    $.ajax({
      url:"{{ route('searchinvoice') }}",
      method:'GET',
      data:{rates:rates},

      dataType:'json',
      sortInitialOrder: "desc",
      success:function(data)
      {
        $('#searchdata').html(data.table_data);
        $('#total_records').text(data.total_data);
      }
    });
 }

$(document).on('change', '#reportrange', function(){
  var arr = [];

  //var query = $("input[name=search]").val();

  // const myArr = ranges.split("-");
  // console.log(myArr);
   fetch_customer_data();
  });


$(document).on('keyup', '#search', function(){
  // var query = $("input[name=search]").val();
  //var ranges = $("input[name=fromDate]").val();
  //console.log(ranges);
  fetch_customer_data();
  });

    $("#tax_rates").change(function(){
      // var rates = $("#tax_rates").val();
      // search_tax_rates(rates);
      fetch_customer_data();
    });

    $("#contact_state").change(function(){
      fetch_customer_data();
    });

    $("#product_category").change(function(){
      fetch_customer_data();
    });
 });

</script>
</body>
</html>