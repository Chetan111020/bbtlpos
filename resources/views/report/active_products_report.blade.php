@extends('layouts.app')
@section('title', __('Inactive Items Report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ __('Active Items Report')}}</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => action('ReportController@getStockReport'), 'method' => 'get', 'id' => 'stock_report_filter_form' ]) !!}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('category_id', __('category.category') . ':') !!}
                        {!! Form::select('category', $categories, null, ['placeholder' => __('messages.all'), 'class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'category_id']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
                        {!! Form::select('sub_category', array(), null, ['placeholder' => __('messages.all'), 'class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'sub_category_id']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('brand', __('product.brand') . ':') !!}
                        {!! Form::select('brand', $brands, null, ['placeholder' => __('messages.all'), 'class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('unit',__('product.unit') . ':') !!}
                        {!! Form::select('unit', $units, null, ['placeholder' => __('messages.all'), 'class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <input type="hidden" id="date" name="date" value="">
                    <div class="form-group">
                        {!! Form::label('active_items_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('active_items_filter', @format_date('first day of this month') . ' ~ ' . @format_date('last day of this month'), ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'active_items_filter', 'readonly']); !!}

                    </div>
                </div>
                @if(Module::has('Manufacturing'))
                    <div class="col-md-3">
                        <div class="form-group">
                            <br>
                            <div class="checkbox">
                                <label>
                                  {!! Form::checkbox('only_mfg', 1, false, 
                                  [ 'class' => 'input-icheck', 'id' => 'only_mfg_products']); !!} {{ __('manufacturing::lang.only_mfg_products') }}
                                </label>
                            </div>
                        </div>
                    </div>
                @endif
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="inactive_item_report">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>@lang('business.product')</th>
                            <th>@lang('Item code')</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Updated By</th>
                            <th>Updated On</th>
                        </tr>
                    </thead>
                    
                </table>
            </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
<script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>

<script type="text/javascript">
	$(document).ready( function() {
        
        $('#active_items_filter').daterangepicker({
                ranges: ranges,
                autoUpdateInput: false,
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month'),
                locale: {
                    format: moment_date_format
                }
            });
            $('#active_items_filter').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format(moment_date_format) + ' ~ ' + picker.endDate.format(moment_date_format));
                    $("#date").val($(this).val());
                 console.log($(this).val());
                 inactive_item_report.ajax.reload();

           
            }); 

            $('#active_items_filter').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                inactive_item_report.ajax.reload();

            });



        //Product lot Report
        inactive_item_report = $('table#inactive_item_report').DataTable({
            processing: true,
            serverSide: true,
            // aaSorting: [[3, 'desc']],
    
            ajax: {
                url: '/reports/active-items-report',
                data: function(d) {
                    d.location_id = $('#location_id').val();
                    d.category_id = $('#category_id').val();
                    d.sub_category_id = $('#sub_category_id').val();
                    d.brand_id = $('#brand').val();
                    d.unit_id = $('#unit').val();
                    if($('#active_items_filter').val()) {
                        var start = $('input#active_items_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        console.log(start);
                        var end = $('input#active_items_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        d.start_date = start;
                        d.end_date = end;
                    }
                },
            },
            columns: [
                { data: 'sub_sku', name: 'v.sub_sku' },
                { data: 'product', name: 'products.name' },
                { data: 'item_code', name: 'products.item_code' },
                { data: 'cat_name', name: 'categories.name' },
                { data: 'brand_name', name: 'brands.name' },
                { data: 'user_name'},//, name: 'users.name' },
                { data: 'last_updated', name: 'product_activities.created_at' }


            ],
    
            fnDrawCallback: function(oSettings) {
                
            },
        });
    
        inactive_item_report.ajax.reload();
        $(' #location_id, #category_id, #sub_category_id, #unit, #brand').change(function() {
            inactive_item_report.ajax.reload();
        });
        
	});
</script>
@endsection