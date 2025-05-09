$(document).ready(function () {
    if ($('input#iraqi_selling_price_adjustment').length > 0) {
        iraqi_selling_price_adjustment = true
    } else {
        iraqi_selling_price_adjustment = false
    }

    //Date picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    })

    $('#received_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    })

    //get suppliers
    $('#supplier_id')
        .select2({
            ajax: {
                url: '/purchases/get_suppliers',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                    }
                },
                processResults: function (data) {
                    return {
                        results: data,
                    }
                },
            },
            minimumInputLength: 1,
            escapeMarkup: function (m) {
                return m
            },
            templateResult: function (data) {
                if (!data.id) {
                    return data.text
                }
                var html = data.business_name + '- ( ' + data.text + ' - ' + data.contact_id + ' )'
                return html
            },
            language: {
                noResults: function () {
                    var name = $('#supplier_id').data('select2').dropdown.$search.val()
                    return (
                        '<button type="button" data-name="' +
                        name +
                        '" class="btn btn-link add_new_supplier"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp; ' +
                        __translate('add_name_as_new_supplier', { name: name }) +
                        '</button>'
                    )
                },
            },
        })
        .on('select2:select', function (e) {
            var data = e.params.data
            $('#pay_term_number').val(data.pay_term_number)
            $('#pay_term_type').val(data.pay_term_type)
            $('#advance_balance_text').text(__currency_trans_from_en(data.balance), true)
            $('#advance_balance').val(data.balance)
        })

    //Quick add supplier
    $(document).on('click', '.add_new_supplier', function () {
        $('#supplier_id').select2('close')
        var name = $(this).data('name')
        $('.contact_modal').find('input#name').val(name)
        $('.contact_modal')
            .find('select#contact_type')
            .val('supplier')
            .closest('div.contact_type_div')
            .addClass('hide')
        $('.contact_modal').modal('show')
    })

    $('form#quick_add_contact')
        .submit(function (e) {
            e.preventDefault()
        })
        .validate({
            rules: {
                contact_id: {
                    remote: {
                        url: '/contacts/check-contact-id',
                        type: 'post',
                        data: {
                            contact_id: function () {
                                return $('#contact_id').val()
                            },
                            hidden_id: function () {
                                if ($('#hidden_id').length) {
                                    return $('#hidden_id').val()
                                } else {
                                    return ''
                                }
                            },
                        },
                    },
                },
            },
            messages: {
                contact_id: {
                    remote: LANG.contact_id_already_exists,
                },
            },
            submitHandler: function (form) {
                $(form).find('button[type="submit"]').attr('disabled', true)
                var data = $(form).serialize()
                $.ajax({
                    method: 'POST',
                    url: $(form).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function (result) {
                        if (result.success == true) {
                            $('select#supplier_id').append(
                                $('<option>', { value: result.data.id, text: result.data.name })
                            )
                            $('select#supplier_id').val(result.data.id).trigger('change')
                            $('div.contact_modal').modal('hide')
                            toastr.success(result.msg)
                        } else {
                            toastr.error(result.msg)
                        }
                    },
                })
            },
        })
    $('.contact_modal').on('hidden.bs.modal', function () {
        $('form#quick_add_contact').find('button[type="submit"]').removeAttr('disabled')
        $('form#quick_add_contact')[0].reset()
    })

    $(document).on('keyup', '#search_product_one', function () {
        $('#open-datatable').show()
        $('#open-datatable-one').show()

        $('#open-datatable').DataTable().destroy()
        let table = $('#open-datatable')
        let location_id = $('#location_id').val()
        let term = $('#search_product_one').val()
        let data_id = $('#filter_by_lead_status').val()
        table.DataTable({
            // serverSide: true,
            responsive: true,
            processing: false,
            //searching: false,
            paging: false,
            info: false,
            lengthChange: false,
            buttons: [],
            ajax: {
                url: '/purchases/get_products?location_id=' + location_id + '&term=' + term,
                type: 'GET',
                data: null,
            },
            columns: [
                { data: 'item_code', name: 'products.item_code' },
                { data: 'name', name: 'products.name' },
                { data: 'dpp_inc_tax', name: 'variations.dpp_inc_tax' },
                { data: 'default_sell_price', name: 'variations.default_sell_price' },
                { data: 'sku', name: 'products.sku' },
            ],
        })
    })

    //Add products
    if ($('#search_product_two').length > 0) {
        var i = 0
        $('#search_product_two')
            .autocomplete({
                source: function (request, response) {
                    $.getJSON(
                        '/purchases/get_products_one',
                        {
                            location_id: $('#location_id').val(),
                            term: request.term,
                            product_status: $('#product_status').val(),
                        },
                        response
                    )
                },
                minLength: 2,
                response: function (event, ui) {
                    // if (ui.content.length == 1) {
                    //     ui.item = ui.content[0];
                    //     $(this)
                    //         .data('ui-autocomplete')
                    //         ._trigger('select', 'autocompleteselect', ui);
                    //     // $(this).autocomplete('close');
                    // }
                    $('#search_prod_not_sell').hide()
                    $('#search_prod').hide()
                    if (ui.content.length === 0) {
                        $('#search_prod').fadeOut(0)
                        $('#search_prod').show()
                        $('#search_prod').fadeIn()
                        // $("#search_prod").fadeOut(5000);
                        // var term = $(this).data('ui-autocomplete').term;
                        // swal({
                        //     title: LANG.no_products_found,
                        //     text: __translate('add_name_as_new_product', { term: term }),
                        //     buttons: [LANG.cancel, LANG.ok],
                        // }).then(value => {
                        //     if (value) {
                        //         var container = $('.quick_add_product_modal');
                        //         $.ajax({
                        //             url: '/products/quick_add?product_name=' + term,
                        //             dataType: 'html',
                        //             success: function(result) {
                        //                 $(container)
                        //                     .html(result)
                        //                     .modal('show');
                        //             },
                        //         });
                        //     }
                        // });
                    }
                    $('input#search_for_value').val('')
                },
                select: function (event, ui) {
                    var edit = $(this).data('edit')
                    if ($('input#search_for_value').length == 0) {
                        $(this).prepend(
                            '<input type="hidden" id="search_for_value" value="' +
                                $(this).val() +
                                '" />'
                        )
                    } else {
                        $('input#search_for_value').val($(this).val())
                    }
                    $(this).val(null)
                    get_purchase_entry_row(ui.item.product_id, ui.item.variation_id, edit)

                    var div_height = $('#entry_table_row').height()
                    var first = $('#div_height').val()
                    // div_height 600
                    // console.log('div_height:', div_height)
                    // console.log('first:', first)

                    i = i + 1
                    console.log('row', i)
                    if (i == 1) {
                        $('#div_height').val(55)
                    }
                    if (i > 1) {
                        if (div_height == 55 && first === 0) {
                            $('#div_height').val(55)
                        } else {
                            var second = parseInt(first) + 96
                            $('#div_height').val(second)
                        }
                        var check = $('#div_height').val()
                        // console.log('check:', check)
                    }

                    if (i == 6 || i >= 6) {
                        // console.log('style-height: ', '600')
                        if (div_height > 600) {
                            // console.log('success');
                            $('#entry_table_row').addClass('table-height')
                        }
                    }
                },
                close: function (event, ui) {
                    if ($('input#search_for_value').val() != '') {
                        $('input#search_product_two').val($('input#search_for_value').val())
                    }

                    if (event.keyCode === $.ui.keyCode.ESCAPE) {
                        $('input#search_product_two').val('')
                        $('input#search_for_value').val('')
                        $('.ui-autocomplete').hide()
                    } else if ($('input#search_product_two').val() != '') {
                        $('input#search_product_two').focus()
                        $('.ui-autocomplete').show()
                    }
                },
            })
            .autocomplete('instance')._renderItem = function (ul, item) {
            $('#search_prod_not_sell').hide()
            $('#search_prod').hide()
            return $('<li>')
                .append('<div>' + item.text + '</div>')
                .appendTo(ul)
        }
    }
    $(document).on('click', '.ui-menu-item', function () {
        if (!$('div').hasClass('toast-error')) {
            $(this).css({ 'background-color': '#808080', color: 'white' })
        }
    })
    $(document).on('click', '.remove_purchase_entry_row', function () {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(value => {
            if (value) {
                $(this).closest('tr').remove()
                update_table_total()
                update_grand_total()
                update_table_sr_number()
                // var row_id =  $(this).data("row");
                var div_height = $('#entry_table_row').height()
                console.log('remove div_height:', div_height)
                var first = $('#div_height').val()
                console.log('first:', first)

                if (div_height > 55) {
                    var second = parseInt(first) - 96
                    $('#div_height').val(second)
                }
                if (div_height == 55 && first == 55) {
                    var second_first = parseInt(first) - 55
                    $('#div_height').val(second_first)
                }
                var check = $('#div_height').val()
                console.log('second:', check)
                if (div_height > check) {
                    $('#entry_table_row').removeClass('table-height')
                    console.log('success remove', check)
                }
            }
        })
    })

    //On Change of quantity
    $(document).on('change', '.purchase_quantity', function () {
        var row_id = $(this).data('row')
        var row = $(this).closest('tr')
        var quantity = __read_number($(this), true)

        var product_on_hand = $('#' + row_id + '_product_on_hand').val()
        var _row_product_on_hand = parseFloat(product_on_hand) + parseFloat(quantity)
        row.find('.row_on_hand').text(__currency_trans_from_en(_row_product_on_hand, false, true))
        __write_number(row.find('input.row_row_on_hand_2'), _row_product_on_hand, true)
        $('.' + row_id + '_show_Hand').removeClass('hide')
        $('.' + row_id + '_show_Hand').addClass('show')

        // console.log('row id:', row_id)
        // console.log('rows list class: ', '.'+row_id+'_show_Hand')
        // console.log('product_on_hand:', product_on_hand)
        // console.log('onhand: ', _row_product_on_hand)

        // var purchase_before_tax = __read_number(row.find('input.purchase_unit_cost'), true);
        // var purchase_after_tax = __read_number(
        //     row.find('input.purchase_unit_cost_after_tax'),
        //     true
        // );
        var purchase_before_discount = __read_number(
            row.find('input.purchase_unit_cost_without_discount'),
            true
        )
        var additional_charges = __read_number(row.find('input.inline_discounts'), true)

        //Calculate sub totals
        var purchase_after_tax = quantity * purchase_before_discount
        var sub_total_after_tax = purchase_after_tax + additional_charges

        // var sub_total_before_tax = quantity * purchase_before_tax;
        // var sub_total_after_tax = quantity * purchase_after_tax;

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        )
        __write_number(row.find('input.row_subtotal_before_tax_hidden'), sub_total_after_tax, true)

        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        )
        // __write_number(row.find('input.purchase_unit_cost'), sub_total_after_tax, true);
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true)

        update_table_total()
        update_grand_total()
        discountChargesRemove()
    })

    $(document).on('change', '.purchase_unit_cost_without_discount', function () {
        var purchase_before_discount = __read_number($(this), true)

        var row = $(this).closest('tr')
        var additional_charges = __read_number(row.find('input.inline_discounts'), true)
        var quantity = __read_number(row.find('input.purchase_quantity'), true)

        //Calculations.
        // var purchase_before_tax =
        //     parseFloat(purchase_before_discount) -
        //     __calculate_amount('percentage', discount_percent, purchase_before_discount);

        // __write_number(row.find('input.purchase_unit_cost'), purchase_before_tax, true);

        //var sub_total_before_tax = quantity * purchase_before_discount;

        var purchase_after_tax = quantity * purchase_before_discount //24-05-2021
        var sub_total_after_tax = purchase_after_tax + additional_charges //24-05-2021
        // var sub_total_after_tax = purchase_before_discount;    //24-05-2021

        //Tax
        // var tax_rate = parseFloat(
        //     row
        //         .find('select.purchase_line_tax_id')
        //         .find(':selected')
        //         .data('tax_amount')
        // );
        // var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax);

        // var purchase_after_tax = purchase_before_tax + tax;
        // var sub_total_after_tax = quantity * purchase_after_tax;

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        )
        __write_number(row.find('input.row_subtotal_before_tax_hidden'), sub_total_after_tax, true)

        __write_number(row.find('input.purchase_unit_cost_after_tax'), sub_total_after_tax, true)
        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        )
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true)

        row.find('.purchase_product_unit_tax_text').text(__currency_trans_from_en(tax, false, true))
        __write_number(row.find('input.purchase_unit_cost'), purchase_before_discount, true)
        __write_number(row.find('input.purchase_product_unit_tax'), tax, true)

        update_inline_profit_percentage(row)
        update_table_total()
        update_grand_total()
        discountChargesRemove()
    })

    $(document).on('change', '.inline_discounts', function () {
        var row = $(this).closest('tr')

        var additional_charges = __read_number($(this), true)

        var quantity = __read_number(row.find('input.purchase_quantity'), true)
        var purchase_before_discount = __read_number(
            row.find('input.purchase_unit_cost_without_discount'),
            true
        )

        var purchase_after_tax = quantity * purchase_before_discount
        var sub_total_after_tax = purchase_after_tax + additional_charges

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        )
        __write_number(row.find('input.row_subtotal_before_tax_hidden'), sub_total_after_tax, true)
        __write_number(row.find('input.purchase_unit_cost'), sub_total_after_tax, true)
        __write_number(row.find('input.purchase_unit_cost_after_tax'), sub_total_after_tax, true)
        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        )
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true)
        row.find('.purchase_product_unit_tax_text').text(__currency_trans_from_en(tax, false, true))
        __write_number(row.find('input.purchase_product_unit_tax'), tax, true)

        update_inline_profit_percentage(row)
        update_table_total()
        update_grand_total()
    })

    // $(document).on('change', '.inline_discounts', function() {
    //     var row = $(this).closest('tr');

    //     var discount_percent = __read_number($(this), true);

    //     var quantity = __read_number(row.find('input.purchase_quantity'), true);
    //     var purchase_before_discount = __read_number(
    //         row.find('input.purchase_unit_cost_without_discount'),
    //         true
    //     );

    //     //Calculations.
    //     var purchase_before_tax =
    //         parseFloat(purchase_before_discount) -
    //         __calculate_amount('percentage', discount_percent, purchase_before_discount);

    //     __write_number(row.find('input.purchase_unit_cost'), purchase_before_tax, true);

    //     var sub_total_before_tax = quantity * purchase_before_tax;

    //     //Tax
    //     var tax_rate = parseFloat(
    //         row
    //             .find('select.purchase_line_tax_id')
    //             .find(':selected')
    //             .data('tax_amount')
    //     );
    //     var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax);

    //     var purchase_after_tax = purchase_before_tax + tax;
    //     var sub_total_after_tax = quantity * purchase_after_tax;

    //     row.find('.row_subtotal_before_tax').text(
    //         __currency_trans_from_en(sub_total_before_tax, false, true)
    //     );
    //     __write_number(
    //         row.find('input.row_subtotal_before_tax_hidden'),
    //         sub_total_before_tax,
    //         true
    //     );

    //     __write_number(row.find('input.purchase_unit_cost_after_tax'), purchase_after_tax, true);
    //     row.find('.row_subtotal_after_tax').text(
    //         __currency_trans_from_en(sub_total_after_tax, false, true)
    //     );
    //     __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);
    //     row.find('.purchase_product_unit_tax_text').text(
    //         __currency_trans_from_en(tax, false, true)
    //     );
    //     __write_number(row.find('input.purchase_product_unit_tax'), tax, true);

    //     update_inline_profit_percentage(row);
    //     update_table_total();
    //     update_grand_total();
    // });

    $(document).on('change', '.purchase_unit_cost', function () {
        var row = $(this).closest('tr')
        var quantity = __read_number(row.find('input.purchase_quantity'), true)
        var purchase_before_tax = __read_number($(this), true)

        var sub_total_before_tax = quantity * purchase_before_tax

        //Update unit cost price before discount
        var discount_percent = __read_number(row.find('input.inline_discounts'), true)
        var purchase_before_discount = __get_principle(purchase_before_tax, discount_percent, true)
        __write_number(
            row.find('input.purchase_unit_cost_without_discount'),
            purchase_before_discount,
            true
        )

        //Tax
        var tax_rate = parseFloat(
            row.find('select.purchase_line_tax_id').find(':selected').data('tax_amount')
        )
        var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax)

        var purchase_after_tax = purchase_before_tax + tax
        var sub_total_after_tax = quantity * purchase_after_tax

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_before_tax, false, true)
        )
        __write_number(row.find('input.row_subtotal_before_tax_hidden'), sub_total_before_tax, true)

        row.find('.purchase_product_unit_tax_text').text(__currency_trans_from_en(tax, false, true))
        __write_number(row.find('input.purchase_product_unit_tax'), tax, true)

        //row.find('.purchase_product_unit_tax_text').text( tax );
        __write_number(row.find('input.purchase_unit_cost_after_tax'), purchase_after_tax, true)
        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        )
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true)

        update_inline_profit_percentage(row)
        update_table_total()
        update_grand_total()
    })

    $(document).on('change', 'select.purchase_line_tax_id', function () {
        var row = $(this).closest('tr')
        var purchase_before_tax = __read_number(row.find('.purchase_unit_cost'), true)
        var quantity = __read_number(row.find('input.purchase_quantity'), true)

        //Tax
        var tax_rate = parseFloat($(this).find(':selected').data('tax_amount'))
        var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax)

        //Purchase price
        var purchase_after_tax = purchase_before_tax + tax
        var sub_total_after_tax = quantity * purchase_after_tax

        row.find('.purchase_product_unit_tax_text').text(__currency_trans_from_en(tax, false, true))
        __write_number(row.find('input.purchase_product_unit_tax'), tax, true)

        __write_number(row.find('input.purchase_unit_cost_after_tax'), purchase_after_tax, true)

        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        )
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true)

        update_table_total()
        update_grand_total()
    })

    $(document).on('change', '.purchase_unit_cost_after_tax', function () {
        var row = $(this).closest('tr')
        var purchase_after_tax = __read_number($(this), true)
        var quantity = __read_number(row.find('input.purchase_quantity'), true)

        var sub_total_after_tax = purchase_after_tax * quantity

        //Tax
        var tax_rate = parseFloat(
            row.find('select.purchase_line_tax_id').find(':selected').data('tax_amount')
        )
        var purchase_before_tax = __get_principle(purchase_after_tax, tax_rate)
        var sub_total_before_tax = quantity * purchase_before_tax
        var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax)

        //Update unit cost price before discount
        var discount_percent = __read_number(row.find('input.inline_discounts'), true)
        var purchase_before_discount = __get_principle(purchase_before_tax, discount_percent, true)
        __write_number(
            row.find('input.purchase_unit_cost_without_discount'),
            purchase_before_discount,
            true
        )

        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        )
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true)

        __write_number(row.find('.purchase_unit_cost'), purchase_before_tax, true)

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_before_tax, false, true)
        )
        __write_number(row.find('input.row_subtotal_before_tax_hidden'), sub_total_before_tax, true)

        row.find('.purchase_product_unit_tax_text').text(__currency_trans_from_en(tax, true, true))
        __write_number(row.find('input.purchase_product_unit_tax'), tax)

        update_table_total()
        update_grand_total()
    })

    $('#tax_id, #discount_type, #discount_amount, input#shipping_charges').change(function () {
        update_grand_total()
    })

    var pageLength = -1
    var is_loaded = 1
    dateRangeSettings.startDate = moment().subtract(29, 'days').format('MM/DD/YYYY')
    dateRangeSettings.endDate = new Date()

    $(document).ready(function () {
        // Initialize the first date range picker
        $('#purchase_list_filter_date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#purchase_list_filter_date_range').val(
                    start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
                )
                if (pageLength == -1 && is_loaded == 1) {
                    is_loaded = 0
                    pageLength = 25
                    purchase_table.page.len(25).draw()
                }
                purchase_table.ajax.reload()
            }
        )

        // Reset the first date range picker
        $('#purchase_list_filter_date_range').on('cancel.daterangepicker', function (ev, picker) {
            $('#purchase_list_filter_date_range').val('')
            if (pageLength == -1 && is_loaded == 1) {
                is_loaded = 0
                pageLength = 25
                purchase_table.page.len(25).draw()
            }
            purchase_table.ajax.reload()
        })

        // Initialize the second date range picker
        $('#return_date_range').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: moment_date_format,
            },
        })

        $('#return_date_range').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(
                picker.startDate.format(moment_date_format) +
                    ' ~ ' +
                    picker.endDate.format(moment_date_format)
            )
            if (pageLength == -1 && is_loaded == 1) {
                is_loaded = 0
                pageLength = 25
                purchase_table.page.len(25).draw()
            }
            purchase_table.ajax.reload()
        })

        $('#return_date_range').on('cancel.daterangepicker', function (ev, picker) {
            $(this).val('')
            if (pageLength == -1 && is_loaded == 1) {
                is_loaded = 0
                pageLength = 25
                purchase_table.page.len(25).draw()
            }
            purchase_table.ajax.reload()
        })
    })

    //Purchase table
    purchase_table = $('#purchase_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/purchases',
            data: function (d) {
                if ($('#purchase_list_filter_location_id').length) {
                    d.location_id = $('#purchase_list_filter_location_id').val()
                }
                if ($('#purchase_list_filter_supplier_id').length) {
                    d.supplier_id = $('#purchase_list_filter_supplier_id').val()
                }
                if ($('#purchase_list_filter_payment_status').length) {
                    d.payment_status = $('#purchase_list_filter_payment_status').val()
                }
                if ($('#purchase_list_filter_status').length) {
                    d.status = $('#purchase_list_filter_status').val()
                }
                if ($('.req_from').length) {
                    d.req_from = $('.req_from').val()
                }
                if ($('#is_consignment').length) {
                    d.is_consignment = $('#is_consignment').val()
                }

                var start = ''
                var end = ''
                if ($('#purchase_list_filter_date_range').val()) {
                    var start = $('#purchase_list_filter_date_range')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD')
                    var end = $('#purchase_list_filter_date_range')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD')
                    d.start_date = start
                    d.end_date = end
                }
                var retun_start_date = ''
                var return_end_date = ''
                if ($('#return_date_range').val()) {
                    retun_start_date = $('input#return_date_range')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD')
                    return_end_date = $('input#return_date_range')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD')
                }
                d.return_start_date = retun_start_date
                d.return_end_date = return_end_date

                d = __datatable_ajax_callback(d)
            },
        },
        aaSorting: [[1, 'desc']],
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'transaction_date', name: 'transaction_date' },
            {
                data: 'return_date',
                name: 'return_date',
                render: function (data, type, row) {
                    if (data) {
                        let date = new Date(data)
                        let month = ('0' + (date.getMonth() + 1)).slice(-2)
                        let day = ('0' + date.getDate()).slice(-2)
                        let year = date.getFullYear()
                        return `${month}/${day}/${year}`
                    }
                    return ''
                },
            },
            { data: 'ref_no', name: 'ref_no' },
            { data: 'location_name', name: 'BS.name' },
            { data: 'name', name: 'contacts.name' },
            { data: 'status', name: 'status' },
            { data: 'payment_status', name: 'payment_status' },
            { data: 'final_total', name: 'final_total' },
            { data: 'advanceamt', name: 'TP.advance_amt' },
            // { data: 'discount_type', name: 'discount_type' },
            // { data: 'discount_amount', name: 'discount_amount' },
            { data: 'payment_due', name: 'payment_due', orderable: false, searchable: false },
            {
                data: 'is_consignment',
                name: 'is_consignment',
                render: function (data, type, row) {
                    return data == 1 ? 'Yes' : 'No'
                },
            },
            { data: 'added_by', name: 'u.first_name' },
        ],
        fnDrawCallback: function (oSettings) {
            var total_purchase = sum_table_col($('#purchase_table'), 'final_total')
            $('#footer_purchase_total').text(total_purchase)

            var total_due = sum_table_col($('#purchase_table'), 'payment_due')
            $('#footer_total_due').text(total_due)

            var total_purchase_return_due = sum_table_col($('#purchase_table'), 'purchase_return')
            $('#footer_total_purchase_return_due').text(total_purchase_return_due)

            $('#footer_status_count').html(__sum_status_html($('#purchase_table'), 'status-label'))

            $('#footer_payment_status_count').html(
                __sum_status_html($('#purchase_table'), 'payment-status-label')
            )

            __currency_convert_recursively($('#purchase_table'))
        },
        createdRow: function (row, data, dataIndex) {
            $(row).find('td:eq(5)').attr('class', 'clickable_td')
        },
    })

    $(document).on(
        'change',
        '#purchase_list_filter_location_id, \
                    #purchase_list_filter_supplier_id, #purchase_list_filter_payment_status,\
                     #purchase_list_filter_status, #is_consignment',
        function () {
            purchase_table.ajax.reload()
        }
    )

    update_table_sr_number()

    $(document).on('change', '.mfg_date', function () {
        var this_date = $(this).val()
        var this_moment = moment(this_date, moment_date_format)
        var expiry_period = parseFloat($(this).closest('td').find('.row_product_expiry').val())
        var expiry_period_type = $(this).closest('td').find('.row_product_expiry_type').val()
        if (this_date) {
            if (expiry_period && expiry_period_type) {
                exp_date = this_moment
                    .add(expiry_period, expiry_period_type)
                    .format(moment_date_format)
                $(this).closest('td').find('.exp_date').datepicker('update', exp_date)
            } else {
                $(this).closest('td').find('.exp_date').datepicker('update', '')
            }
        } else {
            $(this).closest('td').find('.exp_date').datepicker('update', '')
        }
    })

    $('#purchase_entry_table tbody')
        .find('.expiry_datepicker')
        .each(function () {
            $(this).datepicker({
                autoclose: true,
                format: datepicker_date_format,
            })
        })

    $(document).on('change', '.profit_percent', function () {
        var row = $(this).closest('tr')
        var profit_percent = __read_number($(this), true)

        var purchase_unit_cost = __read_number(row.find('input.purchase_unit_cost_after_tax'), true)
        var default_sell_price =
            parseFloat(purchase_unit_cost) +
            __calculate_amount('percentage', profit_percent, purchase_unit_cost)
        var exchange_rate = $('input#exchange_rate').val()
        __write_number(
            row.find('input.default_sell_price'),
            default_sell_price * exchange_rate,
            true
        )
        discountChargesRemove()
    })

    $(document).on('change', '.default_sell_price', function () {
        var row = $(this).closest('tr')
        update_inline_profit_percentage(row)
        discountChargesRemove()
    })

    /*$('table#purchase_table tbody').on('click', 'a.delete-purchase', function(e) {
        e.preventDefault();
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).attr('href');
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            purchase_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });*/
    // added by developer 1 for delete purchase
    $('table#purchase_table tbody').on('click', 'a.delete-purchase', function (e) {
        e.preventDefault()
        href = $(this).attr('href')
        var config = {
            title: LANG.sure,
            content: {
                element: 'textarea',
                attributes: {
                    id: 'delete_reason',
                    name: 'delete_reason',
                    placeholder: 'Reason',
                    className: 'swal-content__textarea',
                    rows: 6,
                },
            },
            icon: 'warning',
            //buttons: true,
            dangerMode: true,
            buttons: {
                cancel: {
                    text: 'Cancel',
                    visible: true,
                },
                confirm: {
                    text: 'Submit',
                    closeModal: false,
                },
            },
        }

        ;(function trick () {
            swal(config).then(willDelete => {
                if (willDelete) {
                    content = $('#delete_reason').val()
                    if ($.trim(content) != '') {
                        //var data = $(this).serialize();
                        var data = { reason: content }
                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            data: data,
                            dataType: 'json',
                            success: function (result) {
                                if (result.success == true) {
                                    toastr.success(result.msg)
                                    purchase_table.ajax.reload()
                                } else {
                                    toastr.error(result.msg)
                                }
                                swal.close()
                            },
                            error: function (err) {
                                swal(
                                    'Error',
                                    'Unfortunately, an error occurred. Please try again.',
                                    'error'
                                )
                            },
                        })
                    } else {
                        //alert("asdf");
                        //swal('Validate', 'Please enter valid reason', 'error');
                        //alert(href);
                        alert('Please enter valid reason')
                        swal.stopLoading()
                        trick()
                    }
                }
            })
        })()
    })

    $('table#purchase_entry_table').on('change', 'select.sub_unit', function () {
        var tr = $(this).closest('tr')
        var base_unit_cost = tr.find('input.base_unit_cost').val()
        var base_unit_selling_price = tr.find('input.base_unit_selling_price').val()

        var multiplier = parseFloat($(this).find(':selected').data('multiplier'))

        var unit_sp = base_unit_selling_price * multiplier
        var unit_cost = base_unit_cost * multiplier

        var sp_element = tr.find('input.default_sell_price')
        __write_number(sp_element, unit_sp)

        var cp_element = tr.find('input.purchase_unit_cost_without_discount')
        __write_number(cp_element, unit_cost)
        cp_element.change()
    })
    toggle_search()
})

