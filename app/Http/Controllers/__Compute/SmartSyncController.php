<?php

namespace App\Http\Controllers\__Compute;

use App\Http\Controllers\Controller;
use App\Models\SmartSyncTask;
use App\Models\SmartSyncValue;
use App\Product;
use App\User;
use Illuminate\Support\Facades\Request;

class SmartSyncController extends Controller
{
    public function smartProducts(){
        $smart_tasks = SmartSyncTask::join('users as u','u.id','=','smart_sync_tasks.created_by')
            ->select('smart_sync_tasks.*', 'u.first_name')
            ->where('smart_sync_tasks.status', SmartSyncTask::TASK_STATUS_QUEUED)
            ->orderBy('smart_sync_tasks.scheduled_at')
        ->get();
        return view('smartsync.product',compact('smart_tasks'));
    }

    public function smartProductsList(){

        $pre_def_types = ['new','all','failed','delete'];
        $sync_type = request()->input('sync_type');

        if(empty($sync_type) || !in_array($sync_type, $pre_def_types)){
            return "Please specify sync type.";
        }

        $products = SmartSyncValue::productBaseQuery($sync_type)->get();

        return view('smartsync.product_list',compact('products'));
    }

    public function getSmartProductsData(){
        $total_p = Product::where('not_for_selling','<>',1)
            ->where('business_id',4)
            ->whereIn('type', ['single', 'variable'])
        ->count();

        $total_sync_p = Product::where('not_for_selling','<>',1)
            ->where('woocommerce_disable_sync','<>',1)
            ->where('business_id',4)
            ->whereIn('type', ['single', 'variable'])
        ->count();

        $total_web_p = SmartSyncValue::getSmartValue('total_website_products_counted');
        $total_web_p_active = SmartSyncValue::getSmartValue('total_website_products');
        $web_missing_p = SmartSyncValue::getSmartValue('website_only_products');

        $create_p = SmartSyncValue::productBaseQuery("new")->count();
        $sync_p = SmartSyncValue::productBaseQuery("all")->count();
        $failed_p = SmartSyncValue::productBaseQuery("failed")->count();
        $delete_p = SmartSyncValue::productBaseQuery("delete")->count();

        return json_encode([
            'total_p' => $total_p ?? 0,
            'total_sync_p' => $total_sync_p ?? 0,
            'total_web_p' => $total_web_p ?? 0,
            'total_web_p_active' => $total_web_p_active ?? 0,
            'web_missing_p' => $web_missing_p ?? 0,
            'create_p' => $create_p ?? 0,
            'sync_p' => $sync_p ?? 0,
            'failed_p' => $failed_p ?? 0,
            'delete_p' => $delete_p ?? 0
        ]);
    }

    public function getCurrentTaskDetails(){
        $smart_task = SmartSyncTask::where('status', SmartSyncTask::TASK_STATUS_PROCESSING)->orderBy('updated_at','desc')->first();
        if(empty($smart_task)){
            $smart_task = SmartSyncTask::where('status','<>',SmartSyncTask::TASK_STATUS_QUEUED)->orderBy('updated_at','desc')->first();
        }
        if(!empty($smart_task)){
            $smart_task->user_name = User::find($smart_task->created_by)->first_name;
            $smart_task->scheduled_at = date('m-d-Y g:i a',strtotime($smart_task->scheduled_at));
            $smart_task->btn_abort_visibility = "show";
            if ($smart_task->created_by == 6 && auth()->user()->id != 6){
                $smart_task->btn_abort_visibility = "hide";
            }
            return json_encode($smart_task);
        }
        return 0;
    }

    public function addTask(Request $request){
        $output = [
            'success' => 0,
            'msg' => ''
        ];

        $pre_def_types = ['new','all','failed','delete','inventory','webstat','forced','tierprice'];

        if(empty(request()->input('sync_type')) || !in_array(request()->input('sync_type'),$pre_def_types)){
            $output['msg'] = "Please specify sync type.";
            return $output;
        }

        $task = new SmartSyncTask();
        $task->subject_type = "SmartSync:Products";
        $task->subject_params = request()->input('sync_type');
        $task->status = "pending";
        $task->created_by = auth()->user()->id;
        $task->scheduled_at = date('Y-m-d H:i:s',strtotime(request()->input('dt_picker',date('Y-m-d H:i:s'))));
        $output['msg'] = $task->scheduleTask();

        if(!empty($task->id)){
            $output['success'] = 1;
        }

        return $output;
    }

    public function abortTask($id){
        $output = [
            'success' => 0,
            'msg' => 'Unable to stop the task'
        ];
        if(!empty($id)){
            $smart_task = SmartSyncTask::where('id', $id)->whereIn('status',['processing','queued'])->first();
            if(!empty($smart_task)){
                if ($smart_task->created_by == 6 && auth()->user()->id != 6){
                    $output['success'] = 0;
                    $output['msg'] = "Unauthorized access";
                }
                else{
                    if($smart_task->status == SmartSyncTask::TASK_STATUS_PROCESSING){
                        $smart_task->display_msg = "Stopping task.";
                    }
                    else{
                        $smart_task->display_msg = "Task has been aborted.";
                    }
                    $smart_task->status = SmartSyncTask::TASK_STATUS_ABORTED;
                    $smart_task->save();
                    $output['success'] = 1;
                    $output['msg'] = "Task is aborted";
                }
            }
        }
        return json_encode($output);
    }
}