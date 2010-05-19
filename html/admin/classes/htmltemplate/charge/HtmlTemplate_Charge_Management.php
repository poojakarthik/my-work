<?php

class HtmlTemplate_Charge_Management extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('charge_management');
		
		// Get the name of the charge model being displayed (defaults to all)
		if (isset($mxdDataToRender['iChargeModel']))
		{
			$sPageType	= Constant_Group::getConstantGroup('charge_model')->getConstantName($mxdDataToRender['iChargeModel']);
		}
		else
		{
			$sPageType	= 'Charge & Adjustment';
		}
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Manage {$sPageType} Requests");
	}

	public function Render()
	{
		$intMaxPageSize	= $this->mxdDataToRender['Limit'];
		$sChargeModelJS	= (isset($this->mxdDataToRender['iChargeModel']) ? $this->mxdDataToRender['iChargeModel'] : "null");
		
		echo "
		<div id='ManageChargesContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					Flex.Constant.loadConstantGroup(
						['charge_model'], 
						function()
						{
							objChargeManagement = new Charge_Management(\$ID('ManageChargesContainer'), $intMaxPageSize, $sChargeModelJS);
						}, false
					)
				}
			)
		</script>\n";

	}
}

?>
