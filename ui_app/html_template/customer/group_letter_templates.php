<?php
//----------------------------------------------------------------------------//
// HtmlTemplateCustomerGroupLetterTemplates
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateCustomerGroupLetterTemplates
 *
 * Lists the currently used LetterTemplates
 *
 * Lists the currently used LetterTemplates
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateCustomerGroupLetterTemplates
 * @extends	HtmlTemplate
 */
class HtmlTemplateCustomerGroupLetterTemplates extends HtmlTemplate
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
	 * @param	string	$_strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
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
		echo "<h2 class='CustomerGroup'>Current Letter Templates</h2>\n";
		
		// Set up the header for the table of LetterTemplates
		Table()->LetterTemplates->SetHeader("Type", "Template", "CreatedOn", "&nbsp;");
		Table()->LetterTemplates->SetWidth("20%", "55%", "15%", "10%");
		Table()->LetterTemplates->SetAlignment("Left", "Left", "Left", "Right");

		// Convert the DBList of LetterTemplate records into an array where the LetterType is the key
		$arrLetterTemplates = Array();
		foreach (DBL()->LetterTemplate as $dboLetterTemplate)
		{
			$arrLetterTemplates[$dboLetterTemplate->LetterType->Value] = Array(	"Template"=> $dboLetterTemplate->Template->Value,
																				"CreatedOn"=> date("d/m/Y", strtotime($dboLetterTemplate->CreatedOn->Value)));
		}

		// Build the table
		foreach ($GLOBALS['*arrConstant']['LetterType'] as $intLetterType=>$arrLetterType)
		{
			if (isset($arrLetterTemplates[$intLetterType]))
			{
				// The Customer Group has a letter template defined for this LetterType
				$strLetterType	= "<span>". GetConstantDescription($intLetterType, "LetterType") ."</span>";
				$strTemplate	= "<span>{$arrLetterTemplates[$intLetterType]['Template']}</span>";
				$strCreatedOn	= "<span>{$arrLetterTemplates[$intLetterType]['CreatedOn']}</span>";
				$strActionsCell = "<span>Edit/View</span>";
			} 
			else
			{
				// The CustomerGroup does not have a letter template degined for this LetterType
				$strLetterType	= "<span>". GetConstantDescription($intLetterType, "LetterType") ."</span>";
				$strTemplate	= "<span class='Red'>No template specified</span>";
				$strCreatedOn	= "<span>&nbsp;</span>";
				$strActionsCell = "<span>New</span>";
				
				// Flag the fact that this CustomerGroup does not have a letter template defined for each LetterType
				$bolTemplateUndefined = TRUE;
			}
			
			Table()->LetterTemplates->AddRow($strLetterType, $strTemplate, $strCreatedOn, $strActionsCell);
		}
		
		Table()->LetterTemplates->RowHighlighting = TRUE;
		Table()->LetterTemplates->Render();
		
		if ($bolTemplateUndefined)
		{
			// Alert the user that not all the LetterTypes have a template defined
			$strJsCode = "Vixen.Popup.Alert('At least one of the LetterTypes does not have a template specified.  Please specify a template for each Type of Letter')";
			echo "<script type='text/javascript'>$strJsCode</script>\n";
		}
	}
}

?>
