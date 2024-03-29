var Developer_DatasetPagination	= Class.create
({
	initialize	: function(intCacheMode)
	{
		// Init Dataset & Pagination
		this.objDataset		= new Dataset_Ajax(intCacheMode, {sObject: 'Employee', sMethod: 'getDataSet'});
		this.objDataset.setSortingFields(
			{
				'FirstName'	: 'DESC',
				'LastName'	: 'DESC'
			}
		);
		
		// Testing for followup search
		/*this.objDataset		= new Dataset_Ajax(intCacheMode, {sObject: 'FollowUp', sMethod: 'getDataSet'});
		this.objDataset.setFilter(
			{
				due_datetime			: {mFrom: '2010/01/01', mTo: '2010/08/01'},
				followup_category_id	: [1,2]
			}
		);
		this.objDataset.setSortingFields(
			{
				due_datetime	: 'ASC'
			}
		);*/
		// End Testing for followup search
		
		this.objPagination	= new Pagination(this._updateTable.bind(this), 20, this.objDataset);
		
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
		// Table Footer
		//----------------------------------------------------------------//
		objPage.objTable.objTFOOT				= {};
		objPage.objTable.objTFOOT.domElement	= document.createElement('tfoot');
		objPage.objTable.domElement.appendChild(objPage.objTable.objTFOOT.domElement);
		
		objPage.objTable.objTFOOT.objPagination								= {};
		objPage.objTable.objTFOOT.objPagination.domElement					= document.createElement('th');
		objPage.objTable.objTFOOT.objPagination.domElement.colSpan			= 4;
		objPage.objTable.objTFOOT.objPagination.domElement.style.textAlign	= "right";
		objPage.objTable.objTFOOT.domElement.appendChild(objPage.objTable.objTFOOT.objPagination.domElement);
		
		// First
		objPage.objTable.objTFOOT.objPagination.objFirst							= {};
		objPage.objTable.objTFOOT.objPagination.objFirst.domElement					= document.createElement('span');
		objPage.objTable.objTFOOT.objPagination.objFirst.domElement.innerHTML		= "First";
		objPage.objTable.objTFOOT.objPagination.domElement.appendChild(objPage.objTable.objTFOOT.objPagination.objFirst.domElement);
		
		var objPipe	= document.createElement('span');
		objPipe.innerHTML	= '&nbsp;|&nbsp;';
		objPage.objTable.objTFOOT.objPagination.domElement.appendChild(objPipe);
		
		// Previous
		objPage.objTable.objTFOOT.objPagination.objPrevious							= {};
		objPage.objTable.objTFOOT.objPagination.objPrevious.domElement				= document.createElement('span');
		objPage.objTable.objTFOOT.objPagination.objPrevious.domElement.innerHTML	= "Previous";
		objPage.objTable.objTFOOT.objPagination.domElement.appendChild(objPage.objTable.objTFOOT.objPagination.objPrevious.domElement);
		
		var objPipe	= document.createElement('span');
		objPipe.innerHTML	= '&nbsp;|&nbsp;';
		objPage.objTable.objTFOOT.objPagination.domElement.appendChild(objPipe);
		
		// Next
		objPage.objTable.objTFOOT.objPagination.objNext							= {};
		objPage.objTable.objTFOOT.objPagination.objNext.domElement				= document.createElement('span');
		objPage.objTable.objTFOOT.objPagination.objNext.domElement.innerHTML	= "Next";
		objPage.objTable.objTFOOT.objPagination.domElement.appendChild(objPage.objTable.objTFOOT.objPagination.objNext.domElement);
		
		var objPipe	= document.createElement('span');
		objPipe.innerHTML	= '&nbsp;|&nbsp;';
		objPage.objTable.objTFOOT.objPagination.domElement.appendChild(objPipe);
		
		// Last
		objPage.objTable.objTFOOT.objPagination.objLast							= {};
		objPage.objTable.objTFOOT.objPagination.objLast.domElement				= document.createElement('span');
		objPage.objTable.objTFOOT.objPagination.objLast.domElement.innerHTML	= "Last";
		objPage.objTable.objTFOOT.objPagination.domElement.appendChild(objPage.objTable.objTFOOT.objPagination.objLast.domElement);
		
		objPage.objTable.objTFOOT.objPagination.objFirst.fncCallback	= this.objPagination.firstPage.bind(this.objPagination);
		objPage.objTable.objTFOOT.objPagination.objPrevious.fncCallback	= this.objPagination.previousPage.bind(this.objPagination);
		objPage.objTable.objTFOOT.objPagination.objNext.fncCallback		= this.objPagination.nextPage.bind(this.objPagination);
		objPage.objTable.objTFOOT.objPagination.objLast.fncCallback		= this.objPagination.lastPage.bind(this.objPagination);
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
		objPage.objDebugConsole.domElement.style.display			= "none";
		objPage.domElement.appendChild(objPage.objDebugConsole.domElement);
		//----------------------------------------------------------------//
		
		// Set the Page Object
		this._objPage	= objPage;
		
		// Update the Popup
		this._pupPopup.setContent(this._objPage.domElement);
		this._updateTable();
		this._pupPopup.display();
		
		// Load the data
		this.objPagination.getCurrentPage();
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
			this._objPage.objDebugConsole.domElement.innerHTML	+= "&nbsp;&nbsp;&nbsp;&nbsp;[+] I has " + Object.keys(objResultSet.arrResultSet).length + " Recordz!<br />";
			
			/*var strDebug	= '';
			for (var i in objResultSet.arrResultSet)
			{
				strDebug	+= i + ": " + objResultSet.arrResultSet[i] + "\n";
			}
			alert(strDebug);
			alert(objResultSet.arrResultSet);
			alert(objResultSet.arrResultSet.length);*/
			
			// I has recordz
			for (var i in objResultSet.arrResultSet)
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
		this._updatePagination();
	},
	
	_updatePagination	: function(intPageCount)
	{
		if (intPageCount)
		{
			this._removePaginationEvents();
			
			// Attach onClick Event Handlers
			//alert("Current Page: " + this.objPagination.intCurrentPage);
			//alert("Page Count: " + intPageCount);
			if (this.objPagination.intCurrentPage != Pagination.PAGE_FIRST)
			{
				//alert("This is not the first page (" + this.objPagination.intCurrentPage + " != " + Pagination.PAGE_FIRST +")");
				this._objPage.objTable.objTFOOT.objPagination.objFirst.domElement.setAttribute('onclick', '');
				this._objPage.objTable.objTFOOT.objPagination.objFirst.domElement.addEventListener('click', this._objPage.objTable.objTFOOT.objPagination.objFirst.fncCallback, false);
				
				this._objPage.objTable.objTFOOT.objPagination.objPrevious.domElement.setAttribute('onclick', '');
				this._objPage.objTable.objTFOOT.objPagination.objPrevious.domElement.addEventListener('click', this._objPage.objTable.objTFOOT.objPagination.objPrevious.fncCallback, false);
			}
			if (this.objPagination.intCurrentPage < (intPageCount - 1) && intPageCount)
			{
				//alert("This is not the last page (" + this.objPagination.intCurrentPage + " != " + Pagination.PAGE_LAST +")");
				this._objPage.objTable.objTFOOT.objPagination.objNext.domElement.setAttribute('onclick', '');
				this._objPage.objTable.objTFOOT.objPagination.objNext.domElement.addEventListener('click', this._objPage.objTable.objTFOOT.objPagination.objNext.fncCallback, false);
				
				this._objPage.objTable.objTFOOT.objPagination.objLast.domElement.setAttribute('onclick', '');
				this._objPage.objTable.objTFOOT.objPagination.objLast.domElement.addEventListener('click', this._objPage.objTable.objTFOOT.objPagination.objLast.fncCallback, false);
			}
		}
		else
		{
			// Get the page count
			this.objPagination.getPageCount(this._updatePagination.bind(this));
		}
	},
	
	_removePaginationEvents	: function()
	{
		this._objPage.objTable.objTFOOT.objPagination.objFirst.domElement.removeAttribute('onclick');
		this._objPage.objTable.objTFOOT.objPagination.objFirst.domElement.removeEventListener('click', this._objPage.objTable.objTFOOT.objPagination.objFirst.fncCallback, false);
		
		this._objPage.objTable.objTFOOT.objPagination.objPrevious.domElement.removeAttribute('onclick');
		this._objPage.objTable.objTFOOT.objPagination.objPrevious.domElement.removeEventListener('click', this._objPage.objTable.objTFOOT.objPagination.objPrevious.fncCallback, false);
		
		this._objPage.objTable.objTFOOT.objPagination.objNext.domElement.removeAttribute('onclick');
		this._objPage.objTable.objTFOOT.objPagination.objNext.domElement.removeEventListener('click', this._objPage.objTable.objTFOOT.objPagination.objNext.fncCallback, false);
		
		this._objPage.objTable.objTFOOT.objPagination.objLast.domElement.removeAttribute('onclick');
		this._objPage.objTable.objTFOOT.objPagination.objLast.domElement.removeEventListener('click', this._objPage.objTable.objTFOOT.objPagination.objLast.fncCallback, false);
	},
	
	close			: function()
	{
		this._removePaginationEvents();
		
		// Remove Event Listeners
		this.domPopupSubmitButton.removeEventListener('click', this.objPagination.getCurrentPage.bind(this.objPagination), false);
		this.domPopupCloseButton.removeEventListener('click', this.close.bindAsEventListener(this), false);
		
		// Kill Popup (as much as we can)
		this._pupPopup.setContent('');
		this._pupPopup.hide();
		
		return true;
	},
})