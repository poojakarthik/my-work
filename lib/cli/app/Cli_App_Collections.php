<?php

/**
 * Cli_App_Collections
 *
 * @parent	Cli
 */
class Cli_App_Collections extends Cli
{
	const	SWITCH_TEST_RUN					= 't';
	const	SWITCH_MODE					= 'm';
	const	SWITCH_ACCOUNT_ID				= 'a';
        const	SWITCH_REDISTRIBUTE_FULL			= 'f';
        const	SWITCH_REDISTRIBUTE_FULL_INCLUDE_ARCHIVED	= 'r';
	const	SWITCH_PAYMENT_ID				= 'p';
	const	SWITCH_LIMIT					= 'lm';
	const	SWITCH_PAYMENT_RESPONSE_ID			= 'pr';
	const	SWITCH_FILE_IMPORT_ID				= 'i';

	const	MODE_PROCESS					= 'PROCESS';
	const	MODE_APPLY					= 'APPLY';
	const	MODE_NORMALISE					= 'NORMALISE';
	const	MODE_EXPORT					= 'EXPORT';
	const	MODE_BALANCE_DISTRIBUTION			= 'BALANCE_DISTRIBUTION';
	const	MODE_BATCH_PROCESS				= 'BATCH_PROCESS';

	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$this->_aArgs = $this->getValidatedArguments();

