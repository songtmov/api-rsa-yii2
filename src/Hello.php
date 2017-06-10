<?php
namespace Sky\Demo;
use yii\base\Exception;

class Hello
{
    private $private_dir;
    private $public_dir;
    private $private_key;
    private $public_key;
    private $pi_key;
    private $pu_key;
    public $encrypted;
    public $decrypted;

    public function __construct()
    {
    	$this->private_dir = dirname(dirname(__FILE__))."/rsa/id_rsa";
    	$this->public_dir = dirname(dirname(__FILE__))."/rsa/id_rsa.pub";
    }

    private function getRsa(){
    	if(!extension_loaded('OpenSSL')){
    		throw new Exception("Error Processing Request", 1);
    	}
    	$private_res = fopen($this->private_dir, 'r');
    	$public_res = fopen($this->public_dir, 'r');
    	if($private_res && $public_res){
        	$this->private_key = fread($private_res,filesize($this->private_dir));
        	$this->public_key = fread($public_res,filesize($this->public_dir));
        }else{
        	return false;
        }
        fclose($private_res);
        fclose($public_res);
    }

    private static function content($text){
    	if($text == null){
    		throw new Exception("Error Processing Request", 1);
    	}
    }

    public function hello($content)
    {
    	try {
    		$this->getRsa();
    		$this::content($content);
    	} catch (Exception $e) {
    		$this->p($e."Error , Your input content is null or OpenSSL is not load !");
    	}
    	
    	$this->rsa_status();
    	return $content;
	}

	private function is_rsa(){
		$pi_key = openssl_pkey_get_private($this->private_key);
		$pu_key = openssl_pkey_get_public($this->public_key);
		if($pi_key === false || $pu_key === false){
			throw new Exception("Error Processing Request", 1);
		}else{
			$this->pi_key = $pi_key;
			$this->pu_key = $pu_key;
		}
	}

	public function rsa_status(){
		try {
			$this->is_rsa();
		} catch (Exception $e) {
			$this->p($e."rsa is error!");
		}
	}

	public function p($content = null){
		echo "<pre>";
		var_dump($content);
		echo "</pre>";
	}

	public function prien($content = null,$decode = true){
		openssl_private_encrypt($this->hello($content),$this->encrypted,$this->pi_key);
		if($decode){
			$this->encrypted = base64_encode($this->encrypted);
		}
		return $this->encrypted;
	}

	public function puben($content = null,$decode = true){
		openssl_public_encrypt($this->hello($content),$this->encrypted,$this->pu_key);
		if($decode){
			$this->encrypted = base64_encode($this->encrypted);
		}
		return $this->encrypted;
	}

	public function pubde($content = null,$decode = true){
		if($decode){
			openssl_public_decrypt(base64_decode($this->hello($content)),$this->decrypted,$this->pu_key);
		}else{
			openssl_public_decrypt($content,$this->decrypted,$this->pu_key);
		}
		return $this->decrypted;
	}

	public function pride($content = null,$decode = true){
		if($decode){
			openssl_private_decrypt(base64_decode($this->hello($content)),$this->decrypted,$this->pi_key);
		}else{
			openssl_private_decrypt($content,$this->decrypted,$this->pi_key);
		}
		return $this->decrypted;
	}
}	
