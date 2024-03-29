<?php
class Cli_App_Collections extends Cli {
	const	SWITCH_TEST_RUN = 't';
	const	SWITCH_MODE = 'm';
	const	SWITCH_ACCOUNT_ID = 'a';
	const	SWITCH_REDISTRIBUTE_FULL = 'f';
	const	SWITCH_REDISTRIBUTE_FULL_INCLUDE_ARCHIVED = 'r';
	const	SWITCH_EVENT_IDENTIFIER = 'e';
	const	SWITCH_EFFECTIVE_DATE = 'd';
	const	SWITCH_NEXT_DATE = 'n';

	const	MODE_BALANCE_DISTRIBUTION = 'BALANCE_DISTRIBUTION';
	const	MODE_PROCESS = 'PROCESS';
	const	MODE_EVENT_ELIGIBILITY = 'EVENT_ELIGIBILITY';

	const	EVENT_IDENTIFIER_DIRECTDEBIT = 'directdebit';

	function run() {
		try {
			// The arguments are present and in a valid format if we get past this point.
			$this->_aArgs = $this->getValidatedArguments();

			$sMode	= '_'.strtolower($this->_aArgs[self::SWITCH_MODE]);
			if (!method_exists($this, $sMode)) {
				throw new Exception("Invalid Mode '{$sMode}'");
			} else {
				$oDataAccess = DataAccess::getDataAccess();

				// TEST MODE: Start Transaction
				if ($this->_aArgs[self::SWITCH_TEST_RUN] && !$oDataAccess->TransactionStart()) {
					throw new Exception_Database($oDataAccess->Error());
				}

				try {
					// Call the approrite MODE method
					$this->$sMode();

					// TEST MODE: Force Rollback
					if ($this->_aArgs[self::SWITCH_TEST_RUN]) {
						throw new Exception("TEST MODE");
					}
				} catch (Exception $oException) {
					// TEST MODE: Rollback
					if ($this->_aArgs[self::SWITCH_TEST_RUN]) {
						$oDataAccess->TransactionRollback();
					}
					throw $oException;
				}

				// TEST MODE: Commit
				if ($this->_aArgs[self::SWITCH_TEST_RUN]) {
					$oDataAccess->TransactionCommit();
				}
			}
		} catch (Exception $oException) {
			echo "\n".$oException."\n";
			return 1;
		}
	}

	protected function _process() {
		Flex_Process::factory(Flex_Process::PROCESS_COLLECTIONS_PROCESS)->lock();
		try {
			$this->log("Starting.");

			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			if ($arrArgs[self::SWITCH_TEST_RUN]) {
				$this->log("Running in test mode. All changes will be rolled back.", true);
			}

			$iAccountId = null;
			$aPromises;
			$aActiveSuspensions;
			$aAccounts;

			if ($arrArgs[self::SWITCH_ACCOUNT_ID]) {
				$iAccountId = $arrArgs[self::SWITCH_ACCOUNT_ID];
				Log::getLog()->log("Processing Collections for Account {$iAccountId}, starting with balance redistribution for this account only.");
				Logic_Account::getInstance($iAccountId)->redistributeBalances();
			} else {
				Log::getLog()->log("Processing Collections in batch for all accounts, starting with balance redistribution for all accounts that need it.");
				$aAccountsForRedistribution = Account::getForBalanceRedistribution();
				Logic_Account::batchRedistributeBalances($aAccountsForRedistribution);
			}

			Logic_Account::clearCache();

			$iAccountsBatchProcessIteration = 1;

			if ($iAccountId !== null) {
				$oPromise =	Logic_Account::getInstance($iAccountId)->getActivePromise();
				$aPromises = $oPromise === null ? array() : array($oPromise);
				$oSuspension =	Collection_Suspension::getActiveForAccount($iAccountId);
				$aActiveSuspensions = $oSuspension === null ? array() : array($oSuspension);
				Logic_Collection_BatchProcess_Report::setProcessInvocationType(Logic_Collection_BatchProcess_Report::INVOCATION_TYPE_ACCOUNT);
			} else {
				$aPromises = Logic_Collection_Promise::getActivePromises();
				$aActiveSuspensions = Collection_Suspension::getActive();
				Logic_Collection_BatchProcess_Report::setProcessInvocationType(Logic_Collection_BatchProcess_Report::INVOCATION_TYPE_BATCH);
			}

			try {
				Logic_Collection_Promise::batchProcess($aPromises);
			} catch (Exception $e) {
				Logic_Collection_BatchProcess_Report::addException($e);
				Log::getLog($e->__toString());

				if ($e instanceof Exception_Database) {
					throw $e;
				}
			}

			Logic_Account::clearCache();

			try {
				Logic_Collection_Suspension::batchProcess($aActiveSuspensions);
			} catch(Exception $e) {
				Logic_Collection_BatchProcess_Report::addException($e);

				if ($e instanceof Exception_Database) {
					throw $e;
				}
				Log::getLog()->log($e->__toString());
			}

			Logic_Account::clearCache();

			try {
				if (Collections_Schedule::getEligibility()) {
					if ($iAccountId === null) {
						$aExcludedAccounts = Logic_Collection_BatchProcess_Report::getAccountsWithExceptions();
						$aAccounts = Logic_Account::getForBatchCollectionProcess($aExcludedAccounts);
					} else {
						$oAccount = Logic_Account::getInstance($iAccountId);
						$aAccounts = array($oAccount->id => $oAccount);
					}

					$iCompletedInstances = 0;
					
					Logic_Stopwatch::getInstance()->start();
					//Check if there are any uncompleted automated events left over from last time......
					//Logic_Collection_Event_Instance::completeScheduledInstancesForAccounts($aAccounts);
					$iIteration = 1;
					do {
						$iCompletedInstances = Logic_Account::batchProcessCollections($aAccounts);
						//Log::getlog()->log("Completed Scheduled Events for collections process iteration $iIteration In : ".Logic_Stopwatch::getInstance()->lap()." seconds.");
						Log::getLog()->log("-------End Account Collections Process Iteration {$iIteration} -------------------------");
						$iIteration++;
					}
					while ($iCompletedInstances > 0);

					Log::getLog()->log("Finished. Total Collections Process Time: ".Logic_Stopwatch::getInstance()->split());
				} else {
					throw new Exception("The Collections Batch Process is not eligible to run today.");
				}
			} catch (Exception $e) {
				if ($e instanceof Exception_Database) {
					Logic_Collection_BatchProcess_Report::addException($e);
					throw $e;
				}
				Log::getLog()->log($e->__toString());
				Logic_Collection_BatchProcess_Report::addException($e);
			}
		} catch(Exception $exception) {
			// We can now show the error message
			Log::getLog()->log($exception->__toString());
			Logic_Collection_BatchProcess_Report::addException($e);			
		}

		$sPath = FILES_BASE_PATH.'temp/';
		$bPathExists = file_exists($sPath);
		if (!$bPathExists) {
			$bPathExists = mkdir($sPath, 0777, true);
		}

		Logic_Collection_BatchProcess_Report::emailReport();
		$this->log("Finished.");
		return 0;
	}

