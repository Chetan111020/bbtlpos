<div class="col-md-3">
	<div class="form-group">
		@php
			$is_disabled = !empty($product->woocommerce_disable_sync) ? true : false;
      if(empty($product) && !empty($duplicate_product->woocommerce_disable_sync)){
        $is_disabled = true;
      }
		@endphp
      <br>
        <label>
        	<input type="hidden" name="woocommerce_disable_sync" value="0">
        	 @if(@$action == 'edit')
                <input type="checkbox" name="woocommerce_disable_sync" value="1" {{ ( $is_disabled) ? 'checked' : '' }}  class="input-icheck"  ><strong>@lang('woocommerce::lang.woocommerce_disable_sync')</strong>
             @elseif(@$action == 'add')
          	    {!! Form::checkbox('woocommerce_disable_sync', 1, $is_disabled, ['class' => 'input-icheck','checked']); !!} <strong>@lang('woocommerce::lang.woocommerce_disable_sync')</strong>
          	 @endif
        </label>
        @show_tooltip(__('woocommerce::lang.woocommerce_disable_sync_help'))
  	</div>
</div>