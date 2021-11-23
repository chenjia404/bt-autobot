<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MintService;

class qbt_qki extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qbt_qki {group_id} {amount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $group_id = $this->argument('group_id');
        $amount = $this->argument('amount');
        if($group_id <= 0)
            exit("group_id错误\n");
        $CollectionService = new MintService('qki');
        try
        {
            $CollectionService->qbt_qki($group_id,$amount);

        }
        catch (\Exception $exception)
        {
            echo $exception->getMessage();
        }
    }
}
