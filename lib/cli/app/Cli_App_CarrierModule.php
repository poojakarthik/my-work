<?php
/**
 * Cli_App_CarrierModule
 *
 * Application to
 *
 * @class	Cli_App_Motorpass
 * @parent	Cli
 */
class Cli_App_CarrierModule extends Cli
{
	const	SWITCH_TEST_RUN		= 't';
	const	SWITCH_MODE			= 'm';
	const	SWITCH_CARRIER_ID	= 'c';
	const	SWITCH_CLASS_NAME	= 'n';
	
	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$this->_arrArgs = $this->getValidatedArguments();
			
			if ($this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode.  No files will be sent or imported.", true);
			}
			
			switch ($this->_arrArgs[self::SWITCH_MODE])
			{
				case 'CREATE':
					$this->_createCarrierModule();
					break;
			}
		}
		catch (Exception $oException)
		{
			return 1;
		}
	}
	
	protected function _createCarrierModule()
	{
		$iCarrierId	= $this->_arrArgs[self::SWITCH_CARRIER_ID];
		$sClassName	= $this->_arrArgs[self::SWITCH_CLASS_NAME];
		
		if (!is_subclass_of($sClassName, Resource_Type_Base))
		{
			throw new Exception("'{$sClassName}' does not extends Resource_Type_Base");
		}
		
		Callback::create('createCarrierModule', $sClassName)->invoke($iCarrierId);
	}
	
	function getCommandLineArguments()
	{
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "No changes to the database and files will not be sent.",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_MODE => array(
				self::ARG_REQUIRED		=> TRUE,
				self::ARG_DESCRIPTION	=> "Carrier Module operation to perform [CREATE]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("CREATE"))'
			),
			
			self::SWITCH_CARRIER_ID => array(
				self::ARG_REQUIRED		=> true,
				self::ARG_LABEL			=> "CARRIER_ID",
				self::ARG_DESCRIPTION	=> "Carrier Id",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),
			
			self::SWITCH_CLASS_NAME => array(
				self::ARG_REQUIRED		=> true,
				self::ARG_LABEL			=> "MODULE_CLASS_NAME",
				self::ARG_DESCRIPTION	=> "Carrier Id",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			)
		);
	}
}
?>