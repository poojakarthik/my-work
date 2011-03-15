<?php

class HtmlTemplate_Adjustment_Management extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('filter');
		$this->LoadJavascript('section');
		$this->LoadJavascript('sort');
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('page_adjustment_management');
		$this->LoadJavascript('popup_adjustment_management_action_adjustment');
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Manage Adjustment Requests");
	}

	public function Render()
	{
		echo "
		<div id='ManageAdjustmentsContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					new Page_Adjustment_Management(\$ID('ManageAdjustmentsContainer'));
				}
			)
		</script>\n";

	}
}

?>
