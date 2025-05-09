<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-body">
        @include('sale_pos.receipts.echeck_pdf', 
          ['receipts' => $receipts])
    </div>
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->