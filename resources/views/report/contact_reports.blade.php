@extends('layouts.app')
@section('title', __( 'Customers Balance Difference Reports' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'Customers Balance Difference Reports' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="form-group">
                    
                </div>
            </div>
            <div class="col-md-4">
                <input type="hidden" id="date" name="date" value="">
                <div class="form-group">
                    
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="stale_customer_report_tbl">
                        <thead>
                            <tr>
                                <th> Customer Name </th>
                                <!--<th> Customer Mobile </th>-->
                                <th> Open Balance </th>
                                <th> Balance </th>
                                <th> Due Balance </th>
                                <th> Difference </th>
                            </tr>
                        </thead>
                        <tfoot>
                            
                        </tfoot>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function(){
            
            swal({
                title: 'Please wait...',
                text: 'Data Loading',
                icon: 'info',
                buttons: false,
                closeOnClickOutside: false,
                closeOnEsc: false,
            });
            
            $.ajax({
               url: '{{ route("reports.customersBalanceReportsFinal") }}',
               type: 'POST',
               data: {
                 _token: "{{ csrf_token() }}",
               },
               cache: false,
               success: function(result){
                   // Assuming result is an array of objects as shown in your example
                   var table = $('#stale_customer_report_tbl').DataTable({
                       data: result,
                       columns: [
                           { data: 'Name', name: 'Customer Name' },
                        //   { data: 'Mobile', name: 'Customer Mobile' },
                           { 
                               data: 'total_credit_balance', 
                               name: 'Open Balance',
                               render: function(data, type, row) {
                                   return parseFloat(data).toFixed(2); // Format to 2 decimal places
                               }
                           },
                           { 
                               data: 'total_balance', 
                               name: 'Balance',
                               render: function(data, type, row) {
                                   return parseFloat(data).toFixed(2); // Format to 2 decimal places
                               }
                           },
                           { 
                               data: 'due_total_balance', 
                               name: 'Due Balance',
                               render: function(data, type, row) {
                                   return parseFloat(data).toFixed(2); // Format to 2 decimal places
                               }
                           },
                           { 
                               data: 'differenceData', 
                               name: 'Difference',
                               render: function(data, type, row) {
                                   return parseFloat(data).toFixed(2); // Format to 2 decimal places
                               }
                           }
                       ],
                       "order": [[ 2, "desc" ]] // Sort by Open Balance descending
                   });
                   
                  swal.close();
               }
            });
        });
    </script>
@endsection