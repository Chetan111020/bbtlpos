$(document).ready(function() {
    //Add products
    if ($('#search_product_for_label').length > 0) {
        $('#search_product_for_label')
            .autocomplete({
                source: '/purchase-order/get_products_one?check_enable_stock=false',
                minLength: 2,
                response: function(event, ui) {
                    if (ui.content.length == 1) {
                        ui.item = ui.content[0];
                        $(this)
                            .data('ui-autocomplete')
                            ._trigger('select', 'autocompleteselect', ui);
                        $(this).autocomplete('close');
                    } else if (ui.content.length == 0) {
                        swal(LANG.no_products_found);
                    }
                },
                select: function(event, ui) {
                    $(this).val(null);
                    get_label_product_row(ui.item.product_id, ui.item.variation_id);
                },
            })
            .autocomplete('instance')._renderItem = function(ul, item) {
            return $('<li>')
                .append('<div>' + item.text + '</div>')
                .appendTo(ul);
        };
    }

    $('div#price_type_div').hide();
    $('input#is_show_price').change(function() {
        if ($(this).is(':checked')) {
            $('div#price_type_div').show();
        } else {
            $('div#price_type_div').hide();
        }
    });
    
    $('input#is_business_name').change(function() {
        if ($(this).is(':checked')) {
            $('div#is_business_name').val(1);
        } else {
            $('div#is_business_name').val();
        }
    });
     $('input#is_item_code').change(function() {
        if ($(this).is(':checked')) {
            $('div#is_item_code').val(1);
        } else {
            $('div#is_item_code').val();
        }
    });
     $('input#is_variations').change(function() {
        if ($(this).is(':checked')) {
            $('div#is_variations').val(1);
        } else {
            $('div#is_variations').val();
        }
    });
    

    $('button#labels_preview').click(function() {
        if ($('form#preview_setting_form table#product_table tbody tr').length > 0) {
            var url = base_path + '/labels/preview?' + $('form#preview_setting_form').serialize();

            window.open(url, 'newwindow');

            // $.ajax({
            //     method: 'get',
            //     url: '/labels/preview',
            //     dataType: 'json',
            //     data: $('form#preview_setting_form').serialize(),
            //     success: function(result) {
            //         if (result.success) {
            //             $('div.display_label_div').removeClass('hide');
            //             $('div#preview_box').html(result.html);
            //             __currency_convert_recursively($('div#preview_box'));
            //         } else {
            //             toastr.error(result.msg);
            //         }
            //     },
            // });
        } else {
            swal(LANG.label_no_product_error).then(value => {
                $('#search_product_for_label').focus();
            });
        }
    });

    $(document).on('click', 'button#print_label', function() {
        window.print();
    });
});

function get_label_product_row(product_id, variation_id) {
    if (product_id) {
        var row_count = $('table#product_table tbody tr').length;
        $.ajax({
            method: 'GET',
            url: '/labels/add-product-row',
            dataType: 'html',
            data: { product_id: product_id, row_count: row_count, variation_id: variation_id },
            success: function(result) {
                $('table#product_table tbody').append(result);
            },
        });
    }
}
