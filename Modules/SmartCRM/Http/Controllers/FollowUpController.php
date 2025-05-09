<?php

namespace Modules\SmartCRM\Http\Controllers;

use App\Contact;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SmartCRM\Models\FollowUp;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class FollowUpController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $business_id = auth()->user()->business_id;
        $view_own_customers_only = FollowUp::viewOwnCustomersOnly();

        if (request()->ajax()) {

            // $followups_query = FollowUp::with(['contact','agent']);
            $followups_query = FollowUp::whereHas('contact', function($query) use($view_own_customers_only) {
                if($view_own_customers_only){
                    $query->where('sales_rep', auth()->user()->id);
                }
            })->with('contact', 'agent');


            if (!empty(request()->contact_id)) {
                $contact_id = request()->contact_id;
                $followups_query->where('contact_id', $contact_id);
            }
            if (!empty(request()->fil_status)) {
                $status = strtolower(request()->fil_status);
                $followups_query->where('status', $status);
            }
            if (!empty(request()->assigned_to)) {
                $assigned_to = request()->assigned_to;
                $followups_query->where('assigned_to', $assigned_to);
            }
            if (!empty(request()->status)) {
                $status = request()->status;
                $followups_query->where('status', $status);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $followups_query->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
            }

            $followups = $followups_query->get();

            return DataTables::of($followups)
                ->addColumn('action', function($row){
                        $html =
                        '<div class="btn-group"><button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">'. __("messages.actions") . '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu dropdown-menu-left" role="menu">';

                        $html .= '<li><a href="/contacts/' . $row->contact_id . '?followup='. $row->id .'"><i class="fa fa-eye"></i> Contact Details</a></li>';
                        $html .= '<li><a href="'. route('smartcrm.followup.view',$row->id) .'" class="view-modal modal__trigger"><i class="fa fa-eye"></i> View</a></li>';
                        $html .= '<li><a href="' . route('smartcrm.followup.edit',$row->id) . '" class="edit-modal"><i class="fa fa-edit"></i> Edit</a></li>';
                        $html .= '<li><a href="' . route('smartcrm.followup.destroy',$row->id) . '" class="delete-followup"><i class="fa fa-trash"></i> ' . __("messages.delete") . '</a></li>';
                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->editColumn('scheduled_at',function($row){
                    if($row->scheduled_at){
                        return date('m/d/Y H:i',strtotime($row->scheduled_at));
                    }
                })
                ->editColumn('created_at',function($row){
                    return date('m/d/Y H:i A',strtotime($row->created_at));
                })
                ->editColumn('contact_id',function($row){
                    return $row->contact->name;
                })
                ->editColumn('title',function($row){
                    $html = "<p>".$row->title."</p>";
                    foreach(explode(",",$row->tags) as $tag){
                        $html .= "<span class='label bg-info'>$tag</span>&nbsp;";
                    }
                    return $html;
                })
                ->editColumn('assigned_to',function($row){
                    return $row->agent->first_name . " " . $row->agent->last_name;
                })
                ->editColumn('channel',function($row){
                    return FollowUp::format($row->channel);
                })
                ->editColumn('priority',function($row){
                    return FollowUp::format($row->priority);
                })
                ->editColumn('status',function($row){
                    return FollowUp::format($row->status);
                })
                ->rawColumns(['action', 'title'])
            ->make(true);
        }

        $customers = Contact::customersCompanyDropdown($business_id, false, true, $view_own_customers_only);
        $users = User::forDropdown($business_id, false);
        $status = FollowUp::keyValueMap(FollowUp::STATUS);
        $priorities = FollowUp::keyValueMap(FollowUp::PRIORITIES);
        $channel = FollowUp::keyValueMap(FollowUp::CHANNEL);
        return view('smartcrm::followup.index', compact('customers', 'users', 'status', 'priorities', 'channel'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(Request $request)
    {
        return redirect()->back();
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {

       $validator = Validator::make($request->all(), [
            'contact_id' => 'required',
            'title' => 'required',
            // 'assigned_to' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $followup = FollowUp::create([
            'contact_id' => $request->contact_id,
            'title' => $request->title,
            'status' => $request->status,
            'priority' => $request->priority ?? '',
            'channel' => $request->channel,
            'tags' => $request->tags,
            'note' => $request->notes,
            'scheduled_at' => $request->scheduled_at !== null ? date('Y-m-d H:i:s', strtotime($request->scheduled_at)) : null,
            // 'assigned_to' => $request->assigned_to,
            'assigned_to' => auth()->user()->id,
        ]);

        $output = [
            'success' => 1,
            'msg' => 'Follow up record added'
        ];
        return redirect()->back()->with('status', $output);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $followup = FollowUp::findOrFail($id);
        if($followup->contact_id == request()->get('contact')){
            return view('smartcrm::followup.contact_view', compact('followup'));
        }
        else{
            abort(404);
        }
    }

    public function view($id)
    {
        $followup = FollowUp::with(['contact', 'agent'])->where('id', $id)->first();

        return view('smartcrm::followup.view', compact('followup'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $view_own_customers_only = FollowUp::viewOwnCustomersOnly();

        $followup = FollowUp::findOrFail($id);

        $business_id = auth()->user()->business_id;
        $customers = Contact::customersCompanyDropdown($business_id, false, true, $view_own_customers_only);
        // $customers = Contact::customersDropdown($business_id, false);
        $users = User::forDropdown($business_id);
        $status = FollowUp::keyValueMap(FollowUp::STATUS);
        $priorities = FollowUp::keyValueMap(FollowUp::PRIORITIES);
        $channel = FollowUp::keyValueMap(FollowUp::CHANNEL);

        return view('smartcrm::followup.edit', compact('followup','customers', 'users', 'status', 'priorities', 'channel'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
         $validator = Validator::make($request->all(), [
            'contact_id' => 'required',
            'title' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $Updatefollowup = FollowUp::findOrFail($id);

        // dd($request->all());
        $Updatefollowup->update([
            'contact_id' => $request->contact_id,
            'title' => $request->title,
            'status' => $request->status,
            'priority' => $request->priority ?? '',
            'channel' => $request->channel,
            'tags' => $request->tags,
            'note' => $request->notes,
            'scheduled_at' => $request->scheduled_at !== null ? date('Y-m-d H:i:s', strtotime($request->scheduled_at)) : null,
        ]);

        $output = [
            'success' => 1,
            'msg' => 'Follow up record update'
        ];
        return redirect()->back()->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $followup = FollowUp::findOrFail($id);
        $followup->delete();

        $output = [
            'success' => 1,
            'msg' => 'Follow up record deleted'
        ];
        return response($output);
    }

    public function startQueue($id = null){
        $order_formatted_priorities = implode(',',array_map(function($item){return "'".$item."'";},FollowUp::PRIORITIES));
        $followups = FollowUp::whereDate('scheduled_at',date('Y-m-d'))
            ->where('assigned_to', auth()->user()->id)
            ->where('status', '<>', 'closed')
            ->orderByRaw('FIELD(priority,'.$order_formatted_priorities.')')
        ->get();

        $next_element = [];
        if(!empty($followups)){
            $next_element = $followups[0];
            if(!empty($id)){
                foreach($followups as $key=>$f){
                    if($f->id == $id){
                        $next_element =  $followups[$key + 1] ?? [];
                        break;
                    }
                }
            }
        }

        if(empty($next_element)){
            return redirect()->route('smartcrm.followup.index');
        }
        return redirect('/contacts/'.$next_element->contact_id.'?followup='.$next_element->id);
    }
}
