<?php

 class ExportTelcoBlue extends ExportBase {
 	const API_AUTHENTICATION_HEADER = 'API_AUTHENTICATION';
 	const PACKAGE_FLAG_AUXILIARY = 'AUXILIARY';

 	public $intBaseFileType = RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_TELCOBLUE;
	public $_strDeliveryType;
	public $_intFrequencyType = FREQUENCY_SECOND;
	public $_intFrequency = 1;
	public $_intEarliestDelivery = 0;

	protected $_arrFileContent;
	protected $_arrDefine;
	protected $_arrFilename;
	protected $_arrHeader;
	protected $_arrFooter;
	protected $_ptrFile;

	private $_aData = array();
	private $_aExportedRequestIds = array();

	// Maps service types to wholesale identifier contexts (the config property that stores the identifier context)
 	private static $_aServiceIdentifierContextConfigProperties = array(
 		SERVICE_TYPE_ADSL => array(),
 		SERVICE_TYPE_MOBILE => array(
 			PROVISIONING_TYPE_FULL_SERVICE => 'WholesaleIdentifierContextMobile',
	 		PROVISIONING_TYPE_FULL_SERVICE_PLAN_CHANGE => 'WholesaleIdentifierContextMobile',
	 		PROVISIONING_TYPE_FULL_SERVICE_REVERSE => 'WholesaleIdentifierContextMobile'
 		),
 		SERVICE_TYPE_LAND_LINE => array(
 			PROVISIONING_TYPE_FULL_SERVICE => 'WholesaleIdentifierContextFullService',
	 		PROVISIONING_TYPE_PRESELECTION => 'WholesaleIdentifierContextPreselection',
	 		PROVISIONING_TYPE_FULL_SERVICE_PLAN_CHANGE => 'WholesaleIdentifierContextFullService',
	 		PROVISIONING_TYPE_PRESELECTION_PLAN_CHANGE => 'WholesaleIdentifierContextPreselection',
	 		PROVISIONING_TYPE_FULL_SERVICE_REVERSE => 'WholesaleIdentifierContextFullService',
	 		PROVISIONING_TYPE_PRESELECTION_REVERSE => 'WholesaleIdentifierContextPreselection',
 		),
 		SERVICE_TYPE_INBOUND => array(),
 		SERVICE_TYPE_DIALUP => array()
 	);
	
	function __construct($iCarrierId) {
 		// Parent Constructor
 		parent::__construct($iCarrierId);
 		
 		// Module Description
 		$this->strDescription = "Telco Blue Provisioning Export";
 		
 		 // Module Configuration
		$this->_arrModuleConfig['APIURL']['Type'] = DATA_TYPE_STRING;
		$this->_arrModuleConfig['APIURL']['Description'] = "URL of the API to connect to";

 		$this->_arrModuleConfig['APIPort']['Type'] = DATA_TYPE_INTEGER;
		$this->_arrModuleConfig['APIPort']['Description'] = "Port to connect to on API Server";

 		$this->_arrModuleConfig['APIPassword']['Type'] = DATA_TYPE_STRING;
		$this->_arrModuleConfig['APIPassword']['Description'] = "Password with which to authenticate with the API";
 		
 		$this->_arrModuleConfig['WholesaleAccountId']['Type'] = DATA_TYPE_INTEGER;
		$this->_arrModuleConfig['WholesaleAccountId']['Description'] = "Wholesale Account Id of this system";

		$this->_arrModuleConfig['WholesaleIdentifierContextFullService']['Type'] = DATA_TYPE_INTEGER;
		$this->_arrModuleConfig['WholesaleIdentifierContextFullService']['Description'] = "Wholesale Product Identifier Context that represents Full Service Landlines";
		$this->_arrModuleConfig['WholesaleIdentifierContextPreselection']['Type'] = DATA_TYPE_INTEGER;
		$this->_arrModuleConfig['WholesaleIdentifierContextPreselection']['Description'] = "Wholesale Product Identifier Context that represents Preselection Landlines";

		$this->_arrModuleConfig['WholesaleIdentifierContextMobile']['Type'] = DATA_TYPE_INTEGER;
		$this->_arrModuleConfig['WholesaleIdentifierContextMobile']['Description'] = "Wholesale Product Identifier Context that represents Mobiles";
 		
		// Define File Format, Delimiter & New Line (all not necessary for this module but the variables need to exist for parent class functionality)
 		$this->_strFileFormat = null;
 		$this->_strDelimiter = null;
 		$this->_strNewLine = null;
 		$this->_arrDefine = array();
 		$this->_iTimestamp = time();
 		$this->_intMinRequests = 0;

 		// These are setup in an empty way to satisfy parent class functionality which
 		// expects this module to generate a file... which it doesn't.
		$this->_arrDefine['Header']	= null;
 		$this->_arrDefine['Footer'] = null;

 		// Accepted provisioning types
 		$this->_arrDefine[PROVISIONING_TYPE_FULL_SERVICE] = array();
 		$this->_arrDefine[PROVISIONING_TYPE_PRESELECTION] = array();
 		$this->_arrDefine[PROVISIONING_TYPE_FULL_SERVICE_PLAN_CHANGE] = array();
 		$this->_arrDefine[PROVISIONING_TYPE_PRESELECTION_PLAN_CHANGE] = array();
 		$this->_arrDefine[PROVISIONING_TYPE_FULL_SERVICE_REVERSE] = array();
 		$this->_arrDefine[PROVISIONING_TYPE_PRESELECTION_REVERSE] = array();

 		// No file content to be generated by this module
 		$this->_arrFileContent = array();
 	}
 	
	// Output: Exports a ProvisioningRequest Record to a format accepted by the Carrier
 	function Output($aRequest) {
 		try {
	 		// Modifications to the request record
	 		$aRequest['CarrierRef']	= $aRequest['Id'];

 			// Fetch service and rate plan info for the request
	 		$oService = Service::getForId($aRequest['Service']);

	 		// The identifier is the services fnn
	 		$sIdentifier = $oService->FNN;

	 		// The wholesale identifier type is derived
	 		$iRequestType = $aRequest['Type'];
	 		$iServiceIdentifierContext = $this->_getServiceIdentifierContext($oService->ServiceType, $iRequestType);

	 		// Create address data if necessary
	 		$oServiceAddress = $oService->getServiceAddress();
	 		$aAddress = null;
			if ($oServiceAddress !== null) {
				// Address is not required
				/*$aAddress = array(
					'address_type' => $oServiceAddress->ServiceAddressType,
					'address_type_number' => $oServiceAddress->ServiceAddressTypeNumber,
					'address_type_suffix' => $oServiceAddress->ServiceAddressTypeSuffix,
					'street_number_start' => $oServiceAddress->ServiceStreetNumberStart,
					'street_number_end'	=> $oServiceAddress->ServiceStreetNumberEnd,
					'street_number_suffix' => $oServiceAddress->ServiceStreetNumberSuffix,
					'street_name' => $oServiceAddress->ServiceStreetName,
					'street_type' => $oServiceAddress->ServiceStreetType,
					'street_type_suffix' => $oServiceAddress->ServiceStreetTypeSuffix,
					'property_name' => $oServiceAddress->ServicePropertyName,
					'locality' => $oServiceAddress->ServiceLocality,
					'state' => $oServiceAddress->ServiceState,
					'postcode' => $oServiceAddress->ServicePostcode
				);*/
			}

	 		// Fetch the rate plan
	 		$oRatePlan = Rate_Plan::getForId($oService->getCurrentServiceRatePlan()->RatePlan);

	 		// Validate the wholesale plan and build the request data based on the provisioning type
	 		$sNow = DataAccess::getDataAccess()->getNow();
	 		$aData = array('client_reference' => $aRequest['Id']);
	 		$bHasFullServicePackage = !empty($oRatePlan->fullservice_wholesale_plan) && ($oRatePlan->fullservice_wholesale_plan !== null);
	 		$bHasPreSelectionPackage = !empty($oRatePlan->preselection_wholesale_plan) && ($oRatePlan->preselection_wholesale_plan !== null);
	 		$sDescription = '';
	 		switch ($iRequestType) {
	 			case PROVISIONING_TYPE_FULL_SERVICE:
			 		if (!$bHasFullServicePackage) {
			 			throw new Exception("The rate plan for the requested service does not have a fullservice_wholesale_plan");
			 		}

					$oPackageData = self::_parseWholesalePlan($oRatePlan->fullservice_wholesale_plan);
			 		$aData = array_merge($aData, array(
						'package' => $oPackageData->iPackage,
						'identifier' => $sIdentifier,
						'identifier_context' => $iServiceIdentifierContext,
						'address' => $aAddress,
						'ca_signed_date' => $aRequest['AuthorisationDate']
					), $oPackageData->aFlags);

			 		$sURL = "/services";
			 		$aExtraHeaders = array();
			 		$sDescription = 'Full Service Add Service';
	 				break;
	 			case PROVISIONING_TYPE_PRESELECTION:
		 			if (!$bHasPreSelectionPackage) {
			 			throw new Exception("The rate plan for the requested service does not have a preselection_wholesale_plan");
			 		}

			 		$oPackageData = self::_parseWholesalePlan($oRatePlan->preselection_wholesale_plan);
			 		$aData = array_merge($aData, array(
						'package' => $oPackageData->iPackage,
						'identifier' => $sIdentifier,
						'identifier_context' => $iServiceIdentifierContext,
						'address' => $aAddress,
						'ca_signed_date' => $aRequest['AuthorisationDate']
					), $oPackageData->aFlags);

			 		$sURL = "/services";
			 		$aExtraHeaders = array();
			 		$sDescription = 'Pre-Selection Add Service';
	 				break;
	 			case PROVISIONING_TYPE_FULL_SERVICE_PLAN_CHANGE:
	 				if (!$bHasFullServicePackage) {
			 			throw new Exception("The rate plan for the requested service does not have a fullservice_wholesale_plan");
			 		}

			 		$oPackageData = self::_parseWholesalePlan($oRatePlan->fullservice_wholesale_plan);
			 		$aData = array_merge($aData, array(
						'package' => $oPackageData->iPackage,
						'address' => $aAddress,
						'ca_signed_date' => $aRequest['AuthorisationDate']
					), $oPackageData->aFlags);

			 		$sURL = "/services/{$iServiceIdentifierContext}:{$sIdentifier}/package";
			 		$aExtraHeaders = array("OVERRIDE_METHOD: PUT");
			 		$sDescription = 'Full Service Plan Change';
	 				break;
	 			case PROVISIONING_TYPE_PRESELECTION_PLAN_CHANGE:
			 		if (!$bHasPreSelectionPackage) {
			 			throw new Exception("The rate plan for the requested service does not have a preselection_wholesale_plan");
			 		}

			 		$oPackageData = self::_parseWholesalePlan($oRatePlan->preselection_wholesale_plan);
			 		$aData = array_merge($aData, array(
						'package' => $oPackageData->iPackage,
						'address' => $aAddress,
						'ca_signed_date' => $aRequest['AuthorisationDate']
					), $oPackageData->aFlags);

			 		$sURL = "/services/{$iServiceIdentifierContext}:{$sIdentifier}/package";
			 		$aExtraHeaders = array("OVERRIDE_METHOD: PUT");
			 		$sDescription = 'Pre-Selection Plan Change';
	 				break;
	 			case PROVISIONING_TYPE_FULL_SERVICE_REVERSE:
	 				if (!$bHasFullServicePackage) {
			 			throw new Exception("The rate plan for the requested service does not have a fullservice_wholesale_plan");
			 		}

	 				$sURL = "/services/{$iServiceIdentifierContext}:{$sIdentifier}";
	 				$aExtraHeaders = array("OVERRIDE_METHOD: DELETE");
	 				$sDescription = 'Full Service Remove Service';
	 				break;	 			
	 			case PROVISIONING_TYPE_PRESELECTION_REVERSE:
	 				if (!$bHasPreSelectionPackage) {
			 			throw new Exception("The rate plan for the requested service does not have a preselection_wholesale_plan");
			 		}
			 		
	 				$sURL = "/services/{$iServiceIdentifierContext}:{$sIdentifier}";
	 				$aExtraHeaders = array("OVERRIDE_METHOD: DELETE");
	 				$sDescription = 'Pre-Selection Remove Service';
	 				break;
	 		}

	 		$aRequest['Description'] = $sDescription;

	 		// Make the request, Using curl to make a request to the Telco Blue wholesale reseller api
	 		try {
	 			$aRequest['SentOn']	= $sNow;
	 			$this->_APIRequest($sURL, $aData, $aExtraHeaders);
	 			$aRequest['Status'] = REQUEST_STATUS_EXPORTING;
	 		} catch (ExportTelcoBlue_Exception_APICommunication $oEx) {
	 			// Failed to communicate with the api, try again later
	 			$aRequest['Status']	= REQUEST_STATUS_WAITING;
	 			$aRequest['Description'] = "An error occurred communicating with the API";
	 		} catch (Exception $oEx) {
	 			$aRequest['Status'] = REQUEST_STATUS_REJECTED;
	 			$aRequest['Description'] = $oEx->getMessage();
	 		}
	 	} catch (Exception $oEx) {
	 		// Something failed, return as such
	 		$aRequest['Status']	= REQUEST_STATUS_WAITING;
	 		$aRequest['Description'] = $oEx->getMessage();
	 	}

	 	// Cache, for delivery
	 	$this->_aExportedRequestIds[] = $aRequest['Id'];
 		
	 	// Return the modified Request
	 	return $aRequest;
 	}
 	
 	private static function _parseWholesalePlan($sWholesalePackage) {
 		// Extract the package and any special ordering flags that have been supplied
		preg_match('/(\d+)\s?(\(([;A-Z_-]+)\))?/', $sWholesalePackage, $aPackageParts);
		$iPackage = (int)$aPackageParts[1];
		$aFlags = array();
		if (isset($aPackageParts[3])) {
			$aOptions = explode(';', $aPackageParts[3]);
			foreach ($aOptions as $sOption) {
				switch ($sOption) {
					case self::PACKAGE_FLAG_AUXILIARY:
						$aFlags['is_auxiliary'] = true;
						break;
				}
			}
		}
		
		return (object)array(
			'iPackage' => $iPackage,
			'aFlags' => $aFlags
		);
	}

 	private function _getServiceIdentifierContext($iServiceType, $iRequestType) {
 		if (!isset(self::$_aServiceIdentifierContextConfigProperties[$iServiceType]) || !isset(self::$_aServiceIdentifierContextConfigProperties[$iServiceType][$iRequestType])) {
 			throw new Exception("Cannot find service identifier context (Service Type: {$iServiceType}; Provisioning Type: {$iRequestType})");
 		}

 		$sConfigProperty = self::$_aServiceIdentifierContextConfigProperties[$iServiceType][$iRequestType];
 		return $this->GetConfigField($sConfigProperty);
 	}

	// _Render: (Override) Renders this file to its final output format. No output file so nothing required
 	protected function _Render($bRenderToFile=true) {
 		return Array('Pass' => TRUE);
 	}

 	// _UpdateDB: (Override) No file export record needs to be created
 	protected function _UpdateDB() {
 		return array('Pass' => true, 'Description' => "UpdateDB() Successful");
 	}

 	// _Deliver: (Override) No delivery required
 	protected function _Deliver() {
 		// Mark each request as delivered
 		foreach ($this->_aExportedRequestIds as $iId) {
 			$oRequest = new Provisioning_Request(array('Id' => $iId), true);
 			$oRequest->Status = REQUEST_STATUS_DELIVERED;
 			$oRequest->SentOn = DataAccess::getDataAccess()->getNow();
 			$oRequest->save();
 		}

 		return array('Pass' => true, 'Description' => "No delivery required");
 	}

 	private function _APIRequest($sURL, $aData, $aExtraHeaders=array()) {
 		// Build the url, e.g. /services
 		$sURL = $this->GetConfigField('APIURL').$sURL;

 		// Setup the curl request (a POST request)
 		$oCURL = new CURL();
		$oCURL->URL = $sURL;
		$oCURL->HEADER = 1;
		$oCURL->RETURNTRANSFER = true;
		$oCURL->SSL_VERIFYPEER = false;
		$oCURL->FOLLOWLOCATION = 1;
		$oCURL->TIMEOUT = 40;
		$oCURL->PORT = $this->GetConfigField('APIPort');
		$oCURL->POST = 1;

		// Add the post data
		$oCURL->POSTFIELDS = rawurlencode(JSON_Services::encode($aData));
		
		// Add headers, including api authentication header
		$iWholesaleAccountId = $this->GetConfigField('WholesaleAccountId');
		$sAPIPassword = sha1($this->GetConfigField('APIPassword'));
		$aHeaders = array(
			"Content-Type: application/json;",
			self::API_AUTHENTICATION_HEADER.": ".sha1("{$iWholesaleAccountId};{$sAPIPassword}")
		);

		foreach ($aExtraHeaders as $sHeader) {
			$aHeaders[] = $sHeader;
		}

		$oCURL->HTTPHEADER = $aHeaders;

 		// Send the request
		try {
			$sResponse = $oCURL->execute();	
		} catch (Exception $oEx) {
			throw new ExportTelcoBlue_Exception_APICommunication($oEx->getMessage(), "URL: {$sURL}");
		}

		// Process the response, look for errors
		$aResponseParts = explode("\r\n\r\n", $sResponse);
		if (empty($aResponseParts)) {
			throw new ExportTelcoBlue_Exception_APICommunication("Unexpected response from API", "Response: {$sResponse}");
		}

		$iBodyPartIndex = count($aResponseParts) - 1;
		if (isset($aResponseParts[$iBodyPartIndex - 1])) {
			// A header was returned in the response
			$aHeader = explode("\n", $aResponseParts[$iBodyPartIndex - 1]);
			$aStatus = explode(" ", $aHeader[0]);
			$iResponseStatus = (int)$aStatus[1];
		} else {
			// No header, weird response
			throw new ExportTelcoBlue_Exception_APICommunication("Unexpected response from API, no HTTP header", "Response: {$sResponse}");
		}
		
		$sBody = $aResponseParts[$iBodyPartIndex];
		$mBody = JSON_Services::decode($sBody);
		$bInvalidJSONBody = ($mBody === null && trim($sBody) != 'null');
		switch ($iResponseStatus) {
			case 400: // BAD_REQUEST
			case 404: // NOT_FOUND
			case 405: // METHOD_NOT_ALLOWED
			case 500: // SERVER_ERROR
			case 501: // NOT_IMPLEMENTED
				if (!$bInvalidJSONBody && property_exists($mBody, 'oException') && property_exists($mBody->oException, 'sMessage')) {
					// Error passed back from API
					throw new Exception("API Error: {$mBody->oException->sMessage}");
				} else {
					// Other Http error
					throw new ExportTelcoBlue_Exception_APICommunication("Error response from API. HTTP Status: {$iResponseStatus}; URL: '{$sURL}'", "Response: '{$sBody}'");
				}
				break;
		}
		
		if ($bInvalidJSONBody) {
			// JSON decode error
			throw new ExportTelcoBlue_Exception_APICommunication("Invalid JSON response from API. URL: '{$sURL}'", "Response: '{$sBody}'");
		}
 	}
}

class ExportTelcoBlue_Exception_APICommunication extends Exception_Assertion {
	public function __construct($sMessage, $sExtraDetails=null, $sAssertionName=null) {
		parent::__construct($sMessage, "Telcoblue Wholesale Provisioning Export - API Communication Error\n{$sExtraDetails}", null);
	}
}

?>