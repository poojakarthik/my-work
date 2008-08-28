<?php
//----------------------------------------------------------------------------//
// HtmlTemplateLoggedOut
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateLoggedOut
 *
 * HTML Template object for the client app, "logged out" page
 *
 * HTML Template object for the client app, "logged out" page
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateLoggedOut
 * @extends	HtmlTemplate
 */
class HtmlTemplateLoggedOut extends HtmlTemplate
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
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
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
		$strMainPageHref = Href()->MainPage();
		$strLoginHref = Href()->Console();
		
		echo "<div class='WideContent' style='height:300px;'>\n";
		
		print "
		<br/><br/>
		<TABLE align=center class=login-table-style-main-title>
		<TR>
			<TD>Logged Out</TD>
		</TR>
		</TABLE>
		<TABLE align=center class=login-table-style-main>
		<TR VALIGN=\"TOP\">
			<TD>";

		if (AuthenticatedUser()->_arrUser != NULL)
		{
			// The user was logged in when they tried to log out.  The logging out has been successful.
			$strLogoutMsg = "You have been successfully logged out.";
		}
		else
		{
			// The user wasnt logged in when they tried to log out.  
			// This should only occur, if the user logged out using another browser window, or machine
			$strLogoutMsg = "Apparently this session is not the most recent.  You are most likely still logged in on another machine, or browser window.<br />";
			$strLogoutMsg .= "To insure you have properly logged out.  Please log in again and then log out.";
		}
		
		echo "<span class='DefaultOutputSpan Default'>$strLogoutMsg</span>\n";
		echo "<br /><br />\n";
		echo "<a href='$strLoginHref' ><span>Customer System Login</span></a>\n";
		//echo "<a href='$strLoginHref' ><span>". APP_NAME ." Login</span></a>\n";
		
		/* There is no home page, so for now we will omit this link...
		echo "<br />\n";
		echo "<a href='$strMainPageHref' ><span>Back to homepage</span></a>\n";
		*/
		
			print "
			</TD>
		</TR>
		</TABLE>
		<br/>";

		echo "</div>\n"; // WideContent

		
		// As there is nothing much for the user to do on this page (with the above link removed),
		// automatically redirect the user to the login page.
		?>
		<script>
			function goToLogin()
			{
				document.location = "<?php echo $strLoginHref; ?>";
			}
			window.setTimeout(goToLogin, 5 *1000);
		</script>
		<?php

	}
}

?>
