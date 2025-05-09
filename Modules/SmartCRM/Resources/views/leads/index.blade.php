@extends('layouts.app')
@section('title', 'Leads')
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css"
        integrity="sha512-xmGTNt20S0t62wHLmQec2DauG9T+owP9e6VU8GigI0anN7OXLip9i7IwEhelasml2osdxX71XcYm6BQunTQeQg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

@endsection

@section('content')
    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3 col-sm-3">
                <div class="form-group">
                    {!! Form::label('filter_created_by', __('Created By') . ':') !!}
                    {!! Form::select('filter_created_by', $users, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%', 'placeholder' => __('All')
                    ]) !!}
                </div>
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Leads')])
            @if (auth()->user()->can('delivery.create'))
                @slot('tool')
                    <div class="box-tools">
                        <button type="button" class="btn btn-block btn-primary btn-modal"
                            data-href="{{ route('smartcrm.lead.LeadsCreate') }}" data-container=".contact_modal">
                            <i class="fa fa-plus"></i> @lang('messages.add')</button>
                        {{-- <button class="btn btn-primary" data-toggle="modal" data-target="#followupmodal">+ Add New</button> --}}

                    </div>
                    <div class="modal fade contact_modal" tabindex="-1" role="dialog" data-backdrop="static"
                        aria-labelledby="gridSystemModalLabel">
                    </div>
                @endslot
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="Contact_Datatable">
                    <thead>
                        <tr>
                            <th>@lang('messages.action')</th>
                            <th>@lang('lang_v1.contact_id')</th>
                            <th>@lang('business.business_name')</th>
                            <th>@lang('user.name')</th>
                            <th>@lang('business.email')</th>
                            <th>@lang('Created By')</th>
                            <th>@lang('lang_v1.added_on')</th>
                            <th>@lang('business.first_name')</th>
                            <th>@lang('business.address')</th>
                            <th>@lang('contact.mobile')</th>
                </table>
                <tbody></tbody>

            </div>
            <div class="modal fade" id="EditModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
            </div>
            <div class="modal fade" id="ViewModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
            </div>
        @endcomponent
    </section>
@endsection

@section('javascript')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"
        integrity="sha512-9UR1ynHntZdqHnwXKTaOm1s6V9fExqejKvg5XMawEMToW4sSw+3jtLrYfZPijvnwnnE8Uol1O9BcAskoxgec+g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
    function getUserLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    // Get latitude and longitude
                    var latitude = position.coords.latitude;
                    var longitude = position.coords.longitude;

                    // Populate coordinates field in the modal with latitude and longitude values
                    $("#coordinates").val(latitude + ", " + longitude);
                });
            } else {
                // Handle geolocation not supported by the browser
                alert("Geolocation is not supported by this browser.");
            }
        }
        $(document).on('shown.bs.modal', '.contact_modal', function(e) {
            getUserLocation();
            initAutocomplete();
        });



        function initializeDataTable() {
            contact_table = $('#Contact_Datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('smartcrm.lead.Leads') }}",
                    data: function(d) {
                        d.created_by = $('#filter_created_by').val();
                        d = __datatable_ajax_callback(d);
                    }
                },
                columns: [{
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'contact_id',
                        name: 'contact_id'
                    },
                    {
                        data: 'supplier_business_name',
                        name: 'supplier_business_name'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'user.first_name',
                        name: 'user.first_name',
                        render: function(data, type, row) {
                            return data;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'first_name',
                        name: 'first_name'
                    },
                    {
                        data: 'address',
                        name: 'address',
                        orderable: false
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                ],
            });
        }

        // Initialize DataTable on document ready
        $(document).ready(function() {
            initializeDataTable();

            $(document).on('change','.send_mail_checkbox',function(){
                if($(this).is(":checked")) {
                    $('.send_mail_div').show();
                } else {
                    $('.send_mail_div').hide();
                }
            });

            // Change event for filter_created_by
            $(document).on('change', '#filter_created_by', function() {
                contact_table.ajax.reload();
            });

            $(document).on('click', 'a.edit-modal', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('href'),
                    dataType: 'html',
                    success: function(result) {
                        $('#EditModal')
                            .html(result)
                            .modal('show');
                    },
                });
            });
            $(document).on('click', 'a.view-modal', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('href'),
                    dataType: 'html',
                    success: function(result) {
                        $('#ViewModal')
                            .html(result)
                            .modal('show');
                    },
                });
            });
            $(document).on('click', '.delete-leads', function(e) {
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
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    contact_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
