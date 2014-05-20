<?php

class HtmlTemplate_Customer_Group_Record_Type_Visibility extends FlexHtmlTemplate {
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL) {
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render() {

		?>
		<article class='flex-page'></article>
		<script>
			// Provide component once all dom content has been loaded.
			document.observe('DOMContentLoaded',
				module.provide.bind(module, ["flex/component/page/customer/group/record-type-visibility"], function () {
					// Instantiate Component
					new require('flex/component/page/customer/group/record-type-visibility')({
						// Component.CONFIG
						'iCustomerGroupId' : <?php echo $this->mxdDataToRender['iCustomerGroupId']; ?>
					});
				})
			);
		</script>
		<?
	}
}
