<?php
class ImportTelcoBlue extends ImportBase {
	const DEBUG_LOGGING = true;
	const LINE_TYPE_RESPONSE = 'r';
	const LINE_TYPE_NOTIFICATION = 'n';

	private static $_aResponseDescriptions = array(
		'ACTIVATION_PENDING' => array(
			'COMPLETED' => "Activation pending",
			'FAILED' => "Failed to start activating"
		),
		'ACTIVATION_FAILED' => array(
			'COMPLETED' => "Activation failed",
			'FAILED' => "Failed to finalise activation failure"
		),
		'ACTIVE' => array(
			'COMPLETED' => "Activated",
			'FAILED' => "Failed to activate"
		),
		'SUSPENDED' => array(
			'COMPLETED' => "Suspended",
			'FAILED' => "Suspension failed"
		),
		'DEACTIVATED' => array(
			'COMPLETED' => "Deactivated",
			'FAILED' => "Deactivation failed"
		)
	);

	public $intBaseCarrier = null;
	public $intBaseFileType = RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_TELCOBLUE;
	
	private $_aDefineNotification;
	private $_aDefineResponse;

 	function __construct($intCarrier) {
 		// Parent Constructor
 		parent::__construct($intCarrier);

 		$this->intBaseCarrier = $intCarrier;
 		$this->strDescription = 'Importing Telco Blue Wholesale Provisioning File';

 		// Module Config
		$this->_arrModuleConfig['ChurnAwayNotificationType']['Type'] = DATA_TYPE_INTEGER;
		$this->_arrModuleConfig['ChurnAwayNotificationType']['Description'] = "Wholesale Notification Type - Churn Away";

		$this->_arrModuleConfig['DisconnectionNotificationType']['Type'] = DATA_TYPE_INTEGER;
		$this->_arrModuleConfig['DisconnectionNotificationType']['Description'] = "Wholesale Notification Type - Disconnection";
		
		// Define row start (account for header rows)
		// Row numbers start at 1
		// For a file without any header row, set this to 1
		// For a file with 1 header row, set this to 2
		$this->_intStartRow = 1;
 		
 		// Delimiter
 		$this->_strDelimiter = ",";
 		$this->_strEnclosed = '"';
 		$this->_strEndOfLine = "\n";

 		// Line structure
		$this->_aDefineNotification = array(
			'LineType' => array('Index' => 0),
			'FNN' => array('Index' => 1),
			'IdentifierContext' => array('Index' => 2),
			'NotificationType' => array('Index' => 3),
			'Package' => array('Index' => 4),
			'Status' => array('Index' => 5),
			'StatusResult' => array('Index' => 6),
			'Timestamp' => array('Index' => 7)
		);

		$this->_aDefineResponse = array(
			'LineType' => array('Index' => 0),
			'ClientReference' => array('Index' => 1),
			'Subject' => array('Index' => 2),
			'Action' => array('Index' => 3),
			'Detail' => array('Index' => 4),
			'Status' => array('Index' => 5),
			'Timestamp' => array('Index' => 6)
		);
 	}
 	
	// PreProcess: Pre-processes a file, removes all responses that aren't contract related and any notifications without an FNN
 	function PreProcess($aRawData) {
 		Log::get()->logIf(self::DEBUG_LOGGING, "\t[*] Pre-processing RAW Data:");
 		self::_logArray($aRawData);

 		$aPreProcessedData = array();
 		foreach ($aRawData as $sLine) {
 			$sLine = trim($sLine);
 			if (!empty($sLine)) {
 				Log::get()->logIf(self::DEBUG_LOGGING, "\t[*] Splitting raw line");
 				$aData = $this->_SplitLine($sLine);
 				switch ($aData['LineType']) {
 					case self::LINE_TYPE_RESPONSE:
 						Log::get()->logIf(self::DEBUG_LOGGING, "\t[*] Response: Subject={$aData['Subject']}, Detail={$aData['Detail']}");
 						// A response
 						switch ($aData['Subject']) {
		 					case 'CONTRACT':
		 						// A contract order item update, the Detail is the identifier of the service
		 						if (!empty($aData['Detail'])) {
		 							$aPreProcessedData[] = $sLine;
		 						}
		 						break;
		 				}
		 				break;
		 			case self::LINE_TYPE_NOTIFICATION:
		 				Log::get()->logIf(self::DEBUG_LOGGING, "\t[*] Notification: FNN={$aData['FNN']}");
		 				// A notification
		 				if (!empty($aData['FNN'])) {
		 					// We only care about service notifications
		 					$aPreProcessedData[] = $sLine;
		 				}
		 				break;
 				}
 			}
 		}

 		Log::get()->logIf(self::DEBUG_LOGGING, "\t[*] Pre-Processed Data: ");
 		self::_logArray($aPreProcessedData);
 		return $aPreProcessedData;
 	}

