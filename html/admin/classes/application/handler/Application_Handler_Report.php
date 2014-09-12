<?php
class Application_Handler_Report extends Application_Handler {
	const TEMPORARY_DIRECTORY = "temp/";

	// View all Reports
	public function Manage($subPath) {
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_ADMIN, PERMISSION_ACCOUNTS));

		try
		{
			$aDetailsToRender	= array();
			$this->LoadPage('report_manage', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message']		= "An error occured";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		/* echo "
			<script>
				module.provide(['flex/component/page/datareport/list'], function () {
					var component = new (require('flex/component/page/report/list'))();
					document.querySelector('#content').appendChild(component.getNode());
				});
			</script>
		"; */
	}

	// View all Reports
	public function AddNewReport($subPath) {
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_ADMIN, PERMISSION_ACCOUNTS));

		try
		{
			$aDetailsToRender	= array();
			$this->LoadPage('report_addnewreport', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message']		= "An error occured";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		/* echo "
			<script>
				module.provide(['flex/component/page/datareport/list'], function () {
					var component = new (require('flex/component/page/report/list'))();
					document.querySelector('#content').appendChild(component.getNode());
				});
			</script>
		"; */
	}

	public function Download()
	{
		if (isset($_REQUEST['sFileName']) && isset($_REQUEST['iCSV']))
		{
			$sFileName		= urldecode($_REQUEST['sFileName']);
			$sDecodedPath	= FILES_BASE_PATH.self::TEMPORARY_DIRECTORY.date('Y')."/".date('F')."/".date('j')."/".$sFileName;

			if(file_exists($sDecodedPath)) {
				// Defined 'Content-type'
				if ((int)$_REQUEST['iCSV'] == 1)
				{
					header('Content-type: text/csv');
				}
				else
				{
					header('Content-type: application/x-msexcel');
				}
				
				// Set the file to be downloaded as an attachment
				header('Content-Disposition: attachment; filename="'.addslashes($sFileName).'"');
				echo @file_get_contents($sDecodedPath);
				@unlink($sDecodedPath);
				die;
			}
			echo $sDecodedPath;
			
			
		}
	}
}
