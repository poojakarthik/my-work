<?php

/**
 * Version 40 of database update.
 * This version: -
 *	1:	Increases style configuration fields in CustomerGroup table.
 */

class Flex_Rollout_Version_000040 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);

		$strSQL = "ALTER TABLE `CustomerGroup` CHANGE `customer_primary_color` `customer_primary_color` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Primary colour displayed in the customer management interface',
CHANGE `customer_secondary_color` `customer_secondary_color` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Secondary colour displayed in the customer management interface',
CHANGE `customer_breadcrumb_menu_color` `customer_breadcrumb_menu_color` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Secondary colour displayed in the customer management interface' ";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE `CustomerGroup` CHANGE `customer_primary_color` `customer_primary_color` CHAR( 7 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Primary colour displayed in the customer management interface',
CHANGE `customer_secondary_color` `customer_secondary_color` CHAR( 7 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Secondary colour displayed in the customer management interface',
CHANGE `customer_breadcrumb_menu_color` `customer_breadcrumb_menu_color` CHAR( 7 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Secondary colour displayed in the customer management interface'";
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
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
