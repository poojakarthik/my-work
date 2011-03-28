<?php

/**
 * Cli_App_Collections
 *
 * @parent	Cli
 */
class Cli_App_Collections extends Cli
{
	const	SWITCH_TEST_RUN								= 't';
	const	SWITCH_MODE									= 'm';
	const	SWITCH_ACCOUNT_ID							= 'a';
	const	SWITCH_REDISTRIBUTE_FULL					= 'f';
	const	SWITCH_REDISTRIBUTE_FULL_INCLUDE_ARCHIVED	= 'r';

	const	MODE_BALANCE_DISTRIBUTION	= 'BALANCE_DISTRIBUTION';
	const	MODE_PROCESS				= 'PROCESS';

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

	protected function _process()
	{
		Flex_Process::factory(Flex_Process::PROCESS_COLLECTIONS_PROCESS)->lock();
		try
		{
			$this->log("Starting.");

			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode. All changes will be rolled back.", TRUE);
			}

			$iAccountId = NULL;
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
				$oPromise =	Logic_Account::getInstance($iAccountId)->getActivePromise();
				$aPromises = $oPromise === null ? array() : array($oPromise);
				$oSuspension =	Collection_Suspension::getActiveForAccount($iAccountId);
				$aActiveSuspensions = $oSuspension === null ? array() : array($oSuspension);
				Logic_Collection_BatchProcess_Report::setProcessInvocationType(Logic_Collection_BatchProcess_Report::INVOCATION_TYPE_ACCOUNT);

			}
			else
			{
				$aPromises =	Logic_Collection_Promise::getActivePromises();
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
				if (Collections_Schedule::getEligibility())
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
				else
				{
					throw new Exception("The Collections Batch Process is not eligible to run today.");
				}
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
				Log::getLog()->log("Account, Time, Memory Usage, iterations, Debit Collectables, Credit Collectables, Credit Payments, Credit Adjustments, Debit Payments,Debit Adjustments, Account Balance (based on amounts) , Payable Balance (based on balances) ");
				$aAccounts = Account::getForBalanceRedistribution($iMode);
				Logic_Account::batchRedistributeBalances($aAccounts);				
				
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
				self::ARG_DESCRIPTION	=> "Operation to perform [".self::MODE_PROCESS."|".self::MODE_BALANCE_DISTRIBUTION."]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("'.self::MODE_PROCESS.'","'.self::MODE_BALANCE_DISTRIBUTION.'"))'
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