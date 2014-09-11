<?php

class DO_Sales_SaleItemServiceLandline extends DO_Sales_Base_SaleItemServiceLandline
{
	public function __set($propertyName, $value)
	{
		if ($value !== null)
		{
			switch($propertyName)
			{
				case 'fnn':
					$value = DO_SalesSanitation::cleanFNN($value);
					break;
					
				case 'billName':
					// Treat like a business name
					$value = DO_SalesSanitation::cleanBusinessName($value);
					break;
					
				case 'billAddressLine1':
				case 'billAddressLine2':
					$value = DO_SalesSanitation::cleanAddressLine($value);
					break;
					
				case 'billLocality':
				case 'serviceLocality':
					$value = DO_SalesSanitation::cleanAddressLocality($value);
					break;
					
				case 'billPostcode':
				case 'servicePostcode':
					$value = DO_SalesSanitation::cleanPostcode($value);
					break;
					
				case 'serviceAddressTypeSuffix':
				case 'serviceStreetNumberSuffix':
					// Convert to upper case
					$value = strtoupper(trim($value));
					break;
					
				case 'serviceStreetName':
				case 'servicePropertyName':
					// These should be proper nouns
					$value = DO_SalesSanitation::fixProperNounCasing(DO_SalesSanitation::removeExcessWhitespace($value));
					break;
			}
		}
		
		return parent::__set($propertyName, $value);
	}

