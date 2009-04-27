<?php

/**
 * Version 174 of database update.
 * This version: -
 *	
 *	1:	Add the FileDownload.customer_group_id Field
 *	2:	Add the FileImport.customer_group_id Field
 *	3:	Add the ProvisioningRequest.customer_group_id Field
 *	4:	Add the ProvisioningResponse.customer_group_id Field
 *
 */

class Flex_Rollout_Version_000174 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the FileDownload.customer_group_id Field
		$strSQL = "	ALTER TABLE	FileDownload
						ADD	customer_group_id	BIGINT	UNSIGNED	NULL	COMMENT '(FK) Customer Group';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the FileDownload.customer_group_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	FileDownload
										DROP	customer_group_id;";
		
		//	2:	Add the FileExport.customer_group_id Field
		$strSQL = "	ALTER TABLE	FileExport
						ADD	customer_group_id	BIGINT	UNSIGNED	NULL	COMMENT '(FK) Customer Group';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the FileExport.customer_group_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	FileExport
										DROP	customer_group_id;";
		
		//	3:	Add the ProvisioningRequest.customer_group_id Field
		$strSQL = "	ALTER TABLE	ProvisioningRequest
						ADD	customer_group_id	BIGINT	UNSIGNED	NULL	COMMENT '(FK) Customer Group';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ProvisioningRequest.customer_group_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	ProvisioningRequest
										DROP	customer_group_id;";
		
		//	4:	Add the ProvisioningResponse.customer_group_id Field
		$strSQL = "	ALTER TABLE	ProvisioningResponse
						ADD	customer_group_id	BIGINT	UNSIGNED	NULL	COMMENT '(FK) Customer Group';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ProvisioningResponse.customer_group_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	ProvisioningResponse
										DROP	customer_group_id;";
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>