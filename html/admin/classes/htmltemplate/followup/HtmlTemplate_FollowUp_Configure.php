<?php

class HtmlTemplate_FollowUp_Configure extends FlexHtmlTemplate
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
		$this->LoadJavascript('reflex_date_format');
		$this->LoadJavascript('date_time_picker_dynamic');
		
		// Control fields & other inputs
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_text');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('control_field_date_picker');
		$this->LoadJavascript('control_field_combo_date');
		
		// Pseudo ORM
		$this->LoadJavascript('employee');
		$this->LoadJavascript('followup_category');
		$this->LoadJavascript('followup_status');
		$this->LoadJavascript('followup_closure');
		
		// Classes that renders the page
		$this->LoadJavascript('component_followup_category_list');
		$this->LoadJavascript('component_followup_closure_list');
		$this->LoadJavascript('component_followup_modify_reason_list');
		$this->LoadJavascript('component_followup_recurring_modify_reason_list');
		$this->LoadJavascript('component_followup_reassign_reason_list');
		$this->LoadJavascript('popup_record_edit');
		$this->LoadJavascript('page_followup_configure');
	}

	public function Render()
	{
		echo "
		<div id='FollowUpConfigureContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					Flex.Constant.loadConstantGroup(
						['followup_closure_type', 'followup_type', 'followup_recurrence_period', 'status'], 
						function()
						{
							objFollowUpConfigure = new Page_FollowUp_Configure(\$ID('FollowUpConfigureContainer'));
						}, false
					)
				}
			)
		</script>\n";
	}
}

?>