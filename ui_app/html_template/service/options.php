<?php
//----------------------------------------------------------------------------//
// HtmlTemplateServiceOptions
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceOptions
 *
 * A specific HTML Template object
 *
 * An service options HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateServiceOptions
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceOptions extends HtmlTemplate
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
		echo "<h2 class='options'>Service Options</h2>\n";
		echo "<div class='NarrowForm DefaultOutput'>\n";
		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		echo "<tr><td width='2%'>&nbsp;</td><td>\n";
	
			$strEditServiceLink = Href()->EditService(DBO()->Service->Id->Value);
			echo "<li><a href='$strEditServiceLink'>Edit Service Details</a></li>\n";
			
			echo "<li>[TODO] View Unbilled Charges</li>\n";
			echo "<li>[TODO] View Recurring Adjustments</li>\n";
			
			$strChangePlanLink = Href()->ChangePlan(DBO()->Service->Id->Value);
			echo "<li><a href='$strChangePlanLink'>Change Plan</a></li>\n";
			echo "<li>[TODO] Change of Lessee</li>\n";
			
			$strAddServiceNoteLink = Href()->AddServiceNote(DBO()->Service->Id->Value);
			echo "<li><a href='$strAddServiceNoteLink'>Add Service Note</a></li>\n";		
			
			$strAddAdjustmentLink = Href()->AddAdjustment(DBO()->Service->Account->Value, DBO()->Service->Id->Value);
			echo "<li><a href='$strAddAdjustmentLink'>Add Adjustment</a></li>\n";
		
			$strAddRecurringAdjustmentLink = Href()->AddRecurringAdjustment(DBO()->Service->Account->Value, DBO()->Service->Id->Value);
			echo "<li><a href='$strAddRecurringAdjustmentLink'>Add Recurring Adjustment</a></li>\n";
			
		echo "</td></tr>\n";
		echo "</table>\n";
		
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
	}
}

?>
