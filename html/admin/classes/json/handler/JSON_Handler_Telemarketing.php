<?php

class JSON_Handler_Telemarketing extends JSON_Handler {
	protected $_JSONDebug	= '';

	public function __construct() {
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function addFNNToBlacklist($strFNN) {
		try {
			try {
				DataAccess::getDataAccess()->TransactionStart();
				
				// Does this FNN already exist on the Blacklist?
				if ($objFNNExists = Telemarketing_FNN_Blacklist::getForTypeAndFNN(TELEMARKETING_FNN_BLACKLIST_NATURE_OPTOUT, $strFNN)) {
					throw new JSON_Handler_Telemarketing_Exception_AlreadyBlacklisted("The FNN '{$strFNN}' is already on the Flex Telemarketing Blacklist.");
				}
				
				// Add to the Blacklist
				$objFNN	= new Telemarketing_FNN_Blacklist();
				
				$objFNN->fnn									= $strFNN;
				$objFNN->cached_on								= date("Y-m-d H:i:s");
				$objFNN->expired_on								= "9999-12-31 23:59:59";
				$objFNN->telemarketing_fnn_blacklist_nature_id	= TELEMARKETING_FNN_BLACKLIST_NATURE_OPTOUT;
				
				$objFNN->save();
				
				DataAccess::getDataAccess()->TransactionCommit();
			} catch (Exception $eException) {
				DataAccess::getDataAccess()->TransactionRollback();
				throw $eException;
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
				"Success" => true,
				"strFNN" => $strFNN,
				"strDebug" => (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
			);
		} catch (Exception $oEx) {
			return array(
				'sExceptionClass' => get_class($oEx),
				"Success" => false,
				"ErrorMessage" => $oEx->getMessage(),
				"strDebug" => (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
			);
		}
	}
}

class JSON_Handler_Telemarketing_Exception_AlreadyBlacklisted extends Exception {}

?>