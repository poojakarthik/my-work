<?php

class Application_Handler_Contact extends Application_Handler
{
	// View/Edit a contact
	public function View($aSubPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_OPERATOR_VIEW, PERMISSION_OPERATOR_EXTERNAL));
		
		try
		{
			$iContactId			= (int)$aSubPath[0];
			$aDetailsToRender	= array();
			
			// Get contact
			$aDetailsToRender['oContact']	= Contact::getForId($iContactId);
			
			// Get all note types
			$aDetailsToRender['aNoteTypes']	= Note_Type::getAll();
			
			// Get accounts
			$aAccounts	= $aDetailsToRender['oContact']->getAccounts(true);
			
			// Calculate account overdue amounts
			foreach ($aAccounts as $oAccount)
			{
				$oAccount->fOverdueAmount	= $GLOBALS['fwkFramework']->GetOverdueBalance($oAccount->Id);
			}
			
			$aDetailsToRender['aAccounts']	= $aAccounts;
			
			// Get the contact titles list
			$aDetailsToRender['aContactTitles']	= Contact_Title::getAll();
			
			// Load page_template
			$this->LoadPage('contact_view', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message']		= "An error occured";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
}

?>
