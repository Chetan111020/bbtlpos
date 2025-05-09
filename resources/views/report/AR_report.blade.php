@extends('layouts.app')
@section('title', 'AR Report')

@section('content')
 <style>
        .button {
            -moz-appearance: none;
            -webkit-appearance: none;
            appearance: none;
            border: none;
            background: none;
            color: #0f1923;
            cursor: pointer;
            position: relative;
            padding: 8px;
            margin-bottom: 20px;
            text-transform: uppercase;
            font-weight: bold;
            transition: all .15s ease;
        }

        .button::before,
        .button::after {
            content: '';
            display: block;
            position: absolute;
            right: 0;
            left: 0;
            height: calc(50% - 5px);
            border: 1px solid #7D8082;
            transition: all .15s ease;
        }

        .button::before {
            top: 0;
            border-bottom-width: 0;
        }

        .button::after {
            bottom: 0;
            border-top-width: 0;
        }

        .button:active,
        .button:focus {
            outline: none;
        }

        .button:active::before,
        .button:active::after {
            right: 3px;
            left: 3px;
        }

        .button:active::before {
            top: 3px;
        }

        .button:active::after {
            bottom: 3px;
        }

        .button_lg {
            position: relative;
            display: block;
            padding: 10px 20px;
            color: #fff;
            background-color: #0f1923;
            overflow: hidden;
            box-shadow: inset 0px 0px 0px 1px transparent;
        }

        .button_lg::before {
            content: '';
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 2px;
            height: 2px;
            background-color: #0f1923;
        }

        .button_lg::after {
            content: '';
            display: block;
            position: absolute;
            right: 0;
            bottom: 0;
            width: 4px;
            height: 4px;
            background-color: #0f1923;
            transition: all .2s ease;
        }

        .button_sl {
            display: block;
            position: absolute;
            top: 0;
            bottom: -1px;
            left: -8px;
            width: 0;
            background-color: #ff4655;
            transform: skew(-15deg);
            transition: all .2s ease;
        }

        .button_text {
            position: relative;
        }

        .button:hover {
            color: #0f1923;
        }

        .button:hover .button_sl {
            width: calc(100% + 15px);
        }

        .button:hover .button_lg::after {
            background-color: #fff;
        }
    </style>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>AR Report</h1>

    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                    <div class="col-md-3">
                        <div class="form-group">

                            {!! Form::label('sell_list_filter_customer_id', __('contact.customer') . ':') !!}
                            {!! Form::select('sell_list_filter_customer_id', $customers, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('sr_id', __('report.sales_representative_name') . ':') !!}
                            {!! Form::select('sr_id', $users, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'sr_id',
                                'placeholder' => __('report.all_sales_representative'),
                            ]) !!}
                        </div>
                    </div>
                    <!--<br>-->
                    <!--<a class="btn btn-success pull-right" id="exportBtn" style="background-color: #22d15b; border-color: #22d15b;" href="{{ action('ReportController@downloadARExcel') }}">-->
                    <!--    Export To Excel-->
                    <!--</a>-->
                    <a role="button" href="{{ action('ReportController@downloadARExcel') }}" class="button btn-sm pull-right">
                        <span class="button_lg">
                            <span class="button_sl"></span>
                            <span class="button_text">Export To Excel</span>
                        </span>
                    </a>
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped ajax_view" style="width: 100%" id="AR_sell_table">
                            <thead>
                                <tr>

                                    <th>@lang('sale.customer_name')</th>
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
            $('#AR_sell_table tbody').on('click', 'tr', function(event) {

                const id = $(this).attr('data-contact_id');

                if (id) {
                    var href = "/reports/customer-AR-report/" + id;
                    window.open(href, '_blank');
                }
            });
             
            AR_sell_table = $('#AR_sell_table').DataTable({
                processing: true,
                serverSide: true,
                // aaSorting: [
                //     [0, 'desc']
                // ],
                "ajax": {
                    "url": "/reports/AR-report",
                    "data": function(d) {
                         d.customer_id = $('#sell_list_filter_customer_id').val();
                         d.sr_id = $('#sr_id').val();
                    }
                },
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                pageLength: -1, 
                columns: [

                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
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

            $('#sell_list_filter_customer_id, #sr_id').change(function() {
                AR_sell_table.ajax.reload();

            });

        });
    </script>
@endsection
