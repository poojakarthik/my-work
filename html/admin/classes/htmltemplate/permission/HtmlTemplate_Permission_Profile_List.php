<?php
class HtmlTemplate_Permission_Profile_List extends FlexHtmlTemplate
{
	public function __construct($iContext=NULL, $sId=NULL, $mDataToRender=NULL)
	{
		parent::__construct($iContext, $sId, $mDataToRender);
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('reflex_validation');
		$this->LoadJavascript('reflex_style');
		$this->LoadJavascript('reflex_fx_reveal');
		$this->LoadJavascript('reflex_control');
		$this->LoadJavascript('reflex_control_tree');
		$this->LoadJavascript('reflex_control_tree_node');
		$this->LoadJavascript('reflex_control_tree_node_root');
		$this->LoadJavascript('reflex_control_tree_node_checkable');
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_text');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('operation_tree');
		$this->LoadJavascript('status');
		$this->LoadJavascript('operation');
		$this->LoadJavascript('operation_profile');
		$this->LoadJavascript('component_operation_profile_list');
		$this->LoadJavascript('popup_operation_profile_edit');
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Manage Permission Profiles");
	}

	function Render()
	{
		echo "
			<div id='PermissionProfileListContainer'></div>
			<script type='text/javascript'>
				Event.observe(window, 'load', 
					function()
					{
						var oPage = new Component_Operation_Profile_List(\$ID('PermissionProfileListContainer'));
					}, false);
			</script>\n";
	}
}

?>