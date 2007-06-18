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

// create framework instance
$GLOBALS['fwkFramework'] = new Framework();
$framework = $GLOBALS['fwkFramework'];

require_once('class.ezpdf.php');


// prebuilt class that allows a table of contents to be made
class Creport extends Cezpdf {

	var $reportContents = array();

	function Creport($p,$o)
	{
	  $this->Cezpdf($p,$o);
	}
	
	function rf($info)
	{
	  // this callback records all of the table of contents entries, it also places a destination marker there
	  // so that it can be linked too
	  $tmp = $info['p'];
	  $lvl = $tmp[0];
	  $lbl = rawurldecode(substr($tmp,1));
	  $num=$this->ezWhatPageNumber($this->ezGetCurrentPageNumber());
	  $this->reportContents[] = array($lbl,$num,$lvl );
	  $this->addDestination('toc'.(count($this->reportContents)-1),'FitH',$info['y']+$info['height']);
	}
	
	function dots($info)
	{
	  // draw a dotted line over to the right and put on a page number
	  $tmp = $info['p'];
	  $lvl = $tmp[0];
	  $lbl = substr($tmp,1);
	  $xpos = 520;
	  
	  $size=16;
	  $thick=1;
	
	  $this->saveState();
	  $this->setLineStyle($thick,'round','',array(0,10));
	  $this->line($xpos,$info['y'],$info['x']+5,$info['y']);
	  $this->restoreState();
	  $this->addText($xpos+5,$info['y'],$size,$lbl);
	
	
	}
}

$pdf = new Creport('a4','portrait');
$pdf -> ezSetMargins(50,70,50,50);

// header and footer start

$all = $pdf->openObject();
$pdf->saveState();
$pdf->setStrokeColor(0,0,0,1);
$pdf->line(20,40,578,40);
$pdf->line(20,822,578,822);
$pdf->addText(50,30,8,'© Copyright 2006 Voiptel Pty Ltd');
$pdf->addText(450,30,8, 'COMERCIAL IN CONFIDENCE');
$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');

// header and footer end


//$mainFont = './fonts/Helvetica.afm';
$mainFont = './fonts/Times-Roman.afm';
$codeFont = './fonts/Courier.afm';
// select a font
$pdf->selectFont($mainFont);


// start front page

$arrFrontPageTableData = array (
	array('1'=>'Author', '2'=>':', '3'=>'Jared Herbohn'),
	array('1'=>'Date', '2'=>':', '3'=>date('d-m-Y')),
	array('1'=>'Revision', '2'=>':', '3'=>'1.2.3')
	);
$arrFrontPageTableOptions = array ('fontSize'=>12, 'showLines'=>0, 'showHeadings'=>0, 'shaded'=>0, 'rowGap'=>2, 'xPos'=>100, 'xOrientation'=>'right');
$arrDisclosureStatement = array(array('1'=>'The information contained within this document is confidential and must not be made available to any person who has not entered into a non-disclosure agreement with Voiptel Pty Ltd.  This document must not be used for any purpose other than the purpose for which it was originally supplied.'));
$arrDisclosureStatementOptions = array('fontSize'=>16, 'showHeadings'=>0, 'showLines'=>0, 'xPos'=>300,'xOrientation'=>'centre', 'width'=>300, 'cols'=>array('1'=>array('justification'=>'full')));

$pdf->ezSetDy(-100);
$pdf->ezText("Vixen Developer Manual",30,array('justification'=>'centre'));
$pdf->ezText("\n(Appendix C)\n\n",20,array('justification'=>'centre'));
$pdf->ezText("Database Table Descriptions\n\n",24,array('justification'=>'centre'));
$pdf->ezTable($arrFrontPageTableData, '', '', $arrFrontPageTableOptions);
$pdf->setColor(1,0,0,1);
$pdf->ezText("\nCOMMERCIAL-IN-CONFIDENCE", 30,array('justification'=>'centre'));
$pdf->setColor(0,0,0,1);
$pdf->ezText("\n", 14,array('justification'=>'centre'));
$pdf->ezTable($arrDisclosureStatement, '', '', $arrDisclosureStatementOptions);

// end front page


// content start

$qryTables = new Query();
$arrTable = array();
if(!$refTables = $qryTables->Execute("SHOW TABLES"))
{
	// Return False
			
}
	
$bolFirstTime = TRUE;

$arrTableOutputOptions = array('width'=>500, 'cols'=>array('Name'=>array('width'=>100), 'Type'=>array('width'=>100), 'Description'=>array('width'=>300)));

While ($arrTable = $refTables->fetch_array())
{
	$strTable = $arrTable[0];
	$pdf->ezNewPage();
	if($bolFirstTime)
	{
		$pdf->ezStartPageNumbers(300,30,8,'','',1);
		$bolFirstTime = FALSE;
	}

    $strTableText = $strTable.'<C:rf:1'.rawurlencode($strTable).'>'."\n";
    $pdf->ezText($strTableText,26,array('justification'=>'centre'));

	
	if(!$refColumns = $qryTables->Execute("SHOW FULL COLUMNS FROM $strTable"))
	{
		// Return False
	}
	$arrTableDef = array();
	While($arrColumn = $refColumns->fetch_array())
	{
		$arrTableDef[] = array('Name'=>$arrColumn['Field'], 'Type'=>$arrColumn['Type'], 'Description'=>$arrColumn['Comment']);
	}
	$pdf->ezTable($arrTableDef, '', '', $arrTableOutputOptions);
}

// content end

// write table of contents start - copied code

$pdf->ezStopPageNumbers(1,1);


$pdf->ezInsertMode(1,1,'after');
$pdf->ezNewPage();
$pdf->ezText("Contents\n",26,array('justification'=>'centre'));
$xpos = 520;
$contents = $pdf->reportContents;
$pdf->setStrokeColor(0,0,0,1);
foreach($contents as $k=>$v){
  switch ($v[2]){
    case '1':
      $y=$pdf->ezText('<c:ilink:toc'.$k.'>'.$v[0].'</c:ilink><C:dots:1'.$v[1].'>',16,array('aright'=>$xpos));
      break;
    case '2':
      $pdf->ezText('<c:ilink:toc'.$k.'>'.$v[0].'</c:ilink><C:dots:2'.$v[1].'>',12,array('left'=>50,'aright'=>$xpos));
      break;
  }
}

// write table of contents end

if (isset($d) && $d){
  $pdfcode = $pdf->ezOutput(1);
  $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
  echo '<html><body>';
  echo trim($pdfcode);
  echo '</body></html>';
} else {
  $pdf->ezStream();
}
?>
