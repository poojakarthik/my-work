<?php

/**
 * Version 38 of database update.
 * This version: -
 *	1:	Introduces style configuration in CustomerGroup table.
 */

class Flex_Rollout_Version_000038 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);

		$strSQL = "ALTER TABLE `CustomerGroup` ADD `customer_primary_color` CHAR( 7 ) NULL COMMENT 'Primary colour displayed in the customer management interface',
ADD `customer_secondary_color` CHAR( 7 ) NULL COMMENT 'Secondary colour displayed in the customer management interface',
ADD `customer_logo` BLOB NULL COMMENT 'the image data.',
ADD `customer_logo_type` CHAR( 9 ) NULL COMMENT 'The image content type.',
ADD `customer_breadcrumb_menu_color` CHAR( 7 ) NULL COMMENT 'Secondary colour displayed in the customer management interface',
ADD `customer_exit_url` VARCHAR( 255 ) NULL COMMENT 'When customer logs out this is the url they will be redirected to.';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE `CustomerGroup`
  DROP `customer_primary_color`,
  DROP `customer_secondary_color`,
  DROP `customer_logo`,
  DROP `customer_logo_type`,
  DROP `customer_breadcrumb_menu_color`,
  DROP `customer_exit_url`;";
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
