//This file contains all functions used products tab
$(document).keydown(function(e){
   if(e.keyCode == 113){
      var type = $("#type").val();
      if(type == 'variable'){
        $('.add_variation_value_row').click();
      }
  }
 })

 var lastChecked = null;
$(document).ready(function() {

    $(document).on('click','.row-select',function(e) {
        if (!lastChecked) {
            lastChecked = this;
            return;
        }

        if (e.shiftKey) {
            var start = $('.row-select').index(this);
            var end = $('.row-select').index(lastChecked);

            $('.row-select').slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastChecked.checked);

            $('.row-select').parent().parent().removeClass('selected');
            $('.row-select:checked').parent().parent().addClass('selected');
        }

        lastChecked = this;
    });

       $(document).on('ifChecked', 'input#enable_vendor', function() {
        $('div#alert_vendor_div').show();

    });
    $(document).on('ifUnchecked', 'input#enable_vendor', function() {
        $('div#alert_vendor_div').hide();

        // $('div#quick_product_opening_stock_div').hide();
        //$('input#alert_quantity').val(0);


    });

    $(document).on('click', '#syncsave', function(e) {
        e.preventDefault();
        var submit_type = $(this).attr('value');
        $('#submit_type').val(submit_type);
        // $('form#product_add_form').validate({
        //     rules: {
        //         sku: {
        //             remote: {
        //                 url: '/products/check_product_sku',
        //                 type: 'post',
        //                 data: {
        //                     sku: function() {
        //                         return $('#gen-bar-code').val();
        //                     },
        //                     product_id: function() {
        //                         if ($('#product_id').length > 0) {
        //                             return $('#product_id').val();
        //                         } else {
        //                             return '';
        //                         }
        //                     },
        //                 },
        //             },
        //         },
        //         expiry_period: {
        //             required: {
        //                 depends: function(element) {
        //                     return (
        //                         $('#expiry_period_type')
        //                         .val()
        //                         .trim() != ''
        //                     );
        //                 },
        //             },
        //         },
        //     },
        //     messages: {
        //         sku: {
        //             remote: LANG.sku_already_exists,
        //         },
        //     },
        // });
        if ($('form#product_add_form').valid()) {
            console.log(123);
            $('form#product_add_form').submit();
        }
    });

    $(document).on('ifChecked', 'input#enable_stock', function() {
        $('div#alert_quantity_div').show();
        $('div#quick_product_opening_stock_div').show();

        //Enable expiry selection
        if ($('#expiry_period_type').length) {
            $('#expiry_period_type').removeAttr('disabled');
        }

        if ($('#opening_stock_button').length) {
            $('#opening_stock_button').removeAttr('disabled');
        }
    });
    $(document).on('ifUnchecked', 'input#enable_stock', function() {
        $('div#alert_quantity_div').hide();
        $('div#quick_product_opening_stock_div').hide();
        $('input#alert_quantity').val(0);

        //Disable expiry selection
        if ($('#expiry_period_type').length) {
            $('#expiry_period_type')
                .val('')
                .change();
            $('#expiry_period_type').attr('disabled', true);
        }
        if ($('#opening_stock_button').length) {
            $('#opening_stock_button').attr('disabled', true);
        }
    });

    //Start For product type single

    //If purchase price exc tax is changed
    $(document).on('change', 'input#single_dpp_inc_tax', function(e) {
        var purchase_exc_tax = __read_number($('input#single_dpp_inc_tax'));
        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var selling_price = __read_number($('input#single_dsp'));
        if(selling_price<0){
            var profit_percent = __read_number($('#profit_percent'));
            var selling_price = __add_percent(purchase_exc_tax, profit_percent);
            __write_number($('input#single_dsp'), selling_price);
        }
        if(selling_price>0){
            var profit_percent = __get_rate(purchase_exc_tax, selling_price);
            __write_number($('input#profit_percent'), profit_percent);
        }


        var selling_price_tier1 = __read_number($('input#single_dsp_tier1'));
        if(selling_price_tier1 <0){
            var profit_percent_tier1 = __read_number($('#profit_percent_tier1'));
            var selling_price_tier1 = __add_percent(purchase_exc_tax, profit_percent_tier1);
            __write_number($('input#single_dsp_tier1'), selling_price_tier1);
        }
        if(selling_price_tier1 >0){
            var profit_percent_tier1 = __get_rate(purchase_exc_tax, selling_price_tier1);
            __write_number($('input#profit_percent_tier1'), profit_percent_tier1);
        }


        var selling_price_tier2 = __read_number($('input#single_dsp_tier2'));
        if(selling_price_tier2 <0){
            var profit_percent_tier2 = __read_number($('#profit_percent_tier2'));
            var selling_price_tier2 = __add_percent(purchase_exc_tax, profit_percent_tier2);
            __write_number($('input#single_dsp_tier2'), selling_price_tier2);
        }
        if(selling_price_tier2 >0){
            var profit_percent_tier2 = __get_rate(purchase_exc_tax, selling_price_tier2);
            __write_number($('input#profit_percent_tier2'), profit_percent_tier2);
        }

        var selling_price_tier3 = __read_number($('input#single_dsp_tier3'));
        if(selling_price_tier3 <0){
            var profit_percent_tier3 = __read_number($('#profit_percent_tier3'));
            var selling_price_tier3 = __add_percent(purchase_exc_tax, profit_percent_tier3);
            __write_number($('input#single_dsp_tier3'), selling_price_tier3);
        }
        if(selling_price_tier3 >0){
            var profit_percent_tier3 = __get_rate(purchase_exc_tax, selling_price_tier3);
            __write_number($('input#profit_percent_tier3'), profit_percent_tier3);
        }

        var selling_price_tier4 = __read_number($('input#single_dsp_tier4'));
        if(selling_price_tier4 <0){
            var profit_percent_tier4 = __read_number($('#profit_percent_tier4'));
            var selling_price_tier4 = __add_percent(purchase_exc_tax, profit_percent_tier4);
            __write_number($('input#single_dsp_tier4'), selling_price_tier4);
        }
        if(selling_price_tier4 >0){
            var profit_percent_tier4 = __get_rate(purchase_exc_tax, selling_price_tier4);
            __write_number($('input#profit_percent_tier4'), profit_percent_tier4);
        }


        // var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
        // __ing_price_inc_tax = __add_percent(selling_price, tax_rate);
        // __write_nwrite_number($('input#single_dpp_inc_tax'), purchase_inc_tax);


        // var profit_percent = __read_number($('#profit_percent'));
        // var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        // __write_number($('input#single_dsp'), selling_price);

        // var sellumber($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });


    //If tax rate is changed
    $(document).on('change', 'select#tax', function() {
        if ($('select#type').val() == 'single') {
            var purchase_exc_tax = __read_number($('input#single_dpp'));
            purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

            var tax_rate = $('select#tax')
                .find(':selected')
                .data('rate');
            tax_rate = tax_rate == undefined ? 0 : tax_rate;

            var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
            __write_number($('input#single_dpp_inc_tax'), purchase_inc_tax);

            var selling_price = __read_number($('input#single_dsp'));
            var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
            __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
        }
    });

    //If purchase price inc tax is changed
    $(document).on('change', 'input#single_dpp_inc_tax', function(e) {
        var purchase_inc_tax = __read_number($('input#single_dpp_inc_tax'));

        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        // var tax_rate = $('select#tax')
        //     .find(':selected')
        //     .data('rate');
        // tax_rate = tax_rate == undefined ? 0 : tax_rate;

        // var purchase_exc_tax = __get_principle(purchase_inc_tax, tax_rate);
        // __write_number($('input#single_dpp'), purchase_exc_tax);
        // $('input#single_dpp').change();

        var profit_percent = __read_number($('#profit_percent'));
        profit_percent = profit_percent == undefined ? 0 : profit_percent;
        var selling_price = __add_percent(purchase_inc_tax, profit_percent);
        __write_number($('input#single_dsp'), selling_price);


        var profit_percent_tier1 = __read_number($('#profit_percent_tier1'));
        profit_percent_tier1 = profit_percent_tier1 == undefined ? 0 : profit_percent_tier1;
        var selling_price_tier1 = __add_percent(purchase_inc_tax, profit_percent_tier1);
        __write_number($('input#single_dsp_tier1'), selling_price_tier1);


        var profit_percent_tier2 = __read_number($('#profit_percent_tier2'));
        profit_percent_tier2 = profit_percent_tier2 == undefined ? 0 : profit_percent_tier2;
        var selling_price_tier2 = __add_percent(purchase_inc_tax, profit_percent_tier2);
        __write_number($('input#single_dsp_tier2'), selling_price_tier2);


        var profit_percent_tier3 = __read_number($('#profit_percent_tier3'));
        profit_percent_tier3 = profit_percent_tier3 == undefined ? 0 : profit_percent_tier3;
        var selling_price_tier3 = __add_percent(purchase_inc_tax, profit_percent_tier3);
        __write_number($('input#single_dsp_tier3'), selling_price_tier3);


        var profit_percent_tier4 = __read_number($('#profit_percent_tier4'));
        profit_percent_tier4 = profit_percent_tier4 == undefined ? 0 : profit_percent_tier4;
        var selling_price_tier4 = __add_percent(purchase_inc_tax, profit_percent_tier4);
        __write_number($('input#single_dsp_tier4'), selling_price_tier4);


        // var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        // __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input#profit_percent', function(e) {
        // var tax_rate = $('select#tax')
        //     .find(':selected')
        //     .data('rate');
        // tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_inc_tax = __read_number($('input#single_dpp_inc_tax'));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        // var purchase_exc_tax = __read_number($('input#single_dpp'));
        // purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

        var profit_percent = __read_number($('input#profit_percent'));
        // console.log (profit_percent);
        var selling_price = __add_percent(purchase_inc_tax, profit_percent);
        __write_number($('input#single_dsp'), selling_price);

        // var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        // __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input#profit_percent_tier1', function(e) {
        // console.log('eeeeeeeeee');
        var purchase_inc_tax = __read_number($('input#single_dpp_inc_tax'));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        var profit_percent_tier1 = __read_number($('input#profit_percent_tier1'));
         console.log (profit_percent_tier1);
        var selling_price = __add_percent(purchase_inc_tax, profit_percent_tier1);
        __write_number($('input#single_dsp_tier1'), selling_price);

    });

    $(document).on('change', 'input#profit_percent_tier2', function(e) {
        // console.log('eeeeeeeeee');
        var purchase_inc_tax = __read_number($('input#single_dpp_inc_tax'));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        var profit_percent_tier2 = __read_number($('input#profit_percent_tier2'));
        //  console.log (profit_percent_tier2);
        var selling_price = __add_percent(purchase_inc_tax, profit_percent_tier2);
        __write_number($('input#single_dsp_tier2'), selling_price);

    });

    $(document).on('change', 'input#profit_percent_tier3', function(e) {
        // console.log('eeeeeeeeee');
        var purchase_inc_tax = __read_number($('input#single_dpp_inc_tax'));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        var profit_percent_tier3 = __read_number($('input#profit_percent_tier3'));
        //  console.log (profit_percent_tier3);
        var selling_price = __add_percent(purchase_inc_tax, profit_percent_tier3);
        __write_number($('input#single_dsp_tier3'), selling_price);

    });

    $(document).on('change', 'input#profit_percent_tier4', function(e) {
        // console.log('eeeeeeeeee');
        var purchase_inc_tax = __read_number($('input#single_dpp_inc_tax'));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        var profit_percent_tier4 = __read_number($('input#profit_percent_tier4'));
        //  console.log (profit_percent_tier4);
        var selling_price = __add_percent(purchase_inc_tax, profit_percent_tier4);
        __write_number($('input#single_dsp_tier4'), selling_price);

    });

    $(document).on('change', 'input#single_dsp', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var selling_price = __read_number($('input#single_dsp'));
        var purchase_exc_tax = __read_number($('input#single_dpp_inc_tax'));

        if(parseFloat(selling_price))
        {
            var profit_percent = __get_rate(purchase_exc_tax, selling_price);
            __write_number($('input#profit_percent'), profit_percent);
            __write_number($('input#profit_percent_tier1'), profit_percent);
            __write_number($('input#profit_percent_tier2'), profit_percent);
            __write_number($('input#profit_percent_tier3'), profit_percent);
            __write_number($('input#profit_percent_tier4'), profit_percent);

        }
        else
        {
             __write_number($('input#profit_percent'), '');
            __write_number($('input#profit_percent_tier1'), '');
            __write_number($('input#profit_percent_tier2'), '');
            __write_number($('input#profit_percent_tier3'), '');
            __write_number($('input#profit_percent_tier4'), '');

        }

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });


    $(document).on('change', 'input#single_dsp_tier1', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var selling_price = __read_number($('input#single_dsp_tier1'));
        var purchase_exc_tax = __read_number($('input#single_dpp_inc_tax'));

        if(parseFloat(selling_price))
        {
            var profit_percent_tier1 = __get_rate(purchase_exc_tax, selling_price);
            __write_number($('input#profit_percent_tier1'), profit_percent_tier1);
        }
        else
        {
            __write_number($('input#profit_percent_tier1'), '');
        }

        // var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        // __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });

     $(document).on('change', 'input#single_dsp_tier2', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var selling_price = __read_number($('input#single_dsp_tier2'));
        var purchase_exc_tax = __read_number($('input#single_dpp_inc_tax'));

        if(parseFloat(selling_price))
        {
            var profit_percent_tier2 = __get_rate(purchase_exc_tax, selling_price);
            __write_number($('input#profit_percent_tier2'), profit_percent_tier2);
        }
        else
        {
            __write_number($('input#profit_percent_tier2'), '');
        }

        // var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        // __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input#single_dsp_tier3', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var selling_price = __read_number($('input#single_dsp_tier3'));
        var purchase_exc_tax = __read_number($('input#single_dpp_inc_tax'));

        if(parseFloat(selling_price))
        {
            var profit_percent_tier3 = __get_rate(purchase_exc_tax, selling_price);
            __write_number($('input#profit_percent_tier3'), profit_percent_tier3);
        }
        else
        {
            __write_number($('input#profit_percent_tier3'), '');
        }

        // var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        // __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });
    
    $(document).on('change', 'input#single_dsp_tier4', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var selling_price = __read_number($('input#single_dsp_tier4'));
        var purchase_exc_tax = __read_number($('input#single_dpp_inc_tax'));

        if(parseFloat(selling_price))
        {
            var profit_percent_tier4 = __get_rate(purchase_exc_tax, selling_price);
            __write_number($('input#profit_percent_tier4'), profit_percent_tier4);
        }
        else
        {
            __write_number($('input#profit_percent_tier4'), '');
        }

        // var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        // __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input#single_dsp_inc_tax', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;
        var selling_price_inc_tax = __read_number($('input#single_dsp_inc_tax'));

        // var selling_price = __get_principle(selling_price_inc_tax, tax_rate);
        __write_number($('input#single_dsp'), selling_price);

        var selling_price = __read_number($('input#single_dsp'));
        var purchase_exc_tax = __read_number($('input#single_dpp_inc_tax'));
        var profit_percent = __get_rate(purchase_exc_tax, selling_price);
        __write_number($('input#profit_percent'), profit_percent);
    });

    $(document).on('click', '.submit_product_form', function(e) {
         if(e.which == 1){ // Enter key
            return false;
        }
        // e.preventDefault();
        var submit_type = $(this).attr('value');
        $('#submit_type').val(submit_type);
        $('form#product_add_form').validate({
            rules: {
                sku: {
                    remote: {
                        url: '/products/check_product_sku',
                        type: 'post',
                        data: {
                            sku: function() {
                                return $('#gen-bar-code').val();
                            },
                            product_id: function() {
                                if ($('#product_id').length > 0) {
                                    return $('#product_id').val();
                                } else {
                                    return '';
                                }
                            },
                        },
                    },
                },
                expiry_period: {
                    required: {
                        depends: function(element) {
                            return (
                                $('#expiry_period_type')
                                .val()
                                .trim() != ''
                            );
                        },
                    },
                },
            },
            messages: {
                sku: {
                    remote: LANG.sku_already_exists,
                },
            },
        });
        if ($('form#product_add_form').valid()) {
            // alert('hi');
            $('form#product_add_form').submit();
        }
    });
    //End for product type single

    //Start for product type Variable
    //If purchase price exc tax is changed
    $(document).on('change', 'input.variable_stock', function(e) {
        var tr_obj = $(this).closest('tr');

        var stock = __read_number($(this));
        stock = stock == undefined ? 0 : stock;

        // var stock_qty = $('select#tax')
        //     .find(':selected')
        //     .data('rate');
        // tax_rate = tax_rate == undefined ? 0 : tax_rate;
        //
        // var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
        // __write_number(tr_obj.find('input.variable_dpp_inc_tax'), purchase_inc_tax);
        //
        // var profit_percent = __read_number(tr_obj.find('input.variable_profit_percent'));
        // var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        // __write_number(tr_obj.find('input.variable_dsp'), selling_price);
        //
        // var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        // __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('click', 'input.variable_dsp', function(e) {
        var tr_obj = $(this).closest('tr');

        var _sellingPrice = __read_number($(this));
        // _sellingPrice = _sellingPrice == undefined ? 0 : _sellingPrice;

        // var tax_rate = $('select#tax')
        //     .find(':selected')
        //     .data('rate');
        // tax_rate = tax_rate == undefined ? 0 : tax_rate;
        //
        // var purchase_inc_tax = __add_percent(_sellingPrice, tax_rate);
        // __write_number(tr_obj.find('input.variable_dpp_inc_tax'), purchase_inc_tax);
        //
        // var profit_percent = __read_number(tr_obj.find('input.variable_profit_percent'));
        // var selling_price = __add_percent(_sellingPrice, profit_percent);
        // __write_number(tr_obj.find('input.variable_dsp'), selling_price);
        //
        // var selling_price_inc_tax = __add_percent(_sellingPrice, tax_rate);
        // __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input.variable_dpp', function(e) {
        var tr_obj = $(this).closest('tr');

        var purchase_exc_tax = __read_number($(this));
        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
        __write_number(tr_obj.find('input.variable_dpp_inc_tax'), purchase_inc_tax);

        var profit_percent = __read_number(tr_obj.find('input.variable_profit_percent'));
        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number(tr_obj.find('input.variable_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });

    //If purchase price inc tax is changed
    $(document).on('change', 'input.variable_dpp_inc_tax', function(e) {
        var tr_obj = $(this).closest('tr');

        var purchase_inc_tax = __read_number($(this));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_exc_tax = __get_principle(purchase_inc_tax, tax_rate);
        __write_number(tr_obj.find('input.variable_dpp'), purchase_exc_tax);

        var profit_percent = __read_number(tr_obj.find('input.variable_profit_percent'));
        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number(tr_obj.find('input.variable_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input.variable_profit_percent', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;
        var tr_obj = $(this).closest('tr');
        var profit_percent = __read_number($(this));

        var purchase_exc_tax = __read_number(tr_obj.find('input.variable_dpp'));
        var purchase_exc_tax_one = __read_number(tr_obj.find('input.variable_dpp_inc_tax'));

        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;
        if(purchase_exc_tax_one != undefined && purchase_exc_tax_one > 0){
            purchase_exc_tax = purchase_exc_tax_one == undefined ? 0 : purchase_exc_tax_one;
        }


        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number(tr_obj.find('input.variable_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input.variable_dsp', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var tr_obj = $(this).closest('tr');
        var selling_price = __read_number($(this));
        var purchase_exc_tax = __read_number(tr_obj.find('input.variable_dpp'));

        var profit_percent = __get_rate(purchase_exc_tax, selling_price);
        __write_number(tr_obj.find('input.variable_profit_percent'), profit_percent);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input.variable_dsp_inc_tax', function(e) {
        var tr_obj = $(this).closest('tr');
        var selling_price_inc_tax = __read_number($(this));

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var selling_price = __get_principle(selling_price_inc_tax, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp'), selling_price);

        var purchase_exc_tax = __read_number(tr_obj.find('input.variable_dpp'));
        var profit_percent = __get_rate(purchase_exc_tax, selling_price);
        __write_number(tr_obj.find('input.variable_profit_percent'), profit_percent);
    });

    $(document).on('click', '.add_variation_value_row', function() {
        var variation_row_index = $(this)
            .closest('.variation_row')
            .find('.row_index')
            .val();
        var variation_value_row_index = $(this)
            .closest('table')
            .find('tr:last .variation_row_index')
            .val();

        if (
            $(this)
            .closest('.variation_row')
            .find('.row_edit').length >= 1
        ) {
            var row_type = 'edit';
        } else {
            var row_type = 'add';
        }

        var table = $(this).closest('table');

        $.ajax({
            method: 'GET',
            url: '/products/get_variation_value_row',
            data: {
                variation_row_index: variation_row_index,
                value_index: variation_value_row_index,
                row_type: row_type,
            },
            dataType: 'html',
            success: function(result) {
                if (result) {
                    table.append(result);
                    toggle_dsp_input();
                }
            },
        });
    });

    // $(document).on('change', '.variation_template', function() {
    //     tr_obj = $(this).closest('tr');

    //     if ($(this).val() !== '') {
    //         tr_obj.find('input.variation_name').val(
    //             $(this)
    //             .find('option:selected')
    //             .text()
    //         );

    //         var template_id = $(this).val();
    //         var row_index = $(this)
    //             .closest('tr')
    //             .find('.row_index')
    //             .val();
    //         $.ajax({
    //             method: 'POST',
    //             url: '/products/get_variation_template',
    //             dataType: 'html',
    //             data: { template_id: template_id, row_index: row_index },
    //             success: function(result) {
    //                 if (result) {
    //                     tr_obj
    //                         .find('table.variation_value_table')
    //                         .find('tbody')
    //                         .html(result);
    //                     toggle_dsp_input();
    //                 }
    //             },
    //         });
    //     }
    // });

    $(document).on('click', '.remove_variation_value_row', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var count = $(this)
                    .closest('table')
                    .find('.remove_variation_value_row').length;
                if (count === 1) {
                    $(this)
                        .closest('.variation_row')
                        .remove();
                } else {
                    $(this)
                        .closest('tr')
                        .remove();
                }
            }
        });
    });

    //If tax rate is changed
    $(document).on('change', 'select#tax', function() {
        if ($('select#type').val() == 'variable') {
            var tax_rate = $('select#tax')
                .find(':selected')
                .data('rate');
            tax_rate = tax_rate == undefined ? 0 : tax_rate;

            $('table.variation_value_table > tbody').each(function() {
                $(this)
                    .find('tr')
                    .each(function() {
                        var purchase_exc_tax = __read_number($(this).find('input.variable_dpp'));
                        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

                        var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
                        __write_number(
                            $(this).find('input.variable_dpp_inc_tax'),
                            purchase_inc_tax
                        );

                        var selling_price = __read_number($(this).find('input.variable_dsp'));
                        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
                        __write_number(
                            $(this).find('input.variable_dsp_inc_tax'),
                            selling_price_inc_tax
                        );
                    });
            });
        }
    });
    //End for product type Variable
    $(document).on('change', '#tax_type', function(e) {
        toggle_dsp_input();
    });
    toggle_dsp_input();

    $(document).on('change', '#expiry_period_type', function(e) {
        if ($(this).val()) {
            $('input#expiry_period').prop('disabled', false);
        } else {
            $('input#expiry_period').val('');
            $('input#expiry_period').prop('disabled', true);
        }
    });

    $(document).on('click', 'a.view-product', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'html',
            success: function(result) {
                $('#view_product_modal')
                    .html(result)
                    .modal('show');
                __currency_convert_recursively($('#view_product_modal'));
            },
        });
    });
    var img_fileinput_setting = {
        showUpload: false,
        showPreview: true,
        browseLabel: LANG.file_browse_label,
        removeLabel: LANG.remove,
        previewSettings: {
            image: { width: 'auto', height: 'auto', 'max-width': '100%', 'max-height': '100%' },
        },
    };
    $('#upload_image').fileinput(img_fileinput_setting);
    $('#upload_main_image').fileinput(img_fileinput_setting);

    if ($('textarea#product_description').length > 0) {
        tinymce.init({
            selector: 'textarea#product_description',
            height: 250
        });
    }
});

function toggle_dsp_input() {
    var tax_type = $('#tax_type').val();
    if (tax_type == 'inclusive') {
        $('.dsp_label').each(function() {
            $(this).text(LANG.inc_tax);
        });
        $('#single_dsp').addClass('hide');
        $('#single_dsp_inc_tax').removeClass('hide');

        $('.add-product-price-table')
            .find('.variable_dsp_inc_tax')
            .each(function() {
                $(this).removeClass('hide');
            });
        $('.add-product-price-table')
            .find('.variable_dsp')
            .each(function() {
                $(this).addClass('hide');
            });
    } else if (tax_type == 'exclusive') {
        $('.dsp_label').each(function() {
            $(this).text(LANG.exc_tax);
        });
        $('#single_dsp').removeClass('hide');
        $('#single_dsp_inc_tax').addClass('hide');

        $('.add-product-price-table')
            .find('.variable_dsp_inc_tax')
            .each(function() {
                $(this).addClass('hide');
            });
        $('.add-product-price-table')
            .find('.variable_dsp')
            .each(function() {
                $(this).removeClass('hide');
            });
    }
}

function get_product_details(rowData) {
    var div = $('<div/>')
        .addClass('loading')
        .text('Loading...');

    $.ajax({
        url: '/products/' + rowData.id,
        dataType: 'html',
        success: function(data) {
            div.html(data).removeClass('loading');
        },
    });

    return div;
}

//Quick add unit
$(document).on('submit', 'form#quick_add_unit_form', function(e) {
    e.preventDefault();
    $(this)
        .find('button[type="submit"]')
        .attr('disabled', true);
    var data = $(this).serialize();

    $.ajax({
        method: 'POST',
        url: $(this).attr('action'),
        dataType: 'json',
        data: data,
        success: function(result) {
            if (result.success == true) {
                var newOption = new Option(result.data.short_name, result.data.id, true, true);
                // Append it to the select
                $('#unit_id')
                    .append(newOption)
                    .trigger('change');
                $('div.view_modal').modal('hide');
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
});

//Quick add brand
$(document).on('submit', 'form#quick_add_brand_form', function(e) {
    e.preventDefault();
    $(this)
        .find('button[type="submit"]')
        .attr('disabled', true);
    var data = $(this).serialize();

    $.ajax({
        method: 'POST',
        url: $(this).attr('action'),
        dataType: 'json',
        data: data,
        success: function(result) {
            if (result.success == true) {
                var newOption = new Option(result.data.name, result.data.id, true, true);
                // Append it to the select
                $('#brand_id')
                    .append(newOption)
                    .trigger('change');
                $('div.view_modal').modal('hide');
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
});

$(document).on('click', '.delete-media', function() {
    swal({
        title: LANG.sure,
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then(willDelete => {
        if (willDelete) {
            var url = $(this).data('href');
            var thumbnail = $(this).closest('.img-thumbnail');
            $.ajax({
                url: url,
                dataType: 'json',
                success: function(result) {
                    if (result.success == true) {
                        thumbnail.remove();
                        toastr.success(result.msg);
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        }
    });
});

$(document).on('click', 'button.apply-all', function() {
    var val = $(this).closest('.input-group').find('input').val();
    var target_class = $(this).data('target-class');
    $(this).closest('tbody').find('tr').each(function() {
        element = $(this).find(target_class);
        element.val(val);
        element.change();
    });
});

$(document).on('click','.close',function(){
    $(".preview_image").css("display","none");
//    var product_id = $("#product_id").val();
//     swal({
//         title: LANG.sure,
//         icon: 'warning',
//         buttons: true,
//         dangerMode: true,
//     }).then(willDelete => {
//         if (willDelete) {
//             $.ajax({
//                 method: 'POST',
//                 url: '/products/remove-main-item-image',
//                 data:{
//                     product_id: product_id,
//                 },
//                 dataType: 'html',
//                 success: function(data) {
//                     $(".preview_image").css("display","none");
//                     // div.html(data).removeClass('loading');
//                 },
//             });
//         }
//     })
})

$(document).on('click','.update',function(){
    var product_id = $("#product_id").val();

    if( $('.preview_image').css('display') == 'none' ) {
        $.ajax({
            method: 'POST',
            url: '/products/remove-main-item-image',
            data:{
                product_id: product_id,
            },
            dataType: 'html',
            success: function(data) {
                console.log('success');
            },
        });
    }
    // alert(product_id);
});