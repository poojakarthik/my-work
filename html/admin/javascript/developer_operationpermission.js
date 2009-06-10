// Class: Document_Edit
// Handles the creation/editing of Flex Documents
var Developer_OperationPermission	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		// Popup
		this._pupPopup	= new Reflex_Popup(35);
		
		this._pupPopup.setTitle("Operation Permissions Test");
		this._pupPopup.addCloseButton();
		
		this._buildPopup();
	},
	
	_buildPopup		: function(objResponse)
	{
		if (objResponse == undefined)
		{
			//alert("Getting Data");
			
			// Load all of the required Data
			var fncJsonFunc		= jQuery.json.jsonFunction(Developer_OperationPermission._handleResponse.curry(this._buildPopup.bind(this)), null, 'Developer_Permissions', 'getDetails');
			fncJsonFunc();
		}
		else
		{
			//alert("Drawing Popup");
			
			// Containing DIV
			objPage				= {};
			objPage.domElement	= document.createElement('div');

			//----------------------------------------------------------------//
			// Table
			//----------------------------------------------------------------//
			objPage.objTable			= {};
			objPage.objTable.domElement	= document.createElement('table');
			objPage.domElement.appendChild(objPage.objTable.domElement);
			//----------------------------------------------------------------//
			
			//----------------------------------------------------------------//
			// Employee
			//----------------------------------------------------------------//
			
			// Build Input Element
			domEmployeeSelect		= document.createElement('select');
			domEmployeeSelect.name	= 'employee_id';
			
			for (i = 0; i < objResponse.arrEmployees.length; i++)
			{
				domEmployeeOption			= document.createElement('option');
				domEmployeeOption.value		= objResponse.arrEmployees[i].Id;
				domEmployeeOption.innerHTML	= objResponse.arrEmployees[i].FirstName + ' ' + objResponse.arrEmployees[i].LastName;
				domEmployeeSelect.appendChild(domEmployeeOption);
				
				if (objResponse.arrEmployees[i].Id == objResponse.intCurrentEmployeeId)
				{
					domEmployeeSelect.selectedIndex	= i;
					
					domEmployeeOption.style.color		= '#2861E6';
					domEmployeeOption.style.fontWeight	= 'bold';
				}
			}
			
			// Build TR
			objPage.objTable.objEmployeeTR	= Developer_OperationPermission.outputFieldFactory('Employee', domEmployeeSelect, "Employee to test permission against");
			//----------------------------------------------------------------//
			
			// Operation Profile
			// TODO
			
			// Operation
			// TODO
			
			// Set the Page Object
			this._objPage	= objPage;
			
			// Update the Popup
			this._pupPopup.setContent(this._objPage.domElement);
			this._pupPopup.display();
		}
	}
});

// Static Methods
Developer_OperationPermission._handleResponse	= function(fncCallback, objResponse)
{
	//alert(objResponse);
	//alert(fncCallback);
	if (objResponse)
	{
		if (objResponse.Success)
		{
			//alert("Invoking Callback");
			fncCallback(objResponse);
			return true;
		}
		else if (objResponse.Message)
		{
			$Alert(objResponse.Message);
			return false;
		}
	}
	else
	{
		$Alert(objResponse);
		return false;
	}
}

Developer_OperationPermission.outputFieldFactory	= function(strLabel, domOutputElement, strDescription)
{
	objTR								= {};
	objTR.objTH							= {};
	objTR.objTD							= {};
	objTR.objTD.objOutputDIV			= {};
	objTR.objTD.objOutputDIV.objOutput	= {};
	objTR.objTD.objDescription			= {};
	
	objTR.domElement		= document.createElement('tr');
	
	objTR.objTH.domElement	= document.createElement('th');
	objTR.domElement.appendChild(objTR.objTH.domElement);
	
	objTR.objTD.domElement	= document.createElement('td');
	objTR.domElement.appendChild(objTR.objTD.domElement);
	
	objTR.objTD.objOutputDIV.domElement	= document.createElement('div');
	objTR.objTD.domElement.appendChild(objTR.objTD.objOutputDIV.domElement);
	
	objTR.objTD.objOutputDIV.objOutput.domElement	= domOutputElement;
	objTR.objTD.domElement.appendChild(objTR.objTD.objOutputDIV.objOutput.domElement);
	
	if (strDescription != undefined)
	{
		objTR.objTD.objDescription.domElement						= document.createElement('div')
		objTR.objTD.objDescription.domElement.style.color			= "#666";
		objTR.objTD.objDescription.domElement.style.fontStyle		= "italic";
		objTR.objTD.objDescription.domElement.domElement.innerHTML	= strDescription;
		objTR.objTD.domElement.appendChild(objTR.objTD.objDescription.domElement);
	}
	
	return objTR;
}