			$sMode	= '_'.strtolower($this->_aArgs[self::SWITCH_MODE]);
			if (!method_exists($this, $sMode))
			{
				throw new Exception("Invalid Mode '{$sMode}'");
			}
			else
			{
				$oDataAccess	= DataAccess::getDataAccess();

				// TEST MODE: Start Transaction
				if ($this->_aArgs[self::SWITCH_TEST_RUN] && !$oDataAccess->TransactionStart())
				{
					throw new Exception_Database($oDataAccess->Error());
				}

				try
				{
					// Call the approrite MODE method
					$this->$sMode();

					// TEST MODE: Force Rollback
					if ($this->_aArgs[self::SWITCH_TEST_RUN])
					{
						throw new Exception("TEST MODE");
					}
				}
				catch (Exception $oException)
				{
					// TEST MODE: Rollback
					if ($this->_aArgs[self::SWITCH_TEST_RUN])
					{
						$oDataAccess->TransactionRollback();
					}
					throw $oException;
				}

				// TEST MODE: Commit
				if ($this->_aArgs[self::SWITCH_TEST_RUN])
				{
				    $oDataAccess->TransactionCommit();
				}
			}
		}
		catch (Exception $oException)
		{
			echo "\n".$oException."\n";
			return 1;
		}
	}

	protected function _batch_process()
	{
	    Flex_Process::factory(Flex_Process::PROCESS_COLLECTIONS_BATCH_PROCESS)->lock();
	    try
	    {
		$this->log("Starting.");

		// The arguments are present and in a valid format if we get past this point.
		$arrArgs = $this->getValidatedArguments();

		if ($arrArgs[self::SWITCH_TEST_RUN])
		{
		    $this->log("Running in test mode. All changes will be rolled back.", TRUE);
		}

		$iAccountId;
		$aPromises;
		$aActiveSuspensions;
		$aAccounts;

		if ($arrArgs[self::SWITCH_ACCOUNT_ID])
		{
		    $iAccountId = $arrArgs[self::SWITCH_ACCOUNT_ID];
		    Log::getLog()->log("Processing Collections for Account $iAccountId");
		}
		else
		{
		    Log::getLog()->log("Processing Collections in batch for all accounts");
		}
		
		
		    $iAccountsBatchProcessIteration = 1;

		    if ($iAccountId !== null)
		    {
			$oPromise =  Logic_Account::getInstance($iAccountId)->getActivePromise();
			$aPromises = $oPromise === null ? array() : array($oPromise);
			$oSuspension =  Collection_Suspension::getActiveForAccount($iAccountId);
			$aActiveSuspensions = $oSuspension === null ? array() : array($oSuspension);
			Logic_Collection_BatchProcess_Report::setProcessInvocationType(Logic_Collection_BatchProcess_Report::INVOCATION_TYPE_ACCOUNT);

		    }
		    else
		    {
			$aPromises =  Logic_Collection_Promise::getActivePromises();
			$aActiveSuspensions = Collection_Suspension::getActive();
			Logic_Collection_BatchProcess_Report::setProcessInvocationType(Logic_Collection_BatchProcess_Report::INVOCATION_TYPE_BATCH);
		    }


		   
		    try
		    {
			Logic_Collection_Promise::batchProcess($aPromises);
			
		    }
		    catch (Exception $e)
		    {
			 Logic_Collection_BatchProcess_Report::addException($e);
			 Log::getLog($e->__toString());
			
			if ($e instanceof Exception_Database)
			{
			    throw $e;
			}
			
		    }

		   
		    try
		    {
			Logic_Collection_Suspension::batchProcess($aActiveSuspensions);
			
		    }
		    catch(Exception $e)
		    {
			
			Logic_Collection_BatchProcess_Report::addException($e);

			if ($e instanceof Exception_Database)
			{
			    throw $e;
			}
			Log::getLog()->log($e->__toString());
		    }

		    
		    try
		    {
			if ($iAccountId === null)
			{
			    $aExcludedAccounts = Logic_Collection_BatchProcess_Report::getAccountsWithExceptions();
			    $aAccounts = Logic_Account::getForBatchCollectionProcess($aExcludedAccounts);
			}
			else
			{

			    $oAccount = Logic_Account::getInstance($iAccountId);
			    $aAccounts = array($oAccount);
			}

			$iCompletedInstances = 0;
			Logic_Collection_Event_Instance::completeWaitingInstances(true);
			Logic_Stopwatch::getInstance()->start();
			$iIteration = 1;
			do
			{
			    Log::getLog()->log("About to process ".count($aAccounts)." In Batch.");
			    $iCompletedInstances = Logic_Account::batchProcessCollections($aAccounts);
			    Log::getlog()->log("Completed Scheduled Events In : ".Logic_Stopwatch::getInstance()->lap());
			    Log::getLog()->log("-------End Account Batch Collections Process Iteration $iIteration -------------------------");
			    $iIteration++;
			}
			while ($iCompletedInstances > 0);

			
			Log::getLog()->log("Total Time: ".Logic_Stopwatch::getInstance()->split());
		    }
		    catch (Exception $e)
		    {
			
			if ($e instanceof Exception_Database)
			{
			    Logic_Collection_BatchProcess_Report::addException($e);
			    throw $e;
			}
			Log::getLog()->log($e->__toString());
			Logic_Collection_BatchProcess_Report::addException($e);
		    }



		    

		    $sPath = FILES_BASE_PATH.'temp/';
		    $bPathExists = file_exists ($sPath);
		    if (!$bPathExists)
		    {
			$bPathExists = mkdir ($sPath , 0777 , true);
		    }

		    Logic_Collection_BatchProcess_Report::emailReport();
		



		$this->log("Finished.");
		return 0;

	    }
	    catch(Exception $exception)
	    {
		    // We can now show the error message
		    $this->showUsage($exception->getMessage());
		     Logic_Collection_BatchProcess_Report::addException($e);
		    return 1;
	    }

	}

	protected function _balance_distribution()
	{
	    Flex_Process::factory(Flex_Process::PROCESS_BALANCE_REDISTRIBUTION)->lock();

	    try
	    {
		    $this->log("Starting.");

		    // The arguments are present and in a valid format if we get past this point.
		    $arrArgs = $this->getValidatedArguments();

		    if ($arrArgs[self::SWITCH_TEST_RUN])
		    {
			    $this->log("Running in test mode. All changes will be rolled back.", TRUE);
		    }

		    $iMode;

		    if ($arrArgs[self::SWITCH_REDISTRIBUTE_FULL])
		    {
			if($arrArgs[self::SWITCH_REDISTRIBUTE_FULL_INCLUDE_ARCHIVED])
			{
			    $this->log("Doing redistribution on all accounts, including archived accounts.", TRUE);
			    $iMode = Account::BALANCE_REDISTRIBUTION_FORCED_INCLUDING_ARCHIVED;
			}
			else
			{
			    $this->log("Doing redistribution on all accounts", TRUE);
			    $iMode = Account::BALANCE_REDISTRIBUTION_FORCED;
			}

		    }
		    else if ($arrArgs[self::SWITCH_ACCOUNT_ID])
		    {
		       $iMode = $arrArgs[self::SWITCH_ACCOUNT_ID];
			Log::getLog()->log("Doing redistribution for Account $iMode");
		    }
		    else
		    {
			$this->log("Doing redistribution on accounts that need it only.", TRUE);
			$iMode = Account::BALANCE_REDISTRIBUTION_REGULAR;
		    }

		    if ($arrArgs[self::SWITCH_TEST_RUN])
		    {
			$oDataAccess = DataAccess::getDataAccess();
			$oDataAccess->TransactionStart();
		    }

		    try
		    {
			Log::getLog()->log("Account, Time, Memory Usage, delete linking data, reset balances, iterations, Debit Collectables, Credit Collectables, Credit Payments, Credit Adjustments, Debit Payments,Debit Adjustments, Account Balance Prior, Payable Balance Prior, Payable Balance After, Overdue Balance");

			$aAccounts = Account::getForBalanceRedistribution($iMode);
			Logic_Account::batchRedistributeBalances($aAccounts);
			Log::getLog()->log("After, ".memory_get_usage (TRUE ));//self::$aMemory['after_before_cache_clear'] = memory_get_usage (TRUE );
			
			
		    }
		    catch (Exception $e)
		    {
			
			 Log::getLog()->log($e->__toString());

		    }

		    if ($arrArgs[self::SWITCH_TEST_RUN])
		    {
			$oDataAccess->TransactionRollback();
		    }

		    $this->log("Finished.");
		    return 0;

	    }
	    catch(Exception $exception)
	    {
		    // We can now show the error message
		    $this->showUsage($exception->getMessage());
		    return 1;
	    }
	}

	protected function _process()
	{
		// Ensure that Payment Import isn't already running, then identify that it is now running
		Flex_Process::factory(Flex_Process::PROCESS_PAYMENTS_IMPORT)->lock();

		// Optional FileImport.Id parameter
		$iFileImportId	= $this->_aArgs[self::SWITCH_FILE_IMPORT_ID];
		if ($iFileImportId && ($oFileImport = File_Import::getForId()))
		{
			if ($oFileImport->Status !== FILE_IMPORTED)
			{
				throw new Exception("Only Files with Status FILE_IMPORTED (".FILE_IMPORTED.") can be Processed");
			}

			// Make sure that we have a Carrier Module defined to process this File
			Carrier_Module::getForDefinition(MODULE_TYPE_NORMALISATION_PAYMENT, $oFileImport->FileType, $oFileImport->Carrier);
		}

		// Optional Limit Parameter
		$iLimit	= (isset($this->_aArgs[self::SWITCH_LIMIT]) ? (int)$this->_aArgs[self::SWITCH_LIMIT] : null);

		// Process the Files
		try
		{
			Resource_Type_File_Import_Payment::preProcessFiles($iFileImportId, $iLimit);
		}
		catch (Exception $oException)
		{
			// TODO: Transaction if testing
			throw $oException;
		}
	}

	protected function _normalise()
	{
		// Ensure that Payment Normalisation isn't already running, then identify that it is now running
		Flex_Process::factory(Flex_Process::PROCESS_PAYMENTS_NORMALISATION)->lock();

		// Optional file_import_data.id parameter
		$iFileImportDataId	= $this->_aArgs[self::SWITCH_FILE_IMPORT_DATA_ID];
		if ($iFileImportDataId && ($oFileImportData = File_Import_Data::getForId($iFileImportDataId)))
		{
			if ($oFileImportData->Status !== FILE_IMPORT_DATA_STATUS_IMPORTED)
			{
				throw new Exception("Only File Data with Status FILE_IMPORT_DATA (".FILE_IMPORT_DATA_STATUS_IMPORTED.") can be Normalised");
			}

			// Make sure that we have a Carrier Module defined to process this File
			Carrier_Module::getForDefinition(MODULE_TYPE_NORMALISATION_PAYMENT, $oFileImport->FileType, $oFileImport->Carrier);
		}

		// Optional Limit Parameter
		$iLimit	= (isset($this->_aArgs[self::SWITCH_LIMIT]) ? (int)$this->_aArgs[self::SWITCH_LIMIT] : null);

		// Process the Records
		try
		{
			Resource_Type_File_Import_Payment::processRecords($iFileImportDataId, $iLimit);
		}
		catch (Exception $oException)
		{
			// TODO: Transaction if testing
			throw $oException;
		}
	}

	protected function _apply()
	{
		// Ensure that Payment Processing isn't already running, then identify that it is now running
		Flex_Process::factory(Flex_Process::PROCESS_PAYMENTS_PROCESSING)->lock();

		// Optional Payment.Id parameter
		$iPaymentId	= $this->_aArgs[self::SWITCH_PAYMENT_ID];
		if ($iPaymentId && ($oPayment = Payment::getForId($iPaymentId)))
		{
			Log::getLog()->log("Applying Payment #{$iPaymentId}");
			$oPayment->process();
			return;
		}

		// Apply the Payments
		try
		{
			Payment::processAll();
		}
		catch (Exception $oException)
		{
			// TODO: Transaction if testing
			throw $oException;
		}
	}

	protected function _export()
	{
		// Ensure that Payment Export isn't already running, then identify that it is now running
		Flex_Process::factory(Flex_Process::PROCESS_PAYMENTS_EXPORT)->lock();

		try
		{
			$bTestRun	= $this->_aArgs[self::SWITCH_TEST_RUN];
			if ($bTestRun)
			{
				Log::getLog()->log("** TEST MODE **");
				Log::getLog()->log("The exported files will NOT be delivered, instead will be emailed to ybs-admin@ybs.net.au");

				// Enable file delivery testing (this will force emailling of all files to ybs-admin@ybs.net.au)
				Resource_Type_File_Deliver::enableTestMode();

				$oDataAccess	= DataAccess::getDataAccess();
				if ($oDataAccess->TransactionStart() === false)
				{
					throw new Exception("Failed to START db transaction");
				}
				Log::getLog()->log("Transaction started");
			}

			Resource_Type_File_Export_Payment::exportDirectDebits();

			if ($bTestRun)
			{
				if ($oDataAccess->TransactionRollback() === false)
				{
					throw new Exception("Failed to ROLLBACK db transaction");
				}
				Log::getLog()->log("Transaction rolled back");
			}
		}
		catch (Exception $oException)
		{
			throw new Exception("Failed to export. ".$oException->getMessage());
		}
	}

	function getCommandLineArguments()
	{
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "No changes to the database.",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),

			self::SWITCH_MODE => array(
				self::ARG_LABEL			=> "MODE",
				self::ARG_REQUIRED		=> TRUE,
				self::ARG_DESCRIPTION	=> "Operation to perform [".self::MODE_PROCESS."|".self::MODE_NORMALISE."|".self::MODE_APPLY."|".self::MODE_EXPORT."|".self::MODE_BALANCE_DISTRIBUTION."|".self::MODE_BATCH_PROCESS."]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("'.self::MODE_PROCESS.'","'.self::MODE_NORMALISE.'","'.self::MODE_APPLY.'","'.self::MODE_EXPORT.'","'.self::MODE_BALANCE_DISTRIBUTION.'","'.self::MODE_BATCH_PROCESS.'"))'
			),

			self::SWITCH_PAYMENT_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "PAYMENT_ID",
				self::ARG_DESCRIPTION	=> "Payment Id (".self::MODE_APPLY." Mode only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_PAYMENT_RESPONSE_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "PAYMENT_RESPONSE_ID",
				self::ARG_DESCRIPTION	=> "Payment Response Id (".self::MODE_NORMALISE." Mode only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_FILE_IMPORT_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "FILE_IMPORT_ID",
				self::ARG_DESCRIPTION	=> "File Import Id (".self::MODE_PROCESS.", ".self::MODE_NORMALISE.", ".self::MODE_APPLY." Modes only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_LIMIT => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "LIMIT",
				self::ARG_DESCRIPTION	=> "Limit/Maximum Items to Process (".self::MODE_PROCESS.", ".self::MODE_NORMALISE." Modes only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),
		    self::SWITCH_REDISTRIBUTE_FULL => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "Redistribution on all accounts that are not archived.",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
                    self::SWITCH_REDISTRIBUTE_FULL_INCLUDE_ARCHIVED => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "Redistribution on all accounts.",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
                     self::SWITCH_ACCOUNT_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "ACCOUNT_ID",
				self::ARG_DESCRIPTION	=> "Account Id",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
		));
	}
}

?>