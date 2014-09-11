<?php

class JSON_Handler_Customer_Status extends JSON_Handler
{
	//------------------------------------------------------------------------//
	// modify
	//------------------------------------------------------------------------//
	/**
	 * modify()
	 *
	 * Handles ajax request from client, to modify a customer status
	 * 
	 * Handles ajax request from client, to modify a customer status
	 *
	 * @param	int		$intStatusId		id of the CustomerStatus to modify
	 * @param	string	$strDefaultNormalAction		customer_status.default_action_description
	 * @param	string	$strDefaultOverdueAction	customer_status.default_overdue_action_description
	 * @param	array	$arrRoleSpecificActions		indexed array of objects defining the UserRole specific action descriptions for this CustomerStatus
	 * 												obj->UserRoleId		id of the user role
	 * 												obj->Normal			normal action description
	 * 												obj->Overdue		action description when customer is overdue
	 * @return	array		["ERROR"]				this will only be present in the array, if an error was encountered
	 * 						["Success"]				TRUE if the customer status could be updated, else FALSE
	 * 						["ValidationErrors"]	string detailing all the Validation errors that were encountered, if there were any
	 * @method
	 */
	public function modify($intStatusId, $strDefaultNormalAction, $strDefaultOverdueAction, $arrRoleSpecificActions)
	{
		if (!AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
		{
			return array("ERROR" => "You are not authorised to modify Customer Statuses");
		}

		// Retrieve the CustomerStatus object and update it
		try
		{
			$objStatus = Customer_Status::getForId($intStatusId);
			if ($objStatus === NULL)
			{
				throw new Exception("Could not find Customer Status with id: $intStatusId");
			}
			
			$arrUserRoles = User_Role::getAll();
			
			// Validate everything
			$arrErrors = array();
			
			$strDefaultNormalAction		= trim($strDefaultNormalAction);
			$strDefaultOverdueAction	= trim($strDefaultOverdueAction);
			
			if (strlen($strDefaultNormalAction) == 0)
			{
				$arrErrors[] = "Default Action cannot be blank";
			}
			if (strlen($strDefaultOverdueAction) == 0)
			{
				$arrErrors[] = "Default Overdue Action cannot be blank";
			}
			
			// Validate each of the user role specific actions
			$arrActionsToUpdate = array();
			for ($i=0; $i < count($arrRoleSpecificActions); $i++)
			{
				$arrErrorsForRole = array();
				$arrValidationErrors = array();
				$arrRoleSpecificActions[$i]->Normal		= trim($arrRoleSpecificActions[$i]->Normal);
				$arrRoleSpecificActions[$i]->Overdue	= trim($arrRoleSpecificActions[$i]->Overdue);
				
				// If both the Normal and Overdue descriptions are blank, then disregard them
				if (strlen($arrRoleSpecificActions[$i]->Normal) == 0 && strlen($arrRoleSpecificActions[$i]->Overdue) == 0)
				{
					continue;
				}
				
				if (!array_key_exists($arrRoleSpecificActions[$i]->UserRoleId, $arrUserRoles))
				{
					// The UserRole does not exist
					$arrErrors[] = "User Role with user_role_id: {$arrRoleSpecificActions[$i]->UserRoleId}, does not exist";
					continue;
				}
				
				// Check that both a Normal and an Overdue description have been supplied
				if (strlen($arrRoleSpecificActions[$i]->Normal) == 0 || strlen($arrRoleSpecificActions[$i]->Overdue) == 0)
				{
					$arrErrors[] = "When declaring role specific Actions, both Normal and Overdue actions must be declared. User Role, '{$arrUserRoles[$arrRoleSpecificActions[$i]->UserRoleId]->name}', has only one of these declared";
					continue;
				}
				
				// Validate the normal action
				if (!Customer_Status::isValidActionDescription($arrRoleSpecificActions[$i]->Normal, $arrValidationErrors))
				{
					// The action description is invalid
					$arrErrorsForRole[] = "{$arrUserRoles[$arrRoleSpecificActions[$i]->UserRoleId]->name} Normal Action Description is invalid for the following reasons: ". implode(", ", $arrValidationErrors) .".";
				}
				
				// Validate the overdue action
				$arrValidationErrors = array();
				if (!Customer_Status::isValidActionDescription($arrRoleSpecificActions[$i]->Overdue, $arrValidationErrors))
				{
					// The action description is invalid
					$arrErrorsForRole[] = "{$arrUserRoles[$arrRoleSpecificActions[$i]->UserRoleId]->name} Overdue Action Description is invalid for the following reasons: ". implode(", ", $arrValidationErrors) .".";
				}
				
				if (count($arrErrorsForRole) > 0)
				{
					// Add the error messages specific to this Role, to the big list of errors
					$arrErrors = array_merge($arrErrors, $arrErrorsForRole);
				}
				else
				{
					// The descriptions are valid
					$arrActionsToUpdate[] = $arrRoleSpecificActions[$i];
				}
			}
			
			if (count($arrErrors) > 0)
			{
				return array(	"Success" 			=> FALSE,
								"ValidationErrors"	=> implode("<br />", $arrErrors));
			}

			// Save the details
			$objStatus->setActionDescription(NULL, $strDefaultNormalAction, $strDefaultOverdueAction);
			foreach ($arrActionsToUpdate as $objDetails)
			{
				$objStatus->setActionDescription($objDetails->UserRoleId, $objDetails->Normal, $objDetails->Overdue);
			}
		}
		catch (Exception $e)
		{
			return array("ERROR" => $e->getMessage());
		}
		
		return array("Success"	=> TRUE);
	}
	
	// This will run the report, 
	public function buildSummaryReport($arrCustomerGroups, $arrCustomerStatuses, $arrInvoiceRuns, $strRenderMode)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

		$objReportBuilder = new Customer_Status_Summary_Report();
		$objReportBuilder->SetBoundaryConditions($arrCustomerGroups, $arrCustomerStatuses, $arrInvoiceRuns);

		$objReportBuilder->BuildReport();

		$strReport = $objReportBuilder->GetReport($strRenderMode);
		
		$strRenderMode = strtolower($strRenderMode);
		
		if ($strRenderMode == "html")
		{
			// The user wants the output rendered in the page
			return array(	"Success" => TRUE,
							"Report" => $strReport
						);
		}
		elseif ($strRenderMode == 'excel')
		{
			// The user wants to retrieve the report as an excel spreadsheet
			// Store the report in the user's session, so that the user can retrieve it, not through ajax
			$_SESSION['CustomerStatus']['SummaryReport']['Content'] = $strReport;
			return array(	"Success" => TRUE,
							"Report" => NULL,
							"ReportLocation" => Href()->CustomerStatusSummaryReport(TRUE)
						);
		}
		else
		{
			// Render it in the page
			return array(	"Success" => TRUE,
							"Report" => $strReport
						);
		}
	}
	
