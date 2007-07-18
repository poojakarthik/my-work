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

		// Check if the user has admin privileges
		$bolHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		// define the table's header
		if ($bolHasAdminPerm)
		{
			// User has admin permisions and can therefore delete an adjustment
			Table()->AdjustmentTable->SetHeader("Date", "Code", "Amount", "&nbsp;");
			Table()->AdjustmentTable->SetWidth("20%", "30%", "40%", "10%");
			Table()->AdjustmentTable->SetAlignment("left", "left", "right", "center");
		}
		else
		{
			// User cannot delete adjustments
			Table()->AdjustmentTable->SetHeader("Date", "Code", "Amount");
			Table()->AdjustmentTable->SetWidth("20%", "30%", "50%");
			Table()->AdjustmentTable->SetAlignment("left", "left", "right");
		
		}
		
		// add the rows
		foreach (DBL()->Charge as $dboCharge)
		{
			// add the row
			if ($bolHasAdminPerm)
			{
				// Only charges having status = waiting or approved can be deleted
				if (($dboCharge->Status->Value == CHARGE_WAITING) || ($dboCharge->Status->Value == CHARGE_APPROVED))
				{
					// build the "Delete Adjustment" link
					$strDeleteAdjustmentHref  = Href()->DeleteAdjustment($dboCharge->Id->Value);
					$strDeleteAdjustmentLabel = "<span class='DefaultOutputSpan Default'><a href='$strDeleteAdjustmentHref'><img src='img/template/delete.png' title='Delete Adjustment' /></a></span>";
				}
				else
				{
					$strDeleteAdjustmentLabel = "";
				}
				
				Table()->AdjustmentTable->AddRow(	$dboCharge->CreatedOn->AsValue(),
												//$dboCharge->Status->AsCallback("GetConstantDescription", Array("ChargeStatus")), 
												$dboCharge->ChargeType->AsValue(),
												$dboCharge->Amount->AsCallback("AddGST"),
												$strDeleteAdjustmentLabel);
			}
			else
			{
				Table()->AdjustmentTable->AddRow(	$dboCharge->CreatedOn->AsValue(),
												//$dboCharge->Status->AsCallback("GetConstantDescription", Array("ChargeStatus")), 
												$dboCharge->ChargeType->AsValue(),
												$dboCharge->Amount->AsCallback("AddGST"));
			}
			
			// add tooltip
			$strToolTipHtml = $dboCharge->CreatedBy->AsCallback("GetEmployeeName", NULL, RENDER_OUTPUT);
			$strToolTipHtml .= $dboCharge->ApprovedBy->AsCallback("GetEmployeeName", NULL, RENDER_OUTPUT);
			$strStatus = GetConstantDescription($dboCharge->Status->Value, "ChargeStatus");
			$strToolTipHtml .= $dboCharge->Status->AsArbitrary($strStatus, RENDER_OUTPUT);
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
		
		// button to add an adjustment
		$strHref = Href()->AddAdjustment(DBO()->Account->Id->Value);
		echo "<div class='Right'>\n";
		$this->Button("Add Adjustment", $strHref);
		echo "</div>\n";
		
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
		echo "<div class='SmallSeperator'></div>\n";
	}
}

?>
