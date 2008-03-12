<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// provisioning_history_list.php
//----------------------------------------------------------------------------//
/**
 * provisioning_history_list
 *
 * HTML Template for the View Provisioning History HTML object
 *
 * HTML Template for the View Provisioning History HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all provisiong requests and responses relating to either an account, or single service 
 * and can be embedded in pages or popup windows
 *
 * @file		history_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateProvisioningHistoryList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateProvisioningHistoryList
 *
 * HTML Template class for the View Provisioning History HTML object
 *
 * HTML Template class for the View Provisioning History HTML object
 *
 * @package	ui_app
 * @class	HtmlTemplateHistoryList
 * @extends	HtmlTemplate
 */
class HtmlTemplateProvisioningHistoryList extends HtmlTemplate
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
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		$this->LoadJavascript("provisioning_history");
	}

	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	function Render()
	{
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_POPUP:
				$this->_RenderAsPopup();
				break;
			case HTML_CONTEXT_PAGE:
				$this->_RenderInPage();
				break;
			default:
				$this->_RenderHistory();	
				break;
		}
	}

	//------------------------------------------------------------------------//
	// _RenderInPage()
	//------------------------------------------------------------------------//
	/**
	 * _RenderInPage()
	 *
	 * Render the history for being embedded in a page
	 *
	 * Render the history for being embedded in a page
	 *
	 * @method
	 */
	private function _RenderInPage()
	{
		$arrCatFilterOptions = Array(	PROVISIONING_HISTORY_CATEGORY_BOTH		=> "Both",
										PROVISIONING_HISTORY_CATEGORY_REQUESTS	=> "Requests",
										PROVISIONING_HISTORY_CATEGORY_RESPONSES	=> "Responses");

		$arrTypeFilterOptions = Array(	PROVISIONING_HISTORY_FILTER_ALL				=> "Show All",
										PROVISIONING_HISTORY_FILTER_BARRINGS_ONLY	=> "Barrings Only"); 
		
		$arrMaxItems = Array(10 => "10", 50 => "50", 100 => "100", 200 => "200", 500 => "500", 0 => "Show All");
		
		echo "<h2 class='Provisioning'>History</h2>\n";

		// Render filtering controls
		echo "<div class='GroupedContent'>";
		echo "<div style='height:25px'>";
		echo "<div class='Left'>";
		
		// Create a combobox containing all the Category filter options
		echo "<span>Category</span>\n";
		echo "<span>\n";
		echo "   <select id='ProvHistoryCategoryCombo' onChange='Vixen.ProvisioningHistoryList.intCategoryFilter = this.value;' style='width:150px'>\n";
		foreach ($arrCatFilterOptions as $intOption=>$strDescription)
		{
			$strSelected = (DBO()->History->CategoryFilter->Value == $intOption) ? "selected='selected'" : "";
			echo "      <option $strSelected value='$intOption'>$strDescription</option>\n";
		}
		echo "   </select>\n";
		echo "</span>\n";
		
		// Create a combobox containing all the Request Type filter options
		echo "<span style='margin-left:20px'>Request Type</span>\n";
		echo "<span>\n";
		echo "   <select id='ProvHistoryTypeCombo' onChange='Vixen.ProvisioningHistoryList.intTypeFilter = this.value;' style='width:180px'>\n";
		foreach ($arrTypeFilterOptions as $intOption=>$strDescription)
		{
			$strSelected = (DBO()->History->TypeFilter->Value == $intOption) ? "selected='selected'" : "";
			echo "      <option $strSelected value='$intOption'>$strDescription</option>\n";
		}
		foreach ($GLOBALS['*arrConstant']['Request'] as $intOption => $arrConstant)
		{
			$strSelected = (DBO()->History->TypeFilter->Value == $intOption) ? "selected='selected'" : "";
			echo "      <option $strSelected value='$intOption'>{$arrConstant['Description']}</option>\n";
		}
		echo "   </select>\n";
		echo "</span>\n";
		
		// Create a combobox containing all the MaxItems options
		echo "<span style='margin-left:20px'>Max Items</span>\n";
		echo "<span>\n";
		echo "   <select id='ProvHistoryMaxItemsCombo' onChange='Vixen.ProvisioningHistoryList.intMaxItems = this.value;' style='width:100px'>\n";
		foreach ($arrMaxItems as $intOption=>$strDescription)
		{
			$strSelected = (DBO()->History->MaxItems->Value == $intOption) ? "selected='selected'" : "";
			echo "      <option $strSelected value='$intOption'>$strDescription</option>\n";
		}
		echo "   </select>\n";
		echo "</span>\n";

		echo "</div>\n"; //Left
		echo "<div class='Right'>\n";
		$this->Button("Filter", "Vixen.ProvisioningHistoryList.ApplyFilter(true);");
		echo "</div>\n"; //Right
		echo "</div>\n"; //height=25px
		echo "</div>\n"; // GroupedContent
		
		echo "<div class='TinySeperator'></div>\n";
		
		// Render the history
		$strHistoryContainerDivId = "HistoryContainerForPage";
		echo "<div id='$strHistoryContainerDivId'>";
		$this->_RenderHistory();
		echo "</div>";
		
		// Initialise the javascript object
		$intAccountId		= DBO()->Account->Id->Value;
		$intServiceId		= (DBO()->Service->Id->Value) ? DBO()->Service->Id->Value : "null";
		$intCategoryFilter	= DBO()->History->CategoryFilter->Value;
		$intTypeFilter		= (DBO()->History->TypeFilter->Value) ? DBO()->History->TypeFilter->Value : "null";
		$intMaxItems		= DBO()->History->MaxItems->Value;
		$strJavascript	= "	if (Vixen.ProvisioningHistoryList == undefined)
							{
								Vixen.ProvisioningHistoryList = new VixenProvisioningHistoryListClass;
							}
							Vixen.ProvisioningHistoryList.Initialise($intAccountId, $intServiceId, $intCategoryFilter, $intTypeFilter, $intMaxItems, '$strHistoryContainerDivId', true);
						";
							
		echo "<script type='text/javascript'>$strJavascript</script>\n";
	}

	private function _RenderFilter()
	{
		//TODO remove the filter control code from _RenderInPage and stick it here
		// So that _RenderInPage and _RenderAsPopup can use it
	}
	
	//------------------------------------------------------------------------//
	// _RenderAsPopup()
	//------------------------------------------------------------------------//
	/**
	 * _RenderAsPopup()
	 *
	 * Render the History as a popup
	 *
	 * Render the History as a popup
	 *
	 * @method
	 */
	private function _RenderAsPopup()
	{
		echo "<div class='GroupedContent'>";
		
		$arrCatFilterOptions = Array(	PROVISIONING_HISTORY_CATEGORY_BOTH		=> "Both",
										PROVISIONING_HISTORY_CATEGORY_REQUESTS	=> "Requests",
										PROVISIONING_HISTORY_CATEGORY_RESPONSES	=> "Responses");

		$arrTypeFilterOptions = Array(	PROVISIONING_HISTORY_FILTER_ALL				=> "Show All",
										PROVISIONING_HISTORY_FILTER_BARRINGS_ONLY	=> "Barrings Only"); 
		
		$arrMaxItems = Array(10 => "10", 50 => "50", 100 => "100", 200 => "200", 500 => "500", 0 => "Show All");
		
		// Render filtering controls
		echo "<div style='height:25px'>";
		echo "<div class='Left'>";
		
		// Create a combobox containing all the Category filter options
		echo "<span>Category</span>\n";
		echo "<span>\n";
		echo "   <select id='ProvHistoryCategoryCombo' onChange='Vixen.ProvisioningHistoryPopup.intCategoryFilter = this.value;' style='width:150px'>\n";
		foreach ($arrCatFilterOptions as $intOption=>$strDescription)
		{
			$strSelected = (DBO()->History->CategoryFilter->Value == $intOption) ? "selected='selected'" : "";
			echo "      <option $strSelected value='$intOption'>$strDescription</option>\n";
		}
		echo "   </select>\n";
		echo "</span>\n";
		
		// Create a combobox containing all the Request Type filter options
		echo "<span style='margin-left:20px'>Request Type</span>\n";
		echo "<span>\n";
		echo "   <select id='ProvHistoryTypeCombo' onChange='Vixen.ProvisioningHistoryPopup.intTypeFilter = this.value;' style='width:180px'>\n";
		foreach ($arrTypeFilterOptions as $intOption=>$strDescription)
		{
			$strSelected = (DBO()->History->TypeFilter->Value == $intOption) ? "selected='selected'" : "";
			echo "      <option $strSelected value='$intOption'>$strDescription</option>\n";
		}
		foreach ($GLOBALS['*arrConstant']['Request'] as $intOption => $arrConstant)
		{
			$strSelected = (DBO()->History->TypeFilter->Value == $intOption) ? "selected='selected'" : "";
			echo "      <option $strSelected value='$intOption'>{$arrConstant['Description']}</option>\n";
		}
		echo "   </select>\n";
		echo "</span>\n";
		
		// Create a combobox containing all the MaxItems options
		echo "<span style='margin-left:20px'>Max Items</span>\n";
		echo "<span>\n";
		echo "   <select id='ProvHistoryMaxItemsCombo' onChange='Vixen.ProvisioningHistoryPopup.intMaxItems = this.value;' style='width:100px'>\n";
		foreach ($arrMaxItems as $intOption=>$strDescription)
		{
			$strSelected = (DBO()->History->MaxItems->Value == $intOption) ? "selected='selected'" : "";
			echo "      <option $strSelected value='$intOption'>$strDescription</option>\n";
		}
		echo "   </select>\n";
		echo "</span>\n";

		echo "</div>\n"; //Left
		echo "<div class='Right'>\n";
		$this->Button("Filter", "Vixen.ProvisioningHistoryPopup.ApplyFilter(true);");
		echo "</div>\n"; //Right
		echo "</div>\n"; //height=25px
		
		echo "<div class='TinySeperator'></div>\n";
	
		// Render the History
		$strHistoryContainerDivId = "HistoryContainerForPopup";
		echo "<div id='ContainerDiv_ScrollableDiv_History' style='border: solid 1px #606060; padding: 5px 5px 5px 5px'>\n";
		echo "<div id='ScrollableDiv_History' style='overflow:auto; height:410px; width:auto; padding: 0px 3px 0px 3px'>\n";
		echo "<div id='$strHistoryContainerDivId'>\n";
		$this->_RenderHistory();
		echo "</div>\n";
		echo "</div>\n"; //ScrollableDiv_History
		echo "</div>\n"; //ContainerDiv_ScrollableDiv_History
		
		echo "</div>\n"; // GroupedContent
		
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Close", "Vixen.Popup.Close(this);");
		echo "</div></div>\n";
		
		// Initialise the javascript object
		$intAccountId		= DBO()->Account->Id->Value;
		$intServiceId		= (DBO()->Service->Id->Value) ? DBO()->Service->Id->Value : "null";
		$intCategoryFilter	= DBO()->History->CategoryFilter->Value;
		$intTypeFilter		= (DBO()->History->TypeFilter->Value) ? DBO()->History->TypeFilter->Value : "null";
		$intMaxItems		= DBO()->History->MaxItems->Value;
		$strPopupId			= $this->_objAjax->strId;
		$strJavascript	= "	if (Vixen.ProvisioningHistoryPopup == undefined)
							{
								Vixen.ProvisioningHistoryPopup = new VixenProvisioningHistoryListClass;
							}
							Vixen.ProvisioningHistoryPopup.Initialise($intAccountId, $intServiceId, $intCategoryFilter, $intTypeFilter, $intMaxItems, '$strHistoryContainerDivId', false, '$strPopupId');
						";
							
		echo "<script type='text/javascript'>$strJavascript</script>\n";
	}
	
	//------------------------------------------------------------------------//
	// _RenderHistory()
	//------------------------------------------------------------------------//
	/**
	 * _RenderHistory()
	 *
	 * Renders the items in the history
	 *
	 * Renders the items in the history
	 *
	 * @method
	 */
	private function _RenderHistory()
	{
		$arrHistory = DBO()->History->Records->Value;
		
		$bolForServiceOnly = (DBO()->Service->Id->Value)? TRUE : FALSE;
		
		if ($bolForServiceOnly)
		{
			Table()->History->SetHeader("&nbsp;", "Date", "Type", "Carrier", "Status", "Description");
			Table()->History->SetWidth("3%", "8%", "20%", "10%", "15%", "44%");
			Table()->History->SetAlignment("Left", "Left", "Left", "Left","Left", "Left");
		}
		else
		{
			Table()->History->SetHeader("&nbsp;", "Date", "Service", "Type", "Carrier", "Status", "Description");
			Table()->History->SetWidth("3%", "8%", "10", "20%", "10%", "15%", "34%");
			Table()->History->SetAlignment("Left", "Left", "Left", "Left","Left", "Left", "Left");
		}

		foreach ($arrHistory as $arrRecord)
		{
			if ($arrRecord['Outbound'])
			{
				$strOutboundCell = "<img src='img/template/outbound.png' />";
				$strStatusCell = GetConstantDescription($arrRecord['Status'], "RequestStatus");
				
				if ($arrRecord['Status'] == REQUEST_STATUS_WAITING)
				{
					// The request has not been sent yet.  It can be cancelled
					$strCancel = Href()->CancelProvisioningRequest($arrRecord['Id']);
					$strStatusCell .= "&nbsp;<a href='$strCancel'>(Cancel)</a>";
				}
			}
			else
			{
				$strOutboundCell = "<img src='img/template/inbound.png' />";
				$strStatusCell = GetConstantDescription($arrRecord['Status'], "ResponseStatus");
			}
			
			// Build the TimeStamp field
			$strTimeStampCell	= date("d/m/y H:i:s", strtotime($arrRecord['TimeStamp']));
			
			$strRequestType	= GetConstantDescription($arrRecord['Type'], "Request");
			$strCarrier		= GetConstantDescription($arrRecord['Carrier'], "Carrier");
			
			$strDescription = htmlspecialchars($arrRecord['Description'], ENT_QUOTES);
			
			if ($bolForServiceOnly)
			{
				Table()->History->AddRow($strOutboundCell, $strTimeStampCell, $strRequestType, $strCarrier, $strStatusCell, $strDescription);
			}
			else
			{
				Table()->History->AddRow($strOutboundCell, $strTimeStampCell, $arrRecord['FNN'], $strRequestType, $strCarrier, $strStatusCell, $strDescription);
			}
		}
		
		if (count($arrHistory) == 0)
		{
			// There are no invoices to stick in this table
			Table()->History->AddRow("No records to display");
			Table()->History->SetRowAlignment("left");
			$intColumns = ($bolForServiceOnly)? 6 : 7;
			Table()->History->SetRowColumnSpan($intColumns);
		}
		
		Table()->History->Render();
	}
	
	//DEPRICATED
	private function _RenderRequestGroup($arrRecordGroup, $arrHistory, $bolForServiceOnly)
	{	
		$strBorderColor 	= "89b100";
		$strBackgroundColor = "f1f7e1";
		$strTextColor 		= "000000";
		
		// Setup the group div
		echo "<div style='border: solid 1px #{$strBorderColor}; background-color: #{$strBackgroundColor}; color: #{$strTextColor}; padding: 3px'>\n";
		
		// Group Details
		$strDetailsHtml = "Request: ";
		$strDetailsHtml .= date("l, M j, Y g:i:s A", strtotime($arrHistory[$arrRecordGroup[0]]['TimeStamp']));
		if ($arrHistory[$arrRecordGroup[0]]['Employee'] != NULL && $arrHistory[$arrRecordGroup[0]]['Employee'] != USER_ID)
		{
			$strDetailsHtml .= " Created by ". GetEmployeeName($arrHistory[$arrRecordGroup[0]]['Employee']) . ".";
		}
		else
		{
			$strDetailsHtml .= " Created by Automated System.";
		}
		
		// Output the Grouping details
		echo "<span style='font-size: 9pt'>$strDetailsHtml</span>\n";
		echo "<div class='TinySeperator'></div>\n";
		
		// Output each record of the group
		echo "<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
		
		for ($i=0; $i < count($arrRecordGroup); $i++)
		{
			$arrRequest = $arrHistory[$arrRecordGroup[$i]];
			
			$strRequest = GetConstantDescription($arrRequest['Type'], "Request");
			$strStatus	= GetConstantDescription($arrRequest['Status'], "RequestStatus");
			$strCarrier = GetConstantDescription($arrRequest['Carrier'], "Carrier");
			
			if ($arrRequest['Status'] == REQUEST_STATUS_WAITING)
			{
				// The request has not been sent yet.  It can be cancelled
				$strCancel = Href()->CancelProvisioningRequest($arrRequest['Id']);
				$strStatus .= "<a href='$strCancel' style='margin-left:30px'>(Cancel)</a>";
			}
			
			echo "<tr>";
			
			if ($bolForServiceOnly)
			{
				// The history is relating to a single service so don't worry about declaring which service it is
				if ($i == 0)
				{
					// First Record so include widths
					echo "<td width='30%'>$strRequest</td><td width='20%'>$strCarrier</td><td width='50%'>$strStatus</td>";
				}
				else
				{
					echo "<td>$strRequest</td><td>$strCarrier</td><td>$strStatus</td>";
				}
			}
			else
			{
				// This history is relating to an entire account
				if ($i == 0)
				{
					// First Record so include widths
					echo "<td width='15%'>{$arrRequest['FNN']}</td><td width='30%'>$strRequest</td><td width='20%'>$strCarrier</td><td width='35%'>$strStatus</td>";
				}
				else
				{
					echo "<td>{$arrRequest['FNN']}</td><td>$strRequest</td><td>$strCarrier</td><td>$strStatus</td>";
				}
			}
			echo "</tr>\n";
		}
		
		echo "</table>\n";
		
		echo "</div>\n";
		
		// Include a separator
		echo "<div class='TinySeperator'></div>\n";
		
	}
}

?>