function addToDataTable (
    id,
    variation_id = null,
    purchase_line_id = null,
    weighing_scale_barcode = null,
    quantity = 1
) {
    // alert(id);
    //Get item addition method
    var variation_id = id
    var item_addtn_method = 0
    var add_via_ajax = true
    if (variation_id != null && $('#item_addition_method').length) {
        item_addtn_method = $('#item_addition_method').val()
    }
    if (item_addtn_method == 0) {
        add_via_ajax = true
    } else {
        var is_added = false
        //Search for variation id in each row of pos table
        $('#pos_table tbody')
            .find('tr')
            .each(function () {
                var row_v_id = $(this).find('.row_variation_id').val()
                var enable_sr_no = $(this).find('.enable_sr_no').val()
                var modifiers_exist = false
                if ($(this).find('input.modifiers_exist').length > 0) {
                    modifiers_exist = true
                }
                if (
                    row_v_id == variation_id &&
                    enable_sr_no !== '1' &&
                    !modifiers_exist &&
                    !is_added
                ) {
                    add_via_ajax = false
                    is_added = true
                    //Increment product quantity
                    qty_element = $(this).find('.pos_quantity')
                    var qty = __read_number(qty_element)
                    __write_number(qty_element, qty + 1)
                    qty_element.change()
                    round_row_to_iraqi_dinnar($(this))
                    $('input#search_product_one').focus().select()
                }
            })
    }

    if (add_via_ajax) {
        var product_row = $('input#product_row_count').val()
        var location_id = $('input#location_id').val()
        var customer_id = $('select#customer_id').val()
        var is_direct_sell = false
        if (
            $('input[name="is_direct_sale"]').length > 0 &&
            $('input[name="is_direct_sale"]').val() == 1
        ) {
            is_direct_sell = true
        }

        var price_group = ''
        if ($('#price_group').length > 0) {
            price_group = parseInt($('#price_group').val())
        }

        //If default price group present
        if ($('#default_price_group').length > 0 && !price_group) {
            price_group = $('#default_price_group').val()
        }

        //If types of service selected give more priority
        if (
            $('#types_of_service_price_group').length > 0 &&
            $('#types_of_service_price_group').val()
        ) {
            price_group = $('#types_of_service_price_group').val()
        }

        $.ajax({
            method: 'GET',
            url: '/sells/pos/get_product_row/' + variation_id + '/' + location_id,
            async: false,
            data: {
                product_row: product_row,
                customer_id: customer_id,
                is_direct_sell: is_direct_sell,
                price_group: price_group,
                purchase_line_id: purchase_line_id,
                weighing_scale_barcode: weighing_scale_barcode,
                quantity: quantity,
            },
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    $('table#pos_table tbody')
                        .append(result.html_content)
                        .find('input.pos_quantity')
                    //increment row count
                    $('input#product_row_count').val(parseInt(product_row) + 1)
                    var this_row = $('table#pos_table tbody').find('tr').last()
                    pos_each_row(this_row)

                    //For initial discount if present
                    var line_total = __read_number(this_row.find('input.pos_line_total'))
                    this_row.find('span.pos_line_total_text').text(line_total)

                    pos_total_row()

                    //Check if multipler is present then multiply it when a new row is added.
                    if (__getUnitMultiplier(this_row) > 1) {
                        this_row.find('select.sub_unit').trigger('change')
                    }

                    if (result.enable_sr_no == '1') {
                        var new_row = $('table#pos_table tbody').find('tr').last()
                        new_row.find('.add-pos-row-description').trigger('click')
                    }

                    round_row_to_iraqi_dinnar(this_row)
                    __currency_convert_recursively(this_row)

                    $('input#search_product_one').focus().select()

                    //Used in restaurant module
                    if (result.html_modifier) {
                        $('table#pos_table tbody')
                            .find('tr')
                            .last()
                            .find('td:first')
                            .append(result.html_modifier)
                    }

                    //scroll bottom of items list
                    $('.pos_product_div').animate(
                        { scrollTop: $('.pos_product_div').prop('scrollHeight') },
                        1000
                    )
                } else {
                    toastr.error(result.msg)
                    $('input#search_product_one').focus().select()
                }
            },
        })
    }
}
$(document).on('click', function (e) {
    if ($(e.target).closest('#open-datatable_wrapper').length === 0) {
        $('#open-datatable_wrapper').hide()
        $('#open-datatable-one').hide()
    }
})

