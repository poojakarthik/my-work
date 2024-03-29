<?php

class HtmlTemplate_Service_Unbilled extends FlexHtmlTemplate
{
	public function __construct($iContext=NULL, $sId=NULL, $mDataToRender=NULL)
	{
		parent::__construct($iContext, $sId, $mDataToRender);
	}

	public function Render()
	{
		HtmlTemplate_Invoice_Service::renderCharges($this->mxdDataToRender['Charges']);
		HtmlTemplate_Invoice_Service::renderCDRs(
			$this->mxdDataToRender['CDRs'], 
			$this->mxdDataToRender['RecordTypes'], 
			$this->mxdDataToRender['filter'],
			null,
			$this->mxdDataToRender['ServiceType'],
			$this->mxdDataToRender['ServiceId']
		);
	}
}

?>