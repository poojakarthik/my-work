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
				$this->_RenderHistory(DBO()->History->JsObjectName->Value);	
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
		$strObjectName = "ProvisioningHistoryList". ((DBO()->Service->Id->IsSet)? DBO()->Service->Id->Value : "");
		
		if (!DBO()->Service->Id->Value)
		{
			echo "<h2 class='ProvisioningHistory'>Account Provisioning History</h2>\n";
		}
		else
		{
			echo "<h2 class='ProvisioningHistory'>Provisioning History</h2>\n";
		}
		$this->_RenderFilterControls($strObjectName);
		
		// Render the history
		$strHistoryContainerDivId = "HistoryContainerForPage". ((DBO()->Service->Id->IsSet)? DBO()->Service->Id->Value : "");
		echo "<div id='$strHistoryContainerDivId'>";
		$this->_RenderHistory($strObjectName);
		echo "</div>";
		echo "<div class='Seperator'></div>\n";
		
		// Initialise the javascript object
		$intAccountId		= DBO()->Account->Id->Value;
		$intServiceId		= (DBO()->Service->Id->Value) ? DBO()->Service->Id->Value : "null";
		$intCategoryFilter	= DBO()->History->CategoryFilter->Value;
		$intTypeFilter		= (DBO()->History->TypeFilter->Value) ? DBO()->History->TypeFilter->Value : "null";
		$intMaxItems		= DBO()->History->MaxItems->Value;
		$strJavascript	= "	if (Vixen.$strObjectName == undefined)
							{
								Vixen.$strObjectName = new VixenProvisioningHistoryClass;
							}
							Vixen.$strObjectName.Initialise($intAccountId, $intServiceId, $intCategoryFilter, $intTypeFilter, $intMaxItems, '$strHistoryContainerDivId', true);
						";
							
		echo "<script type='text/javascript'>$strJavascript</script>\n";
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
		$strObjectName = "ProvisioningHistoryPopup". ((DBO()->Service->Id->IsSet)? DBO()->Service->Id->Value : "");
		
		$this->_RenderFilterControls($strObjectName);
	
		// Render the History
		$strHistoryContainerDivId = "HistoryContainerForPopup". ((DBO()->Service->Id->IsSet)? DBO()->Service->Id->Value : "");
		echo "<div id='ContainerDiv_ScrollableDiv_History' style='border: solid 1px #606060; padding: 5px 5px 5px 5px'>\n";
		echo "<div id='ScrollableDiv_History' style='overflow:auto; height:410px; width:auto; padding: 0px 3px 0px 3px'>\n";
		echo "<div id='$strHistoryContainerDivId'>\n";
		$this->_RenderHistory($strObjectName);
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
		$strJavascript	= "	if (Vixen.$strObjectName == undefined)
							{
								Vixen.$strObjectName = new VixenProvisioningHistoryClass;
							}
							Vixen.$strObjectName.Initialise($intAccountId, $intServiceId, $intCategoryFilter, $intTypeFilter, $intMaxItems, '$strHistoryContainerDivId', false, '$strPopupId');
						";
							
		echo "<script type='text/javascript'>$strJavascript</script>\n";
	}


	//------------------------------------------------------------------------//
	// _RenderFilterControls
	//------------------------------------------------------------------------//
	/**
	 * _RenderFilterControls
	 *
	 * Renders the filter controls
	 *
	 * Renders the filter controls
	 *
	 * @param	string	$strObjectName	name of the javascript VixenProvisioningHistoryClass object
	 * 									which facilitates the provisioning history  
	 *
	 * @return	void
	 * @method
	 */
	private function _RenderFilterControls($strObjectName)
	{
		$arrCatFilterOptions = Array(	PROVISIONING_HISTORY_CATEGORY_BOTH		=> "Show All",
										PROVISIONING_HISTORY_CATEGORY_REQUESTS	=> "Requests",
										PROVISIONING_HISTORY_CATEGORY_RESPONSES	=> "Responses");

		$arrTypeFilterOptions = Array(	PROVISIONING_HISTORY_FILTER_ALL				=> "Show All",
										PROVISIONING_HISTORY_FILTER_BARRINGS_ONLY	=> "Barrings Only"); 
		
		$arrMaxItems = Array(10 => "10", 50 => "50", 100 => "100", 200 => "200", 500 => "500", 0 => "Show All");

		// Render filtering controls
		echo "<div class='GroupedContent' >";
		echo "<div style='height:25px'>";
		echo "<div class='Left'>";
		
		// Create a combobox containing all the Category filter options
		echo "<span>Category</span>\n";
		echo "<span>\n";
		echo "   <select id='ProvHistoryCategoryCombo' onChange='Vixen.$strObjectName.intCategoryFilter = this.value;' style='border:solid 1px #D1D1D1'>\n";
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
		echo "   <select id='ProvHistoryTypeCombo' onChange='Vixen.$strObjectName.intTypeFilter = this.value;' style='border:solid 1px #D1D1D1'>\n";
		foreach ($arrTypeFilterOptions as $intOption=>$strDescription)
		{
			$strSelected = (DBO()->History->TypeFilter->Value == $intOption) ? "selected='selected'" : "";
			echo "      <option $strSelected value='$intOption'>$strDescription</option>\n";
		}
		DBL()->provisioning_type->Where->SetString("TRUE");
		DBL()->provisioning_type->SetColumns("id, name");
		DBL()->provisioning_type->OrderBy("name");
		DBL()->provisioning_type->Load();
		
		foreach (DBL()->provisioning_type as $dboProvisioningType)
		{
			$strSelected = (DBO()->History->TypeFilter->Value == $dboProvisioningType->id->Value) ? "selected='selected'" : "";
			echo "      <option $strSelected value='{$dboProvisioningType->id->Value}'>{$dboProvisioningType->name->Value}</option>\n";
		}
		echo "   </select>\n";
		echo "</span>\n";
		
		// Create a combobox containing all the MaxItems options
		echo "<span style='margin-left:20px'>Max Items</span>\n";
		echo "<span>\n";
		echo "   <select id='ProvHistoryMaxItemsCombo' onChange='Vixen.$strObjectName.intMaxItems = this.value;' style='border:solid 1px #D1D1D1'>\n";
		foreach ($arrMaxItems as $intOption=>$strDescription)
		{
			$strSelected = (DBO()->History->MaxItems->Value == $intOption) ? "selected='selected'" : "";
			echo "      <option $strSelected value='$intOption'>$strDescription</option>\n";
		}
		echo "   </select>\n";
		echo "</span>\n";

		echo "</div>\n"; //Left
		echo "<div class='Right'>\n";
		$this->Button("Filter", "Vixen.$strObjectName.ApplyFilter(true);");
		echo "</div>\n"; //Right
		echo "</div>\n"; //height=25px
		echo "</div>\n"; // GroupedContent
		
		echo "<div class='TinySeperator' style='clear:both'></div>\n";
		
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
	private function _RenderHistory($strObjectName)
	{
		$bolUserHasOperatorPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		
		$arrHistory = DBO()->History->Records->Value;
		
		$bolForServiceOnly = (DBO()->Service->Id->Value)? TRUE : FALSE;
		
		if ($bolForServiceOnly)
		{
			Table()->History->SetHeader("&nbsp;", "Date", "Carrier", "Type", "Status");
			Table()->History->SetWidth("3%", "10%", "10%", "20%", "57%");
			Table()->History->SetAlignment("Center", "Left", "Left", "Left", "Left");
		}
		else
		{
			Table()->History->SetHeader("&nbsp;", "Date", "Service", "Carrier", "Type", "Status");
			Table()->History->SetWidth("3%", "10%", "10", "10%", "20%", "47%");
			Table()->History->SetAlignment("Center", "Left", "Left", "Left", "Left", "Left");
		}

		foreach ($arrHistory as $arrRecord)
		{
			if ($arrRecord['Outbound'])
			{
				//$strOutboundCell = "<img src='img/template/outbound.png' />";
				$strOutboundCell	= "O";
				$strStatusCell		= GetConstantDescription($arrRecord['Status'], "provisioning_request_status");
				
				if ($arrRecord['Status'] == REQUEST_STATUS_WAITING && $bolUserHasOperatorPerm)
				{
					// The request has not been sent yet.  It can be cancelled
					$strCancel = "javascript:Vixen.$strObjectName.CancelProvisioningRequest({$arrRecord['Id']});";
					$strStatusCell .= "&nbsp;<a href='$strCancel'>(Cancel)</a>";
				}
				
				// Include the description if there is one
				if ($arrRecord['Description'] != "")
				{
					$strStatusCell .= " - ". $arrRecord['Description'];
				}
			}
			else
			{
				//$strOutboundCell	= "<img src='img/template/inbound.png' />";
				$strOutboundCell	= "I";
				$strStatusCell		= $arrRecord['Description'];
			}
			
			// Build the TimeStamp field
			$strTimeStampCell	= date("j M y H:i:s", strtotime($arrRecord['TimeStamp']));
			
			$strRequestType	= GetConstantDescription($arrRecord['Type'], "provisioning_type");
			$strCarrier		= GetConstantDescription($arrRecord['Carrier'], "Carrier");
			
			$strDescription = htmlspecialchars($arrRecord['Description'], ENT_QUOTES);
			
			if ($arrRecord['Employee'] != NULL)
			{
				$strEmployee = htmlspecialchars(GetEmployeeName($arrRecord['Employee']), ENT_QUOTES);
				$strRequestType = "<span title='Requested by $strEmployee'>$strRequestType</span>";
			}
			
			if ($bolForServiceOnly)
			{
				Table()->History->AddRow($strOutboundCell, $strTimeStampCell, $strCarrier, $strRequestType, $strStatusCell);
			}
			else
			{
				
				/*// Have a link to the Service's individual Provisioning history from the FNN, if the service is known for this record
				if ($arrRecord['Service'] != NULL)
				{
					$strServiceProvHistoryLink = Href()->ViewProvisioningHistory($arrRecord['Service']);
					$strFnnCell = "<a href='$strServiceProvHistoryLink' title='View History'>{$arrRecord['FNN']}</a>";
				}
				else
				{
					$strFnnCell = $arrRecord['FNN'];
				}*/

				// Have a link to the Service Details page from the FNN, if the service is known for this record
				if ($arrRecord['Service'] != NULL)
				{
					$strServiceLink	= Href()->ViewService($arrRecord['Service']);
					$strFnnCell		= "<a href='$strServiceLink' title='View Service'>{$arrRecord['FNN']}</a>";
				}
				else
				{
					$strFnnCell = $arrRecord['FNN'];
				}
				
				Table()->History->AddRow($strOutboundCell, $strTimeStampCell, $strFnnCell, $strCarrier, $strRequestType, $strStatusCell);
			}
		}
		
		if (count($arrHistory) == 0)
		{
			// There are no invoices to stick in this table
			Table()->History->AddRow("No records to display");
			Table()->History->SetRowAlignment("left");
			$intColumns = ($bolForServiceOnly)? 5 : 6;
			Table()->History->SetRowColumnSpan($intColumns);
		}
		
		Table()->History->Render();
	}
}

?>
