@extends('layouts.app')
@section('title', __( 'Back Order Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Back Order Report</h1>
</section>
@php
    $curr_customer = Request::get('customer') ?? 0;
@endphp
<!-- Main content -->
<section class="content">
    <div class="col-md-12">
        <div class="row g-3">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('customer_id',  __('Select Customer') . ':') !!}
                    <select id="customer_id" class="form-control select2" name="customer_id" style="width:100%;">
                        <option value="all">All</option>
                        @foreach ($customers as $item)
                            <option value="{{ $item->id }}" {{($item->id == $curr_customer)? 'selected' : '' }}>
                                @if (!empty($item->name))
                                    {{ $item->name }}
                                @elseif (!empty($item->supplier_business_name))
                                    {{ $item->supplier_business_name }}
                                @else
                                    {{ $item->contact_id }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('dtrange',  __('Date Range') . ':') !!}
                    <input type="text" id="dtrange" class="form-control" readonly />
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Search by Products</label>
                    {!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product', 'placeholder' => __('Enter Barcode To See Product'),
                    'tabindex' => "-1"
                    ]); !!}
                    <input type="hidden" id="variation_id" value="all" />
                </div>
            </div>
            <div class="col-md-12" style="display:flex;">
                <button class="btn bg-white" style="margin-left:auto;" id="clear_product_filter">Clear Product Filter</button>
            </div>
        @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 shadow rounded table-responsive">
            <table class="table table-bordered table-striped bg-white" id="mytable">
                <thead>
                    <tr>
                        <th>Transaction Date</th>
                        <th>Invoice No</th>
                        <th>Customer Name</th>
                        <th>Invoice Total</th>
                        <th>Ordered Products</th>
                        <th>Available Products</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')
<script>
    $(document).ready(function() {

        dateRangeSettings.locale.format = "YYYY/MM/DD";

        dateRangeSettings.startDate = '{{ date("Y-01-01") }}';
        dateRangeSettings.endDate = '{{ date("Y-12-31") }}';

        //Date range as a button
        $('#dtrange').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#dtrange').val(start.format('YYYY/MM/DD') + ' - ' + end.format('YYYY/MM/DD'));
                dtable.ajax.reload();
            }
        );

        var url = "{{ route('reports.back_order') }}";
        var dtable = $('#mytable').DataTable({
            processing: true,
            serverSide: true,
            "ajax": {
                "url": url,
                "data": function(d){
                    d.dates = $('#dtrange').val();
                    d.customer = $('#customer_id').val();
                    d.variation_id = $('#variation_id').val();
                }
            },
            aaSorting: [[0, 'asc']],
            columns: [
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'invoice_no', name: 'invoice_no'},
                { data: 'name', name: 'name'},
                { data: 'final_total', name: 'final_total' , "sClass": "text-right"},
                { data: 'products', name: 'products' , "sClass": "text-right"},
                { data: 'back_order', name: 'back_order' , "sClass": "text-right"},
                { data: 'action', name: 'action', searchable: false, orderable: false},
            ]
        });

        $('.select2').on('select2:select', function (e) {
            dtable.ajax.reload();
        });

        $('#clear_product_filter').on('click', function(){
            $('#variation_id').val('all');
            $('#search_product').val('');
            dtable.ajax.reload();
        });

        if ($('#search_product').length) {
            //Add Product
            $('#search_product')
                .autocomplete({
                    source: function(request, response) {
                        var price_group = '';
                        var search_fields = ['name','sku','item_code'];

                        search_fields.push("item_code");

                        if ($('#price_group').length > 0) {
                            price_group = $('#price_group').val();
                        }
                        $.getJSON(
                            '/products/list', {
                                price_group: price_group,
                                location_id: $('input#location_id').val(),
                                term: request.term,
                                not_for_selling: 0,
                                search_fields: search_fields
                            },
                            response
                        );
                    },
                    minLength: 2,
                    response: function(event, ui) {
                        var is_overselling_allowed = false;
                        if ($('input#is_overselling_allowed').length) {
                            is_overselling_allowed = true;
                        }
                        if (ui.content.length == 1) {
                            ui.item = ui.content[0];
                            if ((ui.item.enable_stock == 1 && ui.item.qty_available > 0) || (ui.item.enable_stock == 1 && ui.item.qty_available < 0 && is_overselling_allowed)  ||
                                (ui.item.enable_stock == 0)) {
                                let customer_id = $('#customer_id').val();
                                    if (customer_id === "") {
                                        toastr.error("Customer Not Selected!");
                                        $(this).autocomplete('close');
                                    } else{
                                        $(this)
                                            .data('ui-autocomplete')
                                            ._trigger('select', 'autocompleteselect', ui);
                                        $(this).autocomplete('close');
                                    }
                            }
                        } else if (ui.content.length == 0) {
                            toastr.error(LANG.no_products_found);
                            $('input#search_product').select();
                        }
                    },
                    focus: function(event, ui) {
                        if (ui.item.qty_available <= 0) {
                            return false;
                        }
                    },
                    select: function(event, ui) {
                        $('#variation_id').val(ui.item.variation_id);
                        dtable.ajax.reload();
                    },
                })
                .autocomplete('instance')._renderItem = function(ul, item) {
                    var is_overselling_allowed = false;
                    if ($('input#is_overselling_allowed').length) {
                        is_overselling_allowed = true;
                    }
                    if (item.enable_stock == 1 && item.qty_available <= 0 && !is_overselling_allowed) {
                    var string = '<li class="ui-state-disabled"><b>' + item.name;
                        if (item.type == 'variable') {
                            string += '-' + item.variation;
                        }
                        var selling_price = item.selling_price;
                        if (item.variation_group_price) {
                            selling_price = item.variation_group_price;
                        }
                        string +=
                            ' </b> (' +
                            item.sub_sku +
                            ')' +
                            '<br> Price: ' +
                            selling_price +
                            ' (Out of stock) </li>';
                        return $(string).appendTo(ul);
                    } else {
                        var string = '<div><b>' + item.name;
                        if (item.type == 'variable') {
                            string += '-' + item.variation;
                        }

                        var selling_price = item.selling_price;
                        if (item.variation_group_price) {
                            selling_price = item.variation_group_price;
                        }

                        string += ' </b> (' + item.sub_sku + ')' + '<br> Price: ' + selling_price;
                        if (item.enable_stock == 1) {
                            var qty_available = __currency_trans_from_en(item.qty_available, false, false, __currency_precision, true);
                            if(qty_available > 1){
                                string += ' - <span style="color:#1abb1a;">' + qty_available + item.unit;
                            }
                            else if(qty_available < 1){
                                string += ' - <span style="color:red;"> ' + qty_available + item.unit;
                            }
                        }
                        string += '</span></div>';

                        return $('<li>')
                            .append(string)
                            .appendTo(ul);
                    }
                };
        }


    });
</script>
@endsection