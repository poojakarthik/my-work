<?php

class Application_Handler_Account extends Application_Handler
{
	// Renders the page for Account Creation
	public function Create($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		
		try
		{
			$qryQuery	= new Query();
			
			//----------------------------------------------------------------//
			// Retrieve Data required to build the page
			//----------------------------------------------------------------//
			
			// Customer Groups
			$arrDetailsToRender['arrCustomerGroups']	= Customer_Group::getAll();
			
			// Countries & States (Australia only)
			$resCountries	= $qryQuery->Execute("SELECT * FROM country WHERE code = 'AUS'");
			if ($resCountries === false)
			{
				throw new Exception($resCountries->Error());
			}
			$arrDetailsToRender['arrCountries']	= array();
			while ($arrCountry = $resCountries->fetch_assoc())
			{
				// States
				$resStates	= $qryQuery->Execute("SELECT * FROM state WHERE country_id = {$arrCountry['id']})");
				if ($resStates === false)
				{
					throw new Exception($resStates->Error());
				}
				$arrCountry['arrStates']	= array();
				while ($arrState = $resStates->fetch_assoc())
				{
					$arrCountry['arrStates'][$arrState['id']]	= $arrState;
				}
				
				$arrDetailsToRender['arrCountries'][$arrCountry['id']]	= $arrCountry;
			}
			
			$this->LoadPage('telemarketing_file_history', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		catch (Exception $eException)
		{
			$arrDetailsToRender['Message']		= "An error occured";
			$arrDetailsToRender['ErrorMessage']	= $eException->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
}
?>