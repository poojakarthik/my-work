<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// extra_details_inbound.php
//----------------------------------------------------------------------------//
/**
 * extra_details_inbound
 *
 * HTML Template for the Inbound Details popup window, used on the Service Bulk Add webpage
 *
 * HTML Template for the Inbound Details popup window, used on the Service Bulk Add webpage
 *
 * @file		extra_details_inbound.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// HtmlTemplateServiceExtraDetailsInbound
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceExtraDetailsInbound
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateServiceExtraDetailsInbound
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceExtraDetailsInbound extends HtmlTemplate
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
		echo "<center><h2>Inbound - <div id='ExtraDetailTitleFnn' style='display:inline'>". DBO()->Service->FNN->Value ."</div></h2></center>";
		echo "<form id='VixenForm_Inbound'>\n";
		echo "<div class='GroupedContent'>\n";

		// Handle extra inbound phone details
		DBO()->ServiceInboundDetail->AnswerPoint->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("attribute:maxlength"=>25, "style:width"=>"234px"));
		DBO()->ServiceInboundDetail->Configuration->RenderInput();
		
		echo "</div>\n";  // GroupedContent
		
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(this)");
		$this->Button("Back", "Vixen.ServiceBulkAdd.Inbound.Previous()");
		$this->Button("Save", "Vixen.ServiceBulkAdd.Inbound.Next()");
		echo "</div></div>\n";
		
		echo "</form>\n";
		
		// Initialise the form
		echo "<script type='text/javascript'>Vixen.ServiceBulkAdd.Inbound.Initialise();</script>\n";
	}	                                     
}

?>
