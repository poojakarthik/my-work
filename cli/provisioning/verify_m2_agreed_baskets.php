<?php

define('M2_AGREED_BASKETS_REGEX_HEADER',	"^(?<RecordType>H)(?<FileName>(?<WholesaleProvider>[a-z]{3})(?<SPCode>\d{3})(?<FileType>a)(?<Date>(?<Year>\d{4})(?<Month>\d{2})(?<Day>\d{2}))(?<FileExtension>\.txt))$");
define('M2_AGREED_BASKETS_REGEX_CONTENT',	"^(?<RecordType>D)(?<Sequence>\d{8})(?<ServiceFNN>\d{6,29})(\ *)(?<Basket>00[1-6])(?<Nature>[CS])(?<Date>(?<Year>\d{4})(?<Month>\d{2})(?<Day>\d{2})).*$");
define('M2_AGREED_BASKETS_REGEX_FOOTER',	"^(?<RecordType>T)(?<RecordCount>\d{8})$");

// Framework
require_once(dirname(__FILE__).'/../lib/classes/Flex.php');
Flex::load();

// Read Command Line Arguments
$sImportPath	= $argv[1];
$sExportPath	= $argv[2];

Log::getLog()->log("Opening Import File from path '{$sImportPath}'...");

// Validate Import Path
if (!@is_readable($sImportPath))
{
	throw new Exception("Import Path '{$sImportPath}' is not readable");
}
elseif (!($rImportFile = @fopen($sImportPath, 'r')))
{
	throw new Exception("Unable to open Import Path '{$sImportPath}' for reading");
}

Log::getLog()->log("Validating Export path path '{$sExportPath}'...");

// Validate Export Path
if (!@is_writable($sExportPath))
{
	throw new Exception("Export Path '{$sExportPath}' is not writable");
}
$oExportCSV	= new File_CSV();
$oExportCSV->setColumns(array(
								'Service Number',
								'Flex Status',
								'Customer Group'
							));

$aCustomerGroups	= array();
$oQuery				= new Query();

Log::getLog()->log("Parsing Import File...");

// PARSE THE IMPORT FILE
$sFileEffectiveDate	= null;
$iLineNumber		= 0;
while ($sLine = fgets($rImportFile))
{
	$iLineNumber++;
	
	$aTokens	= array();
	if (preg_match_all(M2_AGREED_BASKETS_REGEX_HEADER, $sLine, $aTokens, PREG_SET_ORDER))
	{
		// Header
		$sFileEffectiveDate	= "{$aTokens[0]['Year']}-{$aTokens[0]['Month']}-{$aTokens[0]['Day']}";
		
		Log::getLog()->log("[H] Header Found... File Date is '{$sFileEffectiveDate}'");
	}
	elseif (preg_match_all(M2_AGREED_BASKETS_REGEX_FOOTER, $sLine, $aTokens, PREG_SET_ORDER))
	{
		// Footer
		Log::getLog()->log("[F] Footer Found...");
	}
	elseif (preg_match_all(M2_AGREED_BASKETS_REGEX_CONTENT, $sLine, $aTokens, PREG_SET_ORDER))
	{
		if ($sFileEffectiveDate === null)
		{
			throw new Exception("Found Data/Content Row before Header!");
		}
		
		// Data/Content
		$sFNN				= $aTokens[0]['ServiceFNN'];
		
		Log::getLog()->log("[+] Data Row Found... FNN: {$sFNN}");
		
		$sProvisioningDate	= "{$aTokens[0]['Year']}-{$aTokens[0]['Month']}-{$aTokens[0]['Day']}";
		
		// Check against Flex DB
		$aFNNOwner	= FindFNNOwner($sFNN, date("Y-m-d H:i:s", strtotime($sFileEffectiveDate) - 1));
		if (!$aFNNOwner)
		{
			// Couldn't find an Owner File Effective Date
			$aFNNInstances	= Service::getFNNInstances($sFNN);
			
			if (count($aFNNInstances))
			{
				// Get Service Instance before to the File Date (ignoring ones after it)
				array_reverse($aFNNInstances);
				foreach ($aFNNInstances as $aFNNInstance)
				{
					if ($aFNNInstance['CreatedOn'] < $sFileEffectiveDate)
					{
						// Use this Instance
						$aFNNOwner	=	array
										(
											'Account'		=> $aFNNInstance['Account'],
											'AccountGroup'	=> $aFNNInstance['AccountGroup'],
											'Service'		=> $aFNNInstance['Id']
										);
						break;
					}
				}
			}
		}
		
		// Could we find any kind of Owner?
		if ($aFNNOwner)
		{
			$oAccount	= new Account(array('Id'=>$aFNNOwner['Account']), false, true);
			
			if (!array_key_exists($oAccount->CustomerGroup, $aCustomerGroups))
			{
				$aCustomerGroups[$oAccount->CustomerGroup]	= Customer_Group::getForId($oAccount->CustomerGroup);
				
				$sInvoiceRunDate	= Invoice_Run::predictNextInvoiceDate($oAccount->CustomerGroup, $sFileEffectiveDate);
				
				$rResult	= $oQuery->Execute("SELECT Id FROM InvoiceRun WHERE customer_group_id = {$oAccount->CustomerGroup} AND BillingDate = '{$sInvoiceRunDate}' AND invoice_run_type_id = ".INVOICE_RUN_TYPE_LIVE." ORDER BY BillingDate DESC LIMIT 1");
				if ($rResult === false)
				{
					throw new Exception($oQuery->Error());
				}
				elseif ($aInvoiceRun = $rResult->fetch_assoc())
				{
					$aCustomerGroups[$oAccount->CustomerGroup]->last_invoice_run_id	= $aInvoiceRun['Id'];
				}
				else
				{
					throw new Exception("No last Invoice Run for Customer Group {$oAccount->CustomerGroup}!");
				}
			}
			$oCustomerGroup	= $aCustomerGroups[$oAccount->CustomerGroup];
			
			$oServiceCurrentInstance	= Service::getForId($aFNNOwner['Service'], false, true);
			
			// Add to Export File
			$oExportCSV->addRow(array	(
											'Service Number'	=> $sFNN,
											/*'Flex Rate Plan'	=> $aBillingData['rate_plan_name'],
											'Revenue'			=> $aBillingData['service_revenue'],
											'M2 TelcoBlue Cost'	=> $aBillingData['m2_telcoblue_cost'],
											'M2 Voicetalk Cost'	=> $aBillingData['m2_voicetalk_cost'],
											'Optus Cost'		=> $aBillingData['optus_cost'],
											'Other Cost'		=> $aBillingData['other_cost'],
											'Total Cost'		=> $aBillingData['total_cost'],
											'Units'				=> $aBillingData['units'],*/
											'Customer Group'	=> $oCustomerGroup->externalName,
											'Flex Status'		=> GetConstantDescription($oServiceCurrentInstance, 'service_status'),
										));
		}
		else
		{
			$oExportCSV->addRow(array	(
											'Service Number'	=> $sFNN,
											'Flex Status'		=> 'Unknown Service'
										));
		}
	}
	elseif (!trim($sLine))
	{
		// Blank line -- ignore
		continue;
	}
	else
	{
		// Unknown
		throw new Exception("Unable to parse Line {$iLineNumber}: '{$sLine}'");
	}
}

// Write Output File
$oExportCSV->saveToFile($sExportPath);

// Exit with success code
exit(0);

?>