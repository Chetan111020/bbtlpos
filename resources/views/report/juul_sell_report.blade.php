@extends('layouts.app')
@section('title', __('Juul sell report'))
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>{{ __('Juul Sell Report')}}</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <div class="row">
        
    @component('components.filters', ['title' => __('report.filters')])
<div class="col-md-12">
      <div class="col-md-12">
        <form action="{{route('juul.filter')}}" method="POST" autocomplete="off">
            @csrf
            <div class="form-group col-md-4" style="width: 216px;">
                  <label>Select Date Range :-</label>
                    <div class='input-group date'>
                        <input name="fromDate" readonly="" name="toDate" id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;" autocomplete="off">
                     </div>
            </div>
            <div class="form-group col-md-4">               
                    <div class='input-group date' style="margin-top: 25px;"> 
                    <button class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i>
                    Search</button>
                    </div>
            </div>
        </form>
    </div>
    {{ csrf_field() }}
    @endcomponent
        </div>
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#juul" data-toggle="tab" aria-expanded="true"><i class="fa fa-list" aria-hidden="true"></i> Juul Sell Report</a>
                    </li>
                    <!-- <li>
                        <a href="#psr_detailed_with_purchase_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-list" aria-hidden="true"></i> @lang('lang_v1.detailed_with_purchase')</a>
                    </li>
                    <li>
                        <a href="#psr_grouped_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-bars" aria-hidden="true"></i> @lang('lang_v1.grouped')</a>
                    </li> -->
                </ul>
                <div class="tab-content">
                <center><button onclick="ExportToExcel('xlsx')" class="btn btn-primary" style="    margin-bottom: 10px;"><i class="fa fa-download"></i> Export Excel File</button></center>
                    <div class="tab-pane active" id="juul">
                           
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" 
                            id="tbl_exporttable_to_xls">
                                <thead>
                                    <tr>
                                        <th colspan="2" width="15%">Name Account #</th>
                                        <th>Name</th>
                                        <th>Name Address</th>
                                        <th>Name City</th>
                                        <th>Name State</th>
                                        <th>Name Zip</th>
                                        <th>UPC</th>
                                        <th>Item Description</th>
                                        <th>Manufacturer Number</th>
                                        <th>Items Per Selling Unit</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <?php $sr = 1;?>
                                <tbody id="tableData">
                                   @if (!$Gorupquery->isEmpty())                                   
                                    @foreach($Gorupquery as $query => $qr)
                                    <?php  $count = 0;?>
                                    <tr>
                                       <td  colspan="2"><span style="font-weight: 600;"> {{$query}} </span></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                    </tr>
                                    @foreach($qr as $con => $qrrr)
                                    <tr>                                        
                                      <td  colspan="2" style="text-align: right;">{{$qrrr->contact_id}}</td>
                                        <td>{{$qrrr->customer}}</td>
                                        <td>{{$qrrr->address}}</td>
                                        <td>{{$qrrr->city}}</td>
                                        <td>{{$qrrr->state}}</td>
                                        <td>{{$qrrr->zip_code}}</td>
                                        <td>{{$qrrr->sub_sku}}</td>
                                        <td>{{$qrrr->product_name}}</td>
                                        <td>{{$qrrr->sub_sku}}</td>
                                        <td>{{round($qrrr->Box_qty)}} </td>
                                        <!-- {{$qrrr->unit}} -->
                                        <td>{{round($qrrr->sell_qty)}}</td>
                                            <?php $count = $count+round($qrrr->sell_qty); ?>
                                            <!-- <td style="width: 100px;">-->
                                            <!--    {{date('m-d-Y', strtotime($qrrr->transaction_date))}}-->
                                            <!--    {{$qrrr->subtotal}}-->
                                            <!--</td>-->
                                    </tr>
                                    @endforeach
                                    <tr>
                                       
                                       <td colspan="2"> <span style="font-weight: 600;"> {{$query}} TOTAL</span>
                                       </td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td></td>
                                       <td><span style="font-weight: 600;"> {{$count}}</span></td>
                                    </tr>
                                   
                                    @endforeach
                                    @else
                                     <tr>
                                        <td colspan="12"><span style="font-weight: 600;">
                                            <center>NO RECORD FOUND</center></span>
                                        </td>
                                      </tr>
                                    @endif
                                    
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray font-17 footer-total text-center">
                                        <td colspan="6"><strong></strong></td>
                                        <td></td>
                                        <td></td>   
                                        <td></td>   
                                        <td id="footer_tax"></td>
                                        <td colspan="2" id="footer_total_sold"></td>
                                    </tr>
                                </tfoot>
                            </table>
                         
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade view_register" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
@endsection

@section('javascript')
<script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>

<script>
$(function() {

   
    // if({{$from}}!=''){
        var fromDate = '{{$from}}';
        var toDate = '{{$to}}';
    // } else{
    //     var fromDate = moment().subtract(29, 'days');
    //     var toDate = moment();  
    // }
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




    //export to excerl
        function ExportToExcel(type, fn, dl) {
            var elt = document.getElementById('tbl_exporttable_to_xls');
            var wb = XLSX.utils.table_to_book(elt, { sheet: "sheet1" });
            return dl ?
                XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }) :
                XLSX.writeFile(wb, fn || ('Juul_sell_Report.' + (type || 'xlsx')));
        }

   </script>
   <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>

@endsection