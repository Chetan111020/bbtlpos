@extends('layouts.app')
@section('title', __('Tax') . '' . __('Report'))

@section('content')
<style>
    div.dataTables_filter input { border: 1px solid black; }
</style>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ __('Tax')}} {{ __('Report')}}</h1>
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

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('contact_filter_customer_id', __('contact.customer') . ':') !!}
                            {!! Form::select('contact_filter_customer_id', $customers, null, ['class' => 'form-control select2 contact_filter_customer_id','id' => 'contact_filter_customer_id', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>State:</label>
                            <select class="form-control state_select" id= "state_select">
                                <option>All</option>
                                @foreach ($states as $item)
                                    <option>{{ $item->state }}</option>
                                @endforeach
                            </select>
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
					<table class="table table-bordered table-striped" style="" data-page-length="-1" id="all_tax_report">
						<thead>
						  <tr>
                            <th style="%">Invoice Date</th>
                            <th style="">Invoice No</th>
                            <th style="">Tax Id</th>
                            <th style="">Name</th>
                            <th style="">Address</th>
                            <th style="">City</th>
                            <th style="">State</th>
                            <th style="">Zip</th>
                            <th style="">Payment Status</th>
                            <th style="">Qty</th>
                            <th style="">Price</th>
                            <th style="">Order Note</th>
                            <th>Open Amount</th>
                            <th>Close Amount</th>
                            <th style="">Tax Amount</th>
                            <th>Taxed Products </th>
                            <th>Total Before Tax</th>
                            <th style="">Total Amount</th>
                            <th>Shipping Charges</th>
                        </tr>
						</thead>
						<tbody></tbody>
						<tfoot>
							<tr class="bg-gray font-17 footer-total text-center">
								<td colspan="12"><strong>@lang('sale.total'):</strong></td>
								<td></td>
								<td></td>
								<td><span class="display_currency" data-currency_symbol ="true"  id="footer_tax" ></span></td>
								<td></td>
								<td><span class="display_currency" data-currency_symbol ="true"  id="footer_total_before_tax" ></span></td>
								<td><span class="display_currency" data-currency_symbol ="true"  id="footer_total"></span></td>
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
                    all_tax_report.ajax.reload();
            });

            $('#date_filter').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                all_tax_report.ajax.reload();
            });

            all_tax_report = $("#all_tax_report").DataTable({
                processing: true,
                serverSide: true,
                "order": [[ 1, "desc" ]] ,
                "ajax": {
                    "url": "/reports/all-tax-report",
                	"data": function ( d ) {
                        d.customer_id = $('.contact_filter_customer_id').val();
                        d.state = $('#state_select').val();
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
                // //         targets: 5 ,
                //         orderable: true,
                //         searchable: true,
                //     },
                // ],
                // scrollY:        true,
                // scrollCollapse: true,
                columns: [
                        { data: 'invoice_date', name: 'transaction_date'},
                        { data: 'invoice_no', name: 'invoice_no'},
                        { data: 'tax_id', name: 'tax_id'},
                        { data: 'name', name: 'contacts.name'},
                        { data: 'address', name: 'contacts.address_line_1'},
                        { data: 'city', name: 'contacts.city'},
                        { data: 'state', name: 'contacts.state'},
                        { data: 'zip_code', name: 'contacts.zip_code'},
                        { data: 'payment_status', name: 'payment_status'},
                        { data: 'item_qty', name: 'item_qty'},
                        { data: 'price', name: 'price',searchable: false, visible :false},
                        { data: 'order_note', name: 'order_note'},
                        { data: 'open_amount', name: 'open_amount'},
                        { data: 'close_amount', name: 'close_amount'},
                        { data: 'tax_amount', name: 'tax_amount'},
                        { data: 'total_taxed_products', name: 'total_taxed_products',searchable: false},
                        { data: 'total_before_tax', name: 'total_before_tax'},
                        { data: 'final_total', name: 'final_total'},
                        { data: 'shipping_charges', name: 'shipping_charges'},

                ],

                fnDrawCallback: function(oSettings) {
                    //  $('#footer_qty').html(
                    //     sum_table_col($('#all_tax_report'), 'total_qty')
                    // );

                    var footer_tax = sum_table_col($('#all_tax_report'), 'tax_amount');
                    $('#footer_tax').text(footer_tax);

                    var footer_total_before_tax = sum_table_col($('#all_tax_report'), 'total_before_tax');
                    $('#footer_total_before_tax').text(footer_total_before_tax);



                    var final_total = sum_table_col($('#all_tax_report'), 'final_total');
                    $('#footer_total').text(final_total);

                    __currency_convert_recursively($('#all_tax_report'));
                },

            });
                data_table_initailized = true;

        });


        $('#contact_filter_customer_id, #state_select, #date_filter').change( function(){
              all_tax_report.ajax.reload();
        });


    </script>
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
@endsection