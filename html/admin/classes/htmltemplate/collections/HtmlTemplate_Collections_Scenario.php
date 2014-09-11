<?php

class HtmlTemplate_Collections_Scenario extends FlexHtmlTemplate
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
		$this->LoadJavascript('component_collections_event_type');
		$this->LoadJavascript('component_collections_event');
		$this->LoadJavascript('component_collections_scenario');
		$this->LoadJavascript('component_collections_scenario_event_timeline');
		$this->LoadJavascript('popup_collections_scenario_event_timeline_event');
		$this->LoadJavascript('popup_collections_event_type');
		$this->LoadJavascript('popup_collections_event');
		$this->LoadJavascript('page_collections_scenario');
	}

	public function Render()
	{
		$sRenderMode 	= ($this->mxdDataToRender['bRenderMode'] ? 'true' : 'false');
		$sLoadOnly 		= ($this->mxdDataToRender['bLoadOnly'] ? 'true' : 'false');
		$sScenarioId 	= ($this->mxdDataToRender['iScenarioId'] !== null ? $this->mxdDataToRender['iScenarioId'] : 'null');
		
		echo "
		<div id='CollectionsScenarioContainer'></div>
		<script type='text/javascript'>
			Event.observe(
				window, 
				'load',
				function()
				{
					new Page_Collections_Scenario(\$ID('CollectionsScenarioContainer'), {$sRenderMode}, {$sScenarioId}, {$sLoadOnly});
				}
			)
		</script>\n";
	}
}

?>