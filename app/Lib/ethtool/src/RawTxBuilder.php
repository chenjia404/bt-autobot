<?php
namespace EthTool;

use Web3\Utils;

class RawTxBuilder{
	protected $credential;
	protected $tx = [];
	
	static function create(){
		return new self();
	}
	
	public function credential($credential){
		$this->credential = $credential;
		return $this;
	}
	public function reset(){
		$this->tx = [];
	}
	public function chainId($chainId){
		$this->tx['chainId'] = $chainId;
		return $this;
	}
	public function nonce($nonce){
		$this->tx['nonce'] = $nonce;
		return $this;
	}
	public function gasLimit($limit){
		$limit = Utils::toHex(Utils::toBn($limit),true);
		$this->tx['gasLimit'] = $limit;
		return $this;
	}
	public function gasPrice($price){
		$price = Utils::toHex(Utils::toBn($price),true);
		$this->tx['gasPrice'] = $price;
		return $this;
	}
	public function from($from){
		$this->tx['from'] = strtolower($from);
		return $this;
	}
	public function to($to){
		$this->tx['to'] = strtolower($to);
		return $this;
	}
	public function value($value){
		$this->tx['value'] = Utils::toHex(Utils::toBn($value),true);
		return $this;
	}
	public function data($data){
		$this->tx['data'] = $data;
		return $this;
	}
	public function getPlainTx(){
		if(!$this->credential) throw new Exception('credential required but not set');
		return $this->credential->signTransaction($this->tx);
	}
	public function contract($abi){
		if(!$this->credential) throw new Exception('credential required but not set');
		$provider = new DummyProvider();
		$contract  = new RawContract($provider,$abi);
		$contract->credential($this->credential)->transaction($this->tx);
		return $contract;
	}	
}