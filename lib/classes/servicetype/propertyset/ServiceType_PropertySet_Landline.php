<?php
class ServiceType_PropertySet_Landline {
	private $_orm;

	private function __construct(Service_Address $serviceAddressORM) {
		$this->_orm = $serviceAddressORM;
	}

	public static function getForService($serviceId) {
		try {
			// Look for existing ServiceAddress data
			$instance = new ServiceType_PropertySet_Landline(Service_Address::getForService($serviceId));
		} catch (Service_Address_Exception_NoneForService $exception) {
			// Non existing ServiceAddress data
			$instance = new ServiceType_PropertySet_Landline(new Service_Address(array(
				'Service' => $serviceId
			)));
		}
		return $instance;
	}

	public function save() {
		// Validate
		// TODO

		$this->_orm->save();
	}

	private function _autofillAccountDetails() {
		$account = Account::getForId(Service::getForId($this->Service)->Account);
		$primaryContact = $account->getPrimaryContact();

		$this->AccountGroup = $account->AccountGroup;
		$this->Account = $account->Id;

		$this->BillName = $account->BusinessName;
		$this->BillAddress1 = $account->Address1;
		$this->BillAddress2 = $account->Address2;
		$this->BillLocality = $account->Suburb;
		$this->BillPostcode = $account->Postcode;

		if ($this->Residential) {
			// Residential/Private Service
			$this->EndUserTitle = $primaryContact->Title;
			$this->EndUserGivenName = $primaryContact->FirstName;
			$this->EndUserFamilyName = $primaryContact->LastName;
			$this->DateOfBirth = $primaryContact->DOB;

			$this->EndUserCompanyName = null;
			$this->ABN = null;
		} else {
			// Business Service
			$this->EndUserCompanyName = $account->BusinessName;
			$this->ABN = $account->ABN;

			$this->EndUserTitle = null;
			$this->EndUserGivenName = null;
			$this->EndUserFamilyName = null;
			$this->DateOfBirth = null;
		}
		$this->Employer = null;
		$this->Occupation = null;
	}

	private function _validateStandardAddress() {
		// Address Type & friends
		if ($this->ServiceAddressType) {
			// ServiceAddressType
			if (!array_key_exists($this->ServiceAddressType, $GLOBALS['*arrConstant']['ServiceAddrType'])) {
				throw new Exception_Validation(sprintf('Invalid Service Address Type (%s)', $this->ServiceAddressType));
			}

			// ServiceAddressTypeNumber
			if (!$this->ServiceAddressTypeNumber) {
				if (!preg_match('/^[1-9]\d{0,4}$/', $this->ServiceAddressTypeNumber)) {
					throw new Exception_Validation(sprintf('Missing or invalid Service Address Type Number (%s) for Service Address Type (%s)', $this->ServiceAddressTypeNumber, $this->ServiceAddressType));
				}
			} else {
				$this->ServiceAddressTypeNumber = null;
			}

			// ServiceAddressTypeSuffix
			if ($this->ServiceAddressTypeSuffix) {
				if (!preg_match('/^[a-z]{1,2}$/i', $this->ServiceAddressTypeSuffix)) {
					throw new Exception_Validation(sprintf('Invalid Service Address Type Suffix (%s)', $this->ServiceAddressTypeSuffix));
				}
			} else {
				$this->ServiceAddressTypeSuffix = null;
			}
		} else {
			$this->ServiceAddressType = null;
			$this->ServiceAddressTypeNumber = null;
			$this->ServiceAddressTypeSuffix = null;
		}

		// Street Address
		if ($this->ServiceStreetNumberStart) {
			// ServiceStreetNumberStart
			if (!preg_match('/^[1-9]\d{0,4}$/', $this->ServiceStreetNumberStart)) {
				throw new Exception_Validation(sprintf('Invalid Service Street Start Number (%s)', $this->ServiceStreetNumberStart));
			}

			// ServiceStreetNumberEnd
			if ($this->ServiceStreetNumberEnd) {
				if (!preg_match('/^[1-9]\d{0,4}$/', $this->ServiceStreetNumberEnd) || intval($this->ServiceStreetNumberEnd) <= intval($this->ServiceStreetNumberStart)) {
					throw new Exception_Validation(sprintf('Invalid Service Street End Number (%s)', $this->ServiceStreetNumberEnd));
				}
			} else {
				$this->ServiceStreetNumberEnd = null;
			}

			// ServiceStreetNumberSuffix
			if ($this->ServiceStreetNumberSuffix) {
				if (!preg_match('/^[a-z]$/i', $this->ServiceStreetNumberSuffix)) {
					throw new Exception_Validation(sprintf('Invalid Service Street Number Suffix (%s)', $this->ServiceStreetNumberSuffix));
				}
			} else {
				$this->ServiceStreetNumberSuffix = null;
			}
		} else {
			$this->ServiceStreetNumberStart = null;
			$this->ServiceStreetNumberEnd = null;
			$this->ServiceStreetNumberSuffix = null;
		}

		// ServicePropertyName
		if (!$this->ServicePropertyName) {
			$this->ServicePropertyName = null;
		}

		// ServiceStreetName
		if ($this->ServiceStreetName) {
			if (!$this->ServiceStreetNumberStart) {
				throw new Exception_Validation(sprintf('Missing Street Number Start where Street Name supplied'));
			}
		} else {
			$this->ServiceStreetName = null;
		}

		if ($this->ServiceStreetNumberStart && !$this->ServiceStreetName && !$this->ServicePropertyName) {

		}
	}

	private function _validatePostalAddress() {

	}

	private function _validateAllotmentAddress() {

	}

	public function __get($property) {
		return $this->_orm->$property;
	}

	public function __set($property, $value) {
		$this->_orm->$property = $value;
	}
}