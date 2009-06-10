// Class: Document_Edit
// Handles the creation/editing of Flex Documents
var Developer_OperationPermission	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		// Popup
		this._pupPopup	= new Reflex_Popup(20);
		
		this._pupPopup.setTitle("Operation Permissions Test");
		this._pupPopup.addCloseButton();
		
		this._objPage	= this._buildPopup();
	},
	
	_buildPopup		: function(objResponse)
	{
		if (objResponse == undefined)
		{
			//alert("Getting Data");
			
			// Load all of the required Data
			var fncJsonFunc		= jQuery.json.jsonFunction(Developer_OperationPermission._handleResponse.curry(this._buildPopup), null, 'Developer_Permissions', 'getDetails');
			fncJsonFunc();
		}
		else
		{
			//alert("Drawing Popup");
			
			// Containing DIV
			objPage				= {};
			objPage.domElement	= document.createElement('div');
			
			// Table
			objPage.objTable			= {};
			objPage.objTable.domElement	= document.createElement('table');
			objPage.domElement.appendChild(objPage.objTable.domElement);
			
			// Employee
			objPage.objTable.objEmployeeTR				= {};
			objPage.objTable.objEmployeeTR.domElement	= document.createElement('tr');
			objPage.objTable.domElement.appendChild(objPage.objTable.objEmployeeTR.domElement);
			
			objPage.objTable.objEmployeeTR.objEmployeeSELECT			= {};
			objPage.objTable.objEmployeeTR.objEmployeeSELECT.domElement	= document.createElement('select');
			objPage.objTable.objEmployeeTR.domElement.appendChild(objPage.objTable.objEmployeeTR.objEmployeeSELECT.domElement);
			
			for (i = 0; i < objResponse.arrEmployees.length; i++)
			{
				domEmployeeOption			= document.createElement('option');
				domEmployeeOption.value		= objResponse.arrEmployees[i].Id;
				domEmployeeOption.innerHTML	= objResponse.arrEmployees[i].FirstName + ' ' + objResponse.arrEmployees[i].LastName;
				objPage.objTable.objEmployeeTR.objEmployeeSELECT.domElement.appendChild(domEmployeeOption);
				
				objPage.objTable.objEmployeeTR.objEmployeeSELECT.domElement.selectedIndex	= (objResponse.arrEmployees[i].Id == objResponse.intCurrentEmployeeId) ? i : objPage.objTable.objEmployeeTR.objEmployeeSELECT.domElement.selectedIndex;
			}
			
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