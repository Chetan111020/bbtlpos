@extends('layouts.app')
@section('title', 'E-check Generator')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>E-check Generator</h1>
</section>

<!-- Main content -->
<section class="content">
  {!! Form::open(['url' => action('SellPosController@eCheckGeneratePdf'), 'method' => 'post', 'id' => 'echecks_form' ]) !!}
    <div class="row">
            <div class="form-group col-md-12">
                <span style="font-weight: bold;">Your Info</span>
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="name" id="name" class="form-control" placeholder="Enter Your Name*" required />
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="address1" id="address1" class="form-control" placeholder="Enter Your Address*" required />
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="address2" id="address2" class="form-control" placeholder="Enter Your Address.." />
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="address3" id="address3" class="form-control" placeholder="Enter Your Address.." />
            </div>
            <div class="form-group col-md-12">
              <span style="font-weight: bold;">Bank Info</span>
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="bname" id="bname" class="form-control" placeholder="Bank Name*" required />
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="baddress1" id="baddress1" class="form-control" placeholder="Bank Address*" required/>
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="baddress2" id="baddress3" class="form-control" placeholder="Bank Address.." />
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="baddress1" id="baddress1" class="form-control" placeholder="Bank Address.." />
            </div>
            <!-- <div class="form-group col-md-12">
              <span style="font-weight: bold;">Payee Info</span>
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="pname" id="pname" class="form-control" placeholder="Payee Name" />
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="paddress1" id="paddress1" class="form-control" placeholder="Payee Address" />
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="paddress2" id="paddress2" class="form-control" placeholder="Payee Address.." />
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="paddress3" id="paddress3" class="form-control" placeholder="Payee Address.." />
            </div> -->
            <div class="form-group col-md-12">
              <span style="font-weight: bold;">Other Info</span>
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="edate" id="edate" class="form-control" readonly placeholder="Enter Date" value=" <?= date("m/d/Y") ?>"/>
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="rname" id="rname" class="form-control" placeholder="Enter Receiptn's name*" required />
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="amount" id="amount" class="form-control" placeholder="Enter amount*" required/>
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="anumber" id="anumber" class="form-control" placeholder="Account Number*" required/>
            </div>
            <div class="form-group col-md-12">
              <input  type="text" name="memo" id="memo" class="form-control" placeholder="Memo" />
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="cno" id="cno" class="form-control" placeholder="Check No" />
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="tcode" id="tcode" class="form-control" placeholder="Transit Code" />
            </div>
            <div class="form-group col-md-3">
              <input  type="text" name="rno" id="rno" class="form-control" placeholder="Routing Number" />
            </div>
            <div class="form-group col-md-3" id="gen_down">
                <span style="float:left;margin: 2px;">
                  <input type="submit" class="btn btn-sm btn-primary" id="generate" value="Generate E-check">
                </span>
                <span style="float:left;margin: 2px;">
                  <button type="button" class="btn btn-sm btn-primary" id="eCheckGeneratePdf"> Preview E-check</button>
                </span>
            </div>
            <div id="error_mssage" style="color: red;margin-left:22px;">
              @if (\Session::has('success'))
                <div class="alert alert-success">
                    <ul>
                        <li>{!! \Session::get('success') !!}</li>
                    </ul>
                </div>
              @endif
            </div>
    </div>  
  {!! Form::close() !!}
</section>
<!-- /.content -->
@endsection
@section('javascript')
    <script type="text/javascript">
      $( "#edate" ).datepicker({
      todayHighlight: true,
      autoclose: true,
      orientation:"bottom"
      });
      $(document).on('keyup','#amount', function(){
        this.value = this.value.replace(/[^0-9\.]/g,'');
        if(this.value==0)
        {
            $(this).val("");
        }
      });
      $(document).on('keyup','#anumber', function(){
        this.value = this.value.replace(/[^0-9]/g,'');
      });
      $(document).on('keyup','#cno', function(){
        this.value = this.value.replace(/[^0-9]/g,'');
      });
      $(document).on('keyup','#tcode', function(){
        this.value = this.value.replace(/[^0-9]/g,'');
      });
      $(document).on('keyup','#rno', function(){
        this.value = this.value.replace(/[^0-9]/g,'');
      });
      $(document).on('click', '#eCheckGeneratePdf', function () {
          var url = "{{action('SellPosController@eCheckGeneratePdf')}}";
          var flag = "false";
          $(":input[required]").each(function () {
              if ($.trim($(this).val()) != "")
              {                
                  flag = "true";          
              }
              else
              {
                flag = "false"; 
              }
          });
          if(flag == "true")
          {
            $.ajax({
                url: url+'?display=preview',
                data: $('#echecks_form').serialize(),
                method: 'POST',
                success: function (result) {
                    $('.view_modal')
                        .html(result)
                        .modal('show');
                },
            });
          }
          else
          {
              toastr.error("Please enter required fields!!");
          }
        })
    </script>
@endsection