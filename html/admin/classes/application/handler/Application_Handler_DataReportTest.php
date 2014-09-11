<?php

class Application_Handler_DataReportTest extends Application_Handler
{
	const TEMPORARY_DIRECTORY	= "upload/datareporttest/";
	
	// View all DataReports
	public function ListAll($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_ADMIN, PERMISSION_ACCOUNTS));
		
		try
		{
			$aDetailsToRender	= array();
			$this->LoadPage('datareport_list', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message']		= "An error occured";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
	
	
}

?>
