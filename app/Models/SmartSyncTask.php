<?php

namespace App\Models;

use App\PurchaseLine;
use App\Transaction;
use Illuminate\Database\Eloquent\Model;

class SmartSyncTask extends Model
{
    //
    protected $fillable = [
        'subject_type',
        'subject_params',
        'status',
        'created_by',
        'progress',
        'display_msg',
        'smart_queue',
        'scheduled_at',
    ];

    // pre defined status
    const TASK_STATUS_QUEUED = "queued";
    const TASK_STATUS_PROCESSING = "processing";
    const TASK_STATUS_COMPLETED = "completed";
    const TASK_STATUS_ABORTED = "aborted";

    // pre defined queues
    const TASK_QUEUE_ON_DEMAND = "on_demand";
    const TASK_QUEUE_SCHEDULED = "scheduled";

    public function scheduleTask(){
        $response_msg = "";
        $pre_task = self::where('subject_type', $this->subject_type)
            ->where('subject_params',$this->subject_params)
            ->where('status', self::TASK_STATUS_QUEUED)
            ->orderBy('scheduled_at')
        ->get();

        $buffer_sec = SmartSyncValue::getEstimatedTime($this->subject_type, $this->subject_params) * 60;

        $save_task = TRUE;
        foreach($pre_task as $task){
            $task_sch = strtotime($task->scheduled_at);
            if(strtotime($this->scheduled_at) > ($task_sch - $buffer_sec ) && strtotime($this->scheduled_at) < ($task_sch + $buffer_sec )){
                $response_msg = "One or more same task is already scheduled. Please try again after scheduled tasks are completed or change the schedule time.";
                $save_task = FALSE;
                break;
            }
        }

        if($save_task){
            $this->status = self::TASK_STATUS_QUEUED;
            $this->save();
            $response_msg = "Task has been scheduled successfully.";
        }

        return $response_msg;
    }

    public function getProductsFromTask(){
        $transaction = Transaction::find($this->transaction_id ?? '0');
        $products = [];
        if(isset($transaction)){
            if($transaction->type == 'purchase'){
                $purchase_lines = PurchaseLine::where('transaction_id',$transaction->id)->get();
                $products = array_column($purchase_lines->toArray(),'product_id');
            }
        }
        return $products;
    }
}