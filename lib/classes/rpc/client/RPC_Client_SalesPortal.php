<?php
/**
 * RPC_Client_SalesPortal
 *
 * Handles outbound Remote Procedure Calls encoded in Sales Portal JSON
 *
 * @class	RPC_Client_SalesPortal
 */
class RPC_Client_SalesPortal
{
	/**
	 * __construct()
	 *
	 * @constructor
	 */
	public function __construct($strBaseURL, $bolLogConversation=true)
	{
		parent::__construct($strBaseURL, $bolLogConversation);
		
		// Init cURL object
		$this->_resSession	= curl_init();
		curl_setopt($this->_resSession, CURLOPT_RETURNTRANSFER	, true);
		curl_setopt($this->_resSession, CURLOPT_SSL_VERIFYPEER	, false);
		curl_setopt($this->_resSession, CURLOPT_COOKIESESSION	, true);
		curl_setopt($this->_resSession, CURLOPT_COOKIEFILE		, "/dev/null");	// Stores a cookie in memory, but doesn't retain it
		curl_setopt($this->_resSession, CURLOPT_POST			, true);
	}
	
	/**
	 * _prepareRequest()
	 *
	 * Prepare the RPC Request
	 *
	 * @param	stdClass	$objData		Data to encode
	 *
	 * @return	string						Encoded string
	 *
	 * @constructor
	 */
	protected static function _prepareRequest($strFunction, $arrParameters, $strObject)
	{
		// Prepare URL
		$strURL	= $this->_strBaseURL;
		if (trim($strObject))
		{
			$strURL	.= $strObject.'/';
		}
		$strURL	.= $strFunction;
		
		curl_setopt($this->_resSession, CURLOPT_URL			, $strURL);
		curl_setopt($this->_resSession, CURLOPT_POSTFIELDS	, array('json'=>json_encode($arrParameters)));
	}
	
	/**
	 * _request()
	 *
	 * Sends an encoded RPC Request to this URL
	 *
	 * @param	string	$strLogType			Type of the new Log
	 *
	 * @return	string						Encoded Response
	 *
	 * @method
	 */
	protected function _request($strEncodedRequest)
	{
		// Perform Request
		$strEncodedResponse	= curl_exec($this->_resSession);
		if ($strEncodedResponse === false)
		{
			throw new Exception("cURL Request Failed (".curl_errno($this->_resSession)."): ".curl_error($this->_resSession));
		}
		
		// Return the encoded Response string
		return $strEncodedResponse;
	}
	
	/**
	 * _parseResponse()
	 *
	 * Decodes and parses the RPC Response.  Should be overridden to perform per-format decoding.
	 *
	 * @param	string			$strEncodedResponse			Encoded Response to parse
	 *
	 * @return	stdClass
	 *
	 * @constructor
	 */
	protected static function _parseResponse($strEncodedResponse)
	{
		$objResponse	= json_decode($strEncodedResponse);
		
		$intJSONError	= json_last_error();
		switch ($intJSONError)
		{
			case JSON_ERROR_NONE:
				return $objResponse;
				break;
			
			case JSON_ERROR_DEPTH:
				throw new Exception("JSON Response Parse Error: Maximum stack depth exceeded");
				break;
			
			case JSON_ERROR_CTRL_CHAR:
				throw new Exception("JSON Response Parse Error: Control character error");
				break;
			
			case JSON_ERROR_SYNTAX:
				throw new Exception("JSON Response Parse Error: Syntax error");
				break;
			
			default:
				throw new Exception("JSON Response Parse Error: Unspecified error");
				break;
		}
	}
}
?>