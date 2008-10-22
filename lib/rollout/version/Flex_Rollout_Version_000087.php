<?php

/**
 * Version 87 of database update.
 * This version: -
 *	1:	Adds indexes to the ProvisioningResponse Table
 */

class Flex_Rollout_Version_000087 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds indexes to the ProvisioningResponse Table
		$strSQL = "CREATE INDEX Account ON ProvisioningResponse (Account);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ProvisioningResponse.Account Index. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP INDEX Account ON ProvisioningResponse;";
		
		$strSQL = "CREATE INDEX Service ON ProvisioningResponse (Service);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ProvisioningResponse.Service Index. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP INDEX Service ON ProvisioningResponse;";
		
		$strSQL = "CREATE INDEX FNN ON ProvisioningResponse (FNN);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ProvisioningResponse.FNN Index. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP INDEX FNN ON ProvisioningResponse;";
		
		$strSQL = "CREATE INDEX Carrier ON ProvisioningResponse (Carrier);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ProvisioningResponse.Carrier Index. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP INDEX Carrier ON ProvisioningResponse;";
		
		$strSQL = "CREATE INDEX Type ON ProvisioningResponse (Type);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ProvisioningResponse.Type Index. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP INDEX Type ON ProvisioningResponse;";
		
		$strSQL = "CREATE INDEX Request ON ProvisioningResponse (Request);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ProvisioningResponse.Request Index. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP INDEX Request ON ProvisioningResponse;";
		
		$strSQL = "CREATE INDEX Status ON ProvisioningResponse (Status);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ProvisioningResponse.Status Index. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP INDEX Status ON ProvisioningResponse;";
		
		$strSQL = "CREATE INDEX request_status ON ProvisioningResponse (request_status);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ProvisioningResponse.request_status Index. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP INDEX request_status ON ProvisioningResponse;";
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
