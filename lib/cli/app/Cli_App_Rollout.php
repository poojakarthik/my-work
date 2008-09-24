<?php


class Cli_App_Rollout extends Cli
{
	const SWITCH_TEST_RUN = "t";

	function run()
	{
		try
		{
			$this->log("Starting.");

			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode. All changes will be rolled back.", TRUE);
			}
			
			try 
			{
				$this->requireOnce('lib/rollout/Flex_Rollout_Incremental.php');
	
				Flex_Rollout_Incremental::updateToLatestVersion(NULL, $arrArgs[self::SWITCH_TEST_RUN]);
			}
			catch (NonIncrementalRolloutException $ex)
			{
				// Include the Rollout handler class 
				$this->requireOnce('lib/rollout/Flex_Rollout.php');
	
				Flex_Rollout::updateToLatestVersion(NULL, $arrArgs[self::SWITCH_TEST_RUN]);
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
				self::ARG_DESCRIPTION	=> "for testing script outcome [performs full rollout and rollback (i.e. there should be no change)]",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
		
		);
	}
}


?>
