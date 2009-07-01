<?php

class Application_Handler_RecurringAdjustment extends Application_Handler
{
	const MAX_RECORDS_PER_PAGE = 25;
	

	// Lists sales
	public function ManageRecurringAdjustmentRequests($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Manage Recurring Adjustment Requests");
		
		try
		{
			$intDefaultLimit = self::MAX_RECORDS_PER_PAGE;
	
			// We currently don't cache anything, because the pagination functionality doesn't currently support sorting

			$detailsToRender = array();
			$detailsToRender['Limit']	= $intDefaultLimit;
			
			$this->LoadPage('recurring_adjustment_management', HTML_CONTEXT_DEFAULT, $detailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured while trying to build the \"Manage Recurring Adjustment Requests\" page";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
}

?>
