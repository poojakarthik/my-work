<?php

class FlexHtmlTemplate extends HtmlTemplate
{
	public $_strId = NULL;
	public $mxdDataToRender = NULL;

	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		if ($intContext !== NULL)
		{
			$this->_intContext = $intContext;
		}
		if ($strId !== NULL)
		{
			$this->_strId = $strId;
		}
		if ($mxdDataToRender !== NULL)
		{
			$this->mxdDataToRender = $mxdDataToRender;
		}
	}
}

?>
