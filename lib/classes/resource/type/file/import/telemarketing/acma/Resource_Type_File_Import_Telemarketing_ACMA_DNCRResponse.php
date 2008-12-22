<?php
/**
 * Resource_Type_File_Import_Telemarketing_ACMA_DNCRResponse
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Import_Telemarketing_ACMA_DNCRResponse
 */
class Resource_Type_File_Import_Telemarketing_ACMA_DNCRResponse
{
	protected	$_objFileImport;
	
	const		DNCR_EXPIRY_PERIOD_DAYS		= 190;		// ~6 months
	
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * @constructor
	 */
	public function __construct($objFileImport)
	{
		if (!($objFileImport instanceof File_Import))
		{
			throw new Exception("Invalid File_Import object passed");
		}
		
		$this->_objFileImport		= $objFileImport;
	}
	
	/**
	 * normalise()
	 *
	 * Normalises and Imports the File into Flex
	 * 
	 * @return	array						Array of Error messages
	 * 
	 * @method
	 */
	public function normalise()
	{
		// Open the file
		$this->_resFile	= $this->_objFileImport->fopen();
		
		// Parse each Line
		$intLine	= 0;
		$arrErrors	= array();
		while (!feof($this->_resFile))
		{
			$intLine++;
			
			if ($strLine = trim(fgets($this->_resFile)))
			{
				// Normalise the Line
				$arrNormalised	= $this->_normaliseLine($strLine);
				
				if (!$arrNormalised['__ERRORS__'])
				{
					// If the FNN is DNCR-Registered, then Blacklist it
					if (strtolower($arrNormalised['DNCRRegistered']) === 'y')
					{
						// Insert the normalised Line into the Database
						$objBlackListedFNN	= new Telemarketing_FNN_Blacklist();
						
						$objBlackListedFNN->fnn										= $arrNormalised['FNN'];
						$objBlackListedFNN->cached_on								= Data_Source_Time::currentTimestamp();
						$objBlackListedFNN->expired_on								= date("Y-m-d H:i:s", strtotime("+".self::DNCR_EXPIRY_PERIOD_DAYS." days", strtotime($objBlackListedFNN->cached_on)));
						$objBlackListedFNN->telemarketing_fnn_blacklist_nature_id	= TELEMARKETING_FNN_BLACKLIST_NATURE_DNCR;
						$objBlackListedFNN->file_import_id							= $this->_objFileImport->Id;
						
						//throw new Exception(print_r($objBlackListedFNN->toArray(), true));
						
						$objBlackListedFNN->save();
					}
				}
				else
				{
					$arrErrors[]	= "Line {$intLine} had the following errors: ".implode('; ', $arrNormalised['__ERRORS__']);
				}
			}
		}
		
		// Close the file
		fclose($this->_resFile);
		
		return $arrErrors;
	}
	
	/**
	 * _normaliseLine()
	 *
	 * Normalises a raw data record for Import into Flex
	 * 
	 * @param	string	$strLine				Line of raw data to Normalise
	 * 
	 * @method
	 */
	protected static function _normaliseLine($strLine)
	{
		$arrNormalised					= array();
		$arrNormalised['__ERRORS__']	= array();
		
		// Explode the CSV
		$arrExplode		= self::parseCSV($strLine);
		
		// Ensure that we have the correct number of fields
		$intActualColumns	= count($arrExplode);
		$intRequiredColumns	= count(self::_getFileFormatDefinition(null, '__COLUMNS__'));
		if ($intActualColumns !== $intRequiredColumns)
		{
			$arrNormalised['__ERRORS__'][]	= "Incorrect number of CSV fields (Actual: {$intActualColumns};Expected: {$intRequiredColumns})";
		}
		
		$intCallPeriodLengthDays	= self::CALL_PERIOD_LENGTH_DAYS;
		
		// Pull the data
		$arrNormalised['FNN']				= $arrExplode[0];
		$arrNormalised['DNCRRegistered']	= $arrExplode[1];
		
		// Validate
		if (!preg_match("/^0[2378]\d{8}$/", $arrNormalised['FNN']))
		{
			$arrNormalised['__ERRORS__'][]	= "FNN '{$arrNormalised['FNN']}' is invalid!";
		}
		if (!preg_match("/^([Yy]|[Nn])$/", $arrNormalised['DNCRRegistered']))
		{
			$arrNormalised['__ERRORS__'][]	= "DNCR-Registered flag '{$arrNormalised['DNCRRegistered']}' is invalid!";
		}
		
		return $arrNormalised;
	}
	
	/**
	 * _getFileFormatDefinition()
	 *
	 * Normalises a raw data record for Import into Flex
	 * 
	 * @param	string	$strLine				Line of raw data to Normalise
	 * 
	 * @method
	 */
	protected static function _getFileFormatDefinition($strField=null, $strProperty=null)
	{
		static	$arrFileFormatDefinition;
		if (!$arrFileFormatDefinition)
		{
			$arrColumns	=	array
							(
								'FNN'				=>	array
														(
															'Index'			=> 0,
															'Validation'	=> "/^0[2378]\d{8}$/"
														),
								'DNCRRegistered'	=>	array
														(
															'Index'			=> 1,
															'Validation'	=> "/^([Yy]|[Nn])$/"
														)
							);
			
			$arrFileFormatDefinition	= array();
			$arrFileFormatDefinition['__COLUMNS__']	= $arrColumns;
			
			$arrFileFormatDefinition['__INDEXES__']	= array();
			foreach ($arrColumns as $strName=>$arrDefinition)
			{
				$arrFileFormatDefinition['__INDEXES__'][$arrDefinition['Index']]	= array_merge(array('Name'=>$strName), $arrDefinition);
			}
		}
		
		// Return the Definition
		if ($strField !== null)
		{
			if ($strProperty !== null)
			{
				return $arrFileFormatDefinition['__COLUMNS__'][$strField][$strProperty];
			}
			else
			{
				return $arrFileFormatDefinition['__COLUMNS__'][$strField];
			}
		}
		elseif($strProperty !== null)
		{
			return $arrFileFormatDefinition[$strProperty];
		}
		else
		{
			return $arrFileFormatDefinition;
		}
	}
	
	public static function parseCSV($strLine, $strDelimiter=',', $strEnclose='"', $strEscape='\\')
	{
		$arrCSV			= array();
		
		$strField		= '';
		$bolInEscape	= false;
		$bolInEnclose	= false;
		for ($i = 0; $i < strlen($strLine); $i++)
		{
			$strChar	= $strLine{$i};
			
			switch ($strChar)
			{
				case $strEscape:
					// Escape Character
					$strField		.= ($bolInEscape) ? $strChar : '';
					$bolInEscape	= !$bolInEscape;
					break;
				
				case $strEnclose:
					// Enclose Character
					$strField		.= ($bolInEscape) ? $strChar : '';
					$bolInEnclose	= !$bolInEnclose;
					break;
				
				case $strDelimiter:
					// Delimiter
					if ($bolInEnclose)
					{
						$strField		.= $strChar;
					}
					else
					{
						// Push this field to the CSV array
						$arrCSV[]	= $strField;
						$strField	= '';
					}
					break;
				
				default:
					// Normal Character
					$strField	.= $strChar;
					break;
			}
		}
		// Push any remaining field to the CSV array
		$arrCSV[]	= $strField;
		
		return $arrCSV;
	}
}