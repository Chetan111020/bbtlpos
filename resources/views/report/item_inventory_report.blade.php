@extends('layouts.app')
<title>Item Inventory Report </title>
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Item Inventory</h1>
    </section>
    <style>
        tbody, td {
            text-align: leftcenter;
        }
    </style>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('year_list',  __('Year') . ':') !!}
                            {!! Form::select('year_list', $years, date('Y'), ['class' => 'form-control select2','id'=>'year_list', 'style' => 'width:100%']); !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('week_list',  __('Week') . ':') !!}
                            {!! Form::select('week_list', $weeks,'week_'.date('W'), ['class' => 'form-control select2','id'=>'week_list', 'style' => 'width:100%']); !!}
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="item_inventory_report">
                            <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Product</th>
                                <th>MNP(Barcode)</th>
                                <th>Quantity On Hand</th>
                                <th>MSA Category</th>
                                <th>Items Per Selling Unit</th>
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


        $(function () {
            item_inventory_report='';
            var selected_week = $('#week_list').val();
            var selected_year = $('#year_list').val();
            getInventoryItems(selected_week,selected_year);
        });

        function getInventoryItems(selected_week,selected_year) {

            item_inventory_report = $('#item_inventory_report').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,

                ajax: {
                    url: '{{ url("reports/item-inventory-list") }}',
                    type: "POST",
                    data: function (d) {
                        d.year = selected_year;
                        d.week = selected_week;

                    },
                },
                columns: [
                    {data: 'item_code', name: 'item_code'},
                    {data: 'name', name: 'name'},
                    {data: 'sku', name: 'sku'},
                    {data: 'on_hand_item', name: 'on_hand_item'},
                    {data: 'msa_category', name: 'msa_category'},
                    {data: 'qty_box', name: 'qty_box'},

                ]
            });
        }


        $('#year_list, #week_list').change(function () {
            var selected_week = $('#week_list').val();
            var selected_year = $('#year_list').val();
            item_inventory_report.ajax.reload();

            getInventoryItems(selected_week,selected_year);
        });


    </script>

@endsection