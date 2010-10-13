<?php
/**
 * Rate
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Rate
 */
class Rate extends ORM_Cached
{
	const	RATING_PRECISION	= 2;	// Round to the nearest whole cent
	
	const	RATE_SEARCH_LOGGING		= true;
	const	RATE_ALGORITHM_LOGGING	= true;
	
	protected 			$_strTableName			= "Rate";
	protected static	$_strStaticTableName	= "Rate";
	
	protected static function _logRateSearch($sMessage, $bNewLine=true)
	{
		if (self::RATE_SEARCH_LOGGING)
		{
			Log::getLog()->log($sMessage, $bNewLine);
		}
	}
	
	protected static function _logRateAlgorithm($sMessage, $bNewLine=true)
	{
		if (self::RATE_ALGORITHM_LOGGING)
		{
			Log::getLog()->log($sMessage, $bNewLine);
		}
	}
	
	public static function getForCDR($mCDR)
	{
		$oCDR		= CDR::getForId(ORM::extractId($mCDR));
		
		self::_logRateSearch("Finding a Rate for CDR {$oCDR->Id}...");
		
		// Is this a Fleet call?
		$bFleet					= false;
		$oDestinationService	= Service::getCurrentForFNN($oCDR->Destination, $oCDR->StartDatetime);
		if ($oDestinationService && $oDestinationService->Account == $oCDR->Account)
		{
			self::_logRateSearch("Destination Service {$oCDR->Destination} is on the same Flex Account {$oCDR->Account} - searching for Fleet Rate on Destination...");
			// Same Account -- try to find a Fleet Rate on the Destination Service (must be a perfect match)
			if ($oDestinationFleetRate = Rate::getForServiceAndDefinition($oDestinationService, null, $oCDR->StartDatetime, null, true))
			{
				// Fleet Rate found on Destination Service
				self::_logRateSearch("Fleet Rate {$oDestinationFleetRate->Id} found on Destination");
				$bFleet	= true;
			}
		}
		
		// Search for a Rate (try Fleet first, if eligible)
		if ($bFleet && null !== ($oRate = Rate::getForServiceAndDefinition($oCDR->Service, $oCDR->RecordType, $oCDR->StartDatetime, $oCDR->DestinationCode, true)))
		{
			self::_logRateSearch("Fleet Rate {$oRate->Id} found on Service");
		}
		elseif (null !== ($oRate = Rate::getForServiceAndDefinition($oCDR->Service, $oCDR->RecordType, $oCDR->StartDatetime, $oCDR->DestinationCode, false, false)))
		{
			// Allow "closest match" for non-Fleet Rates
			self::_logRateSearch("Normal Rate {$oRate->Id} found on Service");
		}
		else
		{
			self::_logRateSearch("No Rate found on Service!");
		}
		return $oRate;
	}
	
