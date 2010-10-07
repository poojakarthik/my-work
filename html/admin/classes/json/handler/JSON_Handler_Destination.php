<?php

class JSON_Handler_Destination extends JSON_Handler
{
	const	DESTINATION_UNKNOWN	= -1;
	
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getDestinationContexts()
	{
		$bIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aORMs					= Destination_Context::getAll();
			$aDestinationContexts	= array();
			foreach ($aORMs as $iDestinationContextId=>$oDestinationContext)
			{
				$aDestinationContexts[]	= $oDestinationContext->toStdClass();
			}
			
			return	array(
						'bSuccess'	=> true,
						'Success'	=> true,
						'aRecords'	=> $aDestinationContexts,
						'sDebug'	=> $bIsGod ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $oException)
		{
			$sMessage	= ($bIsGod ? $oException->getMessage() : 'An error occured accessing the database');
			return	array(
						'bSuccess'	=> false,
						'Success'	=> false,
						'sMessage'	=> $sMessage,
						'Message'	=> $sMessage,
						'sDebug'	=> $bIsGod ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function ImportTranslations($oDestinations, $iCarrierId)
	{
		$bIsGod		= Employee::getForId(Flex::getUserId())->isGod();
		$iCarrierId	= (int)$iCarrierId;
		
		try
		{
			//throw new Exception(print_r((array)$oDestinations, true));
			
			if (!DataAccess::getDataAccess()->TransactionStart())
			{
				throw new Exception("Unable to start a transaction");
			}
			
			try
			{
				$iNewTranslations		= 0;
				$iUpdatedTranslations	= 0;
				$iDeletedTranslations	= 0;
				$iIgnoredTranslations	= 0;
				
				$aDestinations	= (array)$oDestinations;
				foreach ($aDestinations as $mCarrierCode=>$oCarrierDestination)
				{
					$iDestinationCode	= (int)$oCarrierDestination->iDestinationCode;
					
					$oCDRCallTypeTranslation = CDR_Call_Type_Translation::getForCarrierCode($iCarrierId, $mCarrierCode);
					
					if ($iDestinationCode === self::DESTINATION_UNKNOWN)
					{
						// Unknown Destination -- remove any existing translations
						if ($oCDRCallTypeTranslation)
						{
							$iDeletedTranslations++;
							$oCDRCallTypeTranslation->delete();
						}
						else
						{
							$iIgnoredTranslations++;
						}
					}
					else
					{
						// Modify existing records or create new ones
						if (!$oCDRCallTypeTranslation)
						{
							$iNewTranslations++;
							
							$oCDRCallTypeTranslation	= new CDR_Call_Type_Translation();
							
							$oCDRCallTypeTranslation->carrier_id	= $iCarrierId;
							$oCDRCallTypeTranslation->carrier_code	= $mCarrierCode;
						}
						else
						{
							$iUpdatedTranslations++;
						}
						
						$oCDRCallTypeTranslation->code			= $iDestinationCode;
						$oCDRCallTypeTranslation->description	= $oCarrierDestination->sDescription;
						
						$oCDRCallTypeTranslation->save();
					}
				}
				
				Log::getLog()->log("Translations Created	: {$iNewTranslations}");
				Log::getLog()->log("Translations Updated	: {$iUpdatedTranslations}");
				Log::getLog()->log("Translations Deleted	: {$iDeletedTranslations}");
				Log::getLog()->log("Translations Ignored	: {$iIgnoredTranslations}");
				
				//throw new Exception("TEST MODE");
			}
			catch (Exception $oException)
			{
				DataAccess::getDataAccess()->TransactionRollback();
				throw $oException;
			}
			/**/
			if (!DataAccess::getDataAccess()->TransactionCommit())
			{
				throw new Exception("Unable to commit the transaction! Data should be rolled back automatically, though data corruption or auto-commit can occur in rare circumstances.");
			}
			/**/
			return	array(
						'bSuccess'	=> true,
						'Success'	=> true,
						'sMessage'	=> $sMessage,
						'Message'	=> $sMessage,
						'sDebug'	=> $bIsGod ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $oException)
		{
			$sMessage	= ($bIsGod ? $oException->getMessage() : 'An error occured accessing the database');
			return	array(
						'bSuccess'	=> false,
						'Success'	=> false,
						'sMessage'	=> $sMessage,
						'Message'	=> $sMessage,
						'sDebug'	=> $bIsGod ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function SearchDestinationContext($bCountOnly=false, $iLimit=0, $iOffset=0, $oSort=null, $oFilter=null)
	{
		$bIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			//
			// NOTE: 	This is designed to be used by a Control_Field_Text_AJAX object on the client side
			//			As a result, the count only, offset and sorting data are ignored.
			//
			
			// Extract filter data
			$sSearchTerm			= $oFilter->search_term;
			$iDestinationContextId	= (isset($oFilter->destination_context_id)) ? (int)$oFilter->destination_context_id : null;
			
			// Create array of results
			$aMatches	= Destination::getForDescriptionLike($sSearchTerm, $iDestinationContextId);
			$aResults	= array();
			reset($aMatches);
			while (($iLimit <= 0 || count($aResults) < $iLimit) && current($aMatches) !== false)
			{
				$oDestination	= current($aMatches);
				
				$oDestination->sDescriptiveLabel	= "{$oDestination->Description} ({$oDestination->Code})";
				
				$aResults[]	= $oDestination;
				
				next($aMatches);
			}
			
			return	array(
						'bSuccess'		=> true,
						'Success'		=> true,
						'aRecords'		=> $aResults,
						'iRecordCount'	=> count($aResults),
						/*'aRecords'		=> $aMatches,
						'iRecordCount'	=> count($aMatches),*/
						'sDebug'		=> $bIsGod ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $oException)
		{
			$sMessage	= ($bIsGod ? $oException->getMessage() : 'An error occured accessing the database');
			return	array(
						'bSuccess'	=> false,
						'Success'	=> false,
						'sMessage'	=> $sMessage,
						'Message'	=> $sMessage,
						'sDebug'	=> $bIsGod ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function MatchDestinationsFromCSV($sDestinationsCSV, $aIgnoreWords=array())
	{
		try
		{
			return	array(
						'Success'		=> true,
						'aResults'		=> self::matchDestinationsCSV($sDestinationsCSV, $aIgnoreWords)
					);
		}
		catch (Exception $oException)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			$sMessage	= $bUserIsGod ? $oException->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.';
			return 	array(
						'Success'	=> false,
						'sMessage'	=> $sMessage,
						'Message'	=> $sMessage
					);
		}
	}
	
	public function MatchDestinationsFromArray($aDestinations, $aIgnoreWords=array())
	{
		try
		{
			return	array(
						'Success'		=> true,
						'aResults'		=> self::matchDestinations($aDestinations, $aIgnoreWords)
					);
		}
		catch (Exception $oException)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			$sMessage	= $bUserIsGod ? $oException->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.';
			return 	array(
						'Success'	=> false,
						'sMessage'	=> $sMessage,
						'Message'	=> $sMessage
					);
		}
	}
	
	public static function matchDestinationsCSV($sDestinationsCSV, $aIgnoreWords=array())
	{
		try
		{
			// Parse CSV
			$oImportFile	= new File_CSV(',', '"', '\\', array('carrier_code', 'description'));
			$oImportFile->importFileAsString($sDestinationsCSV);
			
			$aDestinations	= array();
			foreach ($oImportFile as $aRow)
			{
				if ($aRow['carrier_code'] && $aRow['description'])
				{
					$aDestinations[$aRow['carrier_code']]	= $aRow['description'];
				}
			}
			
			return	array(
						'Success'		=> true,
						'aResults'		=> self::matchDestinations($aDestinations, $aIgnoreWords)
					);
		}
		catch (Exception $oException)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			$sMessage	= $bUserIsGod ? $oException->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.';
			return 	array(
						'Success'	=> false,
						'sMessage'	=> $sMessage,
						'Message'	=> $sMessage
					);
		}
	}
	
	public static function matchDestinations($aDestinations, $aIgnoreWords=array())
	{
		$aResults	= array();
		foreach ($aDestinations as $mCarrierCode=>$sCarrierDescription)
		{
			$aResults[$mCarrierCode]	= array(
					'mCarrierCode'			=> $mCarrierCode,
					'sCarrierDescription'	=> $sCarrierDescription,
					'aMatches'				=> Destination::getForDescriptionWords($sCarrierDescription, $aIgnoreWords)
				);
		}
		return $aResults;
	}
}

?>