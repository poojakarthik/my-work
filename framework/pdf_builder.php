<?php
//----------------------------------------------------------------------------//
// VixenPdf
//----------------------------------------------------------------------------//
/**
* VixenPdf
*
* PDF Builder class
*
* PDF Builder class
* Used to create standard Vixen PDF documents
*
*
* @prefix	pdf
*
* @package	framework
* @class	VixenPdf
*/

require_once('pdf/class.ezpdf.php');

class VixenPdf  
{


	//------------------------------------------------------------------------//
	// __Construct
	//------------------------------------------------------------------------//
	/**
	* __Construct()
	*
	* PDF Builder class
	*
	* PDF Builder class
	*
	* @param	string	$strTitle			optional description
	* @param	string	$strSubTitle		optional description
	* @param	string	$strDocumentTitle	optional description
	* @param	string	$strAuthor			optional description
	*
	* @return void
	*
	* @method
	*/
	 function __Construct($strTitle=NULL, $strSubTitle=NULL, $strDocumentTitle=NULL, $strAuthor=NULL)
	 {
		$this->pdf = new Creport('a4','portrait');
		
		$this->pdf->ezSetCmMargins(1.5,1.0,2.0,2.0);
		$this->strTitle = $strTitle;
		$this->strSubTitle = $strSubTitle;
		$this->strDocumentTitle = $strDocumentTitle;
		$this->strAuthor = $strAuthor;
		$this->arrFrontPageInfo = array();
		
		$strFont = 'pdf/fonts/Times-Roman.afm';
		$this->pdf->selectFont($strFont);
		
		$all = $this->pdf->openObject();
		$this->pdf->saveState();
		$this->pdf->setStrokeColor(0,0,0,1);
		$this->pdf->line(20,40,578,40);
		$this->pdf->line(20,822,578,822);
		$this->pdf->addText(50,30,8,'© Copyright 2006 Voiptel Pty Ltd');
		$this->pdf->addText(450,30,8, 'COMERCIAL IN CONFIDENCE');
		$this->pdf->restoreState();
		$this->pdf->closeObject();
		$this->pdf->addObject($all,'all');
			
		
	 }
	

	//------------------------------------------------------------------------//
	// Title
	//------------------------------------------------------------------------//
	/**
	* Title()
	*
	* Set the title
	*
	* Set the title
	*
	* @param	string	$strTitle		title
	*
	* @return void
	*
	* @method
	*/
	 function Title($strTitle)
	 {
		$this->strTitle = $strTitle;
	 }


	//------------------------------------------------------------------------//
	// SubTitle
	//------------------------------------------------------------------------//
	/**
	* SubTitle()
	*
	* Set the sub title
	*
	* Set the sub title
	*
	* @param	string	$strSubTitle		sub title
	*
	* @return void
	*
	* @method
	*/
	 function SubTitle($strSubTitle)
	 {
	 	$this->SubTitle = $strSubTitle;
	 }


	//------------------------------------------------------------------------//
	// DocumentTitle
	//------------------------------------------------------------------------//
	/**
	* DocumentTitle()
	*
	* Set the document title
	*
	* Set the document title
	*
	* @param	string	$strDocumentTitle		document title
	*
	* @return void 
	*
	* @method
	*/
	 function DocumentTitle($strDocumentTitle)
	 {
	 	$this->DocumentTitle = $strDocumentTitle;
	 }


	//------------------------------------------------------------------------//
	// Author
	//------------------------------------------------------------------------//
	/**
	* Author()
	*
	* Set the author
	*
	* Set the author
	*
	* @param	string	$strAuthor		authors name
	*
	* @return void
	*
	* @method
	*/
	 function Author($strAuthor)
	 {
		$this->$strAuthor = $strAuthor;
	 }


	//------------------------------------------------------------------------//
	// Date
	//------------------------------------------------------------------------//
	/**
	* Date()
	*
	* Set the Date
	*
	* Set the Date
	*
	* @param	string	$strDate		date, should be in the form of dd-mm-yyyy
	*
	* @return void
	*
	* @method
	*/
	 function Date($strDate)
	 {
		$this->strDate = $strDate;
	 }


