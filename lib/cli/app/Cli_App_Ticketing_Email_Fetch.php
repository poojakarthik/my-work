<?php

require_once dirname(__FILE__) . '/' . '../../classes/Flex.php';
Flex::load();

class Cli_App_Ticketing_Email_Fetch extends Cli
{
	function run()
	{
		$arrSummary = array();

		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			// Load the ticketing configuration
			Ticketing_Service::loadEmails();
		}
		catch(Exception $exception)
		{
			$this->showUsage("ERROR: " . $exception->getMessage());
		}
	}

	function getCommandLineArguments()
	{
		return array(

		);
	}
}