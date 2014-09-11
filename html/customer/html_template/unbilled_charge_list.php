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
		//echo "<h2 class='Charge'>Unbilled Debits & Credits</h2>\n";
		echo "
		<TABLE width=\"100%\">
		<TR>
			<TD style=\"width: 200px; text-indent: 5; font-size: 8pt;\"><a href=\"#\" onclick=\"javascript:window.print();return false;\"><IMG SRC=\"./img/generic/Button_Printer.jpg\" WIDTH=\"18\" HEIGHT=\"20\" BORDER=\"0\" ALT=\"\"></a> <a href=\"#\" onclick=\"javascript:window.print();return false;\">Print This Page</a></TD>
			<TD style=\"text-indent: 175; font-weight: bold;\">Unbilled Debits & Credits</TD>
		</TR>
		<TR>
			<TD COLSPAN=\"2\" style=\"text-indent: 5; font-size: 8pt;\"><a href=\"http://www.adobe.com/\"><img src=\"./img/template/acrobat_15x15.jpg\" width=\"15\" height=\"15\" border=\"0\" alt=\"\"></a> Please ensure you have the latest copy of acrobat reader and provider link to download. <a href=\"http://www.adobe.com/\">Download Acrobat Reader</a></TD>
		</TR>
		</TABLE>\n";
				
		Table()->Charges->SetHeader("Date", "Code", "Description", "Amount (inc GST)", "&nbsp;");
		Table()->Charges->SetWidth("10%", "15%", "50%", "20%", "5%");
		Table()->Charges->SetAlignment("left", "left", "left", "right", "center");
		
		// Declare variable to store the Total charges
		$fltTotalCharges = 0;
		
		// add the rows
		foreach (DBL()->Charge as $dboCharge)
		{

			if ($dboCharge->Nature->Value == NATURE_DR)
			{
				/*
				Table()->Charges->AddRow($dboCharge->CreatedOn->AsValue(),
											$dboCharge->ChargeType->AsValue(),
											$dboCharge->Description->AsValue(),
											$dboCharge->Amount->AsCallback("AddGST"),
											"&nbsp;");
				*/
				// Add the charge to the total charges
				$fltTotalCharges += $dboCharge->Amount->Value;
			}
			else
			{
				/*
				Table()->Charges->AddRow($dboCharge->CreatedOn->AsValue(),
											$dboCharge->ChargeType->AsValue(),
											$dboCharge->Description->AsValue(),
											$dboCharge->Amount->AsCallback("AddGST"),
											"<span>". NATURE_CR ."</span>");
				*/
				// Subtract the charge from the total charges
				$fltTotalCharges -= $dboCharge->Amount->Value;
			}
		}
		
		// Add GST to the total charges
		$fltTotalCharges = AddGST($fltTotalCharges);
		
		//if (Table()->Charges->RowCount() == 0)
		if ($fltTotalCharges == 0)
		{
			// There are no charges to stick in this table
			Table()->Charges->AddRow("<span>No charges to display</span>");
			Table()->Charges->SetRowAlignment("left");
			Table()->Charges->SetRowColumnSpan(5);
		}
		else
		{
			if ($fltTotalCharges < 0)
			{
				// Make the value positive and mark it as a credit
				$fltTotalCharges = $fltTotalCharges * (-1);
				$strNature = "<span style='font-weight:bold;'>". NATURE_CR ."</span>";
			}
			else
			{
				$strNature = "&nbsp;";
			}
		
			// Append the total to the table
			$strTotal				= "<span style='font-weight:bold;'>Total Charges ($):</span>\n";
			$strTotalCharges	= "<span class='Currency' style='font-weight:bold;'>". OutputMask()->MoneyValue($fltTotalCharges, 2) ."</span>\n";
			
			Table()->Charges->AddRow($strTotal, $strTotalCharges, $strNature);
			Table()->Charges->SetRowAlignment("left", "right", "center");
			Table()->Charges->SetRowColumnSpan(3, 1, 1);
		}
		

		// add the rows
		foreach (DBL()->Charge as $dboCharge)
		{

			if ($dboCharge->Nature->Value == NATURE_DR)
			{
				Table()->Charges->AddRow($dboCharge->CreatedOn->AsValue(),
											$dboCharge->ChargeType->AsValue(),
											$dboCharge->Description->AsValue(),
											$dboCharge->Amount->AsCallback("AddGST"),
											"&nbsp;");
				// Add the charge to the total charges
				$fltTotalCharges += $dboCharge->Amount->Value;
			}
			else
			{
				Table()->Charges->AddRow($dboCharge->CreatedOn->AsValue(),
											$dboCharge->ChargeType->AsValue(),
											$dboCharge->Description->AsValue(),
											$dboCharge->Amount->AsCallback("AddGST"),
											"<span>". NATURE_CR ."</span>");
				// Subtract the charge from the total charges
				$fltTotalCharges -= $dboCharge->Amount->Value;
			}
		}

		Table()->Charges->AddRow($strTotal, $strTotalCharges, $strNature);
		Table()->Charges->SetRowAlignment("left", "right", "center");
		Table()->Charges->SetRowColumnSpan(3, 1, 1);

		Table()->Charges->Render();
		
		echo "<div class='Seperator'></div>\n";
	}
}

?>
