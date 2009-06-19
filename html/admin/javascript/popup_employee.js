var Popup_Employee	= Class.create(Reflex_Popup,
{
	initialize	: function($super, intEmployeeId, bolDisplayOnLoad)
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
		
		if (intEmployeeId)
		{
			// Load the Employee
			// TODO
			this.buildContent();
		}
		else
		{
			this.buildContent();
		}
	},
	
	buildContent	: function(objResponse)
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
		var objTabPage	= {};
		objTabPage.domElement	= document.createElement('div');
		objTabPage.domElement.innerHTML	= '[ Details ]';
		
		objTabPage.objTable	= {};
		
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