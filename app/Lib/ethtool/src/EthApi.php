<?php
namespace EthTool;

abstract class EthApi {
	abstract public function getTransactionCount($address);
	abstract public function sendRawTransaction($raw);
	abstract public function getTransactionReceipt($txid);
	
	public function waitForTransactionReceipt($txid,$timeout=600,$interval=15){
		$t0 = time();
		while(true){
			$receipt = $this->getTransactionReceipt($txid);
			if($receipt) return $receipt;
			
			if((time() - $t0) > $timeout) break;
			sleep($interval);  
		}
	}
}