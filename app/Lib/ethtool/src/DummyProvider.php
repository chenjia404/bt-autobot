<?php
namespace EthTool;

use Web3\Providers\Provider;
use Web3\Providers\IProvider;

class DummyProvider extends Provider implements IProvider{
	public function __construct(){}
	public function send($method, $callback){}
	public function batch($status){}
	public function execute($callback){}
}