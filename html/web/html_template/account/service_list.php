<?php
//----------------------------------------------------------------------------//
// HtmlTemplateAccountServiceList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountServiceList
 *
 * HTML Template object for the client app, List of all Services for account
 *
 * HTML Template object for the client app, List of all Services for account
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateAccountServiceList
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountServiceList extends HtmlTemplate
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
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
		
		// Load all java script specific to the page here
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
		echo "<div class='WideContent'>\n";
		echo "<h2 class='Services'>Services</h2>\n";
		
		
		Table()->Services->SetHeader("FNN", "Service Type", "Current Plan", "Current Unbilled Charges (inc GST)", "&nbsp;", "&nbsp;");
		Table()->Services->SetWidth("10%", "15%", "35%", "15%", "3%", "22%");
		Table()->Services->SetAlignment("left", "left", "left", "right", "center", "center");
		
		// Declare variable to store the Total Charges
		$fltTotalCharges = 0;
		
		// add the rows
		foreach (DBL()->Service as $dboService)
		{
			// Find the current plan for the service
			$mixCurrentPlan = GetCurrentPlan($dboService->Id->Value);
			if ($mixCurrentPlan === FALSE)
			{
				// There is no current plan for this service
				DBO()->RatePlan->Name = "No Current Plan";
			}
			else
			{
				// a plan was found
				DBO()->RatePlan->Id = $mixCurrentPlan;
				DBO()->RatePlan->Load();
			}
			
			// Calculate the total unbilled charges for this service (inc GST)
			// Note that we are not including service adjustments in this calculation, just unbilled CDRs relating to the service, that aren't credit CDRs
			$dboService->TotalUnbilled = AddGST(UnbilledServiceCDRTotal($dboService->Id->Value, TRUE));

			// build the "View Unbilled Charges for Service" link
			$strViewUnbilledCharges = Href()->ViewUnbilledChargesForService($dboService->Id->Value);
			$strViewUnbilledChargesLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewUnbilledCharges' style='color:blue; text-decoration: none;'>View Unbilled Charges</a></span>";

			if ($dboService->TotalUnbilled->Value < 0)
			{
				// Total Unbilled Charges is a CR.  Change it to a positive value and flag it as a CR in the table
				$dboService->TotalUnbilled = $dboService->TotalUnbilled->Value * (-1);
				$strNature = "<span class='DefaultOutputSpan Default'>". NATURE_CR ."</span>";
				
				// subtract the total charges for this service from the total for all services of the account. (this already includes GST)
				$fltTotalCharges -= $dboService->TotalUnbilled->Value;
			}
			else
			{
				// Total Unbilled Charges is a DR
				$strNature = "&nbsp;";
				
				// add the total charges for this service to the total for all services of the account. (this already includes GST)
				$fltTotalCharges += $dboService->TotalUnbilled->Value;
			}
			
			Table()->Services->AddRow($dboService->FNN->AsValue(),
									$dboService->ServiceType->AsCallback("GetConstantDescription", Array("ServiceType")),
									DBO()->RatePlan->Name->AsValue(),
									$dboService->TotalUnbilled->AsValue(),
									$strNature,
									$strViewUnbilledChargesLabel);
		}
		
		if (Table()->Services->RowCount() == 0)
		{
			// There are no services to stick in this table
			Table()->Services->AddRow("<span class='DefaultOutputSpan Default'>No services to display</span>");
			Table()->Services->SetRowAlignment("left");
			Table()->Services->SetRowColumnSpan(6);
		}
		else
		{
			if ($fltTotalCharges < 0)
			{
				// The total Charges is negative.  Change it to a positive and flag it as a CR
				$fltTotalCharges = $fltTotalCharges * (-1);
				$strNature = "<span class='DefaultOutputSpan Default' style='font-weight:bold;'>". NATURE_CR ."</span>";
			}
			else
			{
				// The total Charges is positive
				$strNature = "&nbsp;";
			}
		
			// Append the total to the table
			$strTotal			= "<span class='DefaultOutputSpan Default' style='font-weight:bold;'>Total Charges:</span>\n";
			$strTotalCharges	= "<span class='DefaultOutputSpan Currency' style='font-weight:bold;'>". OutputMask()->MoneyValue($fltTotalCharges, 2, TRUE) ."</span>\n";
			
			Table()->Services->AddRow($strTotal, $strTotalCharges, $strNature, "&nbsp;");
			Table()->Services->SetRowAlignment("left", "right", "center", "center");
			Table()->Services->SetRowColumnSpan(3, 1, 1, 1);
		}
		
		Table()->Services->Render();
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