 	// Normalise: Normalises a line from a Provisioning File
	function Normalise($sLine, $iLineNumber) {
		Log::get()->logIf(self::DEBUG_LOGGING, "\t[*] Normalising line {$iLineNumber}: {$sLine}");

 		// Split the Line using the file definition
 		$aData = $this->_SplitLine($sLine);
 		$aResponse = array(); 		

 		// All line types have a timestamp value
 		$aResponse['EffectiveDate'] = date("Y-m-d H:i:s", strtotime($aData['Timestamp']));

 		// Generate ProvisioningResponse properties based on the line type
		$sDescription = null;
 		switch ($aData['LineType']) {
 			case self::LINE_TYPE_RESPONSE:
 				// A response to a request
 				$this->_handleResponse($aResponse, $aData);
 				break;
 			case self::LINE_TYPE_NOTIFICATION:
 				// A notification, unrequested
		 		$this->_handleNotification($aResponse, $aData);
 				break;
 		}
		
		return $aResponse;
 	}

 	private function _handleResponse(&$aResponse, $aData) {
 		Log::get()->logIf(self::DEBUG_LOGGING, "\t\t[*] It's a Response");
 		if ($aData['ClientReference'] !== '') {
 			// Has a client reference, fetch the provisioning request
 			Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t[*] Got client reference: '{$aData['ClientReference']}'");
 			try {
	 			$oRequest = new Provisioning_Request(array('Id' => $aData['ClientReference']), true);
				Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t[*] Found provisioning request: {$oRequest->Id}");
				$aResponse['FNN'] = $oRequest->FNN;
				$aResponse['Type'] = $oRequest->Type;
				$aResponse['Request'] = $oRequest->Id;
			} catch (Exception $oEx) {
				// Failed to locate a provisioning request, this shouldn't happend but if it does act as though it wasn't request by flex
				$aResponse['FNN'] = $aData['Detail'];
				$aResponse['Type'] = null;
				$aResponse['Request'] = null;
			}
 		} else {
 			// No client reference, not requested by flex
 			Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t[*] The Response has no client_reference");
 			$aResponse['FNN'] = $aData['Detail'];
			$aResponse['Type'] = null;
			$aResponse['Request'] = null;
 		}

 		// Find the service details
		$aResponse = $this->FindFNNOwner($aResponse, self::DEBUG_LOGGING);

 		// Build the provisioning response row data
 		$aResponse['CarrierRef'] = null;
		$aResponse['Status'] = null;

		// Request status and description depend on the status of the response
		$iRequestStatusId = null;
		switch ($aData['Status']) {
			case 'PENDING':
				$iRequestStatusId = REQUEST_STATUS_DELIVERED;
				$sDescription = "Request is still being processed";
				break;
			case 'COMPLETE':
			case 'COMPLETE_NO_CHANGE':
				$iRequestStatusId = REQUEST_STATUS_COMPLETED;
				$sDescription = "Request has been completed";
				break;
			case 'FAILED':
				$iRequestStatusId = REQUEST_STATUS_REJECTED;
				$sDescription = "Request was unable to be completed";
				break;
			default:
				$iRequestStatusId = REQUEST_STATUS_DELIVERED;
				$aResponse['Status'] = RESPONSE_STATUS_CANT_NORMALISE;
				$sDescription = "Unable to determine the status from response data";
		}
		
		$aResponse['request_status'] = $iRequestStatusId;
		$aResponse['Description'] = $sDescription;
		Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t[*] Response Description: {$sDescription}");
 	}

 	private function _handleNotification(&$aResponse, $aData) {
 		$oLog = Log::get();
 		$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] It's a Notification");
 		
 		// Basic response fields
 		$aResponse['FNN'] = $aData['FNN'];
		$aResponse['Type'] = null;
		$aResponse['Request'] = null;
		$aResponse['CarrierRef'] = null;
		$aResponse['Status']	= null;
		$aResponse['request_status'] = REQUEST_STATUS_COMPLETED;