if ($('input[name="products_ids"]').val() && $('input[name="variations_ids"]').val()) {
    var p_ids = $('input[name="products_ids"]').val().split(',').map(Number)
    var v_ids = $('input[name="variations_ids"]').val().split(',').map(Number)

    if (p_ids.length) {
        p_ids.filter(function (value, index) {
            get_purchase_entry_row(value, v_ids[index], null, true)
        })
    }
}

var multiple_count = 0
var multiple_bool = false

function get_purchase_entry_row (product_id, variation_id, edit = null, multiple = false) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
    })

    if (product_id) {
        multiple_bool = multiple

        var row_count = $('#row_count').val()
        var location_id = $('#location_id').val()

        if (multiple) {
            $('#row_count').val(parseInt(row_count) + 1)
        }

        $.ajax({
            method: 'POST',
            url: '/purchases/get_purchase_entry_row',
            dataType: 'html',
            data: {
                product_id: product_id,
                row_count: row_count,
                variation_id: variation_id,
                location_id: location_id,
                edit: edit,
            },
            success: function (result) {
                $(result)
                    .find('.purchase_quantity')
                    .each(function () {
                        row = $(this).closest('tr')

                        $('#purchase_entry_table tbody').append(
                            update_purchase_entry_row_values(row)
                        )
                        update_row_price_for_exchange_rate(row)

                        update_inline_profit_percentage(row)

                        update_table_total()
                        update_grand_total()
                        update_table_sr_number()

                        //Check if multipler is present then multiply it when a new row is added.
                        if (__getUnitMultiplier(row) > 1) {
                            row.find('select.sub_unit').trigger('change')
                        }
                    })
                if ($(result).find('.purchase_quantity').length) {
                    $('#row_count').val(
                        $(result).find('.purchase_quantity').length + parseInt(row_count)
                    )
                }
            },
        })
    }
}