	//------------------------------------------------------------------------//
	// Revision
	//------------------------------------------------------------------------//
	/**
	* Revision()
	*
	* Set the Revision
	*
	* Set the Revision
	*
	* @param	string	$strRevision		revision number, should be in the form of n.n.n
	*
	* @return void
	*
	* @method
	*/
	 function Revision($strRevision)
	 {
		$this->strRevision = $strRevision;
	 }
	 
	 
	//------------------------------------------------------------------------//
	// RevisionHistory
	//------------------------------------------------------------------------//
	/**
	* RevisionHistory()
	*
	* Set the Revision History
	*
	* Set the Revision History
	*
	* @param	array	$arrRevisionHistory		array of revision history
	*											$arrRevisionHistory[]['Author'] 	= Author
	*											$arrRevisionHistory[]['Date'] 		= Date (dd-mm-yyyy)
	*											$arrRevisionHistory[]['Revision'] 	= Revision (n.n.n)
	*											$arrRevisionHistory[]['Notes'] 		= Notes
	*
	* @return void
	*
	* @method
	*/
	 function RevisionHistory($arrRevisionHistory)
	 {
		$this->$arrRevisionHistory = $arrRevisionHistory;
	 }


	//------------------------------------------------------------------------//
	// AddHeading
	//------------------------------------------------------------------------//
	/**
	* AddHeading()
	*
	* Add a heading to the PDF
	*
	* Add a heading to the PDF
	*
	* @param	string	$strHeading		text of the heading
	* @param	int		$intType		optional heading type constant
	*
	* @return void
	*
	* @method
	*/
	 function AddHeading($strHeading, $intType=NULL)
	 {

	 }


	//------------------------------------------------------------------------//
	// AddText
	//------------------------------------------------------------------------//
	/**
	* AddText()
	*
	* Add a section of text to the PDF
	*
	* Add a section of text to the PDF
	*
	* @param	string	$strText		text to be added to the PDF
	*
	* @return void
	*
	* @method
	*/
	 function AddText($strText)
	 {

	 }


	//------------------------------------------------------------------------//
	// AddTable
	//------------------------------------------------------------------------//
	/**
	* AddTable()
	*
	* Add a table to the PDF
	*
	* Add a table to the PDF
	*
	* @param	array	$arrTable		array containing rows for the table
	*									$arrTable[RowNumber]['ColumnName'] = ColumnValue
	* @param	array	$arrColumns		optional array of colums to use from arrTable
	*									$arrColumns[ColumnName]['Alias'] = optional Column Alias
	*									$arrColumns[ColumnName]['Width'] = optional Column Width
	*
	* @return 
	*
	* @method
	*/
	 function AddTable($arrTable, $arrColumns=NULL)
	 {

	 }


