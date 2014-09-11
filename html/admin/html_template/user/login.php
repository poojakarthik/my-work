<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// login.php
//----------------------------------------------------------------------------//
/**
 * login
 *
 * HTML Template for the UserLogin HTML object
 *
 * HTML Template for the UserLogin HTML object
 * This popup allows the user to log in
 *
 * @file		login.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.06
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateUserLogin
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateUserLogin
 *
 * HTML Template class for the UserLogin HTML object
 *
 * HTML Template class for the UserLogin HTML object
 * This popup allows the user to log in via ajax, thus retaining the current state of the page/functionality that they were using when prompted for their login details
 *
 * @package	ui_app
 * @class	HtmlTemplateUserLogin
 * @extends	HtmlTemplate
 */
class HtmlTemplateUserLogin extends HtmlTemplate
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
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext			= $intContext;
		$this->_strContainerDivId	= $strId;
		
		$this->LoadJavascript("login");
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
		echo "
<div class='MsgNotice'>Your session has expired.  Please enter your login details</div>
<form id='LoginForm'>
	<div class='GroupedContent'>
		<table style='width:100%;background-color:inherit'>
			<tr>
				<td style='width:50%'>User Name</td>
				<td>
					<input type='text' name='UserName' style='width:100%'></input>
				</td>
			</tr>
			<tr>
				<td>Password</td>
				<td>
					<input type='password' name='Password' style='width:100%' onkeypress='if ((event.which && event.which == 13) || (event.keyCode && event.keyCode == 13)) Vixen.Login.Submit();'></input>
				</td>
			</tr>
		</table>
	</div>
	<div style='float:right;padding:3px 0px'>
		<input type='button' value='   OK   ' onclick='Vixen.Login.Submit()'></input>
		<input type='button' value='Cancel' onclick='Vixen.Popup.Close(this)' ></input>
	</div>
	<div style='float:none;clear:both;'></div>
</form>
<script type='text/javascript'>Vixen.Login.InitialisePopup();</script>
";
	}
}

?>
