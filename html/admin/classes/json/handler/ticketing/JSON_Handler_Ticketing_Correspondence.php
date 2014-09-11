<?php

class JSON_Handler_Ticketing_Correspondence extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getForId($iId)
	{
		try
		{
			$oCorrespondence	= Ticketing_Correspondance::getForId($iId);
			$aDetails			= 	array(
										'id'						=> $oCorrespondence->id,
										'ticket_id'					=> $oCorrespondence->ticket_id,
										'summary'					=> $oCorrespondence->summary,
										'details'					=> $oCorrespondence->details,
										'user_id'					=> $oCorrespondence->user_id,
										'contact_id'				=> $oCorrespondence->contact_id,
										'customer_group_email_id'	=> $oCorrespondence->customer_group_email_id,
										'source_id'					=> $oCorrespondence->source_id,
										'delivery_status_id'		=> $oCorrespondence->delivery_status_id,
										'creation_datetime'			=> $oCorrespondence->creation_datetime,
										'delivery_datetime'			=> $oCorrespondence->delivery_datetime
									);
			$aDetails['source_name']			= Ticketing_Correspondance_Source::getForId($aDetails['source_id'])->name;
			$aDetails['delivery_status_name']	= Ticketing_Correspondance_Delivery_Status::getForId($aDetails['delivery_status_id'])->name;
			
			return	array(
						"Success"			=> true,
						"oCorrespondence"	=> $aDetails
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod ? $e->getMessage() : 'There was an error getting the correspondence details'
					);
		}
	}
}

class JSON_Handler_Ticketing_Correspondence_Exception extends Exception
{
	// No changes
}

?>