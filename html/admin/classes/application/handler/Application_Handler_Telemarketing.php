<?php

class Application_Handler_Telemarketing extends Application_Handler
{
	// View all Breached Contracts which are pending approval
	public function Wash($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		// Build List of Breached Contracts and their recommended actions
		try
		{
			// Get list of Top-level Dealers
			$arrDetailsToRender['CallCentres']	= Dealer::getCallCentres();
			
			// Get the Customer Groups/Vendors
			$arrDetailsToRender['Vendors']		= Customer_Group::getAll();
			
			$this->LoadPage('telemarketing_wash', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
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
