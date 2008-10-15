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
	
	const	FLEX_FRONTEND_HOST				= "10.50.50.131";
	const	FLEX_FRONTEND_USERNAME			= "ybs-admin";
	const	FLEX_FRONTEND_SHARED_KEY_FILE	= "/home/ybs-admin/.ssh/id_dsa";
	
	const	FLEX_MANAGEMENT_REPORT_PATH		= "/data/www/reports.yellowbilling.com.au/html/";
	
	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$this->_arrArgs = $this->getValidatedArguments();

			if ($this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode. All changes will be rolled back.", TRUE);
			}

			// Any additional Includes
			//$this->requireOnce('flex.require.php');
			require_once("Spreadsheet/Excel/Writer.php");
			$this->requireOnce('lib/classes/Flex.php');
			Flex::load();

			if ($this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				define('BILLING_TEST_MODE'	, TRUE);
			}
			define('INVOICE_XML_PATH', FILES_BASE_PATH.'invoices/xml/');

			// Start a new Transcation
			$bolTransactionResult	= DataAccess::getDataAccess()->TransactionStart();
			$this->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully started!");

			// Perform the operation
			switch ($this->_arrArgs[self::SWITCH_MODE])
			{
				case 'GENERATE':
					// Generate an Invoice Run
					$this->_preGenerateScripts();
					$this->_generate();
					break;

				case 'EXPORT':
					if (!$this->_arrArgs[self::SWITCH_INVOICE_RUN])
					{
						throw new Exception("You must supply an Invoice Run Id when running EXPORT!");
					}

					// Revoke Temporary Invoice Runs
					$objInvoiceRun	= new Invoice_Run(Array('Id' => $this->_arrArgs[self::SWITCH_INVOICE_RUN]), TRUE);
					$objInvoiceRun->export();
					$this->log($this->_copyXML($objInvoiceRun->Id));
					break;
				
				case 'REPORTS':
					if (!$this->_arrArgs[self::SWITCH_INVOICE_RUN])
					{
						throw new Exception("You must supply an Invoice Run Id when running REPORTS!");
					}

					// Revoke Temporary Invoice Runs
					$objInvoiceRun	= new Invoice_Run(Array('Id' => $this->_arrArgs[self::SWITCH_INVOICE_RUN]), TRUE);
					$this->_copyManagementReports(Report_Management::runAll($objInvoiceRun));
					break;

				case 'REVOKE':
					if (!$this->_arrArgs[self::SWITCH_INVOICE_RUN])
					{
						throw new Exception("You must supply an Invoice Run Id when running REVOKE!");
					}
					
					// Revoke Temporary Invoice Runs
					$objInvoiceRun	= new Invoice_Run(Array('Id' => $this->_arrArgs[self::SWITCH_INVOICE_RUN]), TRUE);
					$objInvoiceRun->revoke();
					break;

				case 'COMMIT':
					if (!$this->_arrArgs[self::SWITCH_INVOICE_RUN])
					{
						throw new Exception("You must supply an Invoice Run Id when running COMMIT!");
					}
					
					// Commit the Invoice Run
					$objInvoiceRun	= new Invoice_Run(Array('Id' => $this->_arrArgs[self::SWITCH_INVOICE_RUN]), TRUE);
					$objInvoiceRun->commit();
					break;
					break;

				default:
					throw new Exception("Invalid MODE '{$this->_arrArgs[self::SWITCH_MODE]}' specified!");
			}

			// If not in test mode, Commit the Transaction
			if (!$this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$bolTransactionResult	= DataAccess::getDataAccess()->TransactionCommit();
				$this->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully committed!");
			}
			else
			{
				$bolTransactionResult	= DataAccess::getDataAccess()->TransactionRollback();
				$this->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully revoked!");
			}
			return 0;
		}
		catch(Exception $exception)
		{
			$bolTransactionResult	= DataAccess::getDataAccess()->TransactionRollback();
			$this->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully revoked!");

			if ($this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$strMessage	= $exception->__toString();
			}
			else
			{
				$strMessage	= $exception->getMessage();
			}

			// We can now show the error message
			$this->showUsage($strMessage);
			return 1;
		}
	}

	private function _generate()
	{
		// Are there any Invoice Runs due?
		$selPaymentTerms		= new StatementSelect("payment_terms", "customer_group_id, invoice_day, payment_terms", "id = (SELECT MAX(id) FROM payment_terms pt2 WHERE customer_group_id = payment_terms.customer_group_id)", "customer_group_id");
		$selInvoiceRunSchedule	= new StatementSelect("invoice_run_schedule", "*", "customer_group_id = <customer_group_id> AND <InvoiceDate> = SUBDATE(CURDATE(), INTERVAL invoice_day_offset DAY)");

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
			// Process each set of current Payment Terms
			while ($arrPaymentTerms = $selPaymentTerms->Fetch())
			{
				$this->log("\tCustomer Group: ".GetConstantDescription($arrPaymentTerms['customer_group_id'], 'CustomerGroup'));

				// Predict the next Billing Date
				$strDay				= str_pad($arrPaymentTerms['invoice_day'], 2, '0', STR_PAD_LEFT);
				$intInvoiceDatetime	= strtotime(date("Y-m-{$strDay} 00:00:00"));
				if ((int)date("d") > $arrPaymentTerms['invoice_day'])
				{
					// Billing Date is next Month
					$intInvoiceDatetime	= strtotime("+1 month", $intInvoiceDatetime);
				}
				$strInvoiceDate	= date("Y-m-d", $intInvoiceDatetime);
				$this->log("\t\t * Predicted Billing Date\t: {$strInvoiceDate}");

				// Are there any Invoice Runs Scheduled for today?
				if ($selInvoiceRunSchedule->Execute(Array('InvoiceDate' => $strInvoiceDate, 'customer_group_id' => $arrPaymentTerms['customer_group_id'])))
				{
					while ($arrInvoiceRunSchedule = $selInvoiceRunSchedule->Fetch())
					{
						$this->log("\t\t + Generating '{$arrInvoiceRunSchedule['description']}' Invoice Run for ".GetConstantDescription($arrInvoiceRunSchedule['customer_group_id'], 'CustomerGroup')."\n");

						// Yes, so lets Generate!
						$objInvoiceRun	= new Invoice_Run();
						$objInvoiceRun->generate($arrPaymentTerms['customer_group_id'], $arrInvoiceRunSchedule['invoice_run_type_id'], $intInvoiceDatetime, $arrInvoiceRunSchedule['id']);
						$this->log($this->_copyXML($objInvoiceRun->Id));
					}
				}
				elseif ($selInvoiceRunSchedule->Error())
				{
					throw new Exception("DB ERROR: ".$selInvoiceRunSchedule->Error());
				}
				else
				{
					$this->log("\t\t ! No Invoice Runs Scheduled for today");
					continue;
				}
			}
		}
	}
	
	private function _preGenerateScripts()
	{
		$strWorkingDirectory	= getcwd();
		chdir(BACKEND_BASE_PATH.'process/');
		
		// Run the Multi-part script
		$strCommand				= "php multipart.php pre_billing.cfg.php";
		$ptrProcess				= popen($strCommand, 'r');
		$arrBlank				= Array();
		stream_set_blocking($ptrProcess, 0);
		while (!feof($ptrProcess))
		{
			$arrProcess	= Array($ptrProcess);
			if (stream_select($arrProcess, $arrBlank, $arrBlank, 0, 500000))
			{
				// Check for output every 0.5s
				self::debug(stream_get_contents($ptrProcess), FALSE);
			}
		}
		$intReturnCode = pclose($ptrProcess);
		
		chdir($strWorkingDirectory);
		
		// Was there an error running a child script?
		if ($intReturnCode > 0)
		{
			throw new Exception("There was an error running one of the pre-Generate Scripts");
		}
	}
	
	private function _copyXML($intInvoiceRunId)
	{
		$strFlexXMLPath	= FLEX_BASE_PATH."files/invoices/xml/";
		
		$strSCPCommand	= "scp -i ".self::FLEX_FRONTEND_SHARED_KEY_FILE." -r {$strFlexXMLPath}{$intInvoiceRunId} ".self::FLEX_FRONTEND_USERNAME."@".self::FLEX_FRONTEND_HOST.":{$strFlexXMLPath}";
		
		self::debug($strSCPCommand);
		return shell_exec($strSCPCommand);
	}
	
	private function _copyManagementReports($strSourcePath)
	{
		if (is_dir($strSourcePath))
		{
			// Copy the Management Reports
			$strDestinationPath	= str_replace(FLEX_BASE_PATH."files/reports/", self::FLEX_MANAGEMENT_REPORT_PATH.CUSTOMER_URL_NAME.'/', dirname($strSourcePath));
			
			$strSCPCommand	= "scp -i ".self::FLEX_FRONTEND_SHARED_KEY_FILE." -r {$strSourcePath} ".self::FLEX_FRONTEND_USERNAME."@".self::FLEX_FRONTEND_HOST.":{$strFlexXMLPath}";
			
			self::debug($strSCPCommand);
			return shell_exec($strSCPCommand);
		}
	}
	
	private function _commit()
	{
		// TODO
		
	}

	public static function debug($mixMessage, $bolNewLine=TRUE)
	{
		if (defined('BILLING_TEST_MODE') && BILLING_TEST_MODE)
		{
			if (!is_scalar($mixMessage))
			{
				$mixMessage	= print_r($mixMessage, TRUE);
			}
			CliEcho($mixMessage, $bolNewLine);
		}
		else
		{
			// FIXME: Output to normal log
			CliEcho($mixMessage, $bolNewLine);
		}
	}

	function getCommandLineArguments()
	{
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "Revokes the transaction that encapsualtes Billing, and provides debug data",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),

			self::SWITCH_MODE => array(
				self::ARG_LABEL			=> "MODE",
				self::ARG_REQUIRED		=> TRUE,
				self::ARG_DESCRIPTION	=> "Invoice Run operation to perform [GENERATE|COMMIT|REVOKE|EXPORT|REPORTS]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("GENERATE","COMMIT","REVOKE","EXPORT","REPORTS"))'
			),

			self::SWITCH_INVOICE_RUN	=> array(
				self::ARG_LABEL			=> "INVOICE_RUN_ID",
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "The Invoice Run Id to Commit or Revoke (required for COMMIT, REVOKE, EXPORT, and REPORTS)",
				self::ARG_DEFAULT		=> NULL,
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			)
		);
	}
}
?>