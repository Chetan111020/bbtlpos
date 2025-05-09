@extends('layouts.app')
@section('title', __( 'Product Wise Sale Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'Product Wise Sale Report' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row no-print">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
                {{-- <div class="form-group pull-right"> --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('category_id', __('lang_v1.search_category') . ':') !!}
                            {!! Form::select('product', $categories, null, ['class' => 'form-control select2', 'id' => 'category_id', 'style' => 'width:100%', 'placeholder' => __('Select Category')]); !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('brand_id', __('lang_v1.search_brand') . ':') !!}
                            {!! Form::select('brand', $brands, null, ['class' => 'form-control select2', 'id' => 'brand_id', 'style' => 'width:100%', 'placeholder' => __('Select Brand')]); !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <input type="hidden" id="date" name="date" value="">
                        <div class="form-group">
                            {!! Form::label('product_list_date_filter', __('report.date_range') . ':') !!}
                            {!! Form::text('product_list_date_filter', @format_date('first day of this month') . ' ~ ' . @format_date('last day of this month'), ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'product_list_date_filter', 'readonly']); !!}

                            {{-- {!! Form::label('category_list_date_filter', __('report.date_range') . ':') !!}
                            {!! Form::text('category_list_date_filter', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!} --}}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <button class="btn btn-primary" id="getdata" >Submit</button>
                        </div>
                    </div>
                {{-- </div> --}}
            @endcomponent
        </div>
    </div>

    <div id="product-table" style="display: none" >
        <div class="table-responsive">
            <table class="table table-bordered table-striped" data-display-length='-1' style="width: 100%" id="product_wise_sale">
                <thead>
                    <tr>
                        <td>Product</td>
                        <td>Sale Total</td>
                        <td>Quantity</td>
                        <td>Sale Percentage</td>
                        <td>Inventory</td>
                        <td>Inventory Percentage</td>
                        <td>Difference</td>
                        <td>Current Stock</td>
                    </tr>
                </thead>
                <tfoot>
                    <td><strong>Total:</strong></td>
                    <td class="display_currency footer_sale_total" data-currency_symbol ="true"></td>
                    <td class="footer_quantity_total"></td>
                    <td class="footer_sale_percent"></td>
                    <td class="display_currency footer_invent_total" data-currency_symbol ="true"></td>
                    <td class="footer_invent_percent" ></td>
                    <td>0</td>
                    <td class="footer_total_stock"></td>
                </tfoot>
            </table>
        </div>
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')
<script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>

<script type="text/javascript">

    $(document).ready( function() {
        $('#product_list_date_filter').daterangepicker({
                ranges: ranges,
                autoUpdateInput: false,
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month'),
                locale: {
                    format: moment_date_format
                }
            });
            $('#product_list_date_filter').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format(moment_date_format) + ' ~ ' + picker.endDate.format(moment_date_format));
                    $("#date").val($(this).val());
                 console.log($(this).val());
                // product_wise.ajax.reload();
                // product_wise.ajax.reload();
            }); 

            $('#product_list_date_filter').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                product_wise.ajax.reload();
                product_wise.ajax.reload();
            });


        product_wise =  $('#product_wise_sale').DataTable({
            processing: true,
            // serverSide: true,
            "ajax": {
                "url": "/reports/product-wise-sale-report",
                "data": function ( d ) {

                    d.category_id = $('#category_id').val();
                    d.brand_id = $('#brand_id').val();

                    if($('#product_list_date_filter').val()) {
                        var start = $('input#product_list_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        console.log(start);
                        var end = $('input#product_list_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        d.start_date = start;
                        d.end_date = end;
                        
                    }
                }
            },
            columns: [
                { data: 'product_name', name: 'product_name' , "searchable": true },
                { data: 'total_sold_price', "searchable": false},
                { data: 'quantity' , "searchable": false},
                { data: 'sale_percentage', "searchable": false},
                { data: 'total_stock_price',"searchable": false},
                { data: 'inventory_percentage',"searchable": false},
                { data: 'difference',"searchable": false},
                                { data: 'stock',"searchable": false},

                
            ],
            fnDrawCallback: function(oSettings) {
                var total_sale = sum_table_col($('#product_wise_sale'), 'total_sale');
                $('#product_wise_sale .footer_sale_total').text(total_sale);

                var total_quantity = sum_table_col($('#product_wise_sale'), 'total_quantity');
                $('#product_wise_sale .footer_quantity_total').text(total_quantity);
                
                var total_stock = sum_table_col($('#product_wise_sale'), 'stock');
                $('#product_wise_sale .footer_total_stock').text(total_stock);

                var total_percent = sum_table_col($('#product_wise_sale'), 'total-sale-percent');
                var percentage_sale = total_percent.toFixed(2) +'%';
                $('#product_wise_sale .footer_sale_percent').text(percentage_sale);

                var total_sale = sum_table_col($('#product_wise_sale'), 'total_inventory');
                $('#product_wise_sale .footer_invent_total').text(total_sale);

                var total_percent = sum_table_col($('#product_wise_sale'), 'total-inventory-percent');
                var percentage_sale = total_percent.toFixed(2) +'%';
                $('#product_wise_sale .footer_invent_percent').text(percentage_sale);

                __currency_convert_recursively($('#product_wise_sale'));
            },       
        });

        if ($('table#product_wise_sale').length == 1) {
            $('#category_id').change(function() {
                product_wise.ajax.reload();
            });
            $('#brand_id').change(function() {
                product_wise.ajax.reload();
            });
        }

    });
  

$("#getdata").on('click',function(){
    $("#product-table").removeAttr("style");
    product_wise.ajax.reload();
    
    // var date = $("#date").val();

    // console.log(date.slice(0, date.lastIndexOf(' ') + 1));

    // var end_date = date.substring(date.indexOf('~') + 1);
    // var start_date = date.substring(date.indexOf('~') - 1)

    
        
});
   
    // $(document).ready( function() {

    
    // });
   
</script>

@endsection