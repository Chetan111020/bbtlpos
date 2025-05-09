@extends('layouts.app')
@section('title', __('lang_v1.' . $type . 's'))
@php
    $api_key = env('GOOGLE_MAP_API_KEY');
@endphp
@if (!empty($api_key))
    @section('css')
        @include('contact.partials.google_map_styles')
    @endsection
@endif
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> @lang('lang_v1.' . $type . 's')
            <small>@lang('contact.manage_your_contact', ['contacts' => __('lang_v1.' . $type . 's')])</small>
        </h1>
        <!-- <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active">Here</li>
            </ol> -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-md-12">
                                <h4>Filter</h4>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>State</label>
                                    <select id="contact_state" type="text" value="" name="state"
                                        class="form-control select2">
                                        <option value="">All</option>
                                        <option value="Alabama">Alabama</option>
                                        <option value="Alaska">Alaska</option>
                                        <option value="Arizona">Arizona</option>
                                        <option value="Arkansas">Arkansas</option>
                                        <option value="California">California</option>
                                        <option value="Colorado">Colorado</option>
                                        <option value="Connecticut">Connecticut</option>
                                        <option value="Delaware">Delaware</option>
                                        <option value="District Of Columbia">District Of Columbia</option>
                                        <option value="Florida">Florida</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Hawaii">Hawaii</option>
                                        <option value="Idaho">Idaho</option>
                                        <option value="Illinois">Illinois</option>
                                        <option value="Indiana">Indiana</option>
                                        <option value="Iowa">Iowa</option>
                                        <option value="Kansas">Kansas</option>
                                        <option value="Kentucky">Kentucky</option>
                                        <option value="Louisiana">Louisiana</option>
                                        <option value="Maine">Maine</option>
                                        <option value="Maryland">Maryland</option>
                                        <option value="Massachusetts">Massachusetts</option>
                                        <option value="Michigan">Michigan</option>
                                        <option value="Minnesota">Minnesota</option>
                                        <option value="Mississippi">Mississippi</option>
                                        <option value="Missouri">Missouri</option>
                                        <option value="Montana">Montana</option>
                                        <option value="Nebraska">Nebraska</option>
                                        <option value="Nevada">Nevada</option>
                                        <option value="New Hampshire">New Hampshire</option>
                                        <option value="New Jersey">New Jersey</option>
                                        <option value="New Mexico">New Mexico</option>
                                        <option value="New York">New York</option>
                                        <option value="North Carolina">North Carolina</option>
                                        <option value="North Dakota">North Dakota</option>
                                        <option value="Ohio">Ohio</option>
                                        <option value="Oklahoma">Oklahoma</option>
                                        <option value="Oregon">Oregon</option>
                                        <option value="Pennsylvania">Pennsylvania</option>
                                        <option value="Rhode Island">Rhode Island</option>
                                        <option value="South Carolina">South Carolina</option>
                                        <option value="South Dakota">South Dakota</option>
                                        <option value="Tennessee">Tennessee</option>
                                        <option value="Texas">Texas</option>
                                        <option value="Utah">Utah</option>
                                        <option value="Vermont">Vermont</option>
                                        <option value="Virginia">Virginia</option>
                                        <option value="Washington">Washington</option>
                                        <option value="West Virginia">West Virginia</option>
                                        <option value="Wisconsin">Wisconsin</option>
                                        <option value="Wyoming">Wyoming</option>
                                    </select>
                                </div>
                            </div>
                            @if ($type == 'customer')
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Account Rep</label>
                                        <select name="contact_account_rep" id="contact_account_rep"
                                            class="form-control select2">
                                            <option value="">All</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    data-account="{{ $user->first_name }} {{ $user->last }}">
                                                    {{ $user->first_name }} {{ $user->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Sales Rep</label>
                                        <select name="contact_sales_rep" id="contact_sales_rep"
                                            class="form-control select2">
                                            <option value="">All</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->first_name }}
                                                    {{ $user->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                            @if ($type == 'supplier')
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>City</label>
                                        <select id="contact_city" type="text" value="" name="city"
                                            class="form-control select2">
                                            <option value="">All</option>
                                            <option value="New York">New York</option>
                                            <option value="California">California</option>
                                            <option value="Texas">Texas</option>
                                            <option value="Arizona">Arizona</option>
                                            <option value="Pennsylvania">Pennsylvania</option>
                                            <option value="California">California</option>
                                            <option value="California">California</option>
                                        </select>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('contact_filter_customer_id', $type == 'customer' ? __('contact.customer') : 'Supplier' . ':') !!}
                                    {!! Form::select('contact_filter_customer_id', $customers, null, [
                                        'class' => 'form-control select2',
                                        'style' => 'width:100%',
                                        'placeholder' => __('lang_v1.all'),
                                    ]) !!}
                                </div>
                            </div>

                            @if ($type == 'customer')
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <br>
                                        <label>
                                            {!! Form::checkbox('exact_search_contact', 1, false, [
                                                'class' => 'input-icheck',
                                                'id' => 'exact_search_contact',
                                            ]) !!} Exact Search
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <br>
                                        <label>
                                            {!! Form::checkbox('inactive_contact', 1, false, ['class' => 'input-icheck', 'id' => 'inactive_contact']) !!} Show Inactive
                                        </label>
                                    </div>
                                </div>
                            @endif
                            @if ($type == 'supplier')
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <br>
                                        <label>
                                            {!! Form::checkbox('inactive_supplier', 1, false, ['class' => 'input-icheck', 'id' => 'inactive_supplier']) !!} Show Inactive
                                        </label>
                                    </div>
                                </div>

                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" value="{{ $type }}" id="contact_type">
        @component('components.widget', [
            'class' => 'box-primary',
            'title' => __('contact.all_your_contact', ['contacts' => __('lang_v1.' . $type . 's')]),
        ])
            @if (auth()->user()->can('supplier.create') ||
                    auth()->user()->can('customer.create') ||
                    auth()->user()->can('supplier.view_own') ||
                    auth()->user()->can('customer.view_own'))
                @slot('tool')
                    <div class="box-tools">
                        <button type="button" class="btn btn-block btn-primary btn-modal"
                            data-href="{{ action('ContactController@create', ['type' => $type]) }}"
                            data-container=".contact_modal">
                            <i class="fa fa-plus"></i> @lang('messages.add')</button>
                    </div>
                @endslot
            @endif
            @if (auth()->user()->can('supplier.view') ||
                    auth()->user()->can('customer.view') ||
                    auth()->user()->can('supplier.view_own') ||
                    auth()->user()->can('customer.view_own'))
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="contact_table1">
                        <thead>
                            <tr>
                                @if ($type == 'customer')
                                    <th><input type="checkbox" id="select-all-rows"></th>
                                @endif
                                <th>@lang('messages.action')</th>
                                <th>@lang('lang_v1.contact_id')</th>
                                @if ($type == 'supplier')
                                    <th>@lang('business.business_name')</th>
                                    <th>@lang('contact.name')</th>
                                    <th>@lang('business.email')</th>
                                    <th>@lang('Created By')</th>
                                    <th>@lang('contact.tax_no')</th>
                                    <th>@lang('contact.pay_term')</th>
                                    <th>@lang('Open Balance')</th>
                                    <th>@lang('Credits')</th>
                                    <th>@lang('lang_v1.advance_balance')</th>
                                    <th>@lang('lang_v1.added_on')</th>
                                    <th>@lang('business.address')</th>
                                    <th>@lang('contact.mobile')</th>
                                    <th>@lang('Previous Balance')</th>
                                @elseif($type == 'customer')
                                    <th>@lang('business.business_name')</th>
                                    <th>@lang('user.name')</th>
                                    <th>@lang('business.email')</th>
                                    <th>@lang('Created By')</th>
                                    <th>@lang('contact.tax_no')</th>
                                    <th>@lang('lang_v1.credit_limit')</th>
                                    <th>@lang('contact.pay_term')</th>
                                    <th>@lang('Balance Due UF')</th>
                                    <th>@lang('contact.total_sale_due')</th>

                                    <th>@lang('Credit')</th>
                                    <th>@lang('lang_v1.advance_balance')</th>
                                    <th>@lang('lang_v1.added_on')</th>
                                    @if ($reward_enabled)
                                        <th id="rp_col">{{ session('business.rp_name') }}</th>
                                    @endif
                                    <th>@lang('business.first_name')</th>
                                    <th>@lang('lang_v1.customer_group')</th>
                                    <th>@lang('business.address')</th>
                                    <th>@lang('contact.mobile')</th>
                                    <th>@lang('account.opening_balance')</th>
                                    <th>@lang('lang_v1.sales_rep')</th>
                                    <th>@lang('lang_v1.account_rep')</th>
                                    {{-- <th>@lang('lang_v1.ref_code')</th> --}}
                                    {{-- <th>@lang('lang_v1.note')</th> --}}
                                    {{-- <th>@lang('lang_v1.file')</th> --}}
                                    {{-- <th>@lang('lang_v1.tax_id')</th> --}}
                                    <th>@lang('lang_v1.nyc')</th>

                                @endif
                                {{-- @php
                                 $custom_labels = json_decode(session('business.custom_labels'), true);
                             @endphp --}}
                                <th style="display:none;">
                                    {{ $custom_labels['contact']['custom_field_1'] ?? __('lang_v1.contact_custom_field1') }}
                                </th>
                                <th style="display:none;">
                                    {{ $custom_labels['contact']['custom_field_2'] ?? __('lang_v1.contact_custom_field2') }}
                                </th>
                                <th style="display:none;">
                                    {{ $custom_labels['contact']['custom_field_3'] ?? __('lang_v1.contact_custom_field3') }}
                                </th>
                                <th style="display:none;">
                                    {{ $custom_labels['contact']['custom_field_4'] ?? __('lang_v1.contact_custom_field4') }}
                                </th>
                                <th style="display:none;">
                                    {{ $custom_labels['contact']['custom_field_5'] ?? __('lang_v1.custom_field', ['number' => 5]) }}
                                </th>
                                <th style="display:none;">
                                    {{ $custom_labels['contact']['custom_field_6'] ?? __('lang_v1.custom_field', ['number' => 6]) }}
                                </th>
                                <th style="display:none;">
                                    {{ $custom_labels['contact']['custom_field_7'] ?? __('lang_v1.custom_field', ['number' => 7]) }}
                                </th>
                                <th style="display:none;">
                                    {{ $custom_labels['contact']['custom_field_8'] ?? __('lang_v1.custom_field', ['number' => 8]) }}
                                </th>
                                <th style="display:none;">
                                    {{ $custom_labels['contact']['custom_field_9'] ?? __('lang_v1.custom_field', ['number' => 9]) }}
                                </th>
                                <th style="display:none;">
                                    {{ $custom_labels['contact']['custom_field_10'] ?? __('lang_v1.custom_field', ['number' => 10]) }}
                                </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="font-17 text-center footer-total">

                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td @if ($type == 'supplier') colspan="6"
                                    @elseif($type == 'customer')
                                    @if ($reward_enabled)
                                    colspan="10"
                                    @else
                                    colspan="9" @endif
                                    @endif>
                                    <strong>
                                        @lang('sale.total'):
                                    </strong>
                                </td>
                                <td><span class="display_currency" id="footer_contact_due"
                                        data-currency_symbol="true"></span></td>
                                <td><span class="display_currency" id="footer_contact_return_due"
                                        data-currency_symbol="true"></span></td>
                                {{-- <td></td> --}}
                                {{-- <td></td> --}}
                                {{-- <td></td> --}}
                                {{-- <td></td> --}}
                                {{-- <td></td> --}}
                                {{-- <td></td> --}}
                                {{-- <td></td> --}}
                                {{-- <td></td> --}}
                                {{-- <td></td> --}}
                                {{-- <td></td> --}}
                            </tr>
                            @if ($type == 'customer')
                                <tr>
                                    <td colspan="20">
                                        &nbsp;
                                        {!! Form::open(['url' => action('ContactController@bulkeditcustomer'), 'method' => 'post', 'id' => 'bulk_edit']) !!}
                                        {!! Form::hidden('selected_customer', null, ['id' => 'selected_customer_for_edit']) !!}
                                        <button type="button" id="edit-selected" class="btn btn-xs btn-primary"><a
                                                href="javascript:void(0)" style="color:white"><i
                                                    class="fa fa-edit"></i>{{ __('lang_v1.bulk_edit') }}</a></button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            @endif
        @endcomponent

        <div class="modal fade contact_modal" tabindex="-1" role="dialog" data-backdrop="static"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade pay_contact_due_modal payment_modal" tabindex="-1" role="dialog" data-backdrop="static"
            aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

    <div class="modal fade" id="bulkeditmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['url' => action('ContactController@bulkeditcustomer'), 'method' => 'post', 'id' => 'edit_bulk']) !!}
                <input type="hidden" name="customer_id" id="customer_id" value="">
                <div class="modal-header">
                    <h4 class="modal-title d-inline-block" style="display:inline-block;">Multiple Customer Update</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4><span class="add_to_location_title hide">@lang('lang_v1.add_location_to_the_selected_products')</span><span
                            class="remove_from_location_title hide">@lang('lang_v1.remove_location_from_the_selected_products')</span></h4>
                </div>
                {{--  <div class="modal-header">
                            <h5 style="text-align:left">Multiple Item Update</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title"><span class="add_to_location_title hide">@lang( 'lang_v1.add_location_to_the_selected_products' )</span><span class="remove_from_location_title hide">@lang( 'lang_v1.remove_location_from_the_selected_products' )</span></h4>
                        </div>  --}}
                <div class="modal-body">
                    <div>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <span class="input-group-addon"><small>Account Rep</small></span>
                                        <select name="customer_account_rep" id="customer_account_rep"
                                            class="form-control select2 input-sm" style="width: 100%;">
                                            <option value="0">Please Select</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->first_name }}
                                                    {{ $user->last_name }}</option>
                                            @endforeach
                                        </select>
                                        {{--  {!! Form::select('category_id', $categories, !empty($duplicate_product->category_id) ? $duplicate_product->category_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2 input-sm category_id', 'style' => 'width: 100%;','id'=> 'category_id']); !!}   --}}
                                    </div>
                                    <div class="col-md-4">
                                        <span class="input-group-addon"><small>Sales Rep</small></span>
                                        <select name="customer_sales_rep" class="form-control select2 input-sm"
                                            style="width: 100%;" id="customer_sales_rep">
                                            <option value="0">Please Select</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->first_name }}
                                                    {{ $user->last_name }}</option>
                                            @endforeach
                                        </select>
                                        {{--  {!! Form::select('brand_id', $brands, !empty($duplicate_product->brand_id) ? $duplicate_product->brand_id    : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2 input-sm category_id', 'style' => 'width: 100%;','id'=> 'brand_id']); !!}  --}}
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary submit"
                        id="customerbulkedit">@lang('messages.save')</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

@stop
@section('javascript')
    <script>
        $(document).on(
            'ifChanged',
            '#exact_search_contact,#inactive_contact,#inactive_supplier',
            function() {
                contact_table1.ajax.reload();
            }
        );
        $(document).on(
            'change',
            '#contact_state,#contact_city,#contact_filter_customer_id,#contact_account_rep,#contact_sales_rep',
            function() {
                contact_table1.ajax.reload();
                // if ($("#product_list_tab").hasClass('active')) {
                // }
                //
                // if ($("#product_stock_report").hasClass('active')) {
                //     stock_report_table.ajax.reload();
                // }
            }
        );
        var contact_table_type = $('#contact_type').val();

        var columns = [{
                data: 'check_box',
                searchable: false,
                orderable: false
            },
            {
                data: 'action',
                searchable: false,
                orderable: false
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
                data: 'created_username',
                name: 'createdfor.username'
            },
            {
                data: 'tax_number',
                name: 'tax_number'
            },
            {
                data: 'credit_limit',
                name: 'credit_limit'
            },
            {
                data: 'pay_term',
                name: 'pay_term',
                searchable: false,
                orderable: false
            },
            {
                data: 'due_raw',
                name: 'due_raw',
                searchable: false,
                visible: false
            },
            {
                data: 'due',
                name: 'due'
            },

            {
                data: 'return_due',
                searchable: false,
                orderable: false
            },
            {
                data: 'balance',
                name: 'balance',
                searchable: false
            },
            {
                data: 'created_at',
                name: 'contacts.created_at'
            }
        ];
        //
        // if ($('#rp_col').length) {
        //     columns.push({ data: 'total_rp', name: 'total_rp' });
        // }
        Array.prototype.push.apply(columns, [{
                data: 'first_name',
                name: 'first_name'
            },
            {
                data: 'customer_group',
                name: 'cg.name'
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
            {
                data: 'opening_balance',
                name: 'opening_balance',
                searchable: false
            },
            {
                data: 'sales_rep',
                name: 'sales_rep'
            },
            {
                data: 'account_rep',
                name: 'account_rep'
            },
            {
                data: 'is_nyc',
                name: 'is_nyc'
            },

            // { data: 'custom_field1', name: 'custom_field1'},
            // { data: 'custom_field2', name: 'custom_field2'},
            // { data: 'custom_field3', name: 'custom_field3'},
            // { data: 'custom_field4', name: 'custom_field4'},
            // { data: 'custom_field5', name: 'custom_field5'},
            // { data: 'custom_field6', name: 'custom_field6'},
            // { data: 'custom_field7', name: 'custom_field7'},
            // { data: 'custom_field8', name: 'custom_field8'},
            // { data: 'custom_field9', name: 'custom_field9'},
            // { data: 'custom_field10', name: 'custom_field10'},
        ]);


        contact_table1 = $('#contact_table1').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            columnDefs: [{
                type: 'any-number',
                targets: 11
            }],
            "ajax": {
                "url": "/customer-new",
                "data": function(d) {
                    d.type = $('#contact_type').val();
                    d.contact_state = $('#contact_state').val();
                    d.contact_city = $('#contact_city').val();
                    d.contact_filter_customer_id = $('#contact_filter_customer_id').val();
                    d.contact_account_rep = $("#contact_account_rep").val();
                    d.contact_sales_rep = $("#contact_sales_rep").val();
                    d.exact_search = $('#exact_search_contact').is(':checked') ? 1 : 0;
                    d.inactive_contact = $('#inactive_contact').is(':checked') ? 1 : 0;
                    d.inactive_supplier = $('#inactive_supplier').is(':checked') ? 1 : 0;
                    d = __datatable_ajax_callback(d);
                }
            },
            // scrollY:        "75vh",
            // scrollX:        true,
            // scrollCollapse: true,
            lengthMenu: [
                [10, 25, 50, 100, 500, -1],
                [10, 25, 50, 100, 500, "All"]
            ],
            order: [
                [3, "asc"]
            ],
            columns: columns,
            createdRow: function(row, data, dataIndex) {
                $(row).find('td:eq(0)').attr('class', 'selectable_td');
            },
            fnDrawCallback: function(oSettings) {
                var total_due = sum_table_col($('#contact_table1'), 'contact_due');
                $('#footer_contact_due').text(total_due);

                var total_return_due = sum_table_col($('#contact_table1'), 'return_due');
                $('#footer_contact_return_due').text(total_return_due);
                __currency_convert_recursively($('#contact_table1'));
            },

        });
        $('.contact_modal').on('shown.bs.modal', function(e) {
            var nowDate = new Date();
            var today = new Date(
                nowDate.getFullYear(),
                nowDate.getMonth(),
                nowDate.getDate(),
                0,
                0,
                0,
                0
            );
            $('#sales_tax_id_expiration_date').datepicker({
                autoclose: true,
                startDate: today,
            });
            $('#tobbaco_lic_expiration_date').datepicker({
                autoclose: true,
                startDate: today,
            });
            $('#vape_tax_id_expiration_date').datepicker({
                autoclose: true,
                startDate: today,
            });
            $('#any_id_expiration_date').datepicker({
                autoclose: true,
                startDate: today,
            });
            $('#more_btn').click(function() {
                $('#more_div').toggleClass('hide');
            });
            $('div.lead_additional_div').hide();

            if ($('select#contact_type').val() == 'customer') {
                $('div.supplier_fields').hide();
                $('div.customer_fields').show();
                $('.form-download-button').show();
                $('.supplier_status').attr('disabled', 'disabled');
            } else if ($('select#contact_type').val() == 'supplier') {
                $('div.supplier_fields').show();
                $('div.customer_fields').hide();
                $('.supplier_status').removeAttr('disabled');
                $('.customer_status').attr('disabled', 'disabled');
                $('.form-download-button').hide();
            } else if ($('select#contact_type').val() == 'lead') {
                $('div.supplier_fields').hide();
                $('div.customer_fields').hide();
                $('div.opening_balance').hide();
                $('div.pay_term').hide();
                $('div.lead_additional_div').show();
                $('div.shipping_addr_div').hide();
            }

            $('select#contact_type').change(function() {
                var t = $(this).val();

                if (t == 'supplier') {
                    $('div.supplier_fields').fadeIn();
                    $('div.customer_fields').fadeOut();
                    $('.customer_status').attr('disabled', 'disabled');
                    $('.supplier_status').removeAttr('disabled');
                    $('.customer_status').removeAttr('required');
                    $('div.supplier_fields select#contact_type').val('supplier');
                    $('.form-download-button').hide();
                } else if (t == 'both') {
                    $('div.supplier_fields').fadeIn();
                    $('div.customer_fields').fadeIn();
                } else if (t == 'customer') {
                    $('div.customer_fields').fadeIn();
                    $('div.supplier_fields').fadeOut();
                    $('.supplier_status').attr('disabled', 'disabled');
                    $('.customer_status').removeAttr('disabled');
                    $('div.customer_fields select#contact_type').val('customer');
                    $('.form-download-button').show();
                    // $("#contact_type").val("customer");
                } else if (t == 'lead') {
                    $('div.customer_fields').fadeOut();
                    $('div.supplier_fields').fadeOut();
                    $('div.opening_balance').fadeOut();
                    $('div.pay_term').fadeOut();
                    $('div.lead_additional_div').fadeIn();
                    $('div.shipping_addr_div').hide();
                }
            });

            (function() {
                var previous;

                $('select#select_address')
                    .on('focus', function() {
                        // Store the current value on focus and on change
                        previous = this.value;
                    })
                    .change(function() {
                        // Do something with the previous value after the change
                        var address_number = $(this).val();
                        var address_name = $('#address_name').val();
                        var address_line_1 = $('#address_line_1').val();
                        var address_line_2 = $('#address_line_2').val();
                        var city = $('#city').val();
                        var zip_code = $('#zip_code').val();
                        var data = {
                            address_number: address_number,
                            address_name: address_name,
                            address_line_1: address_line_1,
                            address_line_2: address_line_2,
                            city: city,
                            zip_code: zip_code,
                            previous_address_number: previous,
                        };
                        $.ajax({
                            method: 'Get',
                            url: '/contact/address',
                            dataType: 'json',
                            data: data,
                            success: function(result) {
                                if (result == 0) {
                                    $('#address_name').val('');
                                    $('#address_line_1').val('');
                                    $('#address_line_2').val('');
                                    $('#city').val('');
                                    $('#zip_code').val('');
                                } else {
                                    $('#address_name').val(result.address_name);
                                    $('#address_line_1').val(result.address_line_1);
                                    $('#address_line_2').val(result.address_line_2);
                                    $('#city').val(result.city);
                                    $('#zip_code').val(result.zip_code);
                                }
                            },
                        });

                        // Make sure the previous value is updated
                        previous = address_number;
                    });
            })();

            //Autocomplete for referral
            $('#referral').keyup(function() {
                // Fetch data
                $.ajax({
                    url: '/contact/referral',
                    type: 'get',
                    data: {
                        search: this.value,
                    },
                    success: function(result) {
                        $('#referral_list').fadeIn();
                        $('#referral_list').html(result);
                    },
                });
            });

            $('#mobile').change(function() {
                // if($('#whatsapp').val()=='')
                $('#whatsapp').val(this.value);
            });

            $(document).on('click', 'li', function() {
                var user = $(this).text();
                var user_id = $('input[name=' + user + ']').val();
                $('#referral').val(user);
                $('input[name=referral_id]').val(user_id);
                $('#referral_list').fadeOut();
            });
            $('.contact_modal')
                .find('.select2')
                .each(function() {
                    $(this).select2();
                });

            $('form#contact_add_form, form#contact_edit_form1')
                .submit(function(e) {
                    e.preventDefault();
                })
                .validate({
                    // rules: {
                    //     contact_id: {
                    //         remote: {
                    //             url: '/contacts/check-contact-id',
                    //             type: 'post',
                    //             data: {
                    //                 contact_id: function() {
                    //                     // return $('#contact_id').val();
                    //                 },
                    //                 hidden_id: function() {
                    //                     if ($('#hidden_id').length) {
                    //                         return $('#hidden_id').val();
                    //                     } else {
                    //                         return '';
                    //                     }
                    //                 },
                    //             },
                    //         },
                    //     },
                    // },
                    // messages: {
                    //     contact_id: {
                    //         remote: LANG.contact_id_already_exists,
                    //     },
                    // },

                    submitHandler: function(form) {
                        // alert("Do some stuff...");

                        // e.preventDefault();
                        // var formData = $(form).serialize();
                        var form_id = $(form).attr('id');
                        var form = document.getElementById(form_id);
                        var formData = new FormData(form);

                        // formData = $(form).serialize();
                        $(document).ajaxSend(function() {});
                        $(form).find('button[type="submit"]').attr('disabled', true);
                        $('#overlay').fadeIn(300);
                        $.ajax({
                            method: 'POST',
                            enctype: 'multipart/form-data',
                            url: $(form).attr('action'),
                            dataType: 'json',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(result) {
                                setTimeout(function() {
                                    $('#overlay').fadeOut(300);
                                    $('div.contact_modal').modal('hide');
                                }, 500);

                                contact_table1.ajax.reload();

                                if (result.success == true) {
                                    $('div.contact_modal').modal('hide');
                                    toastr.success(result.msg);

                                    if (typeof contact_table1 != 'undefined') {
                                        contact_table1.ajax.reload();
                                    }

                                    var lead_view = urlSearchParam('lead_view');
                                    if (lead_view == 'kanban') {
                                        initializeLeadKanbanBoard();
                                    } else if (
                                        lead_view == 'list_view' &&
                                        typeof leads_datatable != 'undefined'
                                    ) {
                                        leads_datatable.ajax.reload();
                                    }
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    },
                });
        });

        $(document).on('click', '.edit_contact_button', function(e) {
            e.preventDefault();
            $('div.contact_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });

        $(document).on('click', '.delete_contact_button', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_contact,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).attr('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                contact_table1.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });

        $(document).on('click', '.woocomerce_customer_sync', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                text: "you would like to sync this customer's data to our website?",
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willSync) => {
                if (willSync) {
                    var href = $(this).attr('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'GET',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                contact_table1.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });

        $(document).on('click', '.woocomerce_customer_verify', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                text: "you would like to verify this customer's data to our website?",
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willSync) => {
                if (willSync) {
                    var href = $(this).attr('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'GET',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                contact_table1.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });

        $(document).on('click', '.woocomerce_customer_reset_password', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                text: 'you would like to reset password for woocommerce?',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willSync) => {
                if (willSync) {
                    var href = $(this).attr('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'GET',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                contact_table1.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
        $(document).on('click', 'a.update_contact_status', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            $.ajax({
                url: href,
                dataType: 'json',
                success: function(data) {
                    if (data.success == true) {
                        toastr.success(data.msg);
                        contact_table1.ajax.reload();
                    } else {
                        toastr.error(data.msg);
                    }
                },
            });
        });

        $(document).on('shown.bs.modal', '.contact_modal', function(e) {
            $('.dob-date-picker').datepicker({
                autoclose: true,
                endDate: 'today',
            });
        });
        $(document).on('click', '#customerbulkedit', function(e) {
            var formData = {
                customer_account_rep: $('#customer_account_rep').val(),
                customer_sales_rep: $('#customer_sales_rep').val(),
                customer_id: $('#customer_id').val(),
            };

            $.ajax({
                method: 'POST',
                url: '/contacts/edit-customer',
                data: formData,
                success: function(result) {
                    if (result.success == true) {
                        toastr.success(result.msg);
                        contact_table1.ajax.reload();
                        $('#bulkeditmodal').modal('hide');
                        $('#customer_account_rep ').val([0]).trigger('change');
                        $('#customer_sales_rep').val([0]).trigger('change');
                        $('#customer_id').val('');
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });
    </script>
    @if (!empty($api_key))
        <script>
            // This example adds a search box to a map, using the Google Place Autocomplete
            // feature. People can enter geographical searches. The search box will return a
            // pick list containing a mix of places and predicted search terms.

            // This example requires the Places library. Include the libraries=places
            // parameter when you first load the API. For example:
            // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

            function initAutocomplete() {
                var map = new google.maps.Map(document.getElementById('map'), {
                    center: {
                        lat: -33.8688,
                        lng: 151.2195
                    },
                    zoom: 10,
                    mapTypeId: 'roadmap'
                });

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        initialLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                        map.setCenter(initialLocation);
                    });
                }


                // Create the search box and link it to the UI element.
                var input = document.getElementById('shipping_address');
                var searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

                // Bias the SearchBox results towards current map's viewport.
                map.addListener('bounds_changed', function() {
                    searchBox.setBounds(map.getBounds());
                });

                var markers = [];
                // Listen for the event fired when the user selects a prediction and retrieve
                // more details for that place.
                searchBox.addListener('places_changed', function() {
                    var places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }

                    // Clear out the old markers.
                    markers.forEach(function(marker) {
                        marker.setMap(null);
                    });
                    markers = [];

                    // For each place, get the icon, name and location.
                    var bounds = new google.maps.LatLngBounds();
                    places.forEach(function(place) {
                        if (!place.geometry) {
                            console.log("Returned place contains no geometry");
                            return;
                        }
                        var icon = {
                            url: place.icon,
                            size: new google.maps.Size(71, 71),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(17, 34),
                            scaledSize: new google.maps.Size(25, 25)
                        };

                        // Create a marker for each place.
                        markers.push(new google.maps.Marker({
                            map: map,
                            icon: icon,
                            title: place.name,
                            position: place.geometry.location
                        }));

                        //set position field value
                        var lat_long = [place.geometry.location.lat(), place.geometry.location.lng()]
                        $('#position').val(lat_long);

                        if (place.geometry.viewport) {
                            // Only geocodes have viewport.
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
            }
        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $api_key }}&libraries=places" async defer></script>
        <script type="text/javascript">
            $("#select-all-rows").click(function() {
                $(".checkBoxClass").attr('checked', this.checked);
            });
            $(document).on('shown.bs.modal', '.contact_modal', function(e) {
                initAutocomplete();
            });
        </script>
    @endif
@endsection
