<?php
/**
 * Resource_Type_File_Export_Telemarketing_SalesCom_PermittedDiallingList
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Export_Telemarketing_SalesCom_PermittedDiallingList
 */
class Resource_Type_File_Export_Telemarketing_SalesCom_PermittedDiallingList
{
	protected	$_objFileExport;
	protected	$_arrOutput			= array();
	
	const		NEW_LINE_DELIMITER	= "\n";
	const		FIELD_DELIMITER		= ',';
	const		FIELD_ENCAPSULATOR	= '';
	const		ESCAPE_CHARACTER	= '\\';
	
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * @constructor
	 */
	public function __construct($intCarrier)
	{
		$this->_intCarrier	= $intCarrier;
	}
	
	/**
	 * export()
	 *
	 * Finalises the Export of a file.  Only to be called after
	 * 
	 * @return	array						Array of Error messages
	 * 
	 * @method
	 */
	public function export($arrRecords=array())
	{
		$arrErrors	= array();
		
		// If we were given any additional records, then render them first
		foreach ($arrRecords as $arrRecord)
		{
			$this->_arrOutput[]	= self::_renderRecord($arrRecord); 
		}
		
		// Dump the data to the Export file
		$strFileName	= 'permitted_'.date("YmdHis").'.csv';
		$strCarrier		= GetConstantName($this->_intCarrier, 'Carrier');
		$strFilePath	= FILES_BASE_PATH."export/telemarketing/{$strCarrier}/".__CLASS__.'/'.$strFileName;
		
		if (!is_dir(dirname($strFilePath)))
		{
			mkdir(dirname($strFilePath), 0777, true);
		}
		file_put_contents($strFilePath, implode(self::NEW_LINE_DELIMITER, $this->_arrOutput));
		
		// Create the FileExport entry
		$this->_objFileExport	= new File_Export();
		$this->_objFileExport->FileName		= $strFileName;
		$this->_objFileExport->Location		= $strFilePath;
		$this->_objFileExport->Carrier		= $this->_intCarrier;
		$this->_objFileExport->ExportedOn	= Data_Source_Time::currentTimestamp(null, true);
		$this->_objFileExport->Status		= FILE_DELIVERED;
		$this->_objFileExport->FileType		= RESOURCE_TYPE_FILE_EXPORT_TELEMARKETING_SALESCOM_PERMITTED_DIALLING_LIST;
		$this->_objFileExport->SHA1			= sha1_file($strFilePath);
		$this->_objFileExport->save();
		
		return $arrErrors;
	}
	
	/**
	 * _renderRecord()
	 *
	 * Converts a Flex Data array to a rendered line of text
	 * 
	 * @param	array	$arrRecord					Array representation of Output Data
	 * 
	 * @return	string								Rendered data
	 * 
	 * @method
	 */
	protected static function _renderRecord($arrRecord)
	{
		// HACKHACKHACK: Need to get this done fast
		return $arrRecord['raw_record'];
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
								'FNN'			=>	array
													(
														'Index'			=> 5,
														'Validation'	=> "/^0[2378]\d{8}$/"
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
	
	/**
	 * getFileExport()
	 *
	 * Returns the File_Export object associated with this File
	 * 
	 * @return	File_Export
	 * 
	 * @method
	 */
	function getFileExport()
	{
		return $this->_objFileExport;
	}
}