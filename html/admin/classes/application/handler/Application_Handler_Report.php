<?php
class Application_Handler_Report extends Application_Handler {
	const TEMPORARY_DIRECTORY = "upload/datareport/";

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
}
