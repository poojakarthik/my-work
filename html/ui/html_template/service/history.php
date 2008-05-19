<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// history.php
//----------------------------------------------------------------------------//
/**
 * history
 *
 * HTML Template for the Service History functionality
 *
 * HTML Template for the Service History functionality
 *
 * @file		history.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.05
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// HtmlTemplateServiceHistory
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceHistory
 *
 * HTML Template object defining the various presentation methods to represent the history of a service
 *
 * HTML Template object defining the various presentation methods to represent the history of a service
 * Note that this class does not have a render method, as it is used by other HtmlTemplates
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateServiceHistory
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceHistory extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext=NULL, $strId=NULL)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
	}

	//NOTE: this class doesn't currently have a Render method, because it is used by other HtmlTemplateObjects
	
	//------------------------------------------------------------------------//
	// GetHistory
	//------------------------------------------------------------------------//
	/**
	 * GetHistory()
	 *
	 * Builds a HTML table detailing the history of a service (activations/deactivations)
	 *
	 * Builds a HTML table detailing the history of a service (activations/deactivations)
	 *
	 * @param	array	$arrHistory		History array as defined in AppTemplateAccount->GetServices()
	 * @return	string					html code to render the history as a table
	 * @method
	 */
	static function GetHistory($arrHistory)
	{
		$intLastRecordIndex = count($arrHistory) - 1;
		$strRows = "";
		foreach ($arrHistory as $intIndex=>$arrHistoryItem)
		{
			if ($arrHistoryItem['ClosedOn'] != NULL)
			{
				$strClosedBy		= ($arrHistoryItem['ClosedBy'] != NULL)? "by ". GetEmployeeName($arrHistoryItem['ClosedBy']) ." (". GetEmployeeUserName($arrHistoryItem['ClosedBy']) .")" : "";
				$strClosedOn		= OutputMask()->ShortDate($arrHistoryItem['ClosedOn']);
				$strNatureOfClosure = GetConstantDescription($arrHistoryItem['Status'], "Service");
				$strRows .= "
<tr>
	<td>$strNatureOfClosure</td>
	<td style='text-align:right'>$strClosedOn</td>
	<td>$strClosedBy</td>
</tr>";
			}

			$strCreatedBy			= "by ". GetEmployeeName($arrHistoryItem['CreatedBy']) ." (". GetEmployeeUserName($arrHistoryItem['CreatedBy']) .")";
			$strCreatedOn			= OutputMask()->ShortDate($arrHistoryItem['CreatedOn']);
			$strNatureOfCreation	= ($intIndex == $intLastRecordIndex)? "Created" : "Activated";
			
			$strRows .= "
<tr>
	<td>$strNatureOfCreation</td>
	<td style='text-align:right'>$strCreatedOn</td>
	<td>$strCreatedBy</td>
</tr>";
			
		}
		
		$strTable = "<div style='width:100%;background-color: #D4D4D4'<table style='width:50%'>$strRows</table></div>";
		return $strTable;
	}
	
}

?>
