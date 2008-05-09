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
		$this->LoadJavascript("document_template_history");
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
		$bolUserIsSuperAdmin = AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN);
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
			$strVersion				= $dboTemplate->Version->Value;
			$strDescription			= $dboTemplate->Description->Value;
			$strEffectiveOn			= ($dboTemplate->EffectiveOn->Value != NULL)? OutputMask()->ShortDate($dboTemplate->EffectiveOn->Value) : "Draft";
			$strEffectiveOnTitle	= ($dboTemplate->EffectiveOn->Value != NULL)? "title='". OutputMask()->LongDateAndTime($dboTemplate->EffectiveOn->Value) ."'": "";
			
			// Flag the versions that have been completely overridden and therefore never used
			if ($dboTemplate->Overridden->Value)
			{
				$strEffectiveOnCell = "<span style='text-decoration:line-through' title='This template was never used'>$strEffectiveOn</span>";
			}
			else
			{
				$strEffectiveOnCell = "<span $strEffectiveOnTitle'>$strEffectiveOn</span>";
			}
			
			$strCreatedOnCell	= "<span title='". OutputMask()->LongDateAndTime($dboTemplate->CreatedOn->Value) ."'>". OutputMask()->ShortDate($dboTemplate->CreatedOn->Value) ."</span>";
			$strModifiedOnCell	= "<span title='". OutputMask()->LongDateAndTime($dboTemplate->ModifiedOn->Value) ."'>". OutputMask()->ShortDate($dboTemplate->ModifiedOn->Value) ."</span>";
			
			$strLastUsedOnCell	= "";
			if ($dboTemplate->LastUsedOn->Value != NULL)
			{
				$strLastUsedOnCell = "<span title='". OutputMask()->LongDateAndTime($dboTemplate->LastUsedOn->Value) ."'>". OutputMask()->ShortDate($dboTemplate->LastUsedOn->Value) ."</span>";
			}
			
			$strActionsCell = "";
			$strNew			= "<img src='img/template/new.png' title='Build new template based on this one' onclick='Vixen.DocumentTemplateHistory.BuildNew({$dboTemplate->Id->Value}, {$dboTemplate->Version->Value})' style='cursor:pointer'/>";
			$strView		= "<img src='img/template/view.png' title='View the template' onclick='Vixen.DocumentTemplateHistory.View({$dboTemplate->Id->Value})' style='cursor:pointer'/>";
			$strEdit		= "<img src='img/template/edit.png' title='Edit Draft' onclick='Vixen.DocumentTemplateHistory.Edit({$dboTemplate->Id->Value})' style='cursor:pointer'/>";
			if (!$bolUserIsSuperAdmin)
			{
				// The user cannot add or edit templates, they can only view them
				$strActionsCell = $strView;
			} 
			else
			{
				// The user can add and edit templates
				if ($dboTemplate->EffectiveOn->Value == NULL)
				{
					// The template is the draft template
					$intDraftVersion	= $dboTemplate->Version->Value;
					$strActionsCell		= $strEdit . $strView;
				}
				else
				{
					// The template has been committed (it has an effective on date set)
					if ($dboTemplate->EffectiveOn->Value > $strNow)
					{
						// The Template can still be editted as its effective date has not been reached yet
						$strActionsCell = $strEdit;
					}
					
					$strActionsCell	.= $strNew . $strView;
				}
			}
			
			Table()->TemplateHistory->AddRow($strVersion, $strDescription, $strEffectiveOnCell, $strCreatedOnCell, $strModifiedOnCell, $strLastUsedOnCell, $strActionsCell);
		}

		Table()->TemplateHistory->Render();
		
		$intCustomerGroup	= DBO()->CustomerGroup->Id->Value;
		$strDraftVersion	= ($intDraftVersion != NULL)? $intDraftVersion : "null";
		echo "<script type='text/javascript'>Vixen.DocumentTemplateHistory.Initialise($intCustomerGroup, $strDraftVersion)</script>\n";
		
		echo "<!-- END HtmlTemplateDocumentTemplateHistory -->\n";	
	}
}

?>
