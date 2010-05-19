<?php

class HtmlTemplate_Charge_Management extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('charge_management');
	}

	public function Render()
	{
		$intMaxPageSize = $this->mxdDataToRender['Limit'];
		
		echo "
<div id='ManageChargesContainer'></div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			objChargeManagement = new Charge_Management(\$ID('ManageChargesContainer'), $intMaxPageSize);
		}, false)
</script>\n";

	}
}

?>
