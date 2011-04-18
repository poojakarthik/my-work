<?php

class Application_Handler_Email extends Application_Handler {
	public function DownloadAttachment($aSubPath) {
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		$aDetailsToRender = array();
		
		try {
			// Get the attachment
			$oAttachment = Email_Attachment::getForId($aSubPath[0]);
			if (!$oAttachment) {
				throw new Exception("Invalid attachment specified");
			}
			
			// Output using the record for details
			header("Content-type: {$oAttachment->mime_type}");
			header("Content-Disposition: {$oAttachment->disposition}; filename=\"{$oAttachment->filename}\"");
			header("Content-Encoding: {$oAttachment->encoding}");
			echo $oAttachment->content;
		} catch (Exception $e) {
			$aDetailsToRender['Message'] 		= "An error occured while trying to build the \"Manage Adjustment Requests\" page";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
}

?>