@extends('layouts.app')
@section('title', 'Smart Sync | Products')
@section('css')
<link rel="stylesheet" href="/fonts/google-fonts/google-fonts.css" />
<style>
    @import url('https://fonts.googleapis.com/css?family=Poppins');

    .sidebar-menu>li>a>i{
        width: 25px;
        font-size: 16px;
        text-align: center;
        margin-left: -12px;
        margin-right: 5px;
    }

    .myshadow{
        box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.2);
        /* border-radius: 10px 0 10px 0; */
        overflow: hidden;
    }

    .loader {
        width: 0%;
        height: 5px;
        display: inline-block;
        position: absolute;
        bottom: 0;
        left: 0;
        background: #00ff00;
        overflow: hidden;
        transition: width 1s;
    }
    .loader-after {
        content: '';
        width: 192px;
        height: 5px;
        background:linear-gradient(90deg, transparent, rgb(187 255 187), transparent);
        position: absolute;
        bottom: 0;
        left: 0;
        box-sizing: border-box;
        animation: animloader 1.5s linear infinite;
    }

    @keyframes animloader {
    0% {
        left: 0;
        transform: translateX(-100%);
        width:250px;
    }
    50%{
        width:400px;
    }
    100% {
        left: 100%;
        width:250px;
        transform: translateX(0%);
    }
    }

    .course {
        background-color: #fff;
        /* border-radius: 10px 0 10px 0; */
        box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.2);
        display: flex;
        width: 100%;
        margin: 30px 0;
        overflow: hidden;
    }

    .course h6 {
        opacity: 0.6;
        margin: 0;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .course h2 {
        letter-spacing: 1px;
        margin: 10px 0;
    }

    .course-preview {
        background-color: #2A265F;
        color: #fff;
        padding: 30px;
        max-width: 250px;
    }

    .course-preview a {
        color: #fff;
        display: inline-block;
        font-size: 12px;
        opacity: 0.6;
        margin-top: 30px;
        text-decoration: none;
    }

    .course-info {
        padding: 30px;
        position: relative;
        width: 100%;
    }

    .progress-container {
        position: absolute;
        top: 30px;
        right: 30px;
        text-align: right;
        width: 150px;
    }

    .progress {
        background-color: #ddd;
        height: 5px;
        width: 100%;
    }

    .progress-after {
        background-color: #2A265F;
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 5px;
        width: 0%;
        transition: width 1s;
    }

    .progress-text {
        font-size: 12px;
        opacity: 0.6;
        letter-spacing: 1px;
    }
</style>
@endsection

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Products Sync
        </h1>
    </section>

<div class="" style="padding-bottom:10px;">

    <div class="col-lg-6">
        <div class="course">
            <div class="course-preview" style="width:40%">
                <h6 class="text-white">Overview</h6>
                <h2 class="text-white">ERP<br/>Products</h2>
                <a href="/products" target="_blank">View all products <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="course-info">
                <div class="progress-container">
                    <div class="progress">
                        <div id="erp_perc_bar" class="progress-after"></div>
                    </div>
                    <span class="progress-text">
                        <span id="erp_perc">0%</span> Sync enabled
                    </span>
                </div>
                <h6>{{ config('business-info.erp_name') }}</h6>
                <h2><span id="total_p">0</span> <small>Active</small></h2>
                <h2><span id="total_sync_p">0</span> <small>Sync Enabled</small></h2>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="course">
            <div class="course-preview" style="width:40%">
                <h6 class="text-white">Overview</h6>
                <h2 class="text-white">Website Products</h2>
                <a href="{{ config('business-info.website_url') }}" target="_blank">Visit website <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="course-info">
                <div class="progress-container">
                    <div class="progress">
                        <div id="web_perc_bar" class="progress-after"></div>
                    </div>
                    <span class="progress-text">
                        <span id="web_perc">0%</span> Active
                    </span>
                </div>
                <h6>{{ config('business-info.name') }}</h6>
                <h2><span id="total_web_p">0</span> <small>Synced</small></h2>
                <h2><span id="web_missing_p">0</span> <small>Not in ERP</small></h2>
            </div>
        </div>
    </div>

</div>
<div class="col-sm-12" style="margin-bottom:15px;">
    <div class="col-sm-12 bg-white" style="margin-top:15px;padding:30px;">
        <span style="display:flex;width:100%;justify-content:space-between;">
            <span>Current task details</span>
            <div>
                <span id="queue" class="badge" style="border-radius:0;border:solid #a1e1ff 2px;color:#37bfff;background:transparent;"></span>
                <span id="status" class="badge" style="border-radius:0;"></span>
            </div>
        </span>
        <h3 style="display:flex;width:100%;justify-content:space-between;">
            <span id="subject_name"></span>
            <span id="progress"></span>
        </h3>
        <h5 id="user_name"></h5>
        <br/>
        <h5 style="display:flex;width:100%;justify-content:space-between;">
            <span id="display_msg"></span>
            <button id="btn_abort" data-task-id="0" class="btn btn-xs btn-danger btn-abort myshadow" style="border-radius:0;display:none;">Abort task</button>
        </h5>
        <span class="loader">
            <span class="loader-after"></span>
        </span>
    </div>

    <div class="col-sm-12 bg-white" style="margin:15px 0;padding:30px;">
        <strong style="display:inline-block;margin-bottom:15px;">Upcoming scheduled tasks</strong>
        <div id="task_table_container">
            <table id="task_table" class="table">
                <thead>
                    <tr>
                        <th>Scheduled At</th>
                        <th>Subject Type</th>
                        <th>Operation</th>
                        <th>Status</th>
                        <th>Added By</th>
                        <th style="width:100px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse  ($smart_tasks as $item)
                    <tr>
                        <td>{{ date('m-d-Y g:i a',strtotime($item->scheduled_at)) }}</td>
                        <td>{{ str_replace('SmartSync:', '', $item->subject_type) }}</td>
                        <td>{{ ucfirst($item->subject_params) }}</td>
                        <td>{{ ucfirst($item->status) }}</td>
                        <td>{{ $item->first_name }}</td>
                        <td align="right">
                            @if ($item->created_by == 6 && auth()->user()->id != 6)
                            @else
                            <button class="btn btn-xs btn-danger btn-abort myshadow" data-task-id="{{ $item->id }}" style="border-radius: 0;">Abort Task</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td><i class="text-muted">No task scheduled</i></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-sm-12 bg-white" style="margin-top: 15px;padding:30px;">
        <strong>Website Sync Details</strong>
    </div>

    <div class="col-sm-3 bg-white" style="padding:0;display:flex;flex-direction:column;">
        <svg xmlns="http://www.w3.org/2000/svg" style="margin: 30px auto;width:50px;color:11cdef;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="btn-link btn-show-list" style="cursor: pointer;" data-show="new">
            <h4 style="text-align: center;"><span id="create_p">0</span></h4>
            <strong><h4 style="text-align: center;">Products To Be Created</h4></strong>
        </span>
        <button class="btn-show-save btn btn-info" data-sync="new" data-toggle="modal" data-target="#myModal" style="border-radius:0;margin-top:10px;">Add To Website</button>
    </div>

    <div class="col-sm-3 bg-white" style="padding:0;display:flex;flex-direction:column;">
        {{-- <svg xmlns="http://www.w3.org/2000/svg" style="margin: 30px auto;width:50px;color:aquamarine;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11.25l-3-3m0 0l-3 3m3-3v7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg> --}}
        <svg xmlns="http://www.w3.org/2000/svg" style="margin: 30px auto;width:50px;color:aquamarine;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
        <span class="btn-link btn-show-list" style="cursor: pointer;" data-show="all">
            <h4 style="text-align: center;"><span id="sync_p">0</span></h4>
            <strong><h4 style="text-align: center;">Products To Be Synced</h4></strong>
        </span>
        <button class="btn-show-save btn" data-sync="all" data-toggle="modal" data-target="#myModal" style="background: aquamarine;border-radius:0;margin-top:10px;">Sync To Website</button>
    </div>

    <div class="col-sm-3 bg-white" style="padding:0;display:flex;flex-direction:column;">
        {{-- <svg xmlns="http://www.w3.org/2000/svg" style="margin: 30px auto;width:50px;color:#ffad46;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg> --}}
        <svg xmlns="http://www.w3.org/2000/svg" style="margin: 30px auto;width:50px;color:#ffad46;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <span class="btn-link btn-show-list" style="cursor: pointer;" data-show="failed">
            <h4 style="text-align: center;"><span id="failed_p">0</span></h4>
            <strong><h4 style="text-align: center;">Products Failed To Sync</h4></strong>
        </span>
        <button class="btn-show-save btn btn-warning" data-sync="failed" data-toggle="modal" data-target="#myModal" style="border-radius:0;margin-top:10px;">Fix & Sync</button>
    </div>


    <div class="col-sm-3 bg-white" style="padding:0;display:flex;flex-direction:column;">
        <svg xmlns="http://www.w3.org/2000/svg" style="margin: 30px auto;width:50px;color:#f5365c;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
        </svg>
        <span class="btn-link btn-show-list" style="cursor: pointer;" data-show="delete">
            <h4 style="text-align: center;"><span id="delete_p">0</span></h4>
            <strong><h4 style="text-align: center;">Products To Be Removed</h4></strong>
        </span>
        <button class="btn-show-save btn btn-danger" data-sync="delete" data-toggle="modal" data-target="#myModal" style="border-radius:0;margin-top:10px;">Remove From Webiste</button>
    </div>
</div>


    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" style="width: 400px !important;" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">Schedule Task</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Sync Type</label>
                            <select id="sync_type" class="form-control">
                                <option value="forced">Force All Products</option>
                                <option value="tierprice">Tier Price</option>
                                <option value="webstat">Web Stats</option>
                                <option value="new">New Products</option>
                                <option value="all">All Products</option>
                                <option value="failed">Failed</option>
                                <option value="delete">Delete</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Schedule At</label>
                            <input type="text" class="form-control" id="dt_picker" required/>
                        </div>
                        <div class="col-md-12 form-group" style="display: flex;">
                            <button id="btn-save-task" class="btn btn-primary" style="width:100%;border-radius: 0;">Save Task</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="myModalList" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">Products</h4>
                </div>
                <div class="modal-body list-body" style="max-height: 500px;overflow: auto;">

                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
<script>
$(document).ready(function(){
    $('#dt_picker').datetimepicker();
    $(document).on('click','.btn-abort',function(){
        $('.btn-abort').removeClass('abort-btn-active');
        $(this).addClass('abort-btn-active').prop('disabled', true);
        var task_id = $(this).data('task-id');
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: "/smart-sync/task/abort/" + task_id,
            success: function (data) {
                if(data.success == 1){
                    toastr.success(data.msg);
                    reloadTask();
                    reloadCount();
                }
                else{
                    toastr.warning(data.msg);
                    $('.abort-btn-active').prop('disabled', false);
                }
            },
            error: function (jqXHR, exception) {
                $('.abort-btn-active').prop('disabled', false);
            },
        });
    });

    $(document).on('click','.btn-show-save',function(){
        var sync_t = $(this).data('sync');
        $('#sync_type').val(sync_t);
    });

    $(document).on('click','#btn-save-task',function(){
        var dt_picker = $('#dt_picker').val();
        var sync_type = $('#sync_type').val();
        $('#btn-save-task').prop('disabled', true);
        $.ajax({
            type: "POST",
            dataType: 'json',
            data: {
                'dt_picker': dt_picker,
                'sync_type': sync_type
            },
            url: "{{ route('smart.task.add') }}",
            success: function (data) {
                if(data.success == 1){
                    toastr.success(data.msg);
                    $('#myModal').modal('hide');
                    reloadTask();
                    reloadCount();
                    setTimeout(() => {
                        $('#btn-save-task').prop('disabled', false);
                    }, 5000);
                }
                else{
                    toastr.warning(data.msg);
                }
            },
            error: function (jqXHR, exception) {
                $('#btn-save-task').prop('disabled', false);
            },
        });
    });

    $(document).on('click','.btn-show-list',function(){
        var sync_type = $(this).data('show');

        $.ajax({
            type: "GET",
            dataType: 'html',
            data: {
                'sync_type': sync_type
            },
            url: "{{ route('smart.products.list') }}",
            success: function (data) {
                $('.list-body').html(data);
                $('#myModalList').modal('show');
            }
        });
    });

    reloadCount();
    reloadTask();

    setInterval(() => {
        reloadCount();
        reloadTask();
    }, 10000);
});
function reloadCount(){
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "{{ route('smart.products.data') }}",
        success: function (data) {
            var erp_perc = Math.round((data.total_sync_p * 100) / data.total_p) + "%";
            var web_perc = Math.round((data.total_web_p_active * 100) / data.total_web_p) + "%";
            $('#erp_perc').html(erp_perc);
            $('#web_perc').html(web_perc);
            $('#erp_perc_bar').width(erp_perc);
            $('#web_perc_bar').width(web_perc);

            $('#total_p').html(data.total_p);
            $('#total_sync_p').html(data.total_sync_p);
            $('#total_web_p').html(data.total_web_p);
            $('#total_web_p_active').html(data.total_web_p_active);
            $('#web_missing_p').html(data.web_missing_p);
            $('#create_p').html(data.create_p);
            $('#sync_p').html(data.sync_p);
            $('#failed_p').html(data.failed_p);
            $('#delete_p').html(data.delete_p);
        }
    });
}
function reloadTask(){
    $( "#task_table_container" ).load( "{{ route('smart.products') }} #task_table" );

    var subject_op_pre = {
        'new': 'New',
        'all': 'All',
        'forced': 'Forced',
        'failed': 'Failed'
    };
    var subject_op_post = {
        'delete': 'Delete',
        'webstat': 'Web Stats',
        'inventory': 'Inventory'
    };

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "{{ route('smart.task.data') }}",
        success: function (data) {
            console.log(data);
            if(data != 0){
                var subject_name = data.subject_type.replace("SmartSync:","");

                if(subject_op_pre[data.subject_params] != undefined){
                    subject_name = subject_op_pre[data.subject_params] + " Products";
                }
                if(subject_op_post[data.subject_params] != undefined){
                    subject_name = "Products " + subject_op_post[data.subject_params];
                }
                $('#subject_name').html(subject_name += " Sync");

                var bg_color = "#0082ff";
                $('#btn_abort').data('task-id', 0).hide();
                $('.loader').css("background", '#00ff00');
                $('.loader-after').hide();

                if(data.status == "completed"){
                    bg_color = "#00ff00";
                }
                else if(data.status == "aborted"){
                    bg_color = "#ff3c3c";
                    $('.loader').css("background", bg_color);
                }
                else{
                    if(data.btn_abort_visibility == "show"){
                        $('#btn_abort').data('task-id', data.id).show();
                    }
                    $('.loader-after').show();
                }
                $('#status').html(capitalizeFirstLetter(data.status)).css("background", bg_color);
                $('#queue').html(capitalizeFirstLetter(data.smart_queue.replace("_"," ")));
                $('#progress').html(Math.round(data.progress) + "% Completed");
                $('#display_msg').html("Output: " + data.display_msg);
                $('#user_name').html("Scheduled at " + data.scheduled_at + " by " + data.user_name);
                $('.loader').width(data.progress+'%');
            }
        }
    });
}
function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}
</script>
@endsection