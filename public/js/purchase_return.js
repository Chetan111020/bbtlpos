$(document).ready(function() {
    //get suppliers
    $('#supplier_id').select2({
        ajax: {
            url: '/purchases/get_suppliers',
            dataType: 'json',
            delay: 250,
            data: function(params) {
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
        minimumInputLength: 1,
        escapeMarkup: function(m) {
            return m;
        },
        templateResult: function(data) {
            if (!data.id) {
                return data.text;
            }
            var html = data.text + ' - ' + data.business_name + ' (' + data.contact_id + ')';
            return html;
        }
    });
    //Add products
    if ($('#search_product_for_purchase_return').length > 0) {
        //Add Product
        $('#search_product_for_purchase_return')
            .autocomplete({
                source: function(request, response) {
                    $.getJSON(
                        '/products/list', { location_id: $('#location_id').val(), term: request.term, not_for_selling: $('#nfs_items').is(':checked') ? 1 : 0 },
                        response
                    );
                },
                minLength: 2,
                response: function(event, ui) {
                    if (ui.content.length == 1) {
                        ui.item = ui.content[0];
                        // if (ui.item.qty_available > 0 && ui.item.enable_stock == 1) {
                            $(this)
                                .data('ui-autocomplete')
                                ._trigger('select', 'autocompleteselect', ui);
                            $(this).autocomplete('close');
                        // }
                    } else if (ui.content.length == 0) {
                        toastr.error(LANG.no_products_found);
                    }
                    $('input#search_for_value').val('');
                },
                focus: function(event, ui) {
                    // if (ui.item.qty_available <= 0) {
                    //     return false;
                    // }
                },
                select: function(event, ui) {
                    if($('input#search_for_value').length==0)
                    {
                        $(this).prepend('<input type="hidden" id="search_for_value" value="'+$(this).val()+'" />');
                    }
                    else
                    {
                        $('input#search_for_value').val($(this).val());
                    }
                    // if (ui.item.qty_available > 0) {
                        $(this).val(null);
                        purchase_return_product_row(ui.item.variation_id);
                    // } else {
                    //     alert(LANG.out_of_stock);
                    // }
                },
                close: function(event, ui) {

                    if($('input#search_for_value').val()!="")
                    {
                        $('input#search_product_for_purchase_return').val($('input#search_for_value').val());
                    }

                    if (event.keyCode === $.ui.keyCode.ESCAPE) {

                        $('input#search_product_for_purchase_return').val('');
                        $('input#search_for_value').val('');
                        $('.ui-autocomplete').hide();
                    }
                    else if($('input#search_product_for_purchase_return').val()!="")
                    {
                        $('input#search_product_for_purchase_return').focus();
                        $('.ui-autocomplete').show();
                    }
                },
            })
            .autocomplete('instance')._renderItem = function(ul, item) {
                // if (item.qty_available <= 0) {
                    // var string = '<li class="ui-state-disabled">' + item.name;
                    // if (item.type == 'variable') {
                    //     string += '-' + item.variation;
                    // }
                    // string += ' (' + item.sub_sku + ') (Out of stock) </li>';
                    // return $(string).appendTo(ul);
                // }
                //else
                 if (item.enable_stock != 1) {
                    return ul;
                } else {
                    var string = '<div>' + item.name;
                    if (item.type == 'variable') {
                        string += '-' + item.variation;
                    }
                    if(item.qty_available > 0){
                        string += ' (' + item.sub_sku + ') </div>';
                    }
                    else{
                        string += ' (' + item.sub_sku + ') (Out of stock) </div>';
                    }
                    return $('<li>')
                        .append(string)
                        .appendTo(ul);
                }
            };
    }
    $(document).on('click', '.ui-menu-item', function() {
        if(!$('div').hasClass('toast-error'))
        {
            $(this).css({'background-color': '#808080' , 'color' : 'white'});
        }
    });
    $('select#location_id').change(function() {
        if ($(this).val()) {
            $('#search_product_for_purchase_return').removeAttr('disabled');
        } else {
            $('#search_product_for_purchase_return').attr('disabled', 'disabled');
        }
        $('table#stock_adjustment_product_table tbody').html('');
        $('#product_row_index').val(0);
    });
    $(document).on('change', 'input.loose_qty,input.loose_price', function() {
        update_table_row($(this).closest('tr'));
    });
    $(document).on('change', 'input.inv_return', function() {
        update_table_row($(this).closest('tr'));
    });
    $(document).on('change', 'input.gar_q', function() {
        update_table_row($(this).closest('tr'));
    });
    $(document).on('change', 'input.box_price', function() {
        update_table_row($(this).closest('tr'));
    });
    $(document).on('change', 'input.piece_price', function() {
        update_table_row($(this).closest('tr'));
    });
    $(document).on('change', 'input.product_quantity', function() {
        update_table_row($(this).closest('tr'));
    });
    $(document).on('change', 'input.product_unit_price', function() {
        update_table_row($(this).closest('tr'));
    });

    $(document).on('change', '#shipping_charges,#discount_amount', function() {
        update_table_total();
    });

    $(document).on('click', '.remove_product_row', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                $(this)
                    .closest('tr')
                    .remove();
                update_table_total();
            }
        });
    });

    //Date picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    $('form#purchase_return_form').validate();

    $(document).on('click', 'button#submit_purchase_return_form', function(e) {
        e.preventDefault();

        //Check if product is present or not.
        if ($('table#purchase_return_product_table tbody tr').length <= 0) {
            toastr.warning(LANG.no_products_added);
            $('input#search_product_for_purchase_return').select();
            return false;
        }

        if ($('form#purchase_return_form').valid()) {
            $('form#purchase_return_form').submit();
        }
    });

    $('#tax_id').change(function() {
        update_table_total();
    });

    $('#purchase_return_product_table tbody')
        .find('.expiry_datepicker')
        .each(function() {
            $(this).datepicker({
                autoclose: true,
                format: datepicker_date_format,
            });
        });


});

