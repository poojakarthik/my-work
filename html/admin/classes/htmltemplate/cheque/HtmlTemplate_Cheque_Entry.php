<?php
class HtmlTemplate_Cheque_Entry extends FlexHtmlTemplate {
	public function Render() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$H = new DOM_Factory($dom);

		$dom->appendChild(
			$article = $H->article(array('class' => 'flex-page'),
				$H->script("
					document.addEventListener('DOMContentLoaded', function () {
						module.provide(['flex/component/page/customer/payments/cheque-entry'], function () {
							var chequeEntry = new (require('flex/component/page/customer/payments/cheque-entry'));
							var page = document.querySelector('.flex-page');
							page.innerHTML = '';
							page.appendChild(chequeEntry.getNode());
						});
					});
				")
			)
		);
		echo $dom->saveHTML($article);
	}
}