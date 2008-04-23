<?php
//----------------------------------------------------------------------------//
// HtmlTemplateDocumentTemplateHistory
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateDocumentTemplateHistory
 *
 * The Document Template History HTML Template object
 *
 * The Document Template History HTML Template object
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateDocumentTemplateHistory
 * @extends	HtmlTemplate
 */
class HtmlTemplateDocumentTemplateHistory extends HtmlTemplate
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
		
		$this->LoadJavascript("table_sort");
		$this->LoadJavascript("document_template");
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
		echo "<!-- START HtmlTemplateDocumentTemplateHistory -->\n";
		
		Table()->TemplateHistory->SetHeader("Version", "Description", "Effective", "Created", "Modified", "Last Used", "&nbsp");
		Table()->TemplateHistory->SetWidth("5%", "45%", "10%", "10%", "10%", "10%", "10%");
		Table()->TemplateHistory->SetAlignment("Left", "Left", "Left", "Left", "Left", "Left", "Right");
		
		// Sorting functionality cannot currently handle sorting dates in the format "dd/mm/yyyy" 
		// as you can't just do a string comparison. Currently the pagination funcationality only works
		// if sorting is turned on
		Table()->TemplateHistory->SetSortable(TRUE);
		Table()->TemplateHistory->SetSortFields(NULL, NULL, NULL, NULL, NULL, NULL, NULL);
		
		Table()->TemplateHistory->SetPageSize(20);
		$intDraftVersion = NULL;
		$strNow = GetCurrentDateAndTimeForMySQL();
		foreach (DBL()->Templates as $dboTemplate)
		{
			$strVersion			= $dboTemplate->Version->Value;
			$strDescription		= $dboTemplate->Description->Value;
			$strEffectiveOn		= ($dboTemplate->EffectiveOn->Value != NULL)? OutputMask()->ShortDate($dboTemplate->EffectiveOn->Value) : "Draft";
			$strCreatedOn		= OutputMask()->ShortDate($dboTemplate->CreatedOn->Value);
			$strLastModifiedOn	= OutputMask()->ShortDate($dboTemplate->LastModifiedOn->Value);
			$strLastUsedOn		= ($dboTemplate->LastUsedOn->Value != NULL)? OutputMask()->ShortDate($dboTemplate->LastUsedOn->Value) : "";
			
			// Flag the versions that have been completely overridden and therefore never used
			if ($dboTemplate->Overridden->Value)
			{
				$strEffectiveOn = "<span style='text-decoration:line-through' title='This template was never used'>$strEffectiveOn</span>";
			}
			
			$strActionsCell = "";
			if ($dboTemplate->EffectiveOn->Value == NULL)
			{
				// The template is the draft template
				$intDraftVersion	= $dboTemplate->Version->Value;
				$strActionsCell		= "<img src='img/template/edit.png' title='Edit Draft' onclick='Vixen.DocumentTemplate.EditTemplate({$dboTemplate->Id->Value})' style='cursor:pointer'/>";
			}
			else
			{
				// The template has been committed (it has an effective on date set)
				$strEdit = "";
				if ($dboTemplate->EffectiveOn->Value > $strNow)
				{
					// The Template can still be editted as its effective date has not been reached yet
					$strEdit	= "<img src='img/template/edit.png' title='Edit Draft' onclick='Vixen.DocumentTemplate.Edit({$dboTemplate->Id->Value})' style='cursor:pointer'/>";
				}
				
				$strNew			= "<img src='img/template/new.png' title='Build new template based on this one' onclick='Vixen.DocumentTemplate.BuildNew({$dboTemplate->Id->Value}, {$dboTemplate->Version->Value})' style='cursor:pointer'/>";
				$strView		= "<img src='img/template/view.png' title='View the template' onclick='Vixen.DocumentTemplate.View({$dboTemplate->Id->Value})' style='cursor:pointer'/>";
				$strActionsCell	= $strView  . $strNew . $strEdit;
			}
			
			Table()->TemplateHistory->AddRow($strVersion, $strDescription, $strEffectiveOn, $strCreatedOn, $strLastModifiedOn, $strLastUsedOn, $strActionsCell);
		}

		Table()->TemplateHistory->Render();
		
		$intCustomerGroup	= DBO()->CustomerGroup->Id->Value;
		$strDraftVersion	= ($intDraftVersion != NULL)? $intDraftVersion : "null";
		echo "<script type='text/javascript'>Vixen.DocumentTemplate.InitialiseHistoryPage($intCustomerGroup, $strDraftVersion)</script>\n";
		
		echo "<!-- END HtmlTemplateDocumentTemplateHistory -->\n";	
	}
}

?>
