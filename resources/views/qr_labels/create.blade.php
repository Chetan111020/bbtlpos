@extends('layouts.app')
@section('title', __('barcode.print_labels'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <br>
    <h1>QR Labels</h1>
</section>

<!-- Main content -->
<section class="content no-print">
	{!! Form::open(['url' => route('qr.generate'), 'method' => 'post']) !!}

	@component('components.widget', ['class' => 'box-primary', 'title' => __( 'barcode.info_in_labels' )])
		<div class="row">

			<div class="col-sm-6">
				<h3>
                    <input type="checkbox" style="height:15px;width:15px;" value="1" name="show_aisle" checked />
                    Aisle
                </h3>
			</div>
			<div class="col-sm-6">
				<h3>
                    <input type="checkbox" style="height:15px;width:15px;" value="1" name="show_rack" checked />
                    Rack
                </h3>
			</div>

            <div class="col-sm-2">
				<div class="form-group">
					<label>Start From</label>
                    <input type="number" class="form-control" value="1" name="aisle_start_from" />
				</div>
			</div>

            <div class="col-sm-2">
				<div class="form-group">
					<label>Count</label>
                    <input type="number" class="form-control" value="5" name="aisle_count" />
				</div>
			</div>

            <div class="col-sm-2">
				<div class="form-group">
					<label>Increament</label>
                    <input type="number" class="form-control" value="1" name="aisle_inc" />
				</div>
			</div>

            <div class="col-sm-2">
				<div class="form-group">
					<label>Start From</label>
                    <input type="number" class="form-control" value="1" name="rack_start_from" />
				</div>
			</div>

            <div class="col-sm-2">
				<div class="form-group">
					<label>Count</label>
                    <input type="number" class="form-control" value="5" name="rack_count" />
				</div>
			</div>

            <div class="col-sm-2">
				<div class="form-group">
					<label>Increament</label>
                    <input type="number" class="form-control" value="1" name="rack_inc" />
				</div>
			</div>

			<div class="col-sm-6">
				<h3>
                    <input type="checkbox" style="height:15px;width:15px;" value="1" name="show_shelf" checked />
                    Shelf
                </h3>
			</div>
			<div class="col-sm-6">
				<h3>
                    <input type="checkbox" style="height:15px;width:15px;" value="1" name="show_bin" checked />
                    Bin
                </h3>
			</div>

            <div class="col-sm-2">
				<div class="form-group">
					<label>Start From</label>
                    <input type="number" class="form-control" value="1" name="shelf_start_from" />
				</div>
			</div>

            <div class="col-sm-2">
				<div class="form-group">
					<label>Count</label>
                    <input type="number" class="form-control" value="5" name="shelf_count" />
				</div>
			</div>

            <div class="col-sm-2">
				<div class="form-group">
					<label>Increament</label>
                    <input type="number" class="form-control" value="1" name="shelf_inc" />
				</div>
			</div>

            <div class="col-sm-2">
				<div class="form-group">
					<label>Start From</label>
                    <input type="number" class="form-control" value="1" name="bin_start_from" />
				</div>
			</div>

            <div class="col-sm-2">
				<div class="form-group">
					<label>Count</label>
                    <input type="number" class="form-control" value="5" name="bin_count" />
				</div>
			</div>

            <div class="col-sm-2">
				<div class="form-group">
					<label>Increament</label>
                    <input type="number" class="form-control" value="1" name="bin_inc" />
				</div>
			</div>
			<div class="col-sm-12">
				<hr/>
			</div>


            <div class="col-sm-2">
				<div class="form-group">
					<label>Label Height (In)</label>
                    <input type="number" class="form-control" value="1" name="label_h" required/>
				</div>
			</div>

            <div class="col-sm-2">
				<div class="form-group">
					<label>Label Width (In)</label>
                    <input type="number" class="form-control" value="4" name="label_w" required/>
				</div>
			</div>

			<div class="col-sm-12" style="height: 40px;"></div>

			<div class="col-sm-2 col-sm-offset-5">
				<button type="submit" id="labels_preview" class="btn btn-primary pull-right btn-flat btn-block">@lang( 'barcode.preview' )</button>
			</div>
		</div>
	@endcomponent
	{!! Form::close() !!}

	<div class="col-sm-8 hide display_label_div">
		<h3 class="box-title">@lang( 'barcode.preview' )</h3>
		<button type="button" class="col-sm-offset-2 btn btn-success btn-block" id="print_label">Print</button>
	</div>
	<div class="clearfix"></div>
</section>

<!-- Preview section-->
<div id="preview_box">
</div>

@stop
@section('javascript')
	{{-- <script src="{{ asset('js/labels.js?v=' . $asset_v) }}"></script> --}}
@endsection
