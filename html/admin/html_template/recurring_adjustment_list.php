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
		$this->LoadJavascript("tooltip");
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
		$bolHasAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		$bolUserIsGod		= AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD);
		
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
					$strDeleteRecurringAdjustmentLabel = "<img src='img/template/delete.png' title='Cancel Recurring Adjustment' onclick='$strDeleteRecurringAdjustmentHref'></img>";
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
			$strToolTipHtml = "";
			if ($bolUserIsGod)
			{
				// Display the associated RecurringCharge Id if the user is GOD
				$strToolTipHtml .= $dboRecurringCharge->Id->AsOutput();
			}
			if ($dboRecurringCharge->Service->Value)
			{
				if ($bolUserIsGod)
				{
					// Display the associated service Id if the user is GOD
					$strToolTipHtml .= $dboRecurringCharge->Service->AsOutput();
				}
				// The Recurring Charge is a Service Recurring Charge.  Display the FNN of the Service
				$strToolTipHtml .= $dboRecurringCharge->FNN->AsOutput();
			}
			
			// Add GST to the MinCharge and RecursionCharge
			$dboRecurringCharge->MinCharge			= AddGST($dboRecurringCharge->MinCharge->Value);
			$dboRecurringCharge->RecursionCharge	= AddGST($dboRecurringCharge->RecursionCharge->Value);
			
			// TimesToCharge requires the Recursion Charge to not equal 0
			if (($dboRecurringCharge->RecursionCharge->Value) != 0)
			{
				// Calculate the required number of recursions
				$fltMinCharge = OutputMask()->FormatFloat($dboRecurringCharge->MinCharge->Value, 2, 2);
				$fltRecursionCharge = OutputMask()->FormatFloat($dboRecurringCharge->RecursionCharge->Value, 2, 2);
				
				$dboRecurringCharge->TimesToCharge = ceil(abs(($fltMinCharge / $fltRecursionCharge) - 0.01));
			}
			else
			{	
				// The recursion charge is 0, which should never really happen, but I've found cases where it is this value
				$dboRecurringCharge->TimesToCharge = "Infinity";
			}
			
			$strToolTipHtml .= $dboRecurringCharge->LastChargedOn->AsOutput();
			$strToolTipHtml .= $dboRecurringCharge->TotalCharged->AsCallback("AddGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
			$strToolTipHtml .= $dboRecurringCharge->Nature->AsOutput();
			DBO()->ChargeTypesAvailable->RecurringFreqType = $dboRecurringCharge->RecurringFreqType->Value;
			$strRecurringFreq = $dboRecurringCharge->RecurringFreq->Value ." ". DBO()->ChargeTypesAvailable->RecurringFreqType->FormattedValue();
			$strToolTipHtml .= $dboRecurringCharge->RecurringFreq->AsArbitrary($strRecurringFreq, RENDER_OUTPUT);
			$strToolTipHtml .= $dboRecurringCharge->TimesToCharge->AsOutput();
			$strToolTipHtml .= $dboRecurringCharge->TotalRecursions->AsOutput();
			$strToolTipHtml .= $dboRecurringCharge->CancellationFee->AsCallback("AddGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
			$strToolTipHtml .= $dboRecurringCharge->MinCharge->AsOutput(CONTEXT_INCLUDES_GST);
			$strToolTipHtml .= $dboRecurringCharge->RecursionCharge->AsOutput(CONTEXT_INCLUDES_GST);
			$strToolTipHtml .= $dboRecurringCharge->Continuable->AsOutput();
			$strToolTipHtml .= $dboRecurringCharge->UniqueCharge->AsOutput();
			
			
			$intTimesToCharge = $dboRecurringCharge->TimesToCharge->Value;
			// Work out the end date
			if (is_numeric($intTimesToCharge))
			{
				// The end date depends on the Recurring Frequency type, the recurring frequency and the times to charge
				switch ($dboRecurringCharge->RecurringFreqType->Value)
				{
					case BILLING_FREQ_DAY:
						$intTotalNumOfDays	= $intTimesToCharge * $dboRecurringCharge->RecurringFreq->Value;
						$intEndTime			= strtotime("+{$intTotalNumOfDays} days", strtotime($dboRecurringCharge->StartedOn->Value));
						break;
						
					case BILLING_FREQ_MONTH:
						$intTotalNumOfMonths	= $intTimesToCharge * $dboRecurringCharge->RecurringFreq->Value;
						$intEndTime				= strtotime("+{$intTotalNumOfMonths} months", strtotime($dboRecurringCharge->StartedOn->Value));
						break;
						
					case BILLING_FREQ_HALF_MONTH:
						// If there is an even number of half months, then you can just work out how many whole months to add to the CreatedOn date
						// If there is an odd number of half months, then add the even number of months on to the CreatedOn date; find out
						// what 1 month beyond this date would be and then find the middle of these 2 dates expressed in seconds
						$intTotalNumOfHalfMonths	= $intTimesToCharge * $dboRecurringCharge->RecurringFreq->Value;
						$intTotalNumOfMonths		= (int)($intTotalNumOfHalfMonths / 2);
						$bolExtraHalfMonth			= $intTotalNumOfHalfMonths % 2;
						$intEndTime					= strtotime("+{$intTotalNumOfMonths} months", strtotime($dboRecurringCharge->StartedOn->Value));
						
						if ($bolExtraHalfMonth)
						{
							$intOneMonthBeyondEndTime	= strtotime("+1 months", $intEndTime);
							$intEndTime					= $intEndTime + ((int)(($intOneMonthBeyondEndTime - $intEndTime) / 2));
						}
						break;
				}
				$strEndTime = date("d/m/Y", $intEndTime);
			}
			else
			{
				// TimesToCharge is not a number.  It must equal Infinity
				$strEndTime = "Infinity";
			}
			
			$strToolTipHtml .= $dboRecurringCharge->StartedOn->AsOutput();
			
			$dboRecurringCharge->EndDate = $strEndTime;
			$strToolTipHtml .= $dboRecurringCharge->EndDate->AsOutput();
			
			Table()->RecurringAdjustmentTable->SetToolTip($strToolTipHtml);
			
			// Add Indexes
			Table()->RecurringAdjustmentTable->AddIndex("RecurringAdjustmentId", $dboRecurringCharge->Id->Value);
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
			// Link other tables to this one
			Table()->RecurringAdjustmentTable->LinkTable("AdjustmentTable", "RecurringAdjustmentId");
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
		echo "<div style='height:330px'></div>\n";
	}
}

?>
