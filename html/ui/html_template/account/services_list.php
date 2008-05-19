<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// services_list.php
//----------------------------------------------------------------------------//
/**
 * services_list
 *
 * HTML Template for the Account Services table
 *
 * HTML Template for the Account Services table
 * This file defines the class responsible for defining and rendering the layout
 * of the HTML Template used by the Account Services popup
 *
 * @file		services_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Ross, Joel
 * @version		7.09
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// HtmlTemplateAccountServicesList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountServicesList
 *
 * HTML Template object defining the presentation of the Account Services table
 *
 * HTML Template object defining the presentation of the Account Services table
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountServicesList
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountServicesList extends HtmlTemplate
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
		
		$this->LoadJavascript("account_services");
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("retractable");
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function Render()
	{
		// Declare the id of the container div for the VixenTable which displays all the services
		$strTableContainerDivId = "AccountServicesTableDiv";
		
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_POPUP:
				$this->RenderPopup($strTableContainerDivId);
				break;
			case HTML_CONTEXT_PAGE:
				$this->RenderInPage($strTableContainerDivId);
				break;
			default:
				$this->RenderTable();
		}
	}
	
	//------------------------------------------------------------------------//
	// RenderPopup
	//------------------------------------------------------------------------//
	/**
	 * RenderPopup()
	 *
	 * Render this HTML Template for use in a popup.  Includes a close button
	 *
	 * Render this HTML Template for use in a popup.  Includes a close button
	 *
	 * @param	string	$strTableContainerDivId		The id for the container div for the VixenTable
	 *
	 * @method
	 */
	function RenderPopup($strTableContainerDivId)
	{
		echo "<div class='PopupLarge'>\n";
		
		// Work out if a virtical scroll bar will be required
		$strTableContainerStyle = (DBL()->Service->RecordCount() > 14) ? "style='overflow:auto; height:450px'": "";
		
		// Draw the table container
		echo "<div id='$strTableContainerDivId' $strTableContainerStyle>\n";
		
		// Draw the table
		$this->RenderTable();
		
		echo "</div>\n";  // Table Container
	
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			$strBulkAddServiceLink = Href()->AddServices(DBO()->Account->Id->Value);
			$this->Button("Add Services", "window.location='$strBulkAddServiceLink'");
		}
		$this->Button("Close", "Vixen.Popup.Close(this);");
		echo "</div></div>\n";

		echo "</div>\n";  //PopupLarge
		
		// Initialise the javascript object that facilitates this popup (Vixen.AccountServices)
		echo "<script type='text/javascript'>Vixen.AccountServices.Initialise('{$this->_objAjax->strId}')</script>";
	}
	
	//------------------------------------------------------------------------//
	// RenderInPage
	//------------------------------------------------------------------------//
	/**
	 * RenderInPage()
	 *
	 * Render this HTML Template for use in a page.  
	 *
	 * Render this HTML Template for use in a page.
	 *
	 * @param	string	$strTableContainerDivId		The id for the container div for the VixenTable
	 *
	 * @method
	 */
	function RenderInPage($strTableContainerDivId)
	{
		echo "
<div style='width:100%;height:auto'>
	<h2 class='Services' style='float:left'>Services</h2>
			
	<select id='ServicesListFilterCombo' style='float:left;margin-left:20px' onChange='Vixen.AccountServices.ReloadList(true)'>
		<option value='0'>Show All</option>
		<option value='". SERVICE_ACTIVE ."' selected='selected'>Active Only</option>
		<option value='". SERVICE_DISCONNECTED ."'>Disconnected</option>
		<option value='". SERVICE_ARCHIVED ."'>Archived</option>
	</select>
</div>
<div style='clear:both'></div>";

		// Draw the table
		echo "<div id='$strTableContainerDivId'>\n";
		$this->RenderTable();

		echo "</div>\n";
		
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			$strBulkAddServiceLink = Href()->AddServices(DBO()->Account->Id->Value);
			$this->Button("Add Services", "window.location = \"$strBulkAddServiceLink\"");
		}
		echo "</div></div>\n";
		
		$intAccountId = DBO()->Account->Id->Value;
		// Initialise the javascript object that facilitates this HtmlTemplate
		echo "<script type='text/javascript'>Vixen.AccountServices.Initialise($intAccountId, null, '$strTableContainerDivId')</script>\n";
	}
	
	//------------------------------------------------------------------------//
	// RenderTable
	//------------------------------------------------------------------------//
	/**
	 * RenderTable()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderTable()
	{
		$bolUserHasOperatorPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		$arrServices			= DBO()->Account->Services->Value;
		
		Table()->Services->SetHeader("&nbsp;", "FNN #", "Plan", "&nbsp;", "&nbsp;", "&nbsp;", "Actions");
		Table()->Services->SetWidth("3%", "10%", "46%", "7%", "11%", "15%", "8%");
		Table()->Services->SetAlignment("Left", "Left", "Left", "Right", "Left", "Left", "Left");
		
		$strStatusTitles = "Status :<br />Line :";
		
		foreach ($arrServices as $arrService)
		{
			// Build the Actions Cell
			$strEditService				= "";
			$strChangePlan				= "";
			$strProvisioning			= "";
			$strViewProvisioningHistory	= "";
			if ($bolUserHasOperatorPerm)
			{
				// The user can edit stuff
				$strEditServiceLink	= Href()->EditService($arrService['Id']);
				$strEditService		= "<img src='img/template/edit.png' title='Edit Service' onclick='$strEditServiceLink' style='cursor:pointer'/>";
				
				$strChangePlanLink	= Href()->ChangePlan($arrService['Id']);
				$strChangePlan		= "<img src='img/template/plan.png' title='Change Plan' onclick='$strChangePlanLink' style='cursor:pointer'/>";
	
				// Include a button for provisioning, if the service is a landline
				if ($arrService['ServiceType'] == SERVICE_TYPE_LAND_LINE)
				{
					$strProvisioningLink	= Href()->Provisioning($arrService['Id']);
					$strProvisioning		= "<a href='$strProvisioningLink' title='Provisioning'><img src='img/template/provisioning.png'></img></a>";
					
					$strViewProvisioningHistoryLink = Href()->ViewProvisioningHistory($arrService['Id']);
					$strViewProvisioningHistory		= "<img src='img/template/provisioning_history.png' title='View Provisioning History' onclick='$strViewProvisioningHistoryLink' style='cursor:pointer'/>";
				}
			}
			
			$strViewServiceNotesLink	= Href()->ViewServiceNotes($arrService['Id']);
			$strViewServiceNotes		= "<img src='img/template/note.png' title='View Notes' onclick='$strViewServiceNotesLink' style='cursor:pointer'/>";
			
			$strViewUnbilledChargesLink = Href()->ViewUnbilledCharges($arrService['Id']);
			$strViewUnbilledCharges 	= "<a href='$strViewUnbilledChargesLink' title='View Unbilled Charges'><img src='img/template/cdr.png'></img></a>";
			
			$strActionsCell				= "$strViewServiceNotes $strEditService $strChangePlan $strViewUnbilledCharges $strProvisioning $strViewProvisioningHistory";

			// Create a link to the View Plan for Service page
			$strViewServiceRatePlanLink = Href()->ViewServiceRatePlan($arrService['Id']);

			if ($arrService['CurrentPlan'] != NULL)
			{
				// The Service has a current plan
				$strPlanCell = "<a href='$strViewServiceRatePlanLink' title='View Service Specific Plan'>{$arrService['CurrentPlan']['Name']}</a>";
			}
			else
			{
				// There is no current plan for the service
				$strPlanCell = "<span class='Red'>No Plan Selected</span>";
			}
			
			if ($arrService['FuturePlan'] != NULL)
			{
				$strStartDate = OutputMask()->ShortDate($arrService['FuturePlan']['StartDatetime']); 
				$strPlanCell .= "<br />As from $strStartDate : <a href='$strViewServiceRatePlanLink' title='View Service Specific Plan'>{$arrService['FuturePlan']['Name']}</a>";
			}
			
			// Work out the Date to display along with the status
			$intClosedOn	= strtotime($arrService['History'][0]['ClosedOn']);
			$intCurrentDate	= strtotime(GetCurrentDateForMySQL());
			
			// Check if the ClosedOn date has been set
			$bolFlagStatus = FALSE;
			if ($arrService['History'][0]['ClosedOn'] == NULL)
			{
				// The service is not scheduled to close.  It is either active or hasn't been activated yet.
				// Check if it is currently active
				$intCreatedOn = strtotime($arrService['History'][0]['CreatedOn']);
				if ($intCurrentDate >= $intCreatedOn)
				{
					// The service is currently active
					$strStatusDesc = "Opened";
				}
				else
				{
					// This service hasn't been activated yet (change of lessee has been scheduled at a future date)
					$strStatusDesc = "Opens";
					$bolFlagStatus = TRUE;
				}
				$strStatusDescDate = OutputMask()->ShortDate($arrService['History'][0]['CreatedOn']);
			}
			else
			{
				// The service has a ClosedOn date; check if it is in the future or past
				if ($intClosedOn >= $intCurrentDate)
				{
					// The service is scheduled to be closed in the future (change of lessee has been scheduled at a future date) or today
					$strStatusDesc = "Closes";
					$bolFlagStatus = TRUE;
				}
				else
				{
					// The service has been closed
					$strStatusDesc = "Closed";
				}
				$strStatusDescDate = OutputMask()->ShortDate($arrService['History'][0]['ClosedOn']);
			}

			// Prepare the Status' of the service
			$strStatus			= GetConstantDescription($arrService['History'][0]['Status'], "Service");
			$strLineStatus		= GetConstantDescription($arrService['History'][0]['LineStatus'], "LineStatus");
			$strLineStatusDate	= $arrService['History'][0]['LineStatusDate'];

			$strLineStatusDesc = NULL;
			if ($strLineStatus === FALSE)
			{
				// The line status is unknown
				$strLineStatus = "Unknown";
			}
			elseif ($strLineStatusDate != NULL)
			{
				// LineStatus Date has been supplied
				$strLineStatusDate = substr($strLineStatusDate, 11, 8) ." ". substr($strLineStatusDate, 8, 2) ."/". substr($strLineStatusDate, 5, 2) ."/". substr($strLineStatusDate, 0, 4);
				$strLineStatusDesc = "Line Status was last updated: $strLineStatusDate";
			}

			$strStatusCell = "$strStatus<br />$strLineStatus";
			if ($strLineStatusDesc)
			{
				$strStatusCell = "<span title='$strLineStatusDesc'>$strStatusCell</span>";
			}
			
			$strStatusDescCell = "$strStatusDesc $strStatusDescDate";
			if ($bolFlagStatus)
			{
				$strStatusDescCell = "<span class='Red'>$strStatusDescCell</span>";
			}
			
			$strViewServiceLink	= Href()->ViewService($arrService['Id']);
			$strFnnDescription	= ($arrService['FNN'] != NULL)? $arrService['FNN'] : "[not specified]";
			$strFnnCell			= "<a href='$strViewServiceLink' title='View Service Details'>$strFnnDescription</a>";
			
			
			switch ($arrService['ServiceType'])
			{
				case SERVICE_TYPE_MOBILE:
					$strServiceTypeClass = "ServiceTypeIconMobile";
					break;
				case SERVICE_TYPE_LAND_LINE:
					$strServiceTypeClass = "ServiceTypeIconLandLine";
					break;
				case SERVICE_TYPE_ADSL:
					$strServiceTypeClass = "ServiceTypeIconADSL";
					break;
				case SERVICE_TYPE_INBOUND:
					$strServiceTypeClass = "ServiceTypeIconInbound";
					break;
				default:
					$strServiceTypeClass = "ServiceTypeIconBlank";
					break;
			}
			
			$strServiceTypeCell = "<div class='$strServiceTypeClass'></div>";

			$strHistoryDetails = HtmlTemplateServiceHistory::GetHistory($arrService['History']);
			
			
			Table()->Services->AddRow($strServiceTypeCell, $strFnnCell,	$strPlanCell, $strStatusTitles, $strStatusCell, $strStatusDescCell, $strActionsCell);
			Table()->Services->SetDetail($strHistoryDetails);
		}
		
		// If the account has no services then output an appropriate message in the table
		if (Table()->Services->RowCount() == 0)
		{
			// There are no services to stick in this table
			Table()->Services->AddRow("No services to display");
			Table()->Services->SetRowAlignment("left");
			Table()->Services->SetRowColumnSpan(7);
		}
		
		// Row highlighting doesn't seem to be working with popups
		// Row highlighting has been turned off, because it stops working if the Service table is ever redrawn
		Table()->Services->RowHighlighting = TRUE;
		Table()->Services->Render();
	}
}

?>
