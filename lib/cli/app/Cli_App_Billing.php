<?php
//----------------------------------------------------------------------------//
// Cli_App_Billing
//----------------------------------------------------------------------------//
/**
 * Cli_App_Billing
 *
 * Billing CLI Application
 *
 * Billing CLI Application
 *
 * @class	Cli_App_Billing
 * @parent	Cli
 */
class Cli_App_Billing extends Cli
{
	const	SWITCH_TEST_RUN			= "t";
	const	SWITCH_MODE				= "m";
	const	SWITCH_INVOICE_RUN		= "i";
	
	function run()
	{
		try
		{
			$this->log("Starting.");
			
			// The arguments are present and in a valid format if we get past this point.
			$this->_arrArgs = $this->getValidatedArguments();
			
			if ($this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode. All changes will be rolled back.", TRUE);
			}
			
			// Any additional Includes
			$this->requireOnce(FLEX_BASE_PATH.'flex.require.php');
			$this->requireOnce(FLEX_BASE_PATH.'lib/classes/Flex.php');
			
			if ($this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				define('BILLING_TEST_MODE'	, TRUE);
			}
			
			// Start a new Transcation
			DataAccess::getDataAccess()->TransactionStart();
			
			// Perform the operation
			switch ($this->_arrArgs[self::SWITCH_MODE])
			{
				case 'GENERATE':
					// Generate an Invoice Run
					$this->_generate();
					break;
					
				case 'REVOKE':
					// Revoke Temporary Invoice Runs
					$objInvoiceRun	= new Invoice_Run(Array('Id' => $this->_arrArgs[self::SWITCH_INVOICE_RUN]), TRUE);
					$objInvoiceRun->revoke();
					break;
					
				case 'COMMIT':
					// Commit the Invoice Run
					$this->_commit();
					break;
				
				default:
					throw new Exception("Invalid MODE '{$this->_arrArgs[self::SWITCH_MODE]}' specified!");
			}
			
			// If not in test mode, Commit the Transaction
			if (!$this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				DataAccess::getDataAccess()->TransactionCommit();
			}
			else
			{
				DataAccess::getDataAccess()->TransactionRollback();
			}
			
			$this->log("Finished.");
			return 0;
		}
		catch(Exception $exception)
		{
			DataAccess::getDataAccess()->TransactionRollback();
			
			// We can now show the error message
			$this->showUsage($exception->getMessage());
			return 1;
		}
	}
	
	private function _generate()
	{
		// Are there any Invoice Runs due?
		$selPaymentTerms		= new StatementSelect("payment_terms", "invoice_day, payment_terms", "1", "id DESC", "1");
		$selInvoiceRunSchedule	= new StatementSelect("invoice_run_schedule", "*", "CURDATE() = ADDDATE(<InvoiceDate>, INTERVAL invoice_day_offset DAY");
		
		if (!$selPaymentTerms->Execute())
		{
			if ($selPaymentTerms->Error())
			{
				throw new Exception("DB ERROR: ".$selPaymentTerms->Error());
			}
			else
			{
				throw new Exception("No Payment Terms data found!");
			}
		}
		else
		{
			$arrPaymentTerms	= $selPaymentTerms->Fetch();
			
			// Predict the next Billing Date
			$strDay				= str_pad($arrPaymentTerms['invoice_day'], 2, '0', STR_PAD_LEFT);
			$intInvoiceDatetime	= strtotime(date("Y-m-{$strDay} 00:00:00"));
			if ((int)date("d") > $arrPaymentTerms['invoice_day'])
			{
				// Billing Date is next Month
				$intInvoiceDatetime	= strtotime("+1 month", $intInvoiceDatetime);
			}
			$strInvoiceDate	= date("Y-m-d", $intInvoiceDatetime);
			
			// Are there any Invoice Runs Scheduled for today?
			if ($selInvoiceRunSchedule->Execute(Array('InvoiceDate' => $strInvoiceDate)))
			{
				while ($arrInvoiceRunSchedule = $selInvoiceRunSchedule->Fetch())
				{
					$this->log("Generating {'{$arrInvoiceRunSchedule['description']}'} Invoice Run for ".GetConstantDescription($arrInvoiceRunSchedule['customer_group_id'], 'CustomerGroup'));
					
					// Yes, so lets Generate!
					Invoice_Run::generate($arrInvoiceRunSchedule['customer_group_id'], $arrInvoiceRunSchedule['invoice_run_type_id'], $intInvoiceDatetime, $arrInvoiceRunSchedule['id']);
				}
			}
			elseif ($selInvoiceRunSchedule->Error())
			{
				throw new Exception("DB ERROR: ".$selInvoiceRunSchedule->Error());
			}
			else
			{
				$this->log("No Invoice Runs Scheduled for today");
				return;
			}
		}
	}
	
	private function _commit()
	{
		// TODO
		throw new Exception("Cli_App_Billing::_commit() has not been implemented yet!");
	}
	
	public static function debug($strMessage, $bolNewLine=TRUE)
	{
		if (defined(BILLING_TEST_MODE) && BILLING_TEST_MODE)
		{
			CliEcho($strMessage, $bolNewLine);
		}
	}
	
	function getCommandLineArguments()
	{
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "For testing the Billing Implementation with dummy data",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_MODE => array(
				self::ARG_LABEL			=> "MODE",
				self::ARG_REQUIRED		=> TRUE,
				self::ARG_DESCRIPTION	=> "Invoice Run operation to perform [GENERATE|COMMIT|REVOKE]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("GENERATE","COMMIT","REVOKE"))'
			),
			
			self::SWITCH_INVOICE_RUN	=> array(
				self::ARG_LABEL			=> "INVOICE_RUN_ID",
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "The Invoice Run Id to Commit or Revoke (required for COMMIT and REVOKE)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			)
		);
	}
}
?>