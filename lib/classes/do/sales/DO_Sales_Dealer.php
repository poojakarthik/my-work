<?php

class DO_Sales_Dealer extends DO_Sales_Base_Dealer
{
	const SYSTEM_DEALER_ID = 1;
	
	private $_arrPermittedProductIds	= null;
	private $_arrPermittedVendorIds		= null;
	private $_arrPermittedSaleTypeIds	= null;
	
	
	public function __set($propertyName, $value)
	{
		if ($value !== null)
		{
			// Only string values need to be sanitized at this high a level.  Everything else is done at a lower level
			switch ($propertyName)
			{
				case 'username':
					// Just remove excess whitespace
					$value = DO_SalesSanitation::removeExcessWhitespace($value);
					break;
				
				case 'password':
					// Leave it alone
					break;
				
				case 'firstName':
					$value = DO_SalesSanitation::cleanPersonFirstName($value);
					break;
					
				case 'lastName':
					$value = DO_SalesSanitation::cleanPersonLastName($value);
					break;

				case 'businessName':
					$value = DO_SalesSanitation::cleanBusinessName($value);
					break;
				
				case 'tradingName':
					$value = DO_SalesSanitation::cleanTradingName($value);
					break;
				
				case 'abn':
					$value = DO_SalesSanitation::cleanABN($value);
					break;

				case 'addressLine1':
				case 'addressLine2':
				case 'postalAddressLine1':
				case 'postalAddressLine2':
					$value = DO_SalesSanitation::cleanAddressLine($value);
					break;
				
				case 'suburb':
				case 'postalSuburb':
					$value = DO_SalesSanitation::cleanAddressSuburb($value);
					break;
				
				case 'postcode':
				case 'postalPostcode':
					$value = DO_SalesSanitation::cleanPostcode($value);
					break;
				
				case 'phone':
				case 'mobile':
				case 'fax':
					$value = DO_SalesSanitation::cleanFNN($value);
					break;

				case 'email':
					$value = DO_SalesSanitation::cleanEmailAddress($value);
					break;

				case 'bankAccountBsb':
					$value = DO_SalesSanitation::cleanBankBSB($value);
					break;
					
				case 'bankAccountNumber':
					$value = DO_SalesSanitation::cleanBankAccountNumber($value);
					break;
	
				case 'bankAccountName':
					$value = DO_SalesSanitation::cleanBankAccountName($value);
					break;
			}
		}
		
		return parent::__set($propertyName, $value);
	}

	// NOTE: This validation is a little weak because we have to accomodate for dealers in other countries 
	// where address, phone numbers and bank account details might be different to the Australian standards
	protected function _isValidValue($propertyName, $value)
	{
		// This bit does low-level validation based on the associated field of the database table that the class represents.
		// It handles things such as string length, data type and nullability constraints
		if (!parent::_isValidValue($propertyName, $value))
		{
			return false;
		}

		if ($value === null)
		{
			// We have already done the low level check to see if the field is manditory, so if the value is still set to null, then it should be considered valid.
			// Although this doesn't take into account scenarios where a value can only be set to null, when some other value is set to a specific value.
			// Validation rules of that nature should be declared in the class' isValid() method
			return true;
		}

		switch ($propertyName)
		{
			case 'username':
				return DO_SalesValidation::isValidUsername($value);
				break;
			
			case 'firstName':
				return DO_SalesValidation::isValidPersonFirstName($value);
				break;

			case 'lastName':
				return DO_SalesValidation::isValidPersonLastName($value);
				break;

			case 'businessName':
				return DO_SalesValidation::isValidBusinessName($value);
				break;
			
			case 'tradingName':
				return DO_SalesValidation::isValidTradingName($value);
				break;

			case 'abn':
				return DO_SalesValidation::isValidABN($value);

			case 'addressLine1':
			case 'addressLine2':
			case 'postalAddressLine1':
			case 'postalAddressLine2':
				return DO_SalesValidation::isValidAddressLine($value);
				break;

			case 'suburb':
			case 'postalSuburb':
				return DO_SalesValidation::isValidAddressSuburb($value);
				break;

			case 'postcode':
			case 'postalPostcode':
				return DO_SalesValidation::isValidPostcode($value);
				break;

			case 'phone':
				return DO_SalesValidation::isValidLandlineFNN($value);
				break;

			case 'mobile':
				return DO_SalesValidation::isValidMobileMSN($value);
				break;

			case 'fax':
				return DO_SalesValidation::isValidLandlineFNN($value);
				break;

			case 'email':
				return DO_SalesValidation::isValidEmailAddress($value);
				break;

			case 'bankAccountBsb':
				return DO_SalesValidation::isValidBankBSB($value);
				break;
				
			case 'bankAccountNumber':
				return DO_SalesValidation::isValidBankAccountNumber($value);
				break;

			case 'bankAccountName':
				return DO_SalesValidation::isValidBankAccountName($value);
				break;

			default:
				// No validation - assume has already been validated
				return true;
		}
	}

