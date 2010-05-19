<?php

class HtmlTemplate_Recurring_Charge_Management extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('recurring_charge_management');
	}

	public function Render()
	{
		$intMaxPageSize = $this->mxdDataToRender['Limit'];
		
		echo "
<div id='ManageRecurringChargesContainer'></div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			objRecurringChargeManagement = new Recurring_Charge_Management(\$ID('ManageRecurringChargesContainer'), $intMaxPageSize);
		}, false)
</script>\n";

	}
}

?>
