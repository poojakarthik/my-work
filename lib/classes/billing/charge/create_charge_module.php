<?php

// Framework
require_once("../../Flex.php");

CliEcho("\n[ CREATE CHARGE MODULE ]\n");

// Command Line Parameters
$strClass			= $argv[1];
$strCustomerGroup	= $argv[2];
$bolValidParameters	= TRUE;
if (!SubclassOf($strClass, 'Billing_Charge'))
{
	$bolValidParameters	= FALSE;
	CliEcho("'$strClass' does not inherit from Billing_Charge!");
}

if (strtoupper($strCustomerGroup) === 'ALL')
{
	$intCustomerGroup				= NULL;
	$strCustomerGroupDescription	= "All/Default";
} 
elseif (GetConstantDescription((int)$strCustomerGroup, 'CustomerGroup'))
{
	$intCustomerGroup				= (int)$strCustomerGroup;
	$strCustomerGroupDescription	= GetConstantDescription($intCustomerGroup, 'CustomerGroup');
}
else
{
	$bolValidParameters	= FALSE;
	CliEcho("'$strCustomerGroup' is not a valid Customer Group (nor the 'ALL' wildcard)!");
}

// On error, print out usage
if ($bolValidParameters === FALSE)
{
	CliEcho();
	CliEcho("USAGE: 'php create_charge_module [ClassName] [CustomerGroup]'");
	CliEcho("\tClassName\t: Billing_Charge subclass in implement");
	CliEcho("\tCustomerGroup\t: CustomerGroup.Id or '*' to apply to all CustomerGroups (can be overridden by CustomerGroup-specific modules)");
	CliEcho();
	exit(1);
}

CliEcho("CLASS\t\t: {$strClass}");
CliEcho("CUSTOMER GROUP\t: {$strCustomerGroupDescription}");

// Create Module
// HACKHACKHACK: Need to do this, because PHP 5.2 doesn't support Late Static Binding
$intInsertId	= NULL;
switch ($strClass)
{
	case 'Billing_Charge_Account_AccountProcessing':
		$intInsertId	= Billing_Charge_Account_AccountProcessing::CreateModule($intCustomerGroup);
		break;
		
	case 'Billing_Charge_Account_Postage':
		$intInsertId	= Billing_Charge_Account_Postage::CreateModule($intCustomerGroup);
		break;
		
	case 'Billing_Charge_Service_Inbound':
		$intInsertId	= Billing_Charge_Service_Inbound::CreateModule($intCustomerGroup);
		break;
		
	case 'Billing_Charge_Service_Pinnacle':
		$intInsertId	= Billing_Charge_Service_Pinnacle::CreateModule($intCustomerGroup);
		break;
}

CliEcho("Module Created with Id '{$intInsertId}'\n");

exit(0);

// SubclassOf: Determines if $strChild inherits from $strParent
function SubclassOf($strChild, $strParent)
{
	$strGetParent	= get_parent_class($strChild);
	if ($strGetParent)
	{
		if ($strGetParent === $strParent)
		{
			return TRUE;
		}
		else
		{
			return SubclassOf($strGetParent, $strParent);
		}
	}
	else
	{
		FALSE;
	}
}
?>