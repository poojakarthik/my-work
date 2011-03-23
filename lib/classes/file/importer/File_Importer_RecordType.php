<?php
class File_Importer_RecordType
{
	protected	$_oFileImporter;
	
	protected	$_aFields		= array();
	protected	$_sRecordClass	= 'File_Importer_Record';
	
	public function __construct(File_Importer $oFileImporter)
	{
		$this->_oFileImporter	= $oFileImporter;
	}

	public function createField($sAlias, $sClass='File_Importer_Field', $mBeforeField=null) {
		$oFieldInstance	= new $sClass($this);
		$this->addField($sAlias, $oFieldInstance, $mBeforeField);
		return $oFieldInstance;
	}
	
	public function addField($sAlias, File_Importer_Field $oField, $mBeforeField=null)
	{
		// Check if this alias is already registered
		if (isset($this->_aFields[$sAlias]))
		{
			throw new Exception("Field Alias '{$sAlias}' is already registered!");
		}
		
		// Insert the new Field (can't use array_splice, as it doesn't preserve associative keys)
		$aExistingFields	= $this->_aFields;
		$this->_aFields		= array();
		foreach ($aExistingFields as $sFieldAlias=>$oFieldDefinition)
		{
			// Insert our new Field if this is the 'before' Field
			if ($mBeforeField && $oFieldDefinition === $this->getField($mBeforeField))
			{
				$this->_aFields[$sAlias]	= $oField;
			}
			
			$this->_aFields[$sFieldAlias]	= $oFieldDefinition;
		}
		
		// If our new Field haven't been inserted yet, push it on to the end of the array
		if (!in_array($oField, $this->_aFields, true))
		{
			$this->_aFields[$sAlias]	= $oField;
		}
		
		return $this;
	}
	
	public function getField($sAlias)
	{
		if ($sAlias instanceof File_Importer_Field)
		{
			if (!in_array($sAlias, $this->_aFields))
			{
				throw new Exception("Field '{$sAlias}' is not registered with this Record Type");
			}
			return $sAlias;
		}
		else
		{
			if (!isset($this->_aFields[$sAlias]))
			{
				throw new Exception("Field Alias '{$sAlias}' is not registered with this Record Type");
			}
			return $this->_aFields[$sAlias];
		}
	}
	
	public function getFields()
	{
		return $this->_aFields;
	}
	
	public function setRecordClass($sRecordClass)
	{
		if (!class_exists($sRecordClass))
		{
			throw new Exception("Record Class '{$sRecordClass}' is undefined");
		}
		elseif (!is_subclass_of($sRecordClass, 'File_Importer_Record'))
		{
			throw new Exception("Record Class '{$sRecordClass}' does not extend File_Importer_Record");
		}
		
		$this->_sRecordClass	= $sRecordClass;
		return $this;
	}
	
	public function newRecord($mData)
	{
		return new $this->_sRecordClass($this, $mData);
	}

	public function parseData($mData) {
		// Process the data
		$aData	= array();
		foreach ($this->_aFields as $sAlias=>$oField) {
			$aData[$sAlias]	= $oField->extract($mData);
		}
		return $aData;
	}
	
	public static function factory()
	{
		return new self();
	}
}
?>