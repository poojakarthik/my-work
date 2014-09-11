<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// extra_details_land_line.php
//----------------------------------------------------------------------------//
/**
 * extra_details_land_line
 *
 * HTML Template for the LandLine Details popup window, used on the Service Bulk Add webpage
 *
 * HTML Template for the LandLine Details popup window, used on the Service Bulk Add webpage
 *
 * @file		extra_details_land_line.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// HtmlTemplateServiceExtraDetailsLandLine
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceExtraDetailsLandLine
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 * @package	ui_app
 * @class	HtmlTemplateServiceExtraDetailsLandLine
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceExtraDetailsLandLine extends HtmlTemplate
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
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		//$this->LoadJavascript("date_time_picker_xy");
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
		echo "<center><h2>Land Line - <div id='ExtraDetailTitleFnn' style='display:inline'>". DBO()->Service->FNN->Value ."</div></h2></center>";
		echo "<form id='VixenForm_LandLine' >\n";
		echo "<div class='GroupedContent'>\n";
		
		echo "<div style='height:25px'>";
		echo "<div class='Left'>";

		// Calculate the values
		$strIndialChecked	= (DBO()->Service->Indial100->Value) ? "checked='1'" : "";
		$strDisplayELB		= (DBO()->Service->Indial100->Value) ? "" : "visibility:hidden;";
		$strELBChecked		= (DBO()->Service->Indial100->Value && DBO()->Service->ELB->Value) ? "checked='1'" : "";
		$strAuthDate		= DBO()->Service->AuthorisationDate->Value;
		
		// Create the AuthorisationDate textbox
		echo "	<span class='RequiredInput'>*&nbsp;</span><span>Authorisation Date</span>
				<span>
					<input id='Service.AuthorisationDate' type='text' maxlength='10' style='width:85px' value='$strAuthDate' />
				</span>";
		
		// Create Indial100 checkbox
		echo "	<span style='margin-left:50px;'>Indial100</span>
				<span>
					<input type='checkbox' class='DefaultInputCheckBox' id='Service.Indial100' $strIndialChecked/>
				</span>";
		
		// Create Extension Level Billing checkbox
		echo "	<span id='Container_ELB' style='margin-left:50px;$strDisplayELB'>
					<span>Extension Level Billing</span>
					<span>
						<input type='checkbox' class='DefaultInputCheckBox' id='Service.ELB' $strELBChecked/>
					</span>
				</span>";

		echo "</div>\n"; //Left
		echo "</div>\n"; //height=25px
		echo "</div>";  // GroupedContent
		echo "</form>";
		
		echo "<div class='SmallSeparator'></div>\n";
		
		echo "<script type='text/javascript'>Vixen.ServiceBulkAdd.LandLine.Initialise()</script>\n";
	}
}

?>
