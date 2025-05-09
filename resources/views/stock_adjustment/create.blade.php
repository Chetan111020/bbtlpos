@extends('layouts.app')
@section('title', __('stock_adjustment.add'))

@section('content')
<style type="text/css">
	.failure{
		color: #a94442; border-color: #ebccd1; display: none;
	}

	input[type=text]:focus, textarea:focus {
	    border: 1px solid rgba(81, 203, 238, 1);
      box-shadow: 0 0 5px rgba(81, 203, 238, 1);
	}
</style>

<!-- Content Header (Page header) -->
<section class="content-header">
<br>
    <h1>@lang('stock_adjustment.add')</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content no-print">
	{!! Form::open(['url' => action('StockAdjustmentController@store'), 'method' => 'post', 'id' => 'stock_adjustment_form', 'files' => true]) !!}
	<div class="box box-solid">
		<div class="box-body">
			<div class="row">
				<div class="col-sm-3" style="display:none;">
					<div class="form-group">
						{!! Form::label('location_id', __('purchase.business_location').':*') !!}
						{!! Form::select('location_id', $business_locations, 4, ['class' => 'form-control select2', 'placeholder' => __('messages.location'), 'required']); !!}
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label('ref_no', __('purchase.ref_no').':') !!}
						{!! Form::text('ref_no', null, ['class' => 'form-control']); !!}
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label('transaction_date', __('messages.date') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</span>
							{!! Form::text('transaction_date', @format_datetime('now'), ['class' => 'form-control', 'required']); !!}
						</div>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label('adjustment_type', __('stock_adjustment.adjustment_type') . ':*') !!} @show_tooltip(__('tooltip.adjustment_type'))
						{!! Form::select('adjustment_type', [ 'normal' =>  __('stock_adjustment.normal'), 'abnormal' =>  __('stock_adjustment.abnormal')], null, ['class' => 'form-control select2 adjustment_type_click', 'placeholder' => __('messages.please_select'), 'required']); !!}
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label('document', 'Attach Document:') !!}
						{!! Form::file('documents[]', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types'))), 'class' => 'file']); !!}

						<p class="help-block">
							@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
							@includeIf('components.document_help_text')
						</p>
						<div class="contents"></div>
						<br><span><a href="javascript:void(0);" class="add btn btn-default" >Add More</a></span>
					</div>
				</div>
			</div>
		</div>
	</div> <!--box end-->
	<div class="box box-solid">
		<div class="box-header">
        	<h3 class="box-title">{{ __('stock_adjustment.search_products') }}</h3>
       	</div>
		<div class="box-body">
			<div class="row">
				<div class="col-sm-8 col-sm-offset-2">
					<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-search"></i>
							</span>
							{!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product_for_srock_adjustment', 'placeholder' => __('stock_adjustment.search_product'),]); !!}
						</div>
					</div>
					<div class="alert-box failure" id="search_prod">No matching product found!</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-10 col-sm-offset-1">
					<input type="hidden" id="product_row_index" value="0">
					<input type="hidden" id="total_amount" name="final_total" value="0">
					<div class="table-responsive">
					<table class="table table-bordered table-striped table-condensed" 
					id="stock_adjustment_product_table">
						<thead>
							<tr>
								<th class="col-sm-4 text-center">	
									@lang('sale.product')
								</th>
								<th class="col-sm-1 text-center">Cost</th>
								<th class="col-sm-1 text-center">
									@lang('sale.qty')
								</th>
								<th class="col-sm-1 text-center">Qty On Hand</th>
								<th class="col-sm-1 text-center">New Qty</th>
								<th class="col-sm-1 text-center">Diff Qty</th>
								<th class="col-sm-2 text-center">
									@lang('sale.subtotal')
								</th>
								<th class="col-sm-2 text-center"><i class="fa fa-trash" aria-hidden="true"></i></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
						<tfoot>
							<tr class="text-center"><td colspan="2"></td><td><div class="pull-right"><b>@lang('stock_adjustment.total_amount'):</b> <span id="total_adjustment">0.00</span></div></td></tr>
						</tfoot>
					</table>
					</div>
				</div>
			</div>
		</div>
	</div> <!--box end-->
	<div class="box box-solid">
		<div class="box-body">
			<div class="row">
				{{-- <div class="col-sm-4">
					<div class="form-group">
							{!! Form::label('total_amount_recovered', __('stock_adjustment.total_amount_recovered') . ':') !!} @show_tooltip(__('tooltip.total_amount_recovered'))
							{!! Form::text('total_amount_recovered', 0, ['class' => 'form-control input_number', 'placeholder' => __('stock_adjustment.total_amount_recovered')]); !!}
					</div>
				</div> --}}
				<div class="col-sm-12">
					<div class="form-group">
							{!! Form::label('additional_notes', __('stock_adjustment.reason_for_stock_adjustment') . ':') !!}
							{!! Form::textarea('additional_notes', null, ['class' => 'form-control', 'placeholder' => __('stock_adjustment.reason_for_stock_adjustment'), 'rows' => 3]); !!}
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<button type="submit" class="btn btn-primary pull-right">@lang('messages.save')</button>
				</div>
			</div>

		</div>
	</div> <!--box end-->
	{!! Form::close() !!}
</section>
@stop
@section('javascript')
	<script src="{{ asset('js/stock_adjustment.js?v=' . $asset_v) }}"></script>
	<script type="text/javascript">
		__page_leave_confirmation('#stock_adjustment_form');

        onScan.attachTo(document, {
            suffixKeyCodes: [13], // enter-key expected at the end of a scan
            reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
            onScan: function(sCode, iQty) {
                $('input#search_product_for_srock_adjustment').val(sCode);
            },
            onScanError: function(oDebug) {
                // console.log(oDebug);
            },
            minLength: 2,
            ignoreIfFocusOn: ['input', '.form-control']
            // onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
            //     console.log('Pressed: ' + iKeyCode);
            // }
        });
        
		$(document).ready(function() {
			$('form').change(function() {
			 	allRows();
			}).click(function() {
			    allRows();
			});

			function allRows()
			{
			    $('#stock_adjustment_product_table tbody')
			    .find('tr')
			    .each(function() {
			     //   console.log('row',$(this).closest('tr'));
			        update_table_row($(this).closest('tr'));
			    });
			}
			
			//Prevent enter key function except texarea
		    $('form').on('keyup keypress', function(e) {
		        var keyCode = e.keyCode || e.which;
		        if (keyCode === 13 && e.target.tagName != 'TEXTAREA') {
		            e.preventDefault();
		            return false;
		        }
		    });
		});			

		$('body').on('keydown', 'input, select, textarea', function(e) {
			var self = $(this)
			  , form = self.parents('form:eq(0)')
			  , focusable
			  , next
			  , prev
			  ;
			if (e.shiftKey) {
				if (e.keyCode == 107) {
				    focusable =   form.find('input,a,select,button,textarea').filter(':visible');
				    prev = focusable.eq(focusable.index(this)-1); 
				    if (prev.length) {
				        prev.focus();
				   			console.log('first')
				    } else {
				        form.submit();
				    }
				}
			}else{
				if (e.keyCode == 107) {
				    focusable = form.find('input,a,select,button,textarea').filter(':visible');
				    next = focusable.eq(focusable.index(this)+1);
				    if (next.length) {
				        var row_id =  $(this).data("row");
				        var row_input =  $(this).data("input");				        	
			        	var j = row_id +1;
			        	var next_row = $('#input_'+j+'_box'+1).val();
			        	if(next_row)
			        	{
			        		document.getElementById('input_'+j+'_box'+1).focus();
			        	}else{
				        	document.getElementById('search_product_for_srock_adjustment').focus();
			        	}
				        
				    } else {
				        form.submit();
				    }
				    return false;
				}
			}
		});
		$(document).ready(function() {
		  	$(".add").click(function() {
		    	$('<div><br><input class="files" name="documents[]" type="file" accept="application/pdf,text/csv,application/zip,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/jpg,image/png" ><span class="rem" ><a href="javascript:void(0);" title="Clear selected files" class="deleteDoc btn btn-danger btn-xs fileinput-remove fileinput-remove-button"><i class="glyphicon glyphicon-trash"></i>  <span class="hidden-xs">Remove</span></span></div>').appendTo(".contents");
		    });
			$('.contents').on('click', '.rem', function() {
			    $(this).parent("div").remove();
			});
		});
	</script>
@endsection
