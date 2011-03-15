<?php

class HtmlTemplate_Account_Class_Management extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('sort');
		$this->LoadJavascript('filter');
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('sort');
		$this->LoadJavascript('filter');
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_text');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('section');
		$this->LoadJavascript('reflex_validation');
		$this->LoadJavascript('reflex_loading_overlay');
		$this->LoadJavascript('customer_group');
		$this->LoadJavascript('popup_account_class');
		$this->LoadJavascript('popup_account_class_replace_default_for_customer_groups');
		$this->LoadJavascript('component_account_class_list');
		$this->LoadJavascript('component_customer_group_account_class_configuration');
		$this->LoadJavascript('page_account_class_management');
	}

	public function Render()
	{
		$iAccountId = DBO()->Account->Id->Value;
		echo "
		<div id='AccountClassManagement'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					new Page_Account_Class_Management(\$ID('AccountClassManagement'));
				}
			)
		</script>\n";
	}
}

?>