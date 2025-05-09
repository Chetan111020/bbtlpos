$(document).ready(function() {
    //Add products
    if ($('#search_product_for_srock_adjustment').length > 0) {
        //Add Product
        $('#search_product_for_srock_adjustment')
            .autocomplete({
                source: function(request, response) {
                    $.getJSON(
                        '/products/list',
                        { location_id: $('#location_id').val(), term: request.term },
                        response
                    );
                },
                minLength: 2,
                response: function(event, ui) {
                    console.log('ui:', ui)
                    if (ui.content.length == 1) {
                        ui.item = ui.content[0];
                        // if (ui.item.qty_available > 0 && ui.item.enable_stock == 1) {
                        //     $(this)
                        //         .data('ui-autocomplete')
                        //         ._trigger('select', 'autocompleteselect', ui);
                        //     $(this).autocomplete('close');
                        // }
                    } else if (ui.content.length === 0) {
                        // swal(LANG.no_products_found);
                        $("#search_prod").fadeOut(0);  
                        $('#search_prod').show();   
                        $("#search_prod").fadeIn();  
                    }
                    $('input#search_for_value').val('');
                },
                focus: function(event, ui) {
                    if (ui.item.qty_available <= 0) {
                        return false;
                    }
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
                    allRowsTwo();
                    if (ui.item.qty_available > 0) {
                        $(this).val(null);
                        stock_adjustment_product_row(ui.item.variation_id);
                    } else {
                        // alert(LANG.out_of_stock);
                        stock_adjustment_product_row(ui.item.variation_id);
                    }
                },
                close: function(event, ui) {
                    
                    if($('input#search_for_value').val()!="")
                    {
                        $('input#search_product_for_srock_adjustment').val($('input#search_for_value').val());
                    }

                    if (event.keyCode === $.ui.keyCode.ESCAPE) {
                        
                        $('input#search_product_for_srock_adjustment').val('');
                        $('input#search_for_value').val('');
                        $('.ui-autocomplete').hide();
                    }
                    else if($('input#search_product_for_srock_adjustment').val()!="")
                    { 
                        $('input#search_product_for_srock_adjustment').focus();
                        $('.ui-autocomplete').show();
                    }
                },
            })
            .autocomplete('instance')._renderItem = function(ul, item) {
                $('#search_prod').hide(); 
            if (item.qty_available <= 0) {
                // var string = '<li class="ui-state-disabled">' + item.name;
                // if (item.type == 'variable') {
                //     string += '-' + item.variation;
                // }
                // string += ' (' + item.sub_sku + ') (Out of stock) </li>';
                // return $(string).appendTo(ul);

                var string = '<div>' + item.name;
                if (item.type == 'variable') {
                    string += '-' + item.variation;
                }

                string += ' (' + item.sub_sku + ') ' +item.text+' (<span class="label bg-gray"> Out of stock </span>) </div>';
                return $('<li>')
                    .append(string)
                    .appendTo(ul);
            } else if (item.enable_stock != 1) {
                return ul;
            } else {
                var string = '<div>' + item.name;
                if (item.type == 'variable') {
                    string += '-' + item.variation;
                }
                string += ' (' + item.sub_sku + ')' +item.text+' </div>';
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
    
    $(document).on('click','#notforselling',function(){
        var productid = $("#productid").val();
        console.log(productid);
       if($(this).prop('checked') == false){
            var selling = 0;
            $.ajax({
                method: 'POST',
                url: '/stock-adjustments/update_not_for_selling',
                dataType: 'json',
                data: {
                    productid: productid,
                    selling: selling,
                },
                success: function(result) {
                    if(result.success == true){
                        toastr.success(result.message);
                    }
                   else if(result.success == false){
                        toastr.success(result.message);
                    }
                },
            });
       }
       else if($(this).prop('checked') == true){
        var selling = 1;
            $.ajax({
                method: 'POST',
                url: '/stock-adjustments/update_not_for_selling',
                dataType: 'json',
                data: {
                    productid: productid,
                    selling: selling,
                },
                success: function(result) {
                    if(result.success == true){
                        toastr.success(result.message);
                    }
                    else if(result.success == false){
                        toastr.success(result.message);
                    }
                },
            });
       }
    });
    
    
    
    $(document).on('click','#outofstock',function(){
        var productid = $("#productid").val();
       if($(this).prop('checked') == false){
            var outofstock = 0;
            $.ajax({
                method: 'POST',
                url: '/stock-adjustments/update_out_of_stock',
                dataType: 'json',
                data: {
                    productid: productid,
                    outofstock: outofstock,
                },
                success: function(result) {
                    if(result.success == true){
                        toastr.success(result.message);
                    }
                    else if(result.success == false){
                        toastr.success(result.message);
                    }
                },
            });
       }
       else if($(this).prop('checked') == true){
        var outofstock = 1;
            $.ajax({
                method: 'POST',
                url: '/stock-adjustments/update_out_of_stock',
                dataType: 'json',
                data: {
                    productid: productid,
                    outofstock: outofstock,
                },
                success: function(result) {
                    if(result.success == true){
                        toastr.success(result.message);
                    }
                    else if(result.success == false){
                        toastr.success(result.message);
                    }
                },
            });
       }
    });

    $('select#location_id').change(function() {
        if ($(this).val()) {
            $('#search_product_for_srock_adjustment').removeAttr('disabled');
        } else {
            $('#search_product_for_srock_adjustment').attr('disabled', 'disabled');
        }
        $('table#stock_adjustment_product_table tbody').html('');
        $('#product_row_index').val(0);
        update_table_total();
    });

    $(document).on('change', 'input.product_quantity', function() {
        update_table_row($(this).closest('tr'));
    });
    $(document).on('change', 'input.product_unit_price', function() {
        update_table_row($(this).closest('tr'));
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
                document.getElementById('search_product_for_srock_adjustment').focus();
            }
        });
    });

    //Date picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    $('form#stock_adjustment_form').validate();

    stock_adjustment_table = $('#stock_adjustment_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/stock-adjustments',
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
            },
        ],
        aaSorting: [[1, 'desc']],
        columns: [
            { data: 'action', name: 'action' },
            { data: 'transaction_date', name: 'transaction_date' },
            { data: 'ref_no', name: 'ref_no' },
            { data: 'location_name', name: 'BL.name' },
            { data: 'adjustment_type', name: 'adjustment_type' },
            { data: 'final_total', name: 'final_total' },
            { data: 'total_amount_recovered', name: 'total_amount_recovered' },
            { data: 'additional_notes', name: 'additional_notes' },
            { data: 'added_by', name: 'u.first_name' },
        ],
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#stock_adjustment_table'));
        },
    });
    var detailRows = [];

    /*$(document).on('click', 'button.delete_stock_adjustment', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).data('href');
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            stock_adjustment_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });*/
    // added by developer 1 for delete stock adjustments
    $(document).on('click', 'button.delete_stock_adjustment', function(e) {
        e.preventDefault();
        href = $(this).attr('data-href');
        var config = {
                        title: LANG.sure,
                        content:{
                            element:"textarea",
                                attributes: {
                                    id: "delete_reason",
                                    name: "delete_reason",
                                    placeholder: "Reason",
                                    className: "swal-content__textarea",
                                    rows:6
                             },
                        },
                        icon: 'warning',
                        //buttons: true,
                        dangerMode: true,
                        buttons: {
                          cancel: {
                            text: 'Cancel',
                            visible: true
                          },
                          confirm: {
                            text: 'Submit',
                            closeModal: false
                          }
                        }
                    };

        (function trick() {
            swal(config).then(willDelete => {
                if (willDelete) {
                    content = $('#delete_reason').val();
                    if($.trim(content)!="")
                    {
                        //var data = $(this).serialize();
                        var data = { reason: content };
                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            data: data,
                            dataType: 'json',
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    stock_adjustment_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                                swal.close();
                            },
                            error: function (err) {
                              swal('Error', 'Unfortunately, an error occurred. Please try again.', 'error');
                            }
                        });
                    } else {
                        //alert("asdf");
                        //swal('Validate', 'Please enter valid reason', 'error');
                        //alert(href);
                        alert('Please enter valid reason');
                        swal.stopLoading();
                        trick();
                    }
                }
            })
        })();
    });
});

