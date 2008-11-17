<?php

/**
 * Version 96 of database update.
 * This version: -
 *	1:	Adds sale table used to reference sales in flex
 *	2:	Adds sale_item table used to reference sale_items in flex
 */

class Flex_Rollout_Version_000096 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1: Add sale table used to reference sales in flex
		$strSQL = "	CREATE TABLE sale
					(
						id BIGINT(20) UNSIGNED NOT NULL COMMENT 'Unique Id for this record',
						external_reference VARCHAR(255) NOT NULL COMMENT 'Defines how to reference this sale in the sales database',
						account_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK into Account table',
						
						CONSTRAINT pk_sale PRIMARY KEY (id),
						CONSTRAINT un_sale_external_reference UNIQUE (external_reference),
						CONSTRAINT fk_sale_account_id FOREIGN KEY (account_id) REFERENCES Account(Id) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE = innodb COMMENT = 'Defines sales';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create the sale table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS sale;";
		
		// 2: Adds sale_item table used to reference sale_items in flex
		$strSQL = "	CREATE TABLE sale_item
					(
						id BIGINT(20) UNSIGNED NOT NULL COMMENT 'Unique Id for this record',
						external_reference VARCHAR(255) NOT NULL COMMENT 'Defines how to reference this sale_item in the sales database',
						sale_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK into sale table',
						service_id BIGINT(20) UNSIGNED NULL COMMENT 'FK into Service table',
						
						CONSTRAINT pk_sale_item PRIMARY KEY (id),
						CONSTRAINT un_sale_item_external_reference UNIQUE (external_reference),
						CONSTRAINT fk_sale_item_sale_id FOREIGN KEY (sale_id) REFERENCES sale(id) ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT fk_sale_item_service_id FOREIGN KEY (service_id) REFERENCES Service(Id) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE = innodb COMMENT = 'Defines sale items';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create the sale_item table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS sale_item;";
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
