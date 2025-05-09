<?php

namespace App\Console;

use App\Helpers\SmartSyncHelper;
use App\Helpers\TransactionHelper;
use App\Models\SmartSyncTask;
use App\Transaction;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use DateTime;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Modules\Woocommerce\Utils\WoocommerceUtil;
use App\Console\Commands\CustbalanceReportCron;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /**
         * Register your recurring task in this call at the end of function.
         * DONT MODIFY EXISTING CODE
         */
        $schedule->call(function(){
            $hours = [
                0,1,2,3,4,5,6,7,8,9,10,11,
                12,13,14,15,16,17,18,19,20,21,22,23
            ];
            foreach($hours as $hour){
                $base_time = new DateTime(date("Y-m-d 00:00:00"));
                $schedule_dt = $base_time->modify("+".$hour." hour")->format("Y-m-d H:i:S");

                // add task 1
                $task1 = new SmartSyncTask();
                $task1->subject_type = "SmartSync:Products";
                $task1->subject_params = "all";
                $task1->status = SmartSyncTask::TASK_STATUS_QUEUED;
                $task1->created_by = 6;
                $task1->smart_queue = SmartSyncTask::TASK_QUEUE_SCHEDULED;
                $task1->scheduled_at = $schedule_dt;
                $task1->save();

                // add task 3
                $task3 = new SmartSyncTask();
                $task3->subject_type = "SmartSync:Products";
                $task3->subject_params = "delete";
                $task3->status = SmartSyncTask::TASK_STATUS_QUEUED;
                $task3->created_by = 6;
                $task3->smart_queue = SmartSyncTask::TASK_QUEUE_SCHEDULED;
                $task3->scheduled_at = $schedule_dt;
                $task3->save();

                if(in_array($hour,[1,4,7,19,22])){
                    // add task 2
                    $task2 = new SmartSyncTask();
                    $task2->subject_type = "SmartSync:Products";
                    $task2->subject_params = "inventory";
                    $task2->status = SmartSyncTask::TASK_STATUS_QUEUED;
                    $task2->created_by = 6;
                    $task2->smart_queue = SmartSyncTask::TASK_QUEUE_SCHEDULED;
                    $task2->scheduled_at = $schedule_dt;
                    $task2->save();
                }
                if($hour % 2 == 0){
                    // add task 4
                    $task4 = new SmartSyncTask();
                    $task4->subject_type = "SmartSync:Products";
                    $task4->subject_params = "tierprice";
                    $task4->status = SmartSyncTask::TASK_STATUS_QUEUED;
                    $task4->created_by = 6;
                    $task4->smart_queue = SmartSyncTask::TASK_QUEUE_SCHEDULED;
                    $task4->scheduled_at = $schedule_dt;
                    $task4->save();
                }
            }
        })->name('schedule_registration_worker')->daily();

        /**
         * This function call will run all the scheduled task registered through SmartSyncTask object.
         */
        $schedule->call(function(){
            $task = SmartSyncTask::where('status','queued')
                ->where('scheduled_at','<=',date("Y-m-d H:i:s"))
                ->orderBy('scheduled_at')
            ->first();
            if(!empty($task)){
                $task->status = SmartSyncTask::TASK_STATUS_PROCESSING;
                $task->save();
                if($task->subject_type == "SmartSync:Products"){
                    SmartSyncHelper::smartSyncManager($task->subject_params, $task);
                }
                else{
                    Artisan::call($task->subject_type . " " . $task->subject_params);
                }
                $task->status = SmartSyncTask::TASK_STATUS_COMPLETED;
                $task->save();
            }
        })->name('on_demand')->withoutOverlapping()->everyMinute();

        $schedule->call(function(){
            $wooutil = new WoocommerceUtil(new TransactionUtil(),new ProductUtil());
            $wooutil->syncOrders(4, 6);
            $wooutil->syncBrands(4, 6);
            $wooutil->syncCategories(4, 6);
        })->hourly();

        $schedule->call(function(){
            $current_time = date('H:i');
            $current_day = date('w');

            if ($current_day != 0 && strtotime($current_time) >= strtotime('05:00') && strtotime($current_time) <= strtotime('15:00')) {
                TransactionHelper::checkMismatchInvoices();
            }
        })->everyFiveMinutes();

        $schedule->call(function(){
            $expired_transactions = Transaction::where('token_expire_at','<=',date('Y-m-d'))->update([
                'invoice_token' => null,
                'token_expire_at' => null
            ]);
            TransactionHelper::updateContactsTransactionDate();
        })->daily();

        $schedule->command('SmartSync:Products reconsile')->name('reconsile_daily')->daily();

        $schedule->call(function () {
            $controller = new \App\Http\Controllers\InvoiceSchemeController;
            $controller->theInvoiceRepairer();
        })->everyMinute();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
