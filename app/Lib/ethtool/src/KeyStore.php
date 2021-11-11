<?php

namespace EthTool;

use Elliptic\EC;
use kornrunner\Keccak;

define("KeyVersion3", 3);

class KeyStore{
  static function save($privateKey,$password,$dir){ 
      $address = self::privateToAddress($privateKey);
      $opts = [];
      try{
          $salt = isset($opts['salt']) ? $opts['salt'] : random_bytes(32);
          $iv = isset($opts['iv']) ? $opts['iv'] : random_bytes(16);
      } catch (\Exception $e){
          throw $e;
      }
      $kdf = isset($opts['kdf']) ? $opts['kdf'] : "scrypt";
      $kdfparams = array(
          "dklen" => isset($opts['dklen']) ? $opts['dklen'] : 32,
          'salt' => bin2hex($salt),
      );
      if($kdf === 'pbkdf2'){
          $kdfparams['c'] = isset($opts['c']) ? $opts['c'] : 262144;
          $kdfparams['prf'] = 'hmac-sha256';
          $derivedKey = hash_pbkdf2("sha256", $password, $salt, $kdfparams['c'], $kdfparams['dklen'] * 2, false );
      }else if($kdf = 'scrypt'){
          $kdfparams['n'] = isset($opts['n']) ? $opts['n'] : 4096;
          $kdfparams['r'] = isset($opts['r']) ? $opts['r'] : 8;
          $kdfparams['p'] = isset($opts['p']) ? $opts['p'] : 1;
          $derivedKey =  self::getScrypt($password, $salt , $kdfparams['n'],$kdfparams['r'],$kdfparams['p'],$kdfparams['dklen']);
      }else{
          throw new \Exception('Unsupported kdf');
      }
      $derivedKeyBin = hex2bin($derivedKey); 
      $method = 'aes-128-ctr';
      $ciphertext = openssl_encrypt(hex2bin($privateKey), $method, substr($derivedKeyBin,0,16),$options=1 , $iv); 
			$mac = HasherV3::hash(substr($derivedKeyBin,16,32) . $ciphertext);
      try{
          $uuid = self::guidv4(random_bytes(16));
      }catch (\Exception $e){
          throw $e;
      }
      $json = array(
          "version" => KeyVersion3,
          "id" => $uuid,
          "address" => $address,
          'crypto' => array(
              'ciphertext' => bin2hex($ciphertext),
              'cipherparams' => array(
                  'iv' => bin2hex($iv),
              ),
              'cipher' => $method,
              'kdf' => $kdf,
              'kdfparams' => $kdfparams,
              'mac' => $mac,
          ),
      );
    
      $txt = json_encode($json);
			$wallet = $dir . '/' . self::getFileName($address);
      file_put_contents($wallet,$txt);
      return $wallet;
  }


  static function load($password,$wallet){
      $input = file_get_contents($wallet);
      
      $json = json_decode($input);
      if($json->version !== KeyVersion3)
          throw new \Exception('Not supported wallet version');
      if($json->crypto->kdf === 'scrypt'){
          $kdfparams = $json->crypto->kdfparams;
          $derivedKey =  self::getScrypt($password, hex2bin($kdfparams->salt) , $kdfparams->n,$kdfparams->r,$kdfparams->p,$kdfparams->dklen); //hex string
      }else if($json->crypto->kdf === 'pbkdf2'){
          $kdfparams = $json->crypto->kdfparams;
          $derivedKey = hash_pbkdf2("sha256", $password, hex2bin($kdfparams->salt), $kdfparams->c, $kdfparams-> dklen * 2, false );
      }else{
          throw new \Exception('Unsupported key derivation scheme');
      }
      $derivedKeyBin = hex2bin($derivedKey);
      $ciphertext = hex2bin($json->crypto->ciphertext);
      $method = $json->crypto->cipher;
      $iv = hex2bin($json->crypto->cipherparams->iv);
			$mac = HasherV3::hash(substr($derivedKeyBin,16,32) . $ciphertext);
      if($mac !== $json->crypto->mac){
          throw new \Exception('Key derivation failed - possibly wrong passphrase');
      }
      $seed = openssl_decrypt($ciphertext, $method, substr($derivedKeyBin,0,16), $options=1, $iv);
      if(strlen($seed) < 32){
          $string = hex2bin("00000000"."00000000"."00000000"."00000000"."00000000"."00000000"."00000000"."00000000").$seed;
          $seed = substr($string,-32);
      }
      return bin2hex($seed);
  }
  
	static function getFileName($address){
		$date = new \DateTime();
		$dateStr = $date->format('Y-m-d\TH-i-s.u000\Z');
		$fileName = sprintf('UTC--%s--%s',$dateStr,$address);
		return $fileName;
	}
	
  static function privateToAddress($priv_hex){
      $ec = new EC('secp256k1');
      $keyPair = $ec->keyFromPrivate($priv_hex);
      $public = $keyPair->getPublic()->encode('hex');
      return substr(Keccak::hash(substr(hex2bin($public), 1), 256), 24);
  }
  
  static function  guidv4($data) {
      assert(strlen($data) == 16);
      $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
      $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
      return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }
    
  static function getScrypt($password, $salt, $N, $r, $p, $kdlen) {
    if ($N == 0 || ($N & ($N - 1)) != 0) {
        throw new \InvalidArgumentException("N must be > 0 and a power of 2");
    }
    if ($N > PHP_INT_MAX / 128 / $r) {
        throw new \InvalidArgumentException("Parameter N is too large");
    }
    if ($r > PHP_INT_MAX / 128 / $p) {
        throw new \InvalidArgumentException("Parameter r is too large");
    }
    return  scrypt($password, $salt, $N, $r, $p, $kdlen);
  }
  
}
