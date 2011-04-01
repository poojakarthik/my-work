<?php

class JSON_Handler_Email_Template extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function createTemplate($oDetails)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aErrors = array();
			
			if ($oDetails->name === '')
			{
				$aErrors[] = "Name was not supplied";
			}
			else if (strlen($oDetails->description) > 255)
			{
				$aErrors[] = "Name cannot be more than 255 characters";
			}
			
			if ($oDetails->description === '')
			{
				$aErrors[] = "Description was not supplied";
			}
			else if (strlen($oDetails->description) > 255)
			{
				$aErrors[] = "Description cannot be more than 255 characters";
			}
			
			if ($oDetails->correspondence_template_id === '')
			{
				$aErrors[] = "Correspondence Template was not supplied";
			}
			
			if ($oDetails->datasource_sql == '')
			{
				$aErrors[] = "Data Source SQL was not supplied";
			}
			
			if (($oDetails->customer_group_ids === null) || count($oDetails->customer_group_ids) == 0)
			{
				$aErrors[] = "No Customer Groups supplied";
			}
			
			if (count($aErrors) > 0)
			{
				return array('bSuccess' => false, 'aErrors' => $aErrors);
			}
			
			$oDataAccess = DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false)
			{
				throw new Exception("Failed to start transaction");
			}
			
			try
			{
				// Create email_template
				$oEmailTemplate 				= new Email_Template();
				$oEmailTemplate->name 			= $oDetails->name;
				$oEmailTemplate->description	= $oDetails->description;
				$oEmailTemplate->class_name		= 'Email_Template_Logic_Correspondence';
				$oEmailTemplate->save();
				
				// Create email_template_correspondence
				$oEmailTemplateCorrespondence 								= new Email_Template_Correspondence();
				$oEmailTemplateCorrespondence->email_template_id 			= $oEmailTemplate->id;
				$oEmailTemplateCorrespondence->datasource_sql 				= $oDetails->datasource_sql;
				$oEmailTemplateCorrespondence->correspondence_template_id	= $oDetails->correspondence_template_id;
				$oEmailTemplateCorrespondence->save();
				
				// Create email_template_customer_group(s)
				foreach ($oDetails->customer_group_ids as $iCustomerGroupId)
				{
					$oEmailTemplateCustomerGroup 					= new Email_Template_Customer_Group();
					$oEmailTemplateCustomerGroup->customer_group_id	= $iCustomerGroupId;
					$oEmailTemplateCustomerGroup->email_template_id	= $oEmailTemplate->id;
					$oEmailTemplateCustomerGroup->save();
				}
			}
			catch (Exception $oException)
			{
				if ($oDataAccess->TransactionRollback() === false)
				{
					throw new Exception("Failed to rollback transaction");
				}
				
				throw $oException;
			}
			
			if ($oDataAccess->TransactionCommit() === false)
			{
				throw new Exception("Failed to commit transaction");
			}
			
			return array('bSuccess' => true);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
}

class JSON_Handler_Email_Template_Exception extends Exception
{
	// No changes
}

?>