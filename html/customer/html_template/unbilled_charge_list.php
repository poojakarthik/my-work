<?php
//----------------------------------------------------------------------------//
// HtmlTemplateUnbilledChargeList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateUnbilledChargeList
 *
 * HTML Template object for the client app, List of all Unbilled charges for an Account or a specific service
 *
 * HTML Template object for the client app, List of all Unbilled charges for an Account or a specific service
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateUnbilledChargeList
 * @extends	HtmlTemplate
 */
class HtmlTemplateUnbilledChargeList extends HtmlTemplate
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
		//echo "<h2 class='Adjustment'>Unbilled Debits & Credits</h2>\n";
		echo "
		<TABLE width=\"100%\">
		<TR>
			<TD style=\"width: 200px; text-indent: 5; font-size: 8pt;\">View Itemisation | <a href=\"#\" onclick=\"javascript:window.print();return false;\">Print This Page</a></TD>
			<TD style=\"text-indent: 175; font-weight: bold;\">Unbilled Debits & Credits</TD>
		</TR>
		</TABLE>\n";
				
		Table()->Adjustments->SetHeader("Date", "Code", "Description", "Amount (inc GST)", "&nbsp;");
		Table()->Adjustments->SetWidth("10%", "15%", "50%", "20%", "5%");
		Table()->Adjustments->SetAlignment("left", "left", "left", "right", "center");
		
		// Declare variable to store the Total adjustments
		$fltTotalAdjustments = 0;
		
		// add the rows
		foreach (DBL()->Charge as $dboCharge)
		{

			if ($dboCharge->Nature->Value == NATURE_DR)
			{
				/*
				Table()->Adjustments->AddRow($dboCharge->CreatedOn->AsValue(),
											$dboCharge->ChargeType->AsValue(),
											$dboCharge->Description->AsValue(),
											$dboCharge->Amount->AsCallback("AddGST"),
											"&nbsp;");
				*/
				// Add the charge to the total adjustments
				$fltTotalAdjustments += $dboCharge->Amount->Value;
			}
			else
			{
				/*
				Table()->Adjustments->AddRow($dboCharge->CreatedOn->AsValue(),
											$dboCharge->ChargeType->AsValue(),
											$dboCharge->Description->AsValue(),
											$dboCharge->Amount->AsCallback("AddGST"),
											"<span>". NATURE_CR ."</span>");
				*/
				// Subtract the charge from the total adjustments
				$fltTotalAdjustments -= $dboCharge->Amount->Value;
			}
		}
		
		// Add GST to the total adjustments
		$fltTotalAdjustments = AddGST($fltTotalAdjustments);
		
		//if (Table()->Adjustments->RowCount() == 0)
		if ($fltTotalAdjustments == 0)
		{
			// There are no adjustments to stick in this table
			Table()->Adjustments->AddRow("<span>No adjustments to display</span>");
			Table()->Adjustments->SetRowAlignment("left");
			Table()->Adjustments->SetRowColumnSpan(5);
		}
		else
		{
			if ($fltTotalAdjustments < 0)
			{
				// Make the value positive and mark it as a credit
				$fltTotalAdjustments = $fltTotalAdjustments * (-1);
				$strNature = "<span style='font-weight:bold;'>". NATURE_CR ."</span>";
			}
			else
			{
				$strNature = "&nbsp;";
			}
		
			// Append the total to the table
			$strTotal				= "<span style='font-weight:bold;'>Total Adjustments ($):</span>\n";
			$strTotalAdjustments	= "<span class='Currency' style='font-weight:bold;'>". OutputMask()->MoneyValue($fltTotalAdjustments, 2) ."</span>\n";
			
			Table()->Adjustments->AddRow($strTotal, $strTotalAdjustments, $strNature);
			Table()->Adjustments->SetRowAlignment("left", "right", "center");
			Table()->Adjustments->SetRowColumnSpan(3, 1, 1);
		}
		

		// add the rows
		foreach (DBL()->Charge as $dboCharge)
		{

			if ($dboCharge->Nature->Value == NATURE_DR)
			{
				Table()->Adjustments->AddRow($dboCharge->CreatedOn->AsValue(),
											$dboCharge->ChargeType->AsValue(),
											$dboCharge->Description->AsValue(),
											$dboCharge->Amount->AsCallback("AddGST"),
											"&nbsp;");
				// Add the charge to the total adjustments
				$fltTotalAdjustments += $dboCharge->Amount->Value;
			}
			else
			{
				Table()->Adjustments->AddRow($dboCharge->CreatedOn->AsValue(),
											$dboCharge->ChargeType->AsValue(),
											$dboCharge->Description->AsValue(),
											$dboCharge->Amount->AsCallback("AddGST"),
											"<span>". NATURE_CR ."</span>");
				// Subtract the charge from the total adjustments
				$fltTotalAdjustments -= $dboCharge->Amount->Value;
			}
		}

		Table()->Adjustments->AddRow($strTotal, $strTotalAdjustments, $strNature);
		Table()->Adjustments->SetRowAlignment("left", "right", "center");
		Table()->Adjustments->SetRowColumnSpan(3, 1, 1);

		Table()->Adjustments->Render();
		
		echo "<div class='Seperator'></div>\n";
	}
}

?>
