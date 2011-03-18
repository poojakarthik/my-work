<?php

class HtmlTemplate_Account_Collections_Summary extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('control_tab_group');
		$this->LoadJavascript('control_tab');
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_text');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('component_date_picker');
		$this->LoadJavascript('control_field_date_picker');
		$this->LoadJavascript('control_field_number');
		$this->LoadJavascript('reflex_loading_overlay');
		$this->LoadJavascript('account');
		$this->LoadJavascript('component_collections_suspension');
		$this->LoadJavascript('popup_account_suspend_from_collections');
		$this->LoadJavascript('popup_account_end_collections_suspension');
		$this->LoadJavascript('component_account_collections');
		$this->LoadJavascript('popup_collections_suspension_view');
		$this->LoadJavascript('popup_collections_promise_instalment_view');
		$this->LoadJavascript('popup_collections_event_instance_view');
		$this->LoadJavascript('popup_account_tio_complaint_view');
		$this->LoadJavascript('popup_account_tio_complaint_end');
		$this->LoadJavascript('popup_account_promise_edit');
		$this->LoadJavascript('popup_account_promise_edit_schedule');
		$this->LoadJavascript('popup_account_promise_cancel');
		$this->LoadJavascript('popup_collections_scenario_event_view');
	}

	public function Render()
	{
		$iAccountId = DBO()->Account->Id->Value;
		echo "
		<div id='AccountCollectionsSummary'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					new Component_Account_Collections({$iAccountId}, \$ID('AccountCollectionsSummary'));
				}
			)
		</script>\n";
	}
}

?>