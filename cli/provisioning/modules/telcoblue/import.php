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
		$this->_arrModuleConfig['ChurnNotificationType']['Type'] = DATA_TYPE_INTEGER;
		$this->_arrModuleConfig['ChurnNotificationType']['Description'] = "Wholesale Notification Type - Churn";

		$this->_arrModuleConfig['ReverseChurnNotificationType']['Type'] = DATA_TYPE_INTEGER;
		$this->_arrModuleConfig['ReverseChurnNotificationType']['Description'] = "Wholesale Notification Type - Reverse Churn";

		$this->_arrModuleConfig['ChurnAwayNotificationType']['Type'] = DATA_TYPE_INTEGER;
		$this->_arrModuleConfig['ChurnAwayNotificationType']['Description'] = "Wholesale Notification Type - Churn Away";

		$this->_arrModuleConfig['DisconnectionNotificationType']['Type'] = DATA_TYPE_INTEGER;
		$this->_arrModuleConfig['DisconnectionNotificationType']['Description'] = "Wholesale Notification Type - Disconnection";

		$this->_arrModuleConfig['WholesaleIdentifierContextFullService']['Type'] = DATA_TYPE_INTEGER;
		$this->_arrModuleConfig['WholesaleIdentifierContextFullService']['Description'] = "Wholesale Product Identifier Context that represents Full Service Landlines";

		$this->_arrModuleConfig['WholesaleIdentifierContextPreselection']['Type'] = DATA_TYPE_INTEGER;
		$this->_arrModuleConfig['WholesaleIdentifierContextPreselection']['Description'] = "Wholesale Product Identifier Context that represents Preselection Landlines";

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
			'NotificationTimestamp' => array('Index' => 7),
			'EffectiveTimestamp' => array('Index' => 8),
			'Description' => array('Index' => 9)
		);

		$this->_aDefineResponse = array(
			'LineType' => array('Index' => 0),
			'ClientReference' => array('Index' => 1),
			'Subject' => array('Index' => 2),
			'Action' => array('Index' => 3),
			'Detail' => array('Index' => 4),
			'Status' => array('Index' => 5),
			'ModifiedTimestamp' => array('Index' => 6),
			'EffectiveTimestamp' => array('Index' => 7),
			'Notifications' => array('Index' => 8),
			'Package' => array('Index' => 9),
			'IdentifierContext' => array('Index' => 10)
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

		// All line types have an effective timestamp (it may be overriden depending on the data)
		$aResponse['EffectiveDate'] = date("Y-m-d H:i:s", strtotime($aData['EffectiveTimestamp']));

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
		$aNotifications = self::_getResponseNotifications($aData);
		$oRequest = null;
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
				// Failed to locate a provisioning request, this shouldn't happen but if it does act as though it wasn't request by flex
				$oRequest = null;
			}
		}

		$bRequested = ($oRequest !== null);
		if (!$bRequested) {
			// No client reference, not requested by flex
			Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t[*] The Response has no client_reference");
			$aResponse['FNN'] = $aData['Detail'];
			$aResponse = $this->FindFNNOwner($aResponse, self::DEBUG_LOGGING);
			$aResponse['Type'] = $this->_getProvisioningTypeForUnrequestedResponse($aResponse, $aData, $aNotifications);
			$aResponse['Request'] = null;
			$sUnrequestedChangeDescription = $this->_getDescriptionForUnrequestedResponse($aData);
		}

		if (!isset($aResponse['Service'])) {
			// Find the service details
			$aResponse = $this->FindFNNOwner($aResponse, self::DEBUG_LOGGING);
		}

		// Build the provisioning response row data
		$aResponse['CarrierRef'] = null;
		$aResponse['Status'] = null;

		// Request status and description depend on the status of the response
		$iRequestStatusId = null;
		switch ($aData['Status']) {
			case 'PENDING':
				$iRequestStatusId = REQUEST_STATUS_DELIVERED;

				// Set the effective date to that of the request
				if ($bRequested) {
					$sDescription = "Request is still being processed";
					$aResponse['EffectiveDate'] = $oRequest->SentOn;
				} else {
					// Not requested
					$sDescription = "Unrequested change is being processed: {$sUnrequestedChangeDescription}";
				}
				break;
			case 'COMPLETE':
			case 'COMPLETE_NO_CHANGE':
				$iRequestStatusId = REQUEST_STATUS_COMPLETED;
				if ($bRequested) {
					$sDescription = "Request has been completed";
				} else {
					$sDescription = "Unrequested change has been completed: {$sUnrequestedChangeDescription}";
				}
				break;
			case 'FAILED':
				$iRequestStatusId = REQUEST_STATUS_REJECTED;

				// Set the effective date to that of the request
				if ($bRequested) {
					$sDescription = "Request was unable to be completed";
				} else {
					$sDescription = "Unrequested change was unable to be completed: {$sUnrequestedChangeDescription}";
				}
				break;
			default:
				$iRequestStatusId = REQUEST_STATUS_DELIVERED;
				$aResponse['Status'] = RESPONSE_STATUS_CANT_NORMALISE;
				$sDescription = "Unable to determine the status from response data";

				// Set the effective date to that of the request
				if ($bRequested) {
					$aResponse['EffectiveDate'] = $oRequest->SentOn;
				}
		}

		// Add notifications descriptions to the description
		if (!empty($aNotifications)) {
			Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t[*] Got ".count($aNotifications)." Notifications");
			$aNotificationStrings = array();
			foreach ($aNotifications as $oNotification) {
				Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t\t[*] Notification: {$oNotification->description}");
				$sNotificationTimestamp = date('d/m/Y H:i:s', strtotime($oNotification->effective_timestamp !== null ? $oNotification->effective_timestamp : $oNotification->notification_timestamp));
				$aNotificationStrings[] = "{$oNotification->description} ({$sNotificationTimestamp})";
			}
			$sDescription .= ". ".implode('. ', $aNotificationStrings);
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
		$aResponse['Status'] = null;
		$aResponse['request_status'] = REQUEST_STATUS_COMPLETED;

		// Find the service details
		$aResponse = $this->FindFNNOwner($aResponse, self::DEBUG_LOGGING);
		if (isset($aResponse['Service'])) {
			// Fetch the current rate plan of the service
			$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] Owner Service: {$aResponse['Service']}");
		} else {
			$oLog->logIf(self::DEBUG_LOGGING, "\t\t[*] No Owner Service found");
		}

		// Convert the notification type to a provisioning type (if a notification type is supplied)
		$iNotificationType = (int)$aData['NotificationType'];
		if ($iNotificationType !== null) {
			// A notification type is present, determine the corresponding Flex provisioning type
			$iIdentifierContext = (int)$aData['IdentifierContext'];
			$aResponse['Type'] = $this->_getProvisioningTypeForNotification($iNotificationType, $iIdentifierContext);

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

	private function _getProvisioningTypeForNotification($iNotificationType, $iIdentifierContext) {
		$iChurnAway = $this->GetConfigField('ChurnAwayNotificationType');
		$iDisconnection = $this->GetConfigField('DisconnectionNotificationType');
		$iIdentifierContextFullService = $this->GetConfigField('WholesaleIdentifierContextFullService');
		$iIdentifierContextPreselection = $this->GetConfigField('WholesaleIdentifierContextPreselection');

		Log::get()->logIf(self::DEBUG_LOGGING, "\t\t[*] An owner was found and notification type returned ({$iNotificationType}), getting associated provisioning type (Churn Away: {$iChurnAway}; Disconnection: {$iDisconnection})");

		// Define provisioning type mapping info
		$aProvisioningTypes = array(
			$iChurnAway => array(
				$iIdentifierContextFullService => PROVISIONING_TYPE_LOSS_FULL,
				$iIdentifierContextPreselection => PROVISIONING_TYPE_LOSS_PRESELECT
			),
			$iDisconnection => array(
				$iIdentifierContextFullService => PROVISIONING_TYPE_DISCONNECT_FULL,
				$iIdentifierContextPreselection => PROVISIONING_TYPE_DISCONNECT_PRESELECT
			)
		);

		if (isset($aProvisioningTypes[$iNotificationType]) && isset($aProvisioningTypes[$iNotificationType][$iIdentifierContext])) {
			$iProvisioningType = $aProvisioningTypes[$iNotificationType][$iIdentifierContext];
			Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t[*] Found provisioning type: {$iProvisioningType}");
			return $iProvisioningType;
		} else {
			Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t[*] No provisioning type found (Notification Type: {$iNotificationType}; Identifier Context: {$iIdentifierContext})");
			return null;
		}
	}

	private function _getProvisioningTypeForUnrequestedResponse($aResponse, $aData, $aNotifications) {
		// NOTE: This function looks at the notifications that were returned along with a response and checks the notification types of each (along with the identifier context of the service)
		// It then determines the Flex provisioning type that is appropriate (currently it stops looking once it finds a match)
		Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t\t[*] Trying to determine the provisioning_type");
		if ($aNotifications && !empty($aNotifications)) {
			$iChurn = $this->GetConfigField('ChurnNotificationType');
			$iReverseChurn = $this->GetConfigField('ReverseChurnNotificationType');
			$iIdentifierContextFullService = $this->GetConfigField('WholesaleIdentifierContextFullService');
			$iIdentifierContextPreselection = $this->GetConfigField('WholesaleIdentifierContextPreselection');
			$iIdentifierContext = (int)$aData['IdentifierContext'];

			// Define provisioning type mapping info
			$aProvisioningTypes = array(
				$iChurn => array(
					$iIdentifierContextFullService => PROVISIONING_TYPE_FULL_SERVICE,
					$iIdentifierContextPreselection => PROVISIONING_TYPE_PRESELECTION
				),
				$iReverseChurn => array(
					$iIdentifierContextFullService => PROVISIONING_TYPE_FULL_SERVICE_REVERSE,
					$iIdentifierContextPreselection => PROVISIONING_TYPE_PRESELECTION_REVERSE
				)
			);

			// Check notifications until a provisioning type is found
			foreach ($aNotifications as $oNotification) {
				if (isset($aProvisioningTypes[$oNotification->notification_type]) && isset($aProvisioningTypes[$oNotification->notification_type][$iIdentifierContext])) {
					$iProvisioningType = $aProvisioningTypes[$oNotification->notification_type][$iIdentifierContext];
					Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t\t\t[*] Found provisioning type: {$iProvisioningType}");
					return $iProvisioningType;
				} else {
					Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t\t\t[*] No provisioning type found (Notification Type: {$oNotification->notification_type}; Identifier Context: {$iIdentifierContext})");
				}
			}
		} else {
			Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t\t\t[*] Can't determine provisioning type, no notifications present in data");
		}

		Log::get()->logIf(self::DEBUG_LOGGING, "\t\t\t\t[*] No provisioning type found in the data");
		return null;
	}

	private function _getDescriptionForUnrequestedResponse($aData) {
		$iIdentifierContextFullService = $this->GetConfigField('WholesaleIdentifierContextFullService');
		$iIdentifierContextPreselection = $this->GetConfigField('WholesaleIdentifierContextPreselection');
		$aDescriptions = array(
			'ADD' => array(
				$iIdentifierContextFullService => "Adding Full Service",
				$iIdentifierContextPreselection => "Adding Preselection"
			),
			'REMOVE' => array(
				$iIdentifierContextFullService => "Removing Full Service",
				$iIdentifierContextPreselection => "Removing Preselection"
			)
		);

		$sAction = $aData['Action'];
		$iIdentifierContext = (int)$aData['IdentifierContext'];
		if (isset($aDescriptions[$sAction]) && isset($aDescriptions[$sAction][$iIdentifierContext])) {
			return $aDescriptions[$sAction][$iIdentifierContext];
		}

		return 'Unknown';
	}

	private static function _logArray($aArray) {
		foreach ($aArray as $sKey => $mValue) {
			Log::get()->logIf(self::DEBUG_LOGGING, "\t\t{$sKey} => {$mValue}");
		}
	}

	private static function _getResponseNotifications($aData) {
		$sNotifications = $aData['Notifications'];

		// Convert ; to ,
		$sNotifications = preg_replace('/;/', ',', $sNotifications);

		// Remove surrounding quotes
		$sNotifications = preg_replace('/^"|"$/', '', $sNotifications);

		// Unescape backslashes & quotes
		$sNotifications = preg_replace('/(\\\)(\\\|")/', '$2', $sNotifications);

		return JSON_Services::decode($sNotifications);
	}

	public function LinkToRequest($aNormalised) {
		// This is done elsewhere. This override is simply to prevent Flex from applying the default resolution
		return null;
	}

	private static $_aEndStates = array(REQUEST_STATUS_COMPLETED, REQUEST_STATUS_REJECTED);
	public function updateRequestStatus($aResponse, $aRequest) {
		Log::get()->log("\tResolving Response @ {$aResponse['EffectiveDate']} against Request Last Updated @ {$aRequest['LastUpdated']}");
		if (in_array($aRequest['Status'], self::$_aEndStates)) {
			// Requests that are already at an end state don't get updated
			Log::get()->log("\t\tRequest #{$aRequest['Id']} is already at end-state {$aRequest['Status']}");
			return;
		}

		if (!in_array($aResponse['request_status'], self::$_aEndStates)) {
			if (!(strtotime($aRequest['LastUpdated']) < strtotime($aResponse['EffectiveDate']))) {
				// If the Response isn't an end state (i.e. "pending"), and isn't newer than the last update, don't update the Request
				Log::get()->log("\t\tResponse #{$aResponse['Id']} is for a non-end-state {$aResponse['Status']} and is not newer ({$aResponse['EffectiveDate']}) than Request @ {$aRequest['LastUpdated']}");
				return;
			}
		}

		// All other cases, update the Request
		Log::get()->log("\t\tUpdating Request #{$aResponse['Request']} with Response #{$aResponse['Id']} ({$aResponse['request_status']}: {$aResponse['Description']}) @ {$aResponse['EffectiveDate']}");
		//Log::get()->log(var_dump($aResponse, true));
		Query::run('
			UPDATE ProvisioningRequest
			SET Response = <Id>,
				LastUpdated = <EffectiveDate>,
				Status = <request_status>,
				Description = <Description>
			WHERE Id = <Request>
		', $aResponse);
		//Log::get()->log("\t\t\tUpdated " . DataAccess::get()->refMysqliConnection->affected_rows . ": " . var_dump(Query::run('SELECT * FROM ProvisioningRequest WHERE Id = <Id>', $aRequest)->fetch_assoc(), true));
	}
}