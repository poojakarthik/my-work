<?php

class Cli_App_Calculate_Customer_Statuses extends Cli
{
	function run()
	{
		$mungeError = '';

		try
		{
			// Include the application... 
			$this->requireOnce("flex.require.php");
			$this->requireOnce("./lib/classes/Flex.php");
			
			Flex::load();

			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			// Get the Invoice runs
			$selInvoiceRuns = new StatmentSelect("InvoiceRun", "Id, InvoiceRun, BillingDate", "(BillingDate + INTERVAL 40 DAY) > NOW()", "Id DESC");
			
			if (($outcome = $selInvoiceRuns->Execute()) === FALSE)
			{
				throw new Exception("Failed to retrieve Invoice Run information: ". $selInvoiceRuns->Error());
			}
			
			$arrInvoiceRuns = $selInvoiceRuns->FetchAll();
			
			// Loop through each invoice run returned (this will do the most recent InvoiceRun First)
			$arrResults = array();
			foreach ($arrInvoiceRuns as $arrInvoiceRun)
			{
				$intSuccessful		= 0;
				$intUnclassifiable	= 0;
				$intFailed			= 0;
				$arrAccountIds = Customer_Status_Calculator::getEligibleAccounts($arrInvoiceRun['Id']);
				
				foreach($arrAccountIds as $intAccountId)
				{
					try
					{
						$intRecId = Customer_Status_Calculator::updateFor($intAccountId, $arrInvoiceRun["Id"]);
						if ($intRecId === FALSE)
						{
							// The account did not satisfy any of the CustomerStatuses
						}
					}
					catch (Exception $e)
					{
					}
				}
				
				$arrResults[$arrInvoiceRun['InvoiceRun']] = array();
			}
			
			
	

			$arrAccountIds = array();
			while ($arrAccount = $selEligibleAccounts->Fetch())
			{
				$arrAccountIds[] = $arrAccount['Id'];
			}
			
			
			//TODO! Does this need to be in a transaction? I don't think it does
			
			
			
			// Get all eligible accounts that had an invoice generated for this invoice run
			
			

			// Get list of all Accounts eligible for an account status
			$arrEligibleAccounts = Customer_Status_Calculator::getEligibleAccounts($intInvoiceRun);
			
			// Work out what InvoiceRuns to work with and probably retrieve the details you need regarding these invoice runs
			// Maybe make an Invoice_Run class
			//TODO!
			
			
			// Calculate the 
		
			$arrCustomerStatuses = Customer_Status::getAll();
			Debug($arrCustomerStatuses);

			// Must have worked! Exit with 'OK' code 0
			return 0;
		}
		catch (Exception $exception)
		{
			$this->showUsage("ERROR: " . $exception->getMessage());
		}
	} 

	function getCommandLineArguments()
	{
		$commandLineArguments = array();
		return $commandLineArguments;
	}

}

?>
