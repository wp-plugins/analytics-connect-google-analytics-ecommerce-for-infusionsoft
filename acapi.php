<?php

/**
 * @class ACapi
 * @brief Easy access to the Analytics Connect JSON-RPC API
 * 
 * This class is instantiated with an API key. Once it has been
 * instantiated, the call() method is used to make calls to the API.
 * 
 * Rather than using call(), you can also call any API methods on 
 * this class. For example, rather than calling
 * @code
 *  $api->call('Call', $params);
 * @endcode
 * you can call
 * @code
 *  $api->Call($params);
 * @endcode
 * 
 * Calls made using this class are synchronous - the method blocks until the
 * request is completed.
 * 
 * Requires PHP 5.0+ and the CURL and JSON modules.
 * CURL: http://php.net/manual/en/book.curl.php
 * JSON Module: http://pecl.php.net/package/json
 * 
 * @version 0.1
 * @date March 8, 2013
 */

class ACAPI {
	protected $url = 'https://ecommerceanalyticsconnect.com/api/v1/';
	protected $curl = NULL;
	protected $key = NULL;
	public $order = NULL;
	/**
	 * Initializes the API access class.
	 * 
	 * @param string $key
	 * @throws ACAPIException if parameter is invalid
	 */
	function __construct($apiKey, $apiOrder) {
		$this->key = $apiKey;
		$this->order = $apiOrder;
		$this->curl = curl_init($this->url);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_HEADER, false);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
	}
	
	function __destruct() {
		if ($this->curl) {
			curl_close($this->curl);
		}
	}
	
	/**
	 * Calls Analytics Connect API method
	 * 
	 * See call() for detailed specs.
	 * 
	 * @return array
	 * @throws ACAPIException
	 */
	public function __call($name, $args) {
		$params = null;
		if (count($args) === 1 && is_array($args[0])) {
			// e.g. $api->getLead(array('leadId' => 11))
			$params = $args[0];
		} else {
			// e.g. $api->getLead(11)
			$params = $args;
		}
		return $this->call($name, $params);
	}
	
	/**
	 * Calls a Analytics Connect API method.
	 * 
	 * Returns the result from that call or, if there was an error on the server, 
	 * throws an exception.
	 * 
	 * @param string $method
	 * @param array|null $params
	 * @return array
	 * @throws ACAPIException
	 */
	public function call($method, array $params = NULL) {		
		$payload = array(
			'key' => $this->key,
			'method' => $method,
			'params' => $params,
			'order' => $this->order,
			'id' => $this->_generateRequestId()
		);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->json_encode($payload));
		$fullResult = curl_exec($this->curl);
		$fullResult = $this->json_decode($fullResult);
		return $fullResult;
	}
	
	/**
	 * Generates a random JSON request ID
	 * 
	 * @return string
	 */
	protected function _generateRequestId() {
		return substr(md5(rand()), 0, 8);
	}
	
	/**
	 * Encodes object in JSON
	 * 
	 * Can be overridden to support PHP installations without built-in JSON support.
	 */
	protected function json_encode($x) {
		return json_encode($x);
	}
	
	/**
	 * Decodes object from JSON
	 * 
	 * Can be overridden to support PHP installations without built-in JSON support.
	 */
	protected function json_decode($x, $assoc = FALSE) {
	$json = str_replace(array("\n","\r"),"",$json); 
    $json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/','$1"$3":',$json); 
    return json_decode($x,$assoc); 
	}
}