<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// vixen_header.php
//----------------------------------------------------------------------------//
/**
 * page_header
 *
 * HTML Template for the page header object
 *
 * HTML Template for the page header object
 *
 * @file		vixen_header.php
 * @language	PHP
 * @package		ui_app
 * @author		Jared 'flame' Herbohn
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateVixenHeader
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateVixenHeader
 *
 * HTML Template class for the HTML Vixen header object
 *
 * HTML Template class for the HTML Vixen header object
 *
 *
 *
 * @package	ui_app
 * @class	HtmlTemplateVixenHeader
 * @extends	HtmlTemplate
 */
class HtmlTemplateVixenHeader extends HtmlTemplate
{
	function __construct()
	{
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
		
		echo "
	<div id='LoginBox' class='LoginBox' style='left: 400px; top:300px;'>
		<div id='TopBar' class='TopBar'>
		TelcoBlue Internal System
		</div>
			
			
			<form method='POST' action='account_view.php?Account.Id=$strTarget'>
				<table>
					<tr>
						<td>
							<label for='UserName'>Username:</label>
						</td>
						<td>
							<input type='text' name='UserName' id='UserName' maxlength='21'/>
						</td>
					</tr>
					<tr>
						<td>
							<label for='PassWord'>Password:</label>
						</td>
						<td>
							<input type='password' name='PassWord' />
						</td>
					</tr>
					<tr>
						<td/>
						<td>
							<input type='submit' value='Continue &#xBB;' class='Right'/>
						</td>
					</tr>
				</table>
			</form>
	</div>
    <div class='Logo'>
      <img src='img/template/vixen_logo.png' border='0'>
    </div>
    <div id='Header' class='sectionContainer'>
      <span class='LogoSpacer'></span>
      <div class='sectionContent'>
        <div class='Left'>
			TelcoBlue Internal Management System
		</div>
        <div class='Right'>
			Version 7.03
									
			<div class='Menu_Button'>
				<a href='#' onclick=\"return ModalDisplay ('#modalContent-ReportBug')\">
					<img src='img/template/bug.png' alt='Report Bug' title='Report Bug' border='0'>
				</a>
			</div>
		</div>


        <div class='Clear'></div>
      </div>
      <div class='Clear'></div>
    </div>
    <div class='Clear'></div>
    <div class='Seperator'></div>";

	}
}

?>
