<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// adjustment_list.php
//----------------------------------------------------------------------------//
/**
 * adjustment_list
 *
 * HTML Template for the Adjustment List HTML object
 *
 * HTML Template for the Adjustment List HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all adjustments relating to an account and can be embedded in
 * various Page Templates
 *
 * @file		adjustment_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateAdjustmentList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAdjustmentList
 *
 * HTML Template class for the Adjustment List HTML object
 *
 * HTML Template class for the Adjustment List HTML object
 * Lists all adjustments related to an account
 *
 * @package	ui_app
 * @class	HtmlTemplateAdjustmentList
 * @extends	HtmlTemplate
 */
class HtmlTemplateAdjustmentList extends HtmlTemplate
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
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		//$this->LoadJavascript("validate_adjustment");
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
		echo "<h2 class='Adjustment'>Adjustments</h2>\n";
		echo "<div class='NarrowColumn'>\n";

		
		// define the table's header
		Table()->AdjustmentTable->SetHeader("Date", "Code", "Amount");
		
		// NOTE: Currently widths and alignments are not taken into account when the table is rendered
		Table()->AdjustmentTable->SetWidth("20%", "30%", "50%");
		Table()->AdjustmentTable->SetAlignment("Left", "Left", "Right");
		
		// add the rows
		foreach (DBL()->Charge as $dboCharge)
		{
			Table()->AdjustmentTable->AddRow(	$dboCharge->CreatedOn->AsValue(),
												//$dboCharge->Status->AsCallback("GetConstantDescription", Array("ChargeStatus")), 
												$dboCharge->ChargeType->AsValue(),
												$dboCharge->Amount->AsCallback("AddGST"));
			
			// add tooltip
			$strToolTipHtml = $dboCharge->CreatedBy->AsCallback("GetEmployeeName", NULL, RENDER_OUTPUT);
			$strToolTipHtml .= $dboCharge->ApprovedBy->AsCallback("GetEmployeeName", NULL, RENDER_OUTPUT);
			$strToolTipHtml .= $dboCharge->Description->AsOutput();
			$strToolTipHtml .= $dboCharge->Notes->AsOutput();
			Table()->AdjustmentTable->SetToolTip($strToolTipHtml);
			
			// add indexes
			Table()->AdjustmentTable->AddIndex("InvoiceRun", $dboCharge->InvoiceRun->Value);
		}
		
		// Link other tables to this one
		Table()->AdjustmentTable->LinkTable("InvoiceTable", "InvoiceRun");
		
		Table()->AdjustmentTable->RowHighlighting = TRUE;
		Table()->AdjustmentTable->Render();
		echo "</div>\n";
		//echo "<div class='Seperator'></div>\n";
	}
}

?>
