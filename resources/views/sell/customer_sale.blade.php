<!DOCTYPE html>
<html>
<head>
    <title>Customer Sales</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.css')
    @yield('css')
    @include('layouts.partials.javascripts')
</head>
<body>
<section class="content-header no-print">
    <h2>Customer Sales List ({{$customer->name}})</h2>
</section>
<section class="invoice print_section" id="receipt_section"></section>
<section class="content no-print">
    <div class="row no-print">
        <div class="col-md-12">
        <input type="hidden" id="customer_id" value="{{$customer->id}}">
          <!-- Custom Tabs -->
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#c_sale" id="c_sale" data-toggle="tab" aria-expanded="true">Sales</a></li>
                <li><a href="#sell_return_table" id="c_credit_memo" data-toggle="tab" aria-expanded="true">Credit Memos</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="c_sale">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" style="margin-top: 20px">
                                    <thead>
                                    <tr class="row-border blue-heading"> 
                                        <th>Action</th>
                                        <th>Date</th>
                                        <th>Invoice Number</th>
                                        <th>Total Amount</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                       
                                    @foreach($sells as $sell)
                                        @if(!empty($sell->id))
                                        <tr>
                                            <td>
                                                <a target="_blank" href="{{route('sales.invoice',$sell->id)}}">
                                                    <i class="fas fa-eye"></i> View More</a>
                                            </td>
                                            <td>{{$sell->transaction_date}}</td>
                                            <td>{{$sell->invoice_no}}</td>
                                            <td>{{$currency->symbol.''.number_format($sell->final_total,2)}}</td>
                                        </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>                    
                </div>
                <div class="tab-pane" id="sell_return_table">
                    @include('sell.customer_creditmemo')
                </div>
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- nav-tabs-custom -->
        </div>
    </div>
 <script type="text/javascript">
        $(document).ready(function () {
            $("#c_credit_memo").click(function(){      
                
           var customer_id = $("#customer_id").val();       
            $.ajax({        
                method: 'GET',      
                url: '/sales/getcreditmemo',     
                data: { customer_id: customer_id },     
                dataType: 'html',       
                success: function (result) {        
                    $('#sell_return_table')       
                        .html(result);           
                },      
            });     
        })

        $("#c_sale").click(function(){
            location.reload();
        });

        });
</script>
</section>
</body>
</html>

