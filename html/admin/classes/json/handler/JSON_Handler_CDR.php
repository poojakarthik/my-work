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
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> FollowUp::searchFor(null, null, get_object_vars($oFieldsToSort), $aFilter, true)
						);
			}
			else
			{
				$iLimit		= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset	= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aFollowUps	= $this->GetDelinquentFNNs($iLimit, $iOffset, get_object_vars($oFieldsToSort), $aFilter);
				$aResults	= array();
				$iCount		= 0;


				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aFollowUps,
							"iRecordCount"	=>count($aFollowUps)
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



	private function GetDelinquentFNNs($iLimit, $iOffset, $aSortFields, $aFilter, $bCountOnly = false)
	{
		$aFilter['StartDatetime']->mFrom = ConvertUserDateToMySqlDate($aFilter['StartDatetime']->mFrom);
		$aFilter['StartDatetime']->mTo = ConvertUserDateToMySqlDate($aFilter['StartDatetime']->mTo);
		$aFilter['Status'] = CDR_BAD_OWNER;

		$aWhere	= StatementSelect::generateWhere(null, $aFilter);
		$sOrderByClause	=	StatementSelect::generateOrderBy(array(), $aSortFields);

		$sLimitClause	= StatementSelect::generateLimit($iLimit, $iOffset);

		$arrColumns			= Array("FNN"					=>	"FNN",
									"ServiceType"			=>	"ServiceType",
									"Carrier"				=>	"Carrier",
		 							"carrier_label"			=> "Carrier.Name",
									"TotalCost"				=>	"SUM(Cost)",
									"EarliestStartDatetime"	=>	"MIN(StartDatetime)",
									"LatestStartDatetime"	=>	"MAX(StartDatetime)",
									"Count"					=>	"Count(CDR.Id)");

		$selDelinquentCDRs	= new StatementSelect("CDR, Carrier", $arrColumns, $aWhere['sClause']." AND CDR.Carrier = Carrier.Id", $sOrderByClause, $sLimitClause, "FNN, ServiceType, Carrier");
		$mixResult			= $selDelinquentCDRs->Execute($aWhere['aValues']);
		$arrRecordSet	= $selDelinquentCDRs->FetchAll();
		return $bCountOnly?count($arrRecordSet):$arrRecordSet;

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