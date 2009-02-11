<?php

class Application_Handler_RatePlan extends Application_Handler
{
	const	PLAN_BROCHURE_FILE_EXTENSION	= 'pdf';
	const	PLAN_BROCHURE_MIME_CONTENT_TYPE	= 'application/pdf';
	
	const	AUTH_SCRIPT_FILE_EXTENSION		= 'txt';
	const	AUTH_SCRIPT_MIME_CONTENT_TYPE	= 'text/plain';
	
	// Attaches a Brochure to a Rate Plan
	public function SetBrochure($subPath)
	{
		$bolVerboseErrors	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		$arrDetailsToRender	= array();
		try
		{
			//throw new Exception(print_r($_POST, true));
			
			if (!DataAccess::getDataAccess()->TransactionStart())
			{
				throw new Exception("Flex was unable to start a Transaction.  The Upload has been aborted.  Please try again shortly.");
			}
			
			$qryQuery	= new Query();
			
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_RATE_MANAGEMENT | PERMISSION_PROPER_ADMIN))
			{
				throw new Exception("You do not have sufficient privileges to upload a Plan Brochure!" . (($bolVerboseErrors) ? ' But you do have GOD mode... wtf' : ''));
			}
			
			// Check the File Name format
			$strFileName	= $_FILES['Plan_SetBrochure_File']['name'];
			$arrFileName	= explode('.', $strFileName);
			$strMIME		= $_FILES['Plan_SetBrochure_File']['type'];
			$strExtension	= end($arrFileName);
			if (strtolower($strExtension) !== strtolower(self::PLAN_BROCHURE_FILE_EXTENSION) || $strMIME !== self::PLAN_BROCHURE_MIME_CONTENT_TYPE)
			{
				throw new Exception("'{$strFileName}' is not a valid PDF file (Extension: '{$strExtension}'; MIME: '{$strMIME}').  Ensure that you are trying to upload the correct file, and try again.");
			}
			
			// Set this as the Plan's new Brochure
			$objRatePlan	= new Rate_Plan(array('Id'=>$_POST['Plan_SetBrochure_RatePlanId']), true);
			$objRatePlan->setBrochure($_FILES['Plan_SetBrochure_File']['tmp_name']);
			
			// Commit the transaction
			DataAccess::getDataAccess()->TransactionCommit();
			
			// Generate Response
			$arrDetailsToRender['Success']	= true;
		}
		catch (Exception $e)
		{
			DataAccess::getDataAccess()->TransactionRollback();
			
			$arrDetailsToRender['Success']	= false;
			$arrDetailsToRender['Message']	= $e->getMessage();
		}
		
		// Render the JSON'd Array
		flush();
		echo JSON_Services::instance()->encode($arrDetailsToRender);
		die;
	}
	
	// Attaches an Authorisation Script to a Rate Plan
	public function SetAuthScript($subPath)
	{
		$bolVerboseErrors	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		$arrDetailsToRender	= array();
		try
		{
			//throw new Exception(print_r($_POST, true));
			
			if (!DataAccess::getDataAccess()->TransactionStart())
			{
				throw new Exception("Flex was unable to start a Transaction.  The Upload has been aborted.  Please try again shortly.");
			}
			
			$qryQuery	= new Query();
			
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_RATE_MANAGEMENT | PERMISSION_PROPER_ADMIN))
			{
				throw new Exception("You do not have sufficient privileges to upload an Authorisation Script!" . (($bolVerboseErrors) ? ' But you do have GOD mode... wtf' : ''));
			}
			
			// Check the File Name format
			$strFileName	= $_FILES['Plan_SetAuthScript_File']['name'];
			$arrFileName	= explode('.', $strFileName);
			$strMIME		= $_FILES['Plan_SetAuthScript_File']['type'];
			$strExtension	= end($arrFileName);
			if (strtolower($strExtension) !== strtolower(self::AUTH_SCRIPT_FILE_EXTENSION) || $strMIME !== self::AUTH_SCRIPT_MIME_CONTENT_TYPE)
			{
				throw new Exception("'{$strFileName}' is not a valid text file (Extension: '{$strExtension}'; MIME: '{$strMIME}').  Ensure that you are trying to upload the correct file, and try again.");
			}
			
			// Set this as the Plan's new Brochure
			$objRatePlan	= new Rate_Plan(array('Id'=>$_POST['Plan_SetAuthScript_RatePlanId']), true);
			$objRatePlan->setAuthorisationScript($_FILES['Plan_SetAuthScript_File']['tmp_name']);
			
			// Commit the transaction
			DataAccess::getDataAccess()->TransactionCommit();
			
			// Generate Response
			$arrDetailsToRender['Success']	= true;
		}
		catch (Exception $e)
		{
			DataAccess::getDataAccess()->TransactionRollback();
			
			$arrDetailsToRender['Success']	= false;
			$arrDetailsToRender['Message']	= $e->getMessage();
		}
		
		// Render the JSON'd Array
		flush();
		echo JSON_Services::instance()->encode($arrDetailsToRender);
		die;
	}
}
?>