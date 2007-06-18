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

	 }
}
?>