	public static function getForServiceAndDefinition($mService, $mRecordType, $mDatetime, $mDestination=null, $bFleet=false, $bPerfectMatch=true)
	{
		$oService		= Service::getForId(ORM::extractId($mService));
		$oRecordType	= ($mRecordType !== null) ? Record_Type::getForId(ORM::extractId($mRecordType)) : null;
		$oDestination	= ($mDestination) ? Destination::getForId(ORM::extractId($mDestination)) : null;
		
		$iDatetime	= (is_string($mDatetime)) ? strtotime($mDatetime) : (int)$mDatetime;
		$sDay		= date('l', $iDatetime);
		
	 	$aWhere							= array();
	 	$aWhere['service_id']			= $oService->Id;
	 	$aWhere['effective_datetime']	= date('Y-m-d H:i:s', $iDatetime);
		$aWhere['record_type_id']		= ($oRecordType === null) ? null : $oRecordType->Id;
		$aWhere['destination_code']		= ($oDestination) ? $oDestination->Code : 0;
		$aWhere['is_fleet']				= (int)!!$bFleet;
		$aWhere['use_perfect_match']	= (int)!!$bPerfectMatch;
		$aWhere['is_fleet_check_only']	= ($oRecordType === null) ? 1 : 0;
		
		self::_logRateSearch("Search for ", false);
		self::_logRateSearch(($bFleet) ? 'Fleet' : 'Standard', false);
		self::_logRateSearch(" Rate for Service {$aWhere['service_id']} ({$oService->FNN}) on {$aWhere['effective_datetime']} ({$sDay}) for ", false);
		if ($oRecordType === null)
		{
			self::_logRateSearch("any Call Type and date/time restrictions (Destination Fleet eligibility)");
		}
		elseif ($oDestination === null)
		{
			self::_logRateSearch("Call Type {$oRecordType->Name} ({$oRecordType->Id})");
		}
		else
		{
			self::_logRateSearch("Call Type {$oRecordType->Name}: {$oDestination->Description} ({$oRecordType->Id}:{$oDestination->Code})");
		}
		
		$selForServiceAndDefinition	= self::_preparedStatement('selForServiceAndDefinition');
		if ($selForServiceAndDefinition->Execute($aWhere) === false)
		{
			throw new Exception($selForServiceAndDefinition->Error());
		}
		
		// NOTE: This query isn't limited to one result, though it is ordered so that the first result is the best match
		if ($aRate = $selForServiceAndDefinition->Fetch())
		{
			return new Rate($aRate);
		}
		else
		{
			return null;
		}
	}
	
	public static function roundToRatingStandard($fAmountInDollars, $iDecimalPlaces=self::RATING_PRECISION)
	{
		// Round up to the specified precision (and then round() to make sure there aren't float precision errors)
		$iFraction	= pow(10, $iDecimalPlaces);
		return round(ceil($fAmountInDollars * $iFraction) / $iFraction, $iDecimalPlaces);
	}
	
	public function calculateChargeForCDR($mCDR)
	{
		$oCDR	= CDR::getForId(ORM::extractId($mCDR));
		return $this->calculateCharge($oCDR->Units, $oCDR->Cost, $oCDR->StartDatetime, ($oCDR->EndDatetime) ? $oCDR->EndDatetime : $oCDR->StartDatetime);
	}
	
