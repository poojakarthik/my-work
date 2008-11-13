<?php

class DO_Sales_SaleItemServiceLandline extends DO_Sales_Base_SaleItemServiceLandline
{
	protected function _isValidValue($propertyName, $value)
	{
		if (!parent::_isValidValue($propertyName, $value))
		{
			return false;
		}

		switch ($propertyName)
		{

			case 'serviceAddressTypeNumber':
				return $value === null || intval($value) > 0;

			case 'fnn':
				return preg_match("/^0[12378]\\d{8}$/", $value);

			case 'serviceAddressTypeSuffix':
			case 'serviceStreetNumberSuffix':
				return ($value == null) || preg_match("/^[A-Z]+$/", $value);

			case 'billPostcode':
			case 'servicePostcode':
				return preg_match("/^\\d{4}$/", $value);

			default:
				// No validation - assume is correct already as is not for data source
				return true;

		}
	}
	
	public function __set($propertyName, $value)
	{
		if (is_string($value))
		{
			$value = trim($value);
			if ($value == '')
			{
				$value = null;
			}
		}
		
		switch($propertyName)
		{
			// Convert to upper case, those values that should be in upper case
			case 'serviceAddressTypeSuffix':
			case 'serviceStreetNumberSuffix':
				if (is_string($value)) $value = strtoupper($value);
				break;
				
		}
		
		return parent::__set($propertyName, $value);
	}
	
	public function isValid($bolThrowException=false)
	{
		if (!parent::isValid($bolThrowException))
		{
			return false;
		}
		
		// We now need to check all of the properties for this record to ensure that the 
		// business rules are obeyed.
		
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
					if ($this->serviceStreetName)
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
	

}

?>
