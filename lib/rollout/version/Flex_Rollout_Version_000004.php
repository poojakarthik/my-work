<?php

/**
 * Version 4 (four) of database update.
 * This version: -
 *	1:	Creates payment_terms table
 */

class Flex_Rollout_Version_000004 extends Flex_Rollout_Version
{
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		$strSQL = "
			CREATE TABLE payment_terms (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
				invoice_day SMALLINT UNSIGNED NOT NULL COMMENT 'Day of month on which to invoice',
				payment_terms SMALLINT UNSIGNED NOT NULL COMMENT 'Number of days after invoicing when payment becomes due',
				overdue_notice_days SMALLINT UNSIGNED NOT NULL COMMENT 'Number of days after invoicing when an overdue notice should be sent',
				suspension_notice_days SMALLINT UNSIGNED NOT NULL COMMENT 'Number of days after invoicing when a suspension notice should be sent',
				final_demand_notice_days SMALLINT UNSIGNED NOT NULL COMMENT 'Number of days after invoicing when a final demand notice should be sent',
				automatic_barring_days SMALLINT UNSIGNED NOT NULL COMMENT 'Number of days after invoicing when the account should be automatically barred',
				PRIMARY KEY ( id )
			) ENGINE = InnoDB COMMENT = 'System-wide payment terms' 
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create payment_terms table. ' . mysqli_errno() . '::' . mysqli_error());
		}

		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		// Need to get the payment terms from the user
		$intInvoiceDay 				= 						  $this->getInteger("On which day of the month should invoices be generated?");
		$intPaymentTerms 			= $intInvoiceDay 		+ $this->getInteger("How many days after invoicing before payment is due?");
		$intOverdueDays 			= $intPaymentTerms 		+ $this->getInteger("How many days after payment is due before overdue notices should be sent?");
		$intSuspensionDays 			= $intOverdueDays 		+ $this->getInteger("How many days after overdue notices are sent before suspension notices should be sent?");
		$intFinalDemandDays 		= $intSuspensionDays 	+ $this->getInteger("How many days after suspension notices are sent before final demands should be sent?");
		$intAutomaticBarringDays 	= $intFinalDemandDays 	+ $this->getInteger("How many days after final demands are sent before accounts should be automatically barred?");

		$strSQL = "
			INSERT INTO payment_terms 
						(invoice_day, 			payment_terms, 				overdue_notice_days, 
						suspension_notice_days, final_demand_notice_days, 	automatic_barring_days)
				VALUES ($intInvoiceDay, 		$intPaymentTerms, 			$intOverdueDays, 
						$intSuspensionDays, 	$intFinalDemandDays, 		$intAutomaticBarringDays)";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate payment_terms table. ' . mysqli_errno() . '::' . mysqli_error());
		}
	}

	public function getInteger($message)
	{
		$ok = TRUE;
		do
		{
			$msg = "\n".$message;
			if (!$ok)
			{
				$msg = "\nInvalid response. Please enter an integer value." . $msg;
			}
			$response = $this->getUserResponse($msg);
			$ok = FALSE;
		} while(!is_numeric($response));
		return intval($response);
	}
}

?>
