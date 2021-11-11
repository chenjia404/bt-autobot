<?php

namespace EthTool;

use GuzzleHttp\Client;

class EthApiEtherscan extends EthApi{
	protected $apiKey;
	protected $apiBase;
	protected $client;
	
	public function __construct($apiKey,$chainId=1){
		$this->client = new Client();
		
		$this->apiKey = $apiKey;
		
		if($chainId == 1) $this->apiBase = 'https://api.etherscan.io/api';
		else if($chainId == 3) $this->apiBase = 'https://api-ropesten.etherscan.io/api';
		else if($chainId ==4) $this->apiBase = 'https://api-rinkeby.etherscan.io/api';
	}
	private function exec($cmd){
		$payload = [
			'form_params' => array_merge([
				'apikey'=>$this->apiKey,
				'module'=>'proxy',
			],$cmd)
		];
		
		$rsp = $this->client->post($this->apiBase,$payload);
		$ret = json_decode($rsp->getBody());
		return $ret->result;
	}
	public function getTransactionCount($address){
		$cmd = [
			'action'=>'eth_getTransactionCount',
			'address'=>$address,
			'tag'=>'latest'
		];
		return $this->exec($cmd);
	}
	public function sendRawTransaction($rawtx){
		$cmd = [
			'action'=>'eth_sendRawTransaction',
			'hex'=>$rawtx
		];
		return $this->exec($cmd);
	}
	public function getTransactionReceipt($txid){
		$cmd = [
			'action'=>'eth_getTransactionReceipt',
			'txhash'=>$txid
		];
		return $this->exec($cmd);
	}
	
}