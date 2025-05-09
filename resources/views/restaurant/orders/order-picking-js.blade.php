@section('scripts')
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script>
        function editPickingQty(id) {
            let previousQty = document.getElementById('pickingQtyInputBox').innerText;
            let qtyBox = '<input id="updatedPickedQty" data-content="' + id +
                '" onkeypress="getProductQty(event, id)" onInput="pickingUpdatedQty()" style="width:60px;" type="text" value="' +
                previousQty + '" name="qty">';
            document.getElementById('pickingQtyInputBox').innerHTML = qtyBox;
        }

        $(document).on("click", "#pickedQty", function(e) {
            e.preventDefault();
            var qty = parseInt($("#pickingQtyInputBox").html());
            var qty_on_hand = parseInt($("#pickingQtyonHand").html());
            if (qty > qty_on_hand) {
                alert("qty is higher than qty on hand.");
            } else {
                $('form#picked_add_form').submit();
            }

        });
        // $(document).on("click", "#pickingItem", function(e) {
        //     var audio = $('#success-audio')[0];
        //     if (audio !== undefined) {
        //         audio.play();
        //     }
        // });
        $(document).ready(function() {
            let currentUrl = window.location.pathname.split('/');
            let order_id = currentUrl.filter(Boolean).pop();
            reloadDataInPage(order_id);
        });
        $(document).on("click", "#pickingItem", function(e) {
            e.preventDefault(); // prevent default form submission

            var audio = $('#success-audio')[0];
            if (audio !== undefined) {
                audio.play();
            }
            let currentUrl = window.location.pathname.split('/');
            let order_id = currentUrl.filter(Boolean).pop();

            let form = $('#picked_add_form');
            let url = form.attr('action');
            let product_id = url.split('/').pop();

            let data = {
                updatedPickedQty: $('#pickedQty').val(),
                _token: '{{ csrf_token() }}' // include CSRF token
            };

            $.ajax({
                url: url,
                method: 'GET',
                data: data,
                success: function(response) {
                    reloadDataInPage(order_id);
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        });


        $(document).on("click", ".editedBtn", function(e) {
            var qty = parseInt($("#pickingQtyInputBox").html());
            var qty_on_hand = parseInt($("#pickingQtyonHand").html());
            //if(parseInt(qty) < parseInt(qty_on_hand)){
            var r = confirm("Are you sure this product is out of Stock? We have " + qty_on_hand + " on hand");
            if (r == false) {
                e.preventDefault();
            }
            // }else{
            //     e.preventDefault();
            // }
        });

        $(document).on("click", ".editedBtnOne", function(e) {
            var qty = parseInt($("#pickingQtyInputBox").html());
            var qty_on_hand = parseInt($(this).attr('data-stock'));
            var r = confirm("Are you sure this product is out of Stock? We have " + qty_on_hand + " on hand");
            if (r == false) {
                e.preventDefault();
            }
        });

        $(document).on("click", ".leave-page", function(e) {
            var r = confirm("Are you sure you want to leave this page");
            if (r == false) {
                e.preventDefault();
            }
        });

        async function reloadDataInPage(order_id = '') {
            if (!order_id || order_id == '') {
                let currentUrl = window.location.pathname.split('/');
                order_id = currentUrl.filter(Boolean).pop();
            }
            let orderData = await getOrderData(order_id);
            createHtmlCode(orderData);
        }

        function createHtmlCode(orderData) {
            emptyNeccessaryData();
            let productDetailDiv = createProductDetailDiv(orderData);
            let tableHtml = createHtmlForTable(orderData);
            let pickedTabData = createHtmlForPickedTabData(orderData.pickedProducts);
            let outOfStockTabData = createHtmlForOutOfStockTabData(orderData.outOfStockProducts);
            let editedTabData = createHtmlForEditedTabData(orderData.editedProducts);
            let incorrectLocationTabData = createHtmlForIncorrectLocationTabData(orderData.incorrectLocationProducts);
            let totalPickedMissedData = createHtmlForPickedMissedData(orderData);
            $('#productDetailDivContainer').html(productDetailDiv);
            $('#productTableBody').html(tableHtml);
            $('#picked tbody').html(pickedTabData);
            $('#not_there tbody').html(outOfStockTabData);
            $('#edited tbody').html(editedTabData);
            $('#location_incorrect tbody').html(incorrectLocationTabData);
            $('#pickedMissedDiv').html(totalPickedMissedData);
            // Update the counts in tab titles
            $('.pickedTab button').html(`Picked (${orderData.pickedProducts.length})`);
            $('.notThereTab button').html(`Out of Stock (${orderData.outOfStockProducts.length})`);
            $('.editedTab button').html(`Edited (${orderData.editedProducts.length})`);
            $('.locationIncorrectTab button').html(`Location Incorrect (${orderData.incorrectLocationProducts.length})`);
        }

        function createHtmlForPickedMissedData(data) {
            return `
            <div class="d-flex justify-content-between">
                <div class="col-md-2">
                    
                        <p>Selected Item</p>
                    
                    <button type="submit" class="btn btn-sm form-control pickedBtn" id="pick-check-selected" style="width: 100px; height: 30px">
                        <i class="fa fa-check"></i> Pick
                    </button>
                </div>
                <div class="col-md-5">
                    <p class="p-0 m-0">
                        <b class="m-0 p-0">Total Picked Items:</b> ${data.pickedProductsCount}
                    </p>
                    <p class="p-0 m-0">
                        <b class="m-0 p-0">Total Missed Items:</b> ${data.outOfStockProductsCount}
                    </p>
                </div>
                </div>
            `;
        }


        function createHtmlForPickedTabData(data) {
            return data.map(item => `
        <tr id="table-row-${item.id}">
            <td>${item.product_name}</td>
            <td><img src="/uploads${item.image}" class="img img-responsive" width="75px"></td>
            <td>A:${item.aisle} R:${item.rack} S:${item.shelf} B:${item.bin}</td>
            <td>${item.sku}</td>
            <td>${Math.round(item.quantity)}</td>
            <td>${Math.round(item.stock)}</td>
            <td>${item.picking_started_time ? formatDateTime(item.picking_started_time) : ''}</td>
            <td>${item.picking_completed_time ? formatDateTime(item.picking_completed_time) : ''}</td>
            <td><button onclick="undoPicking(${item.id})" class="btn btn-sm btn-primary"><i class="fa fa-undo"></i> Undo</button></td>
        </tr>
    `).join('');
        }

        function createHtmlForOutOfStockTabData(data) {
            return data.map(item => `
        <tr>
            <td>${item.product_name} [${item.item_code ?? ''}]</td>
            <td><img src="/uploads${item.image}" class="img img-responsive" width="75px"></td>
            <td>A:${item.aisle} R:${item.rack} S:${item.shelf} B:${item.bin}</td>
            <td>${item.sku}</td>
            <td>${Math.round(item.quantity)}</td>
            <td>${Math.round(item.stock)}</td>
            <td><button onclick="undoOutOfStock(${item.id})" class="btn btn-sm btn-primary"><i class="fa fa-undo"></i> Undo</button></td>
        </tr>
    `).join('');
        }

        function createHtmlForEditedTabData(data) {
            return data.map(item => `
        <tr>
            <td>${item.product_name} [${item.item_code ?? ''}]</td>
            <td><img src="/uploads${item.image}" class="img img-responsive" width="75px"></td>
            <td>A:${item.aisle} R:${item.rack} S:${item.shelf} B:${item.bin}</td>
            <td>${item.sku}</td>
            <td>${Math.round(item.quantity)}</td>
            <td>${Math.round(item.stock)}</td>
        </tr>
    `).join('');
        }

        function createHtmlForIncorrectLocationTabData(data) {
            return data.map(item => `
        <tr>
            <td>${item.product_name} [${item.item_code ?? ''}]</td>
            <td><img src="/uploads${item.image}" class="img img-responsive" width="75px"></td>
            <td>A:${item.aisle} R:${item.rack} S:${item.shelf} B:${item.bin}</td>
            <td>${item.sku}</td>
            <td>${Math.round(item.quantity)}</td>
            <td>${Math.round(item.stock)}</td>
        </tr>
    `).join('');
        }

        function formatDateTime(datetime) {
            const date = new Date(datetime);
            const formattedDate = date.toLocaleDateString();
            const formattedTime = date.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
            return `${formattedDate} ${formattedTime}`;
        }

        function emptyNeccessaryData() {
            $('#productDetailDivContainer').html('');
            $('#productTableBody').html('');
            $('#picked tbody').html('');
            $('#not_there tbody').html('');
            $('#edited tbody').html('');
            $('#location_incorrect tbody').html('');
            $('#pickedMissedDiv').html('');
        }

        function getOrderStatusText(status) {
            switch (status) {
                case 'ask_for_payment_before_ship':
                    return 'Ask For Payment Before Shipping';
                case 'ok_to_ship':
                    return 'Okay to Deliver/Ship (Payment Confirmed)';
                default:
                    return 'Ask In The Office';
            }
        }

        function getContactData(contact) {
            return `
                <div style="border: 1px solid #dee2e6; border-radius: 10px; padding: 15px; box-shadow: 2px 2px 8px rgba(0,0,0,0.1);">
                    <div class="row mb-1">
                        <div class="col-md-4">
                            <strong>Order No:</strong>#${contact.invoice_no || 'N/A'}
                        </div>
                        <div class="col-md-4">
                            <strong>Order By:</strong> ${contact.first_name || 'N/A'}
                        </div>
                        <div class="col-md-4">
                            <strong>Customer:</strong> ${contact.customer_name || 'N/A'}
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-4">
                            <strong>Company:</strong> ${contact.company_name || 'N/A'}
                        </div>
                        <div class="col-md-4">
                        <strong>Note:</strong> ${contact.additional_notes || 'None'}
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-flag"></i> Status: <b>${getOrderStatusText(contact.p_status)}</b></h6>
                        </div>
                    </div>
                    
            </div>`;
        }

        function createProductDetailDiv(orderData) {
            let contact = orderData.contacts;
            let contactBlock = getContactData(contact);

            let orderProduct = orderData.orderProduct;
            if (!orderProduct || orderProduct.length === 0) {
                return `<h2 style="color:red;">All Items have been picked. Please finalize the picking.</h2>`;
            }

            let product = orderProduct;
            let imageSrc = product.image ?
                `/uploads${product.image}` :
                '/assets/static/data-not-found.png';

            let qty = product.edit_quantity != 0.00 ? Math.round(product.edit_quantity) : Math.round(product.quantity);
            let total = (product.unit_price * qty).toFixed(2);

            let location = {
                aisle: product.aisle ?? 0,
                rack: product.rack ?? 0,
                shelf: product.shelf ?? 0,
                bin: product.bin ?? 0
            };

            return `
            <div style="margin: 5px;">
                                    ${contactBlock}
                                </div>
                    <div class="row d-flex justify-content-between">
                        <div class="col-md-4 d-flex flex-column" style="min-height: 300px;">
                            <div style="border: 3px solid rgb(225 29 72 / var(--tw-border-opacity, 1)); border-radius: 12px; flex-grow: 1;" class="d-flex justify-content-between">
                                <div style="margin: 5px;">
                                    <div class="row">
                                        <div class="col-md-12 text-center">
                                            <h6 style="font-weight: 600;">${product.product_name || ''}</h6>
                                        </div>
                                    </div>

                                    <div class="row justify-content-center my-2">
                                        <img src="${imageSrc}" class="img-fluid rounded" style="width: 70%; height: auto; max-height: 175px;">
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <div style="border-radius: 8px; overflow: hidden;">
                                                <table class="table table-bordered table-sm mb-0" style="font-size: 11px; text-align: center; background-color: #b9b9e1;">
                                                    <tr>
                                                        <td><strong>Aisle:</strong> <span>${location.aisle}</span></td>
                                                        <td><strong>Rack:</strong> <span>${location.rack}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Shelf:</strong> <span>${location.shelf}</span></td>
                                                        <td><strong>Bin:</strong> <span>${location.bin}</span></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 d-flex flex-column justify-content-between" style="min-height: 300px;">
                            <div style="border: 3px solid rgb(225 29 72 / var(--tw-border-opacity, 1)); border-radius: 12px; flex-grow: 1;">
                                <div style="margin: 5px;">
                                    <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 10px; padding: 15px; box-shadow: 2px 2px 8px rgba(0,0,0,0.1); min-height:100%; min-height: 300px;">
                                        <div class="row mb-1">
                                            <div class="col-md-12">
                                                <h6 class="text-success">
                                                    <i class="fas fa-dollar-sign"></i> Unit Price: 
                                                    <span>$${parseFloat(product.unit_price).toFixed(2)}</span>
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-md-12">
                                                <h6>
                                                    <i class="fas fa-sort-numeric-up"></i> Quantity: 
                                                    <span id="pickingQtyInputBox" data-product="${product.id}">${qty}</span>
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-md-12">
                                                <h6 class="text-success">
                                                    <i class="fas fa-calculator"></i> Total: 
                                                    <span>$${total}</span>
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-md-12">
                                                <h6>
                                                    <i class="fas fa-boxes"></i> Current Stock: 
                                                    <span id="pickingQtyonHand" style="color: ${product.stock < qty ? 'red' : 'green'};">
                                                        ${Math.round(product.stock)}
                                                    </span>
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-md-12">
                                                <h6>
                                                    <i class="fas fa-barcode"></i> Barcode: ${product.sku || ''}
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6>
                                                    <i class="fas fa-sticky-note"></i> Note: ${product.sell_line_note || ''}
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                     <div class="col-md-4 d-flex flex-column" style="min-height: 300px;">
                        <div style="border: 3px solid rgb(225 29 72 / var(--tw-border-opacity, 1)); border-radius: 12px; flex-grow: 1; padding-left:10px;">
                            <div id="orderPackingOptions" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%; padding: 10px;">
                                
                                <div class="row">
                                    <div class="col-md-12 m-1" style="width:95%;">
                                        <form id="picked_add_form" method="GET" action="/modules/api/kitchen/pick/product/${product.id}">
                                            <input id="pickedQty" type="hidden" name="updatedPickedQty" value="">
                                            <button type="submit" id="pickingItem" class="btn btn-lg pickedBtn form-control">Picked</button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12 m-1" style="width:95%;">
                                        <button type="button" onclick="markOutOfStock(${product.id})"
                                            class="btn btn-lg editedBtn form-control">Out Of Stock</button>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12 m-1" style="width:95%;">
                                        <button onclick="editPickingQty(${product.id})" type="button"
                                            class="btn btn-lg notThereBtn form-control">Edit Quantity</button>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12 m-1" style="width:95%;">
                                        <button onclick="markLocationMisMatch(${product.id})" 
                                            class="btn btn-lg locationIncorrectBtn form-control">
                                            Incorrect Location
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

            
                    `;
        }



        function createHtmlForTable(orderData) {
            let html = '';

            orderData.orderdetails.forEach((item, index) => {
                let qty = item.edit_quantity != 0.00 ? Math.round(item.edit_quantity) : Math.round(item.quantity);
                let total = (item.unit_price * qty).toFixed(2);
                let location = `A:${item.aisle} R:${item.rack} S:${item.shelf} B:${item.bin}`;

                html += `
                <tr id="raw-${index}">
                    <td><input type="checkbox" class="row-select" value="${item.id}"></td>
                    <td style="min-width: 180px; font-size: 12px;">
                        <p>${item.product_name} [${item.item_code ?? ''}]</p>
                    </td>
                    <td>
                        <img src="{{ !empty($item->image) ? '/uploads' . $item->image : '/assets/static/data-not-found.png' }}" class="img img-responsive" width="75px">
                    </td>

                    <td>${location}</td>
                    <td>${item.sku}</td>
                    <td>$${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td style="background: yellow; font-weight: 900;">
                        <span id="producQty_${item.id}" data-product="${item.id}">${qty}</span>
                    </td>
                    <td>$${total}</td>
                    <td>${Math.round(item.stock)}</td>
                    <td>
                        <button onclick="editProductQty(${item.id})" class="btn btn-sm btn-primary">
                     Edit
                        </button>
                    </td>
                    <td>
                        <form action="/modules/restaurant/kitchen/pick/product/${item.id}">
                            <input type="hidden" name="updatedPickedQty" value="">
                            <input type="hidden" name="raw" value="${index}">
                            <button type="submit" id="pickingItem" class="btn btn-sm pickedBtn form-control">Pick</button>
                        </form>
                    </td>
                    <td>
                        
                        <button 
                            onclick="markOutOfStock({{ $item->id }})" 
                            class="btn btn-sm btn-primary editedBtnOne" 
                            data-stock="{{ round($item->stock) }}">
                            Stockout
                        </button>

                    </td>
                </tr>
            `;
            });

            return html;
        }

        async function getOrderData(order_id) {
            try {
                let url = '/modules/api/kitchen/pick/order/' + order_id;

                const response = await $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        order_id: order_id,
                        _token: '{{ csrf_token() }}'
                    }
                });

                return response.data;
            } catch (error) {
                console.error('Fetch Error:', error);
                return [];
            }
        }

        function markOutOfStock(id) {
            $.ajax({
                url: "/modules/api/kitchen/product/out-of-stock/" + id,
                type: 'GET',
                success: function() {
                    reloadDataInPage();
                },
                error: function(xhr) {
                    console.error('Error marking out of stock:', xhr);
                    alert('Failed to mark product as out of stock.');
                }
            });
        }

        function pickingUpdatedQty() {
            let updatedPickedQty = document.getElementById("updatedPickedQty");
            let newQty = updatedPickedQty.value;
            let pickedQty = document.getElementById("pickedQty");
            pickedQty.value = newQty;
        }

        function editProductQty(id) {
            let p_qty_box = document.getElementById('producQty_' + id);
            let pre_qty = p_qty_box.innerText;
            p_qty_box.innerHTML = '<input data-content="' + id +
                '" onkeypress="getProductQty(event, id)" style="width:60px;" type="text" value="' + pre_qty +
                '" name="produc_qty">';
        }

        function markLocationMisMatch(id) {
            if (!confirm("Mark this product as having an incorrect location?")) return;

            $.ajax({
                url: "/modules/api/kitchen/product/incorrect-location/" + id,
                type: 'GET',
                success: function() {
                    reloadDataInPage();
                },
                error: function(xhr) {
                    console.error('Error marking location mismatch:', xhr);
                    alert('Failed to mark product location as incorrect.');
                }
            });
        }

        function getProductQty(e, id) {
            let key = e.keyCode || e.which;
            let p_qty = e.target.value;
            let p_id = e.target.getAttribute('data-content');
            if (key === 13) {
                $.ajax({
                    // url:'{{ action('Restaurant\KitchenController@updateProductQty') }}',
                    url: '{{ action('Restaurant\KitchenController@editProductQty') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        p_id: p_id,
                        p_qty: p_qty
                    },
                    success: function(data) {
                        // console.log('data:', data)
                        // location.reload();
                        reloadDataInPage();
                    }
                })
            }
        }

        $(document).on("blur", "#updatedPickedQty", function(e) {
            let p_qty = $(this).val();
            let p_id = $(this).attr('data-content');
            $.ajax({
                // url:'{{ action('Restaurant\KitchenController@updateProductQty') }}',
                url: '{{ action('Restaurant\KitchenController@editProductQty') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    p_id: p_id,
                    p_qty: p_qty
                },
                success: function(data) {
                    // console.log('data:', data);
                    // location.reload();
                    reloadDataInPage();
                }
            })
        });

        function undoPicking(id) {
            $.ajax({
                url: "/modules/kitchen/picking/undo/" + id,
                type: 'GET',
                data: null,
                success: function() {
                    reloadDataInPage();
                    // location.reload();
                }
            })
        }

        function undoOutOfStock(id) {
            $.ajax({
                url: "/modules/kitchen/outofstock/undo/" + id,
                type: 'GET',
                data: null,
                success: function() {
                    reloadDataInPage();
                    // location.reload();
                }
            })
        }

        $(document).ready(function() {
            @if (Session::has('raw'))
                window.location = "#raw-{{ Session::get('raw') }}";
            @endif
        });


        $(document).on('click', '#pick-check-selected', function(e) {
            e.preventDefault();
            var selected_rows = getSelectedRows();

            if (selected_rows.length > 0) {
                var result = confirm("Are you sure you want to pick selected products?");
                if (result) {
                    console.log('selected_rows:', selected_rows)
                    // var formData = {
                    //     selected_rows: selected_rows,
                    // };
                    $.ajax({
                        method: 'POST',
                        url: "{{ url('/modules/kitchen/selected/pick/product') }}",
                        data: {
                            selected_rows: selected_rows,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(success) {
                            console.log('success: ', success)
                            if (success == 1) {
                                var audio = $('#success-audio')[0];
                                if (audio !== undefined) {
                                    audio.play();
                                }
                                reloadDataInPage();
                                // location.reload();
                            } else {
                                var audio = $('#error-audio')[0];
                                if (audio !== undefined) {
                                    audio.play();
                                }
                                alert('undefined');
                            }
                        },
                        error: function(error) {
                            console.log('error:', error)
                        }
                    });
                }
            } else {
                $('input.row-select').val('');
                var audio = $('#error-audio')[0];
                if (audio !== undefined) {
                    audio.play();
                }
                alert('undefined');
                // swal('undefined');
            }
        })

        function getSelectedRows() {
            var selected_rows = [];
            var i = 0;
            $('.row-select:checked').each(function() {
                selected_rows[i++] = $(this).val();
            });
            return selected_rows;
        }

        $(document).on('click', '#select-all-rows', function(e) {
            if (this.checked) {
                $(this)
                    .closest('table')
                    .find('tbody')
                    .find('input.row-select')
                    .each(function() {
                        if (!this.checked) {
                            $(this)
                                .prop('checked', true)
                                .change();
                        }
                    });
            } else {
                $(this)
                    .closest('table')
                    .find('tbody')
                    .find('input.row-select')
                    .each(function() {
                        if (this.checked) {
                            $(this)
                                .prop('checked', false)
                                .change();
                        }
                    });
            }
        });
    </script>
@endsection
