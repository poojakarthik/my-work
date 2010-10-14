<?php

class HtmlTemplate_Email_Template_History extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);

		// AJAX and pagination
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('filter');
		$this->LoadJavascript('sort');

		// Helper classes
		$this->LoadJavascript('reflex_validation');
		$this->LoadJavascript('reflex_sorter');
		$this->LoadJavascript('reflex_anchor');
		$this->LoadJavascript('component_date_picker');
		$this->LoadJavascript('actions_and_notes');

		// Control fields & other components
		$this->LoadJavascript('section');
		$this->LoadJavascript('section_expandable');
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('control_field_date_picker');
		$this->LoadJavascript('control_tab');
		$this->LoadJavascript('control_field_combo_date');
		$this->LoadJavascript('control_field_textarea');
		$this->LoadJavascript('control_tab_group');
		$this->LoadJavascript('control_field_checkbox');
		$this->LoadJavascript('email_template_table');
		$this->LoadJavascript('control_field_text');




		// Classes that renders the page
		$this->LoadJavascript('popup_email_text_editor');
		$this->LoadJavascript('popup_email_html_preview');
		$this->LoadJavascript('popup_email_save_confirm');
		$this->LoadJavascript('popup_email_test_email');


		$this->LoadJavascript('page_email_template_history');
		$this->LoadJavascript('component_email_template_history');









	}

	public function Render()
	{


		echo "
		<div id='TemplateVersionContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window,
				'load',
				function()
				{

					objFollowUpList = new Page_Email_Template_History(\$ID('TemplateVersionContainer'),". $this->mxdDataToRender['iTemplateId'].",'". $this->mxdDataToRender['sTemplateName']."', '".$this->mxdDataToRender['customerGroup']->external_name."');


				}
			)
		</script>\n";
	}
}

?>