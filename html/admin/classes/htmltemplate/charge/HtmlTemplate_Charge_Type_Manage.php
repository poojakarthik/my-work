<?php

class HtmlTemplate_Charge_Type_Manage extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('reflex_validation');
		$this->LoadJavascript("page_charge_type");
		$this->LoadJavascript("popup_charge_type");
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Manage Single Adjustment Types");
	}

	public function Render()
	{
		echo "
<div id='ChargeTypeContainer'></div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			objAdjustmentType = new Page_Charge_Type(\$ID('ChargeTypeContainer'));
		}, false)
</script>\n";

	}
}

?>