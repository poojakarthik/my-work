<?php

require_once("../../lib/classes/Flex.php");
Flex::load();

/* CONFIG */
define('INVOICE_WHERE'	, "DeliveryMethod = ".DELIVERY_METHOD_DO_NOT_SEND);

define('XML_PATH'		, Flex::getBase().'files/invoices/xml/');

define('CREATE_AS_COPY'	, true);

/* CONFIG */

$dbConnection	= Data_Source::get();
$dbConnection->setFetchMode(MDB2_FETCHMODE_ASSOC);

$arrModifiedFiles		= array();
$arrBackupFiles			= array();
$arrReplacementFiles	= array();

$arrBatchWriteFiles		= array();

// Load Invoices from the DB
$intInvoiceRunId	= (int)$argv[1];
$resInvoiceRun		= $dbConnection->query("SELECT * FROM Invoice WHERE invoice_run_id = {$intInvoiceRunId}".(INVOICE_WHERE ? ' AND '.INVOICE_WHERE : ''));
if (PEAR::isError($resInvoiceRun))
{
	throw new Exception("MDB2 Error: ".$resInvoiceRun->getMessage()." \n\n Native Message: ".$resInvoiceRun->getUserInfo());
}
if ($resInvoiceRun->numRows())
{
	try
	{
		while ($arrInvoice = $resInvoiceRun->fetchRow())
		{
			Log::getLog()->log(" [*] Updating Invoice #{$arrInvoice['id']}/Account #{$arrInvoice['Account']}...");
			
			// Find the XML file for this Invoice
			$strXMLPath	= XML_PATH.$intInvoiceRunId."/{$arrInvoice['Account']}.xml";
			if (file_exists($strXMLPath))
			{
				$arrModifiedFiles[]	= $strXMLPath;
				$strFileContents	= file_get_contents($strXMLPath);
				$strNewFileContents	= $strFileContents;
				
				if ((int)$arrInvoice['DeliveryMethod'] === DELIVERY_METHOD_EMAIL_SENT)
				{
					
				}
				
				// Delivery Method
				$strDeliveryMethod	= Constant_Group::getConstantGroup('delivery_method')->getConstantAlias((int)$arrInvoice['DeliveryMethod']);
				$strNewFileContents	=	preg_replace
										(
											"/(\<DeliveryMethod\>)([\w]+)(\<\/DeliveryMethod\>)/is",
											"<DeliveryMethod>{$strDeliveryMethod}</DeliveryMethod>",
											$strNewFileContents,
											1
										);
										
				Log::getLog()->log("\t[+] Updated Delivery Method to {$strDeliveryMethod}");
				
				// Due Date
				$strDueDate			= date("j M y", strtotime($arrInvoice['DueOn']));
				$strNewFileContents	=	preg_replace
										(
											"/(\<DueDate\>)(\d{1,2}\ [a-z]{3}\ \d{2})(\<\/DueDate\>)/is",
											"<DueDate>{$strDueDate}</DueDate>",
											$strNewFileContents,
											1
										);
				Log::getLog()->log("\t[+] Updated Due Date to {$strDueDate}");
				
				$strBackupPath		= $strXMLPath.'.bak';
				file_put_contents($strBackupPath, $strFileContents);
				$arrBackupFiles[]	= $strBackupPath; 
				
				if (CREATE_AS_COPY)
				{
					file_put_contents($strXMLPath.'.new', $strNewFileContents);
				}
				else
				{
					$arrBatchWriteFiles[$strXMLPath]	= $strNewFileContents;
				}
			}
			else
			{
				throw new Exception("Unable to find XML for Invoice #{$arrInvoice['Id']} (Account #{$arrInvoice['Account']})");
			}
		}
		
		// Perform Batch Write
		if (!CREATE_AS_COPY)
		{
			foreach ($arrBatchWriteFiles as $strPath=>$strNewFileContents)
			{
				file_put_contents($strPath, $strNewFileContents);
			}
		}
	}
	catch (Exception $eException)
	{
		// Rollback File Changes
		foreach ($arrReplacementFiles as $strPath)
		{
			unlink($strPath);
		}
		
		throw $eException;
	}
}
else
{
	throw new Exception("No Invoices for Invoice Run Id '{$argv[1]}'");
}


?>