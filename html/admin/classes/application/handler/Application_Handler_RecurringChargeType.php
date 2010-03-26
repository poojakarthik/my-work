<?php

class Application_Handler_RecurringChargeType extends Application_Handler
{
	// View all Charge Types
	public function Manage($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		
		// Build List of Action Types
		try
		{
			$aDetailsToRender = array();
			$this->LoadPage('recurring_charge_type_list', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
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
