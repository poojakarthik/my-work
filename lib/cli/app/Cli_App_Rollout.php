<?


class Cli_App_Rollout extends Cli
{

	function run()
	{
		try
		{
			// Include the Rollout handler class 
			$this->requireOnce('lib/rollout/Flex_Rollout.php');

			Flex_Rollout::updateFromVersion();

			// We need to connect to the database and check that the 'DatabaseSchemaVersion' table
			// exists.


			// Next we need to select record (if there is one) from that table

			// We then need to list each of the rollout scripts from /lib/rollout/update IN ORDER 

			// For each script that is BEFORE the current db version, we need to call the rollout() function 

			// We now need to update the 'DatabaseSchemaVersion' table with the last update applied

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
