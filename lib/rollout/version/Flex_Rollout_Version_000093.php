<?php

/**
 * Version 93 of database update.
 * This version: -
 *	1:	Adds the Rate.allow_cdr_hiding Field
 *	2:	Adds the RatePlan.allow_cdr_hiding Field
 */

class Flex_Rollout_Version_000093 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds the Rate.allow_cdr_hiding Field
		$strSQL = "ALTER TABLE Rate " .
					"ADD allow_cdr_hiding TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: Allows Zero-Rated CDRs to be hidden on the Invoice (must also be set at the RatePlan level); 0: Normal behaviour';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Rate.allow_cdr_hiding Field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE Rate " .
								"DROP allow_cdr_hiding;";
		
		// 2:	Adds the RatePlan.allow_cdr_hiding Field
		$strSQL = "ALTER TABLE RatePlan " .
					"ADD allow_cdr_hiding TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: Allows Zero-Rated CDRs to be hidden on the Invoice (must also be set at the Rate level); 0: Normal behaviour';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the RatePlan.allow_cdr_hiding Field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE RatePlan " .
								"DROP allow_cdr_hiding;";
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
