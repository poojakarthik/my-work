<?php

class Application_Handler_FollowUp extends Application_Handler
{
	public function Manage($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		$aDetailsToRender	= array();
		
		try
		{
			// Set the bread crumb, diffferent if an employee id is specified in the url
			$sBreadCrumb	= 'Manage All Follow-Ups';
			$iEmployeeId	= null;
			
			if ($subPath[0] && Employee::getForId(Flex::getUserId())->isGod())
			{
				$iEmployeeId	= (int)$subPath[0];
				$oEmployee		= Employee::getForId($subPath[0]);
				
				if ($oEmployee)
				{
					$sBreadCrumb						= 'Manage Follow-Ups For '.$oEmployee->getName();
					$aDetailsToRender['iEmployeeId']	= $iEmployeeId;
				}
			}
			
			$aDetailsToRender['bEditMode']	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
			
			BreadCrumb()->Employee_Console();
			BreadCrumb()->SetCurrentPage($sBreadCrumb);
			
			$this->LoadPage('followup_list', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] 		= "An error occured while trying to build the \"Manage Follow-Ups\" page";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
	
	public function ManageRecurring($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		$aDetailsToRender	= array();
		
		try
		{
			// Set the bread crumb, diffferent if an employee id is specified in the url
			$sBreadCrumb	= 'Manage All Recurring Follow-Ups';
			$iEmployeeId	= null;
			
			if ($subPath[0] && Employee::getForId(Flex::getUserId())->isGod())
			{
				$iEmployeeId	= (int)$subPath[0];
				$oEmployee		= Employee::getForId($subPath[0]);
				
				if ($oEmployee)
				{
					$sBreadCrumb						= 'Manage Recurring Follow-Ups For '.$oEmployee->getName();
					$aDetailsToRender['iEmployeeId']	= $iEmployeeId;
				}
			}
			
			$aDetailsToRender['bEditMode']		= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
			$aDetailsToRender['bRecurringOnly']	= true;
			
			BreadCrumb()->Employee_Console();
			BreadCrumb()->SetCurrentPage($sBreadCrumb);
			
			$this->LoadPage('followup_recurring_list', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] 		= "An error occured while trying to build the \"Manage Recurring Follow-Ups\" page";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
}

?>
