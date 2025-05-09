@extends('layouts.app')
@section('title', __( 'Under Stocked Product Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Under Stocked Product Report</h1>
</section>
@php
    $curr_supplier = Request::get('supplier') ?? 0;
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
                    {!! Form::label('supplier_id',  __('Select Supplier') . ':') !!}
                    <select id="supplier_id" class="form-control select2" name="supplier" style="width:100%;">
                        <option value="all">All</option>
                        @foreach ($suppliers as $item)
                            <option value="{{ $item->id }}" {{($item->id == $curr_supplier)? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
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
            <div class="col-md-12" style="height:64px;display:flex;">
                <div class="form-group" style="margin:auto 0;" id="check_element">
                    <input type="checkbox" id="neg_qty" class="input-icheck"/>
                    <label for="neg_qty">Hide Negitive On Hand Quantity</label>
                </div>
                <div class="form-group" style="margin:auto 3rem;" id="check_element">
                    <input type="checkbox" id="new_pro" class="input-icheck"/>
                    <label for="new_pro">Hide New Products</label>
                </div>
            </div>
        @endcomponent
        </div>
    </div>
    
    <div class="row" id="convert_div">
        <div class="col-md-12">
            <form action="{{ route('po.auto.create.post') }}" method="POST" style="margin: 20px 0;">
                @csrf
                <input type="hidden" name="products" value="" id="products" />
                <button class="btn btn-primary">Convert to Purchase Order</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 shadow rounded table-responsive">
            <table class="table table-bordered table-striped bg-white" id="mytable">
                <thead>
                    <tr>
                        <th style="width:35%">Product Name</th>
                        {{-- <th>Supplier ID</th> --}}
                        <th>Quantity on Hand</th>
                        <th>Safety Stock<br/><small>(3 Month Avg QTY)</small></th>
                        <th>Buffer Quantity<br/><small>(15 Days Buffer QTY)</small></th>
                        <th>Quantity to Buy</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @foreach ($safetyStockData as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->supplier_id }}</td>
                            <td>{{ $item->qty_available }}</td>
                            <td>{{ $item->safety }}</td>
                            <td>{{ $item->buffer }}</td>
                            <td>{{ $item->to_buy }}</td>
                        </tr>
                    @endforeach --}}
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
        loadData();
    });

    $('.select2').on('select2:select', function (e) {
        loadData();
    });

    $(document).on('ifChanged', '#neg_qty,#new_pro', function () {
        loadData();
    });

    // $(document).on('ifChanged', '#new_pro', function () {
    //     loadData();
    // });

    function loadData(){
        var supplier_id = $('#supplier_id').val();
        var brand_id = $('#brand_id').val();
        var category_id = $('#category_id').val();
        var neg_qty = ($('#neg_qty').is(":checked")) ? neg_qty = 0 : neg_qty = 1;
        var new_pro = ($('#new_pro').is(":checked")) ? new_pro = 0 : new_pro = 1;

        var url = "{{ route('reports.understock') }}";
        var data = "supplier="+supplier_id+"&brand="+brand_id+"&category="+category_id+"&neg_qty="+neg_qty+"&new_pro="+new_pro;

        $.ajax({
            'url': url,
            'method': "GET",
            'data' : data,
            'contentType': 'application/json'
        }).done( function(data) {
            $('#convert_div').hide();
            $('#products').val("");
            $('#mytable').DataTable().clear().destroy();

            if(data[1] == 0){
                $('#products').val(data[2]);
                $('#convert_div').show();
            }

            $('#mytable').DataTable( {
                "aaData": data[0],
                "columns": [
                    { "data": "name" },
                    // { "data": "supplier_id" },
                    { "data": "qty_available" },
                    { "data": "safety" },
                    { "data": "buffer" },
                    { "data": "to_buy" }
                ]
            })
        });
    }
</script>
@endsection