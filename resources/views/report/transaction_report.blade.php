@extends('layouts.app')
@section('title', __( 'MA Vape Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'MA Vape Report' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('contact_filter_customer_id', __('contact.customer') . ':') !!}
                    {!! Form::select('contact_filter_customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}      
                </div>
            </div>
            <div class="col-md-4">
                <input type="hidden" id="date" name="date" value="">
                <div class="form-group">
                    {!! Form::label('transaction_date_filter', __('report.date_range') . ':') !!}
                    {!! Form::text('transaction_date_filter', 
                        @format_date('first day of this month') . ' ~ ' . @format_date('last day of this month'), 
                        ['placeholder' => __('lang_v1.select_a_date_range'), 
                        'class' => 'form-control', 'id' => 'transaction_date_filter', 'readonly']); 
                    !!}

                    {{-- {!! Form::label('transaction_date_filter', __('report.date_range') . ':') !!}
                    {!! Form::text('transaction_date_filter', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!} --}}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="transaction_report_table">
                        <thead>
                        <tr>
                            <td>Invoice Date</td>
                            <td>Invoice No</td>
                            <td>Tax Id</td>
                            <td>Name</td>
                            <td>Address</td>
                            <td>City</td>
                            <td>State</td>
                            <td>Zip</td>
                            <td>Payment Status</td>
                            <td>Qty</td>
                            <td>Price</td>
                            <td>Order Note</td>
                            <td>Tax Amount</td>
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
    <script type="text/javascript">
        $('#transaction_date_filter').daterangepicker({
            ranges: ranges,
            autoUpdateInput: true,
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            locale: {
                format: moment_date_format
            }
        });
        

        $(function () {
            item_inventory_report='';
            var date_filter = $('#transaction_date_filter').val();
            getInventoryItems(date_filter,'');
        });

        function getInventoryItems(date_filter,customer_id) {
            if(date_filter){
                var date_filter = date_filter;
            } else {
                var date_filter = '';
            }
            if(customer_id){
                var customer_id = customer_id;
            } else {
                var customer_id = '';
            }
            // alert(customer_id);
            item_inventory_report = $('#transaction_report_table').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,

                ajax: {
                    url: '{{ url("reports/ma-vape-report-list") }}',
                    type: "POST",
                    data: function (d) {
                        d.date_filter = date_filter;
                        d.customer_id = customer_id;
                    },
                },
                columns: [
                    { data: 'invoice_date', name: 'invoice_date'},
                    { data: 'invoice_no', name: 'invoice_no'},
                    { data: 'tax_id', name: 'tax_id'},
                    { data: 'name', name: 'name'},
                    { data: 'address', name: 'address'},
                    { data: 'city', name: 'city'},
                    { data: 'state', name: 'state'},
                    { data: 'zip_code', name: 'zip_code'},
                    { data: 'payment_status', name: 'payment_status'},
                    { data: 'item_qty', name: 'item_qty'},
                    { data: 'price', name: 'price'},
                    { data: 'order_note', name: 'order_note'},
                    { data: 'tax_amount', name: 'tax_amount'},
                ]
            });
        }

        // $('#transaction_date_filter').click(function () {
        //     alert('hii');
        //     // var selected_week = $('#week_list').val();
        //     // var selected_year = $('#year_list').val();
        //     // item_inventory_report.ajax.reload();

        //     // getInventoryItems(selected_week,selected_year);
        // });
        // $('#transaction_date_filter').on('apply.daterangepicker', (e, picker) => {
        //     var date_filter = $('#transaction_date_filter').val();
        //     alert(date_filter);
        // });
        $('#transaction_date_filter, #contact_filter_customer_id').change(function () {
            var date_filter = $('#transaction_date_filter').val();
            var customer_id = $('#contact_filter_customer_id').val();
            item_inventory_report.ajax.reload();
            getInventoryItems(date_filter,customer_id);
        });


    </script>
    {{-- <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
    var sales_tree_report;
    var start,end;    
    $(document).ready( function() {
        $('#transaction_date_filter').daterangepicker({
            ranges: ranges,
            autoUpdateInput: false,
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            locale: {
                format: moment_date_format
            }
        });
        $('#transaction_date_filter').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format(moment_date_format) + ' ~ ' + picker.endDate.format(moment_date_format));
                $("#date").val($(this).val());
            //  console.log($(this).val());
            // category_wise.ajax.reload();
            // category_wise.ajax.reload();
        }); 

        $('#transaction_date_filter').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            sales_tree_report.ajax.reload();
            sales_tree_report.ajax.reload();
        });
        
        //Sales-Tree Report (category-brand-product wise)
        sales_tree_report = $('table#transaction_report_table').DataTable({
            paging: false,
            processing: true,
            serverSide: true,
            //aaSorting: [[2, 'desc']],
            order: [],
            ajax: {
                url: '/reports/transaction-report',
                data: function(d) {
                    if($('#transaction_date_filter').val()) {
                        start = $('input#transaction_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        end = $('input#transaction_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
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
                    { data: 'invoice_date', name: 'invoice_date'  },
                    { data: 'invoice_no', "searchable": false},
                    { data: 'tax_id', "searchable": false},
                    { data: 'name',"searchable": false},
                    { data: 'address',"searchable": false},
                    { data: 'city',"searchable": false},
                    { data: 'state',"searchable": false},
                    { data: 'zip_code',"searchable": false},
                    { data: 'payment_status',"searchable": false},
                    { data: 'item_qty',"searchable": false},
                    { data: 'tax_amount',"searchable": false},
            ],
            fnDrawCallback: function(oSettings) {
                var total_sale = sum_table_col($('#transaction_report_table'), 'total_sale');
                $('#transaction_report_table .footer_sale_total').text(total_sale);
    
                var total_percent = sum_table_col($('#transaction_report_table'), 'total-sale-percent');
                var percentage_sale = total_percent +'%';
                $('#transaction_report_table .footer_sale_percent').text(percentage_sale);
    
                var total_sale = sum_table_col($('#transaction_report_table'), 'total_inventory');
                
                $('#transaction_report_table .footer_invent_total').text(total_sale);
    
                var total_percent = sum_table_col($('#transaction_report_table'), 'total-inventory-percent');
                var percentage_sale = total_percent +'%';
                $('#transaction_report_table .footer_invent_percent').text(percentage_sale);
                
                var total_diff = sum_table_col($('#transaction_report_table'), 'footer-total-diff') +'%';
                $('#transaction_report_table .footer_total_diff').text(total_diff);
                
                __currency_convert_recursively($('#transaction_report_table'));
            },
            createdRow: function(row, data, dataIndex) {
                
                $(row).find('td:eq(0)').attr('id', data.tra_id);
                // if (data.transaction_id!=null && data.transaction_id !=0) {
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

    $('#transaction_report_table').on('click','tr td.details-control', function(e) {
        
        if ($('#transaction_report_table').find('tbody > tr').hasClass("details"))
        {
            $('#transaction_report_table').find('.details').next('tr').empty();
            $('#transaction_report_table').find('tbody > tr').removeClass("details");
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
    </script> --}}
@endsection