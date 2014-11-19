<?php
class Application_Handler_Report extends Application_Handler {
	const TEMPORARY_DIRECTORY = "temp/";

	// Manage Reports: List of reports with privilege to configure, schedule, and run as per their PERMISSION Level
	public function Manage($subPath) {
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_REPORT_USER));

		try	{
			$aDetailsToRender = array();
			$this->LoadPage('report_manage', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		} catch (Exception $e) {
			$aDetailsToRender['Message'] = "An error occured";
			$aDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}


	public function Download() {
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_REPORT_USER);
		if (isset($_REQUEST['sFileName']) && isset($_REQUEST['iCSV'])) {
			$sFileName = urldecode($_REQUEST['sFileName']);
			$sDecodedPath = FILES_BASE_PATH . self::TEMPORARY_DIRECTORY . date('Y') . "/" . date('F') . "/" . date('j') . "/" . $sFileName;

			if (file_exists($sDecodedPath)) {
				// Defined 'Content-type'
				if ((int)$_REQUEST['iCSV'] == 1) {
					header('Content-type: text/csv');
				} else {
					header('Content-type: application/x-msexcel');
				}
				
				// Set the file to be downloaded as an attachment
				header('Content-Disposition: attachment; filename="'.addslashes($sFileName).'"');
				if (false === @file_get_contents($sDecodedPath)) {
				    throw new Exception('Couldn\'t get file ' . $sDecodedPath. ' contents: ' . ($php_errormsg ? $php_errormsg : 'Unknown Error'));
				}
				echo @file_get_contents($sDecodedPath);
				@unlink($sDecodedPath);
				exit(0);
			}
			echo $sDecodedPath;		
		}
	}
}