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
			// Load all of the required Data
			var fncJsonFunc		= jQuery.json.jsonFunction(Developer_OperationPermission._handleResponse.curry(this._buildPopup), null, 'Developer_Permissions', 'getDetails');
			fncJsonFunc();
		}
		else
		{
			alert("Drawing Popup");
			
			// Containing DIV
			objPage	= {};
			objPage.domElement	= document.createElement('div');
			
			// Table
			objPage.objTable.domElement	= document.createElement('table');
			objPage.domElement.appendChild(objPage.objTable.domElement);
			
			// Employee
			objPage.objTable.objEmployeeTR.domElement	= document.createElement('tr');
			objPage.objTable.domElement.appendChild(objPage.objTable.objEmployeeTR.domElement);
			
			objPage.objTable.objEmployeeSELECT.domElement	= document.createElement('select');
			objPage.objTable.objEmployeeTR.domElement.appendChild(objPage.objTable.objEmployeeSELECT.domElement);
			
			for (i = 0; i < objResponse.arrEmployees.length; i++)
			{
				objEmployeeOption			= document.createElement('option');
				objEmployeeOption.value		= objResponse.arrEmployees[i].Id;
				objEmployeeOption.innerHTML	= objResponse.arrEmployees[i].FirstName + ' ' + objResponse.arrEmployees[i].LastName;
				objPage.objTable.objEmployeeTR.domElement.appendChild(objPage.objTable.objEmployeeSELECT.domElement);
				
				objPage.objTable.objEmployeeSELECT.domElement.selectedIndex	= (objResponse.arrEmployees[i].Id == objResponse.intCurrentEmployeeId) ? i : objPage.objTable.objEmployeeSELECT.domElement.selectedIndex;
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
Developer_OperationPermission._handleResponse	= function(objResponse, fncCallback)
{
	//alert(Object.inspect(arguments));
	if (objResponse)
	{
		if (objResponse.Success)
		{
			fncCallback();
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