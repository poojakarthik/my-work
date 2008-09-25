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
 


 class HtmlTemplateCustomerFAQAll extends HtmlTemplate
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
		$this->LoadJavascript('javascript_functions');
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
		echo "<div class='customer-standard-display-title'>&nbsp;</div><br/><br/>";
		echo "<form method=\"GET\" action=\"./flex.php/Console/FAQ/\">";

		echo "
		<div class='customer-standard-table-title-style-confirm-details'>Search FAQ Database</div>
		<div class='GroupedContent'>
		<table class=\"customer-standard-table-style\">
		<tr>
			<td><input type=\"text\" name=\"s\" value=\"$_GET[s]\" size=\"30\"> <INPUT TYPE=\"reset\"> <INPUT TYPE=\"submit\" VALUE=\"Search\"> [<A HREF=\"./flex.php/Console/FAQ/?all=1\">show all</A>]</td>
		</tr>
		</table>
		</div><br/>";

		if(DBO()->FAQ->All->Value !== NULL)
		{
			echo "<div class='customer-standard-table-title-style-password'>Results " . DBO()->Total->Start->Value . " - " . DBO()->Total->NextPage->Value . " of about " . DBO()->Total->Search->Value . "</div>
			<div class='GroupedContent'>
			<TABLE class=\"customer-standard-table-style\">
			<TR VALIGN=\"TOP\">
			<TD width=\"10\"></TD>
			<TD>";
			
			print "<table width=\"100%\">
			<tr>
				<td>Title</td>
				<td>Last Updated</td>
				<td>Hits</td>
			</tr>";
			foreach(DBO()->FAQ->All->Value as $results){
				foreach($results as $key=>$val){
					$$key=$val;
				}
				echo "
				<tr>
					<td>$customer_faq_id. <A HREF=\"javascript:view_faq($customer_faq_id)\">$customer_faq_subject</A></td>
					<td>$customer_faq_time_updated</td>
					<td>$customer_faq_hits</td>
				</tr>";
			}
			print "</table>";

			print "
			</TD>
			</TR>
			</TABLE>
			</div>
			<br/>";

			if(DBO()->Search->Pages->Value)
			{
				echo "
				<div class='customer-standard-table-title-style-confirm-details'>Result Pages</div>
				<div class='GroupedContent'>
				<table class=\"customer-standard-table-style\">
				<tr>
					<td>" . DBO()->Search->Pages->Value . "</td>
				</tr>
				</table>
				</div><br/>";
			}
		}
		else
		{
			echo "Error";
		}

		echo "</FORM>";
	}
}

?>