	protected function _isValidValue($propertyName, $value)
	{
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
			case 'fnn':
				return DO_SalesValidation::isValidLandlineFNN($value);

			case 'hasExtensionLevelBilling':
				// ExtensionLevelBilling is only applicable if isIndial100 == true (ExtensionLevelBilling should be nullible for when isIndial100 == false, but currently isn't nullible)
				return (($this->isIndial100 === true) || ($value === false));

			case 'billName':
				// Treat just like business names
				return DO_SalesValidation::isValidBusinessName($value);
				
			case 'billAddressLine1':
			case 'billAddressLine2':
				return DO_SalesValidation::isValidAddressLine($value);
				
			case 'billLocality':
			case 'serviceLocality':
				return DO_SalesValidation::isValidAddressLocality($value);

			case 'billPostcode':
			case 'servicePostcode':
				return DO_SalesValidation::isValidPostcode($value);

			case 'serviceAddressTypeNumber':
				// Must be 1 <= X <= 99999 (webill requirement)
				return (DO_SalesValidation::isValidPositiveInteger($value) && ($value >= 1) && ($value <= 99999));

			case 'serviceAddressTypeSuffix':
			case 'serviceStreetNumberSuffix':
				// Must be upper case alphabet chars only
				return preg_match("/^[A-Z]+$/", $value);

			case 'serviceStreetNumberStart':
				// Must be 1 <= X <= 99999 (webill requirement)
				return (DO_SalesValidation::isValidPositiveInteger($value) && ($value >= 1) && ($value <= 99999));
				
			case 'serviceStreetNumberEnd':
				// Must be serviceStreetNumberStart < X <= 99999 (webill requirement)
				return (DO_SalesValidation::isValidPositiveInteger($this->serviceStreetNumberStart) && ($this->serviceStreetNumberStart >= 1) && DO_SalesValidation::isValidPositiveInteger($value) && ($value > $this->serviceStreetNumberStart) && ($value <= 99999));

			case 'serviceStreetName':
				return self::isValidServiceStreetName($value);
				
			case 'servicePropertyName':
				return self::isValidServicePropertyName($value);
				
			default:
				// No validation - assume is correct
				return true;
		}
	}
	
	public static function isValidServiceStreetName($strName)
	{
		// Check that the string is not empty and has no leading or trailing whitespace
		if (!DO_SalesValidation::isTrimmed($strName) || $strName === '')
		{
			return false;
		}
		
		// Make sure there are only legal chars in it (legal characters being the alphabet, spaces, periods, hyphens and apostrophes)
		return preg_match("/^(?:[A-Za-z\\.\\-\\' ]+)$/", $strName) ? true : false;
	}

	public static function isValidServicePropertyName($strName)
	{
		// Check that the string is not empty and has no leading or trailing whitespace
		if (!DO_SalesValidation::isTrimmed($strName) || $strName === '')
		{
			return false;
		}
		
		// Make sure there are only legal chars in it (legal characters being the alphabet, digits, spaces, periods, hyphens and apostrophes)
		return preg_match("/^(?:[A-Za-z0-9\\.\\-\\' ]+)$/", $strName) ? true : false;
	}
	
	// This will nullify fields that aren't needed, based on the important fields: landlineServiceAddressTypeId, its category of address type, whether 
	// or not a street name has been supplied, etc
	public function sanitise()
	{
		// Low level sanitation of the properties has already been done, when they were initially set using the __set() method
		
		/*
		 * Interesting side note:
		 * 
		 * I was thinking about making a method called _mustBeNull($strPropertyName) which would determine whether the individual property should be null, but this 
		 * cannot be done, because it would be dependent on the values other fields, so you couldn't just iterate through all the properties and check if each one should
		 * be set to null, and then set it to null
		 */
		
		if ($this->landlineServiceAddressTypeId === null)
		{
			// No address type has been specified
			$this->serviceAddressTypeNumber		= null;
			$this->serviceAddressTypeSuffix		= null;
		}
		else
		{
			// An address type has been specified
			$objLandlineServiceAddressType				= $this->getLandlineServiceAddressType();
			$intLandlineServiceAddressTypeCategoryId	= $objLandlineServiceAddressType->landlineServiceAddressTypeCategoryId;
			
			if ($intLandlineServiceAddressTypeCategoryId == DO_Sales_LandlineServiceAddressTypeCategory::POSTAL)
			{
				// ServiceAddressType is a POSTAL address
				// Nullify the fields that aren't used for postal addresses
				$this->serviceStreetNumberStart				= null;
				$this->serviceStreetNumberEnd				= null;
				$this->serviceStreetNumberSuffix			= null;
				$this->serviceStreetName					= null;
				$this->landlineServiceStreetTypeId			= null;
				$this->landlineServiceStreetTypeSuffixId	= null;
				$this->servicePropertyName					= null;
			}
			else
			{
				// ServiceAddressType is not a postal address type, and can therefore have street details
				if ($intLandlineServiceAddressTypeCategoryId == DO_Sales_LandlineServiceAddressTypeCategory::ALLOTMENT)
				{
					// ALLOTMENTs do not have Street numbers
					$this->serviceStreetNumberStart		= null;
					$this->serviceStreetNumberEnd		= null;
					$this->serviceStreetNumberSuffix	= null;
				}
				else
				{
					// ServiceAddressType must be a STANDARD address type 
					// (Nothing to do here, exclusive to this category of address type)
				}
				
				// Do other things common to ALLOTMENT & STANDARD address types 
				// (Nothing to add here, which can't be expressed in simpler rules independent of the category of address type) 
			}			
		}

		// Nullify things that are dependent on serviceStreetName being set
		if ($this->serviceStreetName === null)
		{
			$this->landlineServiceStreetTypeId			= null;
			$this->landlineServiceStreetTypeSuffixId	= null;
		}
		
		// Nullify things that are dependent on landlineServiceStreetTypeId being set
		if ($this->landlineServiceStreetTypeId === null)
		{
			$this->landlineServiceStreetTypeSuffixId = null;
		}
		
		if ($this->serviceStreetName === null && $this->servicePropertyName === null)
		{
			$this->serviceStreetNumberStart = null;
		}
		
		// Nullify things that are dependent on serviceStreetNumberStart being set
		if ($this->serviceStreetNumberStart === null)
		{
			$this->serviceStreetNumberEnd		= null;
			$this->serviceStreetNumberSuffix	= null;
		}
		
		if ($this->isIndial100 != true)
		{
			// hasExtensionLevelBilling is only applicable if isIndial100 == true (currently hasExtensionLevelBilling cannot be set to null, although it probably should be able to be)
			$this->hasExtensionLevelBilling = false;
		}
	}
	
	
	// We must run the high level sanitation method before saving
	public function save()
	{
		$this->sanitise();
		return parent::save();
	}
	
	// It is recommended the sanitise() method be run, prior to this method being run
	public function isValid($bolThrowException=false)
	{
		$arrProblems = array();
		
		try
		{
			// Do standard validation
			if (!parent::isValid($bolThrowException))
			{
				// This is caught in this try block which will return false because $bolThrowException == false
				throw new DO_Exception_Validation($this->getObjectLabel(), "Something was invalid, but bolThrowException == false, so a DO_Exception_Validation wasn't thrown before now");
			}
	
			// Retrieve the associated sale item if one has been set (sometimes this isn't set, if we are just validating an object that hasn't been saved to the database yet)
			if ($this->isSaved() || $this->saleItemId !== null)
			{
				$doSaleItem = $this->getSaleItem();
				
				// Retrieve the associated product (we are not validating the sale item so don't bother checking if $doSaleItem.productId is set)
				$doProduct = $doSaleItem->getProduct();
		
				// Make sure the product relating to this object (via the sale item) is of product_type 'Landline' (product_type.id == 1)
				if ($doProduct->productTypeId != DO_Sales_ProductType::SERVICE_LANDLINE)
				{
					$arrProblems[] = "The related product '{$doProduct->name}' isn't a landline service.";
				}
			}
			
			// Iterate through each property, and see if it is manditory, optional or must not be set.
			$intAddressTypeCategoryId = null;
			if ($this->landlineServiceAddressTypeId !== null)
			{
				// An address type has been specified.  Find its category (POSTAL, ALLOTMENT, STANDARD)
				$objAddressType				= $this->getLandlineServiceAddressType();
				$intAddressTypeCategoryId	= $objAddressType->landlineServiceAddressTypeCategoryId;
			}
			
			$arrProperties = array(
									'serviceAddressTypeNumber',
									'serviceAddressTypeSuffix',
									'landlineServiceStreetTypeId',
									'landlineServiceStreetTypeSuffixId',
									'serviceStreetNumberEnd',
									'serviceStreetNumberSuffix',
									'servicePropertyName',
									'serviceStreetName',
									'serviceStreetNumberStart'
									);
			foreach ($arrProperties as $strProperty)
			{
				$bolPropertyIsSet = ($this->{$strProperty} !== null)? true : false;
				
				$bolMustBeNull	= false;
				$bolMustBeSet	= false;
				
				// Note that these rules won't report on everything that is invalid, but if anything is invalid, it will be gauranteed to be caught somewhere
				/*
				 * For example, if both serviceStreetNumberEnd and serviceStreetNumberStart are specified, but the address is a postal address,
				 * it will only report on the the serviceStreetNumberStart property being invalid.  It will not consider serviceStreetNumberEnd to be invalid even
				 * though it is
				 */  
				switch ($strProperty)
				{
					case 'serviceAddressTypeNumber':
						// Manditory if landlineServiceAddressTypeId is set, otherwise it must be null 
						if ($this->landlineServiceAddressTypeId !== null)
						{
							$bolMustBeSet = true;
						}
						else
						{
							$bolMustBeNull = true;
						}
						break;
						
					case 'serviceAddressTypeSuffix':
						// Must be null if landlineServiceAddressTypeId is not set, otherwise optional
						if ($this->landlineServiceAddressTypeId === null)
						{
							$bolMustBeNull = true;
						}
						break;
						
					case 'landlineServiceStreetTypeId':
						// Manditory if serviceStreetName is set, otherwise must be null
						if ($this->serviceStreetName !== null)
						{
							$bolMustBeSet = true;
						}
						else
						{
							$bolMustBeNull = true;
						}
						break;

					case 'landlineServiceStreetTypeSuffixId':
						// Must be null if landlineServiceStreetTypeId is not set, otherwise optional
						if ($this->landlineServiceStreetTypeId === null)
						{
							$bolMustBeNull = true;
						}
						break;

					case 'serviceStreetNumberEnd':
					case 'serviceStreetNumberSuffix':
						// Must be null if serviceStreetNumberStart is not set, otherwise optional
						if ($this->serviceStreetNumberStart === null)
						{
							$bolMustBeNull = true;
						}
						break;
						
					case 'serviceStreetName':
						// Must be null if it is a postal address
						// Must be set if it is not a postal address and property name is not set
						// Otherwise it's optional
						if ($intAddressTypeCategoryId == DO_Sales_LandlineServiceAddressTypeCategory::POSTAL)
						{
							$bolMustBeNull = true;
						}
						elseif ($this->servicePropertyName === null)
						{
							$bolMustBeSet = true;
						}
						break;
						
					case 'servicePropertyName':
						// Must be null if it is a postal address
						// Must be set if it is not a postal address and service street name is not set
						// Otherwise it's optional
						if ($intAddressTypeCategoryId == DO_Sales_LandlineServiceAddressTypeCategory::POSTAL)
						{
							$bolMustBeNull = true;
						}
						elseif ($this->serviceStreetName === null)
						{
							$bolMustBeSet = true;
						}
						break;
						
					case 'serviceStreetNumberStart':
						// Must be null IF it is a postal address OR an allotment address
						// Must be set IF (it is NOT a postal OR allotment address) AND servicePropertyName is not set
						// Otherwise optional
						if ($intAddressTypeCategoryId == DO_Sales_LandlineServiceAddressTypeCategory::POSTAL || $intAddressTypeCategoryId == DO_Sales_LandlineServiceAddressTypeCategory::ALLOTMENT)
						{
							$bolMustBeNull = true;
						}
						elseif ($this->servicePropertyName === null)
						{
							$bolMustBeSet = true;
						}
						break;
						
					default:
						throw new Exception(__METHOD__ ." :: Unaccounted for property '{$strProperty}' when checking if it is manditory, optional or must not be set");
				}
				
				if ($bolMustBeSet && !$bolPropertyIsSet)
				{
					// The field is manditory but it is currently set to null
					$arrProblems[] = $this->getPropertyLabel($strProperty) . " cannot be null.";
				}
				if ($bolMustBeNull && $bolPropertyIsSet)
				{
					// The field must be null, but it is currently set to something other than null
					$arrProblems[] = $this->getPropertyLabel($strProperty) . " must be null.";
				}
			}
		}
		catch (DO_Exception_Validation $e)
		{
			// Add the problems to the greater list of problems
			$arrProblems = array_merge($arrProblems, $e->errors);
		}

		if (count($arrProblems))
		{
			// Problems were encountered
			if (!$bolThrowException)
			{
				return false;
			}
			throw new DO_Exception_Validation($this->getObjectLabel(), $arrProblems);
		}
		
		return true;



		// ******************************** END OF NEW isValid() method and START OF OLD ONE ******************************************

		
		
		if (!parent::isValid($bolThrowException))
		{
			return false;
		}
		
		// TODO! I think all this function has to do is check which fields are manditory and which ones should be null
		// the sanitise() method will set the ones that should be null, to null, so if they are still not set to null, then that's invalid
		// Perhaps the sanitise() method should be declared in the DO_Base class, but not many objects should require sanitation at a level above what is handled by the __set() method 
		
		// We now need to check all of the properties for this record to ensure that the business rules are obeyed
		
		$arrProblems = array();
		
		$landlineServiceAddressType = $this->getLandlineServiceAddressType();

		// Validate the service's physical address
		$landlineServiceAddressTypeCategory = $landlineServiceAddressType ? $landlineServiceAddressType->getLandlineServiceAddressTypeCategory() : null;
		if ($landlineServiceAddressType)
		{
			// An Address Type has been specified
			if (!$this->serviceAddressTypeNumber)
			{
				$arrProblems[] = "Address Type Number must be declared";
			}
			if ($this->serviceAddressTypeSuffix && !preg_match("/^[a-z]*$/i", $this->serviceAddressTypeSuffix))
			{
				$arrProblems[] = "Address Type Suffix must consist only of letters";
			}
		}
		else
		{
			// No address type has been specified
			$this->landlineServiceAddressTypeId		= null;
			$this->serviceAddressTypeNumber			= null;
			$this->serviceAddressTypeSuffix			= null;
		}

		if ($landlineServiceAddressTypeCategory->name == 'Postal')
		{
			// ServiceAddressType is a postal address
			// null the fields that aren't used for postal addresses
			$this->serviceStreetNumberStart		= null;
			$this->serviceStreetNumberEnd		= null;
			$this->serviceStreetNumberSuffix	= null;
			$this->serviceStreetName			= null;
			$this->landlineServiceStreetTypeId	= null;
			$this->serviceStreetTypeSuffix		= null;		// TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! This property doesn't even exist! 
															// I think we are thinking of landlineServiceStreetTypeSuffixId
			$this->servicePropertyName			= null;
		}
		else
		{
			// ServiceAddressType is not a postal address type, and can therefore have street details
			if ($landlineServiceAddressTypeCategory->name == 'Allotment')
			{
				// LOTs do not have Street numbers
				$this->serviceStreetNumberStart		= null;
				$this->serviceStreetNumberEnd		= null;
				$this->serviceStreetNumberSuffix	= null;
			}
			else //  Must be a standard address
			{
				// Validate the Street Number
				if ($this->serviceStreetNumberStart === null)
				{
					// Street Number Start has not been specified
					if ($this->serviceStreetName && !$this->servicePropertyName)
					{
						$arrProblems[] = "Street Number Start must be declared";
					}
				}
				else if ($this->serviceStreetNumberStart <= 0)
				{
					$arrProblems[] = "Street Number Start is invalid";
				}
				// If an end number has been declared
				else if ($this->serviceStreetNumberEnd !== null && $this->serviceStreetNumberEnd <= $this->serviceStreetNumberStart)
				{
					// The end number is less than or equal to the start number
					$arrProblems[] = "Street Number End must be greater than Street Number Start";
				}
			}
			
			if (!$this->serviceStreetName && !$this->servicePropertyName)
			{
				$arrProblems[] = "At least one of the fields 'Street Name' or 'Property Name' must be specified";
			}
			else if ($this->serviceStreetName)
			{
				// A street name has been declared
				// You don't need to test the ServiceStreetType as it is always valid
				$landlineServiceStreetType = $this->getLandlineServiceStreetType();
				if (!$landlineServiceStreetType)
				{
					$arrProblems[] = "'Service Street Type' must be specified when 'Street Name' is specified";
				}
				else if ($landlineServiceStreetType->description == 'Not Required')
				{
					// Suffix is not required
					$this->serviceStreetTypeSuffix = null;
				}
			}
			else
			{
				// A street name has not been declared, so street attributes are irrelevant
				$this->landlineServiceStreetTypeId	= null;
				$this->serviceStreetTypeSuffix		= null;
				
				$this->serviceStreetNumberStart		= null;
				$this->serviceStreetNumberEnd		= null;
				$this->serviceStreetNumberSuffix	= null;
			}
		}
		
		if (!$this->serviceLocality)
		{
			$arrProblems[] = "Physical Address Locality must be declared";
		}
		
		$state = $this->getLandlineServiceState();
		if (!$state)
		{
			$arrProblems[] = "Physical Address State must be declared";
		}

		if (!$this->servicePostcode)
		{
			$arrProblems[] = "Physical Address Postcode must be declared";
		}
		
		if (!count($arrProblems)) return true;
		
		if ($bolThrowException)
		{
			throw new Exception("The landline service address is invalid: -\n\t" . implode("\n\t", $arrProblems));
		}
		else
		{
			return false;
		}
		
		return (count($arrProblems)) ? FALSE : TRUE;
	}


	// This will also nullify those fields that aren't required based on the more important fields.
	// For example, if $this->landlineServiceAddressTypeId == null, then it will nullify serviceAddressTypeNumber and serviceAddressTypeSuffix
