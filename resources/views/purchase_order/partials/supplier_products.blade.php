<style type="text/css">
	.date-rage-filter {
		float: right;
	}
</style>
<div class="modal fade" id="supplier_product_modal">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">

		{!! Form::open(['url' => action('PurchaseOrderController@updateStatus'), 'method' => 'post', 'id' => 'supplier_products_form' ]) !!}

		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">@lang( 'Products' )</h4>
		</div>

		<div class="modal-body">
			<div class="row">
				<div class="col-md-3 col-sm-12 date-rage-filter">
		            <div class="form-group">
		                {!! Form::label('sup_product_filter_date_range', __('report.date_range') . ':') !!}
		                {!! Form::text('sup_product_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
		            </div>
		        </div>
                <div class="col-md-12 col-sm-12">
                    <div class="table-responsive">
                        <table data-stripe-classes="[]" class="table supplier_products_table_custom table-bordered table-striped ajax_view bg-gray" id="supplier_products_table" style="width:100%">
                        	<thead>
					            <tr>
					            	<th><input type="checkbox" class="select-all-row" id="select-all-row"></th>
					            	<th>Code</th>
					                <th>Name</th>
					                <th>Qty On Hand</th>
					                <th>Total Sold</th>
					            </tr>
					        </thead>
                    	</table>
                    </div>
                </div>
            </div>
		</div>

		<div class="modal-footer">
			<button type="button" class="btn btn-primary add-product-row" disabled>
				@lang( 'messages.add' )
			</button>
			<button type="button" class="btn btn-default" data-dismiss="modal">
				@lang( 'messages.close' )
			</button>
		</div>

		{!! Form::close() !!}

		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>