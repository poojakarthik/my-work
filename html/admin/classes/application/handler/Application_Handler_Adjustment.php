<?php

class Application_Handler_Adjustment extends Application_Handler
{
	const MAX_RECORDS_PER_PAGE = 25;

	public function ManageRequests($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Manage Adjustment Requests");
		
		$aDetailsToRender	= array();
		
		try
		{
			$this->LoadPage('adjustment_management', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] 		= "An error occured while trying to build the \"Manage Adjustment Requests\" page";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
	
	public function ManageTypes($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Manage Adjustment Types");
		
		$aDetailsToRender = array();
		
		try
		{
			$this->LoadPage('adjustment_type_list', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] 		= "An error occured while trying to build the \"Manage Adjustment Types\" page";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
}

?>
