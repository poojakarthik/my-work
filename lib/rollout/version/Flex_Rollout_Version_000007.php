<?php

/**
 * Version 7 (seven) of database update.
 * This version: -
 * 1:	Alters name of Service.cdr_discount to Service.cdr_count
 * 2:	Adds payment_terms.samples_internal_initial_days
 * 3:	Adds payment_terms.samples_internal_final_days
 * 4:	Adds payment_terms.samples_silver_days
 * 14:	Adds payment_terms.samples_bronze_days
 */

class Flex_Rollout_Version_000007 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);
	
		// Fix Service.cdr_discount
		$strSQL	= "ALTER TABLE Service CHANGE cdr_discount cdr_count int(11) NULL DEFAULT NULL COMMENT 'The number of Unbilled CDRs the Service had at the last Rating run'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to rename Service.cdr_discount to Service.cdr_count. ' . mysqli_errno() . '::' . mysqli_error());
		}
		$this->rollbackSQL[] ='ALTER TABLE Service CHANGE cdr_count cdr_discount int(11) NULL DEFAULT NULL;';
		
		// Add new payment_terms fields pertaining to Sample Billing Runs
		$strSQL	=	"ALTER TABLE payment_terms 
						ADD samples_internal_initial_days SMALLINT NOT NULL COMMENT 'Offset in days from the Billing Date that the Initial YBS Internal Samples are run', 
						ADD samples_internal_final_days SMALLINT NOT NULL COMMENT 'Offset in days from the Billing Date that the Final YBS Internal Samples are run',  
						ADD samples_bronze_days SMALLINT NOT NULL COMMENT 'Offset in days from the Billing Date that the Bronze Samples are run', 
						ADD samples_silver_days SMALLINT NOT NULL COMMENT 'Offset in days from the Billing Date that the Silver Samples are run' 
					;"; 
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add new Billing Samples columns to payment_terms. ' . mysqli_errno() . '::' . mysqli_error());
		}
		$this->rollbackSQL[] =	'ALTER TABLE payment_terms 
									DROP COLUMN samples_internal_initial_days,
									DROP COLUMN samples_internal_final_days,
									DROP COLUMN samples_bronze_days,
									DROP COLUMN samples_silver_days
								;';

		// Need to get the additional payment terms from the user
		$intInternalIntitialOffset	= 0 - abs($this->getUserResponseInteger("How many days before the Invoice Run should the Intitial YBS Internal Samples run?"));
		$intInternalFinalOffset		= 0 - abs($this->getUserResponseInteger("How many days before the Invoice Run should the Final YBS Internal Samples run?"));
		$intBronzeOffset 			= 0 - abs($this->getUserResponseInteger("How many days before the Invoice Run should the Bronze Samples run?"));
		$intSilverOffset 			= 0 - abs($this->getUserResponseInteger("How many days before the Invoice Run should the Silver Samples run?"));

		$strSQL = "	UPDATE payment_terms SET 
						samples_internal_initial_days = $intInternalIntitialOffset,
						samples_internal_final_days = $intInternalFinalOffset,
						samples_bronze_days = $intBronzeOffset,
						samples_silver_days = $intSilverOffset";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate payment_terms table. ' . mysqli_errno() . '::' . mysqli_error());
		}
		
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
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . mysqli_errno() . '::' . mysqli_error());
				}
			}
		}
	}
}

?>
