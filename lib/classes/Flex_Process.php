<?php
class Flex_Process
{
	// Registered Processes
	const	PROCESS_COLLECTION				= 'collection';

	const   PROCESS_BALANCE_REDISTRIBUTION	= 'collections-balance-redistribution';
	const   PROCESS_COLLECTIONS_PROCESS		= 'collections-process';
	
	const	PROCESS_PAYMENTS_IMPORT			= 'payments-import';
	const	PROCESS_PAYMENTS_NORMALISATION	= 'payments-normalisation';
	const	PROCESS_PAYMENTS_PROCESSING		= 'payments-processing';
	const	PROCESS_PAYMENTS_DIRECTDEBIT	= 'payments-direct-debit';
	const	PROCESS_PAYMENTS_EXPORT			= 'payments-export';
	
	const	PROCESS_CDR_IMPORT				= 'cdr-import';
	const	PROCESS_CDR_NORMALISATION		= 'cdr-normalisation';
	const	PROCESS_CDR_RATING				= 'cdr-rating';
	
	const	PROCESS_BILLING_GENERATE		= 'billing-generate';
	const	PROCESS_BILLING_REVOKE			= 'billing-revoke';
	const	PROCESS_BILLING_COMMIT			= 'billing-commit';
	
	const	PROCESS_PROVISIONING_IMPORT		= 'provisioning-import';
	const	PROCESS_PROVISIONING_EXPORT		= 'provisioning-export';
	const	PROCESS_PROVISIONING_LINESTATUS	= 'provisioning-line-status';
	
	const	PROCESS_CHARGES_RECURRING		= 'charges-recurring';
	
	const	PROCESS_LATE_NOTICES			= 'late-notices';
	
	// Other Class Constants
	const	PROCESSES_DIRECTORY				= 'processes/running/';
	
	// Static Members
	static protected	$_aProcesses	= array();
	
	// Instance Members
	protected	$_sProcessName;
	protected	$_sLockFilePath;
	protected	$_rLockFile;
	
	protected function __construct($sProcessName)
	{
		$this->_sProcessName		= $sProcessName;
		$this->_sLockFilePath	= self::_buildProcessRunningFilename($sProcessName);
	}
	
	public function lock()
	{
		if (Flex::assert(!$this->isLocked(), "Process {$this->_sProcessName} cannot run because it is already running", null, "Process {$this->_sProcessName} blocked"))
		{
			// Script is not running
			Log::getLog()->log("Creating Lock File @ '{$this->_sLockFilePath}'...");
			
			// Create Running File
			$iWouldBlock	= 0;
			$this->_rLockFile	= fopen($this->_sLockFilePath, 'a');
			if (Flex::assert($this->_rLockFile && flock($this->_rLockFile, LOCK_EX | LOCK_NB, $iWouldBlock) && !$iWouldBlock))
			{
				// Write the current timestamp to the file, and leave it open
				fwrite($this->_rLockFile, date("Y-m-d H:i:s")."\n");
				return true;
			}
		}
		
		return false;
	}
	
	public function unlock()
	{
		if ($this->_rLockFile)
		{
			fclose($this->_rLockFile);
		}
	}
	
	public function isLocked()
	{
		// Check if there is a File Lock on the Hash file
		if (!file_exists($this->_sLockFilePath))
		{
			return false;
		}
		elseif ($rFile = fopen($this->_sLockFilePath, 'r'))
		{
			$iWouldBlock	= 0;
			$bLocked		= (!flock($rFile, LOCK_EX | LOCK_NB, $iWouldBlock) || $iWouldBlock);
			
			fclose($rFile);
			return $bLocked;
		}
		else
		{
			throw new Exception("Unable to read Process Lock file at '{$sLockFilePath}'");
		}
	}
	
	private static function _buildProcessRunningFilename($sProcessName)
	{
		$sLockFilePath	= FILES_BASE_PATH.self::PROCESSES_DIRECTORY;
		@mkdir($sLockFilePath, 0777, true);
		return $sLockFilePath.$sProcessName;
	}
	
	public static function factory($sProcessName)
	{
		if (!array_key_exists($sProcessName, self::$_aProcesses))
		{
			self::$_aProcesses[$sProcessName]	= new Flex_Process($sProcessName);
		}
		return self::$_aProcesses[$sProcessName];
	}
}
?>
