@extends('layouts.app')
@section('title', __( 'Sales Tree Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'Sales Tree Report' )
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
                        {!! Form::label('category_list_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('category_list_date_filter', @format_date('first day of this month') . ' ~ ' . @format_date('last day of this month'), ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'category_list_date_filter', 'readonly']); !!}

                        {{-- {!! Form::label('category_list_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('category_list_date_filter', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!} --}}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <button class="btn btn-primary" id="getdata" >Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12" id="category-table" style="display: none">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" 
                    id="sales_tree_report_table">
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                
                                <td>Category</td>
                                <td>Sale Total</td>
                                <td>Sale Percentage</td>
                                <td>Inventory</td>
                                <td>Inventory Percentage</td>
                                <td>Difference</td>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-gray font-17 footer-total text-center">
                                <td>&nbsp;</td>
                                
                                <td><strong>Total:</strong></td>
                                <td class="display_currency footer_sale_total" data-currency_symbol ="true"></td>
                                <td class="footer_sale_percent"></td>
                                <td class="display_currency footer_invent_total" data-currency_symbol ="true">></td>
                                <td class="footer_invent_percent"></td>
                                <td class="footer_total_diff">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade view_register" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

@endsection

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
    var sales_tree_report;
    var start,end;    
    $(document).ready( function() {
        $('#category_list_date_filter').daterangepicker({
            ranges: ranges,
            autoUpdateInput: false,
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            locale: {
                format: moment_date_format
            }
        });
        $('#category_list_date_filter').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format(moment_date_format) + ' ~ ' + picker.endDate.format(moment_date_format));
                $("#date").val($(this).val());
        }); 

        $('#category_list_date_filter').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            sales_tree_report.ajax.reload();
            sales_tree_report.ajax.reload();
        });
        
        //Sales-Tree Report (category-brand-product wise)
        sales_tree_report = $('table#sales_tree_report_table').DataTable({
            paging: false,
            processing: true,
            serverSide: true,
            aaSorting: [[1, 'asc']],
            ajax: {
                url: '/reports/sales-tree-report',
                data: function(d) {
                    if($('#category_list_date_filter').val()) {
                        start = $('input#category_list_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        end = $('input#category_list_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        d.start_date = start;
                        d.end_date = end;
                    }
                },
            },
            columns: [
                {
                    orderable: false,
                    searchable: false,
                    data: null,
                    defaultContent: '',
                },
                    { data: 'category_name', name: 'category_name'  },
                    { data: 'total_sold_price', "searchable": false},
                    { data: 'sale_percentage', "searchable": false},
                    { data: 'total_stock_price',"searchable": false},
                    { data: 'inventory_percentage',"searchable": false},
                    { data: 'difference',"searchable": false},
            ],
            fnDrawCallback: function(oSettings) {
                var total_sale = sum_table_col($('#sales_tree_report_table'), 'total_sale');
                $('#sales_tree_report_table .footer_sale_total').text(total_sale);
    
                var total_percent = sum_table_col($('#sales_tree_report_table'), 'total-sale-percent');
                var percentage_sale = total_percent +'%';
                $('#sales_tree_report_table .footer_sale_percent').text(percentage_sale);
    
                var total_sale = sum_table_col($('#sales_tree_report_table'), 'total_inventory');
                
                $('#sales_tree_report_table .footer_invent_total').text(total_sale);
    
                var total_percent = sum_table_col($('#sales_tree_report_table'), 'total-inventory-percent');
                var percentage_sale = total_percent +'%';
                $('#sales_tree_report_table .footer_invent_percent').text(percentage_sale);
                
                var total_diff = sum_table_col($('#sales_tree_report_table'), 'footer-total-diff') +'%';
                $('#sales_tree_report_table .footer_total_diff').text(total_diff);
                
                __currency_convert_recursively($('#sales_tree_report_table'));
            },
            createdRow: function(row, data, dataIndex) {
                $(row).find('td:eq(0)').attr('id', data.category_id);
                // if (!data.transaction_id) {
                    $(row)
                        .find('td:eq(0)')
                        .addClass('details-control');
                // }
            },
        });
        
        
    });
    
    //get selected date wise data in sale-tree report
    $("#getdata").on('click',function(){
        $("#category-table").removeAttr("style");
        sales_tree_report.ajax.reload();
    });
    
    // Array to track the ids of the details displayed rows
    var ppr_detail_rows = [];

    $('#sales_tree_report_table').on('click','tr td.details-control', function(e) {
        
        if ($('#sales_tree_report_table').find('tbody > tr').hasClass("details"))
        {
            $('#sales_tree_report_table').find('.details').next('tr').empty();
            $('#sales_tree_report_table').find('tbody > tr').removeClass("details");
        }
        
        var tr = $(this).closest('tr');
        var row = sales_tree_report.row(tr);
        var idx = $.inArray(tr.attr('id'), ppr_detail_rows);
        var categoryId = tr.find('td.details-control').attr('id');
        
        if (row.child.isShown()) {
            tr.removeClass('details');
            row.child.hide();

            // Remove from the 'open' array
            ppr_detail_rows.splice(idx, 1);
        } else {
            tr.addClass('details');

            row.child(show_child_brandData(row.data(),categoryId)).show();

            // Add to the 'open' array
            if (idx === -1) {
                ppr_detail_rows.push(tr.attr('id'));
            }
        }
    });
    
    
    // On each draw, loop over the `detailRows` array and show any child rows
    if(sales_tree_report !== undefined){
        sales_tree_report.on('draw', function() {
            $.each(ppr_detail_rows, function(i, id) {
                $('#' + id + ' td.details-control').trigger('click');
            });
        });
    }
    function show_child_brandData(rowData,categoryId) {
        
        var div = $('<div/>')
            .addClass('loading')
            .text('Loading...');
        $.ajax({
            url: '/reports/brand-tree-report',
            method: 'POST',
            data: { 
                start_date: start,
                end_date: end,
                category_id: categoryId,
            },
            dataType: 'html',
            success: function(data) {
                div.html(data).removeClass('loading');
                __currency_convert_recursively(div);
            },
        });
    
        return div;
    }
    </script>
@endsection