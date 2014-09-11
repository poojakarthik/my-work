<?php

// Load Framework
require_once("../../flex.require.php");
LoadApplication();

CliEcho("\n[ CREATE DIRECT DEBIT CARRIER MODULE ]\n");

if (is_int(stripos($argv[1], '?')))
{
	// Print Help
	CliEcho("** Usage: 'php create_directdebit_module.php <Carrier.Id> <CustomerGroup.Id> <ClassName>'\n");
	CliEcho("<Carrier.Id>\t: The Id for the Carrier this module is to be created for");
	CliEcho("<ClassName>\t: The Class of the module to create");
	CliEcho();
	die;
}

$intCarrier			= (int)$argv[1];
$intCustomerGroup	= (int)$argv[2];
$strClassName		= trim($argv[3]);

if (!Carrier::getForId($intCarrier, true))
{
	CliEcho("ERROR: '$intCarrier' is not a valid Carrier Id!\n");
	die;
}
elseif (!GetConstantName($intCustomerGroup, 'CustomerGroup'))
{
	CliEcho("ERROR: '$intCustomerGroup' is not a valid CustomerGroup!\n");
	die;
}
elseif (!class_exists($strClassName) || !is_subclass_of($strClassName, 'CarrierModule'))
{
	CliEcho("ERROR: '$strClassName' is not a valid CarrierModule Class!\n");
	die;
}

CliEcho("Creating new Module...\t\t\t", FALSE);
$objModule	= new $strClassName($intCarrier, $intCustomerGroup);

if (!$objModule)
{
	CliEcho("[ FAILED ]\n\tERROR: There was an error instanciating the CarrierModule Class '$strClassName'\n");
	die;
}

if (($mixResult = $objModule->CreateModuleConfig()) !== TRUE)
{
	CliEcho("[ FAILED ]\n\t".$mixResult."\n");
	die;
}

CliEcho("[   OK   ]\n");
?>