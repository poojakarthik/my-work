
	
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
		// Check common fields
		if (document.getElementById("BillName").value.length == 0)
		{
			alert("Please enter a value for the Bill Name field");
			document.getElementById("BillName").focus();
			return false;
		}
		if (document.getElementById("BillAddress1").value.length == 0)
		{
			alert("Please enter a value for the Bill Address Line 1 field");
			document.getElementById("BillAddress1").focus();
			return false;
		}
		if (document.getElementById("BillLocality").value.length == 0)
		{
			alert("Please enter a value for the Bill Locality field");
			document.getElementById("BillLocality").focus();
			return false;
		}
		if (document.getElementById("BillPostcode").value.length != 4)
		{
			alert("Please enter a 4-digit value for the Bill Postcode field");
			document.getElementById("BillPostcode").focus();
			return false;
		}
		if (document.getElementById("ServiceLocality").value.length == 0)
		{
			alert("Please enter a value for the Service Locality field");
			document.getElementById("ServiceLocality").focus();
			return false;
		}
		if (document.getElementById("ServiceState").options[document.getElementById("ServiceState").selectedIndex].value.length == 0)
		{
			alert("Please enter a value for the Service State field");
			document.getElementById("ServiceState").focus();
			return false;
		}
		if (document.getElementById("ServicePostcode").value.length != 4)
		{
			alert("Please enter a 4-digit value for the Service Postcode field");
			document.getElementById("ServicePostcode").focus();
			return false;
		}
		
		// clean residential/business fields
		if (document.getElementById("Residential:FALSE").checked == true)
		{
			// Business
			
			// check mandatory
			if (document.getElementById("ABN").value.length == 0)
			{
				alert("Please enter a value for the ABN field");
				document.getElementById("ABN").focus();
				return false;
			}
			if (document.getElementById("EndUserCompanyName").value.length == 0)
			{
				alert("Please enter a value for the Company Name field");
				document.getElementById("EndUserCompanyName").focus();
				return false;
			}
		}
		else
		{
			// Residential
			
			// check mandatory
			if (document.getElementById("EndUserTitle").value.length == 0)
			{
				alert("Please enter a value for the Title field");
				document.getElementById("EndUserTitle").focus();
				return false;
			}
			if (document.getElementById("EndUserGivenName").value.length == 0)
			{
				alert("Please enter a value for the Given Name field");
				document.getElementById("EndUserGivenName").focus();
				return false;
			}
			if (document.getElementById("EndUserFamilyName").value.length == 0)
			{
				alert("Please enter a value for the Family Name field");
				document.getElementById("EndUserFamilyName").focus();
				return false;
			}
			/*
			if (document.getElementByName("DateOfBirth[day]").selectedIndex < 1 || 
				document.getElementByName("DateOfBirth[month]").selectedIndex < 1 || 
				document.getElementByName("DateOfBirth[year]").selectedIndex < 1)
			{
				alert("Please enter a value for the Date Of Birth field");
				document.getElementByName("DateOfBirth[day]").focus();
				return false;
			}*/
		}
		
		
		// clean service address fields
		elmServiceAddressType = document.getElementById("ServiceAddressType");
		strServiceAddressType = elmServiceAddressType.options[elmServiceAddressType.selectedIndex].value;
		switch (strServiceAddressType)
		{
			// LOT Addresses
			case "LOT":
				// Check Mandatory Fields
				if (document.getElementById("ServiceAddressTypeNumber").value.length == 0)
				{
					alert("Please enter a value for the Address Type Number field");
					document.getElementById("ServiceAddressTypeNumber").focus();
					return false;
				}
				if (document.getElementById("ServiceAddressType").options[document.getElementById("ServiceAddressType").selectedIndex].value.length == 0)
				{
					alert("Please enter a value for the Address Type field");
					document.getElementById("ServiceAddressType").focus();
					return false;
				}				
				
				// Clean Fields
				document.getElementById("ServiceStreetNumberStart").value	= "";
				document.getElementById("ServiceStreetNumberEnd").value	= "";
				document.getElementById("ServiceStreetNumberSuffix").value = "";
				
				
				// Check dependencies
				if (document.getElementById("ServiceStreetName").value.length > 0)
				{
					if (document.getElementById("ServiceStreetType").selectedIndex < 1)
					{
						alert("Please select a value for the Street Type field");
						document.getElementById("ServiceStreetType").focus();
						return false;
					}
				}
				else
				{
					document.getElementById("ServiceStreetType").value		= "";
					document.getElementById("ServiceStreetTypeSuffix").value	= "";
					if (document.getElementById("ServicePropertyName").value.length == 0)
					{
						alert("Please enter a value for either the Property Name or Street Name field");
						document.getElementById("ServiceStreetName").focus();
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
				document.getElementById("ServiceStreetNumberStart").value		= "";
				document.getElementById("ServiceStreetNumberEnd").value		= "";
				document.getElementById("ServiceStreetNumberSuffix").value	= "";
				document.getElementById("ServiceStreetName").value			= "";
				document.getElementById("ServiceStreetType").value			= "";
				document.getElementById("ServiceStreetTypeSuffix").value		= "";
				document.getElementById("ServicePropertyName").value			= "";
				
				// Check Mandatory Fields
				if (document.getElementById("ServiceAddressTypeNumber").value < 1)
				{
					alert("Please enter a value for the ServiceAddressTypeNumber field");
					document.getElementById("ServiceAddressTypeNumber").focus();
					return false;
				}
				if (document.getElementById("ServiceAddressType").options[document.getElementById("ServiceAddressType").selectedIndex].value.length == 0)
				{
					alert("Please enter a value for the Address Type field");
					document.getElementById("ServiceAddressType").focus();
					return false;
				}				
				break;
				
			// Standard Addresses
			default:				
				// Check dependencies
				if (document.getElementById("ServiceStreetName").value.length > 0)
				{
					if (document.getElementById("ServiceStreetType").value.length == 0)
					{
						alert("Please enter a value for the ServiceStreetType field");
						document.getElementById("ServiceStreetType").focus();
						return false;
					}
				}
				else
				{
					document.getElementById("ServiceStreetType").value		= "";
					document.getElementById("ServiceStreetTypeSuffix").value	= "";
					if (document.getElementById("ServicePropertyName").value.length == 0)
					{
						alert("Please enter a value for either the Property Name or Street Name field");
						document.getElementById("ServiceStreetName").focus();
						return false;
					}
				}
				
				if (document.getElementById("ServiceStreetNumberStart").value.length == 0)
				{
					document.getElementById("ServiceStreetNumberEnd").value		= "";
					document.getElementById("ServiceStreetNumberSuffix").value	= "";
				}
				
				if (document.getElementById("ServicePropertyName").value.length == 0)
				{
					if (document.getElementById("ServiceStreetNumberStart").value.length == 0)
					{
						alert("Please enter a value for the Street Number Start field");
						document.getElementById("ServiceStreetNumberStart").focus();
						return false;
					}
				}
				
				if (strServiceAddressType == "")
				{
					document.getElementById("ServiceAddressTypeNumber").value = "";
					document.getElementById("ServiceAddressTypeSuffix").value = "";
				}
				else
				{
					if (document.getElementById("ServiceAddressTypeNumber").value.length == 0)
					{
						alert("Please enter a value for the Address Type Number field");
						document.getElementById("ServiceAddressTypeNumber").focus();
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
		if (document.getElementById("Residential:FALSE").checked == true)
		{
			ShowBusiness();
		}
		else
		{
			ShowResidential();
		}
		
		SetMandatory("ServiceAddressType"			, false);
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
				SetMandatory("ServiceAddressType", true);
				
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
				SetMandatory("ServiceAddressType", true);
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
					SetMandatory("ServiceStreetNumberStart", false);
				}
				else
				{
					SetMandatory("ServiceStreetName", true);
					SetMandatory("ServiceStreetNumberStart", true);
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
