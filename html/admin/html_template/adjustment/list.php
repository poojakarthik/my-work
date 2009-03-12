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
		echo "<h2 class='Adjustment'>Adjustments</h2>\n";

		// Check if the user has admin privileges
		$bolHasProperAdminPerm		= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
		$bolHasCreditManagementPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT);
		$bolUserIsGod				= AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD);
		$bolUserHasOperatorPerm		= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		
		$bolUserCanDeleteCharges	= ($bolHasProperAdminPerm || $bolHasCreditManagementPerm);
		
		// define the table's header
		if ($bolUserCanDeleteCharges)
		{
			// User has admin permisions and can therefore delete an adjustment
			Table()->AdjustmentTable->SetHeader("Date", "Code", "&nbsp;","Amount ($)", "&nbsp;");
			Table()->AdjustmentTable->SetWidth("20%", "29%", "3%", "38%", "10%");
			Table()->AdjustmentTable->SetAlignment("left", "left", "left", "right", "center");
		}
		else
		{
			// User cannot delete adjustments
			Table()->AdjustmentTable->SetHeader("Date", "Code", "&nbsp;", "Amount ($)");
			Table()->AdjustmentTable->SetWidth("20%", "29%", "3%", "48%");
			Table()->AdjustmentTable->SetAlignment("left", "left", "left", "right");
		
		}
		
		// add the rows
		foreach (DBL()->Charge as $dboCharge)
		{
			if ($dboCharge->Nature->Value == NATURE_CR)
			{
				$strNature = $dboCharge->Nature->FormattedValue();
			}
			else
			{
				$strNature = "&nbsp;";
			}
			
			// add the row
			if ($bolUserCanDeleteCharges)
			{
				// Only charges having status = waiting or approved can be deleted
				if (($dboCharge->Status->Value == CHARGE_WAITING) || ($dboCharge->Status->Value == CHARGE_APPROVED) || ($dboCharge->Status->Value == CHARGE_TEMP_INVOICE))
				{
					// build the "Delete Adjustment" link
					$strDeleteAdjustmentHref  = Href()->DeleteAdjustment($dboCharge->Id->Value);
					$strDeleteAdjustmentLabel = "<img src='img/template/delete.png' title='Delete Adjustment' onclick='$strDeleteAdjustmentHref'></img>";
				}
				else
				{
					$strDeleteAdjustmentLabel = "&nbsp;";
				}
				
				
				
				Table()->AdjustmentTable->AddRow($dboCharge->ChargedOn->FormattedValue(),
												$dboCharge->ChargeType->Value,
												$strNature,
												$dboCharge->Amount->AsCallback("AddGST"),
												$strDeleteAdjustmentLabel);
			}
			else
			{
				Table()->AdjustmentTable->AddRow($dboCharge->ChargedOn->FormattedValue(),
												$dboCharge->ChargeType->Value,
												$strNature,
												$dboCharge->Amount->AsCallback("AddGST"));
			}
			
			// add tooltip
			$strToolTipHtml = "";
			if ($bolUserIsGod)
			{
				// Display the associated charge Id if the user is GOD
				$strToolTipHtml .= $dboCharge->Id->AsOutput();
			}
			
			if ($dboCharge->Service->Value)
			{
				if ($bolUserIsGod)
				{
					// Display the associated service Id if the user is GOD
					$strToolTipHtml .= $dboCharge->Service->AsOutput();
				}
				// The Charge is a Service Charge. Display the FNN
				$strToolTipHtml .= $dboCharge->FNN->AsOutput();
			}
			
			if ($dboCharge->CreatedBy->Value && $dboCharge->CreatedBy->Value != USER_ID)
			{
				$strToolTipHtml .= $dboCharge->CreatedBy->AsCallback("GetEmployeeName", NULL, RENDER_OUTPUT);
			}
			
			if ($dboCharge->ApprovedBy->Value && $dboCharge->ApprovedBy->Value != USER_ID)
			{
				$strToolTipHtml .= $dboCharge->ApprovedBy->AsCallback("GetEmployeeName", NULL, RENDER_OUTPUT);
			}
			
			$strStatus = GetConstantDescription($dboCharge->Status->Value, "ChargeStatus");
			$strToolTipHtml .= $dboCharge->Status->AsArbitrary($strStatus, RENDER_OUTPUT);
			$strToolTipHtml .= $dboCharge->Description->AsOutput();
			
			if ($dboCharge->Notes->Value != "")
			{
				$strToolTipHtml .= $dboCharge->Notes->AsOutput();
			}
			
			Table()->AdjustmentTable->SetToolTip($strToolTipHtml);
			
			// Add indexes
			Table()->AdjustmentTable->AddIndex("invoice_run_id", $dboCharge->invoice_run_id->Value);
			if ($dboCharge->LinkType->Value == CHARGE_LINK_PAYMENT)
			{
				// This charge relates directly to a payment
				Table()->AdjustmentTable->AddIndex("PaymentId", $dboCharge->LinkId->Value);
			} 
			elseif ($dboCharge->LinkType->Value == CHARGE_LINK_RECURRING)
			{
				// This charge relates directly to a recurring adjustment
				Table()->AdjustmentTable->AddIndex("RecurringAdjustmentId", $dboCharge->LinkId->Value);
			}
		}

		if (DBL()->Charge->RecordCount() == 0)
		{
			// There are no adjustments to stick in this table
			Table()->AdjustmentTable->AddRow("<span class='DefaultOutputSpan Default'>No adjustments to display</span>");
			Table()->AdjustmentTable->SetRowAlignment("left");
			if ($bolHasProperAdminPerm)
			{
				Table()->AdjustmentTable->SetRowColumnSpan(5);
			}
			else
			{
				Table()->AdjustmentTable->SetRowColumnSpan(4);
			}
		}
		else
		{
			// Link other tables to this one
			Table()->AdjustmentTable->LinkTable("InvoiceTable", "invoice_run_id");
			Table()->AdjustmentTable->LinkTable("RecurringAdjustmentTable", "RecurringAdjustmentId");
			
			// The current implementation of the highlighting of associated records cannot handle this link
			//Table()->AdjustmentTable->LinkTable("PaymentTable", "PaymentId");
			
			Table()->AdjustmentTable->RowHighlighting = TRUE;
		}

		Table()->AdjustmentTable->Render();
		
		// Button to add an adjustment
		if ($bolUserHasOperatorPerm)
		{
			// The user can add adjustments
			$strHref = Href()->AddAdjustment(DBO()->Account->Id->Value);
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->Button("Add Adjustment", $strHref);
			echo "</div></div>\n";
		}
		else
		{
			// The user can not add adjustments
			// This separator is added for spacing reasons
			echo "<div class='SmallSeperator'></div>\n";
		}
	}
}

?>
