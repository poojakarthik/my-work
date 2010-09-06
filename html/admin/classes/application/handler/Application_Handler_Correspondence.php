<?php

class Application_Handler_Correspondence extends Application_Handler
{
	public function CreateFromCSV($subPath)
	{
		// TODO: Check user permissions
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_OPERATOR));
		
		$aOutput	= array();
		try
		{
			// Validate input before proceeding
			$aErrors	= array();
			
			// Delivery date time
			$iDeliveryDateTime	= null;
			if (!isset($_POST['delivery_datetime']))
			{
				// Missing
				$aErrors[]	= "No delivery date time supplied.";
			}
			else
			{
				// Given, validate the date string (should be Y-m-d H:i:s)
				$iDeliveryDateTime	= strtotime($_POST['delivery_datetime']);
				if ($iDeliveryDateTime === false)
				{
					// Invalid date string
					$aErrors[]	= "Invalid delivery date time supplied ('".$_POST['delivery_datetime']."').";
				}
			}
			
			// CSV file
			$aFileInfo	= null;
			if (!isset($_FILES['csv_file']))
			{
				// Missing
				$aErrors[]	= 'No CSV file supplied.';
			}
			else
			{
				// Check error code
				$aFileInfo	= $_FILES['csv_file'];
				switch ($aFileInfo['error'])
				{
					case UPLOAD_ERR_OK:
						// Check file extension
						if ($aFileInfo['type'] !== 'text/csv')
						{
							$aErrors[]	= "The incorrect type of file was supplied ('".$aFileInfo['type']."'). Please supply a CSV (Comma Separated Values) file.";
						}
						break;
					case UPLOAD_ERR_INI_SIZE:
						$aErrors[]	= "The CSV file you supplied is too large. Maximum size is ".ini_get('upload_max_filesize').".";
					// No MAX_FILE_SIZE supplied with form
					case UPLOAD_ERR_PARTIAL:
					case UPLOAD_ERR_NO_FILE:
						$aErrors[]	= 'No CSV file supplied.';
						break;
					case UPLOAD_ERR_NO_TMP_DIR:
					case UPLOAD_ERR_CANT_WRITE:
					case UPLOAD_ERR_EXTENSION:
						$aErrors[]	= 'Unable to receive your CSV file due a server error. Please contact YBS for assistance.'; 
				}
			}
			
			// Correspondence_Template id
			$iCorrespondenceTemplateId	= null;
			$oTemplateORM				= null;
			if (!isset($_POST['correspondence_template_id']))
			{
				// Missing
				$aErrors[]	= "No Correspondence Template Id supplied.";
			}
			else
			{
				try
				{
					// Try and load it
					$oTemplateORM	= Correspondence_Template_ORM::getForId($iCorrespondenceTemplateId);
					
					// All good
					$iCorrespondenceTemplateId	= (int)$_POST['correspondence_template_id'];
				}
				catch (Exception $oEx)
				{
					// Invalid
					$sId		= $_POST['correspondence_template_id'];
					$aErrors[]	= "Invalid Correspondence Template Id supplied (".($sId == '' ? 'Not supplied' : "'{$sId}'").")";
				}
			}
			
			if (count($aErrors) > 0)
			{
				// Got errors, add them to the output array and throw exception 
				$aOutput['aErrors']	= $aErrors;
				throw new Exception("There was errors in the form information.");
			}
			
			// Create correspondence run
			$oDA	= DataAccess::getDataAccess();
			$oDA->TransactionStart();
			$oSource	= new Correspondence_Source_Csv(file_get_contents($aFileInfo['tmp_name']));
			$oTemplate	= Correspondence_Template::createFromORM($oTemplateORM, $oSource);
			$oTemplate->createRun(false, date('Y-m-d H:i:s', $iDeliveryDateTime), null, true);
			$oDA->TransactionRollback();
			
			$aOutput['bSuccess']	= true;
		}
		catch (Exception $e)
		{
			$aOutput['bSuccess']	= false;
			$aOutput['sMessage']	= $e->getMessage();
		}
		
		echo JSON_Services::instance()->encode($aOutput);		
		die;
	}
}

?>
