@extends('layouts.app')
@section('title', __( 'State Tax Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'State Tax Report' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('contact_filter_customer_id', __('contact.customer') . ':') !!}
                    {!! Form::select('contact_filter_customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>State:</label>
                    <select class="form-control state_select">
                        @foreach ($states as $item)
                            <option>{{ $item->state }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <input type="hidden" id="date" name="date" value="">
                <div class="form-group">
                    {!! Form::label('transaction_date_filter', __('report.date_range') . ':') !!}
                    {!! Form::text('transaction_date_filter',
                        @format_date('first day of this month') . ' ~ ' . @format_date('last day of this month'),
                        ['placeholder' => __('lang_v1.select_a_date_range'),
                        'class' => 'form-control', 'id' => 'transaction_date_filter', 'readonly']);
                    !!}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="transaction_report_table">
                        <thead>
                        <tr>
                            <td>Invoice Date</td>
                            <td>Invoice No</td>
                            <td>Tax Id</td>
                            <td>Name</td>
                            <td>Address</td>
                            <td>City</td>
                            <td>State</td>
                            <td>Zip</td>
                            <td>Payment Status</td>
                            <td>Qty</td>
                            <td>Price</td>
                            <td>Order Note</td>
                            <td>Tax Amount</td>
                        </tr>
                        </thead>
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
    $('#transaction_date_filter').daterangepicker({
        ranges: ranges,
        autoUpdateInput: true,
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        locale: {
            format: moment_date_format
        }
    });

    $(function () {
        item_inventory_report='';
        var date_filter = $('#transaction_date_filter').val();
        getInventoryItems(date_filter,'');
    });

    function getInventoryItems(date_filter,customer_id) {
        if(date_filter){
            var date_filter = date_filter;
        } else {
            var date_filter = '';
        }
        if(customer_id){
            var customer_id = customer_id;
        } else {
            var customer_id = '';
        }
        // alert(customer_id);
        item_inventory_report = $('#transaction_report_table').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,

            ajax: {
                url: '{{ route("report.get_state_tax") }}',
                type: "POST",
                data: function (d) {
                    d.date_filter = date_filter;
                    d.customer_id = customer_id;
                    d.state = $('.state_select').val();
                },
            },
            columns: [
                { data: 'invoice_date', name: 'invoice_date'},
                { data: 'invoice_no', name: 'invoice_no'},
                { data: 'tax_id', name: 'tax_id'},
                { data: 'name', name: 'name'},
                { data: 'address', name: 'address'},
                { data: 'city', name: 'city'},
                { data: 'state', name: 'state'},
                { data: 'zip_code', name: 'zip_code'},
                { data: 'payment_status', name: 'payment_status'},
                { data: 'item_qty', name: 'item_qty'},
                { data: 'price', name: 'price'},
                { data: 'order_note', name: 'order_note'},
                { data: 'tax_amount', name: 'tax_amount'},
            ]
        });
    }

    $('#transaction_date_filter, #contact_filter_customer_id, .state_select').change(function () {
        var date_filter = $('#transaction_date_filter').val();
        var customer_id = $('#contact_filter_customer_id').val();
        item_inventory_report.ajax.reload();
        getInventoryItems(date_filter,customer_id);
    });
</script>
@endsection