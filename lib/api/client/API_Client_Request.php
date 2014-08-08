<?php
class API_Client_Request extends API_Request {
	protected $sURL;
	protected $cURL;

	private function __construct($method, $path, array $options=array()) {
		$options = array_merge(array(
			'headers' => array()
		), $options);

		$verifyHost = 2;
		if (isset($GLOBALS['**API']['verify_host']) && $GLOBALS['**API']['verify_host'] === false) {
			$verifyHost = 0;
		}

		$this->cURL = new CURL();
		$this->cURL->URL = "https://{$GLOBALS['**API']['host']}/{$path}";
		$this->cURL->HEADER = 1;
		$this->cURL->USERPWD = "{$GLOBALS['**API']['user']}:{$GLOBALS['**API']['pass']}";
		$this->cURL->SSL_VERIFYPEER = false;
		$this->cURL->SSL_VERIFYHOST = $verifyHost;
		$this->cURL->RETURNTRANSFER = true;
		$this->cURL->FOLLOWLOCATION = 1;
		$this->cURL->TIMEOUT = 40;

		//set the server port as configured in the flex.cfg file
		$this->cURL->PORT = $GLOBALS['**API']['port'];

		if ($method === API_Request::HTTP_METHOD_PUT) {
			$dataStream = fopen('php://temp', 'r+');
			fwrite($dataStream, $options['data']);
			rewind($dataStream);

			$this->cURL->PUT = true;
			$this->cURL->INFILE = $dataStream;
			$this->cURL->INFILESIZE = strlen($options['data']);
			//if we don't add the follow header, for some reason PHP will generate the response with status code 100 (continue)
			//this could be similar to what is described in a user comment on http://php.net/manual/en/function.curl-setopt.php about POST requests with over 1024 bytes
			$options['headers']['Expect'] = null;
		} else if ($method === API_Request::HTTP_METHOD_POST) {
			$this->cURL->POST = 1;
			$this->cURL->POSTFIELDS = $options['data'];
		} else if ($method === API_Request::HTTP_METHOD_PATCH) {
			$options['headers']['OVERRIDE_METHOD'] = 'Patch';
			$this->cURL->POST = 1;
			$this->cURL->POSTFIELDS = $options['data'];
		}

		// Headers
		if (count($options['headers'])) {
			$this->cURL->HTTPHEADER = array_map('self::_formatHeader', array_keys($options['headers']), array_values($options['headers']));
		}
	}

	private static function _formatHeader($header, $value) {
		return "{$header}: {$value}";
	}

	public function send() {
		$sResponse = $this->cURL->execute();
		$oResponse = new API_Client_Response($sResponse);
		if ($this->cURL->HTTP_CODE >= 400) {
			throw new API_Client_Request_Exception($this->cURL, $oResponse);
		}
		return $oResponse;
	}

	public function create($method, $path, array $options=array()) {
		return new self($method, $path, $options);
	}

	public static function get($path, array $options=array()) {
		$request = new self(API_Request::HTTP_METHOD_GET, $path, $options);
		return $request->send();
	}

	public static function post($path, $data, array $options=array()) {
		$request = new self(
			API_Request::HTTP_METHOD_POST,
			$path,
			array_merge($options, array('data' => $data)),
			$data
		);
		return $request->send();
	}
}

class API_Client_Request_Exception extends Exception {
	public function __construct(CURL $curl, API_Client_Response $response) {
		parent::__construct(sprintf('API call to %s returned %d %s: %s',
			$curl->URL,
			$response->getResponseStatusCode(),
			API_Response::$aCodes[$response->getResponseStatusCode()],
			$response->getBody()
		), $response->getResponseStatusCode());
	}
}