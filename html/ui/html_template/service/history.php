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

	function Render()
	{
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_POPUP:
				$this->RenderAsPopup();
				break;
				
			default:
				echo "ERROR: HtmlTemplateServiceHistory does not now how to render with context '{$this->_intContext}'"; 
				break;
		}
	}
	
	function RenderAsPopup()
	{
		$arrService	= DBO()->Service->AsArray->Value;
		
		Table()->History->SetHeader("Action", "Timestamp", "Instigator");
		Table()->History->SetWidth("34%", "33%", "33%");
		Table()->History->SetAlignment("Left", "Left", "Left");
		
		$intLastRecordIndex = count($arrService['History']) - 1;
		foreach ($arrService['History'] as $intIndex=>$arrHistoryItem)
		{
			if ($arrHistoryItem['ClosedOn'] != NULL)
			{
				$strClosedBy		= ($arrHistoryItem['ClosedBy'] != NULL)? GetEmployeeName($arrHistoryItem['ClosedBy']) ." (". GetEmployeeUserName($arrHistoryItem['ClosedBy']) .")" : "";
				$strClosedOn		= OutputMask()->ShortDate($arrHistoryItem['ClosedOn']);
				$strNatureOfClosure = GetConstantDescription($arrHistoryItem['Status'], "Service");
				Table()->History->AddRow($strNatureOfClosure, $strClosedOn, $strClosedBy);
			}

			$strCreatedBy			= GetEmployeeName($arrHistoryItem['CreatedBy']) ." (". GetEmployeeUserName($arrHistoryItem['CreatedBy']) .")";
			$strCreatedOn			= OutputMask()->ShortDate($arrHistoryItem['CreatedOn']);
			$strNatureOfCreation	= ($intIndex == $intLastRecordIndex)? "Created" : "Activated";
			Table()->History->AddRow($strNatureOfCreation, $strCreatedOn, $strCreatedBy);
		}
		
		ob_start();
		Table()->History->Render();
		$strTable = ob_get_clean();
		
		echo "
<div id='ContainerDiv_ScrollableDiv_History' style='border: solid 1px #D1D1D1; padding: 5px 5px 5px 5px'>
	<div id='ScrollableDiv_History' style='overflow:auto; height:200px; width:auto; padding: 0px 3px 0px 3px'>
		$strTable
	</div>
</div>
<div class='ButtonContainer'>
	<input type='button' style='float:right' value='Close' onClick='Vixen.Popup.Close(this)'></input>
</div>
";
	}
	
	//------------------------------------------------------------------------//
	// GetHistoryForTableDropDownDetail
	//------------------------------------------------------------------------//
	/**
	 * GetHistoryForTableDropDownDetail()
	 *
	 * Builds a HTML table detailing the history of a service (activations/deactivations)
	 *
	 * Builds a HTML table detailing the history of a service (activations/deactivations)
	 *
	 * @param	array	$arrHistory		History array as defined in AppTemplateAccount->GetServices()
	 * @return	string					html code to render the history as a table
	 * @method
	 */
	static function GetHistoryForTableDropDownDetail($arrHistory)
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
