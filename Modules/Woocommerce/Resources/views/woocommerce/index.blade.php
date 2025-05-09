@extends('layouts.app')
@section('title', __('woocommerce::lang.woocommerce'))
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" integrity="sha512-xmGTNt20S0t62wHLmQec2DauG9T+owP9e6VU8GigI0anN7OXLip9i7IwEhelasml2osdxX71XcYm6BQunTQeQg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection
@section('content')
    @include('woocommerce::layouts.nav')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('woocommerce::lang.woocommerce')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @php
            $is_superadmin = auth()
                ->user()
                ->can('superadmin');
        @endphp
        <div class="row">
            @if (!empty($alerts['connection_failed']))
                <div class="col-sm-12">
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <ul>
                            <li>{{ $alerts['connection_failed'] }}</li>
                        </ul>
                    </div>
                </div>
            @endif


            <div class="col-sm-6">
                @if ($is_superadmin ||
                    auth()->user()->can('woocommerce.syc_categories'))
                    <div class="col-sm-12">
                        <div class="box box-solid">
                            <div class="box-header">
                                <i class="fa fa-tags"></i>
                                <h3 class="box-title">@lang('woocommerce::lang.sync_product_categories'):</h3>
                            </div>
                            <div class="box-body">
                                @if (!empty($alerts['not_synced_cat']) || !empty($alerts['updated_cat']))
                                    <div class="col-sm-12">
                                        <div class="alert alert-warning alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert"
                                                aria-hidden="true">×</button>
                                            <ul>
                                                @if (!empty($alerts['not_synced_cat']))
                                                    <li>{{ $alerts['not_synced_cat'] }}</li>
                                                @endif
                                                @if (!empty($alerts['updated_cat']))
                                                    <li>{{ $alerts['updated_cat'] }}</li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-sm-6">
                                    <button type="button" class="btn btn-primary btn-block" id="sync_product_categories">
                                        <i class="fa fa-refresh"></i> @lang('woocommerce::lang.sync')</button>
                                    <span class="last_sync_cat"></span>
                                </div>
                                <div class="col-sm-12">
                                    <br>
                                    <button type="button" class="btn btn-danger btn-xs" id="reset_categories"> <i
                                            class="fa fa-undo"></i> @lang('woocommerce::lang.reset_synced_cat')</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="box box-solid">
                            <div class="box-header">
                                <i class="fa fa-tags"></i>
                                <h3 class="box-title">Sync Brands:</h3>
                            </div>
                            <div class="box-body">
                                @if (!empty($alerts['not_synced_brands']) || !empty($alerts['updated_brands']))
                                    <div class="col-sm-12">
                                        <div class="alert alert-warning alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert"
                                                aria-hidden="true">×</button>
                                            <ul>
                                                @if (!empty($alerts['not_synced_brands']))
                                                    <li>{{ $alerts['not_synced_brands'] }}</li>
                                                @endif
                                                @if (!empty($alerts['updated_brands']))
                                                    <li>{{ $alerts['updated_brands'] }}</li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-sm-6">
                                    <button type="button" class="btn btn-primary btn-block" id="sync_product_brands"> <i
                                            class="fa fa-refresh"></i> @lang('woocommerce::lang.sync')</button>
                                    <span class="last_sync_brands"></span>
                                </div>
                                <div class="col-sm-12">
                                    <br>
                                    <button type="button" class="btn btn-danger btn-xs" id="reset_brands"> <i
                                            class="fa fa-undo"></i> Reset synced brands</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- $is_superadmin ||
                    auth()->user()->can('woocommerce.map_tax_rates') || --}}
                @if ( false)
                    <div class="col-sm-12">
                        <div class="box box-solid">
                            <div class="box-header">
                                <i class="fa fa-percent"></i>
                                <h3 class="box-title">@lang('woocommerce::lang.map_tax_rates'):</h3>
                            </div>
                            <div class="box-body" style="height: 500px;overflow-x:auto;">
                                {!! Form::open([
                                    'action' => '\Modules\Woocommerce\Http\Controllers\WoocommerceController@mapTaxRates',
                                    'method' => 'post',
                                ]) !!}
                                <div class="col-xs-12">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>@lang('woocommerce::lang.pos_tax_rate')</th>
                                                <th>@lang('woocommerce::lang.equivalent_woocommerce_tax_rate')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (!empty($tax_rates))
                                                @foreach ($tax_rates as $tax_rate)
                                                    <tr>
                                                        <td>{{ $tax_rate->name }}:</td>
                                                        <td>{!! Form::select('taxes[' . $tax_rate->id . ']', $woocommerce_tax_rates, $tax_rate->woocommerce_tax_rate_id, [
                                                            'class' => 'form-control',
                                                        ]) !!}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2">
                                                    <button type="submit" class="btn btn-danger pull-right">
                                                        @lang('messages.save')
                                                    </button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                @endif
            </div>


            @if ($is_superadmin ||
            auth()->user()->can('woocommerce.sync_orders'))
                <div class="col-sm-6">
                    <div class="col-sm-12">
                        <div class="box box-solid">
                            <div class="box-header">
                                <i class="fa fa-cart-plus"></i>
                                <h3 class="box-title">@lang('woocommerce::lang.sync_orders'):</h3>
                            </div>
                            <div class="box-body">
                                <div class="col-sm-6">
                                    <button type="button" class="btn btn-success btn-block" id="sync_orders"> <i
                                            class="fa fa-refresh"></i> @lang('woocommerce::lang.sync')</button>
                                    <span class="last_sync_orders"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="box box-solid">
                            <div class="box-header">
                                <i class="fa fa-cart-plus"></i>
                                <h3 class="box-title">Sync Selected Orders:</h3>
                            </div>
                            <div class="box-body">
                                <div class="col-sm-12" style="display: flex;flex-direction: column;">
                                    <label style="width:100%;">Enter Web Order Ids:</label>
                                    <input type="text" id="web_ids" class="form-control" style="width:100%;" />
                                </div>
                                <div class="col-sm-6" style="margin-top: 10px;">
                                    <button type="button" class="btn btn-info btn-block" id="sync_orders_select">
                                        <i class="fa fa-refresh"></i>Sync Selected</button>
                                    <span class="last_sync_orders"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>


        <div class="modal fade" tabindex="-1" role="dialog" id="create_products_modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Products to be synced</h4>
                    </div>
                    <div class="modal-body" style="height: 500px;overflow-x:auto;">
                        <table class="table">
                            @foreach ($alerts['not_synced_product'] ?? [] as $item)
                                <tr>
                                    <td>{{ $item->name . " - " . $item->sku }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->


        <div class="modal fade" tabindex="-1" role="dialog" id="update_products_modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Products to be synced</h4>
                    </div>
                    <div class="modal-body" style="height: 500px;overflow-x:auto;">
                        <table class="table">
                            @foreach ($alerts['not_updated_product'] ?? [] as $item)
                                <tr>
                                    <td>{{ $item->name . " - " . $item->sku }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </section>

    <style>
        .alert-box-shadow {
            box-shadow: 0 0 2rem 0 rgba(136, 152, 170, .15) !important;
        }
    </style>
@stop
@section('javascript')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js" integrity="sha512-9UR1ynHntZdqHnwXKTaOm1s6V9fExqejKvg5XMawEMToW4sSw+3jtLrYfZPijvnwnnE8Uol1O9BcAskoxgec+g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            var tagInputEle = $('#web_ids');
            tagInputEle.tagsinput();



            syncing_text = '<i class="fa fa-refresh fa-spin"></i> ' + "{{ __('woocommerce::lang.syncing') }}...";
            update_sync_date();

            //Sync Product Categories
            $('#sync_product_categories').click(function() {
                $(window).bind('beforeunload', function() {
                    return true;
                });
                var btn_html = $(this).html();
                $(this).html(syncing_text);
                $(this).attr('disabled', true);
                $.ajax({
                    url: "{{ action('\Modules\Woocommerce\Http\Controllers\WoocommerceController@syncCategories') }}",
                    dataType: "json",
                    timeout: 0,
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            update_sync_date();
                        } else {
                            toastr.error(result.msg);
                        }
                        $('#sync_product_categories').html(btn_html);
                        $('#sync_product_categories').removeAttr('disabled');
                        $(window).unbind('beforeunload');
                    }
                });
            });

            //Sync Product Brands
            $('#sync_product_brands').click(function() {
                $(window).bind('beforeunload', function() {
                    return true;
                });
                var btn_html = $(this).html();
                $(this).html(syncing_text);
                $(this).attr('disabled', true);
                $.ajax({
                    url: "{{ action('\Modules\Woocommerce\Http\Controllers\WoocommerceController@syncBrands') }}",
                    dataType: "json",
                    timeout: 0,
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            update_sync_date();
                        } else {
                            toastr.error(result.msg);
                        }
                        $('#sync_product_brands').html(btn_html);
                        $('#sync_product_brands').removeAttr('disabled');
                        $(window).unbind('beforeunload');
                    }
                });
            });

            //Sync Products
            $('.sync_products').click(function() {
                $(window).bind('beforeunload', function() {
                    return true;
                });
                var btn = $(this);
                var btn_html = btn.html();
                btn.html(syncing_text);
                btn.attr('disabled', true);

                sync_products(btn, btn_html);
            });

            //Sync Products Delete
            $('.sync_product_delete').click(function() {
                $(window).bind('beforeunload', function() {
                    return true;
                });
                var btn = $(this);
                var btn_html = btn.html();
                btn.html(syncing_text);
                btn.attr('disabled', true);

                sync_products_delete(btn, btn_html);
            });

            //Sync Orders
            $('#sync_orders').click(function() {
                $(window).bind('beforeunload', function() {
                    return true;
                });
                var btn = $(this);
                var btn_html = btn.html();
                btn.html(syncing_text);
                btn.attr('disabled', true);

                $.ajax({
                    url: "{{ action('\Modules\Woocommerce\Http\Controllers\WoocommerceController@syncOrders') }}",
                    dataType: "json",
                    timeout: 0,
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            update_sync_date();
                        } else {
                            toastr.error(result.msg);
                        }
                        btn.html(btn_html);
                        btn.removeAttr('disabled');
                        $(window).unbind('beforeunload');
                    }
                });
            });


            //Sync Orders
            $('#sync_orders_select').click(function() {
                if(tagInputEle.val() == ""){
                    toastr.warning('Please enter atleast 1 order id');
                }
                else{
                    var btn = $(this);
                    var btn_html = btn.html();
                    btn.html(syncing_text);
                    btn.attr('disabled', true);

                    $.ajax({
                        url: "{{ action('\Modules\Woocommerce\Http\Controllers\WoocommerceController@syncOrdersSelect') }}",
                        type: 'POST',
                        dataType: "json",
                        data: {
                            'ids': tagInputEle.val()
                        },
                        timeout: 0,
                        success: function(result) {
                            if (result.success) {
                                toastr.success(result.msg);
                                update_sync_date();
                            } else {
                                toastr.error(result.msg);
                            }
                            btn.html(btn_html);
                            btn.removeAttr('disabled');
                        }
                    });
                }

            });

        });

        function update_sync_date() {
            $.ajax({
                url: "{{ action('\Modules\Woocommerce\Http\Controllers\WoocommerceController@getSyncLog') }}",
                dataType: "json",
                timeout: 0,
                success: function(data) {
                    if (data.categories) {
                        $('span.last_sync_cat').html('<small>{{ __('woocommerce::lang.last_synced') }}: ' +
                            data.categories + '</small>');
                    }
                    if (data.brands) {
                        $('span.last_sync_brands').html('<small>{{ __('woocommerce::lang.last_synced') }}: ' +
                            data.brands + '</small>');
                    }
                    if (data.new_products) {
                        $('span.last_sync_new_products').html(
                            '<small>{{ __('woocommerce::lang.last_synced') }}: ' + data.new_products +
                            '</small>');
                    }
                    if (data.all_products) {
                        $('span.last_sync_all_products').html(
                            '<small>{{ __('woocommerce::lang.last_synced') }}: ' + data.all_products +
                            '</small>');
                    }
                    if (data.orders) {
                        $('span.last_sync_orders').html('<small>{{ __('woocommerce::lang.last_synced') }}: ' +
                            data.orders + '</small>');
                    }

                }
            });
        }

        //Reset Synced Categories
        $(document).on('click', 'button#reset_brands', function() {
            var checkbox = document.createElement("div");
            checkbox.setAttribute('class', 'checkbox');
            checkbox.innerHTML =
                '<label><input type="checkbox" id="yes_reset_brands"> {{ __('woocommerce::lang.yes_reset') }}</label>';
            swal({
                title: LANG.sure,
                text: "All synced brands will be reset",
                icon: "warning",
                content: checkbox,
                buttons: true,
                dangerMode: true,
            }).then((confirm) => {
                if (confirm) {
                    if ($('#yes_reset_brands').is(":checked")) {
                        $(window).bind('beforeunload', function() {
                            return true;
                        });
                        var btn = $(this);
                        btn.attr('disabled', true);
                        $.ajax({
                            url: "{{ action('\Modules\Woocommerce\Http\Controllers\WoocommerceController@resetBrands') }}",
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                } else {
                                    toastr.error(result.msg);
                                }
                                btn.removeAttr('disabled');
                                $(window).unbind('beforeunload');
                                location.reload();
                            }
                        });
                    }
                }
            });
        });

        //Reset Synced Categories
        $(document).on('click', 'button#reset_categories', function() {
            var checkbox = document.createElement("div");
            checkbox.setAttribute('class', 'checkbox');
            checkbox.innerHTML =
                '<label><input type="checkbox" id="yes_reset_cat"> {{ __('woocommerce::lang.yes_reset') }}</label>';
            swal({
                title: LANG.sure,
                text: "{{ __('woocommerce::lang.confirm_reset_cat') }}",
                icon: "warning",
                content: checkbox,
                buttons: true,
                dangerMode: true,
            }).then((confirm) => {
                if (confirm) {
                    if ($('#yes_reset_cat').is(":checked")) {
                        $(window).bind('beforeunload', function() {
                            return true;
                        });
                        var btn = $(this);
                        btn.attr('disabled', true);
                        $.ajax({
                            url: "{{ action('\Modules\Woocommerce\Http\Controllers\WoocommerceController@resetCategories') }}",
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                } else {
                                    toastr.error(result.msg);
                                }
                                btn.removeAttr('disabled');
                                $(window).unbind('beforeunload');
                                location.reload();
                            }
                        });
                    }
                }
            });
        });

        //Reset Synced products
        $(document).on('click', 'button#reset_products', function() {
            var checkbox = document.createElement("div");
            checkbox.setAttribute('class', 'checkbox');
            checkbox.innerHTML =
                '<label><input type="checkbox" id="yes_reset_product"> {{ __('woocommerce::lang.yes_reset') }}</label>';
            swal({
                title: LANG.sure,
                text: "{{ __('woocommerce::lang.confirm_reset_product') }}",
                icon: "warning",
                content: checkbox,
                buttons: true,
                dangerMode: true,
            }).then((confirm) => {
                if (confirm) {
                    if ($('#yes_reset_product').is(":checked")) {
                        $(window).bind('beforeunload', function() {
                            return true;
                        });
                        var btn = $(this);
                        btn.attr('disabled', true);
                        $.ajax({
                            url: "{{ action('\Modules\Woocommerce\Http\Controllers\WoocommerceController@resetProducts') }}",
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                } else {
                                    toastr.error(result.msg);
                                }
                                btn.removeAttr('disabled');
                                $(window).unbind('beforeunload');
                                location.reload();
                            }
                        });
                    }
                }
            });
        });

        function sync_products(btn, btn_html, offset = 0) {
            var type = btn.data('sync-type');
            $.ajax({
                url: "{{ action('\Modules\Woocommerce\Http\Controllers\WoocommerceController@syncProducts') }}?type=" +
                    type + "&offset=" + offset,
                dataType: "json",
                timeout: 0,
                success: function(result) {
                    if (result.success) {
                        if (result.total_products > 0) {
                            offset++;
                            sync_products(btn, btn_html, offset)
                        } else {
                            update_sync_date();
                            btn.html(btn_html);
                            btn.removeAttr('disabled');
                            $(window).unbind('beforeunload');
                        }
                        toastr.success(result.msg);

                    } else {
                        toastr.error(result.msg);
                        btn.html(btn_html);
                        btn.removeAttr('disabled');
                        $(window).unbind('beforeunload');
                    }
                }
            });
        }

        function sync_products_delete(btn, btn_html, offset = 0) {

            $.ajax({
                url: "{{ action('\Modules\Woocommerce\Http\Controllers\WoocommerceController@syncProductsDelete') }}?offset=" + offset,
                dataType: "json",
                timeout: 0,
                success: function(result) {
                    if (result.success) {
                        if (result.total_products > 0) {
                            offset++;
                            toastr.success(result.msg);
                            sync_products_delete(btn, btn_html, offset);
                        } else {
                            update_sync_date();
                            btn.html(btn_html);
                            btn.removeAttr('disabled');
                            $(window).unbind('beforeunload');
                        }
                    } else {
                        toastr.error(result.msg);
                        btn.html(btn_html);
                        btn.removeAttr('disabled');
                        $(window).unbind('beforeunload');
                    }
                }
            });

        }
    </script>
@endsection
