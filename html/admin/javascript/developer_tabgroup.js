var Developer_TabGroup	= Class.create
({
	initialize	: function()
	{
		// Create Popup
		this._pupPopup	= new Reflex_Popup(40);
		
		this._pupPopup.setTitle("Tab Group Test");
		this._pupPopup.addCloseButton(this.close.bindAsEventListener(this));
		
		// Draw the Popup
		var objPage			= {};
		objPage.domElement	= document.createElement('div');
		
		// Create a Tab Group
		this.objControlTabGroup	= new Control_Tab_Group(objPage.domElement);
		
		//--------------------------------------------------------------------//
		// Create Tabs
		//--------------------------------------------------------------------//
		this.arrTabs	= {};
		
		// Main Tab
		var domTabMain			= document.createElement('div');
		domTabMain.innerHTML	= 'I am in the Main Tab!';
		this.arrTabs['Main']	= new Control_Tab('Main', domTabMain);
		
		// Secondary Tab
		var domTabSecondary			= document.createElement('div');
		domTabSecondary.innerHTML	= 'I am in the Secondary Tab!';
		this.arrTabs['Secondary']	= new Control_Tab('Secondary', domTabSecondary);
		//--------------------------------------------------------------------//
		
		// Display Tab Group
		//this.objControlTabGroup.render();
		
		// Set the Page Object
		this._objPage	= objPage;
		
		// Update the Popup
		this._pupPopup.setContent(this._objPage.domElement);
		this._pupPopup.display();
	},
	
	close			: function()
	{
		// Kill Popup (as much as we can)
		this._pupPopup.setContent('');
		this._pupPopup.hide();
		
		return true;
	}
});