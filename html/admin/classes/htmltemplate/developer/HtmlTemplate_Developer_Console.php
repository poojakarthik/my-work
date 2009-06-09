<?php
class HtmlTemplate_Developer_Console extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
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
				echo "\n	<li><a {$objFunction->strType}='{$strURL}'></a>{$objFunction->strName}</li>";
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