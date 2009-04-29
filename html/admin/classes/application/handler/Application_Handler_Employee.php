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

			$arrMessages = Employee_Message::getAllEffectiveSince($strEarliestEffectiveOnTimestamp);
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
}

?>
