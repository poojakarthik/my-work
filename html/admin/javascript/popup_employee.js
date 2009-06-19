var Popup_Employee	= Class.create(Reflex_Popup,
{
	initialize	: function($super, bolDisplayOnLoad)
	{
		$super();
		
		this.bolDisplayOnLoad	= (bolDisplayOnLoad) ? true : false;
		
		// Load the Employee
		// TODO
		this.buildContent();
	},
	
	buildContent	: function(objResponse)
	{
		// Build Content
		this._objPage			= {};
		this._objPage.domElement	= document.createElement('div');
		
		// Create a Tab Group
		this.objControlTabGroup	= new Control_Tab_Group(this._objPage.domElement);
		
		//--------------------------------------------------------------------//
		// Create Tabs
		//--------------------------------------------------------------------//
		this.arrTabs	= {};
		
		// Details Tab
		var domTabMain			= document.createElement('div');
		domTabMain.innerHTML	= '[Details]';
		this.objControlTabGroup.addTab('Details', new Control_Tab('Details', domTabMain));
		
		// Permission Profiles Tab
		var domTabSecondary			= document.createElement('div');
		domTabSecondary.innerHTML	= '[Profiles]';
		this.objControlTabGroup.addTab('Profiles', new Control_Tab('Permissions Profiles (Simple)', domTabSecondary));
		
		// Permission Operations Tab
		var domTabSecondary			= document.createElement('div');
		domTabSecondary.innerHTML	= '[Operations]';
		this.objControlTabGroup.addTab('Operations', new Control_Tab('Permission Operations (Advanced)', domTabSecondary));
		//--------------------------------------------------------------------//
		
		// Update the Popup
		this._pupPopup.setContent(this._objPage.domElement);
		this._pupPopup.display();
	},
	
	display		: function($super)
	{
		// If we have loaded, then display, otherwise automatically display once loaded
		if (this.objPage)
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