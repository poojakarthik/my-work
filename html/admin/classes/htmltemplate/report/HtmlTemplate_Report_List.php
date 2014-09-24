<?php
class HtmlTemplate_Report_List extends FlexHtmlTemplate {

	public function Render() {
		?>
		<article class='flex-page'></article>
		<script>
			// Provide component once all dom content has been loaded.
			document.observe('DOMContentLoaded',
				module.provide.bind(module, ["flex/component/page/report/list"], function () {
					// Instantiate Component
					new require('flex/component/page/report/list')();
				})
			);
		</script>

		<?
	}
}
