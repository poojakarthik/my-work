<?php
/**
 * Cli_App_Correspondence
 *
 * @parent	Cli
 */
class Cli_App_Correspondence extends Cli
{
	const	SWITCH_TEST_RUN			= 't';
	const	SWITCH_MODE				= 'm';
	
	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$this->_arrArgs = $this->getValidatedArguments();
			
			switch ($this->_arrArgs[self::SWITCH_MODE])
			{
				case 'DISPATCH':
					Correspondence_Dispatcher::sendWaitingRuns();
					break;
				
				default:
					throw new Exception("Unknown Mode '{$this->_arrArgs[self::SWITCH_MODE]}'");
					break;
			}
		}
		catch (Exception $oException)
		{
			echo "\n".$oException."\n";
			return 1;
		}
	}
	
	function getCommandLineArguments()
	{
		return array(
			/*self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "No changes to the database.",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),*/
			
			self::SWITCH_MODE => array(
				self::ARG_REQUIRED		=> TRUE,
				self::ARG_LABEL			=> "MODE",
				self::ARG_DESCRIPTION	=> "Correspondence operation to perform [DISPATCH]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("DISPATCH"))'
			)
		);
	}
}
?>