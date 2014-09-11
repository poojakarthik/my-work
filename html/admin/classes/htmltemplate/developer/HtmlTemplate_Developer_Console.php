<?php
class HtmlTemplate_Developer_Console extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		$this->LoadJavascript('developer_operationpermission');
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('developer_datasetpagination');
		
		$this->LoadJavascript('control_tab');
		$this->LoadJavascript('control_tab_group');
		$this->LoadJavascript('developer_tabgroup');
	}

	public function Render()
	{
		$arrFunctions	= $this->mxdDataToRender['arrFunctions'];
		
		if (count($arrFunctions))
		{
			echo "
<ul>";
			foreach ($arrFunctions as $objFunction)
			{
				$strURL	= str_replace("'", "\\'", $objFunction->strURL);
				echo "\n	<li><a {$objFunction->strType}='{$strURL}'>{$objFunction->strName}</a></li>";
			}
			
			echo "
</ul>
";

		}
		else
		{
			echo "There are no Developer Tools defined.";
		}
	}
}
?>