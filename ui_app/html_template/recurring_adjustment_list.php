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
		$bolHasAdminPerm = AuthenticatedUser()->UserHasPerm(PRIVILEGE_ADMIN);
		
		//HACK HACK HACK!!!! remove this line when we have properly implemented users loging in
		$bolHasAdminPerm = TRUE;
		//HACK HACK HACK!!!!
		
		// define the table's header
		if ($bolHasAdminPerm)
		{
			// User has admin permisions and can therefore delete an adjustment
			Table()->RecurringAdjustmentTable->SetHeader("Date", "Description", "");
			Table()->RecurringAdjustmentTable->SetWidth("20%", "70%", "10%");
			Table()->RecurringAdjustmentTable->SetAlignment("Left", "Left", "Center");
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
					$strDeleteRecurringAdjustmentLabel = "<span class='DefaultOutputSpan Default'><a href='$strDeleteRecurringAdjustmentHref' class='DeleteButton'></a></span>";
				}
				else
				{
					$strDeleteRecurringAdjustmentLabel = "";
				}
				
				Table()->RecurringAdjustmentTable->AddRow($dboRecurringCharge->CreatedOn->AsValue(), $dboRecurringCharge->Description->AsValue(), $strDeleteRecurringAdjustmentLabel);
			}
			else
			{
				Table()->RecurringAdjustmentTable->AddRow($dboRecurringCharge->CreatedOn->AsValue(), $dboRecurringCharge->Description->AsValue());
			}
			// add tooltip
			$strToolTipHtml = $dboRecurringCharge->LastChargedOn->AsOutput();
			$strToolTipHtml .= $dboRecurringCharge->TotalCharged->AsCallback("AddGST", NULL, RENDER_OUTPUT);
			
			Table()->RecurringAdjustmentTable->SetToolTip($strToolTipHtml);
			
			// add indexes
			//TODO! 
		}		
		
		// Link other tables to this one
		//TODO!
		
		Table()->RecurringAdjustmentTable->RowHighlighting = TRUE;

		Table()->RecurringAdjustmentTable->Render();
		
		$strHref = Href()->AddRecurringAdjustment(DBO()->Account->Id->Value);
		echo "<div class='Right'>\n";
		$this->Button("Add Recurring Adjustment", $strHref);
		echo "</div>\n";
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
		echo "<div class='Seperator'></div>\n";
		//echo "<div class='Seperator'></div>\n";
	}
}

?>