/*	public function isValid($bolThrowException=false)
	{
		if (!parent::isValid($bolThrowException))
		{
			return false;
		}
		
		// We now need to check all of the properties for this record to ensure that the business rules are obeyed
		
		$arrProblems = array();
		
		$landlineServiceAddressType = $this->getLandlineServiceAddressType();

		// Validate the service's physical address
		$landlineServiceAddressTypeCategory = $landlineServiceAddressType ? $landlineServiceAddressType->getLandlineServiceAddressTypeCategory() : null;
		if ($landlineServiceAddressType)
		{
			// An Address Type has been specified
			if (!$this->serviceAddressTypeNumber)
			{
				$arrProblems[] = "Address Type Number must be declared";
			}
			if ($this->serviceAddressTypeSuffix && !preg_match("/^[a-z]*$/i", $this->serviceAddressTypeSuffix))
			{
				$arrProblems[] = "Address Type Suffix must consist only of letters";
			}
		}
		else
		{
			// No address type has been specified
			$this->landlineServiceAddressTypeId		= null;
			$this->serviceAddressTypeNumber			= null;
			$this->serviceAddressTypeSuffix			= null;
		}

		if ($landlineServiceAddressTypeCategory->name == 'Postal')
		{
			// ServiceAddressType is a postal address
			// null the fields that aren't used for postal addresses
			$this->serviceStreetNumberStart		= null;
			$this->serviceStreetNumberEnd		= null;
			$this->serviceStreetNumberSuffix	= null;
			$this->serviceStreetName			= null;
			$this->landlineServiceStreetTypeId	= null;
			$this->serviceStreetTypeSuffix		= null;
			$this->servicePropertyName			= null;
		}
		else
		{
			// ServiceAddressType is not a postal address type, and can therefore have street details
			if ($landlineServiceAddressTypeCategory->name == 'Allotment')
			{
				// LOTs do not have Street numbers
				$this->serviceStreetNumberStart		= null;
				$this->serviceStreetNumberEnd		= null;
				$this->serviceStreetNumberSuffix	= null;
			}
			else //  Must be a standard address
			{
				// Validate the Street Number
				if ($this->serviceStreetNumberStart === null)
				{
					// Street Number Start has not been specified
					if ($this->serviceStreetName && !$this->servicePropertyName)
					{
						$arrProblems[] = "Street Number Start must be declared";
					}
				}
				else if ($this->serviceStreetNumberStart <= 0)
				{
					$arrProblems[] = "Street Number Start is invalid";
				}
				// If an end number has been declared
				else if ($this->serviceStreetNumberEnd !== null && $this->serviceStreetNumberEnd <= $this->serviceStreetNumberStart)
				{
					// The end number is less than or equal to the start number
					$arrProblems[] = "Street Number End must be greater than Street Number Start";
				}
			}
			
			if (!$this->serviceStreetName && !$this->servicePropertyName)
			{
				$arrProblems[] = "At least one of the fields 'Street Name' or 'Property Name' must be specified";
			}
			else if ($this->serviceStreetName)
			{
				// A street name has been declared
				// You don't need to test the ServiceStreetType as it is always valid
				$landlineServiceStreetType = $this->getLandlineServiceStreetType();
				if (!$landlineServiceStreetType)
				{
					$arrProblems[] = "'Service Street Type' must be specified when 'Street Name' is specified";
				}
				else if ($landlineServiceStreetType->description == 'Not Required')
				{
					// Suffix is not required
					$this->serviceStreetTypeSuffix = null;
				}
			}
			else
			{
				// A street name has not been declared, so street attributes are irrelevant
				$this->landlineServiceStreetTypeId	= null;
				$this->serviceStreetTypeSuffix		= null;
				
				$this->serviceStreetNumberStart		= null;
				$this->serviceStreetNumberEnd		= null;
				$this->serviceStreetNumberSuffix	= null;
			}
		}
		
		if (!$this->serviceLocality)
		{
			$arrProblems[] = "Physical Address Locality must be declared";
		}
		
		$state = $this->getLandlineServiceState();
		if (!$state)
		{
			$arrProblems[] = "Physical Address State must be declared";
		}

		if (!$this->servicePostcode)
		{
			$arrProblems[] = "Physical Address Postcode must be declared";
		}
		
		if (!count($arrProblems)) return true;
		
		if ($bolThrowException)
		{
			throw new Exception("The landline service address is invalid: -\n\t" . implode("\n\t", $arrProblems));
		}
		else
		{
			return false;
		}
		
		return (count($arrProblems)) ? FALSE : TRUE;
	}
*/
	
	//------------------------------------------------------------------------//
	// getForSaleItem
	//------------------------------------------------------------------------//
	/**
	 * getForSaleItem()
	 *
	 * Returns a DO_Sales_SaleItemServiceLandline object corresponding to the passed DO_Sales_SaleItem object
	 *
	 * Returns a DO_Sales_SaleItemServiceLandline object corresponding to the passed DO_Sales_SaleItem object
	 * There should only ever be one sale_item_service_landline object relating to a sale_item
	 * 
	 * @param	DO_Sales_SaleItem	$doSaleItem					The sale item representing an intance of a Landline Service product
	 * @param	boolean				$bolExceptionOnNotFound		If TRUE, then throws an exception if the sale_item_service_landline record can't be found
	 * 															If FALSE, then returns NULL if the sale_item_service_landline record can't be found
	 * 
	 * @return	mixed				DO_Sales_SaleItemServiceLandline object when found
	 * 								NULL when can't be found and $bolExceptionOnNotFound == FALSE
	 *
	 * @method
	 */
	public static function getForSaleItem(DO_Sales_SaleItem $doSaleItem, $bolExceptionOnNotFound=FALSE)
	{
		$arrSaleItems = self::listForSaleItem($doSaleItem);
		
		$intCount = count($arrSaleItems);
		
		if ($intCount == 0)
		{
			if ($bolExceptionOnNotFound)
			{
				throw new Exception("Cannot find SaleItemServiceLandline object for SaleItem with id: {$doSaleItem->id}");
			}
			else
			{
				return NULL;
			}
		}
		elseif ($intCount > 1)
		{
			throw new Exception("Found multiple SaleItemServiceLandline objects ($intCount found) relating to the SaleItem with id: {$doSaleItem->id}");
		}
		else
		{
			return current($arrSaleItems);
		}
	}

}

?>
