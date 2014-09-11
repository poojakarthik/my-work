<?php

class HtmlTemplate_Charge_Type_Manage extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('reflex_validation');
		$this->LoadJavascript("page_charge_type");
		$this->LoadJavascript("popup_charge_type");
		
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
		BreadCrumb()->SetCurrentPage("Manage Single {$sPageType} Types");
	}

	public function Render()
	{
		$sChargeModelJS	= (isset($this->mxdDataToRender['iChargeModel']) ? $this->mxdDataToRender['iChargeModel'] : "null");
		
		echo "
		<div id='ChargeTypeContainer'></div>
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
							objChargeType = new Page_Charge_Type(\$ID('ChargeTypeContainer'), $sChargeModelJS);
						}, false
					)
				}
			)
		</script>\n";
	}
}

?>