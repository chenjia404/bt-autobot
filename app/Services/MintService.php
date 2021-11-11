<?php


namespace App\Services;


use App\Models\Address;
use App\Models\AddressGroup;
use ERC20\ERC20;
use EthereumRPC\Contracts\ABI;
use EthereumRPC\EthereumRPC;
use EthTool\Credential;
use EthTool\RawTxBuilder;
use quarkblockchain\QkNodeRPC;

class MintService
{

    protected $qk_node;


    public function __construct($net_type = 'qki')
    {
        if($net_type == 'eth')
        {
            $url = env('ETH_RPC_HOST');
        }elseif($net_type == 'heco'){
            $url = env('HECO_RPC_HOST');
        }
        else{
            $url = env('RPC_HOST');
        }
//        if($net_type == 'heco'){
//            $url_arr['port'] = 443;
//        }
        $url_arr = parse_url($url);
        $this->qk_node = new QkNodeRPC($url_arr['host'],$url_arr['port']??443);
    }



    public function mint_hbtv5($group_id)
    {
        $group = AddressGroup::find($group_id);
        $chainid = "128";
        $contract_address = '0x506CcdB45d67349b6E4c59b220c0e790B1264D8a';
        $gasPrice = '3000000001';


        $real_last_block = (new RpcService())->rpc('eth_getBlockByNumber', [['latest', true]],'heco');
        $block_time = base_convert($real_last_block[0]['result']['timestamp'],16,10);
        if(time() - $block_time > 20 )
        {
            echo "区块没有同步\n";
            sleep(20);
            return;
        }


        $rpc = new RpcService();


        $addresses = Address::where('group_id',$group_id)
//            ->where('nonce','<','4')
            ->orderBy('last_check_time','asc')
            ->orderBy('updated_at','asc')
            ->limit(500)
            ->get();


        $erc20 = new ERC20($this->qk_node);
        $erc20->abiPath('./public/contract/hbt.abi');
        $token = $erc20->token($contract_address);

        foreach ($addresses as $address)
        {
            if(time() - $address->last_check_time < 300)
            {
                echo "最新更新过\n";
                continue;
            }
            $nonce = $rpc->getTransactionCount($address->address,"heco");
            if($nonce != $address->nonce)
            {
                $address->nonce = $nonce;
                $address->save();
                echo "更新nonce\n";
            }


            $qki_amount = $this->qk_node->QKI()->getBalance($address->address);

            //需要余额大于1才挖矿
            if(bccomp($qki_amount,'0.002',8) >= 0)
            {

                $address_qki_credential = Credential::fromKey($address->private_key);

                $power = $token->call("power", [$address->address]);


                $address->power = $power[0];
                $address->save();


                $abi = file_get_contents('./public/contract/hbt.abi');
                $balanceOf = $token->call("balanceOf", [$address->address]);

                if($balanceOf[0] >= 100 && $power[0] > 20000000)
                {
                    //燃烧bt
                    if($balanceOf[0] > 100)
                    {
                        $rtb = RawTxBuilder::create()
                            ->credential($address_qki_credential)
                            ->gasLimit('150000')
                            ->gasPrice($gasPrice)
                            ->chainId($chainid)
                            ->nonce((int)$address->nonce)
                            ->contract($abi)  //创建合约对象
                            ->at($contract_address)       //设置合约对象的部署地址
                            ->getSendTx('burn',$balanceOf[0]);
                        $data = (new RpcService())->rpc('eth_sendRawTransaction', [[$rtb]],"heco");


                        if(isset($data[0]['error']))
                        {
                            echo $data[0]['error']['message'] . "\n";
                            continue;
                        }
                        else
                        {
                            $address->nonce++;
                            $address->save();
                            echo "燃烧:" . $data[0]['result'] . "\n";
                            continue;
                        }

                    }

                }


                //进行挖矿
                $last_miner = $token->call("last_miner", [$address->address]);
                $mint_time = $last_miner[0] + 86400 + (time()-1614086139)/365;
                if(time() - $last_miner[0] < 86400 + (time()-1614086139)/365)
                {

                    //刷新更新时间，避免阻塞
                    $address->last_check_time = $last_miner[0];
                    $address->save();
                    echo "下次挖矿时间:" . date("Y-m-d H:i:s",$mint_time) . " ";
                    echo "跳过\n";
                    continue;
                }
                $rtb = RawTxBuilder::create()
                    ->credential($address_qki_credential)
                    ->gasLimit('150000')
                    ->gasPrice($gasPrice)
                    ->chainId($chainid)
                    ->nonce((int)$address->nonce)
                    ->to($contract_address)                    //设置交易接收账户
                    ->value("0")              //设置交易量，1kwei，单位wei
                    ->data("0x1249c58b")
                    ->getPlainTx();	             //获取裸交易码流

                $data = (new RpcService())->rpc('eth_sendRawTransaction', [[$rtb]],'heco');


                if(isset($data[0]['error']))
                {
                    echo "挖矿:" . $data[0]['error']['message'] . "\n";
                    continue;
                }
                else
                {

                    $address->nonce++;
                    $address->save();
                    echo "挖矿:" . $data[0]['result'] . "\n";
                }
            }
        }
    }


    
    public function mint_qbt($group_id,$amount = 500)
    {
        $group = AddressGroup::find($group_id);
        $chainid = "20181205";
        $net_type = 'qki';
        $contract_address = '0xdBA7683d7F14cC9C18AE1777A518e49885B55889';
        $anti_bot_a = '1000000000000000000';
        $gasPrice = '150000000000';
        $min_gas = bcmul('1000000000000000000','0.1',0);


        $real_last_block = (new RpcService())->rpc('eth_getBlockByNumber', [['latest', true]],$net_type);
        $block_time = base_convert($real_last_block[0]['result']['timestamp'],16,10);
        if(time() - $block_time > 20 )
        {
            echo "区块没有同步\n";
            sleep(20);
            return;
        }

        $qki_credential = \EthTool\Credential::fromKey($group->private_key);

        $qki_amount = $this->qk_node->QKI()->getBalance($qki_credential->getAddress());
        echo $qki_credential->getAddress() . " 矿工费:{$qki_amount}QKI\n";


        $rpc = new RpcService();
        $qki_address_nonce = $rpc->getTransactionCount($qki_credential->getAddress(),$net_type);
        if($qki_address_nonce != $group->address_nonce)
        {
            $group->address_nonce = $qki_address_nonce;
            $group->save();
            echo "更新nonce\n";
        }

        $min_nonce = 11;
        if($group_id > 1)
            $min_nonce = 0;
        $addresses = Address::where('group_id',$group_id)
            ->where('nonce','>=',$min_nonce)
            ->orderBy('id','asc')
            ->orderBy('updated_at','asc')
            ->limit($amount)
            ->get();

        $abi = file_get_contents('./public/contract/qbt.abi');

        $erc20 = new ERC20($this->qk_node);
        $erc20->abiPath('./public/contract/qbt.abi');
        $token = $erc20->token($contract_address);

        foreach ($addresses as $address)
        {
            if(time() - $address->last_check_time < 300)
            {
                echo "最新更新过\n";
                continue;
            }
            $nonce = $rpc->getTransactionCount($address->address,$net_type);
            if($nonce != $address->nonce)
            {
                $address->nonce = $nonce;
                $address->save();
                echo "更新nonce\n";
            }


            $qki_amount = $this->qk_node->QKI()->getBalance($address->address);
            $gas_qki_amount = $this->qk_node->QKI()->getBalance($qki_credential->getAddress());
            $power = $token->call("power", [$address->address]);

            //需要余额大于1才挖矿
            if(bccomp($qki_amount,'0.1',8) >= 0)
            {

                $address_qki_credential = Credential::fromKey($address->private_key);
                if($power[0] < 100)
                {
                    echo "算力不足\n";
                    $rtb = RawTxBuilder::create()
                        ->credential($address_qki_credential)
                        ->gasLimit('100000')
                        ->gasPrice($gasPrice)
                        ->chainId($chainid)
                        ->nonce((int)$address->nonce)
                        ->contract($abi)  //创建合约对象
                        ->at($contract_address)       //设置合约对象的部署地址
                        ->getSendTx('airdrop');

                    $data = (new RpcService())->rpc('eth_sendRawTransaction', [[$rtb]],$net_type);
                    sleep(1);


                    if(isset($data[0]['error']))
                    {
                        echo "领取空投:";
                        echo $data[0]['error']['message'] . "\n";
                        continue;
                    }
                    else
                    {
                        $address->nonce++;
                        $address->save();
                        echo "领取空投:" . $data[0]['result'] . "\n";
                        continue;
                    }
                    continue;
                }
                elseif($power[0] >= 90000)
                {
                    $address->power = $power[0];
                    $address->save();

                    $TokenBalanceOf = $token->call("CoinBalanceOf", [$address->address]);
                    //todo 检查质押
                    $TokenBalance = $TokenBalanceOf[0];
                    if(bccomp($TokenBalance,$anti_bot_a,0) < 0)
                    {
                        echo "{$address->address}补充质押{$TokenBalance} 当前qki $qki_amount\n";

                        //检查qki余额
                        if(bccomp(bcmul($qki_amount,"1000000000000000000"),bcadd($anti_bot_a,$min_gas,0),2) < 0)
                        {
                            //计算需要的qki数量
                            $need_qki_amount = bcsub($anti_bot_a,$TokenBalance,0);
                            $send_qki_amount = bcsub($need_qki_amount,bcmul($qki_amount,"1000000000000000000"),0);
                            $send_qki_amount = bcadd($send_qki_amount,$min_gas,0);
                            if( bccomp($send_qki_amount,0) > 0)
                            {
                                //如果需要的矿工费不多，直接补充一个标准
                                if (bccomp($send_qki_amount,$min_gas) < 0)
                                    $send_qki_amount = $min_gas;
                                echo "挖矿账户{$address->address} qki质押不足,开始补充 矿工费账户:{$gas_qki_amount}\n";
                                $rtb = RawTxBuilder::create()
                                    ->credential($qki_credential)
                                    ->gasLimit('200000')
                                    ->gasPrice($gasPrice)
                                    ->chainId($chainid)
                                    ->nonce((int)$group->address_nonce)
                                    ->to($address->address)                    //设置交易接收账户
                                    ->value($need_qki_amount)              //补充到1.01qki
                                    ->getPlainTx();	             //获取裸交易码流
    
    
    
                                $data = (new RpcService())->rpc('eth_sendRawTransaction', [[$rtb]],$net_type);
    
                                if(isset($data[0]['error']))
                                {
                                    echo $data[0]['error']['message'] . "\n";
                                    //刷新更新时间，避免阻塞
                                    $address->nonce++;
                                    $address->save();
                                    sleep(1);
                                    continue;
                                }
                                else
                                {
    
                                    $group->address_nonce++;
                                    $group->save();
    
                                    //刷新更新时间，避免阻塞
                                    $address->nonce++;
                                    $address->save();
                                    sleep(2);
                                    echo $data[0]['result'] . "\n";
                                    continue;
                                }
                            }
                        }
                        else
                        {
                            //计算需要的qki数量
                            $send_qki_amount = bcsub($anti_bot_a,$TokenBalance,0);
                            $rtb = RawTxBuilder::create()
                                    ->credential($address_qki_credential)
                                    ->gasLimit('100000')
                                    ->gasPrice($gasPrice)
                                    ->chainId($chainid)
                                    ->nonce((int)$address->nonce)
                                    ->value($send_qki_amount)
                                    ->contract($abi)  //创建合约对象
                                    ->at($contract_address)       //设置合约对象的部署地址
                                    ->getSendTx('deposit');

                                $data = (new RpcService())->rpc('eth_sendRawTransaction', [[$rtb]],$net_type);
                                sleep(1);
                                if(isset($data[0]['error']))
                                {
                                    echo "质押qki:";
                                    echo $data[0]['error']['message'] . "\n";
                                    continue;
                                }
                                else
                                {
                                    $address->nonce++;
                                    $address->save();
                                    echo "质押qki:" . $data[0]['result'] . "\n";
                                    continue;
                                }
                        }
                    }
                }

                elseif($power[0] > 1000)
                {
                    $address->power = $power[0];
                    $address->save();
                }

                $balanceOf = $token->call("balanceOf", [$address->address]);

                if($balanceOf[0] >= 100)
                {
                    //归集bt
                    if($balanceOf[0] > 1000)
                    {
                        $send_bt = bcmul($balanceOf[0],"0.665",0);
                        $rtb = RawTxBuilder::create()
                            ->credential($address_qki_credential)
                            ->gasLimit('120000')
                            ->gasPrice($gasPrice)
                            ->chainId($chainid)
                            ->nonce((int)$address->nonce)
                            ->contract($abi)  //创建合约对象
                            ->at($contract_address)       //设置合约对象的部署地址
                            ->getSendTx('transfer',$group->collection_address,$send_bt);

                        $data = (new RpcService())->rpc('eth_sendRawTransaction', [[$rtb]],$net_type);
                        sleep(1);

                        if(isset($data[0]['error']))
                        {
                            echo "归集bt:";
                            echo $data[0]['error']['message'] . "\n";
                            continue;
                        }
                        else
                        {
                            $address->nonce++;
                            $address->save();
                            echo "归集bt:" . $data[0]['result'] . "\n";
                            continue;
                        }
                    }
                    elseif($balanceOf[0] < 100)
                    {
                        $rtb = RawTxBuilder::create()
                            ->credential($address_qki_credential)
                            ->gasLimit('150000')
                            ->gasPrice($gasPrice)
                            ->chainId($chainid)
                            ->nonce((int)$address->nonce)
                            ->contract($abi)  //创建合约对象
                            ->at($contract_address)       //设置合约对象的部署地址
                            ->getSendTx('burn',$balanceOf[0]);
                        $data = (new RpcService())->rpc('eth_sendRawTransaction', [[$rtb]],$net_type);
                        sleep(1);


                        if(isset($data[0]['error']))
                        {
                            echo $data[0]['error']['message'] . "\n";
                            continue;
                        }
                        else
                        {
                            $address->nonce++;
                            $address->save();
                            echo "燃烧:" . $data[0]['result'] . "\n";
                            continue;
                        }

                    }

                }

                //进行挖矿
                $last_miner = $token->call("last_miner", [$address->address]);
                if(time() - $last_miner[0] < 86400 + (time()-1636442691 )/365)
                {

                    //刷新更新时间，避免阻塞
                    $address->last_check_time = $last_miner[0];
                    $address->save();
                    continue;
                }
                $rtb = RawTxBuilder::create()
                    ->credential($address_qki_credential)
                    ->gasLimit('150000')
                    ->gasPrice($gasPrice)
                    ->chainId($chainid)
                    ->nonce((int)$address->nonce)
                    ->to($contract_address)                    //设置交易接收账户
                    ->value("0")              //设置交易量，1kwei，单位wei
                    ->data("0x1249c58b")
                    ->getPlainTx();	             //获取裸交易码流

                $data = (new RpcService())->rpc('eth_sendRawTransaction', [[$rtb]],$net_type);
                sleep(1);


                if(isset($data[0]['error']))
                {
                    echo "挖矿:" . $data[0]['error']['message'] . "\n";
                    continue;
                }
                else
                {

                    $address->nonce++;
                    $address->save();
                    echo "挖矿:" . $data[0]['result'] . "\n";
                }
            }
            else //补充qki
            {
                //计算需要的qki数量
                $send_qki_amount = bcmul('1000000000000000000',bcsub('0.1',$qki_amount,8),0);
                //如果需要的矿工费不多，直接补充一个标准
                if (bccomp($send_qki_amount,$min_gas) < 0)
                $send_qki_amount = $min_gas;

                //如果是0算力，没有矿工费，就需要转矿工费+质押
                if($power[0] < 100)
                {
                    $send_qki_amount = bcadd($send_qki_amount,$anti_bot_a);
                }

                echo "挖矿账户{$address->address} qki不足,开始补充 矿工费账户:{$gas_qki_amount}\n";
                $rtb = RawTxBuilder::create()
                    ->credential($qki_credential)
                    ->gasLimit('200000')
                    ->gasPrice($gasPrice)
                    ->chainId($chainid)
                    ->nonce((int)$group->address_nonce)
                    ->to($address->address)                    //设置交易接收账户
                    ->value($send_qki_amount)              //补充到1.01qki
                    ->getPlainTx();	             //获取裸交易码流



                $data = (new RpcService())->rpc('eth_sendRawTransaction', [[$rtb]],$net_type);

                if(isset($data[0]['error']))
                {
                    echo $data[0]['error']['message'] . "\n";
                    //刷新更新时间，避免阻塞
                    $address->nonce++;
                    $address->save();
                    sleep(1);
                }
                else
                {

                    $group->address_nonce++;
                    $group->save();

                    //刷新更新时间，避免阻塞
                    $address->nonce++;
                    $address->save();
                    sleep(2);
                    echo $data[0]['result'] . "\n";
                }
            }
        }
    }
}