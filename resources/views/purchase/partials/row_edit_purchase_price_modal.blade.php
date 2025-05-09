<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title pull-left" id="myModalLabel">{{ $product->name }} - {{ $variation->sub_sku }}</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="stock_history">
                    <l-squircle
                    size="37"
                    stroke="5"
                    stroke-length="0.15"
                    bg-opacity="0.1"
                    speed="0.9"
                    color="black"
                    ></l-squircle>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>
