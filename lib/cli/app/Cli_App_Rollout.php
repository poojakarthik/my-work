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
			// If there is an error, we need to rollback the database

			// We should also give each script a chance to rollback any non-db changes made,
			// altough 99 times out of a hundred there won't be any.
			// This should be done in the reverse order to which the rollout's were applied

			// We can now show the error message
			$this->showUsage($exception->getMessage());
			return 1;
		}
	}

}


?>