	//------------------------------------------------------------------------//
	// BuildPdf
	//------------------------------------------------------------------------//
	/**
	* BuildPdf()
	*
	* Return the PDF as A string
	*
	* Return the PDF as A string
	*
	* @return  string Contents of PDF
	*
	* @method
	*/
	 function BuildPdf()
	 {
	 	// FRONT PAGE
		
		// title
	 	if(!(substr($this->strTitle, 0, 3) == '<b>'))
		{
			$this->strTitle = '<b>'. $this->strTitle . '</b>';
		}
		$this->pdf->ezText("\n", 24);
		$this->pdf->ezText($this->strTitle, 28, array('justification'=>'centre'));
		
		$this->SubTitle($this->strSubTitle);
		
		// subtitle
		if(!(substr($this->strSubTitle, 0, 3) == '<b>'))
		{
			$this->strSubTitle = '<b>'. $this->strSubTitle . '</b>';
		}
		$this->pdf->ezText($this->strSubTitle, 18, array('justification'=>'centre'));
		
		// document title
		if(!(substr($this->strDocumentTitle, 0, 3) == '<b>'))
		{
			$this->strDocumentTitle = '<b>'. $this->strDocumentTitle . '</b>';
		}
		$this->pdf->ezText("\n", 24);
		$this->pdf->ezText("$this->strDocumentTitle\n\n", 24, array('justification'=>'centre'));
		
		// author
		if($this->strAuthor)
		{
			$this->strAuthor = ': ' . $this->strAuthor;
			$this->arrFrontPageInfo[0] = array('1'=>'Author', '2'=>$this->strAuthor);
		}
		
		// date
		if(!$this->strDate)
		{
			$this->strDate = date('d-m-Y');
		}
		$this->strDate = ': ' . $this->strDate;
		$this->arrFrontPageInfo[1] = array('1'=>'Date', '2'=>$this->strDate);
		
		// revision
		if($this->strRevision)
		{
			$this->strRevision = ': ' . $this->strRevision;
			$this->arrFrontPageInfo[2] = array('1'=>'Revision', '2'=>$this->strRevision);
		}
		
		// build author, date + revision table
		$this->pdf->ezTable($this->arrFrontPageInfo, '', '', array('fontSize'=>12,'showLines'=>0, 'showHeadings'=>0, 'shaded'=>0, 'rowGap'=>2, 'xPos'=>0, 'xOrientation'=>'right'));
		
		// commercial-in-confidence
		$arrDisclosureStatement = array(array('1'=>'<b>The information contained within this document is confidential and must not be made available to any person who has not entered into a non-disclosure agreement with Voiptel Pty Ltd.  This document must not be used for any purpose other than the purpose for which it was originally supplied.</b>'));
		$arrDisclosureStatementOptions = array('fontSize'=>14, 'showHeadings'=>0, 'showLines'=>0, 'xPos'=>300,'xOrientation'=>'centre', 'width'=>300, 'cols'=>array('1'=>array('justification'=>'full')));

		$this->pdf->setColor(1,0,0,1);
		$this->pdf->ezText("\n\n<b>COMMERCIAL-IN-CONFIDENCE</b>", 28,array('justification'=>'centre'));
		$this->pdf->setColor(0,0,0,1);
		$this->pdf->ezText("\n", 14,array('justification'=>'centre'));
		$this->pdf->ezTable($arrDisclosureStatement, '', '', $arrDisclosureStatementOptions);
		$this->pdf->ezNewPage();
		
		// REVISION HISTORY
		$arrRevisionHistoryOptions = array('width'=>550);
		$strTableTitle = 'Revision History';
		$strTableTitle = $strTableTitle.'<C:rf:1'.rawurlencode($strTableTitle).'>'."\n";
    	$this->pdf->ezText($strTableTitle,26,array('justification'=>'centre'));
		$this->pdf->ezTable($this->arrRevisionHistory, '', '', $arrRevisionHistoryOptions);
	 }


	//------------------------------------------------------------------------//
	// RenderPdf
	//------------------------------------------------------------------------//
	/**
	* RenderPdf()
	*
	* Render the PDF to the browser
	*
	* Render the PDF to the browser
	*
	* @return void
	*
	* @method
	*/
	 function RenderPdf()
	 {
	 	$this->BuildPdf();
		$this->pdf->ezStream();
	 }
}

// from readme.php by 'R&OS Ltd' provided with ezpdf class

class Creport extends Cezpdf {

var $reportContents = array();

function Creport($p,$o){
  $this->Cezpdf($p,$o);
}

function rf($info){
  // this callback records all of the table of contents entries, it also places a destination marker there
  // so that it can be linked too
  $tmp = $info['p'];
  $lvl = $tmp[0];
  $lbl = rawurldecode(substr($tmp,1));
  $num=$this->ezWhatPageNumber($this->ezGetCurrentPageNumber());
  $this->reportContents[] = array($lbl,$num,$lvl );
  $this->addDestination('toc'.(count($this->reportContents)-1),'FitH',$info['y']+$info['height']);
}

function dots($info){
  // draw a dotted line over to the right and put on a page number
  $tmp = $info['p'];
  $lvl = $tmp[0];
  $lbl = substr($tmp,1);
  $xpos = 520;

  switch($lvl){
    case '1':
      $size=16;
      $thick=1;
      break;
    case '2':
      $size=12;
      $thick=0.5;
      break;
  }

  $this->saveState();
  $this->setLineStyle($thick,'round','',array(0,10));
  $this->line($xpos,$info['y'],$info['x']+5,$info['y']);
  $this->restoreState();
  $this->addText($xpos+5,$info['y'],$size,$lbl);


}


}
?>
