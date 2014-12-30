<?php

class JSON_Handler_FollowUp_Reassign_Reason extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getDataSet($bCountOnly=false, $iLimit=0, $iOffset=0, $oSort=null, $oFilter=null)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Reassign_Reason_Exception('You do not have permission to view Follow-Up Reassign Reasons.');
			}

			$aSort		= is_object($oSort) ? get_object_vars($oSort) : $aSort;
			$aFilter	= get_object_vars($oFilter);

			if ($bCountOnly)
			{
				// Count Only
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> FollowUp_Reassign_Reason::searchFor(null, null, $aSort, $aFilter, true)
						);
			}
			else
			{
				$iLimit		= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset	= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aReasons	= FollowUp_Reassign_Reason::searchFor($iLimit, $iOffset, $aSort, $aFilter);
				$aResults	= array();
				$iCount		= 0;
				foreach ($aReasons as $oReason)
				{
					// Add to Result Set
					$aResults[$iCount+$iOffset]	= $oReason->toStdClass();
					$iCount++;
				}

				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResults,
							"iRecordCount"	=> FollowUp_Reassign_Reason::searchFor(null, null, $aSort, $aFilter, true)
						);
			}
		}
		catch (JSON_Handler_FollowUp_Reassign_Reason_Exception $oException)
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : 'There was an error getting the dataset'
					);
		}
	}

	public function getForId($iId)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Reassign_Reason_Exception('You do not have permission to view Follow-Up Reassign Reasons.');
			}

			// If no exceptions were thrown, then everything worked
			return 	array(
						"Success"	=> true,
						"oRecord"	=> FollowUp_Reassign_Reason::getForId($iId)->toStdClass()
					);
		}
		catch (JSON_Handler_FollowUp_Reassign_Reason_Exception $oException)
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : 'There was an error getting the reason details'
					);
		}
	}

	public function deactivate($iId)
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
				throw new JSON_Handler_FollowUp_Reassign_Reason_Exception('You do not have permission to deactivate Follow-Up Reassign Reasons.');
			}

			// Change status and save
			$oReason			= FollowUp_Reassign_Reason::getForId($iId);
			$oReason->status_id	= STATUS_INACTIVE;
			$oReason->save();

			$oDataAccess->TransactionCommit();

			// If no exceptions were thrown, then everything worked
			return 	array(
						"Success"	=> true
					);
		}
		catch (JSON_Handler_FollowUp_Reassign_Reason_Exception $oException)
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : 'There was an error deactivating the reason'
					);
		}
	}

	public function save($iId, $oDetails)
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
				throw new JSON_Handler_FollowUp_Reassign_Reason_Exception('You do not have permission to view Follow-Up Reassign Reasons.');
			}

			if ($iId)
			{
				$oReason	= FollowUp_Reassign_Reason::getForId($iId);
			}
			else
			{
				$oReason	= new FollowUp_Reassign_Reason();
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

			$oReason->name			= $oDetails->name;
			$oReason->description	= $oDetails->description;
			$oReason->status_id		= $oDetails->status_id;
			$oReason->save();

			$oDataAccess->TransactionCommit();

			// If no exceptions were thrown, then everything worked
			return 	array(
						"Success"	=> true,
						"iRecordId"	=> $oReason->id
					);
		}
		catch (JSON_Handler_FollowUp_Reassign_Reason_Exception $oException)
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : 'There was an error getting the reason details'
					);
		}
	}
}

class JSON_Handler_FollowUp_Reassign_Reason_Exception extends Exception
{
	// No changes
}

?>