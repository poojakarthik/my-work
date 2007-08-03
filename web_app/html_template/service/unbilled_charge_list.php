<?php
//----------------------------------------------------------------------------//
// HtmlTemplateServiceUnbilledChargeList DEPRECIATED use HtmlTemplateUnbilledChargeList instead
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceUnbilledChargeList
 *
 * HTML Template object for the client app, List of all Unbilled charges for Service
 *
 * HTML Template object for the client app, List of all Unbilled charges for Service
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateServiceUnbilledChargeList
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceUnbilledChargeList extends HtmlTemplate
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
		$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
		//$this->LoadJavascript("tooltip");
		
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
		echo "<div class='WideContent'>\n";
		echo "<h2 class='Adjustment'>Unbilled Adjustments</h2>\n";
				
		Table()->Adjustments->SetHeader("Date", "Code", "Description", "Amount (inc GST)", "&nbsp;");
		Table()->Adjustments->SetWidth("10%", "15%", "50%", "20%", "5%");
		Table()->Adjustments->SetAlignment("left", "left", "left", "right", "left");
		
		// Declare variable to store the Total adjustments
		$fltTotalAdjustments = 0;
		
		// add the rows
		foreach (DBL()->Charge as $dboCharge)
		{
			Table()->Adjustments->AddRow($dboCharge->CreatedOn->AsValue(),
											$dboCharge->ChargeType->AsValue(),
											$dboCharge->Description->AsValue(),
											$dboCharge->Amount->AsCallback("AddGST"),
											$dboCharge->Nature->AsValue());

			if ($dboCharge->Nature->Value == NATURE_DR)
			{
				// Add the charge to the total adjustments
				$fltTotalAdjustments += $dboCharge->Amount->Value;
			}
			else
			{
				// Subtract the charge from the total adjustments
				$fltTotalAdjustments -= $dboCharge->Amount->Value;
			}
		}
		
		// Add GST to the total adjustments
		$fltTotalAdjustments = AddGST($fltTotalAdjustments);
		
		if (Table()->Adjustments->RowCount() == 0)
		{
			// There are no adjustments to stick in this table
			Table()->Adjustments->AddRow("<span class='DefaultOutputSpan Default'>No adjustments to list</span>");
			Table()->Adjustments->SetRowAlignmnet("center");
			Table()->Adjustments->SetRowColumnSpan(5);
		}
		else
		{
			// Append the total to the table
			$strTotal				= "<span class='DefaultOutputSpan Default' style='font-weight:bold;'>Total Adjustments:</span>\n";
			$strTotalAdjustments	= "<span class='DefaultOutputSpan Currency' style='font-weight:bold;'>". OutputMask()->MoneyValue($fltTotalAdjustments, 2, TRUE) ."</span>\n";
			
			Table()->Adjustments->AddRow($strTotal, $strTotalAdjustments, "&nbsp;");
			Table()->Adjustments->SetRowAlignment("left", "right", "&nbsp;");
			Table()->Adjustments->SetRowColumnSpan(3, 1, 1);
		}
		
		// You may want to Append a row to the end of this table which displays the total value of the adjustments
		
		Table()->Adjustments->Render();
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
