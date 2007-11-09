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
		echo "<div class='NarrowColumn'>\n";

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
			
			// add tooltip
			if ($dboRecurringCharge->Service->Value)
			{
				// The Recurring Charge is a Service Recurring Charge.  Display the FNN of the Service
				$strFNN = $dboRecurringCharge->FNN->AsOutput();
			}
			else
			{
				$strFNN = "";
			}
			$strToolTipHtml  = $strFNN;
			$strToolTipHtml .= $dboRecurringCharge->LastChargedOn->AsOutput();
			$strToolTipHtml .= $dboRecurringCharge->TotalCharged->AsCallback("AddGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
			$strToolTipHtml .= $dboRecurringCharge->Nature->AsOutput();
			
			Table()->RecurringAdjustmentTable->SetToolTip($strToolTipHtml);
		}		
		
		if (DBL()->RecurringCharge->RecordCount() == 0)
		{
			// There are no adjustments to stick in this table
			Table()->RecurringAdjustmentTable->AddRow("<span class='DefaultOutputSpan Default'>No recurring adjustments to display</span>");
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
		
		$strHref = Href()->AddRecurringAdjustment(DBO()->Account->Id->Value);
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Add Recurring Adjustment", $strHref);
		echo "</div></div>\n";
		echo "</div>\n";
	}
}

?>
