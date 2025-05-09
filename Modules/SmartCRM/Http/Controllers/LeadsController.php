<?php

namespace Modules\SmartCRM\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Contact;
use App\User;
use App\SellingPriceGroup;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ContactUtil;
use App\Utils\Util;
use Modules\SmartCRM\Models\Leads;
use DB;
use App\Notifications\CustomerNotification;
use App\NotificationTemplate;
use App\Transaction;
use \Notification;
use PDF;
use ZipArchive;
use File;
use QrCode;
use App\Utils\NotificationUtil;

class LeadsController extends Controller
{

    protected $commonUtil;
    protected $contactUtil;
    protected $notificationUtil;


    public function __construct(
         NotificationUtil $notificationUtil,
        Util $commonUtil,
        ContactUtil $contactUtil
    ) {
        $this->notificationUtil = $notificationUtil;
        $this->commonUtil = $commonUtil;
        $this->contactUtil = $contactUtil;
    }


    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        // return view('smartcrm::index');

        if (request()->ajax()) {

            $data = Leads::with(['user'])
                ->orderBy('created_at', 'desc');

            if (!empty(request()->created_by)) {
                $created_by = request()->created_by;
                $data->where('created_by', $created_by);
            }

            $leads = $data->get();

            return DataTables::of($leads)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html =
                            '<div class="btn-group"><button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">' . __("messages.actions") . '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu dropdown-menu-left" role="menu">';

                        $html .= '<li><a href="' . route('smartcrm.lead.ShowLeads', $row->id) . '" class="view-modal modal__trigger"><i class="fa fa-eye"></i> View</a></li>';
                        $html .= '<li><a href="' . route('smartcrm.lead.EditLeads', $row->id) . '" class="edit-modal"><i class="fa fa-edit"></i> Edit</a></li>';
                        $html .= '<li><a href="' . route('smartcrm.lead.DeleteLeads', $row->id) . '" class="delete-leads"><i class="fa fa-trash"></i> ' . __("messages.delete") . '</a></li>';
                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->addColumn('address', '{{implode(", ", array_filter([$address_line_1, $address_line_2, $city, $state, $country, $zip_code]))}}')
                ->editColumn('created_by ', function ($row) {
                })
                ->editColumn('created_at', function ($row) {
                    return date('m/d/Y H:i A', strtotime($row->created_at));
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $business_id = request()->session()->get('user.business_id');

        $users = User::forDropdown($business_id, false);

        return view('smartcrm::leads.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        $customer_groups = SellingPriceGroup::forDropdown($business_id);

        $users = User::select('id', 'first_name', 'last_name')->get();

        $template_for = 'new_lead';
        $notification_template = NotificationTemplate::getTemplate($business_id, $template_for);

        $customer_notifications = NotificationTemplate::customerNotifications();

        $template_name = '';

        $tags = [];
        if (array_key_exists($template_for, $customer_notifications)) {
            $template_name = $customer_notifications[$template_for]['name'];
            $tags = $customer_notifications[$template_for]['extra_tags'];
        }

        return view('smartcrm::leads.create', compact('users', 'customer_groups', 'notification_template', 'template_name', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try {

            $business_id = $request->session()->get('user.business_id');

            $input = $request->only([
                'type', 'supplier_business_name', 'first_name', 'mobile', 'landline', 'alternate_number', 'city', 'state', 'country',
                'address_line_1', 'address_line_2', 'customer_group_id', 'zip_code', 'contact_id',
                'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4', 'cig_license_no',
                'custom_field5', 'custom_field6', 'custom_field7', 'custom_field8', 'cig_license_expiry_date',
                'custom_field9', 'custom_field10', 'email', 'shipping_address',
                'position', 'dob', 'contact_person_1', 'contact_person_2',
                'tobacco_license_no', 'expiry_date', 'referal_code', 'fax',
                'note', 'tax', 'is_nyc', 'cigar_customer', 'whatsapp', 'storetype', 'talktime', 'selling_item', 'coordinates'
            ]);


            $input['type'] = 'customer';
            $input['first_name'] = strtoupper($request->first_name);
            $input['name'] = strtoupper($request->first_name);

            $input['is_nyc'] = $request->input('is_nyc');
            $input['whatsapp'] = $request->input('whatsapp');
            // $input['tax_number'] = strtoupper($request->input('tax'));
            $input['supplier_business_name'] = strtoupper($request->supplier_business_name);
            $input['address_line_1'] = strtoupper($request->address_line_1);

            $input['contact_person_1'] = strtoupper($request->contact_person_1);
            $input['contact_person_2'] = strtoupper($request->contact_person_2);
            $input['email'] = strtoupper($request->email);
            $input['address_line_2'] = strtoupper($request->address_line_2);
            $input['city'] = strtoupper($request->city);
            $input['state'] = $request->input('state');
            $input['business_id'] = $business_id;
            $input['mobile'] = $request->whatsapp;
            $input['created_by'] = $request->session()->get('user.id');

            $input['coordinates'] = $request->input('coordinates');

            $input['selling_item'] = $request->input('selling_item');

            $input['storetype'] = implode(',', $request->input('storetype', []));

            $input['talktime'] = implode(',', $request->input('talktime', []));

            $input['tax_number'] = strtoupper($request->input('tax'));

            $input['tobacco_license_no'] = strtoupper($request->tobacco_license_no);

            if (!empty($input['dob'])) {
                $input['dob'] = $this->commonUtil->uf_date($input['dob']);
            }


            $map_location =  $input['address_line_1'] . ',' . $input['city'] . ',' . $input['state'] . ',' . $input['country']
                            . ',' .$input['zip_code'];

            $lat_long = json_encode($map_location);

            $guzzle = new \GuzzleHttp\Client();

            $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($lat_long) . "+&sensor=false+CA&key=AIzaSyC8Jc4HBUsp9w_I9-rUTBS3t7v0atcBzWc";
            $geo = $guzzle->request('post', $url);
            $geo_result = json_decode($geo->getBody());


            $latitude = $geo_result->results[0]->geometry->location->lat ?? '';

            $longitude = $geo_result->results[0]->geometry->location->lng ?? '';

            $input['lat'] = $latitude;
            $input['lgn'] = $longitude;

            $output = $this->contactUtil->createNewLeads($input);

            // send email
            if($request->email && $request->notification_type == 'email_only'){
                $user_id = $request->session()->get('user.id');
                $customer_notifications = NotificationTemplate::customerNotifications();
                // $supplier_notifications = NotificationTemplate::supplierNotifications();

                $data = $request->only(['to_email', 'subject', 'email_body', 'mobile_number', 'sms_body', 'notification_type', 'cc', 'bcc']);

                $emails_array = array_map('trim', explode(',', $data['to_email']));

                $transaction_id = $request->input('transaction_id');
                $business_id = request()->session()->get('business.id');

                $orig_data = [
                    'email_body' => $data['email_body'],
                    'sms_body' => $data['sms_body'],
                    'subject' => $data['subject']
                ];

                if ($request->input('template_for') == 'new_booking') {
                    $tag_replaced_data = $this->notificationUtil->replaceBookingTags($business_id, $orig_data, $transaction_id);

                    $data['email_body'] = $tag_replaced_data['email_body'];
                    $data['sms_body'] = $tag_replaced_data['sms_body'];
                    $data['subject'] = $tag_replaced_data['subject'];
                } else {
                    $tag_replaced_data = $this->notificationUtil->replaceTags($business_id, $orig_data, $transaction_id);

                    $data['email_body'] = $tag_replaced_data['email_body'];
                    $data['sms_body'] = $tag_replaced_data['sms_body'];
                    $data['subject'] = $tag_replaced_data['subject'];
                }


                $data['email_settings'] = request()->session()->get('business.email_settings');

                $data['sms_settings'] = request()->session()->get('business.sms_settings');

                $notification_type = $request->input('notification_type');

                if (array_key_exists($request->input('template_for'), $customer_notifications)) {
                    if ($notification_type == 'email_only') {
                        $user_id = $request->session()->get('user.id');
                        $transaction = Transaction::where('id', $transaction_id)->with(['business', 'location'])->first();

                        Notification::route('mail', $emails_array)
                                        ->notify(new CustomerNotification($data));


                    } elseif ($notification_type == 'sms_only') {
                        $this->notificationUtil->sendSms($data);
                        //added by developer1

                    } elseif ($notification_type == 'both') {
                        Notification::route('mail', $emails_array)
                                    ->notify(new CustomerNotification($data));

                        $this->notificationUtil->sendSms($data);

                    }
                }
            }

            $output = [
                'success' => 1,
                'msg' => 'Lead record added'
            ];
            return redirect()->back()->with('status', $output);
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
            return $output;
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        // $contact = Leads::findOrFail($id);
        $contact = Leads::with(['user'])
        ->select(
            'leads.*',
            DB::raw("CONCAT_WS(', ',
                COALESCE(address_line_1, ''),
                COALESCE(address_line_2, ''),
                COALESCE(city, ''),
                COALESCE(state, ''),
                COALESCE(country, '')
            ) as full_address")
        )->where('id', $id)->first();


        return view('smartcrm::leads.view', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $contact = Leads::findOrFail($id);

        return view('smartcrm::leads.edit', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        try {
            $business_id = $request->session()->get('user.business_id');

            $input = $request->only([
                'type', 'supplier_business_name', 'first_name', 'mobile', 'landline', 'alternate_number', 'city', 'state', 'country',
                'address_line_1', 'address_line_2', 'customer_group_id', 'zip_code', 'contact_id',
                'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4', 'cig_license_no',
                'custom_field5', 'custom_field6', 'custom_field7', 'custom_field8', 'cig_license_expiry_date',
                'custom_field9', 'custom_field10', 'email', 'shipping_address',
                'position', 'dob', 'contact_person_1', 'contact_person_2',
                'tobacco_license_no', 'expiry_date', 'referal_code', 'fax',
                'note', 'tax', 'is_nyc', 'cigar_customer', 'whatsapp', 'storetype', 'talktime', 'selling_item', 'coordinates'
            ]);


            $input['type'] = 'customer';
            $input['first_name'] = strtoupper($request->first_name);
            $input['name'] = strtoupper($request->first_name);

            $input['is_nyc'] = $request->input('is_nyc');
            $input['whatsapp'] = $request->input('whatsapp');
            // $input['tax_number'] = strtoupper($request->input('tax'));
            $input['supplier_business_name'] = strtoupper($request->supplier_business_name);
            $input['address_line_1'] = strtoupper($request->address_line_1);

            $input['contact_person_1'] = strtoupper($request->contact_person_1);
            $input['contact_person_2'] = strtoupper($request->contact_person_2);
            $input['email'] = strtoupper($request->email);
            $input['address_line_2'] = strtoupper($request->address_line_2);
            $input['city'] = strtoupper($request->city);
            $input['state'] = $request->input('state');
            $input['business_id'] = $business_id;
            $input['mobile'] = $request->whatsapp;
            $input['created_by'] = $request->session()->get('user.id');

            $input['coordinates'] = $request->input('coordinates');

            $input['selling_item'] = $request->input('selling_item');

            $input['storetype'] = implode(',', $request->input('storetype', []));

            $input['talktime'] = implode(',', $request->input('talktime', []));

            $input['tax_number'] = strtoupper($request->input('tax'));

            $input['tobacco_license_no'] = strtoupper($request->tobacco_license_no);

            if (!empty($input['dob'])) {
                $input['dob'] = $this->commonUtil->uf_date($input['dob']);
            }

            $output = $this->contactUtil->updateLeads($input, $id, $business_id);

            Leads::where('id', $id)->update(["state" => $request->input('state')]);

            $output = [
                'success' => 1,
                'msg' => 'Lead Updated Successfully'
            ];
            return redirect()->back()->with('status', $output);
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $contact = Leads::findOrFail($id);
            $contact->delete();

            $output = [
                'success' => 1,
                'msg' => 'Follow up record deleted'
            ];
            return response($output);
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
            return $output;
        }
    }
}
