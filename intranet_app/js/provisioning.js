


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
		alert("Showing Residential...");
		
		// show each of the residential-only fields
		document.getElementById("ResidentialSpecific").style.display	= "block";
		
		// hide each of the business-only fields
		document.getElementById("BusinessSpecific").style.isplay		= "none";
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
		alert("Showing Business...");
		
		// show each of the business-only fields
		document.getElementById("BusinessSpecific").style.display		= "block";
		
		// hide each of the residential-only fields
		document.getElementById("ResidentialSpecific").style.display	= "none";
	}

