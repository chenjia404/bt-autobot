<?php
namespace EthTool;

use Web3\Web3;
use Web3\Contract;
use Web3\Utils;

class RawContract extends Contract{
  protected $credential;
	protected $tx = [];
    
  public function credential($credential){
    $this->credential = $credential;
		return $this;
  }
	
	public function transaction($tx){
		$this->tx = $tx;
		return $this;
	}
  
  public function getDeployTx(){
    if (isset($this->constructor)) {
      $constructor = $this->constructor;
      $arguments = func_get_args();

      if (count($arguments) < count($constructor['inputs'])) {
          throw new InvalidArgumentException('Please make sure you have put all constructor params and callback.');
      }
      if (!isset($this->bytecode)) {
          throw new \InvalidArgumentException('Please call bytecode($bytecode) before new().');
      }            
      $params = array_splice($arguments, 0, count($constructor['inputs']));
      $data = $this->ethabi->encodeParameters($constructor, $params);
      
			$transaction = $this->tx;

      $transaction['data'] = '0x' . $this->bytecode . Utils::stripZero($data);

      $transaction['from'] = $this->credential->getAddress();

      
      $signed = $this->credential->signTransaction($transaction);
			return $signed;
    }
  }
  
  public function getSendTx(){
    if (isset($this->functions)) {
      $arguments = func_get_args();
      $method = array_splice($arguments, 0, 1)[0];

      if (!is_string($method) || !isset($this->functions[$method])) {
        throw new InvalidArgumentException('Please make sure the method exists.');
      }
      $function = $this->functions[$method];

      if (count($arguments) < count($function['inputs'])) {
        throw new InvalidArgumentException('Please make sure you have put all function params and callback.');
      }
      $params = array_splice($arguments, 0, count($function['inputs']));
      $data = $this->ethabi->encodeParameters($function, $params);
      $functionName = Utils::jsonMethodToString($function);
      $functionSignature = $this->ethabi->encodeFunctionSignature($functionName);
      $transaction = $this->tx;
      
      $transaction['to'] = $this->toAddress;
      $transaction['data'] = $functionSignature . Utils::stripZero($data);

      $transaction['from'] = $this->credential->getAddress();

      $signed = $this->credential->signTransaction($transaction);
			
			return $signed;
    }  
  }

}