// function purchase_return_product_row(variation_id) {
//     var row_index = parseInt($('#product_row_index').val());
//     var location_id = $('#location_id').val();
//     $.ajax({
//         method: 'POST',
//         url: '/purchase-return/get_product_row',
//         data: { row_index: row_index, variation_id: variation_id, location_id: location_id },
//         dataType: 'html',
//         success: function(result) {
//             $('table#purchase_return_product_table tbody').append(result);

//             $('table#purchase_return_product_table tbody tr:last').find('.expiry_datepicker').datepicker({
//                 autoclose: true,
//                 format: datepicker_date_format,
//             });

//             $('#purchase_return_product_table tbody')
//             .find('tr')
//             .each(function() {
//                 var row_v_id = $(this)
//                     .find('.row_variation_id')
//                     .val();
//                 if (
//                     row_v_id == variation_id
//                 ) {
//                     qty_element = $(this).find('.inv_return');
//                     qty_element.change();
//                 }
//             });

//             update_table_total();
//             $('#product_row_index').val(row_index + 1);
//         },
//     });
// }
function purchase_return_product_row(variation_id) {

    var add_via_ajax = true;
    var is_added = false;
    var rowCount = $('#purchase_return_product_table tr').length;
    var i = 1;
    //Search for variation id in each row of sell return table
    $('#purchase_return_product_table tbody')
        .find('tr')
        .each(function() {
            $(this).attr("id",i);
            var row_v_id = $(this)
                .find('.row_variation_id')
                .val();
            if (
                row_v_id == variation_id &&
                /*enable_sr_no !== '1' &&
                !modifiers_exist &&*/
                !is_added
            ) {
                add_via_ajax = false;
                is_added = true;

                //Increment product quantity
                $(this).attr("id",rowCount);
                qty_element = $(this).find('.inv_return');
                var qty = __read_number(qty_element);
                __write_number(qty_element, qty + 1);
                qty_element.change();
            }
            i++;
        });
        sortTableRow();
    if (add_via_ajax) {
        var row_index = parseInt($('#product_row_index').val());
        var location_id = $('#location_id').val();
        $.ajax({
            method: 'POST',
            url: '/purchase-return/get_product_row',
            data: { row_index: row_index, variation_id: variation_id, location_id: location_id },
            dataType: 'html',
            success: function(result) {
                $('table#purchase_return_product_table tbody').append(result);

                $('table#purchase_return_product_table tbody tr:last').find('.expiry_datepicker').datepicker({
                    autoclose: true,
                    format: datepicker_date_format,
                });

                update_table_total();
                $('#product_row_index').val(row_index + 1);
            },
        });
    }
}

function update_table_total() {
    var shipping_charges = parseFloat($('#shipping_charges').val());
    var discount_amount = parseFloat($('#discount_amount').val());
    var table_total = 0;
    $('table#purchase_return_product_table tbody tr').each(function() {
        var this_total = parseFloat(__read_number($(this).find('input.sub_total')));
        if (this_total) {
            table_total += this_total;
        }
    });
    var tax_rate = parseFloat($('option:selected', $('#tax_id')).data('tax_amount'));
    var tax = __calculate_amount('percentage', tax_rate, table_total);
    __write_number($('input#tax_amount'), tax);

    var final_total = table_total + tax + (shipping_charges || 0) - (discount_amount || 0);
    $('input#total_amount').val(final_total);
    $('span#total_return').text(__number_f(final_total));

    update_footer();
}

