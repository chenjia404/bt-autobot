<?php

namespace App\Console\Commands;

use App\Models\Address;
use Illuminate\Console\Command;

class export_address extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export_address {group_id} {amount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导出地址';

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

        $address = Address::whereIsExport(0)
            ->where('group_id',$group_id)
            ->limit($amount)->get();

        $filename = "address.{$group_id}." . date("Y-m-dHis") . ".txt";
        foreach ($address as $item) {
            $item->is_export = 2;
            $item->save();
            file_put_contents($filename,$item->address . "\n",FILE_APPEND);
            echo "";
        }
    }
}
