<?php
class API_Server_Response extends API_Response {

	public function setStatus($iStatusCode) {
		$this->iResponseStatus =$iStatusCode;
	}

	public function setBody($sBody) {
		$this->sBody = $sBody;
	}

	public function setContentType($sContentType) {
		$this->sContentType = $sContentType;
	}

	public function send($iResponseCode, $aHeaders, $sBody) {
		header("HTTP/1.1 {$iResponseCode} " . API_Response::$aCodes[$iResponseCode]);
		foreach($aHeaders as $sHeaderName=>$sHeaderValue) {
			header("{$sHeaderName}: {$sHeaderValue}");
		}
		echo $sBody;
	}

	public function setErrorResponse($iErrorCode, $sErrorMessage) {
		$this->setStatus($iErrorCode);
		$this->setContentType(API_Response::CONTENT_TYPE_HTML);
		$this->setBody("An API processing error occurred. Status code: $iErrorCode (".self::getStatusCodeMessage()."). API Error Message: $sErrorMessage");
	}

	public function setResponseDetails($sContentType, $sBody, $iStatusCode = self::STATUS_CODE_OK) {
		$this->setStatus($iStatusCode);
		$this->setContentType($sContentType);
		$this->setBody($sBody);
	}

}