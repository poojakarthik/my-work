<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// page
//----------------------------------------------------------------------------//
/**
 * page
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		page.php
 * @language	PHP
 * @package		monitor_application
 * @author		Jared 'flame' Herbohn
 * @version		7.02
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
// Create an Instance of the Page Class
$objPage = new VixenPage($arrConfig);


 // base class
 class VixenPageBase
 {
	function _EncodeBackLink($strHref, $strValue)
	{
		$strBackValue 	= urlencode($strValue);
		$strBackHref 	= urlencode($_SERVER['REQUEST_URI']);
		if (strpos($strHref, '?') !== FALSE)
		{
			$strJoin = "&";
		}
		else
		{
			$strJoin = "?";
		}
		$strBackLink = $strHref.$strJoin."__BackValue=".$strBackValue."&__BackHref=".$strBackHref;
		return $strBackLink;
	}
	
	function _DecodeBackLink()
	{
		$arrBack = Array();
		$arrBack['Value'] 	= urldecode($_GET['__BackValue']);
		$arrBack['Href'] 	= urldecode($_GET['__BackHref']);
		if ($arrBack['Value'] && $arrBack['Href'])
		{
			return $arrBack;
		}
		else
		{
			return FALSE;
		}
	}
 }
 
 
//----------------------------------------------------------------------------//
// VixenPage
//----------------------------------------------------------------------------//
/**
 * VixenPage
 *
 * Page Display Module
 *
 * Page Display Module
 *
 *
 * @prefix		obj
 *
 * @package		monitor_application
 * @class		VixenPage
 */
 class VixenPage extends VixenPageBase
 {
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Application
	 *
	 * Constructor for the Application
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			Application
	 *
	 * @method
	 */
 	function __construct($arrConfig)
 	{

		$this->_arrElements = Array();
	}
	
	// render the page
	function Render()
 	{
		$objPage = $this;
		require_once('template.php');
	}
	
	// Display a page element
	function Display($strElement)
 	{
		echo $this->_arrElements[$strElement];
	}
	
	// Add a page element
	function Add($strElement, $strValue)
	{
		$this->_arrElements[$strElement] = $strValue;
	}
	
	// Append to an existing page element
	function Append($strElement, $strValue, $strNewline=TRUE)
	{
		$this->_arrElements[$strElement] .= $strValue;
		if ($strNewline == TRUE)
		{
			$this->_arrElements[$strElement] .= "<br>\n";
		}
	}
	
	// Add an Page Title element
	function AddPageTitle($strValue)
	{
		$this->_arrElements['PageTitle'] = $strValue;
	}
	
	// Add an Error element
	function AddError($strValue)
	{
		$this->_arrElements['Error'] = $strValue;
	}
	
	// Add a link to the body
	function AddLink($strHref, $strValue, $strNewline=TRUE)
	{
		$this->Append('Body', "<a href=\"$strHref\">$strValue</a>", $strNewline);
	}
	
	// Set the Page Link for this page
	function SetPageLink($strValue)
	{
		$this->_strPageLink = $strValue;
	}
	
	// Add a farward link to the body
	function AddForwardLink($strHref, $strValue, $strName=NULL, $strNewline=TRUE)
	{
		if (!$strName)
		{
			$strName = $this->_strPageLink;
		}
		if ($strName)
		{
			$strHref = $this->_EncodeBackLink($strHref, $strName);
		}
		$this->Append('Body', "<a href=\"$strHref\">$strValue</a>", $strNewline);
	}
	
	// Add a back link to the body
	function AddBackLink($strNewline=TRUE)
	{
		$arrBackLink = $this->_DecodeBackLink();
		if ($arrBackLink)
		{
			$this->Append('Body', "<a href=\"{$arrBackLink['Href']}\">{$arrBackLink['Value']}</a>", $strNewline);
		}
	}
		
	// return a new table object
	function NewTable($strClass="Standard", $strDataClass="Standard")
	{
		return new VixenPageTable($strClass, $strDataClass, $this->_strPageLink);
	}
	
	// Add a table to the body
	function AddTable($objValue)
	{
		$this->Append('Body', $objValue->Output());
	}
	
	// Add a title line to the body
	function AddTitle($strValue, $strStyle='Standard')
	{
		$this->Append('Body', "<h5 class=\"{$strStyle}Title\">$strValue</h5>\n", FALSE);
	}
	
	// Add a text line to the body
	function AddLine($strValue, $strStyle='Standard')
	{
		$this->Append('Body', "<span class=\"{$strStyle}Text\">$strValue</span>\n", TRUE);
	}
	
	// Add text to the body
	function AddText($strValue, $strStyle='Standard')
	{
		$this->Append('Body', "<span class=\"{$strStyle}Text\">$strValue</span>\n", FALSE);
	}
	
	// Add Pagination to the body
	function AddPagination($strHref, $strGetValues='', $intStart=0, $intLimit=20, $intMaxRecords=0)
	{
		$intLimit = (int)$intLimit;
		$intFirstPage = 0;
		$intNextPage = $intStart + $intLimit;
		if ($intMaxRecords)
		{
			$intLastPage = $intMaxRecords - $intLimit;	
			if ($intLastPage < $intNextPage)
			{
				$intNextPage = $intLastPage;
			}
		}
		$intPrevPage = max($intFirstPage, ($intStart - $intLimit));

		$this->Pagination							= TRUE;
		$this->_arrElements['PaginationFirst'] 		= "$strHref?$strGetValues&Start=$intFirstPage&Limit=$intLimit";
		if ($intLastPage)
		{
			$this->_arrElements['PaginationLast'] 		= "$strHref?$strGetValues&Start=$intLastPage&Limit=$intLimit";
		}
		$this->_arrElements['PaginationNext'] 		= "$strHref?$strGetValues&Start=$intNextPage&Limit=$intLimit";
		$this->_arrElements['PaginationPrevious'] 	= "$strHref?$strGetValues&Start=$intPrevPage&Limit=$intLimit";
	}
 
 }
 
 // table class
 class VixenPageTable extends VixenPageBase
 {
 	function __construct($strClass="Standard", $strDataClass="Standard", $strPageLink = NULL)
 	{
		$this->_arrRows 		= Array();
		$this->_intColumns 		= 0;
		$this->_strClass 		= $strClass;
		$this->_strDataClass 	= $strDataClass;
		$this->_strPageLink 	= $strPageLink;
	}
	
	// output table
 	function Output()
	{
		$strOutput = "<table class=\"".$this->_strClass."Table\">\n";
		foreach($this->_arrRows AS $arrRow)
		{
			$strOutput .= "	<tr class=\"".$this->_strClass."Tr\">\n";
			for ($n=0;$n < $this->_intColumns;$n++)
			{
				$strOutput .= "		<td class=\"".$this->_strClass."Td\">\n";
				$strOutput .= "			<span class=\"".$this->_strDataClass."Text\">";
				if (is_array($arrRow[$n]))
				{
					if ($this->_strPageLink)
					{
						$strHref = $this->_EncodeBackLink($arrRow[$n]['Href'], $this->_strPageLink);
					}
					else
					{
						$strHref = $arrRow[$n]['Href'];
					}
					$strOutput .= "<a href=\"{$strHref}\">{$arrRow[$n]['Value']}</a>";
				}
				else
				{
					$strOutput .= "{$arrRow[$n]}";
				}
				$strOutput .= "</span>\n";
				$strOutput .= "		</td>\n";
			}
			$strOutput .= "	</tr>\n";
		}
		$strOutput .= "</table>\n";
		return $strOutput;
	}
	
	// add a row to the table
	function AddRow($arrRow, $strHref = FALSE)
	{
		// work out no. of cols
		$this->_intColumns = max($this->_intColumns, count($arrRow));	
		
		// add global hrefs
		if ($strHref !== FALSE)
		{
			foreach($arrRow as $intKey=>$mixValue)
			{
				if (!is_array($mixValue))
				{
					$arrRow[$intKey] = Array('Value'=>$mixValue, 'Href'=> $strHref);
				}
			}
		}
		
		// add the row
		$this->_arrRows[] = $arrRow;
	}
 }



?>