	protected function _balance_distribution() {
		try {
			$this->log("Starting.");

			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			if ($arrArgs[self::SWITCH_TEST_RUN]) {
				$this->log("Running in test mode. All changes will be rolled back.", TRUE);
			}

			$iMode;
			$iAccountId;

			if ($arrArgs[self::SWITCH_REDISTRIBUTE_FULL]) {
				Flex_Process::factory(Flex_Process::PROCESS_BALANCE_REDISTRIBUTION)->lock();
				$iAccountId = NULL;
				if($arrArgs[self::SWITCH_REDISTRIBUTE_FULL_INCLUDE_ARCHIVED]) {
					$this->log("Doing redistribution on all accounts, including archived accounts.", TRUE);
					$iMode = Account::BALANCE_REDISTRIBUTION_FORCED_INCLUDING_ARCHIVED;
				} else {
					$this->log("Doing redistribution on all accounts", TRUE);
					$iMode = Account::BALANCE_REDISTRIBUTION_FORCED;
				}
			} else if ($arrArgs[self::SWITCH_ACCOUNT_ID]) {
				$iAccountId		= $arrArgs[self::SWITCH_ACCOUNT_ID];
				$iMode			= NULL;
				Log::getLog()->log("Doing redistribution for Account {$iAccountId}");
			} else {
				Flex_Process::factory(Flex_Process::PROCESS_BALANCE_REDISTRIBUTION)->lock();
				$this->log("Doing redistribution on accounts that need it only.", TRUE);
				$iMode = Account::BALANCE_REDISTRIBUTION_REGULAR;
				$iAccountId = NULL;
			}

			if ($arrArgs[self::SWITCH_TEST_RUN]) {
				$oDataAccess = DataAccess::getDataAccess();
				$oDataAccess->TransactionStart();
			}

			try {
				$aAccounts = Account::getForBalanceRedistribution($iMode, $iAccountId);
				Logic_Account::batchRedistributeBalances($aAccounts);
			} catch (Exception $e) {
				Log::getLog()->log($e->__toString());
			}

			if ($arrArgs[self::SWITCH_TEST_RUN]) {
				$oDataAccess->TransactionRollback();
			}

			$this->log("Finished.");
			return 0;
		} catch(Exception $exception) {
			// We can now show the error message
			$this->showUsage($exception->getMessage());
			return 1;
		}
	}

