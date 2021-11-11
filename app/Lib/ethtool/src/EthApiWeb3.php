<?php

namespace EthTool;

use Web3\Web3;
use Web3\Utils;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

class EthApiWeb3 extends EthApi{
	protected $web3;
	
	public function __construct($url,$timeout=60){
    $this->web3  = new Web3(new HttpProvider(new HttpRequestManager($url,$timeout)));
	}
	public function getTransactionCount($address){
		$cb = new Callback;
		try{
			$this->web3->eth->getTransactionCount($address,'pending',$cb);
			return Utils::toHex($cb->result,true);
		}catch(Exception $e){
			echo 'error getTransactionCount  => ' . $e . PHP_EOL;
		}
	}
	public function sendRawTransaction($rawtx){
		$cb = new Callback;
		try{
			$this->web3->eth->sendRawTransaction($rawtx,$cb);
			return $cb->result;
		}catch(Exception $e){
			echo 'error sendRawTransaction  => ' . $e . PHP_EOL;
		}
	}
	public function getTransactionReceipt($txid){
		$cb = new Callback;
		try{
			$this->web3->eth->getTransactionReceipt($txid,$cb);
			return $cb->result;
		}catch(Exception $e){
			echo 'error getTransactionReceipt  => ' . $e . PHP_EOL;
		}
	}
	
}