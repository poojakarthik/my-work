<?php

$_SESSION = array();
// Load Flex.php
require_once(dirname(__FILE__) . "/../../classes/Flex.php");
Flex::load();


class Cli_App_Model_Data_Source extends Cli
{
	const SWITCH_TEST_RUN = "t";
	const SWITCH_DATA_SOURCE = "d";

	function run()
	{
		try
		{
			$this->log("Starting.");
			
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode.", TRUE);
			}
			
			$datasource = $arrArgs[self::SWITCH_DATA_SOURCE];
			
			$this->log("Trying to model $datasource.");

			$this->modelDB($datasource);

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
	
	function modelDB($strDataSourceName)
	{
		$modeler = DO_Modeler::getModelerForDataSource($strDataSourceName);
		$modeler->load();
		$modeler->prepare();
		$modeler->generateModelFiles(dirname(__FILE__) . '/../../classes/');
	}

	function getCommandLineArguments()
	{
		return array(

			self::SWITCH_DATA_SOURCE => array(
				self::ARG_LABEL			=> "DATA_SOURCE_NAME", 
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "the name of the data source to model",
				self::ARG_DEFAULT		=> "sales",
				self::ARG_VALIDATION	=> 'Cli::_validString("%1$s")'
			),
		
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
