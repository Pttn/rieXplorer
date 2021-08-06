<?php // Simplified and adapted version of https://github.com/aceat64/EasyBitcoin-PHP/blob/master/easybitcoin.php (Mit License)
// (c) 2013-2017 aceat64
// (c) 2021 Pttn

class RiecoinRPC {
	private $credentials;
	private $url;
	private $id = 0;
	
	public function __construct($daemonConf) {
		if (!isset($daemonConf['rpcuser'], $daemonConf['rpcpassword'], $daemonConf['rpcip'], $daemonConf['rpcport']))
			echo 'Missing Daemon Configuration params';
		else {
			$this->credentials = $daemonConf['rpcuser'] . ':' . $daemonConf['rpcpassword'];
			$this->url = 'http://' . $daemonConf['rpcip'] . ':' . $daemonConf['rpcport'];
		}
	}
	
	public function __call($method, $params) {
		$this->id++; // The ID should be unique for each call
		$request = json_encode(array(
			'method' => $method,
			'params' => $params,
			'id'	 => $this->id
		));
		$curl = curl_init($this->url);
		$options = array(
			CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
			CURLOPT_USERPWD        => $this->credentials,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_HTTPHEADER     => array('Content-type: application/json'),
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $request
		);
		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);
		curl_close($curl);
		return json_decode($response, true);
	}
}
?>
