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
	const	SWITCH_TEST_RUN				= 't';
	const	SWITCH_MODE					= 'm';
	const	SWITCH_CARRIER_ID			= 'c';
	const	SWITCH_CLASS_NAME			= 'n';
	const	SWITCH_CUSTOMER_GROUP_ID	= 'g';
	
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
			echo "\n".$oException."\n";
			return 1;
		}
	}
	
	protected function _createCarrierModule()
	{
		$iCarrierId			= $this->_arrArgs[self::SWITCH_CARRIER_ID];
		$iCustomerGroupId	= $this->_arrArgs[self::SWITCH_CUSTOMER_GROUP_ID];
		$sClassName			= $this->_arrArgs[self::SWITCH_CLASS_NAME];
		
		if (!is_subclass_of($sClassName, 'Resource_Type_Base'))
		{
			throw new Exception("Class '{$sClassName}' does not extends Resource_Type_Base");
		}
		
		Log::getLog()->log("Creating Carrier Module for Carrier '{$iCarrierId}' with Class '{$sClassName}'");
		Callback::create('createCarrierModule', $sClassName)->invoke($iCarrierId, $iCustomerGroupId);
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
				self::ARG_LABEL			=> "MODE",
				self::ARG_DESCRIPTION	=> "Carrier Module operation to perform [CREATE]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("CREATE"))'
			),
			
			self::SWITCH_CARRIER_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "CARRIER_ID",
				self::ARG_DESCRIPTION	=> "Carrier Id",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),
			
			self::SWITCH_CLASS_NAME => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "MODULE_CLASS_NAME",
				self::ARG_DESCRIPTION	=> "Carrier Id",
				self::ARG_VALIDATION	=> 'Cli::_validClassName("%1$s")'
			),

			self::SWITCH_CUSTOMER_GROUP_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DEFAULT		=> null,
				self::ARG_LABEL			=> "CUSTOMER_GROUP_ID",
				self::ARG_DESCRIPTION	=> "Customer Group Id",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			)
		);
	}
}
?>