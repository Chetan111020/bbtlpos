<div class="modal-dialog modal-xl no-print" role="document">
  <div class="modal-content">
    <div class="modal-header">
    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalTitle"> @lang('sale.sell_details') (<b>@lang('sale.invoice_no'):</b> {{ $sell->invoice_no }})
    </h4>
</div>
<div class="modal-body">
    <div class="row">
      <div class="col-xs-12">
          <p class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($sell->transaction_date) }}</p>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-4">
        <b>{{ __('sale.invoice_no') }}:</b> #{{ $sell->invoice_no }}<br>
        <b>{{ __('sale.status') }}:</b> 
            {{ __('sale.' . $sell->status) }}
        <br>
        <b>{{ __('sale.payment_status') }}:</b> @if(!empty($sell->payment_status)){{ __('lang_v1.' . $sell->payment_status) }}<br>
        @endif
      </div>
      <div class="col-sm-4">
        @if(!empty($contact->supplier_business_name))
          {{ $contact->supplier_business_name }}<br>
        @endif
        <b>{{ __('sale.customer_name') }}:</b> {{ $contact->name }}<br>
        <b>{{ __('business.address') }}:</b>
          {!! $contact->contact_address !!}
          @if($contact->mobile)
          <br>
              {{__('contact.mobile')}}: {{ $contact->mobile }}
          @endif
          @if($contact->alternate_number)
          <br>
              {{__('contact.alternate_contact_number')}}: {{ $contact->alternate_number }}
          @endif
          @if($contact->landline)
            <br>
              {{__('contact.landline')}}: {{ $contact->landline }}
          @endif
      </div>
      <div class="col-sm-4">
      @if(in_array('tables' ,$enabled_modules))
         <strong>@lang('restaurant.table'):</strong>
          {{$sell->table->name ?? ''}}<br>
      @endif
      @if(in_array('service_staff' ,$enabled_modules))
          <strong>@lang('restaurant.service_staff'):</strong>
          {{$sell->service_staff->user_full_name ?? ''}}<br>
      @endif

      <strong>@lang('sale.shipping'):</strong>
      <span class="label @if(!empty($shipping_status_colors[$t_line->shipping_status])) {{$shipping_status_colors[$t_line->shipping_status]}} @else {{'bg-gray'}} @endif">{{$shipping_statuses[$t_line->shipping_status] ?? '' }}</span><br>
      @if(!empty($t_line->shipping_address))
        {{$t_line->shipping_address}}
      @else
        {{$t_line->shipping_address ?? '--'}}
      @endif
      @if(!empty($t_line->delivered_to))
        <br><strong>@lang('lang_v1.delivered_to'): </strong> {{$t_line->delivered_to}}
      @endif
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-sm-12 col-xs-12">
        <h4>{{ __('sale.products') }}:</h4>
      </div>

      <div class="col-sm-12 col-xs-12">
        <div class="table-responsive">
            <table class="table bg-gray">
                  <tr class="bg-green">
                      <th>#</th>
                      <th>{{ __('sale.product') }}</th>
                      <th style="width: 10px;">Packing Started Time</th>
                      <th style="width: 10px;">Packing Comleted Time</th>
                      <th>{{ __('sale.qty') }}</th>
                      <th>{{ __('sale.unit_price') }}</th>
                      <th>{{ __('sale.tax') }}</th>
                      <th>{{ __('sale.price_inc_tax') }}</th>
                      <th>{{ __('sale.subtotal') }}</th>
                  </tr>
                  @foreach($sell_lines as $sell_line)
                      <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>
                              {{ $sell_line['product']['name'] }}
                              @if( $sell_line['product']['type'] == 'variable')
                              - {{ $sell_line['variations']['product_variation']['name'] ?? ''}}
                              - {{ $sell_line['variations']['name'] ?? ''}},
                              @endif
                              {{ $sell_line['variations']['sub_sku'] ?? ''}}
                              @php
                              $brand = $sell_line['product']['brand'];
                              @endphp
                              @if(!empty($brand['name']))
                              , {{$brand['name']}}
                              @endif

                              @if(!empty($sell_line['sell_line_note']))
                              <br> {{$sell_line['sell_line_note']}}
                              @endif
                          </td>
                            @if ($sell_line['packing_started_time'])
                              <td>{{$sell_line['packing_started_time']}}</td>
                            @else
                              <td>N/A</td>
                            @endif
                            @if ($sell_line['packing_completed_time'])
                              <td>{{$sell_line['packing_completed_time']}}</td> 
                            @else
                              <td>N/A</td>
                            @endif
                          <td>
                              <span data-currency_symbol="false" data-is_quantity="true">{{ round($sell_line['quantity']) }}</span> @if(!empty($sell_line['sub_unit'])) {{$sell_line['sub_unit']['short_name']}} @else {{$sell_line['product']['unit']['short_name']}} @endif
                          </td>
                          <td>
                              <span class="display_currency" data-currency_symbol="true">{{ $sell_line['unit_price_before_discount'] }}</span>
                          </td>
                          <td>
                              <span class="display_currency" data-currency_symbol="true">{{ $sell_line['city_tax_amount'] + $sell_line['pos_line_tax_amount'] }}</span> 
                              @if(!empty($taxes[$sell_line['tax_id']]))
                              ( {{ $taxes[$sell_line['tax_id']]}} )
                              @endif
                          </td>
                          <td>
                              <span class="display_currency" data-currency_symbol="true">{{ $sell_line['unit_price_inc_tax'] }}</span>
                          </td>
                          <td>
                              <span class="display_currency" data-currency_symbol="true">{{ $sell_line['quantity'] * $sell_line['unit_price_inc_tax'] }}</span>
                          </td>
                      </tr>
                  @endforeach
              </table>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-12 col-xs-12">
        <h4>{{ __('sale.payment_info') }}:</h4>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="table-responsive">
          <table class="table bg-gray">
            <tr class="bg-green">
              <th>#</th>
              <th>{{ __('messages.date') }}</th>
              <th>{{ __('purchase.ref_no') }}</th>
              <th>{{ __('sale.amount') }}</th>
              <th>{{ __('sale.payment_mode') }}</th>
              <th>{{ __('sale.payment_note') }}</th>
            </tr>
            @php
              $total_paid = 0;
            @endphp
            @foreach($payment_lines as $payment_line)
              @php
                if($payment_line->is_return == 1){
                  $total_paid -= $payment_line->amount;
                } else {
                  $total_paid += $payment_line->amount;
                }
              @endphp
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ @format_date($payment_line->paid_on) }}</td>
                <td>{{ $payment_line->payment_ref_no }}</td>
                <td><span class="display_currency" data-currency_symbol="true">{{ $payment_line->amount }}</span></td>
                <td>
                  {{ $payment_types[$payment_line->method] ?? $payment_line->method }}
                  @if($payment_line->is_advance == 1 && $payment_line->advance_amt > 0)
                  <br/>
                  (Advance)
                  @elseif($payment_line->is_return == 1)
                    <br/>
                    ( {{ __('lang_v1.change_return') }} )
                  @endif
                </td>
                <td>@if($payment_line->note) 
                  {{ ucfirst($payment_line->note) }}
                  @else
                  --
                  @endif
                </td>
              </tr>
            @endforeach
          </table>
        </div>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="table-responsive">
          <table class="table bg-gray">
            <tr>
              <th>Sub Total: </th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $t_line->total_before_tax }}</span></td>
            </tr>
          
            @if(in_array('types_of_service' ,$enabled_modules) && !empty($t_line->packing_charge))
              <tr>
                <th>{{ __('lang_v1.packing_charge') }}:</th>
                <td><b>(+)</b></td>
                <td><div class="pull-right"><span class="display_currency" @if( $t_line->packing_charge_type == 'fixed') data-currency_symbol="true" @endif>{{ $t_line->packing_charge }}</span> @if( $t_line->packing_charge_type == 'percent') {{ '%'}} @endif </div></td>
              </tr>
            @endif
            @if(session('business.enable_rp') == 1 && !empty($t_line->rp_redeemed) )
              <tr>
                <th>{{session('business.rp_name')}}:</th>
                <td><b>(-)</b></td>
                <td> <span class="display_currency pull-right" data-currency_symbol="true">{{ $t_line->rp_redeemed_amount }}</span></td>
              </tr>
            @endif
            <tr>
              <th>{{ __('sale.order_tax') }}: 
              </th> 
              <td><b>(+)</b></td>
              <td class="text-right">
                @php 
                  $i = 0;
                  if(count($sell_lines) > 0)
                  {
                      foreach ($sell_lines as $sell_line) {
                          $i = $i +  $sell_line['city_tax_amount'] + $sell_line['pos_line_tax_amount'];
                      }   
                  }
                @endphp
                <strong><small></small></strong>  <span class="display_currency pull-right" data-currency_symbol="true">{{ $i }}</span><br>
              </td>
            </tr>
            <tr>
              <th>{{ __('sale.shipping') }}: @if($t_line->shipping_details)({{$t_line->shipping_details}}) @endif</th>
              <td><b>(+)</b></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $t_line->shipping_charges }}</span></td>
            </tr>
            <tr>
              <th>Order Total:</th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $t_line->total_before_tax + $t_line->shipping_charges + $i }}</span></td>
            </tr>
            <tr>
              <th>{{ __('sale.discount') }}:</th>
              <td><b>(-)</b></td>
              <td><div class="pull-right"><span class="display_currency" @if( $t_line->discount_type == 'fixed') data-currency_symbol="true" @endif>{{ $t_line->discount_amount }} @if( $t_line->discount_type == 'percentage') {{ '%'}} @endif </span></div></td>
            </tr>
            <tr>
                 @php
                  $total_paid = (string) $total_paid;
                  $total_remaining =  $t_line->final_total - $total_paid;
                @endphp
              <th>{{ __('sale.total_payable') }}: </th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $t_line->final_total }}</span></td>
            </tr>
            <tr>
              <th>{{ __('sale.total_paid') }}:</th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ $total_paid }}</span></td>
            </tr>
            <tr>
              <th>{{ __('sale.total_remaining') }}:</th>
              <td></td>
              <td>
                <!-- Converting total paid to string for floating point substraction issue -->
                @php
                  $total_paid = (string) $total_paid;
                @endphp
                <span class="display_currency pull-right" data-currency_symbol="true" >{{ $total_remaining }}</span></td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-5">
        <strong>{{ __( 'sale.sell_note')}}:</strong><br>
        <p class="well well-sm no-shadow bg-gray">
          @if($t_line->additional_notes)
            {!! nl2br($t_line->additional_notes) !!}
          @else
            --
          @endif
        </p>
      </div>
      <div class="col-sm-5">
        <strong>{{ __( 'sale.staff_note')}}:</strong><br>
        <p class="well well-sm no-shadow bg-gray">
          @if($t_line->staff_note)
            {!! nl2br($t_line->staff_note) !!}
          @else
            --
          @endif
        </p>
      </div>
      <div class="col-sm-2">
        <strong>@lang('lang_v1.box_qty')</strong><br>
        <p class="well well-sm no-shadow bg-gray">
            {!! nl2br($t_line->box_qty ?? 0) !!}
        </p>
      </div>
    </div>
  </div>
  <div class="modal-footer">
      <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    var element = $('div.modal-xl');
    __currency_convert_recursively(element);
  });
</script>
