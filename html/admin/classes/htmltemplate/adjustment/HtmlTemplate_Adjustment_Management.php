<?php

class HtmlTemplate_Adjustment_Management extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('adjustment_management');
	}

	public function Render()
	{
		$intMaxPageSize = $this->mxdDataToRender['Limit'];
		
		echo "
<div id='ManageAdjustmentsContainer'></div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			objAdjustmentManagement = new Adjustment_Management(\$ID('ManageAdjustmentsContainer'), $intMaxPageSize);
		}, false)
</script>\n";

	}
}

?>
