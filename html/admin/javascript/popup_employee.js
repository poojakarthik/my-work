var Popup_Employee	= Class.create(Reflex_Popup,
{
	initialize	: function($super, bolRenderMode, mixEmployee, bolDisplayOnLoad)
	{
		$super(35);
		this.setTitle("Employee");
		this.addCloseButton();
		this.setIcon("../admin/img/template/user_edit.png");
		
		this.domEditButton				= document.createElement('button');
		this.domEditButton.innerHTML	= "<img class='icon' src='../admin/img/template/user_edit.png' alt='' />Edit";
		
		this.domCloseButton				= document.createElement('button');
		this.domCloseButton.innerHTML	= "Close";
		
		this.domSaveButton				= document.createElement('button');
		this.domSaveButton.innerHTML	= "<img class='icon' src='../admin/img/template/tick.png' alt='' />Save";
		
		this.domCancelButton			= document.createElement('button');
		this.domCancelButton.innerHTML	= "<img class='icon' src='../admin/img/template/delete.png' alt='' />Cancel";
		
		this.addEventListeners();
		
		this.setFooterButtons([this.domEditButton, this.domCloseButton], true);
		
		this.bolDisplayOnLoad	= (bolDisplayOnLoad || bolDisplayOnLoad === undefined) ? true : false;
		
		this.bolInitialRenderMode	= bolRenderMode;
		
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
		else if (bolRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			// New Employee
			this.objEmployee	= new Employee();
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
		this.arrTabs.Details	= this.buildContentDetails();
		this.objControlTabGroup.addTab('Details', new Control_Tab('Details', this.arrTabs.Details.domElement, '../admin/img/template/view.png'));
		
		// Permissions Tab
		this.arrTabs.Permissions	= this.buildContentPermissions();
		this.objControlTabGroup.addTab('Permissions', new Control_Tab('Permissions', this.arrTabs.Permissions.domElement, '../admin/img/template/key.png'));
		//--------------------------------------------------------------------//
		
		this.setControlMode(this.bolInitialRenderMode);
		
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
		objTabPage.table.tbody.UserName.tr				= objControls.UserName.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.UserName.tr.domElement);
		
		// First Name
		objTabPage.table.tbody.FirstName				= {};
		objTabPage.table.tbody.FirstName.objControl		= objControls.FirstName;
		objTabPage.table.tbody.FirstName.tr				= objControls.FirstName.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.FirstName.tr.domElement);
		
		// Last Name
		objTabPage.table.tbody.LastName					= {};
		objTabPage.table.tbody.LastName.objControl		= objControls.LastName;
		objTabPage.table.tbody.LastName.tr				= objControls.LastName.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.LastName.tr.domElement);
		
		// Date of Birth
		objTabPage.table.tbody.DOB					= {};
		objTabPage.table.tbody.DOB.objControl		= objControls.DOB;
		objTabPage.table.tbody.DOB.tr				= objControls.DOB.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.DOB.tr.domElement);
		
		// Email
		objTabPage.table.tbody.Email				= {};
		objTabPage.table.tbody.Email.objControl		= objControls.Email;
		objTabPage.table.tbody.Email.tr				= objControls.Email.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.Email.tr.domElement);
		
		// Extension
		objTabPage.table.tbody.Extension				= {};
		objTabPage.table.tbody.Extension.objControl		= objControls.Extension;
		objTabPage.table.tbody.Extension.tr				= objControls.Extension.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.Extension.tr.domElement);
		
		// Phone
		objTabPage.table.tbody.Phone				= {};
		objTabPage.table.tbody.Phone.objControl		= objControls.Phone;
		objTabPage.table.tbody.Phone.tr				= objControls.Phone.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.Phone.tr.domElement);
		
		// Mobile
		objTabPage.table.tbody.Mobile					= {};
		objTabPage.table.tbody.Mobile.objControl		= objControls.Mobile;
		objTabPage.table.tbody.Mobile.tr				= objControls.Mobile.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.Mobile.tr.domElement);
		
		// Password
		objTabPage.table.tbody.PassWord				= {};
		objTabPage.table.tbody.PassWord.objControl	= objControls.PassWord;
		objTabPage.table.tbody.PassWord.tr			= objControls.PassWord.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.PassWord.tr.domElement);
		
		// Password Confirm
		objTabPage.table.tbody.PassWordConfirm				= {};
		objTabPage.table.tbody.PassWordConfirm.objControl	= objControls.PassWordConfirm;
		objTabPage.table.tbody.PassWordConfirm.tr			= objControls.PassWordConfirm.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.PassWordConfirm.tr.domElement);
		
		// Role
		/*objTabPage.table.tbody.user_role_id					= {};
		objTabPage.table.tbody.user_role_id.objControl		= objControls.user_role_id;
		objTabPage.table.tbody.user_role_id.tr				= objControls.user_role_id.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.user_role_id.tr.domElement);*/
		
		// Archived
		objTabPage.table.tbody.Archived				= {};
		objTabPage.table.tbody.Archived.objControl	= objControls.Archived;
		objTabPage.table.tbody.Archived.tr			= objControls.Archived.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.Archived.tr.domElement);
		
		//--------------------------------------------------------------------//
		
		objControls.PassWord.setValidateFunction(this._passwordConfirm.bind(this));
		objControls.PassWord.setDependant(objControls.PassWordConfirm);
		objControls.PassWordConfirm.setValidateFunction(this._passwordConfirm.bind(this));
		objControls.PassWordConfirm.setDependant(objControls.PassWord);
		
		return objTabPage;
	},
	
	_passwordConfirm	: function()
	{
		//alert(this.arrTabs.Details.table.tbody.PassWordConfirm.objControl.getElementValue() + " === " + this.arrTabs.Details.table.tbody.PassWord.objControl.getElementValue());
		return (this.arrTabs.Details.table.tbody.PassWordConfirm.objControl.getElementValue() === this.arrTabs.Details.table.tbody.PassWord.objControl.getElementValue());
	},
	
	buildContentPermissions	: function()
	{
		var objTabPage	= {};
		objTabPage.domElement	= document.createElement('div');
		objTabPage.domElement.innerHTML	= '[ Permissions ]';
		
		return objTabPage;
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
	},
	
	setControlMode	: function(bolControlMode)
	{
		//alert('Set Control Mode...');
		switch (bolControlMode)
		{
			case Control_Field.RENDER_MODE_EDIT:
				// Change footer buttons
				this.setFooterButtons([this.domSaveButton, this.domCancelButton], true);
				
				// Show "Confirm Password"
				this.arrTabs.Details.table.tbody.PassWordConfirm.tr.domElement.style.display	= 'table-row';
				break;
				
			case Control_Field.RENDER_MODE_VIEW:
				// Change footer buttons
				this.setFooterButtons([this.domEditButton, this.domCloseButton], true);
				
				// Hide "Confirm Password"
				this.arrTabs.Details.table.tbody.PassWordConfirm.tr.domElement.style.display	= 'none';
				break;
			
			default:
				throw "Invalid Control Mode '" + bolControlMode + "'";
		}
		
		for (i in this.arrTabs.Details.table.tbody)
		{
			if (i != 'domElement')
			{
				this.arrTabs.Details.table.tbody[i].objControl.setRenderMode(bolControlMode);
			}
		}
	},
	
	hide	: function($super)
	{
		this.removeEventListeners();
		$super();
	},
	
	addEventListeners	: function()
	{
		this.arrEventHandlers						= {};
		this.arrEventHandlers.setControlModeEdit	= this.setControlMode.bind(this, Control_Field.RENDER_MODE_EDIT);
		this.arrEventHandlers.setControlModeView	= this.setControlMode.bind(this, Control_Field.RENDER_MODE_VIEW);
		this.arrEventHandlers.hide					= this.hide.bind(this);
		this.arrEventHandlers.save					= $Alert.curry('Saving!');
		
		this.domEditButton.addEventListener('click'		, this.arrEventHandlers.setControlModeEdit	, false);
		this.domCancelButton.addEventListener('click'	, this.arrEventHandlers.setControlModeView	, false);
		this.domCloseButton.addEventListener('click'	, this.arrEventHandlers.hide				, false);
		this.domSaveButton.addEventListener('click'		, this.arrEventHandlers.save				, false);
	},
	
	removeEventListeners	: function()
	{
		this.domEditButton.removeEventListener('click'		, this.arrEventHandlers.setControlModeEdit	, false);
		this.domCancelButton.removeEventListener('click'	, this.arrEventHandlers.setControlModeView	, false);
		this.domCloseButton.removeEventListener('click'		, this.arrEventHandlers.hide				, false);
		this.domSaveButton.removeEventListener('click'		, this.arrEventHandlers.save				, false);
	}
});