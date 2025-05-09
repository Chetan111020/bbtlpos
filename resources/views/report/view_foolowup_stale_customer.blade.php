@extends('layouts.app')
@section('title', 'Stale '. __('report.customer') . '  ' . __('report.reports'))
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css"
        integrity="sha512-xmGTNt20S0t62wHLmQec2DauG9T+owP9e6VU8GigI0anN7OXLip9i7IwEhelasml2osdxX71XcYm6BQunTQeQg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

@endsection
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Follow Up Stale {{ __('report.customer')}} {{ __('report.reports')}}</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('sr_id',  __('Sales Representative') . ':') !!}
                        {!! Form::select('sr_id', $users, null, ['class' => 'form-control select2 selectBox1', 'style' => 'width:100%', 'id' => 'sr_id', 'placeholder' => __('report.all_users')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('id', __( 'lang_v1.customer' ) . ':') !!}
                        {!! Form::select('id', $customers, null, ['class' => 'form-control select2 selectBox2', 'style' => 'width:100%', 'id' => 'contact_id', 'placeholder' => __('All')]); !!}
                    </div>
                </div>

                 <div class="col-md-3" style="display:none;">
                    <div class="form-group">
                         {!! Form::label('from_date', __('lang_v1.date') . ':') !!}
                         {!! Form::text('from_date', null , ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'stale_report_date_range', 'readonly']); !!}

                    </div>
                </div>

            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="stale_customer_report_tbl">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.customer')</th>
                            <th>@lang('lang_v1.mobile_number')</th>
                            <th>Address</th>
                            <th>@lang('report.total_sell')</th>
                            <th>Sales Representative</th>
                            <th>@lang('lang_v1.last_order_date')</th>
                            <th>@lang('lang_v1.days')</th>
                            <th>Due</th>
                            <th>Last Follow Up</th>
                            <th>New Follow Up</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 footer-total text-center">
                            <td><strong>@lang('sale.total'):</strong></td>
                            <td></td>
                            <td></td>
                            <td><span class="display_currency" id="footer_total_invoice" data-currency_symbol ="true"></span></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><span class="display_currency" id="footer_total_due" data-currency_symbol ="true"></span></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade" id="followupmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('smartcrm.followup.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Create a follow up</h4>
                </div>
                <div class="modal-body row">

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('contact_id', __('contact.customer') . ':*') !!}
                            {!! Form::select('contact_id', $customers, null, ['required', 'class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'contacts_id']); !!}
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Subject:*</label>
                            <input type="text" class="form-control" name="title" required/>
                        </div>
                    </div>



                    {{-- @if(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Administration#' . auth()->user()->business_id))
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('assigned_to',  __('report.user') . ':') !!}
                            {!! Form::select('assigned_to', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="assigned_to" value="{{ auth()->user()->id }}">
                    @endif --}}

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Next Schedule At:</label>
                            <input type="text" class="form-control" name="scheduled_at" id="scheduled_at"/>
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('status',  __('Status') . ':') !!}
                            {!! Form::select('status', $status, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                        </div>
                    </div>
                    <!--<div class="col-md-4">-->
                    <!--    <div class="form-group">-->
                    <!--        {!! Form::label('priority',  __('Priority') . ':') !!}-->
                    <!--        {!! Form::select('priority', $priorities, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}-->
                    <!--    </div>-->
                    <!--</div>-->
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('channel',  __('Channel') . ':') !!}
                            {!! Form::select('channel', $channel, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Conversation Notes:</label>
                            <textarea class="form-control" rows="3" name="notes"></textarea>
                        </div>
                    </div>

                    <div class="col-sm-12" style="display: flex;flex-direction: column;">
                        <label style="width:100%;">Tags:</label>
                        <input type="text" id="tags" name="tags" class="form-control" style="width:100%;" />
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('javascript')
 <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"
        integrity="sha512-9UR1ynHntZdqHnwXKTaOm1s6V9fExqejKvg5XMawEMToW4sSw+3jtLrYfZPijvnwnnE8Uol1O9BcAskoxgec+g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script type="text/javascript">
var tagInputEle = $('#tags');
            tagInputEle.tagsinput();
        $(document).ready(function(){

        // // Function to save selected options to localStorage
        // function saveSelectedOptions() {
        //     var selectedOption1 = $(".selectBox1").val();
        //     var selectedOption2 = $(".selectBox2").val();
        //     localStorage.setItem("selectedOption1", selectedOption1);
        //     localStorage.setItem("selectedOption2", selectedOption2);
        // }

        // // Function to load selected options from localStorage
        // function loadSelectedOptions() {
        //     var selectedOption1 = localStorage.getItem("selectedOption1");
        //     var selectedOption2 = localStorage.getItem("selectedOption2");
        //     if (selectedOption1) {
        //         $(".selectBox1").val(selectedOption1);
        //     }
        //     if (selectedOption2) {
        //         $(".selectBox2").val(selectedOption2);
        //     }
        // }

        // // Call loadSelectedOptions when the page loads
        // loadSelectedOptions();

        // // Call saveSelectedOptions when the selections change
        // $(".selectBox1, .selectBox2").change(saveSelectedOptions);

            dateRangeSettings.startDate = '01/01/2022';
            dateRangeSettings.endDate = moment().add(1, 'day');

            ranges['From 2022'] = [
                '01/01/2022',
                moment().add(1, 'day')
            ];

            if($('#stale_report_date_range').length == 1){
                $('#stale_report_date_range').daterangepicker(
                    dateRangeSettings,
                    function (start, end) {
                        $('#stale_report_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                        stale_customer_report_tbl.ajax.reload();
                    }
                );

                $('#stale_report_date_range').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    stale_customer_report_tbl.ajax.reload();
                });
            }

             stale_customer_report_tbl = $('#stale_customer_report_tbl').DataTable({
                processing: true,
                stateSave: true,
                aaSorting: [[6, 'desc']],
                // serverSide: true,
                ajax: {
                    url: '/reports/followup-stale-customer',
                    data: function(d) {
                        d.sr_id = $('#sr_id').val();
                        d.contact_id = $('#contact_id').val();
                        d.start_date = $('#stale_report_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.end_date = $('#stale_report_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'mobile', name: 'mobile' },
                    { data: 'address', name: 'address'},
                    { data: 'total_sell_return', name: 'total_sell_return' },
                    { data: 'user_name', name: 'user_name' },
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'days', name: 'days' },
                    { data: 'due', name: 'due' },
                    { data: 'followup_date', name: 'followup_date' },
                    {
                        data: null,
                        render: function(data, type, full, meta) {
                            return '<button class="btn btn-primary follow-up-button" data-toggle="modal" data-target="#followupmodal" data-contact-id="' + full.id + '">Create Follow Up</button>';
                        }
                    }

                ],
                fnDrawCallback: function(oSettings) {

                    var total_sell_return = sum_table_col($('#stale_customer_report_tbl'), 'total_invoice');
                    $('#footer_total_invoice').text(total_sell_return);

                     var total_due = sum_table_col($('#stale_customer_report_tbl'), 'total_due');
                    $('#footer_total_due').text(total_due);

                    __currency_convert_recursively($('#stale_customer_report_tbl'));
                },
            });
            $('#scheduled_at').datetimepicker().val('{{ date("m/d/Y H:i:s") }}');
            $('#contacts_id').select2();
            $('#stale_customer_report_tbl').on('click', '.follow-up-button', function () {
    var contactId = $(this).data('contact-id');
    $('#contacts_id').val(contactId).trigger('change'); // Set the selected option and trigger 'change' for select2
});
            if($('#stale_customer_report_tbl').length != 0){
                $('#sr_id, #contact_id, #stale_report_date_range').change(function() {
                    stale_customer_report_tbl.ajax.reload();
                });
            }

        })
    </script>
@endsection


