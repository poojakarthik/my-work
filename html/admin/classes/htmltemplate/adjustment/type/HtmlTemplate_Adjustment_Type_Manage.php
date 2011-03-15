<?php

class HtmlTemplate_Adjustment_Type_Manage extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('filter');
		$this->LoadJavascript('sort');
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_text');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('control_field_checkbox');
		$this->LoadJavascript('section');
		$this->LoadJavascript('reflex_validation');
		$this->LoadJavascript('reflex_loading_overlay');
		$this->LoadJavascript('popup_adjustment_type');
		$this->LoadJavascript('component_adjustment_type_list');
		$this->LoadJavascript('popup_adjustment_review_outcome');
		$this->LoadJavascript('component_adjustment_review_outcome_list');
		$this->LoadJavascript('page_adjustment_configure');
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Manage Adjustment Types & Outcomes");
	}

	public function Render()
	{
		echo "
		<div id='AdjustmentConfigureContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					new Page_Adjustment_Configure(\$ID('AdjustmentConfigureContainer'));
				}
			)
		</script>\n";
	}
}

?>