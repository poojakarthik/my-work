<?php

class HtmlTemplate_DataReport_List extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript("page_datareport_list");
		$this->LoadJavascript("popup_datareport");
		$this->LoadJavascript("popup_data_report_permission");
		$this->LoadJavascript("reflex_style");
		$this->LoadJavascript("reflex_fx_reveal");
		$this->LoadJavascript("reflex_control");
		$this->LoadJavascript("reflex_control_tree");
		$this->LoadJavascript("reflex_control_tree_node");
		$this->LoadJavascript("reflex_control_tree_node_root");
		$this->LoadJavascript("reflex_control_tree_node_checkable");
		$this->LoadJavascript("control_field");
		$this->LoadJavascript("control_field_text");
		$this->LoadJavascript("control_field_password");
		$this->LoadJavascript("control_field_checkbox");
		$this->LoadJavascript("control_field_date_picker");
		$this->LoadJavascript("control_field_select");
		$this->LoadJavascript("operation_tree");
		$this->LoadJavascript("status");
		$this->LoadJavascript("operation_profile");
		$this->LoadJavascript("reflex_sorter");
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Data Reports");
	}

	public function Render()
	{
		echo "
<div id='DataReportListContainer'></div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			objAdjustmentType = new Page_DataReport_List(\$ID('DataReportListContainer'));
		}, false)
</script>\n";

	}
}

?>