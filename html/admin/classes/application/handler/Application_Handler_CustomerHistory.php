<?php

class Application_Handler_CustomerHistory extends Application_Handler
{
	const NEXT_PAGE_ACCOUNT_OVERVIEW	= "AccountOverview";
	const NEXT_PAGE_CONTACT_DETAILS		= "ContactDetails";
	const SUPRESS_ERRORS_FLAG			= "SupressErrors";

	public function Record($subPath)
	{
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);

		$bolSupressErrors = FALSE;
		if (is_array($subPath) && count($subPath) == 1)
		{
			$strAction = strtolower(array_shift($subPath));
			if ($strAction == strtolower(self::SUPRESS_ERRORS_FLAG))
			{
				$bolSupressErrors = TRUE;
			}
		}
		
		// Log this page request in the EmployeeAccountHistory table
		try
		{
			$intAccountId = (isset($_GET['AccountId']))? intval($_GET['AccountId']) : NULL;
			$intContactId = (isset($_GET['ContactId']))? intval($_GET['ContactId']) : NULL;
			
			try
			{
				TransactionStart();
				$objUser = Employee::getForId(AuthenticatedUser()->GetUserId());
					
				if ($objUser !== NULL)
				{
					$objUser->recordCustomerInAccountHistory($intAccountId, $intContactId);
				}
			}
			catch (Exception $e)
			{
				TransactionRollback();
				if (!$bolSupressErrors)
				{
					throw $e;
				}
			}
			TransactionCommit();
			
			// Work out what page to redirect the user to
			if (isset($_GET['NextPage']) && in_array($_GET['NextPage'], array(self::NEXT_PAGE_ACCOUNT_OVERVIEW, self::NEXT_PAGE_CONTACT_DETAILS)))
			{
				$strNextPage = $_GET['NextPage'];
			}
			else
			{
				if ($intAccountId !== NULL)
				{
					$strNextPage = self::NEXT_PAGE_ACCOUNT_OVERVIEW;
				}
				else if ($intContactId !== NULL)
				{
					$strNextPage = self::NEXT_PAGE_COONTACT_DETAILS;
				}
				else
				{
					throw new exception("No page has been specified to redirect the user to");
				}
			}
			
			switch ($strNextPage)
			{
				case self::NEXT_PAGE_ACCOUNT_OVERVIEW:
					$strNewLocation = Href()->AccountOverview($intAccountId);
					break;
					
				case self::NEXT_PAGE_CONTACT_DETAILS:
					$strNewLocation = Href()->ViewContact($intContactId);
					break;
			}

			// Redirect the user
			header("Location: " . preg_replace("/reflex.php\/.*$/", $strNewLocation, $_SERVER['REQUEST_URI']));
			die;
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to record this customer in the account history";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		
		
	}

}

?>
