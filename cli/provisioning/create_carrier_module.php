<?php

// Load Framework
require_once("../../flex.require.php");
LoadApplication();

CliEcho("\n[ CREATE CARRIER MODULE ]\n");

if (is_int(stripos($argv[1], '?')))
{
	// Print Help
	CliEcho("** Usage: 'php create_carrier_module <Carrier.Id> <ClassName>'\n");
	CliEcho("<Carrier.Id>\t: The Id for the Carrier this module is to be created for");
	CliEcho("<ClassName>\t: The Class of the module to create");
	CliEcho('');
	die;
}

$intCarrier		= (int)$argv[1];
$strClassName	= trim($argv[2]);

if (!GetConstantName($intCarrier, 'Carrier'))
{
	CliEcho("ERROR: '$intCarrier' is not a valid Carrier Id!\n");
	die;
}
elseif (!class_exists($strClassName))
{
	CliEcho("ERROR: '$strClassName' is not a valid Class!\n");
	die;
}

CliEcho("Creating new Module...\t\t\t", FALSE);
@$objModule	= new $strClassName($intCarrier);

if (!$objModule || !is_subclass_of('CarrierModule') || !is_subclass_of('ExportBase'))
{
	CliEcho("[ FAILED ]\n\tERROR: '$strClassName' is not a CarrierModule class!\n");
	die;
}

if (($mixResult = $objModule->CreateModuleConfig()) !== TRUE)
{
	CliEcho("[ FAILED ]\n\t".$mixResult."\n");
	die;
}

CliEcho("[   OK   ]\n");
?>