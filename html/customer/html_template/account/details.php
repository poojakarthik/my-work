<?php
//----------------------------------------------------------------------------//
// HtmlTemplateAccountDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountDetails
 *
 * HTML Template object for the Account Details
 *
 * HTML Template object for the Account Details
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateAccountDetails
 * @extends	HtmlTemplate
 */
 


 class HtmlTemplateAccountDetails extends HtmlTemplate
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
		#echo "<div class='NarrowContent'>\n";
	
		echo "<div class='customer-standard-display-title'>Home</div><br/><br/>";

		echo "<div class='customer-standard-table-title-style-account'>Account Details</div>\n";
		//echo "<h2 class='Account'>Account Details</h2>\n";

		echo "	
		<div class='GroupedContent'>
		<TABLE class=\"customer-standard-table-style\">";
		
		if(DBO()->Account->BusinessName->Value)
		{
			print "
			<TR>
				<TD width=\"200\">Business Name: </TD>
				<TD>" . DBO()->Account->BusinessName->Value . "</TD>
			</TR>";
		}
		if(DBO()->Account->TradingName->Value)
		{
			print "
			<TR>
				<TD>Trading Name: </TD>
				<TD>" . DBO()->Account->TradingName->Value . "</TD>
			</TR>";
		}
		if(DBO()->Account->ABN->Value)
		{
			print "
			<TR>
				<TD>ABN: </TD>
				<TD>" . DBO()->Account->ABN->Value . "</TD>
			</TR>";
		}
		if(DBO()->Account->ACN->Value)
		{
			print "
			<TR>
				<TD>ACN: </TD>
				<TD>" . DBO()->Account->ACN->Value . "</TD>
			</TR>";
		}
		$strCustomerBalance = "$" . number_format(DBO()->Account->CustomerBalance->Value, 2, '.', '');
		if(eregi("-",$strCustomerBalance))
		{
			$strCustomerBalance = str_replace("-","",$strCustomerBalance) . " CR";
		}
		print "
		<TR>
			<TD>Customer Balance: </TD>
			<TD>$strCustomerBalance</TD>
		</TR>
		<TR>
			<TD>Overdue: </TD>
			<TD>" . "$" . number_format(Framework()->GetOverdueBalance(DBO()->Account->Id->Value), 2, '.', '') . "</TD>
		</TR>";

		$strUnbilledAdjustments = "$" . number_format(DBO()->Account->UnbilledAdjustments->Value, 2, '.', '');
		if(eregi("-",$strUnbilledAdjustments))
		{
			$strUnbilledAdjustments = str_replace("-","",$strUnbilledAdjustments) . " CR";
		}
		print "
		<TR>
			<TD>Unbilled Debits & Credits: </TD>
			<TD>$strUnbilledAdjustments</TD>
		</TR>
		<TR>
			<TD>Unbilled Calls: </TD>
			<TD>" . "$" . number_format(DBO()->Account->UnbilledCDRs->Value, 2, '.', '') . "</TD>
		</TR>";

		
		$BillingMethod = DBO()->Account->BillingMethod->Value;
		$strDescriptionOfMethod = $GLOBALS['*arrConstant']['BillingMethod'][$BillingMethod]['Description'];
		print "
		<TR>
			<TD>Billing Method: </TD>
			<TD>$strDescriptionOfMethod</TD>
		</TR>
		</TABLE>
		</div>
		<br/>\n";

