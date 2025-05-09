@extends('layouts.app')
@section('title', __('Product Selling') . '' . __('report.reports'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ __('Product Selling')}} {{ __('report.reports')}}</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
	    <div class="row no-print ">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('br_account_rep', __('Account Rep') . ':') !!}
                            {!! Form::select('br_account_rep', $account_rep, 'All', ['class' => 'form-control select2','placeholder' => __('All'), 'id' => __('br_account_rep')]); !!}
                        </div>        
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('br_sales_rep', __('Sales Rep') . ':') !!}
                            {!! Form::select('br_sales_rep', $account_rep, 'All', ['class' => 'form-control select2','placeholder' => __('All'), 'id' => __('br_sales_rep')]); !!}
                        </div>        
                    </div>
                    
                   
                     <div class="col-md-3">
                        <div class="form-group">
                            <?php echo Form::label('category_id', __('product.category') . ':'); ?>

                            <?php echo Form::select('category_id', $categories, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'category_id', 'placeholder' => __('lang_v1.all')]);; ?>

                        </div>
                    </div>
                    
                     <div class="col-md-3">
                        <div class="form-group">
                            <?php echo Form::label('brand_id', __('product.brand') . ':'); ?>

                            <?php echo Form::select('brand_id', $brands, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'brand_id', 'placeholder' => __('lang_v1.all')]);; ?>

                        </div>
                    </div>
                
                    <div class="col-md-3">
                        <input type="hidden" id="date" name="date" value="">
                        <div class="form-group">
                            {!! Form::label('date_filter', __('report.date_range') . ':') !!}
                            {!! Form::text('date_filter', @format_date('first day of this month') . ' ~ ' . @format_date('last day of this month'), ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'date_filter', 'readonly']); !!}
                        </div>
                    </div>
                    
                  
                    <!--<div class="col-md-4">-->
                    <!--    <div class="form-group">-->
                    <!--        <button class="btn btn-primary" id="getdata" >Submit</button>-->
                    <!--    </div>-->
                    <!--</div>-->
            @endcomponent
        </div>
    </div>
	<div id="due-table" >
		<div class="row">
			<div class="col-md-12">
				@component('components.widget', ['class' => 'box-primary'])

				<div class="table-responsive">
					<table class="table table-bordered table-striped" style="width: 100%" data-page-length="-1" id="category_wise_sales_report">
						<thead>
							<tr>
								<th style='width: 15%;'>@lang('Product')</th>
								<th style='width: 10%;'>@lang('Category')</th>
								<th style='width: 8%;'>@lang('Brand')</th>
								<th style='width: 7%;'>Invoice No</th>
								<th style='width: 15%;'>Customer</th>
								<th style='width: 10%;'>Transaction Date</th>
								<th style='width: 7%;'>quantity</th>
								<th style='width: 7%;'>Unit Price</th>
								<th style='width: 7%;'>Cost</th>
								<th style='width: 7%;'>Profit</th>
								<th style='width: 7%;'>GP</th>

							</tr>
						</thead>
						<tbody></tbody>
						<tfoot>
							<tr class="bg-gray font-17 footer-total text-center">
								<td colspan="6"><strong>@lang('sale.total'):</strong></td>
								<td><span class="display_currency" data-currency_symbol ="false"  id="footer_qty" ></span></td>
								<td><span class="display_currency" data-currency_symbol ="true"  id="footer_sell_price"></span></td>
								<td><span class="display_currency" data-currency_symbol ="true" id="footer_cost_price"></span></td>
								<td><span class="display_currency" data-currency_symbol ="true" id="footer_profit" ></span></td>
								<td></td>
								
							</tr> 
						</tfoot>
					</table>
				</div>
				@endcomponent
			</div>
		</div>
	</div>

</section>
<!-- /.content -->

@endsection


@section('javascript')
    <script type="text/javascript">

        $(document).ready(function(){

			 $('#date_filter').daterangepicker({
				startDate: moment().startOf('month'),
				endDate: moment().endOf('month'),
                ranges: ranges,
      		    //maxDate:  moment().subtract(7,'day'),
                autoUpdateInput: false,
                locale: {
                    format: moment_date_format
                }
            });
			
            $('#date_filter').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format(moment_date_format) + ' ~ ' + picker.endDate.format(moment_date_format));
                    $("#date").val($(this).val());
                    console.log($(this).val());
                    category_wise_sales_report.ajax.reload();
            }); 

            $('#date_filter').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                category_wise_sales_report.ajax.reload();
            });
            
            category_wise_sales_report = $("#category_wise_sales_report").DataTable({
                processing: true,
                serverSide: true,
                "order": [[ 3, "desc" ]] ,
                "ajax": {
                    "url": "/reports/product-selling-report",
                	"data": function ( d ) {
                	    d.br_account_rep = $('#br_account_rep').val();
                        d.br_sales_rep = $('#br_sales_rep').val();
                        d.category_id = $('#category_id').val();
                        d.brand_id = $('#brand_id').val();

						if($('#date_filter').val()) {
							var start = $('input#date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
							console.log(start);
							var end = $('input#date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
							d.start_date = start;
							d.end_date = end;
                    	}
                	}
                },
                // columnDefs: [
                //     {
                //         targets: 5 ,
                //         orderable: true,
                //         searchable: true,
                //     },
                // ],
                columns: [
                        { data: 'product_name', name: 'product_name' },
                        { data: 'category_name', name: 'category_name' },
                        { data: 'brand_name', name: 'brand_name' },
                        { data: 'invoice_no', name: 'invoice_no' },
                        { data: 'customer_name', name: 'customer_name'},	
        				{ data: 'transaction_date', name: 'transaction_date' },
                        { data: 'quantity', name: 'quantity' },
                        { data: 'sell_price', name: 'sell_price' },
                        { data: 'cost_price', name: 'cost_price' },
                        { data: 'profit', name: 'profit' },
                        { data: 'gp', name: 'gp' },

                ],
                
                fnDrawCallback: function(oSettings) {                   
                     $('#footer_qty').html(
                        sum_table_col($('#category_wise_sales_report'), 'total_qty')
                    );

                    var profit = sum_table_col($('#category_wise_sales_report'), 'total_profit');
                    $('#footer_profit').text(profit);
                    
                    
                    var total_sell_price = sum_table_col($('#category_wise_sales_report'), 'total_sell_price');
                    $('#footer_sell_price').text(total_sell_price);
                    
                    var total_cost_price = sum_table_col($('#category_wise_sales_report'), 'total_cost_price');
                    $('#footer_cost_price').text(total_cost_price);
                    
                    
                    

                    __currency_convert_recursively($('#category_wise_sales_report'));
                },

            }); 
                data_table_initailized = true;
          
        });
                   
    
        $('select#br_account_rep, #br_sales_rep , #category_id , #brand_id, #date_filter').change( function(){
              category_wise_sales_report.ajax.reload();    
        }); 

         
    </script>
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
@endsection