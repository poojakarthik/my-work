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
		//echo "<div class='WideContent'>\n";

		
		// define the table's header
		Table()->RecurringAdjustmentTable->SetHeader("Date", "Description");
		
		// NOTE: Currently widths and alignments are not taken into account when the table is rendered
		Table()->RecurringAdjustmentTable->SetWidth("20%", "80%");
		Table()->RecurringAdjustmentTable->SetAlignment("Left", "Left");
		
		// add the rows
		foreach (DBL()->RecurringCharge as $dboRecurringCharge)
		{
			Table()->RecurringAdjustmentTable->AddRow($dboRecurringCharge->CreatedOn->AsValue(), $dboRecurringCharge->Description->AsValue());
			
			// add tooltip
			$strToolTipHtml = $dboRecurringCharge->LastChargedOn->AsOutput();
			$strToolTipHtml .= $dboRecurringCharge->TotalCharged->AsOutput();
			
			Table()->RecurringAdjustmentTable->SetToolTip($strToolTipHtml);
			
			// add indexes
			//TODO! 
		}		
		
		// Link other tables to this one
		//TODO!
		
		Table()->RecurringAdjustmentTable->RowHighlighting = TRUE;

		Table()->RecurringAdjustmentTable->Render();
		//echo "</div>\n";
		//echo "<div class='Seperator'></div>\n";
	}
}

?>
