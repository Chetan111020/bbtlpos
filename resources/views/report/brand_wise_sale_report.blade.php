@extends('layouts.app')
@section('title', __( 'Brand Wise Sale Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'Brand Wise Sale Report' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row no-print">
        <div class="col-md-12">
            <div class="form-group pull-right">
                <div class="col-md-8">
                    <input type="hidden" id="date" name="date" value="">
                    <div class="form-group">
                        {!! Form::label('brand_list_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('brand_list_date_filter', @format_date('first day of this month') . ' ~ ' . @format_date('last day of this month'), ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'brand_list_date_filter', 'readonly']); !!}

                        {{-- {!! Form::label('category_list_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('category_list_date_filter', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!} --}}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <button class="btn btn-primary" id="getdata" style="margin-top: 24px;">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div id="brand-table" style="display: none" >
    <div class="table-responsive">
        <table class="table table-bordered table-striped" style="width: 100%" id="brand_wise_sale">
            <thead>
                <tr>
                    <td>Brand</td>
                    <td>Sale Total</td>
                    <td>Sale Percentage</td>
                    <td>Inventory</td>
                    <td>Negative Inventory</td>
                    <td>Inventory Percentage</td>
                    <td>Difference</td>
                </tr>
            </thead>
            <tfoot>
                <td><strong>Total:</strong></td>
                <td class="display_currency footer_sale_total" data-currency_symbol ="true"></td>
                <td class="footer_sale_percent"></td>
                <td class="display_currency footer_invent_total" data-currency_symbol ="true">></td>
                <td class= "display_currency footer_negative_inventory" data-currency_symbol ="true"></td>
                <td class="footer_invent_percent"></td>
                <td>0</td>
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
        $('#brand_list_date_filter').daterangepicker({
                ranges: ranges,
                autoUpdateInput: false,
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month'),
                locale: {
                    format: moment_date_format
                }
            });
            $('#brand_list_date_filter').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format(moment_date_format) + ' ~ ' + picker.endDate.format(moment_date_format));
                    $("#date").val($(this).val());
                 console.log($(this).val());
                // category_wise.ajax.reload();
                // category_wise.ajax.reload();
            }); 

            $('#brand_list_date_filter').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                brand_wise.ajax.reload();
                brand_wise.ajax.reload();
            });


            brand_wise =  $('#brand_wise_sale').DataTable({
            processing: true,
            serverSide: true,
            
            "ajax": {
                "url": "/reports/brand-wise-sale-report",
                "data": function ( d ) {
                    if($('#brand_list_date_filter').val()) {
                        var start = $('input#brand_list_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        console.log(start);
                        var end = $('input#brand_list_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        d.start_date = start;
                        d.end_date = end;
                        
            }
                }
            },
            columns: [
                { data: 'brand_name', name: 'brand_name' , searchable: true},
                { data: 'total_sold_price', searchable: false},
                { data: 'sale_percentage', searchable: false},
                { data: 'total_stock_price',searchable: false},
                { data: 'negative_inventory',searchable: false},
                { data: 'inventory_percentage',searchable: false},
                { data: 'difference',searchable: false},
                
            ],
            fnDrawCallback: function(oSettings) {
                var total_sale = sum_table_col($('#brand_wise_sale'), 'total_sale');
                $('#brand_wise_sale .footer_sale_total').text(total_sale);

                var total_sale_percent = sum_table_col($('#brand_wise_sale'), 'total-sale-percent');
                var percentage_sale = parseFloat(total_sale_percent).toFixed(2);
                percentage_sale =  percentage_sale +'%';
                $('#brand_wise_sale .footer_sale_percent').text(percentage_sale);

                var total_inventory = sum_table_col($('#brand_wise_sale'), 'total_inventory');
                $('#brand_wise_sale .footer_invent_total').text(total_inventory);
                
                var total_negative_inventory = sum_table_col($('#brand_wise_sale'), 'total_negative_inventory');
                $('#brand_wise_sale .footer_negative_inventory').text(total_negative_inventory);
                
                var total_invent_percent = sum_table_col($('#brand_wise_sale'), 'total-inventory-percent');
                var percentage_inventory = parseFloat(total_invent_percent).toFixed(2);
                percentage_inventory = percentage_inventory +'%';
                $('#brand_wise_sale .footer_invent_percent').text(percentage_inventory);

                __currency_convert_recursively($('#brand_wise_sale'));
            },
        });
    
    });


    

$("#getdata").on('click',function(){
    $("#brand-table").removeAttr("style");
    brand_wise.ajax.reload();

    // var date = $("#date").val();

    // console.log(date.slice(0, date.lastIndexOf(' ') + 1));

    // var end_date = date.substring(date.indexOf('~') + 1);
    // var start_date = date.substring(date.indexOf('~') - 1)

    
        
});
    // $(document).ready( function() {

    
    // });
</script>

@endsection
