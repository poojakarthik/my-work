<?php


class Cli_App_Rollout extends Cli
{

	function run()
	{
		try
		{
			$this->log("Starting.");

			// Include the Rollout handler class 
			$this->requireOnce('lib/rollout/Flex_Rollout.php');

			Flex_Rollout::updateToLatestVersion();

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

}


?>
