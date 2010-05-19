<?php

class HtmlTemplate_Employee_List extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('page_employee_list');
		$this->LoadJavascript('pagination');
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage('Employee List');
	}

	public function Render()
	{
		echo "
<div id='EmployeeListContainer'></div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			var oEmployeeList = new Page_Employee_List(\$ID('EmployeeListContainer'));
		}, false)
</script>\n";

	}
}

?>