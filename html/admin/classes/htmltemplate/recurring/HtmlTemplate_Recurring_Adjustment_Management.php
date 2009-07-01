<?php

class HtmlTemplate_Recurring_Adjustment_Management extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('recurring_adjustment_management');
	}

	public function Render()
	{
		$intMaxPageSize = $this->mxdDataToRender['Limit'];
		
		echo "
<div id='ManageRecurringAdjustmentsContainer'></div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			objRecurringAdjustmentManagement = new Recurring_Adjustment_Management(\$ID('ManageRecurringAdjustmentsContainer'), $intMaxPageSize);
		}, false)
</script>\n";

	}
}

?>
