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
		$this->_intContext			= $intContext;
		$this->_strContainerDivId	= $strId;
		
		$this->LoadJavascript("table_sort");
		
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
		$objService	= DBO()->Service->AsObject->Value;
		
		Table()->History->SetHeader("Timestamp", "Event", "Performed By");
		Table()->History->SetWidth("24%", "43%", "33%");
		Table()->History->SetAlignment("Left", "Left", "Left");
		Table()->History->SetSortable(TRUE);
		Table()->History->SetSortFields(NULL, NULL, NULL);
		Table()->History->SetPageSize(10);


		$arrHistoryForDisplay = HtmlTemplateServiceHistory::_GetHistoryDetailsForDisplay($objService);
		
		foreach ($arrHistoryForDisplay as $arrHistoryItem)
		{
			Table()->History->AddRow($arrHistoryItem['TimeStamp'], $arrHistoryItem['Event'], $arrHistoryItem['EmployeeName']);
		}
		
		ob_start();
		Table()->History->Render();
		$strTable = ob_get_clean();
		
		echo "
$strTable
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
		$arrHistoryForDisplay = self::_GetHistoryDetailsForDisplay($arrHistory);
		
		foreach ($arrHistoryForDisplay as $intIndex=>$arrHistoryItem)
		{
			if ($intIndex == 0)
			{
				// Declare the widths in the first row
				$strRows .= "
<tr>
	<td width='26%'>{$arrHistoryItem['TimeStamp']}</td>
	<td width='44%'>{$arrHistoryItem['Event']}</td>
	<td width='30%'>{$arrHistoryItem['EmployeeName']}</td>
</tr>";
			}
			else
			{
				$strRows .= "
<tr>
	<td>{$arrHistoryItem['TimeStamp']}</td>
	<td>{$arrHistoryItem['Event']}</td>
	<td>{$arrHistoryItem['EmployeeName']}</td>
</tr>";
			}
		}
		
		$strTable = "<table style='width:100%'>$strRows</table>";
		return $strTable;
	}

	// Returns a description of the last event as a string
	static function GetLastEvent($mixService)
	{
		$arrHistory = self::_GetHistoryDetailsForDisplay($mixService);
		return $arrHistory[0];
	}
	
	//$mixService can be a ModuleService object OR an array of Service records detailing the history of a service
	/* @return		array			$arrHistory[]	['TimeStamp']
	 * 												['Event']		(Description, includes link to Related Account if there is one)
	 * 												['EmployeeName']
	 */ 
	static private function _GetHistoryDetailsForDisplay($mixService)
	{
		static $intNowTimeStamp;
		if (!isset($intNowTimeStamp))
		{
			$intNowTimeStamp = strtotime(GetCurrentISODateTime());
		}
		
		// Retrieve the History of the service
		if (is_object($mixService))
		{
			$arrHistory = $mixService->GetHistory();
		}
		else
		{
			$arrHistory = ModuleService::GetHistoryForAnonymous($mixService);
		}
		
		$arrHistoryForDisplay = Array();
		foreach ($arrHistory as $arrHistoryItem)
		{
			$intTimeStamp	= strtotime($arrHistoryItem['TimeStamp']);
			$strTimeStamp	= date("H:i:s M j, Y", $intTimeStamp);
			$strEmployee	= GetEmployeeName($arrHistoryItem['Employee']);
			
			// Check if there is another account related to this history item
			$strAccount = "";
			if ($arrHistoryItem['RelatedAccount'] != NULL)
			{
				// Create a link to it
				$strAccountLink = Href()->AccountOverview($arrHistoryItem['RelatedAccount']);
				$strAccount = "<a href='$strAccountLink'>{$arrHistoryItem['RelatedAccount']}</a>";
			}
			
			if ($arrHistoryItem['IsCreationEvent'])
			{
				switch ($arrHistoryItem['Event'])
				{
					case SERVICE_CREATION_NEW:
						$strEvent = "Created";
						if (count($arrHistory) == 1)
						{
							// This is the only item in the history.  Check if the activation is pending
							$intStatus = (is_object($mixService))? $mixService->GetStatus() : $mixService[0]['Status'];
							
							if ($intStatus == SERVICE_PENDING)
							{
								$strEvent .= " (Pending Activation)";
							}
						}
						break;
						
					case SERVICE_CREATION_ACTIVATED:
						$strEvent = "Activated";
						break;
						
					case SERVICE_CREATION_LESSEE_CHANGED:
						$strEvent = "Acquired from Account: $strAccount<br />(Change of Lessee)";
						break;
						
					case SERVICE_CREATION_ACCOUNT_CHANGED:
						$strEvent = "Acquired from Account: $strAccount<br />(Account Move)";
						break;
						
					case SERVICE_CREATION_LESSEE_CHANGE_REVERSED:
						$strEvent = "Activated<br />(Reversal of Change of Lessee)";
						break;
						
					case SERVICE_CREATION_ACCOUNT_CHANGE_REVERSED:
						$strEvent = "Activated<br />(Reversal of Account Move)";
						break;
						
					default:
						$strEvent = GetConstantDescription($arrHistoryItem['Event'], "ServiceCreation");
						break;
				}
			}
			else
			{
				switch ($arrHistoryItem['Event'])
				{
					case SERVICE_CLOSURE_DISCONNECTED:
						$strEvent = "Disconnected";
						break;
						
					case SERVICE_CLOSURE_ARCHIVED:
						$strEvent = "Archived";
						break;
						
					case SERVICE_CLOSURE_LESSEE_CHANGED:
						$strEvent = "Moved to Account: $strAccount<br />(Change of Lessee)";
						break;
						
					case SERVICE_CLOSURE_ACCOUNT_CHANGED:
						$strEvent = "Moved to Account: $strAccount<br />(Account Move)";
						break;
						
					case SERVICE_CLOSURE_LESSEE_CHANGE_REVERSED:
						$strEvent = "Deactivated<br />(Reversal of Change of Lessee)";
						break;
						
					case SERVICE_CLOSURE_ACCOUNT_CHANGE_REVERSED:
						$strEvent = "Deactivated<br />(Reversal of Account Move)";
						break;
						
					default:
						$strEvent = GetConstantDescription($arrHistoryItem['Event'], "ServiceClosure");
						break;
				}
			}
			
			// Check if the event has been scheduled for a future time
			if ($intTimeStamp > $intNowTimeStamp)
			{
				$strEvent = "Will be " . strtolower(substr($strEvent, 0, 1)) . substr($strEvent, 1);
				$strEvent = "<span style='color:#FF0000'>$strEvent</span>";
			}
			
			$arrHistoryForDisplay[] = Array(
											"TimeStamp" => $strTimeStamp,
											"Event"		=> $strEvent,
											"EmployeeName"	=> $strEmployee
											);
		}
		return $arrHistoryForDisplay;
	}
}

?>