$(document).on('click', '#notforselling', function () {
    var productid = $('#productid').val()
    if ($(this).prop('checked') == false) {
        var selling = 0
        $.ajax({
            method: 'POST',
            url: '/purchases/update_not_for_selling',
            dataType: 'json',
            data: {
                productid: productid,
                selling: selling,
            },
            success: function (result) {
                if (result.success == true) {
                    toastr.success(result.message)
                } else if (result.success == false) {
                    toastr.success(result.message)
                }
            },
        })
    } else if ($(this).prop('checked') == true) {
        var selling = 1
        $.ajax({
            method: 'POST',
            url: '/purchases/update_not_for_selling',
            dataType: 'json',
            data: {
                productid: productid,
                selling: selling,
            },
            success: function (result) {
                if (result.success == true) {
                    toastr.success(result.message)
                } else if (result.success == false) {
                    toastr.success(result.message)
                }
            },
        })
    }
})

$(document).on('click', '#outofstock', function () {
    var productid = $('#productid').val()
    if ($(this).prop('checked') == false) {
        var outofstock = 0
        $.ajax({
            method: 'POST',
            url: '/purchases/update_out_of_stock',
            dataType: 'json',
            data: {
                productid: productid,
                outofstock: outofstock,
            },
            success: function (result) {
                if (result.success == true) {
                    toastr.success(result.message)
                } else if (result.success == false) {
                    toastr.success(result.message)
                }
            },
        })
    } else if ($(this).prop('checked') == true) {
        var outofstock = 1
        $.ajax({
            method: 'POST',
            url: '/purchases/update_out_of_stock',
            dataType: 'json',
            data: {
                productid: productid,
                outofstock: outofstock,
            },
            success: function (result) {
                if (result.success == true) {
                    toastr.success(result.message)
                } else if (result.success == false) {
                    toastr.success(result.message)
                }
            },
        })
    }
})

