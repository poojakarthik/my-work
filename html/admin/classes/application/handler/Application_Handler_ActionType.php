<?php

class Application_Handler_ActionType extends Application_Handler
{
	const	RECORD_DISPLAY_LIMIT	= 24;
	
	// View all Breached Contracts which are pending approval
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
				$objStdClassActionType	= $objActionType->toArray();
				$objStdClassActionType['arrAllowableActionAssociationTypes']	= 
				
				$arrDetailsToRender['arrActionTypes'][$intIndex]	= $objStdClassActionType;
			}
			
			$arrDetailsToRender['arrCustomerGroups']		= Customer_Group::getAll();
			$arrDetailsToRender['arrActionAssociationType']	= Customer_Group::getAll();
			
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
