<?php

/**
 * Version 240 of database update.
 *
 *	Collections - Data migration for payments and adjustments
 *	
 */

class Flex_Rollout_Version_000241 extends Flex_Rollout_Version
{
		private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Create Email Notification for collections batch process report",
									'sAlterSQL'			=>	"INSERT INTO email_notification VALUES (NULL, 'Collections Batch Process Report', 'Collections Batch Process Report', 'COLLECTIONS_BATCH_PROCESS_REPORT', 0);",
									'sRollbackSQL'		=>	"DELETE FROM email_notification WHERE system_name = 'COLLECTIONS_BATCH_PROCESS_REPORT';",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Setup YBS as recipient of the new email notification",
									'sAlterSQL'			=>	"INSERT INTO email_notification_address  VALUES (NULL, (SELECT id FROM email_notification WHERE system_name = 'COLLECTIONS_BATCH_PROCESS_REPORT'), (SELECT id FROM email_address_usage WHERE const_name = 'EMAIL_ADDRESS_USAGE_TO'), 'ybs-admin@ybs.net.au', NULL );",
									'sRollbackSQL'		=>	"DELETE FROM email_notification_address WHERE email_notification_id = (select id from email_notification_address WHERE system_name = 'COLLECTIONS_BATCH_PROCESS_REPORT');",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								)
							);


		// Perform Batch Rollout
		$iRolloutVersionNumber	= self::getRolloutVersionNumber(__CLASS__);
		$iStepNumber			= 0;
		foreach ($aOperations as $aOperation)
		{
			$iStepNumber++;

			$this->outputMessage("Applying {$iRolloutVersionNumber}.{$iStepNumber}: {$aOperation['sDescription']}...\n");

			// Attempt to apply changes
			$oResult	= Data_Source::get($aOperation['sDataSourceName'])->query($aOperation['sAlterSQL']);
			if (MDB2::isError($oResult))
			{
				throw new Exception(__CLASS__ . " Failed to {$aOperation['sDescription']}. " . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
			}

			// Append to Rollback Scripts (if one or more are provided)
			if (array_key_exists('sRollbackSQL', $aOperation))
			{
				$aRollbackSQL	= (is_array($aOperation['sRollbackSQL'])) ? $aOperation['sRollbackSQL'] : array($aOperation['sRollbackSQL']);

				foreach ($aRollbackSQL as $sRollbackQuery)
				{
					if (trim($sRollbackQuery))
					{
						$this->rollbackSQL[] =	$sRollbackQuery;
					}
				}
			}
		}
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (MDB2::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>