function update_purchase_entry_row_values (row) {
    if (typeof row != 'undefined') {
        var quantity = __read_number(row.find('.purchase_quantity'), true)
        var unit_cost_price = __read_number(row.find('.purchase_unit_cost'), true)
        var row_subtotal_before_tax = quantity * unit_cost_price

        var tax_rate = parseFloat(
            $('option:selected', row.find('.purchase_line_tax_id')).attr('data-tax_amount')
        )

        var unit_product_tax = __calculate_amount('percentage', tax_rate, unit_cost_price)

        var unit_cost_price_after_tax = unit_cost_price + unit_product_tax
        var row_subtotal_after_tax = quantity * unit_cost_price_after_tax

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(row_subtotal_before_tax, false, true)
        )
        __write_number(row.find('.row_subtotal_before_tax_hidden'), row_subtotal_before_tax, true)
        __write_number(row.find('.purchase_product_unit_tax'), unit_product_tax, true)
        row.find('.purchase_product_unit_tax_text').text(
            __currency_trans_from_en(unit_product_tax, false, true)
        )
        row.find('.purchase_unit_cost_after_tax').text(
            __currency_trans_from_en(unit_cost_price_after_tax, true)
        )
        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(row_subtotal_after_tax, false, true)
        )
        __write_number(row.find('.row_subtotal_after_tax_hidden'), row_subtotal_after_tax, true)

        row.find('.expiry_datepicker').each(function () {
            $(this).datepicker({
                autoclose: true,
                format: datepicker_date_format,
            })
        })
        return row
    }
}

