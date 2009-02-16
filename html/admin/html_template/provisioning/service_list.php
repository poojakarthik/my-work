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
		$arrSelectedServices	= DBO()->Request->ServiceIds->Value;
		$arrServices			= DBO()->Account->Services->Value;
		$intCurrentDate			= strtotime(GetCurrentISODate());
		
		// Flag records that can/can't be provisioned and find out if there are any
		// service address details missing, or are pending activation
		$bolHasFlaggedServices = FALSE;
		
		$intFilter = (DBO()->List->Filter->IsSet) ? DBO()->List->Filter->Value : 0;
		
		echo "
<div style='width:100%;height:auto'>
	<h2 class='Services' style='float:left'>Services</h2>
			
	<select id='ServicesListFilterCombo' style='float:left;margin-left:20px' onChange='Vixen.ProvisioningPage.ReloadServiceList(true)'>
		<option value='0' ". 							(($intFilter == 0)? "selected='selected'" : "") 					.">Show All</option>
		<option value='". SERVICE_ACTIVE ."' ".			(($intFilter == SERVICE_ACTIVE)? "selected='selected'" : "")		.">Active Only</option>
		<option value='". SERVICE_DISCONNECTED ." ".	(($intFilter == SERVICE_DISCONNECTED)? "selected='selected'" : "")	."'>Disconnected</option>
		<option value='". SERVICE_ARCHIVED ."' ".		(($intFilter == SERVICE_ARCHIVED)? "selected='selected'" : "")		.">Archived</option>
	</select>
