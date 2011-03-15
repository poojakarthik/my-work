<?php

class HtmlTemplate_Barring_Action_Ledger extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		// AJAX and pagination
		$this->LoadJavascript('component_date_picker');
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_text');
		$this->LoadJavascript('control_field_textarea');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('control_field_date_picker');
		$this->LoadJavascript('section');
		$this->LoadJavascript('sort');
		$this->LoadJavascript('filter');
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('reflex_validation');
		$this->LoadJavascript('employee');
		$this->LoadJavascript('component_barring_action_ledger');
		$this->LoadJavascript('page_barring_action_ledger');
		$this->LoadJavascript('popup_custom_row_selection');
		$this->LoadJavascript('popup_select_spreadsheet_file_type');
	}

	public function Render()
	{
		echo "
		<div id='BarringActionLedgerContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					new Page_Barring_Action_Ledger(\$ID('BarringActionLedgerContainer'));
				}
			)
		</script>\n";
	}
}

?>