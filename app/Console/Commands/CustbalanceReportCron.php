<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Contact;
use App\Utils\TransactionUtil; // Make sure this utility class is imported if it exists

class CustbalanceReportCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custbalancereport:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate customer balance report';

    protected $transactionUtil;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil)
    {
        parent::__construct();
        $this->transactionUtil = $transactionUtil;
    }


    /**
     * Execute the console command.
     *
     * @return array
     */
    public function handle()
    {
        $business_id = 4; // Replace with the appropriate value or logic to get the business_id

        // Set start and end dates (example dates)
        $start = '2021-01-01';
        $end = '2025-06-28';

        // Fetch all contacts
        $AllContactsData = Contact::where('contact_status', 'active')->get();
        // Initialize an array to store results
        $contactsBalance = [];
        
        
        foreach ($AllContactsData as $cData) {
            $contact_id = $cData->id;
            // Initialize variables
            $advance_balance = 0; // You can fetch or initialize as needed
            $ledger_details = $this->transactionUtil->getLedgerDetails($contact_id, $start, $end, $advance_balance);
            // $ledger_details = $this->transactionUtil->getLedgerDetails($contact_id, $start, $end,$advance_balance);
            $balance = 0;
            $total_due_balance = 0;

            // Process ledger details
            for ($i = count($ledger_details['ledger']) - 1; $i >= 0; $i--) {

                if ($cData->type == 'supplier') { // Assuming $cData holds the current contact data
                    if ($ledger_details['ledger'][$i]['total'] > 0 && ($ledger_details['ledger'][$i]['type'] == 'expense' || $ledger_details['ledger'][$i]['type'] == 'Purchase')) {
                        $balance += $ledger_details['ledger'][$i]['total'];
                        $ledger_details['ledger'][$i]['credit'] = $ledger_details['ledger'][$i]['total'];
                    } elseif ($ledger_details['ledger'][$i]['total'] > 0 && $ledger_details['ledger'][$i]['type'] == 'Purchase Return') {
                        $balance -= $ledger_details['ledger'][$i]['total'];
                        $ledger_details['ledger'][$i]['debit'] = $ledger_details['ledger'][$i]['total'];
                    } elseif ($ledger_details['ledger'][$i]['payment_method'] == 'Advance') {
                        // Do nothing
                    } elseif ($ledger_details['ledger'][$i]['type'] == 'Payment') {
                        $tr_type = $ledger_details['ledger'][$i]['transaction_type'] ?? '';
                        if ($tr_type == 'purchase_return') {
                            $balance += (float)$ledger_details['ledger'][$i]['credit'];
                        } else {
                            if ($ledger_details['ledger'][$i]['debit'] <= 0) {
                                $ledger_details['ledger'][$i]['debit'] = $ledger_details['ledger'][$i]['credit'];
                            }
                            $balance -= (float)$ledger_details['ledger'][$i]['debit'];
                            $ledger_details['ledger'][$i]['credit'] = '';
                        }
                    }
                } else { // Assuming the contact type is not 'supplier'
                    if ($ledger_details['ledger'][$i]['type'] == 'Payment' && $ledger_details['ledger'][$i]['transaction_type'] == 'expense') {
                        $ledger_details['ledger'][$i]['debit'] = "";
                    }
                    if ($ledger_details['ledger'][$i]['type'] == 'Sell' || $ledger_details['ledger'][$i]['type'] == 'Opening Balance') {
                        $balance += $ledger_details['ledger'][$i]['total'];
                    } else {
                        if ($ledger_details['ledger'][$i]['ref_no'] && $ledger_details['ledger'][$i]['is_advance'] == 1 && $ledger_details['ledger'][$i]['advance_amt'] > 0 && $i != count($ledger_details['ledger']) - 1) {
                            if ($ledger_details['ledger'][$i]['transaction_id'] == $ledger_details['ledger'][$i + 1]['transaction_id'] && $ledger_details['ledger'][$i + 1]['payment_status'] == '') {
                                $ledger_details['ledger'][$i + 1]['others'] = $ledger_details['ledger'][$i]['others'];
                            }
                        } elseif ($ledger_details['ledger'][$i]['payment_method'] == 'Advance' || $ledger_details['ledger'][$i]['payment_method'] == 'Credit') {
                            // Do nothing
                        } else {
                            if (($ledger_details['ledger'][$i]['total'] > 0 && $ledger_details['ledger'][$i]['type'] == 'expense') || $ledger_details['ledger'][$i]['type'] == 'Sell Return') {
                                // Do nothing
                            } elseif ($ledger_details['ledger'][$i]['total'] > 0) {
                                $balance -= $ledger_details['ledger'][$i]['total'];
                            } elseif ($ledger_details['ledger'][$i]['credit'] > 0) {
                                $balance -= $ledger_details['ledger'][$i]['credit'];
                            } elseif ($ledger_details['ledger'][$i]['credit'] < 0) {
                                $balance -= $ledger_details['ledger'][$i]['credit'];
                            } elseif ($ledger_details['ledger'][$i]['type'] == 'Purchase' && $ledger_details['ledger'][$i]['total'] < 0) {
                                $balance -= $ledger_details['ledger'][$i]['total'];
                            }
                        }
                    }

                    if ($ledger_details['ledger'][$i]['type'] == 'Sell Return' && $ledger_details['ledger'][$i]['transaction_type'] == 'not_payment') {
                        $balance -= $ledger_details['ledger'][$i]['total'];
                    }

                    if ($ledger_details['ledger'][$i]['type'] == 'Payment' && $ledger_details['ledger'][$i]['transaction_type'] == 'sell_return') {
                        $balance += $ledger_details['ledger'][$i]['debit'];
                    }

                    if ($ledger_details['ledger'][$i]['type'] == 'Payment') {
                        $ledger_details['ledger'][$i]['total'] = $ledger_details['ledger'][$i]['credit'];
                    }

                    if ($ledger_details['ledger'][$i]['type'] == 'Sell' || $ledger_details['ledger'][$i]['type'] == 'Opening Balance') {
                        $ledger_details['ledger'][$i]['debit'] = $ledger_details['ledger'][$i]['total'];
                    } elseif ($ledger_details['ledger'][$i]['type'] == 'Sell Return') {
                        $ledger_details['ledger'][$i]['credit'] = $ledger_details['ledger'][$i]['total'];
                    } else {
                        $ledger_details['ledger'][$i]['credit'] = $ledger_details['ledger'][$i]['credit'];
                    }

                    if ($ledger_details['ledger'][$i]['type'] == 'expense') {
                        $balance += $ledger_details['ledger'][$i]['total'];
                        if ($ledger_details['ledger'][$i]['total'] >= 0) {
                            $ledger_details['ledger'][$i]['debit'] = $ledger_details['ledger'][$i]['total'];
                        } else {
                            $ledger_details['ledger'][$i]['credit'] = $ledger_details['ledger'][$i]['total'] * -1;
                        }
                        $ledger_details['ledger'][$i]['type'] = "Adjustment";
                    }
                }

                $ledger_details['ledger'][$i]['balance'] = $balance;
            }

            $total_balance = 0;
            $total_credit_balance = 0;
            $difference = 0;
            $DifferenceData = 0;
            $balance_due_total = 0;

            
            // Below loop for calculating Total Credit Data, Total Balance
            // foreach ($ledger_details['ledger'] as $data) {
            //     if (($data['payment_status'] == 'Due' && $data['type'] == 'Sell') || $data['type'] == 'Sell Return') {
            //         if ($data['type'] == 'Sell Return' && $data['payment_status'] == 'Paid') {
            //             // Do nothing
            //         } else {
            //             if ($data['credit'] > 0) {
            //                 $total_credit_balance += $data['credit'];
            //             } else {
            //                 $total_credit_balance += $data['total'];
            //             }
            //         }
            //     }
            //     $total_balance += $data['balance'];
            // }
            // Chunk size
            $chunkSize = 100; 
        
            // Initialize totals
            $total_credit_balance = 0;
            $total_balance = 0;
        
            // Process ledger details in chunks
            $chunks = array_chunk($ledger_details['ledger'], $chunkSize);
        
            foreach ($chunks as $chunk) {
                foreach ($chunk as $data) {
                    if (($data['payment_status'] == 'Due' && $data['type'] == 'Sell') || $data['type'] == 'Sell Return') {
                        if (!($data['type'] == 'Sell Return' && $data['payment_status'] == 'Paid')) {
                            if ($data['credit'] > 0) {
                                $total_credit_balance += $data['credit'];
                            } else {
                                $total_credit_balance += $data['total'];
                            }
                        }
                    }
                    $total_balance += $data['balance'];
                }
            }
        
            $total_due_balance = $ledger_details['balance_due'];
        
            // Prepare data for this contact
            $contactData = [
                'Name' => $cData->name,
                'total_balance' => $total_balance,
                'total_credit_balance' => $total_credit_balance,
                'differenceData' => $total_balance - $total_credit_balance,
                'due_total_balance' => $total_due_balance,
                'differenceDueData' => intval(($total_balance - $total_credit_balance) - $total_due_balance)
            ];
        
            // Add contact data to results array
            if ($total_balance - $total_credit_balance != 0) {
                $contactsBalance[] = $contactData;
            }
        }
        return $contactsBalance;
    }
}
