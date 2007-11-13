<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// recurring_adjustment_list.php
//----------------------------------------------------------------------------//
/**
 * recurring_adjustment_list
 *
 * HTML Template for the Recurring Adjustment List HTML object
 *
 * HTML Template for the Recurring Adjustment List HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all recurring adjustments relating to an account and can be embedded in
 * various Page Templates
 *
 * @file		recurring_adjustment_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRecurringAdjustmentList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRecurringAdjustmentList
 *
 * HTML Template class for the Recurring Adjustment List HTML object
 *
 * HTML Template class for the Recurring Adjustment List HTML object
 * Lists all adjustments related to an account
 *
 * @package	ui_app
 * @class	HtmlTemplateRecurringAdjustmentList
 * @extends	HtmlTemplate
 */
class HtmlTemplateRecurringAdjustmentList extends HtmlTemplate
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
		$this->LoadJavascript("dhtml");
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
		echo "<h2 class='Adjustment'>Recurring Adjustments</h2>\n";

		// Check if the user has admin privileges
		$bolHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		
		// define the table's header
		if ($bolHasAdminPerm)
		{
			// User has admin permisions and can therefore delete an adjustment
			Table()->RecurringAdjustmentTable->SetHeader("Date", "Description", "&nbsp;");
			Table()->RecurringAdjustmentTable->SetWidth("20%", "70%", "10%");
			Table()->RecurringAdjustmentTable->SetAlignment("left", "left", "center");
		}
		else
		{
			// User cannot delete adjustments
			Table()->RecurringAdjustmentTable->SetHeader("Date", "Description");
			Table()->RecurringAdjustmentTable->SetWidth("20%", "80%");
			Table()->RecurringAdjustmentTable->SetAlignment("Left", "Left");
		}
		
		// add the rows
		foreach (DBL()->RecurringCharge as $dboRecurringCharge)
		{
			// add the row
			if ($bolHasAdminPerm)
			{
				// You can only delete recurring charges that aren't archived
				if ($dboRecurringCharge->Archived->Value == 0)
				{
					// build the "Delete Recurring Adjustment" link
					$strDeleteRecurringAdjustmentHref  = Href()->DeleteRecurringAdjustment($dboRecurringCharge->Id->Value);
					$strDeleteRecurringAdjustmentLabel = "<span><a href='$strDeleteRecurringAdjustmentHref'><img src='img/template/delete.png' title='Cancel Recurring Adjustment' /></a></span>";
				}
				else
				{
					$strDeleteRecurringAdjustmentLabel = "<span>&nbsp;</span>";
				}
				
				Table()->RecurringAdjustmentTable->AddRow($dboRecurringCharge->CreatedOn->AsValue(), $dboRecurringCharge->Description->AsValue(), $strDeleteRecurringAdjustmentLabel);
			}
			else
			{
				Table()->RecurringAdjustmentTable->AddRow($dboRecurringCharge->CreatedOn->AsValue(), $dboRecurringCharge->Description->AsValue());
			}
			
			// Add tooltip
			$strFNN = "";
			if ($dboRecurringCharge->Service->Value)
			{
				// The Recurring Charge is a Service Recurring Charge.  Display the FNN of the Service
				$strFNN = $dboRecurringCharge->FNN->AsOutput();
			}
			
			// Add GST to the MinCharge and RecursionCharge
			$dboRecurringCharge->MinCharge			= AddGST($dboRecurringCharge->MinCharge->Value);
			$dboRecurringCharge->RecursionCharge	= AddGST($dboRecurringCharge->RecursionCharge->Value);
			
			$strToolTipHtml  = $strFNN;
			$strToolTipHtml .= $dboRecurringCharge->LastChargedOn->AsOutput();
			$strToolTipHtml .= $dboRecurringCharge->TotalCharged->AsCallback("AddGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
			$strToolTipHtml .= $dboRecurringCharge->Nature->AsOutput();
			$strToolTipHtml .= $dboRecurringCharge->TotalRecursions->AsOutput();
			$strToolTipHtml .= $dboRecurringCharge->CancellationFee->AsCallback("AddGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
			DBO()->ChargeTypesAvailable->RecurringFreqType = $dboRecurringCharge->RecurringFreqType->Value;
			$strRecurringFreq = $dboRecurringCharge->RecurringFreq->Value ." ". DBO()->ChargeTypesAvailable->RecurringFreqType->FormattedValue();
			$strToolTipHtml .= $dboRecurringCharge->RecurringFreq->AsArbitrary($strRecurringFreq, RENDER_OUTPUT);
			$strToolTipHtml .= $dboRecurringCharge->MinCharge->AsOutput(CONTEXT_INCLUDES_GST);
			$strToolTipHtml .= $dboRecurringCharge->RecursionCharge->AsOutput(CONTEXT_INCLUDES_GST);
			if ((int)($dboRecurringCharge->RecursionCharge->Value) != 0)
			{
				// Calculate the minimum number of recursions
				// BUG if this division works out to be a whole number then 1 is added to it
				// Maybe I should check if it is an int
				$fltMinCharge = OutputMask()->FormatFloat($dboRecurringCharge->MinCharge->Value, 2, 2);
				$fltRecursionCharge = OutputMask()->FormatFloat($dboRecurringCharge->RecursionCharge->Value, 2, 2);
				
				$dboRecurringCharge->TimesToCharge = ceil($fltMinCharge / $fltRecursionCharge);
			}
			else
			{	
				// The recursion charge is 0, which should never really happen, but I've found cases where it is this value
				$dboRecurringCharge->TimesToCharge = "Infinity";
			}
			//$strToolTipHtml .= $dboRecurringCharge->TimesToCharge->AsOutput();
			$strToolTipHtml .= $dboRecurringCharge->Continuable->AsOutput();
			$strToolTipHtml .= $dboRecurringCharge->UniqueCharge->AsOutput();
			
			$intTimesToCharge = $dboRecurringCharge->TimesToCharge->Value;
/* TODO! Get the end date displaying properly  (Currently it doesn't work for BILLING_FREQ_HALF_MONTH)			
			// Work out the end date
			switch ($dboRecurringCharge->RecurringFreqType->Value)
			{
				case BILLING_FREQ_DAY:
					$intTotalDays = $intTimesToCharge * $dboRecurringCharge->RecurringFreq->Value;
					$intEndTime = strtotime("+{$intTotalDays} days", strtotime($dboRecurringCharge->CreatedOn->Value));
					$strEndTime = date("d/m/Y", $intEndTime);
					break;
				case BILLING_FREQ_MONTH:
					$intTotalMonths = $intTimesToCharge * $dboRecurringCharge->RecurringFreq->Value;
					$intEndTime = strtotime("+{$intTotalMonths} months", strtotime($dboRecurringCharge->CreatedOn->Value));
					$strEndTime = date("d/m/Y", $intEndTime);
					break;
				case BILLING_FREQ_HALF_MONTH:
					$intTotalHalfMonths = $intTimesToCharge * $dboRecurringCharge->RecurringFreq->Value;
					
					$intTotalMonths	= (int)($intTotalHalfMonths / 2);
					$bolHalfwayThroughTheMonth = $intTotalHalfMonth % 2;
					$intEndTime = strtotime("+{$intTotalMonths} months", strtotime($dboRecurringCharge->CreatedOn->Value));
					
					if ($bolHalfwayThroughTheMonth)
					{
						$intOtherEndOfMonth = strtotime("+1 months", $intEndTime);
						$intEndTime = $intEndTime + ((int)(($intOtherEndOfMonth - $intEndTime) / 2));
					}
					
					$strEndTime = date("d/m/Y", $intEndTime);
					break;
			}
			
			$strToolTipHtml .= "<span>end time = $strEndTime</span><br />";
*/			
			
			Table()->RecurringAdjustmentTable->SetToolTip($strToolTipHtml);
		}		
		
		if (DBL()->RecurringCharge->RecordCount() == 0)
		{
			// There are no adjustments to stick in this table
			Table()->RecurringAdjustmentTable->AddRow("<span>No recurring adjustments to display</span>");
			Table()->RecurringAdjustmentTable->SetRowAlignment("left");
			if ($bolHasAdminPerm)
			{
				Table()->RecurringAdjustmentTable->SetRowColumnSpan(3);
			}
			else
			{
				Table()->RecurringAdjustmentTable->SetRowColumnSpan(2);
			}
		}
		else
		{
			Table()->RecurringAdjustmentTable->RowHighlighting = TRUE;
		}

		Table()->RecurringAdjustmentTable->Render();
		
		// Button to add a recurring adjustment
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			// The user can add recurring adjustments
			$strHref = Href()->AddRecurringAdjustment(DBO()->Account->Id->Value);
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->Button("Add Recurring Adjustment", $strHref);
			echo "</div></div>\n";
		}
		else
		{
			// The user can not add recurring adjustments
			// This separator is added for spacing reasons
			echo "<div class='SmallSeperator'></div>\n";
		}
		
		// Sometimes the tooltip is rendered off the bottom of the screen.  This prevents that from being a problem.
		echo "<div style='height:300px'></div>\n";
	}
}

?>