function update_row_price_for_exchange_rate (row) {
    var exchange_rate = $('input#exchange_rate').val()

    if (exchange_rate == 1) {
        return true
    }

    var purchase_unit_cost_without_discount = __read_number(
        row.find('.purchase_unit_cost_without_discount'),
        true
    ) // exchange_rate;
    __write_number(
        row.find('.purchase_unit_cost_without_discount'),
        purchase_unit_cost_without_discount,
        true
    )

    var purchase_unit_cost = __read_number(row.find('.purchase_unit_cost'), true) // exchange_rate;
    __write_number(row.find('.purchase_unit_cost'), purchase_unit_cost, true)

    var row_subtotal_before_tax_hidden = __read_number(
        row.find('.row_subtotal_before_tax_hidden'),
        true
    ) // exchange_rate;
    row.find('.row_subtotal_before_tax').text(
        __currency_trans_from_en(row_subtotal_before_tax_hidden, false, true)
    )
    __write_number(
        row.find('input.row_subtotal_before_tax_hidden'),
        row_subtotal_before_tax_hidden,
        true
    )

    var purchase_product_unit_tax = __read_number(row.find('.purchase_product_unit_tax'), true) // exchange_rate;
    __write_number(row.find('input.purchase_product_unit_tax'), purchase_product_unit_tax, true)
    row.find('.purchase_product_unit_tax_text').text(
        __currency_trans_from_en(purchase_product_unit_tax, false, true)
    )

    var purchase_unit_cost_after_tax = __read_number(
        row.find('.purchase_unit_cost_after_tax'),
        true
    ) // exchange_rate;
    __write_number(
        row.find('input.purchase_unit_cost_after_tax'),
        purchase_unit_cost_after_tax,
        true
    )

    var row_subtotal_after_tax_hidden = __read_number(
        row.find('.row_subtotal_after_tax_hidden'),
        true
    ) // exchange_rate;
    __write_number(
        row.find('input.row_subtotal_after_tax_hidden'),
        row_subtotal_after_tax_hidden,
        true
    )
    row.find('.row_subtotal_after_tax').text(
        __currency_trans_from_en(row_subtotal_after_tax_hidden, false, true)
    )
}

