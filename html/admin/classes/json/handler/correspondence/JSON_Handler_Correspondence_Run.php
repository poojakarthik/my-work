<?php

class JSON_Handler_Correspondence_Run extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function scheduleRunFromSQLTemplate($iCorrespondenceTemplateId, $sScheduleDateTime, $bProcessNow)
	{
		try
		{
			// TODO: Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR)))
			{
				throw new JSON_Handler_Correspondence_Run_Exception('You do not have permission to create Correspdondence Runs.');
			}
			
			// Validate input before proceeding
			$aErrors	= array();
			
			// Delivery date time
			$iDeliveryDateTime	= null;
			if (is_null($sScheduleDateTime))
			{
				// Missing
				$aErrors[]	= 'No delivery date time supplied.';
			}
			else
			{
				// Given, validate the date string (should be Y-m-d H:i:s)
				$iDeliveryDateTime	= strtotime($sScheduleDateTime);
				if ($iDeliveryDateTime === false)
				{
					// Invalid date string
					$aErrors[]	= "Invalid delivery date time supplied ('".$sScheduleDateTime."').";
				}
			}
			
			// Correspondence_Template id
			$oTemplateORM	= null;
			if (is_null($iCorrespondenceTemplateId))
			{
				// Missing
				$aErrors[]	= 'No Correspondence Template Id supplied.';
			}
			else
			{
				try
				{
					// Try and load it
					$oTemplateORM	= Correspondence_Template_ORM::getForId($iCorrespondenceTemplateId);
					
					// All good
					$iCorrespondenceTemplateId	= (int)$iCorrespondenceTemplateId;
				}
				catch (Exception $oEx)
				{
					// Invalid
					$aErrors[]	= "Invalid Correspondence Template Id supplied (".($iCorrespondenceTemplateId == '' ? 'Not supplied' : "'{$iCorrespondenceTemplateId}'").")";
				}
			}
			
			// Process now
			if (is_null($bProcessNow))
			{
				// Missing
				$aErrors[]	= "Please specify whether to process the SQL template now or at time of delivery.";
			}
			
			if (count($aErrors) > 0)
			{
				// Validation errors, return
				return 	array(
							'bSuccess'	=> false,
							'aErrors'	=> $aErrors,
					 		'sMessage'	=> 'There were errors in the form information.'
						);
			}
			
			//$oSource	= new Correspondence_Source_SQL();
			//$oTemplate	= Correspondence_Template::createFromORM($oTemplateORM, $oSource);
			//$oTemplate->createRun($bProcessNow, date('Y-m-d H:i:s', $iDeliveryDateTime), null, true);
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						'bSuccess'	=> true,
						'sDebug'	=> "{$iCorrespondenceTemplateId}, {$iDeliveryDateTime}, ".($bProcessNow ? 'now' : 'deliv.')
					);
		}
		catch (JSON_Handler_Correspondence_Run_Exception $oException)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
					);
		}
	}
}

class JSON_Handler_Correspondence_Run_Exception extends Exception
{
	// No changes
}

?>