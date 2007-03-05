


 	//------------------------------------------------------------------------//
	// ShowResidential
	//------------------------------------------------------------------------//
	/**
	* ShowResidential()
	*
	* Shows Residential-only fields, and hides Business-only fields
	*
	* Shows Residential-only fields, and hides Business-only fields
	*
	* @method
	*/
	function ShowResidential()
	{		
		// show each of the residential-only fields
		document.getElementById("ResidentialSpecific").style.display	= "block";
		
		// hide each of the business-only fields
		document.getElementById("BusinessSpecific").style.display		= "none";
	}
	
 	//------------------------------------------------------------------------//
	// ShowBusiness
	//------------------------------------------------------------------------//
	/**
	* ShowBusiness()
	*
	* Shows Business-only fields, and hides Residential-only fields
	*
	* Shows Business-only fields, and hides Residential-only fields
	*
	* @method
	*/
	function ShowBusiness()
	{		
		// show each of the business-only fields
		document.getElementById("BusinessSpecific").style.display		= "block";
		
		// hide each of the residential-only fields
		document.getElementById("ResidentialSpecific").style.display	= "none";
	}
	
 	//------------------------------------------------------------------------//
	// EnableElementById
	//------------------------------------------------------------------------//
	/**
	* EnableElementById()
	*
	* Enables an element based on its Id
	*
	* Enables an element based on its Id
	*
	* @param		string	strId		The Id of the element to enable
	*
	* @method
	*/
	function EnableElementById(strId)
	{		
		// show the element
		document.getElementById(strId).disabled = false;
	}
	
 	//------------------------------------------------------------------------//
	// DisableElementById
	//------------------------------------------------------------------------//
	/**
	* DisableElementById()
	*
	* Disables an element based on its Id
	*
	* Disables an element based on its Id
	*
	* @param		string	strId		The Id of the element to disable
	*
	* @method
	*/
	function DisableElementById(strId)
	{		
		// show the element
		document.getElementById(strId).disabled = true;
	}
	
 	//------------------------------------------------------------------------//
	// SetMandatory
	//------------------------------------------------------------------------//
	/**
	* SetMandatory()
	*
	* Sets whether a field is mandatory or not
	*
	* Sets whether a field is mandatory or not
	*
	* @param		bool	bolMandatory		The status to set
	*
	* @method
	*/
	function SetMandatory(bolMandatory)
	{		
		// set the mandatory status
		if (bolMandatory)
		{
			document.getElementById(strId + "Mandatory").style.visibility = true;
		}
		else
		{
			document.getElementById(strId + "Mandatory").style.visibility = false;
		}
	}
	
 	//------------------------------------------------------------------------//
	// UpdateServiceAddress
	//------------------------------------------------------------------------//
	/**
	* UpdateServiceAddress()
	*
	* Enables and Disables certain elements for the Service Address
	*
	* Enables and Disables certain elements for the Service Address
	*
	* @method
	*/
	function UpdateServiceAddress()
	{
		SetMandatory("ServiceAddressTypeNumber"	, false);
		SetMandatory("ServiceAddressTypeSuffix"		, false);
		SetMandatory("ServiceStreetStartNumber"	, false);
		SetMandatory("ServiceStreetEndNumber"		, false);
		SetMandatory("ServiceStreetNumberSuffix"	, false);
		SetMandatory("ServiceStreetName"			, false);
		SetMandatory("ServiceStreetType"			, false);
		SetMandatory("ServiceStreetTypeSuffix"		, false);
		SetMandatory("ServicePropertyName"		, false);
	
		switch (document.getElementById("ServiceAddressType").value)
		{
			// LOT Addresses
			case "LOT":
				// Enable fields
				EnableElementById("ServiceAddressTypeNumber");
				EnableElementById("ServiceAddressTypeSuffix");
				EnableElementById("ServiceStreetName");
				EnableElementById("ServicePropertyName");
				
				// Disable fields
				DisableElementById("ServiceStreetStartNumber");
				DisableElementById("ServiceStreetEndNumber");
				DisableElementById("ServiceStreetNumberSuffix");
				
				// Set Mandatory Status
				SetMandatory("ServiceAddressTypeNumber", true);
				
				// Check dependencies
				if (document.getElementById("ServiceStreetName").length > 0)
				{
					EnableElementById("ServiceStreetType");
					EnableElementById("ServiceStreetTypeSuffix");
					SetMandatory("ServiceStreetType", true);
				}
				else
				{
					DisableElementById("ServiceStreetType");
					DisableElementById("ServiceStreetTypeSuffix");
					SetMandatory("ServicePropertyName", true);
				}
				
				if (document.getElementById("ServiceStreetName").length > 0)
				{
					SetMandatory("ServiceStreetName", false);
				}
				else
				{
					SetMandatory("ServiceStreetName", true);
				}
				
				break;
			
			// Postal Addresses
			case "POB":
			case "PO":
			case "BAG":
			case "CMA":
			case "CMB":
			case "PB":
			case "GPO":
			case "MS":
			case "RMD":
			case "RMB":
			case "LB":
			case "RMS":
			case "RSD":
				// Enable fields
				EnableElementById("ServiceAddressTypeNumber");
				EnableElementById("ServiceAddressTypeSuffix");
				
				// Disable fields
				DisableElementById("ServiceStreetStartNumber");
				DisableElementById("ServiceStreetEndNumber");
				DisableElementById("ServiceStreetNumberSuffix");
				DisableElementById("ServiceStreetName");
				DisableElementById("ServiceStreetType");
				DisableElementById("ServiceStreetTypeSuffix");
				DisableElementById("ServicePropertyName");
				
				// Set Mandatory Status
				SetMandatory("ServiceAddressTypeNumber", true);
				break;
				
			// Standard Addresses
			default:
				// Enable fields
				EnableElementById("ServiceStreetStartNumber");
				EnableElementById("ServiceStreetName");
				EnableElementById("ServicePropertyName");
				
				// Check dependencies
				if (document.getElementById("ServiceStreetName").length > 0)
				{
					EnableElementById("ServiceStreetType");
					EnableElementById("ServiceStreetTypeSuffix");
					SetMandatory("ServiceStreetType", true);
				}
				else
				{
					DisableElementById("ServiceStreetType");
					DisableElementById("ServiceStreetTypeSuffix");
					SetMandatory("ServicePropertyName", true);
				}
				
				if (document.getElementById("ServicePropertyName").length > 0)
				{
					SetMandatory("ServiceStreetName", false);
				}
				else
				{
					SetMandatory("ServiceStreetName", true);
				}
				
				if (document.getElementById("ServiceAddressType").value == null)
				{
					EnableElementById("ServiceAddressTypeNumber");
					EnableElementById("ServiceAddressTypeSuffix");
					SetMandatory("ServiceAddressTypeNumber", true);
				}
				else
				{
					DisableElementById("ServiceAddressTypeNumber");
					DisableElementById("ServiceAddressTypeSuffix");
				}
				break;
		}
	}
	
	UpdateServiceAddress();