 		// Find the service details
		$aResponse = $this->FindFNNOwner($aResponse, self::DEBUG_LOGGING);
		$oCurrentRatePlan = null;
		if (isset($aResponse['Service'])) {
			// Fetch the current rate plan of the service
			$oCurrentRatePlan = ((isset($aResponse['Service']) && ($aResponse['Service'] !== null)) ? Service::getForId($aResponse['Service'])->getCurrentPlan() : null);
			$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] Owner Service: {$aResponse['Service']}");
		} else {
			$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] No Owner Service found");
		}

		// Convert the notification type to a provisioning type (if a notification type is supplied)
		$iNotificationType = (int)$aData['NotificationType'];
		if (($iNotificationType !== null) && ($oCurrentRatePlan !== null)) {
			// A notification type is present
			$iChurnAway = $this->GetConfigField('ChurnAwayNotificationType');
			$iDisconnection = $this->GetConfigField('DisconnectionNotificationType');
			$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] An owner was found and notification type returned ({$iNotificationType}), getting associated provisioning type (Churn Away: {$iChurnAway}; Disconnection: {$iDisconnection})");

			// Determine the type of notification (record as an inbound provisioning type)
			$iWholesalePackage = (int)$aData['Package'];
			switch ($iNotificationType) {
				case $iChurnAway:
					$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] It's a CHURN AWAY notification");

					// It's either PROVISIONING_TYPE_LOSS_FULL or PROVISIONING_TYPE_LOSS_PRESELECT. Check the package.
					if ($oCurrentRatePlan->fullservice_wholesale_plan == $iWholesalePackage) {
						// The package matches the full service wholesale plan for the services current rate plan. It'a a full service loss notification.
						$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] It's a FULL SERVICE - CHURN AWAY notification (Wholesale Package: {$iWholesalePackage})");
						$aResponse['Type'] = PROVISIONING_TYPE_LOSS_FULL;
					} else if ($oCurrentRatePlan->preselection_wholesale_plan == $iWholesalePackage) {
						// The package matches the preselection wholesale plan for the services current rate plan. It'a a preselection loss notification.
						$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] It's a PRESELECTION - CHURN AWAY notification (Wholesale Package: {$iWholesalePackage})");
						$aResponse['Type'] = PROVISIONING_TYPE_LOSS_PRESELECT;
					}
					break;
				case $iDisconnection:
					$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] It's a DISCONNECTION notification");

					// It's either PROVISIONING_TYPE_DISCONNECT_FULL or PROVISIONING_TYPE_DISCONNECT_PRESELECT. Check the package.
					if ($oCurrentRatePlan->fullservice_wholesale_plan == $iWholesalePackage) {
						// The package matches the full service wholesale plan for the services current rate plan. It'a a full service disconnection notification.
						$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] It's a FULL SERVICE - DISCONNECTION notification (Wholesale Package: {$iWholesalePackage})");
						$aResponse['Type'] = PROVISIONING_TYPE_DISCONNECT_FULL;
					} else if ($oCurrentRatePlan->preselection_wholesale_plan == $iWholesalePackage) {
						// The package matches the preselection wholesale plan for the services current rate plan. It'a a preselection disconnection notification.
						$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] It's a PRESELECTION - DISCONNECTION notification (Wholesale Package: {$iWholesalePackage})");
						$aResponse['Type'] = PROVISIONING_TYPE_DISCONNECT_PRESELECT;
					}
					break;
			}

			// If a provisioning_type matching the notification type was found, use it to describe the response
			if ($aResponse['Type']) {
				$sDescription = "Notification: ".Constant_Group::getConstantGroup('provisioning_type')->getConstantDescription($aResponse['Type']);
			} else {
				$sDescription = "No Provisioning Type could be found for the Notification Type ({$iNotificationType})";
			}
		} else {
			// No notification type, can't determine a provisioning type but we may be able to give it a meaningful description
			$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] No notificaiton type or no owner, can't determine a provisioning type");
			$sStatus = $aData['Status'];
			$sStatusResult = $aData['StatusResult'];
			if ($sStatus !== null) {
				// A status change notification
				$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] It's a status change notification (Status: {$sStatus}; Status Result: {$sStatusResult})");
				if (isset(self::$_aResponseDescriptions[$sStatus]) && isset(self::$_aResponseDescriptions[$sStatus][$sStatusResult])) {
					// A supported status change notification
					$sDescription = self::$_aResponseDescriptions[$sStatus][$sStatusResult];
					$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] Supported Status Change: {$sDescription}");
				} else {
					// Unsupported status change
					$aResponse['Status'] = RESPONSE_STATUS_CANT_NORMALISE;
					$sDescription = "Unsupported status and status result for notification (Status: {$sStatus}; Status Result: {$sStatusResult})";
					$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] Unsupported Status Change");
				}
			} else {
				// Not a status change, but no notification type, very strange
				$aResponse['Status'] = RESPONSE_STATUS_CANT_NORMALISE;
				$sDescription = "Invalid data. No Notification Type or Status change.";
				$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] It's not a status change, this should not happen");
			}
		}

		$aResponse['Description'] = $sDescription;
 	}

 	protected function _SplitLine($sLine) {
 		$sLineType = substr($sLine, 0, 1);
 		switch ($sLineType) {
 			case self::LINE_TYPE_RESPONSE:
 				$this->_arrDefine = $this->_aDefineResponse;
 				break;
 			case self::LINE_TYPE_NOTIFICATION:
 				$this->_arrDefine = $this->_aDefineNotification;
 				break;
 		}

 		return parent::_SplitLine($sLine);
 	}

 	private static function _logArray($aArray) {
 		foreach ($aArray as $sKey => $mValue) {
 			Log::get()->logIf(self::DEBUG_LOGGING, "\t\t{$sKey} => {$mValue}");
 		}
 	}
}

?>
