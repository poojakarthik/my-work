<?php

class Application_Handler_Employee extends Application_Handler
{
	// View all the Customer Statuses in a tabulated format
	public function ManageDailyMessages($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Daily Message Management");

		try
		{
			// We want to retrieve all messages that have an effective_on date > today - 7 days

			$strEarliestEffectiveOnTimestamp = date("Y-m-d 00:00:00", strtotime("-7 day", strtotime(GetCurrentISODateTime())));

			//$arrMessages = Employee_Message::getAllEffectiveSince($strEarliestEffectiveOnTimestamp);
			$arrMessages = Employee_Message::getMesagesForTypeAndFromEffectiveOnTimestamp($strEarliestEffectiveOnTimestamp, constant('EMPLOYEE_MESSAGE_TYPE_GENERAL'));
			//$arrMessages = Employee_Message::getAll(20);
	
			$arrDetailsToRender = array();
			$arrDetailsToRender['MessageHistory'] = $arrMessages;
	
			$this->LoadPage('employee_message_management', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to generate the Employee Message Management page";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
	

	public function TechnicalNoticeManagement($subPath) {
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Technical Notice Management");

		try
		{
			// We want to retrieve all messages that have an effective_on date > today - 7 days

			$strEarliestEffectiveOnTimestamp = date("Y-m-d 00:00:00", strtotime("-7 day", strtotime(GetCurrentISODateTime())));

			//$arrMessages = Employee_Message::getAllEffectiveSince($strEarliestEffectiveOnTimestamp);
			$arrMessages = Employee_Message::getMesagesForTypeAndFromEffectiveOnTimestamp($strEarliestEffectiveOnTimestamp, constant('EMPLOYEE_MESSAGE_TYPE_TECHNICAL'));

			//$arrMessages = Employee_Message::getAll(20);
	
			$arrDetailsToRender = array();
			$arrDetailsToRender['MessageHistory'] = $arrMessages;
	
			$this->LoadPage('employee_technical_notice_management', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to generate the Employee Message Management page";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}

	public function EmployeeList($subPath)
	{
		try
		{
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN))
			{
				throw new Exception('You do not have permission to view this page');
			}
			
			$aDetailsToRender	= array();
			$this->LoadPage('employee_list_all', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] 		= "An error occured when trying to generate the Employee List page";
			$aDetailsToRender['ErrorMessage'] 	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
}

?>