function stock_adjustment_product_row(variation_id) {
    var row_index = parseInt($('#product_row_index').val());
    var location_id = $('select#location_id').val();
    $.ajax({
        method: 'POST',
        url: '/stock-adjustments/get_product_row',
        data: { row_index: row_index, variation_id: variation_id, location_id: location_id },
        dataType: 'html',
        success: function(result) {
            $('table#stock_adjustment_product_table tbody').append(result);
            update_table_total();
            $('#product_row_index').val(row_index + 1);
            document.getElementById('input_'+row_index+'_box'+1).focus();
            allRowsTwo();
        },
    });
}

function update_table_total() {
    var table_total = 0;
    $('table#stock_adjustment_product_table tbody tr').each(function() {
        var this_total = parseFloat(__read_number($(this).find('input.product_line_total')));
        if (this_total) {
            table_total += this_total;
        }
    });
    $('input#total_amount').val(table_total);
    $('span#total_adjustment').text(__number_f(table_total));
}

function update_table_row(tr) {
    var quantity = parseFloat(__read_number(tr.find('input.product_quantity')));
    var unit_price = parseFloat(__read_number(tr.find('input.product_unit_price')));
    var row_total = 0;
    if (quantity && unit_price) {
        row_total = quantity * unit_price;
    }
    tr.find('input.product_line_total').val(__number_f(row_total));
    update_table_total();
    newChange(tr);    
}

$(document).on('shown.bs.modal', '.view_modal', function() {
    __currency_convert_recursively($('.view_modal'));
});

//adjustment_type
function newChange(tr){
    var qty_on_hand = parseFloat(__read_number(tr.find('input.qty_on_hand')));
    var adjType = $('select#adjustment_type').val();
    var quantity = parseFloat(__read_number(tr.find('input.product_quantity')));
    
    var nq = 0.00;
    if(adjType == 'abnormal')
    {
        nq = quantity + qty_on_hand;
        tr.find('input.new_qty').val(__number_f(nq));
        tr.find('input.diff_qty').val(__number_f(quantity));
    }
    if(adjType == 'normal')
    {
        nq = quantity - qty_on_hand;
        tr.find('input.new_qty').val(__number_f(quantity));
        tr.find('input.diff_qty').val(__number_f(nq));
    }
}

function allRowsTwo()
{
    $('#stock_adjustment_product_table tbody')
    .find('tr')
    .each(function() {
        // console.log('row',$(this).closest('tr'));
        update_table_row($(this).closest('tr'));
    });
}