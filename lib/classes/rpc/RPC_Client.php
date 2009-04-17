<?php
/**
 * RPC_Client
 *
 * Handles outbound Remote Procedure Calls
 *
 * @class	RPC_Client
 */
abstract class RPC_Client
{
	protected	$_strURL;
	
	protected	$_bolLogConversation;
	protected	$_intRequestId			= 0;
	protected	$_arrConversationLog	= array();
	
	/**
	 * __construct()
	 *
	 * @constructor
	 */
	protected function __construct($strBaseURL, $bolLogConversation=true)
	{
		$this->_strURL				= $strBaseURL;
		$this->_bolLogConversation	= ($bolLogConversation === true);
	}
	
	/**
	 * call()
	 *
	 * Calls a remote function for this URL
	 *
	 * @param	string	$strFunction						Remote Object to call.  Can be null for .
	 * @param	string	$strFunction						Remote Function to call
	 * @param	[array	$arrParameters					]	Array of parameters to pass to the remote function
	 * @param	[string	$strObject						]	Object to call the Remot Function on
	 *
	 * @return	RPC_Response
	 *
	 * @method
	 */
	public function call($strFunction, $arrParameters=array(), $strObject=null)
	{
		// Increment the Request Id
		$this->_intRequestId++;
		
		// Prepare the Request
		$strEncodedRequest	= $this->_prepareRequest($strFunction, $arrParameters, $strObject);
		
		// Perform the RPC Request
		$strEncodedResponse	= $this->_request($strEncodedRequest);
		
		// Parse the Response
		$objRPCResponse		= $this->_parseResponse($strEncodedResponse);
		
		// Log the Request
		if ($this->_bolLogConversation)
		{
			$this->_arrConversationLog[$this->_intRequestId]	=	array
																	(
																		'strEncodedRequest'		=> $strEncodedRequest,
																		'strEncodedResponse'	=> $strEncodedResponse,
																		'objRPCResponse'		=> $objRPCResponse
																	);
		}
		
		// Return the RPC_Response
		return $objRPCResponse;
	}
	
	/**
	 * _request()
	 *
	 * Sends an encoded RPC Request to this URL.  TODO: Possibly add a generic implementation later.
	 *
	 * @param	string	$strLogType			Type of the new Log
	 *
	 * @return	string						Encoded Response
	 *
	 * @method
	 */
	abstract protected function _request($strEncodedRequest);
	
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
	abstract protected static function _prepareRequest($strFunction, $arrParameters, $strObject);
	
	/**
	 * _parseResponse()
	 *
	 * Decodes and parses the RPC Response.  Should be overridden to perform per-format decoding.
	 *
	 * @param	string			$strEncodedResponse			Encoded Response to parse
	 *
	 * @return	RPC_Response
	 *
	 * @constructor
	 */
	abstract protected static function _parseResponse($strEncodedResponse);
}
?>