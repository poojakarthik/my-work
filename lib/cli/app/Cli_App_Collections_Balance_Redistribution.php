<?php


class Cli_App_Collections_Balance_Redistribution extends Cli
{
	const SWITCH_TEST_RUN = "t";
        const SWITCH_REDISTRIBUTE_FULL = "f";
        const SWITCH_REDISTRIBUTE_FULL_INCLUDE_ARCHIVED = "r";
        const SWITCH_ACCOUNT_ID = "a";

	function run()
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
			
			try 
			{
			    $oDataAccess = DataAccess::getDataAccess();
			    $oDataAccess->TransactionStart();
			    Log::getLog()->log("Account, Time, Memory Usage, delete linking data, reset balances, iterations, Debit Collectables, Credit Collectables, Credit Payments, Credit Adjustments, Debit Payments,Debit Adjustments");

			    $aAccounts = Account::getForBalanceRedistribution($iMode);
			    Logic_Account::batchProcessBalanceRedistribution($aAccounts);
			    Log::getLog()->log("After, ".memory_get_usage (TRUE ));//self::$aMemory['after_before_cache_clear'] = memory_get_usage (TRUE );
			    if ($arrArgs[self::SWITCH_TEST_RUN])
			    {
				$oDataAccess->TransactionRollback();
			    }
			    else
			    {
				$oDataAccess->TransactionCommit();
			    }
			}
			catch (Exception $e)
			{
                             $oDataAccess->TransactionRollback();
                             Log::getLog()->log($e->__toString());
                             
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
			)
		);
	}
}


?>
