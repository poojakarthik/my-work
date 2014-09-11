<?php

class JSON_Handler_Flex_Config extends JSON_Handler
{
	
	public function setLogo($oRequest) {

		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? 'There was an error accessing the database' : ''
					);
		}
		try {

			// Current Config
			$oCurrentFlexConfig = Flex_Config::instance();

			// New Config
			$oFlexConfig								= Flex_Config::createInstance();
			$oFlexConfig->internal_contact_list_html	= $oCurrentFlexConfig->internal_contact_list_html;
			$oFlexConfig->created_by					= Employee::SYSTEM_EMPLOYEE_ID;
			$oFlexConfig->created_on					= date('Y-m-d H:i:s');
			$oFlexConfig->logo							= base64_decode($this->_prepareDataURL($oRequest->mContent));
			$oFlexConfig->logo_mime_type				= $oRequest->sMimeType;

			// Save
			$oFlexConfig->save();

			// Commit db transaction
			$oDataAccess->TransactionCommit();
			return array("Success" => true);

		}
		catch (Exception $e) {
			// Rollback db transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? 'There was an error accessing the database' : ''
					);
		}

	}

	private function _prepareDataURL($sDataURL) {
		$aSplit = split('base64,', $sDataURL);
		return $aSplit[1];
	}
}

?>