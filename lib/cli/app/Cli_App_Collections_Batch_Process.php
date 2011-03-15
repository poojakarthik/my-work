<?php


class Cli_App_Collections_Batch_Process extends Cli
{
	const SWITCH_TEST_RUN = "t";

        const SWITCH_ACCOUNT_ID = 'a';

	function run()
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
			
			try 
			{
                            $oDataAccess = DataAccess::getDataAccess();
                            $oDataAccess->TransactionStart();
                          

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
				$oDataAccess	= DataAccess::getDataAccess();
				$oDataAccess->TransactionStart();
				Logic_Collection_Promise::batchProcess($aPromises);
				$oDataAccess->TransactionCommit();
                            }
                            catch (Exception $e)
                            {
                                 Logic_Collection_BatchProcess_Report::addException($e);
				 Log::getLog($e->__toString());
				 $oDataAccess->TransactionRollback();
                                if ($e instanceof Exception_Database)
                                {
                                    throw $e;
                                }
				Log::getLog()->log($e->__toString());
                            }

                            try
                            {
				$oDataAccess = DataAccess::getDataAccess();
				$oDataAccess->TransactionStart();
                                Logic_Collection_Suspension::batchProcess($aActiveSuspensions);
				$oDataAccess->TransactionCommit();
                            }
                            catch(Exception $e)
                            {
				$oDataAccess->TransactionRollback();
				Logic_Collection_BatchProcess_Report::addException($e);

				if ($e instanceof Exception_Database)
                                {
                                    throw $e;
                                }
				Log::getLog()->log($e->__toString());
                            }

                            try
                            {
				$oDataAccess	= DataAccess::getDataAccess();
				$oDataAccess->TransactionStart();
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
                                do
                                {                                    
                                    Log::getLog()->log("About to process ".count($aAccounts)." In Batch.");
				    $iCompletedInstances = Logic_Account::batchProcessCollections($aAccounts);
				     Log::getlog()->log("Completed Events In : ".Logic_Stopwatch::getInstance()->lap());
				    Log::getLog()->log("-------End Account Batch Collections Process Iteration-------------------------");
                                }
				while ($iCompletedInstances > 0);

				$oDataAccess->TransactionCommit();
				Log::getLog()->log("Total Time: ".Logic_Stopwatch::getInstance()->split());
                            }
                            catch (Exception $e)
                            {
				$oDataAccess->TransactionRollback();
				if ($e instanceof Exception_Database)
                                {
				    Logic_Collection_BatchProcess_Report::addException($e);
				    throw $e;
                                }
				Log::getLog()->log("Hello: ".$e->__toString());
				Logic_Collection_BatchProcess_Report::addException($e);
                            }

                            

                            if ($arrArgs[self::SWITCH_TEST_RUN])
                            {
                                $oDataAccess->TransactionRollback();
                            }
                            else
                            {
                                $oDataAccess->TransactionCommit();
                            }

			    $sPath = FILES_BASE_PATH.'temp/';
			    $bPathExists = file_exists ($sPath);
			    if (!$bPathExists)
			    {
				$bPathExists = mkdir ($sPath , 0777 , true);
			    }

			    Logic_Collection_BatchProcess_Report::emailReport();
			}
			catch (Exception $e)
			{
                             $oDataAccess->TransactionRollback();
                             Log::getLog()->log($e->__toString());
			     Logic_Collection_BatchProcess_Report::addException($e);
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
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "for testing script outcome - database changes will be rolled back",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
                        self::SWITCH_ACCOUNT_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "ACCOUNT_ID",
				self::ARG_DESCRIPTION	=> "Account Id",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),
		
		);
	}
}


?>