	protected function _event_eligibility() {
		// Check arguments
		$mCollectionEventIdentifier = $this->_aArgs[self::SWITCH_EVENT_IDENTIFIER];
		$sEffectiveDate = $this->_aArgs[self::SWITCH_EFFECTIVE_DATE];
		$bNextDate = $this->_aArgs[self::SWITCH_NEXT_DATE];

		//throw new Exception(print_r($this->_aArgs, true));

		// Event Identifier
		if (trim($mCollectionEventIdentifier)) {
			$bIsDirectDebit = (strtolower(preg_replace('/[\s\_\-]/', '', $mCollectionEventIdentifier)) == self::EVENT_IDENTIFIER_DIRECTDEBIT) ? true : false;
			$oCollectionEvent = ($bIsDirectDebit) ? null : Collection_Event::getForId($mCollectionEventIdentifier);
		} else {
			throw new Exception('You must supply a Collections Event (or Direct Debit) to check for eligibilty.');
		}

		// Date
		$iEffectiveDate = time();
		if ($sEffectiveDate) {
			$iEffectiveDate = strtotime($sEffectiveDate);
			if ($iEffectiveDate === false) {
				throw new Exception("'{$sEffectiveDate}' is not a valid effective date");
			}
		}
		$sEffectiveDate = date("Y-m-d", $iEffectiveDate);

		$bEligible = false;
		$sEligibleDate = $sEffectiveDate;
		$sEventDescription = ($bIsDirectDebit) ? 'Direct Debit' : "Collection Event #{$oCollectionEvent->id} '{$oCollectionEvent->name}'";
		while (!$bEligible && ($sEligibleDate === $sEffectiveDate || $bNextDate)) {
			$bEligible = Collections_Schedule::getEligibility(($oCollectionEvent) ? $oCollectionEvent->id : null, $bIsDirectDebit, $sEligibleDate);
			Log::getLog()->log("{$sEventDescription} is ".(($bEligible) ? 'eligible' : '*not* eligible')." on ".date('l, j F Y', strtotime($sEligibleDate)));

			$sEligibleDate = date('Y-m-d', strtotime('+1 day', strtotime($sEligibleDate)));
		}
	}

	function getCommandLineArguments() {
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "No changes to the database.",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			),
			self::SWITCH_MODE => array(
				self::ARG_LABEL => "MODE",
				self::ARG_REQUIRED => TRUE,
				self::ARG_DESCRIPTION => "Operation to perform [".self::MODE_PROCESS."|".self::MODE_BALANCE_DISTRIBUTION."|".self::MODE_EVENT_ELIGIBILITY."]",
				self::ARG_VALIDATION => 'Cli::_validInArray("%1$s", array("'.self::MODE_PROCESS.'","'.self::MODE_BALANCE_DISTRIBUTION.'", "'.self::MODE_EVENT_ELIGIBILITY.'"))'
			),
			self::SWITCH_REDISTRIBUTE_FULL => array(
				self::ARG_REQUIRED => FALSE,
				self::ARG_DESCRIPTION => "Redistribution on all accounts that are not archived.",
				self::ARG_DEFAULT => FALSE,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			),
			self::SWITCH_REDISTRIBUTE_FULL_INCLUDE_ARCHIVED => array(
				self::ARG_REQUIRED => FALSE,
				self::ARG_DESCRIPTION => "Redistribution on all accounts.",
				self::ARG_DEFAULT => FALSE,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			),
			self::SWITCH_ACCOUNT_ID => array(
				self::ARG_REQUIRED => false,
				self::ARG_LABEL => "ACCOUNT_ID",
				self::ARG_DESCRIPTION => "Account Id",
				self::ARG_VALIDATION => 'Cli::_validInteger("%1$s")'
			),
			self::SWITCH_EVENT_IDENTIFIER => array(
				self::ARG_REQUIRED => false,
				self::ARG_LABEL => "EVENT_IDENTIFIER",
				self::ARG_DESCRIPTION => 'Event Identifier (EVENT_ELIGIBILITY mode only).  If supplied, must be a Collection Event Id or "'.self::EVENT_IDENTIFIER_DIRECTDEBIT.'".',
				self::ARG_DEFAULT => null,
				self::ARG_VALIDATION => 'Cli::_validString("%1$s")'
			),
			self::SWITCH_EFFECTIVE_DATE	=> array(
				self::ARG_REQUIRED => false,
				self::ARG_LABEL => "EFFECTIVE_DATE",
				self::ARG_DESCRIPTION => 'Effective Date (EVENT_ELIGIBILITY mode only).',
				self::ARG_DEFAULT => null,
				self::ARG_VALIDATION => 'Cli::_validString("%1$s")'
			),
			self::SWITCH_NEXT_DATE => array(
				self::ARG_REQUIRED => FALSE,
				self::ARG_DESCRIPTION => "Predict next valid date in EVENT_ELIGIBILITY mode",
				self::ARG_DEFAULT => FALSE,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			)
		);
	}
}