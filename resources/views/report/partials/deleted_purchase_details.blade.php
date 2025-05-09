<div class="modal-dialog modal-xl" role="document">
  <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalTitle"> @lang('purchase.purchase_details') (<b>@lang('purchase.ref_no'):</b> #{{ $purchase->invoice_no }})
        </h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-sm-12">
          <p class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($purchase->transaction_date) }}</p>
        </div>
      </div>
    
        <div class="row invoice-info">
          @if(!empty($contact))
            <div class="col-sm-4 invoice-col">
              @lang('purchase.supplier'):
              <address>
                <strong>{{ $contact->supplier_business_name }}</strong>
                {{ $contact->name }}
                @if(!empty($contact->address_line_1))
                  <br>{{$contact->address_line_1}}
                @endif
                @if(!empty($contact->address_line_2))
                  <br>{{$contact->address_line_2}}
                @endif
                @if(!empty($contact->city) || !empty($contact->state) || !empty($contact->country))
                  <br>{{implode(',', array_filter([$contact->city, $contact->state, $contact->country, $contact->zip_code]))}}
                @endif
                @if(!empty($contact->tax_number))
                  <br>@lang('contact.tax_no'): {{$contact->tax_number}}
                @endif
                @if(!empty($contact->mobile))
                  <br>@lang('contact.mobile'): {{$contact->mobile}}
                @endif
                @if(!empty($contact->email))
                  <br>@lang('business.email'): {{$contact->email}}
                @endif
              </address>
            </div>
          @endif
          @if(!empty($business))
          <div class="col-sm-4 invoice-col">
            @lang('business.business'):
            <address>
              <strong>{{ $business->name }}</strong>
              {{ $business->name }}
              @if(!empty($business->landmark))
                <br>{{$business->landmark}}
              @endif
              @if(!empty($business->city) || !empty($business->state) || !empty($business->country))
                <br>{{implode(',', array_filter([$business->city, $business->state, $business->country]))}}
              @endif
              
              @if(!empty($business->tax_number_1))
                <br>{{$business->tax_label_1}}: {{$business->tax_number_1}}
              @endif

              @if(!empty($business->tax_number_2))
                <br>{{$business->tax_label_2}}: {{$business->tax_number_2}}
              @endif

              @if(!empty($business->mobile))
                <br>@lang('contact.mobile'): {{$business->mobile}}
              @endif
              @if(!empty($business->email))
                <br>@lang('business.email'): {{$business->email}}
              @endif
            </address>
          </div>
         @endif
          <div class="col-sm-4 invoice-col">
            <b>@lang('purchase.ref_no'):</b> #{{ $purchase->invoice_no }}<br/>
            <b>Received Date:</b> {{ @format_date($purchase->transaction_date) }}<br/>
            <b>Invoice @lang('messages.date'):</b> {{ @format_date($purchase->received_date ?? $purchase->transaction_date) }}<br/>
            <b>@lang('purchase.purchase_status'):</b> {{ __('lang_v1.' . $purchase->status) }}<br>
            <b>@lang('purchase.payment_status'):</b> {{ __('lang_v1.' . $purchase->payment_status) }}<br>
          </div>
        </div>
        <div class="row invoice-info">
          <div class="col-sm-12 invoice-col">
            @if($extra_document && count($extra_document) > 0)
            <span><b>View Files:</b></span>
                <tr>
                    @forelse ($extra_document as $key => $doc)
                        @php $path = !empty($doc) ? asset('/uploads/documents/' . $doc) : null; @endphp
                        <td>
                          <div class="btn-group">
                              <a href="{{ $path }}" class="pull-left btn btn-primary" target="_blank" title="View Document" >
                                <i class="fas fa-eye"></i> 
                              </a>
                              <a href="{{$path}}" download="{{$doc}}" class="btn btn-primary no-print" title="Download"> 
                                <i class="fa fa-download"></i>  
                              </a>
                          </div>                  
                        </td>
                    @empty
                    @endforelse
                </tr>
            @endif
          </div>
        </div>
        <br>
  <div class="row">
    <div class="col-sm-12 col-xs-12">
      <div class="table-responsive">
        <table class="table bg-gray">
          <thead>
            <tr class="bg-green">
              <th>#</th>
              <th>@lang('product.product_name')</th>
              <th class="text-right">@lang('purchase.purchase_quantity')</th>
              <th class="text-right">@lang( 'lang_v1.unit_cost_before_discount' )</th>
              <th class="text-right">@lang( 'lang_v1.discount_percent' )</th>
              <th class="no-print text-right">@lang('purchase.unit_cost_before_tax')</th>
              <th class="no-print text-right">@lang('purchase.subtotal_before_tax')</th>
              <th class="text-right">@lang('sale.tax')</th>
              <th class="text-right">@lang('purchase.unit_cost_after_tax')</th>
              <th class="text-right">@lang('purchase.unit_selling_price')</th>
              <th class="text-right">@lang('sale.subtotal')</th>
            </tr>
          </thead>
          @php 
            $total_before_tax = 0.00;
          @endphp
          @if($purchase_lines && count($purchase_lines) > 0)
            @foreach($purchase_lines as $purchase_line)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  {{ $purchase_line['product']['name'] }}
                   @if( $purchase_line['product']['type'] == 'variable')
                    - {{ $purchase_line['variations']['product_variation']['name']}}
                    - {{ $purchase_line['variations']['name']}}
                   @endif
                </td>
                <td><span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $purchase_line['quantity'] }}</span> @if(!empty($purchase_line['sub_unit'])) {{$purchase_line['sub_unit']['short_name']}} @else {{$purchase_line['product']['unit']['short_name']}} @endif</td>
                <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line['pp_without_discount']}}</span></td>
                <td class="text-right"><span class="display_currency">{{ $purchase_line['discount_percent']}}</span> %</td>
                <td class="no-print text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line['purchase_price'] }}</span></td>
                <td class="no-print text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line['quantity'] * $purchase_line['purchase_price'] }}</span></td>
                <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line['item_tax'] }} </span> <br/><small>@if(!empty($taxes[$purchase_line['tax_id']])) ( {{ $taxes[$purchase_line['tax_id']]}} ) </small>@endif</td>
                <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line['purchase_price_inc_tax'] }}</span></td>
                @php
                  $sp = $purchase_line['default_sell_price'] ?? $purchase_line['variations']['default_sell_price'];
                  if(!empty($purchase_line['sub_unit']['base_unit_multiplier'])) {
                    $sp = $sp * $purchase_line['sub_unit']['base_unit_multiplier'];
                  }
                @endphp
                <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{$sp}}</span></td>
                <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line['purchase_price_inc_tax'] * $purchase_line['quantity'] }}</span></td>
              </tr>
              @php 
                $total_before_tax += ($purchase_line['quantity'] * $purchase_line['pp_without_discount']);
              @endphp
            @endforeach
          @endif
        </table>
      </div>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm-12 col-xs-12">
      <h4>{{ __('sale.payment_info') }}:</h4>
    </div>
    <div class="col-md-6 col-sm-12 col-xs-12">
      <div class="table-responsive">
        <table class="table">
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
            $total_advance = 0;
          @endphp
          @forelse($payment_lines as $payment_line)
            @php
              $total_paid += $payment_line->amount;
              $total_advance += $payment_line->advance_amt;
            @endphp
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ @format_date($payment_line->paid_on) }}</td>
              <td>{{ $payment_line->payment_ref_no }}</td>
              <td><span class="display_currency" data-currency_symbol="true">{{ $payment_line->amount }}</span></td>
              <td>{{ $payment_methods[$payment_line->method] ?? '' }}</td>
              <td>@if($payment_line->note) 
                {{ ucfirst($payment_line->note) }}
                @else
                --
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center">
                @lang('purchase.no_payments')
              </td>
            </tr>
          @endforelse
        </table>
      </div>
    </div>
    <div class="col-md-6 col-sm-12 col-xs-12">
      <div class="table-responsive">
        <table class="table">
          <!-- <tr class="hide">
            <th>@lang('purchase.total_before_tax'): </th>
            <td></td>
            <td><span class="display_currency pull-right">{{ $total_before_tax }}</span></td>
          </tr> -->
          <tr>
            <th>@lang('purchase.net_total_amount'): </th>
            <td></td>
            <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $total_before_tax }}</span></td>
          </tr>
          <tr>
            <th>@lang('purchase.discount'):</th>
            <td>
              <b>(-)</b>
              @if($purchase->discount_type == 'percentage')
                ({{$purchase->discount_amount}} %)
              @endif
            </td>
            <td>
              <span class="display_currency pull-right" data-currency_symbol="true">
                @if($purchase->discount_type == 'percentage')
                  {{$purchase->discount_amount * $total_before_tax / 100}}
                @else
                  {{$purchase->discount_amount}}
                @endif                  
              </span>
            </td>
          </tr>
          <tr>
            <th>Advance Amount:</th>
            <td>
              <b>(+)</b>
            </td>
            <td>
              <span class="display_currency pull-right" data-currency_symbol="true">
                {{$total_advance}}             
              </span>
            </td>
          </tr>
          <tr>
            <th>@lang('purchase.purchase_tax'):</th>
            <td><b>(+)</b></td>
            <td class="text-right">
                @if(!empty($purchase_taxes))
                  @foreach($purchase_taxes as $k => $v)
                    <strong><small>{{$k}}</small></strong> - <span class="display_currency pull-right" data-currency_symbol="true">{{ $v }}</span><br>
                  @endforeach
                @else
                0.00
                @endif
              </td>
          </tr>
          @if( !empty( $purchase->shipping_charges ) )
            <tr>
              <th>@lang('purchase.additional_shipping_charges'):</th>
              <td><b>(+)</b></td>
              <td><span class="display_currency pull-right" >{{ $purchase->shipping_charges }}</span></td>
            </tr>
          @endif
          <tr>
            <th>@lang('purchase.purchase_total'):</th>
            <td></td>
            <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ $purchase->final_total  }}</span></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-6">
      <strong>@lang('purchase.shipping_details'):</strong><br>
      <p class="well well-sm no-shadow bg-gray">
        @if($purchase->shipping_details)
          {{ $purchase->shipping_details }}
        @else
          --
        @endif
      </p>
    </div>
    <div class="col-sm-6">
      <strong>@lang('purchase.additional_notes'):</strong><br>
      <p class="well well-sm no-shadow bg-gray">
        @if($purchase->additional_notes)
          {{ $purchase->additional_notes }}
        @else
          --
        @endif
      </p>
    </div>
  </div>
  {{-- Barcode --}}
  <div class="row print_section">
    <div class="col-xs-12">
      <img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($purchase->invoice_no, 'C128', 2,30,array(39, 48, 54), true)}}">
    </div>
  </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary no-print" aria-label="Print" 
      onclick="$(this).closest('div.modal-content').printThis();"><i class="fa fa-print"></i> @lang( 'messages.print' )
      </button>
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