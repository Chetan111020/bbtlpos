<!-- app css -->
<div class="col-md-12 col-sm-12">
	<a href="/sells-return{{ !empty($customer_id) ? '?contact='.$customer_id : '' }}">
	<button type="button" class="btn btn-sm btn-primary pull-right" style="margin-right : 2%;" ">
		@lang('messages.add')&nbsp;
		<i class="fa fa-plus"></i>
	</button>
	</a>
</div>
<div class="col-md-12 col-sm-12 ">
    <div class="table-responsive">
        <table class="table table-striped " id="sell_return_table">
        	<thead>
        		<tr class="row-border blue-heading">
        		    <th>@lang('messages.action')</th>
        			<th>@lang('messages.date')</th>
                    <th>@lang('Credit Memo No')</th>
                    <th>@lang('sale.customer_name')</th>
                    <th>@lang('Returned Quantity')</th>
                    <th>@lang('sale.location')</th>
                    <th>@lang('purchase.payment_status')</th>
                    <th>@lang('sale.total_amount')</th>
                    <th>@lang('purchase.payment_due')</th>

        		</tr>
        	</thead>
        	<tbody>

        		@foreach($sell_return as  $data)
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
                                <li><a href="#" class="btn-modal" data-container=".view_modal" data-href="{{action('SellReturnController@show', [$data->id])}}"><i class="fas fa-eye" aria-hidden="true"></i> @lang("messages.view")</a></li>
                                <li><a href="{{action('SellReturnController@add', [$data->id])}}" ><i class="fa fa-edit" aria-hidden="true"></i> @lang("messages.edit")</a></li>
                             	<li><a href="{{action('SellReturnController@destroy', [$data->id])}}" class="delete_sell_return" ><i class="fa fa-trash" aria-hidden="true"></i> @lang("messages.delete")</a></li>
                                <li><a href="#" class="print-invoice" data-href="{{action('SellReturnController@printInvoice', [$data->id])}}"><i class="fa fa-print" aria-hidden="true"></i> @lang("messages.print")</a></li>

                                @if($data->payment_status != "paid")
                                    <li><a href="{{action('TransactionPaymentController@addPayment', [$data->id])}}" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a></li>
                                @endif

                                <li><a href="#" data-href="{{ action('NotificationController@getTemplate', ['transaction_id' => $data->id,'template_for' => 'sell_return'])}}" class="btn-modal" data-container=".view_modal"><i class="fa fa-envelope" aria-hidden="true"></i>Credit Memo Notification</a></li>

                                <li><a href="{{action('TransactionPaymentController@show', [$data->id])}}" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.view_payments")</a></li>
                            </ul>
                            </div>
        				</td>
        				<td>{{@format_datetime($data->transaction_date)}}</td>
        				<td><button type="button" class="btn btn-link btn-modal" data-container=".view_modal" data-href="{{action('SellReturnController@show', [$data->parent_sale_id])}}">{{$data->invoice_no}}</button></td>
        				<td>{{$data->contact->name}}</td>
        				@php $quantity_returned = $data->sell_lines->sum('quantity_returned') + $data->sell_lines->sum('gar_box_return_qty') @endphp
        				<td>{{@num_format($quantity_returned)}}</td>
        				<td>{{$data->location->name}}</td>
        				<td><a href="{{action('TransactionPaymentController@show', [$data->id])}}" class="view_payment_modal payment-status payment-status-label" data-orig-value="{{$data->payment_status}}" data-status-name="{{$data->payment_status}}"><span class="label @payment_status($data->payment_status)">{{$data->payment_status}}</span></a></td>
        				<td>$ {{number_format($data->final_total,2)}}</td>
        				@php $due = $data->final_total - $data->amount_paid; @endphp
        				<td>$ {{$due}}</td>

        			</tr>
        		@endforeach
        	</tbody>
        	<tfoot>
        		<tr class="bg-gray font-17 text-center footer-total">
        			<td colspan="5"><strong>@lang('sale.total'):</strong></td>
        			<td id="footer_payment_status_count_sr">
        				@if($due_payment > 0)
        					<span>Due- {{$due_payment}}</span> <br>
        				@endif
        				@if($partial_payment > 0)
        					<span>Partial- {{$partial_payment}}</span><br>
        				@endif
        				@if($paid_payment > 0)
        					<span>Paid- {{$paid_payment}}</span> </td>
        				@endif
        			<td><span class="display_currency" id="footer_sell_return_total" data-currency_symbol ="true">{{$total_paid}}</span></td>
        			<td><span class="display_currency" id="footer_total_due_sr" data-currency_symbol ="true">{{$total_due}}</span></td>
        			<td></td>
        			<td></td>
        		</tr>
        	</tfoot>
        </table>
    </div>
</div>

<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
<script>
	/*$(document).on('click', 'a.delete_sell_return', function (e) {
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
						} else {
							toastr.error(result.msg);
						}
					},
				});
			}
		});
	});*/

	// added by developer 1 for delete credit memo
    $(document).on('click', 'a.delete_sell_return', function(e) {
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
                                    //sell_return_table.ajax.reload();
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
