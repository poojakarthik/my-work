<?php

class HtmlTemplate_Sale_View extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render()
	{
		$objSale = $this->mxdDataToRender['Sale'];
		
		Debug($objSale);
	}
}

?>
