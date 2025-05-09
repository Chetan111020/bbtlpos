@if(!session('business.enable_price_tax'))
  @php
    $default = 0;
    $class = 'hide';
  @endphp
@else
  @php
    $default = null;
    $class = '';
  @endphp
@endif

<div class="table-responsive">
    <table class="table table-bordered add-product-price-table table-condensed {{$class}}">
        <tr>
          <th>@lang('product.default_purchase_price')</th>
            <th>@lang('product.default_selling_price')</th>
            <th>Tier-1 Price - Retail Store</th>
            <th>Tier-2 Price - Multi Store</th>
            <th>Tier-3 Price - Distributor</th>
            <th>Tier LI </th>
            <!-- <th>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</th> -->
            <th>On Hand</th>
          @if(empty($quick_add))
            <!-- <th>@lang('lang_v1.product_image')</th> -->
          @endif
        </tr>
        <tr>
          <td>
            <!-- <div class="col-sm-6">
              {!! Form::label('single_dpp', trans('product.exc_of_tax') . ':*') !!}

              {!! Form::text('single_dpp', $default, ['class' => 'form-control input-sm dpp input_number', 'placeholder' => __('product.exc_of_tax'), 'required']); !!}
            </div> -->

            <div class="col-sm-12">
              {!! Form::label('single_dpp_inc_tax', trans('product.inc_of_tax') . ':*') !!}

              {!! Form::text('single_dpp_inc_tax', $default, ['class' => 'form-control input-sm dpp_inc_tax input_number', 'id' => 'single_dpp_inc_tax', 'placeholder' => __('product.inc_of_tax'), 'required']); !!}
              <span class="costerror"></span>
            </div>

          </td>

            <td>
                <label></label>
                {!! Form::text('single_dsp',  $default, ['class' => 'form-control input-sm dsp input_number', 'placeholder' => __('product.exc_of_tax'), 'id' => 'single_dsp', 'required']); !!}

                {{-- {!! Form::text('single_dsp_inc_tax', $default, ['class' => 'form-control input-sm hide input_number', 'placeholder' => __('product.inc_of_tax'), 'id' => 'single_dsp_inc_tax', 'required']); !!} --}}
                <span class="error selling"></span>
                <label>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</label>
                {!! Form::text('profit_percent', @num_format($profit_percent), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent', 'required']); !!}
            </td>

            <td>
                <label></label>
                {!! Form::text('single_dsp_tier1',  $default, ['class' => 'form-control input-sm dsp_tier1 input_number', 'placeholder' => 'Tier-1 Price', 'id' => 'single_dsp_tier1', 'required']); !!}
                <span class="error tier1"></span>
                <label>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</label>
                {!! Form::text('profit_percent_tier1', @num_format($profit_percent), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent_tier1', 'required']); !!}
            </td>

            <td>
                <label></label>
                {!! Form::text('single_dsp_tier2',  $default, ['class' => 'form-control input-sm dsp_tier2 input_number', 'placeholder' => 'Tier-2 Price', 'id' => 'single_dsp_tier2', 'required']); !!}
                <span class="error tier2"></span>
                <label>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</label>
                {!! Form::text('profit_percent_tier2', @num_format($profit_percent), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent_tier2', 'required']); !!}
            </td>

            <td>
                <label></label>
                {!! Form::text('single_dsp_tier3',  $default, ['class' => 'form-control input-sm dsp_tier3 input_number', 'placeholder' => 'Tier-3 Price', 'id' => 'single_dsp_tier3', 'required']); !!}
                <span class="error tier3"></span>
                <label>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</label>
                {!! Form::text('profit_percent__tier3', @num_format($profit_percent), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent_tier3', 'required']); !!}
            </td>

           <td>
                <label></label>
                {!! Form::text('single_dsp_tier4',  $default, ['class' => 'form-control input-sm dsp_tier4 input_number', 'placeholder' => 'Tier LI Price', 'id' => 'single_dsp_tier4', 'required']); !!}
                <span class="error tier4"></span>
                <label>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</label>
                {!! Form::text('profit_percent__tier4', @num_format($profit_percent), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent_tier4', 'required']); !!}
            </td>


 
          <td><label></label>
            {!! Form::text('stock',  $default, ['class' => 'form-control input-sm dsp input_number', 'placeholder' => __('product.stock'), 'id' => '', 'required']); !!}</td>
          @if(empty($quick_add))
          <!-- <td>
              <div class="form-group">
                {!! Form::label('variation_images', __('lang_v1.product_image') . ':') !!}
                {!! Form::file('variation_images[]', ['class' => 'variation_images', 'accept' => 'image/*', 'multiple']); !!}
                <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p></small>
              </div>
          </td> -->
          @endif
        </tr>
    </table>
</div>