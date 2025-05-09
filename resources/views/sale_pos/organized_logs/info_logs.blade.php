    <!-- START Order Information Logs 'Created' Table -->
          @if($infoLogs->where('neat_txn_id', $txnLog->id)->isNotEmpty() && $txnLog->activity_title == 'Created')
        @php
            // Get all the rows data
            $rows = [];
            foreach ($infoLogs->where('neat_txn_id', $txnLog->id) as $log) {
                if ($log->contact_id) {
                    $rows[] = ['Customer Name', optional($log->contact)->first_name];
                }
                if ($log->transaction_date) {
                    $rows[] = ['Transaction Date', $log->transaction_date ? \Carbon\Carbon::parse($log->transaction_date)->format('F j, Y h:i a') : ''];
                }
                if ($log->price_group !== null) {
                    $rows[] = ['Price Group', isset($price_group_names[$log->price_group]) ? $price_group_names[$log->price_group] : 'Default Selling Price'];
                }
                if ($log->order_status) {
                    $rows[] = ['Order Status', ucwords($log->order_status)];
                }
                if ($log->shipping_status) {
                    $rows[] = ['Shipping Status', ucwords($log->shipping_status)];
                }
                if ($log->delivery_method) {
                    $delivery_array_names = [
                        'posShippingModalUpdateDelivery' => 'Delivery',
                        'posShippingModalUpdatePickup' => 'Pickup',
                        'posShippingModalUpdateShipping' => 'Shipping',
                        'posShippingModalUpdatePallet' => 'Consignment',
                        'posShippingModalUpdateSelfNonPicking' => 'Walk In (Self)',
                        'posShippingModalUpdateSelf' => 'Walk In (Self)',
                        'posShippingModalUpdateDeliveryNonPicking' => 'Walk In (Delivery)',
                        'posShippingModalUpdateShippingNonPicking' => 'Walk In (Shipping)',
                    ];
                    $rows[] = ['Delivery Method', isset($delivery_array_names[$log->delivery_method]) ? $delivery_array_names[$log->delivery_method] : $log->delivery_method];
                }
                if ($log->shipping_charges) {
                    $rows[] = ['Shipping Charges', $log->shipping_charges !== null ? '$' . number_format($log->shipping_charges, 2) : ''];
                }
                if ($log->discount_amount) {
                    if ($log->discount_type == 'fixed') {
                        $discount = '$' . number_format($log->discount_amount, 2);
                    } elseif ($log->discount_type == 'percentage') {
                        $discount = number_format($log->discount_amount, 2) . '%';
                    } else {
                        $discount = '';
                    }
                    $rows[] = ['Discount', $discount];
                }
                if ($log->tax_status !== null) {
                    $rows[] = ['Tax Applicable', ucwords($log->tax_status == 'on' ? 'Yes' : 'No')];
                }
                if ($log->notpickpack !== null) {
                    $rows[] = ['Pick & Pack', $log->notpickpack == 1 ? 'No' : 'Yes'];
                }
                if ($log->sale_note) {
                    $rows[] = ['Sell Note ', ucwords($log->sale_note)];
                }
                if ($log->staff_note) {
                    $rows[] = ['Staff Note ', ucwords($log->staff_note)];
                }
                if ($log->final_total) {
                    $rows[] = ['Total Payable', $log->final_total !== null ? '$' . number_format($log->final_total, 2) : ''];
                }
            }
    
            // Determine the number of rows
            $rowCount = count($rows);
        @endphp
        <table class="table bg-white">
            <thead>
                <tr class="bg-blue">
                    <th>Info Title</th>
                    <th>Initial Info</th>
                    <th style="background:#008de3;">Info Title</th>
                    <th style="background:#008de3;">Initial Info</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < $rowCount; $i += 2)
                    <tr>
                        <td style="color:#0067c2;">{{ $rows[$i][0] ?? '' }}</td>
                        <td>{{ $rows[$i][1] ?? '' }}</td>
                        <td style="color:#0067c2;">{{ $rows[$i+1][0] ?? '' }}</td>
                        <td>{{ $rows[$i+1][1] ?? '' }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    @endif
    <!-- END Order Information Logs 'Created' Table -->





    <!-- START Order Information Logs 'Edited' Table -->
       @if($infoLogs->where('neat_txn_id', $txnLog->id)->isNotEmpty() && $txnLog->activity_title != 'Created')
    @php
        // Collect all row data
        $rows = [];
        foreach ($infoLogs->where('neat_txn_id', $txnLog->id) as $log) {
            if ($log->contact_id || $log->new_contact_id) {
                $rows[] = ['Customer Name', optional($log->contact)->first_name, optional($log->newcontact)->first_name];
            }
            if ($log->transaction_date || $log->new_transaction_date) {
                $rows[] = [
                    'Transaction Date',
                    $log->transaction_date ? \Carbon\Carbon::parse($log->transaction_date)->format('F j, Y h:i a') : '',
                    $log->new_transaction_date ? \Carbon\Carbon::parse($log->new_transaction_date)->format('F j, Y h:i a') : '',
                ];
            }
            if ($log->price_group !== null || $log->new_price_group !== null) {
                $rows[] = [
                    'Price Group',
                    isset($price_group_names[$log->price_group]) ? $price_group_names[$log->price_group] : 'Default Selling Price',
                    isset($price_group_names[$log->new_price_group]) ? $price_group_names[$log->new_price_group] : 'Default Selling Price',
                ];
            }
            if ($log->order_status || $log->new_order_status) {
                $rows[] = ['Order Status', ucwords($log->order_status), ucwords($log->new_order_status)];
            }
            if ($log->shipping_status || $log->new_shipping_status) {
                $rows[] = ['Shipping Status', ucwords($log->shipping_status), ucwords($log->new_shipping_status)];
            }
            if ($log->delivery_method || $log->new_delivery_method) {
                $delivery_array_names = [
                    'posShippingModalUpdateDelivery' => 'Delivery',
                    'posShippingModalUpdatePickup' => 'Pickup',
                    'posShippingModalUpdateShipping' => 'Shipping',
                    'posShippingModalUpdatePallet' => 'Consignment',
                    'posShippingModalUpdateSelfNonPicking' => 'Walk In (Self)',
                    'posShippingModalUpdateSelf' => 'Walk In (Self)',
                    'posShippingModalUpdateDeliveryNonPicking' => 'Walk In (Delivery)',
                    'posShippingModalUpdateShippingNonPicking' => 'Walk In (Shipping)',
                ];
                $rows[] = [
                    'Delivery Method',
                    isset($delivery_array_names[$log->delivery_method]) ? $delivery_array_names[$log->delivery_method] : $log->delivery_method,
                    isset($delivery_array_names[$log->new_delivery_method]) ? $delivery_array_names[$log->new_delivery_method] : $log->new_delivery_method,
                ];
            }
            if ($log->shipping_charges || $log->new_shipping_charges) {
                $rows[] = [
                    'Shipping Charges',
                    $log->shipping_charges !== null ? '$' . number_format($log->shipping_charges, 2) : '',
                    $log->new_shipping_charges !== null ? '$' . number_format($log->new_shipping_charges, 2) : '',
                ];
            }
            if ($log->discount_amount || $log->new_discount_amount) {
                $previous_discount = $log->discount_type == 'fixed' ? '$' . number_format($log->discount_amount, 2) : number_format($log->discount_amount, 2) . '%';
                $new_discount = $log->new_discount_type == 'fixed' ? '$' . number_format($log->new_discount_amount, 2) : number_format($log->new_discount_amount, 2) . '%';
                $rows[] = ['Discount', $previous_discount, $new_discount];
            }
            if ($log->tax_status || $log->new_tax_status) {
                $rows[] = ['Tax Applicable', ucwords($log->tax_status == 'on' ? 'Yes' : 'No'), ucwords($log->new_tax_status == 'on' ? 'Yes' : 'No')];
            }
            if ($log->notpickpack !== null) {
                $rows[] = ['Pick & Pack', $log->notpickpack == 1 ? 'No' : 'Yes'];
            }
            if ($log->sale_note || $log->new_sale_note ) {
                $rows[] = [
                'Sell Note',ucwords($log->sale_note), ucwords($log->new_sale_note)];
            }
            if ($log->staff_note || $log->new_staff_note ) {
                $rows[] = [
                'Staff Note', ucwords($log->staff_note), ucwords($log->new_staff_note)];
            }
            if ($log->shipping_address || $log->new_shipping_address) {
            $old_address = (string) $log->shipping_address === "[object Object]" ? '' : ucwords($log->shipping_address);
            $new_address = (string) $log->new_shipping_address === "[object Object]" ? '' : ucwords($log->new_shipping_address);
                if ($old_address || $new_address) {
                    $rows[] = ['Shipping Address', $old_address, $new_address];
                }
            }
            if ($log->shipping_details || $log->new_shipping_details) {
                $old_details = (string) $log->shipping_details === "[object Object]" ? '' : ucwords($log->shipping_details);
                $new_details = (string) $log->new_shipping_details === "[object Object]" ? '' : ucwords($log->new_shipping_details);
                if ($old_details || $new_details) {
                    $rows[] = ['Shipping Details', $old_details, $new_details];
                }
            }
            if ($log->final_total || $log->new_final_total) {
                $rows[] = [
                    'Total Payable',
                    $log->final_total !== null ? '$' . number_format($log->final_total, 2) : '',
                    $log->new_final_total !== null ? '$' . number_format($log->new_final_total, 2) : '',
                ];
            }
        }
    @endphp
        <table class="table bg-white">
            <thead>
                <tr class="bg-blue">
                    <th>Info Title</th>
                    <th>Previous Info</th>
                    <th>New Info</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td style="color:#0067c2;">{{ $row[0] ?? '' }}</td>
                        <td>{{ $row[1] ?? '' }}</td>
                        <td>{{ $row[2] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <!-- END Order Information Logs 'Edited' Table -->
    
    
    
