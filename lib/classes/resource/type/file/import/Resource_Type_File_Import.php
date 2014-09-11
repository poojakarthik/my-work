<?php
abstract class Resource_Type_File_Import extends Resource_Type_Base
{
	protected	$_oFileImport;
	protected	$_oFileImporter;

	public function __construct($mCarrierModule, $mFileImport)
	{
		parent::__construct($mCarrierModule);

		// File Import ORM
		$this->_oFileImport	= ($mFileImport instanceof File_Import) ? $mFileImport : File_Import::getForId(ORM::extractId($mFileImport));

		// File Importer
		$this->_configureFileImporter();
	}

	abstract public function getRecords();

	abstract public function processRecord($sRecord);

	public function process()
	{
		$aRecords	= $this->getRecords();
		foreach ($aRecords as $sRecord)
		{
			$this->processRecord($sRecord);
		}
	}

	public function getFileImport()
	{
		return $this->_oFileImport;
	}

	public function save()
	{
		$this->_oFileImport->NormalisedOn	= date('Y-m-d H:i:s');
		$this->_oFileImport->save();
		return $this;
	}

	protected function _configureFileImporter() {
		// NOTE: default no-op behaviour
	}

	protected static function factory($sClassName, $mFileImport)
	{
		$oFileImport		= File_Import::getForId(ORM::extractId($mFileImport));
		$aCarrierModules	= Carrier_Module::getForDefinition($mCarrierModuleType, $mFileImport->FileType, $mFileImport->Carrier);
		if (count($aCarrierModules) > 1)
		{
			throw new Exception(count($aCarrierModules)." found for unique Carrier Module definition for '{$oFileImport->FileName}' ({$oFileImport->Id})");
		}
		elseif (count($aCarrierModules) < 1)
		{
			throw new Exception("No Carrier Module found for '{$oFileImport->FileName}' ({$oFileImport->Id})");
		}
		return new $sClassName(array_pop($aCarrierModules), $mFileImport);
	}
}
?>