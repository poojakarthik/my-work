<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// generator_customer
//----------------------------------------------------------------------------//
/**
 * generator_customer
 *
 * Generates customer data
 *
 * Generates customer data for use in demo system
 * 
 * @file		generator_customer.php
 * @language	PHP
 * @package		setup_scripts
 * @author		Rich 'Waste' Davis
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

require_once("../../flex.require.php");

// Statements
$selAccountGroups	= new StatementSelect("AccountGroup", "Id", "Archived = 0");
$insAccountGroup	= new StatementInsert("AccountGroup", NULL, TRUE);
$insAccount			= new StatementInsert("Account");
$insContact			= new StatementInsert("Contact");

$arrColumns = Array();
$arrColumns['AccountGroup']		= NULL;
$arrColumns['PrimaryContact']	= NULL;
$ubiAccount			= new StatementUpdateById("Account", $arrColumns);

$arrColumns = Array();
$arrColumns['Account']			= NULL;
$ubiContact			= new StatementUpdateById("Contact", $arrColumns);



// Read in Names
$arrFiles = Array('surname.csv', 'male.csv', 'female.csv', 'suburb.csv', 'business.csv', 'street.csv', 'jobtitle.csv');
$arrNames = Array();
foreach ($arrFiles as $strFile)
{
	$ptrFile = fopen($strFile, 'r');
	while (!feof($ptrFile))
	{
		$arrNames[basename($strFile, '.csv')][] = rtrim(ucwords(strtolower(fgets($ptrFile))));
	}
	fclose($ptrFile);
}

// Randomly choose number of customers to generate
$intCustomers = rand(50, 100);

for ($i = 0; $i < $intCustomers; $i++)
{
	$arrAccount = DataAccess::getDataAccess()->FetchClean('Account');
	$arrContact	= DataAccess::getDataAccess()->FetchClean('Contact');
	
	// Generate names, etc
	switch (rand(0, 1))
	{
		case 1:
			$strSex = 'female';
			$arrContact['Title']	= "Mrs";
			break;
		default:
			$strSex = 'male';
			$arrContact['Title']	= "Mr";
			break;
	}
	$arrContact['FirstName']		= $arrNames[$strSex][rand(0, count($arrNames[$strSex])-1)];
	$arrContact['LastName']			= $arrNames['surname'][rand(0, count($arrNames['surname'])-1)];
	$arrContact['DOB']				= date("Y-m-d", rand(0, time()));
	$intAccountType					= rand(0, 1);
	$intPostCode					= rand(0, 999);
	switch(rand(0, 7))
	{
		case 0:
			$arrAccount['State']	= "NSW";
			$arrContact['Phone']	= "02";
			$intPostCode			+= 1000;
			break;
		case 1:
			$arrAccount['State']	= "VIC";
			$arrContact['Phone']	= "03";
			$intPostCode			+= 3000;
			break;
		case 2:
			$arrAccount['State']	= "ACT";
			$arrContact['Phone']	= "02";
			$intPostCode			+= 2000;
			break;
		case 3:
			$arrAccount['State']	= "SA";
			$arrContact['Phone']	= "08";
			$intPostCode			+= 5000;
			break;
		case 4:
			$arrAccount['State']	= "NT";
			$arrContact['Phone']	= "08";
			break;
		case 5:
			$arrAccount['State']	= "WA";
			$arrContact['Phone']	= "08";
			$intPostCode			+= 6000;
			break;
		case 6:
			$arrAccount['State']	= "TAS";
			$arrContact['Phone']	= "03";
			$intPostCode			+= 7000;
			break;
		case 7:
			$arrAccount['State']	= "QLD";
			$arrContact['Phone']	= "07";
			$intPostCode			+= 4000;
			break;		
	}
	$arrAccount['Postcode']	= str_pad($intPostCode, 4, STR_PAD_LEFT);
	$arrAccount['Suburb']	= trim($arrNames['surname'][rand(0, count($arrNames['surname'])-1)] . " " . $arrNames['suburb'][rand(0, count($arrNames['suburb'])-1)]);
	if (rand(0, 1))
	{
		$arrAccount['Address1']	= "Unit " . rand(0, 200);
		$arrAccount['Address2']	= rand(0, 999) . " " . $arrNames['surname'][rand(0, count($arrNames['surname'])-1)] . " " . $arrNames['street'][rand(0, count($arrNames['street'])-1)];
	}
	else
	{
		$arrAccount['Address1']	= rand(0, 999) . " " . $arrNames['surname'][rand(0, count($arrNames['surname'])-1)] . " " . $arrNames['street'][rand(0, count($arrNames['street'])-1)];;
		$arrAccount['Address2']	= "";
	}
	$arrAccount['Country']	= "AU";
	if ($intAccountType)
	{
		// Residential
		$arrAccount['BusinessName']	= "{$arrContact['Title']} {$arrContact['FirstName']} {$arrContact['LastName']}";
		$arrAccount['ABN']			= "";
		$arrAccount['ACN']			= "";
		$arrContact['JobTitle']		= "";
	}
	else
	{
		// Generate Company Name & ABN
		$arrAccount['BusinessName']	=  trim($arrNames['surname'][rand(0, count($arrNames['surname'])-1)] . " " . $arrNames['business'][rand(0, count($arrNames['business'])-1)]);
		$arrAccount['ABN']			= GenerateABN();
		$arrAccount['ACN']			= substr($arrAccount['ABN'], 2);
		$arrContact['JobTitle']		= $arrNames['jobtitle'][rand(0, count($arrNames['jobtitle'])-1)];
	}
	
	if (rand(0, 20) == 16 && $selAccountGroups->Execute() > 0)
	{
		// Choose an AccountGroup
		$arrAccountGroups = $selAccountGroups->FetchAll();
		$intAG = rand(1, count($arrAccountGroups));
		$arrAccount['AccountGroup']	= $arrAccountGroups[$intAG]['Id'];
		$arrContact['AccountGroup']	= $arrAccountGroups[$intAG]['Id'];
		$arrAccountGroup = $arrAccountGroups[$intAG];
	}
	else
	{
		// Create an AccountGroup
		$arrAccountGroup = Array();
		$arrAccountGroup['Id']			= $arrAccount['Id'];
		$arrAccountGroup['CreatedBy']	= 1;
		$arrAccountGroup['CreatedOn']	= date("Y-m-d");
		$arrAccountGroup['Archived']	= 0;
		$intAccountGroup = $insAccountGroup->Execute($arrAccountGroup);
		
		$arrAccount['AccountGroup']	= $intAccountGroup;
		$arrContact['AccountGroup']	= $intAccountGroup;
	}
	
	// Add account
	$arrAccount['CustomerGroup']		= rand(1, 3);
	$arrAccount['DisableDDR']			= rand(0, 1);
	$arrAccount['DisableLatePayment']	= rand(-5, 1);
	$arrAccount['PaymentTerms']			= 14;
	$arrAccount['BillingMethod']		= (rand(0, 5) == 5) ? 1 : 0;
	$arrAccount['BillingDate']			= 1;
	$arrAccount['BillingFreq']			= 1;
	$arrAccount['BillingFreqType']		= 2;
	$arrAccount['CreatedBy']			= 1;
	$arrAccount['BillingType']			= 3;
	$arrAccount['CreatedOn']			= date("Y-m-d");
	$arrAccount['Archived']				= 0;
	$arrContact['Account']				= $arrAccount['Id'] = $insAccount->Execute($arrAccount);
	
	// Add contact
	$arrContact['CustomerContact']	= 1;
	switch (rand(0, 3))
	{
		case 0:
			$arrContact['Phone']			.= rand(30000000, 99999999);
			$arrContact['Mobile']			= "";
			$arrContact['Fax']				= "";
			break;
		case 1:
			$arrContact['Phone']			= "";
			$arrContact['Mobile']			= "04".rand(0, 99999999);
			$arrContact['Fax']				= "";
			break;
		case 2:
			$arrContact['Phone']			.= rand(30000000, 99999999);
			$arrContact['Mobile']			= "04".rand(0, 99999999);
			$arrContact['Fax']				= "";
			break;
		case 3:
			$arrContact['Phone']			.= rand(30000000, 99999999);
			$arrContact['Mobile']			= "04".rand(0, 99999999);
			$arrContact['Fax']				= substr($arrContact['Phone'], 0, 6) . rand(0, 9999);
			break;
	}
	$arrContact['UserName']			= "";	// AccountGroup # or Account #?
	$arrContact['PassWord']			= sha1(strtolower($arrContact['FirstName'])); // AccountGroup # or Account #?
	$arrContact['Email']			= strtolower($arrContact['FirstName'] . '@' . str_replace(' ', '', $arrAccount['BusinessName']) . '.com.au');
	$arrContact['Archived']			= 0;
	$arrContact['Id']				= $arrAccount['PrimaryContact'] = $insContact->Execute($arrContact);
	
	// Update Account with Primary Contact
	$ubiAccount->Execute($arrAccount);
	
	//Debug(Array('AccountGroup' => $arrAccountGroup, 'Account' => $arrAccount, 'Contact' => $arrContact));
	CliEcho("Added Customer: {$arrAccount['BusinessName']}");
}
die;








// Generates a valid ABN via Brute Force
function GenerateABN()
{
	$arrWeightingFactor	= Array(10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19);
	do
	{
		$intABN		= rand(10000000000, 99999999999);
		$strCheck	= (string)($intABN - 10000000000);
		
		$arrCompare	= Array();
		foreach ($arrWeightingFactor as $intKey=>$intWeight)
		{
			$arrCompare[$intKey] = $strCheck[$intKey] * $arrWeightingFactor[$intKey];
		}
		$intTotal	= array_sum($arrCompare);
	}
	while ($intTotal % 89 !== 0);
	
	return (string)$intABN;
}

?>
