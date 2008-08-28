<?php
require_once("../../flex.require.php");

$strURL			= '10.50.50.132';
$strUser		= 'ybs_admin';
$strPassword	= 'Y3ll0w4dmiN#';
$strDatabase	= 'flex_telcoblue';

$arrCols = DataAccess::getDataAccess()->FetchClean('CDR');
unset($arrCols['CarrierRef']);
unset($arrCols['CDR']);
unset($arrCols['Charge']);
unset($arrCols['Rate']);
unset($arrCols['RatedOn']);
unset($arrCols['InvoiceRun']);
unset($arrCols['SequenceNo']);
$arrCols['NormalisedOn']	= new MySQLFunction("NOW()");
$arrCols['FNN']	= NULL;
$insCDR		= new StatementInsert("CDR", $arrCols);
$selService = new StatementSelect("Service", "*", "ServiceType = <ServiceType> AND (ClosedOn IS NULL OR ClosedOn <= <StartDatetime>)", "RAND()", 1);

// Connect to MINX
$sqlMinx = new mysqli($strURL, $strUser, $strPassword, $strDatabase);

// Retrieve new CDRs
if (!$resResult = $sqlMinx->query("SELECT * FROM CDR WHERE NormalisedOn > SUBDATE(CURDATE(), INTERVAL 1 DAY) AND Status IN (101, 150) ORDER BY RAND() LIMIT 1000"))
{
	Debug($sqlMinx->error());
	die;
}

// Manipulate 
while ($arrCDR = $resResult->fetch_assoc())
{
	// Select a Service for this CDR
	$selService->Execute($arrCDR);
	$arrService = $selService->Fetch();
	
	$arrCDR['AccountGroup']	= $arrService['AccountGroup'];
	$arrCDR['Account']		= $arrService['Account'];
	$arrCDR['Service']		= $arrService['Id'];
	$arrCDR['File']			= 0;
	$arrCDR['Status']		= CDR_NORMALISED;
	$arrCDR['NormalisedOn']	= new MySQLFunction("NOW()");
	
	if ($arrService['Indial100'])
	{
		// Randomly choose an FNN in the range
		$arrCDR['FNN']	= substr($arrService['FNN'], 0, -2).str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
	}
	else
	{
		// Set to Service FNN
		$arrCDR['FNN']	= $arrService['FNN'];
	}
	
	// Insert modified CDR
	Debug("Inserting a CDR for {$arrService['FNN']}...");
	$insCDR->Execute($arrCDR);
}

// Close MySQL connection to MINX
$sqlMinx->close();
?>