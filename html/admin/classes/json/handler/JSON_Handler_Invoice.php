<?php

class JSON_Handler_Invoice extends JSON_Handler
{
	
	public function generateInterimInvoice($intAccount, $intInvoiceRunType)
	{
		try
		{
			// Check user permissions
			if (!(AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN) || AuthenticatedUser()->UserHasPerm(PERMISSION_ACCOUNTS)))
			{
				return array(
								'Success'			=> false,
								'ErrorMessage'		=> "Insufficient privileges",
								'HasPermissions'	=> false,
							);
			}
			
			// Attempt to generate the interim/final Invoice
			try
			{
				// Start the Transaction
				DataAccess::getDataAccess()->TransactionStart();
				
				// TODO: Generate the Invoice
				
				// Commit the Transaction
				DataAccess::getDataAccess()->TransactionCommit();
			}
			catch (Exception $eException)
			{
				DataAccess::getDataAccess()->TransactionRollback();
				
				if (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD))
				{
					throw $eException;
				}
				else
				{
					throw new Exception("There was an internal error in Flex.  Please try again.");
				}
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"					=> true,
							"intInvoiceRunType"			=> $intInvoiceRunType,
							"intAccountId"				=> $intAccount
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> false,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage()
						);
		}
	}
}

?>
