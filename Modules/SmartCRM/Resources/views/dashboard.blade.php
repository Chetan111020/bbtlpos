<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@1.1.0/dist/chartjs-chart-matrix.min.js"></script>



@extends('layouts.app')
@section('title', 'CRM')
@include('smartcrm::dashboard-custom-css')
@section('content')
    {{--
@if (auth()->user()->id == 6 || auth()->user()->id == 128) --}}


    <div style="background:white;padding:15px;">
        <h1>Hi {{ Session::get('user.first_name') }},</h1>
        @if (auth()->user()->can('dashboard.data'))
            <div class="row" style="">
                <div class="form-group pull-right" style="margin-right:10%; ">
                    <div class="col-md-10">
                        <div class="form-group">
                            {!! Form::label('date_range', __('report.date_range') . ':') !!}
                            {!! Form::text('date_range', null, [
                                'class' => 'form-control',
                                'id' => 'date_range',
                                'placeholder' => __('lang_v1.select_a_date_range'),
                            ]) !!}
                        </div>


                    </div>
                    <div class="col-md-2">
                        <div class="form-group" style="margin-top: 25px;">
                            <button class="btn btn-primary" name="filtter" id="filtterData">Filtter</button>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row" style="display: none;">
                <div class="form-group pull-right">
                    <div class="col-md-8">
                        <input type="hidden" id="date" name="date" value="">
                        <div class="form-group">
                            {!! Form::label('all_date_filter', __('report.date_range') . ':') !!}
                            {!! Form::text(
                                'all_date_filter',
                                @format_date('first day of this week') . ' ~ ' . @format_date('last day of this week'),
                                [
                                    'placeholder' => __('lang_v1.select_a_date_range'),
                                    'class' => 'form-control',
                                    'id' => 'all_date_filter',
                                    'readonly',
                                ],
                            ) !!}

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group" style="margin-top: 25px;">
                            <button class="btn btn-primary" name="submit" id="submitData">Submit</button>
                        </div>
                    </div>
                </div>

            </div>

            <div style="display:flex;margin:1em;">
                <div style="width:100%;margin:1em;">
                    <div style="display:flex;">
                        <div class="col-lg-3 col-sm-3">
                            <div class="circle-tile ">
                                <div class="circle-tile-heading dash_ele_color1">
                                    <img src="{{ asset('/img/customer.gif') }}" style="height: 80px; width: 70px;"
                                        alt="">
                                </div>
                                <div class="circle-tile-content dash_ele_color1">
                                    <div class="circle-tile-description text-faded dash_ele_color1">Customer</div>
                                    <div class="circle-tile-number text-faded dash_ele_color1">{{ $allCustomer }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-3">
                            <div class="circle-tile ">
                                <div class="circle-tile-heading dash_ele_color5">
                                    <img src="{{ asset('/img/total_sell.gif') }}" style="height: 80px; width: 70px;"
                                        alt="">

                                </div>
                                <div class="circle-tile-content dash_ele_color5">
                                    <div class="circle-tile-description text-faded dash_ele_color5">Sales</div>
                                    <div class="circle-tile-number text-faded dash_ele_color5 total_sell"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-3">
                            <div class="circle-tile">
                                <div class="circle-tile-heading dash_ele_color2">
                                    <img src="{{ asset('/img/draft.gif') }}" style="height: 80px; width: 70px;"
                                        alt="">
                                </div>
                                <div class="circle-tile-content dash_ele_color2">
                                    <div class="circle-tile-description text-faded dash_ele_color2">Drafts</div>
                                    <div class="circle-tile-number text-faded dash_ele_color2 draft"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-3">
                            <div class="circle-tile ">
                                <div class="circle-tile-heading dash_ele_color3">
                                    <img src="{{ asset('/img/transaction.gif') }}" style="height: 80px; width: 70px;"
                                        alt="">
                                </div>
                                <div class="circle-tile-content dash_ele_color3">
                                    <div class="circle-tile-description text-faded dash_ele_color3">Orders</div>
                                    <div class="circle-tile-number text-faded dash_ele_color3 total_transactions"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <strong>
                <p>
                <h3 style="margin-left: 50px;margin-top: -20px;">Follow Up :</h3>
                </p>
            </strong>
            <div style="display:flex;margin:1em;">
                <div style="width:100%;margin:1em;">
                    <div style="display:flex;">
                        <div class="col-lg-4 col-sm-4">
                            <div class="card dash_ele_color5">
                                <div class="" style="display: flex">
                                    <div style="width: 50%" class="title">
                                        <img src="{{ asset('/img/open.gif') }}" style="height: 80px; width: 70px;"
                                            alt="">
                                    </div>
                                    <div style="width: 50%">
                                        <p class="title-text">
                                            Open
                                        </p>
                                        <div class="data">
                                            <p>{{ $openStatus }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4">
                            <div class="card dash_ele_color3">
                                <div class="" style="display: flex">
                                    <div style="width: 50%" class="title">
                                        <img src="{{ asset('/img/work-in-progress.gif') }}"
                                            style="height: 80px; width: 70px;" alt="">
                                    </div>
                                    <div style="width: 50%">
                                        <p class="title-text">
                                            In Process
                                        </p>
                                        <div class="data">
                                            <p>{{ $inProcessStatus }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4">
                            <div class="card dash_ele_color4">
                                <div class="" style="display: flex">
                                    <div style="width: 50%" class="title">
                                        <img src="{{ asset('/img/closed.gif') }}" style="height: 80px; width: 70px;"
                                            alt="">
                                    </div>
                                    <div style="width: 50%">
                                        <p class="title-text">
                                            Closed
                                        </p>
                                        <div class="data">
                                            <p>{{ $closedStatus }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif


        <div style="display:flex;margin:1em; margin-top:8em;">
            <div style="height:400px;width:100%;margin:1em;background:white;">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Sales')])
                    <div id="chart1"></div>
                @endcomponent
            </div>
        </div>

        <div style="display:flex;margin:1em; margin-top:8em;">
            <div style="height:400px;width:100%;margin:1em;background:white;">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('New Orders by Sales Rep')])
                    <canvas id="ordersBySalesRepChart" height="100"></canvas>
                @endcomponent
            </div>
        </div>

        <div style="display: flex; margin: 1em; padding-top: 12em;">
            <div style="flex: 1; height: 400px; background: white; margin: 1em;">
                @component('components.widget', [
                    'class' => 'box-primary',
                    'title' => __('Sales Rep Activity Overview'),
                    'header' => view('components.dateFillter'),
                ])
                    <canvas id="sales_rep_heatmap" style="height:350px;"></canvas>
                @endcomponent

            </div>
        </div>


        <div style="display:flex;margin:1em; margin-top:8em;">
            <div style="height:400px;width:100%;margin:1em;background:white;">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Top Clients by Rep')])
                    <div id="top_clients_leaderboard" style="padding: 1em;"></div>
                @endcomponent
            </div>
        </div>


        <div style="display:flex;margin:1em; margin-top:14em;">
            <div style="width:100%;margin:1em;">

                <div class="panel with-nav-tabs panel-info">
                    <div class="panel-heading">
                        <h3 style="color: #31708f">Recent Transactions</h3>
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#final" data-toggle="tab">@lang('sale.final')</a></li>
                            <li><a href="#quotation" data-toggle="tab">@lang('lang_v1.quotation')</a></li>
                            <li><a href="#draft" data-toggle="tab">@lang('sale.draft')</a></li>
                            <li><a href="#paid_transactions" data-toggle="tab">Paid</a></li>
                            <li><a href="#partial_transactions" data-toggle="tab">Partial</a></li>
                            <li><a href="#due_transactions" data-toggle="tab">Due</a></li>
                        </ul>
                    </div>
                    <div class="panel-body">
                        <div class="tab-content" style="height: 400px;">
                            <div class="tab-pane fade in active" id="final">
                                <div class="table-responsive">
                                    <div id="error"></div>
                                    <table class="table table-hover">
                                        {{-- @if ($final->count() > 0)
                                    @if (isset($final) && !empty($final)) --}}
                                        <thead id="final_head">

                                        </thead>
                                        <tbody id="final_table">
                                        </tbody>
                                        {{-- @endif
                                @else
                                    <p class="text-center">@lang('sale.no_recent_transactions')</p>
                                @endif --}}
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="quotation">
                                <div class="table-responsive">
                                    <div id="error"></div>
                                    <table class="table table-hover">
                                        {{-- @if ($quotation->count() > 0)
                                    @if (isset($quotation) && !empty($quotation)) --}}
                                        <thead id="quotation_head">

                                        </thead>
                                        <tbody id="quotation_table">

                                        </tbody>
                                        {{-- @endif
                                @else
                                    <p class="text-center">@lang('sale.no_recent_transactions')</p>
                                @endif --}}
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="draft">
                                <div class="table-responsive">
                                    <div id="error"></div>
                                    <table class="table table-hover">
                                        {{-- @if ($draft->count() > 0)
                                    @if (isset($draft) && !empty($draft)) --}}
                                        <thead id="draft_head">
                                        </thead>
                                        <tbody id="draft_table">

                                        </tbody>
                                        {{-- @endif
                                @else
                                    <p class="text-center">@lang('sale.no_recent_transactions')</p>
                                @endif --}}
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="paid_transactions">
                                <div class="table-responsive">
                                    <div id="error"></div>
                                    <table class="table table-hover">

                                        <thead id="paid_head">
                                        </thead>
                                        <tbody id="paid_table">

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="partial_transactions">
                                <div class="table-responsive">
                                    <div id="error"></div>
                                    <table class="table table-hover">
                                        {{-- @if ($draft->count() > 0)
                                    @if (isset($draft) && !empty($draft)) --}}
                                        <thead id="partial_head">
                                        </thead>
                                        <tbody id="partial_table">

                                        </tbody>

                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="due_transactions">
                                <div class="table-responsive">
                                    <div id="error"></div>
                                    <table class="table table-hover">
                                        {{-- @if ($draft->count() > 0)
                                    @if (isset($draft) && !empty($draft)) --}}
                                        <thead id="due_head">
                                        </thead>
                                        <tbody id="due_table">

                                        </tbody>
                                        {{-- @endif
                                @else
                                    <p class="text-center">@lang('sale.no_recent_transactions')</p>
                                @endif --}}
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                    <small>&nbsp;&nbsp;&nbsp;&nbsp;last 10 recently added</small>
                </div>
            </div>
        </div>
        <div style="margin: 1em;">
            <div class="row">
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary btn-modal"
                        data-href="{{ route('smartcrm.lead.LeadsCreate') }}" data-container=".contact_modal">
                        <i class="fa fa-plus"></i> Add Lead</button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" onclick="initMap();" type="button" id="LoadButton"><i
                            class='fa fa-map-marker' style='color:#e22727'></i> Load
                        Google Map</button>
                </div>
            </div>
        </div>
        <br>
        <div style="display: flex; margin: 1em;" class="hide" id="GoogleMap">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('')])
                <div id="map" style="width: 100%; height: 400px;"></div>
            @endcomponent
        </div>
        <div class="modal fade" id="followupmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('smartcrm.followup.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Create a follow up</h4>
                        </div>
                        <div class="modal-body row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('contact_id', __('contact.customer') . ':*') !!}
                                    {!! Form::select('contact_id', $customers, null, [
                                        'required',
                                        'class' => 'form-control select2',
                                        'style' => 'width:100%',
                                        'placeholder' => __('Please Select'),
                                        'id' => 'contacts_id',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Subject:*</label>
                                    <input type="text" class="form-control" name="title" required />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Next Schedule At:</label>
                                    <input type="text" class="form-control" name="scheduled_at" id="scheduled_at" />
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('status', __('Status') . ':') !!}
                                    {!! Form::select('status', $status, null, ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
                                </div>
                            </div>
                            <!--<div class="col-md-4">-->
                            <!--    <div class="form-group">-->
                            <!--        {!! Form::label('priority', __('Priority') . ':') !!}-->
                            <!--        {!! Form::select('priority', $priorities, null, ['class' => 'form-control select2', 'style' => 'width:100%']) !!}-->
                            <!--    </div>-->
                            <!--</div>-->
                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('channel', __('Channel') . ':') !!}
                                    {!! Form::select('channel', $channel, null, ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
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
                                <input type="text" id="tags" name="tags" class="form-control tags-input"
                                    style="width:100%;" />
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
        <div class="modal fade contact_modal" tabindex="-1" role="dialog" data-backdrop="static"
            aria-labelledby="gridSystemModalLabel">
        </div>
    @endsection
    @include('smartcrm::dashboard-js')
