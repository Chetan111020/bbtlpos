@extends('layouts.app')
@section('title', __('lang_v1.daily_items_report'))
@section('content')

    <!-- Content Header (Page header) -->
    <style>
        tbody, td {
            text-align: leftcenter;
        }
    </style>
    <section class="content-header">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                    <div class="col-md-3">
                        <div class="form-group">
                          <label>Select Date</label>
                          <input name="fromDate" readonly="" class="form-control" id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;" autocomplete="off">
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>
        <h1>{{ __('lang_v1.new_created_items')}}</h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="added_item_report">
                            <thead>
                            <tr>
                            <th>Id</th>
                            <th>Product Name</th>
                            <th>Item Code</th>
                            <th>Product SKU</th>
                            <th>Current Stock</th>
                            <th>Selling Price</th>
                            <th>Category</th>
                            <th>Sub Category</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
    <section class="content-header">
        <h1>{{ __('lang_v1.new_purchased_items')}}</h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="purchased_item_report">
                        <thead>
                        <tr>
                            <th>Id</th>
                            <th>Product Name</th>
                            <th>Item Code</th>
                            <th>Product SKU</th>
                            <th>Purchase Qty</th>
                            <th>Selling Price</th>
                            <th>Category</th>
                            <th>Sub Category</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->

@endsection

@section('javascript')

    <script type="text/javascript">

        var date = new Date();
        var today = new Date(date.getFullYear(), date.getMonth(), date.getDate());

        $('#reportrange').datepicker({
            format: 'dd/mm/yyyy',
            orientation: 'bottom',
            autoclose: true,
        });

        $('#reportrange').datepicker('setDate', today);

        //var count=0;
        $('#reportrange').datepicker().change(function() {
           // count++;
            //alert(count);
          //if(count==3) 
          //{
                var selected_date = $("input[name=fromDate]").val();
                if(selected_date!="")
                {
                    purchased_item_report='';
                    getPurchasedItems(selected_date);
                    added_item_report='';
                    getAddedItems(selected_date);
                }
            //count = 0;
          //}
        });

        $(function () {
            var selected_date = $("input[name=fromDate]").val();
            purchased_item_report='';
            getPurchasedItems(selected_date);
            added_item_report='';
            getAddedItems(selected_date);
        });

        function getPurchasedItems(selected_date) {

            purchased_item_report = $('#purchased_item_report').DataTable({
                destroy: true,
                processing: true,
                serverSide: false,

                ajax: {
                    url: '{{ url("reports/purchased-items-list") }}',
                    type: "GET",
                    data: function (dd) {
                        dd.select_date = selected_date;
                    }
                },
                columns: [
                    {data: 'id', id: 'p.id'},
                    {data: 'name', name: 'p.name'},
                    {data: 'item_code', name: 'p.item_code'},
                    {data: 'sku', name: 'v.sub_sku'},
                    {data: 'purchase_qty', name: 'purchase_lines.quantity'},
                    {data: 'selling_price', searchable: false},
                    {data: 'category', name: 'c1.name'},
                    {data: 'sub_category', name: 'c2.name'},
                ],
                fnDrawCallback: function (oSettings) {
                    __currency_convert_recursively($('#purchased_item_report'));
                },
                columnDefs: [
                  {
                    targets: 4,
                    render: $.fn.dataTable.render.number('', '.', 2, '')
                  }
                ]
            });
        }

        function getAddedItems(selected_date) {

            added_item_report = $('#added_item_report').DataTable({
                destroy: true,
                processing: true,
                serverSide: false,

                ajax: {
                    url: '{{ url("reports/added-items-list") }}',
                    type: "GET",
                    data: function (d) {
                        d.select_date = selected_date;
                    }
                },
                columns: [
                    {data: 'id', id: 'id'},
                    {data: 'name', name: 'products.name'},
                    {data: 'item_code', name: 'products.item_code'},
                    {data: 'sku', name: 'products.sku'},
                    {data: 'qty_available', name: 'vld.qty_available'},
                    {data: 'selling_price', searchable: false},
                    {data: 'category', name: 'c1.name'},
                    {data: 'sub_category', name: 'c2.name'},
                ],
                fnDrawCallback: function (oSettings) {
                    __currency_convert_recursively($('#added_item_report'));
                },
                columnDefs: [
                  {
                    targets: 4,
                    render: $.fn.dataTable.render.number('', '.', 2, '')
                  }
                ]
            });
        }


    </script>

@endsection