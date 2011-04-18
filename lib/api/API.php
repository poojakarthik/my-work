<?php

class API {	

	public static $aRegisteredRequestHandlers = array(
														"API_Server_Request_Handler_Account_Invoice_PDF"
														);

	public static function processRequest()
	{
		try
		{	
			$oRequest	= new API_Server_Request();

			if (isset($_SERVER['HTTP_OVERRIDE_METHOD']))
				$sRequestMethod = strtolower($_SERVER['HTTP_OVERRIDE_METHOD']);
			else
				$sRequestMethod = strtolower($_SERVER['REQUEST_METHOD']);
			
			$oRequest->setMethod($sRequestMethod);

			$aData			= array();
			switch ($sRequestMethod)
			{
				case API_Request::HTTP_METHOD_GET :
					//when getting data the parameters are passed through the url query string
					break;				
				case API_Request::HTTP_METHOD_PUT :
					//this can for some reason force a response code 100 (continue) if the request headers do not contain 'Expect: '
					parse_str(file_get_contents('php://input'), $put_vars);					
					$oRequest->setData($put_vars);
					break;
				case API_Request::HTTP_METHOD_PATCH :
				case API_Request::HTTP_METHOD_POST:
					$oRequest->setData($_POST);
					break;
				default :
					throw new API_Exception("Unsupported Request Method", API_Response::STATUS_CODE_METHOD_NOT_ALLOWED);
			}			
			//work out which api request handler should process this one further
			$sHandlerClassName = $oRequest->resolveHandlerClass();
			
			// Run the handler
			$oResponse = new API_Server_Response();
			call_user_func_array(array($sHandlerClassName, 'handleRequest'), array($oRequest, $oResponse));
			
			$oResponse->send();
		}
		catch(Exception $e)
		{
			$oResponse= new API_Server_Response();
			$iResponseCode = isset($e->iResponseCode) && $e->iResponseCode !== NULL ? $e->iResponseCode : API_Response::STATUS_CODE_SERVER_ERROR;
			$oResponse->setErrorResponse($iResponseCode, $e->getMessage().". Details: ".$e->__toString()."<br>Request Details: ".$oRequest->toString());
			$oResponse->send();
		}
		
	}
}

class API_Exception extends Exception
{
	public $iResponseCode;

	public function  __construct($message = NULL, $iResponseCode = NULL,  $code = NULL, $previous = NULL) {
		parent::__construct($message, $code, $previous);
		$this->iResponseCode = $iResponseCode;
	}
}
?>
