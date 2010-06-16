<?php

class HtmlTemplate_FollowUp_Recurring_List extends FlexHtmlTemplate
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
		$this->LoadJavascript('followup_recurring_modify_reason');
		$this->LoadJavascript('followup_reassign_reason');
		
		// Classes that renders the page
		$this->LoadJavascript('page_followup_recurring_list');
		$this->LoadJavascript('popup_followup_reassign');
		$this->LoadJavascript('popup_followup_recurring_end_date');
	}

	public function Render()
	{
		$sEditJSON		= ($this->mxdDataToRender['bEditMode'] ? 'true' : 'false');
		$sEmployeeId	= (isset($this->mxdDataToRender['iEmployeeId']) ? $this->mxdDataToRender['iEmployeeId'] : 'null');
		
		echo "
		<div id='FollowUpRecurringListContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					Flex.Constant.loadConstantGroup(
						['followup_closure_type', 'followup_type', 'followup_recurrence_period'], 
						function()
						{
							objFollowUpRecurringList = new Page_FollowUp_Recurring_List(\$ID('FollowUpRecurringListContainer'), $sEmployeeId, $sEditJSON);
						}, false
					)
				}
			)
		</script>\n";
	}
}

?>