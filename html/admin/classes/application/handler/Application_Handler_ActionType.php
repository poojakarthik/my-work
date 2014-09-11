<?php

class Application_Handler_ActionType extends Application_Handler
{
	// View all Action Types
	public function Manage($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		
		// Build List of Action Types
		try
		{
			$arrDetailsToRender	= array();
			
			$arrDetailsToRender['arrActionTypes']	= array();
			$arrActionTypes							= Action_Type::getAll();
			foreach ($arrActionTypes as $intIndex=>$objActionType)
			{
				$arrDetailsToRender['arrActionTypes'][$intIndex]	= $objActionType;
			}
			
			$this->LoadPage('action_type_list', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
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
