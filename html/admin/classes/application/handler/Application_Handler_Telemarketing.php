<?php

class Application_Handler_Telemarketing extends Application_Handler
{
	// Shows a history of Proposed Dialling Lists and their associated data
	public function History($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		// Build List of Breached Contracts and their recommended actions
		try
		{
			// Get list of Imported Proposed Dialler files
			// TODO
			
			$this->LoadPage('telemarketing_file_history', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message']		= "An error occured";
			$arrDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
	
	// Uploads a posted file
	public function Upload($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		// Build List of Breached Contracts and their recommended actions
		try
		{
			// Import the File (into FileImport)
			// TODO
			
			$this->LoadPage('sales_fnn_washing_uploaded', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message']		= "An error occured";
			$arrDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
}

?>
