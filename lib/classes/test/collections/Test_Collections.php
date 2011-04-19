<?php

class Test_Collections extends Test {
	
	public function __construct() {
		parent::__construct("Collections Related Tests");
	}
	
	public function generateLateNoticeXml($iDocumentTemplateTypeId, $iAccountId) {
		if (!in_array($iDocumentTemplateTypeId, array(DOCUMENT_TEMPLATE_TYPE_OVERDUE_NOTICE, DOCUMENT_TEMPLATE_TYPE_SUSPENSION_NOTICE, DOCUMENT_TEMPLATE_TYPE_FINAL_DEMAND_NOTICE, DOCUMENT_TEMPLATE_TYPE_FRIENDLY_REMINDER))) {
			throw new Exception("Invalid document template type id");
		}
		
		if (!Account::getForId($iAccountId)) {
			throw new Exception("Invalid account id");
		}
		
		$aAccount	= reset(Account::getAccountDataForLateNotice(array($iAccountId)));
		$mSuccess	= BuildLatePaymentNotice($iDocumentTemplateTypeId, $aAccount, FILES_BASE_PATH, time());
		if ($mSuccess !== null) {
			if ($mSuccess === false) {
				Log::getLog()->log("Failed");
			} else {
				Log::getLog()->log("Success: {$mSuccess}");
			}
		}
		return $mSuccess;
	}
}

?>