function iraqi_dinnar_selling_price_adjustment (row) {
    var default_sell_price = __read_number(row.find('input.default_sell_price'), true)

    //Adjsustment
    var remaining = default_sell_price % 250
    if (remaining >= 125) {
        default_sell_price += 250 - remaining
    } else {
        default_sell_price -= remaining
    }

    __write_number(row.find('input.default_sell_price'), default_sell_price, true)

    update_inline_profit_percentage(row)
}

function update_inline_profit_percentage (row) {
    //Update Profit percentage
    var default_sell_price = __read_number(row.find('input.default_sell_price'), true)
    var exchange_rate = $('input#exchange_rate').val()
    default_sell_price_in_base_currency = default_sell_price / parseFloat(exchange_rate)

    // var purchase_after_tax = __read_number(row.find('input.purchase_unit_cost_after_tax'), true);
    // var profit_percent = __get_rate(purchase_after_tax, default_sell_price_in_base_currency);
    var purchase_after_tax = __read_number(
        row.find('input.purchase_unit_cost_without_discount'),
        true
    )
    var profit_percent = __get_rate(purchase_after_tax, default_sell_price_in_base_currency)
    __write_number(row.find('input.profit_percent'), profit_percent, true)

    // console.log('exchange_rate :', exchange_rate)
    // console.log('default_sell_price: ', default_sell_price)
    // console.log('default_sell_price_in_base_currency:', default_sell_price_in_base_currency)
    // console.log('purchase_after_tax :', purchase_after_tax)
    // console.log('profit_percent:', profit_percent)
}

