<?php

class Archive_Tar {

	private $_sArchiveFile;
	private $_sCompression;

	const TAR_OPERATION_MODE_ADD = 'r';
	const TAR_OPERATION_MODE_CREATE = 'c';

	const TAR_COMPRESSION_FLAG_BZIP2 = 'y';
	const TAR_COMPRESSION_FLAG_GZIP = 'z';

	public function __construct($sArchiveFile, $sCompression=null) {
		if (!is_dir(dirname($sArchiveFile))) {
			throw new Exception("Output directory does not exist: {$sArchiveFile}");
		}
		$this->_sArchiveFile = $sArchiveFile;
		$this->_sCompression = $sCompression;
	}

	// Operations
	public function add($mFiles) {
		return $this->_save(self::TAR_OPERATION_MODE_ADD, $mFiles);
	}

	public function addModify($mFiles, $sAddPath=null, $sRemovePath=null) {
		if(!empty($sAddPath)) {
			throw new Exception("Unsupported/unimplemented Parameter sAddPath: {$sAddPath}");
		}
		return $this->_save(self::TAR_OPERATION_MODE_ADD, $mFiles, $sRemovePath);
	}

	public function create($mFiles) {
		return $this->_save(self::TAR_OPERATION_MODE_CREATE, $mFiles);
	}

	public function createModify($mFiles, $sAddPath=null, $sRemovePath=null) {
		if(!empty($sAddPath)) {
			throw new Exception("Unsupported/unimplemented Parameter sAddPath: {$sAddPath}");
		}
		return $this->_save(self::TAR_OPERATION_MODE_CREATE, $mFiles, $sRemovePath);
	}

	// Saving
	private function _save($sOperationMode, $files, $sRemovePath=null) {
		$tempFilesFromPath = tempnam(sys_get_temp_dir(), 'flex-archive-tar-files-from');
		if (false === file_put_contents($tempFilesFromPath, implode("\n", $files))) {
			throw new Exception();
		}

		exec(sprintf('tar %s --files-from=%s -P%s%sf %s',
			!$sRemovePath ? '' : '--transform=' . escapeshellarg('s/^\/' . str_replace('/', '\\/', implode('/', array_filter(explode('/', $sRemovePath)))) . '//x'),
			escapeshellarg($tempFilesFromPath),
			$this->_getCompressionMode() ?: '',
			$sOperationMode,
			$this->_sArchiveFile
		), $outputLines, $errorCode);

		if ($errorCode !== 0) {
			throw new Exception();
		}

		@unlink($tempFilesFromPath);
		return true;
	}

	private function _getCompressionMode() {
		if ($this->_sCompression) {
			// Compression.
			if (($this->_sCompression === true) || ($this->_sCompression == 'gz')) {
				return self::TAR_COMPRESSION_FLAG_GZIP;
			} else if ($sCompression == 'bz2') {
				return self::TAR_COMPRESSION_FLAG_BZIP2;
			} else {
				throw new Exception("Unsupported compression type {$sCompression}, supported types are 'gz' and 'bz2'.\n");
				return false;
			}
		} else {
			return false;
		}
	}

	public function save() {
		throw new Exception("Unsupported/Not implemented method: setErrorHandling()");
	}
	public function setErrorHandling($sErrorMode) {
		throw new Exception("Unsupported/Not implemented method: setErrorHandling()");
	}

}