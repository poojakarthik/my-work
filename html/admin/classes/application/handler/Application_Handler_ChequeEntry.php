<?php
class Application_Handler_ChequeEntry extends Application_Handler {
	public static $permissions = array(PERMISSION_PROPER_ADMIN);

	public function Process($subPath) {
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_PROPER_ADMIN));

		try {
			BreadCrumb()->Employee_Console();
			BreadCrumb()->SetCurrentPage('Cheque Entry');
			$this->LoadPage('cheque_entry', HTML_CONTEXT_DEFAULT, array());
		} catch (Exception $exception) {
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, array(
				'Message' => 'An error occured while trying to build the Cheque Entry page',
				'ErrorMessage' => $exception->getMessage()
			));
		}
	}
}