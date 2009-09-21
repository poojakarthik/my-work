<?php

/**
 * Version 187 of database update.
 * This version: -
 *	
 *	1:	Add the CustomerGroup.interim_invoice_delivery_method_id Field
 *
 */

class Flex_Rollout_Version_000187 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the CustomerGroup.interim_invoice_delivery_method_id Field
		$strSQL = "	ALTER TABLE		CustomerGroup
					ADD COLUMN		interim_invoice_delivery_method_id						BIGINT	UNSIGNED	NULL	COMMENT '(FK) Delivery Method for Interim Invoices (NULL will resolve to regular Account setting)',
					ADD CONSTRAINT	fk_customer_group_interim_invoice_delivery_method_id	FOREIGN KEY (interim_invoice_delivery_method_id)	REFERENCES delivery_method(id)	ON UPDATE CASCADE	ON DELETE SET NULL;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the CustomerGroup.interim_invoice_delivery_method_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	CustomerGroup
									DROP CONSTRAINT	fk_customer_group_interim_invoice_delivery_method_id,
									DROP COLUMN		interim_invoice_delivery_method_id;";
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