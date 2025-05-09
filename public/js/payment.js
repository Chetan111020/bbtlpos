$(document).ready(function() {
    $(document).on('click', '.add_payment_modal', function(e) {
        e.preventDefault();
        var container = $('.payment_modal');

        $.ajax({
            url: $(this).attr('href'),
            dataType: 'json',
            success: function(result) {
                if (result.status == 'due') {
                    container.html(result.view).modal('show');
                    __currency_convert_recursively(container);
                    $('#paid_on').datetimepicker({
                        format: moment_date_format + ' ' + moment_time_format,
                        ignoreReadonly: true,
                    });
                    container.find('form#transaction_payment_add_form').validate();
                    set_default_payment_account();

                    $('.payment_modal')
                        .find('input[type="checkbox"].input-icheck')
                        .each(function() {
                            $(this).iCheck({
                                checkboxClass: 'icheckbox_square-blue',
                                radioClass: 'iradio_square-blue',
                            });
                        });
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });
    $(document).on('click', '.edit_payment', function(e) {
        e.preventDefault();
        var container = $('.edit_payment_modal');

        $.ajax({
            url: $(this).data('href'),
            dataType: 'html',
            success: function(result) {
                container.html(result).modal('show');
                $('div.pay_contact_due_modal').modal('hide');	
                $('div.pay_due_modal').modal('hide');	
                __currency_convert_recursively(container);
                $('#paid_on').datetimepicker({
                    format: moment_date_format + ' ' + moment_time_format,
                    ignoreReadonly: true,
                });
                container.find('form#transaction_payment_add_form').validate();
            },
        });
    });

    $(document).on('click', '.view_payment_modal', function(e) {
        e.preventDefault();
        var container = $('.payment_modal');

        $.ajax({
            url: $(this).attr('href'),
            dataType: 'html',
            success: function(result) {
                $(container)
                    .html(result)
                    .modal('show');
                __currency_convert_recursively(container);
            },
        });
    });
    /*$(document).on('click', '.delete_payment', function(e) {
        swal({
            title: LANG.sure,
            text: LANG.confirm_delete_payment,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                $.ajax({
                    url: $(this).data('href'),
                    method: 'delete',
                    dataType: 'json',
                    success: function(result) {
                        if (result.success === true) {
                            $('div.payment_modal').modal('hide');
                            $('div.edit_payment_modal').modal('hide');
                            toastr.success(result.msg);
                             get_contact_payments();
                            if (typeof purchase_table != 'undefined') {
                                purchase_table.ajax.reload();
                            }
                            if (typeof sell_table != 'undefined') {
                                sell_table.ajax.reload();
                            }
                            if (typeof expense_table != 'undefined') {
                                expense_table.ajax.reload();
                            }
                            if (typeof ob_payment_table != 'undefined') {
                                ob_payment_table.ajax.reload();
                            }
                            // project Module
                            if (typeof project_invoice_datatable != 'undefined') {
                                project_invoice_datatable.ajax.reload();
                            }
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });*/
    
    //added by developer1 for delete payments
    $(document).on('click', '.delete_payment', function(e) {
        $(document).off('focusin.modal');
        e.preventDefault();

        href = $(this).data('href');
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
                        var data = { reason: content , url: href};
                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            data: data,
                            dataType: 'json',
                            success: function(result) {
                                if (result.success === true) {
                                    $('div.payment_modal').modal('hide');
                                    $('div.edit_payment_modal').modal('hide');
                                    toastr.success(result.msg);
                                    if($('#contact_payments_div').length)
                                    {
                                        get_contact_payments();
                                    }
                                    if (typeof purchase_table != 'undefined') {
                                        purchase_table.ajax.reload();
                                    }
                                    if (typeof sell_table != 'undefined') {
                                        sell_table.ajax.reload();
                                    }
                                    if (typeof expense_table != 'undefined') {
                                        expense_table.ajax.reload();
                                    }
                                    if (typeof ob_payment_table != 'undefined') {
                                        ob_payment_table.ajax.reload();
                                    }
                                    // project Module
                                    if (typeof project_invoice_datatable != 'undefined') {
                                        project_invoice_datatable.ajax.reload();
                                    }
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
                        //alert(is_suspended);
                        alert('Please enter valid reason');
                        swal.stopLoading();
                        trick();
                    }
                }
            })
        })();
    });

    //view single payment
    $(document).on('click', '.view_payment', function() {
        var url = $(this).data('href');
        var container = $('.view_modal');
        $.ajax({
            method: 'GET',
            url: url,
            dataType: 'html',
            success: function(result) {
                $(container)
                    .html(result)
                    .modal('show');
                __currency_convert_recursively(container);
            },
        });
    });
});

