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
            @if($sell->status == 'draft' && $sell->is_quotation == 1)
              {{ __('lang_v1.quotation') }}
            @else
              {{ __('sale.' . $sell->status) }}
            @endif
          <br>
          <b>{{ __('sale.payment_status') }}:</b> @if(!empty($sell->payment_status)){{ __('lang_v1.' . $sell->payment_status) }}<br>
          @endif
        </div>
        <div class="col-sm-4">
          @if(!empty($sell->contact->supplier_business_name))
            {{ $sell->contact->supplier_business_name }}<br>
          @endif
          <b>{{ __('sale.customer_name') }}:</b> {{ $sell->contact->name }}<br>
          <b>{{ __('business.address') }}:</b><br>
          @if(!empty($sell->billing_address()))
            {{$sell->billing_address()}}
          @else
            {!! $sell->contact->contact_address !!}
            @if($sell->contact->mobile)
            <br>
                {{__('contact.mobile')}}: {{ $sell->contact->mobile }}
            @endif
            @if($sell->contact->alternate_number)
            <br>
                {{__('contact.alternate_contact_number')}}: {{ $sell->contact->alternate_number }}
            @endif
            @if($sell->contact->landline)
              <br>
                {{__('contact.landline')}}: {{ $sell->contact->landline }}
            @endif
            <br>

            @if($sell->contact->tax_number)
                <b>{{__('TAX ID ')}}:</b> {{ $sell->contact->tax_number }}
            @else
                <b>{{__('TAX ID ')}}:</b> None
            @endif
            <br>

            @if($sell->contact->tobacco_license_no)
                <b>{{__('TOBACCO LIC ')}}:</b> {{ $sell->contact->tobacco_license_no }}
            @else
                <b>{{__('TOBACCO LIC ')}}:</b> None
            @endif
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
        <span class="label @if(!empty($shipping_status_colors[$sell->shipping_status])) {{$shipping_status_colors[$sell->shipping_status]}} @else {{'bg-gray'}} @endif">{{$shipping_statuses[$sell->shipping_status] ?? '' }}</span><br>
        @if(!empty($sell->shipping_address()))
          {{$sell->shipping_address()}}
        @else
          {{$sell->shipping_address ?? '--'}}
        @endif
        @if(!empty($sell->delivered_to))
          <br><strong>@lang('lang_v1.delivered_to'): </strong> {{$sell->delivered_to}}
        @endif

        @if(!empty($pick_pack->picked_by))
          <br><strong>@lang('Picked By'): </strong> {{ $pick_pack->picked_by}}
        @endif
        @if(!empty($pick_pack->packed_by))
          <br><strong>@lang('Packed By'): </strong> {{ $pick_pack->packed_by}}
        @endif


        @if(in_array('types_of_service' ,$enabled_modules))
        @php
          $custom_labels = json_decode(session('business.custom_labels'), true);
        @endphp
          @if(!empty($sell->types_of_service))
            <strong>@lang('lang_v1.types_of_service'):</strong>
            {{$sell->types_of_service->name}}<br>
          @endif
          @if(!empty($sell->types_of_service->enable_custom_fields))
            <strong>{{ $custom_labels['types_of_service']['custom_field_1'] ?? __('lang_v1.service_custom_field_1' )}}:</strong>
            {{$sell->service_custom_field_1}}<br>
            <strong>{{ $custom_labels['types_of_service']['custom_field_2'] ?? __('lang_v1.service_custom_field_2' )}}:</strong>
            {{$sell->service_custom_field_2}}<br>
            <strong>{{ $custom_labels['types_of_service']['custom_field_3'] ?? __('lang_v1.service_custom_field_3' )}}:</strong>
            {{$sell->service_custom_field_3}}<br>
            <strong>{{ $custom_labels['types_of_service']['custom_field_4'] ?? __('lang_v1.service_custom_field_4' )}}:</strong>
            {{$sell->service_custom_field_4}}
          @endif
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
            @include('sale_pos.partials.sale_line_details_jadoo')
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
              @foreach($sell->payment_lines as $payment_line)
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
                <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->total_before_tax }}</span></td>
              </tr>
              <!--<tr>-->
              <!--  <th>{{ __('sale.discount') }}:</th>-->
              <!--  <td><b>(-)</b></td>-->
              <!--  <td><div class="pull-right"><span class="display_currency" @if( $sell->discount_type == 'fixed') data-currency_symbol="true" @endif>{{ $sell->discount_amount }}</span> @if( $sell->discount_type == 'percentage') {{ '%'}} @endif</span></div></td>-->
              <!--</tr>-->
              @if(in_array('types_of_service' ,$enabled_modules) && !empty($sell->packing_charge))
                <tr>
                  <th>{{ __('lang_v1.packing_charge') }}:</th>
                  <td><b>(+)</b></td>
                  <td><div class="pull-right"><span class="display_currency" @if( $sell->packing_charge_type == 'fixed') data-currency_symbol="true" @endif>{{ $sell->packing_charge }}</span> @if( $sell->packing_charge_type == 'percent') {{ '%'}} @endif </div></td>
                </tr>
              @endif
              @if(session('business.enable_rp') == 1 && !empty($sell->rp_redeemed) )
                <tr>
                  <th>{{session('business.rp_name')}}:</th>
                  <td><b>(-)</b></td>
                  <td> <span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->rp_redeemed_amount }}</span></td>
                </tr>
              @endif
              <tr>
                <th>{{ __('sale.order_tax') }}:</th>
                <td><b>(+)</b></td>
                <td class="text-right">
                  @php
                    $i = 0;
                    if(count($sell->sell_lines) > 0)
                    {
                        foreach ($sell->sell_lines as $sell_line) {
                            $i = $i +  $sell_line->city_tax_amount + $sell_line->pos_line_tax_amount;
                        }
                    }
                  @endphp
                  <strong><small></small></strong>  <span class="display_currency pull-right" data-currency_symbol="true">{{ $i }}</span><br>
                {{--      @if(!empty($order_taxes))
                    @foreach($order_taxes as $k => $v)
                      <strong><small>{{$k}}</small></strong> - <span class="display_currency pull-right" data-currency_symbol="true">{{ $v }}</span><br>
                    @endforeach
                  @else
                  0.00
                  @endif --}}
                </td>
              </tr>
              <tr>
                <th>{{ __('sale.shipping') }}: @if($sell->shipping_details)({{$sell->shipping_details}}) @endif</th>
                <td><b>(+)</b></td>
                <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->shipping_charges }}</span></td>
              </tr>
              <tr>
                <th>Order Total:</th>
                <td></td>
                <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->total_before_tax + $sell->shipping_charges + $i }}</span></td>
              </tr>
              <tr>
                <th>{{ __('sale.discount') }}:</th>
                <td><b>(-)</b></td>
                <td><div class="pull-right"><span class="display_currency" @if( $sell->discount_type == 'fixed') data-currency_symbol="true" @endif>{{ $sell->discount_amount }}</span> @if( $sell->discount_type == 'percentage') {{ '%'}} @endif</span></div></td>
              </tr>
              <!-- <tr>
                <th>{{ __('lang_v1.round_off') }}: </th>
                <td></td>
                <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->round_off_amount }}</span></td>
              </tr> -->
              <tr>
                   @php
                    $total_paid = (string) $total_paid;
                    if($sell->is_advance == 1 && $sell->payment_status == 'paid'){
                             $total_remaining =  $sell->amount - $sell->final_total - $sell->advanceamt;
                    }
                    else{
                        $total_remaining =  $sell->final_total - $total_paid;
                    }
                  @endphp
                <th>{{ __('sale.total_payable') }}: </th>
                <td></td>
                <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->final_total }}</span></td>
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
            @if($sell->additional_notes)
              {!! nl2br($sell->additional_notes) !!}
            @else
              --
            @endif
          </p>
        </div>
        <div class="col-sm-5">
          <strong>{{ __( 'sale.staff_note')}}:</strong><br>
          <p class="well well-sm no-shadow bg-gray">
            @if($sell->staff_note)
              {!! nl2br($sell->staff_note) !!}
            @else
              --
            @endif
          </p>
        </div>
        <div class="col-sm-2">
          <strong>@lang('lang_v1.box_qty')</strong><br>
          <p class="well well-sm no-shadow bg-gray">
              {!! nl2br($sell->box_qty ?? 0) !!}
          </p>
        </div>
      </div>
      {{-- <div class="row">
            <div class="col-sm-12">
              <table class="table bg-gray">
                <thead>
                  <tr class="bg-green">
                    <th>User Name</th>
                    <th>Action</th>
                    <th>Message</th>
                    <th width="10%">Date And Time</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($activity_logs as $log)
                    <tr>
                      <td>{{$log->first_name}}</td>
                      @if($log->description == 'added')
                        <td>Added</td>
                      @elseif($log->description == 'edited')
                        <td>Edited</td>
                      @endif
                      @php $str_array = explode(',',$log->message); @endphp
                      <td>
                        @foreach($str_array as $message)
                          @if($message!="")
                            # {{$message}}<br>
                          @endif
                        @endforeach
                      </td>
                      <td width="10%"> {{ Carbon\Carbon::parse($log->datetime)->format('m-d-Y G:i A') }}</td>
                    </tr>
                  @empty
                    <td colspan="4" style="text-align: center;">No logs Found!!</td>
                  @endforelse
                  <tr></tr>
                </tbody>

              </table>
            </div>
      </div> --}}
    </div>
    <div class="modal-footer">
      <a href="#" class="print-invoice btn btn-primary" data-href="{{route('sell.printInvoice', [$sell->id])}}"><i class="fa fa-print" aria-hidden="true"></i> @lang("messages.print")</a>
        <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    $(document).ready(function(){
      var element = $('div.modal-xl');
      __currency_convert_recursively(element);

      var jrows = $('.jro').map(function(i, row){
          return {
              'name': row.cells[1].textContent.trim(),
              'qty': row.cells[4].textContent.toString().replace(',', ''),
              'price': row.cells[5].textContent.trim(),
              'tax': row.cells[7].textContent.trim(),
              'subtotal': row.cells[9].textContent.toString().replace(',', '').replace('$', '').trim()
          }
      }).get();

      new_jrows = [];
      jrows.forEach(function(row) {
          if (!this[row.name]) {
              this[row.name] = {
                  name: row.name,
                  qty: 0,
                  price: row.price,
                  tax: row.tax,
                  subtotal: 0
              };
              new_jrows.push(this[row.name]);
          }
          this[row.name].qty += Math.round(row.qty);
          this[row.name].subtotal += parseFloat(row.subtotal);

      }, Object.create(null));

      var tableRef = document.getElementById('jtbd');
      for(i = 0; i < new_jrows.length; i++){
          var newRow = tableRef.insertRow(tableRef.rows.length);
          var new_td = "<td>" + (i + 1) + "</td>";
          new_td += "<td>" + new_jrows[i].name + "</td>";
          new_td += "<td class='text-right'>" + new_jrows[i].qty + "</td>";
          new_td += "<td class='text-right'>" + new_jrows[i].price + "</td>";
          new_td += "<td class='text-right'>" + new_jrows[i].tax + "</td>";
          new_td += "<td class='text-right'> $ " + new_jrows[i].subtotal.toFixed(2) + "</td>";
          newRow.innerHTML = new_td;
      }

    });
  </script>
