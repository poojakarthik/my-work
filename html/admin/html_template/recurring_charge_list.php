<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// recurring_charge_list.php
//----------------------------------------------------------------------------//
/**
 * recurring_charge_list
 *
 * HTML Template for the Recurring Charge List HTML object
 *
 * HTML Template for the Recurring Charge List HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all recurring charges relating to an account and can be embedded in
 * various Page Templates
 *
 * @file		recurring_charge_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRecurringChargeList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRecurringChargeList
 *
 * HTML Template class for the Recurring Charge List HTML object
 *
 * HTML Template class for the Recurring Charge List HTML object
 * Lists all charges related to an account
 *
 * @package	ui_app
 * @class	HtmlTemplateRecurringChargeList
 * @extends	HtmlTemplate
 */
class HtmlTemplateRecurringChargeList extends HtmlTemplate
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
		echo "<h2 class='Charge'>Recurring Charges</h2>\n";

		// Check if the user has admin privileges
		$bolHasAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		$bolUserIsGod		= AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD);
		$bolUserHasProperAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
		$bolHasCreditManagementPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT);
		
		$bolCanCancelRecurringCharges	= ($bolUserHasProperAdminPerm || $bolHasCreditManagementPerm);
		
		// define the table's header
		if ($bolCanCancelRecurringCharges)
		{
			// User has admin permisions and can therefore delete an charge
			Table()->RecurringChargeTable->SetHeader("Date", "Description", "Status", "&nbsp;");
			Table()->RecurringChargeTable->SetAlignment("left", "left", "left", "center");
		}
		else
		{
			// User cannot delete charges
			Table()->RecurringChargeTable->SetHeader("Date", "Description", "Status");
			Table()->RecurringChargeTable->SetAlignment("left", "left", "left");
		}
		
		// add the rows
		foreach (DBL()->RecurringCharge as $dboRecurringCharge)
		{
			$objRecurringChargeStatus = Recurring_Charge_Status::getForId($dboRecurringCharge->recurring_charge_status_id->Value);
			
			$strChargeTypeDescription = htmlspecialchars($dboRecurringCharge->ChargeType->Value ." - ". $dboRecurringCharge->Description->Value);
			
			$strRecurringChargeStatus = htmlspecialchars($objRecurringChargeStatus->name);
			
			$strStartedOnFormatted = date("d-m-Y", strtotime($dboRecurringCharge->StartedOn->Value));
			
			if ($bolCanCancelRecurringCharges)
			{
				$strCancelRecurringChargeHref = Href()->CancelRecurringCharge($dboRecurringCharge->Id->Value);
				
				// Check if the Recurring Charge (or request for Recurring Charge) can be Cancelled
				if ($objRecurringChargeStatus->id == Recurring_Charge_Status::getIdForSystemName('AWAITING_APPROVAL'))
				{
					$strCancelRecurringChargeLabel = "<img src='img/template/delete.png' title='Cancel Recurring Charge Request' onclick='$strCancelRecurringChargeHref'></img>";
				}
				elseif ($objRecurringChargeStatus->id == Recurring_Charge_Status::getIdForSystemName('ACTIVE'))
				{
					$strCancelRecurringChargeLabel = "<img src='img/template/delete.png' title='Cancel Recurring Charge' onclick='$strCancelRecurringChargeHref'></img>";
				}
				else
				{
					$strCancelRecurringChargeLabel = "&nbsp;";
				}
				
				Table()->RecurringChargeTable->AddRow($strStartedOnFormatted, $strChargeTypeDescription, $strRecurringChargeStatus, $strCancelRecurringChargeLabel);
			}
			else
			{
				Table()->RecurringChargeTable->AddRow($strStartedOnFormatted, $strChargeTypeDescription, $strRecurringChargeStatus);
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
			
			if ($dboRecurringCharge->in_advance->Value == TRUE)
			{
				$dboRecurringCharge->charged = "In Advance";
			}
			else
			{
				// Recurring Charge is charged in arrears
				if ($dboRecurringCharge->LastChargedOn->Value == $dboRecurringCharge->StartedOn->Value)
				{
					// The LastChargedOn does not truely represent the last time the account was charged
					// Set it to NULL
					$dboRecurringCharge->LastChargedOn = NULL;
				}
				$dboRecurringCharge->charged = "In Arrears";
			}
			$strToolTipHtml .= $dboRecurringCharge->charged->AsOutput();
			if ($dboRecurringCharge->TotalCharged->Value > 0.0)
			{
				$strToolTipHtml .= $dboRecurringCharge->LastChargedOn->AsOutput();
				$strToolTipHtml .= $dboRecurringCharge->TotalCharged->AsCallback("AddGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
			}
			
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
			
			Table()->RecurringChargeTable->SetToolTip($strToolTipHtml);
			
			// Add Indexes
			Table()->RecurringChargeTable->AddIndex("RecurringChargeId", $dboRecurringCharge->Id->Value);
		}
		
		if (DBL()->RecurringCharge->RecordCount() == 0)
		{
			// There are no charges to stick in this table
			Table()->RecurringChargeTable->AddRow("<span>No recurring charges to display</span>");
			Table()->RecurringChargeTable->SetRowAlignment("left");
			Table()->RecurringChargeTable->SetRowColumnSpan(0);
		}
		else
		{
			// Link other tables to this one
			Table()->RecurringChargeTable->LinkTable("ChargeTable", "RecurringChargeId");
			Table()->RecurringChargeTable->RowHighlighting = TRUE;
		}

		Table()->RecurringChargeTable->Render();
		
		// Button to add a recurring charge
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			// The user can add recurring charges
			$strHref = Href()->AddRecurringCharge(DBO()->Account->Id->Value);
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->Button("Request Recurring Charge", $strHref);
			echo "</div></div>\n";
		}
		else
		{
			// The user can not add recurring charges
			// This separator is added for spacing reasons
			echo "<div class='SmallSeperator'></div>\n";
		}
		
		// Sometimes the tooltip is rendered off the bottom of the screen.  This prevents that from being a problem.
		echo "<div style='height:330px'></div>\n";
	}
}

?>
