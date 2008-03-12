<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_list.php
//----------------------------------------------------------------------------//
/**
 * service_list
 *
 * HTML Template for the Provisioning Services table
 *
 * HTML Template for the Provisioning Services table
 *
 * @file		service_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// HtmlTemplateProvisioningServiceList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateProvisioningServiceList
 *
 * HTML Template object defining the presentation of the Provisioning Services table
 *
 * HTML Template object defining the presentation of the Provisioning Services table
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateProvisioningServiceList
 * @extends	HtmlTemplate
 */
class HtmlTemplateProvisioningServiceList extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor
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
		
		$this->LoadJavascript("provisioning_page");
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
		echo "<h2 class='Services'>Services</h2>\n";
		
		// Build the checkbox used to select/unselect all the services
		$strSelectAll = "<input type='checkbox' id='SelectAllServicesCheckbox' class='DefaultInputCheckBox' onchange='Vixen.ProvisioningPage.SelectAllServices();' />";
		
		Table()->Services->SetHeader($strSelectAll, "FNN #", "Plan", "Status", "Line Status", "&nbsp;");
		Table()->Services->SetWidth("4%", "11%", "55%", "11%", "11%", "8%");
		Table()->Services->SetAlignment("Left", "Left", "Left", "Left", "Left", "Right");
		
		foreach (DBL()->Service as $dboService)
		{
			$intServiceId = $dboService->Id->Value;
			
			// Build the Actions Cell
			$strViewAddressLink			= Href()->ViewServiceAddress($intServiceId);
			$strProvisioningHistoryLink	= "javascript:Vixen.ProvisioningPage.ShowHistory($intServiceId)";
			$strActionsCell  = "<a href='$strViewAddressLink'><img src='img/template/address.png' title='Address Details' /></a>";
			$strActionsCell .= "&nbsp;&nbsp;<a href='$strProvisioningHistoryLink'><img src='img/template/provisioning.png' title='Provisioning History' /></a>";

			// Build the checkbox
			if ($dboService->AddressId->Value != NULL)
			{
				// The service already has address details defined for it
				$strSelectCell = "<input type='checkbox' class='DefaultInputCheckBox' name='ServiceCheckbox' Service='$intServiceId' onchange='Vixen.ProvisioningPage.UpdateServiceToggle();'/>";
			}
			else
			{
				// The service does not have Address details specified.  Flag it
				$strSelectCell = "<a href='$strViewAddressLink' title='No Address Details defined'><img src='img/template/flag_red.png'/></a>";
			}

			// Build the FNN cell
			$strViewServiceLink = Href()->ViewService($intServiceId);
			if ($dboService->FNN->Value == NULL)
			{
				// The service doesn't have an FNN yet
				$strFnn = "[not specified]";
			}
			else
			{
				// The service has an FNN
				$strFnn = $dboService->FNN->Value;
			}
			$strFnnCell = "<a href='$strViewServiceLink' title='View Service Details'>$strFnn</a>"; 

			// Work out the Date to display along with the status
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
				$intClosedOn = strtotime($dboService->ClosedOn->Value);
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
			
			// Build the plan cell
			$strViewServiceRatePlanLink = Href()->ViewServiceRatePlan($dboService->Id->Value);
			if ($dboService->CurrentPlanId->Value)
			{
				// A plan was found
				$strPlanCell = "<a href='$strViewServiceRatePlanLink' title='View Service Specific Plan'>{$dboService->CurrentPlanName->Value}</a>";
			}
			else
			{
				// There is no current plan for the service
				$strPlanCell = "<span class='Red'>No Plan Selected</span>";
			}
			
			// Find the future scheduled plan for the service (if there is one)
			if ($dboService->FuturePlanId->Value)
			{
				// A plan has been found, which is scheduled to start for the next billing period
				$strStartDate = date("d/m/Y", strtotime($dboService->FuturePlanStartDatetime->Value));
				$strPlanCell .= "<br />As from $strStartDate : <a href='$strViewServiceRatePlanLink' title='View Service Specific Plan'>{$dboService->FuturePlanName->Value}</a>";
			}
			
			
			// Build the Status cell
			$strStatus = GetConstantDescription($dboService->Status->Value, "Service");
			$strStatusCell = "<span title='$strStatusDesc $strStatusDescDate'>$strStatus<span>";
			
			// Build the Line Status cell
			$strLineStatusCell = GetConstantDescription($dboService->LineStatus->Value, "LineStatus");
			if ($strLineStatusCell === FALSE)
			{
				$strLineStatusCell = "Unknown";
			}
				
			Table()->Services->AddRow($strSelectCell, $strFnnCell, $strPlanCell, $strStatusCell, $strLineStatusCell, $strActionsCell);
		}
		
		// If the account has no services then output an appropriate message in the table
		if (Table()->Services->RowCount() == 0)
		{
			// There are no services to stick in this table
			Table()->Services->AddRow("No services to display");
			Table()->Services->SetRowAlignment("left");
			Table()->Services->SetRowColumnSpan(6);
		}
		
		Table()->Services->Render();
		
		//Initialise the javascript object that manages this list
		$intAccount			= DBO()->Account->Id->Value;
		$intServiceCount	= DBL()->Service->RecordCount();
		echo "<script type='text/javascript'>Vixen.ProvisioningPage.InitialiseServiceList('{$this->_strContainerDivId}', $intAccount, $intServiceCount)</script>\n";
		echo "<div class='SmallSeperator'></div>";
	}
}

?>
