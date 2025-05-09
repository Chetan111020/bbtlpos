<!-- Edit Order tax Modal -->
<div class="modal-dialog" role="document">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">@lang('lang_v1.view_invoice_url') - @lang('sale.invoice_no'): {{$transaction->invoice_no}}</h4>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<input type="text" class="form-control" value="{{$sms_content}}" id="invoice_url">
				<p class="help-block">@lang('lang_v1.invoice_url_help')</p>
			</div>
		</div>
		<div class="modal-footer">

            @if (!empty($sms_number))
                <button type="button" data-contact-no="{{ $sms_number }}" data-msg-content="{{ $transaction->id }}" class="btn btn-success send-sms-btn" style="background-color: #00a65a;border-color: #008d4c;">
                    Share via SMS
                </button>
            @endif

            @if (!empty($wa_link))
                {{-- <a href="{{$wa_link}}" target="_blank" class="btn btn-success" style="background-color: #00a65a;border-color: #008d4c;">
                    Share on WhatsApp
                </a> --}}

                <button type="button" data-contact-no="{{ $wa_number ?? '' }}" data-msg-content="{{ $short_msg ?? '' }}" class="btn btn-success send-wa-btn" style="display:none;background-color: #00a65a;border-color: #008d4c;">
                    Share on WhatsApp
                </button>
            @endif

		    <button type="button" class="btn btn-default" data-dismiss="modal">
		    	@lang('messages.close')
		    </button>

		    <a href="{{$url}}" id="view_invoice_url" target="_blank" rel="noopener" class="btn btn-primary">
				@lang('messages.view')
			</a>
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
	$('input#invoice_url').click(function(){
		$(this).select().focus();
	});
</script>