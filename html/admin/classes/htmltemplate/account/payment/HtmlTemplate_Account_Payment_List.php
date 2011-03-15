<?php

class HtmlTemplate_Account_Payment_List extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('sort');
		$this->LoadJavascript('filter');
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('control_field_text');
		$this->LoadJavascript('control_field_number');
		$this->LoadJavascript('control_field_checkbox');
		$this->LoadJavascript('component_account_payment_list');
		$this->LoadJavascript('popup_account_payment_reverse');
		$this->LoadJavascript('component_account_payment_create');
		$this->LoadJavascript('popup_account_payment_create');
	}

	public function Render()
	{
		$iAccountId = DBO()->Account->Id->Value;
		echo "
		<div id='AccountPaymentList'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					new Component_Account_Payment_List({$iAccountId}, \$ID('AccountPaymentList'));
				}
			)
		</script>\n";
	}
}

?>