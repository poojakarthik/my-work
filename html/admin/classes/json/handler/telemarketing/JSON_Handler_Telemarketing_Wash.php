<?php

class JSON_Handler_Telemarketing_Wash extends JSON_Handler
{
	
	public function getCallCentrePermissions()
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		try
		{
			// Get list of Call Centres
			$arrCallCentres				= Dealer::getCallCentres();
			$arrCallCentrePermissions	= array();
			foreach ($arrCallCentres as $objDealer)
			{
				$arrCallCentrePermissions[$objDealer->id]	= $objDealer->toArray();
			}
			
			// Get list of Vendors
			$arrCustomerGroups	= Customer_Group::getAll();
			$arrVendors			= array();
			foreach ($arrCustomerGroups as $objCustomerGroup)
			{
				$arrVendors[$objCustomerGroup->id]	= array('id' => $objCustomerGroup->id, 'externalName' => $objCustomerGroup->externalName);
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"					=> TRUE,
							"arrCallCentrePermissions"	=> $arrCallCentrePermissions,
							"arrVendors"				=> $arrVendors
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> FALSE,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage()
						);
		}
	}
}

?>
