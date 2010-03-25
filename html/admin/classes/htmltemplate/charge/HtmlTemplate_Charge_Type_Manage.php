<?php

class HtmlTemplate_Charge_Type_Manage extends FlexHtmlTemplate
{
	private	$_arrActionAssociationTypeColumnOrder = array();
	
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript("page_charge_type");
		$this->LoadJavascript("popup_charge_type");
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Manage Single Adjustment Types");
	}

	public function Render()
	{
		$intMaxPageSize = $this->mxdDataToRender['Limit'];
		
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