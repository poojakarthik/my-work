<?php
class HtmlTemplateServiceRateList extends HtmlTemplate {
	function __construct($intContext, $strId) {
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;

		//$this->LoadJavascript("service_rates");
	}

	function Render() {
		$aServiceRates = array();
		foreach (DBL()->CurrentServiceRate as $oServiceRateDBO) {
			$aServiceRates[] = $oServiceRateDBO->AsArray();
		}

		echo "
			<script>
				module.provide(['flex/component/page/service/rate/override/list'], function () {
					var component = new (require('flex/component/page/service/rate/override/list'))({
						serviceId: " . DBO()->Service->Id->Value . ",
						serviceRates: " . json_encode($aServiceRates) . ",
						permissions: " . json_encode(array('newOverrideRate' => AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN))) . "
					});

					document.querySelector('#ServiceRateListDiv').appendChild(component.getNode());
				});
			</script>
		";
	}
}