<?php

class JSON_Handler_FollowUp_Closure extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getDataSet($bCountOnly=false, $iLimit=0, $iOffset=0, $oSort=null, $oFilter=null)
	{
		/*
		 * This dataset ajax method does not support sorting 
		 */
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Closure_Exception('You do not have permission to view Follow-Up Closures.');
			}
			
			if ($bCountOnly)
			{
				// Count Only
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> FollowUp_Closure::getCountOfAll()
						);
			}
			else
			{
				$iLimit		= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset	= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aFilter	= get_object_vars($oFilter);
				$aClosures	= FollowUp_Closure::getAll();
				$aResults	= array();
				$iCount		= 0;		
				foreach ($aClosures as $oClosure)
				{
					if ($iLimit && $iCount >= $iOffset+$iLimit)
					{
						// Break out, as there's no point in continuing
						break;
					}
					elseif ($iCount >= $iOffset)
					{
						// Filter data
						$bFilterSuccess	= true;
						
						foreach ($aFilter as $sField => $mValue)
						{
							if (!isset($oClosure->$sField) || ($oClosure->$sField != $mValue))
							{
								$bFilterSuccess	= false;
								break;
							}
						}
						
						// Add to Result Set
						if ($bFilterSuccess)
						{
							$aResults[$iCount+$iOffset]	= $oClosure->toStdClass();
						}
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
		catch (JSON_Handler_FollowUp_Closure_Exception $oException)
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

class JSON_Handler_FollowUp_Closure_Exception extends Exception
{
	// No changes
}

?>