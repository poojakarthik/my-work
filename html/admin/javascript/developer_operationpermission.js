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
		
		this._objPage	= Developer_OperationPermission._buildPopup();
		this._pupPopup.setContent(this._objPage.domElement);
		
		this._pupPopup.display();
	},
	
	_handleResponse	: function()
	{
		
	}
});

Developer_OperationPermission._buildPopup	= function()
{
	if (objResponse == undefined)
	{
		// Load all of the required Data
		// TODO
	}
	
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
	
	return objPage;
}