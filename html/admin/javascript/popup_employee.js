var Popup_Employee	= Class.create(Reflex_Popup,
{
	initialize	: function($super, mixEmployee, bolDisplayOnLoad)
	{
		$super(35);
		this.setTitle("Employee");
		this.addCloseButton();
		this.setIcon("../admin/img/template/user_edit.png");
		
		this.domSaveButton				= document.createElement('button');
		this.domSaveButton.innerHTML	= "<img class='icon' src='../admin/img/template/tick.png' alt='' />Save";
		
		this.domCancelButton	= document.createElement('button');
		this.domCancelButton.innerHTML	= "<img class='icon' src='../admin/img/template/delete.png' alt='' />Cancel";
		
		this.setFooterButtons([this.domSaveButton, this.domCancelButton], true);
		
		this.bolDisplayOnLoad	= (bolDisplayOnLoad) ? true : false;
		
		if (mixEmployee instanceof Employee)
		{
			// Employee object passed -- build immediately
			this.objEmployee	= mixEmployee;
			this.buildContent();
		}
		else if (Number(mixEmployee) > 0)
		{
			// Employee Id passed -- load via JSON
			//alert("Loading Employee with Id '" + mixEmployee + "'");
			this.objEmployee	= Employee.getForId(mixEmployee, this.buildContent.bind(this));
		}
		else
		{
			throw "Invalid Employee reference '" + mixEmployee + "'";
			//this.buildContent();
			//throw "mixEmployee is not an Employee Id or Employee Object!";
		}
	},
	
	buildContent	: function()
	{
		// Build Content
		this._objPage			= {};
		this._objPage.domElement	= document.createElement('div');
		
		// Create a Tab Group
		this.objControlTabGroup	= new Control_Tab_Group(this._objPage.domElement, false, true);
		
		//--------------------------------------------------------------------//
		// Create Tabs
		//--------------------------------------------------------------------//
		this.arrTabs	= {};
		
		// Details Tab
		this.objControlTabGroup.addTab('Details', new Control_Tab('Details', this.buildContentDetails(), '../admin/img/template/view.png'));
		
		// Permissions Tab
		this.objControlTabGroup.addTab('Permissions', new Control_Tab('Permissions', this.buildContentPermissions(), '../admin/img/template/key.png'));
		//--------------------------------------------------------------------//
		
		// Update the Popup
		this.setContent(this._objPage.domElement);
		if (this.bolDisplayOnLoad)
		{
			this.display();
		}
		
		return true;
	},
	
	buildContentDetails	: function()
	{
		var objTabPage					= {};
		objTabPage.domElement			= document.createElement('div');
		//objTabPage.domElement.innerHTML	= '[ Details ]';
		
		// Table
		objTabPage.table						= {};
		objTabPage.table.domElement				= document.createElement('table');
		objTabPage.table.domElement.className	= 'input';
		objTabPage.domElement.appendChild(objTabPage.table.domElement);
		
		// Table Body
		objTabPage.table.tbody				= {};
		objTabPage.table.tbody.domElement	= document.createElement('tbody');
		objTabPage.table.domElement.appendChild(objTabPage.table.tbody.domElement);
		
		//----------------------------- CONTENTS -----------------------------//
		
		var objControls	= this.objEmployee.getControls();
		
		// Username
		objTabPage.table.tbody.UserName					= {};
		objTabPage.table.tbody.UserName.objControl		= objControls.UserName;
		objTabPage.table.tbody.UserName.tr				= {};
		objTabPage.table.tbody.UserName.tr.domElement	= objControls.UserName.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.UserName.tr.domElement);
		
		// First Name
		objTabPage.table.tbody.FirstName				= {};
		objTabPage.table.tbody.FirstName.objControl		= objControls.FirstName;
		objTabPage.table.tbody.FirstName.tr				= {};
		objTabPage.table.tbody.FirstName.tr.domElement	= objControls.FirstName.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.FirstName.tr.domElement);
		
		// Last Name
		objTabPage.table.tbody.LastName					= {};
		objTabPage.table.tbody.LastName.objControl		= objControls.LastName;
		objTabPage.table.tbody.LastName.tr				= {};
		objTabPage.table.tbody.LastName.tr.domElement	= objControls.LastName.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.LastName.tr.domElement);
		
		//--------------------------------------------------------------------//
		
		
		return objTabPage.domElement;
	},
	
	buildContentPermissions	: function()
	{
		var objTabPage	= {};
		objTabPage.domElement	= document.createElement('div');
		objTabPage.domElement.innerHTML	= '[ Permissions ]';
		
		return objTabPage.domElement;
	},
	
	display		: function($super)
	{
		// If we have loaded, then display, otherwise automatically display once loaded
		if (this._objPage)
		{
			$super();
			return true;
		}
		else
		{
			this.bolDisplayOnLoad	= true;
			return false;
		}
	}
});