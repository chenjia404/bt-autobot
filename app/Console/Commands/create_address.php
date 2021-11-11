<?php

namespace App\Console\Commands;

use App\Models\Address;
use Illuminate\Console\Command;

class create_address extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create_address {group_id} {amount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建地址';

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
        $amount = $this->argument('amount');
        if($amount <= 0)
            exit("amount错误\n");

        for ($i=0 ;$i< $amount;$i++)
        {
            $credential = \EthTool\Credential::new();
            $address = new Address();
            $address->address = $credential->getAddress();
            $address->private_key = $credential->getPrivateKey();
            $address->group_id = $group_id;
            $address->save();
        }
        echo  "创建地址成功\n";
    }
}
