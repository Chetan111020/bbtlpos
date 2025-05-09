@extends('layouts.app')
@section('title', __('lang_v1.sell_return'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>@lang('Credit Memos')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('sell_list_filter_location_id',  __('purchase.business_location') . ':') !!}

                    {!! Form::select('sell_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all') ]); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('sell_list_filter_customer_id',  __('contact.customer') . ':') !!}
                    {!! Form::select('sell_list_filter_customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
                    {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('created_by',  __('report.user') . ':') !!}
                    {!! Form::select('created_by', $sales_representative, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                </div>
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Credit Memos')])
            @slot('tool')
                <div class="box-tools">
                    <a href="{{action('SellReturnController@sellReturn')}}" type="button" class="btn btn-block btn-primary">
                        <i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>
            @endslot
            @include('sell_return.partials.sell_return_list')
        @endcomponent
        <div class="modal fade payment_modal pay_due_modal" tabindex="-1" role="dialog" data-backdrop="static"
             aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" data-backdrop="static"
             aria-labelledby="gridSystemModalLabel">
        </div>
    </section>

    <!-- /.content -->
@stop
@section('javascript')
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    <script>

        function replaceTitleBeforePrint(section_id){
            var old_title = document.title;
            var new_title_element = document.getElementById("title_for_print");

            if(new_title_element){
                document.title = new_title_element.value;
                setTimeout(() => {
                    document.title = old_title;
                }, 3000);
            }
            else{
                console.log('Doc title unavailable');
            }
        }

        $(document).ready(function () {
            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function (start, end) {
                    $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                    sell_return_table.ajax.reload();
                }
            );
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function (ev, picker) {
                $('#sell_list_filter_date_range').val('');
                sell_return_table.ajax.reload();
            });

            sell_return_table = $('#sell_return_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [[1, 'desc']],
                "ajax": {
                    "url": "/sell-return",
                    "data": function (d) {
                        if ($('#sell_list_filter_date_range').val()) {
                            var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                            var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }

                        if ($('#sell_list_filter_location_id').length) {
                            d.location_id = $('#sell_list_filter_location_id').val();
                        }
                        d.customer_id = $('#sell_list_filter_customer_id').val();

                        if ($('#created_by').length) {
                            d.created_by = $('#created_by').val();
                        }
                    }
                },
                columnDefs: [{
                    "targets": [7, 8],
                    "orderable": false,
                    "searchable": false
                }],
                columns: [
                    {data: 'action', name: 'action'},
                    {data: 'transaction_date', name: 'transaction_date'},
                    {data: 'invoice_no', name: 'invoice_no'},
                    {data: 'name', name: 'contacts.name'},
                    {data: 'quantity_returned', name: 'tsl.quantity_returned'},
                    {data: 'business_location', name: 'bl.name'},
                    {data: 'payment_status', name: 'payment_status'},
                    {data: 'final_total', name: 'final_total'},
                    {data: 'payment_due', name: 'payment_due'}

                ],
                "fnDrawCallback": function (oSettings) {
                    var total_sell = sum_table_col($('#sell_return_table'), 'final_total');
                    $('#footer_sell_return_total').text(total_sell);

                    $('#footer_payment_status_count_sr').html(__sum_status_html($('#sell_return_table'), 'payment-status-label'));

                    var total_due = sum_table_col($('#sell_return_table'), 'payment_due');
                    $('#footer_total_due_sr').text(total_due);

                    __currency_convert_recursively($('#sell_return_table'));
                },
                createdRow: function (row, data, dataIndex) {
                    $(row).find('td:eq(2)').attr('class', 'clickable_td');
                }
            });
            $(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_customer_id, #created_by', function () {
                sell_return_table.ajax.reload();
            });
        })

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
                                sell_return_table.ajax.reload();
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
                                        sell_return_table.ajax.reload();
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

@endsection