<?php

class HtmlTemplate_Account_Adjustment_List extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('sort');
		$this->LoadJavascript('filter');
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('component_account_adjustment_list');
		$this->LoadJavascript('popup_account_adjustment_reverse');
	}

	public function Render()
	{
		$iAccountId = DBO()->Account->Id->Value;
		echo "
		<div id='AccountAdjustmentList'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					new Component_Account_Adjustment_List({$iAccountId}, \$ID('AccountAdjustmentList'));
				}
			)
		</script>\n";
	}
}

?>