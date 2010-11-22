<?php

/**
 * Version 1 (one) of database update.
 * This version: -
 *	1:	Adds cdr_count, cdr_amount and discount_start_datetime columns
 *		to the Service table.
 *	2:	Adds discount_cap and default_discount_percentage columns to the
 *		RatePlan table.
 *	3:	Adds discount_percentage column to the Rate table.
 */

class Flex_Rollout_Version_000001 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		$strSQL = "
			ALTER TABLE Service
			ADD cdr_discount INT NULL DEFAULT NULL,
			ADD cdr_amount FLOAT NULL DEFAULT NULL,
			ADD discount_start_datetime DATETIME NULL DEFAULT NULL
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to alter Service table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE Service DROP cdr_discount, DROP cdr_amount, DROP discount_start_datetime";

		$strSQL = "
			ALTER TABLE RatePlan
			ADD discount_cap FLOAT NULL DEFAULT NULL COMMENT 'A dollar amount for an entire plan',
			ADD default_discount_percentage FLOAT NULL DEFAULT NULL COMMENT 'A percentage to autofill when creating new rates'
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to alter RatePlan table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE RatePlan DROP discount_cap, DROP default_discount_percentage";

		$strSQL = "
			ALTER TABLE Rate
			ADD discount_percentage FLOAT NULL DEFAULT NULL COMMENT 'A percentage amount for a rate after the discount cap has been reached'
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to alter Rate table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE Rate DROP discount_percentage";
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
