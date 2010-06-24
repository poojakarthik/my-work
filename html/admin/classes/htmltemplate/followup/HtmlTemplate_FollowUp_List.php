<?php

class HtmlTemplate_FollowUp_List extends FlexHtmlTemplate
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
		$this->LoadJavascript('component_date_picker');
		$this->LoadJavascript('actions_and_notes');
		
		// Control fields & other components
		$this->LoadJavascript('section');
		$this->LoadJavascript('section_expandable');
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('control_field_date_picker');
		$this->LoadJavascript('control_field_combo_date');
		
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
	}

	public function Render()
	{
		// Always show active ones to start with
		$this->mxdDataToRender['bActive']	= true;
		
		$sEditJSON		= ($this->mxdDataToRender['bEditMode'] ? 'true' : 'false');
		$sEmployeeId	= (isset($this->mxdDataToRender['iEmployeeId']) ? $this->mxdDataToRender['iEmployeeId'] : 'null');
		$sActive		= ($this->mxdDataToRender['bActive'] ? 'true' : 'false');
		
		echo "
		<div id='FollowUpListContainer'></div>
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
							objFollowUpList = new Page_FollowUp_List(\$ID('FollowUpListContainer'), $sEmployeeId, $sEditJSON, $sActive);
						}, false
					)
				}
			)
		</script>\n";
	}
}

?>