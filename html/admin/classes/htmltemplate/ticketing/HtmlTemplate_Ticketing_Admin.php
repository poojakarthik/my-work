<?php

class HtmlTemplate_Ticketing_Admin extends FlexHtmlTemplate {
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL) {
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render() {
		echo "
		<article class='flex-page'></article>
		<script>
			// Provide component once all dom content has been loaded.
			document.observe('DOMContentLoaded', 
				module.provide.bind(module, ['flex/component/page/ticketing/administration'], function () {
					// Show loading popup
					var oLoadingPopup = new Reflex_Popup.Loading();
					oLoadingPopup.display();

					// Instantiate Component
					var oComponent = new require('flex/component/page/ticketing/administration')({onready: function() {
						// Ready, attach it to the flex-page if not already attached to something
						if (!oComponent.getNode().parentNode) {
							document.body.select('.flex-page')[0].appendChild(oComponent.getNode());
							oLoadingPopup.hide();
						}
					}});
				})
			);
		</script>";
	}
}

?>
