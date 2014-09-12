<?php
class HtmlTemplateAddNewReport extends HtmlTemplate {
	function __construct($intContext, $strId) {
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;

		//$this->LoadJavascript("service_rates");
	}

	function Render() {
		echo "
		<script>
		module.provide(['flex/component/page/report/list'], function () {
		var component = new (require('flex/component/page/report/list'))();
		document.querySelector('#AddNewReportDiv').appendChild(component.getNode());
		});
		</script>";
	}
}