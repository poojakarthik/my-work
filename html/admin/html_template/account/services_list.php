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
		$bolUserHasOperatorPerm		= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		$arrServices				= DBO()->Account->Services->Value;
		$intCurrentDate				= strtotime(GetCurrentISODate());
		$bolUserIsTicketingUser 	= Ticketing_User::currentUserIsTicketingUser();
		$intAccountId				= DBO()->Account->Id->Value;
		$bolTicketingModuleIsActive	= Flex_Module::isActive(FLEX_MODULE_TICKETING);
		$arrAllowableServiceStatusesForTickets = array(SERVICE_ACTIVE, SERVICE_PENDING, SERVICE_DISCONNECTED);
		
		
		Table()->Services->SetHeader("&nbsp;", "FNN", "Plan", "&nbsp;", "&nbsp;", "Actions");
		
		Table()->Services->SetAlignment("Left", "Left", "Left", "Right", "Left", "Left");
		
		$strStatusTitles = "Status :<br />Line :";
		
		foreach ($arrServices as $arrService)
		{
			// Build the Actions Cell
			$strEditService				= "";
			$strChangePlan				= "";
			$strProvisioning			= "";
			$strViewProvisioningHistory	= "";
			$strCreateTicket			= "";
			$strViewAddress				= "";
			if ($bolUserHasOperatorPerm)
			{
				// The user can edit stuff
				$strEditServiceLink	= Href()->EditService($arrService['Id']);
				$strEditService		= "<img src='img/template/edit.png' title='Edit Service' onclick='$strEditServiceLink'/>";
				
				$strChangePlanLink	= Href()->ChangePlan($arrService['Id']);
				$strChangePlan		= "<img src='img/template/plan.png' title='Change Plan' onclick='$strChangePlanLink'/>";
				
				$strMoveServiceLink	= Href()->MoveService($arrService['Id']);
				$strMoveService		= "<img src='img/template/move.png' title='Move Service' onclick='$strMoveServiceLink'/>";
	
				// Include a button for provisioning, if the service is a landline
				if ($arrService['ServiceType'] == SERVICE_TYPE_LAND_LINE)
				{
					$strProvisioningLink	= Href()->Provisioning($arrService['Id']);
					$strProvisioning		= "<a href='$strProvisioningLink' title='Provisioning'><img src='img/template/provisioning.png'></img></a>";
					
					$strViewProvisioningHistoryLink = Href()->ViewProvisioningHistory($arrService['Id']);
					$strViewProvisioningHistory		= "<img src='img/template/provisioning_history.png' title='View Provisioning History' onclick='$strViewProvisioningHistoryLink'/>";
					
					$strViewAddressLink			= Href()->ViewServiceAddress($arrService['Id']);
					$strViewAddress				= "<img src='img/template/address.png' title='Address Details' onclick='$strViewAddressLink'/>";
				}
				
				if ($bolTicketingModuleIsActive && $bolUserIsTicketingUser && array_search($arrService['History'][0]['Status'], $arrAllowableServiceStatusesForTickets) !== FALSE)
				{
					$strCreateTicket = "<a href='". Href()->AddTicket($intAccountId, $arrService['Id']) ."' title='Create Ticket'><img src='img/template/create_ticket.png'></img></a>";
				}
			}
			
			$strPopupTitle = GetConstantDescription($arrService['ServiceType'], "service_type") ." - ". $arrService['FNN'];
			$strViewServiceNotesLink	= Href()->ActionsAndNotesListPopup(ACTION_ASSOCIATION_TYPE_SERVICE, $arrService['Id'], true, 99999, $strPopupTitle);
			$strViewServiceNotes		= "<img src='img/template/note.png' title='View Notes' onclick='$strViewServiceNotesLink'/>";
			
			$strViewUnbilledChargesLink = Href()->ViewUnbilledCharges($arrService['Id']);
			$strViewUnbilledCharges 	= "<a href='$strViewUnbilledChargesLink' title='View Unbilled Charges'><img src='img/template/cdr.png'></img></a>";
			
			
			
			
			$strActionsCell				= "{$strViewServiceNotes} {$strEditService} {$strChangePlan} {$strViewUnbilledCharges} {$strMoveService} {$strProvisioning} {$strViewProvisioningHistory} {$strViewAddress} {$strCreateTicket}";

			// Create a link to the View Plan for Service page
			$strViewServiceRatePlanLink = Href()->ViewServiceRatePlan($arrService['Id']);

			if ($arrService['CurrentPlan'] != NULL)
			{
				// The Service has a current plan
				$strPlanCell = "<a href='$strViewServiceRatePlanLink' title='View Service Specific Plan'>{$arrService['CurrentPlan']['Name']}</a>";
				
				if (Flex_Module::isActive(FLEX_MODULE_PLAN_BROCHURE))
				{
					if ($arrService['CurrentPlan']['brochure_document_id'])
					{
						$objBrochureDocument		= new Document(array('id'=>$arrService['CurrentPlan']['brochure_document_id']), true);
						$objBrochureDocumentContent	= $objBrochureDocument->getContentDetails();
						
						if ($objBrochureDocumentContent && $objBrochureDocumentContent->bolHasContent)
						{
							$objBrochureIcon			= new File_Type(array('id'=>$objBrochureDocumentContent->file_type_id), true);
							
							$strImageSrc		= "../admin/reflex.php/File/Image/FileTypeIcon/{$objBrochureIcon->id}/16x16";
							$strBrochureLink	= "../admin/reflex.php/File/Document/{$arrService['CurrentPlan']['brochure_document_id']}";
							$strPlanCell		.= " <a href='{$strBrochureLink}' title='Download Plan Brochure'><img src='{$strImageSrc}' alt='Download Plan Brochure' /></a>";
							
							$arrRatePlan		= $arrService['CurrentPlan'];
							$strEmailOnClick	= Rate_Plan::generateEmailButtonOnClick(DBO()->Account->CustomerGroup->Value, array($arrRatePlan), DBO()->Account->Id->Value);
							$strPlanCell		.= "&nbsp;<a onclick='{$strEmailOnClick}' title='Email Plan Brochure'><img src='../admin/img/template/pdf_email.png' alt='Email Plan Brochure' /></a>";
						}
					}
				}
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
				
				if (Flex_Module::isActive(FLEX_MODULE_PLAN_BROCHURE))
				{
					if ($arrService['FuturePlan']['brochure_document_id'])
					{
						$objBrochureDocument		= new Document(array('id'=>$arrService['FuturePlan']['brochure_document_id']), true);
						$objBrochureDocumentContent	= $objBrochureDocument->getContentDetails();
						
						if ($objBrochureDocumentContent && $objBrochureDocumentContent->bolHasContent)
						{
							$objBrochureIcon			= new File_Type(array('id'=>$objBrochureDocumentContent->file_type_id), true);
							
							$strImageSrc		= "../admin/reflex.php/File/Image/FileTypeIcon/{$objBrochureIcon->id}/16x16";
							$strBrochureLink	= "../admin/reflex.php/File/Document/{$arrService['FuturePlan']['brochure_document_id']}";
							$strPlanCell		.= " <a href='{$strBrochureLink}' title='Download Plan Brochure'><img src='{$strImageSrc}' alt='Download Plan Brochure' /></a>";
							
							$arrRatePlan		= $arrService['FuturePlan'];
							$strEmailOnClick	= Rate_Plan::generateEmailButtonOnClick(DBO()->Account->CustomerGroup->Value, array($arrRatePlan), DBO()->Account->Id->Value);
							$strPlanCell		.= "&nbsp;<a onclick='{$strEmailOnClick}' title='Email Plan Brochure'><img src='../admin/img/template/pdf_email.png' alt='Email Plan Brochure' /></a>";
						}
					}
				}
			}
			
			// This is no longer used (but they might want to use it again in the future)
			// Work out the Date to display along with the status
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
				$intClosedOn = strtotime($arrService['History'][0]['ClosedOn']);
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
			$strStatus			= GetConstantDescription($arrService['History'][0]['Status'], "service_status");
			$strLineStatus		= GetConstantDescription($arrService['History'][0]['LineStatus'], "service_line_status");
			$strLineStatusDate	= $arrService['History'][0]['LineStatusDate'];
			if ($arrService['History'][0]['Status'] == SERVICE_PENDING)
			{
				// Highlight the status
				$strStatus = "<span style='color:#FF0000'>$strStatus</span>";
			}
			
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
			$strIndial100Flag	= ($arrService['Indial100'])? " (Indial&nbsp;100&nbsp;range)" : "";
			$strFnnCell			= "<a href='$strViewServiceLink' title='View Service Details'>$strFnnDescription{$strIndial100Flag}</a>";
			
			
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

			$strHistoryDetailsTable = HtmlTemplateServiceHistory::GetHistoryForTableDropDownDetail($arrService['History']);
			$strDropDownDetail = "
<div style='width:100%;height:100%;background-color: #D4D4D4'>
	<div style='width:70%;float:left'>
		$strHistoryDetailsTable
	</div>
</div>
";
			
			Table()->Services->AddRow($strServiceTypeCell, $strFnnCell,	$strPlanCell, $strStatusTitles, $strStatusCell, $strActionsCell);
			Table()->Services->SetDetail($strDropDownDetail);
		}
		
		// If the account has no services then output an appropriate message in the table
		if (Table()->Services->RowCount() == 0)
		{
			// There are no services to stick in this table
			Table()->Services->AddRow("No services to display");
			Table()->Services->SetRowAlignment("left");
			Table()->Services->SetRowColumnSpan(6);
		}
		
		// Row highlighting doesn't seem to be working with popups
		// Row highlighting has been turned off, because it stops working if the Service table is ever redrawn
		Table()->Services->RowHighlighting = TRUE;
		Table()->Services->Render();
	}
}

?>
