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
				//$this->RenderTable();
$this->RenderTableNewWay();
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
		//$this->RenderTable();
$this->RenderTableNewWay();
		
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
		echo "<h2 class='Services'>Services</h2>\n";
		
		// Draw the table
		echo "<div id='$strTableContainerDivId'>\n";
		//$this->RenderTable();
$this->RenderTableNewWay();
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
		// Set up the objects required to find the current plan and future plan of a service
		DBO()->CurrentRatePlan->SetTable("RatePlan");
		DBO()->FutureRatePlan->SetTable("RatePlan");
	
		$bolUserHasOperatorPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);

		Table()->ServiceTable->SetHeader("&nbsp;", "FNN #", "Plan", "&nbsp;", "&nbsp;", "&nbsp;", "Actions");
		Table()->ServiceTable->SetWidth("3%", "10%", "46%", "7%", "11%", "15%", "8%");
		Table()->ServiceTable->SetAlignment("Left", "Left", "Left", "Right", "Left", "Left", "Left");
		
		$strStatusTitles = "Status :<br />Line :";
		
		foreach (DBL()->Service as $dboService)
		{
			// Build the Actions Cell
			$strEditService				= "";
			$strChangePlan				= "";
			$strProvisioning			= "";
			$strViewProvisioningHistory	= "";
			if ($bolUserHasOperatorPerm)
			{
				// The user can edit stuff
				$strEditServiceLink			= Href()->EditService($dboService->Id->Value);
				$strEditService 			= "<a href='$strEditServiceLink' title='Edit Service'><img src='img/template/edit.png'></img></a>";
				
				$strChangePlanLink			= Href()->ChangePlan($dboService->Id->Value);
				$strChangePlan 				= "<a href='$strChangePlanLink' title='Change Plan'><img src='img/template/plan.png'></img></a>";
	
				// Include a button for provisioning, if the service is a landline
				if ($dboService->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
				{
					$strProvisioningLink	= Href()->Provisioning($dboService->Id->Value);
					$strProvisioning		= "<a href='$strProvisioningLink' title='Provisioning'><img src='img/template/provisioning.png'></img></a>";
					
					$strViewProvisioningHistoryLink = Href()->ViewProvisioningHistory($dboService->Id->Value);
					$strViewProvisioningHistory		= "<a href='$strViewProvisioningHistoryLink' title='View Provisioning History'><img src='img/template/provisioning_history.png'></img></a>";
				}
			}
			
			$strViewServiceNotesLink	= Href()->ViewServiceNotes($dboService->Id->Value);
			$strViewServiceNotes 		= "<a href='$strViewServiceNotesLink' title='View Service Notes'><img src='img/template/note.png'></img></a>";
			
			$strViewUnbilledChargesLink = Href()->ViewUnbilledCharges($dboService->Id->Value);
			$strViewUnbilledCharges 	= "<a href='$strViewUnbilledChargesLink' title='View Unbilled Charges'><img src='img/template/cdr.png'></img></a>";
			
			$strActionsCell				= "$strViewServiceNotes $strEditService $strChangePlan $strViewUnbilledCharges $strProvisioning $strViewProvisioningHistory";

			// Create a link to the View Plan for Service page
			$strViewServiceRatePlanLink = Href()->ViewServiceRatePlan($dboService->Id->Value);

			// Find the current plan for the service (if there is one)
			DBO()->CurrentRatePlan->Id = GetCurrentPlan($dboService->Id->Value);
			if (DBO()->CurrentRatePlan->Id->Value)
			{
				// A plan was found
				DBO()->CurrentRatePlan->Load();
				$strCurrentPlan = DBO()->CurrentRatePlan->Name->Value;
				
				$strPlanCell = "<span><a href='$strViewServiceRatePlanLink' title='View Service Specific Plan'>$strCurrentPlan</a></span>";
			}
			else
			{
				// There is no current plan for the service
				$strPlanCell = "<span class='Red'>No Plan Selected</span>";
			}
			
			// Find the future scheduled plan for the service (if there is one)
			DBO()->FutureRatePlan->Id = GetPlanScheduledForNextBillingPeriod($dboService->Id->Value);
			if (DBO()->FutureRatePlan->Id->Value)
			{
				// A plan has been found, which is scheduled to start for the next billing period
				DBO()->FutureRatePlan->Load();
				
				$strFuturePlan = DBO()->FutureRatePlan->Name->Value;
				$strPlanCell .= "<br /><span>As of next billing period : <a href='$strViewServiceRatePlanLink' title='View Service Specific Plan'>$strFuturePlan</a></span>";
			}
			

			// Work out the Date to display along with the status
			$intClosedOn = strtotime($dboService->ClosedOn->Value);
			$intCurrentDate = strtotime(GetCurrentDateForMySQL());
			
			// Check if the ClosedOn date has been set
			$bolFlagStatus = FALSE;
			if ($dboService->ClosedOn->Value == NULL)
			{
				// The service is not scheduled to close.  It is either active or hasn't been activated yet.
				// Check if it is currently active
				$intCreatedOn = strtotime($dboService->CreatedOn->Value);
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
				$strStatusDescDate = OutputMask()->ShortDate($dboService->CreatedOn->Value);
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
				$strStatusDescDate = OutputMask()->ShortDate($dboService->ClosedOn->Value);
			}

			// Prepare the Status' of the service
			$strStatus			= GetConstantDescription($dboService->Status->Value, "Service");
			$strLineStatus		= GetConstantDescription($dboService->LineStatus->Value, "LineStatus");
			$strLineStatusDate	= $dboService->LineStatusDate->Value;

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
			
			$strViewServiceLink = Href()->ViewService($dboService->Id->Value);
			
			if ($dboService->FNN->Value == NULL)
			{
				// The service doesn't have an FNN yet
				$strFnnDescription = "[not specified]";
			}
			else
			{
				// The service has an FNN
				$strFnnDescription = $dboService->FNN->Value;
			}

			$strFnnCell = "<a href='$strViewServiceLink' title='View Service Details'>$strFnnDescription</a>";
			
			//$strServiceType = GetConstantDescription($dboService->ServiceType->Value, "ServiceType");
			
			switch ($dboService->ServiceType->Value)
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
			
			Table()->ServiceTable->AddRow($strServiceTypeCell, $strFnnCell,	$strPlanCell, $strStatusTitles, $strStatusCell, $strStatusDescCell, $strActionsCell);
		}
		
		// If the account has no services then output an appropriate message in the table
		if (Table()->ServiceTable->RowCount() == 0)
		{
			// There are no services to stick in this table
			Table()->ServiceTable->AddRow("No services to display");
			Table()->ServiceTable->SetRowAlignment("left");
			Table()->ServiceTable->SetRowColumnSpan(7);
		}
		
		// Row highlighting doesn't seem to be working with popups
		// Row highlighting has been turned off, because it stops working if the Service table is ever redrawn
		Table()->ServiceTable->RowHighlighting = TRUE;
		Table()->ServiceTable->Render();
	}
	
	//------------------------------------------------------------------------//
	// RenderTableNewWay
	//------------------------------------------------------------------------//
	/**
	 * RenderTableNewWay()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderTableNewWay()
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
					$strViewProvisioningHisotry		= "<img src='img/template/provisioning_history.png' title='View Provisioning History' onclick='$strViewProvisioningHistoryLink' style='cursor:pointer'/>";
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
			
			$strHistoryDetails = $this->_GetHistory($arrService);
			
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
	
	// Builds a HTML table detailing the history of a service (activations/deactivations)
	private function _GetHistory($arrService)
	{
return "Insert History here";		
		$intLastRecordIndex = count($arrService['History']) - 1;
		$strRows = "";
		foreach ($arrService['History'] as $intIndex=>$arrHistoryItem)
		{
			$strCreatedBy			= GetEmployeeName($arrHistoryItem['CreatedBy']) ." (". GetEmployeeUserName($arrHistoryItem['CreatedBy']) .")";
			$strCreatedOn			= OutputMask()->ShortDate($arrHistoryItem['CreatedOn']);
			$strNatureOfCreation	= ($intIndex == $intLastRecordIndex)? "Created" : "Activated";
			/*
			if ($arrHistoryItem['ClosedOn'] != NULL)
			{
				$strClosedBy	= ($arrHistoryItem['ClosedBy'] != NULL)? $strClosedBy = GetEmployeeName($arrHistoryItem['ClosedBy']) ." (". GetEmployeeUserName($arrHistoryItem['ClosedBy']) .")" : "";
				$strClosedOn	= ($arrHistoryItem['ClosedOn'] != NULL)? OutputMask()->ShortDate($arrHistoryItem['ClosedOn']) : "";
			
				$strNatureOfClosure		= ($arrHistoryItem['ClosedOn'] == )
				
			}
			*/
			
		}
	}
	
}

?>
