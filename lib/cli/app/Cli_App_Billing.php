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
	const	SWITCH_ACCOUNT_ID		= "a";
	const	SWITCH_SKIP_PREBILLING	= "k";
	const	SWITCH_FAKE_DATE		= "d";
	
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
			
			// Set the default Log() to redirect to Cli_App_Billing::debug()
			Log::registerFunctionLog('Cli_App_Billing', 'debug', 'Cli_App_Billing');
			Log::setDefaultLog('Cli_App_Billing');
			
			// Start a new Transcation
			//$bolTransactionResult	= DataAccess::getDataAccess()->TransactionStart();
			//Log::getLog()->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully started!");
			
			$oDataAccessFlex	= DataAccess::getDataAccess();
			$oQuery				= new Query();
			
			// Perform the operation
			switch ($this->_arrArgs[self::SWITCH_MODE])
			{
				case 'GENERATE':
					// Generate an Invoice Run
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
					Log::getLog()->log($this->_copyXML($objInvoiceRun->Id));
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

				case 'REVOKE_ALL_INTERIM':
					// Retrieve list of Temporary Interim (not Final) Invoice Runs
					$oResult	= $oQuery->Execute("SELECT Id FROM InvoiceRun WHERE invoice_run_type_id = ".INVOICE_RUN_TYPE_INTERIM." AND invoice_run_status_id = ".INVOICE_RUN_STATUS_TEMPORARY);
					if ($oResult === false)
					{
						throw new Exception($oQuery);
					}
					
					// Revoke all Temporary Interim (not Final) Invoice Runs
					while ($aInvoiceRun = $oResult->fetch_assoc())
					{
						$objInvoiceRun	= new Invoice_Run($aInvoiceRun, true);
						$objInvoiceRun->revoke();
					}
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

				case 'REGENERATE':
					if (!$this->_arrArgs[self::SWITCH_INVOICE_RUN])
					{
						throw new Exception("You must supply an Invoice Run Id when running REGENERATE!");
					}
					
					$objOldInvoiceRun	= new Invoice_Run(Array('Id'=>(int)$this->_arrArgs[self::SWITCH_INVOICE_RUN]), TRUE);
					if ($objOldInvoiceRun->invoice_run_status_id != INVOICE_RUN_STATUS_TEMPORARY)
					{
						throw new Exception("Cannot Regenerate Invoice Run #{$objOldInvoiceRun->Id} because it is not a Temporary Invoice Run! (Actual Status: ".Constant_Group::getConstantGroup('invoice_run_status')->getConstantName($objOldInvoiceRun->invoice_run_status_id).")");
					}
					
					if ($this->_arrArgs[self::SWITCH_ACCOUNT_ID])
					{
						// Regenerating a single Invoice
						$objAccount		= new Account(Array('Id'=>(int)$this->_arrArgs[self::SWITCH_ACCOUNT_ID]), FALSE, TRUE);
						$objInvoiceRun	= $objOldInvoiceRun;
						$objInvoiceRun->calculateBillingPeriodDates($objInvoiceRun->BillingDate);
						
						//$this->debug($objAccount->toArray());
						//$this->debug($objInvoiceRun->toArray());
						
						// Regenerate this Account for this Invoice Run
						$objInvoice	= new Invoice();
						$objInvoice->generate($objAccount, $objInvoiceRun);
						
						// Regenerate Invoice Run Totals
						$objInvoiceRun->calculateTotals();
						
						// Copy XML
						Log::getLog()->log($this->_copyXML($objInvoiceRun->Id, (int)$this->_arrArgs[self::SWITCH_ACCOUNT_ID]));
					}
					else
					{
						// Regenerating an entire InvoiceRun
						$objInvoiceRun	= new Invoice_Run();
						$objInvoiceRun->generateCustomerGroup($objOldInvoiceRun->customer_group_id, $objOldInvoiceRun->invoice_run_type_id, strtotime($objOldInvoiceRun->BillingDate), $objOldInvoiceRun->invoice_run_schedule_id);
						Log::getLog()->log($this->_copyXML($objInvoiceRun->Id));
					}
					break;
				
				case 'ARCHIVE':
					/*
					// For a future, less hacky age...
					$strSQL	= "SELECT * FROM InvoiceRun WHERE CDRArchivedState IS NULL AND invoice_run_status_id = ".INVOICE_RUN_STATUS_COMMITTED;
					while ($arrInvoiceRun = $resInvoiceRuns->fetch_assoc())
					{
						$objInvoiceRun	= new Invoice_Run($arrInvoiceRun);
						$objInvoiceRun->archiveToCDRInvoiced();
					}
					*/
					
					$oUnarchivedInvoiceRunsResult	= $oQuery->Execute("	SELECT		ir.Id
																			
																			FROM		InvoiceRun ir
																						JOIN invoice_run_status irs ON (irs.id = ir.invoice_run_status_id)
																			
																			WHERE		irs.const_name = 'INVOICE_RUN_STATUS_COMMITTED'
																						AND	(
																								SELECT		Id
																								FROM		CDR cdr
																								WHERE		invoice_run_id = ir.Id
																								LIMIT		1
																							) IS NOT NULL;");
					if ($oUnarchivedInvoiceRunsResult === false)
					{
						throw new Exception($oQuery->Error());
					}
					while ($aInvoiceRun = $oQuery->fetch_assoc)
					{
						// Archive this Invoice Run
						$this->log("Archiving Invoice Run {$aInvoiceRun['Id']}...");
						self::_archiveInvoiceRunCDRs($aInvoiceRun['Id']);
					}
					break;
					
				case 'SAMPLE_ACCOUNT':
					// Load the Account
					$iAccountId	= (int)$this->_arrArgs[self::SWITCH_ACCOUNT_ID];
					try
					{
						$oAccount	= new Account(Array('Id'=>$iAccountId), false, true);
					}
					catch (Exception_ORM_LoadById $eException)
					{
						throw new Exception("Unable to load Account with Id {$iAccountId}");
					}
					
					Log::getLog()->log("Sampling Account #{$iAccountId}...");
					
					// Was there a Fake Date provided?
					$sDatetime	= date("Y-m-d H:i:s", ($this->_arrArgs[self::SWITCH_FAKE_DATE]) ?  $this->_arrArgs[self::SWITCH_FAKE_DATE] : time());
					$sDate		= date("Y-m-d", strtotime($sDatetime));
					
					Log::getLog()->log("Effective Date: {$sDate} ($sDatetime)");
					
					// Predict the next Billing Date
					$sInvoiceDate	= Invoice_Run::predictNextInvoiceDate($oAccount->CustomerGroup, $sDatetime);
					Log::getLog()->log("Predicted Production Date: {$sInvoiceDate}");
					
					$oDataAccessFlex->TransactionStart();
					try
					{
						Log::getLog()->log("Generating Sample Invoice Run...");
						
						// Perform the Sample Invoice Run!
						$oInvoiceRun	= new Invoice_Run();
						$oInvoiceRun->generateSingle($oAccount->CustomerGroup, INVOICE_RUN_TYPE_SAMPLES, strtotime($sInvoiceDate), $iAccountId);
						
						// Copy PDF to the front-end Server
						$this->_copyXML($oInvoiceRun->Id);
						
						$oDataAccessFlex->TransactionCommit();
					}
					catch (Exception $eException)
					{
						$oDataAccessFlex->TransactionRollback();
						throw $eException;
					}
					break;

				default:
					throw new Exception("Invalid MODE '{$this->_arrArgs[self::SWITCH_MODE]}' specified!");
			}

			// If not in test mode, Commit the Transaction
			/*if (!$this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$bolTransactionResult	= DataAccess::getDataAccess()->TransactionCommit();
				Log::getLog()->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully committed!");
			}
			else
			{
				$bolTransactionResult	= DataAccess::getDataAccess()->TransactionRollback();
				Log::getLog()->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully revoked!");
			}*/
			return 0;
		}
		catch(Exception $exception)
		{
			//$bolTransactionResult	= DataAccess::getDataAccess()->TransactionRollback();
			//Log::getLog()->log("Transaction was " . ((!$bolTransactionResult) ? 'not ' : '') . "successfully revoked!");

			if ($this->_arrArgs[self::SWITCH_TEST_RUN] || $this->_arrArgs[Cli::SWITCH_VERBOSE])
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
		// Was there a Fake Date provided?
		$strDatetime	= date("Y-m-d H:i:s", ($this->_arrArgs[self::SWITCH_FAKE_DATE]) ?  $this->_arrArgs[self::SWITCH_FAKE_DATE] : time());
		$strDate		= date("Y-m-d", strtotime($strDatetime));
		Log::getLog()->log("Today's date\t: {$strDatetime} ({$strDate})");
		
		// Are there any Invoice Runs due?
		$selPaymentTerms		= new StatementSelect("payment_terms", "customer_group_id, invoice_day, payment_terms", "id = (SELECT MAX(id) FROM payment_terms pt2 WHERE customer_group_id = payment_terms.customer_group_id)", "customer_group_id");
		$selInvoiceRunSchedule	= new StatementSelect("invoice_run_schedule", "*", "customer_group_id = <customer_group_id> AND '{$strDate}' = ADDDATE(<InvoiceDate>, INTERVAL invoice_day_offset DAY)");

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
				Log::getLog()->log("\tCustomer Group: ".Customer_Group::getForId($arrPaymentTerms['customer_group_id'])->externalName);

				// Predict the next Billing Date
				$strInvoiceDate		= Invoice_Run::predictNextInvoiceDate($arrPaymentTerms['customer_group_id'], $strDatetime);
				$intInvoiceDatetime	= strtotime($strInvoiceDate);
				Log::getLog()->log("\t\t * Predicted Billing Date\t: {$strInvoiceDate}");

				// Are there any Invoice Runs Scheduled for today?
				if ($selInvoiceRunSchedule->Execute(Array('InvoiceDate' => date('Y-m-d', $intInvoiceDatetime), 'customer_group_id' => $arrPaymentTerms['customer_group_id'])))
				{
					// There is at least one scheduled today, so run the pre-Billing Scripts
					$this->_preGenerateScripts();
					
					while ($arrInvoiceRunSchedule = $selInvoiceRunSchedule->Fetch())
					{
						Log::getLog()->log("\t\t + Generating '{$arrInvoiceRunSchedule['description']}' Invoice Run for ".Customer_Group::getForId($arrInvoiceRunSchedule['customer_group_id'])->externalName."\n");
						
						// Yes, so lets Generate!
						try
						{
							$objInvoiceRun	= new Invoice_Run();
							$objInvoiceRun->generateCustomerGroup($arrPaymentTerms['customer_group_id'], $arrInvoiceRunSchedule['invoice_run_type_id'], $intInvoiceDatetime, $arrInvoiceRunSchedule['id']);
						}
						catch (Exception $eException)
						{
							// Perform a Revoke on the Temporary Invoice Run
							if ($objInvoiceRun->Id)
							{
								$objInvoiceRun->revoke();
							}
							throw $eException;
						}
						Log::getLog()->log($this->_copyXML($objInvoiceRun->Id));
						
						// Generate Invoice Sample Email
						$objInvoiceRun->generateSampleList();
					}
				}
				elseif ($selInvoiceRunSchedule->Error())
				{
					throw new Exception("DB ERROR: ".$selInvoiceRunSchedule->Error());
				}
				else
				{
					Log::getLog()->log("\t\t ! No Invoice Runs Scheduled for today");
					continue;
				}
			}
		}
	}
	
	private function _preGenerateScripts()
	{
		static	$bolHasRun	= FALSE;
			
		if (!$bolHasRun && !$this->_arrArgs[self::SWITCH_SKIP_PREBILLING])
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
			
			// Make sure this doesn't run twice
			$bolHasRun	= TRUE;
		}
		return;
	}
	
	private function _copyXML($intInvoiceRunId, $intAccountId=null)
	{
		$strFlexXMLPath	= FLEX_BASE_PATH."files/invoices/xml/";
		
		if ($intAccountId > 0)
		{
			$strInvoicePath			= "/{$intAccountId}.xml";
			$strFlexXMLRemotePath	= "{$strFlexXMLPath}{$intInvoiceRunId}";
			$strOptions				= '';
		}
		else
		{
			$strInvoicePath			= '';
			$strFlexXMLRemotePath	= $strFlexXMLPath;
			$strOptions				= '-r';
		}
		
		$strSCPCommand	= "scp -i ".self::FLEX_FRONTEND_SHARED_KEY_FILE." {$strOptions} {$strFlexXMLPath}{$intInvoiceRunId}{$strInvoicePath} ".self::FLEX_FRONTEND_USERNAME."@".self::FLEX_FRONTEND_HOST.":{$strFlexXMLRemotePath}";
		
		self::debug($strSCPCommand);
		return shell_exec($strSCPCommand);
	}
	
	private function _copyManagementReports($strSourcePath)
	{
		if (is_dir($strSourcePath))
		{
			// Copy the Management Reports
			$strDestinationPath	= str_replace(FLEX_BASE_PATH."files/reports/", self::FLEX_MANAGEMENT_REPORT_PATH.CUSTOMER_URL_NAME.'/', dirname($strSourcePath));
			
			$strSCPCommand	= "scp -i ".self::FLEX_FRONTEND_SHARED_KEY_FILE." -r {$strSourcePath} ".self::FLEX_FRONTEND_USERNAME."@".self::FLEX_FRONTEND_HOST.":{$strDestinationPath}";
			
			self::debug($strSCPCommand);
			return shell_exec($strSCPCommand);
		}
	}
	
	private static function _archiveInvoiceRunCDRs($iInvoiceRunId)
	{
		$dsArchiveDB						= Data_Source::get('cdr');
		$oQuery								= new Query();
		
		$sFlexDatabaseHost			= $GLOBALS['**arrDatabase']['flex']['URL'];
		$sFlexDatabaseName			= $GLOBALS['**arrDatabase']['flex']['Database'];
		$sFlexDatabaseUser			= $GLOBALS['**arrDatabase']['flex']['User'];
		$sFlexDatabasePass			= $GLOBALS['**arrDatabase']['flex']['Password'];
		
		$sArchiveDatabaseHost		= $GLOBALS['**arrDatabase']['cdr']['URL'];
		$sArchiveDatabaseName		= $GLOBALS['**arrDatabase']['cdr']['Database'];
		$sArchiveDatabaseUser		= $GLOBALS['**arrDatabase']['cdr']['User'];
		$sArchiveDatabasePass		= $GLOBALS['**arrDatabase']['cdr']['Password'];	// Not actually used -- uses .pgpass file
		
		$sMySQLDumpFilename			= "{$sFlexDatabaseName}_CDR_{$iInvoiceRunId}.sql";
		$sPostgreSQLDumpFilename	= "{$sFlexDatabaseName}_CDR_{$iInvoiceRunId}.pgsql";
		$sPostgreSQLInsertFilename	= "{$sFlexDatabaseName}_cdr_invoiced_{$iInvoiceRunId}.pgsql";
		
		// Perform the MySQL Dump
		$sMySQLDumpCommand	= "mysqldump -h {$sFlexDatabaseHost} -u {$sFlexDatabaseUser} --password='{$sFlexDatabasePass}' {$sFlexDatabaseName} -t CDR --where='invoice_run_id={$iInvoiceRunId}' > {$sMySQLDumpFilename}";
		$iMySQLDumpReturn	= null;
		$sMySQLDumpOutput	= system($sMySQLDumpCommand, $iMySQLDumpReturn);
		if ($iMySQLDumpReturn > 0)
		{
			throw new Exception("Failed to get CDR dump: \n{$sMySQLDumpOutput}\n");
		}
		
		// Convert MySQL statements to PostgreSQL statements
		$sMySQL2PostgreSQLCommand	= "perl /data/bin/mysql2pgsql.pl {$sMySQLDumpFilename} {$sPostgreSQLDumpFilename}";
		$iMySQL2PostgreSQLReturn	= null;
		$sMySQL2PostgreSQLOutput	= system($sMySQL2PostgreSQLCommand, $iMySQL2PostgreSQLReturn);
		if ($iMySQL2PostgreSQLReturn > 0)
		{
			throw new Exception("Failed to Convert MySQL statements to PostgreSQL statements: \n{$sMySQL2PostgreSQLOutput}\n");
		}
		
		// Archivify Statements
		$sArchivifyStatementsCommand	= "perl -pi -e 's/INTO \"cdr\"/INTO cdr_invoiced_{$iInvoiceRunId}/' {$sPostgreSQLDumpFilename}";
		$iArchivifyStatementsReturn		= null;
		$sArchivifyStatementsOutput		= system($sArchivifyStatementsCommand, $iArchivifyStatementsReturn);
		if ($iArchivifyStatementsReturn > 0)
		{
			throw new Exception("Failed to archivify statements to PostgreSQL statements: \n{$sArchivifyStatementsOutput}\n");
		}
		
		// Trim the Dump file to only include INSERT statements
		$sTrimCommand	= "grep \"INSERT INTO\" {$sPostgreSQLDumpFilename} > {$sPostgreSQLInsertFilename}";
		$iTrimReturn	= null;
		$sTrimOutput	= system($sTrimCommand, $iTrimReturn);
		if ($iArchivifyStatementsReturn > 0)
		{
			throw new Exception("Failed to trim the Dump file to only include INSERT statements: \n{$sTrimOutput}\n");
		}
		
		// Create the cdr_invoiced_$iInvoiceRunId Table in to Archive DB
		$sCreateTableCommand	= "echo 'CREATE TABLE cdr_invoiced_{$iInvoiceRunId} (CHECK(invoice_run_id = {$iInvoiceRunId})) INHERITS (cdr_invoiced);' | psql -h {$sArchiveDatabaseHost} -U {$sArchiveDatabaseUser} {$sArchiveDatabaseName}";
		$iCreateTableReturn		= null;
		$sCreateTableOutput		= system($sCreateTableCommand, $iCreateTableReturn);
		if ($iCreateTableReturn > 0)
		{
			throw new Exception("Failed to create the cdr_invoiced_$iInvoiceRunId Table in to Archive DB: \n{$sCreateTableOutput}\n");
		}
		
		// Insert the CDRs into the Archive DB (requires .pgpass to be set up)
		$sInsertCommand	= "psql -h {$sArchiveDatabaseHost} -U {$sArchiveDatabaseUser} {$sArchiveDatabaseName} < {$sPostgreSQLInsertFilename}";
		$iInsertReturn	= null;
		$sInsertOutput	= system($sInsertCommand, $iInsertReturn);
		if ($iInsertReturn > 0)
		{
			throw new Exception("Failed to create the cdr_invoiced_$iInvoiceRunId Table in to Archive DB: \n{$sInsertOutput}\n");
		}
		
		// Verify CDR counts
		$rVerifyMySQLResult	= $oQuery->Execute("SELECT COUNT(Id) AS cdr_count FROM CDR WHERE invoice_run_id = {$iInvoiceRunId}");
		if ($rVerifyMySQLResult === false)
		{
			throw new Exception($oQuery->Error());
		}
		$aVerifyMySQL	= $rVerifyMySQLResult->fetch_assoc();
		
		$oVerifyPostgreSQLPartitionResult	= $dsArchiveDB->query("SELECT COUNT(id) AS cdr_count FROM cdr_invoiced_{$iInvoiceRunId} WHERE 1");
		if (PEAR::isError($oVerifyPostgreSQLPartitionResult))
		{
			throw new Exception($oVerifyPostgreSQLPartitionResult->getMessage() . " (DB Error: " . $oVerifyPostgreSQLPartitionResult->getUserInfo() . ")");
		}
		$aVerifyPostgreSQLPartition	= $oVerifyPostgreSQLPartitionResult->fetchRow(MDB2_FETCHMODE_ASSOC);
		
		$oVerifyPostgreSQLTableResult	= $dsArchiveDB->query("SELECT COUNT(id) AS cdr_count FROM cdr_invoiced WHERE invoice_run_id = {$iInvoiceRunId}");
		if (PEAR::isError($oVerifyPostgreSQLTableResult))
		{
			throw new Exception($oVerifyPostgreSQLTableResult->getMessage() . " (DB Error: " . $oVerifyPostgreSQLTableResult->getUserInfo() . ")");
		}
		$aVerifyPostgreSQLTable	= $oVerifyPostgreSQLTableResult->fetchRow(MDB2_FETCHMODE_ASSOC);
		
		$iCDRCount					= (int)$aVerifyMySQL['cdr_count'];
		$iCDRInvoicedCount			= (int)$aVerifyPostgreSQLPartition['cdr_count'];
		$iCDRInvoicePartitionCount	= (int)$aVerifyPostgreSQLTable['cdr_count'];
		if (!($iCDRCount === $iCDRInvoicedCount && $iCDRCount === $iCDRInvoicePartitionCount))
		{
			throw new Exception("CDR Count Mismatch: (CDR: {$iCDRCount}; cdr_invoiced_{$iInvoiceRunId}: {$iCDRInvoicePartitionCount}; cdr_invoiced: {$iCDRInvoicedCount})");
		}
		
		// Remove CDRs from Flex database
		$oDeleteResult	= $oQuery->Execute("DELETE FROM CDR WHERE invoice_run_id = {$iInvoiceRunId}");
		if ($oDeleteResult === false)
		{
			throw new Exception($oQuery->Error());
		}
		
		// File Cleanup -- BZ2 them for now, manual cleanup later
		$sCompressCommand	= "bzip2 {$sMySQLDumpFilename} {$sPostgreSQLDumpFilename} {$sPostgreSQLInsertFilename}";
		$iCompressReturn	= null;
		$sCompressOutput	= system($sCompressCommand, $iCompressReturn);
		if ($iCompressReturn > 0)
		{
			throw new Exception("Failed to compress dump files: \n{$sCompressOutput}\n");
		}
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
			if (!is_scalar($mixMessage))
			{
				$mixMessage	= print_r($mixMessage, TRUE);
			}
			CliEcho($mixMessage, $bolNewLine);
		}
	}

	function getCommandLineArguments()
	{
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "Revokes the transaction that encapsulates Billing, and provides debug data",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),

			self::SWITCH_MODE => array(
				self::ARG_LABEL			=> "MODE",
				self::ARG_REQUIRED		=> TRUE,
				self::ARG_DESCRIPTION	=> "Invoice Run operation to perform [GENERATE|COMMIT|REVOKE|REVOKE_ALL_INTERIM|EXPORT|REPORTS|REGENERATE|ARCHIVE|SAMPLE_ACCOUNT]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("GENERATE","COMMIT","REVOKE","REVOKE_ALL_INTERIM","EXPORT","REPORTS","REGENERATE","ARCHIVE","SAMPLE_ACCOUNT"))'
			),

			self::SWITCH_INVOICE_RUN	=> array(
				self::ARG_LABEL			=> "INVOICE_RUN_ID",
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "The Invoice Run Id to Commit or Revoke (required for COMMIT, REVOKE, EXPORT, REGENERATE, and REPORTS)",
				self::ARG_DEFAULT		=> NULL,
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_ACCOUNT_ID	=> array(
				self::ARG_LABEL			=> "ACCOUNT_ID",
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "The Account Id to re/generate an Invoice for (required for SAMPLE_ACCOUNT; optional for REGENERATE)",
				self::ARG_DEFAULT		=> NULL,
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),
			
			self::SWITCH_SKIP_PREBILLING => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "Skips the pre-Billing scripts (only applicable to GENERATE)",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_FAKE_DATE => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "Forces Billing to think that today is a given date",
				self::ARG_DEFAULT		=> NULL,
				self::ARG_VALIDATION	=> 'Cli::_validDate("%1$s")'
			),
		);
	}
}
?>