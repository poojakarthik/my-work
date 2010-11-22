<?php

/**
 * Version 11 (eleven) of database update.
 * This version: -
 *	1:	Alters carrier_provisioning_support table, again
 *	2:	Populates carrier_provisioning_support table
 */

class Flex_Rollout_Version_000011 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		// Create the provisioning_type Table
		$strSQL = " ALTER TABLE carrier_provisioning_support CHANGE carrier_module_id carrier_id BIGINT( 20 ) UNSIGNED NOT NULL COMMENT 'FK to Carrier table';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to rename carrier_provisioning_support.carrier_module_id to carrier_id. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE carrier_provisioning_support CHANGE carrier_id carrier_module_id BIGINT( 20 ) UNSIGNED NOT NULL COMMENT 'FK to CarrierModule table';";

		// Populate the carrier_provisioning_support Table ()
		$strSQL = "
			INSERT INTO carrier_provisioning_support 
			(carrier_id, provisioning_type_id, status_id)
			VALUES
			(1, 902, 2),
			(1, 903, 2),
			(2, 902, 2),
			(2, 903, 2),
			(5, 902, 2),
			(5, 903, 2);
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to alter carrier_provisioning_support table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE carrier_provisioning_support;";
	}

	function rollback()
	{
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);
				if (!$qryQuery->Execute($this->rollbackSQL[$l]))
				{
					throw new Exception_Database(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
