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
		try
		{

			$sSql = "SELECT  Carrier AS Carrier, Carrier.Name AS carrier_label
					 FROM CDR join Carrier on (CDR.Carrier = Carrier.Id )
					 WHERE CDR.status = ".CDR_DELINQUENT_WRITTEN_OFF." OR CDR.status = ".CDR_BAD_OWNER."
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
		catch (Exception $e)
		{
					return 	array(
							"Success"		=> false,
							"sMessage"		=> $e->__toString()
						);
		}


	}

	public function getStatusList()
	{
		try
		{

			$aStatusList = array(CDR_BAD_OWNER => GetConstantDescription(CDR_BAD_OWNER, "CDR"),
								CDR_DELINQUENT_WRITTEN_OFF =>GetConstantDescription(CDR_DELINQUENT_WRITTEN_OFF, "CDR"),
								self::SHOW_BOTH	=> "Both"
								);

			return 	array(
								"Success"		=> true,
								"aData"		=> $aStatusList
							);
		}
		catch (Exception $e)
		{
					return 	array(
							"Success"		=> false,
							"sMessage"		=> $e->__toString()
						);
		}
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


	public function ExportToCSV($oFilter)
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
			//$aData =CDR::GetStatusInfoForCDRs($aCDRIds);
				$aFilter		= get_object_vars($oFilter);
			$aData = CDR::GetDelinquentCDRsPaginated(null, null, array(), $aFilter, false);
			foreach ($aData as $aRecord)
			{
				$oFile->addRow($aRecord);
			}


			$sPath = FILES_BASE_PATH.'temp/';
			$sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());


			$sFilename	= "DelinquentCDRExport".'.'.$sTimeStamp.'.csv';

			$oFile->saveToFile($sPath.$sFilename);

			return 	array(
							"Success"		=> true,
							"FileName"		=>$sFilename

						);
		}
		catch (Exception $e)
		{
			return 	array(
					"Success"		=> false,
					"sMessage"		=> $e->__toString()
				);
		}
	}

function getPossibleOwnersForFNN($strFNN, $intServiceType)
{

		try
		{

			$aServices = CDR::getPossibleOwnersForFNN($strFNN, $intServiceType);


			return 	array(
								"Success"		=> true,
								"aData"		=> $aServices
							);
		}
		catch (Exception $e)
		{
					return 	array(
							"Success"		=> false,
							"sMessage"		=> $e->__toString()
						);
		}


}

function GetDelinquentCDRsPaginated($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null, $iSummaryCharacterLimit=30)
{

		try
		{

			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_CDR_Exception('You do not have permission to move delinquent CDRs.');
			}

			$aFilter		= get_object_vars($oFilter);

			if ($bCountOnly)
			{

				return 	array(
							"Success"		=> true,
							"iRecordCount"	=>  CDR::GetDelinquentCDRsPaginated(null, null, get_object_vars($oFieldsToSort), $aFilter, true)
						);
			}
			else
			{
				$iLimit		= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset	= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aFNN		= CDR::GetDelinquentCDRsPaginated($iLimit, $iOffset, get_object_vars($oFieldsToSort), $aFilter);
				$aResults	= array();
				$iCount		= 0;


				return 	array(
							"Success"		=> true,
							"aRecords"		=>$aFNN,
							"iRecordCount"	=>CDR::GetDelinquentCDRsPaginated(null, null, get_object_vars($oFieldsToSort), $aFilter, true)
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
	function GetDelinquentCDRs($strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType, $iStatus = CDR_BAD_OWNER)
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		try
		{

			$arrReturnData = CDR::GetDelinquentCDRs($strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType, $iStatus);

			return 	array(
								"Success"		=> true,
								"aRecords"		=> $arrReturnData

							);
		}
		catch (Exception $e)
		{
					return 	array(
							"Success"		=> false,
							"sMessage"		=> $e->__toString()
						);
		}

	}

	function bulkWriteOffForFNN($strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType)
	{
		try
		{

			$aCDRIDs = CDR::GetDelinquentCDRIDs($strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType);
		}
		catch (Exception $e)
		{
					return 	array(
							"Success"		=> false,
							"sMessage"		=> $e->__toString()
						);


		}

		return $this->writeOffDelinquentCDRs($aCDRIDs);
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

			//$aData =CDR::GetStatusInfoForCDRs($aCDRIds);

			return	array(
						'Success'	=> true,
						'aData'		=> $aCDRIds,
						'sStatus'	=> GetConstantDescription(CDR_DELINQUENT_WRITTEN_OFF, "CDR")
					);
		}
		catch (Exception $oException)
		{
			return	array(
						'Success'	=> false,
						'sMessage'	=> $oException->__toString()
					);
		}
	}




	function AssignCDRsToServices($strFNN	, $intCarrier, $intServiceType, $arrCDRs)
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);


		try
		{

		TransactionStart();
		$arrSuccessfulCDRs = CDR::assignCDRsToService($strFNN, $intCarrier, $intServiceType, $arrCDRs);
		// Everything worked out
			TransactionCommit();
			return	array(
						'Success'	=> true,
						'aData'		=>$arrSuccessfulCDRs
					);
		}
		catch (Exception $e)
		{
			TransactionRollback();
			return	array(
						'Success'	=> false,
						'sMessage'	=> $e->__toString()
					);

		}



	}

function GetStatusInfoForCDRs($aCDRIDs, $bFilterOnlyDelinquents = false)
{

	try
	{

		$aData =CDR::GetStatusInfoForCDRs($aCDRIDs);
		$aResult = array();
		if ($bFilterOnlyDelinquents)
		{
			foreach($aData as $aRecord)
			{
				$aRecord['Status'] == CDR_BAD_OWNER?$aResult[] = $aRecord:null;

			}
		}
		else
		{
			$aResult = $aData;
		}

		return	array(
							'Success'	=> true,
							'aData'		=>$aResult
						);

	}
	catch(Exception $e)
	{
			return	array(
						'Success'	=> false,
						'sMessage'	=>$e->__toString()
					);


	}
}



function BulkAssignCDRsToServices ($strFNN, $intCarrier, $intServiceType,  $strStartDate,$strEndDate, $iServiceId)
{


	$aCDRs = CDR::GetDelinquentCDRIDs($strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType);
$aServiceInfo = array();
	try
	{

		TransactionStart();
		foreach ($aCDRs as $iCDR)
		{
			$oCDR = new stdClass();
			$oCDR->Id = $iCDR;
			$oCDR->Service = $iServiceId;
			$aServiceInfo = CDR::assignCDRsToService($strFNN, $intCarrier, $intServiceType, array($oCDR));

		}


		TransactionCommit();
			return	array(
						'Success'	=> true,
						'aData'		=>$aCDRs,
						'aServiceInfo' => $aServiceInfo

					);

	}
	catch(Exception $e)
	{

		TransactionRollback();
		return	array(
								'Success'	=> false,
								'sMessage'	=> $e->__toString()
							);

	}



}


}

class JSON_Handler_CDR_Exception extends Exception
{
	// No changes
}

?>