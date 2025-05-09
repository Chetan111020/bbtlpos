<div class="table-responsive bg-gray">
    <table class="table table-condensed bg-gray" id="brand-tree-report-table">
      <thead>
      <tr>
        <th>&nbsp;</th>
        <th>@lang('Brand')</th>
        <th>@lang('Sale Total')</th>
        <th>@lang('Sale Percentage')</th>
        <th>@lang('Inventory')</th>
        <th>@lang('Negative Inventory')</th>
        <th>@lang('Inventory Percentage')</th>
        <th>@lang('Difference')</th>
      </tr>
      </thead>
      <tbody>
      @forelse ($resultData as $data)
            @php
                @$sale_percentage = 0;
                @$inventory_percent = 0;
            @endphp
            
            @if($total_sale!==0)
                @php
                    @$spercent =  ($data->unit_price*100)/$total_sale;
                    @$sale_percentage = round($spercent,2);
                @endphp
            @endif
            
            @if($total_invent!==0)
                @php
                    @$ipercent =  ($data->total_inventory*100)/$total_invent;
                    @$inventory_percent = round($ipercent,2);
                @endphp
            @endif
        
        <tr role="row" class="product-tree-tr">
            <input type="hidden" name="brandId" class="brandId" value="{{ $data->brand_id }}"/>
            <input type="hidden" name="categoryId" class="categoryId" value="{{ $categoryId }}" />
            <td class="product-details-control"></td>
            <td>{!! $data->brand_name !!}</td>
            <td><span class="display_currency total_sale" data-currency_symbol="true" data-orig-value="{{ $data->unit_price }}">{!! $data->unit_price !!}</span></td>
            <td><span class="total-sale-percent" >{!! $sale_percentage !!} % </span></td>
            <td><span class="display_currency total_inventory" data-currency_symbol="true" data-orig-value="{{ $data->total_inventory }}">{!! $data->total_inventory !!}</span></td>
            <td><span class="display_currency total_negative_inventory" data-currency_symbol="true" data-orig-value="{{ $data->negative_inventory }}">{!! $data->negative_inventory !!}</span></td>
            <td><span class="total-inventory-percent" data-orig-value="{{ $inventory_percent }}">{!! $inventory_percent !!} % </span></td>
            <td><span class="inv_diff" data-orig-value="{{ ($inventory_percent - $sale_percentage) }}" >{!! ($inventory_percent - $sale_percentage) !!} % </span></td>
        </tr>
      @empty
        
      @endforelse
        <tr role="row" class="product-tree-tr non-brand">
            <input type="hidden" name="brandId" class="brandId" value="0"/>
            <input type="hidden" name="categoryId" class="categoryId" value="{{ $categoryId }}" />
            <td class="product-details-control"></td>
            <td>Non-Branded Product</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
      </tbody>
      <tfoot>
        <td></td>
        <td><strong>Total:</strong></td>
        <td class="display_currency b_footer_sale_total" data-currency_symbol ="true"></td>
        <td class="b_footer_sale_percent"></td>
        <td class="display_currency b_footer_invent_total" data-currency_symbol ="true">></td>
        <td class="display_currency b_footer_negative_inventory" data-currency_symbol ="true"></td>
        <td class="b_footer_invent_percent"></td>
        <td class="b_footer_diff">0</td>
    </tfoot>
    </table>
</div>

    <script type="text/javascript">
        // Array to track the ids of the details displayed rows
        var ppr_detail_product_rows = [];
        if ( $.fn.DataTable.isDataTable( '#brand-tree-report-table' ) ) {
            $('#brand-tree-report-table' ).DataTable().destroy();
         }
        var brandTable = $('#brand-tree-report-table').DataTable({
            paging: false,
            language: {
                searchPlaceholder: "Search Brand Records..."
            },
            fnDrawCallback: function(oSettings) {
                var total_sale = sum_table_col($('#brand-tree-report-table'), 'total_sale');
                $('#brand-tree-report-table .b_footer_sale_total').text(total_sale);
    
                var total_percent = sum_table_col($('#brand-tree-report-table'), 'total-sale-percent');
                var percentage_sale = total_percent +'%';
                $('#brand-tree-report-table .b_footer_sale_percent').text(percentage_sale);
    
                var total_sale = sum_table_col($('#brand-tree-report-table'), 'total_inventory');
                $('#brand-tree-report-table .b_footer_invent_total').text(total_sale);
                
                var negative_inventory = sum_table_col($('#brand-tree-report-table'), 'total_negative_inventory');
                $('#brand-tree-report-table .b_footer_negative_inventory').text(negative_inventory);
                
                var total_inv_percent = sum_table_col($('#brand-tree-report-table'), 'total-inventory-percent');
                var inv_percentage_sale = total_inv_percent +'%';
                $('#brand-tree-report-table .b_footer_invent_percent').text(inv_percentage_sale);
                
                var inv_diff = sum_table_col($('#brand-tree-report-table'), 'inv_diff') + '%';
                $('#brand-tree-report-table .b_footer_diff').text(inv_diff);
    
                __currency_convert_recursively($('#brand-tree-report-table'));
            },
        }); //initialize the display data-table for append product-data
        
        
        //brand-data > row click get & display product-report data
        $('table#brand-tree-report-table').on('click','tr td.product-details-control', function(e) {
            if ($('#brand-tree-report-table').find('tbody > tr').hasClass("product-details"))
            {
                $('#brand-tree-report-table').find('.product-details').next('tr').empty();
                $('#brand-tree-report-table').find('tbody > tr').removeClass("product-details");
            }
            var tr = $(this).closest('tr'); //find the clicked tr data
            var row = brandTable.row(tr);
            var idx = $.inArray(tr.attr('id'), ppr_detail_product_rows);
            
            //get brand and category id for getting particular product
            var brandId = $(this).closest('tr .product-tree-tr').find('.brandId').val();
            var categoryId = $(this).closest('tr .product-tree-tr').find('.categoryId').val();
            console.log(row.child.isShown());
            if (row.child.isShown()) {
                tr.removeClass('product-details');
                row.child.hide();
    
                // Remove from the 'open' array
                ppr_detail_product_rows.splice(idx, 1);
            } else {
                tr.addClass('product-details');
                var nonBrandFlag=0;
                if(tr.hasClass('non-brand')){
                    nonBrandFlag=1;
                }
                    var resultData = show_child_productData(row.data(),brandId,categoryId,nonBrandFlag);
                    row.child(resultData).show();
                }
                // Add to the 'open' array
                if (idx === -1) {
                    ppr_detail_product_rows.push(tr.attr('id'));
                }
        });
        
        // brand-data on click > get and display related product-report data
        function show_child_productData(rowData,brandId,categoryId,nonBrandFlag) {
            var div = $('<div/>')
                .addClass('loading')
                .text('Loading...');
            var start,end;
            if($('#category_list_date_filter').val()) {
                start = $('input#category_list_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                end = $('input#category_list_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
            }
            $.ajax({
                type: 'POST',
                url: '/reports/product-tree-report',
                data: {
                    nonBrandFlag: nonBrandFlag,
                    categoryId: categoryId,
                    brandId: brandId,
                    startDate:start,
                    endDate:end,
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