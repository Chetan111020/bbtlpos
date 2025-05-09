@extends('layouts.app')
@section('title', __( 'End of the Week Report' ))
<style>
  .card-img-top{
    height: 100px;
  }
</style>
@section('content')
 <!--<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>-->
<script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-3d.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'Weekend Report' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
           <div class="form-group pull-right">
                <div class="col-md-8">
                    <input type="hidden" id="date" name="date" value="">
                    <div class="form-group">
                        {!! Form::label('all_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('all_date_filter', @format_date('first day of this week') . ' ~ ' . @format_date('last day of this week'), ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'all_date_filter', 'readonly']); !!}

                        {{-- {!! Form::label('all_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('all_date_filter', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!} --}}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <button class="btn btn-primary" id="submitData" >Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row row-custom">
            
        	<div class="col-md-4 col-sm-6 col-xs-12 col-custom">
    	      <div class="info-box info-box-new-style">
    	        <span class="info-box-icon bg-yellow">
    	        	<i class="ion ion-ios-paper-outline"></i>
    	        	<i class="fa fa-exclamation"></i>
    	        </span>

    	        <div class="info-box-content">
    	          <span class="info-box-text">{{ __('Vendor Bill') }}</span>
    	          <span class="info-box-number purchase_due"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
    	        </div>
    	        <!-- /.info-box-content -->
    	      </div>
    	      <!-- /.info-box -->
    	    </div>
    	    <!-- /.col -->
    	    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
    	      <div class="info-box info-box-new-style">
    	        <span class="info-box-icon bg-aqua"><i class="ion ion-ios-cart-outline"></i></span>

    	        <div class="info-box-content">
    	          <span class="info-box-text">{{ __('Payment To Vendor') }}</span>
    	          <span class="info-box-number vendor_payment"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
    	        </div>
    	        <!-- /.info-box-content -->
    	      </div>
    	      <!-- /.info-box -->
    	    </div>
    	    <!-- /.col -->
    	    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
    	      <div class="info-box info-box-new-style">
    	        <span class="info-box-icon bg-aqua"><i class="ion ion-cash"></i></span>

    	        <div class="info-box-content">
    	          <span class="info-box-text">{{ __('Purchase Order') }}</span>
    	          <span class="info-box-number purchase_order"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
    	        </div>
    	        <!-- /.info-box-content -->
    	      </div>
    	      <!-- /.info-box -->
    	    </div>
    	    <!-- /.col -->
            <div class="col-md-2"></div>
    	    <!-- fix for small devices only -->
    	    <!-- <div class="clearfix visible-sm-block"></div> -->
    	    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
    	      <div class="info-box info-box-new-style">
    	        <span class="info-box-icon bg-aqua"><i class="ion ion-ios-cart-outline"></i></span>

    	        <div class="info-box-content">
    	          <span class="info-box-text">{{ __('Sales Order') }}</span>
    	          <span class="info-box-number sale_data"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
    	        </div>
    	        <!-- /.info-box-content -->
    	      </div>
    	      <!-- /.info-box -->
    	    </div>
    	    <!-- /.col -->
    	    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
    	      <div class="info-box info-box-new-style">
    	        <span class="info-box-icon bg-yellow">
    	        	<i class="fa fa-dollar"></i>
    				<i class="fa fa-exclamation"></i>
    	        </span>

    	        <div class="info-box-content">
    	          <span class="info-box-text">{{ __('Gross Profit') }}</span>
    	          <span class="info-box-number gp"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
    	        </div>
    	        <!-- /.info-box-content -->
    	      </div>
    	      <!-- /.info-box -->
    	    </div>
    	    <div class="col-md-2"></div>
    	    
      	</div>
      	 
       @component('components.widget', ['class' => 'box-primary', 'title' => __('Weekend Chart')])
      	        <!--<div id="piechart"></div>-->
                <figure class="highcharts-figure">
                  <div id="container" style="height:400;"></div>
                </figure>
      @endcomponent

        <button type="button" class="btn btn-primary download-stats">Download Image</button>
        <div class="box" style="border-top-color: transparent;">
            <div class="box-body" id="donwloadable_resource" style="padding: 100px 50px;">

                <div class="row">
                    <div class="col-md-4  col-sm-4 text-center">
                        <div class="card mb-4">
                            <i class='bx bxl-dropbox card-img-top'></i>
                            <img class="card-img-top" src="{{ asset('img/report/vendor.png') }}" alt="Card image cap">
                            <div class="card-body">
                                <h3 class="card-title">VENDOR BILL</h3>
                                <span class="card-text info-box-number text-center purchase_due"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4  col-sm-4 text-center">
                        <div class="card mb-4">
                            <i class='bx bxl-dropbox card-img-top'></i>
                            <img class="card-img-top" src="{{ asset('img/report/payment1.png') }}" alt="Card image cap">
                            <div class="card-body">
                                <h3 class="card-title">PAYMENT TO VENDOR</h3>
                                <span class="card-text info-box-number text-center vendor_payment"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4  col-sm-4 text-center">
                        <div class="card mb-4">
                            <!--<i class='bx bxl-dropbox card-img-top'></i>-->
                            <img class="card-img-top" src="{{ asset('img/report/po.png') }}" alt="Card image cap">
                            <div class="card-body">
                                <h3 class="card-title">PURCHASE ORDER</h3>
                                <span class="card-text info-box-number text-center purchase_order"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top:75px;">
                    <div class="col-md-4  col-sm-4 text-center">
                        <div class="card mb-4">
                            <i class='bx bxl-dropbox card-img-top'></i>
                            <img class="card-img-top" src="{{ asset('img/report/sale.png') }}" alt="Card image cap">
                            <div class="card-body">
                                <h3 class="card-title">SALES ORDER</h3>
                                <span class="card-text info-box-number text-center sale_data"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4  col-sm-4 text-center">
                        <div class="card mb-4">
                            <i class='bx bxl-dropbox card-img-top'></i>
                            <img class="card-img-top" src="{{ asset('img/report/gp.png') }}" alt="Card image cap">
                            <div class="card-body">
                                <h3 class="card-title">GROSS PROFIT</h3>
                                <span class="card-text info-box-number text-center gp"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4  col-sm-4 text-center">
                        <div class="card mb-4">
                            <i class='bx bxl-dropbox card-img-top'></i>
                            <img class="card-img-top" src="{{ asset('img/report/discount.png') }}" alt="Card image cap">
                            <div class="card-body">
                                <h3 class="card-title">TOTAL DISCOUNT</h3>
                                <span class="card-text info-box-number text-center discount_data"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
</section>
<!-- /.content -->
<!--<div class="modal fade view_register" tabindex="-1" role="dialog" -->
<!--    aria-labelledby="gridSystemModalLabel">-->
<!--</div>-->

@endsection

@section('javascript')
    <!--<script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>-->
    <!--<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript">
    

        var start,end;
    
        $(document).ready( function(e) {
            $('.download-stats').on('click',function(){
            html2canvas(document.querySelector("#donwloadable_resource")).then(canvas => {
                saveAs(canvas.toDataURL(), 'stats.png');
            });

            function saveAs(uri, filename) {
                var link = document.createElement('a');
                if (typeof link.download === 'string') {
                    link.href = uri;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    window.open(uri);
                }
            }
        });
            $('#all_date_filter').daterangepicker({
                ranges: ranges,
                autoUpdateInput: true,
                startDate: moment().startOf('week'),
                endDate: moment().endOf('week'),
                locale: {
                    format: moment_date_format
                }
            });
            $('#all_date_filter').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format(moment_date_format) + ' ~ ' + picker.endDate.format(moment_date_format));
                    $("#date").val($(this).val());
               
            }); 
    
            $('#all_date_filter').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
            getReportdata();
        });
        
        $(document).on('click', 'button#submitData', function(e) {
            e.preventDefault();
            getReportdata();
        });
        
        
        function getReportdata(){
            
            $('#container').html('');
            var loader = '<i class="fas fa-sync fa-spin fa-fw margin-bottom"></i>';
            $('.vendor_payment').html(loader);
            $('.purchase_due').html(loader);
            $('.purchase_order').html(loader);
            $('.sale_data').html(loader);
            $('.gp').html(loader);
            $('.discount_data').html(loader);
            
            var div = $('<div/>')
                .addClass('loading')
                .text('Loading...');
            
            if($('input#all_date_filter').val()) {
                start = $('input#all_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                end = $('input#all_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
            }
            
            $.ajax({
                url: '/reports/weekend-report',
                method: 'GET',
                data: { 
                    start_date: start,
                    end_date: end,
                },
                dataType: 'json',
                success: function(data) {
                    div.removeClass('loading');
                    
                    $('.purchase_due').html(__currency_trans_from_en(data.purchase_due, true));
                    $('.vendor_payment').html(__currency_trans_from_en(data.vendor_payment, true));
                    $('.purchase_order').html(__currency_trans_from_en(data.purchase_order, true));
                    $('.sale_data').html(__currency_trans_from_en(data.sale_data, true));
                    $('.gp').html(__currency_trans_from_en(data.gp, true)+ ' %');
                    $('.discount_data').html(__currency_trans_from_en(data.discount_data, true));
                    
                    __currency_convert_recursively(div);
                    getchart(data.purchase_due,data.vendor_payment,data.purchase_order,data.sale_data,data.gp);
                     window.scrollTo(0, document.body.scrollHeight);
                },
            });
            return div;
        }
        
        
        //   function getchart(){
        //         if($('input#all_date_filter').val()) {
        //         start = $('input#all_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
        //         end = $('input#all_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
        //     }
            
        //     google.charts.load('current', {'packages':['corechart']});
        //     google.charts.setOnLoadCallback(drawChart);
            
        //     function drawChart() {

        //         var chartData = [];
        //         chartData.push(['test','test2']);
        //                                 $.ajax({
        //                                 url: "/reports/weekend-report",
        //                                 data: {
        //                                         start_date: start,
        //                                         end_date: end, 
        //                                         },
        //                                 dataType: "json",
        //                                 async: false,
        //                             })
        //                             .done(function (response){
        //                                 console.log(response);
        //                                 Object.keys(response).forEach(function(item){
                                               
        //                                 });
        //                                  chartData.push(['VENDOR BILL',response.purchase_due]);
        //                                  chartData.push(['SALES ORDER',response.sale_data]);
        //                                  chartData.push(['PAYMENT TO VENDOR',response.vendor_payment]);
        //                                  chartData.push(['GROSS PROFIT',response.gp]);
        //                                  chartData.push(['PURCHASE ORDER',response.purchase_order]);
        //                             });
                                    
                
        //         var data = google.visualization.arrayToDataTable(chartData);
                
        //         var options = {
        //              is3D: true,
        //              'height': 500,
        //         };
                
        //         var chart = new google.visualization.PieChart(document.getElementById('piechart'));
        //         var formatter = new google.visualization.NumberFormat({pattern:'###,###.###' ,prefix: '$ ',negativeColor: 'red', negativeParens: true});
        //         formatter.format(data, 1);

        //         chart.draw(data, options, formatter);
        //     }
        // }
        
        
     //pie chart   
    function getchart(purchase_due,vendor_payment,purchase_order,sale_data,gp){
                if($('input#all_date_filter').val()) {
                start = $('input#all_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                end = $('input#all_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
            }  
        var chartData = [];
                // chartData.push(['test','test2']);
                                        /*$.ajax({
                                        url: "/reports/weekend-report",
                                        data: {
                                                start_date: start,
                                                end_date: end, 
                                                },
                                        dataType: "json",
                                        async: false,
                                    })
                                    .done(function (response){
                                        console.log(response);*/
                                         chartData.push(['VENDOR BILL',purchase_due]);
                                         chartData.push(['SALES ORDER',sale_data]);
                                         chartData.push(['PAYMENT TO VENDOR',vendor_payment]);
                                         chartData.push(['GROSS PROFIT',gp]);
                                         chartData.push(['PURCHASE ORDER',purchase_order]);
                                    //});
   
        Highcharts.setOptions({
                lang: {
                    thousandsSep: ','
                }
                });
                                 
        Highcharts.chart('container', {
                    
                  chart: {
                    type: 'pie',
                    options3d: {
                      enabled: true,
                      alpha: 45,
                      beta: 0,
                    }
                  },
                  title: {
                    text: ''
                  },
                  tooltip: {
                    pointFormat: 'Value: <b>${point.y:,.2f}</b> , {series.name}: <b>{point.percentage:.1f}%</b>',
                  },
                  
                  plotOptions: {
                    pie: {
                      allowPointSelect: true,
                      cursor: 'pointer',
                      depth: 35,
                      dataLabels: {
                        enabled: true,
                        format: '{point.name}',
                      }
                    },
                  },
                   credits: {
                    enabled: false
                  },
                  responsive: {  
                  rules: [{  
                    condition: {  
                      maxWidth: 500  
                    },  
                    chartOptions: {  
                      legend: {  
                        enabled: false  
                      }  
                    }  
                  }]  
                },
                  series: [{
                    type: 'pie',
                    name: 'Percentage',
                    data: chartData,
                    showInLegend: true
                  }],
                });
                
                }
                    </script>
@endsection