function update_table_row_old(tr) {
    var gar_box_return_qty = parseFloat(__read_number(tr.find('input.gar_box_return_qty')));
    var gar_piece_return_qty = parseFloat(__read_number(tr.find('input.gar_piece_return_qty')));
    var inv_return = parseFloat(__read_number(tr.find('input.inv_return')));
    var loose_qty = parseFloat(__read_number(tr.find('input.loose_qty')));
    var loose_price = parseFloat(__read_number(tr.find('input.loose_price')));
    var gar_q = parseFloat(__read_number(tr.find('input.gar_q')));
    var box_price = parseFloat(__read_number(tr.find('input.box_price')));
    var piece_price = parseFloat(__read_number(tr.find('input.piece_price')));
    var quantity = parseFloat(__read_number(tr.find('input.product_quantity')));
    var unit_price = parseFloat(__read_number(tr.find('input.product_unit_price')));
    var row_total = 0;
    if (quantity && unit_price) {
        row_total = quantity * unit_price;
    }
    var tq = 0;
    var total_qty = 0;
    var tq = gar_box_return_qty + (gar_piece_return_qty / 100);
    var total_qty = inv_return + tq + loose_qty;
    var vendor_return_qty = total_qty - gar_q;
    var vendor_box = Math.trunc(vendor_return_qty);
    var vendor_piece = (vendor_return_qty - vendor_box) * 100;
    var box_p = box_price * vendor_box;
    var piece_p = piece_price * vendor_piece;
    var sub_total = box_p + piece_p;
    tr.find('input.total_qty').val(__number_f(total_qty));
    tr.find('input.vendor_return_qty').val(__number_f(vendor_return_qty));
    tr.find('input.sub_total').val(__number_f(sub_total));
    tr.find('input.product_line_total').val(__number_f(row_total));
    update_table_total();
}

function update_table_row(tr) {
    var inv_return = parseFloat(__read_number(tr.find('input.inv_return')));
    var box_price = parseFloat(__read_number(tr.find('input.box_price')));
    var loose_qty = parseFloat(__read_number(tr.find('input.loose_qty')));
    var loose_price = parseFloat(__read_number(tr.find('input.loose_price')));

    var rtn_total = inv_return * box_price;
    var loose_total = loose_qty * loose_price;
    var sub_total = rtn_total + loose_total;
    tr.find('input.sub_total').val(sub_total);
    tr.find('input.sub_total_display').val(__number_f(sub_total));
    update_table_total();
}
$(document).ready(function(){
    update_footer();
});

function update_footer(){
    var count1 = 0;
    var count2 = 0;
    var count3 = 0;
    var count4 = 0;
    var count5 = 0;
    var count6 = 0;

    $('table#purchase_return_product_table tbody tr').each(function() {
        count1++;
        var temp2 = parseFloat(__read_number($(this).find('input.inv_return')));
        count2 += (temp2 || 0);
        var temp3 = parseFloat(__read_number($(this).find('input.box_price')));
        count3 += (temp3 || 0);
        var temp4 = parseFloat(__read_number($(this).find('input.loose_qty')));
        count4 += (temp4 || 0);
        var temp5 = parseFloat(__read_number($(this).find('input.loose_price')));
        count5 += (temp5 || 0);
        var temp6 = parseFloat(__read_number($(this).find('input.sub_total')));
        count6 += (temp6 || 0);
    });

    $('#tfoot1').html("Total Products: "+count1);
    $('#tfoot2').html("Total Return Qty<br/>"+count2);
    $('#tfoot3').html("Total Unit Price<br/>"+__number_f(count3));
    $('#tfoot4').html("Total Loose Qty<br/>"+count4);
    $('#tfoot5').html("Total Loose Price<br/>"+__number_f(count5));
    $('#tfoot6').html("Net Total<br/>"+__number_f(count6));
}


function get_stock_adjustment_details(rowData) {
    var div = $('<div/>')
        .addClass('loading')
        .text('Loading...');
    $.ajax({
        url: '/stock-adjustments/' + rowData.DT_RowId,
        dataType: 'html',
        success: function(data) {
            div.html(data).removeClass('loading');
        },
    });

    return div;
}


//Sort Table row
function sortTableRow(){
    var rows = $('#purchase_return_product_table tbody  tr').get();
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
           $('#purchase_return_product_table').children('tbody').append(row);
       });
}
