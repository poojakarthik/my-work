<?php
//----------------------------------------------------------------------------//
// HtmlTemplateCustomerGroupDocumentTemplates
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateCustomerGroupDocumentTemplates
 *
 * Lists the currently used DocumentTemplates
 *
 * Lists the currently used DocumentTemplates
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateCustomerGroupDocumentTemplates
 * @extends	HtmlTemplate
 */
class HtmlTemplateCustomerGroupDocumentTemplates extends HtmlTemplate
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
	 * @param	string	$_strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext			= $intContext;
		$this->_strContainerDivId	= $strId;
		
		$this->LoadJavascript("highlight");
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
		$bolTemplateUndefined = FALSE;
		
		//TODO! make a h2 css class for this
		echo "<h2 class='CustomerGroup'>Current Document Templates</h2>\n";
		
		// Set up the header for the table of LetterTemplates
		Table()->DocumentTemplate->SetHeader("Type", "Version", "Effective From");
		Table()->DocumentTemplate->SetWidth("20%", "65%", "15%");
		Table()->DocumentTemplate->SetAlignment("Left", "Left", "Left");

		foreach (DBL()->DocumentTemplate as $dboTemplate)
		{
			$strTemplateType = $dboTemplate->TypeName->Value;
			if ($dboTemplate->TemplateId->Value != NULL)
			{
				// There is an active template
				$strVersion		= $dboTemplate->Version->Value;
				$strEffectiveOn	= OutputMask()->ShortDate($dboTemplate->EffectiveOn->Value); 
			}
			else
			{
				$strVersion		= "[ No template has been defined yet ]";
				$strEffectiveOn	= "";
			}
			
			Table()->DocumentTemplate->AddRow($strTemplateType, $strVersion, $strEffectiveOn);
			//Table()->DocumentTemplate->SetOnClick("Vixen.CustomerGroupDetails.LoadDocumentTemplateHistory({$dboTemplate->TypeId->Value})");
			$strLoadTemplateHistory = Href()->ViewDocumentTemplateHistory(DBO()->CustomerGroup->Id->Value, $dboTemplate->TypeId->Value);
			Table()->DocumentTemplate->SetOnClick("window.location='$strLoadTemplateHistory'");
		}
		
		Table()->DocumentTemplate->RowHighlighting = TRUE;
		Table()->DocumentTemplate->Render();
		
		// Draw the button to link to the "View Document Resources" page
		$strViewDocumentResourceLink = htmlspecialchars(Href()->ViewDocumentResources(DBO()->CustomerGroup->Id->Value), ENT_QUOTES);
		echo "
<div class='ButtonContainer'>
	<input type='button' value='View Document Resources' onClick='window.location = \"$strViewDocumentResourceLink\"' style='float:right'></input>
</div>
			";
	}
}

?>
