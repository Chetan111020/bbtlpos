<!-- app css -->
<div class="col-md-12 col-sm-12">
    <a href="{{action('CombinedPurchaseReturnController@create')}}">
    <button type="button" class="btn btn-sm btn-primary pull-right" style="margin-right : 2%;">
        @lang('messages.add')&nbsp;
        <i class="fa fa-plus"></i>
    </button>
    </a>
</div>
<div class="col-md-12 col-sm-12 ">
    <div class="table-responsive">
        <table class="table table-striped " id="purchase_return_table">
            <thead>
                <tr class="row-border blue-heading">
                    <th>@lang('messages.action')</th>
                    <th>@lang('messages.date')</th>
                    <th>@lang('Credit Memo No')</th>
                    <!--<th>@lang('lang_v1.parent_purchase')</th>-->
                    <th>@lang('purchase.location')</th>
                    <th>@lang('purchase.supplier')</th>
                    <th>@lang('purchase.payment_status')</th>
                    <th>@lang('purchase.grand_total')</th>
                    <th>@lang('Payment Due')</th>
                </tr>
            </thead>
            <tbody>
                @php $total_due = 0; $final_total = 0; @endphp
                @foreach($purchase_return as  $data)
                    <tr>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                                    data-toggle="dropdown" aria-expanded="false">Action
                                    <span class="caret"></span><span class="sr-only">
                                        Toggle Dropdown
                                    </span>
                                </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                <li><a class="btn-modal" href="#" data-container=".view_modal" data-href="{{action('PurchaseReturnController@show', [$data->id])}}" class="view_purchase_return" ><i class="fa fa-eye"></i>View</a></li>
                                @if(!empty($data->return_parent_id))
                                <li><a href="{{action('PurchaseReturnController@add', [$data->return_parent_id])}}" ><i class="fa fa-edit" aria-hidden="true"></i> @lang("messages.edit")</a></li>
                                @else
                                <li><a href="{{action('CombinedPurchaseReturnController@edit', [$data->id])}}" ><i class="fa fa-edit" aria-hidden="true"></i> @lang("messages.edit")</a></li>
                                @endif

                                @if ($data->payment_status != 'paid')
                                    <li><a href="#" data-href="{{route('transaction.markpaid', $data->id)}}" class="btn-one-way"><i class="fas fa-check" aria-hidden="true"></i> Mark as Paid</a></li>
                                @else
                                    <li><a href="#" data-href="{{route('transaction.markdue', $data->id)}}" class="btn-one-way"><i class="fas fa-exclamation" aria-hidden="true"></i> Mark as Due</a></li>
                                @endif

                                <li><a href="{{action('PurchaseReturnController@destroy', [$data->id])}}" class="delete_purchase_return" ><i class="fa fa-trash" aria-hidden="true"></i> @lang("messages.delete")</a></li>

                                <li><a class="print-invoice" href="#" data-href="{{action('PurchaseReturnController@printInvoice', [$data->id])}}" class="print_purchase_return" ><i class="fa fa-print"></i>Print</a></li>

                                @if($data->payment_status != "paid")
                                    <li><a href="{{action('TransactionPaymentController@addPayment', [$data->id])}}" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a></li>
                                @endif

                                <li><a href="{{action('TransactionPaymentController@show', [$data->id])}}" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.view_payments")</a></li>

                                <li><a href="#" data-href="{{action('NotificationController@getTemplate', ['transaction_id' => $data->id,'template_for' => 'vendor_credit_memo'])}}" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i>Credit Memo Email</a></li>
                            </ul>
                            </div>
                        </td>
                        <td>{{@format_datetime($data->transaction_date)}}</td>
                        <td>{{$data->ref_no}}</td>
                        <!--<td>
                        @if (!empty($data->parent_purchase)) {
                            <a href="{{action('PurchaseController@show', [$data->return_parent_id])}}" class="btn-modal" data-container=".view_modal">{{$data->parent_purchase}}</a>
                        @endif
                        </td>-->
                        <td>{{$data->location->name}}</td>
                        <td>{{$data->contact->name}}</td>
                        <td><a href="{{ action('TransactionPaymentController@show', [$data->id])}}" class="view_payment_modal payment-status payment-status-label" data-orig-value="{{$data->payment_status}}" data-status-name="@if($data->payment_status != 'paid'){{__('lang_v1.' . $data->payment_status)}}@else{{__('lang_v1.received')}}@endif"><span class="label @payment_status($data->payment_status)">@if($data->payment_status != "paid"){{__('lang_v1.' . $data->payment_status)}} @else {{__("lang_v1.received")}} @endif
                        </span></a></td>
                        <td>$ {{number_format($data->final_total,2)}}</td>
                        @php $due = $data->final_total - $data->amount_paid; @endphp
                        @php
                            $final_total = $final_total + $data->final_total;
                            $total_due = $total_due + $due;
                        @endphp
                        <td>$ {{number_format($due,2)}}</td>

                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray font-17 text-center footer-total">
                    <td colspan="5"><strong>@lang('sale.total'):</strong></td>
                    <td id="footer_payment_status_count">
                        @if($due_payment > 0)
                            <span>Due- {{$due_payment}}</span> <br>
                        @endif
                        @if($partial_payment > 0)
                            <span>Partial- {{$partial_payment}}</span><br>
                        @endif
                        @if($paid_payment > 0)
                            <span>Paid- {{$paid_payment}}</span> </td>
                        @endif</td>
                    <td><span class="display_currency" id="footer_purchase_return_total" data-currency_symbol ="true">{{$final_total}}</span></td>
                    <td><span class="display_currency" id="footer_total_due" data-currency_symbol ="true">{{$total_due}}</span></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
<script>
    /*$(document).on('click', 'a.delete_purchase_return', function (e) {
        e.preventDefault();
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).attr('href');
                var data = $(this).serialize();

                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    data: data,
                    success: function (result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            location.reload(true);
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });*/
    // added by developer 1 for delete vendor credit memo
    $(document).on('click', 'a.delete_purchase_return', function(e) {
        e.preventDefault();
        href = $(this).attr('href');
        var config = {
                        title: LANG.sure,
                        content:{
                            element:"textarea",
                                attributes: {
                                    id: "delete_reason",
                                    name: "delete_reason",
                                    placeholder: "Reason",
                                    className: "swal-content__textarea",
                                    rows:6
                             },
                        },
                        icon: 'warning',
                        //buttons: true,
                        dangerMode: true,
                        buttons: {
                          cancel: {
                            text: 'Cancel',
                            visible: true
                          },
                          confirm: {
                            text: 'Submit',
                            closeModal: false
                          }
                        }
                    };

        (function trick() {
            swal(config).then(willDelete => {
                if (willDelete) {
                    content = $('#delete_reason').val();
                    if($.trim(content)!="")
                    {
                        //var data = $(this).serialize();
                        var data = { reason: content };
                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            data: data,
                            dataType: 'json',
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    location.reload(true);
                                } else {
                                    toastr.error(result.msg);
                                }
                                swal.close();
                            },
                            error: function (err) {
                              swal('Error', 'Unfortunately, an error occurred. Please try again.', 'error');
                            }
                        });
                    } else {
                        //alert("asdf");
                        //swal('Validate', 'Please enter valid reason', 'error');
                        //alert(href);
                        alert('Please enter valid reason');
                        swal.stopLoading();
                        trick();
                    }
                }
            })
        })();
    });
</script>
