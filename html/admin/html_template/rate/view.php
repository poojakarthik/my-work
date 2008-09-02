<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// view.php
//----------------------------------------------------------------------------//
/**
 * view
 *
 * HTML Template for the viewing the details of a Rate
 *
 * HTML Template for the viewing the details of a Rate
 *
 * @file		view.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		8.02
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRateView
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRateView
 *
 * HTML Template class for the RateView HTML object
 *
 * HTML Template class for the RateView HTML object
 *
 * @package	ui_app
 * @class	HtmlTemplateRateView
 * @extends	HtmlTemplate
 */
class HtmlTemplateRateView extends HtmlTemplate
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

		echo "<div class='GroupedContent'>\n";
		
		// Handle the Archived property
		if (DBO()->Rate->Archived->Value)
		{
			if (DBO()->Rate->Archived->Value == RATE_STATUS_DRAFT)
			{
				// The Rate is currently saved as a draft
				echo "<div style='color:#FF0000;text-align:center'>This rate is currently saved as a draft</div>";
			}
			else
			{
				// The RateGroup must be archived
				echo "<div style='color:#FF0000;text-align:center'>This rate has been archived</div>";
			}
			echo "<div class='ContentSeparator'></div>\n";
		}
		
		$this->_RenderDetails();
		echo "</div>\n"; // GroupedContent

		echo "<div class='ButtonContainer'><div class='right'>\n";
		$this->Button("Close", "Vixen.Popup.Close(this);");
		echo "</div></div>\n";		
	}
	
	function _RenderDetails()
	{
		// Work out what days the rate applies to
		$arrWeekdays = Array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
		
		$bolAvailableEveryDay = TRUE;
		foreach ($arrWeekdays as $strDay)
		{
			if (DBO()->Rate->{$strDay}->Value)
			{
				$strAvailability .= DBO()->Rate->{$strDay}->FormattedValue() . "&nbsp;&nbsp;";
			}
			else
			{
				$bolAvailableEveryDay = FALSE;
			}
		}
		
		if ($bolAvailableEveryDay)
		{
			$strAvailability = "Every Day";
		}
		
		if (DBO()->Rate->StartTime->Value == "00:00:00" && DBO()->Rate->EndTime->Value == "23:59:59")
		{
			if ($bolAvailableEveryDay)
			{
				$strAvailability = "All Day, Every Day";
			}
			else
			{
				DBO()->Rate->Times = "All Day";
			}
		}
		else
		{
			DBO()->Rate->Times = DBO()->Rate->StartTime->Value ." - ". DBO()->Rate->EndTime->Value;
		}
		
		DBO()->Rate->Name->RenderOutput();
		DBO()->Rate->Description->RenderOutput();
		DBO()->Rate->ServiceType->RenderCallback("GetConstantDescription", Array("service_type"), RENDER_OUTPUT);
		
		if (DBO()->Rate->Destination->Value)
		{
			DBO()->Destination->Description->RenderOutput();
		}
		
		DBO()->RecordType->Name->RenderOutput();
		DBO()->Rate->Fleet->RenderOutput();
		DBO()->Rate->AvailableDays = $strAvailability;
		DBO()->Rate->AvailableDays->RenderOutput();
		if (DBO()->Rate->Times->IsSet)
		{
			DBO()->Rate->Times->RenderOutput();
		} 
		

		$bolPassThrough = (DBO()->Rate->PassThrough->Value) ? TRUE : FALSE;
		DBO()->Rate->PassThrough->RenderOutput();
		if (!$bolPassThrough)
		{
			DBO()->Rate->Prorate->RenderOutput();
		}
		DBO()->Rate->Uncapped->RenderOutput();
		DBO()->Rate->StdMinCharge->RenderOutput();
		DBO()->Rate->StdFlagfall->RenderOutput();
		
		if (DBO()->Rate->discount_percentage->Value != NULL)
		{
			// HACK! HACK! HACK! I'm doing this so it truncates the float to 2 decimal places.  All other properties are "Decimals", and don't have this problem
			DBO()->Rate->discount_percentage = OutputMask()->FormatFloat(DBO()->Rate->discount_percentage->Value, 2, 2);
			DBO()->Rate->discount_percentage->RenderOutput();
		}
		
		if ($bolPassThrough)
		{
			// No more details are required for PassThrough Rates
			return;
		}

		// If StdRatePerUnit, StdMarkup, StdPercentage, ExsRatePerUnit, ExsMarkup, ExsPercentage
		// ALL equal zero this equates to Untimed Calls on this Rate
		if (	(DBO()->Rate->StdRatePerUnit->Value == 0)	&&
				(DBO()->Rate->StdMarkup->Value == 0) 		&&
				(DBO()->Rate->StdPercentage->Value == 0) 	&&
				(DBO()->Rate->ExsRatePerUnit->Value == 0) 	&&
				(DBO()->Rate->ExsMarkup->Value == 0) 		&&
				(DBO()->Rate->ExsPercentage->Value == 0)
			)
		{
			DBO()->Rate->StdRatePerUnit->RenderArbitrary("This rate is untimed", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			return;
		}

		// Standard Rate details
		$strUnitType = GetConstantDescription(DBO()->RecordType->DisplayType->Value, 'DisplayTypeSuffix');
		$intStdUnits = DBO()->Rate->StdUnits->Value;
		
		if (DBO()->Rate->StdRatePerUnit->Value > 0)
		{
			$strRate = DBO()->Rate->StdRatePerUnit->FormattedValue() . " Per $intStdUnits $strUnitType";
			DBO()->Rate->StdRatePerUnit->RenderArbitrary($strRate, RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		}
		elseif (DBO()->Rate->StdMarkup->Value > 0)
		{
			$strRate = DBO()->Rate->StdMarkup->FormattedValue() . " Per $intStdUnits $strUnitType";
			DBO()->Rate->StdMarkup->RenderArbitrary($strRate, RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		}
		elseif (DBO()->Rate->StdPercentage->Value > 0)
		{
			DBO()->Rate->StdPercentage->RenderOutput();
		}
		elseif (DBO()->Rate->CapUnits->Value > 0 || DBO()->Rate->CapCost->Value > 0 || DBO()->Rate->CapLimit->Value > 0 || DBO()->Rate->CapUsage->Value > 0)
		{
			// No standard charge details are defined, but a cap has been defined
			// Display the standard details as 0.00 Per X Units
			// It is assumed DBO()->Rate->StdRatePerUnit == 0.0
			$strRate = DBO()->Rate->StdRatePerUnit->FormattedValue() . " Per $intStdUnits $strUnitType";
			DBO()->Rate->StdRatePerUnit->RenderArbitrary($strRate, RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		}
		
		// Cap Start details
		if (DBO()->Rate->CapUnits->Value > 0 || (DBO()->Rate->CapCost->Value == 0 && (DBO()->Rate->CapLimit->Value > 0 || DBO()->Rate->CapUsage->Value > 0)))
		{
			// Either CapUnits has been specified OR no Cap Start has been specified, but a Cap Finish has been
			DBO()->Rate->CapStart = DBO()->Rate->CapUnits->Value . " $strUnitType";
		}
		elseif (DBO()->Rate->CapCost->Value > 0)
		{
			// The Cap Start has been specified as a cost dollar amount
			DBO()->Rate->CapStart = DBO()->Rate->CapCost->FormattedValue() . " Dollars";
		}
		else
		{
			// The rate is not capped
			return;
		}
		DBO()->Rate->CapStart->RenderOutput();
		
		// Cap Stop details
		if (DBO()->Rate->CapUsage->Value > 0)
		{
			// Cap end has been specified in units
			DBO()->Rate->CapStop = DBO()->Rate->CapUsage->Value . " $strUnitType";
		}
		elseif (DBO()->Rate->CapLimit->Value > 0)
		{
			// Cap end has been specified as a dollar amount
			DBO()->Rate->CapStop = DBO()->Rate->CapLimit->FormattedValue . " Dollars";
		}
		else
		{
			// The cap never ends
			DBO()->Rate->CapStop = "Never";
			DBO()->Rate->CapStop->RenderOutput();
			return;
		}
		DBO()->Rate->CapStop->RenderOutput();
		
		DBO()->Rate->ExsFlagfall->RenderOutput();
		
		// Excess Rate details
		$intExsUnits = DBO()->Rate->ExsUnits->Value;
		if (DBO()->Rate->ExsRatePerUnit->Value > 0)
		{
			$strRate = DBO()->Rate->ExsRatePerUnit->FormattedValue() . " Per $intExsUnits $strUnitType";
			DBO()->Rate->ExsRatePerUnit->RenderArbitrary($strRate, RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		}
		elseif (DBO()->Rate->ExsMarkup->Value > 0)
		{
			$strRate = DBO()->Rate->ExsMarkup->FormattedValue() . " Per $intExsUnits $strUnitType";
			DBO()->Rate->ExsMarkup->RenderArbitrary($strRate, RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		}
		elseif (DBO()->Rate->ExsPercentage->Value > 0)
		{
			DBO()->Rate->ExsPercentage->RenderOutput();
		}
		
		if (DBO()->Rate->Archived->Value != RATE_STATUS_ACTIVE)
		{
			DBO()->Rate->Status = GetConstantDescription(DBO()->Rate->Archived->Value, "RateStatus");
			DBO()->Rate->Status->RenderOutput();
		}
	}
}

?>
