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
 


 class HtmlTemplateCustomerFAQView extends HtmlTemplate
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

		if(DBO()->FAQ->Result->Value)
		{
			$arrFAQ = DBO()->FAQ->Result->Value;
			foreach($arrFAQ as $key=>$val){
				$$key=$val;
			}
			echo "
			<TABLE cellpadding=\"10\" width=\"100%\">
			<TR>
				<TD>
				<TABLE class=\"popup_footer_faq\" width=\"100%\">
				<TR>
					<TD><input type=\"button\" value=\"close window\" onClick=\"javascript:window.close()\"></TD>
				</TR>
				</TABLE>
				<TABLE class=\"popup_title_faq\" width=\"100%\">
				<TR>
					<TD>$customer_faq_subject</TD>
				</TR>
				</TABLE>
				<TABLE class=\"popup_content_faq\" width=\"100%\">
				<TR>
					<TD valign=\"top\">$customer_faq_contents</TD>
				</TR>
				</TABLE>
				<TABLE class=\"popup_footer_faq\" width=\"100%\">
				<TR>
					<TD><input type=\"button\" value=\"close window\" onClick=\"javascript:window.close()\"></TD>
				</TR>
				</TABLE>
				</TD>
			</TR>
			</TABLE>";
		}
		else
		{
			echo "<CENTER>Invalid FAQ specified.</CENTER>";
		}
	}
}

?>
