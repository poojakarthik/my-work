<?php

class HtmlTemplate_Collections_Configure extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('reflex_validation');
		$this->LoadJavascript('control_field');
		$this->LoadJavascript('control_field_text');
		$this->LoadJavascript('control_field_select');
		$this->LoadJavascript('control_field_textarea');
		$this->LoadJavascript('control_field_checkbox');
		$this->LoadJavascript('control_tab');
		$this->LoadJavascript('control_tab_group');
		$this->LoadJavascript('sort');
		$this->LoadJavascript('filter');
		$this->LoadJavascript('dataset_ajax');
		$this->LoadJavascript('pagination');
		$this->LoadJavascript('section');
		$this->LoadJavascript('component_collections_event_type');
		$this->LoadJavascript('component_collections_severity');
		$this->LoadJavascript('component_collections_event');
		$this->LoadJavascript('component_collections_scenario');
		$this->LoadJavascript('component_collections_scenario_event_timeline');
		$this->LoadJavascript('popup_collections_scenario_event_timeline_event');
		$this->LoadJavascript('popup_collections_event_type');
		$this->LoadJavascript('popup_collections_severity');
		$this->LoadJavascript('popup_collections_warning');
		$this->LoadJavascript('popup_collections_event');
		$this->LoadJavascript('popup_collections_scenario');
		$this->LoadJavascript('component_collections_event_type_list');
		$this->LoadJavascript('component_collections_severity_list');
		$this->LoadJavascript('component_collections_event_list');
		$this->LoadJavascript('popup_collections_event_list_event_details');
		$this->LoadJavascript('component_collections_scenario_list');
		$this->LoadJavascript('page_collections_configure');
	}

	public function Render()
	{
		echo "
		<div id='CollectionsConfigureContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					new Page_Collections_Configure(\$ID('CollectionsConfigureContainer'));
				}
			)
		</script>\n";
	}
}

?>