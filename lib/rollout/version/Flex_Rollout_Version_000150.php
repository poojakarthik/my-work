<?php

/**
 * Version 150 of database update.
 * This version: -
 *
 *	1:	Add the RatePlan.locked and cdr_required Fields
 */

class Flex_Rollout_Version_000150 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add the RatePlan.locked and cdr_required Fields
		$strSQL =	"ALTER TABLE RatePlan " .
					"ADD locked			TINYINT(1)	NOT NULL	DEFAULT 0	COMMENT '1: Changes from this Plan are restricted; 0: Anyone can change from this Plan', " .
					"ADD cdr_required	TINYINT(1)	NOT NULL	DEFAULT 1	COMMENT '1: CDRs are required for initial Plan Charges; 0: CDRs are not required for initial Plan Charges';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the RatePlan.locked and cdr_required Fields. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE " .
								"DROP locked, " .
								"DROP cdr_required;";
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