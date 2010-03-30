<?php

class HtmlTemplate_DataReport_List extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		//$this->LoadJavascript('pagination');
		//$this->LoadJavascript('reflex_validation');
		$this->LoadJavascript("page_datareport_list");
		$this->LoadJavascript("popup_datareport");
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Data Reports");
	}

	public function Render()
	{
		echo "
<div id='DataReportListContainer'></div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			objAdjustmentType = new Page_DataReport_List(\$ID('DataReportListContainer'));
		}, false)
</script>\n";

	}
}

?>