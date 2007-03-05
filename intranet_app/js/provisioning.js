
	
 	//------------------------------------------------------------------------//
	// CleanInput
	//------------------------------------------------------------------------//
	/**
	* CleanInput()
	*
	* Cleans the input from our form before sending off
	*
	* Cleans the input from our form before sending off
	*
	* @method
	*/
	function CleanInput()
	{		
		// empty necessary fields
		elmServiceAddressType = document.getElementById("ServiceAddressType");
		strServiceAddressType = elmServiceAddressType.options[elmServiceAddressType.selectedIndex].value;
		
		switch (strServiceAddressType)
		{
			// LOT Addresses
			case "LOT":
				// Check Mandatory Fields
				if (getElementById("ServiceAddressTypeNumber").value.length == 0)
				{
					alert("Please enter a value for the Address Type Number field");
					getElementById("ServiceAddressTypeNumber").focus();
					return false;
				}
				
				// Clean Fields
				getElementById("ServiceStreetNumberStart").value	= "";
				getElementById("ServiceStreetNumberEnd").value	= "";
				getElementById("ServiceStreetNumberSuffix").value = "";
				
				
				// Check dependencies
				if (document.getElementById("ServiceStreetName").value.length > 0)
				{
					if (getElementById("ServiceStreetType").selectedIndex < 1)
					{
						alert("Please select a value for the Street Type field");
						getElementById("ServiceStreetType").focus();
						return false;
					}
				}
				else
				{
					getElementById("ServiceStreetType").value		= "";
					getElementById("ServiceStreetTypeSuffix").value	= "";
					if (getElementById("ServicePropertyName").value.length == 0)
					{
						alert("Please enter a value for either the Property Name or Street Name field");
						getElementById("ServiceStreetName").focus();
						return false;
					}
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
				// Clean fields
				getElementById("ServiceStreetNumberStart").value		= "";
				getElementById("ServiceStreetNumberEnd").value		= "";
				getElementById("ServiceStreetNumberSuffix").value	= "";
				getElementById("ServiceStreetName").value			= "";
				getElementById("ServiceStreetType").value			= "";
				getElementById("ServiceStreetTypeSuffix").value		= "";
				getElementById("ServicePropertyName").value			= "";
				
				// Check Mandatory Fields
				if (getElementById("ServiceAddressTypeNumber").value < 1)
				{
					alert("Please enter a value for the ServiceAddressTypeNumber field");
					getElementById("ServiceAddressTypeNumber").focus();
					return false;
				}
				break;
				
			// Standard Addresses
			default:				
				// Check dependencies
				if (document.getElementById("ServiceStreetName").value.length > 0)
				{
					if (getElementById("ServiceStreetType").value.length == 0)
					{
						alert("Please enter a value for the ServiceStreetType field");
						getElementById("ServiceStreetType").focus();
						return false;
					}
				}
				else
				{
					getElementById("ServiceStreetType").value		= "";
					getElementById("ServiceStreetTypeSuffix").value	= "";
					if (getElementById("ServicePropertyName").value.length == 0)
					{
						alert("Please enter a value for either the Property Name or Street Name field");
						getElementById("ServiceStreetName").focus();
						return false;
					}
				}
				
				if (document.getElementById("ServiceStreetNumberStart").value.length == 0)
				{
					getElementById("ServiceStreetNumberEnd").value		= "";
					getElementById("ServiceStreetNumberSuffix").value	= "";
				}
				
				if (strServiceAddressType == "")
				{
					getElementById("ServiceAddressTypeNumber").value = "";
					getElementById("ServiceAddressTypeSuffix").value = "";
				}
				else
				{
					if (getElementById("ServiceAddressTypeNumber").value.length == 0)
					{
						alert("Please enter a value for the Address Type Number field");
						getElementById("ServiceAddressTypeNumber").focus();
						return false;
					}
				}
				break;
		}
	}


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
	function SetMandatory(strId, bolMandatory)
	{		
		// set the mandatory status
		if (bolMandatory)
		{
			document.getElementById(strId + "Mandatory").style.visibility = "visible";
		}
		else
		{
			document.getElementById(strId + "Mandatory").style.visibility = "hidden";
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
		if (document.getElementById("Residential:FALSE").checked = true)
		{
			ShowBusiness();
		}
		else
		{
			ShowResidential();
		}
		
		SetMandatory("ServiceAddressTypeNumber"	, false);
		SetMandatory("ServiceAddressTypeSuffix"		, false);
		SetMandatory("ServiceStreetNumberStart"	, false);
		SetMandatory("ServiceStreetNumberEnd"		, false);
		SetMandatory("ServiceStreetNumberSuffix"	, false);
		SetMandatory("ServiceStreetName"			, false);
		SetMandatory("ServiceStreetType"			, false);
		SetMandatory("ServiceStreetTypeSuffix"		, false);
		SetMandatory("ServicePropertyName"		, false);
		
		elmServiceAddressType = document.getElementById("ServiceAddressType");
		strServiceAddressType = elmServiceAddressType.options[elmServiceAddressType.selectedIndex].value;
	
		switch (strServiceAddressType)
		{
			// LOT Addresses
			case "LOT":
				// Enable fields
				EnableElementById("ServiceAddressTypeNumber");
				EnableElementById("ServiceAddressTypeSuffix");
				EnableElementById("ServiceStreetName");
				EnableElementById("ServicePropertyName");
				
				// Disable fields
				DisableElementById("ServiceStreetNumberStart");
				DisableElementById("ServiceStreetNumberEnd");
				DisableElementById("ServiceStreetNumberSuffix");
				
				// Set Mandatory Status
				SetMandatory("ServiceAddressTypeNumber", true);
				
				// Check dependencies
				if (document.getElementById("ServiceStreetName").value.length > 0)
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
				
				if (document.getElementById("ServicePropertyName").value.length > 0)
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
				DisableElementById("ServiceStreetNumberStart");
				DisableElementById("ServiceStreetNumberEnd");
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
				EnableElementById("ServiceStreetNumberStart");
				EnableElementById("ServiceStreetName");
				EnableElementById("ServicePropertyName");
				
				// Check dependencies
				if (document.getElementById("ServiceStreetName").value.length > 0)
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
				
				if (document.getElementById("ServiceStreetNumberStart").value.length > 0)
				{
					EnableElementById("ServiceStreetNumberEnd");
					EnableElementById("ServiceStreetNumberSuffix");
				}
				else
				{
					DisableElementById("ServiceStreetNumberEnd");
					DisableElementById("ServiceStreetNumberSuffix");
				}
				
				if (document.getElementById("ServicePropertyName").value.length > 0)
				{
					SetMandatory("ServiceStreetName", false);
				}
				else
				{
					SetMandatory("ServiceStreetName", true);
				}
				
				if (strServiceAddressType == "")
				{
					DisableElementById("ServiceAddressTypeNumber");
					DisableElementById("ServiceAddressTypeSuffix");
				}
				else
				{
					EnableElementById("ServiceAddressTypeNumber");
					EnableElementById("ServiceAddressTypeSuffix");
					SetMandatory("ServiceAddressTypeNumber", true);
				}
				break;
		}
	}



