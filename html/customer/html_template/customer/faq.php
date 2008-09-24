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
 


 class HtmlTemplateCustomerFAQ extends HtmlTemplate
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
		echo "<div class='customer-standard-display-title'>&nbsp;</div><br/><br/>";
		echo "<form method=\"GET\" action=\"./flex.php/Console/FAQ/\">";

		echo "
		<div class='customer-standard-table-title-style-confirm-details'>Search FAQ Database</div>
		<div class='GroupedContent'>
		<table class=\"customer-standard-table-style\">
		<tr>
			<td><input type=\"text\" name=\"s\" value=\"$_GET[s]\" size=\"45\"> <INPUT TYPE=\"reset\"> <INPUT TYPE=\"submit\" VALUE=\"Search\"> [<A HREF=\"./flex.php/Console/FAQ/?s=%\">show all</A>]</td>
		</tr>
		</table>
		</div><br/>";

		if(DBO()->Search->Results->Value)
		{
			print "<table width=\"100%\">
			<tr>
				<td>Title</td>
				<td>Last Updated</td>
				<td>Hits</td>
			</tr>";
			foreach(DBO()->Search->Results->Value as $results){
				foreach($results as $key=>$val){
					$$key=$val;
				}
				/* 
					After the above loop, available fields are:
					$customer_faq_id
					$customer_faq_subject
					$customer_faq_contents
					$customer_faq_time_added
					$customer_faq_time_updated
					$customer_faq_download
					$customer_faq_group
					$customer_faq_hits
				*/
				echo "
				<tr>
					<td><A HREF=\"javascript:view_faq($customer_faq_id)\">$customer_faq_subject</A></td>
					<td>$customer_faq_time_updated</td>
					<td>$customer_faq_hits</td>
				</tr>";
			}
			print "</table>";
		}
		else
		{
			echo "<div class='customer-standard-table-title-style-password'>Top 10 questions</div>
			<div class='GroupedContent'>
			<TABLE class=\"customer-standard-table-style\">
			<TR VALIGN=\"TOP\">
			<TD width=\"10\"></TD>
			<TD></TD>
			</TR>
			</TABLE>
			</div>
			<br/>";
		}

		echo "</FORM>";
	}
}

?>
