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

			// Save
			$oFlexConfig					= Flex_Config::instance();
			$oFlexConfig->logo				= $oRequest->mContent;
			$oFlexConfig->logo_mime_type	= $oRequest->sMimeType;
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

}

?>