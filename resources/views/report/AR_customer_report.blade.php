@extends('layouts.app')
@section('title', 'AR Report')

@section('content')
 <style>
        .button {
            -webkit-appearance: none;
            appearance: none;
            position: relative;
            border-width: 0;
            padding: 0 8px 12px;
            min-width: 10em;
            box-sizing: border-box;
            background: transparent;
            font: inherit;
            cursor: pointer;
        }

        .button-top {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 0;
            padding: 8px 16px;
            transform: translateY(0);
            text-align: center;
            color: #fff;
            text-shadow: 0 -1px rgba(0, 0, 0, .25);
            transition-property: transform;
            transition-duration: .2s;
            -webkit-user-select: none;
            user-select: none;
        }

        .button:active .button-top {
            transform: translateY(6px);
        }

        .button-top::after {
            content: '';
            position: absolute;
            z-index: -1;
            border-radius: 4px;
            width: 100%;
            height: 100%;
            box-sizing: content-box;
            background-image: radial-gradient(#3dcd9e, #369d8d);
            text-align: center;
            color: #fff;
            box-shadow: inset 0 0 0px 1px rgba(255, 255, 255, .2), 0 1px 2px 1px rgba(255, 255, 255, .2);
            transition-property: border-radius, padding, width, transform;
            transition-duration: .2s;
        }

        .button:active .button-top::after {
            border-radius: 6px;
            padding: 0 2px;
        }

        .button-bottom {
            position: absolute;
            z-index: -1;
            bottom: 4px;
            left: 4px;
            border-radius: 8px / 16px 16px 8px 8px;
            padding-top: 6px;
            width: calc(100% - 8px);
            height: calc(100% - 10px);
            box-sizing: content-box;
            background-color: #38a19d;
            background-image: radial-gradient(4px 8px at 4px calc(100% - 8px), rgba(255, 255, 255, .25), transparent), radial-gradient(4px 8px at calc(100% - 4px) calc(100% - 8px), rgba(255, 255, 255, .25), transparent), radial-gradient(16px at -4px 0, white, transparent), radial-gradient(16px at calc(100% + 4px) 0, white, transparent);
            box-shadow: 0px 2px 3px 0px rgba(0, 0, 0, 0.5), inset 0 -1px 3px 3px rgba(0, 0, 0, .4);
            transition-property: border-radius, padding-top;
            transition-duration: .2s;
        }

        .button:active .button-bottom {
            border-radius: 10px 10px 8px 8px / 8px;
            padding-top: 0;
        }

        .button-base {
            position: absolute;
            z-index: -2;
            top: 4px;
            left: 0;
            border-radius: 12px;
            width: 100%;
            height: calc(100% - 4px);
            background-color: rgba(0, 0, 0, .15);
            box-shadow: 0 1px 1px 0 black, inset 0 2px 2px rgba(0, 0, 0, .25);
        }
    </style>
    <!-- Content Header (Page header) -->
    

    <!-- Main content -->
    <section class="content no-print">

        <div class="row no-print">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
                            {!! Form::text('from_date', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control',
                                'id' => 'sell_list_filter_date_range',
                                'readonly',
                            ]) !!}

                        </div>
                    </div>
                      <a href="{{ action('ReportController@GetARreports') }}" role="button" type="button"
                        class="button pull-right btn-sm">
                        <div class="button-top">Back to Main list</div>
                        <div class="button-bottom"></div>
                        <div class="button-base"></div>
                    </a>
                @endcomponent
            </div>
        </div>
<section class="content-header" style="padding-top: 0;">
        <h1>{{ $contact->name }}</h1>

    </section>
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped ajax_view" style="width: 100%" id="AR_sell_table">
                            <thead>
                                <tr>
                                    <th>@lang('messages.date')</th>
                                    <th>@lang('sale.invoice_no')</th>
                                    {{-- <th>@lang('sale.customer_name')</th> --}}
                                    <th>@lang('sale.total_amount')</th>
                                    <th>@lang('sale.total_paid')</th>
                                    <th>1-15</th>
                                    <th>16-30</th>
                                    <th>31-45</th>
                                    <th>46-60</th>
                                    <th>61+</th>
                                    <th>@lang('Balance Due')</th>
                                    <th>Sale Rep</th>
                                </tr>
                            </thead>
                            <tbody></tbody>


                        </table>
                    </div>
                @endcomponent


            </div>
        </div>
    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var customer_id = "{{ $id }}";
 ranges['Start to end'] = [
                moment([2021, 0, 1]),
                moment().add(1, 'month').endOf('month')
            ];
            if ($('#sell_list_filter_date_range').length == 1) {
                // $('#sell_list_filter_date_range').daterangepicker(
                //     dateRangeSettings,
                //     function(start, end) {
                //         $('#sell_list_filter_date_range').val(start.format(moment_date_format) +
                //             ' ~ ' + end.format(moment_date_format));
                //         AR_sell_table.ajax.reload();
                //     }
                // );
                $('#sell_list_filter_date_range').daterangepicker({
                    ranges: ranges,
                    autoUpdateInput: true,
                    startDate: moment([2021, 0, 1]),
                    endDate: moment().add(1, 'month').endOf('month'),
                    locale: {
                        format: moment_date_format
                    }
                    
                });

                $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    AR_sell_table.ajax.reload();
                });
            }

            AR_sell_table = $('#AR_sell_table').DataTable({
                processing: true,
                serverSide: true,
                // aaSorting: [
                //     [0, 'desc']
                // ],
                "ajax": {
                    "url": "/reports/customer-AR-report/" + customer_id,
                    "data": function(d) {
                        if ($('#sell_list_filter_date_range').val()) {
                            d.start_date = $('#sell_list_filter_date_range').data(
                                'daterangepicker').startDate.format('YYYY-MM-DD');
                            d.end_date = $('#sell_list_filter_date_range').data(
                                'daterangepicker').endDate.format('YYYY-MM-DD');
                            d.sr_id = $('#sr_id').val();
                        }


                    }
                },
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                pageLength: -1, 
                 
                columns: [{
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    // {
                    //     data: 'customer_name',
                    //     name: 'contacts.name'
                    // },
                    {
                        data: 'final_total',
                        name: 'final_total'
                    },
                    {
                        data: 'total_paid',
                        name: 'total_paid',
                        searchable: false
                    },

                    {
                        data: 'days_1_15',
                        name: 'days_1_15',

                    },
                    {
                        data: 'days_16_30',
                        name: 'days_16_30',

                    },
                    {
                        data: 'days_31_45',
                        name: 'days_31_45',

                    },
                    {
                        data: 'days_46_60',
                        name: 'days_46_60',

                    },
                    {
                        data: 'days_61_plus',
                        name: 'days_61_plus',

                    },
                    {
                        data: 'total_remaining',
                        name: 'total_remaining'
                    },
                    {
                        data: 'user_name',
                        name: 'user_name'
                    },

                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#AR_sell_table'));
                },


            });

            $('#sell_list_filter_date_range').change(function() {
                AR_sell_table.ajax.reload();

            });

        });
    </script>
@endsection
