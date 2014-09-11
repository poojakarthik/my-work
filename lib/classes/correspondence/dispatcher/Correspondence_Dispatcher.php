<?php
interface Correspondence_Dispatcher {
	public function getFileNameForCorrespondenceRunDispatch($oCorrespondenceRunDispatch);
}

class Correspondence_Dispatch_Exception extends Exception {
	public $iError;

	const DATAFILEBUILD = 1;
	const PDF_FILE_COPY = 2;
	const EXPORT_FILE_SAVE = 3;
	const EXPORT_TAR_FILE_SAVE = 4;
	const FILE_DELIVER = 5;
	const TAR_FILE_DELIVER = 6;
	const MAILHOUSE_PROCESSING = 7;
	const SYSTEM_CONFIG = 8;

	public function __construct($iErrorCode, $mException=null) {
		parent::__construct($mException);
		$this->iError = $iErrorCode;
	}

	public function failureReasonToString() {
		// If we have an error message, return it
		if (isset(self::$_aErrorMessages[$this->iError])) {
			return self::$_aErrorMessages[$this->iError];
		}
		return null;
	}

	private static $_aErrorMessages = array(
		self::DATAFILEBUILD => 'Error adding records to export file',
		self::PDF_FILE_COPY => 'Could not create PDF for TAR file',
		self::EXPORT_FILE_SAVE => 'Failed to save export file',
		self::EXPORT_TAR_FILE_SAVE => 'Failed to create PDF TAR file',
		self::FILE_DELIVER => 'Failed to deliver datafile to mailing house',
		self::MAILHOUSE_PROCESSING => 'Mailhouse reported an internal processing error',
		self::SYSTEM_CONFIG => 'Correspondence System Internal Config Error',
		self::TAR_FILE_DELIVER => 'Failed to deliver PDF TAR file to mailing house'
	);
}