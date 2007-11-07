<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_services.php
//----------------------------------------------------------------------------//
/**
 * account_services
 *
 * HTML Template for the Account Services popup
 *
 * HTML Template for the Account Services popup
 * This file defines the class responsible for defining and rendering the layout
 * of the HTML Template used by the Account Services popup
 *
 * @file		account_services.php
 * @language	PHP
 * @package		ui_app
 * @author		Ross, Joel
 * @version		7.09
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// HtmlTemplateAccountServices
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountServices
 *
 * HTML Template object defining the presentation of the Account Services popup
 *
 * HTML Template object defining the presentation of the Account Services popup
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountServices
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountServices extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	public $_intContext;

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
		$strBulkAddServiceLink = Href()->AddServices(DBO()->Account->Id->Value);
		$this->Button("Add Services", "window.location='$strBulkAddServiceLink'");
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
		$this->RenderTable();
		echo "</div>\n";
		
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$strBulkAddServiceLink = Href()->AddServices(DBO()->Account->Id->Value);
		$this->Button("Add Services", $strBulkAddServiceLink);
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
		Table()->ServiceTable->SetHeader("FNN #", "Service", "Plan", "Status", "&nbsp;", "&nbsp;", "Actions");
		Table()->ServiceTable->SetWidth("11%", "10%", "39%", "11%", "7%", "10%", "12%");
		Table()->ServiceTable->SetAlignment("Left", "Left", "Left", "Left", "Left", "Left", "Left");
		
		foreach (DBL()->Service as $dboService)
		{
			// Record the Status of the service
			$strStatusCell = $dboService->Status->AsCallBack("GetConstantDescription", Array("Service"));

			// Build the Actions Cell
			$strViewServiceNotesLink	= Href()->ViewServiceNotes($dboService->Id->Value);
			$strViewServiceNotes 		= "<a href='$strViewServiceNotesLink' title='View Service Notes'><img src='img/template/note.png'></img></a>";
			
			$strEditServiceLink			= Href()->EditService($dboService->Id->Value);
			$strEditService 			= "<a href='$strEditServiceLink' title='Edit Service'><img src='img/template/edit.png'></img></a>";
			
			$strChangePlanLink			= Href()->ChangePlan($dboService->Id->Value);
			$strChangePlan 				= "<a href='$strChangePlanLink' title='Change Plan'><img src='img/template/plan.png'></img></a>";
			
			$strViewUnbilledChargesLink = Href()->ViewUnbilledCharges($dboService->Id->Value);
			$strViewUnbilledCharges 	= "<a href='$strViewUnbilledChargesLink' title='View Unbilled Charges'><img src='img/template/cdr.png'></img></a>";
			
			// Include a button for provisioning, if the service is a landline
			if ($dboService->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
			{
				$strProvisioningLink	= Href()->Provisioning($dboService->Id->Value);
				$strProvisioning		= "<a href='$strProvisioningLink' title='Provisioning'><img src='img/template/provisioning.png'></img></a>";
			}
			else
			{
				// The service is not a landline
				$strProvisioning = "";
			}
			
			$strActionsCell				= "<span>$strViewServiceNotes $strEditService $strChangePlan $strViewUnbilledCharges $strProvisioning</span>";

			// Find the current plan for the service
			$mixCurrentPlanId = GetCurrentPlan($dboService->Id->Value);
			if ($mixCurrentPlanId !== FALSE)
			{
				// A plan was found
				DBO()->RatePlan->Id = $mixCurrentPlanId;
				DBO()->RatePlan->Load();
				$strPlan = DBO()->RatePlan->Name->AsValue();
				
				// Create a link to the View Plan for Service popup (although this currently isn't a popup)
				$strViewServiceRatePlanLink = Href()->ViewServiceRatePlan($dboService->Id->Value);
				
				$strPlanCell = "<a href='$strViewServiceRatePlanLink' title='View Service Specific Plan'>$strPlan</a>";
			}
			else
			{
				// There is no current plan for the service
				$strPlanCell = "<span id='RatePlan.Name'>No Plan Selected</span>";
			}
			

			// Work out the Date to display along with the status
			$intClosedOn = strtotime($dboService->ClosedOn->Value);
			$intCurrentDate = strtotime(GetCurrentDateForMySQL());
			
			// Check if the ClosedOn date has been set
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
				}
				else
				{
					// The service has been closed
					$strStatusDesc = "Closed";
				}
				$strStatusDescDate = OutputMask()->ShortDate($dboService->ClosedOn->Value);
			}

			$strStatusDescCell = "<span>$strStatusDesc</span>";
			$strStatusDateCell = "<span>$strStatusDescDate</span>";
			
				
			$strViewServiceLink = Href()->ViewService($dboService->Id->Value);
			
			if ($dboService->FNN->Value == NULL)
			{
				// The service doesn't have an FNN yet
				$strFnnDescription = "<span>not specified</span>";
			}
			else
			{
				// The service has an FNN
				$strFnnDescription = $dboService->FNN->AsValue();
			}
			
			$strFnnCell = "<a href='$strViewServiceLink' title='View Service Details'>$strFnnDescription</a>";
			
			Table()->ServiceTable->AddRow($strFnnCell, $dboService->ServiceType->AsCallback('GetConstantDescription', Array('ServiceType')), 
											$strPlanCell, $strStatusCell, $strStatusDescCell, $strStatusDateCell, $strActionsCell);
		}
		
		// If the account has no services then output an appropriate message in the table
		if (Table()->ServiceTable->RowCount() == 0)
		{
			// There are no services to stick in this table
			Table()->ServiceTable->AddRow("<span class='DefaultOutputSpan Default'>No services to display</span>");
			Table()->ServiceTable->SetRowAlignment("left");
			Table()->ServiceTable->SetRowColumnSpan(7);
		}
		
		// Row highlighting doesn't seem to be working with popups
		Table()->ServiceTable->RowHighlighting = TRUE;
		Table()->ServiceTable->Render();
	}
}

?>
