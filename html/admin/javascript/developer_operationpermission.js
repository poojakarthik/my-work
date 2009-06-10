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
		
		domPopupSubmitButton		= document.createElement('input');
		domPopupSubmitButton.type	= 'button';
		domPopupSubmitButton.value	= 'Test!';
		domPopupSubmitButton.setAttribute('onclick', this._submit.bind(this));
		
		domPopupCloseButton			= document.createElement('input');
		domPopupCloseButton.type	= 'button';
		domPopupCloseButton.value	= 'Close';
		domPopupCloseButton.setAttribute('onclick', this._pupPopup.hide.bind(this._pupPopup));
		
		this._pupPopup.setFooterButtons([domPopupSubmitButton, domPopupCloseButton], true);
		
		this._buildPopup();
	},
	
	_submit			: function()
	{
		alert("Submitting");
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
			objPage.objTable						= {};
			objPage.objTable.domElement				= document.createElement('table');
			objPage.objTable.domElement.className	= 'reflex';
			objPage.domElement.appendChild(objPage.objTable.domElement);
			//----------------------------------------------------------------//
			
			//----------------------------------------------------------------//
			// Employee
			//----------------------------------------------------------------//
			
			// Build Input Element
			domEmployeeSelect				= document.createElement('select');
			domEmployeeSelect.name			= 'employee_id';
			domEmployeeSelect.style.width	= '100%';
			
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
			
			// Build TR & attach to DOM
			objPage.objTable.objEmployeeTR	= Developer_OperationPermission.outputFieldFactory('Employee', domEmployeeSelect, "Employee to test permission against");
			objPage.objTable.domElement.appendChild(objPage.objTable.objEmployeeTR.domElement);
			//----------------------------------------------------------------//

			//----------------------------------------------------------------//
			// Operation
			//----------------------------------------------------------------//

			// Build Input Element
			domOperationSelect				= document.createElement('select');
			domOperationSelect.name			= 'operation_id';
			domOperationSelect.style.width	= '100%';
			
			for (i = 0; i < objResponse.arrOperations.length; i++)
			{
				domOperationOption				= document.createElement('option');
				domOperationOption.value		= objResponse.arrOperations[i].id;
				domOperationOption.innerHTML	= objResponse.arrOperations[i].name;
				domOperationSelect.appendChild(domOperationOption);
			}
			
			// Build TR & attach to DOM
			objPage.objTable.objOperationTR	= Developer_OperationPermission.outputFieldFactory('Operation', domOperationSelect, "Operation to test");
			objPage.objTable.domElement.appendChild(objPage.objTable.objOperationTR.domElement);
			//----------------------------------------------------------------//
			
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
	
	objTR.objTH.domElement						= document.createElement('th');
	objTR.objTH.domElement.innerHTML			= strLabel;
	objTR.objTH.domElement.style.verticalAlign	= 'top';
	objTR.objTH.domElement.style.textAlign		= 'right';
	objTR.domElement.appendChild(objTR.objTH.domElement);
	
	objTR.objTD.domElement	= document.createElement('td');
	objTR.objTD.domElement.style.verticalAlign	= 'top';
	objTR.objTD.domElement.style.textAlign		= 'left';
	objTR.domElement.appendChild(objTR.objTD.domElement);
	
	objTR.objTD.objOutputDIV.domElement	= document.createElement('div');
	objTR.objTD.domElement.appendChild(objTR.objTD.objOutputDIV.domElement);
	
	objTR.objTD.objOutputDIV.objOutput.domElement	= domOutputElement;
	objTR.objTD.domElement.appendChild(objTR.objTD.objOutputDIV.objOutput.domElement);
	
	if (strDescription != undefined)
	{
		objTR.objTD.objDescription.domElement					= document.createElement('div')
		objTR.objTD.objDescription.domElement.style.color		= "#666";
		objTR.objTD.objDescription.domElement.style.fontStyle	= "italic";
		objTR.objTD.objDescription.domElement.style.fontSize	= "0.8em";
		objTR.objTD.objDescription.domElement.innerHTML			= strDescription;
		objTR.objTD.domElement.appendChild(objTR.objTD.objDescription.domElement);
	}
	
	return objTR;
}