<?php
class File_Exporter_RecordType
{
	protected	$_aFields		= array();
	protected	$_sRecordClass	= 'File_Exporter_Record';
	
	public function process($aRecord)
	{
		$aProcessedRecord	= array();
		foreach ($aRecord as $sFieldAlias=>$mValue)
		{
			$aProcessedRecord[$sFieldAlias]	= $this->getField($sFieldAlias)->process($mValue);
		}
		return $aProcessedRecord;
	}
	
	public function addField($sAlias, File_Exporter_Field $oField, $mBeforeField=null)
	{
		// Check if this alias is already registered
		if (isset($this->_aFields[$sAlias]))
		{
			throw new Exception("Field Alias '{$sAlias}' is already registered!");
		}
		
		$iInsertIndex	= ($mBeforeField) ? $this->getField($mBeforeField) : count($this->_aFields);
		$this->_aFields	= array_splice($this->_aFields, $iInsertIndex, 0, array($sAlias=>$oField));
		
		return $this;
	}
	
	public function getField($sAlias)
	{
		if ($sAlias instanceof File_Exporter_Field)
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
		elseif (!is_subclass_of($sRecordClass, 'File_Exporter_Record'))
		{
			throw new Exception("Record Class '{$sRecordClass}' does not extend File_Exporter_Record");
		}
		
		$this->_sRecordClass	= $sRecordClass;
		return $this;
	}
	
	public function newRecord()
	{
		return new $this->_sRecordClass($this);
	}
	
	public static function factory()
	{
		return new self();
	}
}
?>