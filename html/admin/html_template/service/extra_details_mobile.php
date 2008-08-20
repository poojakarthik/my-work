<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// extra_details_mobile.php
//----------------------------------------------------------------------------//
/**
 * extra_details_mobile
 *
 * HTML Template for the Mobile Details popup window, used on the Service Bulk Add webpage
 *
 * HTML Template for the Mobile Details popup window, used on the Service Bulk Add webpage
 *
 * @file		extra_details_mobile.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// HtmlTemplateServiceExtraDetailsMobile
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceExtraDetailsMobile
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateServiceExtraDetailsMobile
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceExtraDetailsMobile extends HtmlTemplate
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
		
		//$this->LoadJavascript("service_extra_details_mobile");
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
		echo "<center><h2>Mobile - <div id='ExtraDetailTitleFnn' style='display:inline'>". DBO()->Service->FNN->Value ."</div></h2></center>";
		echo "<form id='VixenForm_Mobile'>\n";
		echo "<div class='GroupedContent'>\n";

		DBO()->ServiceMobileDetail->SimPUK->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("attribute:maxlength"=>50, "style:width"=>"232px"));
		DBO()->ServiceMobileDetail->SimESN->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("attribute:maxlength"=>15, "style:width"=>"232px"));
		
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput'>&nbsp;&nbsp;</span>State :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='ServiceMobileDetail.SimState' name='ServiceMobileDetail.SimState' style='width:100%'>\n";
		echo "<option value=''>&nbsp;</option>";
		foreach ($GLOBALS['*arrConstant']['ServiceStateType'] as $strKey=>$arrState)
		{
			$strSelected = (DBO()->ServiceMobileDetail->SimState->Value == $strKey) ? "selected='selected'" : "";
			echo "<option value='$strKey' $strSelected><span>". $arrState['Description'] ."</span></option>";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		DBO()->ServiceMobileDetail->DOB->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("attribute:maxlength"=>10, "style:width"=>"85px"));
		DBO()->ServiceMobileDetail->Comments->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"232px"));
		echo "</div>\n";  // GroupedContent
		
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(this)");
		$this->Button("Back", "Vixen.ServiceBulkAdd.Mobile.Previous()");
		$this->Button("Save", "Vixen.ServiceBulkAdd.Mobile.Next()");
		echo "</div></div>\n";
		
		echo "</form>\n";
		
		// Initialise the form
		echo "<script type='text/javascript'>Vixen.ServiceBulkAdd.Mobile.Initialise();</script>\n";
	}	
}

?>
