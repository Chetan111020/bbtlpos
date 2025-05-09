<?php

namespace App\Console\Commands;

use App\Helpers\SmartSyncHelper;
use Illuminate\Console\Command;

class SmartSyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SmartSync:Products {sync_type=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync products from ERP to Website';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        SmartSyncHelper::smartSyncManager($this->argument('sync_type'));
        $this->info("\n\nRequest Processed");
    }
}
