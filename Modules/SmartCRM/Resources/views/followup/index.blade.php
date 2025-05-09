@extends('layouts.app')
@section('title', 'Smart CRM | Follow Up')
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" integrity="sha512-xmGTNt20S0t62wHLmQec2DauG9T+owP9e6VU8GigI0anN7OXLip9i7IwEhelasml2osdxX71XcYm6BQunTQeQg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('content')

<section class="content-header no-print">
    <h1>Follow Up</h1>
</section>

<section class="content no-print">

    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('filter_contact_id',  __('contact.customer') . ':') !!}
                {!! Form::select('filter_contact_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('filter_assigned_to', 'Agent:') !!}
                {!! Form::select('filter_assigned_to', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('Status:') !!}
                <select class="form-control select2" id="filter_status">
                    <option value="">All</option>
                    @foreach ($status as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('filter_date_range', __('report.date_range') . ':(for Contacted At)') !!}
                {!! Form::text('filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
    @endcomponent

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status')['msg'] }}
        </div>
    @endif

    @component('components.widget', ['class' => 'box-primary', 'title' => __( '')])
        {{-- <button class="btn bg-purple" disabled>+ Add New</button> &nbsp; &nbsp; 🔐 We are working on it !!! --}}
        <button class="btn btn-primary" data-toggle="modal" data-target="#followupmodal">+ Add New</button>
        {{-- <button class="btn bg-purple">Bulk Create</button>
        <a href="{{ route('smartcrm.followup.queue',0) }}" class="btn bg-green pull-right">Start Processing</a> --}}
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __( '')])
        <table class="table table-bordered table-striped" id="followup_table">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Contacted At</th>
                    <th>Next Scheduled At</th>
                    <th>Customer</th>
                    <th style="width: 30%;">Subject</th>
                    <!--<th>Priority</th>-->
                    <th>Status</th>
                    <th>Channel</th>
                    <th>Agent</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    @endcomponent

 <!--Edit Modal-->
    <div class="modal fade" id="EditModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
    </div>
 <!--End Modal-->

 <!--View Modal-->
    <div class="modal fade bd-example-modal-lg" id="ViewModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
    </div>
 <!--View Modal-->

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
                                {!! Form::label('contact_id',  __('contact.customer') . ':*') !!}
                                {!! Form::select('contact_id', $customers, null, ['required', 'class' => 'form-control select2 contact_id', 'style' => 'width:100%', 'placeholder' => __('Please Select')]); !!}
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Subject:*</label>
                                <input type="text" class="form-control title" name="title" required/>
                            </div>
                        </div>

                        {{-- @if(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Administration#' . auth()->user()->business_id))
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('assigned_to', 'Agent:') !!}
                                {!! Form::select('assigned_to', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('Please Select')]); !!}
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
                            <input type="text" id="tags" name="tags" class="form-control tags-input" style="width:100%;" />
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

</section>
@endsection

@section('javascript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js" integrity="sha512-9UR1ynHntZdqHnwXKTaOm1s6V9fExqejKvg5XMawEMToW4sSw+3jtLrYfZPijvnwnnE8Uol1O9BcAskoxgec+g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
$(document).ready( function(){

    dateRangeSettings.startDate = moment().add(-6, 'day').format('MM/DD/YYYY');
    dateRangeSettings.endDate = moment().format('MM/DD/YYYY');

    ranges['Last Three Months'] = [
        moment().add(-3, 'month'),
        moment().add(1, 'day')
    ];

    ranges['From 2022'] = [
        '01/01/2022',
        moment().add(1, 'day')
    ];

    //Date range as a button
    $('#filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            followup_table.ajax.reload();
        }
    );
    $('#filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        followup_table.ajax.reload();
    });

    $('#scheduled_at').datetimepicker().val('{{ date("m/d/Y H:i:s") }}');

    $(document).on('click', 'a.edit-modal, button.edit-modal', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'html',
            success: function(result) {
                $('#EditModal').html(result).modal('show');
                var tagInputEle = $('.tags-input');
                tagInputEle.tagsinput();
            },
        });
    });
    $(document).on('click', 'a.view-modal', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'html',
            success: function(result) {
                $('#ViewModal').html(result).modal('show');
            },
        });
    });
    $(document).on('click', '.delete-followup', function(e){
        e.preventDefault();
        swal({
            title: LANG.sure,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                var href = $(this).attr('href');
                $.ajax({
                    method: "DELETE",
                    url: href,
                    dataType: "json",
                    success: function(result){
                        if(result.success == true){
                            toastr.success(result.msg);
                            followup_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });

    var tagInputEle = $('.tags-input');
    tagInputEle.tagsinput();

    followup_table = $('#followup_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[1, 'desc']],
        "ajax": {
            "url": "{{ route('smartcrm.followup.index') }}",
            "data": function ( d ) {
                d.assigned_to = $('#filter_assigned_to').val();
                d.contact_id = $('#filter_contact_id').val();
                d.fil_status = $('#filter_status').val();
                if($('#filter_date_range').val()) {
                    d.start_date = $('#filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    d.end_date = $('#filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                }
                d = __datatable_ajax_callback(d);
            }
        },
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false},
            { data: 'created_at', name: 'created_at'  },
            { data: 'scheduled_at', name: 'scheduled_at'  },
            { data: 'contact_id', name: 'contact_id'},
            { data: 'title', name: 'title'  },
            // { data: 'priority', name: 'priority'},
            { data: 'status', name: 'status'  },
            { data: 'channel', name: 'channel'  },
            { data: 'assigned_to', name: 'assigned_to'},
        ],
    });

    $(document).on('change', '#filter_assigned_to, #filter_contact_id, #filter_status',  function() {
        followup_table.ajax.reload();
    });
});
</script>
@endsection