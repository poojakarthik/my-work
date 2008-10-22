<?php

/**
 * Version 88 of database update.
 * This version: -
 *	1:	Correct Disconnection entries in service_line_status_update
 */

class Flex_Rollout_Version_000088 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Correct Disconnection Entries in serivce_line_status_update
		$strSQL = "UPDATE service_line_status_update SET new_line_status = ".SERVICE_LINE_DISCONNECTED." WHERE current_line_status IS NULL AND provisioning_type = ".PROVISIONING_TYPE_DISCONNECT_FULL.";";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to correct the Full Service Disconnection entry in service_line_status_update. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "UPDATE service_line_status_update SET new_line_status = ".SERVICE_LINE_CHURNED." WHERE current_line_status IS NULL AND provisioning_type = ".PROVISIONING_TYPE_DISCONNECT_FULL.";";
		
		$strSQL = "UPDATE service_line_status_update SET new_line_status = ".SERVICE_LINE_DISCONNECTED." WHERE current_line_status IS NULL AND provisioning_type = ".PROVISIONING_TYPE_DISCONNECT_PRESELECT.";";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to correct the Preselection Disconnection entry in service_line_status_update. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "UPDATE service_line_status_update SET new_line_status = ".SERVICE_LINE_CHURNED." WHERE current_line_status IS NULL AND provisioning_type = ".PROVISIONING_TYPE_DISCONNECT_PRESELECT.";";
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
