var Popup_Employee	= Class.create(Reflex_Popup,
{
	initialize	: function($super, bolDisplayOnLoad)
	{
		$super(35);
		this.setTitle("Employee");
		
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
		this.objControlTabGroup	= new Control_Tab_Group(this._objPage.domElement, false);
		
		//--------------------------------------------------------------------//
		// Create Tabs
		//--------------------------------------------------------------------//
		this.arrTabs	= {};
		
		// Details Tab
		var domTabDetails			= document.createElement('div');
		domTabDetails.innerHTML	= '[Details]';
		this.objControlTabGroup.addTab('Details', new Control_Tab('Details', domTabDetails));
		
		// Permission Profiles Tab
		var domTabProfiles			= document.createElement('div');
		domTabProfiles.innerHTML	= '[Profiles]';
		this.objControlTabGroup.addTab('Profiles', new Control_Tab('Permissions', domTabProfiles));
		//--------------------------------------------------------------------//
		
		// Update the Popup
		this.setContent(this._objPage.domElement);
		if (this.bolDisplayOnLoad)
		{
			this.display();
		}
		
		return true;
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