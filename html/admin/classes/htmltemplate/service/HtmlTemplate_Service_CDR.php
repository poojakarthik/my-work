<?php

class HtmlTemplate_Service_CDR extends FlexHtmlTemplate
{
	public function __construct($iContext=NULL, $sId=NULL, $mDataToRender=NULL)
	{
		parent::__construct($iContext, $sId, $mDataToRender);
	}

	public function Render()
	{
		HtmlTemplate_Invoice_CDR::renderCDRDetails($this->mxdDataToRender);
	}
}

?>