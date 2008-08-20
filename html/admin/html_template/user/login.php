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
		<div class='PropertyInputControlContainer'>
			<div class='PropertyInputControlLabel'>User Name</div>
			<input type='text' name='UserName' class='PropertyInputControl'></input>
			<div class='PropertyInputControlClear'></div>
		</div>
		<div class='PropertyInputControlContainer'>
			<div class='PropertyInputControlLabel'>Password</div>
			<input type='password' name='Password' class='PropertyInputControl'></input>
			<div class='PropertyInputControlClear'></div>
		</div>
	</div>
	<div style='float:right;padding:3px 0px'>
		<input type='button' value='Cancel' onclick='Vixen.Popup.Close(this)' ></input>
		<input type='button' value='Ok' onclick='Vixen.Login.Submit()'></input>
	</div>
	<div style='float:none;clear:both;'></div>
</form>
<script type='text/javascript'>Vixen.Login.InitialisePopup();</script>
";
	}
}

?>
