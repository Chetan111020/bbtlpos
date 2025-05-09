$(document).ready(function() {
    // event.preventDefault();
    //For edit pos form
    $("#taxFlag").val(0);
    if ($('form#sell_return_form').length > 0) {
        pos_form_obj = $('form#sell_return_form');
    } else {
        pos_form_obj = $('form#add_pos_sell_form');
    }
    if ($('form#sell_return_form').length > 0 || $('form#add_pos_sell_form').length > 0) {
        initialize_printer();
    }

    //Date picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    let buttonName = "";
    $('.btn').each(function () {
        $(this).on('click', function () {
            if ($(this).attr('value') == "remove_tax") {
                buttonName = "remove_tax";
            } else {
                buttonName = 'save';
            }
        });
    });

    var removeTaxClick = 0;
    if($('#netFinalTax').length !=0){
        ($("#netFinalTax").val() == 0 || $("#netFinalTax").val() == '') ? $("#taxFlag").val(0) : $("#taxFlag").val(1);
    }


    pos_form_validator = pos_form_obj.validate({
        submitHandler: function(form) {

            var cnf = true;
            if (cnf) {
                if(buttonName === 'remove_tax'){

                    if(removeTaxClick==1){
                        //remove tax
                        var totalAmount = parseFloat($('#netReturnAmount').val()) - parseFloat($('#netTax').val());
                        $('span#net_return').text(__currency_trans_from_en(totalAmount, true));
                        $(".return_tax_hideshow").hide();
                        $("#taxFlag").val(0);
                        removeTaxClick = 0;
                        toastr.success("Tax Removed Successfully!");
                    }else{
                        //add tax
                        var totalAmount = parseFloat($('#netReturnAmount').val());
                        $('span#net_return').text(__currency_trans_from_en(totalAmount, true));
                        $('span#total_return_tax').text(__currency_trans_from_en(parseFloat($('#netTax').val()), true));
                        $('.return_tax_hideshow').show();
                        $("#taxFlag").val(1);
                        removeTaxClick=1;
                        toastr.success("Tax Added Successfully!");
                    }
                }else{
                    var data = $(form).serialize();
                    var url = $(form).attr('action');
                    $.ajax({
                        method: 'POST',
                        url: url,
                        data: data,
                        dataType: 'json',
                        success: function(result) {
                            if (result.success == 1) {
                                toastr.success(result.msg);
                                if (result.receipt) {
                                    window.location = "/sells-return";
                                }else{
                                    window.location = "/contacts/" + $('#getCustomer').val();
                                }
                                //Check if enabled or not
                                // if (result.receipt.is_enabled) {
                                //     pos_print(result.receipt);
                                // }
                                //window.location = "/sell-return";
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            }
            return false;
        },
    });
});

function initialize_printer() {
    if ($('input#location_id').data('receipt_printer_type') == 'printer') {
        initializeSocket();
    }
}

function pos_print(receipt) {
    //If printer type then connect with websocket
    if (receipt.print_type == 'printer') {
        var content = receipt;
        content.type = 'print-receipt';

        //Check if ready or not, then print.
        if (socket.readyState != 1) {
            initializeSocket();
            setTimeout(function() {
                socket.send(JSON.stringify(content));
            }, 700);
        } else {
            socket.send(JSON.stringify(content));
        }
    } else if (receipt.html_content != '') {
        //If printer type browser then print content
        $('#receipt_section').html(receipt.html_content);
        __currency_convert_recursively($('#receipt_section'));
        setTimeout(function() {
            window.print();
        }, 1000);
    }
}

// //Set the location and initialize printer
// function set_location(){
// 	if($('input#location_id').length == 1){
// 	       $('input#location_id').val($('select#select_location_id').val());
// 	       //$('input#location_id').data('receipt_printer_type', $('select#select_location_id').find(':selected').data('receipt_printer_ty
// 	}

// 	if($('input#location_id').val()){
// 	       $('input#search_product').prop( "disabled", false ).focus();
// 	} else {
// 	       $('input#search_product').prop( "disabled", true );
// 	}

// 	initialize_printer();
// }

if ($('#search_sell_return').length) {
    //Add Product
    $('#search_sell_return')
        .autocomplete({
            source: function(request, response) {
                let _searchKey = request.term;
                $("#setSearchKey").val(_searchKey);
                let custId = $("#getCustomer").val();
                var price_group = '';
                var search_fields = [];
                if(custId == ''){
                    alert("Please select customer!");
                    return false;
                }
                $.getJSON(
                    '/products/sell-return-list', {
                        price_group: price_group,
                        location_id: $('input#location_id').val(),
                        term: _searchKey,
                        not_for_selling: $('#nfs_items').is(':checked') ? 1 : 0,
                        search_fields: search_fields
                    },
                    response
                );
            },
            minLength: 2,
              response: function (event, ui) {
                // automatic scan product start

                /*var is_overselling_allowed = false;
                if ($('input#is_overselling_allowed').length) {
                    is_overselling_allowed = true;
                }*/
                if (ui.content.length == 1) {
                    ui.item = ui.content[0];
                    if (
                        (ui.item.enable_stock == 1 && ui.item.qty_available > 0) ||
                        (ui.item.enable_stock == 1 && ui.item.qty_available < 0) ||
                        ui.item.enable_stock == 0
                    ) {
                        let customer_id = $('#getCustomer').val();
                        if (customer_id === '') {
                            toastr.error('Customer Not Selected!');
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
                    $('input#search_sell_return').select();
                }
                // automatic scan product end
                $('input#search_for_value').val('');
            },
            focus: function(event, ui) {
            },
            select: function(event, item) {
                if($('input#search_for_value').length==0)
                {
                    $(this).prepend('<input type="hidden" id="search_for_value" value="'+$(this).val()+'" />');
                }
                else
                {
                    $('input#search_for_value').val($(this).val());
                }
                // get_sellreturn_entry_row(item.item.variation_id);
                $(this).val(null);
                get_sellreturn_entry_row(
                    item.item.sku,
                    null,
                    item.item.product_id,
                    item.item.variation_id
                );
            },
            close: function(event, ui) {

                if($('input#search_for_value').val()!="")
                {
                    $('input#search_sell_return').val($('input#search_for_value').val());
                }

                if (event.keyCode === $.ui.keyCode.ESCAPE) {

                    $('input#search_sell_return').val('');
                    $('input#search_for_value').val('');
                    $('.ui-autocomplete').hide();
                }
                else if($('input#search_sell_return').val()!="")
                {
                    $('input#search_sell_return').focus();
                    $('.ui-autocomplete').show();
                }
            },
        })
        .autocomplete('instance')._renderItem = function(ul, item) {
            var is_overselling_allowed = false;
                if ($('input#is_overselling_allowed').length) {
                    is_overselling_allowed = true;
                }

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
        };
}
$(document).on('click', '.ui-menu-item', function() {
if(!$('div').hasClass('toast-error'))
    {
        $(this).css({'background-color': '#808080' , 'color' : 'white'});
    }
});
// function get_sellreturn_entry_row(searchKey, custId){
//     var product_row = $('input#product_row_count').val();
//     let customer_id = $("#getCustomer").val();
//     $.ajax({
//         type: "GET",
//         url: "/sell-return/get/item/forReturn",
//         data: {
//             searchKey: searchKey,
//             product_row: product_row,
//             customer_id: customer_id
//         },
//         success: function (data) {
//             $('input#product_row_count').val(parseInt(product_row) + 1);
//             $('#sell_return_table tbody')
//                         .append(data);
//                         //.find('input.pos_quantity');
//             //$('#sell_return_table tbody').html(data);
//         }
//     });
// }

function get_sellreturn_entry_row(searchKey, custId, productID, variationID) {
    var add_via_ajax = true;
    var is_added = false;
    var rowCount = $('#sell_return_table tr').length;
    var i = 1;
    //Search for variation id in each row of sell return table
    $('#sell_return_table tbody')
        .find('tr')
        .each(function () {
            $(this).attr('id', i);
            var row_v_id = $(this).find('.row_variation_id').val();
            if (
                row_v_id == variationID &&
                /*enable_sr_no !== '1' &&
                !modifiers_exist &&*/
                !is_added
            ) {
                add_via_ajax = false;
                is_added = true;

                //Increment product quantity
                $(this).attr('id', rowCount);
                qty_element = $(this).find('.return_qty');
                var qty = __read_number(qty_element);
                __write_number(qty_element, qty + 1);
                qty_element.change();
            }
            i++;
        });
    sortTableRow();
    if (add_via_ajax) {
        var product_row = $('input#product_row_count').val();
        let customer_id = $('#getCustomer').val();
        $.ajax({
            type: 'GET',
            url: '/sell-return/get/item/forReturn',
            data: {
                searchKey: searchKey,
                product_row: product_row,
                customer_id: customer_id,
            },
            success: function (data) {
                $('input#product_row_count').val(parseInt(product_row) + 1);
                $('#sell_return_table tbody').append(data);
                //.find('input.pos_quantity');
                //$('#sell_return_table tbody').html(data);
                update_sell_return_total();
            },
        });
    }
}

//Sort Table row
function sortTableRow() {
    var rows = $('#sell_return_table tbody  tr').get();
    rows.sort(function (a, b) {
        var A = getVal(a);
        var B = getVal(b);
        var result = A - B;
        if (result !== 0) return result;
    });

    function getVal(elm) {
        var v = $(elm).attr('id');
        return v;
    }

    $.each(rows, function (index, row) {
        $('#sell_return_table').children('tbody').append(row);
    });
}
