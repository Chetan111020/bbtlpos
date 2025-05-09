$(document).ready(function() {

    $('.pos_ver_info').html(' POS v24.1.31.1644')

    $(document).on('click', '.new-rec', function() {
        setTimeout(() => {
            toastr.info("Invoice Recalculating, Please wait..");
        }, 100);
        setTimeout(() => {
            var customer_id = $('#customer_id').val();
            var rowCount = $('#pos_table tr').length;
            var i = 1;
            $('table#pos_table tbody tr').each(function() {
                var variation_id =  $(this).find('input.row_variation_id').val();
                var tr = $(this);

                var url = '/recalc/' + variation_id + '/' + customer_id;
                $.ajax({
                    method: 'GET',
                    url: url,
                    dataType: 'json',
                    async: false,
                    success: function(result) {
                        if(result.success == 1){
                            tr.find('.pos_unit_price').val(result.price).trigger('change');
                        }
                    },
                });

                i++;
                console.log(rowCount,i);
                if(rowCount == i) {
                    $.ajax({
                        method: 'GET',
                        url: '/recalc/group-details/'+customer_id,
                        dataType: 'json',
                        success: function(result) {
                            // if($('#recalc_price_group').length){
                            //     $('#recalc_price_group').val(result.group_id);
                            // }
                            if($('#price_group').length){
                                $('#price_group').val(result.group_id);
                            }
                            if($('#price_group_text').length){
                                $('#price_group_text').val(result.group_name);
                            }
                        },
                    });
                    toastr.success("Invoice Recalculated");
                }
            });
        }, 1000);
    });

    $(document).on('change','.pos_line_tax_amount',function(){
        $(this).parent().parent().find('.pos_unit_price').trigger('change');
    });

    setTimeout(() => {
        if($('#tax_total_amt').val() > 0){
            // $('.pos_line_tax_amount').each(function(){
            //     if($(this).val() > 0){
            //         $(this).closest('.pos_unit_price').trigger('change');
            //     }
            // });
            $('.pos_unit_price').each(function(){
                $(this).trigger('change');
            });
        }
    }, 2000);

    $('.cursor_lock').on('change',function(){
        setMyCookie('cursor_lock', $(this).is(":checked"), 7);
    });
    $('.cursor_lock').prop('checked', getMyCookie('cursor_lock') == "true");

    $('.auto-save-btn').click(function() {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        if($("#delivery_method").val() == ''){
            $('#posShippingModalUpdateSelfNonPicking').trigger('click');
        }
        // calculateTax
        //check delivery method selected or not
        var is_selected = checkDeliveryMethod();
        if(is_selected == false ){
            toastr.warning("Please select delivery method first");
            return false;
        }
        var is_valid = isValidPosForm();
        if (is_valid != true) {
            return;
        }

        var data = pos_form_obj.serialize();
        data = data + '&status=draft&autosave=1';
        var url = pos_form_obj.attr('action');

        disable_pos_form_actions();
        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function(result) {
                enable_pos_form_actions();
                if (result.success == 1) {

                    pos_layout = '';
                    if($('.use_pos_layout').length){
                        pos_layout = "/" + $('.use_pos_layout').first().val();
                    }

                    if(result.autosave_id != undefined){
                        window.location.replace("/pos/" + result.autosave_id + "/edit"+ pos_layout +"?autosave=1");
                    }
                    reset_pos_form();

                    toastr.success(result.msg);
                } else {
                    toastr.error(result.msg);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                toastr.error("Something went wrong !");
                enable_pos_form_actions();
            }
        });
    });

    if($('.with_auto_save').length){
        setInterval(() => {
            //Check if product is present or not.
            if ($('table#pos_table tbody').find('.product_row').length <= 0) {
                toastr.warning(LANG.no_products_added);
                return false;
            }
            var is_valid = isValidPosForm();
            if (is_valid != true) {
                return;
            }

            var data = pos_form_obj.serialize();
            data = data + '&status=draft&autosave=1';
            var url = pos_form_obj.attr('action');

            disable_pos_form_actions();
            $.ajax({
                method: 'POST',
                url: url,
                data: data,
                dataType: 'json',
                success: function(result) {
                    enable_pos_form_actions();
                    if (result.success == 1) {
                        toastr.info("Changes saved");
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        }, 15000);
    }

    // console.log("sds");
    setInternalSearchCategory();
    document.cookie = "myJavascriptVar = 0; path=/;";
    customer_set = false;
    //Prevent enter key function except texarea
    $('form').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13 && e.target.tagName != 'TEXTAREA') {
            e.preventDefault();
            return false;
        }
    });

    //For edit pos form
    if ($('form#edit_pos_sell_form').length > 0) {
        pos_total_row();
        pos_form_obj = $('form#edit_pos_sell_form');
    } else {
        pos_total_row();
        pos_form_obj = $('form#add_pos_sell_form');
    }
    if ($('form#edit_pos_sell_form').length > 0 || $('form#add_pos_sell_form').length > 0) {
        initialize_printer();
    }

    $('select#select_location_id').change(function() {
        reset_pos_form();

        var default_price_group = $(this).find(':selected').data('default_price_group')
        if (default_price_group) {
            if ($("#price_group option[value='" + default_price_group + "']").length > 0) {
                $("#price_group").val(default_price_group);
                $("#price_group").change();
            }
        }

        //Set default price group
        if ($('#default_price_group').length) {
            var dpg = default_price_group ?
                default_price_group : 0;
            $('#default_price_group').val(dpg);
        }

        set_payment_type_dropdown();

        if ($('#types_of_service_id').length && $('#types_of_service_id').val()) {
            $('#types_of_service_id').change();
        }
    });

    //recalculate btn
    $('#recalcbtn').on('click',function(){
        var cust = $('#customer_id').val();
        var href = $("#recalcbtn").attr('href');
        var url = href+"?customer="+cust;
        window.location.replace(url);
        // alert(url);
    });

    //get customer
    $('#customer_id').select2({

        ajax: {
            url: '/contacts/customers',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                // console.log(params);
                return {
                    q: params.term, // search term
                    page: params.page,
                };
            },
            processResults: function(data) {
                return {
                    results: data,
                };
            },
        },
        templateResult: function(data) {
            var rowCount = $('#pos_table tr').length;
            var price_group_id = $('#price_group').val();
            var transaction_id = $('#invoice_no').val();

            // if(rowCount > 1 && price_group_id !== "" && typeof(price_group_id) != "undefined" && !transaction_id) {
            //     swal({
            //       title: "You must need to reload the page if you want to change customer",
            //       text: "",
            //       icon: "warning",
            //     });
            //     return false;
            // }
            var template = '';
            if (data.supplier_business_name) {
                template += data.supplier_business_name + "<br>";
            }
           template += data.text + "<br> Address : " + data.address_line_1 + ', ' + data.address_line_2 + ' , ' + data.city + ' , ' + data.state + ' , ' + data.zip_code ;


            if (typeof(data.total_rp) != "undefined") {
                var rp = data.total_rp ? data.total_rp : 0;
                template += "<br><i class='fa fa-gift text-success'></i> " + rp;
            }

            return template;
        },
        minimumInputLength: 1,
        language: {
            noResults: function() {
                var name = $('#customer_id')
                    .data('select2')
                    .dropdown.$search.val();
                return (
                    '<button type="button" data-name="' +
                    name +
                    '" class="btn btn-link add_new_customer"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp; ' +
                    __translate('add_name_as_new_customer', { name: name }) +
                    '</button>'
                );
            },
        },
        escapeMarkup: function(markup) {
            return markup;
        },
    });
    $('#customer_id').on('select2:select', function(e) {
        let selected_customer_id = $('#customer_id').val();
        var val = $('.addresses').html();
        if (typeof val !== 'undefined' && val.length > 0) {
            confirm("Are you want to change the customer");
        }
        var data = e.params.data;
        var customer_id = data.id;
        if (data.pay_term_number) {
            $('input#pay_term_number').val(data.pay_term_number);
        } else {
            $('input#pay_term_number').val('');
        }

        if (data.pay_term_type) {
            $('#pay_term_type').val(data.pay_term_type);
        } else {
            $('#pay_term_type').val('');
        }

        //set customer group id
        var customer_group_id = data.customer_group_id;
        $("#price_group").val(customer_group_id);
        $("#price_group").change();


        $('#advance_balance_text').text(__currency_trans_from_en(data.balance), true);
        $('#advance_balance').val(data.balance);
        $('#customer_state').val(data.state);
        var opening_balance_calc = data.opening_balance - data.opening_balance_paid;
        // old
        // var open_balance  = data.total_invoice - data.invoice_received - data.balance + opening_balance_calc - data.total_sell_return;;
        // new
        var open_balance = data.new_calc_balance;
        open_balance = __number_f(open_balance);
        $('.addresses').html('<div class="col-md-3"><input type="hidden" name="customerid" id="customerid" value="'+ data.id +'"><p style="padding: 0px; margin: 0px;"><b>Address</b></p><p class="p-0 m-0">' + data.address_line_1 + ', ' + data.address_line_2 + ' , ' + data.city + ' , ' + data.state + ' , ' + data.zip_code + '</p></div><div class="col-md-3"><p style="padding: 0px; margin: 0px;"><b>Phone, Email</b></p><p class="p-0 m-0">' + data.mobile + ', ' + data.email + '</p></div><div class="col-md-2"><p style="padding: 0px; margin: 0px;"><b>Contact Person 1</b></p><p class="p-0 m-0">' + data.contact_person_1 + '</p></div><div class="col-md-2"><p style="padding: 0px; margin: 0px;"><b>TAX ID</b></p><p class="p-0 m-0">' + data.tax + '</p></div><div class="col-md-2"><p style="padding: 0px; margin: 0px;"><b class="text-red">Open Balance</b></p><p class="p-0 m-0"><b class="text-red">' + '$ ' + open_balance + '</b></p></div>');
        var rowCount = $('#pos_table tr').length;
        var i = 1;
        let tax_applicable_cust = $('#tax_applicable').val();
        $('table#pos_table tbody tr').each(function() {
            var variation_id =  $(this).find('input.row_variation_id').val();
            var product_id =  $(this).find('input.product_id').val();
            var pos_unit_price_inc_tax =  $(this).find('input.pos_unit_price_inc_tax').val();
            var quantity = __read_number($(this).find('input.pos_quantity'));
            var qty_box = __read_number($(this).find('input.qty_box'));
            var new_qty = quantity;
            if(qty_box > 1) var new_qty = quantity * qty_box;
            var total = __read_number($(this).find('input.pos_line_total'));
            var tr = $(this);

            $.ajax({
                method: 'GET',
                url: '/sells/pos/product-tax',
                data: {customer_id:customer_id, product_id:product_id, variation_id:variation_id, selling_price:pos_unit_price_inc_tax},
                dataType: 'json',
                success: function(result) {
                    var new_total = total;
                    var tax_single = 0;
                    var every_item =  0;
                    var total_tax = 0;
                    var tax_type = 0;
                    if(result.tax>0 && false){
                        tax_single = result.tax;
                        state =  result.state;
                        every_item =  result.every_item;
                        tax_type =  result.tax_type;

                        //State tax calculation
                            if(result.rule == 55 || result.rule == 58 || result.rule ==  59 || result.rule ==  61 || result.rule ==  62){
                                total_tax = parseFloat(tax_single) * parseFloat(quantity);
                            }
                            else if(every_item > 1){
                                var times_of_apply_tax = parseInt(parseFloat(new_qty)/every_item);
                                total_tax = parseFloat(tax_single) * times_of_apply_tax;
                            }else{
                                if(tax_type == 1) total_tax = parseFloat(tax_single) * parseFloat(new_qty);
                                if(tax_type == 2) total_tax = parseFloat(tax_single) * parseFloat(quantity);
                            }
                            new_total = parseFloat(total) + parseFloat(total_tax);
                        // tr.find('span.pos_line_totalamt_text').text(__currency_trans_from_en(new_total,true));
                    }
                        tr.find('input.pos_line_tax_every_item').val(every_item);
                        tr.find('input.pos_line_tax').val(tax_single);
                        tr.find('input.pos_line_tax_amount').val(total_tax);
                        tr.find('input.pos_line_tax_type').val(tax_type);
                    //City tax calculation
                    var city_tax_amt = 0;
                    var city_tax_id = 0;
                    var city_tax_name = 0;
                    var first_item_value = 0;
                    var second_item_value = 0;
                    var city_tax = 0;
                    var city_every_item = 0;
                    var city_tax_type = 0;
                    if(result.city_tax_id != 0 ){
                        city_tax_id = result.city_tax_id;
                        city_every_item =  result.city_every_item;
                        city_tax = result.city_tax;
                        first_item_value = result.first_item_value;
                        second_item_value = result.second_item_value;
                        city_tax_name = result.city_tax_name;
                        city_tax_type =  result.city_tax_type;

                        if( city_tax != 0 ){
                            if(city_every_item > 1) {
                                var times_of_apply_tax = parseInt(parseFloat(new_qty)/city_every_item);
                                city_tax_amt = times_of_apply_tax * city_tax ;
                            } else{
                                if(city_tax_type == 1)  city_tax_amt = new_qty * city_tax ;
                                if(city_tax_type == 2)  city_tax_amt = quantity * city_tax ;
                            }
                        } else {
                            if(city_tax_type == 1) var second_applicable_qty = new_qty - quantity;
                            if(city_tax_type == 2) var second_applicable_qty = 0;
                            city_tax_amt = parseFloat(first_item_value*quantity) + parseFloat(second_item_value * second_applicable_qty);
                        }
                    }
                    // console.log("city_tax_amt",city_tax_amt);
                    tr.find('input.city_tax_id').val(city_tax_id);
                    tr.find('input.city_tax_name').val(city_tax_name);
                    tr.find('input.city_tax_type').val(city_tax_type);
                    tr.find('input.city_tax_value').val(city_tax);
                    tr.find('input.city_tax_value_amt').val(city_tax_amt);
                    tr.find('input.first_item_value_value').val(first_item_value);
                    tr.find('input.second_item_value_value').val(second_item_value);
                    tr.find('input.city_every_item_value').val(city_every_item);
                    tr.find('span.pos_line_city_tax_text').text(__currency_trans_from_en(city_tax_amt, true));
                    new_total = parseFloat(new_total) + parseFloat(city_tax_amt);
                    tr.find('input.pos_line_totalamt_value').val(new_total);
                    tr.find('span.pos_line_tax_text').text(__currency_trans_from_en(total_tax, true));

                    pos_total_row();
                    calculate_balance_due();

                    i++;
                    if(rowCount == i) {
                        toastr.success("Tax Removed!");
                    }
                },
            });

        //    i++;
        //    if(rowCount == i) {
        //     setTimeout(function() {
        //     //    pos_total_row();
        //     }, 5000);
        //    }

        });


    });

    //set_default_customer();

    if ($('#search_product_one').length) {
        //Add Product
        $('#search_product_one')
            .autocomplete({
                source: function(request, response) {
                    var price_group = '';
                    var search_fields = [];
                    $('.search_fields:checked').each(function(i) {
                        search_fields[i] = $(this).val();
                    });

                    if ($('#price_group').length > 0) {
                        price_group = $('#price_group').val();
                    }
                    $.getJSON(
                        '/products/list', {
                            price_group: price_group,
                            location_id: $('input#location_id').val(),
                            term: request.term,
                            not_for_selling: 0,
                            search_fields: search_fields
                        },
                        response
                    );
                },
                minLength: 2,
                response: function(event, ui) {
                    //alert('hi');
                    if (ui.content.length == 1) {
                        ui.item = ui.content[0];
                        if ((ui.item.enable_stock == 1 && ui.item.qty_available > 0) ||
                            (ui.item.enable_stock == 0)) {
                                let customer_id = $('#customer_id').val();
                                if (customer_id === "") {
                                    toastr.error("Customer Not Selected!");
                                    $(this).autocomplete('close');
                                } else {
                                    $(this)
                                        .data('ui-autocomplete')
                                        ._trigger('select', 'autocompleteselect', ui);
                                    $(this).autocomplete('close');
                                }
                        }
                    } else if (ui.content.length == 0) {
                        toastr.error(LANG.no_products_found);
                        $('input#search_product_one').select();
                    }
                },
                focus: function(event, ui) {
                    if (ui.item.qty_available <= 0) {
                        return false;
                    }
                },
                select: function(event, ui) {
                    var searched_term = $(this).val();
                    var is_overselling_allowed = false;
                    if ($('input#is_overselling_allowed').length) {
                        is_overselling_allowed = true;
                    }

                    if (ui.item.enable_stock != 1 || ui.item.qty_available > 0 || is_overselling_allowed) {
                        $(this).val(null);
                        //Pre select lot number only if the searched term is same as the lot number
                        var purchase_line_id = ui.item.purchase_line_id && searched_term == ui.item.lot_number ? ui.item.purchase_line_id : null;
                        pos_product_row(ui.item.variation_id, purchase_line_id);
                    } else {
                        alert(LANG.out_of_stock);
                    }
                },
            })
            .autocomplete('instance')._renderItem = function(ul, item) {
                var is_overselling_allowed = false;
                if ($('input#is_overselling_allowed').length) {
                    is_overselling_allowed = true;
                }
                if (item.enable_stock == 1 && item.qty_available <= 0 && !is_overselling_allowed) {
                    var string = '<li class="ui-state-disabled">' + item.name;
                    if (item.type == 'variable') {
                        string += '-' + item.variation;
                    }
                    var selling_price = item.selling_price;
                    if (item.variation_group_price) {
                        selling_price = item.variation_group_price;
                    }
                    string +=
                        ' (' +
                        item.sub_sku +
                        ')' +
                        '<br> Price: ' +
                        selling_price +
                        ' (Out of stock) </li>';
                    return $(string).appendTo(ul);
                } else {
                    var string = '<div>' + item.name;
                    if (item.type == 'variable') {
                        string += '-' + item.variation;
                    }

                    var selling_price = item.selling_price;
                    if (item.variation_group_price) {
                        selling_price = item.variation_group_price;
                    }

                    string += ' (' + item.sub_sku + ')' + '<br> Price: ' + selling_price;
                    if (item.enable_stock == 1) {
                        var qty_available = __currency_trans_from_en(item.qty_available, false, false, __currency_precision, true);
                        string += ' - ' + qty_available + item.unit;
                    }
                    string += '</div>';

                    return $('<li>')
                        .append(string)
                        .appendTo(ul);
                }
            };
    }

    if ($('#search_product').length) {
        //Add Product
        $('#search_product')
            .autocomplete({
                source: function(request, response) {
                    var price_group = '';
                    var search_fields = [];
                    $('.search_fields:checked').each(function(i) {
                        search_fields[i] = $(this).val();
                    });

                    search_fields.push("item_code");

                    if ($('#price_group').length > 0) {
                        price_group = $('#price_group').val();
                    }
                    $.getJSON(
                        '/products/list', {
                            price_group: price_group,
                            location_id: $('input#location_id').val(),
                            term: request.term,
                            not_for_selling: 0,
                            search_fields: search_fields
                        },
                        response
                    );
                },
                minLength: 2,
                response: function(event, ui) {
                    var is_overselling_allowed = false;
                    if ($('input#is_overselling_allowed').length) {
                        is_overselling_allowed = true;
                    }
                    if (ui.content.length == 1) {
                        ui.item = ui.content[0];
                        if ((ui.item.enable_stock == 1 && ui.item.qty_available > 0) || (ui.item.enable_stock == 1 && ui.item.qty_available < 0 && is_overselling_allowed)  ||
                            (ui.item.enable_stock == 0)) {
                             let customer_id = $('#customer_id').val();
                                if (customer_id === "") {
                                    toastr.error("Customer Not Selected!");
                                    $(this).autocomplete('close');
                                } else{
                                    $(this)
                                        .data('ui-autocomplete')
                                        ._trigger('select', 'autocompleteselect', ui);
                                    $(this).autocomplete('close');
                                }
                        }
                    } else if (ui.content.length == 0) {
                        toastr.error(LANG.no_products_found);
                        swal({
                            title: LANG.no_products_found,

                            icon: "error",
                        })
                        $('input#search_product').select();
                    }
                },
                focus: function(event, ui) {
                    if (ui.item.qty_available <= 0) {
                        return false;
                    }
                },
                select: function(event, ui) {
                    var searched_term = $(this).val();
                    var is_overselling_allowed = false;
                    if ($('input#is_overselling_allowed').length) {
                        is_overselling_allowed = true;
                    }

                    if (ui.item.enable_stock != 1 || ui.item.qty_available > 0 || is_overselling_allowed) {
                        $(this).val(null);

                        //Pre select lot number only if the searched term is same as the lot number
                        var purchase_line_id = ui.item.purchase_line_id && searched_term == ui.item.lot_number ? ui.item.purchase_line_id : null;
                        pos_product_row(ui.item.variation_id, purchase_line_id);
                    } else {
                        alert(LANG.out_of_stock);
                    }
                },
            })
            .autocomplete('instance')._renderItem = function(ul, item) {
                var is_overselling_allowed = false;
                if ($('input#is_overselling_allowed').length) {
                    is_overselling_allowed = true;
                }
                if (item.enable_stock == 1 && item.qty_available <= 0 && !is_overselling_allowed) {
                   var string = '<li class="ui-state-disabled"><b>' + item.name;
                    if (item.type == 'variable') {
                        string += '-' + item.variation;
                    }
                    var selling_price = item.selling_price;
                    if (item.variation_group_price) {
                        selling_price = item.variation_group_price;
                    }
                    string +=
                        ' </b> (' +
                        item.sub_sku +
                        ')' +
                        '<br> Price: ' +
                        selling_price +
                        ' (Out of stock) </li>';
                    return $(string).appendTo(ul);
                } else {
                    var string = '<div><b>' + item.name;
                    if (item.type == 'variable') {
                        string += '-' + item.variation;
                    }

                    var selling_price = item.selling_price;
                    if (item.variation_group_price) {
                        selling_price = item.variation_group_price;
                    }

                    string += ' </b> (' + item.sub_sku + ')' + '<br> Price: ' + selling_price;
                    if (item.enable_stock == 1) {
                        var qty_available = __currency_trans_from_en(item.qty_available, false, false, __currency_precision, true);
                         if(qty_available > 1){
                            string += ' - <span style="color:#1abb1a;">' + qty_available + item.unit;
                        }
                        else if(qty_available < 1){
                            string += ' - <span style="color:red;"> ' + qty_available + item.unit;
                        }
                    }
                     string += '</span></div>';

                    return $('<li>')
                        .append(string)
                        .appendTo(ul);
                }
            };
    }

    //Update line total and check for quantity not greater than max quantity
    $('table#pos_table tbody').on('change', 'input.pos_quantity', function() {
        if (sell_form_validator) {
            sell_form_validator.element($(this));
        }
        if (pos_form_validator) {
            pos_form_validator.element($(this));
        }
        // var max_qty = parseFloat($(this).data('rule-max'));
        var entered_qty = __read_number($(this));

        var tr = $(this).parents('tr');

        var pos_tax_value = __read_number(tr.find('input.pos_tax_value'));
        // var city_tax_value = __read_number(tr.find('input.city_tax_value'));
        // var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));
        // var line_total = (entered_qty * unit_price_inc_tax) + (pos_tax_value * entered_qty);
        var total_tax = (pos_tax_value * entered_qty);

        var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_val'));
        var line_total = entered_qty * unit_price_inc_tax;
        // var taxable_unit =  tr.find('input.taxable_unit').val();

        // var tax_state =  tr.find('input.pos_line_tax_state').val();
        // var customer_state = $("#customer_state").val();
        var tax = __read_number(tr.find('input.pos_line_tax'));  //tax for single quantity
        var every_item =  tr.find('input.pos_line_tax_every_item').val();
        var tax_type =  tr.find('input.pos_line_tax_type').val();
        var qty_box =  tr.find('input.qty_box').val();

        var is_ml =  tr.find('input.pos_line_tax_ml').val();
        if(is_ml == 1){
            qty_box = 1;
        }

        var tax_id =  tr.find('input.pos_line_tax_id').val();
        // if( tax_state != '' &&  customer_state != tax_state){
            // var line_total_tax = 0;
        // } else {
        var line_total_tax = 0;
        var new_qty = entered_qty;
        if(qty_box > 1) var new_qty = entered_qty * qty_box;

            if(tax_id == 55 || tax_id == 58 || tax_id ==  59 || tax_id ==  61 || tax_id ==  62){
                var line_total_tax = entered_qty * tax;
            }
            else if(every_item > 1){
                var times_of_apply_tax = parseInt(parseFloat(new_qty)/every_item);
                var line_total_tax = times_of_apply_tax * tax;
            } else {
                if(tax_type == 1) var line_total_tax = new_qty * tax;
                if(tax_type == 2) var line_total_tax = entered_qty * tax;
            }
        // }

        // city tax calculation
        var city_every_item =  tr.find('input.city_every_item_value').val();
        var city_tax = __read_number(tr.find('input.city_tax_value'));  //tax for single quantity
        var first_item_value = __read_number(tr.find('input.first_item_value_value'));  //first item value value
        var second_item_value = __read_number(tr.find('input.second_item_value_value'));  //second item value value
        var city_tax_type = __read_number(tr.find('input.city_tax_type'));  //city tax type
        var line_total_city_tax = 0;
        if(city_tax > 0){
            if( city_every_item > 1 ){
                var times_of_apply_tax = parseInt(parseFloat(new_qty)/city_every_item);
                 line_total_city_tax = times_of_apply_tax * city_tax;
            } else {
                 if(city_tax_type == 1) line_total_city_tax = new_qty * city_tax;
                 if(city_tax_type == 2) line_total_city_tax = entered_qty * city_tax;
            }
        } else if( first_item_value > 0 ) {
            if(city_tax_type == 1) var second_applicable_qty = new_qty - entered_qty;
            if(city_tax_type == 2) var second_applicable_qty = 0;
             line_total_city_tax = parseFloat(first_item_value * entered_qty) + parseFloat( second_applicable_qty * second_item_value);
        }
        var pos_line_totalamt_text = parseFloat(line_total) + parseFloat(line_total_tax) + parseFloat(line_total_city_tax);
        __write_number(tr.find('input.pos_line_total'), line_total, false, 2);
        __write_number(tr.find('input.pos_tax_value'), total_tax, false, 2);
        __write_number(tr.find('input.city_tax_value_amt'), line_total_city_tax, false, 2);
        tr.find('span.pos_tax_value_text').text(__currency_trans_from_en(total_tax, true));
        tr.find('span.pos_line_city_tax_text').text(__currency_trans_from_en(line_total_city_tax, true));
        tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
        tr.find('span.pos_line_tax_text').text(__currency_trans_from_en(line_total_tax, true));
        tr.find('input.pos_line_tax_amount').val(line_total_tax, true);
        tr.find('span.pos_line_totalamt_text').text(__currency_trans_from_en(pos_line_totalamt_text,true));

        tr.find('input.pos_line_totalamt_value').val(pos_line_totalamt_text);

        var selling_price = __read_number(tr.find('input.pos_unit_price_val'));
        var purchase_exc_tax = __read_number(tr.find('input.cost_val'));
        if(purchase_exc_tax > 0 && selling_price > 0 ){
            var profit_percent = __get_rate(purchase_exc_tax, selling_price);
            tr.find('span.gross-price-val').text(__number_f(profit_percent, false));
        }

        //Change modifier quantity
        tr.find('.modifier_qty_text').each(function() {
            $(this).text(__currency_trans_from_en(entered_qty, false));
        });
        tr.find('.modifiers_quantity').each(function() {
            $(this).val(entered_qty);
        });

        pos_total_row();

        adjustComboQty(tr);
    });

    //If change in unit price update price including tax and line total
    $('table#pos_table tbody').on('change', 'input.pos_unit_price', function() {
        var unit_price = __read_number($(this));
        var tr = $(this).parents('tr');
        // console.log("test");
        //calculate discounted unit price
        var discounted_unit_price = calculate_discounted_unit_price(tr);

        var tax_rate = tr
            .find('select.tax_id')
            .find(':selected')
            .data('rate');
        var quantity = __read_number(tr.find('input.pos_quantity'));
        var city_tax_amt = __read_number(tr.find('input.city_tax_value_amt'));
        var tax_amount = __read_number(tr.find('input.pos_line_tax_amount'));

        // var unit_price_inc_tax = __add_percent(discounted_unit_price, tax_rate);
        var unit_price_inc_tax = __add_percent(unit_price, tax_rate);
        var line_total = quantity * unit_price_inc_tax;
        var pos_line_total = parseFloat(line_total) + parseFloat(tax_amount) + parseFloat(city_tax_amt);
        __write_number(tr.find('input.pos_unit_price_inc_tax'), unit_price);
        __write_number(tr.find('input.pos_line_total'), line_total, false, 2);
        tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
        __write_number(tr.find('input.pos_line_totalamt_value'), pos_line_total, false, 2);
        tr.find('span.pos_line_totalamt_text').text(__currency_trans_from_en(pos_line_total, true));

        var purchase_exc_tax = __read_number(tr.find('input.cost_val'));
        if(purchase_exc_tax > 0 && unit_price > 0 ){
            var profit_percent = __get_rate(purchase_exc_tax, unit_price);
            tr.find('span.gross-price-val').text(__number_f(profit_percent, false));
        }
        pos_each_row(tr);
        pos_total_row();
        round_row_to_iraqi_dinnar(tr);
    });

    //If change in tax rate then update unit price according to it.
    $('table#pos_table tbody').on('change', 'select.tax_id', function() {
        var tr = $(this).parents('tr');

        var tax_rate = tr
            .find('select.tax_id')
            .find(':selected')
            .data('rate');
        var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));

        var discounted_unit_price = __get_principle(unit_price_inc_tax, tax_rate);
        var unit_price = get_unit_price_from_discounted_unit_price(tr, discounted_unit_price);
        __write_number(tr.find('input.pos_unit_price'), unit_price);
        pos_each_row(tr);
    });

    //If change in unit price including tax, update unit price
    $('table#pos_table tbody').on('change', 'input.pos_unit_price_inc_tax', function() {
        var unit_price_inc_tax = __read_number($(this));

        if (iraqi_selling_price_adjustment) {
            unit_price_inc_tax = round_to_iraqi_dinnar(unit_price_inc_tax);
            __write_number($(this), unit_price_inc_tax);
        }

        var tr = $(this).parents('tr');

        var tax_rate = tr
            .find('select.tax_id')
            .find(':selected')
            .data('rate');
        var quantity = __read_number(tr.find('input.pos_quantity'));

        var line_total = quantity * unit_price_inc_tax;
        var discounted_unit_price = __get_principle(unit_price_inc_tax, tax_rate);
        var unit_price = get_unit_price_from_discounted_unit_price(tr, discounted_unit_price);

        __write_number(tr.find('input.pos_unit_price'), unit_price);
        __write_number(tr.find('input.pos_line_total'), line_total, false, 2);
        tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));

        pos_each_row(tr);
        pos_total_row();
    });

    //Change max quantity rule if lot number changes
    $('table#pos_table tbody').on('change', 'select.lot_number', function() {
        var qty_element = $(this)
            .closest('tr')
            .find('input.pos_quantity');

        var tr = $(this).closest('tr');
        var multiplier = 1;
        var unit_name = '';
        var sub_unit_length = tr.find('select.sub_unit').length;
        if (sub_unit_length > 0) {
            var select = tr.find('select.sub_unit');
            multiplier = parseFloat(select.find(':selected').data('multiplier'));
            unit_name = select.find(':selected').data('unit_name');
        }
        var allow_overselling = qty_element.data('allow-overselling');
        if ($(this).val() && !allow_overselling) {
            var lot_qty = $('option:selected', $(this)).data('qty_available');
            var max_err_msg = $('option:selected', $(this)).data('msg-max');

            if (sub_unit_length > 0) {
                lot_qty = lot_qty / multiplier;
                var lot_qty_formated = __number_f(lot_qty, false);
                max_err_msg = __translate('lot_max_qty_error', {
                    max_val: lot_qty_formated,
                    unit_name: unit_name,
                });
            }

            qty_element.attr('data-rule-max-value', lot_qty);
            qty_element.attr('data-msg-max-value', max_err_msg);

            qty_element.rules('add', {
                'max-value': lot_qty,
                messages: {
                    'max-value': max_err_msg,
                },
            });
        } else {
            var default_qty = qty_element.data('qty_available');
            var default_err_msg = qty_element.data('msg_max_default');
            if (sub_unit_length > 0) {
                default_qty = default_qty / multiplier;
                var lot_qty_formated = __number_f(default_qty, false);
                default_err_msg = __translate('pos_max_qty_error', {
                    max_val: lot_qty_formated,
                    unit_name: unit_name,
                });
            }

            qty_element.attr('data-rule-max-value', default_qty);
            qty_element.attr('data-msg-max-value', default_err_msg);

            qty_element.rules('add', {
                'max-value': default_qty,
                messages: {
                    'max-value': default_err_msg,
                },
            });
        }
        qty_element.trigger('change');
    });

    //Change in row discount type or discount amount
    $('table#pos_table tbody').on(
        'change',
        'select.row_discount_type, input.row_discount_amount',
        function() {
            var tr = $(this).parents('tr');

            //calculate discounted unit price
            var discounted_unit_price = calculate_discounted_unit_price(tr);

            var tax_rate = tr
                .find('select.tax_id')
                .find(':selected')
                .data('rate');
            var quantity = __read_number(tr.find('input.pos_quantity'));

            var unit_price_inc_tax = __add_percent(discounted_unit_price, tax_rate);
            var line_total = quantity * unit_price_inc_tax;

            __write_number(tr.find('input.pos_unit_price_inc_tax'), unit_price_inc_tax);
            __write_number(tr.find('input.pos_line_total'), line_total, false, 2);
            tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
            pos_each_row(tr);
            pos_total_row();
            round_row_to_iraqi_dinnar(tr);
        }
    );

    //Remove row on click on remove row
    $('table#pos_table tbody').on('click', 'i.pos_remove_row', function() {
        $(this)
            .parents('tr')
            .remove();
        pos_total_row();
    });

    $(document).on('click', '.product-name', function (e) {
        var url = "/sells/pos/get-stock-history";
        $.ajax({
            url: url,
            dataType: 'html',
            success: function (result) {
                $('.stock_history').html(result);
            },
            data: {
                variation_id:  $(this).data('variation_id'),
                product_id : $(this).data('product_id'),
                status:1
            },
        });
    });

    //Cancel the invoice
    $('button#pos-cancel').click(function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(confirm => {
            if (confirm) {
                reset_pos_form();
            }
        });
    });

    //Save invoice as draft
    $('button#pos-draft').click(function() {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }
        //check delivery method selected or not
        var is_selected = checkDeliveryMethod();
        if(is_selected == false ){
            toastr.warning("Please select delivery method first");
            return false;
        }
        var is_valid = isValidPosForm();
        if (is_valid != true) {
            return;
        }

        var data = pos_form_obj.serialize();
        data = data + '&status=draft';
        var url = pos_form_obj.attr('action');

        disable_pos_form_actions();
        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function(result) {
                enable_pos_form_actions();
                if (result.success == 1) {
                    reset_pos_form();
                    toastr.success(result.msg);
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });

    //Save invoice as payment verified
    $('button#pos-payment-verify').click(function() {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }
        //check delivery method selected or not
        var is_selected = checkDeliveryMethod();
        if(is_selected == false ){
            toastr.warning("Please select delivery method first");
            return false;
        }
        var is_valid = isValidPosForm();
        if (is_valid != true) {
            return;
        }

        var data = pos_form_obj.serialize();
        data = data + '&status=payment-verified';
        var url = pos_form_obj.attr('action');

        disable_pos_form_actions();
        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function(result) {
                enable_pos_form_actions();
                if (result.success == 1) {
                    reset_pos_form();
                    pos_print(result.receipt);
                    toastr.success(result.msg);
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });

    //Save invoice as Quotation
    $('button#pos-quotation').click(function() {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }
        //check delivery method selected or not
        var is_selected = checkDeliveryMethod();
        if(is_selected == false ){
            toastr.warning("Please select delivery method first");
            return false;
        }
        var is_valid = isValidPosForm();
        if (is_valid != true) {
            return;
        }

        var data = pos_form_obj.serialize();
        data = data + '&status=quotation';
        var url = pos_form_obj.attr('action');

        disable_pos_form_actions();
        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function(result) {
                enable_pos_form_actions();
                if (result.success == 1) {
                    reset_pos_form();
                    toastr.success(result.msg);

                    //Check if enabled or not
                    // if (result.receipt.is_enabled) {
                    //     pos_print(result.receipt);
                    // }
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });

    //Finalize invoice, open payment modal
    $('button#pos-finalize').click(function() {

        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }
        //check delivery method selected or not
        var is_selected = checkDeliveryMethod();
        if(is_selected == false ){
            toastr.warning("Please select delivery method first");
            return false;
        }
        if ($('#reward_point_enabled').length) {
            var validate_rp = isValidatRewardPoint();
            if (!validate_rp['is_valid']) {
                toastr.error(validate_rp['msg']);
                return false;
            }
        }

        $('#modal_payment').modal('show');
    });

    $('#modal_payment').one('shown.bs.modal', function() {
        $('#modal_payment')
            .find('input')
            .filter(':visible:first')
            .focus()
            .select();
        if ($('form#edit_pos_sell_form').length == 0) {
            $(this).find('#method_0').change();
        }
    });

    //Finalize without showing payment options
    $('button.pos-express-finalize').click(function() {

        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        if ($('#reward_point_enabled').length) {
            var validate_rp = isValidatRewardPoint();
            if (!validate_rp['is_valid']) {
                toastr.error(validate_rp['msg']);
                return false;
            }
        }

        var pay_method = $(this).data('pay_method');

        //If pay method is credit sale submit form
        if (pay_method == 'credit_sale') {
            $('#is_credit_sale').val(1);
            pos_form_obj.submit();
            return true;
        } else {
            if ($('#is_credit_sale').length) {
                $('#is_credit_sale').val(0);
            }
        }

        //Check for remaining balance & add it in 1st payment row
        var total_payable = __read_number($('input#final_total_input'));
        var total_paying = __read_number($('input#total_paying_input'));
        if (total_payable > total_paying) {
            var bal_due = total_payable - total_paying;

            var first_row = $('#payment_rows_div')
                .find('.payment-amount')
                .first();
            var first_row_val = __read_number(first_row);
            first_row_val = first_row_val + bal_due;
            __write_number(first_row, first_row_val);
            first_row.trigger('change');
        }

        //Change payment method.
        var payment_method_dropdown = $('#payment_rows_div')
            .find('.payment_types_dropdown')
            .first();

        payment_method_dropdown.val(pay_method);
        payment_method_dropdown.change();
        if (pay_method == 'card') {
            $('div#card_details_modal').modal('show');
        } else if (pay_method == 'suspend') {
            $('div#confirmSuspendModal').modal('show');
        } else {
            pos_form_obj.submit();
        }
    });

    $('div#card_details_modal').on('shown.bs.modal', function(e) {
        $('input#card_number').focus();
    });

    $('div#confirmSuspendModal').on('shown.bs.modal', function(e) {
        $(this)
            .find('textarea')
            .focus();
    });

    //on save card details
    $('button#pos-save-card').click(function() {
        $('input#card_number_0').val($('#card_number').val());
        $('input#card_holder_name_0').val($('#card_holder_name').val());
        $('input#card_transaction_number_0').val($('#card_transaction_number').val());
        $('select#card_type_0').val($('#card_type').val());
        $('input#card_month_0').val($('#card_month').val());
        $('input#card_year_0').val($('#card_year').val());
        $('input#card_security_0').val($('#card_security').val());

        $('div#card_details_modal').modal('hide');
        pos_form_obj.submit();
    });

    $('button#pos-suspend').click(function() {
        $('input#is_suspend').val(1);
        $('div#confirmSuspendModal').modal('hide');
        pos_form_obj.submit();
        $('input#is_suspend').val(0);
    });

    //fix select2 input issue on modal
    $('#modal_payment')
        .find('.select2')
        .each(function() {
            $(this).select2({
                dropdownParent: $('#modal_payment'),
            });
        });

    $('button#add-payment-row').click(function() {
        var row_index = $('#payment_row_index').val();
        var location_id = $('input#location_id').val();
        $.ajax({
            method: 'POST',
            url: '/sells/pos/get_payment_row',
            data: { row_index: row_index, location_id: location_id },
            dataType: 'html',
            success: function(result) {
                if (result) {
                    var appended = $('#payment_rows_div').append(result);

                    var total_payable = __read_number($('input#final_total_input'));
                    var total_paying = __read_number($('input#total_paying_input'));
                    var b_due = total_payable - total_paying;
                    $(appended)
                        .find('input.payment-amount')
                        .focus();
                    $(appended)
                        .find('input.payment-amount')
                        .last()
                        .val(__currency_trans_from_en(b_due, false))
                        .change()
                        .select();
                    __select2($(appended).find('.select2'));
                    $(appended).find('#method_' + row_index).change();
                    $('#payment_row_index').val(parseInt(row_index) + 1);
                }
            },
        });
    });

    $(document).on('click', '.remove_payment_row', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                $(this)
                    .closest('.payment_row')
                    .remove();
                calculate_balance_due();
            }
        });
    });

    pos_form_validator = pos_form_obj.validate({
        submitHandler: function(form) {
            // var total_payble = __read_number($('input#final_total_input'));
            // var total_paying = __read_number($('input#total_paying_input'));
            var cnf = true;

            //Ignore if the difference is less than 0.5
            if ($('input#in_balance_due').val() >= 0.5) {
                //cnf = confirm(LANG.paid_amount_is_less_than_payable);
                // if( total_payble > total_paying ){
                //  cnf = confirm( LANG.paid_amount_is_less_than_payable );
                // } else if(total_payble < total_paying) {
                //  alert( LANG.paid_amount_is_more_than_payable );
                //  cnf = false;
                // }
            }

            var total_advance_payments = 0;
            $('#payment_rows_div').find('select.payment_types_dropdown').each(function() {
                if ($(this).val() == 'advance') {
                    total_advance_payments++
                };
            });

            // if (total_advance_payments > 1) {
            //     alert(LANG.advance_payment_cannot_be_more_than_once);
            //     return false;
            // }

            if (cnf) {
                disable_pos_form_actions();

                var data = $(form).serialize();
                data = data + '&status=final';
                var url = $(form).attr('action');
                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == 1) {
                            $('#modal_payment').modal('hide');
                            toastr.success(result.msg);

                            reset_pos_form();

                            //Check if enabled or not
                            if (result.receipt.is_enabled) {
                                pos_print(result.receipt);
                            }
                        } else {
                            toastr.error(result.msg);
                        }

                        enable_pos_form_actions();
                    },
                });
            }
            return false;
        },
    });

    $(document).on('change', '.payment-amount', function() {
        calculate_balance_due();
    });

    //Update discount
    $('button#posEditDiscountModalUpdate').click(function() {

        //if discount amount is not valid return false
        if (!$("#discount_amount_modal").valid()) {
            return false;
        }
        //Close modal
        $('div#posEditDiscountModal').modal('hide');

        //Update values
        $('input#discount_type').val($('select#discount_type_modal').val());
        __write_number($('input#discount_amount'), __read_number($('input#discount_amount_modal')));

        if ($('#reward_point_enabled').length) {
            var reward_validation = isValidatRewardPoint();
            if (!reward_validation['is_valid']) {
                toastr.error(reward_validation['msg']);
                $('#rp_redeemed_modal').val(0);
                $('#rp_redeemed_modal').change();
            }
            updateRedeemedAmount();
        }

        pos_total_row();
    });

    //Shipping
    $('button#posShippingModalUpdate').click(function() {
        //Close modal
        $('div#posShippingModal').modal('hide');

        //update shipping details
        $('input#shipping_details').val($('#shipping_details_modal').val());

        $('input#shipping_address').val($('#shipping_address_modal').val());
        $('input#shipping_status').val($('#shipping_status_modal').val());
        $('input#delivered_to').val($('#delivered_to_modal').val());

        //Update shipping charges
        __write_number(
            $('input#shipping_charges'),
            __read_number($('input#shipping_charges_modal'))
        );

        //$('input#shipping_charges').val(__read_number($('input#shipping_charges_modal')));

        pos_total_row();
    });
 //Shipping
    $('button#posShippingModalUpdateSelf').click(function() {
        $("#delivery_method").val('posShippingModalUpdateSelf');
        //Close modal
        let customer_id = $('#customer_id').val();
        if (customer_id === "") {
            toastr.error("Customer Not Selected!");
        } else {
             $('.notpickpack').prop('checked', false);
            $(this).attr('disabled', true).addClass('highlight-btn-borders');
            $('button#posShippingModalUpdateDelivery').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdatePickup').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateShipping').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateDeliveryNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateSelfNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateShippingNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            toastr.success("Delivery Method Added!");
        }
    });

    //Shipping
    $('button#posShippingModalUpdateDelivery').click(function() {
        $("#delivery_method").val('posShippingModalUpdateDelivery');

        //Close modal
        let customer_id = $('#customer_id').val();
        if (customer_id === "") {
            toastr.error("Customer Not Selected!");
        } else {
             $('.notpickpack').prop('checked', false);
            $(this).attr('disabled', true).addClass('highlight-btn-borders');
            $('button#posShippingModalUpdateSelf').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdatePickup').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateShipping').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateDeliveryNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateSelfNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateShippingNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            deliveryMethod(customer_id);
        }
    });

    //Shipping
    $('button#posShippingModalUpdatePickup').click(function() {
        $("#delivery_method").val('posShippingModalUpdatePickup');
        //Close modal
        let customer_id = $('#customer_id').val();
        if (customer_id === "") {
            toastr.error("Customer Not Selected!");
        } else {
             $('.notpickpack').prop('checked', false);
            $(this).attr('disabled', true).addClass('highlight-btn-borders');
            $('button#posShippingModalUpdateSelf').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateDelivery').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateShipping').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateDeliveryNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateSelfNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateShippingNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            deliveryMethod(customer_id);
        }
    });

    //Shipping
    $('button#posShippingModalUpdateShipping').click(function() {
        $("#delivery_method").val('posShippingModalUpdateShipping');
        //Close modal
        let customer_id = $('#customer_id').val();
        if (customer_id === "") {
            toastr.error("Customer Not Selected!");
        } else {
             $('.notpickpack').prop('checked', false);
            $(this).attr('disabled', true).addClass('highlight-btn-borders');
            $('button#posShippingModalUpdateSelf').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateDelivery').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdatePickup').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateDeliveryNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateSelfNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateShippingNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            deliveryMethod(customer_id);
        }
    });

      //Shipping
    $('button#posShippingModalUpdateSelfNonPicking').click(function() {
        $("#delivery_method").val('posShippingModalUpdateSelfNonPicking');
        //Close modal
        let customer_id = $('#customer_id').val();
        if (customer_id === "") {
            toastr.error("Customer Not Selected!");
        } else {
            $('.notpickpack').prop('checked', true);
            $(this).attr('disabled', true).addClass('highlight-btn-borders');
            $('button#posShippingModalUpdateSelf').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateDelivery').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdatePickup').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateShipping').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateDeliveryNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateShippingNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            toastr.success("Delivery Method Added!");
        }
    });

    //Shipping
    $('button#posShippingModalUpdateDeliveryNonPicking').click(function() {
        $("#delivery_method").val('posShippingModalUpdateDeliveryNonPicking');
        //Close modal
        let customer_id = $('#customer_id').val();
        if (customer_id === "") {
            toastr.error("Customer Not Selected!");
        } else {
             $('.notpickpack').prop('checked', true);
            $(this).attr('disabled', true).addClass('highlight-btn-borders');
            $('button#posShippingModalUpdateSelf').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdatePickup').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateShipping').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateDelivery').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateSelfNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateShippingNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            deliveryMethod(customer_id);
        }
    });


    //Shipping
    $('button#posShippingModalUpdateShippingNonPicking').click(function() {
        $("#delivery_method").val('posShippingModalUpdateShippingNonPicking');
        //Close modal
        let customer_id = $('#customer_id').val();
        if (customer_id === "") {
            toastr.error("Customer Not Selected!");
        } else {
             $('.notpickpack').prop('checked', true);
            $(this).attr('disabled', true).addClass('highlight-btn-borders');
            $('button#posShippingModalUpdateSelf').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateDelivery').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdatePickup').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateShipping').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateDeliveryNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            $('button#posShippingModalUpdateSelfNonPicking').attr('disabled', false).removeClass('highlight-btn-borders');
            deliveryMethod(customer_id);
        }
    });


    function checkDeliveryMethod(){
        var isDisabledSelf = $('#posShippingModalUpdateSelf').prop('disabled');
        var isDisabledDelivery = $('#posShippingModalUpdateDelivery').prop('disabled');
        var isDisabledPickup = $('#posShippingModalUpdatePickup').prop('disabled');
        var isDisabledShipping = $('#posShippingModalUpdateShipping').prop('disabled');
        var isDisabledDeliverynon = $('button#posShippingModalUpdateDeliveryNonPicking').prop('disabled');
        var isDisabledSelfnon = $('button#posShippingModalUpdateSelfNonPicking').prop('disabled');
        var isDisabledShippingnon = $('button#posShippingModalUpdateShippingNonPicking').prop('disabled');
        if(isDisabledDelivery == false && isDisabledPickup == false && isDisabledShipping == false && isDisabledDeliverynon == false && isDisabledSelfnon == false && isDisabledShippingnon == false){
            return false;
        } else {
            return true;
        }
    }

    function deliveryMethod(customer_id) {
        $.ajax({
            url: '/sells/pos/get-customer-address/' + customer_id,
            type: 'GET',
            data: {},
            success: function(address) {
                //update shipping details
                $('input#shipping_details').val(address);
                $('input#shipping_address').val(address);
                $('input#shipping_status').val("ordered");
                $('input#delivered_to').val("");

                //Update shipping charges
                __write_number(
                    $('input#shipping_charges'),
                    __read_number($('input#shipping_charges_modal'))
                );

                pos_total_row();
                toastr.success("Delivery Method Added!");
            }
        });
    }

    $('#posShippingModal').on('shown.bs.modal', function() {
        $('#posShippingModal')
            .find('#shipping_details_modal')
            .filter(':visible:first')
            .focus()
            .select();
    });

    $(document).on('shown.bs.modal', '.row_edit_product_price_model', function() {
        $('.row_edit_product_price_model')
            .find('input')
            .filter(':visible:first')
            .focus()
            .select();
    });

    //Update Order tax
    $('button#posEditOrderTaxModalUpdate').click(function() {
        //Close modal
        $('div#posEditOrderTaxModal').modal('hide');

        var tax_obj = $('select#order_tax_modal');
        var tax_id = tax_obj.val();
        var tax_rate = tax_obj.find(':selected').data('rate');

        $('input#tax_rate_id').val(tax_id);

        __write_number($('input#tax_calculation_amount'), tax_rate);
        pos_total_row();
    });

    $(document).on('click', '.add_new_customer', function() {
        $('#customer_id').select2('close');
        var name = $(this).data('name');
        $('.contact_modal')
            .find('input#name')
            .val(name);
        $('.contact_modal')
            .find('select#contact_type')
            .val('customer')
            .closest('div.contact_type_div')
            .addClass('hide');
        $('.contact_modal').modal('show');
    });
    $('form#quick_add_contact')
        .submit(function(e) {
            e.preventDefault();
        })
        .validate({
            rules: {
                contact_id: {
                    remote: {
                        url: '/contacts/check-contact-id',
                        type: 'post',
                        data: {
                            contact_id: function() {
                                return $('#contact_id').val();
                            },
                            hidden_id: function() {
                                if ($('#hidden_id').length) {
                                    return $('#hidden_id').val();
                                } else {
                                    return '';
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
            submitHandler: function(form) {
                $(form)
                    .find('button[type="submit"]')
                    .attr('disabled', true);
                var data = $(form).serialize();
                $.ajax({
                    method: 'POST',
                    url: $(form).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            $('select#customer_id').append(
                                $('<option>', { value: result.data.id, text: result.data.name })
                            );
                            $('select#customer_id')
                                .val(result.data.id)
                                .trigger('change');
                            $('div.contact_modal').modal('hide');
                            toastr.success(result.msg);
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            },
        });
    $('.contact_modal').on('hidden.bs.modal', function() {

        $('form#quick_add_contact')
            .find('button[type="submit"]')
            .removeAttr('disabled');
        $('form#quick_add_contact')[0].reset();
    });

    //Updates for add sell
    $('select#discount_type, input#discount_amount, input#shipping_charges, \
        input#rp_redeemed_amount').change(function() {
        pos_total_row();
    });
    $('select#tax_rate_id').change(function() {
        var tax_rate = $(this)
            .find(':selected')
            .data('rate');
        __write_number($('input#tax_calculation_amount'), tax_rate);
        pos_total_row();
    });
    //Datetime picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    //Direct sell submit
    sell_form = $('form#add_sell_form');
    if ($('form#edit_sell_form').length) {
        sell_form = $('form#edit_sell_form');
        pos_total_row();
    }
    sell_form_validator = sell_form.validate();

    $('button#submit-sell, button#save-and-print').click(function(e) {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        if ($(this).attr('id') == 'save-and-print') {
            $('#is_save_and_print').val(1);
        } else {
            $('#is_save_and_print').val(0);
        }

        if ($('#reward_point_enabled').length) {
            var validate_rp = isValidatRewardPoint();
            if (!validate_rp['is_valid']) {
                toastr.error(validate_rp['msg']);
                return false;
            }
        }

        if (sell_form.valid()) {
            window.onbeforeunload = null;
            $(this).attr('disabled', true);
            sell_form.submit();
        }
    });

    //REPAIR MODULE:check if repair module field is present send data to filter product
    var is_enabled_stock = null;
    if ($("#is_enabled_stock").length) {
        is_enabled_stock = $("#is_enabled_stock").val();
    }

    var device_model_id = null;
    if ($("#repair_model_id").length) {
        device_model_id = $("#repair_model_id").val();
    }

    //Show product list.
    get_product_suggestion_list(
        $('select#product_category').val(),
        $('select#product_brand').val(),
        $('input#location_id').val(),
        null,
        is_enabled_stock,
        device_model_id
    );
    $('select#product_category, select#product_brand, select#select_location_id').on('change', function(e) {
        $('input#suggestion_page').val(1);
        var location_id = $('input#location_id').val();
        if (location_id != '' || location_id != undefined) {
            get_product_suggestion_list(
                $('select#product_category').val(),
                $('select#product_brand').val(),
                $('input#location_id').val(),
                null
            );
        }

        get_featured_products();
    });

    $(document).on('click', 'div.product_box', function() {
        //Check if location is not set then show error message.

        var customer_id = $("#customer_id").val();

        if (customer_id == '') {
            toastr.warning("Please Select Customer");
            return false;
        }
        if ($('input#location_id').val() == '') {
            toastr.warning(LANG.select_location);
        } else {
            pos_product_row($(this).data('variation_id'));
        }
    });

    $(document).on('shown.bs.modal', '.row_description_modal', function() {
        $(this)
            .find('textarea')
            .first()
            .focus();
    });

    //Press enter on search product to jump into last quantty and vice-versa
    $('#search_product').keydown(function(e) {
        var key = e.which;
        if (key == 9) {
            // the tab key code
            e.preventDefault();
            if ($('#pos_table tbody tr').length > 0) {
                $('#pos_table tbody tr:last')
                    .find('input.pos_quantity')
                    .focus()
                    .select();
            }
        }
    });
    $('#pos_table').on('keypress', 'input.pos_quantity', function(e) {
        var key = e.which;
        if (key == 13) {
            // the enter key code
            $('#search_product').focus();
        }
    });

    $('#exchange_rate').change(function() {
        var curr_exchange_rate = 1;
        if ($(this).val()) {
            curr_exchange_rate = __read_number($(this));
        }
        var total_payable = __read_number($('input#final_total_input'));
        var shown_total = total_payable * curr_exchange_rate;
        $('span#total_payable').text(__currency_trans_from_en(shown_total, false));
    });

    $('select#price_group').change(function() {
        var rowCount = $('#pos_table tr').length;
        var price_group_id = $('#price_group').val();
       if(rowCount > 1 && price_group_id !== "" && typeof(price_group_id) != "undefined") {
            swal({
              title: "You must need to reload the page if you want to change customer",
              text: "",
              icon: "warning",
            });
            return false;
        }
        var id = $(this).val();
        document.cookie = "myJavascriptVar = "+id+"; path=/;"
        if(id && (id != 0 || id != null)){
            $('span.pos_unit_price').addClass("hide");
            $('input.pos_unit_price').removeClass("hide");
            document.cookie = "myJavascriptVar = "+id+"; path=/;"
        }else{
            document.cookie = "myJavascriptVar = 0; path=/;"
            $('span.pos_unit_price').removeClass("hide");
            $('input.pos_unit_price').addClass("hide");
        }
        $('input#hidden_price_group').val($(this).val());
    });

    //Quick add product
    $(document).on('click', 'button.pos_add_quick_product', function() {
        var url = $(this).data('href');
        var container = $(this).data('container');
        $.ajax({
            url: url + '?product_for=pos&action=add',
            dataType: 'html',
            success: function(result) {
                $(container)
                    .html(result)
                    .modal('show');
                $('.os_exp_date').datepicker({
                    autoclose: true,
                    format: 'dd-mm-yyyy',
                    clearBtn: true,
                });
            },
        });
    });

    $(document).on('change', 'form#quick_add_product_form input#single_dpp', function() {
        var unit_price = __read_number($(this));
        $('table#quick_product_opening_stock_table tbody tr').each(function() {
            var input = $(this).find('input.unit_price');
            __write_number(input, unit_price);
            input.change();
        });
    });

    $(document).on('quickProductAdded', function(e) {
        //Check if location is not set then show error message.
        if ($('input#location_id').val() == '') {
            toastr.warning(LANG.select_location);
        } else {
            pos_product_row(e.variation.id);
        }
    });

    $('div.view_modal').on('show.bs.modal', function() {
        __currency_convert_recursively($(this));
    });

    $('table#pos_table').on('change', 'select.sub_unit', function() {
        var tr = $(this).closest('tr');
        var base_unit_selling_price = tr.find('input.hidden_base_unit_sell_price').val();

        var selected_option = $(this).find(':selected');

        var multiplier = parseFloat(selected_option.data('multiplier'));

        var allow_decimal = parseInt(selected_option.data('allow_decimal'));

        tr.find('input.base_unit_multiplier').val(multiplier);

        var unit_sp = base_unit_selling_price * multiplier;

        var sp_element = tr.find('input.pos_unit_price');
        __write_number(sp_element, unit_sp);

        sp_element.change();

        var qty_element = tr.find('input.pos_quantity');
        var base_max_avlbl = qty_element.data('qty_available');
        var error_msg_line = 'pos_max_qty_error';

        if (tr.find('select.lot_number').length > 0) {
            var lot_select = tr.find('select.lot_number');
            if (lot_select.val()) {
                base_max_avlbl = lot_select.find(':selected').data('qty_available');
                error_msg_line = 'lot_max_qty_error';
            }
        }

        qty_element.attr('data-decimal', allow_decimal);
        var abs_digit = true;
        if (allow_decimal) {
            abs_digit = false;
        }
        qty_element.rules('add', {
            abs_digit: abs_digit,
        });

        if (base_max_avlbl) {
            var max_avlbl = parseFloat(base_max_avlbl) / multiplier;
            var formated_max_avlbl = __number_f(max_avlbl);
            var unit_name = selected_option.data('unit_name');
            var max_err_msg = __translate(error_msg_line, {
                max_val: formated_max_avlbl,
                unit_name: unit_name,
            });
            qty_element.attr('data-rule-max-value', max_avlbl);
            qty_element.attr('data-msg-max-value', max_err_msg);
            qty_element.rules('add', {
                'max-value': max_avlbl,
                messages: {
                    'max-value': max_err_msg,
                },
            });
            qty_element.trigger('change');
        }
        adjustComboQty(tr);
    });

    //Confirmation before page load.
    window.onbeforeunload = function() {
        if ($('form#edit_pos_sell_form').length == 0) {
            if ($('table#pos_table tbody tr').length > 0) {
                return LANG.sure;
            } else {
                return null;
            }
        }
    }
    $(window).resize(function() {
        var win_height = $(window).height();
        div_height = __calculate_amount('percentage', 63, win_height);
        $('div.pos_product_div').css('min-height', div_height + 'px');
        $('div.pos_product_div').css('max-height', div_height + 'px');
    });

    //Used for weighing scale barcode
    $('#weighing_scale_modal').on('shown.bs.modal', function(e) {

        //Attach the scan event
        onScan.attachTo(document, {
            suffixKeyCodes: [13], // enter-key expected at the end of a scan
            reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
            onScan: function(sCode, iQty) {
                // console.log('Scanned: ' + iQty + 'x ' + sCode);
                $('input#weighing_scale_barcode').val(sCode);
                $('button#weighing_scale_submit').trigger('click');
            },
            onScanError: function(oDebug) {
                console.log(oDebug);
            },
            minLength: 2
                // onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
                //     console.log('Pressed: ' + iKeyCode);
                // }
        });

        $('input#weighing_scale_barcode').focus();
    });

    $('#weighing_scale_modal').on('hide.bs.modal', function(e) {
        //Detach from the document once modal is closed.
        onScan.detachFrom(document);
    });

    $('button#weighing_scale_submit').click(function() {

        var price_group = '';
        if ($('#price_group').length > 0) {
            price_group = $('#price_group').val();
        }

        if ($('#weighing_scale_barcode').val().length > 0) {
            pos_product_row(null, null, $('#weighing_scale_barcode').val());
            $('#weighing_scale_modal').modal('hide');
            $('input#weighing_scale_barcode').val('');
        } else {
            $('input#weighing_scale_barcode').focus();
        }
    });

    $('#show_featured_products').click(function() {
        if (!$('#featured_products_box').is(':visible')) {
            $('#featured_products_box').fadeIn();
        } else {
            $('#featured_products_box').fadeOut();
        }
    });
    validate_discount_field();
    set_payment_type_dropdown();


    $("#internal_search").on('keyup',function(){
       var term = $(this).val().toLowerCase();
       var categories = $("#category_id").val();
       internalSearch(categories, term);
    });

    $("#category_id").on('change',function(){
        var categories = $("#category_id").val();
        var term = $("#internal_search").val();
        internalSearch(categories, term);
    });

});


function internalSearch(categories, term){
    var rowCount = $('#pos_table tr').length;
    if(term.length>1 || categories.length>0){
        if(rowCount>1){
            var total = 0;
            var total_qty = 0;
            var total_tax = 0;
            $('#pos_table tbody').find('tr').each(function() {
               var name = ($(this).find('span.product-name').html()).toLowerCase();
               var category_id = $(this).find('input.category-id').val();

            //    console.log("cat_arr",categories.includes(category_id) );
               var is_cat_match = true;
               var res = 0;
               if(categories.length > 0) is_cat_match = categories.includes(category_id);
               if(term.length>1) res = name.indexOf(term);
               // if(res == -1){
               //  $(this).addClass('hide');
               // }else{
               //  $(this).removeClass('hide');
               // }

                if(res != -1 && is_cat_match == true){
                    $(this).removeClass('hide');
                    total += parseFloat($(this).find('input.pos_line_total').val());
                    total_qty += parseFloat($(this).find('input.input_quantity').val());
                    total_tax = total_tax + parseFloat($(this).find('input.pos_line_tax').val()) + parseFloat($(this).find('input.city_tax_value_amt').val());

               }else{
                    $(this).addClass('hide');
               }
            })
            if(total_tax>0)
                var final_total = parseFloat(total) + parseFloat(total_tax);
            else
                var final_total = parseFloat(total)

            $(".internal-total-qty").html(total_qty);
            $(".internal-sub-total").html(total.toFixed(2));
            $(".internal-total-tax").html(total_tax);
            $(".internal-total-amt").html(final_total.toFixed(2));
        }
    } else {
        if(rowCount>1){
            $('#pos_table tbody').find('tr').each(function() {
                $(this).removeClass('hide');
            })
        }
    }
}

function setInternalSearchCategory(){
    var cat_arr = [];
    $("#category_id").html('');
    $('#pos_table tbody').find('tr').each(function() {
        var category_id = $(this).find('input.category-id').val();
        var category_text = $(this).find('span.category-text').text();

        if(cat_arr.includes(category_id) == false){
            cat_arr.push(category_id);
            $("#category_id").append( $('<option value='+category_id+'>'+category_text+'</option>'));
        }
    })
}

function set_payment_type_dropdown() {
    var payment_settings = $('#location_id').data('default_payment_accounts');
    payment_settings = payment_settings ? payment_settings : [];
    enabled_payment_types = [];
    for (var key in payment_settings) {
        if (payment_settings[key] && payment_settings[key]['is_enabled']) {
            enabled_payment_types.push(key);
        }
    }
    if (enabled_payment_types.length) {
        $(".payment_types_dropdown > option").each(function() {
            if ($(this).val()) {
                if (enabled_payment_types.indexOf($(this).val()) != -1) {
                    $(this).removeClass('hide');
                } else {
                    $(this).addClass('hide');
                }
            }
        });
    }
}

function get_featured_products() {
    var location_id = $('#location_id').val();
    if (location_id && $('#featured_products_box').length > 0) {
        $.ajax({
            method: 'GET',
            url: '/sells/pos/get-featured-products/' + location_id,
            dataType: 'html',
            success: function(result) {
                if (result) {
                    $('#feature_product_div').removeClass('hide');
                    $('#featured_products_box').html(result);
                } else {
                    $('#feature_product_div').addClass('hide');
                    $('#featured_products_box').html('');
                }
            },
        });
    } else {
        $('#feature_product_div').addClass('hide');
        $('#featured_products_box').html('');
    }
}

function get_product_suggestion_list(category_id, brand_id, location_id, url = null, is_enabled_stock = null, repair_model_id = null) {
    if ($('div#product_list_body').length == 0) {
        return false;
    }

    if (url == null) {
        url = '/sells/pos/get-product-suggestion';
    }
    $('#suggestion_page_loader').fadeIn(700);
    var page = $('input#suggestion_page').val();
    if (page == 1) {
        $('div#product_list_body').html('');
    }
    if ($('div#product_list_body').find('input#no_products_found').length > 0) {
        $('#suggestion_page_loader').fadeOut(700);
        return false;
    }
    $.ajax({
        method: 'GET',
        url: url,
        data: {
            category_id: category_id,
            brand_id: brand_id,
            location_id: location_id,
            page: page,
            is_enabled_stock: is_enabled_stock,
            repair_model_id: repair_model_id
        },
        dataType: 'html',
        success: function(result) {
            $('div#product_list_body').append(result);
            $('#suggestion_page_loader').fadeOut(700);
        },
    });
}

//Get recent transactions
function get_recent_transactions(status, element_obj) {
    if (element_obj.length == 0) {
        return false;
    }
    var transaction_sub_type = $("#transaction_sub_type").val();
    $.ajax({
        method: 'GET',
        url: '/sells/pos/get-recent-transactions',
        data: { status: status, transaction_sub_type: transaction_sub_type },
        dataType: 'html',
        success: function(result) {
            element_obj.html(result);
            __currency_convert_recursively(element_obj);
        },
    });
}

function get_customer_recent_transactions(status, element_obj) {
    if (element_obj.length == 0) {
        return false;
    }

    var customer_id = $("#customer_id").val();

    if (customer_id == '') {
        $('#customer_recent_transactions_modal').modal('hide');
        toastr.warning("Please Select Customer");
        return false;
    }
    var transaction_sub_type = $("#transaction_sub_type").val();
    $.ajax({
        method: 'GET',
        url: '/sells/pos/get-recent-transactions',
        data: { status: status, transaction_sub_type: transaction_sub_type, customer_id: customer_id },
        dataType: 'html',
        success: function(result) {
            element_obj.html(result);
            __currency_convert_recursively(element_obj);
        },
    });
}


//variation_id is null when weighing_scale_barcode is used.
function pos_product_row(variation_id = null, purchase_line_id = null, weighing_scale_barcode = null, quantity = 1, duplicate = null) {
    let selected_customer_id = $('#customer_id').val();
    if (selected_customer_id === "" && !duplicate) {
        toastr.error("Customer Not Selected!");
        return false;
    }
    //Get item addition method
    var item_addtn_method = 0;
    var add_via_ajax = true;

    if (variation_id != null && $('#item_addition_method').length) {
        item_addtn_method = $('#item_addition_method').val();
    }
    if (item_addtn_method == 0) {
        add_via_ajax = true;
    } else {
        var is_added = false;
        var rowCount = $('#pos_table tr').length;
        var i = 1;
        //Search for variation id in each row of pos table
        $('#pos_table tbody')
            .find('tr')
            .each(function() {
                $(this).attr("id",i);
                $(this).removeClass('pos-last-tr');
                var row_v_id = $(this)
                    .find('.row_variation_id')
                    .val();
                var enable_sr_no = $(this)
                    .find('.enable_sr_no')
                    .val();
                var modifiers_exist = false;
                if ($(this).find('input.modifiers_exist').length > 0) {
                    modifiers_exist = true;
                }

                if (
                    row_v_id == variation_id &&
                    enable_sr_no !== '1' &&
                    !modifiers_exist &&
                    !is_added
                ) {
                    add_via_ajax = false;
                    is_added = true;

                    //Increment product quantity
                    $(this).attr("id",rowCount);
                    qty_element = $(this).find('.pos_quantity');
                    var qty = __read_number(qty_element);
                    __write_number(qty_element, qty + 1);
                    qty_element.change();
                    $(this).addClass('pos-last-tr');
                    round_row_to_iraqi_dinnar($(this));
                    var audio = $('#success-audio')[0];
                    if (audio !== undefined) {
                        audio.play();
                    }

                    /*$('input#search_product')
                        .focus()
                        .select();*/
                }
                i++;
            });
        sortTableRow();
    }

    if (add_via_ajax) {
        var product_row = $('input#product_row_count').val();
        var location_id = $('input#location_id').val();
        var customer_id = $('select#customer_id').val();
        var is_direct_sell = false;
        if (
            $('input[name="is_direct_sale"]').length > 0 &&
            $('input[name="is_direct_sale"]').val() == 1
        ) {
            is_direct_sell = true;
        }

        var price_group = '';
        if ($('#price_group').length > 0) {
            price_group = parseInt($('#price_group').val());
        }

        //If default price group present
        if ($('#default_price_group').length > 0 &&
            !price_group) {
            price_group = $('#default_price_group').val();
        }

        //If types of service selected give more priority
        if ($('#types_of_service_price_group').length > 0 &&
            $('#types_of_service_price_group').val()) {
            price_group = $('#types_of_service_price_group').val();
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
                quantity: quantity
            },
            dataType: 'json',
            success: function(result) {
                 if(result == 0){
                    swal({
                      title: "You cannot sell Mini Cigar for this state.",
                      text: "",
                      icon: "warning",
                    });
                }
                else if(result.success == false){
                    toastr.error(result.message);
                }
                else if (result.success == true) {
                    $('table#pos_table tbody')
                        .append(result.html_content)
                        .find('input.pos_quantity');
                    //increment row count
                    $('input#product_row_count').val(parseInt(product_row) + 1);
                    var this_row = $('table#pos_table tbody')
                        .find('tr')
                        .last();
                    pos_each_row(this_row);

                    //For initial discount if present
                    var line_total = __read_number(this_row.find('input.pos_line_total'));
                    this_row.find('span.pos_line_total_text').text(line_total);

                    pos_total_row();

                    //Check if multipler is present then multiply it when a new row is added.
                    if (__getUnitMultiplier(this_row) > 1) {
                        this_row.find('select.sub_unit').trigger('change');
                    }

                    if (result.enable_sr_no == '1') {
                        var new_row = $('table#pos_table tbody')
                            .find('tr')
                            .last();
                        new_row.find('.add-pos-row-description').trigger('click');
                    }

                    round_row_to_iraqi_dinnar(this_row);
                    __currency_convert_recursively(this_row);

                    /*$('input#search_product')
                        .focus()
                        .select();*/

                    //Used in restaurant module
                    if (result.html_modifier) {
                        $('table#pos_table tbody')
                            .find('tr')
                            .last()
                            .find('td:first')
                            .append(result.html_modifier);
                    }

                    //scroll bottom of items list
                    $(".pos_product_div").animate({ scrollTop: $('.pos_product_div').prop("scrollHeight") }, 1000);
                    var audio = $('#success-audio')[0];
                    if (audio !== undefined) {
                        audio.play();
                    }
                } else {
                    toastr.error(result.msg);
                    $('input#search_product')
                        .focus()
                        .select();
                }
            },
        });
    }

    var cat_arr = [];
    $("#category_id").html('');
    $('#pos_table tbody').find('tr').each(function() {
        var category_id = $(this).find('input.category-id').val();
        var category_text = $(this).find('span.category-text').text();

        if(cat_arr.includes(category_id) == false){
            cat_arr.push(category_id);
            $("#category_id").append( $('<option value='+category_id+'>'+category_text+'</option>'));
        }
    })
}


//Sort Table row
function sortTableRow(){
 var rows = $('#pos_table tbody  tr').get();
    rows.sort(function(a, b) {
        var A = getVal(a);
        var B = getVal(b);
        var result = A - B;
          if (result !== 0)
            return result;
    });

    function getVal(elm){
        var v = $(elm).attr('id')
        return v;
    }

    $.each(rows, function(index, row) {
        $('#pos_table').children('tbody').append(row);
    });
}
//Update values for each row
function pos_each_row(row_obj) {
    var unit_price = __read_number(row_obj.find('input.pos_unit_price'));

    var discounted_unit_price = calculate_discounted_unit_price(row_obj);
    var tax_rate = row_obj
        .find('select.tax_id')
        .find(':selected')
        .data('rate');

    var unit_price_inc_tax =
        discounted_unit_price + __calculate_amount('percentage', tax_rate, discounted_unit_price);
    // __write_number(row_obj.find('input.pos_unit_price_inc_tax'), unit_price_inc_tax);

    var discount = __read_number(row_obj.find('input.row_discount_amount'));

    if (discount > 0) {
        var qty = __read_number(row_obj.find('input.pos_quantity'));
        var line_total = qty * unit_price_inc_tax;
        __write_number(row_obj.find('input.pos_line_total'), line_total);
    }

    //var unit_price_inc_tax = __read_number(row_obj.find('input.pos_unit_price_inc_tax'));

    __write_number(row_obj.find('input.item_tax'), unit_price_inc_tax - discounted_unit_price);
}

function pos_total_row() {
    var total_quantity = 0;
    var price_total = get_total();
    var price_subtotal = get_subtotal();
    var tax_total = get_totaltax();
    $('table#pos_table tbody tr').each(function() {
        total_quantity = total_quantity + __read_number($(this).find('input.pos_quantity'));
    });

    //updating shipping charges
    $('span#shipping_charges_amount').text(
        __currency_trans_from_en(__read_number($('input#shipping_charges_modal')), false)
    );

    $('span.total_quantity').each(function() {
        $(this).html(total_quantity);
    });

    // console.log(tax_total,price_total,price_subtotal);
    //$('span.unit_price_total').html(unit_price_total);
    $('span.price_total').html(__currency_trans_from_en(price_total, false));
    $('span.price_subtotal').html(__currency_trans_from_en(price_subtotal, false));
    $('span.tax_total').html(__currency_trans_from_en(tax_total, false));
    $('#tax_total_amt').val(tax_total);
    calculate_billing_details(price_total);
}

function get_subtotal() {
    var price_total = 0;

    $('table#pos_table tbody tr').each(function() {
        price_total = price_total + __read_number($(this).find('input.pos_line_total'));
    });

    //Go through the modifier prices.
    $('input.modifiers_price').each(function() {
        var modifier_price = __read_number($(this));
        var modifier_quantity = $(this).closest('.product_modifier').find('.modifiers_quantity').val();
        var modifier_subtotal = modifier_price * modifier_quantity;
        price_total = price_total + modifier_subtotal;
    });

    return price_total;
}

function get_total() {
    var price_total = 0;

    $('table#pos_table tbody tr').each(function() {
        price_total = price_total + __read_number($(this).find('input.pos_line_totalamt_value'));
    });

    //Go through the modifier prices.
    $('input.modifiers_price').each(function() {
        var modifier_price = __read_number($(this));
        var modifier_quantity = $(this).closest('.product_modifier').find('.modifiers_quantity').val();
        var modifier_subtotal = modifier_price * modifier_quantity;
        price_total = price_total + modifier_subtotal;
    });
    return price_total;
}

function get_totaltax() {
    var tax_total = 0;
    var tax_total = 0;
    var tax_arr = [];
    var arr_tax = [];

    var countS = 0;
    var category_name = null;
    var single_tax_amount = 0;
    var html2 = '';
    var txid = 0;
    var same = [];
    $('table#pos_table tbody tr').each(function() {
        var obj = {};
        var obj1 = {};
        var ar = {};
        var tax_id = __read_number($(this).find('input.pos_line_tax_id'));
        var tax_amount =  __read_number($(this).find('input.pos_line_tax_amount'));
        var name =  ($(this).find('input.pos_line_tax_name').val());
        var city_tax_name =  ($(this).find('input.city_tax_name').val());
        var city_tax_id =  __read_number($(this).find('input.city_tax_id'));
        var city_tax_amount =  __read_number($(this).find('input.city_tax_value_amt'));
        var pos_quantity =  __read_number($(this).find('input.pos_quantity'));
        var category_name =  ($(this).find('input.category-name').val());
        var is_present = false;
        if(tax_arr.length > 0){
            for(var i=0; i<tax_arr.length; i++){
                if(tax_arr[i]['id'] ==  tax_id){
                    tax_arr[i]['tax'] = tax_arr[i]['tax'] + tax_amount;
                    is_present = true;
                }
                if(tax_arr[i]['id'] ==  city_tax_id  ){
                    tax_arr[i]['tax'] = tax_arr[i]['tax'] + city_tax_amount;
                    is_present = true;
                }
            }

            for(var p=0; p<arr_tax.length; p++){
                if(arr_tax[p]['tax_id'] ==  tax_id){
                    arr_tax[p]['city_tax_id'] = city_tax_id;
                    arr_tax[p]['countS'] = arr_tax[p]['countS'] + pos_quantity;
                    arr_tax[p]['single_tax_amount'] = arr_tax[p]['single_tax_amount'] + tax_amount + city_tax_amount;
                    arr_tax[p]['category_name'] = name + ' - '+ category_name;
                    is_present = true;
                }
            }
            if(is_present == false) {
                if(tax_amount>0){
                    obj['id'] = tax_id;
                    obj['tax'] = tax_amount;
                    obj['name'] = name;
                    tax_arr.push(obj);
                }
                if(city_tax_amount>0){
                    obj1['id'] = city_tax_id;
                    obj1['tax'] = city_tax_amount;
                    obj1['name'] = city_tax_name;
                    tax_arr.push(obj1);
                }

                if(tax_amount > 0 || city_tax_amount > 0)
                {
                    countS = pos_quantity;
                    category_taxname = name + ' - '+ category_name;
                    single_tax_amount = tax_amount + city_tax_amount;

                    ar['tax_id'] = tax_id;
                    ar['city_tax_id'] = city_tax_id;
                    ar['countS'] = pos_quantity;
                    ar['single_tax_amount'] = tax_amount + city_tax_amount;
                    ar['category_name'] = name + ' - '+ category_name;
                    arr_tax.push(ar);
                }
            }
        } else{
            if(tax_amount>0){
                obj['id'] = tax_id;
                obj['tax'] = tax_amount;
                obj['name'] = name;
                tax_arr.push(obj);
            }
            if(city_tax_amount>0){
                obj1['id'] = city_tax_id;
                obj1['tax'] = city_tax_amount;
                obj1['name'] = city_tax_name;
                tax_arr.push(obj1);
            }
            if(tax_amount > 0 || city_tax_amount > 0)
            {
                countS = pos_quantity;
                category_taxname = name + ' - '+ category_name;
                single_tax_amount = tax_amount + city_tax_amount;

                ar['tax_id'] = tax_id;
                ar['city_tax_id'] = city_tax_id;
                ar['countS'] = pos_quantity;
                ar['single_tax_amount'] = tax_amount + city_tax_amount;
                ar['category_name'] = name + ' - '+ category_name;
                arr_tax.push(ar);
            }
        }
        if(tax_amount > 0 || city_tax_amount > 0){
            // html2 += '<style > .popover{ max-width: 100%; } </style><tr><td scope="row">'+countS+'</td><td><span class="display_currency" data-currency_symbol="true">$ '+parseFloat(single_tax_amount).toFixed(2)+'</span> </td><td>'+category_taxname+'</td></tr>';
        }

        tax_total = tax_total + __read_number($(this).find('input.pos_line_tax_amount')) + parseFloat(city_tax_amount);

    });
    console.log('arr_tax:', arr_tax);
    console.log(tax_arr);
    var html = '<div>'
    for(var j=0; j<tax_arr.length; j++){
        if(tax_arr[j]['tax']!= 0){
            var id = tax_arr[j]['id'];
            var rule_name =  tax_arr[j]['name'];
            html+='<div class="tax-block"><p> '+ rule_name+'</p><p> '+ (tax_arr[j]['tax']).toFixed(2) +'</p></div>';
        }
    }
    html+='</div>';


    for(var l=0; l<arr_tax.length; l++){
        if(arr_tax[l]['tax']!= 0){
            html2 += '<style > .popover{ max-width: 100%; } </style><tr><td scope="row">'+arr_tax[l]['countS']+'</td><td><span class="display_currency" data-currency_symbol="true">$ '+parseFloat(arr_tax[l]['single_tax_amount']).toFixed(2)+'</span> </td><td>'+arr_tax[l]['category_name']+'</td></tr>';
        }
    }

    if (html2 == null || html2 == "")
    {
        // console.log('html2:', html2)
        html2 = '';
       $('#total_tax').attr('data-content', 'No Tax Added!');
    }else{
        var dat = "<div style='overflow-x:auto;'><table style='width: 360px;' class='table'><thead><tr><th scope='col'>Qty</th><th scope='col' style='width: 30%;'>Tax</th><th scope='col'>Tax Name & Category</th></thead><tbody id='tax_t'>"+html2+"</tbody></table></div>";
        $('#total_tax').attr('data-content', dat);
    }

    $("#tax_rule_details").html(html);
    return tax_total;
}
function calculate_billing_details(price_total) {
    var discount = pos_discount(price_total);
    if ($('#reward_point_enabled').length) {
        total_customer_reward = $('#rp_redeemed_amount').val();
        discount = parseFloat(discount) + parseFloat(total_customer_reward);

        if ($('input[name="is_direct_sale"]').length <= 0) {
            $('span#total_discount').text(__currency_trans_from_en(discount, false));
        }
    }

    var order_tax = pos_order_tax(price_total, discount);

    //Add shipping charges.
    var shipping_charges = __read_number($('input#shipping_charges'));

    //Add packaging charge
    var packing_charge = 0;
    if ($('#types_of_service_id').length > 0 &&
        $('#types_of_service_id').val()) {
        packing_charge = __calculate_amount($('#packing_charge_type').val(),
            __read_number($('input#packing_charge')), price_total);

        $('#packing_charge_text').text(__currency_trans_from_en(packing_charge, false));
    }

    var total_payable = price_total + order_tax - discount + shipping_charges + packing_charge;

    var rounding_multiple = $('#amount_rounding_method').val() ? parseFloat($('#amount_rounding_method').val()) : 0;
    var round_off_data = __round(total_payable, rounding_multiple);
    var total_payable_rounded = round_off_data.number;

    var round_off_amount = round_off_data.diff;
    if (round_off_amount != 0) {
        $('span#round_off_text').text(__currency_trans_from_en(round_off_amount, false));
    } else {
        $('span#round_off_text').text(0);
    }
    $('input#round_off_amount').val(round_off_amount);

    __write_number($('input#final_total_input'), total_payable_rounded);
    var curr_exchange_rate = 1;
    if ($('#exchange_rate').length > 0 && $('#exchange_rate').val()) {
        curr_exchange_rate = __read_number($('#exchange_rate'));
    }
    var shown_total = total_payable_rounded * curr_exchange_rate;
    $('span#total_payable').text(__currency_trans_from_en(shown_total, false));

    $('span.total_payable_span').text(__currency_trans_from_en(total_payable_rounded, true));

    //Check if edit form then don't update price.
    if ($('form#edit_pos_sell_form').length == 0) {
        __write_number($('.payment-amount').first(), total_payable_rounded);
    }

    $(document).trigger('invoice_total_calculated');

    calculate_balance_due();
}

function pos_discount(total_amount) {
    var calculation_type = $('#discount_type').val();
    var calculation_amount = __read_number($('#discount_amount'));

    var discount = __calculate_amount(calculation_type, calculation_amount, total_amount);

    $('span#total_discount').text(__currency_trans_from_en(discount, false));

    return discount;
}

function pos_order_tax(price_total, discount) {
    var tax_rate_id = $('#tax_rate_id').val();
    var calculation_type = 'percentage';
    var calculation_amount = __read_number($('#tax_calculation_amount'));
    var total_amount = price_total - discount;

    if (tax_rate_id) {
        var order_tax = __calculate_amount(calculation_type, calculation_amount, total_amount);
    } else {
        var order_tax = 0;
    }

    $('span#order_tax').text(__currency_trans_from_en(order_tax, false));

    return order_tax;
}

function calculate_balance_due() {
    var total_payable = __read_number($('#final_total_input'));
    var total_paying = 0;
    $('#payment_rows_div')
        .find('.payment-amount')
        .each(function() {
            if (parseFloat($(this).val())) {
                total_paying += __read_number($(this));
            }
        });
    var bal_due = total_payable - total_paying;
    var change_return = 0;

    //change_return
    if (bal_due < 0 || Math.abs(bal_due) < 0.05) {
        __write_number($('input#change_return'), bal_due * -1);
        $('span.change_return_span').text(__currency_trans_from_en(bal_due * -1, true));
        change_return = bal_due * -1;
        bal_due = 0;
    } else {
        __write_number($('input#change_return'), 0);
        $('span.change_return_span').text(__currency_trans_from_en(0, true));
        change_return = 0;
    }

    __write_number($('input#total_paying_input'), total_paying);
    $('span.total_paying').text(__currency_trans_from_en(total_paying, true));

    __write_number($('input#in_balance_due'), bal_due);
    $('span.balance_due').text(__currency_trans_from_en(bal_due, true));

    __highlight(bal_due * -1, $('span.balance_due'));
    __highlight(change_return * -1, $('span.change_return_span'));
}

function isValidPosForm() {
    flag = true;
    $('span.error').remove();

    if ($('select#customer_id').val() == null) {
        // console.log('test')
        flag = false;
        error = '<span class="error">' + LANG.required + '</span>';
        $(error).insertAfter($('select#customer_id').parent('div'));
    }

    if ($('tr.product_row').length == 0) {
        flag = false;
        error = '<span class="error">' + LANG.no_products + '</span>';
        $(error).insertAfter($('input#search_product').parent('div'));
    }

    return flag;
}

function reset_pos_form() {

    //If on edit page then redirect to Add POS page
    if ($('form#edit_pos_sell_form').length > 0) {
        setTimeout(function() {
            window.location = $("input#pos_redirect_url").val();
        }, 4000);
        return true;
    }

    if (pos_form_obj[0]) {
        pos_form_obj[0].reset();
    }
    if (sell_form[0]) {
        sell_form[0].reset();
    }
    set_default_customer();
    set_location();

    $('tr.product_row').remove();
    $('span.total_quantity, span.price_total, span#total_discount, span#order_tax, span#total_payable, span#shipping_charges_amount, span.price_subtotal, span.tax_total').text(0);
    $('span.total_payable_span', 'span.total_paying', 'span.balance_due').text(0);

    //reset address
    $(".addresses").html('');
    $("#tax_rule_details").html('');

    $('#modal_payment').find('.remove_payment_row').each(function() {
        $(this).closest('.payment_row').remove();
    });

    if ($('#is_credit_sale').length) {
        $('#is_credit_sale').val(0);
    }

    //Reset discount
    __write_number($('input#discount_amount'), $('input#discount_amount').data('default'));
    $('input#discount_type').val($('input#discount_type').data('default'));

    //Reset tax rate
    $('input#tax_rate_id').val($('input#tax_rate_id').data('default'));
    __write_number($('input#tax_calculation_amount'), $('input#tax_calculation_amount').data('default'));

    $('select.payment_types_dropdown').val('cash').trigger('change');
    $('#price_group').trigger('change');

    //Reset shipping
    __write_number($('input#shipping_charges'), $('input#shipping_charges').data('default'));
    $('input#shipping_details').val($('input#shipping_details').data('default'));

    if ($('input#is_recurring').length > 0) {
        $('input#is_recurring').iCheck('update');
    };
    if ($('#invoice_layout_id').length > 0) {
        $('#invoice_layout_id').trigger('change');
    };
    $('span#round_off_text').text(0);

    $(document).trigger('sell_form_reset');

    //reset delivery method
    $('div.pos-form-totals').find('button').removeClass('highlight-btn-borders');
}

function set_default_customer() {
    var default_customer_id = $('#default_customer_id').val();
    var default_customer_name = $('#default_customer_name').val();
    var default_customer_balance = $('#default_customer_balance').val();
    var exists = default_customer_id ? $('select#customer_id option[value=' + default_customer_id + ']').length : 0;
    if (exists == 0 && default_customer_id) {
        $('select#customer_id').append(
            $('<option>', { value: default_customer_id, text: default_customer_name })
        );
    }
    $('#advance_balance_text').text(__currency_trans_from_en(default_customer_balance), true);
    $('#advance_balance').val(default_customer_balance);

    $('select#customer_id')
        .val(default_customer_id)
        .trigger('change');

    customer_set = true;
}

//Set the location and initialize printer
function set_location() {
    if ($('select#select_location_id').length == 1) {
        $('input#location_id').val($('select#select_location_id').val());
        $('input#location_id').data(
            'receipt_printer_type',
            $('select#select_location_id')
            .find(':selected')
            .data('receipt_printer_type')
        );
        $('input#location_id').data(
            'default_payment_accounts',
            $('select#select_location_id')
            .find(':selected')
            .data('default_payment_accounts')
        );

        $('input#location_id').attr(
            'data-default_price_group',
            $('select#select_location_id')
            .find(':selected')
            .data('default_price_group')
        );
    }

    if ($('input#location_id').val()) {
        $('input#search_product')
            .prop('disabled', false)
            .focus();
    } else {
        $('input#search_product').prop('disabled', true);
    }

    initialize_printer();
}

function initialize_printer() {
    if ($('input#location_id').data('receipt_printer_type') == 'printer') {
        initializeSocket();
    }
}

$('body').on('click', 'label', function(e) {
    var field_id = $(this).attr('for');
    if (field_id) {
        if ($('#' + field_id).hasClass('select2')) {
            $('#' + field_id).select2('open');
            return false;
        }
    }
});

$('body').on('focus', 'select', function(e) {
    var field_id = $(this).attr('id');
    if (field_id) {
        if ($('#' + field_id).hasClass('select2')) {
            $('#' + field_id).select2('open');
            return false;
        }
    }
});

function round_row_to_iraqi_dinnar(row) {
    if (iraqi_selling_price_adjustment) {
        var element = row.find('input.pos_unit_price_inc_tax');
        var unit_price = round_to_iraqi_dinnar(__read_number(element));
        __write_number(element, unit_price);
        element.change();
    }
}

function pos_print(receipt) {
    //If printer type then connect with websocket
    if (receipt.print_type == 'printer') {
        var content = receipt;
        content.type = 'print-receipt';

        //Check if ready or not, then print.
        if (socket != null && socket.readyState == 1) {
            socket.send(JSON.stringify(content));
        } else {
            initializeSocket();
            setTimeout(function() {
                socket.send(JSON.stringify(content));
            }, 700);
        }

    } else if (receipt.html_content != '') {
        //If printer type browser then print content
        $('#receipt_section').html(receipt.html_content);
        __currency_convert_recursively($('#receipt_section'));
        __print_receipt('receipt_section');
    }
}

function calculate_discounted_unit_price(row) {
    var this_unit_price = __read_number(row.find('input.pos_unit_price'));
    var row_discounted_unit_price = this_unit_price;
    var row_discount_type = row.find('select.row_discount_type').val();
    var row_discount_amount = __read_number(row.find('input.row_discount_amount'));
    if (row_discount_amount) {
        if (row_discount_type == 'fixed') {
            row_discounted_unit_price = this_unit_price - row_discount_amount;
        } else {
            row_discounted_unit_price = __substract_percent(this_unit_price, row_discount_amount);
        }
    }

    return row_discounted_unit_price;
}

function get_unit_price_from_discounted_unit_price(row, discounted_unit_price) {
    var this_unit_price = discounted_unit_price;
    var row_discount_type = row.find('select.row_discount_type').val();
    var row_discount_amount = __read_number(row.find('input.row_discount_amount'));
    if (row_discount_amount) {
        if (row_discount_type == 'fixed') {
            this_unit_price = discounted_unit_price + row_discount_amount;
        } else {
            this_unit_price = __get_principle(discounted_unit_price, row_discount_amount, true);
        }
    }

    return this_unit_price;
}

//Update quantity if line subtotal changes
$('table#pos_table tbody').on('change', 'input.pos_line_total', function() {
    var subtotal = __read_number($(this));
    var tr = $(this).parents('tr');
    var quantity_element = tr.find('input.pos_quantity');
    var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));
    var quantity = subtotal / unit_price_inc_tax;
    __write_number(quantity_element, quantity);

    if (sell_form_validator) {
        sell_form_validator.element(quantity_element);
    }
    if (pos_form_validator) {
        pos_form_validator.element(quantity_element);
    }
    tr.find('span.pos_line_total_text').text(__currency_trans_from_en(subtotal, true));

    pos_total_row();
});

$('div#product_list_body').on('scroll', function() {
    if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
        var page = parseInt($('#suggestion_page').val());
        page += 1;
        $('#suggestion_page').val(page);
        var location_id = $('input#location_id').val();
        var category_id = $('select#product_category').val();
        var brand_id = $('select#product_brand').val();

        var is_enabled_stock = null;
        if ($("#is_enabled_stock").length) {
            is_enabled_stock = $("#is_enabled_stock").val();
        }

        var device_model_id = null;
        if ($("#repair_model_id").length) {
            device_model_id = $("#repair_model_id").val();
        }

        get_product_suggestion_list(category_id, brand_id, location_id, null, is_enabled_stock, device_model_id);
    }
});

$(document).on('ifChecked', '#is_recurring', function() {
    $('#recurringInvoiceModal').modal('show');
});

$(document).on('shown.bs.modal', '#recurringInvoiceModal', function() {
    $('input#recur_interval').focus();
});

$(document).on('click', '#select_all_service_staff', function() {
    var val = $('#res_waiter_id').val();
    $('#pos_table tbody')
        .find('select.order_line_service_staff')
        .each(function() {
            $(this)
                .val(val)
                .change();
        });
});

$(document).on('click', '.print-invoice-link', function(e) {
    e.preventDefault();
    $.ajax({
        url: $(this).attr('href') + "?check_location=true",
        dataType: 'json',
        success: function(result) {
            if (result.success == 1) {
                //Check if enabled or not
                if (result.receipt.is_enabled) {
                    pos_print(result.receipt);
                }
            } else {
                toastr.error(result.msg);
            }

        },
    });
});

function getCustomerRewardPoints() {
    if ($('#reward_point_enabled').length <= 0) {
        return false;
    }
    var is_edit = $('form#edit_sell_form').length ||
        $('form#edit_pos_sell_form').length ? true : false;
    if (is_edit && !customer_set) {
        return false;
    }

    var customer_id = $('#customer_id').val();

    $.ajax({
        method: 'POST',
        url: '/sells/pos/get-reward-details',
        data: {
            customer_id: customer_id
        },
        dataType: 'json',
        success: function(result) {
            $('#available_rp').text(result.points);
            $('#rp_redeemed_modal').data('max_points', result.points);
            updateRedeemedAmount();
            $('#rp_redeemed_amount').change()
        },
    });
}

function updateRedeemedAmount(argument) {
    var points = $('#rp_redeemed_modal').val().trim();
    points = points == '' ? 0 : parseInt(points);
    var amount_per_unit_point = parseFloat($('#rp_redeemed_modal').data('amount_per_unit_point'));
    var redeemed_amount = points * amount_per_unit_point;
    $('#rp_redeemed_amount_text').text(__currency_trans_from_en(redeemed_amount, true));
    $('#rp_redeemed').val(points);
    $('#rp_redeemed_amount').val(redeemed_amount);
}

$(document).on('change', 'select#customer_id', function() {
    var default_customer_id = $('#default_customer_id').val();
    $('button#posShippingModalUpdateSelf').attr('disabled', false);
    $('button#posShippingModalUpdateDelivery').attr('disabled', false);
    $('button#posShippingModalUpdatePickup').attr('disabled', false);
    $('button#posShippingModalUpdateShipping').attr('disabled', false);
    $('input#shipping_details').val("");
    $('input#shipping_address').val("");
    $('input#shipping_status').val("");
    if ($(this).val() == default_customer_id) {
        //Disable reward points for walkin customers
        if ($('#rp_redeemed_modal').length) {
            $('#rp_redeemed_modal').val('');
            $('#rp_redeemed_modal').change();
            $('#rp_redeemed_modal').attr('disabled', true);
            $('#available_rp').text('');
            updateRedeemedAmount();
            pos_total_row();
        }
    } else {
        if ($('#rp_redeemed_modal').length) {
            $('#rp_redeemed_modal').removeAttr('disabled');
        }
        getCustomerRewardPoints();
    }
});


$(document).on('change', '#rp_redeemed_modal', function() {
    var points = $(this).val().trim();
    points = points == '' ? 0 : parseInt(points);
    var amount_per_unit_point = parseFloat($(this).data('amount_per_unit_point'));
    var redeemed_amount = points * amount_per_unit_point;
    $('#rp_redeemed_amount_text').text(__currency_trans_from_en(redeemed_amount, true));
    var reward_validation = isValidatRewardPoint();
    if (!reward_validation['is_valid']) {
        toastr.error(reward_validation['msg']);
        $('#rp_redeemed_modal').select();
    }
});

$(document).on('change', '.direct_sell_rp_input', function() {
    updateRedeemedAmount();
    pos_total_row();
});

function isValidatRewardPoint() {
    var element = $('#rp_redeemed_modal');
    var points = element.val().trim();
    points = points == '' ? 0 : parseInt(points);

    var max_points = parseInt(element.data('max_points'));
    var is_valid = true;
    var msg = '';

    if (points == 0) {
        return {
            is_valid: is_valid,
            msg: msg
        }
    }

    var rp_name = $('input#rp_name').val();
    if (points > max_points) {
        is_valid = false;
        msg = __translate('max_rp_reached_error', { max_points: max_points, rp_name: rp_name });
    }

    var min_order_total_required = parseFloat(element.data('min_order_total'));

    var order_total = __read_number($('#final_total_input'));

    if (order_total < min_order_total_required) {
        is_valid = false;
        msg = __translate('min_order_total_error', { min_order: __currency_trans_from_en(min_order_total_required, true), rp_name: rp_name });
    }

    var output = {
        is_valid: is_valid,
        msg: msg,
    }

    return output;
}

function adjustComboQty(tr) {
    if (tr.find('input.product_type').val() == 'combo') {
        var qty = __read_number(tr.find('input.pos_quantity'));
        var multiplier = __getUnitMultiplier(tr);

        tr.find('input.combo_product_qty').each(function() {
            $(this).val($(this).data('unit_quantity') * qty * multiplier);
        });
    }
}

$(document).on('change', '#types_of_service_id', function() {
    var types_of_service_id = $(this).val();
    var location_id = $('#location_id').val();

    if (types_of_service_id) {
        $.ajax({
            method: 'POST',
            url: '/sells/pos/get-types-of-service-details',
            data: {
                types_of_service_id: types_of_service_id,
                location_id: location_id
            },
            dataType: 'json',
            success: function(result) {
                //reset form if price group is changed
                var prev_price_group = $('#types_of_service_price_group').val();
                if (result.price_group_id) {
                    $('#types_of_service_price_group').val(result.price_group_id);
                    $('#price_group_text').removeClass('hide');
                    $('#price_group_text span').text(result.price_group_name);
                } else {
                    $('#types_of_service_price_group').val('');
                    $('#price_group_text').addClass('hide');
                    $('#price_group_text span').text('');
                }
                $('#types_of_service_id').val(types_of_service_id);
                $('.types_of_service_modal').html(result.modal_html);

                if (prev_price_group != result.price_group_id) {
                    if ($('form#edit_pos_sell_form').length > 0) {
                        $('table#pos_table tbody').html('');
                        pos_total_row();
                    } else {
                        reset_pos_form();
                    }
                } else {
                    pos_total_row();
                }

                $('.types_of_service_modal').modal('show');
            },
        });
    } else {
        $('.types_of_service_modal').html('');
        $('#types_of_service_price_group').val('');
        $('#price_group_text').addClass('hide');
        $('#price_group_text span').text('');
        $('#packing_charge_text').text('');
        if ($('form#edit_pos_sell_form').length > 0) {
            $('table#pos_table tbody').html('');
            pos_total_row();
        } else {
            reset_pos_form();
        }
    }
});

$(document).on('change', 'input#packing_charge', function() {
    pos_total_row();
});

$(document).ready(function() {
    $('#show_featured_products').click();
});

$(document).on('click', '.service_modal_btn', function(e) {
    if ($('#types_of_service_id').val()) {
        $('.types_of_service_modal').modal('show');
    }
});

$(document).on('change', '.payment_types_dropdown', function(e) {
    var default_accounts = $('select#select_location_id').length ?
        $('select#select_location_id')
        .find(':selected')
        .data('default_payment_accounts') : $('#location_id').data('default_payment_accounts');
    var payment_type = $(this).val();
    var payment_row = $(this).closest('.payment_row');
    if (payment_type && payment_type != 'advance') {
        var default_account = default_accounts && default_accounts[payment_type]['account'] ?
            default_accounts[payment_type]['account'] : '';
        var row_index = payment_row.find('.payment_row_index').val();

        var account_dropdown = payment_row.find('select#account_' + row_index);
        if (account_dropdown.length && default_accounts) {
            account_dropdown.val(default_account);
            account_dropdown.change();
        }
    }

    //Validate max amount and disable account if advance
    amount_element = payment_row.find('.payment-amount');
    account_dropdown = payment_row.find('.account-dropdown');
    if (payment_type == 'advance') {
        max_value = $('#advance_balance').val();
        msg = $('#advance_balance').data('error-msg');
        amount_element.rules('add', {
            'max-value': max_value,
            messages: {
                'max-value': msg,
            },
        });
        if (account_dropdown) {
            account_dropdown.prop('disabled', true);
            account_dropdown.closest('.form-group').addClass('hide');
        }
    } else {
        amount_element.rules("remove", "max-value");
        if (account_dropdown) {
            account_dropdown.prop('disabled', false);
            account_dropdown.closest('.form-group').removeClass('hide');
        }
    }
});

$(document).on('show.bs.modal', '#recent_transactions_modal', function() {
    get_recent_transactions('final', $('div#tab_final'));
});

$(document).on('show.bs.modal', '#customer_recent_transactions_modal', function() {
    get_customer_recent_transactions('final', $('div#tab_final'));
});
$(document).on('shown.bs.tab', 'a[href="#tab_quotation"]', function() {
    get_recent_transactions('quotation', $('div#tab_quotation'));
});
$(document).on('shown.bs.tab', 'a[href="#tab_draft"]', function() {
    get_recent_transactions('draft', $('div#tab_draft'));
});

function disable_pos_form_actions() {
    $('div.pos-processing').show();
    $('#pos-save').attr('disabled', 'true');
    $('#finalize_no_print').attr('disabled', 'true');
    $('div.pos-form-actions').find('button').attr('disabled', 'true');
}

function enable_pos_form_actions() {
    $('div.pos-processing').hide();
    $('#pos-save').removeAttr('disabled');
    $('#finalize_no_print').removeAttr('disabled');
    $('div.pos-form-actions').find('button').removeAttr('disabled');
}

$(document).on('change', '#recur_interval_type', function() {
    if ($(this).val() == 'months') {
        $('.subscription_repeat_on_div').removeClass('hide');
    } else {
        $('.subscription_repeat_on_div').addClass('hide');
    }
});

function validate_discount_field() {
    discount_element = $('#discount_amount_modal');
    discount_type_element = $('#discount_type_modal');

    if ($('#add_sell_form').length || $('#edit_sell_form').length) {
        discount_element = $('#discount_amount');
        discount_type_element = $('#discount_type');
    }
    var max_value = parseFloat(discount_element.data('max-discount'));
    if (discount_element.val() != '' && !isNaN(max_value)) {
        if (discount_type_element.val() == 'fixed') {
            var subtotal = get_subtotal();
            //get max discount amount
            max_value = __calculate_amount('percentage', max_value, subtotal)
        }

        discount_element.rules('add', {
            'max-value': max_value,
            messages: {
                'max-value': discount_element.data('max-discount-error_msg'),
            },
        });
    } else {
        discount_element.rules("remove", "max-value");
    }
    discount_element.trigger('change');
}

$(document).on('change', '#discount_type_modal, #discount_type', function() {
    validate_discount_field();
});

$(document).on('click', '#calculateTax', function() {
    if(!$('#customer_state').val().length)
    {
        toastr.error("State should not be null value!!");
        return false;
    }
    $("#calculateTax").attr('disabled','disabled');
    let customer_id = $('#customer_id').val();
    let tax_applicable = $('#tax_applicable').val();
    if(tax_applicable == 0) tax_applicable = 1;
    else tax_applicable = 0;
    if (tax_applicable == 0) {
        $('#tax_deal').val('off');
    } else {
        $('#tax_deal').val('on');
    }
    $('#tax_applicable').val(tax_applicable);
        var i = 1;
        var rowCount = $('#pos_table tr').length;
        $('table#pos_table tbody tr').each(function() {
            var variation_id =  $(this).find('input.row_variation_id').val();
            var product_id =  $(this).find('input.product_id').val();
            var pos_unit_price_inc_tax =  $(this).find('input.pos_unit_price_inc_tax').val();
            var quantity = __read_number($(this).find('input.pos_quantity'));
            var qty_box = __read_number($(this).find('input.qty_box'));
            var product_ml = __read_number($(this).find('input.product_ml'));
            var new_qty = quantity;
            if(qty_box > 1) var new_qty = quantity * qty_box;
            var total = __read_number($(this).find('input.pos_line_total'));
            var tr = $(this);

            $.ajax({
                method: 'GET',
                url: '/sells/pos/product-tax',
                data: {customer_id:customer_id, product_id:product_id, variation_id:variation_id, selling_price:pos_unit_price_inc_tax},
                dataType: 'json',
                success: function(result) {
                    var new_total = total;
                    var tax_single = 0;
                    var every_item =  0;
                    var total_tax = 0;
                    var tax_type = 0;
                    var tax_name = 0;
                    var tax_id = 0;
                    if(result.tax>0 && tax_applicable==1){
                        tax_single = result.tax;
                        state =  result.state;
                        every_item =  result.every_item;
                        tax_type =  result.tax_type;
                        tax_name =  result.name;
                        tax_id = result.rule;
                        //State tax calculation
                            if(result.rule == 55 || result.rule == 58 || result.rule ==  59 || result.rule ==  61 || result.rule ==  62){
                                total_tax = parseFloat(tax_single) * parseFloat(quantity);
                            }
                            else if(every_item > 1){
                                var times_of_apply_tax = parseInt(parseFloat(new_qty)/every_item);
                                total_tax = parseFloat(tax_single) * times_of_apply_tax;
                            } else {
                                if(tax_type == 1) total_tax = parseFloat(tax_single) * parseFloat(new_qty);
                                if(tax_type == 2) total_tax = parseFloat(tax_single) * parseFloat(quantity);
                            }

                            if(result.is_ml == 1){
                                tax_single = parseFloat(tax_single) * parseFloat(product_ml);
                                total_tax = parseFloat(tax_single) * parseFloat(quantity);
                            }

                            new_total = parseFloat(total) + parseFloat(total_tax);
                        // tr.find('span.pos_line_totalamt_text').text(__currency_trans_from_en(new_total,true));
                    }
                        tr.find('input.pos_line_tax_id').val(tax_id);
                        tr.find('input.pos_line_tax_name').val(tax_name);
                        tr.find('input.pos_line_tax_every_item').val(every_item);
                        tr.find('input.pos_line_tax').val(tax_single);
                        tr.find('input.pos_line_tax_ml').val(result.is_ml);
                        tr.find('input.pos_line_tax_amount').val(total_tax);
                        tr.find('input.pos_line_tax_type').val(tax_type);
                    //City tax calculation
                    var city_tax_amt = 0;
                    var city_tax_id = 0;
                    var city_tax_name = 0;
                    var first_item_value = 0;
                    var second_item_value = 0;
                    var city_tax = 0;
                    var city_every_item = 0;
                    var city_tax_type = 0;
                    if(result.city_tax_id != 0 && tax_applicable == 1){
                        city_tax_id = result.city_tax_id;
                        city_every_item =  result.city_every_item;
                        city_tax = result.city_tax;
                        first_item_value = result.first_item_value;
                        second_item_value = result.second_item_value;
                        city_tax_name = result.city_tax_name;
                        city_tax_type =  result.city_tax_type;

                        if( city_tax != 0 ){
                            if(city_every_item > 1) {
                                var times_of_apply_tax = parseInt(parseFloat(new_qty)/city_every_item);
                                city_tax_amt = times_of_apply_tax * city_tax ;
                            } else{
                                if(city_tax_type == 1)  city_tax_amt = new_qty * city_tax ;
                                if(city_tax_type == 2)  city_tax_amt = quantity * city_tax ;
                            }
                        } else {
                            if(city_tax_type == 1) var second_applicable_qty = new_qty - quantity;
                            if(city_tax_type == 2) var second_applicable_qty = 0;
                            city_tax_amt = parseFloat(first_item_value*quantity) + parseFloat(second_item_value * second_applicable_qty);
                        }
                    }
                    tr.find('input.city_tax_id').val(city_tax_id);
                    tr.find('input.city_tax_name').val(city_tax_name);
                    tr.find('input.city_tax_type').val(city_tax_type);
                    tr.find('input.city_tax_value').val(city_tax);
                    tr.find('input.city_tax_value_amt').val(city_tax_amt);
                    tr.find('input.first_item_value_value').val(first_item_value);
                    tr.find('input.second_item_value_value').val(second_item_value);
                    tr.find('input.city_every_item_value').val(city_every_item);
                    tr.find('span.pos_line_city_tax_text').text(__currency_trans_from_en(city_tax_amt, true));
                    new_total = parseFloat(new_total) + parseFloat(city_tax_amt);
                    tr.find('input.pos_line_totalamt_value').val(new_total);
                    tr.find('span.pos_line_tax_text').text(__currency_trans_from_en(total_tax, true));

                    pos_total_row();
                    calculate_balance_due();
                },
            });

           i++;
           if(rowCount == i) {
               let delay = 5000;
                 if(tax_applicable == 1) delay =  rowCount*200  ;
                 if(tax_applicable == 0) delay =  rowCount*100  ;
                 setTimeout(function() {
                //    pos_total_row();
                   $("#calculateTax").removeAttr('disabled');
                   if(tax_applicable == 1) toastr.success("Tax Added!");
                   if(tax_applicable == 0) toastr.success("Tax Removed!");
                 }, delay);
           }

        //   if(rowCount == i) {
         //   setTimeout(function() {
          //     pos_total_row();
        //       $("#calculateTax").removeAttr('disabled');
         //     if(tax_applicable == 1) toastr.success("Tax Added!");
          //    if(tax_applicable == 0) toastr.success("Tax Removed!");
        //    }, 2000);
         //  }

        });


    // let $cells = $("#pos_table tr td"),
    //     $inputs = $cells.find('.productID');
    // let product_ids = [];
    // $.each($inputs, function() {
    //     product_ids.push(this.value);
    // });

    // $taxes = $cells.find('.pos_tax_value');
    // var tax = 0;
    // $.each($taxes, function() {
    //     tax += Number($(this).val());;
    // });

    // $city_taxes = $cells.find('.city_tax_value');
    // var city_tax = 0;
    // $.each($city_taxes, function() {
    //     city_tax += Number($(this).val());;
    // });
    // var total = tax + city_tax;
    // $('#cityTax').text(city_tax);
    // $('#stateTax').text(tax);
    // $('#order_tax').text(total);


    // if (customer_id === "") {
    //     toastr.error("Customer Not Selected!");
    // } else {
    //     $.ajax({
    //         url: '/sells/pos/calculate-customer-tax',
    //         type: 'GET',
    //         data: {
    //             customer_id: customer_id,
    //             product_ids: product_ids
    //         },
    //         success: function(tax) {
    //             $('#cityTax').text(tax.cityTax);
    //             $('#stateTax').text(tax.stateTax);
    //             $('#order_tax').text(tax.totalTax);
    //             toastr.success("Tax Calculated!");
    //         }
    //     });
    // }
});
$(document).on("keyup", "#search_product_pos_one", function() {
    const pattern = /^\d+$/;
    let term_replace = $('#search_product_pos_one').val();
    if(term_replace.length>=10 && pattern.test(term_replace))
    {
        $('#search_product_pos_one').val('');
        $("#open-datatable").hide();
        $("#open-datatable-one").hide();
        $('#search_product').val(term_replace).autocomplete('search').focus();
        return false;
    }
    else
    {
        $("#open-datatable").show();
        $("#open-datatable-one").show();
        //$('#open-datatable').DataTable().destroy();
        let table = $('#open-datatable');
        let location_id = $('#location_id').val();
        let term = $('#search_product_pos_one').val();
        let data_id = $('#filter_by_lead_status').val();

        var price_group = '';
        var search_fields = [];
        $('.search_fields:checked').each(function(i) {
            search_fields[i] = $(this).val();
        });

        search_fields.push("item_code");

        if ($('#price_group').length > 0) {
            price_group = $('#price_group').val();
        }

        table.DataTable({
            destroy:true,
            serverSide: false,
            autoWidth: false,
            //responsive: true,

            processing: true,
            searching: true,
            lengthChange: false,
            iDisplayLength: 70,
            //paging: false,
            order: [],
            // aaSorting: [[0, 'asc']],
            buttons: [],

            ajax: {
                url: "/products/listone?location_id=" + location_id + "&term=" + term+ "&not_for_selling=" + 0 + "&price_group=" + price_group+ "&search_fields=" + search_fields,
                type: 'GET',
                data:  {search_fields: search_fields},
                // dataSrc: function(json) {
                //     if (json.data.length === 0) {
                //         toastr.error(LANG.no_products_found);
                //     }
                //     return json.data;
                // }
            },
            "initComplete": function(data) {
                term = $("#search_product_pos_one").val();
                if((term.length >= 9 && term.length <= 14) && !isNaN(term) && data.aoData.length == 1){
                $(".product-link").click();
                $("#search_product_pos_one").val('');
                $("#open-datatable-one").hide();
                }
            },
            columns: [
                { data: "qty_available", name: 'VLD.qty_available' },
                { data: "name", name: 'products.name', },
                { data: "item_code", name: 'products.item_code'},
                { data: "default_sell_price", name: 'variations.default_sell_price' },
            ]
        });
    }
});

//added by developer 1
$(document).on('click', ".paginate_button", function() {
    return false;
});
//added by developer 1

$(document).on("blur", ".note-text", function() {
    $(this).closest('td').prev('td').find('span').remove();
    $(this).closest('td').prev('td').append('<span>'+$(this).val()+'</span>');
});

$(document).on("click", "#text-button", function() {
    $(".product-link").click();
});

$(document).on("click", "#hide-column", function() {
    var hideColumn = $("#hideColumn").val();
    if(hideColumn == 0) hideColumn=1;
    else hideColumn = 0;
    document.cookie = "hideColumn = "+hideColumn+"; path=/;"
    $("#hideColumn").val(hideColumn);
    $(".cost").toggle();
    $(".gross-price").toggle();
});

$(".packed_method").click(function(){
    alert("Product Already Packed!");
});

function setMyCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    let expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getMyCookie(cname) {
    let name = cname + "=";
    let ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "false";
}