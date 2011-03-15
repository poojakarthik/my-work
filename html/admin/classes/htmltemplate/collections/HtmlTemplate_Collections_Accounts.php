<?php

class HtmlTemplate_Collections_Accounts extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		// AJAX and pagination
		$this->LoadJavascript('reflex_breadcrumb_select');
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
		$this->LoadJavascript('reflex_loading_overlay');
		$this->LoadJavascript('collection_event_type');
		$this->LoadJavascript('collection_event_correspondence');
		$this->LoadJavascript('collection_event_report');
		$this->LoadJavascript('collection_event_action');
		$this->LoadJavascript('collection_event_barring');
		$this->LoadJavascript('collection_event_tdc');
		$this->LoadJavascript('collection_event_oca');
		$this->LoadJavascript('collection_event_charge');
		$this->LoadJavascript('collection_event_severity');
		$this->LoadJavascript('collection_event_milestone');
		$this->LoadJavascript('customer_group');
		$this->LoadJavascript('component_collections_account_management');
		$this->LoadJavascript('popup_collections_event_management_select_events');
		$this->LoadJavascript('popup_collection_event_action');
		$this->LoadJavascript('popup_collection_event_barring');
		$this->LoadJavascript('popup_collection_event_tdc');
		$this->LoadJavascript('page_collections_accounts');
		$this->LoadJavascript('popup_select_spreadsheet_file_type');
	}

	public function Render()
	{
		echo "
		<div id='CollectionsAccountsContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					new Page_Collections_Accounts(\$ID('CollectionsAccountsContainer'));
				}
			)
		</script>\n";
	}
}

?>