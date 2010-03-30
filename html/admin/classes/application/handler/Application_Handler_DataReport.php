<?php

class Application_Handler_DataReport extends Application_Handler
{
	// View all DataReports
	public function ListAll($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		
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
