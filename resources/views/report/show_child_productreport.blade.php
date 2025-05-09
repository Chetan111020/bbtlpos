<div class="table-responsive bg-white">
<table class="table table-condensed" id="product-tree-report-table">
  <thead>
  <tr>
    <th>@lang('Product')</th>
    <th>@lang('Sale Total')</th>
    <th>@lang('Quantity')</th>
    <th>@lang('Sale Percentage')</th>
    <th>@lang('Inventory')</th>
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
                    @$percent =  ($data->unit_price*100)/$total_sale;
                    @$sale_percentage = round($percent,2);
                @endphp
            @endif
            
            @if($total_invent!==0)
                @php
                    @$ipercent =  ($data->total_inventory*100)/$total_invent;
                    @$inventory_percent = round($ipercent,2);
                    
                @endphp
            @endif
        
        <tr>
            <td>{!! $data->product_name !!}</td>
            <td><span class="display_currency total_sale" data-currency_symbol="true" data-orig-value="{{ $data->unit_price }}">{!! $data->unit_price !!}</span></td>
            <td><span class="total_quantity" data-orig-value="{{ $data->quantity }}">{!! $data->quantity !!} </span></td>
            <td><span class="total-sale-percent" data-orig-value="{{ $sale_percentage }}">{!! $sale_percentage !!} % </span></td>
            <td><span class="display_currency total_inventory" data-currency_symbol="true" data-orig-value="{{ $data->total_inventory }}">{!! $data->total_inventory !!}</span></td>
            <td><span class="total-inventory-percent" data-orig-value="{{ $inventory_percent }}">{!! $inventory_percent !!} % </span></td>
            <td><span class="total_inv_diff" data-orig-value="{{ ($inventory_percent - $sale_percentage) }}">{!! ($inventory_percent - $sale_percentage) !!} % </span></td>
        </tr>
      @empty
        <tr class="text-center">
          <td>@lang('purchase.no_records_found')</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
      @endforelse
     </tbody>
     <tfoot class="bg-gray">
        <td><strong>Total:</strong></td>
        <td class="display_currency p_footer_sale_total" data-currency_symbol ="true"></td>
        <td class="display_currency p_footer_quantity"></td>
        <td class="p_footer_sale_percent"></td>
        <td class="display_currency p_footer_invent_total" data-currency_symbol ="true">></td>
        <td class="p_footer_invent_percent"></td>
        <td class="p_footer_diff">0</td>
    </tfoot>
</table>
</div>

<script type="text/javascript">
        if ( $.fn.DataTable.isDataTable( '#product-tree-report-table' ) ) {
            $( '#product-tree-report-table' ).DataTable().destroy();
        }
        var table = $('#product-tree-report-table').DataTable({
                paging: false,
                language: {
                    searchPlaceholder: "Search Product records..."
                },
                fnDrawCallback: function(oSettings) {
                    var total_sale = sum_table_col($('#product-tree-report-table'), 'total_sale');
                    console.log("total_sale"+total_sale);
                    $('#product-tree-report-table .p_footer_sale_total').text(total_sale);
        
                    var total_percent = sum_table_col($('#product-tree-report-table'), 'total-sale-percent');
                    var percentage_sale = total_percent +'%';
                    $('#product-tree-report-table .p_footer_sale_percent').text(percentage_sale);
        
                    var total_sale = sum_table_col($('#product-tree-report-table'), 'total_inventory');
                    $('#product-tree-report-table .p_footer_invent_total').text(total_sale);
                    
                    var quantity = sum_table_col($('#product-tree-report-table'), 'total_quantity');
                    $('#product-tree-report-table .p_footer_quantity').text(quantity);
                    
                    var total_inv_percent = sum_table_col($('#product-tree-report-table'), 'total-inventory-percent');
                    var inv_percentage_sale = total_inv_percent +'%';
                    $('#product-tree-report-table .p_footer_invent_percent').text(inv_percentage_sale);
                    
                    var inv_diff = sum_table_col($('#product-tree-report-table'), 'total_inv_diff') + '%';
                    $('#product-tree-report-table .p_footer_diff').text(inv_diff);
        
                    __currency_convert_recursively($('#product-tree-report-table'));
                },
        }); //initialize the display data-table for append product-data
</script>