</div>
<div style='clear:both'></div>";
		
		// Build the checkbox used to select/unselect all the services
		$strSelectAll = "<input type='checkbox' id='SelectAllServicesCheckbox' class='DefaultInputCheckBox' onchange='Vixen.ProvisioningPage.SelectAllServices();' />";
		
		Table()->Services->SetHeader($strSelectAll, "FNN #", "Plan", "Status", "Line Status", "&nbsp;");
		Table()->Services->SetWidth("4%", "11%", "46%", "11%", "20%", "8%");
		Table()->Services->SetAlignment("Left", "Left", "Left", "Left", "Left", "Right");
		
		foreach ($arrServices as $arrService)
		{
			$intServiceId = $arrService['Id'];
			
			// Build the Actions Cell
			$strViewAddressLink			= Href()->ViewServiceAddress($intServiceId);
			$strEditAddressLink			= Href()->EditServiceAddress($intServiceId);
			$strProvisioningHistoryLink	= "javascript:Vixen.ProvisioningPage.ShowHistory($intServiceId)";
			if ($arrService['AddressId'] != NULL)
			{
				$strActionsCell  = "<img src='img/template/address.png' title='Address Details' onclick='$strViewAddressLink'/>";
			}
			else
			{
				$strActionsCell  = "<img src='img/template/address.png' title='Address Details' onclick='$strEditAddressLink'/>";
			}
			$strActionsCell .= "&nbsp;&nbsp;<img src='img/template/provisioning_history.png' title='Provisioning History' onclick='$strProvisioningHistoryLink'/>";

			// Build the checkbox
			if ($arrService['History'][0]['Status'] == SERVICE_PENDING)
			{
				// The service has not been activated yet, flag it
				$strSelectCell			= "<img src='img/template/flag_red.png' title='Pending Activation' />";
				$bolHasFlaggedServices	= TRUE;
			}
			elseif ($arrService['AddressId'] != NULL)
			{
				// The service already has address details defined for it
				$strChecked		= (in_array($intServiceId, $arrSelectedServices))? "checked='checked'" : "";
				$strSelectCell	= "<input type='checkbox' class='DefaultInputCheckBox' name='ServiceCheckbox' Service='$intServiceId' $strChecked onchange='Vixen.ProvisioningPage.UpdateServiceToggle();'/>";
			}
			else
			{
				// The service does not have Address details specified.  Flag it
				$strSelectCell			= "<img src='img/template/flag_red.png' title='No Address Details defined' onclick='$strEditAddressLink' />";
				$bolHasFlaggedServices	= TRUE;
			}
			
			// Build the FNN cell
			$strViewServiceLink = Href()->ViewService($intServiceId);
			if ($arrService['FNN'] == NULL)
			{
				// The service doesn't have an FNN yet
				$strFnn = "[not specified]";
			}
			else
			{
				// The service has an FNN
				$strFnn = $arrService['FNN'];
			}
			$strIndial100Flag = ($arrService['Indial100'])? " (Indial&nbsp;100&nbsp;range)": "";
			
			$strFnnCell = "<a href='$strViewServiceLink' title='View Service Details'>$strFnn{$strIndial100Flag}</a>"; 

			// This is no longer used (but they might want to use it again in the future)
			// Work out the Date to display along with the status
			// Check if the ClosedOn date has been set
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
				}
				$strStatusDescDate = OutputMask()->ShortDate($arrService['History'][0]['CreatedOn']);
			}
			else
			{
				// The service has a ClosedOn date; check if it is in the future or past
				$intClosedOn = strtotime($arrService['History'][0]['ClosedOn']);
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
				$strStatusDescDate = OutputMask()->ShortDate($arrService['History'][0]['ClosedOn']);
			}
			
			// Build the plan cell
			$strViewServiceRatePlanLink = Href()->ViewServiceRatePlan($intServiceId);
			if ($arrService['CurrentPlan']['Id'])
			{
				// A plan was found
				$strPlanCell = "<a href='$strViewServiceRatePlanLink' title='View Service Specific Plan'>{$arrService['CurrentPlan']['Name']}</a>";
			}
			else
			{
				// There is no current plan for the service
				$strPlanCell = "<span class='Red'>No Plan Selected</span>";
			}
			
			// Find the future scheduled plan for the service (if there is one)
			if ($arrService['FuturePlan']['Id'])
			{
				// A plan has been found, which is scheduled to start for the next billing period
				$strStartDate = date("d/m/Y", strtotime($arrService['FuturePlan']['StartDatetime']));
				$strPlanCell .= "<br />As from $strStartDate : <a href='$strViewServiceRatePlanLink' title='View Service Specific Plan'>{$arrService['FuturePlan']['Name']}</a>";
			}
			
			
			// Build the Status cell
			$strStatus = GetConstantDescription($arrService['History'][0]['Status'], "service_status");
			//$strStatusCell = "<span title='$strStatusDesc $strStatusDescDate'>$strStatus<span>";
			$strStatusCell = $strStatus;
			
			// Build the Line Status cell
			$strLineStatusCell = GetConstantDescription($arrService['History'][0]['LineStatus'], "service_line_status");
			if ($strLineStatusCell === FALSE)
			{
				$strLineStatusCell = "Unknown";
			}
			else
			{
				$strLineStatusDate = $arrService['History'][0]['LineStatusDate'];
				if ($strLineStatusDate != NULL)
				{
					// LineStatusDate has been specified
					$strLineStatusDate	= substr($strLineStatusDate, 11, 8) ." ". substr($strLineStatusDate, 8, 2) ."/". substr($strLineStatusDate, 5, 2) ."/". substr($strLineStatusDate, 0, 4);
					$strLineStatusDesc	= "Line Status was last updated: $strLineStatusDate";
					$strLineStatusCell	= "<span title='$strLineStatusDesc'>$strLineStatusCell</span>";
				}
			}
			
			$strHistoryDetailsTable	= HtmlTemplateServiceHistory::GetHistoryForTableDropDownDetail($arrService['History']);
			$strDropDownDetail		= "<div style='width:100%;background-color: #D4D4D4'>$strHistoryDetailsTable</div>";

			Table()->Services->AddRow($strSelectCell, $strFnnCell, $strPlanCell, $strStatusCell, $strLineStatusCell, $strActionsCell);
			Table()->Services->SetDetail($strDropDownDetail);
		}
		
		// If the account has no services then output an appropriate message in the table
		if (Table()->Services->RowCount() == 0)
		{
			// There are no services to stick in this table
			Table()->Services->AddRow("No provisionable services to display");
			Table()->Services->SetRowAlignment("left");
			Table()->Services->SetRowColumnSpan(6);
		}
		
		Table()->Services->Render();
		
		//Initialise the javascript object that manages this list
		$intAccount				= DBO()->Account->Id->Value;
		$strHasFlaggedServices	= ($bolHasFlaggedServices)? "true" : "false";
		echo "<script type='text/javascript'>Vixen.ProvisioningPage.InitialiseServiceList('{$this->_strContainerDivId}', $intAccount, $strHasFlaggedServices)</script>\n";
		echo "<div class='SmallSeperator'></div>";
	}
}

?>
