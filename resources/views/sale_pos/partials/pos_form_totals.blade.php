<div class="row">
    <div class="col-md-12 pos-form-totals">
        <table class="table table-condensed">
            <tr>
                <td>
                    <small><b>@lang('sale.item'):</b></small>&nbsp;
                    <small><span class="total_quantity">0</span></small>
                </td>
                <td>
                    <b>SubTotal:</b> &nbsp;
                    <span class="price_subtotal">0</span>
                </td>
                <td>
                    <b>Total Tax:</b> &nbsp;
                    <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="" data-html="true" data-trigger="hover" data-original-title="" title="" aria-describedby="popover906809" id="total_tax"></i>
                    <span class="tax_total">0</span>
                    <input type="hidden" name="tax_total_amt" id="tax_total_amt" data-default="0">
                </td>
                <td>
                    <small><b>@lang('sale.total'):</b> &nbsp</small>
                    <small><span class="price_total">0</span></small>
                </td>
            </tr>
            <tr>
                <td style="width: 30%">
                    <small>
                        <span>
                            <b>@lang('sale.shipping')(+): @show_tooltip(__('tooltip.shipping'))</b>
                            <i class="fas fa-edit cursor-pointer" title="@lang('sale.shipping')" aria-hidden="true"
                               data-toggle="modal" data-target="#posShippingModal"></i>
                            <span id="shipping_charges_amount">0</span>
                            <input type="hidden" name="shipping_details" id="shipping_details"
                                   value="@if(empty($edit)){{''}}@else{{$transaction->shipping_details}}@endif"
                                   data-default="">

                            <input type="hidden" name="shipping_address" id="shipping_address"
                                   value="@if(empty($edit)){{''}}@else{{$transaction->shipping_address}}@endif">

                            <input type="hidden" name="shipping_status" id="shipping_status"
                                   value="@if(empty($edit)){{''}}@else{{$transaction->shipping_status}}@endif">

                            <input type="hidden" name="delivered_to" id="delivered_to"
                                   value="@if(empty($edit)){{''}}@else{{$transaction->delivered_to}}@endif">

                            <input type="hidden" name="shipping_charges" id="shipping_charges"
                               value="@if(empty($edit)){{@num_format(0.00)}} @else{{@num_format($transaction->shipping_charges)}} @endif"
                               data-default="0.00">
                        </span>
                    </small>
                    <br/>
                    <br/>
                    @if(!empty($edit))
                    <small>
                        <b>
                            @if($is_discount_enabled)
                            @lang('sale.discount')
                            @show_tooltip(__('tooltip.sale_discount'))
                            @endif
                            @if($is_rp_enabled)
                            {{session('business.rp_name')}}
                            @endif
                            (-):
                            <i class="fas fa-edit cursor-pointer" id="pos-edit-discount" title="@lang('sale.edit_discount')" aria-hidden="true" data-toggle="modal" data-target="#posEditDiscountModal"></i>
                            <span id="total_discount">0</span>
                            <input type="hidden" name="discount_type" id="discount_type"
                                   value="@if(empty($edit)){{'percentage'}}@else{{$transaction->discount_type}}@endif"
                                   data-default="percentage">

                            <input type="hidden" name="discount_amount" id="discount_amount"
                                   value="@if(empty($edit)) {{@num_format($business_details->default_sales_discount)}} @else {{@num_format($transaction->discount_amount)}} @endif"
                                   data-default="{{$business_details->default_sales_discount}}">

                            <input type="hidden" name="rp_redeemed" id="rp_redeemed"
                                   value="@if(empty($edit)){{'0'}}@else{{$transaction->rp_redeemed}}@endif">

                            <input type="hidden" name="rp_redeemed_amount" id="rp_redeemed_amount"
                                   value="@if(empty($edit)){{'0'}}@else {{$transaction->rp_redeemed_amount}} @endif">

                            </span>
                        </b>
                    </small>
                    @endif

                    <br/><label><input type="checkbox" class="cursor_lock" /> Lock Cursor</label>
                </td>
                <td style="width: 25%;">
                    <div class="row">
                        <div class="col-md-12">
                            <small><p class="pb-0 mb-0"><b>City Tax(+) :</b><b id="cityTax">0.00</b></p></small>
                            <small><p class="pb-0 mb-0"><b>State Tax(+) :</b><b id="stateTax">0.00</b></p></small>
                            <small>
                            <span>
                                <b>Total Tax(+): @show_tooltip(__('tooltip.sale_tax'))</b>
                                {{--<i class="fas fa-edit cursor-pointer" title="@lang('sale.edit_order_tax')"--}}
                                   {{--aria-hidden="true"--}}
                                   {{--data-toggle="modal" data-target="#posEditOrderTaxModal" id="pos-edit-tax"></i>--}}
                                <span id="order_tax">
                                    @if(empty($edit))
                                        0
                                    @else
                                        {{$transaction->tax_amount}}
                                    @endif
                                </span>

                                <input type="hidden" name="tax_rate_id"
                                       id="tax_rate_id"
                                       value="@if(empty($edit)) {{$business_details->default_sales_tax}} @else {{$transaction->tax_id}} @endif"
                                       data-default="{{$business_details->default_sales_tax}}">

                                <input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount"
                                       value="@if(empty($edit)) {{@num_format($business_details->tax_calculation_amount)}} @else {{@num_format(optional($transaction->tax)->amount)}} @endif"
                                       data-default="{{$business_details->tax_calculation_amount}}">

                            </span>
                            </small>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <div class="row">
                        @if($packed)
                            <div class="col-md-12">
                                <small><b>Delivery Method:</b></small>
                                <input type="hidden" name="delivery_method" id="delivery_method" value="{{@$transaction->delivery_method}}"/>
                                <!--<button type="button" class="packed_method btn btn-xs btn-success @if(@$transaction->delivery_method =='posShippingModalUpdateSelf')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdateSelf')  disabled="disabled" @endif>-->
                                <!--    <i class="fa fa-user"></i>-->
                                <!--    <b>Self</b>-->
                                <!--</button>-->
                                <button type="button" class="packed_method btn btn-xs btn-warning @if(@$transaction->delivery_method =='posShippingModalUpdateDelivery')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdateDelivery')  disabled="disabled" @endif>
                                    <i class="fa fa-motorcycle"></i>
                                    <b>Delivery</b>
                                </button>
                                <button type="button"  class="packed_method btn btn-xs btn-primary @if(@$transaction->delivery_method =='posShippingModalUpdatePickup')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdatePickup')  disabled="disabled" @endif>
                                    <i class="fa fa-truck-pickup"></i>
                                    <b>Pickup</b>
                                </button>
                                <button style="background-color: #001f3f;color: white;" type="button" class="packed_method btn btn-xs btn-dark @if(@$transaction->delivery_method =='posShippingModalUpdateShipping')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdateShipping')  disabled="disabled" @endif>
                                    <i class="fa fa-plane"></i>
                                    <b>Shipping</b>
                                </button>
                            </div>
                            <div class="col-md-12">
                                <small><b>Walk In Order Type:</b></small>
                                <button type="button" class="packed_method btn btn-xs btn-success @if(@$transaction->delivery_method =='posShippingModalUpdateSelfNonPicking' || @$transaction->delivery_method =='posShippingModalUpdateSelf')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdateSelfNonPicking' || @$transaction->delivery_method =='posShippingModalUpdateSelf')  disabled="disabled" @endif>
                                    <i class="fa fa-user"></i>
                                    <b>Walk In (Self)</b>
                                </button>
                                <button type="button"  attr="1" class="packed_method btn btn-xs btn-warning @if(@$transaction->delivery_method =='posShippingModalUpdateDeliveryNonPicking')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdateDeliveryNonPicking')  disabled="disabled" @endif>
                                    <i class="fa fa-motorcycle"></i>
                                    <b>Walk In (Delivery)</b>
                                </button>
                                <button style="background-color: #001f3f;color: white;" type="button"  attr="1" class="packed_method btn btn-xs btn-dark @if(@$transaction->delivery_method =='posShippingModalUpdateShippingNonPicking')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdateShippingNonPicking')  disabled="disabled" @endif>
                                    <i class="fa fa-plane"></i>
                                    <b>Walk In (Shipping)</b>
                                </button>
                            </div>
                        @else
                            <div class="col-md-12">
                                <small><b>Delivery Method:</b></small>
                                <input type="hidden" name="delivery_method" id="delivery_method" value="{{@$transaction->delivery_method}}"/>
                                <!--<button type="button" id="posShippingModalUpdateSelf" class="btn btn-xs btn-success @if(@$transaction->delivery_method =='posShippingModalUpdateSelf')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdateSelf')  disabled="disabled" @endif>-->
                                <!--    <i class="fa fa-user"></i>-->
                                <!--    <b>Self</b>-->
                                <!--</button>-->
                                <button type="button" id="posShippingModalUpdateDelivery" class="btn btn-xs btn-warning @if(@$transaction->delivery_method =='posShippingModalUpdateDelivery')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdateDelivery')  disabled="disabled" @endif>
                                    <i class="fa fa-motorcycle"></i>
                                    <b>Delivery</b>
                                </button>
                                <button type="button" id="posShippingModalUpdatePickup" class="btn btn-xs btn-primary @if(@$transaction->delivery_method =='posShippingModalUpdatePickup')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdatePickup')  disabled="disabled" @endif>
                                    <i class="fa fa-truck-pickup"></i>
                                    <b>Pickup</b>
                                </button>
                                <button style="background-color: #001f3f;color: white;" type="button" id="posShippingModalUpdateShipping" class="btn btn-xs btn-dark @if(@$transaction->delivery_method =='posShippingModalUpdateShipping')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdateShipping')  disabled="disabled" @endif>
                                    <i class="fa fa-plane"></i>
                                    <b>Shipping</b>
                                </button>
                            </div>
                            <div class="col-md-12">
                                <small><b>Walk In Order Type:</b></small>
                                <button type="button" id="posShippingModalUpdateSelfNonPicking" class="btn btn-xs btn-success @if(@$transaction->delivery_method =='posShippingModalUpdateSelfNonPicking')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdateSelfNonPicking')  disabled="disabled" @endif>
                                    <i class="fa fa-user"></i>
                                    <b>Walk In (Self)</b>
                                </button>
                                <button type="button" id="posShippingModalUpdateDeliveryNonPicking"  attr="1" class="btn btn-xs btn-warning @if(@$transaction->delivery_method =='posShippingModalUpdateDeliveryNonPicking')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdateDeliveryNonPicking')  disabled="disabled" @endif>
                                    <i class="fa fa-motorcycle"></i>
                                    <b>Walk In (Delivery)</b>
                                </button>
                                <button style="background-color: #001f3f;color: white;" type="button" id="posShippingModalUpdateShippingNonPicking"  attr="1" class="btn btn-xs btn-dark @if(@$transaction->delivery_method =='posShippingModalUpdateShippingNonPicking')  highlight-btn-borders @endif" @if(@$transaction->delivery_method =='posShippingModalUpdateShippingNonPicking')  disabled="disabled" @endif>
                                    <i class="fa fa-plane"></i>
                                    <b>Walk In (Shipping)</b>
                                </button>
                            </div>
                        @endif
                    </div>
                    {{--<small>--}}
                        {{--<span>--}}
						{{--<b>@lang('sale.shipping')(+): @show_tooltip(__('tooltip.shipping'))</b>--}}
						{{--<i class="fas fa-edit cursor-pointer" title="@lang('sale.shipping')" aria-hidden="true"--}}
                           {{--data-toggle="modal" data-target="#posShippingModal"></i>--}}
						{{--<span id="shipping_charges_amount">0</span>--}}
						<input type="hidden" name="shipping_details" id="shipping_details"
                               value="@if(empty($edit)){{''}}@else{{$transaction->shipping_details}}@endif"
                               data-default="">

						<input type="hidden" name="shipping_address" id="shipping_address"
                               value="@if(empty($edit)){{''}}@else{{$transaction->shipping_address}}@endif">

						<input type="hidden" name="shipping_status" id="shipping_status"
                               value="@if(empty($edit)){{''}}@else{{$transaction->shipping_status}}@endif">

						<input type="hidden" name="delivered_to" id="delivered_to"
                               value="@if(empty($edit)){{''}}@else{{$transaction->delivered_to}}@endif">

						<input type="hidden" name="shipping_charges" id="shipping_charges"
                               value="@if(empty($edit)){{@num_format(0.00)}} @else{{@num_format($transaction->shipping_charges)}} @endif"
                               data-default="0.00">
					{{--</span>--}}
                    {{--</small>--}}
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <b>
                        {{--@if($is_discount_enabled)--}}
                        {{--@lang('sale.discount')--}}
                        {{--@show_tooltip(__('tooltip.sale_discount'))--}}
                        {{--@endif--}}
                        {{--@if($is_rp_enabled)--}}
                        {{--{{session('business.rp_name')}}--}}
                        {{--@endif--}}
                        {{--(-):--}}
                        {{--<i class="fas fa-edit cursor-pointer" id="pos-edit-discount" title="@lang('sale.edit_discount')" aria-hidden="true" data-toggle="modal" data-target="#posEditDiscountModal"></i>--}}
                        {{--<span id="total_discount">0</span>--}}
                        <input type="hidden" name="discount_type" id="discount_type"
                               value="@if(empty($edit)){{'percentage'}}@else{{$transaction->discount_type}}@endif"
                               data-default="percentage">

                        <input type="hidden" name="discount_amount" id="discount_amount"
                               value="@if(empty($edit)) {{@num_format($business_details->default_sales_discount)}} @else {{@num_format($transaction->discount_amount)}} @endif"
                               data-default="{{$business_details->default_sales_discount}}">

                        <input type="hidden" name="rp_redeemed" id="rp_redeemed"
                               value="@if(empty($edit)){{'0'}}@else{{$transaction->rp_redeemed}}@endif">

                        <input type="hidden" name="rp_redeemed_amount" id="rp_redeemed_amount"
                               value="@if(empty($edit)){{'0'}}@else {{$transaction->rp_redeemed_amount}} @endif">

                        {{--</span>--}}
                    </b>
                </td>
                <td class="@if($pos_settings['disable_order_tax'] != 0) hide @endif">

                </td>
                <td class="@if($pos_settings['disable_discount'] != 0) hide @endif">

                </td>
                @if(in_array('types_of_service', $enabled_modules))
                    <td class="col-sm-3 col-xs-6 d-inline-table">
                        <b>@lang('lang_v1.packing_charge')(+):</b>
                        <i class="fas fa-edit cursor-pointer service_modal_btn"></i>
                        <span id="packing_charge_text">
							0
						</span>
                    </td>
                @endif
                @if(!empty($pos_settings['amount_rounding_method']) && $pos_settings['amount_rounding_method'] > 0)
                    <td>
                        <b id="round_off">@lang('lang_v1.round_off'):</b> <span id="round_off_text">0</span>
                        <input type="hidden" name="round_off_amount" id="round_off_amount" value=0>
                    </td>
                @endif
            </tr>
        </table>
    </div>
</div>