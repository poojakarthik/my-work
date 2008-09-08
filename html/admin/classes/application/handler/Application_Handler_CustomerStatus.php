<?php

class Application_Handler_CustomerStatus extends Application_Handler
{

	// View all the Customer Statuses in a tabulated format
	public function ViewAll($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->SetCurrentPage("Customer Statuses");
		
		$arrDetailsToRender = array();
		$arrDetailsToRender = Customer_Status::getAllOrderedByPrecedence();

		$this->LoadPage('customer_status_view_all', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
	}

	// View the details of a single CustomerStatus
	public function View($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->Manage_Customer_Statuses();
		
		$intId = array_shift($subPath);
		
		try
		{
			$objStatus = Customer_Status::getForId($intId);
			if ($objStatus === NULL)
			{
				// Customer Status with id == $intId could not be found
				throw new exception("Could not find CustomerStatus with id: $intId");
			}
			
			BreadCrumb()->SetCurrentPage(htmlspecialchars($objStatus->name));
			
			$arrDetailsToRender = array();
			$arrDetailsToRender['CustomerStatus'] = $objStatus;
	
			$this->LoadPage('customer_status_view', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to view CustomerStatus with id: $intId";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
	
	// Edit the details of a single CustomerStatus
	public function Edit($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->Manage_Customer_Statuses();
		
		$intId = array_shift($subPath);
		
		try
		{
			$objStatus = Customer_Status::getForId($intId);
			if ($objStatus === NULL)
			{
				// Customer Status with id == $intId could not be found
				throw new exception("Could not find CustomerStatus with id: $intId");
			}
			
			BreadCrumb()->SetCurrentPage(htmlspecialchars($objStatus->name));
			
			$arrDetailsToRender = array();
			$arrDetailsToRender['CustomerStatus'] = $objStatus;
	
			$this->LoadPage('customer_status_edit', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to Edit CustomerStatus with id: $intId";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
	
	

}

?>
