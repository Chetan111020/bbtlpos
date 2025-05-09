<div class="modal-dialog modal-xl no-print" role="document">
  <div class="modal-content">
    <div class="modal-header">
    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalTitle"> @lang('Credit Memo') (<b>@lang('Credit Memo No'):</b> {{ $sell->invoice_no }})
    </h4>
</div>
@php
    $totalTax=0;
@endphp
<div class="modal-body">
   <div class="row">
      <div class="col-sm-6 col-xs-6">
        <h4>@lang('lang_v1.sell_return_details'):</h4>
        <strong>@lang('lang_v1.return_date'):</strong> {{@format_date($sell->transaction_date)}}<br>
        <strong>@lang('contact.customer'):</strong> {{ $sell->contact->name }} <br>
        <strong>@lang('purchase.business_location'):</strong> {{ $sell->location->name }}
      </div>
      <div class="col-sm-6 col-xs-6">
        <h4>@lang('lang_v1.sell_details'):</h4>
        <strong>@lang('sale.invoice_no'):</strong> {{ $sell->invoice_no }} <br>
        <strong>@lang('messages.date'):</strong> {{@format_date($sell->transaction_date)}}
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-sm-12">

      </div>
      <div class="col-sm-4">

      </div>
      <div class="col-sm-12">
        <br>
        <table class="table bg-gray">
          <thead>
            <tr class="bg-green">
                <th>#</th>
                <th>@lang('product.product_name')</th>
                <th>@lang('sale.unit_price')</th>
                <th>@lang('lang_v1.return_quantity')</th>
                <th>@lang('Garbage Quantity')</th>
                <th>@lang('Garbage Piece Quantity')</th>
                <th>@lang('State-Tax')</th>
                <th>@lang('City-Tax')</th>
                <th>@lang('lang_v1.return_subtotal')</th>
            </tr>
        </thead>
        <tbody>
            @php
              $total_before_tax = 0;

            @endphp
            @foreach($sell->sell_lines as $sellKey => $sell_line)

            @php

                    $totalTax += $sell_line->pos_line_tax_amount + $sell_line->city_tax_amount;


                $unit_name = $sell_line->product->unit->short_name;

                if(!empty($sell_line->sub_unit)) {
                $unit_name = $sell_line->sub_unit->short_name;
                }
            @endphp

            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  {{ $sell_line->product->name }}
                  @if( $sell_line->product->type == 'variable')
                    - {{ $sell_line->variations->product_variation->name}}
                    - {{ $sell_line->variations->name}}
                  @endif
                  - {{ $sell_line->product->sku }}
                  @if( isset($sell_line->sell_line_note) &&  $sell_line->sell_line_note)
                  <br>
                  {{ ucfirst($sell_line->sell_line_note) }}
                  @endif
                </td>
                <td><span class="display_currency" data-currency_symbol="true">{{ $sell_line->unit_price_inc_tax }}</span></td>
                <td>{{@format_quantity($sell_line->quantity_returned)}} {{$unit_name}}</td>
                <td>{{@format_quantity($sell_line->gar_box_return_qty)}} {{$unit_name}}</td>
                <td>{{@format_quantity($sell_line->gar_piece_return_qty)}} {{$unit_name}}</td>
                <td><span class="display_currency" data-currency_symbol="true"> {{$sell_line->pos_line_tax_amount}} </span></td>
                <td><span class="display_currency" data-currency_symbol="true"> {{$sell_line->city_tax_amount}}</span></td>

                <td>
                  @php
                    $line_total = ($sell_line->unit_price_inc_tax * $sell_line->quantity_returned) + ($sell_line->unit_price_inc_tax * $sell_line->gar_box_return_qty) + ($sell_line->gar_piece_return_qty * $sell_line->gar_piece_return_price);
                    $total_before_tax += $line_total ;
                  @endphp
                  <span class="display_currency" data-currency_symbol="true">{{$line_total}}</span>
                </td>
            </tr>
            @endforeach
          </tbody>
      </table>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-6 col-sm-offset-6 col-xs-6 col-xs-offset-6">
      <table class="table">
        <tr>
          <th>@lang('purchase.net_total_amount'): </th>
          <td></td>
          <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $total_before_tax }}</span></td>
        </tr>

        <tr>
          <th>@lang('lang_v1.return_discount'): </th>
          <td><b>(-)</b></td>
          <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->discount_amount }}</span></td>
        </tr>
        <tr>
          <th>@lang('Return Tax'): </th>
          <td><b>(+)</b></td>
          <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $totalTax }}</span></td>
        </tr>
        <!-- <tr>
          <th>@lang('lang_v1.total_return_tax'):</th>
          <td><b>(+)</b></td>
          <td class="text-right">
              @if(!empty($sell_taxes))
                @foreach($sell_taxes as $k => $v)
                  <strong><small>{{$k}}</small></strong> - <span class="display_currency pull-right" data-currency_symbol="true">{{ $v }}</span><br>
                @endforeach
              @else
              0.00
              @endif
            </td>
        </tr> -->
        <tr>
          <th>@lang('lang_v1.return_total'):</th>
          <td></td>
          <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ ($sell->final_total) }}</span></td>
        </tr>
        <tr>
          <th>Return Total Paid:</th>
          <td></td>
          <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ ($sell->amount_paid) }}</span></td>
        </tr>
        <tr>
          <th>Return Total Remaining:</th>
          <td></td>
          <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ ($sell->final_total-$sell->amount_paid) }}</span></td>
        </tr>
      </table>
    </div>
    <div class="col-sm-2">
        <strong>@lang('lang_v1.box_qty')</strong><br>
        <p class="well well-sm no-shadow bg-gray">
          {{$sell->box_qty ?? 0}}
        </p>
    </div>
</div>
<div class="row no-print">
        <div class="col-md-12">
          <strong>Credit Memo Log Detail</strong>
        </div>
        <div class="col-md-12">
          <table class="table table-condensed bg-gray">
                  <tr class="bg-green">
                <th>User Name</th>
                <th>Action</th>
                <th>Message</th>
                <th width="10%">Date And Time</th>
              </tr>
              @if(isset($CreditmemoActivityLog))
              @foreach($CreditmemoActivityLog as $log)
              <tr>
                <td>{{$log->first_name}}</td>
                @if($log->description == 'added')
                  <td>Added</td>
                @elseif($log->description == 'edited')
                  <td>Edited</td>
                @endif
                <td>{{$log->message}}</td>
                <td width="10%"> {{ Carbon\Carbon::parse($log->datetime)->format('d-m-Y G:i A') }}</td>
              </tr>
              @endforeach
              @endif
          </table>
        </div>
</div>
<div class="modal-footer">
    {{-- <a href="#" class="print-invoice btn btn-primary" data-href="{{action('SellReturnController@printInvoice', [$sell->id])}}"><i class="fa fa-print" aria-hidden="true"></i> @lang("messages.print")</a> --}}
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