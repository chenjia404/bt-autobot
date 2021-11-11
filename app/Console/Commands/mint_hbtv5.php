<?php

namespace App\Console\Commands;

use App\Models\Address;
use App\Models\AddressGroup;
use App\Models\Setting;
use App\Services\CollectionService;
use App\Services\MintService;
use App\Services\NewSyncService;
use App\Services\RpcService;
use EthTool\Credential;
use EthTool\RawTxBuilder;
use Illuminate\Console\Command;
use mysql_xdevapi\Exception;

class mint_hbtv5 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mint_hbtv5  {group_id}';

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
     * @return mixed
     */
    public function handle()
    {
        $group_id = $this->argument('group_id');
        if($group_id <= 0)
            exit("group_id错误\n");
        $CollectionService = new MintService('heco');
        try
        {
            $CollectionService->mint_hbtv5($group_id);
        }
        catch (\Exception $exception)
        {
            echo $exception->getMessage();
        }
    }
}