	public function isValid($bolThrowException=false)
	{
		if (!parent::isValid($bolThrowException))
		{
			// It's not valid
			return false;
		}
		
		// Make sure the username is unique throughout all dealers, regardless of the status of the dealer (case-insensitive uniqueness)
		// If it didn't have to be case-insensitive, then I could just enforce it using a uniqueness constraint on the dealer.username field
		$ds = self::getDataSource();
		
		// It is assumed that a username has been specified, as it is manditory, and parent::isValid would have failed, if it wasn't specified
		$strUsername = $ds->quote(strtolower($this->username));
		
		// If the object is already saved to the database
		$strOwnRecordException = "";
		if (is_int($this->id))
		{
			// Make the record relating to this object, the exception to the check
			$strOwnRecordException = "AND id != {$this->id}";
		}
		
		$intUsernameCount = self::countFor("LOWER(username) = {$strUsername} {$strOwnRecordException}");

		if ($intUsernameCount)
		{
			// There is at least one other dealer with this same username (when case sensitivity is not considered)
			if ($bolThrowException)
			{
				throw new DO_Exception_Validation($this->getObjectLabel(), "This username is currently in use.  Usernames must be case-insensitive unique.");
			}
			return false;
		}
		return true;
	}

	// Retrieves all dealers who manager other dealers
	public static function listManagers()
	{
		$strDealerTable		= self::getDataSourceObjectName();
		$strDealerIdField	= self::getDataSourceIdName();
		$arrProps			= self::getPropertyDataSourceMappings();
		
		return self::getFor("$strDealerIdField IN (SELECT DISTINCT {$arrProps['upLineId']} FROM $strDealerTable WHERE {$arrProps['upLineId']} IS NOT NULL)", TRUE, "{$arrProps['username']} ASC");
	}
	
	public function listAll($bolIncludeSystemDealer=FALSE)
	{
		$strDealerTable		= self::getDataSourceObjectName();
		$strDealerIdField	= self::getDataSourceIdName();
		$arrProps			= self::getPropertyDataSourceMappings();
		
		$strWhere = ($bolIncludeSystemDealer)? NULL : "$strDealerTable.$strDealerIdField != ". self::SYSTEM_DEALER_ID;
		
		return self::getFor($strWhere, TRUE, "{$arrProps['username']} ASC");
	}

	public static function getForUsername($username)
	{
		$arrWhere = array(	'username'			=> $username,
							'dealerStatusId'	=> DO_Sales_DealerStatus::ACTIVE
						);
		return self::getFor($arrWhere);
	}
	
	public function passwordEquals($password)
	{
			return sha1($password) == $this->password;
	}
	
	public function getPermissions()
	{
		return $this->hasSubordinates() 
					? (Sales_Portal::PERMISSION_UPLINE_DEALER | Sales_Portal::PERMISSION_DEALER) 
					: Sales_Portal::PERMISSION_DEALER;
	}

	public function hasSubordinates()
	{
		static $bolHasSubordinates;
		if (!isset($bolHasSubordinates))
		{
			$bolHasSubordinates = self::countFor(array('upLineId' => $this->id)) > 0;
		}
		return $bolHasSubordinates;
	}
	
