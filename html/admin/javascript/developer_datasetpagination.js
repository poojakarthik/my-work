var Developer_DatasetPagination	= Class.create
({
	initialize	: function()
	{
		// Init Dataset & Pagination
		this.objDataset		= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_FULL_CACHING, {strObject: 'Employee', strMethod: 'getRecords'});
		this.objPagination	= new Pagination(this._updateTable.bind(this), 30, this.objDataset);
		
		// Init Popup	
		this._pupPopup	= new Reflex_Popup(40);
		
		this._pupPopup.setTitle("Dataset with Pagination Test");
		this._pupPopup.addCloseButton(this.close.bindAsEventListener(this));
		
		this.domPopupSubmitButton		= document.createElement('input');
		this.domPopupSubmitButton.type	= 'button';
		this.domPopupSubmitButton.value	= 'Refresh';
		this.domPopupSubmitButton.addEventListener('click', this.objPagination.getCurrentPage.bind(this.objPagination), false);
		
		this.domPopupCloseButton		= document.createElement('input');
		this.domPopupCloseButton.type	= 'button';
		this.domPopupCloseButton.value	= 'Close';
		this.domPopupCloseButton.addEventListener('click', this.close.bindAsEventListener(this), false);
		
		this._pupPopup.setFooterButtons([this.domPopupSubmitButton, this.domPopupCloseButton], true);
		
		// Draw the Popup
		this._renderPopup();
	},
	
	_renderPopup	: function()
	{
		// Containing DIV
		var objPage			= {};
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
		// Table Header
		//----------------------------------------------------------------//
		objPage.objTable.objTHEAD				= {};
		objPage.objTable.objTHEAD.domElement	= document.createElement('thead');
		objPage.objTable.domElement.appendChild(objPage.objTable.objTHEAD.domElement);
		
		objPage.objTable.objTHEAD.objId							= {};
		objPage.objTable.objTHEAD.objId.domElement				= document.createElement('th');
		objPage.objTable.objTHEAD.objId.domElement.innerHTML	= "Id";
		objPage.objTable.objTHEAD.domElement.appendChild(objPage.objTable.objTHEAD.objId.domElement);
		
		objPage.objTable.objTHEAD.objFirstName						= {};
		objPage.objTable.objTHEAD.objFirstName.domElement			= document.createElement('th');
		objPage.objTable.objTHEAD.objFirstName.domElement.innerHTML	= "First Name";
		objPage.objTable.objTHEAD.domElement.appendChild(objPage.objTable.objTHEAD.objFirstName.domElement);
		
		objPage.objTable.objTHEAD.objLastName						= {};
		objPage.objTable.objTHEAD.objLastName.domElement			= document.createElement('th');
		objPage.objTable.objTHEAD.objLastName.domElement.innerHTML	= "Last Name";
		objPage.objTable.objTHEAD.domElement.appendChild(objPage.objTable.objTHEAD.objLastName.domElement);
		
		objPage.objTable.objTHEAD.objUsername						= {};
		objPage.objTable.objTHEAD.objUsername.domElement			= document.createElement('th');
		objPage.objTable.objTHEAD.objUsername.domElement.innerHTML	= "Username";
		objPage.objTable.objTHEAD.domElement.appendChild(objPage.objTable.objTHEAD.objUsername.domElement);
		//----------------------------------------------------------------//
		
		//----------------------------------------------------------------//
		// Table Body
		//----------------------------------------------------------------//
		objPage.objTable.objTBODY				= {};
		objPage.objTable.objTBODY.domElement	= document.createElement('tbody');
		objPage.objTable.domElement.appendChild(objPage.objTable.objTBODY.domElement);
		//----------------------------------------------------------------//
		
		//----------------------------------------------------------------//
		// Debug "Console"
		//----------------------------------------------------------------//
		objPage.objDebugConsole	= {};
		objPage.objDebugConsole.domElement	= document.createElement('div');
		objPage.objDebugConsole.domElement.style.height				= '10em';
		objPage.objDebugConsole.domElement.style.width				= '100%';
		objPage.objDebugConsole.domElement.style.backgroundColor	= '#fff';
		objPage.objDebugConsole.domElement.style.overflowY			= 'scroll';
		objPage.objDebugConsole.domElement.style.borderColor		= '#000';
		objPage.objDebugConsole.domElement.style.borderWidth		= '1px';
		objPage.objDebugConsole.domElement.style.fontFamily			= '"Courier New", Courier, monospace, sans-serif';
		objPage.domElement.appendChild(objPage.objDebugConsole.domElement);
		//----------------------------------------------------------------//
		
		// Set the Page Object
		this._objPage	= objPage;
		
		// Update the Popup
		this._pupPopup.setContent(this._objPage.domElement);
		this._updateTable();
		this._pupPopup.display();
	},
	
	_updateTable	: function(objResultSet)
	{
		// Dump existing content
		this._objPage.objDebugConsole.domElement.innerHTML	+= "Updating Table content...<br />";
		this._objPage.objTable.objTBODY.domElement.innerHTML	= '';
		
		// Update content
		if (!objResultSet || objResultSet.intTotalResults == 0 || objResultSet.arrResultSet.length == 0)
		{
			this._objPage.objDebugConsole.domElement.innerHTML	+= "&nbsp;&nbsp;&nbsp;&nbsp;[-] No Records to Display<br />";
			
			// No records
			var objTR	= document.createElement('tr');
			
			var objTD				= document.createElement('td');
			objTD.colSpan			= 4;
			objTD.innerHTML			= "There are no records to display.";
			objTD.style.textAlign	= 'center';
			objTR.appendChild(objTD);
			
			this._objPage.objTable.objTBODY.domElement.appendChild(objTR);
		}
		else
		{
			this._objPage.objDebugConsole.domElement.innerHTML	+= "&nbsp;&nbsp;&nbsp;&nbsp;[+] I has Recordz!<br />";
			
			var strDebug	= '';
			for (var i in objResultSet)
			{
				strDebug	+= i + ": " + objResultSet[i] + "\n";
			}
			alert(strDebug);
			
			// I has recordz
			for (var i = 0; i < objResultSet.arrResultSet.length; i++)
			{
				var objTR	= document.createElement('tr');
				
				var objTD				= document.createElement('td');
				objTD.innerHTML			= objResultSet.arrResultSet[i].Id;
				objTR.appendChild(objTD);
				
				var objTD				= document.createElement('td');
				objTD.innerHTML			= objResultSet.arrResultSet[i].FirstName;
				objTR.appendChild(objTD);
				
				var objTD				= document.createElement('td');
				objTD.innerHTML			= objResultSet.arrResultSet[i].LastName;
				objTR.appendChild(objTD);
				
				var objTD				= document.createElement('td');
				objTD.innerHTML			= objResultSet.arrResultSet[i].UserName;
				objTR.appendChild(objTD);
				
				this._objPage.objTable.objTBODY.domElement.appendChild(objTR);
			}
		}
		
		// Update pagination navigation
		// TODO
	},
	
	close			: function()
	{
		// Remove Event Listeners
		this.domPopupSubmitButton.removeEventListener('click', this.objPagination.getCurrentPage.bind(this.objPagination), false);
		this.domPopupCloseButton.removeEventListener('click', this.close.bindAsEventListener(this), false);
		
		// Kill Popup (as much as we can)
		this._pupPopup.setContent('');
		this._pupPopup.hide();
		
		return true;
	},
})