$(document).on('change', '#transaction_payment_add_form .payment_types_dropdown', function(e) {
    set_default_payment_account();
});

function set_default_payment_account() {
    var default_accounts = {};

    if (!_.isUndefined($('#transaction_payment_add_form #default_payment_accounts').val())) {
        default_accounts = JSON.parse($('#transaction_payment_add_form #default_payment_accounts').val());
    }

    var payment_type = $('#transaction_payment_add_form .payment_types_dropdown').val();
    if (payment_type && payment_type != 'advance') {
        var default_account = !_.isEmpty(default_accounts) && default_accounts[payment_type]['account'] ? 
            default_accounts[payment_type]['account'] : '';
        $('#transaction_payment_add_form #account_id').val(default_account);
        $('#transaction_payment_add_form #account_id').change();
    }
}

$(document).on('change', '.payment_types_dropdown', function(e) {
    var payment_type = $('#transaction_payment_add_form .payment_types_dropdown').val();
    account_dropdown = $('#transaction_payment_add_form #account_id');
    if (payment_type == 'advance') {
        if (account_dropdown) {
            account_dropdown.prop('disabled', true);
            account_dropdown.closest('.form-group').addClass('hide');
        }
    } else {
        if (account_dropdown) {
            account_dropdown.prop('disabled', false); 
            account_dropdown.closest('.form-group').removeClass('hide');
        }    
    }
});
$(document).on('change', '#discount_type', function(){	
     $('#discount_amount').trigger('keyup');	
})
$(document).on('keyup', '#discount_amount', function(){
    $(".error").text('');
    $(':input[type="submit"]').prop('disabled', false);
   var amount = $("#amount").val();
   amount = amount.replace(",","");
   var bill_amt = $("#bill_amt").val();
   var discount_type = $("#discount_type option:selected").val();
   var discount_amount = $("#discount_amount").val();
   var disc_amt = '';
   $('#error_count').val(0);
   if(discount_type == 'fixed'){
        disc_amt = (bill_amt - amount).toFixed(2) ;
        if(parseFloat(discount_amount) > parseFloat(disc_amt)){
          $(".error").text('Max discount:'+disc_amt);
          $('#error_count').val(1);
          // $("#discount_amount").val('');
        }
        $("#discount_total").val(disc_amt);
        $("#total_discount").text(discount_amount);    
   }
   if(discount_type == 'percentage'){
        percentage_amt = (amount * discount_amount) /100;
        disc_amt =  (bill_amt - amount).toFixed(2) ;  
        if(parseFloat(percentage_amt) > parseFloat(disc_amt)){
            $(".error").text('Max discount:'+disc_amt);
            $('#error_count').val(1);
            // $("#discount_amount").val('');
        }
        $("#discount_total").val(disc_amt);
        $("#total_discount").text(percentage_amt);  
   }
       
});


$(document).on('blur','#amount',function(){

        var bill_amount = $("#bill_amt").val();
        var amount = $(this).val();
        var diff_amount = (bill_amount - amount).toFixed(2);
         $('.amt_warning').html('');
        $('.amt_warning').html('Diff amount :' + diff_amount + '<br> Due amount :' +bill_amount);
        // if(bill_amount >  amount){
        //     $('.amt_warning').text('Due amount is '+bill_amount); 
        // }
         $("#discount_amount").keyup();
   });

//Show Difference Instead of Due Amount
 $(document).on('keyup','.amount', function(){

    $(this).removeAttr('data-rule-max-value');
    var bill_amount = $("#bill_amt").val();
    var amount = $(this).val();
    var diff_amount = (bill_amount - amount).toFixed(2);
        $('.amt_warning').html('');
        $('.amt_warning').html('Diff amount :' + diff_amount + '<br> Due amount :' +bill_amount);
    
 });


