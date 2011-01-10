<?php
/**
 * Destination
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Destination
 */
class Destination extends ORM_Cached
{
	protected 			$_strTableName			= "Destination";
	protected static	$_strStaticTableName	= "Destination";
	
	protected static	$_aMatchWordFilter			= array('offnet', 'onnet', 'off-net', 'on-net', 'off', 'on', 'net', 'telstra', 'mobile');
	protected static	$_aMatchPunctuationFilter	= array('to', 'and', '&', '-', 'is', ',', 'of', ';', '_', 'the');
	
	public static function getForDescriptionLike($sDescription, $mDestinationContext=null)
	{
		$iDestinationContextId	= ($mDestinationContext === null) ? null : ORM::extractId($mDestinationContext);
		
		// Tokenise the Description
		$aDescription	= preg_split('/\s+/', $sDescription);
		if (!count($aDescription))
		{
			// No tokens -- don't search
			return array();
		}
		
		// Build WHERE LIKEs
		$aWhereLike	= array();
		foreach ($aDescription as $sToken)
		{
			$aWhereLike[]	= "Description LIKE '%".mysql_escape_string($sToken)."%'";
		}
		$sWhereLike	= (count($aWhereLike)) ? implode(' OR ', $aWhereLike) : '1';
		
		$sWhereContext	= ($iDestinationContextId === null) ? '1' : 'Context = '.((int)$iDestinationContextId);
		
		$oQuery	= new Query();
		$sQuery	= "	SELECT	*
					FROM	Destination
					WHERE	({$sWhereLike})
							AND ({$sWhereContext})";
		//throw new Exception($sQuery);
		if (($mResult = $oQuery->Execute($sQuery)) === false)
		{
			throw new Exception_Database($oQuery->Error());
		}
		$aResults	= array();
		while ($aResult = $mResult->fetch_assoc())
		{
			//throw new Exception(print_r($aResult, true));
			$oDestinationORM	= new Destination($aResult);
			$aResults[]			= $oDestinationORM->toStdClass();
		}
		return $aResults;
	}
	
	public static function getForDescriptionWords($sDescription, array $aIgnoreWords=array())
	{
		static	$aDestinationORMs;
		$aDestinationORMs	= ($aDestinationORMs) ? $aDestinationORMs : Destination::getAll();
		
		// Filter out any useless words that will just give us junk matches
		$aDescription			= array_unique(preg_split('/\s+/', trim($sDescription)));
		$aDescriptionFull		= array();
		$aDescriptionMinified	= array();
		foreach ($aDescription as $sWord)
		{
			$sWordLower	= strtolower($sWord);
			if (!in_array($sWordLower, self::$_aMatchPunctuationFilter))
			{
				// Not Punctuation -- add to Full
				$aDescriptionFull[]		= $sWordLower;
				if (!in_array($sWordLower, self::$_aMatchWordFilter) && !in_array($sWordLower, $aIgnoreWords))
				{
					// Not in Word Filters (add to Minified)
					$aDescriptionMinified[]	= $sWordLower;
				}
			}
		}
		
		// See if there are any Flex Destinations that have a similar description
		$aMatches	= array();
		foreach ($aDestinationORMs as $iCode=>$oDestination)
		{
			$aFullMatches		= array();
			$aMinifiedMatches	= array();
			
			// Get words in the Flex Destination
			$aFlexDescription		= array_unique(preg_split('/\s+/', trim($oDestination->Description)));
			$aFlexDescriptionClean	= array();
			foreach ($aFlexDescription as $mIndex=>$sWord)
			{
				$sWordLower	= strtolower($sWord);
				if (in_array($sWordLower, self::$_aMatchPunctuationFilter))
				{
					// Punctuation -- continue to the next word
					continue;
				}
				$aFlexDescriptionClean[$sWordLower]	= true;
				
				// Does the word exist in our Carrier Description?
				if (in_array($sWordLower, $aDescriptionMinified))
				{
					$aMinifiedMatches[$sWordLower]	= true;
				}
				if (in_array($sWordLower, $aDescriptionFull))
				{
					$aFullMatches[$sWordLower]	= true;
				}
			}
			
			// Only Destinations that have a Minified Match should be considered
			$iMinifiedMatches	= count(array_keys($aMinifiedMatches, true, true));
			$iFullMatches		= count(array_keys($aFullMatches, true, true));
			if ($iMinifiedMatches > 0)
			{
				// Word match ratio is the number of matching words divided by the number of words in the Full Carrier Description
				$aMatches[$oDestination->Id]	= array(
					'aCarrierMinified'		=> $aDescriptionMinified,
					'aCarrierFull'			=> $aDescriptionFull,
					'aFlexClean'			=> $aFlexDescriptionClean,
					'aMatchesMinified'		=> $aMinifiedMatches,
					'aMatchesFull'			=> $aFullMatches,
					'fFlexMatchRatio'		=> $iFullMatches / count($aFlexDescriptionClean),
					'fCarrierMatchRatio'	=> $iFullMatches / count($aDescriptionFull),
					'bPerfectMatch'			=> ($iFullMatches === count($aDescriptionFull) && $iFullMatches === count($aFlexDescriptionClean))
				);
			}
		}
		asort($aMatches, SORT_NUMERIC);
		$aMatches	= array_reverse($aMatches, true);
		
		$aDestinations	= array();
		foreach ($aMatches as $iDestinationId=>$aMatchDetails)
		{
			$oDestinationStdClass	= $aDestinationORMs[$iDestinationId]->toStdClass();
			
			$oDestinationStdClass->bPerfectMatch	= $aMatchDetails['bPerfectMatch'];
			
			$aDestinations[]	= $oDestinationStdClass;
		}
		/*
		if (stripos($sDescription, 'China - Beijing') !== false)
		{
			$aDebugArray	= array('Description'=>$aDescription, 'DescriptionFull'=>$aDescriptionFull, 'DescriptionMinified'=>$aDescriptionMinified, 'Destinations'=>$aDestinations, 'Matches'=>$aMatches);
			echo JSON_Services::instance()->encode($aDebugArray); die;
			//throw new Exception(print_r($sDebugArray, true));
		}
		/**/
		return $aDestinations;
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

	public function getForCode($iCode)
	{
		$oSelect	= self::_preparedStatement('selByCode');
		$oSelect->Execute(array('Code' => $iCode));
		
		if ($aResult = $oSelect->Fetch())
		{
			return new self($aResult);
		}
		
		return null;
	}

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
				case 'selByCode':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Code = <Code>");
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