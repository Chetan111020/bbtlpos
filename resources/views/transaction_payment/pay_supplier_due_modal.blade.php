
<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('TransactionPaymentController@postPayContactDue'), 'method' => 'post', 'id' => 'pay_contact_due_form', 'files' => true ]) !!}

    {!! Form::hidden("contact_id", $contact_details->contact_id); !!}
    <input type="hidden" value="{{$contact_details->balance}}" id="contact_balance_check">
    {!! Form::hidden("due_payment_type", $due_payment_type); !!}
    @php 
      $prev_url = url()->previous(); 
      $segments = explode('/', str_replace(''.url('').'', '', $prev_url));
    @endphp

    @if(isset($segments[1]))
    {!! Form::hidden('payment_location', $segments[1] ); !!}
    @endif
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'purchase.add_payment' )</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        @if($due_payment_type == 'purchase')
        <div class="col-md-6">
          <div class="">
              @if($contact_details->contact_type == 'supplier')
              <strong>Supplier: </strong><br>
              @elseif($contact_details->contact_type == 'customer')
              
              @endif
              @if($contact_details->contact_type == 'supplier')
              <select name="supplier_id" id="supplier_id" class="form-control" required>
                <option value="">Please Select</option>
                @foreach($supplier_list as $supplier)
                  <option value="{{$supplier->id}}"  {{ ($supplier->id == $contact_details->contact_id)  ? 'selected' : '' }}  >{{$supplier->supplier_business_name}}</option>
                @endforeach
              </select>
              @endif
                @if($contact_details->contact_type == 'customer')
                <strong>@lang('purchase.customer_name'): </strong>{{ $contact_details->name }}<br>
                {{-- <select name="supplier_id" id="supplier_id" class="form-control">
                  @foreach($customer_list as $customer)
                    <option value="{{$customer->id}}" {{ ($customer->id) == $contact_details->contact_id  ? 'selected' : '' }} >{{$customer->name}}</option>
                  @endforeach
                </select> --}}
                @endif

           {{-- <strong>@lang('business.business'): </strong>{{ $contact_details->supplier_business_name }}<br><br> --}}
          </div>
        </div>
        <div class="col-md-6">
          <div class="well">
            <strong>@lang('report.total_purchase'): </strong><span class="display_currency" data-currency_symbol="true">{{ $contact_details->total_purchase }}</span><br>
            <strong>@lang('contact.total_paid'): </strong><span class="display_currency" data-currency_symbol="true">{{ $contact_details->total_paid }}</span><br>
            <strong>@lang('contact.total_purchase_due'): </strong><span class="display_currency" data-currency_symbol="true">{{ $contact_details->total_purchase - $contact_details->total_paid }}</span><br>
             @if(!empty($contact_details->opening_balance) || $contact_details->opening_balance != '0.00')
                  <strong>@lang('lang_v1.opening_balance'): </strong>
                  <span class="display_currency" data-currency_symbol="true">
                  {{ $contact_details->opening_balance }}</span><br>
                  <strong>@lang('lang_v1.opening_balance_due'): </strong>
                  <span class="display_currency" data-currency_symbol="true">
                  {{ $ob_due }}</span>
              @endif
          </div>
        </div>
        @elseif($due_payment_type == 'purchase_return')
        <div class="col-md-6">
          <div class="well">
            <strong>@lang('purchase.supplier'): </strong>{{ $contact_details->name }}<br>
            <strong>@lang('business.business'): </strong>{{ $contact_details->supplier_business_name }}<br><br>
          </div>
        </div>
        <div class="col-md-6">
          <div class="well">
            <strong>@lang('lang_v1.total_purchase_return'): </strong><span class="display_currency" data-currency_symbol="true">{{ $contact_details->total_purchase_return }}</span><br>
            <strong>@lang('lang_v1.total_purchase_return_paid'): </strong><span class="display_currency" data-currency_symbol="true">{{ $contact_details->total_return_paid }}</span><br>
            <strong>@lang('lang_v1.total_purchase_return_due'): </strong><span class="display_currency" data-currency_symbol="true">{{ $contact_details->total_purchase_return - $contact_details->total_return_paid }}</span>
          </div>
        </div>
        @elseif(in_array($due_payment_type, ['sell']))
          <div class="col-md-6">
            <div class="">
              @if($contact_details->contact_type == 'supplier')
                <strong>Supplier: </strong><br>
              @elseif($contact_details->contact_type == 'customer')
                
              @endif

              @if($contact_details->contact_type == 'supplier')
              <select name="supplier_id" id="supplier_id" class="form-control">
                <option value="">Please Select</option>
                @foreach($supplier_list as $supplier)
                  <option value="{{$supplier->id}}"  {{ ($supplier->id == $contact_details->contact_id)  ? 'selected' : '' }}>{{$supplier->supplier_business_name}}</option>
                @endforeach
              </select>
              @endif
                @if($contact_details->contact_type == 'customer')
                <div class="well">
                <strong>@lang('sale.customer_name'): </strong>{{ $contact_details->name }}<br>
              </div>
                {{-- <select name="supplier_id" id="supplier_id" class="form-control">
                  @foreach($customer_list as $customer)
                    <option value="{{$customer->id}}" {{ ($customer->id) == $contact_details->contact_id  ? 'selected' : '' }} >{{$customer->name}}</option>
                  @endforeach
                </select> --}}
                @endif
            {{-- <strong>@lang('business.business'): </strong>{{ $contact_details->supplier_business_name }}<br><br> --}}
              {{-- <strong>@lang('sale.customer_name'): </strong>{{ $contact_details->name }}<br> --}}
              <br><br>
            </div>
          </div>
          <div class="col-md-6">
            <div class="well">
              <strong>@lang('report.total_sell'): </strong><span class="display_currency" data-currency_symbol="true">{{ $contact_details->total_invoice }}</span><br>
              <strong>@lang('contact.total_paid'): </strong><span class="display_currency" data-currency_symbol="true">{{ $contact_details->total_paid }}</span><br>
              <strong>@lang('contact.total_sale_due'): </strong><span class="display_currency" data-currency_symbol="true">{{ $contact_details->total_invoice - $contact_details->total_paid }}</span><br>
              @if(!empty($contact_details->opening_balance) || $contact_details->opening_balance != '0.00')
                  <strong>@lang('lang_v1.opening_balance'): </strong>
                  <span class="display_currency" data-currency_symbol="true">
                  {{ $contact_details->opening_balance }}</span><br>
                  <strong>@lang('lang_v1.opening_balance_due'): </strong>
                  <span class="display_currency" data-currency_symbol="true">
                  {{ $ob_due }}</span>
              @endif
            </div>
          </div>
         @elseif(in_array($due_payment_type, ['sell_return']))
         <div class="col-md-6">
          <div class="well">
            <strong>@lang('sale.customer_name'): </strong>{{ $contact_details->name }}<br>
              <br><br>
          </div>
        </div>
        <div class="col-md-6">
          <div class="well">
            <strong>@lang('lang_v1.total_sell_return'): </strong><span class="display_currency" data-currency_symbol="true">{{ $contact_details->total_sell_return }}</span><br>
            <strong>@lang('lang_v1.total_sell_return_paid'): </strong><span class="display_currency" data-currency_symbol="true">{{ $contact_details->total_return_paid }}</span><br>
            <strong>@lang('lang_v1.total_sell_return_due'): </strong><span class="display_currency" data-currency_symbol="true">{{ $contact_details->total_sell_return - $contact_details->total_return_paid }}</span>
          </div>
        </div>
        @endif
      </div>
      <div class="row payment_row">
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label("amount" , __('sale.amount') . ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fas fa-money-bill-alt"></i>
              </span>
              @if(in_array($due_payment_type, ['sell_return']))
              {!! Form::text("amount", @num_format($payment_line->amount), ['class' => 'form-control input_number', 'required', 'placeholder' => __('sale.amount'), 'data-rule-max-value' => $payment_line->amount, 'data-msg-max-value' => __('lang_v1.max_amount_to_be_paid_is', ['amount' => $amount_formated])]); !!}
              @elseif(in_array($due_payment_type, ['purchase_return']))
              {!! Form::text("amount", @num_format($payment_line->amount), ['class' => 'form-control input_number', 'required', 'placeholder' => __('sale.amount') ]); !!}
              @else
                {!! Form::text("amount", @num_format($payment_line->amount), ['class' => 'form-control input_number', 'required', 'placeholder' => __('sale.amount')]); !!}
              @endif
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label("paid_on" , __('lang_v1.paid_on') . ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </span>
              {!! Form::text('paid_on', @format_datetime($payment_line->paid_on), ['class' => 'form-control', 'readonly', 'required']); !!}
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label("method" , __('purchase.payment_method') . ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fas fa-money-bill-alt"></i>
              </span>
              {!! Form::select("method", $payment_types, $payment_line->method, ['class' => 'form-control select2 payment_types_dropdown', 'required', 'style' => 'width:100%;']); !!}
            </div>
          </div>
        </div>
        @if(!empty($ledger))
        <div class="col-md-12" id="sells_return_list_table" style="display:none;">
          <div class="row">
              <div class="col-md-6" id="sells_return_showDiv" style="display:none;float: right;">
                <div class="form-group">
                  <input type="hidden" value="" id="sell_return_transaction_id" name="return_transaction_ids">
                  {!! Form::label("method" , __('Total Credit Memo due amount ') . ':') !!}
                  <input type="text" placeholder="0.00" class="form-control" id="sell_return_total" readonly>
                </div>
              </div>
          </div>
          <table class="table table-striped" id="sells_return_table">
            <thead>
              <th><input type="checkbox" id="select-all-sells-return-row"></th>
              <th>Date</th>
              <th>Credit Memo No</th>
              <th>Amount</th>
              <th>Credit memo amount due</th>
            </thead>
            <tbody>
              @foreach($ledger as $list)
                @if($list['credit'] != 0)
                  <tr>
                    <td>
                      <input type="checkbox" class="searchTypeOfSellReturn" name="sell_return_check[{{ $list['transaction_id'] }}]" data-id="{{ $list['transaction_id'] }}" value="{{ $list['credit'] }}" onclick='CalcSearchTypeOfSellReturn()'>
                    </td>
                    <td>{{ $list['t_date'] }}</td>
                    <td>{{ $list['invoice_no'] }}</td>
                    <td>@format_currency($list['final_total'])</td>
                    <td><div class="input-group">
                        <input class="form-control input_number sell_return_amount" required="" placeholder="Amount" type="text" value="{{@num_format($list['credit'])}}" readonly>
                        </div> 
                    </td>
                  </tr>
                @endif
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
        @if(!empty($sells))
        <div class="col-md-12" id="sells_list_table">
          <div class="row">
              <div class="col-md-6" id="showDiv" style="display:none;float: right;">
                <div class="form-group">
                  <input type="hidden" value="" id="sell_transaction_id" name="transaction_ids">
                  {!! Form::label("method" , __('Total Sells due amount') . ':') !!}
                  <input type="text" placeholder="0.00" class="form-control" id="sell_total" readonly>
                </div>
              </div>
          </div>
          <table class="table table-striped" id="sells_table">
            <thead>
              <th><input type="checkbox" id="select-all-sells-row" class="hide"></th>
              <th>Date</th>
              <th>Invoice/Reference No</th>
              <th>Amount</th>
              <th>Amount due</th>
              <th>Apply Discount Amount</th>
              <th>Apply Amount</th>
              <th>Remaining Due Amount</th>
            </thead>
            <tbody>
              @foreach($sells as $list)
                @if($list['amount'] != 0)
                  <tr>
                    <td>
                      <input type="checkbox" class="searchTypeOfSell" name="sell_check[{{ $list['transaction_id'] }}]" data-id="{{ $list['transaction_id'] }}" value="{{$list['amount']}}">
                    </td>
                    <td>{{ $list['t_date'] }}</td>
                    <td style="text-align: center;">{{ $list['invoice_no'] }}</td>
                    <td style="text-align: center;">@format_currency($list['final_total'])</td>
                    <td style="text-align: center;">@format_currency($list['amount'])</td>
                    <td><div class="input-group">
                        <input class="form-control input_number discount_amount" name="discount[{{ $list['transaction_id'] }}]" required="" placeholder="Amount" value="0" type="text" data-id="{{ $list['transaction_id'] }}">
                        </div> 
                    </td>
                    <td><div class="input-group">
                        <input class="form-control input_number sell_amount" required="" placeholder="Amount" type="text" value="0" id="{{ $list['transaction_id'] }}" data-amount="{{$list['amount']}}">
                        </div> 
                    </td>
                    <td style="text-align: center;">
                      <span id="remain_due_amount_{{ $list['transaction_id'] }}" class="display_currency" data-currency_symbol="true">{{ $list['amount'] }}</span>
                    </td>
                  </tr>
                @endif
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
        
        <div class="clearfix"></div>
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label('document', __('purchase.attach_document') . ':') !!}
            {!! Form::file('document', ['accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
            <p class="help-block">
            @includeIf('components.document_help_text')</p>
          </div>
        </div>
        @if(!empty($accounts))
          <div class="col-md-3">
            <div class="form-group">
              {!! Form::label("account_id" , __('lang_v1.payment_account') . ':') !!}
              <div class="input-group">
                <span class="input-group-addon">
                  <i class="fas fa-money-bill-alt"></i>
                </span>
                {!! Form::select("account_id", $accounts, !empty($payment_line->account_id) ? $payment_line->account_id : '' , ['class' => 'form-control select2', 'id' => "account_id", 'style' => 'width:100%;']); !!}
              </div>
            </div>
          </div>
        @endif
        <div class="col-md-2">
            <div class="form-group">
                <br>
                <label>
                {!! Form::checkbox('add_advance', 1, false, ['class' => 'input-icheck' , 'onclick' => 'advancecheck()']); !!} <strong>Add Advance</strong>
                </label> @show_tooltip(__('Add whole amount in your advance balance'))
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group" style="display:none;">
                <br>
                <label>
                {!! Form::checkbox('payment_receipt', 1, false, ['class' => 'input-icheck']); !!} <strong>Email Payment Receipt</strong>
                </label> @show_tooltip(__('You can send email with payment receipt'))
            </div>
        </div>
        <div class="clearfix"></div>

          @include('transaction_payment.payment_type_details')
        <div class="col-md-12" id="cash_note">
            <div class="form-group">
                <label>Cash Amount:</label>
                <input type="text" class="form-control" name="cash_note">
            </div>
        </div>
        <div class="col-md-12">
          <div class="form-group">
            {!! Form::label("note", __('lang_v1.payment_note') . ':') !!}
            {!! Form::textarea("note", $payment_line->note, ['class' => 'form-control', 'rows' => 3]); !!}
          </div>
        </div>
      </div>
      </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary" id="saveandnew" style="display:none;">Save & New</button>
      <button type="submit" class="btn btn-primary" id="saveandclose">Save & Close</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
  $(document).on('change', '.payment_types_dropdown', function() {
    var payment_type = $("#method").val();
    if (payment_type == 'credit_memo') {
      resetTransactions(0);
      if(!$("input[name='add_advance']").is(":checked"))
      {
        $("#sells_list_table").show();
        $("#sells_return_list_table").show();
        /*if($("#sells_return_table").length)
        {
          $('#select-all-sells-row').hide();
        }*/
        CalcSearchTypeOfSellReturn();
      }
    }
    else
    {
      if(payment_type=='advance')
      {
        resetTransactions(0);
        var contact_balance = parseFloat($('#contact_balance_check').val());
        if(contact_balance < 0)
        {
          var set_amount = 0.00;
          $('#amount').val(set_amount.toFixed(2));
        }
        else
        {
          $('#amount').val(contact_balance.toFixed(2));
        }

        $('#amount').prop('readonly', true); 
      }
      $("#sells_return_list_table").hide();
      if(!$("input[name='add_advance']").is(":checked"))
      {
        $("#sells_list_table").show();
        /*if($("#sells_table").length)
        {
          $('#select-all-sells-row').show();
        }*/
        CalcSearchTypeOfSell();
      }
    }
    $('#cash_note').hide();
    if(payment_type == 'cash'){
        $('#cash_note').show();
    }
    else{
        $('#cash_note').hide();
    }
  });
  
  $('.searchTypeOfSell').click(function() {
      if($("#sells_table").length)
      {
        var transactionId = $(this).attr('data-id');
        var payment_type = $("#method").val();
        var final_top_amount = $('#amount').val();
        final_top_amount = final_top_amount.replace(/,/g, '');
        if(parseFloat(final_top_amount)>0)
        {

          /// check for total amount
          var check_total_amount = 0;
          if ($("input[name='sell_check["+transactionId+"]']").is(":checked")) 
          {
            $("#sells_table tbody tr input[type=checkbox]:checked").each(function() {
              var transactionIds = $(this).attr('data-id');
              if ($("input[name='sell_check["+transactionIds+"]']").is(":checked")) {
                val_total_amount = $("#"+transactionIds).val(); 
                if(parseFloat(val_total_amount)>0 && val_total_amount!="")
                {
                  val_total_amount = parseFloat(val_total_amount);
                  check_total_amount += val_total_amount;
                }
              } 
            });
          }
          /// check for total amount

          if(parseFloat(final_top_amount)>parseFloat(check_total_amount.toFixed(2)))
          {
            var sell_total_amount = 0;
            sell_total_amount = $('#sell_total').val();
            if(parseFloat(final_top_amount)>0)
            {
              if ($("input[name='sell_check["+transactionId+"]']").is(":checked")) 
              {
                val = $("input[name='sell_check["+transactionId+"]']").val(); 

                var difference_amount = parseFloat(final_top_amount) - parseFloat(sell_total_amount); 

                if(parseFloat(val)>=parseFloat(difference_amount))
                {
                  if(parseFloat(difference_amount)>0)
                  {
                    $("input[name='sell_check["+transactionId+"]']").val(parseFloat(difference_amount).toFixed(2)); 
                    $("input[name='discount["+transactionId+"]']").val(0); 
                    $("#"+transactionId).val(parseFloat(difference_amount).toFixed(2));
                  }
                }
              }
           }
            CalcSearchTypeOfSell();
            if($("input[name='sell_check["+transactionId+"]']").is(":checked")) 
            {
              $("#"+transactionId).prop('readonly', true).change();
            }
            else
            {
              $("#"+transactionId).prop('readonly', false).change();
            }
          }
          else
          {
            toastr.error("Amount is used between other transactions!!");
            $("input[name='sell_check["+transactionId+"]']").prop('checked', false).change();
          }
        }
        else
        {
          toastr.error("Please enter amount!!");
          resetTransactions(0);
        }
      }
  });

  function CalcSearchTypeOfSell()
  {
    var payment_type = $("#method").val();
    if(payment_type=='credit_memo')
    {
      $('#amount').prop('readonly', true);
    }
    else if(payment_type=='advance')
    {
      $('#amount').prop('readonly', true); 
    }
    else
    {
      $('#amount').prop('readonly', false);
    }

    if($("#sells_table").length)
    {
    var add = 0;  
    var transactionIds = [];

    $('#sell_total').val("");
    $('#sell_transaction_id').val("");
    //document.getElementById("showDiv").style.display = "none";
      $("#sells_table input[type=checkbox]").each(function() {
          var transactionId = $(this).attr('data-id');
          if ($("input[name='sell_check["+transactionId+"]']").is(":checked")) {
            val = $("input[name='sell_check["+transactionId+"]']").val(); 
            if(val!="")
            {
              val = parseFloat(val);
              ac_amt = $("#"+transactionId).attr('data-amount');
              $('span#remain_due_amount_'+transactionId).text(__currency_trans_from_en(parseFloat(ac_amt)-val, true));
              $("#"+transactionId).val(val.toFixed(2)); 
              add += val;
              transactionIds.push(transactionId);
            }
          } 
          else
          {
            //if(parseFloat($("#"+transactionId).val())<=0)
            //{
              ac_amt = $("#"+transactionId).attr('data-amount');
              $("input[name='sell_check["+transactionId+"]']").val(ac_amt);
              $('span#remain_due_amount_'+transactionId).text(__currency_trans_from_en(ac_amt, true));
              $("input[name='discount["+transactionId+"]']").val(0); 
              $("#"+transactionId).val(0);
            //}
          }
      });
      var price = add;
      //if(add != 0){
        //document.getElementById("showDiv").style.display = "block";
      //}
      console.log(transactionIds);
      

      //var discount_total = calcDiscountTotal();

      //var sell_final_total = parseFloat(price) + parseFloat(discount_total);
      //alert(price);

      $('#sell_total').val(parseFloat(price).toFixed(2));

      $('#sell_transaction_id').val(transactionIds.toString());
      if(payment_type == 'credit_memo' && $("#sells_return_table").length)
      {
        
      }
      else
      {
        //$('#amount').val(parseFloat(price).toFixed(2));
      }
    }
  }

  function calcDiscountTotal()
  {
    var add_discount_total = 0;  
    
    $("#sells_table tbody tr input[type=checkbox]:checked").each(function() {
        var transactionId = $(this).attr('data-id');
        if ($("input[name='sell_check["+transactionId+"]']").is(":checked")) {
          val = $("input[name='discount["+transactionId+"]']").val(); 
          if(parseFloat(val)>0 && val!="")
          {
            val = parseFloat(val);
            add_discount_total += val;
          }
        } 
    });

    return add_discount_total;
  }

  $(document).on('change','.sell_amount', function(){
    var transactionId = $(this).attr('id');
    var discount_amount = $("input[name='discount["+transactionId+"]']").val();
    var actual_due_amount = $(this).attr('data-amount');

    var actual_amount = parseFloat(actual_due_amount) - parseFloat(discount_amount);

    this.value = this.value.replace(/[^0-9\.]/g,'');
    if(this.value==0)
    {
        $(this).val(0);
    }
    if(this.value!="")
    {
      if(parseFloat(this.value)>parseFloat(actual_amount))
      {
        toastr.error("Amount should not be bigger than due amount minus discount amount");
        $(this).val(actual_amount);
        $("input[name='sell_check["+transactionId+"]']").val(actual_amount);
      }
      else
      {
        $("input[name='sell_check["+transactionId+"]']").val(this.value);
      }
      CalcSearchTypeOfSell();
    }
    else
    {
      $(this).val(0);
    }
  });

  $(document).on('change','.discount_amount', function(){
  var transactionId = $(this).attr('data-id');
  var actual_amount = $('#'+transactionId).attr('data-amount');
  var applied_amount = $('#'+transactionId).val();
  var required_discount_amount = parseFloat(actual_amount) - parseFloat(applied_amount);
  required_discount_amount = required_discount_amount.toFixed(2);
  this.value = this.value.replace(/[^0-9\.]/g,'');
  if(this.value==0)
  {
      $(this).val(0);
  }
  if(this.value!="")
  {
    if(parseFloat(this.value)>parseFloat(required_discount_amount))
    {
        toastr.error("Maximum Discount: "+required_discount_amount);
        var diff_amount = parseFloat(actual_amount) - parseFloat(applied_amount);
        $(this).val(0);
        //$("input[name='sell_check["+transactionId+"]']").val(actual_amount);
        //$('#'+transactionId).val(actual_amount);
        $('span#remain_due_amount_'+transactionId).text(__currency_trans_from_en(diff_amount, true));
    }
    else
    {
      var replaced_amount = parseFloat(actual_amount) - (parseFloat(applied_amount) +parseFloat(this.value));
      //$("input[name='sell_check["+transactionId+"]']").val(replaced_amount);
      //$('#'+transactionId).val(replaced_amount);
      $('span#remain_due_amount_'+transactionId).text(__currency_trans_from_en(replaced_amount, true));
    }
    //CalcSearchTypeOfSell();
  }
  else
  {
    $(this).val(0);
  }
});

  function CalcSearchTypeOfSellReturn()
  {
    if($("#sells_return_table").length)
    {
    $('#amount').prop('readonly', true).change();
    var add = 0;  
    var transactionIds = [];
    $('#sell_return_total').val("");
    $('#sell_return_transaction_id').val("");
    //document.getElementById("sells_return_showDiv").style.display = "none";
      $("#sells_return_table input[type=checkbox]:checked").each(function() {
          var transactionId = $(this).attr('data-id');
          if ($("input[name='sell_return_check["+transactionId+"]']").is(":checked")) {
            val = $("input[name='sell_return_check["+transactionId+"]']").val();      
            val = parseFloat(val);
            add += val;
            transactionIds.push(transactionId);
          } 
      });
      var price = add;
      if(add != 0){
        //document.getElementById("sells_return_showDiv").style.display = "block";
      }
      console.log(transactionIds);
      $('#sell_return_total').val(parseFloat(price).toFixed(2));
      $('#sell_return_transaction_id').val(transactionIds.toString());
      $('#amount').val(parseFloat(price).toFixed(2));

      if($("#sells_table").length && parseFloat(price) > 0)
      {
        //var evalueTransactionAmount = parseFloat(price).toFixed(2);
        //evaluateTransactions(evalueTransactionAmount);
      }

      if($("#sells_table").length && parseFloat(price) <= 0)
      {
        resetTransactions(0);
      }

    }
  }

  function resetTransactions(resetTransactionAmount=0)
  {
    if($("#sells_table").length)
    {
      $('#sell_total').val(0);
      $('#sell_transaction_id').val("");
      //document.getElementById("showDiv").style.display = "none";
      if(resetTransactionAmount == 0)
      {
        $("#sells_table tbody tr input[type=checkbox]").each(function() {
          var transactionId = $(this).attr('data-id');
          var act_val = $("#"+transactionId).attr('data-amount');
          act_val = parseFloat(act_val);
          $(this).prop('checked', false);
          $("input[name='sell_check["+transactionId+"]']").val(act_val.toFixed(2)); 
          $("input[name='discount["+transactionId+"]']").val(0);
          $('span#remain_due_amount_'+transactionId).text(__currency_trans_from_en(act_val, true));
          $("#"+transactionId).val(0); 
          //$("#"+transactionId).prop('readonly', false).change();
        });
        $(".sell_amount").prop('readonly', false);
      }
    }
  }
  function evaluateTransactions(evalueTransactionAmount=0)
  {
    var add = 0;  
    var transactionIds = [];

    resetTransactions(0);
    //document.getElementById("showDiv").style.display = "none";
    $("#sells_table tbody tr input[type=checkbox]").each(function() {
        var transactionId = $(this).attr('data-id');
        if(parseFloat(evalueTransactionAmount)>0 && transactionId!="")
        {
        val = $("#"+transactionId).attr('data-amount');
        if(parseFloat(val) <= parseFloat(evalueTransactionAmount))
        {
          $(this).prop('checked', true).change();
          if($("input[name='sell_check["+transactionId+"]']").is(":checked")) {
              if(parseFloat(val)>0 && val!="")
              {
                val = parseFloat(val);
                $("input[name='sell_check["+transactionId+"]']").val(val.toFixed(2)); 
                $("input[name='discount["+transactionId+"]']").val(0);
                $("#"+transactionId).val(val.toFixed(2)); 
                add += val;
                transactionIds.push(transactionId);
              }
            }
            evalueTransactionAmount = parseFloat(evalueTransactionAmount) - parseFloat(val);
        }
        else
        {
            $(this).prop('checked', true).change();
            if($("input[name='sell_check["+transactionId+"]']").is(":checked")) {
              if(parseFloat(evalueTransactionAmount)>0 && evalueTransactionAmount!="")
              {
                last_val = parseFloat(evalueTransactionAmount);
                $("input[name='sell_check["+transactionId+"]']").val(last_val.toFixed(2)); 
                $("input[name='discount["+transactionId+"]']").val(0);
                $("#"+transactionId).val(last_val.toFixed(2)); 
                add += last_val;
                transactionIds.push(transactionId);
              }
            }
            evalueTransactionAmount = 0;
        }
      }
    });
    var price = add;
    //if(add != 0){
      //document.getElementById("showDiv").style.display = "block";
    //}
    console.log(transactionIds);
    $('#sell_total').val(parseFloat(evalueTransactionAmount).toFixed(2));
    $('#sell_transaction_id').val(transactionIds.toString());
  }

/*  $(document).on('change','.sell_return_amount', function(){
    var transactionId = $(this).attr('id');
    var actual_amount = $(this).attr('data-amount');
    this.value = this.value.replace(/[^0-9\.]/g,'');
    if(this.value==0)
    {
        $(this).val(parseFloat(actual_amount).toFixed(2));
    }
    if(this.value!=0 && this.value!="")
    {
      $("input[name='sell_return_check["+transactionId+"]']").val(this.value);
      CalcSearchTypeOfSellReturn();
    }
    else
    {
      $(this).val(parseFloat(actual_amount).toFixed(2));
    }
  });*/

  function advancecheck()
  {
    var payment_type = $("#method").val();
     
      if($("input[name='add_advance']").is(":checked"))
      {
        if(payment_type == 'advance')
        {
          toastr.error("Please choose another method!!");
          $("input[name='add_advance']").prop('checked', false);
        }
        else
        {
          $("#sells_return_list_table").hide();
          $("#sells_list_table").hide();
          $('#amount').prop('readonly', false).change();
        }
      }
      else
      {
        if(payment_type == 'credit_memo') 
        {
           $("#sells_return_list_table").show();
           $("#sells_list_table").show();
        }
        else
        {
          $("#sells_return_list_table").hide();
          $("#sells_list_table").show(); 
        }
        CalcSearchTypeOfSell();
      }
 }

$(document).on('click', '#select-all-sells-row', function(e) {
    if (this.checked) {
        $("#sells_table input[type=checkbox]").each(function() {
         if (!this.checked) {
                    $(this)
                        .prop('checked', true)
                        .change();
                }  
         });
        CalcSearchTypeOfSell();
    } else {
        $("#sells_table input[type=checkbox]").each(function() {
         if (this.checked) {
                    $(this)
                        .prop('checked', false)
                        .change();
                }  
        });
        CalcSearchTypeOfSell();
    }
});

$(document).on('click', '#select-all-sells-return-row', function(e) {
    if (this.checked) {
        $("#sells_return_table input[type=checkbox]").each(function() {
         if (!this.checked) {
                    $(this)
                        .prop('checked', true)
                        .change();
                }  
         });
        CalcSearchTypeOfSellReturn();
    } else {
        $("#sells_return_table input[type=checkbox]").each(function() {
         if (this.checked) {
                    $(this)
                        .prop('checked', false)
                        .change();
                }  
        });
        CalcSearchTypeOfSellReturn();
    }
});

$(document).on('keyup','#amount', function(){
  this.value = this.value.replace(/[^0-9\.]/g,'');
  resetTransactions(0);
});

</script>