<?php
class API_Server_Request extends API_Request {
	private $_aURLTokens;
	private $_httpAccept;
	private $_method;
	private $_data;
	private $_queryString;
	public $path;
	public $url;

	public function __construct($requestData) {
		$this->path = parse_url($requestData['REQUEST_URI'], PHP_URL_PATH);
		$this->_setMethod($requestData['REQUEST_METHOD']);
		$this->_data = file_get_contents('php://input');
	}

	public function saveRegexMatchedNamedSubpatternsAsProperties($aMatches) {
    	// Populate url object
    	$this->url = new StdClass();
    	foreach($aMatches as $mKey=>$sValue) {
    		if(is_string($mKey)) {
    			$this->url->$mKey = strval($sValue);
    		}
    	}
    }

    public function getHTTPMethod() {
    	return $this->_method;
    }

	private function _setMethod($method) {
		$this->_method = trim(strtolower($method));
	}

	public function getData() {
		return $this->_data;
	}

	public function getMethod() {
		return $this->_method;
	}

	public function getHttpAccept() {
		return $this->_httpAccept;
	}

	public function getParameters() {
		return $this->_tokeniseURL();
	}

	protected function _tokeniseURL() {
		if (!isset($this->_aURLTokens)) {
			$aTokens = explode(API_REQUEST::TOKEN_DELIMITER, $this->path);
			$this->_aURLTokens = $aTokens;
		}
		return $this->_aURLTokens;
	}

	public function resolveHandlerClass() {
		foreach (API::$aRegisteredRequestHandlers as $sHandlerClassName) {
			$aQueryPattern = call_user_func(array($sHandlerClassName, "getQueryRegex"));
			foreach ($aQueryPattern as $sPattern) {
				if (preg_match("/^$sPattern$/", $this->path) >0) {
					return $sHandlerClassName;
				}
			}
		}
		throw new API_Exception ("No API Request Handler defined for path: $this->path", API_Response::STATUS_CODE_NOT_IMPLEMENTED);
	}

	public function toString() {
		$sString;
		foreach ($this->toArray() as $key => $value) {
			if (is_array($value)) {
				$sString .= "$key - ";
				foreach ($value as $key1 => $value1) {
					$sString .= "$key1: $value1";
				}
				$sString .= " - ";
			} else {
				$sString .= "$key: $value; ";
			}
		}
		return $sString;
	}

	public function toArray() {
		$aArray					= array();
		$aArray['data']			= $this->data;
		$aArray['method']		= $this->method;
		//$aArray['query_string'] = $this->_queryString;
		$aArray['path']			= $this->path;
		return array(
			'data' => $this->_data,
			'method' => $this->_method,
			//'queryString' => $this->_queryString,
			'path' => $this->path
		);
	}
}