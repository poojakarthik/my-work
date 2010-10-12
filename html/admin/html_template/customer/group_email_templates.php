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
class HtmlTemplateCustomerGroupEmailTemplates extends HtmlTemplate
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
		echo "<h2 class='CustomerGroup'>Current Email Templates</h2>\n";

		// Set up the header for the table of LetterTemplates
		Table()->EmailTemplate->SetHeader("Type", "Current Version Description", "Effective From");
		Table()->EmailTemplate->SetWidth("20%", "65%", "15%");
		Table()->EmailTemplate->SetAlignment("Left", "Left", "Left");
		$aTemplateDetails = Email_Template_Type::getTemplateVersionDetailsForCustomerGroup(DBO()->CustomerGroup->Id->value);
		foreach ($aTemplateDetails as $aTemplate)
		{
			$strTemplateType = $aTemplate['name'];
			$strVersion		= $aTemplate['description'];
			$strEffectiveOn	= OutputMask()->ShortDate($aTemplate['effective_datetime']);
			/*if ($dboTemplate->TemplateId->Value != NULL)
			{
				// There is an active template
				$strVersion		= $dboTemplate->Version->Value;
				$strEffectiveOn	= OutputMask()->ShortDate($dboTemplate->EffectiveOn->Value);
			}
			else
			{
				$strVersion		= "[ No template has been defined yet ]";
				$strEffectiveOn	= "";
			}*/

			Table()->EmailTemplate->AddRow($strTemplateType, $strVersion, $strEffectiveOn);
			//Table()->DocumentTemplate->SetOnClick("Vixen.CustomerGroupDetails.LoadDocumentTemplateHistory({$dboTemplate->TypeId->Value})");
			$strLoadTemplateHistory = Href()->ViewEmailTemplateHistory(DBO()->CustomerGroup->Id->value, $aTemplate['name'], $aTemplate['id']);
			Table()->EmailTemplate->SetOnClick("window.location='$strLoadTemplateHistory'");
		}

		Table()->EmailTemplate->RowHighlighting = TRUE;
		Table()->EmailTemplate->Render();

		// Draw the button to link to the "View Document Resources" page
		$strViewDocumentResourceLink = htmlspecialchars(Href()->ViewDocumentResources(DBO()->CustomerGroup->Id->Value), ENT_QUOTES);
		echo "
<div class='ButtonContainer'>

</div>
			";
		//<input type='button' value='View Document Resources' onClick='window.location = \"$strViewDocumentResourceLink\"' style='float:right'></input>
	}
}

?>
