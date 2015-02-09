<?php

// Load Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

LoadApplication();

CliEcho("\n[ CREATE NORMALISATION CARRIER MODULE ]\n");

if (is_int(stripos($argv[1], '?')))
{
	// Print Help
	CliEcho("** Usage: 'php create_carrier_module.php <Carrier.Id> <ClassName>'\n");
	CliEcho("<Carrier.Id>\t: The Id for the Carrier this module is to be created for");
	CliEcho("<ClassName>\t: The Class of the module to create");
	CliEcho();
	die;
}

$intCarrier		= (int)$argv[1];
$strClassName	= trim($argv[2]);

if (!Carrier::getForId($intCarrier, true))
{
	CliEcho("ERROR: '$intCarrier' is not a valid Carrier Id!\n");
	die;
}
elseif (!class_exists($strClassName) || !is_subclass_of($strClassName, 'NormalisationModule'))
{
	CliEcho("ERROR: '$strClassName' is not a valid NormalisationModule Class!\n");
	die;
}

CliEcho("Creating new Module...\t\t\t", FALSE);


if (DataAccess::getDataAccess()->TransactionStart())
{
	try
	{
		$oModuleReflection	= (new ReflectionClass($strClassName));

		// Define the new Carrier Module record
		$oCarrierModule	= new Carrier_Module();

		$oCarrierModule->Carrier			= Carrier::getForId($intCarrier)->Id;
		$oCarrierModule->customer_group		= null;									// Currently, we have no need for Customer Group-level Normalisation Modules
		$oCarrierModule->Type				= MODULE_TYPE_NORMALISATION_CDR;
		$oCarrierModule->Module				= $strClassName;
		$oCarrierModule->FileType			= $oModuleReflection->getConstant('RESOURCE_TYPE');
		$oCarrierModule->FrequencyType		= 1;
		$oCarrierModule->Frequency			= 1;
		$oCarrierModule->LastSentOn			= Data_Source_Time::START_OF_TIME;
		$oCarrierModule->EarliestDelivery	= 0;
		$oCarrierModule->Active				= 0;	// Inactive by default

		$oCarrierModule->save();

		// Define the Carrier Module Configuration records
		if (method_exists ($strClassName, "getConfigDefinition" )) {
			$oCarrierModule->getConfig()->define(call_user_func(array($strClassName, 'getConfigDefinition')));
			$oCarrierModule->getConfig()->save();
		}


		//throw new Exception("DEBUG");
		DataAccess::getDataAccess()->TransactionCommit();

		CliEcho("[   OK   ]\n");
	}
	catch (Exception $oException)
	{
		CliEcho("[ FAILED ]\n\t[!] ".$oException->getMessage());
		DataAccess::getDataAccess()->TransactionRollback();

		throw $oException;
	}
}

?>