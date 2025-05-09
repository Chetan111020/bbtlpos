@extends('layouts.app')
@section('title', __('Jadoo Form'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>@lang('Jadoo Form')</h1>
        
    </section>

    <!-- Main content -->
    <section class="content no-print">        
        <div class="box box-solid">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="jadoo_name">Select Product Name:</label>
                            {!! Form::select('jadoo_name',$jadoo_products,null, ['id' =>'jadoo_name','class' => 'form-control select2','placeholder' => 'Please Select']); !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="name">Actual Product Name:</label>
                            <input class="form-control" name="a_name" type="text" id="display_name">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="name">Jadoo Product Name:</label>
                            <input class="form-control" name="r_name" type="text" id="r_name">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="name">Jadoo Barcode:</label>
                            <input class="form-control" name="barcode" type="text" id="barcode">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="name">Jadoo Item Code:</label>
                            <input class="form-control" name="itemcode" type="text" id="itemcode">
                        </div>
                    </div>
                </div>
            </div>
            <div id="error_mssage" style="color: red;margin-left:22px;">
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12">
                        <a href="/jadoo-products" class="btn btn-primary pull-right">@lang('Reset')</a>
                        <button id="save_jadoo_product" style="margin-right: 5px;" class="btn btn-primary pull-right">@lang('Save')</button>
                    </div>
                </div>
            </div>
            <!-- @if (\Session::has('success'))
                <div class="alert alert-success" style="margin:10px;">
                    {!! \Session::get('success') !!}
                </div>
            @endif -->
            <div class="box-body">
                {!! Form::open(['url' => action('ImportProductsController@importJadooProducts'), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                <div class="row">
                    <div class="col-sm-2">
                            
                            <div class="form-group">
                                {!! Form::label('name', __( 'product.file_to_import' ) . ':') !!}
                                {!! Form::file('jadoo_products_file', ['required' => 'required']); !!}
                            </div>
                    </div>
                    <div class="col-sm-10">
                            <div class="form-group" style="margin:15px;">
                                <button type="submit" class="btn btn-primary">@lang('messages.submit')</button>
                                <a href="{{ asset('files/import_jadoo_products_csv_template.xls') }}" class="btn btn-success" download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file')</a>
                            </div> 
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
        <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="jadoo_report_table">
                        <thead>
                        <tr>
                            <td>#</td>
                            <td>Product Name</td>
                            <td>Jadoo Name</td>
                            <td>Barcode</td>
                            <td>Item Code</td>
                            <td>Status</td>

                        </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
    </section>
@stop
@section('javascript')
<script type="text/javascript">

$( "#save_jadoo_product" ).click(function() {
  
  $('#error_mssage').html('');
  var a_name = $("input[name=a_name]").val();
  var name = $("input[name=r_name]").val();
  var jadoo_name = $("#jadoo_name").val();
  var barcode = $("#barcode").val();
  var itemcode = $("#itemcode").val();
  
  if(jadoo_name=="")
  {
   $('#error_mssage').html("Please select Product Name!!");
  }
  else if(a_name=="")
  {
    $('#error_mssage').html("Please enter Actual Name!!");
  }
  else if(name=="")
  {
    $('#error_mssage').html("Please enter Jadoo Product Name!!");
  }
  else
  {
    $("#save_jadoo_product").prop("disabled","disabled");
    doSaveName(jadoo_name,a_name,name,barcode,itemcode,"{{ route('jadoocreatesave') }}");
  }
});

$(document).on('change', '#jadoo_name', function(){
    $('#error_mssage').html("");
    //$('#r_name').val("");
    //$('#display_name').val("");
    let jadoo_id = $(this).val();
    if(jadoo_id!="")
    {
        $.ajax({
            type: "GET",
            url: "/jadoo-product-detail",
            data: {
                jadoo_id: jadoo_id,
            },
            success: function (data) {
                if(data.name)
                {
                    $('#display_name').val(data.name);
                    $('#r_name').val(data.jadoo_name);
                    $('#barcode').val(data.barcode);
                    $('#itemcode').val(data.itemcode);
                }
                else
                {
                    $('#error_mssage').html("Sorry!! No Product Found.");
                }
            }
        });
    }
});

function doSaveName(jadoo_product_id,a_name,name,barcode,itemcode,action){
    $.ajax({
           url:action,
           method:'GET',
           data:{
             jadoo_product_id:jadoo_product_id,
             a_name:a_name,
             name:name,
             barcode:barcode,
             itemcode:itemcode,
             _token : '{{ csrf_token() }}'
            },
           cache : false,
           dataType:'json',
           success:function(data)
           {
                if(data.result=='success'){
                    toastr.success(data.msg);
                    window.location = "/jadoo-products";
                }
                if(data.result=='error'){
                    toastr.error(data.msg);
                    $("input[name=a_name]").val('');
                    $("input[name=r_name]").val('');   
                    $("input[name=barcode]").val('');
                    $("input[name=itemcode]").val('');
                    $('#save_jadoo_product').prop('disabled', false);
                }
                
            },
           complete:function(data){
            // Hide image container
           }
        });
}

$(function () {
            jadoo_items='';
            getJadooItems("");

        });
function getJadooItems(date_filter) {
            if(date_filter){
                var date_filter = date_filter;
            } else {
                var date_filter = '';
            }
            jadoo_items = $('#jadoo_report_table').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url("jadoo-products/list") }}',
                    type: "POST",
                    data: function (d) {
                        d.date_filter = date_filter;
                    },
                },
                columns: [
                    { data: 'loop', name: 'loop', searchable: false},
                    { data: 'name', name: 'name'},
                    { data: 'jadoo_name', name: 'jadoo_name'},
                    { data: 'barcode', name: 'barcode'},
                    { data: 'itemcode', name: 'itemcode'},
                    { data: 'status', name: 'status'},

                ]
            });
        }
</script>
@endsection