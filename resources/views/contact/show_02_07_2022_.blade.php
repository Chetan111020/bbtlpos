@extends('layouts.app')
@section('title', __('contact.view_contact'))

@section('content')
    <!-- Main content -->
    <section class="content no-print">
        <div class="row no-print">
            <div class="col-md-4">
                <h3>@lang('contact.view_contact')</h3>
            </div>
            <div class="col-md-4 col-xs-12 mt-15 pull-right">
                <!--{!! Form::select('contact_id', $contact_dropdown, $contact->id , ['class' => 'form-control select2', 'id' => 'contact_id']); !!}-->
                 <select class="form-control select2" name="contact_id" id="contact_id">
                    @foreach($contact_dropdown as $list)
                        <option {{ ($list->id == $contact->id)? "selected" : ''}} value="{{$list->id}}">
                            @if($list->supplier_business_name)
                                {{$list->supplier_business_name}} - {{$list->name}}({{$list->contact_id}})
                            @else
                               {{$list->name}} - ({{$list->contact_id}})
                            @endif
                        </option>
                    @endforeach  
                </select>
            </div>
        </div>
        <div class="hide print_table_part">
            <style type="text/css">
                .info_col {
                    width: 25%;
                    float: left;
                    padding-left: 10px;
                    padding-right: 10px;
                }
                .text-muted{
                    font-size: 24px;
                }
                .red{
                    color: red;
                }
            </style>
            <div style="width: 100%;">
                <div class="info_col">
                    @include('contact.contact_basic_info')
                </div>
                <div class="info_col">
                    @include('contact.contact_more_info')
                </div>
                @if( $contact->type != 'customer')
                    <div class="info_col">
                        @include('contact.contact_tax_info')
                    </div>
                @endif
                <div class="info_col">
                    @include('contact.contact_payment_info')
                </div>
            </div>
        </div>
        <input type="hidden" id="sell_list_filter_customer_id" value="{{$contact->id}}">
        <input type="hidden" id="purchase_list_filter_supplier_id" value="{{$contact->id}}">
        <input type="hidden" id="customer_id" value="{{$contact->id}}">
        <br>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-solid">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                            <!-- <strong>{{ $contact->name }}</strong><br><br> -->
                                <h3 class="profile-username">
                                    <i class="fas fa-user-tie"></i>
                                    {{ $contact->name }}
                                    <small>
                                        @if($contact->type == 'both')
                                            {{__('role.customer')}} & {{__('role.supplier')}}
                                        @elseif(($contact->type != 'lead'))
                                            {{__('role.'.$contact->type)}}
                                        @endif
                                    </small>
                                </h3>
                            </div>
                        </div>
                        <div class="row">
                            <div style="border-color: #00acd6;" class="col-md-4 border-right">
                                <strong><i class="fa fa-map-marker margin-r-5"></i> @lang('business.address')</strong>
                                <p class="text-muted">
                                    {!! $contact->contact_address !!}
                                </p>
                                @if($contact->supplier_business_name)
                                    <strong><i class="fa fa-briefcase margin-r-5"></i>
                                        @lang('business.business_name')</strong>
                                    <p class="text-muted">
                                        <a href="">{{ $contact->supplier_business_name }}</a>
                                    </p>
                                @endif
                                
                                <strong><i class="fa fa-user margin-r-5"></i> @lang('Created By')</strong>
                                <p class="text-muted">
                                    {{ $contact->username }}
                                </p>

                                <strong><i class="fa fa-mobile margin-r-5"></i> @lang('contact.mobile')</strong>
                                <p class="text-muted">
                                    {{ $contact->mobile }}
                                </p>


                                <strong><i class="fas fa-user-tie"></i> @lang('lang_v1.contact_person_1')</strong>
                                <p class="text-muted">
                                    {{ $contact->contact_person_1 }}
                                </p>

                                <strong><i class="fas fa-user-tie"></i> @lang('lang_v1.contact_person_2')</strong>
                                <p class="text-muted">
                                    {{ $contact->contact_person_2 }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <strong><i class="fa fa-id-card"></i> @lang('lang_v1.tax_id')</strong>
                                <p class="text-muted">
                                    {{ $contact->tax }}
                                </p>

                                <strong><i class="fa fa-id-badge"></i> @lang('business.tobacco_license')</strong>
                                <p class="text-muted">
                                    {{ $contact->tobacco_license_no }}
                                </p>
                                <strong><i class="fas fa-tag"></i> @lang('business.nyc')</strong>
                                <p class="text-muted">
                                    {{ $contact->nyc }}
                                </p>

                                <strong><i class="fas fa-user-tie"></i> @lang('business.sales_rep')</strong>
                                <p class="text-muted red">
                                   {{$contact->sales_firstname}} {{$contact->sales_lastname}}
                                </p>
                                <strong><i class="fa fa-times-circle"></i> @lang('lang_v1.expiry_date')</strong>
                                <p class="text-muted">
                                    {{ $contact->expiry_date }}
                                </p>


                                <strong><i class="fa fa-users"></i> @lang('business.ref_code')</strong>
                                <p class="text-muted">
                                    <?php
                                    $referralID = \Illuminate\Support\Facades\DB::table('contacts')->where('supplier_business_name', substr(strstr($contact->referal_code, '@'), 1))->first();
                                    ?>
                                    @if($referralID != null)
                                        {{ strtok($contact->referal_code, '@') }}<a
                                                href="{{url('contacts',[$referralID->id]) }}">{{substr(strstr($contact->referal_code, '@'), 0)}}</a>
                                    @else
                                            {{ strtok($contact->referal_code, '@').substr(strstr($contact->referal_code, '@'), 0)}}
                                    @endif
                                </p>
                            </div>
                            <div style="border-color: #00acd6;" class="col-md-4 border-left">
                                <strong><i class="fas fa-user-tie"></i> @lang('business.acc_rep')</strong>
                                <p class="text-muted red">
                                    {{$contact->acc_firstname}} {{$contact->acc_lastname}}
                                </p>
                                @if($contact->landline)
                                    <strong><i class="fa fa-phone margin-r-5"></i> @lang('contact.landline')</strong>
                                    <p class="text-muted">
                                        {{ $contact->landline }}
                                    </p>
                                @endif
                                @if($contact->alternate_number)
                                    <strong><i class="fa fa-phone margin-r-5"></i> @lang('contact.alternate_contact_number')
                                    </strong>
                                    <p class="text-muted">
                                        {{ $contact->alternate_number }}
                                    </p>
                                @endif
                                @if($contact->dob)
                                    <strong><i class="fa fa-calendar margin-r-5"></i> @lang('lang_v1.dob')</strong>
                                    <p class="text-muted">
                                        <a>{{ @format_date($contact->dob) }}</a>
                                    </p>
                                @endif
                                <strong><i class="fa fa-mobile margin-r-5"></i> @lang('contact.whatsapp')</strong>
                                <p class="text-muted">
                                    {{ $contact->whatsapp }}
                                </p>
                                <strong><i class="fa fa-briefcase margin-r-5"></i> @lang('contact.note')</strong>
                                <p class="text-muted">
                                    {{ $contact->note }}
                                </p>
                                @if($contact->woocommerce_user_id)
                                <strong><i class="fa fa-key margin-r-5"></i> {{ __('Woocommerce Password') }}</strong>
                                <p class="text-muted">
                                    {{ $contact->contact_id.'$Esd@123' }}
                                </p>
                                @endif
                                 @if($contact->contact_status != 'active')
                                <p class="btn btn-danger" >INACTIVE</p>
                                 @endif
                            </div>
                        </div>
                        {{--                        @include('contact.partials.contact_info_tab')--}}
                        
                        @if($contact->type == 'customer')
                            <a href="{{ action('ContactController@edit', [$contact->id]) }}" class="btn btn-primary edit_contact_button" style="float: right; margin-left: 3px;" ><i class="glyphicon glyphicon-edit"></i>Edit</a>
                        @elseif($contact->type == 'supplier')
                            <a href="{{ action('ContactController@edit', [$contact->id]) }}" class="btn btn-primary edit_contact_button" style="float: right; margin-left: 3px;" ><i class="glyphicon glyphicon-edit"></i>Edit</a>
                        
                        @endif
                        
                        @if($contact->type == 'customer')
                            <a href="{{ action('TransactionPaymentController@getPayContactDue', [$contact->id]) }}?type=sell" style="background-color: green;float: right;color:#fff;" class="btn btn-flat small-box-footer mark_as_cooked_btn col-md-2 pay_sale_due"><i class="fas fa-money-bill-alt"></i> Add Payment</a>
                        @elseif($contact->type == 'supplier')
                            <a href="{{ action('TransactionPaymentController@getPayContactDue', [$contact->id]) }}?type=purchase" style="background-color: green;float: right;color:#fff;" class="btn btn-flat small-box-footer mark_as_cooked_btn col-md-2 pay_sale_due"><i class="fas fa-money-bill-alt"></i> Add Payment</a>
                        @endif
                        
                     

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs nav-justified">
                        <li class="
                            @if(!empty($view_type) &&  $view_type == 'ledger')
                                active
                            @else
                                ''
                            @endif">
                            <a href="#ledger_tab" data-toggle="tab" id="ledgertab" aria-expanded="true"><i class="fas fa-scroll"
                                                                                            aria-hidden="true"></i> @lang('lang_v1.ledger')
                            </a>
                        </li>
                        @if(in_array($contact->type, ['both', 'supplier']))
                            <li class="
                            @if(!empty($view_type) &&  $view_type == 'purchase')
                                    active
@else
                                    ''
@endif">
                                <a href="#purchases_tab" data-toggle="tab" aria-expanded="true"><i
                                            class="fas fa-arrow-circle-down"
                                            aria-hidden="true"></i> @lang( 'purchase.purchases')</a>
                            </li>
                            <li class="
                            @if(!empty($view_type) &&  $view_type == 'stock_report')
                                    active
@else
                                    ''
@endif">
                                <a href="#stock_report_tab" data-toggle="tab" aria-expanded="true"><i
                                            class="fas fa-hourglass-half"
                                            aria-hidden="true"></i> @lang( 'report.stock_report')</a>
                            </li>
                        @endif
                        @if(in_array($contact->type, ['both', 'customer']))
                            <li class="
                            @if(!empty($view_type) &&  $view_type == 'sales')
                                    active
@else
                                    ''
@endif">
                                <a href="#sales_tab" data-toggle="tab" aria-expanded="true" onclick="toggle_click()"><i
                                            class="fas fa-arrow-circle-up" aria-hidden="true"></i> @lang( 'sale.sells')
                                </a>
                            </li>
                            @if(in_array('subscription', $enabled_modules))
                                <li class="
                                @if(!empty($view_type) &&  $view_type == 'subscriptions')
                                        active
@else
                                        ''
@endif">
                                    <a href="#subscriptions_tab" data-toggle="tab" aria-expanded="true"><i
                                                class="fas fa-recycle"
                                                aria-hidden="true"></i> @lang( 'lang_v1.subscriptions')</a>
                                </li>
                            @endif
                        @endif
                          @if(in_array($contact->type, ['both', 'customer']))	
                            <li class="	
                            @if(!empty($view_type) &&  $view_type == 'sell_return')	
                                    active	
@else	
                                    ''	
@endif">	
                                <a href="#sell_return_tab" data-toggle="tab" id="sellreturn" aria-expanded="true"><i	
                                            class="fas fa-arrow-circle-down" aria-hidden="true"></i> Credit Memo	
                                </a>	
                            </li>	
                            	
                        @endif
                        <li class="
                            @if(!empty($view_type) &&  $view_type == 'payments')
                                active
                            @else
                                ''
                            @endif">
                            <a href="#payments_tab" data-toggle="tab" id="payment_tab" aria-expanded="true"><i
                                        class="fas fa-money-bill-alt" aria-hidden="true"></i> @lang('sale.payments')</a>
                        </li>
                        <li class="
                            @if(!empty($view_type) &&  $view_type == 'documents_and_notes')
                                active
                            @else
                                ''
                            @endif
                                ">
                            <a href="#documents_and_notes_tab" data-toggle="tab" aria-expanded="true"><i
                                        class="fas fa-paperclip"
                                        aria-hidden="true"></i> @lang('lang_v1.documents_and_notes')</a>
                        </li>                        

                        @if( in_array($contact->type, ['customer', 'both']) && session('business.enable_rp'))
                            <li class="
                            @if(!empty($view_type) &&  $view_type == 'reward_point')
                                    active
@else
                                    ''
@endif">
                                <a href="#reward_point_tab" data-toggle="tab" aria-expanded="true"><i
                                            class="fas fa-gift"
                                            aria-hidden="true"></i> {{ session('business.rp_name') ?? __( 'lang_v1.reward_points')}}
                                </a>
                            </li>
                        @endif

                        @if(!empty($contact_view_tabs))
                            @foreach($contact_view_tabs as $key => $tabs)
                                @foreach ($tabs as $index => $value)
                                    @if(!empty($value['tab_menu_path']))
                                        @php
                                            $tab_data = !empty($value['tab_data']) ? $value['tab_data'] : [];
                                        @endphp
                                        @include($value['tab_menu_path'], $tab_data)
                                    @endif
                                @endforeach
                            @endforeach
                        @endif

                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane
                                @if(!empty($view_type) &&  $view_type == 'ledger')
                                active
@else
                                ''
@endif"
                             id="ledger_tab">
                            @include('contact.partials.ledger_tab')
                        </div>
                        @if(in_array($contact->type, ['both', 'supplier']))
                            <div class="tab-pane
                            @if(!empty($view_type) &&  $view_type == 'purchase')
                                    active
@else
                                    ''
@endif"
                                 id="purchases_tab">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {!! Form::label('purchase_list_filter_date_range', __('report.date_range') . ':') !!}
                                            {!! Form::text('purchase_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        @include('purchase.partials.purchase_table')
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane
                            @if(!empty($view_type) &&  $view_type == 'stock_report')
                                    active
@else
                                    ''
@endif" id="stock_report_tab">
                                @include('contact.partials.stock_report_tab')
                            </div>
                        @endif
                        @if(in_array($contact->type, ['both', 'customer']))
                            <div class="tab-pane
                            @if(!empty($view_type) &&  $view_type == 'sales')
                                    active
@else
                                    ''
@endif"
                                 id="sales_tab">
                                <div class="row">
                                    <div class="col-md-12">
                                        @component('components.widget')
                                            @include('sell.partials.sell_list_filters', ['only' => ['sell_list_filter_payment_status', 'sell_list_filter_date_range', 'only_subscriptions']])
                                        @endcomponent
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        @include('sale_pos.partials.sales_table')
                                    </div>
                                </div>
                            </div>
                            @if(in_array('subscription', $enabled_modules))
                                @include('contact.partials.subscriptions')
                            @endif
                        @endif
                         <div class="tab-pane	
                            @if(!empty($view_type) &&  $view_type == 'sell_return')	
                            active	
                            @else	
                            ''	
                            @endif"	
                            id="sell_return_tab">	
                            @include('contact.partials.sales_return_table')	
                        </div>
                        <div class="tab-pane
                            @if(!empty($view_type) &&  $view_type == 'documents_and_notes')
                                active
                            @else
                                ''
                            @endif"
                             id="documents_and_notes_tab">
                            @include('contact.partials.documents_and_notes_tab')
                        </div>
                        <div class="tab-pane
                        @if(!empty($view_type) &&  $view_type == 'payments')
                                active
@else
                                ''
@endif" id="payments_tab">
                            <div id="contact_payments_div" style="height: 500px;overflow-y: scroll;"></div>
                        </div>
                        @if( in_array($contact->type, ['customer', 'both']) && session('business.enable_rp'))
                            <div class="tab-pane
                            @if(!empty($view_type) &&  $view_type == 'reward_point')
                                    active
@else
                                    ''
@endif"
                                 id="reward_point_tab">
                                <br>
                                <div class="row">
                                    @if($reward_enabled)
                                        <div class="col-md-3">
                                            <div class="info-box bg-yellow">
                                                <span class="info-box-icon"><i class="fa fa-gift"></i></span>

                                                <div class="info-box-content">
                                                    <span class="info-box-text">{{session('business.rp_name')}}</span>
                                                    <span class="info-box-number">{{$contact->total_rp ?? 0}}</span>
                                                </div>
                                                <!-- /.info-box-content -->
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped"
                                                   id="rp_log_table" width="100%">
                                                <thead>
                                                <tr>
                                                    <th>@lang('messages.date')</th>
                                                    <th>@lang('sale.invoice_no')</th>
                                                    <th>@lang('lang_v1.earned')</th>
                                                    <th>@lang('lang_v1.redeemed')</th>
                                                </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($contact_view_tabs))
                            @foreach($contact_view_tabs as $key => $tabs)
                                @foreach ($tabs as $index => $value)
                                    @if(!empty($value['tab_content_path']))
                                        @php
                                            $tab_data = !empty($value['tab_data']) ? $value['tab_data'] : [];
                                        @endphp
                                        @include($value['tab_content_path'], $tab_data)
                                    @endif
                                @endforeach
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
    <!--<div class="modal fade payment_modal" tabindex="-1" role="dialog"-->
    <!--     aria-labelledby="gridSystemModalLabel">-->
    <!--</div>-->
    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" data-backdrop="static" 
         aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade pay_contact_due_modal payment_modal" tabindex="-1" role="dialog" data-backdrop="static"
         aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" data-backdrop="static" 
         aria-labelledby="gridSystemModalLabel">
    </div>
@stop
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function () {
            
            var startdate = new Date();
            var newDate = moment(startdate).add(1, 'years').add(1, 'days');
            
            // default date range this month last year   
            dateRangeSettings.startDate = new moment('2021-01-01');
            dateRangeSettings.endDate = newDate;
            
            // default date range last two months  
            // dateRangeSettings.startDate = new moment().add(-2,'month');
            // dateRangeSettings.endDate = new Date();
            
            
            $('#ledger_date_range').daterangepicker(
                dateRangeSettings,
                function (start, end) {
                    $('#ledger_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                }
            );
            $('#ledger_date_range').change(function () {
                get_contact_ledger();
            });
            get_contact_ledger();

            rp_log_table = $('#rp_log_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [[0, 'desc']],
                ajax: '/sells?customer_id={{ $contact->id }}&rewards_only=true',
                columns: [
                    {data: 'transaction_date', name: 'transactions.transaction_date'},
                    {data: 'invoice_no', name: 'transactions.invoice_no'},
                    {data: 'rp_earned', name: 'transactions.rp_earned'},
                    {data: 'rp_redeemed', name: 'transactions.rp_redeemed'},
                ]
            });

            supplier_stock_report_table = $('#supplier_stock_report_table').DataTable({
                processing: true,
                serverSide: true,
                'ajax': {
                    url: "{{action('ContactController@getSupplierStockReport', [$contact->id])}}",
                    data: function (d) {
                        d.location_id = $('#sr_location_id').val();
                    }
                },
                columns: [
                    {data: 'product_name', name: 'p.name'},
                    {data: 'sub_sku', name: 'v.sub_sku'},
                    {data: 'purchase_quantity', name: 'purchase_quantity', searchable: false},
                    {data: 'total_quantity_sold', name: 'total_quantity_sold', searchable: false},
                    {data: 'total_quantity_returned', name: 'total_quantity_returned', searchable: false},
                    {data: 'current_stock', name: 'current_stock', searchable: false},
                    {data: 'stock_price', name: 'stock_price', searchable: false}
                ],
                fnDrawCallback: function (oSettings) {
                    __currency_convert_recursively($('#supplier_stock_report_table'));
                },
            });

            $('#sr_location_id').change(function () {
                supplier_stock_report_table.ajax.reload();
            });

            $('#contact_id').change(function () {
                if ($(this).val()) {
                    window.location = "{{url('/contacts')}}/" + $(this).val();
                }
            });
        });

        $("input.transaction_types, input#show_payments").on('ifChanged', function (e) {
            get_contact_ledger();
        });

        $(document).one('shown.bs.tab', 'a[href="#payments_tab"]', function () {
            get_contact_payments();
        });

        $("#payment_tab").click(function(){
            get_contact_payments();
        });

        $("#ledgertab").click(function(){
            get_contact_ledger();
        })

          	
        $("#sellreturn").click(function(){ 		
           		
           var customer_id = $("#customer_id").val();		
            $.ajax({		
                method: 'GET',		
                url: '/sellreturn',		
                data: { customer_id: customer_id },		
                dataType: 'html',		
                success: function (result) {		
                    $('.sell_return')		
                        .html(result);		
                    __currency_convert_recursively($('.sell_return'));		
                    $('#sell_return_table').DataTable({		
                        searching: false,		
                        ordering: false,		
                        paging: false,		
                        dom: 't'		
                    });		
                },		
            });		
        })

        $(document).on('click', '#contact_payments_pagination a', function (e) {
            e.preventDefault();
            get_contact_payments($(this).attr('href'));
        })

        function get_contact_payments(url = null) {
            if (!url) {
                url = "{{action('ContactController@getContactPayments', [$contact->id])}}";
            }
            $.ajax({
                url: url,
                dataType: 'html',
                success: function (result) {
                    $('#contact_payments_div').fadeOut(400, function () {
                        $('#contact_payments_div')
                            .html(result).fadeIn(400);      
                    });
                    
                },
            });
        }

        function get_contact_ledger() {

            var start_date = '';
            var end_date = '';
            var transaction_types = $('input.transaction_types:checked').map(function (i, e) {
                return e.value
            }).toArray();
            var show_payments = $('input#show_payments').is(':checked');

            if ($('#ledger_date_range').val()) {
                start_date = $('#ledger_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                end_date = $('#ledger_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
            }
            $.ajax({
                url: '/contacts/ledger?contact_id={{$contact->id}}&start_date=' + start_date + '&transaction_types=' + transaction_types + '&show_payments=' + show_payments + '&end_date=' + end_date,
                dataType: 'html',
                success: function (result) {
                    $('#contact_ledger_div')
                        .html(result);
                    __currency_convert_recursively($('#contact_ledger_div'));

                    $('#ledger_table').DataTable({
                        searching: false,
                        ordering: false,
                        paging: false,
                        dom: 't'
                    });
                    // location.reload();
                },
            });
        }

        $(document).on('click', '#send_ledger', function () {
            var start_date = $('#ledger_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
            var end_date = $('#ledger_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');

            var url = "{{action('NotificationController@getTemplate', [$contact->id, 'send_ledger'])}}" + '?start_date=' + start_date + '&end_date=' + end_date;

            $.ajax({
                url: url,
                dataType: 'html',
                success: function (result) {
                    $('.view_modal')
                        .html(result)
                        .modal('show');
                },
            });
        })

        $(document).on('click', '#print_ledger_pdf', function () {
            var start_date = $('#ledger_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
            var end_date = $('#ledger_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');

            var url = $(this).data('href') + '&start_date=' + start_date + '&end_date=' + end_date;
            window.open(url);
        });

    </script>
    @include('sale_pos.partials.sale_table_javascript')
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    @if(in_array($contact->type, ['both', 'supplier']))
        <script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
    @endif

    <!-- document & note.js -->
    @include('documents_and_notes.document_and_note_js')
    @if(!empty($contact_view_tabs))
        @foreach($contact_view_tabs as $key => $tabs)
            @foreach ($tabs as $index => $value)
                @if(!empty($value['module_js_path']))
                    @include($value['module_js_path'])
                @endif
            @endforeach
        @endforeach
    @endif

    <script type="text/javascript">
        $(document).ready(function () {
            $('#purchase_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function (start, end) {
                    $('#purchase_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                    purchase_table.ajax.reload();
                }
            );
            $('#purchase_list_filter_date_range').on('cancel.daterangepicker', function (ev, picker) {
                $('#purchase_list_filter_date_range').val('');
                purchase_table.ajax.reload();
            });
        });
        
        function toggle_click(){	
            $(window).on('resize', function () {	
                var table = $('#sell_table').DataTable();	
                table.columns.adjust();	
                 });	
                 $('a[data-toggle="tab"]').on( 'shown.bs.tab', function (e) {	
                    // var target = $(e.target).attr("href"); // activated tab	
                    // alert (target);	
                    $($.fn.dataTable.tables( true ) ).css('width', '100%');	
                    $($.fn.dataTable.tables( true ) ).DataTable().columns.adjust().draw();	
                } );	
                 	
        }
    </script>
    
    <script type="text/javascript">
        $(document).ready(function () {
            ranges['Last Two Months'] = [
                moment().add(-2, 'month'), 
                moment().add(0, 'day')
            ];
            dateRangeSettings.startDate = new moment().add(-2,'month');
            dateRangeSettings.endDate = new Date();
            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function (start, end) {
                    $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                    sell_table.ajax.reload();
                }
            );
        });    
    </script>
    @include('sale_pos.partials.subscriptions_table_javascript', ['contact_id' => $contact->id])
@endsection
