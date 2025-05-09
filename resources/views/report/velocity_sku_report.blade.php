@extends('layouts.app')
@section('title', __( 'SKU Performance Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>SKU Performance Report</h1>
</section>
@php
    $curr_category = Request::get('category') ?? 0;
    $curr_brand = Request::get('brand') ?? 0;
@endphp
<!-- Main content -->
<section class="content">
    <div class="col-md-12">
        <div class="row g-3">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('brand_id',  __('Select Brand') . ':') !!}
                    <select id="brand_id" class="form-control select2" name="brand" style="width:100%;">
                        <option value="all">All</option>
                        @foreach ($brands as $item)
                            <option value="{{ $item->id }}" {{($item->id == $curr_brand)? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('category_id',  __('Select Category') . ':') !!}
                    <select id="category_id" class="form-control select2" name="category" style="width:100%;">
                        <option value="all">All</option>
                        @foreach ($categories as $item)
                            <option value="{{ $item->id }}" {{($item->id == $curr_category)? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('category_id',  __('Date Range') . ':') !!} ( For Avg Sale Price)
                    <input type="text" id="dtrange" class="form-control" readonly />
                </div>
            </div>
            <div class="col-md-6" style="height:64px;display:flex;">
                <div class="form-group" style="margin:auto 3rem;" id="check_element">
                    <input type="checkbox" id="inactive_products" class="input-icheck"/>
                    <label for="inactive_products">Show Inactive Product</label>
                </div>
                <div class="form-group" style="margin:auto 0;" id="check_element">
                    <input type="checkbox" id="neg_qty" class="input-icheck"/>
                    <label for="neg_qty">Hide Negitive On Hand Quantity</label>
                </div>                
            </div>
        @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 shadow rounded table-responsive">
            <table class="table table-bordered table-striped bg-white" id="mytable">
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th style="width:35%;">Product Name</th>
                        <th>Quantity on Hand</th>
                        <th>Velocity (Weighted)</th>
                        <th>Days On Hand</th>
                        <th>Unit Purchase Price</th>
                        <th>Avg Sale Price</th>
                        <th>Avg Gross Profit</th>
                        <th>Gross Profit $</th>
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
        @php
        
            $date_now = new DateTime();
            $start_date = $date_now->modify('-90 day')->format('Y-m-d');
        @endphp
        dateRangeSettings.locale.format = "YYYY/MM/DD";
        // dateRangeSettings.startDate = new Date();
        // dateRangeSettings.endDate = new Date();
        
        dateRangeSettings.startDate = '{{ $start_date }}';
        dateRangeSettings.endDate = new Date();
        
        //Date range as a button
        $('#dtrange').daterangepicker(
            dateRangeSettings,
            // {
            //     startDate: new Date(),
            //     endDate: new Date(),
            //     locale:{
            //         format: "YYYY/MM/DD"
            //     }
            // },
            function (start, end) {
                $('#dtrange').val(start.format('YYYY/MM/DD') + ' - ' + end.format('YYYY/MM/DD'));
                dtable.ajax.reload();
            }
        );

        var url = "{{ route('reports.velocity.sku') }}";
        var dtable = $('#mytable').DataTable({
            processing: true,
            serverSide: true,
            "ajax": {
                "url": url,
                "data": function(d){
                    d.dates = $('#dtrange').val();
                    d.brand = $('#brand_id').val();
                    d.category = $('#category_id').val();
                    d.neg_qty = ($('#neg_qty').is(":checked")) ? 0 : 1;
                    d.inactive_products = ($('#inactive_products').is(":checked")) ? 0 : 1;
                }
            },
            columns:  [
                { data: 'item_code', name: 'item_code' },
                { data: 'name', name: 'name' },
                { data: 'on_hand', name: 'on_hand' , "sClass": "text-right"},
                { data: 'v_all_avg', name: 'v_all_avg' , "sClass": "text-right"},
                { data: 'days_on_hand', name: 'days_on_hand' , "sClass": "text-right"},
                { data: 'default_purchase_price', name: 'default_purchase_price' , "sClass": "text-right"},
                { data: 'avg_sale_price', name: 'avg_sale_price' , "sClass": "text-right"},
                { data: 'avg_gp', name: 'avg_gp' , "sClass": "text-right"},
                { data: 'gp_doller', name: 'gp_doller' , "sClass": "text-right"},
            ]
        });

        $('.select2').on('select2:select', function (e) {
            dtable.ajax.reload();
        });

        $(document).on('ifChanged', '#neg_qty,#inactive_products', function () {
            dtable.ajax.reload();
        });
    });
</script>
@endsection