	public function calculateCharge($iUnits, $fCost, $sStartDatetime, $sEndDatetime)
	{
		$this->_logRateAlgorithm("Rating {$iUnits} Units at \$".number_format($fCost, '4', '.', '')." from {$sStartDatetime} to {$sEndDatetime}");
		
		$fCost			= (float)$fCost;
		$iUnits			= (int)$iUnits;
		$bPassthrough	= !!$this->PassThrough;
		$bProrate		= !!$this->Prorate;
		$fMinimumCharge	= (float)$this->StdMinCharge;
		
		$iCapUnits		= (float)$this->CapUnits;	// Maximum Units for Standard Rate
		$fCapCost		= (float)$this->CapCost;	// Maximum Charge for Standard Rate
		$iCapUsage		= (float)$this->CapUsage;	// Minimum Units for Excess Rate
		$fCapLimit		= (float)$this->CapCost;	// Minimum Charge for Excess Rate
		
		$aStandardRate	= array(
									'fRatePerUnitBlock'				=> (float)$this->StdRatePerUnit,
									'fFlagfall'						=> (float)$this->StdFlagfall,
									'fMarkupPercentage'				=> (float)$this->StdPercentage,
									'fMarkupDollarsPerUnitBlock'	=> (float)$this->StdMarkup,
									'iUnitBlockSize'				=> (int)$this->StdUnits
								);
		$aExcessRate	= array(
									'fRatePerUnitBlock'				=> (float)$this->ExsRatePerUnit,
									'fFlagfall'						=> (float)$this->ExsFlagfall,
									'fMarkupPercentage'				=> (float)$this->ExsPercentage,
									'fMarkupDollarsPerUnitBlock'	=> (float)$this->ExsMarkup,
									'iUnitBlockSize'				=> (int)$this->ExsUnits
								);
		
		// Calculate
		//--------------------------------------------------------------------//
		$fCharge	= 0.0;
		if ($bPassthrough)
		{
			// PASSTHROUGH
			// Passthroughs have a much simpler calculation
			$fCharge	= max($fCost + $aStandardRate['fFlagfall'], $fMinimumCharge);
			$this->_logRateAlgorithm("PASSTHROUGH: \$".number_format($fCharge, '4', '.', '')."\t= max({$fCost} + {$aStandardRate['fFlagfall']}, {$fMinimumCharge})");
		}
		else
		{
			// STANDARD RATE
			// Apply Standard Rate
			$fStandardCharge	= $this->_calculateChargeStage($iUnits, $fCost, $aStandardRate);
			$fCharge			= $fStandardCharge;
			
			$this->_logRateAlgorithm("STANDARD CHARGE: \$".number_format($fCharge, '4', '.', ''));
			
			// CAPPING
			// Apply Capping (unit-based capping takes priority over charge-based capping)
			if ($iCapUnits > 0)
			{
				if ($iUnits > $iCapUnits)
				{
					// Reapply Standard Rate but capped to $iCapUnits Units
					$fCharge	= $this->_calculateChargeStage($iCapUnits, $fCost, $aStandardRate);
					$this->_logRateAlgorithm("STANDARD CHARGE UNIT CAPPED: \$".number_format($fCharge, '4', '.', '')." @ {$iCapUnits} Units");
				}
			}
			elseif ($fCapCost > 0.0)
			{
				if ($fCharge > $fCapCost)
				{
					// Limit the Standard Rate to our dollar Cap
					$fCharge	= $fCapCost;
					$this->_logRateAlgorithm("STANDARD CHARGE DOLLAR CAPPED: \$".number_format($fCharge, '4', '.', '')." @ \$".number_format($fCapCost, '4', '.', ''));
				}
			}
			
			// EXCESS RATE
			// Apply Excess Rate
			if ($iCapUsage && $iUnits > $iCapUsage)
			{
				// Apply the Excess Rate to any usage over $iCapUsage and add to our Charge
				$iExcessUnits	= $iUnits - $iCapUsage;
				$fExcessCharge	= $this->_calculateChargeStage($iExcessUnits, $fCost, $aExcessRate);
				$fCharge		+= $fExcessCharge;
				$this->_logRateAlgorithm("EXCESS CHARGE UNIT START: \$".number_format($fExcessCharge, '4', '.', '')." @ {$iExcessUnits} Excess Units (Excess Start: {$iCapUsage} Units)");
			}
			elseif ($fCapLimit && $fStandardCharge > $fCapLimit)
			{
				// Add Excess Charge & Excess Flagfall to our Charge
				$fExcessCharge	= ($fStandardCharge - $fCapLimit);
				$fCharge		+= $fExcessCharge + $aExcessRate['fFlagfall'];
				$this->_logRateAlgorithm("EXCESS CHARGE DOLLAR START: \$".number_format($fExcessCharge, '4', '.', '')." @ \$".number_format($fCapLimit, '4', '.', '')." + \$".number_format($aExcessRate['fFlagfall'], '4', '.', '')." Flagfall");
			}
			
			$this->_logRateAlgorithm("STANDARD + EXCESS CHARGES: \$".number_format($fCharge, '4', '.', ''));
			
			// PRORATE
			if ($bProrate)
			{
				$iChargeStartDate	= strtotime($sStartDatetime);
				$iChargeEndDate		= strtotime($sEndDatetime);
				$iPeriodStartDate	= strtotime($sStartDatetime);
				$iPeriodEndDate		= strtotime('+1 month', $iChargeStartDate) - 1;
				
				$fCharge	= Invoice::prorate($fCharge, $iChargeStartDate, $iChargeEndDate, $iPeriodStartDate, $iPeriodEndDate, DATE_TRUNCATE_DAY, false, null);
				$this->_logRateAlgorithm("PRORATED: \$".number_format($fCharge, '4', '.', ''));
			}
		}
		
		// ROUNDING
		// Round according to the Rating Standard
		$fCharge	= Rate::roundToRatingStandard($fCharge);
		$this->_logRateAlgorithm("ROUNDED: \$".number_format($fCharge, '4', '.', ''));
		
		return $fCharge;
	}
	
