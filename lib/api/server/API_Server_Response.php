<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of API_Response
 *
 * @author JanVanDerBreggen
 */
class API_Server_Response extends API_Response {
    


	public function setStatus($iStatusCode)
	{
		$this->iResponseStatus =$iStatusCode;
	}

	public function setBody($sBody)
	{
		$this->sBody = $sBody;
	}

	public function setContentType($sContentType)
	{
		$this->sContentType = $sContentType;
	}
	
	public function send()
	{
		$sStatusHeader = 'HTTP/1.1 ' . $this->iResponseStatus . ' ' . self::getStatusCodeMessage();
		header($sStatusHeader);
		header('Content-type: ' . $this->sContentType);		
		echo $this->sBody;
	}

	

	public function setErrorResponse($iErrorCode, $sErrorMessage)
	{
		$this->setStatus($iErrorCode);
		$this->setContentType(API_Response::CONTENT_TYPE_HTML);
		$this->setBody("An API processing error occurred. Status code: $iErrorCode (".self::getStatusCodeMessage()."). API Error Message: $sErrorMessage");
	}

	public function setResponseDetails($sContentType, $sBody, $iStatusCode = self::STATUS_CODE_OK)
	{
		$this->setStatus($iStatusCode);
		$this->setContentType($sContentType);
		$this->setBody($sBody);
	}

}
?>
