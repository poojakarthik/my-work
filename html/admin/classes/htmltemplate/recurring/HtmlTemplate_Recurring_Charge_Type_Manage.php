<?php

class HtmlTemplate_Recurring_Charge_Type_Manage extends FlexHtmlTemplate
{
	private	$_arrActionAssociationTypeColumnOrder = array();
	
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('reflex_validation');
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_text');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('page_recurring_charge_type');
		$this->LoadJavascript('popup_recurring_charge_type');
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Manage Recurring Charge Types");
	}

	public function Render()
	{
		echo "
<div id='RecurringChargeTypeContainer'></div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			objChargeType = new Page_Recurring_Charge_Type(\$ID('RecurringChargeTypeContainer'));
		}, false)
</script>\n";

	}
}

?>