<?php

/**
 * Version 99 of database update.
 * This version: -
 *	1:	Adds the sale.sale_type_id Field and defines it as a foreign key into sale_type table
 */

class Flex_Rollout_Version_000099 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1: Add the sale.sale_type_id Field and define it as a foreign key into sale_type table
		$strSQL = "ALTER TABLE sale ".
					"ADD sale_type_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK into sale_type table', ".
					"ADD CONSTRAINT fk_sale_sale_type_id FOREIGN KEY (sale_type_id) REFERENCES sale_type(id) ON UPDATE CASCADE ON DELETE RESTRICT;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the sale.sale_type_id Field and declare it as a foreign key into the sale_type table. ' . $result->getMessage());
		}
		
		// For Rolling back, the Foreign key constraint must be removed, then the index, then the column
		$this->rollbackSQL[] = "ALTER TABLE sale DROP sale_type_id;";
		$this->rollbackSQL[] = "ALTER TABLE sale DROP INDEX fk_sale_sale_type_id;";
		$this->rollbackSQL[] = "ALTER TABLE sale DROP FOREIGN KEY fk_sale_sale_type_id;";
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
