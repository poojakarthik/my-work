<?php
class HtmlTemplate_Report_Manage extends FlexHtmlTemplate {

	public function Render() {
		?>
		<article class='flex-page'></article>
		<script>
			// Provide component once all dom content has been loaded.
			document.observe('DOMContentLoaded',
				module.provide.bind(module, ["flex/component/page/report/manage"], function () {
					// Instantiate Component
					new require('flex/component/page/report/manage')();
				})
			);
		</script>

		<?
	}
}
