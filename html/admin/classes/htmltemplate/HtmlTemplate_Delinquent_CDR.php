<?php

class HtmlTemplate_Delinquent_CDR extends FlexHtmlTemplate
{

	protected $sStartDate;
	protected $sEndDate;

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
		$this->LoadJavascript('control_field_combo_date');
		$this->LoadJavascript('control_field_text');
		$this->LoadJavascript('control_field_checkbox');

		// Pseudo ORM
		$this->LoadJavascript('employee');
		$this->LoadJavascript('followup_category');
		$this->LoadJavascript('followup_status');
		$this->LoadJavascript('followup_closure');
		$this->LoadJavascript('followup_modify_reason');
		$this->LoadJavascript('followup_reassign_reason');

		// Classes that renders the page
		$this->LoadJavascript('component_followup_list_all');
		$this->LoadJavascript('page_followup_list');
		$this->LoadJavascript('popup_followup_close');
		$this->LoadJavascript('popup_followup_reassign');
		$this->LoadJavascript('popup_followup_due_date');
		$this->LoadJavascript('popup_followup_view');
		$this->LoadJavascript('popup_cdr');
		$this->LoadJavascript('popup_cdr_service_list');



		$this->LoadJavascript('page_delinquent_cdr_list');
		$this->LoadJavascript('component_delinquent_cdr_list');


	}




	public function Render()
	{

		echo "
		<div id='DelinquentCDRContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window,
				'load',
				function()
				{
					CDRList = new Page_Delinquent_CDR_List(\$ID('DelinquentCDRContainer'));

				}
			)
		</script>\n";
	}
}

?>