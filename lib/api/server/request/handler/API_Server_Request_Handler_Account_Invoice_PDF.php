<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of API_Request_Handler_Account
 *
 * @author JanVanDerBreggen
 */
class API_Server_Request_Handler_Account_Invoice_PDF extends API_Server_Request_Handler implements API_Server_Request_Handler_Get {


	public static $aQueryPattern = array(
											"account\/[0-9]+\/invoice\/[0-9]+\/pdf",
											"invoice\/[0-9]+\/pdf"
										);
	public static $sPublicName = "Account Invoice Request";

	public static function handleRequest(API_Server_Request $oRequest, API_Server_Response $oResponse)
	{
		$sMethod = $oRequest->getMethod();
		switch ($sMethod)
		{
			case API_Request::HTTP_METHOD_GET:
				self::create($oRequest, $oResponse)->get();
				break;
			case API_Request::HTTP_METHOD_PATCH:
			case API_Request::HTTP_METHOD_PUT:
				$oResponse->setErrorResponse( API_Response::STATUS_CODE_METHOD_NOT_ALLOWED, "Invalid HTTP Request Method. Only 'GET' is allowed in API Request: ".self::$sPublicName.". Your data (".$oRequest->getData().") was not processed." );
		}
	}

	public static function getQueryRegex ()
	{
		return self::$aQueryPattern;
	}

	public function get()
	{
		$iParameterCount = count($this->aParameters);
		$iInvoiceId = $iParameterCount === 3 ? $this->aParameters[1] : $this->aParameters[3];
		
		$oInvoice = Invoice::getForId($iInvoiceId);
		$iAccountId = $iParameterCount === 3 ? $oInvoice->account_id : $this->aParameters[1];
		$iDate 					= strtotime("-1 month", strtotime($oInvoice->created_on));
		$iYear 					= (int)date("Y", $iDate);
		$iMonth 				= (int)date("m", $iDate);

		// Try to pull the Invoice PDF
		$sInvoice 		= GetPDFContent($iAccountId, $iYear, $iMonth, $iInvoiceId, $oInvoice->invoice_run_id);

		if (!$sInvoice)
		{
			$this->oResponse->setErrorResponse(API_RESPONSE::STATUS_CODE_NOT_FOUND, "PDF not found");
		}
		else
		{
			$this->oResponse->setResponseDetails(API_RESPONSE::CONTENT_TYPE_PDF, $sInvoice);
		}
	}

	public static function create($oRequest, $oResponse)
	{
		return new self($oRequest, $oResponse);
	}
}
?>
