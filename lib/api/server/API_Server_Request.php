<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of API_Request
 *
 * @author JanVanDerBreggen
 */
class API_Server_Request extends API_Request {

	private $request_vars;
	private $data;
	private $http_accept;
	private $method;
	private $sQueryString;
	

	public function __construct()
	{		
		$this->sQueryString = ltrim( substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME'])), "/");
	}

	public function setData($data)
	{
		$this->request_vars = $data;
		if(isset($data['data_']))
			$this->data = json_decode($data['data_']);
		if(isset($data['data']))
			$this->data = json_decode($data['data']);
	}

	public function setMethod($method)
	{
		$this->method = $method;
	}	

	public function getData()
	{
		return $this->data;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getHttpAccept()
	{
		return $this->http_accept;
	}

	public function getRequestVars()
	{
		return $this->request_vars;
	}

	public function getParameters()
	{
		$aTokens	= $this->_tokeniseURL();		
		return $aTokens;
	}

	protected function _tokeniseURL()
	{
		if (!isset($this->_aURLTokens))
		{
			
			$aTokens	= explode(API_REQUEST::TOKEN_DELIMITER, $this->sQueryString);
			// Remove the blank string (first element)
			//array_splice($aTokens, 0, 1);
			$this->_aURLTokens	= $aTokens;
		}

		return $this->_aURLTokens;
	}

	public function resolveHandlerClass()
	{
		foreach (API::$aRegisteredRequestHandlers as $sHandlerClassName)
		{
			$aQueryPattern = call_user_func(array($sHandlerClassName, "getQueryRegex"));
			foreach ($aQueryPattern as $sPattern)
			{
				if (preg_match("/^$sPattern$/", $this->sQueryString) >0)
						return $sHandlerClassName;
			}
		}

		throw new API_Exception ("No API Request Handler defined for query string: $this->sQueryString", API_Response::STATUS_CODE_NOT_IMPLEMENTED);

	}

	public function toString()
	{
		$sString;
		foreach ($this->toArray() as $key => $value)
		{
			if (is_array($value))
			{
				$sString .= "$key - ";
				foreach ($value as $key1 => $value1)
				{
					$sString .= "$key1: $value1";
				}
				$sString .= " - ";
			}
			else
			{
				$sString .= "$key: $value; ";
			}
		}

		return $sString;
	}

	public function toArray()
	{
		$aArray					= array();
		$aArray['data']			= $this->data;
		$aArray['method']		= $this->method;
		$aArray['query_string'] = $this->sQueryString;
		$aArray['request_vars'] = $this->request_vars;
		return $aArray;
	}

}
?>
