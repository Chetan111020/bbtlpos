@extends('layouts.app')
@section('title', __( 'Price Update Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Price Update Report</h1>
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
                    {!! Form::label('category_id',  __('Date Range') . ':') !!}
                    <input type="text" id="dtrange" class="form-control" readonly />
                </div>
            </div>
            <div class="col-md-6" style="height:64px;display:flex;">
                <div class="form-group" style="margin:auto 3rem;" id="check_element">
                    <input type="checkbox" id="inactive_products" class="input-icheck"/>
                    <label for="inactive_products">Show Inactive Product</label>
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
                        <th style="width:35%;">Product Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Sale Price</th>
                        <th>Updated On</th>
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

        dateRangeSettings.startDate = new Date();
        dateRangeSettings.endDate = new Date();

        //Date range as a button
        $('#dtrange').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#dtrange').val(start.format('YYYY/MM/DD') + ' - ' + end.format('YYYY/MM/DD'));
                dtable.ajax.reload();
            }
        );

        var url = "{{ route('reports.price.update') }}";
        var dtable = $('#mytable').DataTable({
            processing: true,
            serverSide: true,
            "ajax": {
                "url": url,
                "data": function(d){
                    d.dates = $('#dtrange').val();
                    d.brand = $('#brand_id').val();
                    d.category = $('#category_id').val();
                    d.inactive_products = ($('#inactive_products').is(":checked")) ? 0 : 1;
                }
            },
            columns:  [
                { data: 'name', name: 'name' },
                { data: 'sku', name: 'sku' },
                { data: 'cat_name', name: 'cat_name'},
                { data: 'brand_name', name: 'brand_name'},
                { data: 'sell_price_inc_tax', name: 'sell_price_inc_tax' , "sClass": "text-right"},
                { data: 'price_updated_at', name: 'price_updated_at'},
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