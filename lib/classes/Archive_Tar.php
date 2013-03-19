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
		return $this->_handleOperation(self::TAR_OPERATION_MODE_ADD, $mFiles);
	}

	public function addModify($mFiles, $sAddPath=null, $sRemovePath=null) {
		if(!empty($sAddPath)) {
			throw new Exception("Unsupported/unimplemented Parameter sAddPath: {$sAddPath}");
		}
		return $this->_handleOperation(self::TAR_OPERATION_MODE_ADD, $mFiles, $sRemovePath);
	}

	public function create($mFiles) {
		return $this->_handleOperation(self::TAR_OPERATION_MODE_CREATE, $mFiles);
	}

	public function createModify($mFiles, $sAddPath=null, $sRemovePath=null) {
		if(!empty($sAddPath)) {
			throw new Exception("Unsupported/unimplemented Parameter sAddPath: {$sAddPath}");
		}
		return $this->_handleOperation(self::TAR_OPERATION_MODE_CREATE, $mFiles, $sRemovePath);
	}

	// Saving
	private function _handleOperation($sOperationMode, $mFiles, $sRemovePath=null) {
		// Files
		$aFiles = is_array($mFiles) ? $mFiles : array($mFiles);
		// Normalise filenames for shell.
		$sFilelist = implode(" ", self::_normaliseForShell($aFiles));
		$sCompressionMode = ($this->_getCompressionMode()) ? $this->_getCompressionMode() : null;

		if ($sRemovePath) {
			// Transform
			$sRemovePath = implode("/", array_filter(explode("/", $sRemovePath)));
			$sPathToRemove = implode(" ", str_replace("/", "\/", self::_normaliseForShell($sRemovePath)));
			$bResult = $this->_save("tar --transform='s/^\/{$sPathToRemove}//x' -P{$sCompressionMode}{$sOperationMode}f {$this->_sArchiveFile} {$sFilelist}");
		} else {
			// Nothing to do.	
			$bResult = $this->_save("tar -{$sCompressionMode}{$sOperationMode}f {$this->_sArchiveFile} {$sFilelist}");
		}
		return $bResult;
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
	private function _save($sCommand) {
		exec($sCommand, $aOutput, $iErrorCode);
		if ($iErrorCode !== 0) {
			throw new Exception("Error creating tar with command {$sCommand}, failed with error: code={$iErrorCode}, output=" . implode("\n", $aOutput));
		} else {
			return true;
		}
	}

	// Normalisation
	private static function _normaliseForShell($mFiles) {
		$aFiles = is_array($mFiles) ? $mFiles : array($mFiles);
		$aEscapedFiles = array();
		foreach($aFiles as $sFile) {
			$aEscapedFiles[] = str_replace(" ", "\ ", escapeshellcmd($sFile));
		}
		return $aEscapedFiles;
	}

	public function save() {
		throw new Exception("Unsupported/Not implemented method: setErrorHandling()");
	}
	public function setErrorHandling($sErrorMode) {
		throw new Exception("Unsupported/Not implemented method: setErrorHandling()");
	}

}