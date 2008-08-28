<?php

class Cli_App_Calculate_Customer_Statuses extends Cli
{
	const SWITCH_DAYS = "d";
	const SWITCH_INVOICE_RUN_ID = "i";
	const SWITCH_BILLING_DATE = "b";

	function run()
	{
		$intStartTime = time();

		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();
			
			// Check that only 1 of either SWITCH_DAYS, SWITCH_INVOICE_RUN_ID or SWITCH_BILLING_DATE has been declared
			$intArgDays			= $arrArgs[self::SWITCH_DAYS];
			$intArgInvoiceRunId	= $arrArgs[self::SWITCH_INVOICE_RUN_ID];
			$strArgDate			= $arrArgs[self::SWITCH_BILLING_DATE];
			
			if	(
				($intArgDays !== NULL && $intArgInvoiceRunId !== NULL)
				||
				($intArgDays !== NULL && $strArgDate !== NULL)
				||
				($intArgInvoiceRunId !== NULL && $strArgDate !== NULL)
				)
			{
				// you can only choose one
				throw new Exception("Conflicting switches.  Choose only one of either -d, -i or -b");
			}
			
			// Include the application... 
			$this->requireOnce("flex.require.php");
			$this->requireOnce("./lib/classes/Flex.php");
			
			Flex::load();

			// Get the Invoice runs
			if ($intArgDays !== NULL)
			{
				$strWhere = "(BillingDate + INTERVAL $intArgDays DAY) > NOW()";
			}
			elseif ($intArgInvoiceRunId !== NULL)
			{
				$strWhere = "Id = $intArgInvoiceRunId";
			}
			elseif ($strArgDate !== NULL)
			{
				$strWhere = "BillingDate = '". date("Y-m-d", $strArgDate) ."'";
			}
			
			$selInvoiceRuns = new StatementSelect("InvoiceRun", "Id, InvoiceRun, BillingDate", $strWhere, "Id DESC");
			
			if (($outcome = $selInvoiceRuns->Execute()) === FALSE)
			{
				throw new Exception("Failed to retrieve Invoice Run information: ". $selInvoiceRuns->Error());
			}
			if ($outcome === 0)
			{
				// No Invoice Runs were found
				throw new Exception("No Invoice Runs were found using the specified constraints:\nWHERE $strWhere");
			}
			
			// At least 1 invoice run was found
			$arrInvoiceRuns = $selInvoiceRuns->FetchAll();
			
			$bolHasErrors = FALSE;
			$bolHasUnclassifiables = FALSE;
			
			// Loop through each invoice run returned (this will do the most recent InvoiceRun First)
			$arrResults = array();
			foreach ($arrInvoiceRuns as $arrInvoiceRun)
			{
				$this->log("Processing Invoice Run: {$arrInvoiceRun['InvoiceRun']} (id: {$arrInvoiceRun['Id']})", FALSE, FALSE, TRUE);				
				
				$intSuccessful		= 0;
				$intUnclassifiable	= 0;
				$intFailed			= 0;
				$arrAccountIds		= Customer_Status_Calculator::getEligibleAccounts($arrInvoiceRun['Id']);

				foreach($arrAccountIds as $intAccountId)
				{
					try
					{
						$intRecId = Customer_Status_Calculator::updateFor($intAccountId, $arrInvoiceRun["Id"]);
						if ($intRecId === FALSE)
						{
							// The account did not satisfy any of the CustomerStatuses
							$intUnclassifiable++;
							$this->log("WARNING: Account $intAccountId did not satisfy any of the Customer Statuses, for Invoice Run: {$arrInvoiceRun['InvoiceRun']} (id: {$arrInvoiceRun['Id']}) ", FALSE, FALSE, TRUE);
						}
						else
						{
							$intSuccessful++;
						}
					}
					catch (Exception $e)
					{
						$this->log("NON FATAL ERROR: ". $e->getMessage(), TRUE, FALSE, TRUE);
						$intFailed++;
					}
				}
				
				$bolHasUnclassifiables	= ($bolHasUnclassifiables || ($intUnclassifiable > 0));
				$bolHasErrors			= ($bolHasErrors || ($intFailed > 0));
				
				$strInvoiceRunSummary = "Summary for Invoice Run: {$arrInvoiceRun['InvoiceRun']} (id: {$arrInvoiceRun['Id']})
\tBillingDate    : {$arrInvoiceRun['BillingDate']}
\tTotalAccounts  : ". count($arrAccountIds) ."
\tSuccessful     : $intSuccessful
\tUnclassifiable : $intUnclassifiable
\tFailed         : $intFailed";
				$this->log($strInvoiceRunSummary, FALSE, FALSE, TRUE);
			}
			
			$this->log("\nCompleted successfully", FALSE, FALSE, TRUE);
			$this->log("Time Taken: ". date("i:s", time() - $intStartTime) . " (min:sec)", FALSE, FALSE, TRUE);
			if ($bolHasUnclassifiables)
			{
				$this->log("Unclassifiable accounts were encountered", FALSE, FALSE, TRUE);
			}
			if ($bolHasErrors)
			{
				$this->log("Non fatal errors were encountered", FALSE, FALSE, TRUE);
			}
			
			$this->log("\n");
			
			// Must have worked! Exit with 'OK' code 0
			return 0;
		}
		catch (Exception $exception)
		{
			$this->showUsage($exception->getMessage());
			return 1;  // Or should this be a negative number, or what?
		}
	} 

	function getCommandLineArguments()
	{
		$commandLineArguments = array(

			self::SWITCH_DAYS => array(
				self::ARG_LABEL 		=> "DAYS",
				self::ARG_REQUIRED 		=> FALSE,
				self::ARG_DESCRIPTION 	=> "customer statuses will be calculated for each Invoice Run which has a BillingDate within the last DAYS (integer) days",
				self::ARG_DEFAULT 		=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_INVOICE_RUN_ID => array(
				self::ARG_LABEL 		=> "INVOICE_RUN_ID",
				self::ARG_REQUIRED 		=> FALSE,
				self::ARG_DESCRIPTION 	=> "customer statuses will be calculated for the Invoice Run with id = INVOICE_RUN_ID (integer)",
				self::ARG_DEFAULT 		=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_BILLING_DATE 	=> array(
				self::ARG_LABEL 		=> "BILLING_DATE",
				self::ARG_REQUIRED 		=> FALSE,
				self::ARG_DESCRIPTION 	=> "customer statuses will be calculated for all Invoice Runs with BillingDate = BILLING_DATE (YYYY-MM-DD)",
				self::ARG_DEFAULT 		=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validDate("%1$s")'
			)
		);
		return $commandLineArguments;
	}

}

?>
