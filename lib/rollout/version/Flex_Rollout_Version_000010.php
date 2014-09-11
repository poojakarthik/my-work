<?php

/**
 * Version 10 (ten) of database update.
 * This version: -
 *	1:	Alters provisioning_type table
 *	2:	Populates provisioning_type table
 */

class Flex_Rollout_Version_000010 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		// Create the provisioning_type Table
		$strSQL = "RENAME TABLE carrier_module_provisioning_support TO carrier_provisioning_support;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to rename carrier_module_provisioning_support table to carrier_provisioning_support. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "RENAME TABLE carrier_provisioning_support TO carrier_module_provisioning_support;";

		// Create the provisioning_type Table
		$strSQL = "
			INSERT INTO provisioning_type 
			(id, name, inbound, outbound, description, const_name)
			VALUES
			(900, 'Full Service', 1, 1, 'Full service request', 'PROVISIONING_TYPE_FULL_SERVICE'),
			(901, 'Pre-Selection', 1, 1, 'Pre-selection request', 'PROVISIONING_TYPE_PRESELECTION'),
			(902, 'Bar', 1, 1, 'Bar request', 'PROVISIONING_TYPE_BAR'),
			(903, 'Unbar', 1, 1, 'Unbar request', 'PROVISIONING_TYPE_UNBAR'),
			(904, 'Activation', 1, 1, 'Activation request', 'PROVISIONING_TYPE_ACTIVATION'),
			(905, 'Deactivation', 1, 1, 'Deactivation request', 'PROVISIONING_TYPE_DEACTIVATION'),
			(906, 'Pre-Selection Reverse', 1, 1, 'Pre-selection reverse request', 'PROVISIONING_TYPE_PRESELECTION_REVERSE'),
			(907, 'Full Service Reverse', 1, 1, 'Full service reverse request', 'PROVISIONING_TYPE_FULL_SERVICE_REVERSE'),
			(913, 'Virtual Pre-Selection', 1, 1, 'Virtual pre-selection request', 'PROVISIONING_TYPE_VIRTUAL_PRESELECTION'),
			(914, 'Virtual Pre-Selection Reverse', 1, 1, 'Virtual pre-selection reverse request', 'PROVISIONING_TYPE_VIRTUAL_PRESELECTION_REVERSE');
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to alter provisioning_type table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE provisioning_type;";
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
