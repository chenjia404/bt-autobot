<?php

namespace App\Services;


use HttpClient\Exception\RequestException;

class RpcService
{
    /**
     * rpc
     * @param $method
     * @param $params
     * @param $net_type
     * @return mixed
     */
    public function rpc($method, $params, $net_type = 'qki')
    {
        $param = array();
        foreach ($params as $key => $item)
        {
            $id = rand(1,100);
            $param[$key] = [
                'jsonrpc'=>"2.0",
                "method"=>$method,
                "params"=>$item,
                "id"=>$id
            ];
        }

        $param = json_encode($param);
        $data_str = $this->curlPost($param,$net_type);
        $data = json_decode($data_str,true);

        return $data;
    }

    public function rpc1($method, $param)
    {
        $id = rand(1, 100);
        $rpc_param = [
            'jsonrpc' => "2.0",
            "method" => $method,
            "params" => $param,
            "id" => $id
        ];
        $curl_param = json_encode($rpc_param);
        $data_str = $this->curlPost($curl_param);
        $data = json_decode($data_str, true);

        return $data;
    }


    /**
     * 获得区块
     * @param $param
     * @return mixed
     */
    public function getBlockByNumber($param,$net_type = 'qki')
    {
        $block = $this->rpc('eth_getBlockByNumber',$param,$net_type);
        return $block;
    }

    /**
     * 获取最后一个区块的高度
     * @return mixed
     */
    public function lastBlockHeightNumber()
    {
        $params = array(
            ['latest',true]
        );
        $blockHeight = $this->rpc('eth_getBlockByNumber',$params);

        $timestamp = base_convert($blockHeight[0]['result']['timestamp'],16,10);
        if($timestamp < time() -20)
            return 0;
        return base_convert($blockHeight[0]['result']['number'],16,10);
    }

    /**
     * 获取区块数组
     * @param $lastBlock
     * @return array
     */
    public function getBlockString($lastBlock)
    {
        $blockArray = array();
        for($i=0;$i<20;$i++)
        {
            $blockArray[$i] = ['0x'.base_convert($lastBlock--,10,16),true];
        }
        return $blockArray;
    }

    /**
     * 根据hash获取区块详情
     * @param $hash
     * @return mixed
     */
    public function getBlockByHash($hash)
    {
        $method = 'eth_getBlockByHash';
        $param = array(
            [$hash,true]
        );
        $blockInfo = $this->rpc($method,$param);
        return $blockInfo[0];
    }

    /**
     * 根据hash获取区块详情
     * @param $hash
     * @return mixed
     */
    public function getTransactionCount($address,$net_type = 'qki')
    {
        $method = 'eth_getTransactionCount';
        $param = array(
            [$address,'latest']
        );
        $blockInfo = $this->rpc($method,$param,$net_type);
        return base_convert($blockInfo[0]['result'],16,10);
    }

    /**
     * post请求
     * @param $data
     * @param $net_type
     * @return mixed
     */
    public function curlPost($data,$net_type = 'qki')
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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept-Encoding:gzip'
        ));
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        $output = curl_exec($ch);
        if ($output === false) {
            throw new RequestException(
                sprintf('cURL error [%d]: %s', curl_error($ch), curl_error($ch))
            );
        }
        curl_close($ch);
        return $output;
    }
}
