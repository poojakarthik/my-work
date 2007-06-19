<?php 
$strFrameworkDir = "../";
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."database_define.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");
require_once($strFrameworkDir."pdf_builder.php");

// create framework instance
$GLOBALS['fwkFramework'] = new Framework();
$framework = $GLOBALS['fwkFramework'];

$pdf = new VixenPdf('Vixen Developer Manual', '(Appendix C)', 'Database Table Descriptions', 'Jared Herbohn');

// front page
$pdf->Date('18-06-2007');
$pdf->Revision('123');

// content

$qryTables = new Query();
$arrTable = array();
if(!$refTables = $qryTables->Execute("SHOW TABLES"))
{
	// Return False
}
	
$bolFirstTime = TRUE;

$arrTableCols = array('Name'=>array('width'=>100), 'Type'=>array('width'=>100), 'Description'=>array('width'=>300));



While ($arrTable = $refTables->fetch_array())
{
	$strTable = $arrTable[0];
    $strTableText = $strTable." Table Description";
    $pdf->AddHeading($strTableText);
	$pdf->AddText("<b>Legend:</b> (FK) = Foreign Key\n");
	if(!$refColumns = $qryTables->Execute("SHOW FULL COLUMNS FROM $strTable"))
	{
		// Return False
	}
	$arrTableDef = array();
	While($arrColumn = $refColumns->fetch_array())
	{
		$arrTableDef[] = array('Name'=>$arrColumn['Field'], 'Type'=>$arrColumn['Type'], 'Description'=>$arrColumn['Comment']);
	}
	$pdf->AddTable($arrTableDef, $arrTableCols);
}

$pdf->RenderPdf();

?>
