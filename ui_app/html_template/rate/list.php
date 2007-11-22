<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// grouplist.php
//----------------------------------------------------------------------------//
/**
 * grouplist
 *
 * HTML Template for the Group List HTML object
 *
 * HTML Template for the Group List HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all rategroups relating to a service and can be embedded in
 * various Page Templates
 *
 * @file		grouplist.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRateList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRateList
 *
 * HTML Template class for the RateList HTML object
 *
 * HTML Template class for the RateList HTML object
 * Lists all rategrops related to a service
 *
 * @package	ui_app
 * @class	HtmlTemplateRateList
 * @extends	HtmlTemplate
 */
class HtmlTemplateRateList extends HtmlTemplate
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
		$this->LoadJavascript("retractable");
		//$this->LoadJavascript("tooltip");
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
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_MINIMUM_DETAIL:
				$this->_RenderMinimumDetail();
				break;
			default:
				$this->_RenderFullDetail();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// _RenderMinimumDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderMinimumDetail()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template with one DBO rate
	 *
	 * @method
	 */
	function _RenderMinimumDetail()
	{
		// Variable to hold the string status of the service i.e. if capped, untimed etc
		// and is output at the end as looks better in one group display rather than fragmented
		$strCallStatus = "";
		$strRecordDisplayType = GetConstantDescription(DBO()->RecordType->DisplayType->Value, 'DisplayTypeSuffix');
		$strAvailability = DBO()->Rate->Monday->AsValue(CONTEXT_DEFAULT,TRUE) . 
								DBO()->Rate->Tuesday->AsValue(CONTEXT_DEFAULT,TRUE) . 
								DBO()->Rate->Wednesday->AsValue(CONTEXT_DEFAULT,TRUE) . 
								DBO()->Rate->Thursday->AsValue(CONTEXT_DEFAULT,TRUE) . 
								DBO()->Rate->Friday->AsValue(CONTEXT_DEFAULT,TRUE) .
								DBO()->Rate->Saturday->AsValue(CONTEXT_DEFAULT,TRUE) .
								DBO()->Rate->Sunday->AsValue(CONTEXT_DEFAULT,TRUE);

		echo "<div class='NarrowContent'>\n";

		DBO()->Rate->Name->RenderOutput();
		DBO()->Rate->Description->RenderOutput();
		DBO()->Rate->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);
		
		if (DBO()->Rate->Destination->Value)
		{
			DBO()->Destination->Description->RenderOutput();
		}
		
		DBO()->RecordType->Name->RenderOutput();
		DBO()->Rate->StartTime->RenderOutput();
		DBO()->Rate->EndTime->RenderOutput();

		// Renders the table for showing the Rate availability
		echo "<table width=335 border=0 cellpadding=0 cellspacing=0>\n";
		echo "<tr><td><div class='DefaultRegularOutput'>&nbsp;&nbsp;Availability : </div></td><td align=right>$strAvailability</td></tr>";
		echo "</table>";

		// If PassThrough is checked show PassThrough, Minimum Charge and Flagfall
		$bolPassThroughChecked = FALSE;
		if (DBO()->Rate->PassThrough->Value)
		{
			$bolPassThroughChecked = TRUE;
			DBO()->Rate->PassThrough->RenderOutput();
			DBO()->Rate->StdMinCharge->RenderOutput();
			DBO()->Rate->StdFlagfall->RenderOutput();
		}
		else
		{
			// If PassThrough is not checked show ProRate, Minimum Charge and Flagfall
			DBO()->Rate->Prorate->RenderOutput();
			DBO()->Rate->StdMinCharge->RenderOutput();
			DBO()->Rate->StdFlagfall->RenderOutput();			
		}
	
		DBO()->Rate->Uncapped->RenderOutput();

		// If StdRatePerUnit, StdMarkup, StdPercentage, ExsRatePerUnit, ExsMarkup, ExsPercentage
		// ALL equal zero this equates to Untimed Calls on this Rate
		$bolUntimedChecked = FALSE;
		if 	((DBO()->Rate->StdRatePerUnit->Value == 0) &&
			(DBO()->Rate->StdMarkup->Value == 0) &&
			(DBO()->Rate->StdPercentage->Value == 0) &&
			(DBO()->Rate->ExsRatePerUnit->Value == 0) &&
			(DBO()->Rate->ExsMarkup->Value == 0) &&
			(DBO()->Rate->ExsPercentage->Value == 0))
		{		
			$strCallStatus .= "<div class='DefaultRegularOutput'>&nbsp;&nbsp;This rate is untimed</div>\n";					
			$bolUntimedChecked = TRUE;
		}
		
		// If PassThrough is unchecked and Untimed is unchecked Rate can have caps and excesses
		if (!$bolPassThroughChecked || !bolUntimedChecked)
		{
			// standard billing units/charge/markup on cost $/markup on cost %
			echo "<table border='0' cellpadding='0' cellspacing='0'>";

			// Only show one of the three Standard Rates
			$bolStdRate = FALSE;
			if (DBO()->Rate->StdRatePerUnit->Value > 0)
			{
				echo "	<tr><td width='2%'>&nbsp;</td><td width='190'><div class='DefaultRegularOutput'>Standard Charge ($) : </div></td>";
				echo "<td><div class='DefaultRegularOutput'>&nbsp;" . DBO()->Rate->StdRatePerUnit->FormattedValue();
				echo " Per " . DBO()->Rate->StdUnits->Value . " " . $strRecordDisplayType . "</div></td></tr>";
				$bolStdRate = TRUE;
			}
			elseif (DBO()->Rate->StdMarkup->Value > 0)
			{
				echo "<tr><td width='2%'>&nbsp;</td><td width='190'><div class='DefaultRegularOutput'>Standard Markup ($) : </div></td>";
				echo "<td><div class='DefaultRegularOutput'>&nbsp;" . DBO()->Rate->StdMarkup->FormattedValue();
				echo  " Per " . DBO()->Rate->StdUnits->Value . " $strRecordDisplayType </div></td></tr>";
			}
			elseif (DBO()->Rate->StdPercentage->Value > 0)
			{
				echo "<tr><td width='2%'>&nbsp;</td><td width='190'><div class='DefaultRegularOutput'>&nbsp;Markup on Cost (%) : </div></td>";
				echo "<td><div class='DefaultRegularOutput'>&nbsp;" . DBO()->Rate->StdPercentage->FormattedValue() . "</div></td></tr>";
			}
			
			echo "</table>\n";
	
			// no capping/start capping at units/start capping at $
			// if the CapUnits is greater than zero OR CapUnits equals zero AND Either CapLimit OR CapUsage equals zero
			$bolShowCapDetails = FALSE;
			
			echo "<table border='0' cellpadding='0' cellspacing='0'>";
			if (DBO()->Rate->CapUnits->Value > 0 || (DBO()->Rate->CapUnits->Value == 0 && (DBO()->Rate->CapLimit->Value > 0 || DBO()->Rate->CapUsage->Value > 0)))
			{
				$bolShowCapDetails = TRUE;
				echo "<tr><td width='3%'>&nbsp;</td><td width='190'><div class='DefaultRegularOutput'>Start Capping at : </div></td>";
				echo "<td><div class='DefaultRegularOutput'>" . DBO()->Rate->CapUnits->Value . " $strRecordDisplayType</div></td></tr>";
			}
			elseif (DBO()->Rate->CapCost->Value > 0 || (DBO()->Rate->CapCost->Value == 0 && (DBO()->Rate->CapLimit->Value > 0 || DBO()->Rate->CapUsage->Value > 0)))
			{
				$bolShowCapDetails = TRUE;
				echo "<tr><td width='3%'>&nbsp;</td><td width='190'><div class='DefaultRegularOutput'>Start Capping at ($) : </div></td>";
				echo "<td><div class='DefaultRegularOutput'>&nbsp;" . DBO()->Rate->CapCost->FormattedValue();
			}
			else
			{
				$strCallStatus .= "<div class='DefaultRegularOutput'>&nbsp;&nbsp;No Capping on this Rate</div>\n";			
			}
			echo "</table>";
			
			//no cap/stop capping at units/stop capping at $/excess flagfall
			$bolShowExcessDetails = FALSE;
			echo "<table border='0' cellpadding='0' cellspacing='0'>";			
			if (DBO()->Rate->CapUsage->Value > 0)
			{
				$bolShowExcessDetails = TRUE;			
				echo "<tr><td width='3%'>&nbsp;</td><td width='190'><div class='DefaultRegularOutput'>Stop Capping at : </div></td>";
				echo "<td><div class='DefaultRegularOutput'>" . DBO()->Rate->CapUsage->Value . " $strRecordDisplayType</div></td></tr>";				
			}
			elseif (DBO()->Rate->CapLimit->Value > 0 && !DBO()->Rate->CapUsage->Value > 0)
			{
				$bolShowExcessDetails = TRUE;	
				echo "<tr><td width='3%'>&nbsp;</td><td width='190'><div class='DefaultRegularOutput'>Stop Capping at ($) : </div></td>";
				echo "<td><div class='DefaultRegularOutput'>&nbsp;" . DBO()->Rate->CapLimit->Value;		
			}
			elseif (DBO()->Rate->ExsFlagfall->Value > 0)
			{
				$bolShowExcessDetails = TRUE;	
				echo "<tr><td width='3%'>&nbsp;</td><td width='190'><div class='DefaultRegularOutput'>Excess Flagfall ($) : </div></td>";				
				echo "<td><div class='DefaultRegularOutput'>&nbsp;" . DBO()->Rate->ExsFlagfall->RenderOutput();		
			}
			else
			{
				if ($bolShowCapDetails)
				{
					$strCallStatus .= "<div class='DefaultRegularOutput'>&nbsp;&nbsp;No Cap Limit on this Rate</div>\n";				
				}
			}

			// show the standard charge if $bolShowExcessDetails is set, that is, capping is on this rate
			if ($bolShowExcessDetails)
			{
				// Show the standard rate per unit if excess details are shown, checking if it isn't already displayed
				if (!$bolStdRate)
				{
					echo "	<tr><td width='2%'>&nbsp;</td><td width='190'><div class='DefaultRegularOutput'>Standard Charge ($) : </div></td>";
					echo "<td valign='top'><div class='DefaultRegularOutput'>" . DBO()->Rate->StdRatePerUnit->FormattedValue();
					echo " Per " . DBO()->Rate->StdUnits->Value . " " . $strRecordDisplayType . "</div></td></tr>";
				}
				
				//exs billing units/exs charge/exs markup cost $/exs markup cost %
				echo "<table border='0' cellpadding='0' cellspacing='0'>";		
				if (DBO()->Rate->ExsRatePerUnit->Value > 0)
				{	
					echo "<tr><td width='2%'>&nbsp;</td><td width='190' valign='top'><div class='DefaultRegularOutput'>Excess Charge ($) : </div></td>";
					echo "<td valign='top'><div class='DefaultRegularOutput'>&nbsp;" . DBO()->Rate->ExsRatePerUnit->FormattedValue() . " Per " . DBO()->Rate->ExsUnits->Value . " $strRecordDisplayType beyond cap limit</div></td></tr>";
				}
				elseif (DBO()->Rate->ExsMarkup->Value > 0)
				{
					echo "<tr><td width='2%'>&nbsp;</td><td width='190' valign='top'><div class='DefaultRegularOutput'>Excess Markup on Cost ($) : </div></td>";
					echo "<td valign='top'><div class='DefaultRegularOutput'>&nbsp;" . DBO()->Rate->ExsMarkup->Value . " Per " . DBO()->Rate->ExsUnits->Value . " $strRecordDisplayType beyond cap limit</div></td></tr>";
				}
				elseif (DBO()->Rate->ExsPercentage->Value > 0)
				{	
					echo "<tr><td width='2%'>&nbsp;</td><td width='190' valign='top'><div class='DefaultRegularOutput'>Excess Markup on Cost (%) : </div></td>";				
					echo "<td valign='top'><div class='DefaultRegularOutput'>&nbsp;" . DBO()->Rate->ExsPercentage->Value . "</div></td></tr>";
				}
				echo "</table>\n";
			}
		}

		if ($strCallStatus)
		{
			echo "<table border='0' cellpadding='0' cellspacing='0'>";	
			echo "<tr><td>" . $strCallStatus . "</td></tr>\n";
			echo "</table>";
		}

		echo "</div>\n";  //NarrowContent

		echo "<div class='ButtonContainer'><div class='right'>\n";
		$this->Button("Close", "Vixen.Popup.Close(\"{$this->_objAjax->strId}\");");
		echo "</div>\n";		
	}

	//------------------------------------------------------------------------//
	// _RenderFullDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderFullDetail()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template with one or more DBO rates
	 *
	 * @method
	 */
	function _RenderFullDetail()
	{
		echo "<div id='ContainerDiv_FormContainerDiv_RateAdd' style='border: solid 1px #606060; padding: 5px 5px 5px 5px'>\n";
		echo "<div id='FormContainerDiv_RateAdd' class='PopupLarge' style='overflow:auto; height:300px; width:auto;'>\n";
	
		Table()->RateTable->SetHeader("Name", "Days Available", "Start Time", "End Time");
		Table()->RateTable->SetAlignment("Left", "Left", "Left", "Left");
		Table()->RateTable->SetWidth("56%", "22%", "11%", "11%");
	
		foreach (DBL()->Rate as $dboRate)
		{
			$strViewRateLink = Href()->ViewRate($dboRate->Id->Value);
			$strDaysAvailable = $dboRate->Monday->AsValue(CONTEXT_DEFAULT,TRUE).
								$dboRate->Tuesday->AsValue(CONTEXT_DEFAULT,TRUE).
								$dboRate->Wednesday->AsValue(CONTEXT_DEFAULT,TRUE).
								$dboRate->Thursday->AsValue(CONTEXT_DEFAULT,TRUE).
								$dboRate->Friday->AsValue(CONTEXT_DEFAULT,TRUE).
								$dboRate->Saturday->AsValue(CONTEXT_DEFAULT,TRUE).
								$dboRate->Sunday->AsValue(CONTEXT_DEFAULT,TRUE);
								
			Table()->RateTable->AddRow("<a href='$strViewRateLink'>" . $dboRate->Name->AsValue() . "</a>",
			//Table()->RateTable->AddRow(	$dboRate->Name->AsValue(),
										$strDaysAvailable,
										$dboRate->StartTime->AsValue(), 
										$dboRate->EndTime->AsValue());
		}
		
		Table()->RateTable->Render();
		echo "</div>\n";
		echo "</div>\n";
				
		echo "<div class='ButtonContainer'><div class='right'>\n";
			$this->Button("Close", "Vixen.Popup.Close(\"{$this->_objAjax->strId}\");");
		echo "</div></div>\n";		

		
	}
}

?>
