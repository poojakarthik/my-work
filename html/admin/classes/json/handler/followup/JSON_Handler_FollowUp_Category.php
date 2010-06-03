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
			
			if ($bCountOnly)
			{
				// Count Only
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> FollowUp_Category::searchFor(null, null, get_object_vars($oFieldsToSort), get_object_vars($oFilter), true)
						);
			}
			else
			{
				$iLimit			= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset		= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aCategories	= FollowUp_Category::searchFor($iLimit, $iOffset, get_object_vars($oFieldsToSort), get_object_vars($oFilter));
				$aResults		= array();
				$iCount			= 0;		
				foreach ($aCategories as $oCategory)
				{
					if ($iLimit && $iCount >= $iOffset+$iLimit)
					{
						// Break out, as there's no point in continuing
						break;
					}
					elseif ($iCount >= $iOffset)
					{
						// Add to Result Set
						$aResults[$iCount+$iOffset]	= $oCategory->toStdClass();
					} 
					
					$iCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResults,
							"iRecordCount"	=> $iCount
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
}

class JSON_Handler_FollowUp_Category_Exception extends Exception
{
	// No changes
}

?>