	// While the Account Report can handle multiple Invoice Runs, there is the potential of the report to be too ridiculously big
	// So I have limitted it to the first InvoiceRun in the array of InvoiceRuns 
	public function buildAccountReport($arrCustomerGroups, $arrCustomerStatuses, $arrInvoiceRuns, $strRenderMode)
	{
		// We current only support excel for this one
		$strRenderMode = "excel";
		
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

		if (is_array($arrInvoiceRuns) && count($arrInvoiceRuns) > 1)
		{
			// Limit it to the first one in the array
			$arrInvoiceRuns = array($arrInvoiceRuns[0]);
		}

		$objReportBuilder = new Customer_Status_Account_Report();
		$objReportBuilder->SetBoundaryConditions($arrCustomerGroups, $arrCustomerStatuses, $arrInvoiceRuns);

		$objReportBuilder->BuildReport();

		$strReport = $objReportBuilder->GetReport($strRenderMode);
		
		$strRenderMode = strtolower($strRenderMode);
		
		if ($strRenderMode == "html")
		{
			// The user wants the output rendered in the page
			return array(	"Success" => TRUE,
							"Report" => $strReport
						);
		}
		elseif ($strRenderMode == 'excel')
		{
			// The user wants to retrieve the report as an excel spreadsheet
			// Store the report in the user's session, so that the user can retrieve it, not through ajax
			$_SESSION['CustomerStatus']['AccountReport']['Content'] = $strReport;
			return array(	"Success" => TRUE,
							"Report" => NULL,
							"ReportLocation" => Href()->CustomerStatusAccountReport(TRUE)
						);
		}
		else
		{
			// Render it in the page
			return array(	"Success" => TRUE,
							"Report" => $strReport
						);
		}
		
		
	}
	

}

?>
