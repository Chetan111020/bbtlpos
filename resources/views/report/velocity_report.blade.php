@extends('layouts.app')
@section('title', __( 'Velocity Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Velocity Report</h1>
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
                        <th>90 Day Velocity</th>
                        <th>60 Day Velocity</th>
                        <th>30 Day Velocity</th>
                        <th>Velocity (Weighted)</th>
                        <th>Days On Hand</th>
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
        var url = "{{ route('reports.velocity') }}";
        var dtable = $('#mytable').DataTable({
            processing: true,
            serverSide: true,
            "ajax": {
                "url": url,
                "data": function(d){
                    d.brand = $('#brand_id').val();
                    d.category = $('#category_id').val();
                    d.neg_qty = ($('#neg_qty').is(":checked")) ? 0 : 1;
                    d.inactive_products = ($('#inactive_products').is(":checked")) ? 0 : 1;
                }
            },
            columns: [
                { data: 'item_code', name: 'item_code' },
                { data: 'name', name: 'name' },
                { data: 'on_hand', name: 'on_hand' , "sClass": "text-right"},
                { data: 'v1_avg', name: 'v1_avg' , "sClass": "text-right"},
                { data: 'v2_avg', name: 'v2_avg' , "sClass": "text-right"},
                { data: 'v3_avg', name: 'v3_avg' , "sClass": "text-right"},
                { data: 'v_all_avg', name: 'v_all_avg' , "sClass": "text-right"},
                { data: 'days_on_hand', name: 'u.days_on_hand' , "sClass": "text-right"}
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