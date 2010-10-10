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
class HtmlTemplateEmailTemplateHistory extends HtmlTemplate
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
		$iTemplateId = $_GET['EmailTemplate_Id'];
		$aTemplateDetails = Email_Template_Details::getForTemplateId($iTemplateId);

		$bolUserIsSuperAdmin = AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN);
		echo "<!-- START HtmlTemplateEmailTemplateHistory -->\n";

		Table()->EmailTemplateHistory->SetHeader("Description", "Effective", "End","Created",   "&nbsp");
		Table()->EmailTemplateHistory->SetWidth( "45%", "15%", "10%", "10%", "20%");
		Table()->EmailTemplateHistory->SetAlignment("Left", "Left", "Left", "Left","Right");

		Table()->EmailTemplateHistory->SetSortable(TRUE);
		Table()->EmailTemplateHistory->SetSortFields(NULL, NULL, NULL, NULL, NULL, NULL, NULL);

		Table()->EmailTemplateHistory->SetPageSize(20);
		$intDraftVersion = NULL;
		$strNow = GetCurrentDateAndTimeForMySQL();

		foreach ($aTemplateDetails as $aTemplateVersion)
		{
			//$strVersion				= $iVersion;
			$strDescription			= $aTemplateVersion['description'];
			$strEffectiveOn			= $aTemplateVersion['effective_datetime'];
			$strEnd					= $aTemplateVersion['end_datetime'];
			if ($strEffectiveOn>$strEnd)
			{
				$strEffectiveOnCell = "<span style='text-decoration:line-through' title='This template was never used'>$strEffectiveOn</span>";
			}
			else
			{
				$strEffectiveOnCell = "<span $strEffectiveOnTitle'>$strEffectiveOn</span>";
			}

			$strCreatedOnCell	= "<span title='". OutputMask()->LongDateAndTime($aTemplateVersion['created_timestamp']) ."'>". OutputMask()->ShortDate($aTemplateVersion['created_timestamp']) ."</span>";
			$strEndDatenCell	= "<span title='". OutputMask()->LongDateAndTime($strEnd) ."'>". OutputMask()->ShortDate($strEnd) ."</span>";


			$oMenu = new MenuItems();
			$sJavaScriptNew = $this->EmailTextEditor($aTemplateVersion['template_version_id'], Email_Template_Logic::CREATE);
			///$sJavaScriptEdit = $this->EmailTextEditor($aTemplateVersion['template_version_id'],Email_Template_Logic::EDIT);
			$sJavaScriptRead = $this->EmailTextEditor($aTemplateVersion['template_version_id'], Email_Template_Logic::READ);

			$strActionsCell = "";
			$strNew			= '<img src="img/template/new.png" title="Build new template based on this one" onclick="'.$sJavaScriptNew.'" style="cursor:pointer"/>';
			$strView		= '<img src="img/template/view.png" title="View the template" onclick="'.$sJavaScriptRead.'" style="cursor:pointer"/>';
			//$strEdit		= '<img src="img/template/edit.png" title="Edit Draft" onclick="'.$sJavaScriptEdit.'" style="cursor:pointer"/>';

			if (!$bolUserIsSuperAdmin)
			{
				// The user cannot add or edit templates, they can only view them
				$strActionsCell = $strView;
			}
			else
			{


					//if ($strEffectiveOn > $strNow)
					//{
						// The Template can still be editted as its effective date has not been reached yet
					//	$strActionsCell = $strEdit;
					//}

					$strActionsCell	.= $strNew;
				}
			//}

			Table()->EmailTemplateHistory->AddRow($strDescription, $strEffectiveOnCell, $strEnd,$strCreatedOnCell, $strActionsCell);
			$iVersion++;
		}

		Table()->EmailTemplateHistory->Render();

		/*$intCustomerGroup	= DBO()->CustomerGroup->Id->Value;
		$strDraftVersion	= ($intDraftVersion != NULL)? $intDraftVersion : "null";
		echo "<script type='text/javascript'>Vixen.DocumentTemplateHistory.Initialise($intCustomerGroup, $strDraftVersion)</script>\n";*/

		echo "<!-- END HtmlTemplateDocumentTemplateHistory -->\n";
	}


	function EmailTextEditor($iDetailsId, $iMode)
	{
		return	"javascript: 	JsAutoLoader.loadScript(
							[
								'../ui/javascript/section.js',
								'javascript/popup_email_text_editor.js',
								'javascript/popup_email_template_select.js',
								'javascript/popup_email_html_preview.js',
								'javascript/popup_email_templates.js',
								'javascript/popup_email_save_confirm.js',
								'../ui/javascript/control_tab_group.js',
								'../ui/javascript/control_tab.js',
								'../ui/javascript/control_tab.js',
								'../ui/javascript/control_field.js',
								'../ui/javascript/dataset_ajax.js',
								'../ui/javascript/dataset_ajax.js',
										'../ui/javascript/pagination.js',
										'../ui/javascript/filter.js',
								'../ui/javascript/control_field_textarea.js',
								'../ui/javascript/control_field_select.js',
								'../ui/javascript/control_field_date_picker.js',
								'../ui/javascript/component_date_picker.js',
								'../ui/javascript/control_field_checkbox.js'

							],
							function()
							{
								new Popup_Email_Text_Editor($iDetailsId, '".$_GET['Template_Name']."', '".DBO()->CustomerGroup->internal_name->Value."',$iMode);
							}
					);";



	}
}

?>
