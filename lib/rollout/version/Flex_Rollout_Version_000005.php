<?php

/**
 * Version 5 (five) of database update.
 * This version: -
 *	1:	Removes the default_discount_percentage property from the RatePlan table
 *  2:  Adds the customer_group property to the RatePlan table
 *	3:	Adds 2 records to the UIAppDocumentation table for the RatePlan.discount_cap and Rate.discount_percentage properties
 */

class Flex_Rollout_Version_000005 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		// Remove the default_discount_percentage property from the service table
		$strSQL = "
			ALTER TABLE RatePlan
				DROP default_discount_percentage,
				ADD customer_group BIGINT( 20 ) UNSIGNED NOT NULL COMMENT 'Customer Group that the RatePlan belongs to' AFTER discount_cap;
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed while modifying the RatePlan table (drop default_discount_percentage property. Add customer_group property. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE RatePlan ADD default_discount_percentage FLOAT NULL DEFAULT NULL COMMENT 'A percentage to autofill when creating new rates', DROP customer_group";

		// Add the records to the UIAppDocumentation  table
		$strSQL = "
			INSERT INTO UIAppDocumentation ( Id , Object , Property , Context , ValidationRule , InputType , OutputType , Label , OutputLabel , OutputMask , Class )
			VALUES 
			(NULL , 'RatePlan', 'discount_cap', '0', 'Optional: IsMoneyValue', 'InputText', 'Label', 'Discount Cap ($)', NULL , 'Method:MoneyValue(<value>)', 'Default'),
			(NULL , 'Rate', 'discount_percentage', '0', 'Optional: UnsignedFloat' , 'InputText', 'Label', 'Discount (%)', NULL , 'Method:FormatFloat(<value>, 2)', 'Default');
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add records to the UIAppDocumentation table for the RatePlan.discount_cap and Rate.discount_percentage propterties. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM UIAppDocumentation WHERE Object='RatePlan' AND Property='discount_cap' AND Context='0'";
		$this->rollbackSQL[] = "DELETE FROM UIAppDocumentation WHERE Object='Rate' AND Property='discount_percentage' AND Context='0'";
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
