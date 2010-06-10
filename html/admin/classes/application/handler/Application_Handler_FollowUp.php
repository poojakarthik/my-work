<?php

class Application_Handler_FollowUp extends Application_Handler
{
	/*
	 * *** NOTE ON USING THIS HANDLER ***
	 *
	 * For Manage & ManageRecurring:
	 * 	- If no sub path	: the logged in users follow-ups are shown
	 *	- If 'All'			: all follow-ups are shown (only if proper admin(
	 * 	- If an employee id	: that employees follow-ups are shown
	 */
	
	public function Manage($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		$aDetailsToRender	= array();
		
		try
		{
			$sEmployee		= $subPath[0];
			$bUserIsAdmin	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
			
			if ($sEmployee)
			{
				if ($sEmployee == 'All')
				{
					if ($bUserIsAdmin)
					{
						$sBreadCrumb	= 'Manage All Follow-Ups';
					}
					else
					{
						// Only admin users can 
						throw new Exception('You do not have permission to view this page');
					}
				}
				else if ($oEmployee	= Employee::getForId((int)$sEmployee))
				{
					$sBreadCrumb						= 'Manage Follow-Ups For '.$oEmployee->getName();
					$aDetailsToRender['iEmployeeId']	= $oEmployee->Id;
				}
			}
			else
			{
				$oEmployee							= Employee::getForId(Flex::getUserId());
				$sBreadCrumb						= 'Manage Follow-Ups For '.$oEmployee->getName();
				$aDetailsToRender['iEmployeeId']	= $oEmployee->Id;
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
			$sEmployee		= $subPath[0];
			$bUserIsAdmin	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
			
			if ($sEmployee)
			{
				if ($sEmployee == 'All')
				{
					if ($bUserIsAdmin)
					{
						$sBreadCrumb	= 'Manage All Recurring Follow-Ups';
					}
					else
					{
						// Only admin users can 
						throw new Exception('You do not have permission to view this page');
					}
				}
				else if ($oEmployee	= Employee::getForId((int)$sEmployee))
				{
					$sBreadCrumb						= 'Manage Recurring Follow-Ups For '.$oEmployee->getName();
					$aDetailsToRender['iEmployeeId']	= $oEmployee->Id;
				}
			}
			else
			{
				$oEmployee							= Employee::getForId(Flex::getUserId());
				$sBreadCrumb						= 'Manage Recurring Follow-Ups For '.$oEmployee->getName();
				$aDetailsToRender['iEmployeeId']	= $oEmployee->Id;
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
