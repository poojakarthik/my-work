<?php

class JSON_Handler_FollowUp_Category extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getDataSet($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Category_Exception('You do not have permission to view Follow-Up Category.');
			}
			
			$aSort		= get_object_vars($oFieldsToSort);
			$aFilter	= get_object_vars($oFilter);
			
			if ($bCountOnly)
			{
				// Count Only
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> FollowUp_Category::searchFor(null, null, $aSort, $aFilter, true)
						);
			}
			else
			{
				$iLimit			= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset		= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aCategories	= FollowUp_Category::searchFor($iLimit, $iOffset, $aSort, $aFilter);
				$aResults		= array();
				$iCount			= 0;		
				foreach ($aCategories as $oCategory)
				{
					// Add to Result Set
					$aResults[$iCount+$iOffset]	= $oCategory->toStdClass();
					$iCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResults,
							"iRecordCount"	=> FollowUp_Category::searchFor(null, null, $aSort, $aFilter, true)
						);
			}
		}
		catch (JSON_Handler_FollowUp_Category_Exception $oException)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error getting the dataset'
					);
		}
	}
	
	public function getForId($iCategoryId)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Category_Exception('You do not have permission to view Follow-Up Categories.');
			}
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						"Success"	=> true,
						"oRecord"	=> FollowUp_Category::getForId($iCategoryId)->toStdClass()
					);
		}
		catch (JSON_Handler_FollowUp_Category_Exception $oException)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error getting the category details'
					);
		}
	}
	
	public function deactivate($iCategoryId)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? 'There was an error accessing the database' : ''
					);
		}
		
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Category_Exception('You do not have permission to deactivate Follow-Up Categories.');
			}
			
			// Change status and save
			$oCategory				= FollowUp_Category::getForId($iCategoryId);
			$oCategory->status_id	= STATUS_INACTIVE;
			$oCategory->save();
			
			$oDataAccess->TransactionCommit();
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						"Success"	=> true
					);
		}
		catch (JSON_Handler_FollowUp_Category_Exception $oException)
		{
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error deactivating the category'
					);
		}
	}
	
	public function save($iCategoryId, $oDetails)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? 'There was an error accessing the database' : ''
					);
		}
		
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Category_Exception('You do not have permission to view Follow-Up Categories.');
			}
			
			if ($iCategoryId)
			{
				$oCategory	= FollowUp_Category::getForId($iCategoryId);
			}
			else
			{
				$oCategory	= new FollowUp_Category();
			}
			
			// Validate input
			$iNameLength	= 128;
			$iDescLength	= 256;
			$aErrors		= array();
			if (is_null($oDetails->name) || strlen($oDetails->name) == 0)
			{
				$aErrors[]	= 'Name missing.';
			}
			else if (strlen($oDetails->description) > $iNameLength)
			{
				$aErrors[]	= "Name is too long, maximum {$iNameLength} characters.";
			}
			
			if (is_null($oDetails->description) || strlen($oDetails->description) == 0)
			{
				$aErrors[]	= 'Description missing.';
			}
			else if (strlen($oDetails->description) > $iDescLength)
			{
				$aErrors[]	= "Description is too long, maximum {$iDescLength} characters.";
			}
			
			if (($oDetails->status_id != STATUS_ACTIVE) && ($oDetails->status_id != STATUS_INACTIVE))
			{
				$aErrors[]	= 'Invalid status';
			}
			
			$oCategory->name		= $oDetails->name;
			$oCategory->description	= $oDetails->description;
			$oCategory->status_id	= $oDetails->status_id;
			$oCategory->save();
			
			$oDataAccess->TransactionCommit();
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						"Success"	=> true,
						"iRecordId"	=> $oCategory->id
					);
		}
		catch (JSON_Handler_FollowUp_Category_Exception $oException)
		{
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error getting the category details'
					);
		}
	}
}

class JSON_Handler_FollowUp_Category_Exception extends Exception
{
	// No changes
}

?>