	protected function _calculateChargeStage($iUnits, $fCost, $aRateDefinition)
	{
		$fRatePerUnitBlock			= (float)$aRateDefinition['fRatePerUnitBlock'];
		$fFlagfall					= (float)$aRateDefinition['fFlagfall'];
		$fMarkupPercentage			= (float)$aRateDefinition['fMarkupPercentage'];
		$fMarkupDollarsPerUnitBlock	= (float)$aRateDefinition['fMarkupDollarsPerUnitBlock'];
		$iUnitBlockSize				= (int)$aRateDefinition['iUnitBlockSize'];
		
		Flex::assert($iUnitBlockSize > 0, "Rate Unit Block Size ({$iUnitBlockSize}) is less than 1", print_r($aRateDefinition, true));
		
		// Calculate Unit Blocks to Charge
		$iUnitBlocks	= ceil($iUnits / $iUnitBlockSize);
		
		// Base Charge
		$fCharge	= ($iUnitBlocks * $fRatePerUnitBlock) + $fFlagfall;
		
		// Markup
		if ($fMarkupPercentage || $fMarkupDollarsPerUnit)
		{
			$fCharge	+= $fCost + (($fMarkupPercentage / 100) * $fCost) + ($iUnitBlocks * $fMarkupDollarsPerUnitBlock);
		}
		
		return $fCharge;
	}
	
	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	protected static function getMaxCacheSize()
	{
		return 100;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}
	
	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement						Name of the statement
	 *
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1");
					break;
				case 'selForServiceAndDefinition':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"	ServiceRateGroup srg
																						JOIN RateGroupRate rgr ON (srg.RateGroup = rgr.RateGroup)
																						JOIN Rate r ON (rgr.Rate = r.Id)",
																					"	r.*,
																						srg.StartDatetime	AS start_datetime,
																						srg.EndDatetime		AS end_datetime",
																					"	srg.Service = <service_id>
																						AND r.Fleet = <is_fleet>
																						AND
																						(
																							/* For Destination-end Fleet checking, we don't care about RecordType, DestinationCode, or Time of Day */
																							(<is_fleet_check_only> != 0)
																							OR
																							(
																								r.RecordType = <record_type_id>
																								AND r.Destination = <destination_code>
																								AND
																								(
																									(r.Monday		= 1 AND	DAYOFWEEK(<effective_datetime>) = 2)
																									OR (r.Tuesday	= 1 AND	DAYOFWEEK(<effective_datetime>) = 3)
																									OR (r.Wednesday	= 1 AND	DAYOFWEEK(<effective_datetime>) = 4)
																									OR (r.Thursday	= 1 AND	DAYOFWEEK(<effective_datetime>) = 5)
																									OR (r.Friday	= 1 AND	DAYOFWEEK(<effective_datetime>) = 6)
																									OR (r.Saturday	= 1 AND	DAYOFWEEK(<effective_datetime>) = 7)
																									OR (r.Sunday	= 1 AND	DAYOFWEEK(<effective_datetime>) = 1)
																								)
																								AND EXTRACT(HOUR_SECOND FROM <effective_datetime>) BETWEEN r.StartTime AND r.EndTime
																							)
																						)
																						AND (<use_perfect_match> = 0 OR <effective_datetime> BETWEEN rgr.effective_start_datetime AND rgr.effective_end_datetime)",
																					"	(<effective_date> BETWEEN srg.StartDatetime AND srg.EndDatetime) DESC,
																						(LEAST(ABS(TIMESTAMPDIFF(SECOND, <effective_datetime>, srg.StartDatetime)), ABS(TIMESTAMPDIFF(SECOND, <effective_datetime>, srg.EndDatetime)))) ASC,
																						srg.CreatedOn DESC,
																						srg.Id DESC");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>