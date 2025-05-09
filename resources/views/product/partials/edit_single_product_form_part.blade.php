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

<div class="col-sm-12"><br>
    <div class="table-responsive">
    <table class="table table-bordered add-product-price-table table-condensed {{$class}}">
        <tr>
          <th>@lang('product.default_purchase_price')</th>
          <th>@lang('product.default_selling_price')</th>
          <th>Tier-1 Price - Retail Store</th>
          <th>Tier-2 Price - Multi Store</th>
          <th>Tier-3 Price - Distributor</th>
          <th>Tier LI</th>
          <!-- <th>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</th> -->
          <th>@lang('product.stock')</th>
          <!-- <th>@lang('lang_v1.product_image')</th> -->
        </tr>
        @foreach($product_deatails->variations as $variation )
            @if($loop->first)
                <tr>
                    <td>
                        <input type="hidden" name="single_variation_id" value="{{$variation->id}}">

                        <!-- <div class="col-sm-6">
                          {!! Form::label('single_dpp', trans('product.exc_of_tax') . ':*') !!}

                          {!! Form::text('single_dpp', @num_format($variation->default_purchase_price), ['class' => 'form-control input-sm dpp input_number', 'placeholder' => __('product.exc_of_tax'), 'required']); !!}
                        </div> -->

                        <div class="col-sm-12">
                          {!! Form::label('single_dpp_inc_tax', trans('product.inc_of_tax') . ':*') !!}

                          {!! Form::text('single_dpp_inc_tax',!empty(round($variation->dpp_inc_tax)) ? @num_format($variation->dpp_inc_tax) : '' , ['class' => 'form-control input-sm dpp_inc_tax input_number', 'placeholder' => __('product.inc_of_tax'), 'required']); !!}
                          <span class="costerror"></span>
                        </div>

                    </td>
                    <td>
                        <label><span class="dsp_label"></span></label>
                        {!! Form::text('single_dsp', !empty(round($variation->default_sell_price)) ? @num_format($variation->default_sell_price) : '', ['class' => 'form-control input-sm dsp input_number', 'placeholder' => __('product.exc_of_tax'), 'id' => 'single_dsp', 'required']); !!}

                        {!! Form::text('single_dsp_inc_tax', @num_format($variation->sell_price_inc_tax), ['class' => 'form-control input-sm hide input_number', 'placeholder' => __('product.inc_of_tax'), 'id' => 'single_dsp_inc_tax', 'required']); !!}
                        <span class="error selling"></span>
                        <label>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</label>
                        @php
                        $profit_percent = 0;
                        if(!empty($variation->default_sell_price))
                        {
                          $cost = $variation->dpp_inc_tax;
                          $sell_price = $variation->default_sell_price;
                          if(isset($cost) && $cost != null && $sell_price > 0)
                          $profit_percent =(1 - ($cost / $sell_price))*100;
                        }
                        @endphp
                        {!! Form::text('profit_percent', @num_format($profit_percent), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent', 'required']); !!}

                        <div class="form-group">
                            <br>
                            <label>
                                {!! Form::checkbox('reset_last_prices', 1, false, ['class' => 'input-icheck', 'style'=>'height:15px;width:15px;', 'id' => 'reset_last_prices']); !!} <strong>Reset Prices</strong>
                            </label>
                        </div>
                    </td>
                    <td>
                        <label><span class="dsp_label"></span></label>
                        {!! Form::text('single_dsp_tier1', !empty($variation_tier_prices['single_dsp_tier1']) ? @num_format($variation_tier_prices['single_dsp_tier1']): '', ['class' => 'form-control input-sm dsp_tier1 input_number', 'placeholder' => 'Tier-1 Price', 'id' => 'single_dsp_tier1']); !!}
                        <span class="error tier1"></span>

                        <label>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</label>
                        @php
                        $profit_percent_tier1 = 0;
                        if(!empty($variation_tier_prices['single_dsp_tier1']))
                        {
                          $cost = $variation->dpp_inc_tax;
                          $sell_price = $variation_tier_prices['single_dsp_tier1'];
                          if(isset($cost) && $cost != null && $sell_price > 0)
                          $profit_percent_tier1 =(1 - ($cost / $sell_price))*100;
                        }
                        @endphp
                        {!! Form::text('profit_percent_tier1', @num_format($profit_percent_tier1), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent_tier1', 'required']); !!}

                        <div class="form-group">
                            <br>
                            <label>
                                {!! Form::checkbox('t1_last_prices', 1, false, ['class' => 'input-icheck', 'style'=>'height:15px;width:15px;', 'id' => 't1_last_prices']); !!} <strong>Reset Prices</strong>
                            </label>
                        </div>
                    </td>

                    <td>
                        <label><span class="dsp_label"></span></label>
                        {!! Form::text('single_dsp_tier2', !empty($variation_tier_prices['single_dsp_tier2']) ? @num_format($variation_tier_prices['single_dsp_tier2']): '', ['class' => 'form-control input-sm dsp_tier2 input_number', 'placeholder' => 'Tier-2 Price', 'id' => 'single_dsp_tier2']); !!}
                        <span class="error tier2"></span>
                        <label>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</label>
                        @php
                        $profit_percent_tier2 = 0;
                        if(!empty($variation_tier_prices['single_dsp_tier2']))
                        {
                          $cost = $variation->dpp_inc_tax;
                          $sell_price = $variation_tier_prices['single_dsp_tier2'];
                          if(isset($cost) && $cost != null && $sell_price > 0)
                          $profit_percent_tier2 =(1 - ($cost / $sell_price))*100;
                        }
                        @endphp
                        {!! Form::text('profit_percent_tier2', @num_format($profit_percent_tier2), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent_tier2', 'required']); !!}

                        <div class="form-group">
                            <br>
                            <label>
                                {!! Form::checkbox('t2_last_prices', 1, false, ['class' => 'input-icheck', 'style'=>'height:15px;width:15px;', 'id' => 't2_last_prices']); !!} <strong>Reset Prices</strong>
                            </label>
                        </div>
                    </td>

                    <td>
                        <label><span class="dsp_label"></span></label>
                        {!! Form::text('single_dsp_tier3', !empty($variation_tier_prices['single_dsp_tier3']) ? @num_format($variation_tier_prices['single_dsp_tier3']): '', ['class' => 'form-control input-sm dsp_tier3 input_number', 'placeholder' => 'Tier-3 Price', 'id' => 'single_dsp_tier3']); !!}
                        <span class="error tier3"></span>
                        <label>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</label>
                        @php
                        $profit_percent_tier3 = 0;
                        if(!empty($variation_tier_prices['single_dsp_tier3']))
                        {
                          $cost = $variation->dpp_inc_tax;
                          $sell_price = $variation_tier_prices['single_dsp_tier3'];
                          if(isset($cost) && $cost != null && $sell_price > 0)
                          $profit_percent_tier3 =(1 - ($cost / $sell_price))*100;
                        }
                        @endphp
                        {!! Form::text('profit_percent_tier3', @num_format($profit_percent_tier3), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent_tier3', 'required']); !!}

                        <div class="form-group">
                            <br>
                            <label>
                                {!! Form::checkbox('t3_last_prices', 1, false, ['class' => 'input-icheck', 'style'=>'height:15px;width:15px;', 'id' => 't3_last_prices']); !!} <strong>Reset Prices</strong>
                            </label>
                        </div>
                    </td>


                    <td>
                        <label><span class="dsp_label"></span></label>
                        {!! Form::text('single_dsp_tier4', !empty($variation_tier_prices['single_dsp_tier4']) ? @num_format($variation_tier_prices['single_dsp_tier4']): '', ['class' => 'form-control input-sm dsp_tier4 input_number', 'placeholder' => 'Tier-4 Price', 'id' => 'single_dsp_tier4']); !!}
                        <span class="error tier4"></span>
                        <label>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</label>
                        @php
                        $profit_percent_tier4 = 0;
                        if(!empty($variation_tier_prices['single_dsp_tier4']))
                        {
                          $cost = $variation->dpp_inc_tax;
                          $sell_price = $variation_tier_prices['single_dsp_tier4'];
                          if(isset($cost) && $cost != null && $sell_price > 0)
                          $profit_percent_tier4 =(1 - ($cost / $sell_price))*100;
                        }
                        @endphp
                        {!! Form::text('profit_percent_tier4', @num_format($profit_percent_tier4), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent_tier4', 'required']); !!}

                        <div class="form-group">
                            <br>
                            <label>
                                {!! Form::checkbox('t4_last_prices', 1, false, ['class' => 'input-icheck', 'style'=>'height:15px;width:15px;', 'id' => 't4_last_prices']); !!} <strong>Reset Prices</strong>
                            </label>
                        </div>
                    </td>


                    <!-- <td>
                      <br/>
                      @php
                        $cost = $variation->dpp_inc_tax;
                        $sell_price = $variation->default_sell_price;
                        if(isset($cost) && $cost != null && $sell_price > 0)
                        $profit_percent =(1 - ($cost / $sell_price))*100;
                      @endphp
                      {!! Form::text('profit_percent', @num_format($profit_percent), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent', 'required']); !!}
                  </td> -->

                     <td>
                        <label><span class="dsp_label"></span></label>
                      {!! Form::text('stock', @num_format(@$qty), ['class' => 'form-control input-sm input_number', 'placeholder' => __('product.stock') ,'id' => 'stock', 'required']); !!}
                    </td>
                    <td>
                        @php
                            $action = !empty($action) ? $action : '';
                        @endphp
                        <!-- @if($action !== 'duplicate')
                            @foreach($variation->media as $media)
                                <div class="img-thumbnail">
                                    <span class="badge bg-red delete-media" data-href="{{ action('ProductController@deleteMedia', ['media_id' => $media->id])}}"><i class="fa fa-close"></i></span>
                                    {!! $media->thumbnail() !!}
                                </div>
                            @endforeach
                        @endif -->
                        <!-- <div class="form-group">
                            {!! Form::label('variation_images', __('lang_v1.product_image') . ':') !!}
                            {!! Form::file('variation_images[]', ['class' => 'variation_images', 'accept' => 'image/*', 'multiple']); !!}
                            <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p></small>
                        </div> -->
                    </td>
                </tr>
            @endif
        @endforeach
    </table>
    </div>
</div>