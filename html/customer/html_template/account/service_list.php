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
		echo "<h2 class='Services'>Services</h2>\n";
		
		Table()->Services->SetHeader("FNN", "Service Type", "Current Plan", "Unbilled Charges (inc GST)", "&nbsp;", "&nbsp;");
		Table()->Services->SetWidth("10%", "15%", "40%", "25%", "5%", "5%");
		Table()->Services->SetAlignment("left", "left", "left", "right", "left", "right");
		
		// Declare variable to store the Total Charges
		$fltTotalCharges = 0;
		
		DBO()->CurrentRatePlan->SetTable("RatePlan");
		DBO()->FutureRatePlan->SetTable("RatePlan");
		$strNextBillingPeriodStartDate = date("jS M, Y", GetStartDateTimeForNextBillingPeriod());
		
		// add the rows
		foreach (DBL()->Service as $dboService)
		{
			// Find the current plan for the service (if there is one)
			DBO()->CurrentRatePlan->Id = GetCurrentPlan($dboService->Id->Value);
			if (DBO()->CurrentRatePlan->Id->Value)
			{
				// A plan was found
				DBO()->CurrentRatePlan->Load();
				$strPlanCell = "<span>". DBO()->CurrentRatePlan->Name->Value ."</span>";
			}
			else
			{
				// There is no current plan for the service
				$strPlanCell = "<span>(No Plan Selected)</span>";
			}
			
			// Find the future scheduled plan for the service (if there is one)
			DBO()->FutureRatePlan->Id = GetPlanScheduledForNextBillingPeriod($dboService->Id->Value);
			if (DBO()->FutureRatePlan->Id->Value)
			{
				// A plan has been found, which is scheduled to start for the next billing period
				DBO()->FutureRatePlan->Load();
				$strPlanCell .= "<br /><span>As of $strNextBillingPeriodStartDate: ". DBO()->FutureRatePlan->Name->Value ."</span>";
			}
			
			
			
			// Calculate the total unbilled charges for this service (inc GST)
			// Note that we are not including service adjustments in this calculation, just unbilled CDRs relating to the service, that aren't credit CDRs
			$dboService->TotalUnbilled = AddGST(UnbilledServiceCDRTotal($dboService->Id->Value, TRUE));

			// build the "View Unbilled Charges for Service" link
			$strViewUnbilledCharges = Href()->ViewUnbilledChargesForService($dboService->Id->Value);
			$strViewUnbilledChargesLabel = "<span><a href='$strViewUnbilledCharges' title='View'><img src='img/template/cdr.gif'></img></a></span>";

			if ($dboService->TotalUnbilled->Value < 0)
			{
				// Total Unbilled Charges is a CR.  Change it to a positive value and flag it as a CR in the table
				$dboService->TotalUnbilled = $dboService->TotalUnbilled->Value * (-1);
				$strNature = "<span>&nbsp;". NATURE_CR ."</span>";
				
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
									$dboService->ServiceType->AsCallback("GetConstantDescription", Array("service_type")),
									$strPlanCell,
									$dboService->TotalUnbilled->AsValue(),
									$strNature,
									$strViewUnbilledChargesLabel);
		}
		
		if (Table()->Services->RowCount() == 0)
		{
			// There are no services to stick in this table
			Table()->Services->AddRow("<span>No services to display</span>");
			Table()->Services->SetRowAlignment("left");
			Table()->Services->SetRowColumnSpan(6);
		}
		else
		{
			if ($fltTotalCharges < 0)
			{
				// The total Charges is negative.  Change it to a positive and flag it as a CR
				$fltTotalCharges = $fltTotalCharges * (-1);
				$strNature = "<span style='font-weight:bold;'>&nbsp;". NATURE_CR ."</span>";
			}
			else
			{
				// The total Charges is positive
				$strNature = "&nbsp;";
			}
		
			// Append the total to the table
			$strTotal			= "<span style='font-weight:bold;'>Total Charges ($):</span>\n";
			$strTotalCharges	= "<span class='Currency' style='font-weight:bold;'>". OutputMask()->MoneyValue($fltTotalCharges, 2) ."</span>\n";
			
			Table()->Services->AddRow($strTotal, $strTotalCharges, $strNature, "&nbsp;");
			Table()->Services->SetRowAlignment("left", "right", "left", "center");
			Table()->Services->SetRowColumnSpan(3, 1, 1, 1);
		}
		
		Table()->Services->RowHighlighting = TRUE;
		Table()->Services->Render();
		
		echo "<div class='Seperator'></div>\n";
	}
}

?>