	// Returns array containing all Dealers under the management hierarchy of $objDealer
	// the array is not associative
	public function getSubordinates()
	{
		$intId				= intval($this->id);
		$arrProps			= self::getPropertyDataSourceMappings();
		$arrManagedDealers	= self::getFor("{$arrProps['upLineId']} = {$intId}", TRUE);
		
		// Add the dealers immediately under $doDealer's management to the array of all dealers under $doDealer's management
		$arrDealers = $arrManagedDealers;
		
		// For each dealer that is immediately under $doDealer's management, find the dealers under their management (the recursion part)
		foreach ($arrManagedDealers as $doManagedDealer)
		{
			$arrDealers = array_merge($arrDealers, $doManagedDealer->getSubordinates());
		}
		return $arrDealers;
	}
	
	
	public function hasSubordinate($doSubDealer)
	{
		$arrSubs = $this->getSubordinates();
		foreach ($arrSubs as $doSub)
		{
			if ($doSub->id == $doSubDealer->id)
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	// Returns array of all the DO_Sales_SaleType objects, with the id of the records as the key, that the dealer is associated with
	public function getSaleTypes()
	{
		$arrDoDealerSaleTypes = DO_Sales_DealerSaleType::listForDealer($this);
		
		$arrAllSaleTypes = DO_Sales_SaleType::getAll();
		$arrSaleTypesForDealer = array();
		foreach ($arrDoDealerSaleTypes as $doDealerSaleType)
		{
			$arrSaleTypesForDealer[$doDealerSaleType->saleTypeId] = $arrAllSaleTypes[$doDealerSaleType->saleTypeId];
		}
		return $arrSaleTypesForDealer;
	}
	
	// Returns array of all the DO_Sales_Vendor objects, with the id of the records as the key, that the dealer is associated with
	public function getVendors()
	{
		$arrDoDealerVendors = DO_Sales_DealerVendor::listForDealer($this);
		
		$arrAllVendors = DO_Sales_Vendor::getAll();
		$arrVendorsForDealer = array();
		foreach ($arrDoDealerVendors as $doDealerVendor)
		{
			$arrVendorsForDealer[$doDealerVendor->vendorId] = $arrAllVendors[$doDealerVendor->vendorId];
		}
		return $arrVendorsForDealer;
	}
	
	// Will return true if the dealer is permitted to configure sales of this product, else false
	public function hasProductPermission($intProductId)
	{
		if ($this->_arrPermittedProductIds === null)
		{
			$this->_arrPermittedProductIds = array();
			$arrDealerProducts = DO_Sales_DealerProduct::listForDealer($this);
			foreach ($arrDealerProducts as $doDealerProduct)
			{
				$this->_arrPermittedProductIds[] = $doDealerProduct->productId;
			}
		}
		
		return in_array($intProductId, $this->_arrPermittedProductIds);
	}
	
	// Will return true if the dealer is permitted to configure sales for this vendor, else false
	public function hasVendorPermission($intVendorId)
	{
		if ($this->_arrPermittedVendorIds === null)
		{
			$this->_arrPermittedVendorIds = array();
			$arrDealerVendors = DO_Sales_DealerVendor::listForDealer($this);
			foreach ($arrDealerVendors as $doDealerVendor)
			{
				$this->_arrPermittedVendorIds[] = $doDealerVendor->vendorId;
			}
		}
		
		return in_array($intVendorId, $this->_arrPermittedVendorIds);
	}

	// Will return true if the dealer is permitted to configure sales of this sale type, else false
	public function hasSaleTypePermission($intSaleTypeId)
	{
		if ($this->_arrPermittedSaleTypeIds === null)
		{
			$this->_arrPermittedSaleTypeIds = array();
			$arrDealerSaleTypes = DO_Sales_DealerSaleType::listForDealer($this);
			foreach ($arrDealerSaleTypes as $doDealerSaleType)
			{
				$this->_arrPermittedSaleTypeIds[] = $doDealerSaleType->saleTypeId;
			}
		}
		
		return in_array($intSaleTypeId, $this->_arrPermittedSaleTypeIds);
	}
	
	
	// Returns TRUE if the dealer can View the sale.  The requirements being that the sale is associated with them, or any of their subordinates
	public function canViewSale($doSale)
	{
		static $cache;
		if (!isset($cache)) $cache = array();
		if (!array_key_exists($doSale->id, $cache))
		{
			$answer = false;
			if ($this->id == $doSale->createdBy)
			{
				// The dealer created the sale
				$answer = true;
			}
			else
			{
				// Check if any of the dealer's subordinates created the sale
				$arrSubDealers = $this->getSubordinates();
				foreach ($arrSubDealers as $doSubDealer)
				{
					if ($doSubDealer->id == $doSale->createdBy)
					{
						$answer = true;
						break;
					}
				}
			}
			$cache[$doSale->id] = $answer;
		}
		return $cache[$doSale->id];
	}
	
	public function isActive()
	{
		return $this->dealerStatusId == DO_Sales_DealerStatus::ACTIVE;
	}
}

?>