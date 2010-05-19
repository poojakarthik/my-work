<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// charge_list.php
//----------------------------------------------------------------------------//
/**
 * charge_list
 *
 * HTML Template for the Charge List HTML object
 *
 * HTML Template for the Charge List HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all charges relating to an account and can be embedded in
 * various Page Templates
 *
 * @file		charge_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateChargeList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateChargeList
 *
 * HTML Template class for the Charge List HTML object
 *
 * HTML Template class for the Charge List HTML object
 * Lists all charges related to an account
 *
 * @package	ui_app
 * @class	HtmlTemplateChargeList
 * @extends	HtmlTemplate
 */
class HtmlTemplateChargeList extends HtmlTemplate
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
		echo "<h2 class='Charge'>Charges</h2>\n";

		// Check if the user has admin privileges
		$bolHasProperAdminPerm		= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
		$bolHasCreditManagementPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT);
		$bolUserIsGod				= AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD);
		$bolUserHasOperatorPerm		= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		
		$bolUserCanDeleteCharges	= ($bolHasProperAdminPerm || $bolHasCreditManagementPerm);
		
		// define the table's header
		if ($bolUserCanDeleteCharges)
		{
			// User has admin permisions and can therefore delete an charge
			Table()->ChargeTable->SetHeader("Date", "Code","Amount ($)", "&nbsp;", "&nbsp;");
			//Table()->ChargeTable->SetWidth("20%", "29%", "3%", "38%", "10%");
			Table()->ChargeTable->SetAlignment("left", "left", "right", "left", "right");
		}
		else
		{
			// User cannot delete charges
			Table()->ChargeTable->SetHeader("Date", "Code","Amount ($)", "&nbsp;");
			//Table()->ChargeTable->SetWidth("20%", "29%", "3%", "48%");
			Table()->ChargeTable->SetAlignment("left", "left", "right", "left");
		
		}
		
		// add the rows
		foreach (DBL()->Charge as $dboCharge)
		{
			$strNature				= ($dboCharge->Nature->Value == NATURE_CR)? 'CR' : "&nbsp;";
			$strChargedOnFormatted	= date('d-m-Y', strtotime($dboCharge->ChargedOn->Value));
			
			$strChargeTypeField = $dboCharge->ChargeType->Value;
			if ($dboCharge->Status->Value == CHARGE_WAITING)
			{
				$strChargeTypeField .= "<br />(Awaiting Approval)";
			}
			
			
			// add the row
			if ($bolUserCanDeleteCharges)
			{
				$strDeleteChargeHref = Href()->DeleteCharge($dboCharge->Id->Value);
				
				// Only charges having status = waiting or approved can be deleted
				if ($dboCharge->Status->Value == CHARGE_WAITING)
				{
					// build the "Cancel Charge Request" link
					$strDeleteChargeLabel = "<img src='img/template/delete.png' title='Cancel Charge Request' onclick='$strDeleteChargeHref'></img>";
				}
				elseif (($dboCharge->Status->Value == CHARGE_APPROVED) || ($dboCharge->Status->Value == CHARGE_TEMP_INVOICE))
				{
					// build the "Delete Charge" link
					$strDeleteChargeLabel = "<img src='img/template/delete.png' title='Delete Charge' onclick='$strDeleteChargeHref'></img>";
				}
				else
				{
					$strDeleteChargeLabel = "&nbsp;";
				}
				
				Table()->ChargeTable->AddRow($strChargedOnFormatted,
												$strChargeTypeField,
												$dboCharge->Amount->AsCallback("AddGST"),
												$strNature,
												$strDeleteChargeLabel);
			}
			else
			{
				Table()->ChargeTable->AddRow($strChargedOnFormatted,
												$strChargeTypeField,
												$dboCharge->Amount->AsCallback("AddGST"),
												$strNature);
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
				$dboCharge->RequestedBy = $dboCharge->CreatedBy->Value;
				$strToolTipHtml .= $dboCharge->RequestedBy->AsCallback("GetEmployeeName", NULL, RENDER_OUTPUT);
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
			
			Table()->ChargeTable->SetToolTip($strToolTipHtml);
			
			// Add indexes
			Table()->ChargeTable->AddIndex("invoice_run_id", $dboCharge->invoice_run_id->Value);
			if ($dboCharge->LinkType->Value == CHARGE_LINK_PAYMENT)
			{
				// This charge relates directly to a payment
				Table()->ChargeTable->AddIndex("PaymentId", $dboCharge->LinkId->Value);
			} 
			elseif ($dboCharge->LinkType->Value == CHARGE_LINK_RECURRING)
			{
				// This charge relates directly to a recurring charge
				Table()->ChargeTable->AddIndex("RecurringChargeId", $dboCharge->LinkId->Value);
			}
		}

		if (DBL()->Charge->RecordCount() == 0)
		{
			// There are no charges to stick in this table
			Table()->ChargeTable->AddRow("<span class='DefaultOutputSpan Default'>No charges to display</span>");
			Table()->ChargeTable->SetRowAlignment("left");
			if ($bolHasProperAdminPerm)
			{
				Table()->ChargeTable->SetRowColumnSpan(5);
			}
			else
			{
				Table()->ChargeTable->SetRowColumnSpan(4);
			}
		}
		else
		{
			// Link other tables to this one
			Table()->ChargeTable->LinkTable("InvoiceTable", "invoice_run_id");
			Table()->ChargeTable->LinkTable("RecurringChargeTable", "RecurringChargeId");
			
			// The current implementation of the highlighting of associated records cannot handle this link
			//Table()->ChargeTable->LinkTable("PaymentTable", "PaymentId");
			
			Table()->ChargeTable->RowHighlighting = TRUE;
		}

		Table()->ChargeTable->Render();
		
		// Button to add an charge
		if ($bolUserHasOperatorPerm)
		{
			// The user can add charges
			$strHref = Href()->AddCharge(DBO()->Account->Id->Value);
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->Button("Request Charge", $strHref);
			echo "</div></div>\n";
		}
		else
		{
			// The user can not add charges
			// This separator is added for spacing reasons
			echo "<div class='SmallSeperator'></div>\n";
		}
	}
}

?>
