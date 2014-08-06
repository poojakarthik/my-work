<?php
class API_Client_Response extends API_Response {

	public $aHeaders;
	public $sBody;

	public function __construct($sResponse) {
		list($sHeader, $sBody) = explode("\r\n\r\n", $sResponse, 2);
		$aHeader = explode("\n", $sHeader);
		//process further
		$aStatus = explode(" ", $aHeader[0]);
		$this->iResponseStatus = (int)$aStatus[1];
		unset($aHeader[0]);
		foreach ($aHeader as $sStatus) {
			$aStatusDetails = explode(':', $sStatus);
			$this->aHeaders[$aStatusDetails[0]] = $aStatusDetails[1];
		}
		$this->sBody = $sBody;
	}

	public function getBody() {
		return $this->sBody;
	}

	public function getResponseStatusCode() {
		return $this->iResponseStatus;
	}
}