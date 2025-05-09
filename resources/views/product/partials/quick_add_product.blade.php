<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
    
    <div class="modal-header">
        <button type="button" class="btn btn-default" data-dismiss="modal" style="float: right; font-size: 21px; font-weight: 700; line-height: 1; color: #000; text-shadow: 0 1px 0 #fff; opacity: .2;"><span aria-hidden="true">&times;</span></button>
	      <h4 class="modal-title" id="modalTitle">@lang( 'product.add_new_product' )</h4>
    </div>
    <div class="modal-body">
         @include('product.create_product_model')        
        
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<style type="text/css">
  .modal-lg {
      width: 98% !important;
  }
  .modal-body {
      padding: 0px 15px  !important;
  }
</style>
<script src="{{ asset('js/app.js?v=' . $asset_v) }}"></script>
<script type="text/javascript">
  $(function(){
        // $('form#product_add_form').removeAttr('action');
        $('form#product_add_form').removeAttr('method');
    });

    
    $(document).ready(function () {
      $("form#product_add_form").off('submit').submit(function (event) {
        var formValues= $(this).serialize();
        var form = jQuery(event.target);
        console.log('form:', formValues)
        console.log('action:', form.attr("action"))
        console.log('action store:', "{{ route('products.store') }}")
        $.ajax({
          type: "POST",
          url: "{{ route('products.store') }}",
          data: formValues,
          dataType: "json",
          encode: true,
        }).done(function (data) {
          console.log('success', data);
            if(data.success == 1)
            { 
                $(".quick_add_product_modal").modal('hide');
                
                $("#successnew").html('<div id="successnew" class="alert alert-success show" style="color: #155724 !important; background-color: #d4edda !important; border-color: #c3e6cb !important;"> <button type="button" class="close" data-dismiss="alert">&times;</button> <strong>Success!</strong> {{ __('product.product_added_success') }}.</div>');
                $("#successnew").fadeOut(5000);
            }  
        })
        .fail(function (data) {
            console.log('error', data)
          $("form").html(
            '<div class="alert alert-danger">Could not reach server, please try again later.</div>'
          );
        });

        event.preventDefault();
      });
    });
</script>