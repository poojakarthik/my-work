<?php

class HtmlTemplate_Carrier_Module_Management extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_text');
		$this->LoadJavascript('control_field_textarea');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('control_field_checkbox');
		$this->LoadJavascript('control_field_number');
		$this->LoadJavascript('control_field_hidden');
		$this->LoadJavascript('control_field_combo_time');
		$this->LoadJavascript('section');
		$this->LoadJavascript('sort');
		$this->LoadJavascript('filter');
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('reflex_loading_overlay');
		$this->LoadJavascript('reflex_validation');
		$this->LoadJavascript('customer_group');
		$this->LoadJavascript('component_carrier_module_list');
		$this->LoadJavascript('component_carrier_module_config');
		$this->LoadJavascript('page_carrier_module_list');
		$this->LoadJavascript('popup_carrier_module');
		$this->LoadJavascript('popup_carrier_module_config_json_editor');
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Manage Carrier Modules");
	}

	public function Render()
	{
		echo "
		<div id='ManageCarrierModuleContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					new Page_Carrier_Module_List(\$ID('ManageCarrierModuleContainer'));
				}
			)
		</script>\n";

	}
}

?>
