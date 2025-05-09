<?php

namespace App\Http\Controllers;

use Datatables;
use App\Transaction;
use App\InvoiceScheme;
use App\InvoiceLayout;
use App\Notifications\InvoiceRepairReportNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class InvoiceSchemeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $schemes = InvoiceScheme::where('business_id', $business_id)
                            ->select(['id', 'name', 'scheme_type', 'prefix', 'start_number', 'invoice_count', 'total_digits', 'is_default']);

            return Datatables::of($schemes)
                ->addColumn(
                    'action',
                    '<button type="button" data-href="{{action(\'InvoiceSchemeController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".invoice_edit_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                        <button type="button" data-href="{{action(\'InvoiceSchemeController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_invoice_button" @if($is_default) disabled @endif><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>&nbsp;
                        @if($is_default)
                            <button type="button" class="btn btn-xs btn-success" disabled><i class="fa fa-check-square-o" aria-hidden="true"></i> @lang("barcode.default")</button>
                        @else
                            <button class="btn btn-xs btn-info set_default_invoice" data-href="{{action(\'InvoiceSchemeController@setDefault\', [$id])}}">@lang("barcode.set_as_default")</button>
                        @endif
                        '
                )
                ->editColumn('prefix', function ($row) {
                    if ($row->scheme_type == 'year') {
                        return date('Y') . '-';
                    } else {
                        return $row->prefix ;
                    }
                })
                ->editColumn('name', function ($row) {
                    if ($row->is_default == 1) {
                        return $row->name . ' &nbsp; <span class="label label-success">' . __("barcode.default") .'</span>' ;
                    } else {
                        return $row->name ;
                    }
                })
                ->removeColumn('id')
                ->removeColumn('is_default')
                ->removeColumn('scheme_type')
                ->rawColumns([5,0])
                ->make(false);
        }

        $invoice_layouts = InvoiceLayout::where('business_id', $business_id)
                                        ->with(['locations'])
                                        ->get();

        return view('invoice_scheme.index')
                    ->with(compact('invoice_layouts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        return view('invoice_scheme.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'scheme_type', 'prefix', 'start_number', 'total_digits']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            if (!empty($request->input('is_default'))) {
                //get_default
                $default = InvoiceScheme::where('business_id', $business_id)
                                ->where('is_default', 1)
                                ->update(['is_default' => 0 ]);
                $input['is_default'] = 1;
            }
            InvoiceScheme::create($input);
            $output = ['success' => true,
                            'msg' => __("invoice.added_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $invoice = InvoiceScheme::where('business_id', $business_id)->find($id);

        return view('invoice_scheme.edit')
            ->with(compact('invoice'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'scheme_type', 'prefix', 'start_number', 'total_digits']);

            $invoice = InvoiceScheme::where('id', $id)->update($input);

            $output = ['success' => true,
                            'msg' => __('invoice.updated_success')
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $invoice = InvoiceScheme::find($id);
                if ($invoice->is_default != 1) {
                    $invoice->delete();
                    $output = ['success' => true,
                                'msg' => __("invoice.deleted_success")
                                ];
                } else {
                    $output = ['success' => false,
                                'msg' => __("messages.something_went_wrong")
                                ];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }
    /**
     * Sets invoice scheme setting as default
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function setDefault($id)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }
        
        if (request()->ajax()) {
            try {
                //get_default
                $business_id = request()->session()->get('user.business_id');
                $default = InvoiceScheme::where('business_id', $business_id)
                                ->where('is_default', 1)
                                 ->update(['is_default' => 0 ]);
                                 
                $invoice = InvoiceScheme::find($id);
                $invoice->is_default = 1;
                $invoice->save();

                $output = ['success' => true,
                            'msg' => __("barcode.default_set_success")
                        ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }
            return $output;
        }
    }
    
     // START the new invoice repair module 
    public static function theInvoiceRepairer($specifiedDate = null)
    {
        if (is_null($specifiedDate)) {
            $specifiedDate = date('Y-m-d'); // Default to current date if not specified
        }

        $duplicateInvoices = DB::table('transactions')
            ->select('invoice_no', DB::raw('COUNT(*) as dup_count'))
            ->whereDate('transaction_date', $specifiedDate)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->groupBy('invoice_no')
            ->having('dup_count', '>', 1)
            ->get();

        $report = [];

        foreach ($duplicateInvoices as $dupInvoice) {
            $transactions = Transaction::where('invoice_no', $dupInvoice->invoice_no)
                ->whereDate('transaction_date', $specifiedDate)
                ->orderBy('transaction_date')
                ->get();

            $dupNo = 1;
            foreach ($transactions as $transaction) {
                if ($dupNo > 1) {
                    $scheme = InvoiceScheme::where('business_id', $transaction->business_id)
                        ->first();

                    $newInvoiceNo = self::generateNewInvoiceNo($scheme);
                    $oldInvoiceNo = $transaction->invoice_no;

                    $transaction->invoice_no = $newInvoiceNo;
                    $transaction->save();

                    $scheme->invoice_count += 1;
                    $scheme->save();

                    $report[] = [
                        'transaction_id' => $transaction->id,
                        'transaction_date' => $transaction->transaction_date,
                        'old_invoice_no' => $oldInvoiceNo,
                        'new_invoice_no' => $newInvoiceNo,
                        'specified_date' => $specifiedDate,
                    ];
                }
                $dupNo++;
            }
        }

         if (!empty($report)) {
        $recipients = ['kashyap.s@brainbean.in', 'jay@brainbean.in'];
        foreach ($recipients as $email) {
            Notification::route('mail', $email)
                ->notify(new InvoiceRepairReportNotification($report));
        }
    }
        return $report;
    }

    private static function generateNewInvoiceNo($scheme)
    {
        $prefix = $scheme->scheme_type == 'blank' ? $scheme->prefix : date('Y') . '-';
        $count = $scheme->start_number + $scheme->invoice_count;
        $count = str_pad($count, $scheme->total_digits, '0', STR_PAD_LEFT);

        return $prefix . $count;
    }

    public function repairInvoices(Request $request)
    {
        $specifiedDate = $request->input('date');
        $report = self::theInvoiceRepairer($specifiedDate);
        return response()->json($report);
    }
    // the new invoice repair module END
}