/*
		echo "<div class='GroupedContent'>";



		DBO()->Account->Id->RenderOutput();
		if (DBO()->Account->BusinessName->Value)
		{
			DBO()->Account->BusinessName->RenderOutput();
		}
		if (DBO()->Account->TradingName->Value)
		{
			DBO()->Account->TradingName->RenderOutput();
		}
		if (trim(DBO()->Account->ABN->Value))
		{
			DBO()->Account->ABN->RenderOutput();
		}
		if (trim(DBO()->Account->ACN->Value))
		{
			DBO()->Account->ACN->RenderOutput();
		}
		
		DBO()->Account->CustomerBalance->RenderOutput();
		DBO()->Account->Overdue->RenderOutput();
		
		$strUnbilledAdjustments = DBO()->Account->UnbilledAdjustments->Value;
		if($strUnbilledAdjustments == "0")
		{
			$strUnbilledAdjustments = "$0.00";
		}
		if($strUnbilledAdjustments != "0")
		{
			$strUnbilledAdjustments = "$" . number_format($strUnbilledAdjustments, 2, '.', '');
		}
		if(eregi("-",$strUnbilledAdjustments))
		{
			$strUnbilledAdjustments = str_replace("-","",$strUnbilledAdjustments) . " CR";
		}
		echo "
		<div class='DefaultElement'>
		   <div id='Account.UnbilledAdjustments.Output' name='Account.UnbilledAdjustments' class='DefaultOutput Currency '>$strUnbilledAdjustments</div>
		   <div id='Account.UnbilledAdjustments.Label' class='DefaultLabel'>
			  <span> &nbsp;</span>
			  <span id='Account.UnbilledAdjustments.Label.Text'>Unbilled Debits & Credits : </span>

		   </div>
		</div>";
		//DBO()->Account->UnbilledAdjustments->RenderOutput();
		DBO()->Account->UnbilledCDRs->RenderOutput();
		

		$BillingMethod = DBO()->Account->BillingMethod->Value;
		$strDescriptionOfMethod = $GLOBALS['*arrConstant']['BillingMethod'][$BillingMethod]['Description'];

		echo "
		<div class='DefaultElement'>
		   <div id='Account.BillingMethod.Output' name='Account.BillingMethod' class='DefaultOutput Default '>$strDescriptionOfMethod</div>
		   <div id='Account.BillingMethod.Label' class='DefaultLabel'>
			  <span> &nbsp;</span>
			  <span id='Account.BillingMethod.Label.Text'>Billing Method : </span>
		   </div>
		</div>";

		echo "</div><br/><br/>";
		*/

		echo "<div class='customer-standard-table-title-style-address'>Billing Address Details</div>\n";
		//echo "<h2 class='Account'>Address Details</h2>\n";

		echo "	
		<div class='GroupedContent'>
		<TABLE class=\"customer-standard-table-style\">
		<TR>
			<TD width=\"200\">Street Address: </TD>
			<TD>" . DBO()->Account->Address1->Value . "</TD>
		</TR>
		<TR>
			<TD>&nbsp;</TD>
			<TD>" . DBO()->Account->Address2->Value . "</TD>
		</TR>
		<TR>
			<TD>Suburb: </TD>
			<TD>" . DBO()->Account->Suburb->Value . "</TD>
		</TR>
		<TR>
			<TD>State: </TD>
			<TD>" . DBO()->Account->State->Value . "</TD>
		</TR>
		<TR>
			<TD>Postcode: </TD>
			<TD>" . DBO()->Account->Postcode->Value . "</TD>
		</TR>
		<TR>
			<TD>Country: </TD>
			<TD>" . DBO()->Account->Country->Value . "</TD>
		</TR>
		</TABLE>
		</div>
		<br/>";
		
		/*
		echo "<h2 class='Account'>Address Details</h2>\n";
		echo "<div class='GroupedContent'>\n";
		// Display the details of their primary account
		echo "
		<div class='DefaultElement'>
		   <div id='Account.Address1.Output' name='Account.Address1' class='DefaultOutput Default '>" . DBO()->Account->Address1->Value . "&nbsp;</div>
		   <div id='Account.Address1.Label' class='DefaultLabel'>
			  <span> &nbsp;</span>
			  <span id='Account.Address1.Label.Text'>Street Address : </span>
		   </div>
		</div>
		<div class='DefaultElement'>
		   <div id='Account.Address2.Output' name='Account.Address2' class='DefaultOutput Default '>" . DBO()->Account->Address2->Value . "&nbsp;</div>
		   <div id='Account.Address2.Label' class='DefaultLabel'>
			  <span> &nbsp;</span>
			  <span id='Account.Address2.Label.Text'></span>
		   </div>
		</div>";
		if (trim(DBO()->Account->Suburb->Value))
		{
			DBO()->Account->Suburb->RenderOutput();
		}
		if (trim(DBO()->Account->State->Value))
		{
			DBO()->Account->State->RenderOutput();
		}
		if (trim(DBO()->Account->Postcode->Value))
		{
			DBO()->Account->Postcode->RenderOutput();
		}
		if (trim(DBO()->Account->Country->Value))
		{
			DBO()->Account->Country->RenderOutput();
		}
		echo "</div><br/><br/>";

		*/



		echo "<div class='customer-standard-table-title-style-contact'>Contact Details</div>\n";

		echo "	
		<div class='GroupedContent'>
		<TABLE class=\"customer-standard-table-style\">
		<TR>
			<TD width=\"200\">Title: </TD>
			<TD>" . DBO()->Contact->Title->Value . "</TD>
		</TR>
		<TR>
			<TD>First Name: </TD>
			<TD>" . DBO()->Contact->FirstName->Value . "</TD>
		</TR>
		<TR>
			<TD>Last Name: </TD>
			<TD>" . DBO()->Contact->LastName->Value . "</TD>
		</TR>
		<TR>
			<TD>Job Title: </TD>
			<TD>" . DBO()->Contact->JobTitle->Value . "</TD>
		</TR>";

		$strDisplayEmailAddress = trim(DBO()->Contact->Email->Value);
		print "
		<TR>
			<TD>E-Mail: </TD>
			<TD>$strDisplayEmailAddress</TD>
		</TR>
		<TR>
			<TD>Phone: </TD>
			<TD>" . DBO()->Contact->Phone->Value . "</TD>
		</TR>
		<TR>
			<TD>Mobile: </TD>
			<TD>" . DBO()->Contact->Mobile->Value . "</TD>
		</TR>
		<TR>
			<TD>Fax: </TD>
			<TD>" . DBO()->Contact->Fax->Value . "</TD>
		</TR>
		</TABLE>
		</div>
		<br/>";

		/*
		echo "<h2 class='Account'>Contact Details</h2>\n";
		echo "<div class='GroupedContent'>\n";
		if (DBO()->Contact->Title->Value)
		{
			DBO()->Contact->Title->RenderOutput();
		}
		if (DBO()->Contact->FirstName->Value)
		{
			DBO()->Contact->FirstName->RenderOutput();
		}
		if (trim(DBO()->Contact->LastName->Value))
		{
			DBO()->Contact->LastName->RenderOutput();
		}
		if (trim(DBO()->Contact->JobTitle->Value))
		{
			DBO()->Contact->JobTitle->RenderOutput();
		}

		$strDisplayEmailAddress = trim(DBO()->Contact->Email->Value);
		echo "
		<div class='DefaultElement'>
		   <div id='Account.BillingMethod.Output' name='Account.BillingMethod' class='DefaultOutput Default '>$strDisplayEmailAddress</div>
		   <div id='Account.BillingMethod.Label' class='DefaultLabel'>
			  <span> &nbsp;</span>
			  <span id='Account.BillingMethod.Label.Text'>Email : </span>
		   </div>
		</div>";

		if (trim(DBO()->Contact->Phone->Value))
		{
			DBO()->Contact->Phone->RenderOutput();
		}
		if (trim(DBO()->Contact->Mobile->Value))
		{
			DBO()->Contact->Mobile->RenderOutput();
		}
		if (trim(DBO()->Contact->Fax->Value))
		{
			DBO()->Contact->Fax->RenderOutput();
		}
		*/

	}
}

?>
