<?php

class JSON_Handler_CDR extends JSON_Handler
{
	protected	$_JSONDebug	= '';

	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}

	public function getCarrierList()
	{
		$sSql = "SELECT  Carrier AS Carrier, Carrier.Name AS carrier_label
				 FROM CDR join Carrier on (CDR.Carrier = Carrier.Id and CDR.status = 107)
				group by Carrier";
		$oQuery = new Query();
		$mResult = $oQuery->Execute($sSql);
		$aCarriers = array();
		if ($mResult)
		{
			while ($aRow = $mResult->fetch_assoc())
			{
				$aCarriers[]= $aRow;
			}
		}


		return 	array(
							"Success"		=> true,
							"aCarriers"		=> $aCarriers
						);



	}

	public function getDelinquentDataSet($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null, $iSummaryCharacterLimit=30)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Exception('You do not have permission to view Follow-Ups.');
			}

			$aFilter		= get_object_vars($oFilter);
			$iNowSeconds	= time();



			if ($bCountOnly)
			{
				// Count Only
				$x=5;
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=>  CDR::GetDelinquentFNNs(null, null, get_object_vars($oFieldsToSort), $aFilter, true)
						);
			}
			else
			{
				$iLimit		= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset	= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aFollowUps	= CDR::GetDelinquentFNNs($iLimit, $iOffset, get_object_vars($oFieldsToSort), $aFilter);
				$aResults	= array();
				$iCount		= 0;


				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aFollowUps,
							"iRecordCount"	=>CDR::GetDelinquentFNNs(null, null, get_object_vars($oFieldsToSort), $aFilter, true)
						);
			}
		}
		catch (JSON_Handler_CDR_Exception $oException)
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


	public function ExportToCSV($oFieldsToSort=null, $oFilter=null)
	{




		try
		{
			// Proper admin required
			AuthenticatedUser()->PermissionOrDie(array(PERMISSION_PROPER_ADMIN));




			$aColumns			= Array("FNN",
									"ServiceType"	,
									"Carrier"	,
		 							"carrier_label"	,
									"TotalCost"	,
									"EarliestStartDatetime"	,
									"LatestStartDatetime",
									"Count"		,
									"Status"
									);



			// Create File_CSV to do the file creation
			$oFile	= new File_CSV();
			$oFile->setColumns($aColumns);



			// Build list of lines for the file
			$aLines	= array();
			$aData =  CDR::GetDelinquentFNNs(null, null, get_object_vars($oFieldsToSort), get_object_vars($oFilter));

			foreach ($aData as $aRecord)
			{
				$oFile->addRow($aRecord);
			}


					$sPath = FILES_BASE_PATH.'temp/';
		$sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());


		$sFilename	= "DelinquentCDRExport"
		.'.'
		.$sTimeStamp
		.'.csv'
		;
		 $oFile->saveToFile($sPath.$sFilename);

			return 	array(
							"Success"		=> true,
							"FileName"		=>$sFilename

						);
		}
		catch (Exception $e)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			echo $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
		}


	}






	 function GetDelinquentCDRs($strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType)
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

/*		$strStartDate	= ConvertUserDateToMySqlDate(DBO()->Delinquents->StartDate->Value);
		$strEndDate		= ConvertUserDateToMySqlDate(DBO()->Delinquents->EndDate->Value);
		$strFNN			= DBO()->Delinquents->FNN->Value;
		$intCarrier		= DBO()->Delinquents->Carrier->Value;
		$intServiceType	= DBO()->Delinquents->ServiceType->Value;*/

		$arrReturnData = CDR::GetDelinquentCDRs($strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType);
		//$arrReturnData['ServiceSelectorHtml']	= $strServiceSelectorHtml;
		return 	array(
							"Success"		=> true,
							"aRecords"		=> $arrReturnData

						);

	}

	function bulkWriteOffForFNN($strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType)
	{
		$CDRData = CDR::GetDelinquentCDRs($strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType);
		$aCDRIds = array();
		foreach ($CDRData['CDRs'] as $aCDR)
		{
			$aCDRIds[] = $aCDR['Id'];
		}

		return $this->writeOffDelinquentCDRs($aCDRIds);
	}


	// writeOffDelinquentCDRs: Given id (or array of ids) writes off each, will fail if any aren't delinquent.
	public function writeOffDelinquentCDRs($mCDRId)
	{
		$bIsGod	= Employee::getForId(Flex::getUserId());
		try
		{
			// Create array of ids
			if (!is_array($mCDRId))
			{
				$aCDRIds	= array($mCDRId);
			}
			else
			{
				$aCDRIds	= $mCDRId;
			}

			// Write off each cdr
			foreach ($aCDRIds as $iId)
			{
				$oCDR	= CDR::getForId($iId);
				$oCDR->writeOff();
			}

			return	array(
						'bSuccess'	=> true,
						'sDebug'	=> ($bIsGod ? $this->_JSONDebug : '')
					);
		}
		catch (Exception $oException)
		{
			return	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bIsGod ? $oException->getMessage() : 'There was an error accessing the database, please contact YBS for assistance'),
						'sDebug'	=> ($bIsGod ? $this->_JSONDebug : '')
					);
		}
	}
}

class JSON_Handler_CDR_Exception extends Exception
{
	// No changes
}

?>