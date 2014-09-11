<?php

/**
 * Version 3 (three) of database update.
 * This version: -
 *	1:	Change size of columns for CardNumber and CVV values in CreditCard table.
 */

class Flex_Rollout_Version_000003 extends Flex_Rollout_Version
{
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		$strSQL = "
			ALTER TABLE CreditCard 
			CHANGE CardNumber CardNumber VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
			CHANGE CVV CVV VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL 
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to alter CreditCard table. ' . $qryQuery->Error());
		}
	}
}

?>