function update_table_total () {
    var total_quantity = 0
    var total_st_before_tax = 0
    var total_subtotal = 0

    $('#purchase_entry_table tbody')
        .find('tr')
        .each(function () {
            total_quantity += __read_number($(this).find('.purchase_quantity'), true)
            total_st_before_tax += __read_number(
                $(this).find('.row_subtotal_before_tax_hidden'),
                true
            )
            total_subtotal += __read_number($(this).find('.row_subtotal_after_tax_hidden'), true)
        })

    $('#total_quantity').text(__number_f(total_quantity, false))
    $('#total_st_before_tax').text(__currency_trans_from_en(total_st_before_tax, true, true))
    __write_number($('input#st_before_tax_input'), total_st_before_tax, true)

    $('#total_subtotal').text(__currency_trans_from_en(total_subtotal, true, true))
    __write_number($('input#total_subtotal_input'), total_subtotal, true)
}

function update_grand_total () {
    var st_before_tax = __read_number($('input#st_before_tax_input'), true)
    var total_subtotal = __read_number($('input#total_subtotal_input'), true)

    //Calculate Discount
    var discount_type = $('select#discount_type').val()
    var discount_amount = __read_number($('input#discount_amount'), true)
    var discount = __calculate_amount(discount_type, discount_amount, total_subtotal)
    $('#discount_calculated_amount').text(__currency_trans_from_en(discount, true, true))

    //Calculate Tax
    var tax_rate = parseFloat($('option:selected', $('#tax_id')).data('tax_amount'))
    var tax = __calculate_amount('percentage', tax_rate, total_subtotal - discount)
    __write_number($('input#tax_amount'), tax)
    $('#tax_calculated_amount').text(__currency_trans_from_en(tax, true, true))

    //Calculate shipping
    var shipping_charges = __read_number($('input#shipping_charges'), true)

    //Calculate Final total
    grand_total = total_subtotal - discount + tax + shipping_charges

    __write_number($('input#grand_total_hidden'), grand_total, true)

    var payment = __read_number($('input.payment-amount'), true)

    var due = grand_total - payment
    // __write_number($('input.payment-amount'), grand_total, true);

    $('#grand_total').text(__currency_trans_from_en(grand_total, true, true))

    $('#payment_due').text(__currency_trans_from_en(due, true, true))

    //__currency_convert_recursively($(document));
}
$(document).on('change', 'input.payment-amount', function () {
    var payment = __read_number($(this), true)
    var grand_total = __read_number($('input#grand_total_hidden'), true)
    var bal = grand_total - payment
    $('#payment_due').text(__currency_trans_from_en(bal, true, true))
})

function update_table_sr_number () {
    var sr_number = 1
    $('table#purchase_entry_table tbody')
        .find('.sr_number')
        .each(function () {
            $(this).text(sr_number)
            sr_number++
        })
}

$(document).on('click', 'button#submit_purchase_form', function (e) {
    e.preventDefault()

    var submit_type = $(this).attr('value')
    $('#submit_type').val(submit_type)

    //Check if product is present or not.
    if ($('table#purchase_entry_table tbody tr').length <= 0) {
        toastr.warning(LANG.no_products_added)
        $('input#search_product_one').select()
        return false
    }

    $('form#add_purchase_form').validate({})
    var payment_types_dropdown = $('.payment_types_dropdown')
    var payment_type = payment_types_dropdown.val()
    var payment_row = payment_types_dropdown.closest('.payment_row')
    var amount_element = payment_row.find('.payment-amount')
    account_dropdown = payment_row.find('.account-dropdown')
    if (payment_type == 'advance') {
        max_value = $('#advance_balance').val()
        msg = $('#advance_balance').data('error-msg')
        amount_element.rules('add', {
            'max-value': max_value,
            messages: {
                'max-value': msg,
            },
        })
        if (account_dropdown) {
            account_dropdown.prop('disabled', true)
        }
    } else {
        amount_element.rules('remove', 'max-value')
        if (account_dropdown) {
            account_dropdown.prop('disabled', false)
        }
    }

    if ($('form#add_purchase_form').valid()) {
        $(this).attr('disabled', true)
        $('form#add_purchase_form').submit()
    }
})

function toggle_search () {
    if ($('#location_id').val()) {
        $('#search_product_one').removeAttr('disabled')
        $('#search_product_one').focus()
    } else {
        $('#search_product_one').attr('disabled', true)
    }
}

$(document).on('change', '#location_id', function () {
    toggle_search()
    $('#purchase_entry_table tbody').html('')
    update_table_total()
    update_grand_total()
    update_table_sr_number()
})

$(document).on('shown.bs.modal', '.quick_add_product_modal', function () {
    var selected_location = $('#location_id').val()
    if (selected_location) {
        $('.quick_add_product_modal')
            .find('#product_locations')
            .val([selected_location])
            .trigger('change')
    }
})

function discountChargesRemove () {
    var total_subtotal = 0
    var total = 0
    var discount = 0
    $('#purchase_entry_table tbody')
        .find('tr')
        .each(function () {
            total_quantity = __read_number(
                $(this).find('.purchase_unit_cost_without_discount'),
                true
            )
            var quantity = __read_number($(this).find('.purchase_quantity'), true)
            var purchase_after_tax = quantity * total_quantity
            total_subtotal += purchase_after_tax
        })

    $('#total_subtotal').text(__currency_trans_from_en(total_subtotal, true, true))
    __write_number($('input#total_subtotal_input'), total_subtotal, true)

    $('#total_discount').text(__currency_trans_from_en(discount, true, true))
    __write_number($('input#total_discount_input'), discount, true)

    $('#discount_charges').val(0.0)
    console.log('total_subtotal:', total_subtotal)
    console.log('discount:', discount)
    console.log('total: ', total)
}

$(document).ready(function () {
    //Prevent enter key function except texarea
    $('form').on('keyup keypress', function (e) {
        var keyCode = e.keyCode || e.which
        if (keyCode === 13 && e.target.tagName != 'TEXTAREA') {
            e.preventDefault()
            return false
        }
    })
})
