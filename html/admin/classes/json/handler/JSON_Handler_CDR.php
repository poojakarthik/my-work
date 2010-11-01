<?php

class JSON_Handler_CDR extends JSON_Handler
{
	protected	$_JSONDebug	= '';

	const SHOW_BOTH = -1;


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

	public function getStatusList()
	{


		$aStatusList = array(CDR_BAD_OWNER => GetConstantDescription(CDR_BAD_OWNER, "CDR"),
							CDR_DELINQUENT_WRITTEN_OFF =>GetConstantDescription(CDR_DELINQUENT_WRITTEN_OFF, "CDR"),
							self::SHOW_BOTH	=> "Show delinquent and written off CDRs"
							);


		return 	array(
							"Success"		=> true,
							"aData"		=> $aStatusList
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
				$aFNN		= CDR::GetDelinquentFNNs($iLimit, $iOffset, get_object_vars($oFieldsToSort), $aFilter);
				$aResults	= array();
				$iCount		= 0;


				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aFNN,
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


	public function ExportToCSV($aCDRIds)//$strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType,$iStatus)
	{




		try
		{
			// Proper admin required
			AuthenticatedUser()->PermissionOrDie(array(PERMISSION_PROPER_ADMIN));




			$aColumns			= Array("Id",
									"Time",
									"Cost",
		 							"Status"
									);



			// Create File_CSV to do the file creation
			$oFile	= new File_CSV();
			$oFile->setColumns($aColumns);



			// Build list of lines for the file
			$aLines	= array();
			//$aData =  CDR::GetDelinquentFNNs(null, null, get_object_vars($oFieldsToSort), get_object_vars($oFilter));
			$aData =CDR::GetStatusInfoForCDRs($aCDRIds);
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






	 function GetDelinquentCDRs($strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType, $iStatus = CDR_BAD_OWNER)
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

/*		$strStartDate	= ConvertUserDateToMySqlDate(DBO()->Delinquents->StartDate->Value);
		$strEndDate		= ConvertUserDateToMySqlDate(DBO()->Delinquents->EndDate->Value);
		$strFNN			= DBO()->Delinquents->FNN->Value;
		$intCarrier		= DBO()->Delinquents->Carrier->Value;
		$intServiceType	= DBO()->Delinquents->ServiceType->Value;*/

		$arrReturnData = CDR::GetDelinquentCDRs($strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType, $iStatus);
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

	function writeOffCDRs($aCDRs)
	{

		return $this->writeOffDelinquentCDRs($aCDRs);

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

			$aData =CDR::GetStatusInfoForCDRs($aCDRIds);



			return	array(
						'bSuccess'	=> true,
						'sDebug'	=> ($bIsGod ? $this->_JSONDebug : ''),
						'aData'		=> $aData
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


//------------------------------------------------------------------------//
	// AssignCDRsToServices
	//------------------------------------------------------------------------//
	/**
	 * AssignCDRsToServices()
	 *
	 * Assigns the passed delinquent CDRs to their respective Services
	 *
	 * Assigns the passed delinquent CDRs to their respective Services
	 * It assumes the following data is passed:
	 * 		DBO()->Delinquents->FNN			The FNN of the Delinquent CDRs
	 * 		DBO()->Delinquents->Carrier		The Carrier of the Delinquent CDRs
	 * 		DBO()->Delinquents->ServiceType	The ServiceType of the Delinquent CDRs
	 * 		DBO()->Delinquents->CDRs		array of objects of the form:
	 * 											arrCDRs[i]->Id		: CDR's Id
	 * 											arrCDRs[i]->Service	: Id of the Service to assign the CDR to
	 * 											arrCDRs[i]->Record	: The record number that the CDR is assigned in the table on the Delinquent CDRs webpage
	 *
	 * @return		void
	 * @method		AssignCDRsToServices
	 */
	function AssignCDRsToServices($strFNN	, $intCarrier, $intServiceType, $arrCDRs)
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		/*$strFNN			= DBO()->Delinquents->FNN->Value;
		$intCarrier		= DBO()->Delinquents->Carrier->Value;
		$intServiceType	= DBO()->Delinquents->ServiceType->Value;
		$arrCDRs		= DBO()->Delinquents->CDRs->Value;*/
		try
		{
		TransactionStart();
		$arrSuccessfulCDRs = CDR::assignCDRsToService($strFNN, $intCarrier, $intServiceType, $arrCDRs);
		}
		catch (Exception $e)
		{
			TransactionRollback();
			return	array(
						'bSuccess'	=> false,
						'sMessage'	=> 'issues',
						'aData'		=>$arrSuccessfulCDRs
					);

		}

			// Everything worked out
			TransactionCommit();
			return	array(
						'bSuccess'	=> true,
						'sMessage'	=> "",
						'aData'		=>$arrSuccessfulCDRs
					);

	}


function BulkAssignCDRsToServices ($strFNN, $intCarrier, $intServiceType,  $strStartDate,$strEndDate, $iServiceId)
{

	$aCDRs = CDR::GetDelinquentCDRs($strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType);
	$aCDRIDs = array();
	try
	{
	TransactionStart();
	foreach ($aCDRs['CDRs'] as $aCDR)
	{
		$oCDR = new stdClass();
		$oCDR->Id = $aCDR['Id'];
		$oCDR->Service = $iServiceId;
		$arrSuccessfulCDRs = CDR::assignCDRsToService($strFNN, $intCarrier, $intServiceType, array($oCDR));
		$aCDRIDs[] = $aCDR['Id'];
	}

	$aData =CDR::GetStatusInfoForCDRs($aCDRIDs);

	}
	catch(Exception $e)
	{

		TransactionRollback();


	}

	TransactionCommit();
			return	array(
						'bSuccess'	=> true,
						'sMessage'	=> "",
						'aData'		=>$aData
					);

}


}

class JSON_Handler_CDR_Exception extends Exception
{
	// No changes
}

?>